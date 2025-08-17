<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\RawMaterial;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function sendLowStockAlert($item, $type)
    {
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'Low Stock Alert',
                'message' => "Low stock detected for {$type}: {$item->name} (SKU: {$item->sku}). Current stock: {$item->current_stock}, Minimum: {$item->minimum_stock}",
                'type' => 'low_stock'
            ]);
        }
    }

    public function sendOrderStatusUpdate($order, $newStatus)
    {
        $customer = $order->customer;
        
        Notification::create([
            'user_id' => $customer->user_id,
            'title' => 'Order Status Update',
            'message' => "Your order #{$order->order_number} status has been updated to: {$newStatus}",
            'type' => 'order_update'
        ]);

        // Send email notification (if configured)
        if ($customer->user->email) {
            // Mail::to($customer->user->email)->send(new OrderStatusUpdated($order, $newStatus));
        }
    }

    public function sendProductionAlert($batch, $message)
    {
        $staffMembers = User::where('role', 'staff')->get();
        
        foreach ($staffMembers as $staff) {
            Notification::create([
                'user_id' => $staff->id,
                'title' => 'Production Alert',
                'message' => "Production batch #{$batch->batch_number}: {$message}",
                'type' => 'production_alert'
            ]);
        }
    }

    public function sendSystemNotification($message, $userRole = null)
    {
        $query = User::query();
        
        if ($userRole) {
            $query->where('role', $userRole);
        }
        
        $users = $query->get();
        
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => 'System Notification',
                'message' => $message,
                'type' => 'system'
            ]);
        }
    }
}