<?php
// app/Http/Controllers/DeliveryController.php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Transaction;
use App\Models\Vehicle;
use App\Models\User;
use App\Notifications\DeliveryCreatedNotification;
use App\Notifications\DeliveryUpdatedNotification;
use App\Notifications\DeliveryAssignedNotification;
use App\Notifications\DeliveryStatusChangedNotification;
use App\Notifications\DeliveryDeletedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class DeliveryController extends Controller
{
    /**
     * Display a listing of deliveries.
     */
    public function index(Request $request)
    {
        $query = Delivery::with(['transaction', 'user', 'vehicle'])->latest();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by driver
        if ($request->has('driver_id') && $request->driver_id !== 'all') {
            $query->where('user_id', $request->driver_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Search by delivery code or transaction
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('delivery_code', 'like', "%{$search}%")
                    ->orWhere('origin', 'like', "%{$search}%")
                    ->orWhere('destination', 'like', "%{$search}%")
                    ->orWhereHas('transaction', function ($q) use ($search) {
                        $q->where('invoice_number', 'like', "%{$search}%");
                    });
            });
        }

        $deliveries = $query->paginate(20)->withQueryString();

        // Get drivers from users table with role 'kurir' or 'driver'
        $drivers = User::whereIn('role', ['kurir', 'driver'])->get();

        // Get available vehicles
        $vehicles = Vehicle::where('status', 'available')->get();

        // Statistics
        $stats = [
            'total' => Delivery::count(),
            'pending' => Delivery::where('status', 'pending')->count(),
            'on_delivery' => Delivery::whereIn('status', ['assigned', 'picked_up', 'on_delivery'])->count(),
            'delivered' => Delivery::where('status', 'delivered')->count(),
            'failed' => Delivery::where('status', 'failed')->count(),
        ];

        return view('delivery.index', compact('deliveries', 'drivers', 'vehicles', 'stats'));
    }

    /**
     * Show the form for creating a new delivery.
     */
    public function create()
    {
        $transactions = Transaction::with('items')
            ->whereDoesntHave('delivery')
            ->latest()
            ->get();

        return view('delivery.create', compact('transactions'));
    }

    /**
     * Store a newly created delivery.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|exists:transactions,id',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'total_items' => 'required|integer|min:1',
            'total_weight' => 'nullable|numeric|min:0',
            'total_volume' => 'nullable|numeric|min:0',
            'estimated_delivery_time' => 'nullable|date|after:now',
            'status' => 'required|in:pending,processing',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Check if delivery already exists for this transaction
            $existingDelivery = Delivery::where('transaction_id', $request->transaction_id)->first();
            if ($existingDelivery) {
                throw new \Exception('Transaksi ini sudah memiliki pengiriman');
            }

            // Generate delivery code
            $deliveryCode = Delivery::generateDeliveryCode();

            // Create delivery
            $delivery = Delivery::create([
                'delivery_code' => $deliveryCode,
                'transaction_id' => $request->transaction_id,
                'origin' => $request->origin,
                'destination' => $request->destination,
                'total_items' => $request->total_items,
                'total_weight' => $request->total_weight ?? 0,
                'total_volume' => $request->total_volume ?? 0,
                'status' => $request->status,
                'estimated_delivery_time' => $request->estimated_delivery_time,
                'notes' => $request->notes,
            ]);

            // KIRIM NOTIFIKASI
            $this->sendDeliveryCreatedNotifications($delivery);

            DB::commit();

            Log::info('Delivery created:', [
                'delivery_code' => $delivery->delivery_code,
                'transaction_id' => $request->transaction_id
            ]);

            return redirect()->route('delivery.show', $delivery)
                ->with('success', 'Pengiriman berhasil dibuat dengan kode: ' . $deliveryCode);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal membuat pengiriman: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified delivery.
     */
    public function show(Delivery $delivery)
    {
        $delivery->load(['transaction', 'user', 'vehicle']);

        // Get available drivers and vehicles for assignment
        $availableDrivers = User::whereIn('role', ['kurir', 'driver'])
            ->whereDoesntHave('deliveries', function ($q) {
                $q->whereIn('status', ['assigned', 'picked_up', 'on_delivery']);
            })
            ->get();

        $availableVehicles = Vehicle::where('status', 'available')->get();

        return view('delivery.show', compact('delivery', 'availableDrivers', 'availableVehicles'));
    }

    /**
     * Show the form for editing the specified delivery.
     */
    public function edit(Delivery $delivery)
    {
        $delivery->load(['transaction', 'user', 'vehicle']);
        $drivers = User::whereIn('role', ['kurir', 'driver'])->get();
        $vehicles = Vehicle::all();

        return view('delivery.edit', compact('delivery', 'drivers', 'vehicles'));
    }

    /**
     * Update the specified delivery.
     */
    public function update(Request $request, Delivery $delivery)
    {
        $validator = Validator::make($request->all(), [
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'total_items' => 'required|integer|min:1',
            'total_weight' => 'nullable|numeric|min:0',
            'total_volume' => 'nullable|numeric|min:0',
            'estimated_delivery_time' => 'nullable|date',
            'status' => 'required|in:pending,processing,assigned,picked_up,on_delivery,delivered,failed,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $oldData = $delivery->toArray();
            $oldStatus = $delivery->status;

            $delivery->update([
                'origin' => $request->origin,
                'destination' => $request->destination,
                'total_items' => $request->total_items,
                'total_weight' => $request->total_weight ?? 0,
                'total_volume' => $request->total_volume ?? 0,
                'status' => $request->status,
                'estimated_delivery_time' => $request->estimated_delivery_time,
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

            // Kirim notifikasi jika ada perubahan data (bukan status)
            if (!empty($changes)) {
                $this->sendDeliveryUpdatedNotifications($delivery, $changes);
            }

            // Kirim notifikasi perubahan status
            if ($oldStatus != $request->status) {
                $this->sendDeliveryStatusChangedNotifications($delivery, $oldStatus, $request->status);
            }

            // If status changed to delivered, set delivered_at
            if ($request->status === 'delivered' && $oldStatus !== 'delivered') {
                $delivery->update(['delivered_at' => now()]);
            }

            DB::commit();

            Log::info('Delivery updated:', [
                'delivery_code' => $delivery->delivery_code,
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ]);

            return redirect()->route('delivery.show', $delivery)
                ->with('success', 'Pengiriman berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery update failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal memperbarui pengiriman: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified delivery.
     */
    public function destroy(Delivery $delivery)
    {
        DB::beginTransaction();

        try {
            // Only pending deliveries can be deleted
            if (!in_array($delivery->status, ['pending', 'cancelled', 'failed'])) {
                throw new \Exception('Hanya pengiriman dengan status pending, cancelled, atau failed yang dapat dihapus');
            }

            // Update vehicle status if assigned
            if ($delivery->vehicle) {
                $delivery->vehicle->update(['status' => 'available']);
            }

            $deliveryCode = $delivery->delivery_code;
            $currentUser = auth()->user();
            
            $delivery->delete();

            // Kirim notifikasi penghapusan
            $this->sendDeliveryDeletedNotifications($deliveryCode, $currentUser);

            DB::commit();

            Log::info('Delivery deleted:', ['delivery_code' => $deliveryCode]);

            return redirect()->route('delivery.index')
                ->with('success', 'Pengiriman ' . $deliveryCode . ' berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery deletion failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus pengiriman: ' . $e->getMessage());
        }
    }

    /**
     * Assign driver and vehicle to delivery.
     */
    public function assign(Request $request, Delivery $delivery)
    {
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|exists:users,id',
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            Log::info('Assigning delivery:', [
                'delivery_id' => $delivery->id,
                'delivery_code' => $delivery->delivery_code,
                'user_id' => $request->driver_id,
                'vehicle_id' => $request->vehicle_id
            ]);

            // Check if delivery can be assigned
            if (!in_array($delivery->status, ['pending', 'processing'])) {
                throw new \Exception('Pengiriman tidak dapat diassign pada status ini');
            }

            // Get user (kurir)
            $user = User::find($request->driver_id);
            if (!$user) {
                throw new \Exception('Kurir tidak ditemukan');
            }

            // Get vehicle
            $vehicle = Vehicle::find($request->vehicle_id);
            if (!$vehicle) {
                throw new \Exception('Kendaraan tidak ditemukan');
            }

            if ($vehicle->status !== 'available') {
                throw new \Exception('Kendaraan tidak tersedia');
            }

            $oldStatus = $delivery->status;

            // Update delivery
            $delivery->update([
                'user_id' => $request->driver_id,
                'vehicle_id' => $request->vehicle_id,
                'status' => 'assigned',
            ]);

            // Update vehicle status
            $vehicle->update(['status' => 'in_use']);

            // KIRIM NOTIFIKASI ASSIGNMENT
            $this->sendDeliveryAssignedNotifications($delivery, $user, $vehicle);

            // KIRIM NOTIFIKASI PERUBAHAN STATUS
            if ($oldStatus != 'assigned') {
                $this->sendDeliveryStatusChangedNotifications($delivery, $oldStatus, 'assigned');
            }

            DB::commit();

            return redirect()->route('delivery.show', $delivery)
                ->with('success', 'Kurir ' . $user->name . ' dan kendaraan ' . $vehicle->name . ' berhasil ditugaskan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery assignment failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menugaskan kurir: ' . $e->getMessage());
        }
    }

    /**
     * Mark delivery as picked up.
     */
    public function pickup(Delivery $delivery)
    {
        DB::beginTransaction();

        try {
            if ($delivery->status !== 'assigned') {
                throw new \Exception('Pengiriman harus diassign terlebih dahulu');
            }

            $oldStatus = $delivery->status;
            
            $delivery->update([
                'status' => 'picked_up',
                'pickup_time' => now(),
            ]);

            // Kirim notifikasi perubahan status
            $this->sendDeliveryStatusChangedNotifications($delivery, $oldStatus, 'picked_up');

            DB::commit();

            return redirect()->back()
                ->with('success', 'Paket telah diambil oleh kurir');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Start delivery (on the way).
     */
    public function startDelivery(Delivery $delivery)
    {
        DB::beginTransaction();

        try {
            if ($delivery->status !== 'picked_up') {
                throw new \Exception('Paket harus diambil terlebih dahulu');
            }

            $oldStatus = $delivery->status;
            
            $delivery->update([
                'status' => 'on_delivery',
                'start_delivery_time' => now(),
            ]);

            // Kirim notifikasi perubahan status
            $this->sendDeliveryStatusChangedNotifications($delivery, $oldStatus, 'on_delivery');

            DB::commit();

            return redirect()->back()
                ->with('success', 'Pengiriman sedang dalam perjalanan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Complete delivery.
     */
    public function complete(Request $request, Delivery $delivery)
    {
        $validator = Validator::make($request->all(), [
            'delivered_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            if (!in_array($delivery->status, ['on_delivery', 'picked_up', 'assigned'])) {
                throw new \Exception('Pengiriman tidak dapat diselesaikan');
            }

            $oldStatus = $delivery->status;
            
            $delivery->update([
                'status' => 'delivered',
                'delivered_at' => $request->delivered_at,
                'notes' => $request->notes ? $delivery->notes . "\n" . $request->notes : $delivery->notes,
            ]);

            // Update vehicle status back to available
            if ($delivery->vehicle) {
                $delivery->vehicle->update(['status' => 'available']);
            }

            // Kirim notifikasi perubahan status
            $this->sendDeliveryStatusChangedNotifications($delivery, $oldStatus, 'delivered');

            DB::commit();

            return redirect()->back()
                ->with('success', 'Pengiriman telah selesai');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Cancel delivery.
     */
    public function cancel(Request $request, Delivery $delivery)
    {
        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            if (in_array($delivery->status, ['delivered', 'cancelled'])) {
                throw new \Exception('Pengiriman tidak dapat dibatalkan');
            }

            $oldStatus = $delivery->status;
            
            $notes = $delivery->notes . "\n[CANCELLED] " . $request->cancellation_reason;

            $delivery->update([
                'status' => 'cancelled',
                'notes' => $notes,
            ]);

            // Update vehicle status back to available if assigned
            if ($delivery->vehicle) {
                $delivery->vehicle->update(['status' => 'available']);
            }

            // Kirim notifikasi perubahan status
            $this->sendDeliveryStatusChangedNotifications($delivery, $oldStatus, 'cancelled');

            DB::commit();

            return redirect()->back()
                ->with('success', 'Pengiriman dibatalkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Request delivery from transaction.
     */
    public function requestDelivery(Request $request, Transaction $transaction)
    {
        $validator = Validator::make($request->all(), [
            'need_delivery' => 'required|in:yes,no',
            'recipient_name' => 'required_if:need_delivery,yes|string|max:255',
            'recipient_phone' => 'nullable|string|max:20',
            'delivery_address' => 'required_if:need_delivery,yes|string|max:500',
            'vehicle_type' => 'required_if:need_delivery,yes|in:motor,mobil,truck',
            'estimated_weight' => 'nullable|numeric|min:0',
            'estimated_time' => 'nullable|integer|min:1',
            'delivery_fee' => 'nullable|numeric|min:0',
            'delivery_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->need_delivery === 'no') {
            return redirect()->route('transactions.show', $transaction)
                ->with('info', 'Transaksi disimpan tanpa pengiriman');
        }

        DB::beginTransaction();

        try {
            // Check if delivery already exists
            if ($transaction->delivery) {
                throw new \Exception('Transaksi ini sudah memiliki pengiriman');
            }

            // Calculate total items
            $totalItems = $transaction->items->sum('qty');

            // Set estimated delivery time
            $estimatedTime = null;
            if ($request->estimated_time) {
                $estimatedTime = now()->addDays($request->estimated_time);
            }

            // Create delivery
            $delivery = Delivery::create([
                'delivery_code' => Delivery::generateDeliveryCode(),
                'transaction_id' => $transaction->id,
                'origin' => $request->origin ?? 'Toko Roni Juntinyuat',
                'destination' => $request->delivery_address,
                'total_items' => $totalItems,
                'total_weight' => $request->estimated_weight ?? 0,
                'status' => 'pending',
                'estimated_delivery_time' => $estimatedTime,
                'notes' => $request->delivery_notes,
                'recipient_name' => $request->recipient_name,
                'recipient_phone' => $request->recipient_phone,
                'vehicle_type' => $request->vehicle_type,
                'delivery_fee' => $request->delivery_fee ?? 0,
                'delivery_address' => $request->delivery_address,
            ]);

            // Kirim notifikasi
            $this->sendDeliveryCreatedNotifications($delivery);

            DB::commit();

            Log::info('Delivery requested:', [
                'delivery_code' => $delivery->delivery_code,
                'transaction_id' => $transaction->id
            ]);

            return redirect()->route('delivery.show', $delivery)
                ->with('success', 'Permintaan pengiriman berhasil dibuat dengan kode: ' . $delivery->delivery_code);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery request failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal membuat permintaan pengiriman: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Quick update delivery status (for AJAX).
     */
    public function quickUpdate(Request $request, Delivery $delivery)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,assigned,picked_up,on_delivery,delivered,failed,cancelled',
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
            $oldStatus = $delivery->status;

            $data = [
                'status' => $request->status,
                'notes' => $request->notes ? $delivery->notes . "\n" . $request->notes : $delivery->notes,
            ];

            if ($request->status === 'delivered' && $oldStatus !== 'delivered') {
                $data['delivered_at'] = now();

                // Update vehicle status
                if ($delivery->vehicle) {
                    $delivery->vehicle->update(['status' => 'available']);
                }
            }

            if ($request->status === 'picked_up' && $oldStatus !== 'picked_up') {
                $data['pickup_time'] = now();
            }

            if ($request->status === 'on_delivery' && $oldStatus !== 'on_delivery') {
                $data['start_delivery_time'] = now();
            }

            if (in_array($request->status, ['assigned']) && !$delivery->user_id) {
                throw new \Exception('Driver belum ditugaskan');
            }

            $delivery->update($data);

            // Kirim notifikasi perubahan status
            if ($oldStatus != $request->status) {
                $this->sendDeliveryStatusChangedNotifications($delivery, $oldStatus, $request->status);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status pengiriman diperbarui',
                'delivery' => $delivery->fresh(['user', 'vehicle'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get delivery details for AJAX.
     */
    public function getDeliveryDetails(Delivery $delivery)
    {
        $delivery->load(['transaction', 'user', 'vehicle']);

        return response()->json([
            'success' => true,
            'delivery' => [
                'id' => $delivery->id,
                'code' => $delivery->delivery_code,
                'status' => $delivery->status,
                'status_badge' => $delivery->getStatusBadgeClass(),
                'status_icon' => $delivery->getStatusIcon(),
                'origin' => $delivery->origin,
                'destination' => $delivery->destination,
                'total_items' => $delivery->total_items,
                'total_weight' => $delivery->total_weight,
                'estimated_time' => $delivery->estimated_delivery_time ? $delivery->estimated_delivery_time->format('d/m/Y H:i') : null,
                'delivered_at' => $delivery->delivered_at ? $delivery->delivered_at->format('d/m/Y H:i') : null,
                'driver' => $delivery->user ? [
                    'name' => $delivery->user->name,
                    'phone' => $delivery->user->phone ?? '-'
                ] : null,
                'vehicle' => $delivery->vehicle ? [
                    'name' => $delivery->vehicle->name,
                    'plate' => $delivery->vehicle->license_plate,
                    'type' => $delivery->vehicle->type
                ] : null,
                'transaction' => [
                    'invoice' => $delivery->transaction->invoice_number,
                    'customer' => $delivery->transaction->customer_name,
                    'total' => $delivery->transaction->total_amount
                ],
                'notes' => $delivery->notes,
            ]
        ]);
    }

    /**
     * Get deliveries for current driver.
     */
    public function myDeliveries()
    {
        $deliveries = Delivery::with(['transaction', 'vehicle'])
            ->where('user_id', auth()->id())
            ->orderByRaw("FIELD(status, 'assigned', 'picked_up', 'on_delivery') DESC")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total' => $deliveries->total(),
            'assigned' => Delivery::where('user_id', auth()->id())->where('status', 'assigned')->count(),
            'on_delivery' => Delivery::where('user_id', auth()->id())->whereIn('status', ['picked_up', 'on_delivery'])->count(),
            'completed' => Delivery::where('user_id', auth()->id())->where('status', 'delivered')->count(),
        ];

        return view('delivery.my-deliveries', compact('deliveries', 'stats'));
    }

    /**
     * Update delivery status for driver.
     */
    public function updateStatus(Request $request, Delivery $delivery)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,on_delivery,delivered,failed,cancelled',
        ]);

        $oldStatus = $delivery->status;
        $delivery->update(['status' => $request->status]);

        // Kirim notifikasi perubahan status
        if ($oldStatus != $request->status) {
            $this->sendDeliveryStatusChangedNotifications($delivery, $oldStatus, $request->status);
        }

        return redirect()->back()->with('success', 'Status pengiriman berhasil diperbarui');
    }

    /**
     * Accept delivery (for courier).
     */
    public function acceptDelivery(Delivery $delivery)
    {
        DB::beginTransaction();

        try {
            if ($delivery->status !== 'assigned') {
                throw new \Exception('Pengiriman tidak dapat diterima');
            }

            if ($delivery->user_id !== auth()->id()) {
                throw new \Exception('Pengiriman ini tidak ditugaskan kepada Anda');
            }

            $oldStatus = $delivery->status;
            
            $delivery->update([
                'status' => 'picked_up',
                'pickup_time' => now(),
            ]);

            // Kirim notifikasi perubahan status
            $this->sendDeliveryStatusChangedNotifications($delivery, $oldStatus, 'picked_up');

            DB::commit();

            return redirect()->back()
                ->with('success', 'Pengiriman telah diterima');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Dashboard for delivery management.
     */
    public function dashboard()
    {
        $stats = [
            'total' => Delivery::count(),
            'pending' => Delivery::where('status', 'pending')->count(),
            'active' => Delivery::whereIn('status', ['assigned', 'picked_up', 'on_delivery'])->count(),
            'delivered_today' => Delivery::whereDate('delivered_at', today())->count(),
            'delivered_this_month' => Delivery::whereMonth('delivered_at', now()->month)->count(),
            'overdue' => Delivery::where('status', '!=', 'delivered')
                ->whereDate('estimated_delivery_time', '<', now())
                ->count(),
        ];

        $recentDeliveries = Delivery::with(['user', 'vehicle'])
            ->latest()
            ->limit(10)
            ->get();

        $chartData = [
            'labels' => ['Pending', 'Assigned', 'On Delivery', 'Delivered', 'Failed'],
            'data' => [
                Delivery::where('status', 'pending')->count(),
                Delivery::where('status', 'assigned')->count(),
                Delivery::whereIn('status', ['picked_up', 'on_delivery'])->count(),
                Delivery::where('status', 'delivered')->count(),
                Delivery::where('status', 'failed')->count(),
            ]
        ];

        return view('delivery.dashboard', compact('stats', 'recentDeliveries', 'chartData'));
    }

    /**
     * Staff/Dashboard for courier.
     */
    public function staffDashboard()
    {
        $myDeliveries = Delivery::where('user_id', auth()->id())
            ->with('transaction')
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'assigned' => Delivery::where('user_id', auth()->id())->where('status', 'assigned')->count(),
            'active' => Delivery::where('user_id', auth()->id())->whereIn('status', ['picked_up', 'on_delivery'])->count(),
            'completed' => Delivery::where('user_id', auth()->id())->where('status', 'delivered')->count(),
            'completed_today' => Delivery::where('user_id', auth()->id())
                ->whereDate('delivered_at', today())
                ->count(),
        ];

        return view('delivery.staff-dashboard', compact('myDeliveries', 'stats'));
    }

    /**
     * Store new courier (kurir).
     */
    public function storeKurir(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validasi gagal. Periksa kembali form Anda.');
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'logistik',
                'phone' => $request->phone,
                'address' => $request->address,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('delivery.index')
                ->with('success', 'Kurir ' . $user->name . ' berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menambah kurir: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menambahkan kurir: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Store new vehicle (kendaraan).
     */
    public function storeKendaraan(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate',
            'type' => 'required|in:motor,mobil,truck',
            'brand' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:2000|max:' . date('Y'),
            'status' => 'nullable|in:available,in_use,maintenance',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validasi gagal. Periksa kembali form Anda.');
        }

        DB::beginTransaction();

        try {
            // Buat kendaraan baru
            $vehicle = Vehicle::create([
                'name' => $request->name,
                'license_plate' => strtoupper($request->license_plate),
                'type' => $request->type,
                'brand' => $request->brand,
                'year' => $request->year,
                'status' => $request->status ?? 'available',
            ]);

            DB::commit();

            return redirect()->route('delivery.index')
                ->with('success', 'Kendaraan ' . $vehicle->name . ' (' . $vehicle->license_plate . ') berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menambah kendaraan: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menambahkan kendaraan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Print delivery note (surat jalan).
     */
    public function printDeliveryNote(Delivery $delivery)
    {
        $delivery->load(['transaction', 'user', 'vehicle']);

        return view('delivery.print-note', compact('delivery'));
    }

    /**
     * Print delivery receipt (resi).
     */
    public function printReceipt(Delivery $delivery)
    {
        $delivery->load(['transaction', 'user', 'vehicle']);

        return view('delivery.print-receipt', compact('delivery'));
    }

    /**
     * Print delivery note
     */
    public function printNote($id)
    {
        $delivery = Delivery::with(['user', 'vehicle', 'transaction'])->findOrFail($id);
        
        return view('delivery.print-note', compact('delivery'));
    }

    // ==================== NOTIFICATION METHODS ====================

    /**
     * Send notifications when delivery is created
     */
    private function sendDeliveryCreatedNotifications($delivery)
    {
        try {
            $currentUser = auth()->user();
            
            // 1. Kirim ke semua user dengan role owner
            $owners = User::where('role', 'owner')->get();
            
            foreach ($owners as $owner) {
                if ($owner->id != $currentUser->id) {
                    $owner->notify(new DeliveryCreatedNotification($delivery, $currentUser));
                    Log::info('Notifikasi delivery terkirim ke owner:', [
                        'owner_id' => $owner->id,
                        'delivery_id' => $delivery->id
                    ]);
                }
            }
            
            // 2. Kirim ke user dengan role logistik (untuk info)
            $logistik = User::where('role', 'logistik')->get();
            
            foreach ($logistik as $user) {
                if ($user->id != $currentUser->id) {
                    $user->notify(new DeliveryCreatedNotification($delivery, $currentUser));
                    Log::info('Notifikasi delivery terkirim ke logistik:', [
                        'user_id' => $user->id,
                        'delivery_id' => $delivery->id
                    ]);
                }
            }
            
            // 3. Kirim ke diri sendiri (pembuat)
            $currentUser->notify(new DeliveryCreatedNotification($delivery, $currentUser));
            Log::info('Notifikasi delivery terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'delivery_id' => $delivery->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi delivery: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when delivery is updated
     */
    private function sendDeliveryUpdatedNotifications($delivery, $changes)
    {
        try {
            $currentUser = auth()->user();
            
            // Kirim ke semua owner
            $owners = User::where('role', 'owner')->get();
            
            foreach ($owners as $owner) {
                if ($owner->id != $currentUser->id) {
                    $owner->notify(new DeliveryUpdatedNotification($delivery, $currentUser, $changes));
                }
            }
            
            // Kirim ke driver jika sudah ditugaskan
            if ($delivery->user_id) {
                $driver = User::find($delivery->user_id);
                if ($driver && $driver->id != $currentUser->id) {
                    $driver->notify(new DeliveryUpdatedNotification($delivery, $currentUser, $changes));
                }
            }
            
            // Kirim ke diri sendiri
            $currentUser->notify(new DeliveryUpdatedNotification($delivery, $currentUser, $changes));
            
            Log::info('Notifikasi update delivery terkirim', [
                'delivery_id' => $delivery->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi update delivery: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when delivery is assigned
     */
    private function sendDeliveryAssignedNotifications($delivery, $driver, $vehicle)
    {
        try {
            $currentUser = auth()->user();
            
            // 1. Kirim ke semua owner
            $owners = User::where('role', 'owner')->get();
            
            foreach ($owners as $owner) {
                if ($owner->id != $currentUser->id) {
                    $owner->notify(new DeliveryAssignedNotification($delivery, $currentUser, $driver, $vehicle));
                }
            }
            
            // 2. Kirim ke driver yang ditugaskan
            $driver->notify(new DeliveryAssignedNotification($delivery, $currentUser, $driver, $vehicle));
            
            // 3. Kirim ke diri sendiri
            $currentUser->notify(new DeliveryAssignedNotification($delivery, $currentUser, $driver, $vehicle));
            
            Log::info('Notifikasi assignment terkirim', [
                'delivery_id' => $delivery->id,
                'driver_id' => $driver->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi assignment: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when delivery status changes
     */
    private function sendDeliveryStatusChangedNotifications($delivery, $oldStatus, $newStatus)
    {
        try {
            $currentUser = auth()->user();
            
            // 1. Kirim ke semua owner
            $owners = User::where('role', 'owner')->get();
            
            foreach ($owners as $owner) {
                if ($owner->id != $currentUser->id) {
                    $owner->notify(new DeliveryStatusChangedNotification($delivery, $currentUser, $oldStatus, $newStatus));
                }
            }
            
            // 2. Kirim ke driver jika sudah ditugaskan
            if ($delivery->user_id) {
                $driver = User::find($delivery->user_id);
                if ($driver && $driver->id != $currentUser->id) {
                    $driver->notify(new DeliveryStatusChangedNotification($delivery, $currentUser, $oldStatus, $newStatus));
                }
            }
            
            // 3. Kirim ke user logistik
            $logistik = User::where('role', 'logistik')->get();
            
            foreach ($logistik as $user) {
                if ($user->id != $currentUser->id && $user->id != ($delivery->user_id ?? 0)) {
                    $user->notify(new DeliveryStatusChangedNotification($delivery, $currentUser, $oldStatus, $newStatus));
                }
            }
            
            // 4. Kirim ke diri sendiri
            $currentUser->notify(new DeliveryStatusChangedNotification($delivery, $currentUser, $oldStatus, $newStatus));
            
            Log::info('Notifikasi perubahan status delivery terkirim', [
                'delivery_id' => $delivery->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi perubahan status: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when delivery is deleted
     */
    private function sendDeliveryDeletedNotifications($deliveryCode, $deletedBy)
    {
        try {
            // Kirim ke semua owner
            $owners = User::where('role', 'owner')
                ->where('id', '!=', $deletedBy->id)
                ->get();
            
            foreach ($owners as $owner) {
                $owner->notify(new DeliveryDeletedNotification($deliveryCode, $deletedBy));
                Log::info('Notifikasi hapus delivery terkirim ke owner:', [
                    'owner_id' => $owner->id,
                    'delivery_code' => $deliveryCode
                ]);
            }
            
            // Kirim ke user logistik
            $logistik = User::where('role', 'logistik')
                ->where('id', '!=', $deletedBy->id)
                ->get();
            
            foreach ($logistik as $user) {
                $user->notify(new DeliveryDeletedNotification($deliveryCode, $deletedBy));
                Log::info('Notifikasi hapus delivery terkirim ke logistik:', [
                    'user_id' => $user->id,
                    'delivery_code' => $deliveryCode
                ]);
            }
            
            // Kirim ke diri sendiri
            $deletedBy->notify(new DeliveryDeletedNotification($deliveryCode, $deletedBy));
            Log::info('Notifikasi hapus delivery terkirim ke diri sendiri:', [
                'user_id' => $deletedBy->id,
                'delivery_code' => $deliveryCode
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi hapus delivery: ' . $e->getMessage());
        }
    }
}