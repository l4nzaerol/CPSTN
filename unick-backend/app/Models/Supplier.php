<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'contact_person', 'phone', 'email', 'address', 'status'
    ];

    public function rawMaterials()
    {
        return $this->hasMany(RawMaterial::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}