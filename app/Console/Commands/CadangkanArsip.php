<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Services\Backup\BundleBackupService;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

/**
 * Buat cadangan terjadwal (atau manual) basis data arsip — satu bundel ZIP
 * lengkap (data + referensi + file fisik), lalu terapkan retensi.
 *
 * Eksekusi terjadwal: daftarkan di routes/console.php, pada host Windows
 * gunakan Task Scheduler menjalankan "php artisan schedule:run" tiap 15 menit.
 */
class CadangkanArsip extends Command
{
    protected $signature = 'arsip:cadangkan {--out= : Direktori tujuan cadangan (default: config backup.path)} {--no-files : Jangan sertakan file fisik lampiran & QR} {--dry-run : Hitung saja tanpa menulis berkas}';

    protected $description = 'Buat cadangan lengkap basis data arsip (ZIP) dan terapkan retensi';

    public function handle(BundleBackupService $bundles): int
    {
        $retensi = (int) config('backup.retention_days');
        $target = $this->option('out') ?: config('backup.path');
        $includeFiles = ! $this->option('no-files');
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Menyusun cadangan arsip (include_files='.($includeFiles ? 'ya' : 'tidak').')…');

        if ($dryRun) {
            $payload = $bundles->buildPayload();
            $files = $includeFiles ? $bundles->gatherFiles() : [];
            $this->info('Simulasi: '.count($payload['dokumen_arsip']).' arsip, '.count($files).' file fisik akan dicadangkan.');

            return self::SUCCESS;
        }

        try {
            $hasil = $bundles->exportBundle(
                generator: 'jadwal-artisan',
                dibuatOleh: null,
                includeFiles: $includeFiles,
                outDir: $target,
            );
        } catch (Throwable $e) {
            $this->error('Pembuatan cadangan gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        $dihapus = $bundles->applyRetention($retensi);

        $this->info('Cadangan selesai: '.$hasil['nama_file']);
        $this->info(sprintf(
            '   Rincian: %d arsip, %d riwayat, %d file fisik (%s KB)',
            $hasil['rincian']['dokumen_arsip'],
            $hasil['rincian']['riwayat_sirkulasi'],
            $hasil['rincian']['file_fisik'],
            round(($hasil['rincian']['total_size_bytes'] ?? 0) / 1024),
        ));
        if ($dihapus > 0) {
            $this->info("   Retensi {$retensi} hari: {$dihapus} cadangan lama dihapus.");
        }

        // Jejak audit — user_id null (Sistem) diperbolehkan oleh skema
        try {
            ActivityLog::create([
                'user_id' => null,
                'action' => 'arsip.cadangkan',
                'route_name' => 'arsip.cadangkan',
                'method' => 'CLI',
                'description' => 'Mencadangkan basis data arsip (otomatis/manual).',
                'metadata' => [
                    'nama_file' => $hasil['nama_file'],
                    'jenis' => 'cadangan_terjadwal',
                    'status' => 'sukses',
                    'jumlah_data' => $hasil['rincian']['dokumen_arsip'],
                    'file_fisik' => $hasil['rincian']['file_fisik'],
                    'total_size_bytes' => $hasil['rincian']['total_size_bytes'] ?? null,
                ],
                'old_values' => null,
                'new_values' => null,
            ]);
        } catch (Throwable) {
            // Kegagalan log tidak boleh membuat cadangan itu sendiri gagal
        }

        return self::SUCCESS;
    }
}
