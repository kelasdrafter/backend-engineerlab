<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\RAB\AhspSource;

class AhspSourceSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table
        AhspSource::truncate();
        
        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $sources = [
            [
                'code' => 'CK',
                'name' => 'Cipta Karya',
                'description' => 'Analisa Harga Satuan Pekerjaan Cipta Karya',
                'icon' => 'building',
                'color' => '#3B82F6',
                'sort_order' => 1,
            ],
            [
                'code' => 'BM',
                'name' => 'Bina Marga',
                'description' => 'Analisa Harga Satuan Pekerjaan Bina Marga',
                'icon' => 'road',
                'color' => '#10B981',
                'sort_order' => 2,
            ],
            [
                'code' => 'SDA',
                'name' => 'Sumber Daya Air',
                'description' => 'Analisa Harga Satuan Pekerjaan Sumber Daya Air',
                'icon' => 'water',
                'color' => '#06B6D4',
                'sort_order' => 3,
            ],
            [
                'code' => 'ME',
                'name' => 'Mekanikal Elektrikal',
                'description' => 'Analisa Harga Satuan Pekerjaan Mekanikal & Elektrikal',
                'icon' => 'bolt',
                'color' => '#F59E0B',
                'sort_order' => 4,
            ],
            [
                'code' => 'CUSTOM',
                'name' => 'Custom AHSP',
                'description' => 'AHSP Kustom yang dibuat oleh user',
                'icon' => 'edit',
                'color' => '#8B5CF6',
                'sort_order' => 99,
            ],
        ];

        foreach ($sources as $source) {
            AhspSource::create(array_merge($source, [
                'is_active' => true,
                'created_by' => 1, // Admin user
            ]));
        }
    }
}