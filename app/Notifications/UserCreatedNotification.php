<?php
// app/Notifications/UserCreatedNotification.php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserCreatedNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $createdBy;

    public function __construct(User $user, User $createdBy)
    {
        $this->user = $user;
        $this->createdBy = $createdBy;
    }

    /**
     * Tentukan channel pengiriman notifikasi
     */
    public function via($notifiable)
    {
        return ['database']; // HANYA database dulu, nonaktifkan mail
    }

    /**
     * Data untuk disimpan di database
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'user_created',
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'user_role' => $this->user->role,
            'created_by' => $this->createdBy->name,
            'created_by_id' => $this->createdBy->id,
            'message' => 'Pengguna baru ' . $this->user->name . ' telah ditambahkan sebagai ' . ucfirst(str_replace('_', ' ', $this->user->role)),
            'icon' => 'user-plus',
            'color' => 'green',
            'time' => now()->toDateTimeString()
        ];
    }
}