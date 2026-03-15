<?php
// app/Http/Controllers/Api/DashboardApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\Member;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardApiController extends Controller
{
    /**
     * Get dashboard statistics based on user role
     */
    public function getDashboardStats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $today = now()->format('Y-m-d');
            $startOfMonth = now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = now()->endOfMonth()->format('Y-m-d');

            $data = [];

            // Base stats for all users
            $data['user'] = [
                'name' => $user->name,
                'role' => $user->role,
                'last_login' => $user->last_login_at ? $user->last_login_at->diffForHumans() : null,
            ];

            // Stats based on role
            switch ($user->role) {
                case 'owner':
                case 'admin':
                    $data = array_merge($data, $this->getOwnerStats($today, $startOfMonth, $endOfMonth));
                    break;

                case 'kasir':
                    $data = array_merge($data, $this->getKasirStats($today, $startOfMonth, $endOfMonth));
                    break;

                case 'kepala_gudang':
                case 'checker':
                    $data = array_merge($data, $this->getGudangStats());
                    break;

                case 'logistik':
                    $data = array_merge($data, $this->getLogistikStats($today));
                    break;

                case 'kurir':
                    $data = array_merge($data, $this->getKurirStats($user->id, $today));
                    break;

                default:
                    $data = array_merge($data, $this->getDefaultStats());
            }

            return response()->json([
                'success' => true,
                'message' => 'Dashboard stats retrieved successfully',
                'data' => $data,
                'meta' => [
                    'last_updated' => now()->format('Y-m-d H:i:s'),
                    'timezone' => config('app.timezone')
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Dashboard API Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get owner/admin dashboard stats
     */
    private function getOwnerStats($today, $startOfMonth, $endOfMonth): array
    {
        // Transaction stats
        $transactions = [
            'today' => [
                'count' => Transaction::whereDate('created_at', $today)->count(),
                'amount' => (float) Transaction::whereDate('created_at', $today)->sum('total_amount'),
                'profit' => (float) Transaction::whereDate('created_at', $today)->sum('profit') ?? 0,
            ],
            'this_month' => [
                'count' => Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                'amount' => (float) Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_amount'),
                'profit' => (float) Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('profit') ?? 0,
            ],
            'status' => [
                'pending' => Transaction::where('status', 'pending')->count(),
                'completed' => Transaction::where('status', 'completed')->count(),
                'cancelled' => Transaction::where('status', 'cancelled')->count(),
            ],
            'payment_methods' => Transaction::whereDate('created_at', $today)
                ->select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
                ->groupBy('payment_method')
                ->get(),
        ];

        // Delivery stats
        $deliveries = [
            'today' => Delivery::whereDate('created_at', $today)->count(),
            'pending' => Delivery::where('status', 'pending')->count(),
            'on_delivery' => Delivery::whereIn('status', ['assigned', 'picked_up', 'on_delivery'])->count(),
            'delivered_today' => Delivery::whereDate('delivered_at', $today)->count(),
            'overdue' => Delivery::where('status', '!=', 'delivered')
                ->whereDate('estimated_delivery_time', '<', now())
                ->count(),
        ];

        // Product stats
        $products = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'low_stock' => Product::where('stock', '<=', 10)->where('stock', '>', 0)->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)->count(),
            'total_value' => (float) Product::sum(DB::raw('price * stock')),
            'categories' => Product::select('category_id', DB::raw('count(*) as count'))
                ->with('category')
                ->groupBy('category_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->category->name ?? 'Uncategorized' => $item->count];
                }),
        ];

        // Member stats
        $members = [
            'total' => Member::count(),
            'active' => Member::where('is_active', true)->count(),
            'total_piutang' => (float) Member::sum('total_piutang'),
            'members_with_piutang' => Member::where('total_piutang', '>', 0)->count(),
            'by_type' => [
                'biasa' => Member::where('tipe_member', 'biasa')->count(),
                'gold' => Member::where('tipe_member', 'gold')->count(),
                'platinum' => Member::where('tipe_member', 'platinum')->count(),
            ],
        ];

        // Receivables stats
        $receivables = [
            'total' => (float) Receivable::sum('amount'),
            'paid' => (float) Receivable::where('status', 'lunas')->sum('amount'),
            'unpaid' => (float) Receivable::where('status', 'belum_lunas')->sum('amount'),
            'overdue' => Receivable::where('status', 'belum_lunas')
                ->whereDate('due_date', '<', now())
                ->count(),
        ];

        // User stats
        $users = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'online_now' => User::where('last_login_at', '>=', now()->subMinutes(5))->count(),
            'by_role' => User::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->get()
                ->pluck('count', 'role'),
        ];

        return [
            'transactions' => $transactions,
            'deliveries' => $deliveries,
            'products' => $products,
            'members' => $members,
            'receivables' => $receivables,
            'users' => $users,
        ];
    }

    /**
     * Get cashier dashboard stats
     */
    private function getKasirStats($today, $startOfMonth, $endOfMonth): array
    {
        return [
            'transactions' => [
                'today' => [
                    'count' => Transaction::whereDate('created_at', $today)->count(),
                    'amount' => (float) Transaction::whereDate('created_at', $today)->sum('total_amount'),
                ],
                'this_month' => [
                    'count' => Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                    'amount' => (float) Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_amount'),
                ],
            ],
            'products' => [
                'total' => Product::count(),
                'active' => Product::where('is_active', true)->count(),
            ],
            'members' => [
                'total' => Member::count(),
                'active' => Member::where('is_active', true)->count(),
            ],
        ];
    }

    /**
     * Get warehouse dashboard stats
     */
    private function getGudangStats(): array
    {
        return [
            'products' => [
                'total' => Product::count(),
                'active' => Product::where('is_active', true)->count(),
                'low_stock' => Product::where('stock', '<=', 10)->where('stock', '>', 0)->count(),
                'out_of_stock' => Product::where('stock', '<=', 0)->count(),
                'total_value' => (float) Product::sum(DB::raw('price * stock')),
                'categories' => Product::select('category_id', DB::raw('count(*) as count'))
                    ->with('category')
                    ->groupBy('category_id')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->category->name ?? 'Uncategorized' => $item->count];
                    }),
            ],
            'incoming_stock_today' => 0, // You can implement this based on your stock movement system
            'outgoing_stock_today' => 0,
        ];
    }

    /**
     * Get logistics dashboard stats
     */
    private function getLogistikStats($today): array
    {
        return [
            'deliveries' => [
                'today' => Delivery::whereDate('created_at', $today)->count(),
                'pending' => Delivery::where('status', 'pending')->count(),
                'assigned' => Delivery::where('status', 'assigned')->count(),
                'on_delivery' => Delivery::where('status', 'on_delivery')->count(),
                'delivered_today' => Delivery::whereDate('delivered_at', $today)->count(),
                'overdue' => Delivery::where('status', '!=', 'delivered')
                    ->whereDate('estimated_delivery_time', '<', now())
                    ->count(),
            ],
            'vehicles' => [
                'total' => \App\Models\Vehicle::count(),
                'available' => \App\Models\Vehicle::where('status', 'available')->count(),
                'in_use' => \App\Models\Vehicle::where('status', 'in_use')->count(),
                'maintenance' => \App\Models\Vehicle::where('status', 'maintenance')->count(),
            ],
            'drivers' => [
                'total' => User::where('role', 'kurir')->count(),
                'available' => User::where('role', 'kurir')
                    ->where('is_active', true)
                    ->whereDoesntHave('deliveries', function($q) {
                        $q->whereIn('status', ['assigned', 'picked_up', 'on_delivery']);
                    })->count(),
                'on_delivery' => User::where('role', 'kurir')
                    ->whereHas('deliveries', function($q) {
                        $q->whereIn('status', ['assigned', 'picked_up', 'on_delivery']);
                    })->count(),
            ],
        ];
    }

    /**
     * Get courier dashboard stats
     */
    private function getKurirStats($userId, $today): array
    {
        return [
            'my_deliveries' => [
                'total' => Delivery::where('user_id', $userId)->count(),
                'pending' => Delivery::where('user_id', $userId)->where('status', 'pending')->count(),
                'assigned' => Delivery::where('user_id', $userId)->where('status', 'assigned')->count(),
                'on_delivery' => Delivery::where('user_id', $userId)->where('status', 'on_delivery')->count(),
                'delivered_today' => Delivery::where('user_id', $userId)
                    ->whereDate('delivered_at', $today)
                    ->count(),
                'completed' => Delivery::where('user_id', $userId)->where('status', 'delivered')->count(),
            ],
            'next_delivery' => Delivery::where('user_id', $userId)
                ->whereIn('status', ['assigned', 'picked_up'])
                ->with(['transaction', 'destinationAddress'])
                ->orderBy('estimated_delivery_time')
                ->first(),
        ];
    }

    /**
     * Get default stats for other roles
     */
    private function getDefaultStats(): array
    {
        return [
            'message' => 'Welcome to Dashboard',
            'date' => now()->format('d F Y'),
        ];
    }

    /**
     * Get chart data for dashboard
     */
    public function getChartData(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', 'week'); // week, month, year
            $type = $request->get('type', 'all'); // transactions, deliveries, revenue, all
            $chartData = [];

            switch ($period) {
                case 'week':
                    $chartData = $this->getWeeklyChartData($type);
                    break;
                case 'month':
                    $chartData = $this->getMonthlyChartData($type);
                    break;
                case 'year':
                    $chartData = $this->getYearlyChartData($type);
                    break;
                default:
                    $chartData = $this->getWeeklyChartData($type);
            }

            return response()->json([
                'success' => true,
                'message' => 'Chart data retrieved successfully',
                'data' => $chartData,
                'meta' => [
                    'period' => $period,
                    'type' => $type,
                    'generated_at' => now()->format('Y-m-d H:i:s')
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Chart data error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve chart data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get weekly chart data
     */
    private function getWeeklyChartData($type): array
    {
        $labels = [];
        $transactions = [];
        $revenue = [];
        $deliveries = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');

            $labels[] = $date->format('D');

            if ($type == 'all' || $type == 'transactions') {
                $transactions[] = Transaction::whereDate('created_at', $dateStr)->count();
            }

            if ($type == 'all' || $type == 'revenue') {
                $revenue[] = (float) Transaction::whereDate('created_at', $dateStr)->sum('total_amount');
            }

            if ($type == 'all' || $type == 'deliveries') {
                $deliveries[] = Delivery::whereDate('created_at', $dateStr)->count();
            }
        }

        $result = ['labels' => $labels];

        if ($type == 'all' || $type == 'transactions') {
            $result['datasets'][] = [
                'label' => 'Transactions',
                'data' => $transactions,
                'borderColor' => 'rgb(59, 130, 246)',
                'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
            ];
        }

        if ($type == 'all' || $type == 'revenue') {
            $result['datasets'][] = [
                'label' => 'Revenue',
                'data' => $revenue,
                'borderColor' => 'rgb(16, 185, 129)',
                'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
            ];
        }

        if ($type == 'all' || $type == 'deliveries') {
            $result['datasets'][] = [
                'label' => 'Deliveries',
                'data' => $deliveries,
                'borderColor' => 'rgb(245, 158, 11)',
                'backgroundColor' => 'rgba(245, 158, 11, 0.5)',
            ];
        }

        return $result;
    }

    /**
     * Get monthly chart data
     */
    private function getMonthlyChartData($type): array
    {
        $labels = [];
        $transactions = [];
        $revenue = [];
        $deliveries = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');

            // Show label every 5 days to avoid clutter
            if ($i % 5 == 0) {
                $labels[] = $date->format('d M');
            } else {
                $labels[] = '';
            }

            if ($type == 'all' || $type == 'transactions') {
                $transactions[] = Transaction::whereDate('created_at', $dateStr)->count();
            }

            if ($type == 'all' || $type == 'revenue') {
                $revenue[] = (float) Transaction::whereDate('created_at', $dateStr)->sum('total_amount');
            }

            if ($type == 'all' || $type == 'deliveries') {
                $deliveries[] = Delivery::whereDate('created_at', $dateStr)->count();
            }
        }

        $result = ['labels' => $labels];

        if ($type == 'all' || $type == 'transactions') {
            $result['datasets'][] = [
                'label' => 'Transactions',
                'data' => $transactions,
                'borderColor' => 'rgb(59, 130, 246)',
                'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
            ];
        }

        if ($type == 'all' || $type == 'revenue') {
            $result['datasets'][] = [
                'label' => 'Revenue',
                'data' => $revenue,
                'borderColor' => 'rgb(16, 185, 129)',
                'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
            ];
        }

        if ($type == 'all' || $type == 'deliveries') {
            $result['datasets'][] = [
                'label' => 'Deliveries',
                'data' => $deliveries,
                'borderColor' => 'rgb(245, 158, 11)',
                'backgroundColor' => 'rgba(245, 158, 11, 0.5)',
            ];
        }

        return $result;
    }

    /**
     * Get yearly chart data
     */
    private function getYearlyChartData($type): array
    {
        $labels = [];
        $transactions = [];
        $revenue = [];
        $deliveries = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth()->format('Y-m-d');
            $endOfMonth = $month->copy()->endOfMonth()->format('Y-m-d');

            $labels[] = $month->format('M Y');

            if ($type == 'all' || $type == 'transactions') {
                $transactions[] = Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            }

            if ($type == 'all' || $type == 'revenue') {
                $revenue[] = (float) Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_amount');
            }

            if ($type == 'all' || $type == 'deliveries') {
                $deliveries[] = Delivery::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            }
        }

        $result = ['labels' => $labels];

        if ($type == 'all' || $type == 'transactions') {
            $result['datasets'][] = [
                'label' => 'Transactions',
                'data' => $transactions,
                'borderColor' => 'rgb(59, 130, 246)',
                'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
            ];
        }

        if ($type == 'all' || $type == 'revenue') {
            $result['datasets'][] = [
                'label' => 'Revenue',
                'data' => $revenue,
                'borderColor' => 'rgb(16, 185, 129)',
                'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
            ];
        }

        if ($type == 'all' || $type == 'deliveries') {
            $result['datasets'][] = [
                'label' => 'Deliveries',
                'data' => $deliveries,
                'borderColor' => 'rgb(245, 158, 11)',
                'backgroundColor' => 'rgba(245, 158, 11, 0.5)',
            ];
        }

        return $result;
    }

    /**
     * Get notifications for dashboard
     */
    public function getNotifications(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Get unread notifications
            $unreadNotifications = $user->unreadNotifications()
                ->limit(20)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $this->formatNotificationType($notification->type),
                        'data' => $notification->data,
                        'message' => $this->formatNotificationMessage($notification),
                        'created_at' => $notification->created_at->diffForHumans(),
                        'created_at_raw' => $notification->created_at->format('Y-m-d H:i:s'),
                        'read_at' => $notification->read_at,
                        'is_read' => false,
                    ];
                });

            // Get recent read notifications
            $recentRead = $user->notifications()
                ->whereNotNull('read_at')
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $this->formatNotificationType($notification->type),
                        'data' => $notification->data,
                        'message' => $this->formatNotificationMessage($notification),
                        'created_at' => $notification->created_at->diffForHumans(),
                        'created_at_raw' => $notification->created_at->format('Y-m-d H:i:s'),
                        'read_at' => $notification->read_at->diffForHumans(),
                        'is_read' => true,
                    ];
                });

            // Get alerts based on user role
            $alerts = $this->getRoleBasedAlerts($user);

            return response()->json([
                'success' => true,
                'message' => 'Notifications retrieved successfully',
                'data' => [
                    'unread' => [
                        'items' => $unreadNotifications,
                        'count' => $user->unreadNotifications->count(),
                        'total' => $user->notifications()->count(),
                    ],
                    'recent_read' => $recentRead,
                    'alerts' => $alerts,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Notifications error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notifications',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Format notification type for display
     */
    private function formatNotificationType($type): string
    {
        $type = class_basename($type);
        $type = str_replace(['Notification', 'notification'], '', $type);
        return preg_replace('/(?<!^)[A-Z]/', ' $0', $type);
    }

    /**
     * Format notification message
     */
    private function formatNotificationMessage($notification): string
    {
        $data = $notification->data;

        if (isset($data['message'])) {
            return $data['message'];
        }

        if (isset($data['title'])) {
            return $data['title'];
        }

        return 'New notification';
    }

    /**
     * Get role-based alerts
     */
    private function getRoleBasedAlerts($user): array
    {
        $alerts = [];

        switch ($user->role) {
            case 'owner':
            case 'admin':
                $alerts['low_stock'] = Product::where('stock', '<=', 10)
                    ->where('stock', '>', 0)
                    ->count();
                $alerts['overdue_deliveries'] = Delivery::where('status', '!=', 'delivered')
                    ->whereDate('estimated_delivery_time', '<', now())
                    ->count();
                $alerts['unpaid_receivables'] = Receivable::where('status', 'belum_lunas')
                    ->whereDate('due_date', '<', now())
                    ->count();
                break;

            case 'kepala_gudang':
            case 'checker':
                $alerts['low_stock'] = Product::where('stock', '<=', 10)
                    ->where('stock', '>', 0)
                    ->count();
                $alerts['out_of_stock'] = Product::where('stock', '<=', 0)->count();
                break;

            case 'logistik':
                $alerts['pending_deliveries'] = Delivery::where('status', 'pending')->count();
                $alerts['unassigned_deliveries'] = Delivery::whereNull('user_id')
                    ->where('status', 'pending')
                    ->count();
                $alerts['vehicles_maintenance'] = \App\Models\Vehicle::where('status', 'maintenance')->count();
                break;

            case 'kurir':
                $alerts['new_assignments'] = Delivery::where('user_id', $user->id)
                    ->where('status', 'assigned')
                    ->count();
                break;
        }

        return $alerts;
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $notification = $user->notifications()->where('id', $id)->first();

            if ($notification) {
                $notification->markAsRead();
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Mark notification error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->unreadNotifications->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Mark all notifications error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read',
            ], 500);
        }
    }

    /**
     * Get owner dashboard data
     */
    public function ownerDashboard(Request $request): JsonResponse
    {
        try {
            $today = now()->format('Y-m-d');
            $startOfMonth = now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = now()->endOfMonth()->format('Y-m-d');

            $data = [
                'summary' => [
                    'today_sales' => (float) Transaction::whereDate('created_at', $today)->sum('total_amount'),
                    'month_sales' => (float) Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_amount'),
                    'total_members' => Member::count(),
                    'total_products' => Product::count(),
                    'pending_deliveries' => Delivery::where('status', 'pending')->count(),
                    'total_receivables' => (float) Receivable::where('status', 'belum_lunas')->sum('amount'),
                ],
                'recent_transactions' => Transaction::with(['user', 'member'])
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(function ($transaction) {
                        return [
                            'id' => $transaction->id,
                            'invoice' => $transaction->invoice_number,
                            'amount' => $transaction->total_amount,
                            'status' => $transaction->status,
                            'cashier' => $transaction->user->name,
                            'member' => $transaction->member->nama ?? 'Umum',
                            'created_at' => $transaction->created_at->diffForHumans(),
                        ];
                    }),
                'recent_deliveries' => Delivery::with(['user', 'transaction'])
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(function ($delivery) {
                        return [
                            'id' => $delivery->id,
                            'code' => $delivery->delivery_code,
                            'destination' => $delivery->destination,
                            'status' => $delivery->status,
                            'driver' => $delivery->user->name ?? 'Not assigned',
                            'estimated' => $delivery->estimated_delivery_time ? $delivery->estimated_delivery_time->format('d/m/Y') : null,
                        ];
                    }),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            Log::error('Owner dashboard error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get owner dashboard data',
            ], 500);
        }
    }

    /**
     * Get cashier dashboard data
     */
    public function kasirDashboard(Request $request): JsonResponse
    {
        try {
            $today = now()->format('Y-m-d');
            $user = $request->user();

            $data = [
                'today' => [
                    'transactions' => Transaction::whereDate('created_at', $today)->count(),
                    'amount' => (float) Transaction::whereDate('created_at', $today)->sum('total_amount'),
                    'my_transactions' => Transaction::where('user_id', $user->id)
                        ->whereDate('created_at', $today)
                        ->count(),
                ],
                'recent_transactions' => Transaction::with(['member'])
                    ->where('user_id', $user->id)
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(function ($transaction) {
                        return [
                            'id' => $transaction->id,
                            'invoice' => $transaction->invoice_number,
                            'amount' => $transaction->total_amount,
                            'member' => $transaction->member->nama ?? 'Umum',
                            'created_at' => $transaction->created_at->format('H:i'),
                        ];
                    }),
                'quick_products' => Product::where('is_active', true)
                    ->where('stock', '>', 0)
                    ->orderBy('sold_count', 'desc')
                    ->limit(20)
                    ->get(['id', 'name', 'price', 'stock']),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            Log::error('Kasir dashboard error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get kasir dashboard data',
            ], 500);
        }
    }

    /**
     * Get warehouse dashboard data
     */
    public function gudangDashboard(Request $request): JsonResponse
    {
        try {
            $data = [
                'stock_summary' => [
                    'total_products' => Product::count(),
                    'total_stock' => Product::sum('stock'),
                    'total_value' => (float) Product::sum(DB::raw('price * stock')),
                    'low_stock' => Product::where('stock', '<=', 10)->where('stock', '>', 0)->count(),
                    'out_of_stock' => Product::where('stock', '<=', 0)->count(),
                ],
                'low_stock_products' => Product::where('stock', '<=', 10)
                    ->where('stock', '>', 0)
                    ->limit(20)
                    ->get(['id', 'name', 'stock', 'unit']),
                'out_of_stock_products' => Product::where('stock', '<=', 0)
                    ->limit(10)
                    ->get(['id', 'name']),
                'categories' => Product::select('category_id', DB::raw('count(*) as total'))
                    ->with('category')
                    ->groupBy('category_id')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'category' => $item->category->name ?? 'Uncategorized',
                            'total' => $item->total,
                        ];
                    }),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            Log::error('Gudang dashboard error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get warehouse dashboard data',
            ], 500);
        }
    }

    /**
     * Get logistics dashboard data
     */
    public function logistikDashboard(Request $request): JsonResponse
    {
        try {
            $today = now()->format('Y-m-d');

            $data = [
                'delivery_summary' => [
                    'total' => Delivery::count(),
                    'pending' => Delivery::where('status', 'pending')->count(),
                    'assigned' => Delivery::where('status', 'assigned')->count(),
                    'on_delivery' => Delivery::whereIn('status', ['picked_up', 'on_delivery'])->count(),
                    'delivered_today' => Delivery::whereDate('delivered_at', $today)->count(),
                    'overdue' => Delivery::where('status', '!=', 'delivered')
                        ->whereDate('estimated_delivery_time', '<', now())
                        ->count(),
                ],
                'vehicle_summary' => [
                    'total' => \App\Models\Vehicle::count(),
                    'available' => \App\Models\Vehicle::where('status', 'available')->count(),
                    'in_use' => \App\Models\Vehicle::where('status', 'in_use')->count(),
                    'maintenance' => \App\Models\Vehicle::where('status', 'maintenance')->count(),
                ],
                'driver_summary' => [
                    'total' => User::where('role', 'kurir')->count(),
                    'available' => User::where('role', 'kurir')
                        ->where('is_active', true)
                        ->whereDoesntHave('deliveries', function($q) {
                            $q->whereIn('status', ['assigned', 'picked_up', 'on_delivery']);
                        })->count(),
                    'on_duty' => User::where('role', 'kurir')
                        ->whereHas('deliveries', function($q) {
                            $q->whereIn('status', ['assigned', 'picked_up', 'on_delivery']);
                        })->count(),
                ],
                'pending_deliveries' => Delivery::where('status', 'pending')
                    ->with(['transaction', 'destinationAddress'])
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(function ($delivery) {
                        return [
                            'id' => $delivery->id,
                            'code' => $delivery->delivery_code,
                            'destination' => $delivery->destination,
                            'estimated' => $delivery->estimated_delivery_time ? $delivery->estimated_delivery_time->format('d/m/Y') : null,
                            'customer' => $delivery->transaction->member->nama ?? $delivery->recipient_name,
                        ];
                    }),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            Log::error('Logistik dashboard error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get logistics dashboard data',
            ], 500);
        }
    }

    /**
     * Get courier dashboard data
     */
    public function kurirDashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $today = now()->format('Y-m-d');

            $data = [
                'summary' => [
                    'assigned_today' => Delivery::where('user_id', $user->id)
                        ->whereDate('assigned_at', $today)
                        ->count(),
                    'completed_today' => Delivery::where('user_id', $user->id)
                        ->whereDate('delivered_at', $today)
                        ->count(),
                    'pending' => Delivery::where('user_id', $user->id)
                        ->whereIn('status', ['assigned', 'picked_up'])
                        ->count(),
                    'total_completed' => Delivery::where('user_id', $user->id)
                        ->where('status', 'delivered')
                        ->count(),
                ],
                'current_delivery' => Delivery::where('user_id', $user->id)
                    ->whereIn('status', ['assigned', 'picked_up', 'on_delivery'])
                    ->with(['transaction', 'destinationAddress', 'transaction.member'])
                    ->orderBy('estimated_delivery_time')
                    ->first(),
                'delivery_history' => Delivery::where('user_id', $user->id)
                    ->where('status', 'delivered')
                    ->latest('delivered_at')
                    ->limit(10)
                    ->get()
                    ->map(function ($delivery) {
                        return [
                            'id' => $delivery->id,
                            'code' => $delivery->delivery_code,
                            'destination' => $delivery->destination,
                            'delivered_at' => $delivery->delivered_at->format('d/m/Y H:i'),
                            'recipient' => $delivery->recipient_name,
                        ];
                    }),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            Log::error('Kurir dashboard error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get courier dashboard data',
            ], 500);
        }
    }
}
