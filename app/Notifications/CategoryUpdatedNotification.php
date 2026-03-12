<?php
// app/Notifications/CategoryUpdatedNotification.php

namespace App\Notifications;

use App\Models\Category;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CategoryUpdatedNotification extends Notification
{
    use Queueable;

    protected $category;
    protected $updatedBy;
    protected $changes;

    public function __construct(Category $category, User $updatedBy, array $changes)
    {
        $this->category = $category;
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
                'name' => 'Nama Kategori',
                'description' => 'Deskripsi',
                'is_active' => 'Status',
                'slug' => 'URL Slug',
                default => ucfirst(str_replace('_', ' ', $field))
            };
            $changedFields[] = $fieldName;
        }

        return [
            'type' => 'category_updated',
            'category_id' => $this->category->id,
            'category_name' => $this->category->name,
            'category_slug' => $this->category->slug,
            'updated_by' => $this->updatedBy->name,
            'updated_by_id' => $this->updatedBy->id,
            'changed_fields' => $changedFields,
            'message' => 'Kategori "' . $this->category->name . '" telah diperbarui oleh ' . $this->updatedBy->name,
            'icon' => 'fa-solid fa-tag',
            'color' => 'blue',
            'time' => now()->toDateTimeString()
        ];
    }
}