# Penguatan Fitur Cadangkan & Pulihkan Basis Data (/backup)
Status: BERJALAN · Service: web (Laravel) · Diperbarui: 2026-10-21 22:10

## Sedang dikerjakan
Paket "Penguatan Backup" — seluruh fase sudah TERTULIS & teruji di level test/build/artisan;
tinggal **verifikasi browser** di UI `/backup` (lihat Langkah berikutnya).
Ditunda (keputusan user): enkripsi AES-256-GCM, dry-run preview `/backup/preview`.

## Status terakhir (terbukti)
- ✅ Fase 1+2+3 implementasi selesai. `php artisan test` = **52 passed (315 asersi)** (termasuk 8 skenario BackupTest, ada skenario 7 round-trip ZIP DB-kosong). `npm run build` ✓ 13.87s. `php artisan arsip:cadangkan` exit 0 (15 arsip, 32 riwayat, 16 file fisik, 442 KB) + sidecar manifest + schedule `0 2 * * *` terdaftar. Runbook `docs/PROSEDUR-PULIH-ARSIP.md` dibuat & ter-link dari README. Changelog Fix #41 tertulis (PENDING verifikasi browser).
- ✅ 2 bug verifikasi terdeteksi & diperbaiki (lihat changelog): (B1) `$userHidup` tak di-`use` closure transaksi importBundle; (B2) `mkdir(): File exists` E_WARNING di Windows → ErrorException di artisan (fixed via `ensureDirectoryExists`).

## Keputusan penting
- Envelope v1: `{format:"sip-arsip-backup", version:1, meta:{dibuat_oleh,dibuat_pada,generator,jumlah_data,checksum_sha256}, data:[...]}`; hash kanonik = `sha256(json_encode(data, JSON_UNESCAPED_UNICODE))` (compact, sama di kedua ujung — jangan hash pretty-print).
- v0 (array mentah) tetap diterima, ditandai "tanapa_checksum"; reject ceksum keras di luar DB + tercatat ActivityLog (via metadata merge dari session key `backup_import_outcome`/`backup_export_outcome`).
- Per-baris: pravalidasi FK seksi/rak terhadap tabel hidup (bukan andalkan error DB), skip + catat alasan (cap 50), duplikat kode dalam file → entri pertama menang.
- User TIDAK diimpor (risiko hidupkan akun soft-deleted/bentrok email unik) — untuk fase bundel.

## Langkah berikutnya
- [x] Fase 1: BackupService + rombak BackupController export/import (selesai, teruji)
- [x] Fase 1: RecordActivity (GET whitelist) + HandleInertiaRequests (flash backup_result) + panel hasil UI + hapus BackupModal.vue (selesai)
- [x] Fase 1: tests/Feature/BackupTest.php skenario v1 (selesai — 8 test)
- [x] Jalankan php artisan test + npm run build (selesai: 52 passed / build 13.87s)
- [x] Changelog Fix #41 (selesai, PENDING verifikasi browser)
- [x] Fase 2: exportBundle + import ZIP + remap FK + riwayat + file fisik + dicatat_oleh gantung (selesai, teruji)
- [x] Fase 2: Command arsip:cadangkan + retensi + config/backup.php + .env.example + schedule dailyAt 02:00 (selesai)
- [x] Fase 2: UI daftar cadangan + indikator terakhir + route /backup/files/{file} & /backup/run (selesai)
- [x] Fase 3: round-trip ZIP DB-kosong (test #7) + docs/PROSEDUR-PULIH-ARSIP.md + tautan README (selesai)
- [ ] **Verifikasi browser** `/backup` (user): klik "Cadangkan Sekarang (ZIP)" → baris baru di "Cadangan Tersimpan" → unduh → ubah 1 karakter → upload ditolak pesan checksum + baris `gagal_verifikasi` di /activity-log; upload utuh → panel metrik sesuai manifest. Setelah hijau → tandai changelog Fix #41 ✅ LIVE + hapus handoff ini.

## Jangan lakukan (jebakan yang sudah ditemukan)
- Jangan hash string pretty-print export lama — harus bentuk compact agar cocok di kedua ujung.
- Import envelope lewat jalur v0 lama akan SILENT RESTORE 0 baris — deteksi `content['format']` wajib sebelum foreach.
- `seksi_id` NOT NULL constrained (cascadeOnDelete) — baris ber-FK gantung harus di-skip, jangan di-null.
- SQLite test: FK constraint ON per config (`env('DB_FOREIGN_KEYS', true)`) tapi uji perilaku di level aplikasi (pravalidasi), bukan andalkan constraint.
- Duplikat kode_arsip di DB = undefined behavior updateOrCreate — selalu cek count dulu.
- `backup.import` & `backup.export` sudah ter-mapping di RecordActivity::description() — tambah mapping baru bila menambah route.
- Jangan impor user/riwayat saat fase bundel tanpa guard (user soft-deleted hidup lagi = bug; dedupe riwayat pakai kunci penuh dokumen+user+jenis_aksi+waktu_aksi, bukan tanggal saja).
- Task Scheduler Windows (`php artisan schedule:run` tiap 15 menit) BELUM ada di host — tulis di runbook; PC mati saat jam schedule = backup lewat hari.
- File handoff ini juga punya 2 file `.json` aneh di folder (flash-success-dismiss.json, qr-storage-link.json) — jangan diubah milik sesi lain.
