<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Queue\SerializesModels;

class RealtimeNotificationCreated implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    /**
     * @var array<string, mixed>
     */
    public array $notification;

    public int|string $userId;
    public int $unreadCount = 0;

    public function __construct(DatabaseNotification $notification)
    {
        $this->userId = $notification->notifiable_id;
        $this->notification = $this->formatNotification($notification);
        $this->unreadCount = $this->resolveUnreadCount();
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("App.Models.User.{$this->userId}")];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'notification' => $this->notification,
            'unread_count' => $this->unreadCount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatNotification(DatabaseNotification $notification): array
    {
        $payload = is_array($notification->data) ? $notification->data : [];
        $type = $this->resolveType($notification, $payload);

        $title = $payload['title'] ?? $this->defaultTitle($type);
        $message = $payload['message'] ?? $payload['body'] ?? 'Notifikasi baru';
        $priority = $this->resolvePriority($payload, $type);
        $isImportant = $this->isImportantPriority($priority);

        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'type_group' => $type,
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'is_important' => $isImportant,
            'icon' => $payload['icon'] ?? 'fas fa-bell',
            'color' => $payload['color'] ?? 'blue',
            'url' => $payload['url'] ?? null,
            'is_read' => !is_null($notification->read_at),
            'read_at' => optional($notification->read_at)?->toISOString(),
            'created_at' => optional($notification->created_at)?->toISOString(),
            'time_ago' => optional($notification->created_at)?->diffForHumans(),
            'data' => $payload,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveType(DatabaseNotification $notification, array $payload): string
    {
        $raw = strtolower((string) ($payload['type'] ?? ''));
        $class = strtolower((string) $notification->type);
        $source = trim($raw) !== '' ? $raw : $class;

        if (str_contains($source, 'stock') || str_contains($source, 'low_stock') || str_contains($source, 'out_of_stock')) {
            return 'stock';
        }
        if (str_contains($source, 'expiry') || str_contains($source, 'expir') || str_contains($source, 'kadaluarsa')) {
            return 'expiry';
        }
        if (str_contains($source, 'transaction')) {
            return 'transaction';
        }
        if (str_contains($source, 'product')) {
            return 'product';
        }
        if (str_contains($source, 'member')) {
            return 'member';
        }
        if (str_contains($source, 'user') || in_array($source, ['create', 'update'], true)) {
            return 'user';
        }
        if (str_contains($source, 'delivery') || str_contains($source, 'pengiriman')) {
            return 'delivery';
        }
        if (str_contains($source, 'vehicle') || str_contains($source, 'kendaraan')) {
            return 'vehicle';
        }
        if (str_contains($source, 'receivable') || str_contains($source, 'piutang')) {
            return 'receivable';
        }
        if (str_contains($source, 'payment')) {
            return 'payment';
        }
        if (str_contains($source, 'category') || str_contains($source, 'kategori')) {
            return 'category';
        }
        if (str_contains($source, 'report')) {
            return 'report';
        }

        return 'default';
    }

    private function defaultTitle(string $type): string
    {
        return match ($type) {
            'transaction' => 'Transaksi',
            'product' => 'Produk',
            'stock' => 'Stok Produk',
            'expiry' => 'Masa Kadaluarsa',
            'member' => 'Member',
            'user' => 'Pengguna',
            'delivery' => 'Pengiriman',
            'vehicle' => 'Kendaraan',
            'receivable' => 'Piutang',
            'payment' => 'Pembayaran',
            'category' => 'Kategori',
            'report' => 'Laporan',
            default => 'Notifikasi',
        };
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function resolvePriority(array $payload, string $type): string
    {
        $raw = strtolower(trim((string) ($payload['priority'] ?? '')));
        if (in_array($raw, ['low', 'normal', 'high', 'critical'], true)) {
            return $raw;
        }

        $payloadType = strtolower((string) ($payload['type'] ?? ''));
        if (str_contains($payloadType, 'out_of_stock')) {
            return 'critical';
        }

        if (in_array($type, ['report', 'receivable', 'payment', 'stock', 'expiry'], true)) {
            return 'high';
        }

        return 'normal';
    }

    private function isImportantPriority(string $priority): bool
    {
        return in_array(strtolower(trim($priority)), ['high', 'critical'], true);
    }

    private function resolveUnreadCount(): int
    {
        if (empty($this->userId)) {
            return 0;
        }

        $user = User::find($this->userId);
        if (!$user) {
            return 0;
        }

        return (int) $user->unreadNotifications()->count();
    }
}
