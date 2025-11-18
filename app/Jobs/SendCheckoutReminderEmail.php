<?php

namespace App\Jobs;

use App\Mail\CheckoutReminderMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Kunjungan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCheckoutReminderEmail implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $kunjunganId;
    protected $adminEmail;

    /**
     * Create a new job instance.
     */
    public function __construct($kunjunganId, $adminEmail = null)
    {
        $this->kunjunganId = $kunjunganId;
        $this->adminEmail = $adminEmail ?? env('MAIL_ADMIN_ADDRESS', 'admin@example.com');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $kunjungan = Kunjungan::with('tamu')->find($this->kunjunganId);

            if (!$kunjungan || $kunjungan->is_checkout) {
                return;
            }

            if ($kunjungan->tamu && $kunjungan->tamu->email_tamu) {
                Mail::to($kunjungan->tamu->email_tamu)
                    ->send(new CheckoutReminderMail($kunjungan));
            }

            $kunjungan->update(['reminder_sent_at' => now()]);
        } catch (\Exception $e) {
            Log::error("Error sending checkout reminder email: " . $e->getMessage());
            throw $e;
        }
    }
}
