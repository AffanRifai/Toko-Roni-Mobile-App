<?php
// app/Models/Member.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'kode_member',
        'nama',
        'email',
        'no_telepon',
        'alamat',
        'tipe_member',
        'limit_kredit',
        'total_piutang',
        'is_active',
        'tanggal_registrasi' // Tambahkan ini
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'limit_kredit' => 'decimal:2',
        'total_piutang' => 'decimal:2',
        'tanggal_registrasi' => 'date', // Cast ke date
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relasi ke piutang
    public function receivables()
    {
        return $this->hasMany(Receivable::class);
    }

    // Relasi ke transaksi
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Scope untuk member aktif
    public function scopeActive($query)
    {
        return $this->where('is_active', true);
    }

    // Generate kode member otomatis
    public static function generateKodeMember()
    {
        $lastMember = self::orderBy('id', 'desc')->first();
        $number = $lastMember ? intval(substr($lastMember->kode_member, -4)) + 1 : 1;
        return 'MBR' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // Boot method untuk set default values
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($member) {
            if (empty($member->tanggal_registrasi)) {
                $member->tanggal_registrasi = now();
            }
            if (empty($member->kode_member)) {
                $member->kode_member = self::generateKodeMember();
            }
        });
    }
}
