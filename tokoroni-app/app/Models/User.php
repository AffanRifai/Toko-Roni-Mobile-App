<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
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

    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var list<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'face_registered_at' => 'datetime', // ✅ TAMBAHKAN INI
        'face_score' => 'float',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'face_descriptor',
    ];
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
     * Get face descriptor as array
     */
    public function getFaceDescriptorArray(): ?array
    {
        if (!$this->face_descriptor) {
            return null;
        }

        return json_decode($this->face_descriptor, true);
    }


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'face_registered_at' => 'datetime',
        ];
    }

    // app/Models/User.php

    // Tambahkan method ini ke model User
    public function isCourier()
    {
        return $this->role === 'kurir';
    }

    public function updateLocation($latitude, $longitude)
    {
        $this->update([
            'current_lat' => $latitude,
            'current_lng' => $longitude,
            'last_location_update' => now()
        ]);
    }

    public function isAvailableForDelivery()
    {
        return $this->delivery_status === 'active'
            && $this->is_active
            && $this->role === 'kurir';
    }

    public function canAcceptMoreDeliveries()
    {
        $maxDeliveries = config('delivery.max_daily_deliveries', 10);
        $todayCount = $this->deliveries()
            ->whereDate('created_at', today())
            ->count();

        return $todayCount < $maxDeliveries;
    }

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
    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'user_id');
    }
    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'vehicle_driver', 'driver_id', 'vehicle_id')
            ->withTimestamps();
    }

    public function isDriver()
    {
        return $this->role === 'driver';
    }
}
