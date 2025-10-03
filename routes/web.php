<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

include_once __DIR__ . "/web-frontend.php";

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::prefix('app')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        generalRoute(App\Http\Controllers\Admin\DashboardController::class, 'dashboard', 'app');

        // generalRoute(App\Http\Controllers\Admin\Konten\MainController::class, 'konten-main', 'konten');
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

        // Presensi Tamu - Admin Routes
        Route::prefix('presensi')->name('admin.presensi.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\PresensiDashboardController::class, 'index'])->name('dashboard');
            Route::get('/kunjungan', [App\Http\Controllers\Admin\PresensiDashboardController::class, 'kunjungan'])->name('kunjungan');
            Route::get('/kunjungan/{id}/detail', [App\Http\Controllers\Admin\PresensiDashboardController::class, 'detailKunjungan'])->name('kunjungan.detail');
            Route::post('/kunjungan/{id}/validate', [App\Http\Controllers\Admin\PresensiDashboardController::class, 'validateKunjungan'])->name('kunjungan.validate');
            Route::post('/kunjungan/{id}/checkout', [App\Http\Controllers\Admin\PresensiDashboardController::class, 'checkoutKunjungan'])->name('kunjungan.checkout');
            Route::get('/report/daily', [App\Http\Controllers\Admin\PresensiDashboardController::class, 'reportDaily'])->name('report.daily');
            Route::get('/export/daily', [App\Http\Controllers\Admin\PresensiDashboardController::class, 'exportDaily'])->name('export.daily');
        });
    });

// Presensi Tamu - Public Routes
Route::prefix('tamu')->name('tamu.')->group(function () {
    Route::get('/', [App\Http\Controllers\Tamu\PresensiController::class, 'index'])->name('index');
    Route::get('/pilih-tujuan', [App\Http\Controllers\Tamu\PresensiController::class, 'pilihTujuan'])->name('pilih-tujuan');
    Route::post('/simpan-tujuan', [App\Http\Controllers\Tamu\PresensiController::class, 'simpanTujuan'])->name('simpan-tujuan');
    Route::get('/form', [App\Http\Controllers\Tamu\PresensiController::class, 'form'])->name('form');
    Route::post('/simpan-form', [App\Http\Controllers\Tamu\PresensiController::class, 'simpanForm'])->name('simpan-form');
    Route::get('/sukses', [App\Http\Controllers\Tamu\PresensiController::class, 'sukses'])->name('sukses');
    Route::post('/checkout', [App\Http\Controllers\Tamu\PresensiController::class, 'checkout'])->name('checkout');
    Route::get('/feedback', [App\Http\Controllers\Tamu\PresensiController::class, 'feedback'])->name('feedback');
    Route::post('/simpan-feedback', [App\Http\Controllers\Tamu\PresensiController::class, 'simpanFeedback'])->name('simpan-feedback');
});

// //temporary
// Route::get('/media/{id}', function ($id) {
//     return serveMedia(decid($id));
// })->name('media.show');
// Route::get('/media/thumb/{id}', function ($id) {
//     return serveMedia(decid($id), true);
// })->name('media.thumb');
