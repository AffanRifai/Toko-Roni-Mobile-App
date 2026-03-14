<?php
// app/Notifications/DeliveryStatusChangedNotification.php

namespace App\Notifications;

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeliveryStatusChangedNotification extends Notification
{
    use Queueable;

    protected $delivery;
    protected $updatedBy;
    protected $oldStatus;
    protected $newStatus;

    public function __construct(Delivery $delivery, User $updatedBy, string $oldStatus, string $newStatus)
    {
        $this->delivery = $delivery;
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
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'assigned' => 'Ditugaskan',
            'picked_up' => 'Telah Diambil',
            'on_delivery' => 'Dalam Perjalanan',
            'delivered' => 'Terkirim',
            'failed' => 'Gagal',
            'cancelled' => 'Dibatalkan'
        ];

        $oldLabel = $statusLabels[$this->oldStatus] ?? $this->oldStatus;
        $newLabel = $statusLabels[$this->newStatus] ?? $this->newStatus;

        return [
            'type' => 'delivery_status_changed',
            'delivery_id' => $this->delivery->id,
            'delivery_code' => $this->delivery->delivery_code,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'old_status_label' => $oldLabel,
            'new_status_label' => $newLabel,
            'updated_by' => $this->updatedBy->name,
            'updated_by_id' => $this->updatedBy->id,
            'message' => 'Status pengiriman ' . $this->delivery->delivery_code . ' berubah dari ' . $oldLabel . ' menjadi ' . $newLabel,
            'icon' => 'fa-solid fa-rotate',
            'color' => $this->getColorForStatus($this->newStatus),
            'time' => now()->toDateTimeString()
        ];
    }

    private function getColorForStatus($status)
    {
        return match($status) {
            'delivered' => 'green',
            'failed', 'cancelled' => 'red',
            'on_delivery' => 'orange',
            'assigned', 'picked_up' => 'purple',
            'pending', 'processing' => 'blue',
            default => 'gray'
        };
    }
}