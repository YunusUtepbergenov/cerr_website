<?php

namespace Database\Factories;

use App\Models\OpenData;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OpenData> */
class OpenDataFactory extends Factory
{
    protected $model = OpenData::class;

    public function definition(): array
    {
        return [
            'year' => $this->faker->numberBetween(2020, 2026),
            'quarter' => $this->faker->numberBetween(1, 4),
            'file_path' => 'open-data/'.$this->faker->uuid().'.pdf',
            'file_name' => $this->faker->slug(2).'.pdf',
            'file_size' => $this->faker->numberBetween(1024, 5_000_000),
            'file_mime' => 'application/pdf',
            'download_count' => 0,
            'is_published' => true,
            'user_id' => null,
        ];
    }
}
