<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id', 'staff_id', 'stage', 'hours_worked',
        'quantity_completed', 'notes', 'log_date'
    ];

    protected $casts = [
        'hours_worked' => 'decimal:2',
        'log_date' => 'date'
    ];

    public function batch()
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
