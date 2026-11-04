# Notifikasi staf online untuk admin (SIP ARSIP)
Status: BERJALAN · Service: backend+frontend (SIP ARSIP) · Diperbarui: 2026-11-01

## Sedang dikerjakan
- Endpoint `GET /api/staf-online` (admin-only) — sudah dibuat, test lulus.
- Composable `useStafOnline.js` (module-singleton, polling 10 dtk) — sudah dibuat.
- Toast `StafOnlineToast.vue` + pill header di `AppLayout.vue` — sudah di-wire.
- Panel "Staf Aktif" di `Dashboard.vue` — sudah ditambahkan.
- CHANGELOG_FIXES Fix #89 — sudah ditambahkan (status: BELUM DEPLOY).

## Status terakhir
- `php artisan test tests/Feature/StafOnlineTest.php` → 3 passed.
- `npm run build` → ✓ built.
- Manual verification belum dilakukan (butuh 2 tab: admin + staf).

## Langkah berikutnya
1. Manual test: buka sebagai admin, login staf di tab lain → lihat toast + pill "n staf online".
2. Commit perubahan (semua file di atas).
3. Deploy → update CHANGELOG_FIXES Fix #89 status BELUM DEPLOY → LIVE.

## Jangan lakukan
- Jangan tambah WebSocket/Reverb — keputusan user = polling ringan.
- Jangan ubah `RecordActivity` untuk mencatat login (fitur ini tidak membutuhkannya).
