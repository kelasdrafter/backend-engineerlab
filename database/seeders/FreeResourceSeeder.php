<?php

namespace Database\Seeders;

use App\Models\FreeResource;
use Illuminate\Database\Seeder;

class FreeResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FreeResource::factory()->create([
            'name' => 'Ebook Dasar Teknik Sipil',
            'thumbnail_url' => 'https://kelasdrafter.com/resources/thumbnails/ebook-teknik-sipil.jpg',
            'assets_url' => 'https://kelasdrafter.com/resources/ebooks/teknik-sipil-dasar.pdf',
            'tags' => 'teknik sipil, dasar, ebook',
            'description' => 'Ebook lengkap yang membahas dasar-dasar teknik sipil untuk pemula.',
        ]);
    }
}
