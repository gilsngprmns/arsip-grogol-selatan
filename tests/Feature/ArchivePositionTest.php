<?php

namespace Tests\Feature;

use App\Models\DokumenArsip;
use App\Models\LokasiArsip;
use App\Models\RakArsip;
use App\Models\Seksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchivePositionTest extends TestCase
{
    use RefreshDatabase;

    public function test_archive_can_be_saved_with_rack_and_physical_position(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'staf']);
        $seksi = Seksi::create(['nama_seksi' => 'Pelayanan']);
        $lokasi = LokasiArsip::create([
            'kode_lokasi' => 'LOC-001',
            'nama_lokasi' => 'Ruang Arsip',
            'is_active' => true,
        ]);
        $rak = RakArsip::create([
            'lokasi_id' => $lokasi->id,
            'seksi_id' => $seksi->id,
            'kode_rak' => 'RAK-001',
            'nama_rak' => 'Rak Utama',
            'nomor_rak' => '1',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/arsip', [
            'kode_arsip' => 'ARSIP-001',
            'nomor_surat' => '001/ARSIP/2026',
            'perihal' => 'Dokumen pengujian',
            'jenis_surat' => 'masuk',
            'sifat_surat' => 'biasa',
            'tanggal_surat' => '2026-09-25',
            'tanggal_diterima' => '2026-09-25',
            'seksi_id' => $seksi->id,
            'rak_id' => $rak->id,
            'nomor_box' => 'BOX-01',
            'nomor_map' => 'MAP-02',
            'nomor_slot' => 'SLOT-03',
            'keterangan_posisi' => 'Rak bagian tengah',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/arsip');

        $this->assertDatabaseHas('dokumen_arsip', [
            'kode_arsip' => 'ARSIP-001',
            'rak_id' => $rak->id,
            'nomor_box' => 'BOX-01',
            'nomor_map' => 'MAP-02',
            'nomor_slot' => 'SLOT-03',
        ]);

        $this->assertSame('Ruang Arsip', DokumenArsip::firstOrFail()->rak->lokasiArsip->nama_lokasi);
    }

    public function test_archive_rejects_rack_from_another_seksi(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'staf']);
        $seksiA = Seksi::create(['nama_seksi' => 'Pemerintahan']);
        $seksiB = Seksi::create(['nama_seksi' => 'Kesejahteraan Rakyat']);
        $lokasi = LokasiArsip::create([
            'kode_lokasi' => 'LOC-001',
            'nama_lokasi' => 'Ruang Arsip',
            'is_active' => true,
        ]);
        $rakB = RakArsip::create([
            'lokasi_id' => $lokasi->id,
            'seksi_id' => $seksiB->id,
            'kode_rak' => 'RAK-KSR-01',
            'nama_rak' => 'Rak No. 1 Seksi Kesejahteraan',
            'nomor_rak' => '1',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->from('/arsip/create')->post('/arsip', [
            'kode_arsip' => 'ARSIP-002',
            'nomor_surat' => '002/ARSIP/2026',
            'perihal' => 'Dokumen salah rak',
            'jenis_surat' => 'masuk',
            'sifat_surat' => 'biasa',
            'tanggal_surat' => '2026-09-25',
            'seksi_id' => $seksiA->id,
            'rak_id' => $rakB->id,
        ]);

        $response->assertSessionHasErrors('rak_id');
        $this->assertDatabaseMissing('dokumen_arsip', ['kode_arsip' => 'ARSIP-002']);
    }
}