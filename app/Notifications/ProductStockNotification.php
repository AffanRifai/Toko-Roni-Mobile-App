<?php
// app/Notifications/ProductStockNotification.php (untuk notifikasi stok)

namespace App\Notifications;

use App\Models\Product;
use App\Models\User;
use App\Traits\InAppNotificationTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductStockNotification extends Notification
{
    use Queueable, InAppNotificationTrait;

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
            'low_stock' => 'fas fa-exclamation-triangle',
            'out_of_stock' => 'fas fa-times-circle',
            'restock' => 'fas fa-arrow-up',
        ];

        $colors = [
            'low_stock' => 'yellow',
            'out_of_stock' => 'red',
            'restock' => 'green',
        ];

        return $this->getInAppPayload(
            'product_stock_' . $this->type,
            $messages[$this->type] ?? 'Notifikasi stok produk',
            $icons[$this->type] ?? 'fas fa-box',
            $colors[$this->type] ?? 'blue',
            route('products.index'), // URL ke halaman produk
            [
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'product_code' => $this->product->code,
                'product_stock' => $this->product->stock,
                'product_unit' => $this->product->unit,
                'min_stock' => $this->product->min_stock,
                'category_name' => $this->product->category?->name,
            ]
        );
    }
}