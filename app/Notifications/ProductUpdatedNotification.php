<?php
// app/Notifications/ProductUpdatedNotification.php

namespace App\Notifications;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductUpdatedNotification extends Notification
{
    use Queueable;

    protected $product;
    protected $updatedBy;
    protected $changes;

    public function __construct(Product $product, User $updatedBy, array $changes)
    {
        $this->product = $product;
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
                'name' => 'Nama Produk',
                'code' => 'Kode Produk',
                'price' => 'Harga Jual',
                'cost_price' => 'Harga Modal',
                'stock' => 'Stok',
                'min_stock' => 'Stok Minimum',
                'unit' => 'Satuan',
                'category_id' => 'Kategori',
                'description' => 'Deskripsi',
                'barcode' => 'Barcode',
                'weight' => 'Berat',
                'dimensions' => 'Dimensi',
                'expiry_date' => 'Tanggal Kadaluarsa',
                'is_active' => 'Status',
                default => ucfirst(str_replace('_', ' ', $field))
            };
            $changedFields[] = $fieldName;
        }

        return [
            'type' => 'product_updated',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_code' => $this->product->code,
            'category_name' => $this->product->category?->name,
            'updated_by' => $this->updatedBy->name,
            'updated_by_id' => $this->updatedBy->id,
            'changed_fields' => $changedFields,
            'message' => 'Produk "' . $this->product->name . '" (' . $this->product->code . ') telah diperbarui oleh ' . $this->updatedBy->name,
            'icon' => 'fa-solid fa-box',
            'color' => 'blue',
            'time' => now()->toDateTimeString()
        ];
    }
}