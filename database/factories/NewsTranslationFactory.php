<?php

namespace Database\Factories;

use App\Models\News;
use App\Models\NewsTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class NewsTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = NewsTranslation::class;

    public $image_array = ['1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg', '6.jpg', '7.jpg', '8.jpg', '9.jpg', '10.jpg', '11.jpg'];

    public function definition(): array
    {
        return [
            'news_id' => News::inRandomOrder()->first()->id ?? News::factory(),
            'lang' => $this->faker->randomElement(['uz', 'kr', 'ru', 'en']),
            'title' => $this->faker->sentence,
            'short_description' => $this->faker->paragraphs(1, true),
            'content' => $this->faker->text(800),
            'image_url' => $this->faker->randomElement($this->image_array),
        ];
    }
}
