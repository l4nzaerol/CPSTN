<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'address'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
         'is_admin' => 'boolean',
    ];

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function assignedBatches()
    {
        return $this->hasMany(ProductionBatch::class, 'assigned_staff_id');
    }

    public function productionLogs()
    {
        return $this->hasMany(ProductionLog::class, 'staff_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isStaff()
    {
        return in_array($this->role, ['admin', 'staff']);
    }
}