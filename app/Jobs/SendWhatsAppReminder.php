<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Kunjungan;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendWhatsAppReminder implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $kunjunganId;
    protected $adminPhoneNumber;

    /**
     * Create a new job instance.
     */
    public function __construct($kunjunganId, $adminPhoneNumber = '+6289621530018')
    {
        $this->kunjunganId = $kunjunganId;
        $this->adminPhoneNumber = $adminPhoneNumber;
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

            $sid = env('TWILIO_SID');
            $token = env('TWILIO_TOKEN');
            $twilio = new Client($sid, $token);

            $gender = $kunjungan->tamu->gender;
            $name = $kunjungan->tamu->name; 
            $kunjunganId = $this->kunjunganId;

            $tamuPhone = $this->formatWhatsAppNumber($kunjungan->tamu->phone_number);
            $adminPhoneNumber = $this->adminPhoneNumber;

            $twilio->messages->create(
                "whatsapp:{$adminPhoneNumber}",
                [
                    "from" => "whatsapp:+14155238886",
                    "contentSid" => "HX343a5b01c4fa03491db7b3baf15753d6",
                    "contentVariables" => json_encode([
                        "pak_bu" => ($gender == "Laki-laki") ? "Pak" : "Bu",
                        "name" => $name,
                        "kunjungan_id" => $kunjunganId
                    ]),
                ]
            );

            $kunjungan->update(['reminder_sent_at' => now()]);
        } catch (\Exception $e) {
            Log::error("Error sending WhatsApp reminder: " . $e->getMessage());
        }
    }

    private function formatWhatsAppNumber($phoneNumber)
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        if (substr($cleaned, 0, 1) === '0') {
            return '+62' . substr($cleaned, 1);
        }
        if (substr($cleaned, 0, 2) === '62') {
            return '+' . $cleaned;
        }
        return '+62' . $cleaned;
    }
}
