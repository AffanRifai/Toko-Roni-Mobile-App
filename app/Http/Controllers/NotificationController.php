<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(20);
        
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi ditandai sebagai dibaca');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi ditandai sebagai dibaca');
    }

    /**
 * Delete a specific notification.
 */
public function destroy($id)
{
    /** @var \App\Models\User $user */
    $user = Auth::user();
    $notification = $user->notifications()->findOrFail($id);
    $notification->delete();

    return back()->with('success', 'Notifikasi berhasil dihapus');
}

/**
 * Delete all notifications.
 */
public function clearAll()
{
    /** @var \App\Models\User $user */
    $user = Auth::user();
    $user->notifications()->delete();
    
    return back()->with('success', 'Semua notifikasi berhasil dihapus');
}

    /**
     * Get unread notifications count (for AJAX polling).
     */
    public function getUnreadCount()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $count = $user->unreadNotifications->count();
        
        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Get recent notifications (for AJAX polling).
     */
    public function getRecent()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->data['message'] ?? 'Notifikasi baru',
                    'type' => $notification->data['type'] ?? 'default',
                    'time' => $notification->created_at->diffForHumans(),
                    'read_at' => $notification->read_at,
                    'data' => $notification->data
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $user->unreadNotifications->count()
        ]);
    }
}