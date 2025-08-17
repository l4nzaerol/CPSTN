<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'description', 'unit', 'unit_cost',
        'current_stock', 'minimum_stock', 'maximum_stock', 'supplier_id'
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_materials')
                    ->withPivot('quantity_required')
                    ->withTimestamps();
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

    public function scopeLowStock($query)
    {
        return $query->whereRaw('current_stock <= minimum_stock');
    }
}
