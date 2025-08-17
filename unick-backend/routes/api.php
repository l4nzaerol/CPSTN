<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ProductionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\NotificationController;

// Authentication
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {
    // Authenticated user profile and logout
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    // Products
    Route::apiResource('products', ProductController::class);
    Route::get('products/low-stock', [ProductController::class, 'lowStock']);
    
    // Orders
    Route::apiResource('orders', OrderController::class);
    Route::post('orders/{order}/approve', [OrderController::class, 'approve']);
    
    // Inventory
    Route::get('inventory/raw-materials', [InventoryController::class, 'rawMaterials']);
    Route::get('inventory/products', [InventoryController::class, 'products']);
    Route::get('inventory/low-stock', [InventoryController::class, 'lowStock']);
    Route::post('inventory/adjust', [InventoryController::class, 'adjustStock']);
    Route::get('inventory/transactions', [InventoryController::class, 'transactions']);
    
    // Production
    Route::apiResource('production-batches', ProductionController::class);
    Route::post('production/{batch}/log', [ProductionController::class, 'addLog']);
    
    // Reports
    Route::get('reports/inventory', [ReportController::class, 'inventory']);
    Route::get('reports/sales', [ReportController::class, 'sales']);
    Route::get('reports/production', [ReportController::class, 'production']);
    Route::get('reports/inventory/download', [ReportController::class, 'generateInventoryPDF']);
    Route::get('reports/sales/download', [ReportController::class, 'generateSalesPDF']);
    Route::get('reports/production/download', [ReportController::class, 'generateProductionPDF']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);
});