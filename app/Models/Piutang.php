<?php
// app/Models/Receivable.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receivable extends Model
{
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

    // Relasi ke member
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    // Relasi ke transaksi
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Relasi ke pembayaran
    public function payments()
    {
        return $this->hasMany(ReceivablePayment::class);
    }

    // Generate nomor piutang otomatis
    public static function generateNoPiutang()
    {
        $date = now()->format('Ymd');
        $last = self::whereDate('created_at', today())->count() + 1;
        return 'PTG' . $date . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}
