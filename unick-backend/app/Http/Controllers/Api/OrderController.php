<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductionBatch;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer.user', 'items.product']);

        if ($request->user()->role === 'customer') {
            $query->where('customer_id', $request->user()->customer->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')
                       ->paginate($request->get('per_page', 15));

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string'
        ]);

        $customer = $request->user()->customer;
        
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'customer_id' => $customer->id,
            'status' => 'pending',
            'order_date' => now(),
            'delivery_date' => $request->delivery_date,
            'notes' => $request->notes,
            'subtotal' => 0,
            'tax_amount' => 0,
            'total_amount' => 0
        ]);

        foreach ($request->items as $item) {
            $product = \App\Models\Product::findOrFail($item['product_id']);
            
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'total_price' => $product->price * $item['quantity']
            ]);
        }

        $order->calculateTotals();

        return response()->json($order->load(['items.product', 'customer.user']), 201);
    }

    public function show(Order $order)
    {
        return response()->json($order->load(['items.product', 'customer.user', 'productionBatch']));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'sometimes|in:pending,approved,in_production,completed,cancelled,shipped,delivered',
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string'
        ]);

        $order->update($request->only(['status', 'delivery_date', 'notes']));

        // Create production batch if approved
        if ($request->status === 'approved' && !$order->productionBatch) {
            ProductionBatch::create([
                'batch_number' => ProductionBatch::generateBatchNumber(),
                'order_id' => $order->id,
                'status' => 'planned'
            ]);
        }

        return response()->json($order->load(['items.product', 'customer.user', 'productionBatch']));
    }

    public function approve(Order $order)
    {
        $order->update(['status' => 'approved']);

        ProductionBatch::create([
            'batch_number' => ProductionBatch::generateBatchNumber(),
            'order_id' => $order->id,
            'status' => 'planned'
        ]);

        return response()->json($order->load(['productionBatch']));
    }
}