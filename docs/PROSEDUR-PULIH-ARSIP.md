# Prosedur Pemulihan Arsip dari Cadangan Bundel (ZIP)

Bundel cadangan = satu file `backup-arsip-YYYYMMDD-His.zip` yang berisi `data.json`
(dokumen + seksi + rak + lokasi + riwayat, dengan checksum SHA-256), `manifest.json`
(rincian konten + daftar file fisik), dan folder `files/` (salinan lampiran & QR).
Restore menolak berkas yang terpotong/terubah **sebelum** menyentuh basis data.

## Cadangan terjadwal (kondisi normal)

- Command `php artisan arsip:cadangkan` berjalan tiap hari 02:00 via jadwal
  (`routes/console.php`). Pada host Windows, daftarkan **Task Scheduler**
  menjalankan `php artisan schedule:run` tiap 15 menit — PC harus menyala di jam itu.
- Retensi 14 hari (`config/backup.php` / `BACKUP_RETENTION_DAYS`): cadangan lama
  dihapus otomatis oleh command berikutnya.
- Halaman `/backup` menampilkan indikator "Cadangan terakhir" — merah bila > 7 hari.

## Langkah pemulihan (skenario DB hilang/rusak di satu mesin)

1. **Ambil bundel terbaru.** Unduh dari mesin sehat lewat `/backup` → "Cadangan
   Tersimpan" → Unduh, atau salin langsung `storage/backups/backup-arsip-*.zip`
   (+ file `.manifest.json`-nya sebagai rujukan rincian). Catat nama file &
   `total_size_bytes` pada manifest.
2. **Siapkan instalasi tujuan** (instalasi segar atau DB yang di-reset) dengan
   versi aplikasi sama, lalu jalankan migrasi & `npm run build` bila baru.
3. **Pulihkan lewat web:** login admin → `/backup` → unggah bundel ZIP → konfirmasi.
   Proses mengekstrak ke temp, memverifikasi checksum bagian data **dan** tiap
   file fisik, baru menulis DB (lokasi → seksi → rak → arsip → riwayat) dan
   menyalin file fisik ke storage publik.
4. **Cocokkan metrik** pada panel hasil dengan `manifest.json` sumber:
   - `Dibuat/Diperbarui` arsip = `rincian_konten.dokumen_arsip`;
   - `File fisik dipulihkan` = `rincian_konten.file_fisik`;
   - `Dilewati` > 0 → baca alasan di panel (mis. pencatat tidak ada di target —
     normal saat restore lintas mesin karena user tidak ikut diimpor).
5. **Uji sampel:** buka 3 dokumen acak → QR masih memindai (`arsip.scan`),
   lampiran terbuka. Cocokkan jumlah `DokumenArsip` (termasuk trash) dengan
   manifest bila ada keraguan.
6. **Kriteria sukses:** checksum "lulus" di panel, metrik sesuai manifest, tidak
   ada FK gantung (tidak ada arsip tanpa seksi), lampiran & QR sample berfungsi.

## Pemulihan darurat hanya DB (tanpa file fisik)

JSON ringkas (`/backup/export`, envelope v1) memulihkan data arsip saja — cocok
bila lampiran/QR sudah tersinkron atau tidak kritis. File fisik dipulihkan
langsung: ekstrak folder `files/` dari bundel ke `storage/app/public/`
(menjaga struktur subfolder `lampiran-arsip/` & `qrcodes/`).

## Salin offsite (wajib minimal mingguan)

Salin bundel terbaru + manifest ke media eksternal (USB/cloud). Ganti cadangan
yang sama setiap kali membuat salinan baru; verifikasi dapat dibuka (ZIP tidak korup)
sebelum disimpan.

## Catatan

- Restore berbasis `kode_arsip` — data lain di sistem tujuan tidak dihapus;
  kode yang sama akan ditimpa (merge/update), kode baru dibuat.
- User tidak ikut diimpor (keputusan desain); riwayat tanpa petugas hidup di
  target dilewati & tercatat di metrik.
- Bundel >40 MB ditolak oleh validasi web (`max:40960`) — naikkan
  `upload_max_filesize`/`post_max_size` bila arsip membesar.
