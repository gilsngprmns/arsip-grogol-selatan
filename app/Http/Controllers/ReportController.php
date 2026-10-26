<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\RakArsip;
use App\Models\Seksi;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Ekspor log aktivitas — mengikuti filter query string yang sama
     * dengan halaman /activity-log (tanpa filter = seluruh log).
     */
    public function activity(Request $request): StreamedResponse
    {
        return $this->csv('laporan-aktivitas', ['waktu', 'pengguna', 'aksi', 'keterangan', 'target', 'ip'], function ($handle) use ($request): void {
            ActivityLog::query()->with('user:id,name')->whereNotIn('route_name', ['login', 'logout'])->filterByRequest($request)->latest()->chunk(200, function ($rows) use ($handle): void {
                foreach ($rows as $row) {
                    // Zona Asia/Jakarta agar konsisten dengan tampilan halaman
                    $waktu = $row->created_at?->copy()->setTimezone('Asia/Jakarta')->format('d M Y, H.i.s') ?? '';
                    $target = filled($row->target_type) ? $row->target_type.': '.($row->target_label ?? '') : '';
                    fputcsv($handle, [$waktu, $row->user?->name ?? 'Sistem', $row->action, $row->description, $target, $row->ip_address], ';');
                }
            });
        });
    }

    public function users(): StreamedResponse
    {
        return $this->csv('laporan-user', ['nama', 'email', 'role', 'seksi', 'status', 'dibuat'], function ($handle): void {
            User::query()->with('seksi:id,nama_seksi')->orderBy('name')->chunk(200, function ($rows) use ($handle): void {
                foreach ($rows as $row) {
                    fputcsv($handle, [$row->name, $row->email, $row->role, $row->seksi?->nama_seksi ?? 'Tanpa seksi', $row->is_active ? 'Aktif' : 'Nonaktif', $row->created_at], ';');
                }
            });
        });
    }

    public function racks(): StreamedResponse
    {
        return $this->csv('laporan-rak', ['kode rak', 'nama rak', 'lokasi', 'jumlah arsip', 'kapasitas', 'status'], function ($handle): void {
            RakArsip::query()->with('lokasiArsip:id,nama_lokasi')->withCount('dokumenArsip')->orderBy('kode_rak')->chunk(200, function ($rows) use ($handle): void {
                foreach ($rows as $row) {
                    fputcsv($handle, [$row->kode_rak, $row->nama_rak, $row->lokasiArsip?->nama_lokasi ?? 'Tanpa lokasi', $row->dokumen_arsip_count, $row->kapasitas ?? 200, $row->is_active ? 'Aktif' : 'Nonaktif'], ';');
                }
            });
        });
    }

    public function sections(): StreamedResponse
    {
        return $this->csv('laporan-rekap-seksi', ['seksi', 'jumlah arsip', 'tersedia', 'dipinjam', 'jumlah user'], function ($handle): void {
            Seksi::query()->withCount(['dokumenArsip', 'users'])->withCount(['dokumenArsip as tersedia_count' => fn ($query) => $query->where('status_arsip', 'tersedia')])->withCount(['dokumenArsip as dipinjam_count' => fn ($query) => $query->where('status_arsip', 'dipinjam')])->orderByOrganisasi()->chunk(200, function ($rows) use ($handle): void {
                foreach ($rows as $row) {
                    fputcsv($handle, [$row->nama_seksi, $row->dokumen_arsip_count, $row->tersedia_count, $row->dipinjam_count, $row->users_count], ';');
                }
            });
        });
    }

    private function csv(string $name, array $headers, callable $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $writer): void {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $headers, ';');
            $writer($handle);
            fclose($handle);
        }, $name.'-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}