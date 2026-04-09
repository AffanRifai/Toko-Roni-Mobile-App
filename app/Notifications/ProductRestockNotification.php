<?php

namespace App\Notifications;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductRestockNotification extends Notification
{
    use Queueable;

    protected $product;
    protected $user;
    protected $quantity;
    protected $oldStock;
    protected $newStock;

    public function __construct(Product $product, User $user, $quantity, $oldStock, $newStock)
    {
        $this->product = $product;
        $this->user = $user;
        $this->quantity = $quantity;
        $this->oldStock = $oldStock;
        $this->newStock = $newStock;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Restock Produk',
            'message' => $this->user->name . ' melakukan restock ' . $this->product->name . ' sebanyak ' . $this->quantity . ' ' . $this->product->unit,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_code' => $this->product->code,
            'quantity' => $this->quantity,
            'old_stock' => $this->oldStock,
            'new_stock' => $this->newStock,
            'restocked_by' => $this->user->name,
            'restocked_by_id' => $this->user->id,
            'type' => 'product_restock',
            'icon' => 'fas fa-boxes',
            'color' => 'green',
            'url' => route('products.show', $this->product->id),
            'created_at' => now(),
        ];
    }
}