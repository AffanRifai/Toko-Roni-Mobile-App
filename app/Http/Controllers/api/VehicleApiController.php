<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleApiController extends Controller
{
    /**
     * Display a listing of vehicles.
     */
    public function index()
    {
        try {
            $vehicles = Vehicle::all();
            return response()->json(['success' => true, 'data' => $vehicles], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Get available vehicles
     */
    public function available()
    {
        try {
            $vehicles = Vehicle::where('status', 'available')->get();
            return response()->json(['success' => true, 'data' => $vehicles], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Get vehicle statistics
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total' => Vehicle::count(),
                'available' => Vehicle::where('status', 'available')->count(),
                'maintenance' => Vehicle::where('status', 'maintenance')->count(),
            ];
            return response()->json(['success' => true, 'data' => $stats], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Show vehicle
     */
    public function show($id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            return response()->json(['success' => true, 'data' => $vehicle], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
    }

    /**
     * Maintenance
     */
    public function setMaintenance($id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->update(['status' => 'maintenance']);
            return response()->json(['success' => true, 'message' => 'Maintenance status set'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Set available
     */
    public function setAvailable($id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->update(['status' => 'available']);
            return response()->json(['success' => true, 'message' => 'Available status set'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }
}
