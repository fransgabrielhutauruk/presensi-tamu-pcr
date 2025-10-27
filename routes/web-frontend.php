<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\DEV;
use App\Http\Controllers\Frontend\MainController;
use App\Http\Controllers\Tamu\KunjunganController;
use App\Http\Controllers\Tamu\KunjunganEventController;
use App\Http\Controllers\Tamu\KunjunganNonEventController;

Route::name('tamu.')->group(function () {
    Route::controller(KunjunganController::class)->group(function () {
        Route::get('/', 'index')->name('home');
        Route::get('/event-or-non-event', 'eventOrNonEvent')->name('event-or-non-event');
        Route::get('/sukses/{kunjunganId}', 'sukses')->name('sukses');
        Route::get('/checkout/{kunjunganId}', 'checkout')->name('checkout');
        Route::post('/checkout/{kunjunganId}', 'storeCheckout')->name('storeCheckout');

        Route::controller(KunjunganNonEventController::class)->group(function () {
            Route::prefix('/non-event')->name('non-event.')->group(function () {
                Route::get('/tujuan', 'tujuan')->name('tujuan');
                Route::get('/presensi', 'formPresensi')->name('form-presensi');
                Route::post('/store-presensi', 'storePresensi')->name('store-presensi');
            });
        });

        // Route::get('/feedback/{kunjunganId}', 'feedback')->name('feedback');
        // Route::post('/feedback/{kunjunganId}', 'storeFeedback')->name('storeFeedback');

        Route::controller(KunjunganEventController::class)->group(function () {

            Route::prefix('/event')->name('event.')->group(function () {
                Route::get('/list', 'listEvent')->name('list');
                Route::get('/identitas', 'identitas')->name('identitas');
                Route::get('/form', 'eventForm')->name('form');
                Route::post('/store', 'storeEvent')->name('store');
                //     Route::get('/civitas-form', 'eventCivitasForm')->name('civitas-form');
                //     Route::post('/civitas-store', 'storeCivitasEvent')->name('civitas-store');
            });
        });
    });
});

Route::name('frontend.')->group(function () {
    Route::controller(MainController::class)->group(function () {
        Route::get('/pcr', 'index')->name('home');
    });

    // Development Routes
    Route::prefix('/dev')->name('dev.')->controller(DEV\MainController::class)->group(function () {
        Route::get('/changelog', 'changelog')->name('changelog');
    });
});
