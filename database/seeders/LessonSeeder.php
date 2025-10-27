<?php

namespace Database\Seeders;

use App\Models\Lesson;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Lesson::factory()->create([
            'chapter_id' => 1,
            'name' => 'Sejarah Teknik Sipil',
            'sequence' => 1,
            'video_url' => 'https://example.com/video/sejarah-teknik-sipil',
            'description' => 'Pelajari tentang sejarah dan perkembangan teknik sipil dari waktu ke waktu.',
            'is_public' => true
        ]);

        Lesson::factory()->create([
            'chapter_id' => 1,
            'name' => 'Sejarah Teknik Sipil',
            'sequence' => 2,
            'video_url' => 'https://example.com/video/sejarah-teknik-sipil',
            'description' => 'Pelajari tentang sejarah dan perkembangan teknik sipil dari waktu ke waktu.',
        ]);

        Lesson::factory()->create([
            'chapter_id' => 1,
            'name' => 'Sejarah Teknik Sipil',
            'sequence' => 3,
            'video_url' => 'https://example.com/video/sejarah-teknik-sipil',
            'description' => 'Pelajari tentang sejarah dan perkembangan teknik sipil dari waktu ke waktu.',
        ]);
    }
}
