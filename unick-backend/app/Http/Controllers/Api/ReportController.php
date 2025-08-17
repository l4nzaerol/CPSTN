<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\ProductionBatch;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function inventory()
    {
        $rawMaterials = RawMaterial::with('supplier')->get();
        $products = Product::all();
        $lowStockMaterials = RawMaterial::lowStock()->count();
        $lowStockProducts = Product::lowStock()->count();

        $totalInventoryValue = $rawMaterials->sum(function($material) {
            return $material->current_stock * $material->unit_cost;
        }) + $products->sum(function($product) {
            return $product->current_stock * $product->price;
        });

        return response()->json([
            'raw_materials' => $rawMaterials,
            'products' => $products,
            'summary' => [
                'total_raw_materials' => $rawMaterials->count(),
                'total_products' => $products->count(),
                'low_stock_materials' => $lowStockMaterials,
                'low_stock_products' => $lowStockProducts,
                'total_inventory_value' => $totalInventoryValue
            ]
        ]);
    }

    public function sales(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $orders = Order::with(['items.product', 'customer.user'])
                      ->whereBetween('order_date', [$startDate, $endDate])
                      ->get();

        $totalSales = $orders->sum('total_amount');
        $totalOrders = $orders->count();
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        $salesByStatus = $orders->groupBy('status')->map->count();
        $salesByMonth = $orders->groupBy(function($order) {
            return $order->order_date->format('Y-m');
        })->map(function($orders) {
            return $orders->sum('total_amount');
        });

        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.order_date', [$startDate, $endDate])
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_quantity'), 
                    DB::raw('SUM(order_items.total_price) as total_sales'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sales', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'orders' => $orders,
            'summary' => [
                'total_sales' => $totalSales,
                'total_orders' => $totalOrders,
                'average_order_value' => $averageOrderValue,
                'sales_by_status' => $salesByStatus,
                'sales_by_month' => $salesByMonth,
                'top_products' => $topProducts
            ]
        ]);
    }

    public function production(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $batches = ProductionBatch::with(['order', 'assignedStaff', 'logs'])
                                 ->whereBetween('created_at', [$startDate, $endDate])
                                 ->get();

        $totalBatches = $batches->count();
        $completedBatches = $batches->where('status', 'completed')->count();
        $inProgressBatches = $batches->whereNotIn('status', ['completed', 'planned'])->count();

        $productionByStatus = $batches->groupBy('status')->map->count();
        
        $efficiency = DB::table('production_logs')
            ->join('production_batches', 'production_logs.batch_id', '=', 'production_batches.id')
            ->whereBetween('production_logs.log_date', [$startDate, $endDate])
            ->select('production_logs.stage', 
                    DB::raw('AVG(production_logs.hours_worked) as avg_hours'),
                    DB::raw('SUM(production_logs.quantity_completed) as total_quantity'))
            ->groupBy('production_logs.stage')
            ->get();

        return response()->json([
            'batches' => $batches,
            'summary' => [
                'total_batches' => $totalBatches,
                'completed_batches' => $completedBatches,
                'in_progress_batches' => $inProgressBatches,
                'completion_rate' => $totalBatches > 0 ? ($completedBatches / $totalBatches) * 100 : 0,
                'production_by_status' => $productionByStatus,
                'efficiency_by_stage' => $efficiency
            ]
        ]);
    }

    public function generateInventoryPDF()
    {
        $data = $this->inventory()->getData();
        
        $pdf = Pdf::loadView('reports.inventory', compact('data'));
        
        return $pdf->download('inventory-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function generateSalesPDF(Request $request)
    {
        $data = $this->sales($request)->getData();
        
        $pdf = Pdf::loadView('reports.sales', compact('data'));
        
        return $pdf->download('sales-report-' . now()->format('Y-m-d') . '.pdf');
    }
}