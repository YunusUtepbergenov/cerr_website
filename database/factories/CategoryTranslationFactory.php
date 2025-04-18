<?php

namespace Database\Factories;

use App\Models\CategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CategoryTranslation>
 */
class CategoryTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = CategoryTranslation::class;
    public function definition(): array
    {
        return [
            'language' => $this->faker->randomElement(['uz', 'ru', 'en', 'kr']),
            'name' => $this->faker->words(2, true),
        ];
    }
}
