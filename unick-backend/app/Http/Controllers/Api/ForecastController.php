<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryForecast;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Order;
use App\Models\InventoryTransaction;
use App\Services\ForecastingService;
use Illuminate\Http\Request;

class ForecastController extends Controller
{
    protected $forecastingService;

    public function __construct(ForecastingService $forecastingService)
    {
        $this->forecastingService = $forecastingService;
    }

    public function demand(Request $request)
    {
        $forecasts = InventoryForecast::with(['product', 'rawMaterial'])
                                    ->where('forecast_date', '>=', now())
                                    ->orderBy('forecast_date')
                                    ->get();

        return response()->json($forecasts);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:product,raw_material',
            'item_id' => 'required|integer',
            'days_ahead' => 'required|integer|min:1|max:365'
        ]);

        $forecasts = $this->forecastingService->generateForecast(
            $request->type,
            $request->item_id,
            $request->days_ahead
        );

        return response()->json($forecasts);
    }

    public function reorderRecommendations()
    {
        $recommendations = $this->forecastingService->getReorderRecommendations();

        return response()->json($recommendations);
    }
}
