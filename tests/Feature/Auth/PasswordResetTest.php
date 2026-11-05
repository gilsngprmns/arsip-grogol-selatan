<?php

namespace Tests\Feature\Auth;

use App\Mail\OtpResetPasswordMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Auth/ForgotPassword'));
    }

    public function test_otp_can_be_sent_to_valid_email(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Clear throttle untuk user ini
        RateLimiter::clear('otp-send:'.$user->email);

        $response = $this->postJson(route('password.otp.send'), [
            'email' => $user->email,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'otp-sent']);

        Mail::assertSent(OtpResetPasswordMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        // Token harus tersimpan sebagai hash di database (bukan OTP plain)
        $token = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotNull($token);
        // Hash tidak boleh sama dengan string OTP 6 digit
        $this->assertNotEquals('123456', $token->token);
        $this->assertEquals(64, strlen($token->token)); // SHA-256 produces 64 char hex
    }

    public function test_otp_cannot_be_sent_to_nonexistent_email(): void
    {
        Mail::fake();

        $response = $this->postJson(route('password.otp.send'), [
            'email' => 'nonexistent@example.com',
        ]);

        // Harus tetap return sukses untuk mencegah email enumeration
        $response->assertOk();
        $response->assertJson(['status' => 'otp-sent']);

        // Tapi tidak ada email yang dikirim
        Mail::assertNotSent(OtpResetPasswordMail::class);
    }

    public function test_otp_can_be_verified_correctly(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Simpan token OTP yang sudah di-hash langsung ke database
        $otpCode = '123456';
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => hash('sha256', $otpCode),
            'created_at' => now(),
        ]);

        $response = $this->postJson(route('password.otp.verify'), [
            'email' => $user->email,
            'otp' => $otpCode,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'otp-verified']);
    }

    public function test_invalid_otp_fails_verification(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => hash('sha256', '123456'),
            'created_at' => now(),
        ]);

        $response = $this->postJson(route('password.otp.verify'), [
            'email' => $user->email,
            'otp' => '000000',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'otp', 'message' => 'Kode OTP salah. Silakan periksa kembali.']);
    }

    public function test_password_can_be_reset_with_valid_otp(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Simpan token OTP
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => hash('sha256', '123456'),
            'created_at' => now(),
        ]);

        $response = $this->postJson(route('password.otp.reset'), [
            'email' => $user->email,
            'otp' => '123456',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'password-reset']);

        // Verifikasi password baru sudah tersimpan
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));

        // Token harus dihapus setelah sukses
        $this->assertNull(DB::table('password_reset_tokens')->where('email', $user->email)->first());
    }

    public function test_password_reset_fails_with_invalid_otp(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson(route('password.otp.reset'), [
            'email' => $user->email,
            'otp' => '000000',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('otp');
    }

    public function test_otp_expires_after_60_minutes(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Simpan token dengan created_at 61 menit yang lalu
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => hash('sha256', '123456'),
            'created_at' => now()->subMinutes(61),
        ]);

        $response = $this->postJson(route('password.otp.verify'), [
            'email' => $user->email,
            'otp' => '123456',
        ]);

        // Setelah 61 menit, token dianggap kadaluarsa
        $response->assertStatus(422);
        $response->assertJson(['error' => 'otp', 'message' => 'Kode OTP telah kadaluarsa. Silakan minta kode baru.']);
    }
}