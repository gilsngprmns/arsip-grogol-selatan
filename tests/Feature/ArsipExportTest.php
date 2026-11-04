<?php

namespace Tests\Feature;

use App\Models\DokumenArsip;
use App\Models\Seksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArsipExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_csv_has_utf8_bom(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seksi = Seksi::factory()->create();

        DokumenArsip::factory()->count(3)->create([
            'seksi_id' => $seksi->id,
            'dicatat_oleh' => $admin->id,
            'tanggal_surat' => '2026-11-03',
            'tanggal_diterima' => '2026-11-02',
        ]);

        $response = $this->actingAs($admin)->get('/arsip/export');
        $response->assertOk();

        $body = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $body);
    }

    public function test_export_csv_has_correct_headers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seksi = Seksi::factory()->create();

        DokumenArsip::factory()->create([
            'seksi_id' => $seksi->id,
            'dicatat_oleh' => $admin->id,
            'tanggal_surat' => '2026-11-03',
            'tanggal_diterima' => null,
        ]);

        $response = $this->actingAs($admin)->get('/arsip/export');
        $body = $response->streamedContent();

        // Strip BOM before parsing
        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);
        $lines = explode("\n", trim($body));

        // First line is header
        $header = str_getcsv($lines[0], ';');
        $expectedHeaders = [
            'Kode Arsip',
            'Nomor Surat',
            'Perihal',
            'Jenis Surat',
            'Sifat Surat',
            'Tanggal Surat',
            'Tanggal Diterima',
            'Asal Instansi',
            'Tujuan Instansi',
            'Seksi',
            'Status Arsip',
            'Catatan',
            'File Path',
        ];
        $this->assertEquals($expectedHeaders, $header);
    }

    public function test_export_csv_data_rows_use_consistent_delimiter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seksi = Seksi::factory()->create();

        DokumenArsip::factory()->create([
            'seksi_id' => $seksi->id,
            'dicatat_oleh' => $admin->id,
            'nomor_surat' => '123/ABC/2026',
            'perihal' => 'Surat uji , koma dan ; titik koma',
            'tanggal_surat' => '2026-11-03',
            'status_arsip' => 'tersedia',
        ]);

        $response = $this->actingAs($admin)->get('/arsip/export');
        $body = $response->streamedContent();

        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);
        $lines = explode("\n", trim($body));

        // Data row should be parseable with semicolon delimiter
        $dataRow = str_getcsv($lines[1], ';');
        $this->assertCount(13, $dataRow);
        $this->assertEquals('123/ABC/2026', $dataRow[1]);
        $this->assertEquals('Surat uji , koma dan ; titik koma', $dataRow[2]);
        $this->assertEquals('2026-11-03', $dataRow[5]);
        $this->assertEquals('tersedia', $dataRow[10]);
    }

    public function test_export_csv_date_format_is_yyyy_mm_dd(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seksi = Seksi::factory()->create();

        DokumenArsip::factory()->create([
            'seksi_id' => $seksi->id,
            'dicatat_oleh' => $admin->id,
            'tanggal_surat' => '2026-05-15',
            'tanggal_diterima' => '2026-05-16',
        ]);

        $response = $this->actingAs($admin)->get('/arsip/export');
        $body = $response->streamedContent();

        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);
        $lines = explode("\n", trim($body));
        $dataRow = str_getcsv($lines[1], ';');

        $this->assertEquals('2026-05-15', $dataRow[5]); // Tanggal Surat
        $this->assertEquals('2026-05-16', $dataRow[6]); // Tanggal Diterima
    }

    public function test_export_csv_null_date_becomes_empty_field(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seksi = Seksi::factory()->create();

        DokumenArsip::factory()->create([
            'seksi_id' => $seksi->id,
            'dicatat_oleh' => $admin->id,
            'tanggal_surat' => '2026-01-01',
            'tanggal_diterima' => null,
        ]);

        $response = $this->actingAs($admin)->get('/arsip/export');
        $body = $response->streamedContent();

        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);
        $lines = explode("\n", trim($body));
        $dataRow = str_getcsv($lines[1], ';');

        $this->assertEquals('2026-01-01', $dataRow[5]);
        $this->assertEquals('', $dataRow[6]);
    }

    public function test_export_csv_response_headers_are_correct(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Seksi::factory()->create();

        $response = $this->actingAs($admin)->get('/arsip/export');

        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringStartsWith('attachment; filename=arsip-dokumen-', $disposition);
        $this->assertStringEndsWith('.csv', $disposition);
    }

    public function test_export_csv_respects_active_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seksiA = Seksi::factory()->create(['nama_seksi' => 'Seksi A']);
        $seksiB = Seksi::factory()->create(['nama_seksi' => 'Seksi B']);

        DokumenArsip::factory()->create([
            'seksi_id' => $seksiA->id,
            'dicatat_oleh' => $admin->id,
            'status_arsip' => 'tersedia',
        ]);

        DokumenArsip::factory()->create([
            'seksi_id' => $seksiB->id,
            'dicatat_oleh' => $admin->id,
            'status_arsip' => 'dipinjam',
        ]);

        // Export with filter status=tersedia
        $response = $this->actingAs($admin)->get('/arsip/export?status=tersedia');
        $body = $response->streamedContent();

        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);
        $lines = explode("\n", trim($body));

        // Header + 1 data row (only 1 arsip with status tersedia)
        $this->assertCount(2, $lines);
    }

    public function test_staff_can_only_export_their_own_seksi(): void
    {
        $seksiA = Seksi::factory()->create(['nama_seksi' => 'Seksi A']);
        $seksiB = Seksi::factory()->create(['nama_seksi' => 'Seksi B']);

        $staffA = User::factory()->create(['role' => 'staf', 'seksi_id' => $seksiA->id]);
        $staffB = User::factory()->create(['role' => 'staf', 'seksi_id' => $seksiB->id]);

        DokumenArsip::factory()->create([
            'seksi_id' => $seksiA->id,
            'dicatat_oleh' => $staffA->id,
        ]);

        DokumenArsip::factory()->create([
            'seksi_id' => $seksiB->id,
            'dicatat_oleh' => $staffB->id,
        ]);

        // Staff A exports — should only see their own seksi
        $response = $this->actingAs($staffA)->get('/arsip/export');
        $body = $response->streamedContent();

        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);
        $lines = explode("\n", trim($body));

        // Header + 1 data row (only 1 from Seksi A)
        $this->assertCount(2, $lines);
    }
}
