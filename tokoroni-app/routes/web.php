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
use App\Http\Controllers\CheckerReportController;

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
    Route::get('/dashboard/manager', [DashboardController::class, 'managerDashboard'])->name('dashboard.manager')->middleware('role:manager');
    Route::get('/dashboard/kasir', [DashboardController::class, 'kasirDashboard'])->name('dashboard.kasir')->middleware('role:kasir');
    Route::get('/dashboard/kepala-gudang', [DashboardController::class, 'gudangDashboard'])->name('dashboard.kepala_gudang')->middleware('role:kepala_gudang');
    Route::get('/dashboard/checker-barang', [DashboardController::class, 'checkerDashboard'])->name('dashboard.checker_barang')->middleware('role:checker_barang');
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
    Route::middleware('role:owner,manager,logistik')->prefix('vehicles')->name('vehicles.')->group(function () {
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
    | TRANSACTION ROUTES (KASIR, OWNER, MANAGER)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:kasir,owner,manager')->prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('/create', [TransactionController::class, 'create'])->name('create');
        Route::post('/', [TransactionController::class, 'store'])->name('store');
        Route::get('/{transaction}/edit', [TransactionController::class, 'edit'])->name('edit');
        Route::put('/{transaction}', [TransactionController::class, 'update'])->name('update');
        Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
        Route::get('/{transaction}/print', [TransactionController::class, 'printReceipt'])->name('print');
        Route::get('/{transaction}/download', [TransactionController::class, 'downloadReceipt'])->name('download');

        // Owner & Manager only routes (delete)
        Route::middleware('role:owner,manager,kepala_gudang')->group(function () {
            Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | PRODUCT ROUTES (OWNER, MANAGER, KEPALA GUDANG, CHECKER BARANG)
    |--------------------------------------------------------------------------
    */
    Route::prefix('products')->name('products.')->group(function () {
        // Semua role yang disebutkan bisa mengakses index dan show (READ ONLY untuk checker)
        Route::middleware('role:owner,manager,kepala_gudang,checker_barang')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/{id}/quick-view', [ProductController::class, 'quickView'])->name('quick-view');
            Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        });

        // Checker Barang bisa melaporkan produk
        Route::middleware('role:checker_barang')->group(function () {
            Route::post('/{product}/report', [ProductController::class, 'reportProduct'])->name('report');
        });

        // Owner, Manager, Kepala Gudang bisa create, edit, delete
        Route::middleware('role:owner,manager,kepala_gudang')->group(function () {
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
            Route::post('/{product}/stock', [ProductController::class, 'stockUpdate'])->name('stock.update');
            Route::post('/import', [ProductController::class, 'import'])->name('import');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | CHECKER REPORT ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth')->prefix('checker-reports')->name('checker.')->group(function () {
        // Checker Barang bisa melihat laporannya sendiri
        Route::middleware('role:checker_barang')->group(function () {
            Route::get('/', [CheckerReportController::class, 'index'])->name('index');
        });

        // Kepala Gudang, Owner, Manager bisa mengelola semua laporan
        Route::middleware('role:kepala_gudang,owner,manager')->group(function () {
            Route::get('/all', [CheckerReportController::class, 'allReports'])->name('all');
            Route::post('/{report}/resolve', [CheckerReportController::class, 'resolve'])->name('resolve');
            Route::delete('/{report}', [CheckerReportController::class, 'destroy'])->name('destroy');
        });
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
            ->middleware('role:owner,manager,logistik');

        Route::post('/kendaraan/store', [DeliveryController::class, 'storeKendaraan'])
            ->name('kendaraan.store')
            ->middleware('role:owner,manager,logistik');
        // ===== END =====

        // CRUD operations (owner, manager, logistik)
        Route::middleware('role:owner,manager,logistik')->group(function () {
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
        });

        // Export (owner, manager, logistik)
        Route::middleware('role:owner,manager,logistik')->group(function () {
            Route::get('/export', [DeliveryController::class, 'export'])->name('export');
        });

        // Delivery actions (owner, manager, logistik, kurir)
        Route::middleware('role:owner,manager,logistik,kurir')->group(function () {
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
        Route::middleware('role:kurir,logistik,owner,manager')->group(function () {
            Route::get('/staff-dashboard', [DeliveryController::class, 'staffDashboard'])->name('staff.dashboard');
            Route::get('/my-deliveries', [DeliveryController::class, 'myDeliveries'])->name('my-deliveries');
            Route::post('/location/update', [DeliveryController::class, 'updateStaffLocation'])->name('location');
            Route::post('/{delivery}/accept', [DeliveryController::class, 'acceptDelivery'])->name('accept');
        });

        // Print routes (semua role bisa akses)
        Route::middleware('auth')->group(function () {
            Route::get('/{delivery}/print/note', [DeliveryController::class, 'printDeliveryNote'])->name('print.note');
            Route::get('/{delivery}/print/receipt', [DeliveryController::class, 'printReceipt'])->name('print.receipt');
        });
    });

    // Request delivery from transaction
    Route::post('/transactions/{transaction}/delivery-request', [DeliveryController::class, 'requestDelivery'])->name('delivery.request');

    // Route untuk laporan PDF
    Route::prefix('reports')->middleware(['auth'])->group(function () {
        Route::get('/delivery/pdf', [DeliveryReportController::class, 'exportPdf'])->name('reports.delivery.pdf');
        Route::get('/delivery/summary', [DeliveryReportController::class, 'summary'])->name('reports.delivery.summary');
    });

    // Route export alternatif dengan nama berbeda
    Route::get('/delivery-report/export-pdf', [DeliveryReportController::class, 'exportPdf'])->name('delivery.export.pdf');
    
     /*
    |--------------------------------------------------------------------------
    | CATEGORY ROUTES (OWNER, MANAGER, KEPALA GUDANG)
    |--------------------------------------------------------------------------
    */
    Route::prefix('categories')->name('categories.')->group(function () {
        // Semua role yang disebutkan bisa mengakses index dan show (READ ONLY untuk checker)
        Route::middleware('role:owner,manager,kepala_gudang,checker_barang')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
        });

        // Owner, Manager, Kepala Gudang bisa create, edit, delete
        Route::middleware('role:owner,manager,kepala_gudang')->group(function () {
            Route::get('/create', [CategoryController::class, 'create'])->name('create');
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
            Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | REPORT ROUTES (OWNER, MANAGER)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:owner,manager')->prefix('reports')->name('reports.')->group(function () {
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
    | USER MANAGEMENT ROUTES (OWNER & MANAGER)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:owner,manager')->prefix('users')->name('users.')->group(function () {
        // Basic CRUD
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');

        // Hanya owner yang bisa hapus user
        Route::middleware('role:owner')->group(function () {
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });

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
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/clear-all', [NotificationController::class, 'clearAll'])->name('clear-all');
    });
});