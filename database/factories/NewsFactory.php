<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<News>
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
            'status' => 'published',
            'scheduled_at' => null,
        ];
    }
}
