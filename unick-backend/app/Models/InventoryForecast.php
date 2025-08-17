<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'raw_material_id', 'product_id', 'forecast_date',
        'predicted_consumption', 'predicted_demand',
        'recommended_reorder_quantity', 'analysis_notes'
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'predicted_consumption' => 'decimal:2',
        'predicted_demand' => 'decimal:2'
    ];

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
