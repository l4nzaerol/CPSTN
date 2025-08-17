<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['materials']);

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'active');
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'category' => 'nullable|string',
            'specifications' => 'nullable|array',
            'image_url' => 'nullable|url',
            'materials' => 'nullable|array',
            'materials.*.id' => 'required_with:materials|exists:raw_materials,id',
            'materials.*.quantity' => 'required_with:materials|numeric|min:0'
        ]);

        $product = Product::create($request->except('materials'));

        if ($request->has('materials')) {
            foreach ($request->materials as $material) {
                $product->materials()->attach($material['id'], [
                    'quantity_required' => $material['quantity']
                ]);
            }
        }

        return response()->json($product->load('materials'), 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load('materials'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'minimum_stock' => 'sometimes|integer|min:0',
            'category' => 'nullable|string',
            'specifications' => 'nullable|array',
            'image_url' => 'nullable|url',
            'status' => 'sometimes|in:active,inactive,discontinued',
            'materials' => 'nullable|array',
            'materials.*.id' => 'required_with:materials|exists:raw_materials,id',
            'materials.*.quantity' => 'required_with:materials|numeric|min:0'
        ]);

        $product->update($request->except('materials'));

        if ($request->has('materials')) {
            $product->materials()->detach();
            foreach ($request->materials as $material) {
                $product->materials()->attach($material['id'], [
                    'quantity_required' => $material['quantity']
                ]);
            }
        }

        return response()->json($product->load('materials'));
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(null, 204);
    }

    public function lowStock()
    {
        $products = Product::lowStock()->get();
        return response()->json($products);
    }
}