<?php

namespace Database\Seeders;

use App\Models\Enumeration;
use Illuminate\Database\Seeder;

class EnumerationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // EnumerationSeeder.php
        Enumeration::factory()->create([
            'name' => 'Teknik Sipil',
            'value' => 'civil_engineering',
            'group' => 'course_category',
            'is_active' => true,
        ]);

        Enumeration::factory()->create([
            'name' => 'Arsitektur',
            'value' => 'architecture',
            'group' => 'course_category',
            'is_active' => true,
        ]);

    }
}
