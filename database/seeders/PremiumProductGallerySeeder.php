<?php

namespace Database\Seeders;

use App\Models\PremiumProduct;
use App\Models\PremiumProductGallery;
use Illuminate\Database\Seeder;

class PremiumProductGallerySeeder extends Seeder
{
    public function run(): void
    {
        $products = PremiumProduct::all();

        foreach ($products as $index => $product) {
            // 3 galleries per product
            for ($i = 1; $i <= 3; $i++) {
                PremiumProductGallery::create([
                    'premium_product_id' => $product->id,
                    'image_url' => 'https://picsum.photos/800/600?random=' . (($index * 10) + $i),
                    'sort_order' => $i,
                ]);
            }
        }
    }
}