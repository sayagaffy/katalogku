<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themes = [
            [
                'name' => 'Nusantara',
                'key' => 'nusantara',
                'palette' => [
                    'primary' => '#16a34a',
                    'background' => '#f7fee7',
                    'text' => '#071a0f',
                    'accent' => '#65a30d',
                ],
                'preview_image' => null,
                'is_default' => true,
            ],
            [
                'name' => 'Laut Biru',
                'key' => 'laut_biru',
                'palette' => [
                    'primary' => '#2563eb',
                    'background' => '#eff6ff',
                    'text' => '#0b1220',
                    'accent' => '#60a5fa',
                ],
                'preview_image' => null,
                'is_default' => false,
            ],
            [
                'name' => 'Kopi Susu',
                'key' => 'kopi_susu',
                'palette' => [
                    'primary' => '#a16207',
                    'background' => '#fffbeb',
                    'text' => '#211708',
                    'accent' => '#fbbf24',
                ],
                'preview_image' => null,
                'is_default' => false,
            ],
        ];

        foreach ($themes as $t) {
            Theme::updateOrCreate(
                ['key' => $t['key']],
                [
                    'name' => $t['name'],
                    'palette' => $t['palette'],
                    'preview_image' => $t['preview_image'],
                    'is_default' => $t['is_default'],
                ]
            );
        }
    }
}

