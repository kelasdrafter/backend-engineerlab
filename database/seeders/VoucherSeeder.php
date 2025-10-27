<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example data 1
        Voucher::factory()->create([
            'code' => 'KODE100',
            'type' => 'Persentase',
            'nominal' => 75,
            'name' => 'Voucher Diskon Akhir Tahun',
            'quota' => 100,
            'description' => 'Ini adalah deskripsi untuk voucher diskon akhir tahun',
            'thumbnail_url' => 'https://kelasdrafter.id/_next/image?url=%2Fassets%2Ficons%2Flogo-sidebar.png&w=48&q=75',
            'start_at' => '2024-01-01',
            'end_at' => '2024-01-29',
            'is_public' => true,
            'is_repeatable' => false,
            'is_active' => true,
        ]);

        // Example data 2
        Voucher::factory()->create([
            'code' => 'KODE456',
            'type' => 'Fixed',
            'nominal' => 90000,
            'name' => 'Voucher Diskon Akhir Tahun',
            'quota' => 100,
            'description' => 'Ini adalah deskripsi untuk voucher diskon akhir tahun',
            'thumbnail_url' => 'https://kelasdrafter.id/_next/image?url=%2Fassets%2Ficons%2Flogo-sidebar.png&w=48&q=75',
            'start_at' => '2024-01-01',
            'end_at' => '2024-06-29',
            'is_public' => true,
            'is_repeatable' => false,
            'is_active' => true,
        ]);
    }
}
