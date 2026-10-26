<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Seksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_open_user_management_or_activity_log(): void
    {
        $staff = User::factory()->create(['role' => 'staf']);

        $this->actingAs($staff)->get('/users')->assertForbidden();
        $this->actingAs($staff)->get('/activity-log')->assertForbidden();
    }

    public function test_admin_can_create_user_and_mutation_is_logged(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seksi = Seksi::create(['nama_seksi' => 'Pelayanan']);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Staf Baru',
            'email' => 'staf-baru@example.com',
            'password' => 'password123',
            'role' => 'staf',
            'seksi_id' => $seksi->id,
            'is_active' => true,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/users');

        $user = User::where('email', 'staf-baru@example.com')->firstOrFail();

        $this->assertSame('Staf Baru', $user->name);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'route_name' => 'users.store',
            'method' => 'POST',
        ]);
    }

    public function test_staff_user_requires_seksi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->from('/users')->post('/users', [
            'name' => 'Staf Tanpa Seksi',
            'email' => 'staf-tanpa-seksi@example.com',
            'password' => 'password123',
            'role' => 'staf',
            'seksi_id' => '',
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('seksi_id');
        $this->assertDatabaseMissing('users', ['email' => 'staf-tanpa-seksi@example.com']);
    }

    public function test_admin_can_be_created_without_seksi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Admin Baru',
            'email' => 'admin-baru@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'seksi_id' => '',
            'is_active' => true,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'email' => 'admin-baru@example.com',
            'role' => 'admin',
            'seksi_id' => null,
        ]);
    }

    public function test_admin_can_reactivate_inactive_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'staf', 'is_active' => false]);

        $response = $this->actingAs($admin)->post(route('users.activate', $user));

        $response->assertSessionHasNoErrors()->assertRedirect('/users');
        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_cannot_deactivate_last_active_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $seksi = Seksi::create(['nama_seksi' => 'Pelayanan']);
        $other = User::factory()->create(['role' => 'staf', 'seksi_id' => $seksi->id, 'is_active' => true]);

        // Satu-satunya admin aktif tidak boleh diturunkan rolenya
        $response = $this->actingAs($admin)->from('/users')->put(route('users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'staf',
            'seksi_id' => $seksi->id,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('user');
        $this->assertSame('admin', $admin->fresh()->role);

        // Admin lain boleh dinonaktifkan selama masih ada admin aktif
        $secondAdmin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->delete(route('users.destroy', $secondAdmin))
            ->assertSessionHasNoErrors()
            ->assertRedirect('/users');
        $this->assertFalse($secondAdmin->fresh()->is_active);
        $this->assertTrue($other->fresh()->is_active);
    }

    public function test_admin_can_reset_user_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'staf', 'password' => bcrypt('old-password')]);

        $response = $this->actingAs($admin)->post(route('users.password', $user), [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/users');
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_users_index_supports_seksi_filter_and_pagination(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seksi = Seksi::create(['nama_seksi' => 'Pemerintahan']);
        User::factory()->create(['role' => 'staf', 'seksi_id' => $seksi->id]);

        $response = $this->actingAs($admin)->get('/users?seksi_id='.$seksi->id);

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Users/Index')
            ->has('users.data')
            ->where('filters.seksi_id', (string) $seksi->id)
            ->has('activeAdminCount'));
    }

    public function test_admin_can_view_activity_log(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'users.store',
            'route_name' => 'users.store',
            'method' => 'POST',
            'description' => 'Menambahkan pengguna baru.',
        ]);

        $this->actingAs($admin)->get('/activity-log')->assertOk();
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}