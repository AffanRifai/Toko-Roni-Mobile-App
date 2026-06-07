<?php
// app/Notifications/TransactionDeletedNotification.php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransactionDeletedNotification extends Notification
{
    use Queueable;

    protected $invoiceNumber;
    protected $deletedBy;
    protected $meta;

    public function __construct(string $invoiceNumber, User $deletedBy, array $meta = [])
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->deletedBy = $deletedBy;
        $this->meta = $meta;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return array_merge([
            'type' => 'transaction_deleted',
            'invoice_number' => $this->invoiceNumber,
            'deleted_by' => $this->deletedBy->name,
            'deleted_by_id' => $this->deletedBy->id,
            'message' => 'Transaksi ' . $this->invoiceNumber . ' telah dihapus oleh ' . $this->deletedBy->name,
            'icon' => 'fa-solid fa-trash',
            'color' => 'red',
            'time' => now()->toDateTimeString()
        ], $this->meta);
    }
}
