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
        $amount = $this->payment->jumlah_bayar; // PASTIKAN INI TERISI

        // Format nominal untuk tampilan
        $formattedAmount = 'Rp ' . number_format($amount, 0, ',', '.');
        $formattedRemaining = 'Rp ' . number_format($remaining, 0, ',', '.');

        // Tentukan status lunas atau belum
        $isLunas = $remaining <= 0;
        $lunasText = $isLunas ? ' (Lunas)' : '';

        return [
            'type' => 'payment_received',
            'receivable_id' => $this->receivable->id,
            'no_piutang' => $this->receivable->no_piutang,
            'payment_id' => $this->payment->id,

            // PASTIKAN NILAI INI TERKIRIM
            'amount' => $amount,
            'amount_formatted' => $formattedAmount,
            'remaining' => $remaining,
            'remaining_formatted' => $formattedRemaining,

            'is_lunas' => $isLunas,
            'received_by' => $this->receivedBy->name,
            'received_by_id' => $this->receivedBy->id,

            // Pesan dengan nominal yang jelas
            'message' => 'Pembayaran piutang ' . $this->receivable->no_piutang .
                ' sebesar ' . $formattedAmount .
                ' diterima oleh ' . $this->receivedBy->name .
                $lunasText,

            // Tambahkan informasi member
            'member_name' => $this->receivable->member->nama ?? 'Unknown',
            'member_code' => $this->receivable->member->kode_member ?? '-',

            'icon' => $isLunas ? 'fa-solid fa-check-circle' : 'fa-solid fa-money-bill-transfer',
            'color' => $isLunas ? 'green' : 'blue',
            'time' => now()->toDateTimeString()
        ];
    }
}
