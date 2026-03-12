<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreDelivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'delivery_code',
        'order_id',
        'delivery_staff_id',
        'delivery_zone_id',
        'recipient_name',
        'recipient_phone',
        'delivery_address',
        'delivery_city',
        'delivery_district',
        'delivery_village',
        'delivery_postal_code',
        'latitude',
        'longitude',
        'delivery_fee',
        'payment_method',
        'cod_amount',
        'notes',
        'package_type',
        'package_weight',
        'items_summary',
        'status',
        'scheduled_at',
        'assigned_at',
        'pickup_at',
        'departure_at',
        'estimated_arrival',
        'delivered_at',
        'recipient_signature',
        'delivery_photo',
        'recipient_id_photo',
        'rating',
        'feedback',
        'location_updates',
        'distance_km',
        'travel_time_minutes',
        'created_by'
    ];

    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'package_weight' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'assigned_at' => 'datetime',
        'pickup_at' => 'datetime',
        'departure_at' => 'datetime',
        'estimated_arrival' => 'datetime',
        'delivered_at' => 'datetime',
        'items_summary' => 'array',
        'location_updates' => 'array',
        'distance_km' => 'decimal:2'
    ];

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopeOnDelivery($query)
    {
        return $query->whereIn('status', ['picked_up', 'on_the_way', 'arrived']);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function deliveryStaff()
    {
        return $this->belongsTo(DeliveryStaff::class);
    }

    public function deliveryZone()
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function tracking()
    {
        return $this->hasMany(DeliveryTracking::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Methods
    public function updateStatus($status, $description = null, $location = null)
    {
        $oldStatus = $this->status;
        $this->status = $status;

        // Update timestamps based on status
        switch ($status) {
            case 'assigned':
                $this->assigned_at = now();
                break;
            case 'picked_up':
                $this->pickup_at = now();
                break;
            case 'on_the_way':
                $this->departure_at = now();
                $this->estimated_arrival = now()->addMinutes($this->deliveryZone->estimated_minutes ?? 30);
                break;
            case 'delivered':
                $this->delivered_at = now();
                break;
        }

        $this->save();

        // Record tracking history
        DeliveryTracking::create([
            'store_delivery_id' => $this->id,
            'status' => $status,
            'location' => $location,
            'description' => $description ?? "Status changed from {$oldStatus} to {$status}",
            'updated_by' => auth()->id()
        ]);

        return $this;
    }

    public function assignToStaff($deliveryStaffId)
    {
        $deliveryStaff = DeliveryStaff::findOrFail($deliveryStaffId);

        if (!$deliveryStaff->isAvailable() || !$deliveryStaff->canAcceptMoreDeliveries()) {
            throw new \Exception('Kurir tidak tersedia atau telah mencapai batas pengiriman hari ini');
        }

        $this->delivery_staff_id = $deliveryStaffId;
        $this->updateStatus('assigned', 'Delivery assigned to staff');

        return $this;
    }

    public function calculateDeliveryFee($distance = null)
    {
        if ($distance === null && $this->latitude && $this->longitude) {
            // Calculate distance from store location
            $storeLat = config('delivery.store_latitude');
            $storeLng = config('delivery.store_longitude');
            $distance = $this->calculateDistance($storeLat, $storeLng);
        }

        if (!$distance) {
            return $this->deliveryZone->delivery_fee ?? 0;
        }

        // Find applicable fee based on distance
        $fee = DeliveryFee::where('is_active', true)
            ->where('distance_min_km', '<=', $distance)
            ->where(function ($query) use ($distance) {
                $query->where('distance_max_km', '>=', $distance)
                      ->orWhereNull('distance_max_km');
            })
            ->orderBy('distance_min_km')
            ->first();

        if ($fee) {
            return $fee->base_fee + ($distance * $fee->fee_per_km);
        }

        return $this->deliveryZone->delivery_fee ?? 0;
    }

    private function calculateDistance($lat1, $lng1)
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        $earthRadius = 6371; // km

        $latFrom = deg2rad($lat1);
        $lngFrom = deg2rad($lng1);
        $latTo = deg2rad($this->latitude);
        $lngTo = deg2rad($this->longitude);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    public function getDeliveryQRCode()
    {
        // Generate QR code for delivery confirmation
        $data = [
            'delivery_code' => $this->delivery_code,
            'recipient_name' => $this->recipient_name,
            'verification_code' => substr(md5($this->id . $this->delivery_code), 0, 6)
        ];

        return json_encode($data);
    }

    public function isCOD()
    {
        return $this->payment_method === 'cod';
    }

    public function getStatusBadge()
    {
        $badges = [
            'pending' => 'secondary',
            'assigned' => 'info',
            'preparing' => 'warning',
            'picked_up' => 'primary',
            'on_the_way' => 'primary',
            'arrived' => 'success',
            'delivered' => 'success',
            'failed' => 'danger',
            'returned' => 'warning',
            'cancelled' => 'dark'
        ];

        return $badges[$this->status] ?? 'secondary';
    }
    
}
