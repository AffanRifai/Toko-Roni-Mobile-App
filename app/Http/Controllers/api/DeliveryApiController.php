<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\DeliveryAssignedNotification;
use App\Notifications\DeliveryCreatedNotification;
use App\Notifications\DeliveryDeletedNotification;
use App\Notifications\DeliveryStatusChangedNotification;
use App\Services\NotificationRoutingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

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
            $query = Delivery::with(['user', 'vehicle', 'transaction'])->latest();

            if ($request->filled('transaction_id')) {
                $query->where('transaction_id', (int) $request->transaction_id);
            }

            if ($request->filled('status')) {
                $query->where('status', strtolower(trim((string) $request->status)));
            }

            $perPage = (int) $request->get('per_page', 20);
            if ($perPage <= 0) {
                $perPage = 20;
            }
            $deliveries = $query->paginate($perPage);
            return response()->json(['success' => true, 'data' => $deliveries], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Store delivery.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|integer|exists:transactions,id',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:1000',
            'total_items' => 'required|integer|min:1',
            'total_weight' => 'nullable|numeric|min:0',
            'total_volume' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,processing,assigned,picked_up,on_delivery,delivered,failed,cancelled',
            'estimated_delivery_time' => 'nullable|date',
            'notes' => 'nullable|string',
            'user_id' => 'nullable|integer|exists:users,id',
            'driver_id' => 'nullable|integer|exists:users,id',
            'vehicle_id' => 'nullable|integer|exists:vehicles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $transactionId = (int) $request->input('transaction_id');
            $existing = Delivery::where('transaction_id', $transactionId)->first();
            if ($existing) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi ini sudah memiliki pengiriman',
                    'data' => $existing->load(['transaction', 'user', 'vehicle']),
                ], 422);
            }

            // Kompatibilitas payload: bisa kirim user_id atau driver_id.
            // Saat delivery baru dibuat, kurir boleh kosong sampai proses assign.
            $driverId = $request->input('user_id') ?? $request->input('driver_id');
            if (empty($driverId)) {
                $driverId = null;
            }

            $delivery = Delivery::create([
                'delivery_code' => Delivery::generateDeliveryCode(),
                'transaction_id' => $transactionId,
                'user_id' => $driverId,
                'vehicle_id' => $request->input('vehicle_id'),
                'origin' => trim((string) $request->input('origin')),
                'destination' => trim((string) $request->input('destination')),
                'total_items' => max(1, (int) $request->input('total_items', 1)),
                'total_weight' => (float) $request->input('total_weight', 0),
                'total_volume' => (float) $request->input('total_volume', 0),
                'status' => strtolower(trim((string) $request->input('status', 'pending'))),
                'estimated_delivery_time' => $request->input('estimated_delivery_time'),
                'notes' => $request->input('notes'),
            ]);

            // Sinkronkan ringkasan pengiriman di transaksi.
            $transaction = Transaction::find($transactionId);
            if ($transaction) {
                $syncPayload = [];
                if (Schema::hasColumn('transactions', 'need_delivery')) {
                    $syncPayload['need_delivery'] = true;
                }
                if (Schema::hasColumn('transactions', 'delivery_address')) {
                    $syncPayload['delivery_address'] = $delivery->destination;
                }
                if (Schema::hasColumn('transactions', 'recipient_name')) {
                    $syncPayload['recipient_name'] = $transaction->recipient_name ?: $transaction->customer_name;
                }
                if (Schema::hasColumn('transactions', 'recipient_phone')) {
                    $syncPayload['recipient_phone'] = $transaction->recipient_phone ?: $transaction->customer_phone;
                }
                if (Schema::hasColumn('transactions', 'desired_delivery_date')) {
                    $syncPayload['desired_delivery_date'] = optional($delivery->estimated_delivery_time)->format('Y-m-d');
                }
                if (Schema::hasColumn('transactions', 'delivery_notes')) {
                    $syncPayload['delivery_notes'] = $delivery->notes;
                }
                if (Schema::hasColumn('transactions', 'delivery_status')) {
                    $syncPayload['delivery_status'] = $delivery->status;
                }
                if (!empty($syncPayload)) {
                    $transaction->update($syncPayload);
                }
            }

            DB::commit();
            $actor = auth()->user();
            if ($actor instanceof User) {
                $this->notifyByEvent(
                    eventKey: 'delivery.created',
                    notification: new DeliveryCreatedNotification($delivery, $actor),
                    actor: $actor
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Delivery created successfully',
                'data' => $delivery->load(['transaction', 'user', 'vehicle']),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('API delivery store error', [
                'message' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pengiriman',
                'error' => $e->getMessage(),
            ], 500);
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
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ]);
        try {
            $driver = User::findOrFail((int) $request->user_id);
            $allowedRoles = ['logistik', 'staff_logistik', 'kurir'];
            if (!in_array($driver->role, $allowedRoles, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User yang dipilih bukan kurir/logistik',
                ], 422);
            }

            $delivery = Delivery::findOrFail($id);
            $payload = [
                'user_id' => (int) $request->user_id,
                'status' => 'assigned',
            ];
            $assignedVehicle = $delivery->vehicle;

            if ($request->filled('vehicle_id')) {
                $vehicle = Vehicle::findOrFail((int) $request->vehicle_id);
                if ($vehicle->status !== 'available') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kendaraan tidak tersedia',
                    ], 422);
                }
                $payload['vehicle_id'] = $vehicle->id;
                $vehicle->update(['status' => 'in_use']);
                $assignedVehicle = $vehicle;
            }

            if (isset($payload['vehicle_id']) &&
                !empty($delivery->vehicle_id) &&
                (int) $delivery->vehicle_id !== (int) $payload['vehicle_id']) {
                Vehicle::where('id', $delivery->vehicle_id)->update([
                    'status' => 'available',
                ]);
            }

            $delivery->update($payload);
            $delivery->load(['user', 'vehicle', 'transaction']);

            $actor = auth()->user();
            if ($actor instanceof User) {
                $this->notifyByEvent(
                    eventKey: 'delivery.assigned',
                    notification: new DeliveryAssignedNotification(
                        $delivery,
                        $actor,
                        $driver,
                        $assignedVehicle
                    ),
                    actor: $actor,
                    extraUserIds: [$driver->id]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Assigned',
                'data' => $delivery,
            ], 200);
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
            $oldStatus = (string) $delivery->status;
            $newStatus = strtolower(trim((string) $request->status));
            $delivery->update(['status' => $newStatus]);

            if (in_array($newStatus, ['delivered', 'cancelled', 'failed'], true)) {
                if (!empty($delivery->vehicle_id)) {
                    Vehicle::where('id', $delivery->vehicle_id)->update([
                        'status' => 'available',
                    ]);
                }
            }
            $actor = auth()->user();
            if ($actor instanceof User && $oldStatus !== $newStatus) {
                $this->notifyByEvent(
                    eventKey: 'delivery.status_changed',
                    notification: new DeliveryStatusChangedNotification($delivery, $actor, $oldStatus, $newStatus),
                    actor: $actor,
                    extraUserIds: array_filter([$delivery->user_id])
                );
            }
            return response()->json(['success' => true, 'message' => 'Status updated'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Delete delivery.
     */
    public function destroy($id)
    {
        try {
            $delivery = Delivery::findOrFail($id);
            $deliveryCode = $delivery->delivery_code;
            $driverId = $delivery->user_id;
            $actor = auth()->user();
            $delivery->delete();
            if ($actor instanceof User) {
                $this->notifyByEvent(
                    eventKey: 'delivery.deleted',
                    notification: new DeliveryDeletedNotification($deliveryCode, $actor),
                    actor: $actor,
                    extraUserIds: array_filter([$driverId])
                );
            }
            return response()->json(['success' => true, 'message' => 'Delivery deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Mark delivery as picked up.
     */
    public function pickup($id)
    {
        return $this->quickUpdateStatus($id, 'picked_up');
    }

    /**
     * Mark delivery as on delivery.
     */
    public function start($id)
    {
        return $this->quickUpdateStatus($id, 'on_delivery');
    }

    /**
     * Mark delivery as delivered.
     */
    public function complete($id)
    {
        try {
            $delivery = Delivery::findOrFail($id);
            $oldStatus = (string) $delivery->status;
            $delivery->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
            $actor = auth()->user();
            if ($actor instanceof User && $oldStatus !== 'delivered') {
                $this->notifyByEvent(
                    eventKey: 'delivery.status_changed',
                    notification: new DeliveryStatusChangedNotification($delivery, $actor, $oldStatus, 'delivered'),
                    actor: $actor,
                    extraUserIds: array_filter([$delivery->user_id])
                );
            }
            return response()->json(['success' => true, 'message' => 'Delivery completed'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Cancel delivery.
     */
    public function cancel($id)
    {
        return $this->quickUpdateStatus($id, 'cancelled');
    }

    /**
     * Export delivery placeholder.
     */
    public function export(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Fitur export pengiriman belum tersedia',
        ], 501);
    }

    /**
     * Available drivers
     */
    public function availableDrivers()
    {
        $drivers = User::whereIn('role', ['logistik', 'staff_logistik', 'kurir'])
            ->where(function ($q) {
                $q->whereNull('is_active')->orWhere('is_active', true);
            })
            ->orderBy('name')
            ->get();
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

    private function quickUpdateStatus(int $id, string $status)
    {
        try {
            $delivery = Delivery::findOrFail($id);
            $oldStatus = (string) $delivery->status;
            $delivery->update(['status' => $status]);
            if (in_array($status, ['delivered', 'cancelled', 'failed'], true)) {
                if (!empty($delivery->vehicle_id)) {
                    Vehicle::where('id', $delivery->vehicle_id)->update([
                        'status' => 'available',
                    ]);
                }
            }
            $actor = auth()->user();
            if ($actor instanceof User && $oldStatus !== $status) {
                $this->notifyByEvent(
                    eventKey: 'delivery.status_changed',
                    notification: new DeliveryStatusChangedNotification($delivery, $actor, $oldStatus, $status),
                    actor: $actor,
                    extraUserIds: array_filter([$delivery->user_id])
                );
            }
            return response()->json(['success' => true, 'message' => 'Status updated'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    private function notifyByEvent(string $eventKey, $notification, User $actor, array $extraUserIds = []): void
    {
        try {
            /** @var NotificationRoutingService $router */
            $router = app(NotificationRoutingService::class);
            $router->send($eventKey, $notification, $actor, [
                'target_user_ids' => $extraUserIds,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Delivery API notification failed: ' . $e->getMessage());
        }
    }
}
