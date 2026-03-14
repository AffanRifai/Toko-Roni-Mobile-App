<?php
// app/Models/ReceivablePayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivablePayment extends Model
{
    protected $table = 'receivable_payments';

    protected $fillable = [
        'receivable_id',
        'tanggal_bayar',
        'jumlah_bayar',
        'metode_bayar',
        'keterangan',
        'kasir_id'
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'jumlah_bayar' => 'decimal:2'
    ];

    /**
     * Relasi ke piutang
     */
    public function receivable()
    {
        return $this->belongsTo(Receivable::class);
    }

    /**
     * Relasi ke kasir
     */
    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    /**
     * Format tanggal bayar
     */
    public function getTanggalBayarFormattedAttribute()
    {
        return $this->tanggal_bayar->format('d/m/Y');
    }

    /**
     * Format jumlah bayar
     */
    public function getJumlahBayarFormattedAttribute()
    {
        return 'Rp ' . number_format($this->jumlah_bayar, 0, ',', '.');
    }
}
