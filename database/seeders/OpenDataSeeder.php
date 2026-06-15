<?php

namespace Database\Seeders;

use App\Models\OpenData;
use Illuminate\Database\Seeder;

class OpenDataSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            ['year' => 2026, 'quarter' => 1, 'titles' => [
                'uz' => 'Asosiy vositalarni xarid qilish to\'g\'risidagi ma\'lumotlar',
                'ru' => 'Сведения о закупке основных средств',
                'en' => 'Information on the purchase of fixed assets',
                'kr' => 'Асосий воситаларни харид қилиш тўғрисидаги маълумотлар',
            ]],
            ['year' => 2025, 'quarter' => 4, 'titles' => [
                'uz' => 'Murojaatlar natijalari',
                'ru' => 'Результаты обращений',
                'en' => 'Results of applications',
                'kr' => 'Мурожаатлар натижалари',
            ]],
        ];

        foreach ($samples as $sample) {
            $entry = OpenData::factory()->create([
                'year' => $sample['year'],
                'quarter' => $sample['quarter'],
            ]);
            foreach ($sample['titles'] as $language => $title) {
                $entry->translations()->create(['language' => $language, 'title' => $title]);
            }
        }
    }
}
