<?php
// app/Notifications/ProductStockNotification.php (untuk notifikasi stok)

namespace App\Notifications;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductStockNotification extends Notification
{
    use Queueable;

    protected $product;
    protected $type; // 'low_stock', 'out_of_stock', 'restock'

    public function __construct(Product $product, string $type)
    {
        $this->product = $product;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $messages = [
            'low_stock' => 'Stok produk "' . $this->product->name . '" (' . $this->product->code . ') menipis. Tersisa ' . $this->product->stock . ' ' . $this->product->unit,
            'out_of_stock' => 'Produk "' . $this->product->name . '" (' . $this->product->code . ') telah habis! Segera lakukan restock.',
            'restock' => 'Stok produk "' . $this->product->name . '" (' . $this->product->code . ') telah ditambahkan. Stok sekarang: ' . $this->product->stock . ' ' . $this->product->unit,
        ];

        $icons = [
            'low_stock' => 'fa-solid fa-exclamation-triangle',
            'out_of_stock' => 'fa-solid fa-times-circle',
            'restock' => 'fa-solid fa-arrow-up',
        ];

        $colors = [
            'low_stock' => 'yellow',
            'out_of_stock' => 'red',
            'restock' => 'green',
        ];

        return [
            'type' => 'product_stock_' . $this->type,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_code' => $this->product->code,
            'product_stock' => $this->product->stock,
            'product_unit' => $this->product->unit,
            'min_stock' => $this->product->min_stock,
            'category_name' => $this->product->category?->name,
            'message' => $messages[$this->type] ?? 'Notifikasi stok produk',
            'icon' => $icons[$this->type] ?? 'fa-solid fa-box',
            'color' => $colors[$this->type] ?? 'blue',
            'time' => now()->toDateTimeString()
        ];
    }
}