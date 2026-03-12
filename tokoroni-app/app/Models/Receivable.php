<?php
// app/Models/Receivable.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receivable extends Model
{
    protected $table = 'receivables';

    protected $fillable = [
        'no_piutang',
        'member_id',
        'transaction_id',
        'invoice_number',
        'tanggal_transaksi',
        'total_piutang',
        'sisa_piutang',
        'jatuh_tempo',
        'status',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'jatuh_tempo' => 'date',
        'total_piutang' => 'decimal:2',
        'sisa_piutang' => 'decimal:2'
    ];

    /**
     * Relasi ke member
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Relasi ke transaksi
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Relasi ke pembayaran piutang
     */
    public function payments()
    {
        return $this->hasMany(ReceivablePayment::class, 'receivable_id', 'id');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        return $this->status === 'LUNAS'
            ? 'bg-green-100 text-green-800'
            : 'bg-yellow-100 text-yellow-800';
    }

    /**
     * Cek apakah sudah lunas
     */
    public function isLunas()
    {
        return $this->status === 'LUNAS';
    }

    /**
     * Cek apakah jatuh tempo
     */
    public function isJatuhTempo()
    {
        if ($this->isLunas()) {
            return false;
        }

        return $this->jatuh_tempo && now()->startOfDay()->gt($this->jatuh_tempo);
    }
}
