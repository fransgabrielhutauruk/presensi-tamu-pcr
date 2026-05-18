<?php

namespace Database\Factories;

use App\Models\Dimension\DmPegawai;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DmPegawai>
 */
class DmPegawaiFactory extends Factory
{
    protected $model = DmPegawai::class;

    public function definition(): array
    {
        return [
            'nip' => fake()->unique()->numerify('######'),
            'nama' => fake()->name(),
            'email' => fake()->safeEmail(),
        ];
    }
}
