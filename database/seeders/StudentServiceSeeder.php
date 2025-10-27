<?php

namespace Database\Seeders;

use App\Models\StudentService;
use Illuminate\Database\Seeder;

class StudentServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StudentService::factory()->create([
            'name' => 'Konsultasi Karir',
            'thumbnail_url' => 'https://www.liberiangeek.net/wp-content/uploads/2019/03/Librecad-Tutorial-Everything-You-Need-to-Know.png',
            'redirect_url' => 'https://kelasdrafter.com/services/career-consultation',
        ]);

        StudentService::factory()->create([
            'name' => 'Bantuan Keuangan',
            'thumbnail_url' => 'https://www.liberiangeek.net/wp-content/uploads/2019/03/Librecad-Tutorial-Everything-You-Need-to-Know.png',
            'redirect_url' => 'https://kelasdrafter.com/services/financial-aid',
        ]);
    }
}
