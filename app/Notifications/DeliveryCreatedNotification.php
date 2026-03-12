<?php
// app/Notifications/DeliveryCreatedNotification.php

namespace App\Notifications;

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeliveryCreatedNotification extends Notification
{
    use Queueable;

    protected $delivery;
    protected $createdBy;

    public function __construct(Delivery $delivery, User $createdBy)
    {
        $this->delivery = $delivery;
        $this->createdBy = $createdBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'delivery_created',
            'delivery_id' => $this->delivery->id,
            'delivery_code' => $this->delivery->delivery_code,
            'origin' => $this->delivery->origin,
            'destination' => $this->delivery->destination,
            'total_items' => $this->delivery->total_items,
            'status' => $this->delivery->status,
            'created_by' => $this->createdBy->name,
            'created_by_id' => $this->createdBy->id,
            'message' => 'Pengiriman baru ' . $this->delivery->delivery_code . ' telah dibuat dari ' . $this->delivery->origin . ' ke ' . $this->delivery->destination,
            'icon' => 'fa-solid fa-truck',
            'color' => 'blue',
            'time' => now()->toDateTimeString()
        ];
    }
}