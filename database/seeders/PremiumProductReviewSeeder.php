<?php

namespace Database\Seeders;

use App\Models\PremiumProduct;
use App\Models\PremiumProductReview;
use Illuminate\Database\Seeder;

class PremiumProductReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = PremiumProduct::all();

        $reviews = [
            // For Revit Product
            [
                [
                    'reviewer_name' => 'Budi Santoso',
                    'reviewer_photo' => 'https://i.pravatar.cc/150?img=11',
                    'review_text' => 'Sangat membantu! Family-nya lengkap dan detail. Menghemat waktu saya berjam-jam dalam project.',
                    'is_published' => true,
                ],
                [
                    'reviewer_name' => 'Siti Rahma',
                    'reviewer_photo' => 'https://i.pravatar.cc/150?img=5',
                    'review_text' => 'Worth it banget! Kualitas family bagus dan sesuai dengan standard Indonesia. Highly recommended!',
                    'is_published' => true,
                ],
                [
                    'reviewer_name' => 'Ahmad Hidayat',
                    'reviewer_photo' => 'https://i.pravatar.cc/150?img=12',
                    'review_text' => 'Koleksi yang sangat berguna untuk arsitek. File rapi dan mudah dipakai. Thanks KelasDrafter!',
                    'is_published' => true,
                ],
            ],
            // For SketchUp Product
            [
                [
                    'reviewer_name' => 'Rina Wijaya',
                    'reviewer_photo' => 'https://i.pravatar.cc/150?img=9',
                    'review_text' => 'Teksturnya bagus dan high quality. Rendering jadi lebih realistis. Puas dengan pembelian ini!',
                    'is_published' => true,
                ],
                [
                    'reviewer_name' => 'Dani Prasetyo',
                    'reviewer_photo' => 'https://i.pravatar.cc/150?img=13',
                    'review_text' => 'Koleksi material yang lengkap. Cocok untuk berbagai jenis project interior maupun eksterior.',
                    'is_published' => true,
                ],
                [
                    'reviewer_name' => 'Lina Kusuma',
                    'reviewer_photo' => 'https://i.pravatar.cc/150?img=10',
                    'review_text' => 'Kualitas tekstur sangat bagus dan seamless. File size juga reasonable. Recommended!',
                    'is_published' => true,
                ],
            ],
        ];

        foreach ($products as $index => $product) {
            foreach ($reviews[$index] as $review) {
                PremiumProductReview::create([
                    'premium_product_id' => $product->id,
                    'reviewer_name' => $review['reviewer_name'],
                    'reviewer_photo' => $review['reviewer_photo'],
                    'review_text' => $review['review_text'],
                    'is_published' => $review['is_published'],
                ]);
            }
        }
    }
}