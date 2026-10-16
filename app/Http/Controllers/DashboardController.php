<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Omzet Hari Ini (Valid completed sales only)
        $todayOmzet = Sale::completed()
            ->whereDate('transaction_date', $today)
            ->sum('total');

        // Jumlah Transaksi Valid Hari Ini
        $todaySalesCount = Sale::completed()
            ->whereDate('transaction_date', $today)
            ->count();

        // Omzet Bulan Berjalan
        $monthlyOmzet = Sale::completed()
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('total');

        // Total Transaksi Valid Bulan Berjalan
        $monthlySalesCount = Sale::completed()
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->count();

        // Produk dengan Stok Minimum atau Habis
        $lowStockCount = Product::active()
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->count();

        $outOfStockCount = Product::active()
            ->where('current_stock', '<=', 0)
            ->count();

        $lowStockProducts = Product::with('category')
            ->active()
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->orderBy('current_stock', 'asc')
            ->limit(5)
            ->get();

        // Ringkasan Pembelian Barang Bulan Ini
        $monthlyPurchasesTotal = Purchase::whereBetween('purchase_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->sum('total_amount');

        // Ringkasan Pengeluaran Operasional Bulan Ini
        $monthlyExpensesTotal = Expense::whereBetween('expense_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->sum('amount');

        // Top 5 Produk Terlaris (Bulan Berjalan, Completed Sales Only)
        $topProducts = SaleItem::select(
                'sale_items.product_id',
                'sale_items.product_name_snapshot',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue')
            )
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.transaction_date', [$startOfMonth, $endOfMonth])
            ->groupBy('sale_items.product_id', 'sale_items.product_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Transaksi Terbaru (Latest 7 transactions)
        $recentSales = Sale::with('cashier')
            ->latest('transaction_date')
            ->limit(7)
            ->get();

        return view('admin.dashboard', compact(
            'todayOmzet',
            'todaySalesCount',
            'monthlyOmzet',
            'monthlySalesCount',
            'lowStockCount',
            'outOfStockCount',
            'lowStockProducts',
            'monthlyPurchasesTotal',
            'monthlyExpensesTotal',
            'topProducts',
            'recentSales'
        ));
    }
}
