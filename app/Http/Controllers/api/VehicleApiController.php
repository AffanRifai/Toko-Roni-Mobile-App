<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\VehicleCreatedNotification;
use App\Notifications\VehicleDeletedNotification;
use App\Notifications\VehicleStatusChangedNotification;
use App\Notifications\VehicleUpdatedNotification;
use App\Services\NotificationRoutingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class VehicleApiController extends Controller
{
    /**
     * Display a listing of vehicles.
     */
    public function index(Request $request)
    {
        try {
            $query = Vehicle::query()->latest();

            $status = $this->normalizeStatus($request->get('status'));
            if (!empty($status) && $status !== 'all') {
                $query->where('status', $status);
            }

            $type = $this->normalizeType($request->get('type'));
            if (!empty($type) && $type !== 'all') {
                $query->where('type', $type);
            }

            $search = trim((string) $request->get('search', ''));
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('license_plate', 'like', "%{$search}%");
                });
            }

            $perPage = (int) $request->get('per_page', 0);
            if ($perPage > 0) {
                $perPage = max(1, min($perPage, 500));
                $vehicles = $query->paginate($perPage);
            } else {
                $vehicles = $query->get();
            }

            return response()->json(['success' => true, 'data' => $vehicles], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memuat data kendaraan'], 500);
        }
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request)
    {
        try {
            $payload = $this->normalizePayload($request);

            $validator = Validator::make($payload, [
                'name' => 'required|string|max:255',
                'license_plate' => 'required|string|max:30|unique:vehicles,license_plate',
                'type' => 'required|in:motorcycle,pickup,van,truck',
                'status' => 'required|in:available,in_use,maintenance',
                'capacity_weight' => 'nullable|numeric|min:0',
                'capacity_volume' => 'nullable|numeric|min:0',
                'last_maintenance' => 'nullable|date',
                'notes' => 'nullable|string',
            ], [
                'license_plate.unique' => 'Plat nomor sudah terdaftar',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = [
                'name' => $payload['name'],
                'license_plate' => strtoupper($payload['license_plate']),
                'type' => $payload['type'],
                'status' => $payload['status'],
                'capacity_weight' => $payload['capacity_weight'] ?? 0,
                'capacity_volume' => $payload['capacity_volume'] ?? 0,
                'last_maintenance' => $payload['last_maintenance'],
            ];

            if (Schema::hasColumn('vehicles', 'notes')) {
                $data['notes'] = $payload['notes'];
            }

            $vehicle = Vehicle::create($data);
            $actor = auth()->user();
            if ($actor instanceof User) {
                $this->notifyByEvent(
                    eventKey: 'vehicle.created',
                    notification: new VehicleCreatedNotification($vehicle, $actor),
                    actor: $actor
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Kendaraan berhasil ditambahkan',
                'data' => $vehicle,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menambah kendaraan'], 500);
        }
    }

    /**
     * Show vehicle.
     */
    public function show($id)
    {
        try {
            $vehicle = Vehicle::with([
                'deliveries' => function ($q) {
                    $q->latest()->limit(25);
                },
            ])->findOrFail($id);

            return response()->json(['success' => true, 'data' => $vehicle], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Kendaraan tidak ditemukan'], 404);
        }
    }

    /**
     * Update vehicle.
     */
    public function update(Request $request, $id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            $payload = $this->normalizePayload($request);
            $before = $vehicle->getOriginal();
            $oldStatus = (string) $vehicle->status;

            $validator = Validator::make($payload, [
                'name' => 'required|string|max:255',
                'license_plate' => 'required|string|max:30|unique:vehicles,license_plate,' . $vehicle->id,
                'type' => 'required|in:motorcycle,pickup,van,truck',
                'status' => 'required|in:available,in_use,maintenance',
                'capacity_weight' => 'nullable|numeric|min:0',
                'capacity_volume' => 'nullable|numeric|min:0',
                'last_maintenance' => 'nullable|date',
                'notes' => 'nullable|string',
            ], [
                'license_plate.unique' => 'Plat nomor sudah terdaftar',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = [
                'name' => $payload['name'],
                'license_plate' => strtoupper($payload['license_plate']),
                'type' => $payload['type'],
                'status' => $payload['status'],
                'capacity_weight' => $payload['capacity_weight'] ?? 0,
                'capacity_volume' => $payload['capacity_volume'] ?? 0,
                'last_maintenance' => $payload['last_maintenance'],
            ];

            if (Schema::hasColumn('vehicles', 'notes')) {
                $data['notes'] = $payload['notes'];
            }

            $vehicle->update($data);
            $actor = auth()->user();
            if ($actor instanceof User) {
                $changes = [];
                foreach ($data as $key => $value) {
                    $oldValue = $before[$key] ?? null;
                    if ((string) $oldValue !== (string) $value) {
                        $changes[$key] = $value;
                    }
                }

                if (!empty($changes)) {
                    $freshVehicle = $vehicle->fresh();
                    $this->notifyByEvent(
                        eventKey: 'vehicle.updated',
                        notification: new VehicleUpdatedNotification($freshVehicle, $actor, $changes),
                        actor: $actor
                    );

                    $newStatus = (string) ($freshVehicle->status ?? $oldStatus);
                    if ($newStatus !== $oldStatus) {
                        $this->notifyByEvent(
                            eventKey: 'vehicle.status_changed',
                            notification: new VehicleStatusChangedNotification($freshVehicle, $actor, $oldStatus, $newStatus),
                            actor: $actor
                        );
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Kendaraan berhasil diperbarui',
                'data' => $vehicle->fresh(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui kendaraan'], 500);
        }
    }

    /**
     * Remove vehicle.
     */
    public function destroy($id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            $vehicleName = $vehicle->name;
            $vehiclePlate = $vehicle->license_plate;

            $activeDeliveries = $vehicle->deliveries()
                ->whereIn('status', ['assigned', 'picked_up', 'on_delivery'])
                ->count();

            if ($activeDeliveries > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kendaraan sedang digunakan untuk pengiriman aktif',
                ], 422);
            }

            $actor = auth()->user();
            $vehicle->delete();
            if ($actor instanceof User) {
                $this->notifyByEvent(
                    eventKey: 'vehicle.deleted',
                    notification: new VehicleDeletedNotification($vehicleName, $vehiclePlate, $actor),
                    actor: $actor
                );
            }

            return response()->json(['success' => true, 'message' => 'Kendaraan berhasil dihapus'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus kendaraan'], 500);
        }
    }

    /**
     * Get available vehicles.
     */
    public function available()
    {
        try {
            $vehicles = Vehicle::where('status', 'available')->latest()->get();
            return response()->json(['success' => true, 'data' => $vehicles], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memuat kendaraan tersedia'], 500);
        }
    }

    /**
     * Get maintenance vehicles.
     */
    public function inMaintenance()
    {
        try {
            $vehicles = Vehicle::where('status', 'maintenance')->latest()->get();
            return response()->json(['success' => true, 'data' => $vehicles], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memuat kendaraan servis'], 500);
        }
    }

    /**
     * Get vehicle statistics.
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total' => Vehicle::count(),
                'available' => Vehicle::where('status', 'available')->count(),
                'in_use' => Vehicle::where('status', 'in_use')->count(),
                'maintenance' => Vehicle::where('status', 'maintenance')->count(),
            ];
            return response()->json(['success' => true, 'data' => $stats], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memuat statistik kendaraan'], 500);
        }
    }

    /**
     * Set maintenance status.
     */
    public function setMaintenance($id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            $oldStatus = (string) $vehicle->status;
            $vehicle->update(['status' => 'maintenance']);
            $actor = auth()->user();
            if ($actor instanceof User && $oldStatus !== 'maintenance') {
                $this->notifyByEvent(
                    eventKey: 'vehicle.status_changed',
                    notification: new VehicleStatusChangedNotification($vehicle->fresh(), $actor, $oldStatus, 'maintenance'),
                    actor: $actor
                );
            }
            return response()->json([
                'success' => true,
                'message' => 'Status kendaraan menjadi servis',
                'data' => $vehicle->fresh(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengubah status kendaraan'], 500);
        }
    }

    /**
     * Set available status.
     */
    public function setAvailable($id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            $oldStatus = (string) $vehicle->status;
            $vehicle->update(['status' => 'available']);
            $actor = auth()->user();
            if ($actor instanceof User && $oldStatus !== 'available') {
                $this->notifyByEvent(
                    eventKey: 'vehicle.status_changed',
                    notification: new VehicleStatusChangedNotification($vehicle->fresh(), $actor, $oldStatus, 'available'),
                    actor: $actor
                );
            }
            return response()->json([
                'success' => true,
                'message' => 'Status kendaraan menjadi tersedia',
                'data' => $vehicle->fresh(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengubah status kendaraan'], 500);
        }
    }

    /**
     * Export placeholder.
     */
    public function export(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Fitur export kendaraan belum tersedia',
        ], 501);
    }

    private function normalizePayload(Request $request): array
    {
        return [
            'name' => trim((string) $request->input('name', '')),
            'license_plate' => trim((string) $request->input('license_plate', '')),
            'type' => $this->normalizeType($request->input('type')),
            'status' => $this->normalizeStatus($request->input('status')),
            'capacity_weight' => $request->filled('capacity_weight') ? (float) $request->input('capacity_weight') : null,
            'capacity_volume' => $request->filled('capacity_volume') ? (float) $request->input('capacity_volume') : null,
            'last_maintenance' => $this->normalizeDate($request->input('last_maintenance')),
            'notes' => trim((string) $request->input('notes', '')),
        ];
    }

    private function normalizeType($raw): string
    {
        $value = strtolower(trim((string) $raw));

        return match ($value) {
            'motor', 'motorcycle' => 'motorcycle',
            'mobil pick-up', 'mobil pickup', 'pickup', 'pick-up' => 'pickup',
            'van' => 'van',
            'truck' => 'truck',
            default => $value,
        };
    }

    private function normalizeStatus($raw): string
    {
        $value = strtolower(trim((string) $raw));

        return match ($value) {
            'tersedia', 'available' => 'available',
            'sedang digunakan', 'digunakan', 'in_use', 'in use' => 'in_use',
            'servis', 'service', 'maintenance' => 'maintenance',
            default => $value,
        };
    }

    private function normalizeDate($raw): ?string
    {
        $value = trim((string) $raw);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function notifyByEvent(string $eventKey, $notification, User $actor): void
    {
        try {
            /** @var NotificationRoutingService $router */
            $router = app(NotificationRoutingService::class);
            $router->send($eventKey, $notification, $actor);
        } catch (\Throwable $e) {
            Log::warning('Vehicle API notification failed: ' . $e->getMessage());
        }
    }
}
