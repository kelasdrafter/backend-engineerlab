<?php

namespace Database\Seeders;

use App\Models\PremiumProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PremiumProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Complete Revit Family Pack - Architecture',
                'slug' => Str::slug('Complete Revit Family Pack - Architecture'),
                'description' => 'Paket lengkap Revit Family untuk arsitektur meliputi furniture, pintu, jendela, dan elemen bangunan lainnya. Total 500+ family siap pakai untuk mempercepat proses desain Anda. Compatible dengan Revit 2020-2024.',
                'price' => 250000,
                'discount_price' => 199000,
                'thumbnail_url' => 'https://picsum.photos/600/400?random=1',
                'file_url' => 'https://drive.google.com/file/d/1aB2cD3eF4gH5iJ6kL7mN8oP9qR0sT1uV/view',
                'view_count' => 150,
                'purchase_count' => 25,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'SketchUp Texture Library - Premium Materials',
                'slug' => Str::slug('SketchUp Texture Library - Premium Materials'),
                'description' => 'Koleksi tekstur premium untuk SketchUp dengan resolusi tinggi. Mencakup material kayu, batu, keramik, kain, dan metal. Total 300+ tekstur seamless dengan bump maps dan normal maps. Format: JPG, PNG.',
                'price' => 150000,
                'discount_price' => 0,
                'thumbnail_url' => 'https://picsum.photos/600/400?random=2',
                'file_url' => 'https://drive.google.com/file/d/2bC3dE4fG5hI6jK7lM8nO9pQ0rR1sS2tT/view',
                'view_count' => 89,
                'purchase_count' => 12,
                'is_featured' => false,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            PremiumProduct::create($product);
        }
    }
}