<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Models\DokumenArsip;
use App\Models\LokasiArsip;
use App\Models\RakArsip;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class RecordActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $oldValues = $this->snapshot($request);
        $response = $next($request);

        if ($request->user() && $this->shouldRecord($request) && $response->getStatusCode() < 400) {
            $metadata = [
                'path' => $request->path(),
                'status' => $response->getStatusCode(),
            ];

            // Meta khas (mis. hasil ekspor/impor backup) diisi oleh controller
            // melalui session()->flash('activity_log_metadata', [...]).
            $custom = $request->session()->get('activity_log_metadata');
            if (is_array($custom)) {
                $metadata = $custom + $metadata;
            }

            $target = $this->targetFor($this->routeModel($request));

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => $request->route()?->getName() ?? strtolower($request->method()),
                'route_name' => $request->route()?->getName(),
                'method' => $request->method(),
                'description' => $this->description($request),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => $metadata,
                'old_values' => $oldValues,
                'new_values' => $this->snapshot($request),
                'target_type' => $target[0],
                'target_id' => $target[1],
                'target_label' => $target[2],
            ]);

            $request->session()->forget('activity_log_metadata');
        }

        return $response;
    }

    /**
     * Model (bila ada) yang ter-bind pada parameter route — dipakai
     * snapshot nilai & pencatatan target.
     */
    private function routeModel(Request $request): ?Model
    {
        return collect($request->route()?->parameters() ?? [])
            ->first(fn ($value) => $value instanceof Model);
    }

    /**
     * Target aksi: [target_type, target_id, target_label]. NULL bila
     * rute tidak membawa model (mis. backup, scan QR, cadangan CLI).
     */
    private function targetFor(?Model $model): array
    {
        return match (true) {
            $model instanceof DokumenArsip => ['dokumen', $model->id, $model->nomor_surat ?: ('Dokumen #'.$model->id)],
            $model instanceof User => ['user', $model->id, $model->name],
            $model instanceof RakArsip => ['rak', $model->id, $model->kode_rak],
            $model instanceof LokasiArsip => ['lokasi', $model->id, $model->nama_lokasi],
            default => [null, null, null],
        };
    }

    private function snapshot(Request $request): ?array
    {
        $model = $this->routeModel($request);

        if ($model instanceof Model) {
            return Arr::except($model->getAttributes(), [
                'password',
                'remember_token',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);
        }

        $input = $request->except([
            '_token',
            '_method',
            'password',
            'password_confirmation',
            'remember',
            'avatar',
            'lampiran',
            'backup_file',
        ]);

        return $input === [] ? null : $input;
    }

    private function shouldRecord(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        // Ekspor/unduh cadangan adalah GET — dicatat juga karena data keluar dari sistem.
        if ($request->isMethod('GET') && in_array($routeName, ['backup.export', 'backup.files'], true)) {
            return true;
        }

        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && ! $request->is('login', 'logout', 'forgot-password', 'reset-password', 'confirm-password', 'email/verification-notification')
            && ! in_array($routeName, [
                'login',
                'logout',
                'password.email',
                'password.store',
                'password.confirm',
                'verification.send',
            ], true);
    }

    private function description(Request $request): string
    {
        $routeName = $request->route()?->getName();

        return match ($routeName) {
            'users.store' => 'Menambahkan pengguna baru.',
            'users.update' => 'Memperbarui data pengguna.',
            'users.destroy' => 'Menonaktifkan pengguna.',
            'users.activate' => 'Mengaktifkan pengguna.',
            'users.password' => 'Meriset kata sandi pengguna.',
            'users.force-delete' => 'Menghapus permanen pengguna.',
            'arsip.store' => 'Menambahkan dokumen arsip.',
            'arsip.update' => 'Memperbarui dokumen arsip.',
            'arsip.destroy' => 'Menghapus dokumen arsip.',
            'arsip.force-delete' => 'Menghapus permanen dokumen arsip.',
            'arsip.pinjam' => 'Mencatat peminjaman dokumen.',
            'arsip.kembalikan' => 'Mencatat pengembalian dokumen.',
            'arsip.settersedia' => 'Mengembalikan status arsip menjadi tersedia.',
            'arsip.scan' => 'Pindai QR arsip.',
            'backup.export' => 'Mengunduh berkas cadangan arsip.',
            'backup.import' => 'Memulihkan data dari berkas cadangan.',
            'backup.run' => 'Membuat cadangan lengkap (bundel).',
            'backup.files' => 'Mengunduh cadangan tersimpan.',
            'arsip.cadangkan' => 'Mencadangkan basis data arsip.',
            'profile.update' => 'Memperbarui profil.',
            'profile.destroy' => 'Menghapus akun sendiri.',
            'rak.store' => 'Menambahkan rak arsip.',
            'rak.update' => 'Memperbarui rak arsip.',
            'rak.destroy' => 'Menghapus rak arsip.',
            'lokasi.store' => 'Menambahkan lokasi penyimpanan.',
            'lokasi.update' => 'Memperbarui lokasi penyimpanan.',
            'lokasi.destroy' => 'Menghapus lokasi penyimpanan.',
            default => sprintf('%s %s', $request->method(), $request->path()),
        };
    }
}