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
            $perPage = $request->get('per_page', 20);
            $notifications = $user->notifications()->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'message' => 'Notifications retrieved successfully',
                'data' => $notifications
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
            $notifications = $user->unreadNotifications()->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Unread notifications retrieved successfully',
                'data' => $notifications,
                'count' => $notifications->count()
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
                'message' => 'Notification marked as read'
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
                'message' => 'All notifications marked as read'
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
                'message' => 'Notification deleted successfully'
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
            $user->notifications()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'All notifications cleared successfully'
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
}
