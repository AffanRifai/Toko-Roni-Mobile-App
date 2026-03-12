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

    public function __construct(string $invoiceNumber, User $deletedBy)
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->deletedBy = $deletedBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'transaction_deleted',
            'invoice_number' => $this->invoiceNumber,
            'deleted_by' => $this->deletedBy->name,
            'deleted_by_id' => $this->deletedBy->id,
            'message' => 'Transaksi ' . $this->invoiceNumber . ' telah dihapus oleh ' . $this->deletedBy->name,
            'icon' => 'fa-solid fa-trash',
            'color' => 'red',
            'time' => now()->toDateTimeString()
        ];
    }
}