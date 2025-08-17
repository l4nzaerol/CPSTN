<?php

namespace App\Services;

use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryTransaction;
use App\Models\InventoryForecast;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForecastingService
{
    public function generateForecast($type, $itemId, $daysAhead)
    {
        if ($type === 'product') {
            return $this->forecastProductDemand($itemId, $daysAhead);
        } else {
            return $this->forecastMaterialConsumption($itemId, $daysAhead);
        }
    }

    public function forecastProductDemand($productId, $daysAhead)
    {
        $product = Product::findOrFail($productId);
        
        // Get historical sales data (last 90 days)
        $historicalData = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                                  ->where('order_items.product_id', $productId)
                                  ->where('orders.status', '!=', 'cancelled')
                                  ->where('orders.order_date', '>=', now()->subDays(90))
                                  ->select(
                                      DB::raw('DATE(orders.order_date) as date'),
                                      DB::raw('SUM(order_items.quantity) as daily_demand')
                                  )
                                  ->groupBy(DB::raw('DATE(orders.order_date)'))
                                  ->orderBy('date')
                                  ->get();

        // Calculate moving average
        $movingAverage = $this->calculateMovingAverage($historicalData->pluck('daily_demand'), 7);
        
        // Generate forecasts for next N days
        $forecasts = [];
        $baseDate = now();
        
        for ($i = 1; $i <= $daysAhead; $i++) {
            $forecastDate = $baseDate->copy()->addDays($i);
            $predictedDemand = $this->applySeasonalityAndTrend($movingAverage, $forecastDate);
            
            $forecast = InventoryForecast::updateOrCreate([
                'product_id' => $productId,
                'forecast_date' => $forecastDate->toDateString()
            ], [
                'predicted_demand' => $predictedDemand,
                'recommended_reorder_quantity' => max(0, $predictedDemand - $product->current_stock),
                'analysis_notes' => "Based on {$historicalData->count()} days of historical data. Moving average: {$movingAverage}"
            ]);

            $forecasts[] = $forecast;
        }

        return $forecasts;
    }

    public function forecastMaterialConsumption($materialId, $daysAhead)
    {
        $material = RawMaterial::findOrFail($materialId);
        
        // Get historical consumption data
        $historicalData = InventoryTransaction::where('raw_material_id', $materialId)
                                            ->where('type', 'out')
                                            ->where('created_at', '>=', now()->subDays(90))
                                            ->select(
                                                DB::raw('DATE(created_at) as date'),
                                                DB::raw('SUM(quantity) as daily_consumption')
                                            )
                                            ->groupBy(DB::raw('DATE(created_at)'))
                                            ->orderBy('date')
                                            ->get();

        $movingAverage = $this->calculateMovingAverage($historicalData->pluck('daily_consumption'), 7);
        
        $forecasts = [];
        $baseDate = now();
        
        for ($i = 1; $i <= $daysAhead; $i++) {
            $forecastDate = $baseDate->copy()->addDays($i);
            $predictedConsumption = $this->applySeasonalityAndTrend($movingAverage, $forecastDate);
            
            $forecast = InventoryForecast::updateOrCreate([
                'raw_material_id' => $materialId,
                'forecast_date' => $forecastDate->toDateString()
            ], [
                'predicted_consumption' => $predictedConsumption,
                'recommended_reorder_quantity' => max(0, $predictedConsumption - $material->current_stock),
                'analysis_notes' => "Based on {$historicalData->count()} days of consumption data. Moving average: {$movingAverage}"
            ]);

            $forecasts[] = $forecast;
        }

        return $forecasts;
    }

    private function calculateMovingAverage($data, $periods = 7)
    {
        if ($data->count() < $periods) {
            return $data->avg();
        }

        $recentData = $data->take(-$periods);
        return $recentData->avg();
    }

    private function applySeasonalityAndTrend($baseValue, $date)
    {
        // Apply day-of-week seasonality (example: lower demand on weekends)
        $dayOfWeek = $date->dayOfWeek;
        $seasonalityFactor = 1.0;
        
        if (in_array($dayOfWeek, [0, 6])) { // Sunday = 0, Saturday = 6
            $seasonalityFactor = 0.7;
        }

        // Apply monthly trend (example: higher demand at month end)
        $dayOfMonth = $date->day;
        $trendFactor = 1.0;
        
        if ($dayOfMonth > 25) {
            $trendFactor = 1.2;
        }

        return round($baseValue * $seasonalityFactor * $trendFactor, 2);
    }

    public function getReorderRecommendations()
    {
        $recommendations = [];

        // Check raw materials
        $lowStockMaterials = RawMaterial::lowStock()->with('supplier')->get();
        
        foreach ($lowStockMaterials as $material) {
            $latestForecast = InventoryForecast::where('raw_material_id', $material->id)
                                             ->where('forecast_date', '>=', now())
                                             ->orderBy('forecast_date')
                                             ->first();

            $recommendations[] = [
                'type' => 'raw_material',
                'item' => $material,
                'current_stock' => $material->current_stock,
                'minimum_stock' => $material->minimum_stock,
                'recommended_quantity' => $latestForecast ? $latestForecast->recommended_reorder_quantity : $material->maximum_stock - $material->current_stock,
                'urgency' => $this->calculateUrgency($material->current_stock, $material->minimum_stock),
                'forecast' => $latestForecast
            ];
        }

        // Check finished products
        $lowStockProducts = Product::lowStock()->get();
        
        foreach ($lowStockProducts as $product) {
            $latestForecast = InventoryForecast::where('product_id', $product->id)
                                             ->where('forecast_date', '>=', now())
                                             ->orderBy('forecast_date')
                                             ->first();

            $recommendations[] = [
                'type' => 'product',
                'item' => $product,
                'current_stock' => $product->current_stock,
                'minimum_stock' => $product->minimum_stock,
                'recommended_quantity' => $latestForecast ? $latestForecast->recommended_reorder_quantity : $product->minimum_stock * 2,
                'urgency' => $this->calculateUrgency($product->current_stock, $product->minimum_stock),
                'forecast' => $latestForecast
            ];
        }

        // Sort by urgency (highest first)
        usort($recommendations, function($a, $b) {
            return $b['urgency'] <=> $a['urgency'];
        });

        return $recommendations;
    }

    private function calculateUrgency($currentStock, $minimumStock)
    {
        if ($currentStock <= 0) return 100; // Critical
        if ($currentStock <= $minimumStock * 0.5) return 90; // High
        if ($currentStock <= $minimumStock) return 70; // Medium
        return 30; // Low
    }
}