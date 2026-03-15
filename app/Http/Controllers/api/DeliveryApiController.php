<?php
// app/Http/Controllers/Api/DeliveryApiController.php
// Add these methods to your existing DeliveryController

namespace App\Http\Controllers;

use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeliveryController extends Controller
{
    /**
     * Get today's delivery statistics
     */
    public function getTodayStats(Request $request)
    {
        try {
            $today = now()->format('Y-m-d');

            $stats = [
                'total' => Delivery::whereDate('created_at', $today)->count(),
                'pending' => Delivery::whereDate('created_at', $today)->where('status', 'pending')->count(),
                'assigned' => Delivery::whereDate('created_at', $today)->where('status', 'assigned')->count(),
                'picked_up' => Delivery::whereDate('created_at', $today)->where('status', 'picked_up')->count(),
                'on_delivery' => Delivery::whereDate('created_at', $today)->where('status', 'on_delivery')->count(),
                'delivered' => Delivery::whereDate('created_at', $today)->where('status', 'delivered')->count(),
                'delivered_today' => Delivery::whereDate('delivered_at', $today)->count(),
                'failed' => Delivery::whereDate('created_at', $today)->where('status', 'failed')->count(),
                'cancelled' => Delivery::whereDate('created_at', $today)->where('status', 'cancelled')->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Today delivery stats retrieved successfully',
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            Log::error('Delivery today stats error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get delivery stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent deliveries
     */
    public function getRecentDeliveries(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);

            $deliveries = Delivery::with(['user', 'vehicle', 'transaction'])
                ->latest()
                ->limit($limit)
                ->get()
                ->map(function ($delivery) {
                    return [
                        'id' => $delivery->id,
                        'delivery_code' => $delivery->delivery_code,
                        'origin' => $delivery->origin,
                        'destination' => $delivery->destination,
                        'status' => $delivery->status,
                        'status_badge' => $delivery->getStatusBadgeClass(),
                        'status_icon' => $delivery->getStatusIcon(),
                        'driver' => $delivery->user->name ?? 'Not assigned',
                        'vehicle' => $delivery->vehicle->name ?? 'Not assigned',
                        'total_items' => $delivery->total_items,
                        'estimated_time' => $delivery->estimated_delivery_time ? $delivery->estimated_delivery_time->format('d/m/Y H:i') : null,
                        'created_at' => $delivery->created_at->format('d/m/Y H:i'),
                        'transaction_invoice' => $delivery->transaction->invoice_number ?? null,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Recent deliveries retrieved successfully',
                'data' => $deliveries
            ], 200);
        } catch (\Exception $e) {
            Log::error('Recent deliveries error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get recent deliveries',
                'error' => $e->getMessage()
            ], 500);aaaa
        }
    }
}
