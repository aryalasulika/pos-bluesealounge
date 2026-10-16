<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $query = Product::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'out') {
                $query->where('current_stock', '<=', 0);
            } elseif ($request->stock_status === 'minimum') {
                $query->whereColumn('current_stock', '<=', 'minimum_stock')->where('current_stock', '>', 0);
            } elseif ($request->stock_status === 'safe') {
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

        $products = $query->latest()->paginate(12)->withQueryString();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:50', 'unique:products,barcode'],
            'name' => ['required', 'string', 'max:150'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'initial_stock' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:30'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            $initialStock = (int) ($validated['initial_stock'] ?? 0);

            $product = Product::create([
                'category_id' => $validated['category_id'],
                'sku' => strtoupper($validated['sku']),
                'barcode' => $validated['barcode'] ? trim($validated['barcode']) : null,
                'name' => $validated['name'],
                'selling_price' => $validated['selling_price'],
                'current_stock' => $initialStock,
                'minimum_stock' => $validated['minimum_stock'],
                'unit' => strtolower($validated['unit']),
                'image_path' => $imagePath,
                'is_active' => true,
            ]);

            if ($initialStock > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'manual_in',
                    'quantity_change' => $initialStock,
                    'stock_before' => 0,
                    'stock_after' => $initialStock,
                    'reference_type' => 'initial_stock',
                    'reference_id' => $product->id,
                    'reason' => 'Stok awal saat pembuatan produk baru',
                    'user_id' => Auth::id(),
                    'created_at' => now(),
                ]);
            }

            AuditLog::record(
                'product_created',
                $product,
                null,
                $product->toArray(),
                "Produk baru '{$product->name}' (SKU: {$product->sku}) berhasil didaftarkan"
            );

            return back()->with('success', "Produk '{$product->name}' berhasil ditambahkan!");
        });
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product->id)],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('products', 'barcode')->ignore($product->id)],
            'name' => ['required', 'string', 'max:150'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:30'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $before = $product->toArray();

        if ($request->hasFile('image')) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $product->image_path = $request->file('image')->store('products', 'public');
        }

        $oldPrice = (float) $product->selling_price;
        $newPrice = (float) $validated['selling_price'];

        $product->category_id = $validated['category_id'];
        $product->sku = strtoupper($validated['sku']);
        $product->barcode = $validated['barcode'] ? trim($validated['barcode']) : null;
        $product->name = $validated['name'];
        $product->selling_price = $validated['selling_price'];
        $product->minimum_stock = $validated['minimum_stock'];
        $product->unit = strtolower($validated['unit']);
        if ($request->has('is_active')) {
            $product->is_active = $request->boolean('is_active');
        }
        $product->save();

        $priceNote = ($oldPrice !== $newPrice) ? " Perubahan harga dari Rp " . number_format($oldPrice, 0, ',', '.') . " ke Rp " . number_format($newPrice, 0, ',', '.') : "";

        AuditLog::record(
            'product_updated',
            $product,
            $before,
            $product->toArray(),
            "Produk '{$product->name}' diperbarui." . $priceNote
        );

        return back()->with('success', "Produk '{$product->name}' berhasil diperbarui.");
    }

    public function toggle(Product $product)
    {
        $product->is_active = !$product->is_active;
        $product->save();

        $statusStr = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
        AuditLog::record(
            'product_status_toggled',
            $product,
            null,
            ['is_active' => $product->is_active],
            "Produk '{$product->name}' {$statusStr}"
        );

        return back()->with('success', "Produk '{$product->name}' berhasil {$statusStr}.");
    }

    public function destroy(Product $product)
    {
        // FR-04 / BR-09: produk dengan riwayat transaksi atau pergerakan stok
        // tidak boleh dihapus permanen agar integritas data dan audit tetap utuh.
        if ($product->saleItems()->count() > 0) {
            return back()->with('error', "Produk '{$product->name}' memiliki riwayat transaksi penjualan sehingga tidak boleh dihapus demi integritas data. Silakan nonaktifkan produk.");
        }

        if ($product->purchaseItems()->count() > 0) {
            return back()->with('error', "Produk '{$product->name}' memiliki riwayat pembelian sehingga tidak boleh dihapus demi integritas data. Silakan nonaktifkan produk.");
        }

        if ($product->stockMovements()->count() > 0) {
            return back()->with('error', "Produk '{$product->name}' memiliki riwayat pergerakan stok sehingga tidak boleh dihapus demi integritas data. Silakan nonaktifkan produk.");
        }

        $before = $product->toArray();
        $name = $product->name;

        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        AuditLog::record('product_deleted', null, $before, null, "Produk '{$name}' dihapus.");

        return back()->with('success', "Produk '{$name}' berhasil dihapus.");
    }
}
