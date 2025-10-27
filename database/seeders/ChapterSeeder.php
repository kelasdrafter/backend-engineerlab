<?php

namespace Database\Seeders;

use App\Models\Chapter;
use Illuminate\Database\Seeder;

class ChapterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Chapter::factory()->create([
            'course_id' => 1,
            'name' => 'Pengenalan Teknik Sipil',
            'sequence' => 1,
        ]);

        Chapter::factory()->create([
            'course_id' => 1,
            'name' => 'Pengenalan Teknik Sipil',
            'sequence' => 2,
        ]);
    }
}
