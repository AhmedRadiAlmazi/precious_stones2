<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'ألماس',
                'slug' => 'diamond',
                'description' => 'أحجار الألماس الطبيعية والنادرة',
                'is_active' => true,
            ],
            [
                'name' => 'ياقوت',
                'slug' => 'ruby',
                'description' => 'أحجار الياقوت الأحمر الفاخرة',
                'is_active' => true,
            ],
            [
                'name' => 'زمرد',
                'slug' => 'emerald',
                'description' => 'أحجار الزمرد الأخضر النقية',
                'is_active' => true,
            ],
            [
                'name' => 'ياقوت أزرق',
                'slug' => 'sapphire',
                'description' => 'أحجار الياقوت الأزرق الملكية',
                'is_active' => true,
            ],
            [
                'name' => 'عقيق',
                'slug' => 'agate',
                'description' => 'أحجار العقيق اليمني والطبيعي',
                'is_active' => true,
            ],
            [
                'name' => 'توباز',
                'slug' => 'topaz',
                'description' => 'أحجار التوباز الملونة',
                'is_active' => true,
            ],
            [
                'name' => 'لؤلؤ',
                'slug' => 'pearl',
                'description' => 'اللؤلؤ الطبيعي والمستزرع',
                'is_active' => true,
            ],
            [
                'name' => 'أحجار نادرة',
                'slug' => 'rare-stones',
                'description' => 'أحجار كريمة نادرة ومميزة',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'description' => $category['description'],
                'is_active' => $category['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Categories seeded successfully!');
        $this->command->info('Created ' . count($categories) . ' categories');
    }
}
