<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InsightRank;

class InsightRankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ranks = [
            [
                'name' => 'Newbie',
                'slug' => 'newbie',
                'min_points' => 0,
                'max_points' => 49,
                'icon' => '🥉',
                'description' => 'Baru mulai, biasanya 0–10 insight/komentar',
                'order' => 1,
            ],
            [
                'name' => 'Explorer',
                'slug' => 'explorer',
                'min_points' => 50,
                'max_points' => 199,
                'icon' => '🧭',
                'description' => 'Sudah aktif berkontribusi beberapa kali',
                'order' => 2,
            ],
            [
                'name' => 'Collaborator',
                'slug' => 'collaborator',
                'min_points' => 200,
                'max_points' => 499,
                'icon' => '🤝',
                'description' => 'Sering membalas & memulai diskusi',
                'order' => 3,
            ],
            [
                'name' => 'Rising Star',
                'slug' => 'rising-star',
                'min_points' => 500,
                'max_points' => 999,
                'icon' => '🌟',
                'description' => 'Mulai dikenal, aktif posting insight',
                'order' => 4,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'min_points' => 1000,
                'max_points' => 1999,
                'icon' => '💼',
                'description' => 'Sangat aktif, kontribusi rutin & berkualitas',
                'order' => 5,
            ],
            [
                'name' => 'Grand Master',
                'slug' => 'grand-master',
                'min_points' => 2000,
                'max_points' => null,
                'icon' => '👑',
                'description' => 'Legenda komunitas',
                'order' => 6,
            ],
        ];

        foreach ($ranks as $rank) {
            InsightRank::updateOrCreate(
                ['slug' => $rank['slug']],
                $rank
            );
        }

        $this->command->info('Insight ranks seeded successfully!');
    }
}