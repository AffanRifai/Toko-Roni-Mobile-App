<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_name',
        'customer_phone',
        'discount',
        'payment_method', // tunai, transfer, kredit
        'cash_received',
        'total_amount',
        'change',
        'user_id',
        'member_id',
        'payment_status', // LUNAS, BELUM LUNAS
        'due_date', // tanggal jatuh tempo untuk kredit
        'notes', // catatan tambahan

        // ===== FIELD BARU UNTUK PENGIRIMAN =====
        'need_delivery', // boolean: perlu dikirim atau tidak
        'delivery_address', // alamat pengiriman
        'recipient_name', // nama penerima
        'recipient_phone', // no telepon penerima
        'items_to_deliver', // json: daftar barang yang akan dikirim
        'items_taken', // json: daftar barang yang dibawa sendiri
        'desired_delivery_date', // tanggal pengiriman yang diinginkan
        'delivery_notes', // catatan pengiriman
        'delivery_fee', // biaya pengiriman
        'delivery_status' // status pengiriman (pending, processing, dll)
    ];

    protected $casts = [
        'discount' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'change' => 'decimal:2',
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

        // ===== CASTING UNTUK FIELD BARU =====
        'need_delivery' => 'boolean',
        'items_to_deliver' => 'array',
        'items_taken' => 'array',
        'desired_delivery_date' => 'date',
        'delivery_fee' => 'decimal:2'
    ];

    // Relasi ke items transaksi
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    // Relasi ke user (kasir)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke member
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    // Relasi ke piutang (jika transaksi kredit)
    public function receivable()
    {
        return $this->hasOne(Receivable::class);
    }

    // Relasi ke delivery (pengiriman)
    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    // ===== ACCESSORS UNTUK PENGIRIMAN =====

    /**
     * Get items to deliver as collection
     */
    public function getItemsToDeliverListAttribute()
    {
        if (empty($this->items_to_deliver)) {
            return collect([]);
        }

        $items = is_string($this->items_to_deliver)
            ? json_decode($this->items_to_deliver, true)
            : $this->items_to_deliver;

        return collect($items);
    }

    /**
     * Get items taken as collection
     */
    public function getItemsTakenListAttribute()
    {
        if (empty($this->items_taken)) {
            return collect([]);
        }

        $items = is_string($this->items_taken)
            ? json_decode($this->items_taken, true)
            : $this->items_taken;

        return collect($items);
    }

    /**
     * Get total items to deliver
     */
    public function getTotalItemsToDeliverAttribute()
    {
        return $this->items_to_deliver_list->sum('qty');
    }

    /**
     * Get total items taken
     */
    public function getTotalItemsTakenAttribute()
    {
        return $this->items_taken_list->sum('qty');
    }

    /**
     * Check if item is in delivery list
     */
    public function isItemDelivered($productId)
    {
        return $this->items_to_deliver_list->contains('id', $productId);
    }

    /**
     * Check if item is taken by customer
     */
    public function isItemTaken($productId)
    {
        return $this->items_taken_list->contains('id', $productId);
    }

    /**
     * Get delivery status badge class
     */
    public function getDeliveryStatusBadgeClass()
    {
        if (!$this->delivery) {
            return 'bg-gray-100 text-gray-800';
        }

        return $this->delivery->getStatusBadgeClass();
    }

    /**
     * Get delivery status icon
     */
    public function getDeliveryStatusIcon()
    {
        if (!$this->delivery) {
            return 'fa-clock';
        }

        return $this->delivery->getStatusIcon();
    }

    // ===== HELPER METHODS =====

    /**
     * Cek apakah transaksi ini kredit
     */
    public function isCredit()
    {
        return $this->payment_method === 'kredit';
    }

    /**
     * Cek apakah transaksi sudah lunas
     */
    public function isPaid()
    {
        return $this->payment_status === 'LUNAS';
    }

    /**
     * Cek apakah transaksi sudah melewati jatuh tempo
     */
    public function isOverdue()
    {
        if (!$this->isCredit() || $this->isPaid()) {
            return false;
        }

        return $this->due_date && now()->startOfDay()->gt($this->due_date);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        if ($this->isPaid()) {
            return 'bg-green-100 text-green-800';
        }

        if ($this->isOverdue()) {
            return 'bg-red-100 text-red-800';
        }

        if ($this->isCredit()) {
            return 'bg-yellow-100 text-yellow-800';
        }

        return 'bg-gray-100 text-gray-800';
    }

    /**
     * Get status text
     */
    public function getStatusText()
    {
        if ($this->payment_method === 'kredit') {
            return $this->payment_status;
        }

        return 'LUNAS';
    }

    // ===== SCOPES =====

    /**
     * Scope untuk filter berdasarkan status pembayaran
     */
    public function scopePaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope untuk filter transaksi kredit
     */
    public function scopeCredit($query)
    {
        return $query->where('payment_method', 'kredit');
    }

    /**
     * Scope untuk filter transaksi tunai
     */
    public function scopeCash($query)
    {
        return $query->where('payment_method', 'tunai');
    }

    /**
     * Scope untuk filter transaksi yang sudah jatuh tempo
     */
    public function scopeOverdue($query)
    {
        return $query->where('payment_method', 'kredit')
            ->where('payment_status', '!=', 'LUNAS')
            ->whereDate('due_date', '<', now()->startOfDay());
    }

    /**
     * Scope untuk filter berdasarkan member
     */
    public function scopeForMember($query, $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    /**
     * Scope untuk filter transaksi yang perlu dikirim
     */
    public function scopeNeedDelivery($query)
    {
        return $query->where('need_delivery', true);
    }

    /**
     * Scope untuk filter transaksi yang sudah memiliki delivery
     */
    public function scopeHasDelivery($query)
    {
        return $query->whereHas('delivery');
    }

    /**
     * Scope untuk filter transaksi yang belum memiliki delivery
     */
    public function scopeWithoutDelivery($query)
    {
        return $query->whereDoesntHave('delivery');
    }

    // ===== BOOT METHOD =====

    /**
     * Boot method untuk handle events
     */
    protected static function boot()
    {
        parent::boot();

        // Saat membuat transaksi baru
        static::creating(function ($transaction) {
            // Jika transaksi kredit, set payment_status ke BELUM LUNAS
            if ($transaction->payment_method === 'kredit') {
                $transaction->payment_status = 'BELUM LUNAS';
            } else {
                $transaction->payment_status = 'LUNAS';
            }
        });

        // Saat transaksi diupdate
        static::updating(function ($transaction) {
            if ($transaction->isDirty('payment_status') && $transaction->payment_status === 'LUNAS') {
            }
        });

        // Saat transaksi dihapus
        static::deleting(function ($transaction) {
            if ($transaction->delivery) {
                $transaction->delivery->delete();
            }
        });
    }
}
