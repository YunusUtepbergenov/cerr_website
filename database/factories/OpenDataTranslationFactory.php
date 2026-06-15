<?php

namespace Database\Factories;

use App\Models\OpenDataTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OpenDataTranslation> */
class OpenDataTranslationFactory extends Factory
{
    protected $model = OpenDataTranslation::class;

    public function definition(): array
    {
        return [
            'language' => $this->faker->randomElement(['uz', 'ru', 'en', 'kr']),
            'title' => $this->faker->sentence(4),
        ];
    }
}
