<?php

namespace Database\Seeders;

use App\Models\PortofolioImage;
use Illuminate\Database\Seeder;

class PortofolioImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PortofolioImage::factory()->create([
            'portfolio_id' => 1,
            'image_url' => 'https://kelasdrafter.com/resources/thumbnails/ebook-teknik-sipil.jpg',
        ]);

        PortofolioImage::factory()->create([
            'portfolio_id' => 1,
            'image_url' => 'https://kelasdrafter.com/resources/thumbnails/ebook-teknik-sipil.jpg',
        ]);

        PortofolioImage::factory()->create([
            'portfolio_id' => 2,
            'image_url' => 'https://kelasdrafter.com/resources/thumbnails/ebook-teknik-sipil.jpg',
        ]);

        PortofolioImage::factory()->create([
            'portfolio_id' => 2,
            'image_url' => 'https://kelasdrafter.com/resources/thumbnails/ebook-teknik-sipil.jpg',
        ]);

        PortofolioImage::factory()->create([
            'portfolio_id' => 3,
            'image_url' => 'https://kelasdrafter.com/resources/thumbnails/ebook-teknik-sipil.jpg',
        ]);

        PortofolioImage::factory()->create([
            'portfolio_id' => 3,
            'image_url' => 'https://kelasdrafter.com/resources/thumbnails/ebook-teknik-sipil.jpg',
        ]);
    }
}
