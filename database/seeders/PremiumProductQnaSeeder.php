<?php

namespace Database\Seeders;

use App\Models\PremiumProduct;
use App\Models\PremiumProductQna;
use Illuminate\Database\Seeder;

class PremiumProductQnaSeeder extends Seeder
{
    public function run(): void
    {
        $products = PremiumProduct::all();

        $qnas = [
            // For Revit Product
            [
                [
                    'question' => 'Apakah file ini compatible dengan Revit 2020?',
                    'answer' => 'Ya, semua family di paket ini compatible dengan Revit 2020 hingga 2024. Anda bisa menggunakannya tanpa masalah.',
                    'sort_order' => 1,
                ],
                [
                    'question' => 'Berapa jumlah family yang didapat?',
                    'answer' => 'Paket ini berisi 500+ Revit family yang sudah dikategorikan berdasarkan fungsi dan jenis elemen.',
                    'sort_order' => 2,
                ],
                [
                    'question' => 'Apakah ada tutorial cara menggunakan family ini?',
                    'answer' => 'Ya, setiap pembelian sudah include PDF guide dan video tutorial singkat cara load dan menggunakan family.',
                    'sort_order' => 3,
                ],
            ],
            // For SketchUp Product
            [
                [
                    'question' => 'Format file apa saja yang tersedia?',
                    'answer' => 'Tekstur tersedia dalam format JPG dan PNG dengan resolusi 4K. Sudah include bump maps dan normal maps untuk rendering.',
                    'sort_order' => 1,
                ],
                [
                    'question' => 'Apakah tekstur seamless?',
                    'answer' => 'Ya, semua tekstur sudah seamless dan bisa di-tile tanpa terlihat sambungan.',
                    'sort_order' => 2,
                ],
                [
                    'question' => 'Bisa dipakai di software lain selain SketchUp?',
                    'answer' => 'Tentu! Tekstur ini universal dan bisa dipakai di software 3D apapun seperti 3ds Max, Blender, Lumion, dll.',
                    'sort_order' => 3,
                ],
            ],
        ];

        foreach ($products as $index => $product) {
            foreach ($qnas[$index] as $qna) {
                PremiumProductQna::create([
                    'premium_product_id' => $product->id,
                    'question' => $qna['question'],
                    'answer' => $qna['answer'],
                    'sort_order' => $qna['sort_order'],
                ]);
            }
        }
    }
}