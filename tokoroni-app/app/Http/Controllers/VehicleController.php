<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\User;
use App\Notifications\VehicleCreatedNotification;
use App\Notifications\VehicleUpdatedNotification;
use App\Notifications\VehicleDeletedNotification;
use App\Notifications\VehicleStatusChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    /**
     * Display a listing of vehicles.
     */
    public function index(Request $request)
    {
        $query = Vehicle::query();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Search by name or plate number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('license_plate', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => Vehicle::count(),
            'available' => Vehicle::where('status', 'available')->count(),
            'in_use' => Vehicle::where('status', 'in_use')->count(),
            'maintenance' => Vehicle::where('status', 'maintenance')->count(),
        ];

        return view('vehicles.index', compact('vehicles', 'stats'));
    }

    /**
     * Show the form for creating a new vehicle.
     */
    public function create()
    {
        return view('vehicles.create');
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request)
    {
        // Validasi dengan field yang benar
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate',
            'type' => 'required|in:motor,mobil,truck',
            'capacity_weight' => 'nullable|numeric|min:0',
            'capacity_volume' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,in_use,maintenance',
            'last_maintenance' => 'nullable|date',
            'notes' => 'nullable|string',
        ], [
            'license_plate.unique' => 'Plat nomor sudah terdaftar',
            'name.required' => 'Nama kendaraan wajib diisi',
            'license_plate.required' => 'Plat nomor wajib diisi',
            'type.required' => 'Jenis kendaraan wajib dipilih',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Create vehicle
            $vehicle = Vehicle::create([
                'name' => $request->name,
                'license_plate' => strtoupper($request->license_plate),
                'type' => $request->type,
                'capacity_weight' => $request->capacity_weight ?? 0,
                'capacity_volume' => $request->capacity_volume ?? 0,
                'status' => $request->status,
                'last_maintenance' => $request->last_maintenance,
                'notes' => $request->notes,
            ]);

            // KIRIM NOTIFIKASI
            $this->sendVehicleCreatedNotifications($vehicle);

            DB::commit();

            Log::info('Vehicle created:', [
                'id' => $vehicle->id,
                'name' => $vehicle->name,
                'license_plate' => $vehicle->license_plate
            ]);

            return redirect()->route('vehicles.index')
                ->with('success', 'Kendaraan ' . $vehicle->name . ' berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vehicle creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menambahkan kendaraan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified vehicle.
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load('deliveries');

        // Get delivery statistics for this vehicle
        $stats = [
            'total_deliveries' => $vehicle->deliveries->count(),
            'completed_deliveries' => $vehicle->deliveries->where('status', 'delivered')->count(),
            'pending_deliveries' => $vehicle->deliveries->where('status', 'pending')->count(),
            'active_delivery' => $vehicle->deliveries->whereIn('status', ['assigned', 'picked_up', 'on_delivery'])->first(),
        ];

        return view('vehicles.show', compact('vehicle', 'stats'));
    }

    /**
     * Show the form for editing the specified vehicle.
     */
    public function edit(Vehicle $vehicle)
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate,' . $vehicle->id,
            'type' => 'required|in:motor,mobil,truck',
            'capacity_weight' => 'nullable|numeric|min:0',
            'capacity_volume' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,in_use,maintenance',
            'last_maintenance' => 'nullable|date',
            'notes' => 'nullable|string',
        ], [
            'license_plate.unique' => 'Plat nomor sudah terdaftar',
            'name.required' => 'Nama kendaraan wajib diisi',
            'license_plate.required' => 'Plat nomor wajib diisi',
            'type.required' => 'Jenis kendaraan wajib dipilih',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $oldData = $vehicle->toArray();
            $oldStatus = $vehicle->status;

            $vehicle->update([
                'name' => $request->name,
                'license_plate' => strtoupper($request->license_plate),
                'type' => $request->type,
                'capacity_weight' => $request->capacity_weight ?? 0,
                'capacity_volume' => $request->capacity_volume ?? 0,
                'status' => $request->status,
                'last_maintenance' => $request->last_maintenance,
                'notes' => $request->notes,
            ]);

            // Catat perubahan untuk notifikasi
            $changes = [];
            $skipFields = ['updated_at', 'created_at'];
            
            foreach ($request->all() as $key => $value) {
                if (in_array($key, $skipFields)) continue;
                
                if (isset($oldData[$key]) && $oldData[$key] != $value) {
                    $changes[$key] = [
                        'old' => $oldData[$key],
                        'new' => $value
                    ];
                }
            }

            // Kirim notifikasi jika ada perubahan data
            if (!empty($changes)) {
                $this->sendVehicleUpdatedNotifications($vehicle, $changes);
            }

            // Kirim notifikasi perubahan status
            if ($oldStatus != $request->status) {
                $this->sendVehicleStatusChangedNotifications($vehicle, $oldStatus, $request->status);
            }

            DB::commit();

            return redirect()->route('vehicles.show', $vehicle)
                ->with('success', 'Kendaraan berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vehicle update failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal memperbarui kendaraan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update vehicle status.
     */
    public function updateStatus(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'status' => 'required|in:available,in_use,maintenance'
        ]);

        $oldStatus = $vehicle->status;
        
        $vehicle->update([
            'status' => $request->status
        ]);

        // Kirim notifikasi perubahan status
        if ($oldStatus != $request->status) {
            $this->sendVehicleStatusChangedNotifications($vehicle, $oldStatus, $request->status);
        }

        $statusLabels = [
            'available' => 'Tersedia',
            'in_use' => 'Sedang Digunakan',
            'maintenance' => 'Servis'
        ];

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Status kendaraan berhasil diperbarui menjadi ' . $statusLabels[$request->status]);
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy(Vehicle $vehicle)
    {
        DB::beginTransaction();

        try {
            // Check if vehicle has active deliveries
            $activeDeliveries = $vehicle->deliveries()
                ->whereIn('status', ['assigned', 'picked_up', 'on_delivery'])
                ->count();

            if ($activeDeliveries > 0) {
                throw new \Exception('Kendaraan sedang digunakan untuk pengiriman aktif');
            }

            $vehicleName = $vehicle->name;
            $vehiclePlate = $vehicle->license_plate;
            $currentUser = auth()->user();

            $vehicle->delete();

            // Kirim notifikasi penghapusan
            $this->sendVehicleDeletedNotifications($vehicleName, $vehiclePlate, $currentUser);

            DB::commit();

            Log::info('Vehicle deleted:', ['vehicle_name' => $vehicleName]);

            return redirect()->route('vehicles.index')
                ->with('success', 'Kendaraan ' . $vehicleName . ' berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vehicle deletion failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus kendaraan: ' . $e->getMessage());
        }
    }

    /**
     * Get available vehicles for AJAX.
     */
    public function getAvailableVehicles()
    {
        $vehicles = Vehicle::where('status', 'available')
            ->orderBy('name')
            ->get()
            ->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'text' => $vehicle->name . ' - ' . $vehicle->license_plate . ' (' . $vehicle->type . ')',
                    'name' => $vehicle->name,
                    'plate' => $vehicle->license_plate,
                    'type' => $vehicle->type,
                    'max_weight' => $vehicle->capacity_weight,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $vehicles
        ]);
    }

    /**
     * Quick update vehicle (for AJAX).
     */
    public function quickUpdate(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:available,in_use,maintenance',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $oldStatus = $vehicle->status;

            if ($request->has('status')) {
                $vehicle->status = $request->status;
            }

            if ($request->has('notes')) {
                $vehicle->notes = $request->notes;
            }

            $vehicle->save();

            // Kirim notifikasi perubahan status
            if ($request->has('status') && $oldStatus != $request->status) {
                $this->sendVehicleStatusChangedNotifications($vehicle, $oldStatus, $request->status);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kendaraan diperbarui',
                'vehicle' => $vehicle
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export vehicles data.
     */
    public function export(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'format' => 'required|in:excel,pdf,csv',
            'status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $query = Vehicle::query();

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $vehicles = $query->get();

        // TODO: Implement export logic (Excel/PDF/CSV)
        // This would typically use a package like maatwebsite/excel

        return redirect()->back()
            ->with('info', 'Fitur export sedang dalam pengembangan');
    }

    /**
     * Get vehicle statistics for dashboard.
     */
    public function getStatistics()
    {
        $stats = [
            'total' => Vehicle::count(),
            'available' => Vehicle::where('status', 'available')->count(),
            'in_use' => Vehicle::where('status', 'in_use')->count(),
            'maintenance' => Vehicle::where('status', 'maintenance')->count(),
            'by_type' => [
                'motor' => Vehicle::where('type', 'motor')->count(),
                'mobil' => Vehicle::where('type', 'mobil')->count(),
                'truck' => Vehicle::where('type', 'truck')->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Bulk update vehicle status.
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_ids' => 'required|array',
            'vehicle_ids.*' => 'exists:vehicles,id',
            'status' => 'required|in:available,in_use,maintenance',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $vehicles = Vehicle::whereIn('id', $request->vehicle_ids)->get();
            $count = 0;

            foreach ($vehicles as $vehicle) {
                $oldStatus = $vehicle->status;
                
                $vehicle->update([
                    'status' => $request->status,
                    'notes' => $request->notes ? $vehicle->notes . "\n" . $request->notes : $vehicle->notes
                ]);

                // Kirim notifikasi untuk setiap kendaraan yang statusnya berubah
                if ($oldStatus != $request->status) {
                    $this->sendVehicleStatusChangedNotifications($vehicle, $oldStatus, $request->status);
                }

                $count++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $count . ' kendaraan berhasil diperbarui',
                'count' => $count
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== NOTIFICATION METHODS ====================

    /**
     * Send notifications when vehicle is created
     */
    private function sendVehicleCreatedNotifications($vehicle)
    {
        try {
            $currentUser = auth()->user();
            
            // 1. Kirim ke semua user dengan role owner
            $owners = User::where('role', 'owner')->get();
            
            foreach ($owners as $owner) {
                if ($owner->id != $currentUser->id) {
                    $owner->notify(new VehicleCreatedNotification($vehicle, $currentUser));
                    Log::info('Notifikasi kendaraan terkirim ke owner:', [
                        'owner_id' => $owner->id,
                        'vehicle_id' => $vehicle->id
                    ]);
                }
            }
            
            // 2. Kirim ke user dengan role logistik
            $logistik = User::where('role', 'logistik')->get();
            
            foreach ($logistik as $user) {
                if ($user->id != $currentUser->id) {
                    $user->notify(new VehicleCreatedNotification($vehicle, $currentUser));
                    Log::info('Notifikasi kendaraan terkirim ke logistik:', [
                        'user_id' => $user->id,
                        'vehicle_id' => $vehicle->id
                    ]);
                }
            }
            
            // 3. Kirim ke diri sendiri (pembuat)
            $currentUser->notify(new VehicleCreatedNotification($vehicle, $currentUser));
            Log::info('Notifikasi kendaraan terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'vehicle_id' => $vehicle->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi kendaraan: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when vehicle is updated
     */
    private function sendVehicleUpdatedNotifications($vehicle, $changes)
    {
        try {
            $currentUser = auth()->user();
            
            // Kirim ke semua owner
            $owners = User::where('role', 'owner')->get();
            
            foreach ($owners as $owner) {
                if ($owner->id != $currentUser->id) {
                    $owner->notify(new VehicleUpdatedNotification($vehicle, $currentUser, $changes));
                    Log::info('Notifikasi update kendaraan terkirim ke owner:', [
                        'owner_id' => $owner->id,
                        'vehicle_id' => $vehicle->id
                    ]);
                }
            }
            
            // Kirim ke user logistik
            $logistik = User::where('role', 'logistik')->get();
            
            foreach ($logistik as $user) {
                if ($user->id != $currentUser->id) {
                    $user->notify(new VehicleUpdatedNotification($vehicle, $currentUser, $changes));
                    Log::info('Notifikasi update kendaraan terkirim ke logistik:', [
                        'user_id' => $user->id,
                        'vehicle_id' => $vehicle->id
                    ]);
                }
            }
            
            // Kirim ke diri sendiri
            $currentUser->notify(new VehicleUpdatedNotification($vehicle, $currentUser, $changes));
            Log::info('Notifikasi update kendaraan terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'vehicle_id' => $vehicle->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi update kendaraan: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when vehicle status changes
     */
    private function sendVehicleStatusChangedNotifications($vehicle, $oldStatus, $newStatus)
    {
        try {
            $currentUser = auth()->user();
            
            // 1. Kirim ke semua owner
            $owners = User::where('role', 'owner')->get();
            
            foreach ($owners as $owner) {
                if ($owner->id != $currentUser->id) {
                    $owner->notify(new VehicleStatusChangedNotification($vehicle, $currentUser, $oldStatus, $newStatus));
                    Log::info('Notifikasi status kendaraan terkirim ke owner:', [
                        'owner_id' => $owner->id,
                        'vehicle_id' => $vehicle->id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus
                    ]);
                }
            }
            
            // 2. Kirim ke user logistik
            $logistik = User::where('role', 'logistik')->get();
            
            foreach ($logistik as $user) {
                if ($user->id != $currentUser->id) {
                    $user->notify(new VehicleStatusChangedNotification($vehicle, $currentUser, $oldStatus, $newStatus));
                    Log::info('Notifikasi status kendaraan terkirim ke logistik:', [
                        'user_id' => $user->id,
                        'vehicle_id' => $vehicle->id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus
                    ]);
                }
            }
            
            // 3. Kirim ke diri sendiri
            $currentUser->notify(new VehicleStatusChangedNotification($vehicle, $currentUser, $oldStatus, $newStatus));
            Log::info('Notifikasi status kendaraan terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'vehicle_id' => $vehicle->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi perubahan status: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when vehicle is deleted
     */
    private function sendVehicleDeletedNotifications($vehicleName, $vehiclePlate, $deletedBy)
    {
        try {
            // Kirim ke semua owner
            $owners = User::where('role', 'owner')
                ->where('id', '!=', $deletedBy->id)
                ->get();
            
            foreach ($owners as $owner) {
                $owner->notify(new VehicleDeletedNotification($vehicleName, $vehiclePlate, $deletedBy));
                Log::info('Notifikasi hapus kendaraan terkirim ke owner:', [
                    'owner_id' => $owner->id,
                    'vehicle_name' => $vehicleName
                ]);
            }
            
            // Kirim ke user logistik
            $logistik = User::where('role', 'logistik')
                ->where('id', '!=', $deletedBy->id)
                ->get();
            
            foreach ($logistik as $user) {
                $user->notify(new VehicleDeletedNotification($vehicleName, $vehiclePlate, $deletedBy));
                Log::info('Notifikasi hapus kendaraan terkirim ke logistik:', [
                    'user_id' => $user->id,
                    'vehicle_name' => $vehicleName
                ]);
            }
            
            // Kirim ke diri sendiri
            $deletedBy->notify(new VehicleDeletedNotification($vehicleName, $vehiclePlate, $deletedBy));
            Log::info('Notifikasi hapus kendaraan terkirim ke diri sendiri:', [
                'user_id' => $deletedBy->id,
                'vehicle_name' => $vehicleName
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi hapus kendaraan: ' . $e->getMessage());
        }
    }
}