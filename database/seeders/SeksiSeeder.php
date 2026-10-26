<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['nama_seksi' => 'Pemerintahan', 'keterangan' => 'Mengurus administrasi pemerintahan umum'],
            ['nama_seksi' => 'Ekonomi Pembangunan', 'keterangan' => 'Mengurus urusan ekonomi dan pembangunan wilayah'],
            ['nama_seksi' => 'Kesejahteraan Rakyat', 'keterangan' => 'Mengurus urusan kesejahteraan rakyat'],
        ];

        foreach ($rows as $row) {
            DB::table('seksi')->updateOrInsert(
                ['nama_seksi' => $row['nama_seksi']],
                [
                    'keterangan' => $row['keterangan'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }
}
