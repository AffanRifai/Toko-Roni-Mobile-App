<?php
// app/Notifications/TransactionCreatedNotification.php

namespace App\Notifications;

use App\Models\Transaction;
use App\Models\User;
use App\Traits\InAppNotificationTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransactionCreatedNotification extends Notification
{
    use Queueable, InAppNotificationTrait;

    protected $transaction;
    protected $items;
    protected $createdBy;

    public function __construct(Transaction $transaction, array $items, User $createdBy)
    {
        $this->transaction = $transaction;
        $this->items = $items;
        $this->createdBy = $createdBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $totalItems = collect($this->items)->sum('qty');
        $paymentMethodLabels = [
            'cash' => 'Tunai',
            'debit_card' => 'Debit',
            'credit_card' => 'Kredit',
            'e_wallet' => 'E-Wallet',
            'transfer' => 'Transfer'
        ];
        
        return $this->getInAppPayload(
            'transaction_created',
            'Transaksi baru ' . $this->transaction->invoice_number . ' sebesar Rp ' . number_format($this->transaction->total_amount, 0, ',', '.') . ' oleh ' . $this->createdBy->name,
            'fas fa-cash-register',
            'green',
            route('transactions.index'), // URL ke riwayat transaksi
            [
                'transaction_id' => $this->transaction->id,
                'invoice_number' => $this->transaction->invoice_number,
                'customer_name' => $this->transaction->customer_name,
                'total_amount' => $this->transaction->total_amount,
                'total_items' => $totalItems,
                'payment_method' => $paymentMethodLabels[$this->transaction->payment_method] ?? $this->transaction->payment_method,
                'payment_status' => $this->transaction->payment_status,
                'created_by' => $this->createdBy->name,
                'created_by_id' => $this->createdBy->id,
            ]
        );
    }
}