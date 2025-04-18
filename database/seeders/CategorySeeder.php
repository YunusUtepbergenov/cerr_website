<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = ['uz', 'ru', 'en', 'kr'];

        Category::factory(10)->create()->each(function ($category) use ($languages) {
            foreach ($languages as $lang) {
                CategoryTranslation::factory()->create([
                    'category_id' => $category->id,
                    'language' => $lang,
                ]);
            }
        });
    }
}
