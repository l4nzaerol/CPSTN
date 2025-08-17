<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'description', 'price', 'current_stock',
        'minimum_stock', 'category', 'specifications', 'image_url', 'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'specifications' => 'array'
    ];

    public function materials()
    {
        return $this->belongsToMany(RawMaterial::class, 'product_materials')
                    ->withPivot('quantity_required')
                    ->withTimestamps();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function forecasts()
    {
        return $this->hasMany(InventoryForecast::class);
    }

    public function isLowStock()
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('current_stock <= minimum_stock');
    }
}
