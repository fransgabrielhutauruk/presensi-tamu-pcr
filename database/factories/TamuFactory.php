<?php

namespace Database\Factories;

use App\Models\Tamu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tamu>
 */
class TamuFactory extends Factory
{
    protected $model = Tamu::class;

    public function definition(): array
    {
        return [
            'nama_tamu' => fake()->name(),
            'jenis_kelamin_tamu' => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'email_tamu' => fake()->safeEmail(),
            'nomor_telepon_tamu' => '08' . fake()->numerify('##########'),
        ];
    }
}
