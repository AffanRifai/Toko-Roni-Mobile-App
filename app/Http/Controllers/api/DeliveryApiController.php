<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeliveryApiController extends Controller
{
    /**
     * Get today's delivery statistics
     */
    public function todayStats()
    {
        try {
            $today = now()->format('Y-m-d');
            $stats = [
                'total' => Delivery::whereDate('created_at', $today)->count(),
                'pending' => Delivery::whereDate('created_at', $today)->where('status', 'pending')->count(),
                'delivered' => Delivery::whereDate('created_at', $today)->where('status', 'delivered')->count(),
            ];
            return response()->json(['success' => true, 'data' => $stats], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Get my deliveries
     */
    public function myDeliveries()
    {
        try {
            $deliveries = Delivery::where('user_id', auth()->id())->latest()->get();
            return response()->json(['success' => true, 'data' => $deliveries], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Get recent deliveries
     */
    public function recent(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $deliveries = Delivery::with(['user', 'vehicle', 'transaction'])->latest()->limit($limit)->get();
            return response()->json(['success' => true, 'data' => $deliveries], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Display a listing of deliveries.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $deliveries = Delivery::with(['user', 'vehicle', 'transaction'])->latest()->paginate($perPage);
            return response()->json(['success' => true, 'data' => $deliveries], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Get statistics
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total' => Delivery::count(),
                'delivered' => Delivery::where('status', 'delivered')->count(),
                'pending' => Delivery::where('status', 'pending')->count(),
            ];
            return response()->json(['success' => true, 'data' => $stats], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Show delivery
     */
    public function show($id)
    {
        try {
            $delivery = Delivery::with(['user', 'vehicle', 'transaction'])->findOrFail($id);
            return response()->json(['success' => true, 'data' => $delivery], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
    }

    /**
     * Update delivery
     */
    public function update(Request $request, $id)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }

    /**
     * Assign delivery
     */
    public function assign(Request $request, $id)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        try {
            $delivery = Delivery::findOrFail($id);
            $delivery->update(['user_id' => $request->user_id, 'status' => 'assigned']);
            return response()->json(['success' => true, 'message' => 'Assigned'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Update status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required']);
        try {
            $delivery = Delivery::findOrFail($id);
            $delivery->update(['status' => $request->status]);
            return response()->json(['success' => true, 'message' => 'Status updated'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Available drivers
     */
    public function availableDrivers()
    {
        $drivers = User::where('role', 'kurir')->get();
        return response()->json(['success' => true, 'data' => $drivers], 200);
    }

    /**
     * Available vehicles
     */
    public function availableVehicles()
    {
        $vehicles = Vehicle::where('status', 'available')->get();
        return response()->json(['success' => true, 'data' => $vehicles], 200);
    }

    /**
     * Track delivery
     */
    public function trackDelivery($code)
    {
        try {
            $delivery = Delivery::where('delivery_code', $code)->with(['user', 'vehicle'])->firstOrFail();
            return response()->json(['success' => true, 'data' => $delivery], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
    }
}
