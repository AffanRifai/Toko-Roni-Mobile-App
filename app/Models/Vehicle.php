<?php
// app/Models/Vehicle.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $table = 'vehicles';

    protected $fillable = [
        'name',
        'type',
        'license_plate',  // Bukan 'vehicle_code'
        'capacity_weight', // Bukan 'max_weight'
        'capacity_volume', // Bukan 'max_volume'
        'status',
        'last_maintenance',
        // 'notes'
    ];

    protected $casts = [
        'capacity_weight' => 'decimal:2',
        'capacity_volume' => 'decimal:2',
        'last_maintenance' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'vehicle_id');
    }

    // Helper untuk status badge
    public function getStatusBadgeClass()
    {
        return [
            'available' => 'bg-green-100 text-green-800 border-green-200',
            'in_use' => 'bg-blue-100 text-blue-800 border-blue-200',
            'maintenance' => 'bg-red-100 text-red-800 border-red-200',
        ][$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    // Scope untuk kendaraan tersedia
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    // Scope berdasarkan jenis
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
