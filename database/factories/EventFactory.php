<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $startTime = fake()->time('H:i:s', '16:00:00');
        $endTime = fake()->time('H:i:s', '23:00:00');

        return [
            'eventkategori_id' => EventKategoriFactory::new()->create()->eventkategori_id,
            'nama_event' => 'Event ' . fake()->unique()->word(),
            'deskripsi_event' => fake()->sentence(),
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => $startTime,
            'waktu_selesai_event' => $endTime,
            'lokasi_event' => fake()->address(),
            'link_dokumentasi_event' => null,
        ];
    }
}
