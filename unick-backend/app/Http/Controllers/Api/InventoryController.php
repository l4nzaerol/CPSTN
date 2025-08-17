<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Models\Product;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function rawMaterials(Request $request)
    {
        $query = RawMaterial::with('supplier');

        if ($request->has('low_stock') && $request->low_stock) {
            $query->lowStock();
        }

        $materials = $query->paginate($request->get('per_page', 15));
        return response()->json($materials);
    }

    public function products(Request $request)
    {
        $query = Product::query();

        if ($request->has('low_stock') && $request->low_stock) {
            $query->lowStock();
        }

        $products = $query->paginate($request->get('per_page', 15));
        return response()->json($products);
    }

    public function lowStock()
    {
        $lowStockMaterials = RawMaterial::lowStock()->with('supplier')->get();
        $lowStockProducts = Product::lowStock()->get();

        return response()->json([
            'raw_materials' => $lowStockMaterials,
            'products' => $lowStockProducts
        ]);
    }

    public function adjustStock(Request $request)
    {
        $request->validate([
            'type' => 'required|in:raw_material,product',
            'item_id' => 'required|integer',
            'adjustment_type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer',
            'unit_cost' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        if ($request->type === 'raw_material') {
            $item = RawMaterial::findOrFail($request->item_id);
            $newStock = $this->calculateNewStock($item->current_stock, $request->adjustment_type, $request->quantity);
            $item->update(['current_stock' => $newStock]);

            $transaction = InventoryTransaction::create([
                'type' => $request->adjustment_type,
                'raw_material_id' => $item->id,
                'quantity' => $request->quantity,
                'unit_cost' => $request->unit_cost,
                'reference' => $request->reference,
                'user_id' => $request->user()->id
            ]);
        } else {
            $item = Product::findOrFail($request->item_id);
            $newStock = $this->calculateNewStock($item->current_stock, $request->adjustment_type, $request->quantity);
            $item->update(['current_stock' => $newStock]);

            $transaction = InventoryTransaction::create([
                'type' => $request->adjustment_type,
                'product_id' => $item->id,
                'quantity' => $request->quantity,
                'unit_cost' => $request->unit_cost,
                'reference' => $request->reference,
                'user_id' => $request->user()->id
            ]);
        }

        return response()->json([
            'item' => $item,
            'transaction' => $transaction
        ]);
    }

    private function calculateNewStock($currentStock, $type, $quantity)
    {
        switch ($type) {
            case 'in':
                return $currentStock + $quantity;
            case 'out':
                return max(0, $currentStock - $quantity);
            case 'adjustment':
                return $quantity;
            default:
                return $currentStock;
        }
    }

    public function transactions(Request $request)
    {
        $query = InventoryTransaction::with(['rawMaterial', 'product', 'user']);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('created_at', 'desc')
                             ->paginate($request->get('per_page', 15));

        return response()->json($transactions);
    }
}