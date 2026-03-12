<?php
// app/Notifications/CategoryDeletedNotification.php

namespace App\Notifications;

use App\Models\Category;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CategoryDeletedNotification extends Notification
{
    use Queueable;

    protected $categoryName;
    protected $deletedBy;

    public function __construct(string $categoryName, User $deletedBy)
    {
        $this->categoryName = $categoryName;
        $this->deletedBy = $deletedBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'category_deleted',
            'category_name' => $this->categoryName,
            'deleted_by' => $this->deletedBy->name,
            'deleted_by_id' => $this->deletedBy->id,
            'message' => 'Kategori "' . $this->categoryName . '" telah dihapus oleh ' . $this->deletedBy->name,
            'icon' => 'fa-solid fa-trash',
            'color' => 'red',
            'time' => now()->toDateTimeString()
        ];
    }
}