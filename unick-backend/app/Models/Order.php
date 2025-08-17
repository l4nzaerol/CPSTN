<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'customer_id', 'subtotal', 'tax_amount',
        'total_amount', 'status', 'notes', 'order_date', 'delivery_date'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'order_date' => 'date',
        'delivery_date' => 'date'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function productionBatch()
    {
        return $this->hasOne(ProductionBatch::class);
    }

    public static function generateOrderNumber()
    {
        $prefix = 'ORD-' . date('Y');
        $lastOrder = static::where('order_number', 'like', $prefix . '%')
                           ->orderBy('id', 'desc')
                           ->first();
        
        if (!$lastOrder) {
            return $prefix . '-0001';
        }

        $lastNumber = intval(substr($lastOrder->order_number, -4));
        return $prefix . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function calculateTotals()
    {
        $this->subtotal = $this->items->sum('total_price');
        $this->tax_amount = $this->subtotal * 0.12; // 12% tax
        $this->total_amount = $this->subtotal + $this->tax_amount;
        $this->save();
    }
}