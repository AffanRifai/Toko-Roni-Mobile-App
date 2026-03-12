<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'category_id',
        'description',
        'price',
        'cost_price',
        'profit_margin',
        'stock',
        'min_stock',
        'unit',
        'supplier_id',
        'barcode',
        'weight',
        'dimensions',
        'expiry_date',
        'image',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'weight' => 'decimal:2',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'stock_status',
        'image_url',
        'profit_amount',
        'formatted_price',
        'formatted_cost_price',
    ];

    /* ==========================
     | Accessors
     ========================== */

    public function getStockStatusAttribute()
    {
        if ($this->stock === 0) {
            return 'out_of_stock';
        }

        if ($this->stock <= $this->min_stock) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function getImageUrlAttribute()
    {
        return $this->image
            ? Storage::url($this->image)
            : asset('images/default-product.png');
    }

    public function getProfitAmountAttribute()
    {
        return $this->cost_price
            ? $this->price - $this->cost_price
            : 0;
    }

    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getFormattedCostPriceAttribute()
    {
        return $this->cost_price
            ? 'Rp ' . number_format($this->cost_price, 0, ',', '.')
            : '-';
    }

    /* ==========================
     | Relationships
     ========================== */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    /* ==========================
     | Scopes
     ========================== */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock')
                     ->where('stock', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock', 0);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                     ->where('expiry_date', '<=', now()->addDays($days));
    }

    /* ==========================
     | Business Logic
     ========================== */

    public function canSell($quantity)
    {
        return $this->is_active && $this->stock >= $quantity;
    }

    public function decreaseStock($quantity)
    {
        if ($this->stock < $quantity) {
            throw new \Exception('Stok tidak mencukupi');
        }

        $this->decrement('stock', $quantity);
        return $this;
    }

    public function increaseStock($quantity)
    {
        $this->increment('stock', $quantity);
        return $this;
    }

    public function isLowStock()
    {
        return $this->stock <= $this->min_stock && $this->stock > 0;
    }

    public function isOutOfStock()
    {
        return $this->stock === 0;
    }
}
