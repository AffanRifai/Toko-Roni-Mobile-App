<?php
// app/Notifications/ProductDeletedNotification.php

namespace App\Notifications;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductDeletedNotification extends Notification
{
    use Queueable;

    protected $productName;
    protected $productCode;
    protected $deletedBy;

    public function __construct(string $productName, string $productCode, User $deletedBy)
    {
        $this->productName = $productName;
        $this->productCode = $productCode;
        $this->deletedBy = $deletedBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'product_deleted',
            'product_name' => $this->productName,
            'product_code' => $this->productCode,
            'deleted_by' => $this->deletedBy->name,
            'deleted_by_id' => $this->deletedBy->id,
            'message' => 'Produk "' . $this->productName . '" (' . $this->productCode . ') telah dihapus oleh ' . $this->deletedBy->name,
            'icon' => 'fa-solid fa-trash',
            'color' => 'red',
            'time' => now()->toDateTimeString()
        ];
    }
}