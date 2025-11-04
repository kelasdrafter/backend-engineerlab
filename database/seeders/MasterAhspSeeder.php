<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\RAB\MasterAhsp;
use App\Models\RAB\MasterAhspItem;
use App\Models\RAB\AhspSource;
use App\Models\RAB\Item;

class MasterAhspSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate tables
        MasterAhspItem::truncate();
        MasterAhsp::truncate();
        
        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get AHSP Source (Cipta Karya)
        $ckSource = AhspSource::where('code', 'CK')->first();

        if (!$ckSource) {
            $this->command->warn('AHSP Source CK not found. Please run AhspSourceSeeder first.');
            return;
        }

        // Get Items
        $semen = Item::where('code', 'M-001')->first();
        $pasir = Item::where('code', 'M-002')->first();
        $pekerja = Item::where('code', 'L-001')->first();
        $tukangBatu = Item::where('code', 'L-002')->first();
        $mixer = Item::where('code', 'E-001')->first();

        if (!$semen || !$pasir || !$pekerja || !$tukangBatu) {
            $this->command->warn('Required items not found. Please run ItemSeeder first.');
            return;
        }

        // Sample Master AHSP: Pasangan Batu Kali
        $ahsp1 = MasterAhsp::create([
            'ahsp_source_id' => $ckSource->id,
            'code' => '5.1',
            'name' => 'Pasangan Batu Kali 1 Pc : 4 Ps',
            'unit' => 'm3',
            'description' => 'Pekerjaan pasangan batu kali dengan perbandingan 1 semen : 4 pasir',
            'is_active' => true,
            'created_by' => 1,
        ]);

        // Composition for AHSP 1
        MasterAhspItem::create([
            'master_ahsp_id' => $ahsp1->id,
            'category' => 'material',
            'item_id' => $semen->id,
            'coefficient' => 196.00,
            'sort_order' => 1,
        ]);

        MasterAhspItem::create([
            'master_ahsp_id' => $ahsp1->id,
            'category' => 'material',
            'item_id' => $pasir->id,
            'coefficient' => 0.52,
            'sort_order' => 2,
        ]);

        MasterAhspItem::create([
            'master_ahsp_id' => $ahsp1->id,
            'category' => 'labor',
            'item_id' => $pekerja->id,
            'coefficient' => 3.50,
            'sort_order' => 3,
        ]);

        MasterAhspItem::create([
            'master_ahsp_id' => $ahsp1->id,
            'category' => 'labor',
            'item_id' => $tukangBatu->id,
            'coefficient' => 1.75,
            'sort_order' => 4,
        ]);

        if ($mixer) {
            MasterAhspItem::create([
                'master_ahsp_id' => $ahsp1->id,
                'category' => 'equipment',
                'item_id' => $mixer->id,
                'coefficient' => 0.25,
                'sort_order' => 5,
            ]);
        }

        $this->command->info('Master AHSP seeded successfully!');
    }
}