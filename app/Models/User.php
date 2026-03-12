<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Delivery;
use App\Models\Vehicle;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'image',
        'status',
        'gender',
        'jenis_toko',
        'face_descriptor',
        'face_score',
        'face_registered_at',
        'is_active',
        'current_lat',
        'current_lng',
        'last_location_update',
        'delivery_status',
        'delivery_rating',
        'delivery_rating_count'
    ];

    /**
     * Hidden attributes
     */
    protected $hidden = [
        'password',
        'remember_token',
        'face_descriptor'
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'face_registered_at' => 'datetime',
        'face_score' => 'float',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_location_update' => 'datetime',
        'delivery_rating' => 'float'
    ];

    /*
    |--------------------------------------------------------------------------
    | FACE RECOGNITION
    |--------------------------------------------------------------------------
    */

    /**
     * Scope user yang memiliki face registered
     */
    public function scopeHasFaceRegistered($query)
    {
        return $query->whereNotNull('face_descriptor')
            ->where('face_score', '>=', 0.5);
    }

    /**
     * Cek apakah user sudah registrasi wajah
     */
    public function hasFaceRegistered(): bool
    {
        return !is_null($this->face_descriptor) &&
            $this->face_score >= 0.5;
    }

    /**
     * Ambil face descriptor sebagai array
     */
    public function getFaceDescriptorArray(): ?array
    {
        if (!$this->face_descriptor) {
            return null;
        }

        return json_decode($this->face_descriptor, true);
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE CHECK
    |--------------------------------------------------------------------------
    */

    public function isCourier()
    {
        return $this->role === 'kurir';
    }

    public function isDriver()
    {
        return $this->role === 'driver';
    }

    /*
    |--------------------------------------------------------------------------
    | DELIVERY MANAGEMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi deliveries
     */
    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'user_id');
    }

    /**
     * Update lokasi kurir
     */
    public function updateLocation($latitude, $longitude)
    {
        $this->update([
            'current_lat' => $latitude,
            'current_lng' => $longitude,
            'last_location_update' => now()
        ]);
    }

    /**
     * Cek apakah kurir siap menerima delivery
     */
    public function isAvailableForDelivery()
    {
        return $this->delivery_status === 'active'
            && $this->is_active
            && $this->role === 'kurir';
    }

    /**
     * Cek apakah kurir masih bisa menerima delivery
     */
    public function canAcceptMoreDeliveries()
    {
        $maxDeliveries = config('delivery.max_daily_deliveries', 10);

        $todayCount = $this->deliveries()
            ->whereDate('created_at', today())
            ->count();

        return $todayCount < $maxDeliveries;
    }

    /**
     * Update rating kurir
     */
    public function updateRating($rating)
    {
        $totalRatings = $this->delivery_rating_count ?? 0;
        $currentRating = $this->delivery_rating ?? 0;

        $newTotal = $totalRatings + 1;

        $newRating = (($currentRating * $totalRatings) + $rating) / $newTotal;

        $this->update([
            'delivery_rating' => round($newRating, 1),
            'delivery_rating_count' => $newTotal
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VEHICLE RELATION
    |--------------------------------------------------------------------------
    */

    public function vehicles()
    {
        return $this->belongsToMany(
            Vehicle::class,
            'vehicle_driver',
            'driver_id',
            'vehicle_id'
        )->withTimestamps();
    }
}
