<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kunjungan;
use App\Jobs\SendWhatsAppReminder;
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
    protected $description = 'Process scheduled WhatsApp reminders for checkout';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i');

        $kunjungans = Kunjungan::with('tamu')
            ->where('is_checkout', false)
            ->where('waktu_keluar', '>=', $currentTime)
            ->whereDate('created_at', $now->toDateString())
            ->whereNull('reminder_sent_at')
            ->get();

        $count = 0;
        foreach ($kunjungans as $kunjungan) {
            SendWhatsAppReminder::dispatch($kunjungan->kunjungan_id);

            $kunjungan->update(['reminder_sent_at' => $now]);

            $count++;
        }

        $this->info("Processed {$count} reminders at {$now->format('Y-m-d H:i:s')}");

        return 0;
    }
}
