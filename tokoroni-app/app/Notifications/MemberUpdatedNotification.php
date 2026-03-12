<?php
// app/Notifications/MemberUpdatedNotification.php

namespace App\Notifications;

use App\Models\Member;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MemberUpdatedNotification extends Notification
{
    use Queueable;

    protected $member;
    protected $updatedBy;
    protected $changes;

    public function __construct(Member $member, User $updatedBy, array $changes)
    {
        $this->member = $member;
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
                'nama' => 'Nama',
                'email' => 'Email',
                'no_telepon' => 'No. Telepon',
                'alamat' => 'Alamat',
                'tipe_member' => 'Tipe Member',
                'limit_kredit' => 'Limit Kredit',
                'is_active' => 'Status',
                default => ucfirst(str_replace('_', ' ', $field))
            };
            $changedFields[] = $fieldName;
        }

        return [
            'type' => 'member_updated',
            'member_id' => $this->member->id,
            'member_name' => $this->member->nama,
            'member_code' => $this->member->kode_member,
            'member_tipe' => $this->member->tipe_member,
            'updated_by' => $this->updatedBy->name,
            'updated_by_id' => $this->updatedBy->id,
            'changed_fields' => $changedFields,
            'message' => 'Data member ' . $this->member->nama . ' (' . $this->member->kode_member . ') telah diperbarui oleh ' . $this->updatedBy->name,
            'icon' => 'fa-solid fa-user-pen',
            'color' => 'blue',
            'time' => now()->toDateTimeString()
        ];
    }
}