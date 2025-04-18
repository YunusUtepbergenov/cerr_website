<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = News::class;
    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->first()->id ?? Category::factory(),
            'user_id' => User::where('role', 'editor')->inRandomOrder()->first()->id ?? User::factory(),
            'slug' => Str::slug($this->faker->sentence),
            'is_main' => $this->faker->randomElement([true, false]),
            'view_count' => $this->faker->randomNumber(4, false),
            'status' => $this->faker->randomElement(['draft', 'published', 'auto_publish', 'disabled']),
            'scheduled_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
        ];
    }
}
