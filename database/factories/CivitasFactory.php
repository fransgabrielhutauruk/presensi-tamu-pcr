<?php

namespace Database\Factories;

use App\Models\Civitas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Civitas>
 */
class CivitasFactory extends Factory
{
    protected $model = Civitas::class;

    public function definition(): array
    {
        return [
            'nama_civitas' => fake()->name(),
            'nip' => null,
            'nim' => null,
            'jenis_kelamin' => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'nomor_telepon' => '08'.fake()->numerify('##########'),
            'email' => fake()->safeEmail(),
        ];
    }
}
