<?php
// app/Notifications/PaymentReceivedNotification.php

namespace App\Notifications;

use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification
{
    use Queueable;

    protected $receivable;
    protected $payment;
    protected $receivedBy;

    public function __construct(Receivable $receivable, ReceivablePayment $payment, User $receivedBy)
    {
        $this->receivable = $receivable;
        $this->payment = $payment;
        $this->receivedBy = $receivedBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $remaining = $this->receivable->sisa_piutang;
        
        return [
            'type' => 'payment_received',
            'receivable_id' => $this->receivable->id,
            'no_piutang' => $this->receivable->no_piutang,
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'remaining' => $remaining,
            'is_lunas' => $remaining <= 0,
            'received_by' => $this->receivedBy->name,
            'received_by_id' => $this->receivedBy->id,
            'message' => 'Pembayaran piutang ' . $this->receivable->no_piutang . ' sebesar Rp ' . number_format($this->payment->amount, 0, ',', '.') . ' diterima oleh ' . $this->receivedBy->name . ($remaining <= 0 ? ' (Lunas)' : ''),
            'icon' => 'fa-solid fa-money-bill-transfer',
            'color' => 'green',
            'time' => now()->toDateTimeString()
        ];
    }
}