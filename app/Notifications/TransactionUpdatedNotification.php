<?php
// app/Notifications/TransactionUpdatedNotification.php

namespace App\Notifications;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransactionUpdatedNotification extends Notification
{
    use Queueable;

    protected $transaction;
    protected $updatedBy;
    protected $changes;

    public function __construct(Transaction $transaction, User $updatedBy, array $changes)
    {
        $this->transaction = $transaction;
        $this->updatedBy = $updatedBy;
        $this->changes = $changes;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $changedFields = [];
        foreach ($this->changes as $field => $value) {
            $fieldName = match($field) {
                'customer_name' => 'Nama Pelanggan',
                'customer_phone' => 'No. Telepon',
                'notes' => 'Catatan',
                default => ucfirst(str_replace('_', ' ', $field))
            };
            $changedFields[] = $fieldName;
        }

        return [
            'type' => 'transaction_updated',
            'transaction_id' => $this->transaction->id,
            'invoice_number' => $this->transaction->invoice_number,
            'updated_by' => $this->updatedBy->name,
            'updated_by_id' => $this->updatedBy->id,
            'changed_fields' => $changedFields,
            'message' => 'Transaksi ' . $this->transaction->invoice_number . ' telah diperbarui oleh ' . $this->updatedBy->name,
            'icon' => 'fa-solid fa-cash-register',
            'color' => 'yellow',
            'time' => now()->toDateTimeString()
        ];
    }
}