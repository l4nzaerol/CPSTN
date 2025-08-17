<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;
use App\Models\ProductionLog;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductionBatch::with(['order.customer.user', 'assignedStaff', 'logs']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $batches = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 15));

        return response()->json($batches);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'assigned_staff_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string'
        ]);

        $batch = ProductionBatch::create([
            'batch_number' => ProductionBatch::generateBatchNumber(),
            'order_id' => $request->order_id,
            'status' => 'planned',
            'assigned_staff_id' => $request->assigned_staff_id,
            'notes' => $request->notes,
        ]);

        return response()->json($batch->load(['order.customer.user', 'assignedStaff', 'logs']), 201);
    }

    public function show(ProductionBatch $production_batch)
    {
        return response()->json($production_batch->load(['order.customer.user', 'assignedStaff', 'logs']));
    }

    public function update(Request $request, ProductionBatch $production_batch)
    {
        $request->validate([
            'status' => 'sometimes|in:planned,in_production,completed,cancelled,on_hold',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'assigned_staff_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string'
        ]);

        $production_batch->update($request->only(['status', 'start_date', 'end_date', 'assigned_staff_id', 'notes']));

        return response()->json($production_batch->load(['order.customer.user', 'assignedStaff', 'logs']));
    }

    public function destroy(ProductionBatch $production_batch)
    {
        $production_batch->delete();
        return response()->json(null, 204);
    }

    public function addLog(ProductionBatch $batch, Request $request)
    {
        $request->validate([
            'stage' => 'required|string',
            'hours_worked' => 'required|numeric|min:0',
            'quantity_completed' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'log_date' => 'nullable|date',
        ]);

        $log = ProductionLog::create([
            'batch_id' => $batch->id,
            'staff_id' => $request->user()->id,
            'stage' => $request->stage,
            'hours_worked' => $request->hours_worked,
            'quantity_completed' => $request->quantity_completed,
            'notes' => $request->notes,
            'log_date' => $request->log_date ?? now()->toDateString(),
        ]);

        return response()->json($log->load(['batch', 'staff']), 201);
    }
}