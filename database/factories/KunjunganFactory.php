<?php

namespace Database\Factories;

use App\Models\Kunjungan;
use Database\Factories\TamuFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kunjungan>
 */
class KunjunganFactory extends Factory
{
    protected $model = Kunjungan::class;

    public function definition(): array
    {
        return [
            'tamu_id' => TamuFactory::new()->create()->tamu_id,
            'civitas_id' => null,
            'event_id' => null,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'instansi',
            'waktu_keluar' => now()->addHour()->format('H:i:s'),
            'transportasi' => 'Mobil',
            'status_validasi' => false,
            'is_checkout' => false,
            'checkout_time' => null,
            'reminder_sent_at' => null,
            'is_vip' => false,
        ];
    }
}
