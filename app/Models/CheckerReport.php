<?php
// app/Models/CheckerReport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckerReport extends Model
{
    protected $table = 'checker_reports';

    protected $fillable = [
        'product_id',
        'reported_by',
        'resolved_by',
        'report_type',
        'notes',
        'quantity',
        'status',
        'reported_at',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function getStatusBadgeClassAttribute()
    {
        return [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'resolved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
        ][$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getReportTypeLabelAttribute()
    {
        return [
            'low_stock' => 'Stok Rendah',
            'expiring' => 'Akan Kadaluarsa',
            'expired' => 'Sudah Kadaluarsa',
            'damaged' => 'Produk Rusak',
            'other' => 'Lainnya',
        ][$this->report_type] ?? $this->report_type;
    }
}