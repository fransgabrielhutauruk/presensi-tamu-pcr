<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

include_once __DIR__ . "/web-frontend.php";

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/switch-role', [App\Http\Controllers\Auth\AuthController::class, 'switchRole'])->name('switch.role');
});

require __DIR__ . '/auth.php';

Route::prefix('app')
    ->middleware(['auth', 'active-role:Mahasiswa,Staf,Admin,Eksekutif'])
    ->group(function () {
        generalRoute(App\Http\Controllers\Admin\DashboardController::class, 'dashboard', 'app');

        // User management hanya untuk Admin dan Eksekutif - menggunakan active role
        Route::middleware('active-role:Mahasiswa,Admin,Eksekutif')->group(function () {
            generalRoute(App\Http\Controllers\Admin\UserController::class, 'user', 'app');
            generalRoute(App\Http\Controllers\Admin\EventController::class, 'event', 'app');
        });

        generalRoute(App\Http\Controllers\Admin\Konten\KontenController::class, 'konten', 'app');
        generalRoute(App\Http\Controllers\Admin\Konten\KontenMainController::class, 'konten-main', 'app');
        generalRoute(App\Http\Controllers\Admin\Konten\KontenSlideController::class, 'konten-slide', 'app');
        generalRoute(App\Http\Controllers\Admin\Konten\KontenPageController::class, 'konten-page', 'app');
        generalRoute(App\Http\Controllers\Admin\Konten\KontenJurusanController::class, 'konten-jurusan', 'app');
        generalRoute(App\Http\Controllers\Admin\Konten\KontenProdiController::class, 'konten-prodi', 'app');

        generalRoute(App\Http\Controllers\Admin\PostController::class, 'post', 'app');
        generalRoute(App\Http\Controllers\Admin\AgendaController::class, 'agenda', 'app');
        generalRoute(App\Http\Controllers\Admin\TestiController::class, 'testi', 'app');

        generalRoute(App\Http\Controllers\Admin\MediaController::class, 'media', 'app', false);
        generalRoute(App\Http\Controllers\Admin\MasterController::class, 'master', 'app');


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
    });
