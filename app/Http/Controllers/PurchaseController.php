<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['recordedByUser', 'items.product'])->latest('purchase_date');

        if ($request->filled('supplier_name')) {
            $query->where('supplier_name', 'like', '%' . trim($request->supplier_name) . '%');
        }

        if ($request->filled('start_date')) {
            $query->whereDate('purchase_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('purchase_date', '<=', $request->end_date);
        }

        $purchases = $query->paginate(15)->withQueryString();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('admin.purchases.index', compact('purchases', 'products'));
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['recordedByUser', 'items.product']);

        return view('admin.purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load('items');
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('admin.purchases.edit', compact('purchase', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePurchase($request);

        return DB::transaction(function () use ($validated) {
            $purchase = Purchase::create([
                'purchase_number' => $this->generatePurchaseNumber(),
                'purchase_date' => $validated['purchase_date'],
                'supplier_name' => $validated['supplier_name'] ?? null,
                'total_amount' => $this->calculateTotal($validated['items']),
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => Auth::id(),
            ]);

            $this->syncItems($purchase, $validated['items']);

            AuditLog::record(
                'purchase_recorded',
                $purchase,
                null,
                $this->snapshot($purchase),
                "Pencatatan pembelian barang #{$purchase->purchase_number} total Rp " . number_format($purchase->total_amount, 0, ',', '.') . " (Catatan: Pembelian tidak menambah stok otomatis sesuai SOP)"
            );

            return redirect()->route('purchases.show', $purchase)->with('success', "Pencatatan pembelian {$purchase->purchase_number} berhasil disimpan.");
        });
    }

    /**
     * FR-12: koreksi catatan pembelian yang dapat ditelusuri.
     */
    public function update(Request $request, Purchase $purchase)
    {
        $validated = $this->validatePurchase($request);

        return DB::transaction(function () use ($validated, $purchase) {
            $purchase->load('items');
            $before = $this->snapshot($purchase);

            $purchase->update([
                'purchase_date' => $validated['purchase_date'],
                'supplier_name' => $validated['supplier_name'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total_amount' => $this->calculateTotal($validated['items']),
            ]);

            // Ganti rincian barang dengan data terbaru.
            $purchase->items()->delete();
            $this->syncItems($purchase, $validated['items']);

            $purchase->load('items');

            AuditLog::record(
                'purchase_updated',
                $purchase,
                $before,
                $this->snapshot($purchase),
                "Koreksi catatan pembelian #{$purchase->purchase_number} oleh " . Auth::user()->name
            );

            return redirect()->route('purchases.show', $purchase)->with('success', "Catatan pembelian {$purchase->purchase_number} berhasil dikoreksi.");
        });
    }

    /**
     * FR-12 / BR-09: pembelian tidak dihapus permanen, tetapi di-soft delete
     * agar riwayat tetap tersimpan dan tindakan terdokumentasi di audit log.
     */
    public function destroy(Purchase $purchase)
    {
        $purchase->load('items');
        $before = $this->snapshot($purchase);

        $purchase->delete();

        AuditLog::record(
            'purchase_deleted',
            $purchase,
            $before,
            ['deleted_at' => now()->toDateTimeString()],
            "Catatan pembelian #{$purchase->purchase_number} dihapus (soft delete) oleh " . Auth::user()->name
        );

        return redirect()->route('purchases.index')->with('success', "Catatan pembelian {$purchase->purchase_number} berhasil dihapus. Data historis tetap tersimpan untuk audit.");
    }

    private function validatePurchase(Request $request): array
    {
        return $request->validate([
            'purchase_date' => ['required', 'date'],
            'supplier_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required', 'string', 'max:150'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit' => ['required', 'string', 'max:30'],
            'items.*.purchase_price' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function calculateTotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $total += ((int) $item['quantity']) * ((float) $item['purchase_price']);
        }

        return round($total, 2);
    }

    private function syncItems(Purchase $purchase, array $items): void
    {
        foreach ($items as $item) {
            $qty = (int) $item['quantity'];
            $price = (float) $item['purchase_price'];

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $item['product_id'] ?? null,
                'product_name_snapshot' => $item['product_name'],
                'quantity' => $qty,
                'unit' => $item['unit'],
                'purchase_price' => $price,
                'subtotal' => round($qty * $price, 2),
            ]);
        }
    }

    private function generatePurchaseNumber(): string
    {
        $datePrefix = Carbon::now()->format('Ymd');
        $countToday = Purchase::withTrashed()->whereDate('created_at', Carbon::today())->count() + 1;
        $purchaseNumber = sprintf('PO-%s-%04d', $datePrefix, $countToday);

        while (Purchase::withTrashed()->where('purchase_number', $purchaseNumber)->exists()) {
            $countToday++;
            $purchaseNumber = sprintf('PO-%s-%04d', $datePrefix, $countToday);
        }

        return $purchaseNumber;
    }

    /**
     * Ringkasan pembelian (header + rincian) untuk before/after audit log.
     */
    private function snapshot(Purchase $purchase): array
    {
        return [
            'purchase_number' => $purchase->purchase_number,
            'purchase_date' => optional($purchase->purchase_date)->toDateString(),
            'supplier_name' => $purchase->supplier_name,
            'notes' => $purchase->notes,
            'total_amount' => (float) $purchase->total_amount,
            'items' => $purchase->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name_snapshot' => $item->product_name_snapshot,
                    'quantity' => (int) $item->quantity,
                    'unit' => $item->unit,
                    'purchase_price' => (float) $item->purchase_price,
                    'subtotal' => (float) $item->subtotal,
                ];
            })->all(),
        ];
    }
}
