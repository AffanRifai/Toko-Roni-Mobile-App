<?php
// app/Notifications/VehicleStatusChangedNotification.php

namespace App\Notifications;

use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VehicleStatusChangedNotification extends Notification
{
    use Queueable;

    protected $vehicle;
    protected $updatedBy;
    protected $oldStatus;
    protected $newStatus;

    public function __construct(Vehicle $vehicle, User $updatedBy, string $oldStatus, string $newStatus)
    {
        $this->vehicle = $vehicle;
        $this->updatedBy = $updatedBy;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $statusLabels = [
            'available' => 'Tersedia',
            'in_use' => 'Sedang Digunakan',
            'maintenance' => 'Servis/Maintenance'
        ];

        $oldLabel = $statusLabels[$this->oldStatus] ?? $this->oldStatus;
        $newLabel = $statusLabels[$this->newStatus] ?? $this->newStatus;

        return [
            'type' => 'vehicle_status_changed',
            'vehicle_id' => $this->vehicle->id,
            'vehicle_name' => $this->vehicle->name,
            'vehicle_plate' => $this->vehicle->license_plate,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'old_status_label' => $oldLabel,
            'new_status_label' => $newLabel,
            'updated_by' => $this->updatedBy->name,
            'updated_by_id' => $this->updatedBy->id,
            'message' => 'Status kendaraan ' . $this->vehicle->name . ' (' . $this->vehicle->license_plate . ') berubah dari ' . $oldLabel . ' menjadi ' . $newLabel,
            'icon' => 'fa-solid fa-rotate',
            'color' => $this->getColorForStatus($this->newStatus),
            'time' => now()->toDateTimeString()
        ];
    }

    private function getColorForStatus($status)
    {
        return match($status) {
            'available' => 'green',
            'in_use' => 'blue',
            'maintenance' => 'red',
            default => 'gray'
        };
    }
}