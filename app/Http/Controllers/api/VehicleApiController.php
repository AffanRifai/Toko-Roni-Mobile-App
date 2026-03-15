<?php
// app/Http/Controllers/Api/VehicleApiController.php
// Add these methods to your existing VehicleController

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    /**
     * Get available vehicles
     */
    public function getAvailableVehicles(Request $request)
    {
        try {
            $type = $request->get('type', 'all');

            $query = Vehicle::where('status', 'available');

            if ($type !== 'all') {
                $query->where('type', $type);
            }

            $vehicles = $query->get(['id', 'name', 'license_plate', 'type', 'brand', 'year']);

            return response()->json([
                'success' => true,
                'message' => 'Available vehicles retrieved successfully',
                'data' => $vehicles
            ], 200);
        } catch (\Exception $e) {
            Log::error('Available vehicles error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get available vehicles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get vehicle statistics
     */
    public function getStatistics(Request $request)
    {
        try {
            $stats = [
                'total' => Vehicle::count(),
                'available' => Vehicle::where('status', 'available')->count(),
                'in_use' => Vehicle::where('status', 'in_use')->count(),
                'maintenance' => Vehicle::where('status', 'maintenance')->count(),
                'by_type' => [
                    'motor' => Vehicle::where('type', 'motor')->count(),
                    'mobil' => Vehicle::where('type', 'mobil')->count(),
                    'truck' => Vehicle::where('type', 'truck')->count(),
                ],
                'usage_percentage' => Vehicle::count() > 0
                    ? round((Vehicle::where('status', 'in_use')->count() / Vehicle::count()) * 100, 2)
                    : 0,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Vehicle statistics retrieved successfully',
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            Log::error('Vehicle statistics error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get vehicle statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick update vehicle
     */
    public function quickUpdate(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:available,in_use,maintenance',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $oldStatus = $vehicle->status;
            $vehicle->update([
                'status' => $request->status,
                'notes' => $request->notes
            ]);

            Log::info('Vehicle quick updated:', [
                'vehicle_id' => $vehicle->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status kendaraan berhasil diperbarui',
                'data' => [
                    'id' => $vehicle->id,
                    'name' => $vehicle->name,
                    'license_plate' => $vehicle->license_plate,
                    'status' => $vehicle->status,
                    'old_status' => $oldStatus
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Vehicle quick update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status kendaraan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update vehicles
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_ids' => 'required|array|min:1',
            'vehicle_ids.*' => 'exists:vehicles,id',
            'status' => 'required|in:available,in_use,maintenance',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $vehicles = Vehicle::whereIn('id', $request->vehicle_ids)->get();
            $updatedCount = 0;
            $failedIds = [];

            foreach ($vehicles as $vehicle) {
                try {
                    $vehicle->update(['status' => $request->status]);
                    $updatedCount++;
                } catch (\Exception $e) {
                    $failedIds[] = $vehicle->id;
                    Log::error('Bulk update failed for vehicle ' . $vehicle->id . ': ' . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil memperbarui {$updatedCount} kendaraan",
                'data' => [
                    'updated_count' => $updatedCount,
                    'failed_ids' => $failedIds,
                    'new_status' => $request->status
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan bulk update',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
