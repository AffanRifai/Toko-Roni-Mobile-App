<?php
// app/Notifications/ProductCreatedNotification.php

namespace App\Notifications;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductCreatedNotification extends Notification
{
    use Queueable;

    protected $product;
    protected $createdBy;

    public function __construct(Product $product, User $createdBy)
    {
        $this->product = $product;
        $this->createdBy = $createdBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'product_created',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_code' => $this->product->code,
            'product_price' => $this->product->price,
            'product_stock' => $this->product->stock,
            'category_id' => $this->product->category_id,
            'category_name' => $this->product->category?->name,
            'created_by' => $this->createdBy->name,
            'created_by_id' => $this->createdBy->id,
            'message' => 'Produk baru "' . $this->product->name . '" (' . $this->product->code . ') telah ditambahkan oleh ' . $this->createdBy->name,
            'icon' => 'fa-solid fa-box',
            'color' => 'green',
            'time' => now()->toDateTimeString()
        ];
    }
}