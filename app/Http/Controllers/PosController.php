<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        $query = Product::with('category')->where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->get();

        return view('pos.index', compact('categories', 'products'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'in:cash,qris'],
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ]);

        // FR-06 / Kriteria #25: jika permintaan ini sudah pernah diproses
        // (klik ganda / retry akibat koneksi terputus), kembalikan transaksi
        // yang sama tanpa membuat transaksi baru.
        $idempotencyKey = $request->input('idempotency_key');
        if ($idempotencyKey) {
            $existing = $this->findExistingSale($idempotencyKey);
            if ($existing) {
                return $this->saleResponse($request, $existing);
            }
        }

        $items = $request->input('items');
        $paymentMethod = $request->input('payment_method');
        $cashAmount = (float) $request->input('payment_amount', 0);

        try {
            $sale = DB::transaction(function () use ($items, $paymentMethod, $cashAmount, $idempotencyKey) {
                $calculatedSubtotal = 0;
                $itemsToProcess = [];

                // 1. Lock and validate each product
                foreach ($items as $itemData) {
                    $product = Product::where('id', $itemData['id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (!$product->is_active) {
                        throw ValidationException::withMessages([
                            'items' => "Produk {$product->name} berstatus nonaktif dan tidak dapat dijual.",
                        ]);
                    }

                    $qty = (int) $itemData['quantity'];

                    if ($product->current_stock < $qty) {
                        throw ValidationException::withMessages([
                            'items' => "Stok produk '{$product->name}' tidak mencukupi. Sisa stok: {$product->current_stock}, diminta: {$qty}.",
                        ]);
                    }

                    $unitPrice = (float) $product->selling_price;
                    $lineSubtotal = round($unitPrice * $qty, 2);
                    $calculatedSubtotal += $lineSubtotal;

                    $itemsToProcess[] = [
                        'product' => $product,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'subtotal' => $lineSubtotal,
                    ];
                }

                $total = round($calculatedSubtotal, 2);

                // 2. Validate Payment
                if ($paymentMethod === 'cash') {
                    if ($cashAmount < $total) {
                        throw ValidationException::withMessages([
                            'payment_amount' => "Jumlah uang tunai (Rp " . number_format($cashAmount, 0, ',', '.') . ") kurang dari total belanja (Rp " . number_format($total, 0, ',', '.') . ").",
                        ]);
                    }
                    $changeAmount = round($cashAmount - $total, 2);
                } else {
                    // QRIS
                    $cashAmount = $total;
                    $changeAmount = 0;
                }

                // 3. Generate unique transaction number TRX-YYYYMMDD-XXXX
                $transactionNumber = $this->generateTransactionNumber();

                // 4. Create Sale
                $sale = Sale::create([
                    'transaction_number' => $transactionNumber,
                    'idempotency_key' => $idempotencyKey,
                    'cashier_id' => Auth::id(),
                    'transaction_date' => now(),
                    'subtotal' => $calculatedSubtotal,
                    'total' => $total,
                    'payment_method' => $paymentMethod,
                    'payment_amount' => $cashAmount,
                    'change_amount' => $changeAmount,
                    'status' => 'completed',
                ]);

                // 5. Create Sale Items, Deduct Stock, and Record Stock Movements
                foreach ($itemsToProcess as $item) {
                    /** @var Product $product */
                    $product = $item['product'];
                    $qty = $item['quantity'];

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'product_name_snapshot' => $product->name,
                        'sku_snapshot' => $product->sku,
                        'quantity' => $qty,
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    $stockBefore = $product->current_stock;
                    $product->current_stock -= $qty;
                    $product->save();
                    $stockAfter = $product->current_stock;

                    StockMovement::create([
                        'product_id' => $product->id,
                        'movement_type' => 'sale',
                        'quantity_change' => -$qty,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'reason' => 'Penjualan POS No. ' . $sale->transaction_number,
                        'user_id' => Auth::id(),
                        'created_at' => now(),
                    ]);
                }

                // 6. Record Audit Log
                AuditLog::record(
                    'sale_completed',
                    $sale,
                    null,
                    [
                        'transaction_number' => $sale->transaction_number,
                        'total' => $sale->total,
                        'payment_method' => $sale->payment_method,
                        'items_count' => count($itemsToProcess),
                    ],
                    "Transaksi penjualan berhasil diselesaikan oleh " . Auth::user()->name
                );

                return $sale;
            });
        } catch (QueryException $e) {
            // Balapan konkuren dengan idempotency key yang sama: ambil transaksi
            // yang sudah lebih dulu tersimpan, jangan buat duplikat.
            if ($idempotencyKey && $this->isUniqueConstraintViolation($e)) {
                $existing = $this->findExistingSale($idempotencyKey);
                if ($existing) {
                    return $this->saleResponse($request, $existing);
                }
            }

            throw $e;
        }

        return $this->saleResponse($request, $sale);
    }

    /**
     * Cari transaksi milik kasir yang sedang login berdasarkan idempotency key.
     */
    private function findExistingSale(string $key): ?Sale
    {
        return Sale::where('idempotency_key', $key)
            ->where('cashier_id', Auth::id())
            ->first();
    }

    /**
     * Nomor transaksi unik dengan format TRX-YYYYMMDD-XXXX.
     */
    private function generateTransactionNumber(): string
    {
        $datePrefix = Carbon::now()->format('Ymd');
        $countToday = Sale::whereDate('transaction_date', Carbon::today())->count() + 1;
        $transactionNumber = sprintf('TRX-%s-%04d', $datePrefix, $countToday);

        while (Sale::where('transaction_number', $transactionNumber)->exists()) {
            $countToday++;
            $transactionNumber = sprintf('TRX-%s-%04d', $datePrefix, $countToday);
        }

        return $transactionNumber;
    }

    /**
     * Deteksi pelanggaran constraint unik (duplikat) dari database.
     */
    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        return ($e->errorInfo[1] ?? null) === 1062;
    }

    /**
     * Bangun respons konsisten untuk JSON (POS terminal) maupun redirect biasa.
     */
    private function saleResponse(Request $request, Sale $sale)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'transaction_number' => $sale->transaction_number,
                'receipt_url' => route('sales.show', $sale),
                'print_url' => route('sales.print', $sale),
            ]);
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Transaksi berhasil disimpan!');
    }
}
