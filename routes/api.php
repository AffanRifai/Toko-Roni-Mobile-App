<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| PUBLIC API ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH API
    |--------------------------------------------------------------------------
    */

    Route::post('/login', [AuthApiController::class, 'login']);
    Route::post('/face-login', [AuthApiController::class, 'faceLogin']);

    /*
    |--------------------------------------------------------------------------
    | FACE RECOGNITION API
    |--------------------------------------------------------------------------
    */

    Route::post('/face/compare', [AuthenticatedSessionController::class, 'compareFace'])
        ->name('api.face.compare');

    Route::get('/faces/registered', [AuthenticatedSessionController::class, 'getRegisteredFaces'])
        ->name('api.faces.registered');

    Route::get('/face/status', [AuthenticatedSessionController::class, 'faceStatus'])
        ->name('api.face.status');

});


/*
|--------------------------------------------------------------------------
| AUTHENTICATED API ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH USER
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [AuthApiController::class, 'profile'])->name('profile');
    Route::post('/logout', [AuthApiController::class, 'logout'])->name('logout');


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD API
    |--------------------------------------------------------------------------
    */

    Route::prefix('dashboard')->name('dashboard.')->group(function () {

        Route::get('/stats', [DashboardController::class, 'getDashboardStats'])->name('stats');

        Route::get('/chart-data', [DashboardController::class, 'getChartData'])->name('chart-data');

        Route::get('/notifications', [DashboardController::class, 'getNotifications'])->name('notifications');

    });


    /*
    |--------------------------------------------------------------------------
    | TRANSACTION API
    |--------------------------------------------------------------------------
    */

    Route::prefix('transactions')->name('transactions.')->group(function () {

        Route::get('/recent', [TransactionController::class, 'recentTransactions'])->name('recent');

        Route::get('/today-stats', [TransactionController::class, 'todayStats'])->name('today-stats');

        Route::get('/search', [TransactionController::class, 'search'])->name('search');

    });


    /*
    |--------------------------------------------------------------------------
    | DELIVERY API
    |--------------------------------------------------------------------------
    */

    Route::prefix('delivery')->name('delivery.')->group(function () {

        Route::get('/today-stats', [DeliveryController::class, 'getTodayStats'])->name('stats');

        Route::get('/recent', [DeliveryController::class, 'getRecentDeliveries'])->name('recent');

    });


    /*
    |--------------------------------------------------------------------------
    | VEHICLE API
    |--------------------------------------------------------------------------
    */

    Route::prefix('vehicles')->name('vehicles.')->group(function () {

        Route::get('/available', [VehicleController::class, 'getAvailableVehicles'])->name('available');

        Route::get('/statistics', [VehicleController::class, 'getStatistics'])->name('statistics');

        Route::post('/{vehicle}/quick-update', [VehicleController::class, 'quickUpdate'])->name('quick-update');

    });


    /*
    |--------------------------------------------------------------------------
    | MEMBER API
    |--------------------------------------------------------------------------
    */

    Route::prefix('members')->name('members.')->group(function () {

        Route::get('/search', [MemberController::class, 'search'])->name('search');

        Route::get('/{member}', [MemberController::class, 'getMemberData'])->name('get');

    });


    /*
    |--------------------------------------------------------------------------
    | PRODUCT API
    |--------------------------------------------------------------------------
    */

    Route::prefix('products')->name('products.')->group(function () {

        Route::get('/search', [ProductController::class, 'search'])->name('search');

        Route::get('/low-stock', [ProductController::class, 'lowStockProducts'])->name('low-stock');

        Route::get('/{id}/quick-view', [ProductController::class, 'quickView'])->name('quick-view');

    });


    /*
    |--------------------------------------------------------------------------
    | REPORT API
    |--------------------------------------------------------------------------
    */

    Route::prefix('reports')->name('reports.')->group(function () {

        Route::get('/sales-chart', [ReportController::class, 'getSalesChartData'])->name('sales-chart');

    });


    /*
    |--------------------------------------------------------------------------
    | ADMIN ONLY API
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:owner,admin')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | USER MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::prefix('users')->name('users.')->group(function () {

            Route::get('/face-registration', [UserController::class, 'getUsersForFaceRegistration'])
                ->name('face-registration');

            Route::get('/faces/descriptors', [UserController::class, 'getFaceDescriptors'])
                ->name('faces.descriptors');

            Route::post('/faces/register', [UserController::class, 'registerFace'])
                ->name('faces.register');

            Route::delete('/faces/{id}', [UserController::class, 'deleteFaceRegistration'])
                ->name('faces.delete');

        });


        /*
        |--------------------------------------------------------------------------
        | BULK OPERATIONS
        |--------------------------------------------------------------------------
        */

        Route::post('/vehicles/bulk/update', [VehicleController::class, 'bulkUpdate'])
            ->name('vehicles.bulk.update');

    });

});
