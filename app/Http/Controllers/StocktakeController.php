<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Stocktake;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StocktakeController extends Controller
{
    public function index()
    {
        $stocktakes = Stocktake::with(['product', 'recordedByUser'])->latest()->paginate(15);
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('admin.stocktakes.index', compact('stocktakes', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'physical_stock' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($validated) {
            $product = Product::where('id', $validated['product_id'])->lockForUpdate()->firstOrFail();

            $systemStock = $product->current_stock;
            $physicalStock = (int) $validated['physical_stock'];
            $difference = $physicalStock - $systemStock;

            // Generate unique stocktake number ST-YYYYMMDD-XXXX
            $datePrefix = Carbon::now()->format('Ymd');
            $countToday = Stocktake::whereDate('created_at', Carbon::today())->count() + 1;
            $stocktakeNumber = sprintf('ST-%s-%04d', $datePrefix, $countToday);

            while (Stocktake::where('stocktake_number', $stocktakeNumber)->exists()) {
                $countToday++;
                $stocktakeNumber = sprintf('ST-%s-%04d', $datePrefix, $countToday);
            }

            $stocktake = Stocktake::create([
                'stocktake_number' => $stocktakeNumber,
                'product_id' => $product->id,
                'system_stock' => $systemStock,
                'physical_stock' => $physicalStock,
                'difference' => $difference,
                'reason' => $validated['reason'],
                'recorded_by' => Auth::id(),
            ]);

            // Update product stock
            $product->current_stock = $physicalStock;
            $product->save();

            // Record stock movement if there is any difference
            StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'stocktake_adjustment',
                'quantity_change' => $difference,
                'stock_before' => $systemStock,
                'stock_after' => $physicalStock,
                'reference_type' => 'stocktake',
                'reference_id' => $stocktake->id,
                'reason' => "Stok Opname {$stocktake->stocktake_number}: {$validated['reason']}",
                'user_id' => Auth::id(),
                'created_at' => now(),
            ]);

            AuditLog::record(
                'stocktake_performed',
                $stocktake,
                ['current_stock' => $systemStock],
                ['current_stock' => $physicalStock, 'difference' => $difference],
                "Stok opname untuk '{$product->name}': sistem={$systemStock}, fisik={$physicalStock}, selisih={$difference}. Alasan: {$validated['reason']}"
            );

            return back()->with('success', "Stok opname {$stocktake->stocktake_number} berhasil dicatat. Stok '{$product->name}' disesuaikan menjadi {$physicalStock} {$product->unit}.");
        });
    }
}
