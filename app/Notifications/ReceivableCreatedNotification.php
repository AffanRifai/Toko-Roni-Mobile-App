<?php
// app/Notifications/ReceivableCreatedNotification.php

namespace App\Notifications;

use App\Models\Receivable;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReceivableCreatedNotification extends Notification
{
    use Queueable;

    protected $receivable;
    protected $transaction;
    protected $createdBy;

    public function __construct(Receivable $receivable, Transaction $transaction, User $createdBy)
    {
        $this->receivable = $receivable;
        $this->transaction = $transaction;
        $this->createdBy = $createdBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'receivable_created',
            'receivable_id' => $this->receivable->id,
            'no_piutang' => $this->receivable->no_piutang,
            'invoice_number' => $this->transaction->invoice_number,
            'member_name' => $this->transaction->member->nama ?? '-',
            'customer_name' => $this->transaction->customer_name,
            'total_piutang' => $this->receivable->total_piutang,
            'jatuh_tempo' => $this->receivable->jatuh_tempo?->format('d/m/Y'),
            'created_by' => $this->createdBy->name,
            'created_by_id' => $this->createdBy->id,
            'message' => 'Piutang baru ' . $this->receivable->no_piutang . ' sebesar Rp ' . number_format($this->receivable->total_piutang, 0, ',', '.') . ' atas nama ' . ($this->transaction->member->nama ?? $this->transaction->customer_name),
            'icon' => 'fa-solid fa-hand-holding-dollar',
            'color' => 'purple',
            'time' => now()->toDateTimeString()
        ];
    }
}