<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpResetPasswordMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class OtpPasswordController extends Controller
{
    /**
     * Tampilkan halaman multi-step lupa kata sandi.
     */
    public function index(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Kirim OTP 6 digit ke email user.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        // Throttle: max 1 request per email setiap 60 detik
        $throttleKey = 'otp-send:'.$email;
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return response()->json([
                'error' => 'email',
                'message' => 'Terlalu banyak permintaan. Tunggu beberapa menit sebelum mencoba lagi.',
            ], 429);
        }

        RateLimiter::hit($throttleKey, 60);

        // Cek apakah email ada di tabel users
        $user = User::where('email', $email)->first();

        // Untuk keamanan, selalu kembalikan status "terkirim" meskipun email tidak ditemukan
        // agar attacker tidak bisa enumeration email
        if ($user) {
            $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $hashedOtp = hash('sha256', $otp);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => $hashedOtp,
                    'created_at' => now(),
                ]
            );

            Mail::to($email)->send(new OtpResetPasswordMail($otp, $email));
        }

        return response()->json([
            'status' => 'otp-sent',
        ]);
    }

    /**
     * Verifikasi OTP yang dimasukkan user.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $email = $request->email;
        $otp = $request->otp;

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            return response()->json([
                'error' => 'otp',
                'message' => 'Kode OTP salah atau kadaluarsa.',
            ], 422);
        }

        // Check expiry 60 minutes
        if (now()->diffInMinutes($record->created_at) < -60) {
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();

            return response()->json([
                'error' => 'otp',
                'message' => 'Kode OTP telah kadaluarsa. Silakan minta kode baru.',
            ], 422);
        }

        if (!hash_equals($record->token, hash('sha256', $otp))) {
            return response()->json([
                'error' => 'otp',
                'message' => 'Kode OTP salah. Silakan periksa kembali.',
            ], 422);
        }

        return response()->json([
            'status' => 'otp-verified',
        ]);
    }

    /**
     * Reset password setelah OTP divalidasi.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = $request->email;
        $otp = $request->otp;

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record || now()->diffInMinutes($record->created_at) < -60
            || !hash_equals($record->token, hash('sha256', $otp))) {
            throw ValidationException::withMessages([
                'otp' => ['Kode OTP salah atau kadaluarsa.'],
            ]);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Pengguna tidak ditemukan.'],
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->remember_token = Str::random(60);
        $user->save();

        // Hapus token setelah sukses
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();

        return response()->json([
            'status' => 'password-reset',
        ]);
    }
}
