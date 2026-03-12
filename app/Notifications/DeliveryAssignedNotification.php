<?php
// app/Notifications/DeliveryAssignedNotification.php

namespace App\Notifications;

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeliveryAssignedNotification extends Notification
{
    use Queueable;

    protected $delivery;
    protected $assignedBy;
    protected $driver;
    protected $vehicle;

    public function __construct(Delivery $delivery, User $assignedBy, User $driver, $vehicle)
    {
        $this->delivery = $delivery;
        $this->assignedBy = $assignedBy;
        $this->driver = $driver;
        $this->vehicle = $vehicle;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'delivery_assigned',
            'delivery_id' => $this->delivery->id,
            'delivery_code' => $this->delivery->delivery_code,
            'origin' => $this->delivery->origin,
            'destination' => $this->delivery->destination,
            'driver_name' => $this->driver->name,
            'driver_id' => $this->driver->id,
            'vehicle_name' => $this->vehicle->name ?? '-',
            'vehicle_plate' => $this->vehicle->license_plate ?? '-',
            'assigned_by' => $this->assignedBy->name,
            'assigned_by_id' => $this->assignedBy->id,
            'message' => 'Pengiriman ' . $this->delivery->delivery_code . ' telah ditugaskan ke ' . $this->driver->name . ' dengan kendaraan ' . ($this->vehicle->name ?? '-'),
            'icon' => 'fa-solid fa-user-check',
            'color' => 'purple',
            'time' => now()->toDateTimeString()
        ];
    }
}