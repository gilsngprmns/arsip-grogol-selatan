<?php

namespace App\Observers;

use App\Models\DokumenArsip;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DokumenArsipObserver
{
    /**
     * Handle the DokumenArsip "created" event.
     */
    public function created(DokumenArsip $arsip): void
    {
        $path = $this->storeQrImage($arsip->kode_arsip);

        $arsip->updateQuietly([
            'qr_code_path' => $path,
        ]);
    }

    /**
     * Handle the DokumenArsip "updated" event.
     */
    public function updated(DokumenArsip $arsip): void
    {
        if (! $arsip->wasChanged('kode_arsip')) {
            return;
        }

        $this->deleteQrFile($arsip->getOriginal('qr_code_path'));

        $path = $this->storeQrImage($arsip->kode_arsip);

        $arsip->updateQuietly([
            'qr_code_path' => $path,
        ]);
    }

    /**
     * Handle the DokumenArsip "force deleted" event.
     */
    public function forceDeleted(DokumenArsip $arsip): void
    {
        $this->deleteQrFile($arsip->qr_code_path);
        if ($arsip->file_path) {
            Storage::disk('public')->delete($arsip->file_path);
        }
    }

    /**
     * Generate dan simpan gambar QR dari kode_arsip.
     * PNG dipakai jika Imagick tersedia; jika tidak, fallback ke SVG.
     */
    public function storeQrImage(string $kodeArsip): string
    {
        Storage::disk('public')->makeDirectory('qrcodes');

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $kodeArsip) ?: 'arsip';
        $hash = substr(md5($kodeArsip), 0, 6);
        $filename = $safeName === 'arsip' ? "arsip-{$hash}" : "{$safeName}-{$hash}";

        if (extension_loaded('imagick')) {
            $contents = QrCode::format('png')->size(300)->generate($kodeArsip);
            $relativePath = "qrcodes/{$filename}.png";
        } else {
            $contents = QrCode::format('svg')->size(300)->generate($kodeArsip);
            $relativePath = "qrcodes/{$filename}.svg";
        }

        Storage::disk('public')->put($relativePath, $contents);

        return $relativePath;
    }

    protected function deleteQrFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
