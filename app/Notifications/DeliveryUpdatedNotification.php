<?php
// app/Notifications/DeliveryUpdatedNotification.php

namespace App\Notifications;

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeliveryUpdatedNotification extends Notification
{
    use Queueable;

    protected $delivery;
    protected $updatedBy;
    protected $changes;

    public function __construct(Delivery $delivery, User $updatedBy, array $changes)
    {
        $this->delivery = $delivery;
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
                'origin' => 'Asal',
                'destination' => 'Tujuan',
                'total_items' => 'Jumlah Item',
                'total_weight' => 'Berat',
                'total_volume' => 'Volume',
                'status' => 'Status',
                'estimated_delivery_time' => 'Estimasi Waktu',
                'user_id' => 'Kurir',
                'vehicle_id' => 'Kendaraan',
                'notes' => 'Catatan',
                default => ucfirst(str_replace('_', ' ', $field))
            };
            $changedFields[] = $fieldName;
        }

        return [
            'type' => 'delivery_updated',
            'delivery_id' => $this->delivery->id,
            'delivery_code' => $this->delivery->delivery_code,
            'origin' => $this->delivery->origin,
            'destination' => $this->delivery->destination,
            'status' => $this->delivery->status,
            'updated_by' => $this->updatedBy->name,
            'updated_by_id' => $this->updatedBy->id,
            'changed_fields' => $changedFields,
            'message' => 'Pengiriman ' . $this->delivery->delivery_code . ' telah diperbarui oleh ' . $this->updatedBy->name,
            'icon' => 'fa-solid fa-truck',
            'color' => 'yellow',
            'time' => now()->toDateTimeString()
        ];
    }
}