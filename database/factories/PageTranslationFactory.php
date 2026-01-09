<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageTranslationFactory extends Factory
{
    protected $model = PageTranslation::class;

    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'language' => fake()->randomElement(['uz', 'kr', 'en', 'ru']),
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(5, true),
            'image' => fake()->imageUrl(),
            'seo_title' => fake()->sentence(),
            'seo_description' => fake()->paragraph(),
        ];
    }
}
