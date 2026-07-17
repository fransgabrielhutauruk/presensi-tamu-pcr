<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\KunjunganController;
use App\Http\Controllers\Admin\KunjunganMonitoringController;
use App\Http\Controllers\Admin\KunjunganValidasiController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__.'/web-frontend.php';

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/switch-role', [AuthController::class, 'switchRole'])->name('switch.role');
});

require __DIR__.'/auth.php';

Route::prefix('app')
    ->middleware(['auth', 'active-role:'.implode(',', UserRole::getAllRoles())])->group(function () {
        Route::post('kunjungan/detail-data', function (Request $request) {
            return app(KunjunganController::class)->data($request, 'detail');
        })->name('app.kunjungan.detail-data');

        Route::post('kunjungan/validate/{id}', [KunjunganValidasiController::class, 'validateSingle'])
            ->name('app.kunjungan.validate-single');
        Route::post('kunjungan/reject/{id}', [KunjunganValidasiController::class, 'rejectSingle'])
            ->name('app.kunjungan.reject-single');
        Route::post('kunjungan/bulk-validasi', [KunjunganValidasiController::class, 'bulkValidasi'])
            ->name('app.kunjungan.bulk-validasi');
        Route::post('kunjungan/restore/{id}', [KunjunganValidasiController::class, 'restoreSingle'])
            ->name('app.kunjungan.restore-single');
        Route::post('kunjungan/force-delete/{id}', [KunjunganValidasiController::class, 'forceDeleteSingle'])
            ->name('app.kunjungan.force-delete-single');
        Route::post('kunjungan/bulk-restore', [KunjunganValidasiController::class, 'bulkRestore'])
            ->name('app.kunjungan.bulk-restore');
        Route::post('kunjungan/bulk-force-delete', [KunjunganValidasiController::class, 'bulkForceDelete'])
            ->name('app.kunjungan.bulk-force-delete');

        Route::middleware('active-role:'.implode(',', UserRole::getAdminEksekutifSecurityRoles()))->group(function () {
            Route::get('kunjungan/monitoring', [KunjunganMonitoringController::class, 'index'])
                ->name('app.kunjungan.monitoring');
            Route::any('kunjungan/data/monitoring-hari-ini/{param2?}/{param3?}/{param4?}', [KunjunganMonitoringController::class, 'data'])
                ->middleware(['ajax']);
            Route::get('kunjungan/monitoring/stats', [KunjunganMonitoringController::class, 'getStats'])
                ->name('app.kunjungan.monitoring.stats');

            generalRoute(KunjunganValidasiController::class, 'kunjungan-validasi', 'app');
            generalRoute(KunjunganController::class, 'kunjungan', 'app');
            generalRoute(FeedbackController::class, 'feedback', 'app');
        });

        Route::middleware('active-role:'.implode(',', UserRole::getAdminEksekutifStafMahasiswaRoles()))->group(function () {
            Route::get('event/qr/{eventId}', [EventController::class, 'showQrCode'])->name('app.event.qr-code');
            Route::post('event/store-vip-guest', [EventController::class, 'storeVipGuest'])->name('app.event.store-vip-guest');
            generalRoute(EventController::class, 'event', 'app');
        });

        Route::middleware('active-role:'.implode(',', UserRole::getAdminEksekutifRoles()))->group(function () {
            generalRoute(DashboardController::class, 'dashboard', 'app');
        });

        Route::middleware('active-role:'.UserRole::ADMIN->value)->group(function () {
            generalRoute(UserController::class, 'user', 'app');
            generalRoute(ActivityLogController::class, 'log-aktivitas', 'app');
            generalRoute(MasterController::class, 'master', 'app');
        });
    });
