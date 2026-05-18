<?php

namespace Database\Factories;

use App\Models\Feedback;
use Database\Factories\KunjunganFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    public function definition(): array
    {
        return [
            'kunjungan_id' => KunjunganFactory::new()->create()->kunjungan_id,
            'rating' => fake()->numberBetween(1, 5),
            'komentar' => fake()->sentence(),
        ];
    }
}
