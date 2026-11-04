<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StafOnlineTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Buat admin + N staf, tanam sesi database, dan asersi:
     *  - staf aktif (30 menit terakhir) muncul,
     *  - staf tidak aktif / is_active=false tidak muncul,
     *  - admin sendiri tidak masuk daftar,
     *  - staff role → 403.
     */
    public function test_admin_sees_active_staff(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $stafAktif = User::factory()->create(['role' => 'staf', 'is_active' => true]);
        $stafStale = User::factory()->create(['role' => 'staf', 'is_active' => true]);
        $stafNonaktif = User::factory()->create(['role' => 'staf', 'is_active' => false]);

        // Sesi aktif (within 30 min) untuk $stafAktif
        DB::table('sessions')->insert([
            'id' => 'saktif',
            'user_id' => $stafAktif->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => 'a',
            'last_activity' => time(), // sekarang → aktif
        ]);

        // Sesi stale (>30 min) untuk $stafStale
        DB::table('sessions')->insert([
            'id' => 'sstale',
            'user_id' => $stafStale->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => 'a',
            'last_activity' => time() - (31 * 60), // 31 menit lalu → tidak aktif
        ]);

        // Sesi untuk admin (tidak boleh muncul di daftar staf)
        DB::table('sessions')->insert([
            'id' => 'sadmin',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => 'a',
            'last_activity' => time(),
        ]);

        $res = $this->actingAs($admin)->getJson(route('activity.staf-online'));

        $res->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('staf.0.id', $stafAktif->id)
            ->assertJsonPath('staf.0.name', $stafAktif->name);

        // Tegas: admin & staf nonaktif/stale tidak masuk
        $stafIds = collect($res->json('staf'))->pluck('id')->all();
        $this->assertFalse(in_array($admin->id, $stafIds), 'Admin tidak boleh masuk daftar staf');
        $this->assertFalse(in_array($stafStale->id, $stafIds), 'Staf stale (>30 mnt) tidak boleh muncul');
        $this->assertFalse(in_array($stafNonaktif->id, $stafIds), 'Staf is_active=false tidak boleh muncul');
    }

    public function test_staff_role_gets_403(): void
    {
        $staf = User::factory()->create(['role' => 'staf', 'is_active' => true]);
        $this->actingAs($staf)->getJson(route('activity.staf-online'))->assertForbidden();
    }

    public function test_guest_cannot_access(): void
    {
        // GET (non-JSON) → redirect ke login oleh guard auth
        $this->get(route('activity.staf-online'))->assertRedirect();
    }
}
