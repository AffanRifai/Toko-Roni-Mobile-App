<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryStaff extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'vehicle_type',
        'vehicle_number',
        'vehicle_brand',
        'phone',
        'working_days',
        'shift_start',
        'shift_end',
        'max_deliveries_per_day',
        'delivery_radius_km',
        'status',
        'current_lat',
        'current_lng',
        'last_location_update',
        'total_deliveries',
        'rating'
    ];

    protected $casts = [
        'working_days' => 'array',
        'delivery_radius_km' => 'decimal:2',
        'current_lat' => 'decimal:8',
        'current_lng' => 'decimal:8',
        'rating' => 'decimal:2',
        'shift_start' => 'datetime:H:i',
        'shift_end' => 'datetime:H:i',
        'last_location_update' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries()
    {
        return $this->hasMany(StoreDelivery::class);
    }

    public function schedules()
    {
        return $this->hasMany(DeliverySchedule::class);
    }

    public function routes()
    {
        return $this->hasMany(DeliveryRoute::class);
    }

    public function performanceRecords()
    {
        return $this->hasMany(DeliveryPerformance::class);
    }

    public function isAvailable()
    {
        return $this->status === 'active' || $this->status === 'on_delivery';
    }

    public function isOnShift()
    {
        $now = now();
        $currentDay = $now->dayOfWeekIso; // 1=Monday, 7=Sunday
        $currentTime = $now->format('H:i:s');

        return in_array($currentDay, $this->working_days ?? [])
            && $currentTime >= $this->shift_start
            && $currentTime <= $this->shift_end;
    }

    public function updateLocation($lat, $lng)
    {
        $this->update([
            'current_lat' => $lat,
            'current_lng' => $lng,
            'last_location_update' => now()
        ]);
    }

    public function getTodayDeliveriesCount()
    {
        return $this->deliveries()
            ->whereDate('created_at', today())
            ->whereIn('status', ['assigned', 'picked_up', 'on_the_way'])
            ->count();
    }

    public function canAcceptMoreDeliveries()
    {
        return $this->getTodayDeliveriesCount() < $this->max_deliveries_per_day;
    }

    public function calculateDistance($lat, $lng)
    {
        if (!$this->current_lat || !$this->current_lng) {
            return null;
        }

        $earthRadius = 6371; // km

        $latFrom = deg2rad($this->current_lat);
        $lngFrom = deg2rad($this->current_lng);
        $latTo = deg2rad($lat);
        $lngTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    public function getCurrentSchedule()
    {
        return $this->schedules()
            ->whereDate('date', today())
            ->where('status', 'scheduled')
            ->first();
    }
}
