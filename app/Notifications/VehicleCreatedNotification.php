<?php
// app/Notifications/VehicleCreatedNotification.php

namespace App\Notifications;

use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VehicleCreatedNotification extends Notification
{
    use Queueable;

    protected $vehicle;
    protected $createdBy;

    public function __construct(Vehicle $vehicle, User $createdBy)
    {
        $this->vehicle = $vehicle;
        $this->createdBy = $createdBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $typeLabels = [
            'motor' => 'Motor',
            'mobil' => 'Mobil',
            'truck' => 'Truck'
        ];

        return [
            'type' => 'vehicle_created',
            'vehicle_id' => $this->vehicle->id,
            'vehicle_name' => $this->vehicle->name,
            'vehicle_plate' => $this->vehicle->license_plate,
            'vehicle_type' => $typeLabels[$this->vehicle->type] ?? $this->vehicle->type,
            'vehicle_status' => $this->vehicle->status,
            'created_by' => $this->createdBy->name,
            'created_by_id' => $this->createdBy->id,
            'message' => 'Kendaraan baru ' . $this->vehicle->name . ' (' . $this->vehicle->license_plate . ') telah ditambahkan oleh ' . $this->createdBy->name,
            'icon' => 'fa-solid fa-truck',
            'color' => 'green',
            'time' => now()->toDateTimeString()
        ];
    }
}