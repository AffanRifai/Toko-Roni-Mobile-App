<?php
// app/Notifications/ReportResolvedNotification.php

namespace App\Notifications;

use App\Models\CheckerReport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportResolvedNotification extends Notification
{
    use Queueable;

    protected $report;
    protected $resolvedBy;

    public function __construct(CheckerReport $report, User $resolvedBy)
    {
        $this->report = $report;
        $this->resolvedBy = $resolvedBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $typeLabels = [
            'low_stock' => 'Stok Rendah',
            'expiring' => 'Akan Kadaluarsa',
            'expired' => 'Sudah Kadaluarsa',
            'damaged' => 'Produk Rusak',
            'other' => 'Lainnya',
        ];

        return [
            'type' => 'report_resolved',
            'report_id' => $this->report->id,
            'product_name' => $this->report->product->name,
            'report_type_label' => $typeLabels[$this->report->report_type] ?? $this->report->report_type,
            'resolved_by' => $this->resolvedBy->name,
            'resolved_by_id' => $this->resolvedBy->id,
            'resolution_notes' => $this->report->resolution_notes,
            'message' => 'Laporan ' . ($typeLabels[$this->report->report_type] ?? $this->report->report_type) . ' untuk produk ' . $this->report->product->name . ' telah diselesaikan oleh ' . $this->resolvedBy->name,
            'icon' => 'fa-solid fa-check-circle',
            'color' => 'green',
            'time' => now()->toDateTimeString()
        ];
    }
}