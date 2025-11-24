<?php

use App\Enums\UserRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KunjunganController;
use App\Http\Controllers\Admin\ActivityLogController;

include_once __DIR__ . "/web-frontend.php";

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/switch-role', [AuthController::class, 'switchRole'])->name('switch.role');
});

require __DIR__ . '/auth.php';

Route::prefix('app')
    ->middleware(['auth', 'active-role:' . implode(',', UserRole::getAllRoles())])->group(function () {
        Route::get('event/qr/{eventId}', [EventController::class, 'showQrCode'])->name('app.event.qr-code');
        generalRoute(EventController::class, 'event', 'app');
        Route::post('kunjungan/validate/{id}', [KunjunganController::class, 'validateSingle'])
            ->name('app.kunjungan.validate-single');
        Route::post('kunjungan/reject/{id}', [KunjunganController::class, 'rejectSingle'])
            ->name('app.kunjungan.reject-single');
        Route::post('kunjungan/bulk-validasi', [KunjunganController::class, 'bulkValidasi'])
            ->name('app.kunjungan.bulk-validasi');
        Route::post('kunjungan/detail-data', function (\Illuminate\Http\Request $request) {
            return app(KunjunganController::class)->data($request, 'detail');
        })->name('app.kunjungan.detail-data');

        Route::middleware('active-role:' . implode(',', UserRole::getAdminEksekutifSecurityRoles()))->group(function () {
            generalRoute(DashboardController::class, 'dashboard', 'app');
            Route::get('kunjungan/monitoring', [KunjunganController::class, 'monitoring'])
                ->name('app.kunjungan.monitoring');
            generalRoute(KunjunganController::class, 'kunjungan', 'app');
        });

        Route::middleware('active-role:' . UserRole::ADMIN->value)->group(function () {
            Route::get('kunjungan/validasi', [KunjunganController::class, 'validasi'])->name('app.kunjungan.validasi');
            generalRoute(UserController::class, 'user', 'app');
            generalRoute(DashboardController::class, 'dashboard', 'app');
            generalRoute(ActivityLogController::class, 'log-aktivitas', 'app');
            generalRoute(FeedbackController::class, 'feedback', 'app');
        });

        // -----------------
        Route::get('icons', function () {
            if (!config('app.debug')) {
                abort(403, 'Icon gallery is only available in debug mode.');
            }

            $cssFile = public_path('theme/plugins/global/plugins.bundle.css');
            if (!file_exists($cssFile)) {
                abort(500, 'Keen Icons CSS not found at ' . $cssFile);
            }

            $css = @file_get_contents($cssFile) ?: '';

            $outline = [];
            $solid = [];
            $duotone = [];

            if ($css) {
                if (preg_match_all('/\.ki-([a-z0-9\-]+)\.ki-outline:before/', $css, $m)) {
                    $outline = array_values(array_unique($m[1]));
                    sort($outline);
                }

                if (preg_match_all('/\.ki-([a-z0-9\-]+)\.ki-solid:before/', $css, $m2)) {
                    $solid = array_values(array_unique($m2[1]));
                    sort($solid);
                }

                if (preg_match_all('/\.ki-([a-z0-9\-]+)\s*\.path1:before/', $css, $m3)) {
                    $duotone = array_values(array_unique($m3[1]));
                    sort($duotone);
                }
            }

            $pageData = (object) [
                'activeMenu' => 'icons',
                'activeRoot' => 'dev',
                'title' => 'Keen Icons Gallery',
                'breadCrump' => [],
            ];

            return view('dev.icons', compact('outline', 'solid', 'duotone', 'pageData'));
        })->name('app.icons');
        // ---------------------
    });
