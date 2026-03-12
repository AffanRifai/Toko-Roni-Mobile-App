<?php
// app/Notifications/VehicleUpdatedNotification.php

namespace App\Notifications;

use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VehicleUpdatedNotification extends Notification
{
    use Queueable;

    protected $vehicle;
    protected $updatedBy;
    protected $changes;

    public function __construct(Vehicle $vehicle, User $updatedBy, array $changes)
    {
        $this->vehicle = $vehicle;
        $this->updatedBy = $updatedBy;
        $this->changes = $changes;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $changedFields = [];
        foreach ($this->changes as $field => $value) {
            $fieldName = match($field) {
                'name' => 'Nama Kendaraan',
                'license_plate' => 'Plat Nomor',
                'type' => 'Jenis Kendaraan',
                'capacity_weight' => 'Kapasitas Berat',
                'capacity_volume' => 'Kapasitas Volume',
                'status' => 'Status',
                'last_maintenance' => 'Terakhir Maintenance',
                'notes' => 'Catatan',
                default => ucfirst(str_replace('_', ' ', $field))
            };
            $changedFields[] = $fieldName;
        }

        $typeLabels = [
            'motor' => 'Motor',
            'mobil' => 'Mobil',
            'truck' => 'Truck'
        ];

        return [
            'type' => 'vehicle_updated',
            'vehicle_id' => $this->vehicle->id,
            'vehicle_name' => $this->vehicle->name,
            'vehicle_plate' => $this->vehicle->license_plate,
            'vehicle_type' => $typeLabels[$this->vehicle->type] ?? $this->vehicle->type,
            'vehicle_status' => $this->vehicle->status,
            'updated_by' => $this->updatedBy->name,
            'updated_by_id' => $this->updatedBy->id,
            'changed_fields' => $changedFields,
            'message' => 'Data kendaraan ' . $this->vehicle->name . ' (' . $this->vehicle->license_plate . ') telah diperbarui oleh ' . $this->updatedBy->name,
            'icon' => 'fa-solid fa-truck',
            'color' => 'yellow',
            'time' => now()->toDateTimeString()
        ];
    }
}