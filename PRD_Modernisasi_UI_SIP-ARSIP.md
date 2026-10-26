# PRD — Modernisasi UI Layout & Filter Responsif

**Produk:** SIP-ARSIP QR — Kelurahan Grogol Selatan
**Dokumen:** PRD-UI-001
**Versi:** 1.0
**Status:** Draft siap eksekusi
**Tanggal:** 8 September 2026

## 1. Ringkasan eksekutif
Meningkatkan pengalaman antarmuka aplikasi arsip agar terasa modern, rapi, dan nyaman di semua ukuran layar. Fokus utama:
* Sidebar dapat dibuka/ditutup (collapse) di desktop, tetap drawer overlay di mobile.
* Bar pencarian & filter tidak memotong teks.
* Seluruh elemen layout responsif tanpa merusak alur bisnis yang sudah ada (filter, CRUD, QR, backup).
* Perubahan bersifat frontend/UI pada stack Laravel + Inertia + Vue 3 + Tailwind yang sudah berjalan. Tidak ada perubahan API backend kecuali tidak diperlukan.

## 2. Latar belakang & masalah saat ini
| ID | Masalah | Dampak | Bukti di kode |
|---|---|---|---|
| P1 | Sidebar desktop fixed 260px, tidak bisa collapse | Area konten sempit; terasa kaku | `Sidebar.vue`, `AppLayout.vue` |
| P2 | Mobile sudah drawer, desktop belum toggle modern | Inkonsistensi UX antar perangkat | `AppLayout.vue` (mobileOpen saja) |
| P3 | Placeholder search terlalu panjang & layout sempit | Teks input/placeholder terpotong | `Arsip/Index.vue` |
| P4 | Select sort xl:col-span-1 terlalu sempit | “Terbaru / Terlama / Kode” terpotong | `Arsip/Index.vue` |
| P5 | Filter memakai grid 12-kolom kaku di xl | Breakpoint menengah mudah overflow/wrap jelek | `Arsip/Index.vue` |

## 3. Tujuan & non-tujuan

### 3.1 Tujuan (Goals)
* **G1** — Sidebar modern: expanded / collapsed di desktop; drawer di mobile.
* **G2** — Teks search & opsi sort selalu terbaca penuh pada viewport umum (360px–1440px+).
* **G3** — Konten utama menyesuaikan lebar sidebar tanpa horizontal scroll tidak perlu pada shell layout.
* **G4** — Preferensi collapse tersimpan antar sesi (localStorage).
* **G5** — Visual tetap konsisten dengan brand (brand-navy, brand-blue, rounded-xl, shadow-sm).

### 3.2 Non-tujuan (Out of scope)
* Redesign ulang seluruh halaman Auth / Profile / Welcome.
* Fitur Ekspor CSV (tetap disabled seperti sekarang).
* Custom dropdown library baru (kecuali native select sudah cukup).
* Card-list pengganti tabel di mobile (boleh fase 2).
* Perubahan logika filter/query backend.
* Dark mode.

## 4. Pengguna & skenario

### 4.1 Persona
| Persona | Peran | Kebutuhan UI |
|---|---|---|
| Staf pengarsipan | Input & cari dokumen harian | Search jelas, sidebar tidak mengganggu |
| Admin | Backup + manajemen penuh | Akses menu Cadangkan tetap jelas saat collapse (ikon + tooltip) |
| Pengguna mobile/tablet | Kerja di ruang terbatas | Drawer mudah dibuka/ditutup; filter stack rapi |

### 4.2 User stories
* Sebagai staf, saya ingin menutup sidebar di desktop agar tabel arsip lebih lebar.
* Sebagai staf, saya ingin membuka kembali sidebar dengan satu klik.
* Sebagai staf mobile, saya ingin menu drawer yang bisa ditutup dengan backdrop atau tombol tutup.
* Sebagai staf, saya ingin membaca placeholder search dan opsi Terbaru/Terlama/Kode tanpa terpotong.
* Sebagai staf, saya ingin preferensi sidebar tetap sama setelah refresh browser.

## 5. Ruang lingkup fungsional

### 5.1 Modul terdampak
| File | Perubahan |
|---|---|
| `resources/js/Layouts/AppLayout.vue` | State collapse desktop, toggle header, wiring props |
| `resources/js/Components/Sidebar.vue` | Mode expanded/collapsed, tooltip, transisi lebar |
| `resources/js/Pages/Arsip/Index.vue` | Relayout filter bar, placeholder, min-width kontrol |
| *(Opsional)* Halaman lain yang memakai AppLayout | Ikut manfaat sidebar tanpa ubah konten |

### 5.2 Fitur harus ada (Must)
* **F1 — Collapsible sidebar (desktop ≥ lg)**
  * State: expanded (default) | collapsed.
  * Lebar:
    * Expanded: 260px (existing)
    * Collapsed: 72px–80px (ikon saja)
  * Transisi lebar ~300ms, easing lembut.
  * Saat collapsed:
    * Label teks menu disembunyikan
    * Badge count disembunyikan atau diganti indikator minimal
    * Subtitle brand disembunyikan; logo “DKI” tetap
    * Item nav tetap klikable via ikon
    * Tooltip/title menampilkan nama item (Semua Dokumen, seksi, Pindai QR, Cadangkan, dll.)
  * Tombol toggle di:
    * Header konten (hamburger/chevron), dan/atau
    * Tepi sidebar (chevron)
  * Persistensi: localStorage key misalnya `siparsip.sidebar.collapsed = "true"|"false"`.
  * Restore state saat mount (hindari flash: boleh default expanded dulu, lalu sync — atau baca sync di setup).

* **F2 — Mobile drawer (< lg) — perbaikan polish**
  * Pertahankan overlay + backdrop (sudah ada).
  * Drawer full-height, lebar ≈ 260px (selalu expanded di mobile; collapse icon-rail tidak wajib di mobile).
  * Tutup saat: klik backdrop, navigasi Link di sidebar, tombol tutup (X) di header drawer, setelah aksi “Pindai QR”.
  * Tombol buka di top bar mobile (ganti/perkaya label “Menu” dengan ikon).

* **F3 — Filter bar anti-truncate (halaman Arsip Index)**
  * Ganti layout kaku `xl:grid-cols-12` + `col-span-1` menjadi flex responsive:
    * Mobile: stack vertikal full width
    * sm+: wrap horizontal dengan gap konsisten
  * Search: `flex-1` + `min-w-0` + basis minimum (~280px)
  * Kontrol status, tanggal, sort:
    * `w-full` di mobile
    * `sm:w-auto` + `min-w-[130px]` (sort) / `min-w-[140px]` (status) di desktop
  * Placeholder search dipersingkat, contoh: *Cari nomor, perihal, atau rak...*
  * Hint “Tekan /” dipindah ke title input atau teks bantuan kecil di bawah baris filter.
  * Tinggi kontrol seragam (~40px / py-2.5).
  * Tidak mengubah perilaku debounce search 350ms dan applyFilters.

* **F4 — Shell layout responsif**
  * Main content: `min-w-0 flex-1` (sudah ada) tetap dijaga.
  * Saat sidebar collapse, tabel/filter mendapat ruang ekstra tanpa reflow rusak.
  * Header page (judul + CTA) tetap flex-col → lg:flex-row seperti sekarang; pastikan tidak bentrok dengan top bar mobile.

### 5.3 Fitur sebaiknya ada (Should)
* **S1** — Chevron animasi (rotasi) saat collapse/expand.
* **S2** — Focus ring konsisten `focus:ring-2 focus:ring-brand-blue/30`.
* **S3** — Escape menutup mobile drawer.
* **S4** — Aria attributes: `aria-expanded`, `aria-controls`, `aria-label` pada tombol toggle.

### 5.4 Fitur bisa ditunda (Could / Fase 2)
* **C1** — Card view tabel arsip di mobile.
* **C2** — Custom select / combobox styled.
* **C3** — Collapse otomatis saat viewport < xl.
* **C4** — Animasi stagger pada item nav.

## 6. Spesifikasi UI/UX detail

### 6.1 Breakpoint behavior
| Viewport | Sidebar | Top bar | Filter | Tabel |
|---|---|---|---|---|
| < 640px | Drawer overlay | Tampil (menu + brand + user) | Stack 1 kolom | Horizontal scroll OK |
| 640–1023px | Drawer overlay | Tampil | Wrap 2 baris | Horizontal scroll OK |
| ≥ 1024px (lg) | Persistent collapsible | Top bar mobile disembunyikan | 1 baris wrap, search fleksibel | Lebar mengikuti sidebar |

*(Selaras Tailwind default: lg = 1024px.)*

### 6.2 Visual tokens (pertahankan)
* **Background app:** `#EEF2F7`
* **Sidebar:** `bg-brand-navy`, active: `bg-brand-blue`
* **Kartu filter/tabel:** `rounded-2xl`, `border-slate-200`, `shadow-sm`
* **Kontrol form:** `rounded-xl`, `text-sm`
* *Jangan ganti ke tema ungu/cream generik; tetap navy–blue institusional.*

### 6.3 Sidebar collapsed — mapping elemen
| Elemen expanded | Collapsed |
|---|---|
| Logo + judul + subtitle | Logo bulat saja |
| “Kategori Pengarsipan” label | Sembunyi |
| Nav text + count badge | Ikon saja (+ title) |
| “Peralatan Cepat” label | Sembunyi |
| Scan / Backup text | Ikon saja |
| User name + status | Avatar saja |
| Link Keluar | Ikon logout atau tetap teks singkat jika muat |

### 6.4 Copy / microcopy
| Lokasi | Copy baru |
|---|---|
| Search placeholder | Cari nomor, perihal, atau rak... |
| Search title (opsional) | Tekan / untuk fokus ke pencarian |
| Toggle sidebar | Ciutkan menu / Perluas menu |
| Tutup drawer | Tutup menu |

## 7. Persyaratan teknis

### 7.1 Stack
* Vue 3 `<script setup>`
* Inertia.js
* Tailwind CSS (utility classes)
* Tidak wajib dependency baru

### 7.2 State management
* **AppLayout**
  * `mobileOpen: boolean`
  * `desktopCollapsed: boolean` *(synced localStorage)*
  * → props ke Sidebar: `collapsed` (desktop only meaningful)
  * → item "Pindai QR Berkas" navigasi ke halaman `/scan` (`route('scan.index')`); drawer mobile tertutup via event `navigate`

* **Catatan:** Ada dua instance Sidebar (mobile + desktop) di AppLayout. Pastikan:
  1. prop `collapsed` hanya diterapkan ke instance desktop, atau
  2. mobile selalu force `collapsed=false`.

### 7.3 Accessibility
* Tombol toggle keyboard-accessible.
* Kontrast teks sidebar tetap memadai.
* Tooltip native title minimum; `aria-label` wajib pada icon-only.

### 7.4 Performance
* Transisi CSS only (hindari animasi JS berat).
* Tidak menambah re-fetch Inertia saat toggle sidebar.
* Filter behavior existing tetap (`preserveState`, `only: [...]`).

### 7.5 Kompatibilitas
* Chrome / Edge / Firefox terbaru.
* Windows (lingkungan utama user).
* Lebar uji: 360, 768, 1024, 1280, 1440.

## 8. Kriteria penerimaan (Acceptance Criteria)

**AC — Sidebar**
* **AC1** Di desktop, klik toggle → sidebar mengecil ke icon-rail; konten melebar.
* **AC2** Klik toggle lagi → kembali full 260px.
* **AC3** Refresh browser → state collapse tetap sesuai localStorage.
* **AC4** Semua link seksi & peralatan tetap berfungsi saat collapsed.
* **AC5** Menu Cadangkan (admin) tetap bisa diakses; non-admin tetap disabled/non-link seperti sekarang.
* **AC6** Mobile: drawer buka/tutup via tombol & backdrop; tidak muncul sidebar persistent.

**AC — Filter & teks**
* **AC7** Placeholder search tidak terpotong di 360px–1440px (atau diganti copy pendek yang muat).
* **AC8** Opsi terpilih Terbaru / Terlama / Kode terbaca penuh di kotak select (tidak ... karena lebar).
* **AC9** Status & date input tidak saling remuk; wrap rapi.
* **AC10** Debounce search & filter Inertia tetap berfungsi sama.

**AC — Responsif & kualitas**
* **AC11** Tidak ada horizontal scroll pada shell layout (kecuali tabel internal yang memang scroll).
* **AC12** Transisi sidebar tidak menyebabkan layout jump kasar.
* **AC13** Visual brand navy/blue konsisten; tidak ada regresi modal/form.

## 9. Rencana implementasi (untuk eksekusi)

* **Fase A — Quick win filter** (prioritas tinggi, risiko rendah)
  * Relayout filter di `Arsip/Index.vue`
  * Persingkat placeholder
  * Set min-width sort & status
  * Estimasi: kecil (≤ 1 jam)

* **Fase B — Collapsible sidebar**
  * State + localStorage di `AppLayout.vue`
  * Prop collapsed + UI conditional di `Sidebar.vue`
  * Tombol toggle + aria
  * Estimasi: sedang (2–4 jam)

* **Fase C — Polish**
  * Escape close drawer
  * Chevron animation
  * Tooltip/title lengkap
  * QA multi-breakpoint
  * Estimasi: kecil–sedang

## 10. Risiko & mitigasi
| Risiko | Dampak | Mitigasi |
|---|---|---|
| Dua instance Sidebar desync | State aneh | Collapse hanya desktop instance |
| Flash of expanded sebelum baca localStorage | Kedipan UI | Baca storage early / class awal dari script singkat |
| Select native Windows tetap memotong | Teks sort masih jelek | Pastikan min-w cukup; uji di Windows |
| Tooltip title lambat | UX kurang | Menerima untuk MVP; custom tooltip fase 2 |
| Regresi navigasi seksi | Filter seksi hilang | Uji klik tiap link saat collapsed & mobile |

## 11. Metrik sukses
* 0 laporan “teks terpotong” pada search/sort setelah rilis.
* Sidebar toggle dipakai (observasi kualitatif user staf).
* Tidak ada regresi fungsional filter/QR/CRUD.
* Lolos checklist AC di atas pada 5 lebar layar uji.

## 12. Dependensi & asumsi
* Aplikasi sudah memakai `AppLayout` + `Sidebar` untuk halaman Arsip (dan halaman terkait).
* Breakpoint proyek mengikuti Tailwind default.
* Brand colors sudah terdefinisi di `tailwind.config.js`.
* Tidak ada requirement multi-bahasa untuk rilis ini (copy Bahasa Indonesia).

## 13. Lampiran — referensi file
| Komponen | Path |
|---|---|
| Layout shell | `resources/js/Layouts/AppLayout.vue` |
| Sidebar | `resources/js/Components/Sidebar.vue` |
| Filter + search | `resources/js/Pages/Arsip/Index.vue` |
| Tabel | `resources/js/Components/ArsipTable.vue` (hanya pastikan tidak regresi) |

## 14. Keputusan produk (ringkas)
| Keputusan | Pilihan |
|---|---|
| Collapse desktop | Icon-rail 72–80px |
| Mobile collapse icon-rail | Tidak (drawer full saja) |
| Persistensi | localStorage |
| Library UI baru | Tidak |
| Fix truncate | Flex layout + min-width + copy pendek |
| Backend | Tidak diubah |
