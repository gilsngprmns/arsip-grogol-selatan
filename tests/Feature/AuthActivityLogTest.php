<?php

namespace Tests\Feature;

use App\Models\DokumenArsip;
use App\Models\Seksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_creates_activity_log(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'auth.login',
        ]);
    }

    public function test_logout_creates_activity_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'auth.logout',
        ]);
    }

    public function test_login_logout_appear_in_activity_log_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => 'password']);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response = $this->actingAs($admin)->get('/activity-log');
        $response->assertOk();
        $response->assertSee('auth.login', false);
    }

    public function test_staff_arsip_store_creates_activity_log_visible_to_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staf  = User::factory()->create(['role' => 'staf']);
        $seksi = Seksi::create(['nama_seksi' => 'Seksi Test']);

        $this->actingAs($staf)->post('/arsip', [
            'kode_arsip'    => 'STF-LOG-001',
            'nomor_surat'   => '001/STF/LOG',
            'perihal'       => 'Dokumen Test Staf',
            'jenis_surat'   => 'masuk',
            'tanggal_surat' => '2026-11-01',
            'seksi_id'      => $seksi->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $staf->id,
            'action'  => 'arsip.store',
        ]);

        $this->actingAs($admin)->get('/activity-log')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Activity/Index')
                ->where('activities.data.0.action', 'arsip.store')
            );
    }

    public function test_staff_arsip_destroy_creates_activity_log_visible_to_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staf  = User::factory()->create(['role' => 'staf']);
        $seksi = Seksi::create(['nama_seksi' => 'Seksi Test']);

        $this->actingAs($staf)->post('/arsip', [
            'kode_arsip'    => 'STF-DEL-001',
            'nomor_surat'   => '002/STF/DEL',
            'perihal'       => 'Dokumen Hapus',
            'jenis_surat'   => 'keluar',
            'tanggal_surat' => '2026-11-01',
            'seksi_id'      => $seksi->id,
        ]);

        $arsip = DokumenArsip::where('kode_arsip', 'STF-DEL-001')->first();
        $this->actingAs($staf)->delete("/arsip/{$arsip->id}");

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $staf->id,
            'action'  => 'arsip.destroy',
        ]);

        // Order-independent: assert both actions exist regardless of page position.
        // DB is refreshed per test; admin sees only the two actions created above.
        $this->actingAs($admin)->get('/activity-log')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Activity/Index')
                ->where('activities.total', 2)
                ->has('activities.data', 2)
            );
        // Verify both actions are present (regardless of index ordering).
        $log = \App\Models\ActivityLog::query()
            ->whereIn('action', ['arsip.store', 'arsip.destroy'])
            ->where('user_id', $staf->id)
            ->pluck('action')
            ->sort()
            ->values();
        $this->assertEquals(['arsip.destroy', 'arsip.store'], $log->all());
    }

    public function test_staff_arsip_store_via_inertia_header_creates_activity_log(): void
    {
        $staf  = User::factory()->create(['role' => 'staf']);
        $seksi = Seksi::create(['nama_seksi' => 'Seksi Test']);

        $this->actingAs($staf)
            ->withHeaders(['X-Inertia' => 'true'])
            ->post('/arsip', [
                'kode_arsip'    => 'STF-INERTIA-001',
                'nomor_surat'   => '003/STF/INERTIA',
                'perihal'       => 'Dokumen Inertia Flow',
                'jenis_surat'   => 'masuk',
                'tanggal_surat' => '2026-11-01',
                'seksi_id'      => $seksi->id,
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $staf->id,
            'action'  => 'arsip.store',
        ]);
    }

    public function test_staff_arsip_update_via_inertia_header_creates_activity_log(): void
    {
        $staf  = User::factory()->create(['role' => 'staf']);
        $seksi = Seksi::create(['nama_seksi' => 'Seksi Test']);

        $this->actingAs($staf)->post('/arsip', [
            'kode_arsip'    => 'STF-UPD-001',
            'nomor_surat'   => '004/STF/UPD',
            'perihal'       => 'Dokumen Update Inertia',
            'jenis_surat'   => 'masuk',
            'tanggal_surat' => '2026-11-01',
            'seksi_id'      => $seksi->id,
        ]);

        $arsip = DokumenArsip::where('kode_arsip', 'STF-UPD-001')->first();

        $this->actingAs($staf)
            ->withHeaders(['X-Inertia' => 'true'])
            ->put("/arsip/{$arsip->id}", [
                'kode_arsip'    => 'STF-UPD-001',
                'nomor_surat'   => '004/STF/UPD',
                'perihal'       => 'Dokumen Update Inertia v2',
                'jenis_surat'   => 'masuk',
                'tanggal_surat' => '2026-11-01',
                'seksi_id'      => $seksi->id,
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $staf->id,
            'action'  => 'arsip.update',
        ]);
    }

    // ── Fix #93: Deteksi kegagalan ──────────────────────────────────────────

    public function test_failed_arsip_store_logs_gagal_description(): void
    {
        $staf  = User::factory()->create(['role' => 'staf']);
        $seksi = Seksi::create(['nama_seksi' => 'Seksi Duplikat']);

        // Buat arsip pertama
        DokumenArsip::create([
            'kode_arsip' => 'DUP-001',
            'nomor_surat' => '010/DUP',
            'perihal'     => 'Dokumen duplikat test',
            'jenis_surat' => 'masuk',
            'tanggal_surat' => '2026-11-01',
            'seksi_id'   => $seksi->id,
            'dicatat_oleh' => $staf->id,
        ]);

        // Coba buat dengan kode yang sama → validasi unique gagal
        $this->actingAs($staf)->post('/arsip', [
            'kode_arsip'    => 'DUP-001',
            'nomor_surat'   => '011/DUP',
            'perihal'       => 'Dokumen duplikat test 2',
            'jenis_surat'   => 'masuk',
            'tanggal_surat' => '2026-11-01',
            'seksi_id'      => $seksi->id,
        ]);

        $log = \App\Models\ActivityLog::where('user_id', $staf->id)
            ->where('action', 'arsip.store')
            ->latest()->first();

        $this->assertNotNull($log, 'Log harus dibuat untuk store gagal');
        $this->assertStringStartsWith('Gagal', $log->description);
        $this->assertSame('gagal', $log->metadata['hasil'] ?? null);
    }

    public function test_successful_arsip_store_logs_sukses(): void
    {
        $staf  = User::factory()->create(['role' => 'staf']);
        $seksi = Seksi::create(['nama_seksi' => 'Seksi Sukses']);

        $this->actingAs($staf)->post('/arsip', [
            'kode_arsip'    => 'OK-001',
            'nomor_surat'   => '020/OK',
            'perihal'       => 'Dokumen sukses test',
            'jenis_surat'   => 'masuk',
            'tanggal_surat' => '2026-11-01',
            'seksi_id'      => $seksi->id,
        ]);

        $log = \App\Models\ActivityLog::where('user_id', $staf->id)
            ->where('action', 'arsip.store')
            ->latest()->first();

        $this->assertNotNull($log);
        $this->assertStringStartsWith('Menambahkan', $log->description, 'Sukses tidak boleh diawali "Gagal"');
        $this->assertSame('sukses', $log->metadata['hasil'] ?? null);
    }

    public function test_failed_tandahilang_logs_gagal(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seksi = Seksi::create(['nama_seksi' => 'Seksi Hilang']);
        $lokasi = \App\Models\LokasiArsip::create([
            'kode_lokasi' => 'LOC-FAIL',
            'nama_lokasi' => 'Ruang Fail',
            'is_active'   => true,
        ]);
        $rak = \App\Models\RakArsip::create([
            'lokasi_id' => $lokasi->id,
            'seksi_id'  => $seksi->id,
            'kode_rak'  => 'RAK-FAIL',
            'nama_rak'  => 'Rak Fail',
            'nomor_rak' => '1',
            'is_active' => true,
        ]);

        // Arsip yang sudah hilang
        $arsip = DokumenArsip::create([
            'kode_arsip'     => 'HILANG-FAIL',
            'nomor_surat'    => '030/HILANG',
            'perihal'        => 'Dokumen sudah hilang',
            'jenis_surat'    => 'masuk',
            'tanggal_surat'  => '2026-11-01',
            'seksi_id'       => $seksi->id,
            'rak_id'         => $rak->id,
            'status_arsip'   => 'hilang',
            'dicatat_oleh'   => $admin->id,
        ]);

        $this->actingAs($admin)->post("/arsip/{$arsip->id}/tandahilang", []);

        $log = \App\Models\ActivityLog::where('user_id', $admin->id)
            ->where('action', 'arsip.tandahilang')
            ->latest()->first();

        $this->assertNotNull($log, 'Log harus dibuat untuk tandahilang gagal');
        $this->assertStringStartsWith('Gagal', $log->description);
        $this->assertSame('gagal', $log->metadata['hasil'] ?? null);
    }

    // ── Fix #94: Pindai QR arsip tercatat di Activity Log ───────────────────

    public function test_scan_by_kode_success_creates_activity_log(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staf  = User::factory()->create(['role' => 'staf']);
        $seksi = Seksi::create(['nama_seksi' => 'Seksi Scan']);

        $arsip = DokumenArsip::create([
            'kode_arsip'    => 'SCAN-OK-001',
            'nomor_surat'   => '050/SCAN',
            'perihal'       => 'Dokumen scan sukses',
            'jenis_surat'   => 'masuk',
            'tanggal_surat' => '2026-11-01',
            'seksi_id'      => $seksi->id,
            'dicatat_oleh'  => $admin->id,
        ]);

        $this->actingAs($staf)->post('/api/arsip/scan', [
            'kode_arsip' => 'SCAN-OK-001',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id'    => $staf->id,
            'action'     => 'arsip.scan',
            'route_name' => 'arsip.scan',
            'method'     => 'POST',
        ]);

        $log = \App\Models\ActivityLog::where('user_id', $staf->id)
            ->where('action', 'arsip.scan')
            ->latest()->first();

        $this->assertNotNull($log);
        $this->assertStringStartsWith('Pindai QR arsip', $log->description);
        $this->assertSame('sukses', $log->metadata['hasil'] ?? null);
    }

    public function test_scan_by_kode_not_found_logs_failure(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staf  = User::factory()->create(['role' => 'staf']);

        $this->actingAs($staf)->post('/api/arsip/scan', [
            'kode_arsip' => 'TIDAK-ADA-999',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id'    => $staf->id,
            'action'     => 'arsip.scan',
            'route_name' => 'arsip.scan',
        ]);

        $log = \App\Models\ActivityLog::where('user_id', $staf->id)
            ->where('action', 'arsip.scan')
            ->latest()->first();

        $this->assertNotNull($log, 'Log harus dibuat untuk scan gagal (kode tidak ditemukan)');
        $this->assertStringStartsWith('Gagal', $log->description);
        $this->assertSame('gagal', $log->metadata['hasil'] ?? null);
    }

    public function test_scan_by_kode_empty_code_logs_failure(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staf  = User::factory()->create(['role' => 'staf']);

        $this->actingAs($staf)->post('/api/arsip/scan', [
            'kode_arsip' => '',
        ]);

        $log = \App\Models\ActivityLog::where('user_id', $staf->id)
            ->where('action', 'arsip.scan')
            ->latest()->first();

        $this->assertNotNull($log, 'Log harus dibuat untuk scan dengan kode kosong');
        $this->assertStringStartsWith('Gagal', $log->description);
        $this->assertSame('gagal', $log->metadata['hasil'] ?? null);
    }

    public function test_admin_get_routes_have_friendly_descriptions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // GET /users
        $this->actingAs($admin)->get('/users');
        $log = \App\Models\ActivityLog::where('user_id', $admin->id)
            ->where('route_name', 'users.index')
            ->where('method', 'GET')
            ->latest()->first();
        $this->assertNotNull($log, 'Log users.index harus ada');
        $this->assertSame('Membuka daftar pengguna.', $log->description);

        // GET /activity-log
        $this->actingAs($admin)->get('/activity-log');
        $log = \App\Models\ActivityLog::where('user_id', $admin->id)
            ->where('route_name', 'activity.index')
            ->where('method', 'GET')
            ->latest()->first();
        $this->assertNotNull($log, 'Log activity.index harus ada');
        $this->assertSame('Membuka log aktivitas.', $log->description);

        // GET /reports/activity.csv
        $this->actingAs($admin)->get('/reports/activity.csv');
        $log = \App\Models\ActivityLog::where('user_id', $admin->id)
            ->where('route_name', 'reports.activity')
            ->where('method', 'GET')
            ->latest()->first();
        $this->assertNotNull($log, 'Log reports.activity harus ada');
        $this->assertSame('Mengekspor laporan aktivitas.', $log->description);

        // GET /reports/users.csv
        $this->actingAs($admin)->get('/reports/users.csv');
        $log = \App\Models\ActivityLog::where('user_id', $admin->id)
            ->where('route_name', 'reports.users')
            ->where('method', 'GET')
            ->latest()->first();
        $this->assertNotNull($log, 'Log reports.users harus ada');
        $this->assertSame('Mengekspor laporan pengguna.', $log->description);

        // GET /reports/racks.csv
        $this->actingAs($admin)->get('/reports/racks.csv');
        $log = \App\Models\ActivityLog::where('user_id', $admin->id)
            ->where('route_name', 'reports.racks')
            ->where('method', 'GET')
            ->latest()->first();
        $this->assertNotNull($log, 'Log reports.racks harus ada');
        $this->assertSame('Mengekspor laporan rak.', $log->description);

        // GET /reports/sections.csv
        $this->actingAs($admin)->get('/reports/sections.csv');
        $log = \App\Models\ActivityLog::where('user_id', $admin->id)
            ->where('route_name', 'reports.sections')
            ->where('method', 'GET')
            ->latest()->first();
        $this->assertNotNull($log, 'Log reports.sections harus ada');
        $this->assertSame('Mengekspor laporan seksi.', $log->description);
    }
}
