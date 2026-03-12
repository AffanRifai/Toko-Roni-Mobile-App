<?php
// app/Notifications/UserUpdatedNotification.php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserUpdatedNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $updatedBy;
    protected $changes;

    public function __construct(User $user, User $updatedBy, array $changes)
    {
        $this->user = $user;
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
            $changedFields[] = ucfirst(str_replace('_', ' ', $field));
        }

        return [
            'type' => 'user_updated',
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'user_role' => $this->user->role,
            'updated_by' => $this->updatedBy->name,
            'updated_by_id' => $this->updatedBy->id,
            'changed_fields' => $changedFields,
            'message' => 'Data pengguna ' . $this->user->name . ' telah diperbarui oleh ' . $this->updatedBy->name,
            'icon' => 'user-edit',
            'color' => 'blue',
            'time' => now()->toDateTimeString()
        ];
    }
}