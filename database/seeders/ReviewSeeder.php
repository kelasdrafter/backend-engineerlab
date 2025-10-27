<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Review::factory()->create([
            'user_id' => 1,
            'batch_id' => 1,
            'review' => 'Pengalaman belajar yang luar biasa, materinya sangat mudah dipahami.',
            'rating' => 5,
        ]);
    }
}
