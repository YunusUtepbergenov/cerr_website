<?php

namespace Database\Factories;

use App\Models\Journal;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalFactory extends Factory
{
    protected $model = Journal::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'cover_image' => 'journals/'.fake()->uuid().'.jpg',
            'link' => fake()->url(),
            'published_at' => fake()->dateTimeBetween('-1 year')->format('Y-m-d'),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
