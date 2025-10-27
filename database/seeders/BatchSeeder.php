<?php

namespace Database\Seeders;

use App\Models\Batch;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Batch::factory()->create([
            'course_id' => 1,
            'name' => 'Batch 1 - 2024',
            'start_date' => '2024-01-05',
            'whatsapp_group_url' => 'https://kelasdrafter.id/',
        ]);

    }
}
