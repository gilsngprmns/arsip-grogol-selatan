<?php

namespace Database\Seeders;

use App\Models\LokasiArsip;
use App\Models\RakArsip;
use App\Models\Seksi;
use Illuminate\Database\Seeder;

class RakArsipSeeder extends Seeder
{
    /**
     * Seed rak fisik sesuai pengarsipan manual:
     * tiap seksi punya rak bernomor (label: "RAK NO X SEKSI …").
     */
    public function run(): void
    {
        $lokasi = LokasiArsip::query()->firstOrCreate(
            ['kode_lokasi' => 'LOC-001'],
            [
                'nama_lokasi' => 'Ruang Arsip Lantai 1',
                'gedung' => 'Gedung Utama',
                'lantai' => '1',
                'ruangan' => 'Ruang Arsip',
                'deskripsi' => 'Lokasi utama arsip kelurahan',
                'is_active' => true,
            ],
        );

        $seksiByName = Seksi::query()
            ->get()
            ->keyBy(fn (Seksi $seksi) => $seksi->nama_seksi);

        // Sesuai label fisik: RAK NO + seksi pemilik
        $racks = [
            [
                'kode_rak' => 'RAK-PEM-01',
                'nama_rak' => 'Rak No. 1 Seksi Pemerintahan',
                'nomor_rak' => '1',
                'seksi' => 'Pemerintahan',
                'kapasitas' => 200,
                'deskripsi' => 'Rak fisik label: RAK NO 1 SEKSI PEMERINTAHAN',
            ],
            [
                'kode_rak' => 'RAK-PEM-02',
                'nama_rak' => 'Rak No. 2 Seksi Pemerintahan',
                'nomor_rak' => '2',
                'seksi' => 'Pemerintahan',
                'kapasitas' => 200,
                'deskripsi' => 'Rak tambahan Seksi Pemerintahan',
            ],
            [
                'kode_rak' => 'RAK-EKB-01',
                'nama_rak' => 'Rak No. 1 Seksi Ekonomi Pembangunan',
                'nomor_rak' => '1',
                'seksi' => 'Ekonomi Pembangunan',
                'kapasitas' => 200,
                'deskripsi' => 'Rak fisik Seksi Ekonomi Pembangunan (EKOBANG)',
            ],
            [
                'kode_rak' => 'RAK-EKB-02',
                'nama_rak' => 'Rak No. 2 Seksi Ekonomi Pembangunan',
                'nomor_rak' => '2',
                'seksi' => 'Ekonomi Pembangunan',
                'kapasitas' => 200,
                'deskripsi' => 'Rak tambahan Seksi Ekonomi Pembangunan',
            ],
            [
                'kode_rak' => 'RAK-KSR-01',
                'nama_rak' => 'Rak No. 1 Seksi Kesejahteraan Rakyat',
                'nomor_rak' => '1',
                'seksi' => 'Kesejahteraan Rakyat',
                'kapasitas' => 200,
                'deskripsi' => 'Rak fisik label: RAK NO … SEKSI KESEJAHTERAAN RAKYAT',
            ],
            [
                'kode_rak' => 'RAK-KSR-02',
                'nama_rak' => 'Rak No. 2 Seksi Kesejahteraan Rakyat',
                'nomor_rak' => '2',
                'seksi' => 'Kesejahteraan Rakyat',
                'kapasitas' => 200,
                'deskripsi' => 'Rak fisik label: RAK NO 2 SEKSI KESEJAHTERAAN RAKYAT',
            ],
        ];

        foreach ($racks as $rack) {
            $seksi = $seksiByName->get($rack['seksi']);

            RakArsip::query()->updateOrCreate(
                ['kode_rak' => $rack['kode_rak']],
                [
                    'lokasi_id' => $lokasi->id,
                    'seksi_id' => $seksi?->id,
                    'nama_rak' => $rack['nama_rak'],
                    'nomor_rak' => $rack['nomor_rak'],
                    'kapasitas' => $rack['kapasitas'],
                    'deskripsi' => $rack['deskripsi'],
                    'is_active' => true,
                ],
            );
        }
    }
}
