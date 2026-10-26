<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DokumenArsip;
use App\Models\LokasiArsip;
use App\Models\RakArsip;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): Response|JsonResponse
    {
        $term = trim($request->string('q')->toString());
        $user = $request->user();
        $isAdmin = $user->role === 'admin';
        $documents = collect();
        $users = collect();
        $racks = collect();
        $locations = collect();
        $activities = collect();

        if ($term !== '') {
            $documents = DokumenArsip::query()
                ->with(['seksi:id,nama_seksi', 'rak:id,kode_rak,nama_rak'])
                ->when(! $isAdmin, fn ($query) => $query->where('seksi_id', $user->seksi_id))
                ->where(function ($query) use ($term) {
                    $query->where('kode_arsip', 'like', "%{$term}%")
                        ->orWhere('nomor_surat', 'like', "%{$term}%")
                        ->orWhere('perihal', 'like', "%{$term}%")
                        ->orWhere('catatan', 'like', "%{$term}%");
                })
                ->latest()->limit(15)
                ->get(['id', 'kode_arsip', 'nomor_surat', 'perihal', 'seksi_id', 'rak_id', 'status_arsip']);

            $racks = RakArsip::query()
                ->with('lokasiArsip:id,nama_lokasi')
                ->where('is_active', true)
                ->where(fn ($query) => $query->where('kode_rak', 'like', "%{$term}%")->orWhere('nama_rak', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'kode_rak', 'nama_rak', 'lokasi_id']);

            $locations = LokasiArsip::query()
                ->where('is_active', true)
                ->where(function ($query) use ($term) {
                    $query->where('kode_lokasi', 'like', "%{$term}%")
                        ->orWhere('nama_lokasi', 'like', "%{$term}%")
                        ->orWhere('gedung', 'like', "%{$term}%")
                        ->orWhere('ruangan', 'like', "%{$term}%");
                })
                ->limit(15)->get(['id', 'kode_lokasi', 'nama_lokasi', 'gedung', 'ruangan']);

            $users = $isAdmin ? User::query()
                ->with('seksi:id,nama_seksi')
                ->where(fn ($query) => $query->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'name', 'email', 'role', 'seksi_id', 'is_active']) : collect();

            $activities = ActivityLog::query()
                ->with('user:id,name,role')
                ->when(! $isAdmin, fn ($query) => $query->where('user_id', $user->id))
                ->where(fn ($query) => $query->where('description', 'like', "%{$term}%")->orWhere('action', 'like', "%{$term}%"))
                ->latest()->limit(15)
                ->get(['id', 'user_id', 'action', 'description', 'created_at']);
        }

        $payload = compact('term', 'documents', 'users', 'racks', 'locations', 'activities');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($payload);
        }

        return Inertia::render('Search/Index', $payload);
    }
}
