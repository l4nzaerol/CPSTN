<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number', 'order_id', 'status', 'start_date',
        'end_date', 'assigned_staff_id', 'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function logs()
    {
        return $this->hasMany(ProductionLog::class, 'batch_id');
    }

    public static function generateBatchNumber()
    {
        $prefix = 'BATCH-' . date('Y');
        $lastBatch = static::where('batch_number', 'like', $prefix . '%')
                           ->orderBy('id', 'desc')
                           ->first();
        
        if (!$lastBatch) {
            return $prefix . '-0001';
        }

        $lastNumber = intval(substr($lastBatch->batch_number, -4));
        return $prefix . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
}
