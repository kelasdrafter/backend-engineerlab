<?php

namespace Database\Seeders;

use App\Models\Experience;
use Illuminate\Database\Seeder;

class ExperienceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Experience::factory()->create([
            'user_id' => '1',
            'job_title' => 'Software Engineer',
            'company_name' => 'KelasDrafter.id',
            'employment_type' => 'full_time',
            'start_date' => '2023-01-15',
            'end_date' => null,
            'location' => 'Jakarta, Indonesia'
        ]);

        Experience::factory()->create([
            'user_id' => '1',
            'job_title' => 'Software Engineer',
            'company_name' => 'KelasDrafter.id',
            'employment_type' => 'part_time',
            'start_date' => '2023-01-15',
            'end_date' => null,
            'location' => 'Jakarta, Indonesia'
        ]);

        Experience::factory()->create([
            'user_id' => '1',
            'job_title' => 'Software Engineer',
            'company_name' => 'KelasDrafter.id',
            'employment_type' => 'contract',
            'start_date' => '2023-01-15',
            'end_date' => null,
            'location' => 'Jakarta, Indonesia'
        ]);

        Experience::factory()->create([
            'user_id' => '1',
            'job_title' => 'Software Engineer',
            'company_name' => 'KelasDrafter.id',
            'employment_type' => 'internship',
            'start_date' => '2023-01-15',
            'end_date' => null,
            'location' => 'Jakarta, Indonesia'
        ]);

        Experience::factory()->create([
            'user_id' => '1',
            'job_title' => 'Software Engineer',
            'company_name' => 'KelasDrafter.id',
            'employment_type' => 'freelance',
            'start_date' => '2023-01-15',
            'end_date' => null,
            'location' => 'Jakarta, Indonesia'
        ]);
    }
}
