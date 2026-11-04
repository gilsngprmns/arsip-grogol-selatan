# DESIGN.md — SIP ARSIP, Kelurahan Grogol Selatan

Panduan arah desain untuk antarmuka SIP ARSIP (aplikasi pengarsipan dokumen kelurahan). File ini deskriptif: mengikat keputusan desain ke apa yang sudah ada di `tailwind.config.js` dan kode, bukan menggantikan keduanya.

## Identitas

SIP ARSIP adalah aplikasi operasional internal pemerintahan kelurahan: dipakai staf pengarsipan dan admin sehari-hari untuk mencatat dokumen, melacak peminjaman, dan memetakan lokasi fisik arsip. Desainnya harus **bersih, tenang, dan hierarkinya jelas** (apa yang penting terbaca dulu, apa yang operasional menyusul). Aksen visual hanya untuk data yang benar-benar penting (angka KPI, badge status, bar kapasitas rak). Tidak ada dekorasi yang berdiri sendiri.

## Dials (antislop)

- Halaman publik (Beranda, Manfaat, Prosedur, Bantuan): ENERGY 1 / RHYTHM 1 / MOTION 2 (scroll-reveal: fade-up via `v-reveal`, stagger ringan antar kartu; override owner 2026-10-31).
- Halaman admin (Dashboard, Arsip, Rak, Users, Activity, Backup, dsb.): ENERGY 1 / RHYTHM 1 / MOTION 2 (scroll-reveal pada panel dan kartu; override owner 2026-10-31).

Aplikasi internal pemerintahan: grid konsisten, tanpa gerak berlebih di area operasional.

## Palet

Dasar netral `slate-*` (latar, teks sekunder, border).
Core (dari `tailwind.config.js`): `brand-navy #0B1F3A` (teks utama, elemen navigasi), `brand-blue #2E6BE6` (aksi, link, highlight), `brand-blue-soft #DBEAFE`.
Aksen fungsional, dipakai terbatas: `amber` (perhatian, hampir penuh, ≥80%), `rose` (bahaya, terlambat, penuh, ≥100%), `emerald` (aman, tersedia).
Batas: maksimal 2-3 warna core + aksen fungsional per antarmuka (aturan R-29 antislop). Warna baru tidak boleh masuk tanpa alasan hierarki yang tertulis.

## Tipografi

Plus Jakarta Sans (sudah dikonfigurasi di `tailwind.config.js`), dengan Source Serif 4 sebagai cadangan untuk konteks formal. Hierarki teks: judul halaman publik `font-display text-4xl md:text-5xl lg:text-6xl font-bold text-brand-navy` (judul halaman admin `text-xl sm:text-2xl font-bold`), judul card `text-sm font-bold`, isi `text-sm`, hint `text-xs`.

## Bentuk & Spasi

Card: `rounded-2xl border border-slate-100 bg-white shadow-sm`. Elemen di dalam card: `rounded-lg` atau `rounded-xl`. Padding card `p-4` (panel) atau `p-5` (card data). Spasi antar card `gap-4`/`gap-5`.

## Prinsip

- Konten menentukan tata letak (C-3): setiap bagian ada karena data/aplikasi membutuhkannya.
- Teknik harus punya tujuan tertulis (R-31 antislop).
- Teks tidak boleh terpotong di breakpoint umum (375 / 768 / 1024 / 1440 px).
