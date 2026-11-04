# Hero Beranda full-viewport (calc(100dvh-4.5rem)) + panah scroll
Status: SELESAI (putaran 2) · Service: frontend-publik/Welcome.vue · Diperbarui: 2026-11-01 20:35

## Sedang dikerjakan — selesai
Putaran 1 (2026-10-31) + putaran 2 (2026-11-01) sudah diterapkan di `resources/js/Pages/Welcome.vue`:
1. Tinggi hero `calc(100dvh - 4.5rem)` + `min-h-[380px]` — user approved (keputusan A).
2. Putaran 1: dot `bottom-5`→`bottom-9`, panah scroll chevron-down di `bottom-2` (`motion-safe:animate-bounce`).
3. **Putaran 2** (permintaan user "ini hilangkan saja"): panah scroll DIHAPUS permanen, dot dikembalikan ke `bottom-5`.
4. Skeleton loading sinkron ke tinggi yang sama (prinsip Fix #67).

Build `Welcome-Ds3WQF1J.js` lulus: grep `animate-bounce` = 0, `bottom-5` = 1, `calc(100dvh` = 2.

## Status terakhir
- **SELESAI**: panah scroll dihapus, dot kembali ke posisi asli.
- **PENDING FOTO**: `public/images/1..5.jpg` masih hilang dari working tree (untracked, tak pernah di-commit). User perlu memulihkan dari sumber aslinya.
- **PENDING CHANGELOG**: entri Fix #96 ditulis di CHANGELOG_FIXES.md dengan status ⚠️ PENDING sampai foto terverifikasi.
- **REGRESI**: panah prev/next carousel, auto-slide 5000ms, dot klikabel — semua tetap berfungsi (putaran 1 terverifikasi sebelumnya).

## Langkah berikutnya
1. User memulihkan `public/images/1.jpg` s.d. `5.jpg`.
2. Refresh browser, tangkap hero (foto tampil), scroll ke section keamanan.
3. Setelah itu: update status handoff → HAPUS file handoff, ubah status changelog → ✅ LIVE.

## Jangan lakukan
- Jangan meng-commit `public/images/*.jpg` sebelum user konfirmasi (keputusan user).
- Jangan mengembalikan panah scroll — user sudah memilih hapus permanen.

