<?php

// Authentication
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {
    // Products
    Route::apiResource('products', ProductController::class);
    
    // Orders
    Route::apiResource('orders', OrderController::class);
    Route::post('orders/{order}/approve', [OrderController::class, 'approve']);
    
    // Inventory
    Route::get('inventory/low-stock', [InventoryController::class, 'lowStock']);
    Route::post('inventory/adjust', [InventoryController::class, 'adjust']);
    
    // Production
    Route::apiResource('production-batches', ProductionController::class);
    Route::post('production/{batch}/log', [ProductionController::class, 'addLog']);
    
    // Reports
    Route::get('reports/inventory', [ReportController::class, 'inventory']);
    Route::get('reports/sales', [ReportController::class, 'sales']);
    
    // Forecasting
    Route::get('forecasts/demand', [ForecastController::class, 'demand']);
    Route::post('forecasts/generate', [ForecastController::class, 'generate']);
});