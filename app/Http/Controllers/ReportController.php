<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Helper to resolve start & end date based on period presets
     */
    private function resolvePeriod(Request $request): array
    {
        $preset = $request->input('period', 'monthly');
        $startDate = null;
        $endDate = null;

        if ($preset === 'today') {
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
            $label = 'Hari Ini (' . $startDate->translatedFormat('d F Y') . ')';
        } elseif ($preset === 'weekly') {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
            $label = 'Minggu Ini (' . $startDate->translatedFormat('d M') . ' - ' . $endDate->translatedFormat('d M Y') . ')';
        } elseif ($preset === 'monthly') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $label = 'Bulan Ini (' . $startDate->translatedFormat('F Y') . ')';
        } elseif ($preset === 'custom') {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
            $label = 'Periode ' . $startDate->translatedFormat('d M Y') . ' s/d ' . $endDate->translatedFormat('d M Y');
        } else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $label = 'Bulan Ini (' . $startDate->translatedFormat('F Y') . ')';
        }

        return [$startDate, $endDate, $label, $preset];
    }

    /**
     * 1. Laporan Omzet Penjualan
     */
    public function sales(Request $request)
    {
        [$startDate, $endDate, $periodLabel, $period] = $this->resolvePeriod($request);

        // Valid completed transactions only
        $salesQuery = Sale::with(['cashier', 'items'])
            ->completed()
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($request->filled('payment_method')) {
            $salesQuery->where('payment_method', $request->payment_method);
        }

        $sales = (clone $salesQuery)->latest('transaction_date')->get();

        $totalOmzet = $sales->sum('total');
        $totalTransactions = $sales->count();
        $totalItemsSold = $sales->sum(fn($s) => $s->items->sum('quantity'));

        $cashOmzet = $sales->where('payment_method', 'cash')->sum('total');
        $cashCount = $sales->where('payment_method', 'cash')->count();

        $qrisOmzet = $sales->where('payment_method', 'qris')->sum('total');
        $qrisCount = $sales->where('payment_method', 'qris')->count();

        // Cancelled count for information (excluded from omzet)
        $cancelledCount = Sale::cancelled()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->count();
        $cancelledPotential = Sale::cancelled()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('total');

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.exports.sales_pdf', compact(
                'sales', 'totalOmzet', 'totalTransactions', 'totalItemsSold',
                'cashOmzet', 'cashCount', 'qrisOmzet', 'qrisCount',
                'cancelledCount', 'cancelledPotential', 'periodLabel'
            ))->setPaper('a4', 'portrait');

            return $pdf->download('Laporan_Omzet_Bluesea_' . date('Ymd_His') . '.pdf');
        }

        if ($request->input('export') === 'excel') {
            return response()->view('admin.reports.exports.sales_excel', compact(
                'sales', 'totalOmzet', 'totalTransactions', 'totalItemsSold',
                'cashOmzet', 'cashCount', 'qrisOmzet', 'qrisCount',
                'cancelledCount', 'cancelledPotential', 'periodLabel'
            ), 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="Laporan_Omzet_Bluesea_' . date('Ymd_His') . '.xls"',
            ]);
        }

        return view('admin.reports.sales', compact(
            'sales', 'totalOmzet', 'totalTransactions', 'totalItemsSold',
            'cashOmzet', 'cashCount', 'qrisOmzet', 'qrisCount',
            'cancelledCount', 'cancelledPotential', 'periodLabel', 'period', 'startDate', 'endDate'
        ));
    }

    /**
     * 2. Laporan Produk Terlaris
     */
    public function topProducts(Request $request)
    {
        [$startDate, $endDate, $periodLabel, $period] = $this->resolvePeriod($request);

        $query = SaleItem::select(
                'sale_items.product_id',
                'sale_items.product_name_snapshot',
                'sale_items.sku_snapshot',
                'products.category_id',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('COUNT(DISTINCT sales.id) as transactions_count')
            )
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.transaction_date', [$startDate, $endDate]);

        if ($request->filled('category_id')) {
            $query->where('products.category_id', $request->category_id);
        }

        $topProducts = $query->groupBy(
                'sale_items.product_id',
                'sale_items.product_name_snapshot',
                'sale_items.sku_snapshot',
                'products.category_id'
            )
            ->orderByDesc('total_quantity')
            ->get();

        $categories = Category::orderBy('name')->get();

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.exports.top_products_pdf', compact(
                'topProducts', 'periodLabel'
            ))->setPaper('a4', 'portrait');

            return $pdf->download('Laporan_Produk_Terlaris_' . date('Ymd_His') . '.pdf');
        }

        if ($request->input('export') === 'excel') {
            return response()->view('admin.reports.exports.top_products_excel', compact(
                'topProducts', 'periodLabel'
            ), 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="Laporan_Produk_Terlaris_' . date('Ymd_His') . '.xls"',
            ]);
        }

        return view('admin.reports.top_products', compact(
            'topProducts', 'categories', 'periodLabel', 'period', 'startDate', 'endDate'
        ));
    }

    /**
     * 3. Laporan Stok Produk
     */
    public function stock(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $query = Product::with('category')->orderBy('name');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'out') {
                $query->where('current_stock', '<=', 0);
            } elseif ($request->status === 'minimum') {
                $query->whereColumn('current_stock', '<=', 'minimum_stock')->where('current_stock', '>', 0);
            } elseif ($request->status === 'safe') {
                $query->whereColumn('current_stock', '>', 'minimum_stock');
            }
        }

        $products = $query->get();

        $totalStockUnits = $products->sum('current_stock');
        $totalStockValue = $products->sum(fn($p) => $p->current_stock * $p->selling_price);

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.exports.stock_pdf', compact(
                'products', 'totalStockUnits', 'totalStockValue'
            ))->setPaper('a4', 'portrait');

            return $pdf->download('Laporan_Stok_Produk_' . date('Ymd_His') . '.pdf');
        }

        if ($request->input('export') === 'excel') {
            return response()->view('admin.reports.exports.stock_excel', compact(
                'products', 'totalStockUnits', 'totalStockValue'
            ), 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="Laporan_Stok_Produk_' . date('Ymd_His') . '.xls"',
            ]);
        }

        return view('admin.reports.stock', compact('products', 'categories', 'totalStockUnits', 'totalStockValue'));
    }

    /**
     * 4. Laporan Riwayat Pembelian
     */
    public function purchases(Request $request)
    {
        [$startDate, $endDate, $periodLabel, $period] = $this->resolvePeriod($request);

        $query = Purchase::with(['recordedByUser', 'items.product'])
            ->whereBetween('purchase_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($request->filled('supplier_name')) {
            $query->where('supplier_name', 'like', "%" . trim($request->supplier_name) . "%");
        }

        $purchases = $query->latest('purchase_date')->get();
        $totalCost = $purchases->sum('total_amount');

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.exports.purchases_pdf', compact(
                'purchases', 'totalCost', 'periodLabel'
            ))->setPaper('a4', 'portrait');

            return $pdf->download('Laporan_Pembelian_Barang_' . date('Ymd_His') . '.pdf');
        }

        if ($request->input('export') === 'excel') {
            return response()->view('admin.reports.exports.purchases_excel', compact(
                'purchases', 'totalCost', 'periodLabel'
            ), 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="Laporan_Pembelian_Barang_' . date('Ymd_His') . '.xls"',
            ]);
        }

        return view('admin.reports.purchases', compact(
            'purchases', 'totalCost', 'periodLabel', 'period', 'startDate', 'endDate'
        ));
    }
}
