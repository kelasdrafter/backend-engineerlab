<?php

namespace Database\Seeders;

use App\Models\PremiumProduct;
use App\Models\PremiumProductVideo;
use Illuminate\Database\Seeder;

class PremiumProductVideoSeeder extends Seeder
{
    public function run(): void
    {
        $products = PremiumProduct::all();

        $videoUrls = [
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'https://www.youtube.com/embed/9bZkp7q19f0',
            'https://www.youtube.com/embed/kJQP7kiw5Fk',
            'https://www.youtube.com/embed/OPf0YbXqDm0',
        ];

        $videoIndex = 0;
        foreach ($products as $product) {
            // 2 videos per product
            for ($i = 1; $i <= 2; $i++) {
                PremiumProductVideo::create([
                    'premium_product_id' => $product->id,
                    'video_url' => $videoUrls[$videoIndex % count($videoUrls)],
                    'sort_order' => $i,
                ]);
                $videoIndex++;
            }
        }
    }
}