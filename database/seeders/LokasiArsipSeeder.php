<?php

namespace Database\Seeders;

use App\Models\LokasiArsip;
use Illuminate\Database\Seeder;

class LokasiArsipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lokasi = [
            [
                'kode_lokasi' => 'LOC-001',
                'nama_lokasi' => 'Ruang Arsip Lantai 1',
                'gedung' => 'Gedung Utama',
                'lantai' => '1',
                'ruangan' => 'Ruang Arsip',
                'deskripsi' => 'Lokasi utama arsip kelurahan',
                'is_active' => true,
            ],
            [
                'kode_lokasi' => 'LOC-TEST',
                'nama_lokasi' => 'Lokasi Testing',
                'gedung' => 'Gedung Testing',
                'lantai' => 'Test',
                'ruangan' => 'Ruang Uji',
                'deskripsi' => 'Lokasi untuk data uji dan pengembangan sistem arsip',
                'is_active' => true,
            ],
        ];

        foreach ($lokasi as $item) {
            LokasiArsip::query()->updateOrCreate(
                ['kode_lokasi' => $item['kode_lokasi']],
                $item,
            );
        }
    }
}
