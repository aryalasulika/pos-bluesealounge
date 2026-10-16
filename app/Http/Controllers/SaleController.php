<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['cashier', 'cancelledByUser', 'items.product'])->latest('transaction_date');

        // If Cashier and policy allows only their own, or let Cashier see all sales (PRD: "Riwayat transaksi sesuai kewenangan")
        if (Auth::user()->isCashier()) {
            $query->where('cashier_id', Auth::id());
        } elseif ($request->filled('cashier_id')) {
            $query->where('cashier_id', $request->cashier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('transaction_number', 'like', "%{$search}%");
        }

        $sales = $query->paginate(15)->withQueryString();
        $cashiers = User::where('role', 'cashier')->get();

        return view('sales.index', compact('sales', 'cashiers'));
    }

    public function show(Sale $sale)
    {
        $this->authorizeView($sale);

        $sale->load(['cashier', 'cancelledByUser', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    public function print(Sale $sale)
    {
        $this->authorizeView($sale);

        $sale->load(['cashier', 'items.product']);

        return view('sales.print', compact('sale'));
    }

    /**
     * FR-08: struk hanya dapat dibuka oleh pengguna yang berwenang.
     * Admin dapat melihat semua struk, sedangkan Kasir hanya struk miliknya.
     */
    private function authorizeView(Sale $sale): void
    {
        $user = Auth::user();

        if ($user->isCashier() && $sale->cashier_id !== $user->id) {
            abort(403, 'Anda hanya dapat membuka struk transaksi milik Anda sendiri.');
        }
    }

    public function cancel(Request $request, Sale $sale)
    {
        // Enforce Admin only
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Hanya Administrator yang memiliki wewenang membatalkan transaksi.');
        }

        $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        return DB::transaction(function () use ($sale, $request) {
            // Lock sale
            $lockedSale = Sale::where('id', $sale->id)->lockForUpdate()->firstOrFail();

            if ($lockedSale->isCancelled()) {
                throw ValidationException::withMessages([
                    'cancellation_reason' => 'Transaksi ini sudah pernah dibatalkan sebelumnya.',
                ]);
            }

            $beforeData = [
                'status' => $lockedSale->status,
                'cancelled_by' => $lockedSale->cancelled_by,
                'cancelled_at' => $lockedSale->cancelled_at,
            ];

            // 1. Return stock for each item
            $lockedSale->load('items');
            foreach ($lockedSale->items as $item) {
                if ($item->product_id) {
                    $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                    if ($product) {
                        $stockBefore = $product->current_stock;
                        $product->current_stock += $item->quantity;
                        $product->save();
                        $stockAfter = $product->current_stock;

                        StockMovement::create([
                            'product_id' => $product->id,
                            'movement_type' => 'cancellation_return',
                            'quantity_change' => $item->quantity,
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockAfter,
                            'reference_type' => 'sale_cancellation',
                            'reference_id' => $lockedSale->id,
                            'reason' => 'Pengembalian stok pembatalan transaksi #' . $lockedSale->transaction_number . ': ' . $request->cancellation_reason,
                            'user_id' => Auth::id(),
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            // 2. Update sale status
            $lockedSale->update([
                'status' => 'cancelled',
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
                'cancellation_reason' => $request->cancellation_reason,
            ]);

            // 3. Record Audit Log
            AuditLog::record(
                'sale_cancelled',
                $lockedSale,
                $beforeData,
                [
                    'status' => 'cancelled',
                    'cancelled_by' => Auth::id(),
                    'cancelled_at' => now()->toDateTimeString(),
                    'reason' => $request->cancellation_reason,
                ],
                "Transaksi #{$lockedSale->transaction_number} dibatalkan oleh " . Auth::user()->name . ". Alasan: " . $request->cancellation_reason
            );

            return redirect()->route('sales.show', $lockedSale)->with('success', 'Transaksi berhasil dibatalkan dan stok produk telah dikembalikan ke sistem.');
        });
    }
}
