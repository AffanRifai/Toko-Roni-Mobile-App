<?php

use Illuminate\Support\Facades\Route;
use app\Http\Controllers\api\AuthApiController;
use app\Http\Controllers\api\DashboardApiController;
use app\Http\Controllers\api\TransactionApiController;
use app\Http\Controllers\api\DeliveryApiController;
use app\Http\Controllers\api\VehicleApiController;
use app\Http\Controllers\api\MemberApiController;
use app\Http\Controllers\api\ProductApiController;
use app\Http\Controllers\api\ReportApiController;
use app\Http\Controllers\api\UserApiController;
use app\Http\Controllers\api\NotificationApiController;
use app\Http\Controllers\api\CategoryApiController;
use app\Http\Controllers\api\ReceivableApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// =========================================================================
// API V1 ROUTES
// =========================================================================
Route::prefix('v1')->name('api.')->group(function () {

    // =========================================================================
    // PUBLIC API (No Authentication Required)
    // =========================================================================
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/login', [AuthApiController::class, 'login'])->name('login');
        Route::post('/face-login', [AuthApiController::class, 'faceLogin'])->name('face-login');
    });

    // Public tracking - NO AUTH REQUIRED
    Route::get('/delivery/track/{code}', [DeliveryApiController::class, 'trackDelivery'])
        ->name('delivery.track');

    // Public face endpoints
    Route::prefix('face')->name('face.')->group(function () {
        Route::get('/registered', [AuthApiController::class, 'getRegisteredFaces'])
            ->name('registered');
        Route::get('/status', [AuthApiController::class, 'faceStatus'])
            ->name('status');
    });

    // =========================================================================
    // PROTECTED API (Sanctum Authentication Required)
    // =========================================================================
    Route::middleware(['auth:sanctum'])->group(function () {

        // ---------------------------------------------------------------------
        // AUTH & PROFILE
        // ---------------------------------------------------------------------
        Route::prefix('auth')->name('auth.')->group(function () {
            Route::post('/logout', [AuthApiController::class, 'logout'])->name('logout');
            Route::get('/profile', [AuthApiController::class, 'profile'])->name('profile');
            Route::put('/profile', [AuthApiController::class, 'updateProfile'])->name('profile.update');
            Route::post('/change-password', [AuthApiController::class, 'changePassword'])->name('change-password');
        });

        // ---------------------------------------------------------------------
        // DASHBOARD
        // ---------------------------------------------------------------------
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/stats', [DashboardApiController::class, 'getDashboardStats'])->name('stats');
            Route::get('/chart-data', [DashboardApiController::class, 'getChartData'])->name('chart-data');

            // Role-specific dashboards
            Route::get('/owner', [DashboardApiController::class, 'ownerDashboard'])->name('owner');
            Route::get('/kasir', [DashboardApiController::class, 'kasirDashboard'])->name('kasir');
            Route::get('/gudang', [DashboardApiController::class, 'gudangDashboard'])->name('gudang');
            Route::get('/logistik', [DashboardApiController::class, 'logistikDashboard'])->name('logistik');
            Route::get('/kurir', [DashboardApiController::class, 'kurirDashboard'])->name('kurir');
        });

        // ---------------------------------------------------------------------
        // NOTIFICATIONS
        // ---------------------------------------------------------------------
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationApiController::class, 'index'])->name('index');
            Route::get('/unread', [NotificationApiController::class, 'unread'])->name('unread');
            Route::post('/{id}/read', [NotificationApiController::class, 'markAsRead'])->name('read');
            Route::post('/read-all', [NotificationApiController::class, 'markAllAsRead'])->name('read-all');
            Route::delete('/{id}', [NotificationApiController::class, 'destroy'])->name('destroy');
            Route::delete('/clear/all', [NotificationApiController::class, 'clearAll'])->name('clear-all');
        });

        // ---------------------------------------------------------------------
        // MEMBERS API
        // ---------------------------------------------------------------------
        Route::prefix('members')->name('members.')->group(function () {
            // Search endpoint harus di atas resource route
            Route::get('/search', [MemberApiController::class, 'search'])->name('search');

            // Resource routes
            Route::get('/', [MemberApiController::class, 'index'])->name('index');
            Route::post('/', [MemberApiController::class, 'store'])->name('store');
            Route::get('/{member}', [MemberApiController::class, 'show'])->name('show');
            Route::put('/{member}', [MemberApiController::class, 'update'])->name('update');
            Route::delete('/{member}', [MemberApiController::class, 'destroy'])->name('destroy');

            // Member specific data
            Route::get('/{member}/data', [MemberApiController::class, 'getMemberData'])->name('get-data');
            Route::get('/{member}/receivables', [MemberApiController::class, 'receivables'])->name('receivables');
            Route::get('/{member}/transactions', [MemberApiController::class, 'transactions'])->name('transactions');
            Route::get('/{member}/payment-history', [MemberApiController::class, 'paymentHistory'])->name('payment-history');

            // Member actions
            Route::post('/{member}/toggle-status', [MemberApiController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{member}/update-limit', [MemberApiController::class, 'updateLimit'])->name('update-limit');

            // Statistics
            Route::get('/statistics/overview', [MemberApiController::class, 'getStatistics'])->name('statistics');
            Route::get('/statistics/top', [MemberApiController::class, 'getTopMembers'])->name('top-members');

            // Export
            Route::get('/export/csv', [MemberApiController::class, 'export'])->name('export');
        });

        // ---------------------------------------------------------------------
        // PRODUCTS API
        // ---------------------------------------------------------------------
        Route::prefix('products')->name('products.')->group(function () {
            // Custom endpoints
            Route::get('/search', [ProductApiController::class, 'search'])->name('search');
            Route::get('/low-stock', [ProductApiController::class, 'lowStock'])->name('low-stock');
            Route::get('/categories', [ProductApiController::class, 'categories'])->name('categories');

            // Resource routes
            Route::get('/', [ProductApiController::class, 'index'])->name('index');
            Route::post('/', [ProductApiController::class, 'store'])->name('store');
            Route::get('/{product}', [ProductApiController::class, 'show'])->name('show');
            Route::put('/{product}', [ProductApiController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductApiController::class, 'destroy'])->name('destroy');

            // Product actions
            Route::post('/{product}/update-stock', [ProductApiController::class, 'updateStock'])->name('update-stock');
            Route::post('/{product}/toggle-active', [ProductApiController::class, 'toggleActive'])->name('toggle-active');

            // Statistics
            Route::get('/statistics/overview', [ProductApiController::class, 'getStatistics'])->name('statistics');

            // Export
            Route::get('/export/csv', [ProductApiController::class, 'export'])->name('export');
        });

        // ---------------------------------------------------------------------
        // CATEGORIES API
        // ---------------------------------------------------------------------
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoryApiController::class, 'index'])->name('index');
            Route::post('/', [CategoryApiController::class, 'store'])->name('store');
            Route::get('/{category}', [CategoryApiController::class, 'show'])->name('show');
            Route::put('/{category}', [CategoryApiController::class, 'update'])->name('update');
            Route::delete('/{category}', [CategoryApiController::class, 'destroy'])->name('destroy');

            Route::get('/{category}/products', [CategoryApiController::class, 'products'])->name('products');
        });

        // ---------------------------------------------------------------------
        // TRANSACTIONS API
        // ---------------------------------------------------------------------
        Route::prefix('transactions')->name('transactions.')->group(function () {
            // Custom endpoints
            Route::get('/recent', [TransactionApiController::class, 'recent'])->name('recent');
            Route::get('/today-stats', [TransactionApiController::class, 'todayStats'])->name('today-stats');
            Route::get('/statistics', [TransactionApiController::class, 'getStatistics'])->name('statistics');

            // Resource routes
            Route::get('/', [TransactionApiController::class, 'index'])->name('index');
            Route::post('/', [TransactionApiController::class, 'store'])->name('store');
            Route::get('/{transaction}', [TransactionApiController::class, 'show'])->name('show');
            Route::put('/{transaction}', [TransactionApiController::class, 'update'])->name('update');
            Route::delete('/{transaction}', [TransactionApiController::class, 'destroy'])->name('destroy');

            // Transaction actions
            Route::post('/{transaction}/complete', [TransactionApiController::class, 'complete'])->name('complete');
            Route::post('/{transaction}/cancel', [TransactionApiController::class, 'cancel'])->name('cancel');
            Route::get('/{transaction}/items', [TransactionApiController::class, 'getItems'])->name('items');
            Route::get('/{transaction}/receipt', [TransactionApiController::class, 'getReceipt'])->name('receipt');

            // Export
            Route::get('/export/csv', [TransactionApiController::class, 'export'])->name('export');
        });

        // ---------------------------------------------------------------------
        // RECEIVABLES API (Piutang)
        // ---------------------------------------------------------------------
        Route::prefix('receivables')->name('receivables.')->group(function () {
            Route::get('/', [ReceivableApiController::class, 'index'])->name('index');
            Route::get('/{receivable}', [ReceivableApiController::class, 'show'])->name('show');
            Route::post('/{receivable}/pay', [ReceivableApiController::class, 'pay'])->name('pay');
            Route::get('/{receivable}/payment-history', [ReceivableApiController::class, 'paymentHistory'])->name('payment-history');
            Route::get('/statistics/overview', [ReceivableApiController::class, 'getStatistics'])->name('statistics');
        });

        // ---------------------------------------------------------------------
        // DELIVERIES API
        // ---------------------------------------------------------------------
        Route::prefix('deliveries')->name('deliveries.')->group(function () {
            // Custom endpoints
            Route::get('/today-stats', [DeliveryApiController::class, 'todayStats'])->name('today-stats');
            Route::get('/my-deliveries', [DeliveryApiController::class, 'myDeliveries'])->name('my-deliveries');
            Route::get('/available-drivers', [DeliveryApiController::class, 'availableDrivers'])->name('available-drivers');
            Route::get('/available-vehicles', [DeliveryApiController::class, 'availableVehicles'])->name('available-vehicles');

            // Resource routes
            Route::get('/', [DeliveryApiController::class, 'index'])->name('index');
            Route::post('/', [DeliveryApiController::class, 'store'])->name('store');
            Route::get('/{delivery}', [DeliveryApiController::class, 'show'])->name('show');
            Route::put('/{delivery}', [DeliveryApiController::class, 'update'])->name('update');
            Route::delete('/{delivery}', [DeliveryApiController::class, 'destroy'])->name('destroy');

            // Delivery actions
            Route::post('/{delivery}/assign', [DeliveryApiController::class, 'assign'])->name('assign');
            Route::post('/{delivery}/pickup', [DeliveryApiController::class, 'pickup'])->name('pickup');
            Route::post('/{delivery}/start', [DeliveryApiController::class, 'start'])->name('start');
            Route::post('/{delivery}/complete', [DeliveryApiController::class, 'complete'])->name('complete');
            Route::post('/{delivery}/cancel', [DeliveryApiController::class, 'cancel'])->name('cancel');
            Route::put('/{delivery}/status', [DeliveryApiController::class, 'updateStatus'])->name('update-status');

            // Statistics
            Route::get('/statistics/overview', [DeliveryApiController::class, 'getStatistics'])->name('statistics');

            // Export
            Route::get('/export/csv', [DeliveryApiController::class, 'export'])->name('export');
        });

        // ---------------------------------------------------------------------
        // VEHICLES API
        // ---------------------------------------------------------------------
        Route::prefix('vehicles')->name('vehicles.')->group(function () {
            // Custom endpoints
            Route::get('/available', [VehicleApiController::class, 'available'])->name('available');
            Route::get('/in-maintenance', [VehicleApiController::class, 'inMaintenance'])->name('in-maintenance');

            // Resource routes
            Route::get('/', [VehicleApiController::class, 'index'])->name('index');
            Route::post('/', [VehicleApiController::class, 'store'])->name('store');
            Route::get('/{vehicle}', [VehicleApiController::class, 'show'])->name('show');
            Route::put('/{vehicle}', [VehicleApiController::class, 'update'])->name('update');
            Route::delete('/{vehicle}', [VehicleApiController::class, 'destroy'])->name('destroy');

            // Vehicle actions
            Route::post('/{vehicle}/maintenance', [VehicleApiController::class, 'setMaintenance'])->name('maintenance');
            Route::post('/{vehicle}/available', [VehicleApiController::class, 'setAvailable'])->name('set-available');

            // Statistics
            Route::get('/statistics/overview', [VehicleApiController::class, 'getStatistics'])->name('statistics');

            // Export
            Route::get('/export/csv', [VehicleApiController::class, 'export'])->name('export');
        });

        // ---------------------------------------------------------------------
        // REPORTS API
        // ---------------------------------------------------------------------
        Route::prefix('reports')->name('reports.')->group(function () {
            // Sales reports
            Route::get('/sales/summary', [ReportApiController::class, 'salesSummary'])->name('sales.summary');
            Route::get('/sales/daily', [ReportApiController::class, 'dailySales'])->name('sales.daily');
            Route::get('/sales/monthly', [ReportApiController::class, 'monthlySales'])->name('sales.monthly');
            Route::get('/sales/yearly', [ReportApiController::class, 'yearlySales'])->name('sales.yearly');
            Route::get('/sales/by-payment', [ReportApiController::class, 'salesByPayment'])->name('sales.by-payment');

            // Product reports
            Route::get('/products/best-selling', [ReportApiController::class, 'bestSelling'])->name('products.best-selling');
            Route::get('/products/stock', [ReportApiController::class, 'stockReport'])->name('products.stock');

            // Member reports
            Route::get('/members/top-spenders', [ReportApiController::class, 'topSpenders'])->name('members.top-spenders');
            Route::get('/members/piutang', [ReportApiController::class, 'piutangReport'])->name('members.piutang');

            // Delivery reports
            Route::get('/delivery/performance', [ReportApiController::class, 'deliveryPerformance'])->name('delivery.performance');

            // Export reports
            Route::get('/export/pdf', [ReportApiController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/excel', [ReportApiController::class, 'exportExcel'])->name('export.excel');
        });

        // ---------------------------------------------------------------------
        // USERS API (OWNER/ADMIN ONLY)
        // ---------------------------------------------------------------------
        Route::prefix('users')->name('users.')->middleware('role:owner,admin')->group(function () {
            // Face registration
            Route::get('/face-registration', [UserApiController::class, 'faceRegistration'])->name('face.registration');
            Route::post('/faces/register', [UserApiController::class, 'registerFace'])->name('faces.register');
            Route::delete('/faces/{id}', [UserApiController::class, 'deleteFace'])->name('faces.delete');

            // User CRUD
            Route::get('/', [UserApiController::class, 'index'])->name('index');
            Route::post('/', [UserApiController::class, 'store'])->name('store');
            Route::get('/{user}', [UserApiController::class, 'show'])->name('show');
            Route::put('/{user}', [UserApiController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserApiController::class, 'destroy'])->name('destroy');

            // User actions
            Route::post('/{user}/toggle-active', [UserApiController::class, 'toggleActive'])->name('toggle-active');
            Route::post('/{user}/change-role', [UserApiController::class, 'changeRole'])->name('change-role');
            Route::post('/{user}/reset-password', [UserApiController::class, 'resetPassword'])->name('reset-password');

            // Statistics
            Route::get('/statistics/overview', [UserApiController::class, 'getStatistics'])->name('statistics');

            // Export
            Route::get('/export/csv', [UserApiController::class, 'export'])->name('export');
        });

        // ---------------------------------------------------------------------
        // UTILITY ENDPOINTS
        // ---------------------------------------------------------------------
        Route::prefix('utils')->name('utils.')->group(function () {
            Route::get('/ping', function() {
                return response()->json([
                    'success' => true,
                    'message' => 'pong',
                    'timestamp' => now()->toIso8601String(),
                    'user' => auth()->user()->name ?? 'Guest'
                ]);
            })->name('ping');

            Route::get('/server-time', function() {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'datetime' => now()->toDateTimeString(),
                        'timestamp' => now()->timestamp,
                        'timezone' => config('app.timezone'),
                        'formatted' => now()->format('Y-m-d H:i:s')
                    ]
                ]);
            })->name('server-time');
        });

    }); // End authenticated routes

}); // End v1 prefix
