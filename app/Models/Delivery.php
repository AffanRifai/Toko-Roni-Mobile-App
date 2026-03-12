<?php
// app/Models/Delivery.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    // HAPUS baris ini: protected $table = 'users';
    // Biarkan Laravel menggunakan nama tabel default 'deliveries'

    protected $fillable = [
        'delivery_code',
        'transaction_id',
        'user_id',
        'vehicle_id',
        'origin',
        'destination',
        'total_items',
        'total_weight',
        'total_volume',
        'status',
        'estimated_delivery_time',
        'delivered_at',
        'notes'
    ];

    protected $casts = [
        'total_weight' => 'decimal:2',
        'total_volume' => 'decimal:2',
        'estimated_delivery_time' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Generate delivery code
    public static function generateDeliveryCode()
    {
        $prefix = 'DEL';
        $date = now()->format('Ymd');
        $last = self::where('delivery_code', 'like', $prefix . $date . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->delivery_code, -4);
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }

        return $prefix . $date . $newNum;
    }

    // Status helpers
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isProcessing()
    {
        return $this->status === 'processing';
    }

    public function isAssigned()
    {
        return $this->status === 'assigned';
    }

    public function isPickedUp()
    {
        return $this->status === 'picked_up';
    }

    public function isOnDelivery()
    {
        return $this->status === 'on_delivery';
    }

    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    // Status badge class
    public function getStatusBadgeClass()
    {
        return [
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
            'assigned' => 'bg-purple-100 text-purple-800 border-purple-200',
            'picked_up' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'on_delivery' => 'bg-orange-100 text-orange-800 border-orange-200',
            'delivered' => 'bg-green-100 text-green-800 border-green-200',
            'failed' => 'bg-red-100 text-red-800 border-red-200',
            'cancelled' => 'bg-gray-100 text-gray-800 border-gray-200',
        ][$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getStatusIcon()
    {
        return [
            'pending' => 'fa-clock',
            'processing' => 'fa-cog fa-spin',
            'assigned' => 'fa-user-check',
            'picked_up' => 'fa-box-open',
            'on_delivery' => 'fa-truck',
            'delivered' => 'fa-check-circle',
            'failed' => 'fa-exclamation-circle',
            'cancelled' => 'fa-times-circle',
        ][$this->status] ?? 'fa-question-circle';
    }
}
