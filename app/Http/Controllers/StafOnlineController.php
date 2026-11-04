<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StafOnlineController extends Controller
{
    /**
     * Daftar staf yang sedang online (aktif 30 menit terakhir, via sesi database).
     * Dipanggil frontend admin dengan polling ringan setiap ~10 detik.
     */
    public function index(Request $request): JsonResponse
    {
        // Nilai cache global & murni data (tidak bergantung viewer): admin sudah
        // dikecualikan oleh `->where('role','staf')`, jadi tak perlu filter per-viewer.
        $staf = Cache::remember('staf-online', 30, function () {
            $cutoff = now()->subMinutes(30)->getTimestamp();

            $ids = DB::table('sessions')
                ->where('last_activity', '>=', $cutoff)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->unique()
                ->values();

            return User::query()
                ->where('role', 'staf')
                ->where('is_active', true)
                ->whereIn('id', $ids)
                ->with('seksi:id,nama_seksi')
                ->get(['id', 'name', 'seksi_id']);
        });

        return response()->json([
            'count'  => $staf->count(),
            'staf'   => $staf->map(fn ($u) => [
                'id'    => $u->id,
                'name'  => $u->name,
                'seksi' => $u->seksi?->nama_seksi,
            ])->values(),
        ]);
    }
}
