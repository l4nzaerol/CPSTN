<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use Illuminate\Http\Request;

class RawMaterialController extends Controller
{
	public function index(Request $request)
	{
		$query = RawMaterial::with('supplier');
		if ($request->has('low_stock') && $request->low_stock) {
			$query->lowStock();
		}
		return response()->json($query->paginate($request->get('per_page', 15)));
	}

	public function store(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:255',
			'sku' => 'required|string|unique:raw_materials',
			'description' => 'nullable|string',
			'unit' => 'nullable|string',
			'unit_cost' => 'required|numeric|min:0',
			'current_stock' => 'required|integer|min:0',
			'minimum_stock' => 'required|integer|min:0',
			'maximum_stock' => 'nullable|integer|min:0',
			'supplier_id' => 'nullable|exists:suppliers,id',
		]);

		$material = RawMaterial::create($request->all());
		return response()->json($material->load('supplier'), 201);
	}

	public function show(RawMaterial $raw_material)
	{
		return response()->json($raw_material->load('supplier'));
	}

	public function update(Request $request, RawMaterial $raw_material)
	{
		$request->validate([
			'name' => 'sometimes|string|max:255',
			'sku' => 'sometimes|string|unique:raw_materials,sku,' . $raw_material->id,
			'description' => 'nullable|string',
			'unit' => 'nullable|string',
			'unit_cost' => 'sometimes|numeric|min:0',
			'current_stock' => 'sometimes|integer|min:0',
			'minimum_stock' => 'sometimes|integer|min:0',
			'maximum_stock' => 'nullable|integer|min:0',
			'supplier_id' => 'nullable|exists:suppliers,id',
		]);

		$raw_material->update($request->all());
		return response()->json($raw_material->load('supplier'));
	}

	public function destroy(RawMaterial $raw_material)
	{
		$raw_material->delete();
		return response()->json(null, 204);
	}
}
