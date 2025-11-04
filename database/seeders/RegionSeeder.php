<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\RAB\Region;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table
        Region::truncate();
        
        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $regions = [
            // DKI Jakarta
            ['code' => 'JKT-01', 'name' => 'Jakarta Pusat', 'province' => 'DKI Jakarta', 'city' => 'Jakarta Pusat', 'type' => 'city'],
            ['code' => 'JKT-02', 'name' => 'Jakarta Utara', 'province' => 'DKI Jakarta', 'city' => 'Jakarta Utara', 'type' => 'city'],
            ['code' => 'JKT-03', 'name' => 'Jakarta Barat', 'province' => 'DKI Jakarta', 'city' => 'Jakarta Barat', 'type' => 'city'],
            ['code' => 'JKT-04', 'name' => 'Jakarta Selatan', 'province' => 'DKI Jakarta', 'city' => 'Jakarta Selatan', 'type' => 'city'],
            ['code' => 'JKT-05', 'name' => 'Jakarta Timur', 'province' => 'DKI Jakarta', 'city' => 'Jakarta Timur', 'type' => 'city'],

            // Jawa Barat
            ['code' => 'BDG', 'name' => 'Bandung', 'province' => 'Jawa Barat', 'city' => 'Bandung', 'type' => 'city'],
            ['code' => 'BGR', 'name' => 'Bogor', 'province' => 'Jawa Barat', 'city' => 'Bogor', 'type' => 'city'],
            ['code' => 'DPK', 'name' => 'Depok', 'province' => 'Jawa Barat', 'city' => 'Depok', 'type' => 'city'],
            ['code' => 'BKS', 'name' => 'Bekasi', 'province' => 'Jawa Barat', 'city' => 'Bekasi', 'type' => 'city'],
            ['code' => 'CRB', 'name' => 'Cirebon', 'province' => 'Jawa Barat', 'city' => 'Cirebon', 'type' => 'city'],

            // Jawa Tengah
            ['code' => 'SMG', 'name' => 'Semarang', 'province' => 'Jawa Tengah', 'city' => 'Semarang', 'type' => 'city'],
            ['code' => 'SKA', 'name' => 'Surakarta', 'province' => 'Jawa Tengah', 'city' => 'Surakarta', 'type' => 'city'],
            ['code' => 'TGL', 'name' => 'Tegal', 'province' => 'Jawa Tengah', 'city' => 'Tegal', 'type' => 'city'],

            // Jawa Timur
            ['code' => 'SBY', 'name' => 'Surabaya', 'province' => 'Jawa Timur', 'city' => 'Surabaya', 'type' => 'city'],
            ['code' => 'MLG', 'name' => 'Malang', 'province' => 'Jawa Timur', 'city' => 'Malang', 'type' => 'city'],
            ['code' => 'KDR', 'name' => 'Kediri', 'province' => 'Jawa Timur', 'city' => 'Kediri', 'type' => 'city'],
            ['code' => 'SDA', 'name' => 'Sidoarjo', 'province' => 'Jawa Timur', 'city' => 'Sidoarjo', 'type' => 'regency'],

            // Bali
            ['code' => 'DPS', 'name' => 'Denpasar', 'province' => 'Bali', 'city' => 'Denpasar', 'type' => 'city'],
            ['code' => 'GNY', 'name' => 'Gianyar', 'province' => 'Bali', 'city' => 'Gianyar', 'type' => 'regency'],

            // Sumatera
            ['code' => 'MDN', 'name' => 'Medan', 'province' => 'Sumatera Utara', 'city' => 'Medan', 'type' => 'city'],
            ['code' => 'PDG', 'name' => 'Padang', 'province' => 'Sumatera Barat', 'city' => 'Padang', 'type' => 'city'],
            ['code' => 'PLB', 'name' => 'Palembang', 'province' => 'Sumatera Selatan', 'city' => 'Palembang', 'type' => 'city'],
            ['code' => 'BDL', 'name' => 'Bandar Lampung', 'province' => 'Lampung', 'city' => 'Bandar Lampung', 'type' => 'city'],

            // Kalimantan
            ['code' => 'PTK', 'name' => 'Pontianak', 'province' => 'Kalimantan Barat', 'city' => 'Pontianak', 'type' => 'city'],
            ['code' => 'PLG', 'name' => 'Palangkaraya', 'province' => 'Kalimantan Tengah', 'city' => 'Palangkaraya', 'type' => 'city'],
            ['code' => 'BJM', 'name' => 'Banjarmasin', 'province' => 'Kalimantan Selatan', 'city' => 'Banjarmasin', 'type' => 'city'],
            ['code' => 'SMD', 'name' => 'Samarinda', 'province' => 'Kalimantan Timur', 'city' => 'Samarinda', 'type' => 'city'],

            // Sulawesi
            ['code' => 'MKS', 'name' => 'Makassar', 'province' => 'Sulawesi Selatan', 'city' => 'Makassar', 'type' => 'city'],
            ['code' => 'MDO', 'name' => 'Manado', 'province' => 'Sulawesi Utara', 'city' => 'Manado', 'type' => 'city'],
            ['code' => 'PLO', 'name' => 'Palu', 'province' => 'Sulawesi Tengah', 'city' => 'Palu', 'type' => 'city'],

            // Papua
            ['code' => 'JPR', 'name' => 'Jayapura', 'province' => 'Papua', 'city' => 'Jayapura', 'type' => 'city'],
        ];

        foreach ($regions as $region) {
            Region::create(array_merge($region, [
                'is_active' => true,
                'created_by' => 1, // Admin user
            ]));
        }
    }
}