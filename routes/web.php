<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DeliveryReportController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| ROOT ROUTE
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| PUBLIC FACE RECOGNITION ROUTES
|--------------------------------------------------------------------------
*/
Route::post('/face-login', [AuthenticatedSessionController::class, 'faceLogin'])->name('face.login.direct');
Route::post('/face-register', [AuthenticatedSessionController::class, 'faceRegister'])->name('face.register.direct');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USERS ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD ROUTES
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data/{range?}', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');

    // Role-specific dashboard routes
    Route::get('/dashboard/owner', [DashboardController::class, 'ownerDashboard'])->name('dashboard.owner')->middleware('role:owner');
    Route::get('/dashboard/kasir', [DashboardController::class, 'kasirDashboard'])->name('dashboard.kasir')->middleware('role:kasir');
    Route::get('/dashboard/gudang', [DashboardController::class, 'gudangDashboard'])->name('dashboard.gudang')->middleware('role:kepala_gudang');
    Route::get('/dashboard/checker', [DashboardController::class, 'gudangDashboard'])->name('dashboard.gudang')->middleware('role:checker');
    Route::get('/dashboard/logistik', [DashboardController::class, 'logistikDashboard'])->name('dashboard.logistik')->middleware('role:logistik');
    Route::get('/dashboard/kurir', [DashboardController::class, 'kurirDashboard'])->name('dashboard.kurir')->middleware('role:kurir');

    /*
    |--------------------------------------------------------------------------
    | PROFILE ROUTES (BREEZE)
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | VEHICLE MANAGEMENT ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:owner,admin,logistik')->prefix('vehicles')->name('vehicles.')->group(function () {
        Route::get('/', [VehicleController::class, 'index'])->name('index');
        Route::get('/create', [VehicleController::class, 'create'])->name('create');
        Route::post('/', [VehicleController::class, 'store'])->name('store');
        Route::get('/{vehicle}', [VehicleController::class, 'show'])->name('show');
        Route::get('/{vehicle}/edit', [VehicleController::class, 'edit'])->name('edit');
        Route::put('/{vehicle}', [VehicleController::class, 'update'])->name('update');
        Route::delete('/{vehicle}', [VehicleController::class, 'destroy'])->name('destroy');

        // Vehicle status management
        Route::post('/{vehicle}/status', [VehicleController::class, 'updateStatus'])->name('status');
        Route::post('/bulk/update', [VehicleController::class, 'bulkUpdate'])->name('bulk.update');

        // Export routes
        Route::get('/export/{format}', [VehicleController::class, 'export'])->name('export');
    });

    /*
    |--------------------------------------------------------------------------
    | MEMBER MANAGEMENT
    |--------------------------------------------------------------------------
    */
    Route::resource('members', MemberController::class);
    Route::get('members/{member}/receivables', [MemberController::class, 'receivables'])->name('members.receivables');
    Route::get('members/{member}/transactions', [MemberController::class, 'transactions'])->name('members.transactions');
    Route::post('members/{member}/toggle-status', [MemberController::class, 'toggleStatus'])->name('members.toggle-status');

    /*
    |--------------------------------------------------------------------------
    | RECEIVABLES (PIUTANG)
    |--------------------------------------------------------------------------
    */
    Route::prefix('receivables')->name('receivables.')->group(function () {
        Route::get('/', [ReceivableController::class, 'index'])->name('index');
        Route::get('/{receivable}', [ReceivableController::class, 'show'])->name('show');
        Route::post('/{receivable}/pay', [ReceivableController::class, 'pay'])->name('pay');
        Route::get('/{receivable}/payment-history', [ReceivableController::class, 'paymentHistory'])->name('payment-history');
        Route::get('/export/{format}', [ReceivableController::class, 'export'])->name('export');
    });

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION ROUTES (KASIR & OWNER)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:kasir,owner')->prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('/create', [TransactionController::class, 'create'])->name('create');
        Route::post('/', [TransactionController::class, 'store'])->name('store');
        Route::get('/{transaction}/edit', [TransactionController::class, 'edit'])->name('edit');
        Route::put('/{transaction}', [TransactionController::class, 'update'])->name('update');
        Route::get('/history', [TransactionController::class, 'index'])->name('history');
        Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
        Route::get('/{transaction}/print', [TransactionController::class, 'printReceipt'])->name('print');
        Route::get('/{transaction}/download', [TransactionController::class, 'downloadReceipt'])->name('download');

        // Owner only routes
        Route::middleware('role:owner')->group(function () {
            Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | PRODUCT ROUTES (OWNER & GUDANG)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:owner,kepala_gudang')->prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');

        // Additional product routes
        Route::post('/{product}/stock', [ProductController::class, 'stockUpdate'])->name('stock.update');
        Route::post('/import', [ProductController::class, 'import'])->name('import');
        Route::get('/{id}/quick-view', [ProductController::class, 'quickView'])->name('quick-view');
    });

    /*
    |--------------------------------------------------------------------------
    | DELIVERY SYSTEM ROUTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('delivery')->name('delivery.')->group(function () {
        Route::get('/dashboard', [DeliveryController::class, 'dashboard'])->name('dashboard');

        // ===== ROUTES KHUSUS UNTUK TAMBAH KURIR DAN KENDARAAN =====
        Route::post('/kurir/store', [DeliveryController::class, 'storeKurir'])
            ->name('kurir.store')
            ->middleware('role:owner,admin,logistik');

        Route::post('/kendaraan/store', [DeliveryController::class, 'storeKendaraan'])
            ->name('kendaraan.store')
            ->middleware('role:owner,admin,logistik');
        // ===== END =====

        // CRUD operations (hanya owner, admin, logistik)
        Route::middleware('role:owner,admin,logistik')->group(function () {
            Route::get('/', [DeliveryController::class, 'index'])->name('index');
            Route::get('/create', [DeliveryController::class, 'create'])->name('create');
            Route::post('/', [DeliveryController::class, 'store'])->name('store');
            Route::get('/{delivery}', [DeliveryController::class, 'show'])->name('show');
            Route::get('/{delivery}/edit', [DeliveryController::class, 'edit'])->name('edit');
            Route::put('/{delivery}', [DeliveryController::class, 'update'])->name('update');
            Route::delete('/{delivery}', [DeliveryController::class, 'destroy'])->name('destroy');

            // Management routes
            Route::get('/staff/list', [DeliveryController::class, 'staff'])->name('staff.index');
            Route::post('/staff/{staff}/status', [DeliveryController::class, 'updateStaffStatus'])->name('staff.status');
            Route::get('/zones/list', [DeliveryController::class, 'zones'])->name('zones.index');
            Route::get('/reports/generate', [DeliveryController::class, 'reports'])->name('reports');

            // Export (admin only)
            Route::get('/export', [DeliveryController::class, 'export'])->name('export');
        });

        // Delivery actions (owner, admin, logistik, kurir)
        Route::middleware('role:owner,admin,logistik,kurir')->group(function () {
            Route::post('/{delivery}/assign', [DeliveryController::class, 'assign'])->name('assign');
            Route::post('/{delivery}/pickup', [DeliveryController::class, 'pickup'])->name('pickup');
            Route::post('/{delivery}/start', [DeliveryController::class, 'startDelivery'])->name('start');
            Route::post('/{delivery}/complete', [DeliveryController::class, 'complete'])->name('complete');
            Route::post('/{delivery}/cancel', [DeliveryController::class, 'cancel'])->name('cancel');
            Route::post('/{delivery}/update-status', [DeliveryController::class, 'updateStatus'])->name('update-status');
            Route::post('/{delivery}/quick-update', [DeliveryController::class, 'quickUpdate'])->name('quick-update');
            Route::get('/{delivery}/details', [DeliveryController::class, 'getDeliveryDetails'])->name('details');
        });

        // Courier-specific routes
        Route::middleware('role:kurir,logistik,owner,admin')->group(function () {
            Route::get('/staff-dashboard', [DeliveryController::class, 'staffDashboard'])->name('staff.dashboard');
            Route::get('/my-deliveries', [DeliveryController::class, 'myDeliveries'])->name('my-deliveries');
            Route::post('/location/update', [DeliveryController::class, 'updateStaffLocation'])->name('location');
            Route::post('/{delivery}/accept', [DeliveryController::class, 'acceptDelivery'])->name('accept');
        });

        // Print routes (semua role bisa akses)
        Route::get('/{delivery}/print/note', [DeliveryController::class, 'printDeliveryNote'])->name('print.note');
        Route::get('/{delivery}/print/receipt', [DeliveryController::class, 'printReceipt'])->name('print.receipt');
    });

    // Request delivery from transaction
    Route::post('/transactions/{transaction}/delivery-request', [DeliveryController::class, 'requestDelivery'])->name('delivery.request');

    // Route untuk laporan PDF
    Route::prefix('reports')->middleware(['auth'])->group(function () {
        // Route untuk PDF laporan pengiriman
        Route::get('/delivery/pdf', [DeliveryReportController::class, 'exportPdf'])->name('reports.delivery.pdf');

        // Route untuk summary (jika ada)
        Route::get('/delivery/summary', [DeliveryReportController::class, 'summary'])->name('reports.delivery.summary');
    });

    // Route export alternatif dengan nama berbeda
    Route::get('/delivery-report/export-pdf', [DeliveryReportController::class, 'exportPdf'])->name('delivery.export.pdf');

    /*
    |--------------------------------------------------------------------------
    | CATEGORY ROUTES (OWNER & GUDANG)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:owner,kepala_gudang')->resource('categories', CategoryController::class)->except('show');

    /*
    |--------------------------------------------------------------------------
    | REPORT ROUTES (OWNER, MANAGER, ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:owner,manager,admin')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'salesReport'])->name('sales');
        Route::get('/inventory', [ReportController::class, 'inventoryReport'])->name('inventory');
        Route::get('/best-selling', [ReportController::class, 'bestSellingProducts'])->name('best-selling');
        Route::get('/cashiers', [ReportController::class, 'cashierPerformance'])->name('cashiers');
        Route::get('/customers', [ReportController::class, 'customerReport'])->name('customers');

        // Export routes
        Route::post('/export/pdf', [ReportController::class, 'exportPDF'])->name('export.pdf');
        Route::post('/sales/export-pdf', [ReportController::class, 'exportSalesPDF'])->name('sales.export-pdf');
        Route::post('/sales/export', [ReportController::class, 'exportSalesPDF'])->name('sales.export');
    });

    /*
    |--------------------------------------------------------------------------
    | USER MANAGEMENT ROUTES (OWNER ONLY)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:owner')->prefix('users')->name('users.')->group(function () {
        // Basic CRUD
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');

        // Additional actions
        Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/export', [UserController::class, 'export'])->name('export');

        // Face registration
        Route::get('/{user}/face-registration', [UserController::class, 'faceRegistration'])->name('face.registration');
        Route::post('/{user}/face', [UserController::class, 'storeFaceData'])->name('face.store');
        Route::delete('/{user}/face', [UserController::class, 'destroyFaceData'])->name('face.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | PROFILE SETTINGS ROUTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/settings', [UserController::class, 'profile'])->name('settings');
        Route::put('/update', [UserController::class, 'updateProfile'])->name('update');
    });

    /*
    |--------------------------------------------------------------------------
    | NOTIFICATIONS
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-as-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-as-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
        Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/clear-all', [App\Http\Controllers\NotificationController::class, 'clearAll'])->name('clear-all');
    });
});
