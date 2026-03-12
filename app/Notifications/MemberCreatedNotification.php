<?php
// app/Notifications/MemberCreatedNotification.php

namespace App\Notifications;

use App\Models\Member;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MemberCreatedNotification extends Notification
{
    use Queueable;

    protected $member;
    protected $createdBy;

    public function __construct(Member $member, User $createdBy)
    {
        $this->member = $member;
        $this->createdBy = $createdBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'member_created',
            'member_id' => $this->member->id,
            'member_name' => $this->member->nama,
            'member_code' => $this->member->kode_member,
            'member_tipe' => $this->member->tipe_member,
            'created_by' => $this->createdBy->name,
            'created_by_id' => $this->createdBy->id,
            'message' => 'Member baru ' . $this->member->nama . ' (' . $this->member->kode_member . ') telah ditambahkan sebagai member ' . ucfirst($this->member->tipe_member),
            'icon' => 'fa-solid fa-user-plus',
            'color' => 'green',
            'time' => now()->toDateTimeString()
        ];
    }
}