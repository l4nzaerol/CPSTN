<?php

namespace App\Services;

use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\InventoryTransaction;
use App\Models\ProductionBatch;

class InventoryService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function adjustStock($type, $itemId, $adjustmentType, $quantity, $unitCost = null, $reference = null, $userId = null)
    {
        if ($type === 'raw_material') {
            $item = RawMaterial::findOrFail($itemId);
        } else {
            $item = Product::findOrFail($itemId);
        }

        $oldStock = $item->current_stock;
        $newStock = $this->calculateNewStock($oldStock, $adjustmentType, $quantity);

        $item->update(['current_stock' => $newStock]);

        // Create transaction record
        $transactionData = [
            'type' => $adjustmentType,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'reference' => $reference,
            'user_id' => $userId
        ];

        if ($type === 'raw_material') {
            $transactionData['raw_material_id'] = $itemId;
        } else {
            $transactionData['product_id'] = $itemId;
        }

        $transaction = InventoryTransaction::create($transactionData);

        // Check for low stock after adjustment
        if ($item->isLowStock()) {
            $this->notificationService->sendLowStockAlert($item, $type);
        }

        return [
            'item' => $item,
            'transaction' => $transaction,
            'old_stock' => $oldStock,
            'new_stock' => $newStock
        ];
    }

    public function consumeMaterialsForProduction(ProductionBatch $batch, $stageQuantity)
    {
        $order = $batch->order;
        
        foreach ($order->items as $orderItem) {
            $product = $orderItem->product;
            
            foreach ($product->materials as $material) {
                $requiredQuantity = $material->pivot->quantity_required * $stageQuantity;
                
                if ($material->current_stock < $requiredQuantity) {
                    throw new \Exception("Insufficient stock for material: {$material->name}. Required: {$requiredQuantity}, Available: {$material->current_stock}");
                }

                $this->adjustStock(
                    'raw_material',
                    $material->id,
                    'out',
                    $requiredQuantity,
                    null,
                    "Production batch: {$batch->batch_number}",
                    $batch->assigned_staff_id
                );
            }
        }
    }

    public function completeProduction(ProductionBatch $batch, $completedQuantity)
    {
        $order = $batch->order;
        
        foreach ($order->items as $orderItem) {
            $product = $orderItem->product;
            $producedQuantity = min($completedQuantity, $orderItem->quantity);
            
            $this->adjustStock(
                'product',
                $product->id,
                'in',
                $producedQuantity,
                null,
                "Production completed: {$batch->batch_number}",
                $batch->assigned_staff_id
            );
        }
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

    public function checkAndAlertLowStock()
    {
        // Check raw materials
        $lowStockMaterials = RawMaterial::lowStock()->get();
        foreach ($lowStockMaterials as $material) {
            $this->notificationService->sendLowStockAlert($material, 'raw_material');
        }

        // Check products
        $lowStockProducts = Product::lowStock()->get();
        foreach ($lowStockProducts as $product) {
            $this->notificationService->sendLowStockAlert($product, 'product');
        }
    }
}