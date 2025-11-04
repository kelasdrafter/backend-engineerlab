<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\RAB\Item;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table
        Item::truncate();
        
        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Material items
        $materials = [
            ['code' => 'M-001', 'name' => 'Semen Portland', 'type' => 'material', 'unit' => 'kg'],
            ['code' => 'M-002', 'name' => 'Pasir Pasang', 'type' => 'material', 'unit' => 'm3'],
            ['code' => 'M-003', 'name' => 'Batu Kali', 'type' => 'material', 'unit' => 'm3'],
            ['code' => 'M-004', 'name' => 'Besi Beton Polos', 'type' => 'material', 'unit' => 'kg'],
            ['code' => 'M-005', 'name' => 'Besi Beton Ulir', 'type' => 'material', 'unit' => 'kg'],
            ['code' => 'M-006', 'name' => 'Kawat Beton', 'type' => 'material', 'unit' => 'kg'],
            ['code' => 'M-007', 'name' => 'Kayu Meranti', 'type' => 'material', 'unit' => 'm3'],
            ['code' => 'M-008', 'name' => 'Paku', 'type' => 'material', 'unit' => 'kg'],
            ['code' => 'M-009', 'name' => 'Cat Tembok', 'type' => 'material', 'unit' => 'kg'],
            ['code' => 'M-010', 'name' => 'Keramik 40x40', 'type' => 'material', 'unit' => 'm2'],
        ];

        // Labor items
        $labors = [
            ['code' => 'L-001', 'name' => 'Pekerja', 'type' => 'labor', 'unit' => 'OH'],
            ['code' => 'L-002', 'name' => 'Tukang Batu', 'type' => 'labor', 'unit' => 'OH'],
            ['code' => 'L-003', 'name' => 'Tukang Kayu', 'type' => 'labor', 'unit' => 'OH'],
            ['code' => 'L-004', 'name' => 'Tukang Besi', 'type' => 'labor', 'unit' => 'OH'],
            ['code' => 'L-005', 'name' => 'Tukang Cat', 'type' => 'labor', 'unit' => 'OH'],
            ['code' => 'L-006', 'name' => 'Kepala Tukang', 'type' => 'labor', 'unit' => 'OH'],
            ['code' => 'L-007', 'name' => 'Mandor', 'type' => 'labor', 'unit' => 'OH'],
        ];

        // Equipment items
        $equipment = [
            ['code' => 'E-001', 'name' => 'Concrete Mixer', 'type' => 'equipment', 'unit' => 'jam'],
            ['code' => 'E-002', 'name' => 'Stamper', 'type' => 'equipment', 'unit' => 'jam'],
            ['code' => 'E-003', 'name' => 'Vibrator', 'type' => 'equipment', 'unit' => 'jam'],
            ['code' => 'E-004', 'name' => 'Water Pump', 'type' => 'equipment', 'unit' => 'jam'],
            ['code' => 'E-005', 'name' => 'Excavator', 'type' => 'equipment', 'unit' => 'jam'],
        ];

        // Insert all items
        $allItems = array_merge($materials, $labors, $equipment);

        foreach ($allItems as $item) {
            Item::create(array_merge($item, [
                'is_active' => true,
                'created_by' => 1, // Admin user
            ]));
        }
    }
}