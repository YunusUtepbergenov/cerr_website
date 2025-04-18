<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\News;
use App\Models\NewsTranslation;
use App\Models\Tag;
use App\Models\User;
use Database\Factories\NewsTranslationsFactory;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        User::factory(5)->create(['role' => 'editor']);

        // Category::factory(5)->create();

        $this->call([
            CategorySeeder::class,
        ]);

        News::factory(1000)->create();
        NewsTranslation::factory(4000)->create();

        Tag::factory(10)->create();
    }
}
