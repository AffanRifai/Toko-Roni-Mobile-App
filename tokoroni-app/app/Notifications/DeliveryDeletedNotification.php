<?php
// app/Notifications/DeliveryDeletedNotification.php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeliveryDeletedNotification extends Notification
{
    use Queueable;

    protected $deliveryCode;
    protected $deletedBy;

    public function __construct(string $deliveryCode, User $deletedBy)
    {
        $this->deliveryCode = $deliveryCode;
        $this->deletedBy = $deletedBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'delivery_deleted',
            'delivery_code' => $this->deliveryCode,
            'deleted_by' => $this->deletedBy->name,
            'deleted_by_id' => $this->deletedBy->id,
            'message' => 'Pengiriman ' . $this->deliveryCode . ' telah dihapus oleh ' . $this->deletedBy->name,
            'icon' => 'fa-solid fa-trash',
            'color' => 'red',
            'time' => now()->toDateTimeString()
        ];
    }
}