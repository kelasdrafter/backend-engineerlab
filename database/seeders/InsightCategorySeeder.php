<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InsightCategory;

class InsightCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Tips & Tricks',
                'slug' => 'tips-tricks',
                'description' => 'Bagikan tips dan trik menarik seputar dunia desain dan arsitektur',
                'icon' => '💡',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => 'Project Showcase',
                'slug' => 'project-showcase',
                'description' => 'Pamerkan project terbaik kamu dan dapatkan feedback dari komunitas',
                'icon' => '🏆',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'Tutorial',
                'slug' => 'tutorial',
                'description' => 'Tutorial step-by-step untuk berbagai software dan teknik desain',
                'icon' => '📚',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'name' => 'Tools & Software',
                'slug' => 'tools-software',
                'description' => 'Review dan rekomendasi tools, plugin, atau software terbaik',
                'icon' => '🛠️',
                'is_active' => true,
                'order' => 4,
            ],
            [
                'name' => 'Career & Industry',
                'slug' => 'career-industry',
                'description' => 'Diskusi seputar karir, industri, dan perkembangan profesi',
                'icon' => '💼',
                'is_active' => true,
                'order' => 5,
            ],
            [
                'name' => 'Discussion',
                'slug' => 'discussion',
                'description' => 'Diskusi umum seputar desain, arsitektur, dan topik terkait',
                'icon' => '💬',
                'is_active' => true,
                'order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            InsightCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('Insight categories seeded successfully!');
    }
}