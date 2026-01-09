<?php

namespace Database\Factories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    protected $model = Video::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'image' => 'video_'.fake()->numberBetween(1, 10).'.jpg',
            'url' => 'https://www.youtube.com/watch?v='.fake()->bothify('??????????'),
        ];
    }
}
