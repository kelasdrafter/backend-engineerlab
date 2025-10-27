<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Test user 1
        User::factory()->create([
            'name' => 'Admin KelasDrafter',
            'email' => 'admin@kelasdrafter.id',
            'role' => 'admin',
            'is_active' => 1,
            'password' => bcrypt('password')
        ]);

        // Test user 2
        User::factory()->create([
            'name' => 'User KelasDrafter',
            'email' => 'user@kelasdrafter.id',
            'role' => 'user',
            'is_active' => 1,
            'password' => bcrypt('password')
        ]);
        
        // Calling Additional Seeders
        $this->call([
            EnumerationSeeder::class,
            StudentServiceSeeder::class,
            CourseSeeder::class,
            BatchSeeder::class,
            FreeResourceSeeder::class,
            PortfolioSeeder::class,
            PortofolioImageSeeder::class,
            ExperienceSeeder::class,
            ReviewSeeder::class,
            CareerSeeder::class,
            VoucherSeeder::class,
            ChapterSeeder::class,
            LessonSeeder::class,
            
            // ✅ NEW: Premium Products Seeders
            PremiumProductSeeder::class,
            PremiumProductGallerySeeder::class,
            PremiumProductVideoSeeder::class,
            PremiumProductCompatibilitySeeder::class,
            PremiumProductQnaSeeder::class,
            PremiumProductReviewSeeder::class,
        ]);
        
    }
}