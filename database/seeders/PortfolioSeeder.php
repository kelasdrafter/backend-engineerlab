<?php

namespace Database\Seeders;

use App\Models\Portfolio;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Portfolio::factory()->create([
            'user_id' => '1',
            'title' => 'Desain Web E-Commerce',
            'status' => 'publish',
            'description' => 'Desain web untuk e-commerce yang menjual berbagai macam produk elektronik.',
        ]);

        Portfolio::factory()->create([
            'user_id' => '1',
            'title' => 'Desain Web E-Commerce',
            'status' => 'on_review',
            'description' => 'Desain web untuk e-commerce yang menjual berbagai macam produk elektronik.',
        ]);

        Portfolio::factory()->create([
            'user_id' => '1',
            'title' => 'Desain Web E-Commerce',
            'status' => 'reject',
            'description' => 'Desain web untuk e-commerce yang menjual berbagai macam produk elektronik.',
        ]);
    }
}
