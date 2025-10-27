<?php

namespace Database\Seeders;

use App\Models\PremiumProduct;
use App\Models\PremiumProductCompatibility;
use Illuminate\Database\Seeder;

class PremiumProductCompatibilitySeeder extends Seeder
{
    public function run(): void
    {
        $products = PremiumProduct::all();

        $compatibilities = [
            // For Revit Product (Product ID 1)
            [
                ['compatibility_text' => 'Autodesk Revit 2024', 'sort_order' => 1],
                ['compatibility_text' => 'Autodesk Revit 2023', 'sort_order' => 2],
                ['compatibility_text' => 'Autodesk Revit 2022', 'sort_order' => 3],
            ],
            // For SketchUp Product (Product ID 2)
            [
                ['compatibility_text' => 'SketchUp Pro 2024', 'sort_order' => 1],
                ['compatibility_text' => 'SketchUp Pro 2023', 'sort_order' => 2],
                ['compatibility_text' => 'SketchUp Make 2017', 'sort_order' => 3],
            ],
        ];

        $index = 0;
        foreach ($products as $product) {
            foreach ($compatibilities[$index] as $compatibility) {
                PremiumProductCompatibility::create([
                    'premium_product_id' => $product->id,
                    'compatibility_text' => $compatibility['compatibility_text'],
                    'sort_order' => $compatibility['sort_order'],
                ]);
            }
            $index++;
        }
    }
}