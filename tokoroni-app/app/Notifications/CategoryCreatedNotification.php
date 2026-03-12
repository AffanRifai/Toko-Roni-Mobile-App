<?php
// app/Notifications/CategoryCreatedNotification.php

namespace App\Notifications;

use App\Models\Category;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CategoryCreatedNotification extends Notification
{
    use Queueable;

    protected $category;
    protected $createdBy;

    public function __construct(Category $category, User $createdBy)
    {
        $this->category = $category;
        $this->createdBy = $createdBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'category_created',
            'category_id' => $this->category->id,
            'category_name' => $this->category->name,
            'category_slug' => $this->category->slug,
            'created_by' => $this->createdBy->name,
            'created_by_id' => $this->createdBy->id,
            'message' => 'Kategori baru "' . $this->category->name . '" telah ditambahkan oleh ' . $this->createdBy->name,
            'icon' => 'fa-solid fa-tag',
            'color' => 'green',
            'time' => now()->toDateTimeString()
        ];
    }
}