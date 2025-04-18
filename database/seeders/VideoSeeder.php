<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $videos = [
            [
                'title' => 'Beautiful Nature Scenes',
                'image' => 'image1.jpg',
                'url' => 'https://www.youtube.com/embed/6v2L2UGZJAM',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'City Timelapse',
                'image' => 'image2.jpg',
                'url' => 'https://www.youtube.com/embed/7ZWBsJdQSdc',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Drone Footage of Mountains',
                'image' => 'image3.jpg',
                'url' => 'https://www.youtube.com/embed/RRZGnKPbY9E',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Ocean Waves',
                'image' => 'image4.jpg',
                'url' => 'https://www.youtube.com/embed/fIXByoDjHSc',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Space Exploration Documentary',
                'image' => 'image1.jpg',
                'url' => 'https://www.youtube.com/embed/qzMQza8xZCc',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Wildlife in Africa',
                'image' => 'image2.jpg',
                'url' => 'https://www.youtube.com/embed/lnKd7VQ9heU',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Northern Lights Timelapse',
                'image' => 'image3.jpg',
                'url' => 'https://www.youtube.com/embed/Rv3r2O6tSb4',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Deep Sea Creatures',
                'image' => 'image4.jpg',
                'url' => 'https://www.youtube.com/embed/6N4xmNGeCVc',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('videos')->insert($videos);
    }
}
