<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $query = Product::with('category');

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

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('current_stock', 'asc')->paginate(15)->withQueryString();

        $lowStockTotal = Product::active()->whereColumn('current_stock', '<=', 'minimum_stock')->count();
        $outOfStockTotal = Product::active()->where('current_stock', '<=', 0)->count();

        return view('admin.stocks.index', compact('products', 'categories', 'lowStockTotal', 'outOfStockTotal'));
    }

    public function addStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($validated) {
            $product = Product::where('id', $validated['product_id'])->lockForUpdate()->firstOrFail();

            $stockBefore = $product->current_stock;
            $product->current_stock += (int) $validated['quantity'];
            $product->save();
            $stockAfter = $product->current_stock;

            StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'manual_in',
                'quantity_change' => (int) $validated['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_type' => 'manual_addition',
                'reason' => $validated['reason'],
                'user_id' => Auth::id(),
                'created_at' => now(),
            ]);

            AuditLog::record(
                'stock_manual_added',
                $product,
                ['stock' => $stockBefore],
                ['stock' => $stockAfter, 'added' => $validated['quantity']],
                "Penambahan stok manual: +{$validated['quantity']} {$product->unit} untuk '{$product->name}'. Alasan: {$validated['reason']}"
            );

            return back()->with('success', "Berhasil menambahkan {$validated['quantity']} {$product->unit} ke stok produk '{$product->name}'.");
        });
    }

    public function movements(Request $request)
    {
        $products = Product::orderBy('name')->get();

        $query = StockMovement::with(['product', 'user'])->latest('created_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $movements = $query->paginate(20)->withQueryString();

        return view('admin.stocks.movements', compact('movements', 'products'));
    }

    /**
     * FR-16: halaman detail produk beserta riwayat perubahan stoknya.
     */
    public function show(Product $product)
    {
        $product->load('category');

        $movements = $product->stockMovements()
            ->with('user')
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $stocktakes = $product->stocktakes()
            ->with('recordedByUser')
            ->latest()
            ->limit(10)
            ->get();

        $totalIn = (int) $product->stockMovements()->where('quantity_change', '>', 0)->sum('quantity_change');
        $totalOut = (int) $product->stockMovements()->where('quantity_change', '<', 0)->sum('quantity_change');

        return view('admin.stocks.show', compact('product', 'movements', 'stocktakes', 'totalIn', 'totalOut'));
    }
}
