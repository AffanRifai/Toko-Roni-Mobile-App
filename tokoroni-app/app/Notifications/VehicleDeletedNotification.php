<?php
// app/Notifications/VehicleDeletedNotification.php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VehicleDeletedNotification extends Notification
{
    use Queueable;

    protected $vehicleName;
    protected $vehiclePlate;
    protected $deletedBy;

    public function __construct(string $vehicleName, string $vehiclePlate, User $deletedBy)
    {
        $this->vehicleName = $vehicleName;
        $this->vehiclePlate = $vehiclePlate;
        $this->deletedBy = $deletedBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'vehicle_deleted',
            'vehicle_name' => $this->vehicleName,
            'vehicle_plate' => $this->vehiclePlate,
            'deleted_by' => $this->deletedBy->name,
            'deleted_by_id' => $this->deletedBy->id,
            'message' => 'Kendaraan ' . $this->vehicleName . ' (' . $this->vehiclePlate . ') telah dihapus oleh ' . $this->deletedBy->name,
            'icon' => 'fa-solid fa-trash',
            'color' => 'red',
            'time' => now()->toDateTimeString()
        ];
    }
}