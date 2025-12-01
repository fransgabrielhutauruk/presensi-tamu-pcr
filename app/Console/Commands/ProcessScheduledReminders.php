<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kunjungan;
use App\Jobs\SendCheckoutReminderEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessScheduledReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled email reminders for checkout';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i');

        $kunjungans = Kunjungan::with('tamu')
            ->where('is_checkout', false)
            ->where('waktu_keluar', '=', $currentTime)
            ->whereDate('created_at', $now->toDateString())
            ->whereNull('reminder_sent_at')
            ->get();

        $count = 0;
        foreach ($kunjungans as $kunjungan) {
            SendCheckoutReminderEmail::dispatch($kunjungan->kunjungan_id);

            $count++;

            $this->line("Reminder sent for kunjungan ID {$kunjungan->kunjungan_id} - Waktu keluar: {$kunjungan->waktu_keluar}");
        }

        if ($count > 0) {
            $this->info("Processed {$count} email reminders at {$now->format('Y-m-d H:i')}");
        } else {
            $this->comment("No reminders to process at {$now->format('Y-m-d H:i')}");
        }

        return 0;
    }
}
