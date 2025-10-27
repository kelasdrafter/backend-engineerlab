<?php

namespace Database\Seeders;

use App\Models\Career;
use Illuminate\Database\Seeder;

class CareerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Career::factory()->create([
            'name' => 'Arsitek Junior',
            'description' => 'Posisi arsitek junior di perusahaan konstruksi ternama, membutuhkan keahlian dalam desain dan perencanaan.',
            'location' => 'Jakarta',
            'category_id' => 2,
            'is_active' => true,
        ]);
    }
}
