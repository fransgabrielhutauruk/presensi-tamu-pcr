<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reminders:process')->everyMinute();

// Menjadwalkan sinkronisasi Data Warehouse setiap tengah malam
Schedule::command('app:etl-sync-kunjungan')->dailyAt('00:00');