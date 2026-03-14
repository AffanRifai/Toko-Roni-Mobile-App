<?php
// app/Notifications/TransactionCreatedNotification.php

namespace App\Notifications;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransactionCreatedNotification extends Notification
{
    use Queueable;

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
        
        return [
            'type' => 'transaction_created',
            'transaction_id' => $this->transaction->id,
            'invoice_number' => $this->transaction->invoice_number,
            'customer_name' => $this->transaction->customer_name,
            'total_amount' => $this->transaction->total_amount,
            'total_items' => $totalItems,
            'payment_method' => $paymentMethodLabels[$this->transaction->payment_method] ?? $this->transaction->payment_method,
            'payment_status' => $this->transaction->payment_status,
            'created_by' => $this->createdBy->name,
            'created_by_id' => $this->createdBy->id,
            'message' => 'Transaksi baru ' . $this->transaction->invoice_number . ' sebesar Rp ' . number_format($this->transaction->total_amount, 0, ',', '.') . ' oleh ' . $this->createdBy->name,
            'icon' => 'fa-solid fa-cash-register',
            'color' => 'green',
            'time' => now()->toDateTimeString()
        ];
    }
}