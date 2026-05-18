<?php

namespace Database\Factories;

use App\Models\EventKategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventKategori>
 */
class EventKategoriFactory extends Factory
{
    protected $model = EventKategori::class;

    public function definition(): array
    {
        return [
            'nama_kategori' => fake()->unique()->words(2, true),
            'deskripsi_kategori' => fake()->sentence(),
        ];
    }
}
