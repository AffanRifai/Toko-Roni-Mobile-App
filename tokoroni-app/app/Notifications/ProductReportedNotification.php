<?php
// app/Notifications/ProductReportedNotification.php

namespace App\Notifications;

use App\Models\CheckerReport;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductReportedNotification extends Notification
{
    use Queueable;

    protected $report;
    protected $product;
    protected $reportedBy;

    public function __construct(CheckerReport $report, Product $product, User $reportedBy)
    {
        $this->report = $report;
        $this->product = $product;
        $this->reportedBy = $reportedBy;
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
            'type' => 'product_reported',
            'report_id' => $this->report->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_code' => $this->product->code,
            'report_type' => $this->report->report_type,
            'report_type_label' => $typeLabels[$this->report->report_type] ?? $this->report->report_type,
            'notes' => $this->report->notes,
            'quantity' => $this->report->quantity,
            'reported_by' => $this->reportedBy->name,
            'reported_by_id' => $this->reportedBy->id,
            'message' => 'Laporan produk: ' . $this->product->name . ' (' . ($typeLabels[$this->report->report_type] ?? $this->report->report_type) . ') oleh ' . $this->reportedBy->name,
            'icon' => 'fa-solid fa-flag',
            'color' => 'orange',
            'time' => now()->toDateTimeString()
        ];
    }
}