<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;

class NotificationApiController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $perPage = max(1, min((int) $request->get('per_page', 20), 100));
            $notifications = $user->notifications()->latest()->paginate($perPage);

            $items = $notifications->getCollection()
                ->map(fn (DatabaseNotification $n) => $this->formatNotification($n))
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Notifications retrieved successfully',
                'data' => $items,
                'meta' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
                'unread_count' => $user->unreadNotifications()->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Notification index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notifications.
     */
    public function unread()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $notifications = $user->unreadNotifications()
                ->latest()
                ->get()
                ->map(fn (DatabaseNotification $n) => $this->formatNotification($n))
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Unread notifications retrieved successfully',
                'data' => $notifications,
                'count' => $notifications->count(),
                'unread_count' => $notifications->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Notification unread error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve unread notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead($id)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $notification = $user->notifications()->findOrFail($id);
            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'unread_count' => $user->unreadNotifications()->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Notification markAsRead error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $user->unreadNotifications->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'unread_count' => 0,
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Notification markAllAsRead error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a specific notification.
     */
    public function destroy($id)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $notification = $user->notifications()->findOrFail($id);
            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully',
                'unread_count' => $user->unreadNotifications()->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Notification destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete all notifications.
     */
    public function clearAll()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $deleted = $user->notifications()->count();
            $user->notifications()->delete();

            return response()->json([
                'success' => true,
                'message' => 'All notifications cleared successfully',
                'deleted_count' => $deleted,
                'unread_count' => 0,
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Notification clearAll error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
}
