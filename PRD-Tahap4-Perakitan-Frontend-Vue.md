# PRD — Tahap 4: Perakitan Frontend dengan Vue
## Sistem Pencarian dan Pengarsipan Dokumen Surat Berbasis Web dengan Teknologi Barcode
**Studi Kasus: Kantor Lurah Grogol Selatan**

---

### Informasi Dokumen

| Item | Keterangan |
|---|---|
| Nama Proyek | Rancang Bangun Sistem Pencarian dan Pengarsipan Dokumen Surat Berbasis Web dengan Teknologi Barcode pada Kelurahan Grogol Selatan |
| Tahapan | Tahap 4 dari 6 — Perakitan Frontend dengan Vue |
| Prasyarat | Tahap 1 (Skema Database), Tahap 2 (Tumpukan Teknologi), dan Tahap 3 (Backend Dasar) sudah disetujui |
| Disusun oleh | Rapli — Mahasiswa Sistem Informasi, UNINDRA (Kerja Praktik) |
| Status | Draft, untuk direview sebelum lanjut ke Tahap 5 |
| Tanggal | 8 September 2026 |

---

## 1. Latar Belakang

Backend Tahap 3 sudah menyediakan `ArsipController` dengan seluruh method resource (`index`, `create`, `store`, `edit`, `update`, `destroy`) yang mengirim data lewat Inertia. Tahap 4 membangun antarmuka Vue yang menerima data tersebut dan menampilkannya sebagai halaman yang bisa dipakai staf kelurahan, mulai dari kerangka tata letak (layout) hingga komponen-komponen kecil yang bisa dipakai ulang.

Karena Breeze (Tahap 2) sudah menyediakan layout autentikasi dan struktur folder `resources/js/Pages` serta `resources/js/Components`, Tahap 4 tinggal memperluas struktur bawaan tersebut, bukan membangun dari nol.

## 2. Tujuan Tahap 4

1. Membangun kerangka tata letak utama (sidebar navigasi + area konten) yang dipakai di seluruh halaman arsip.
2. Memecah tampilan menjadi komponen Vue yang lebih kecil dan bisa dipakai ulang: tabel data, form modal rekam arsip, dan tombol aksi.
3. Merapikan tampilan dengan Tailwind CSS agar konsisten dengan desain yang disetujui pihak kelurahan.
4. Menangkap data asli dari `ArsipController` (Tahap 3) lewat properti Inertia, sehingga tabel arsip menampilkan data sungguhan, bukan data contoh (dummy).

## 3. Ruang Lingkup

**Termasuk dalam Tahap 4:**
- Kerangka layout utama dengan sidebar dan area konten
- Halaman `Arsip/Index.vue` sebagai halaman utama daftar arsip
- Komponen `ArsipTable.vue`, `ArsipFormModal.vue`, `ActionButtons.vue`, dan komponen sidebar
- Styling dengan Tailwind CSS
- Pengambilan data nyata dari backend Tahap 3 lewat props Inertia

**Tidak termasuk (dikerjakan di tahap selanjutnya):**
- Logika pembuatan gambar QR Code dan tampilan cetak stiker label (Tahap 5)
- Pemasangan pembaca QR Code lewat webcam (Tahap 5)
- Upload file lampiran dan fungsi export/import backup (Tahap 6)

## 4. Prasyarat

- Tahap 1 sampai 3 sudah selesai dan disetujui.
- `ArsipController` sudah mengirim prop `arsip` (dari `index()`) dan `seksiList` (dari `create()`/`edit()`) lewat Inertia.
- File desain (mockup) dari pihak kelurahan sudah tersedia sebagai acuan visual. **Catatan: dokumen ini belum menerima file desain tersebut, jadi styling di Bagian 6 disusun sebagai kerangka Tailwind yang wajar dan mudah disesuaikan, bukan hasil menyalin desain final. Sesuaikan warna, jarak, dan tipografi begitu file desain diterima.**

## 5. Struktur Halaman & Komponen Vue

### 5.1 Layout Utama — `resources/js/Layouts/AppLayout.vue`

Perluas layout bawaan Breeze (`AuthenticatedLayout.vue`) menjadi layout dua kolom:
- Kolom kiri: sidebar navigasi (`Sidebar.vue`), berisi menu Dashboard, Data Arsip, dan filter per seksi
- Kolom kanan: area konten utama (`<slot />`) tempat setiap halaman dirender
- Header atas tetap memakai komponen navigasi Breeze yang sudah ada (nama user, tombol logout)

### 5.2 Halaman `resources/js/Pages/Arsip/Index.vue`

Menerima props dari `ArsipController@index`:
- `arsip` — data terpaginasi dari `DokumenArsip::with('seksi')`
- `seksiList` — daftar seksi untuk dropdown filter dan form (lihat Bagian 5.6 untuk cara penyediaannya)

Isi halaman:
- Judul halaman dan tombol "Tambah Arsip" yang membuka `ArsipFormModal`
- Filter sederhana per seksi (dropdown dari `seksiList`)
- `ArsipTable` yang menampilkan daftar arsip beserta `ActionButtons` di tiap baris

### 5.3 Komponen `resources/js/Components/ArsipTable.vue`

Props: `rows` (array data arsip)
Kolom yang ditampilkan: `kode_arsip`, `nomor_surat`, `perihal`, `jenis_surat`, `tanggal_surat`, nama seksi (`row.seksi.nama_seksi`), `status_arsip`, dan kolom aksi berisi `ActionButtons`.
Gunakan badge warna berbeda untuk tiap nilai `status_arsip` (tersedia/dipinjam/hilang) agar mudah dipindai secara visual.

### 5.4 Komponen `resources/js/Components/ArsipFormModal.vue`

Dipakai untuk dua mode: tambah arsip baru dan edit arsip yang sudah ada.

Props: `show` (boolean), `mode` ('create' atau 'edit'), `seksiList`, `initialData` (opsional, dipakai saat mode edit)
Emits: `close`

Isi form mengikuti kolom `dokumen_arsip` dari Tahap 3: `kode_arsip`, `nomor_surat`, `perihal`, `jenis_surat` (select masuk/keluar), `sifat_surat` (select), `tanggal_surat` (date picker), `tanggal_diterima` (date picker, tampil jika `jenis_surat = masuk`), `asal_instansi` (tampil jika `jenis_surat = masuk`), `tujuan_instansi` (tampil jika `jenis_surat = keluar`), `seksi_id` (select dari `seksiList`).

Gunakan `useForm` bawaan Inertia:
- Mode create: `form.post(route('arsip.store'))`
- Mode edit: `form.put(route('arsip.update', initialData.id))`

Tampilkan pesan error validasi dari `form.errors` di bawah tiap input yang relevan.

### 5.5 Komponen `resources/js/Components/ActionButtons.vue`

Props: `arsip` (satu baris data)
Isi: tombol "Edit" yang men-emit event untuk membuka `ArsipFormModal` dalam mode edit dengan `initialData` terisi, dan tombol "Hapus" yang memicu konfirmasi (`confirm()` browser atau modal konfirmasi sederhana) sebelum memanggil `router.delete(route('arsip.destroy', arsip.id))`.

### 5.6 Penyesuaian Backend Pendukung — Shared Props Inertia

Agar `seksiList` tersedia di halaman mana pun tanpa menambah kode di setiap method controller, tambahkan ke `app/Http/Middleware/HandleInertiaRequests.php`:

```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'seksiList' => \App\Models\Seksi::select('id', 'nama_seksi')->get(),
    ]);
}
```

Dengan begini, `Index.vue` dan `ArsipFormModal.vue` bisa memakai modal untuk create maupun edit tanpa perlu berpindah ke halaman `Create.vue`/`Edit.vue` yang sudah dibuat di Tahap 3. Route `create` dan `edit` dari Tahap 3 tetap ada sebagai fallback (misalnya untuk akses langsung lewat URL), tapi alur utama staf kelurahan memakai modal di halaman `Index.vue`.

## 6. Styling dengan Tailwind CSS

Karena file desain final dari pihak kelurahan belum diterima, gunakan kerangka berikut sebagai titik awal:
- Warna aksen utama: satu warna solid untuk tombol utama dan status "tersedia" (hijau), "dipinjam" (kuning), "hilang" (merah)
- Sidebar: lebar tetap sekitar 250px, latar warna gelap kontras dengan area konten
- Tabel: baris berselang warna (zebra striping) memakai `even:bg-gray-50` agar mudah dibaca untuk daftar arsip yang panjang
- Modal: lebar maksimum sedang (`max-w-lg` atau `max-w-xl`), dengan overlay gelap transparan di belakangnya

**Setelah file desain kelurahan diterima, sesuaikan kembali kelas Tailwind di atas agar sama persis dengan mockup yang disetujui.**

## 7. Alur Data dari Backend ke Frontend

1. Staf membuka halaman arsip lewat route `arsip.index`.
2. `ArsipController@index` mengirim prop `arsip` (data asli dari database), sementara `seksiList` sudah otomatis tersedia lewat shared props (Bagian 5.6).
3. `Index.vue` meneruskan `arsip.data` ke `ArsipTable` untuk ditampilkan.
4. Saat staf menekan "Tambah Arsip" atau "Edit", `ArsipFormModal` terbuka dan mengirim data lewat `useForm` ke `store` atau `update`.
5. Setelah berhasil, Inertia otomatis me-refresh prop `arsip` tanpa reload penuh halaman, sehingga tabel langsung menampilkan data terbaru.

## 8. Kriteria Selesai (Definition of Done) Tahap 4

- [ ] Layout utama dengan sidebar dan area konten berhasil dibangun dan dipakai di halaman arsip
- [ ] `ArsipTable`, `ArsipFormModal`, dan `ActionButtons` berhasil dipecah menjadi komponen terpisah dan dapat dipakai ulang
- [ ] Data yang tampil di tabel adalah data asli dari database (bukan data contoh), diverifikasi dengan menambah satu data arsip lewat form dan melihatnya langsung muncul di tabel
- [ ] Tambah, edit, dan hapus arsip berfungsi penuh lewat modal tanpa reload halaman penuh
- [ ] Tampilan sudah dirapikan dengan Tailwind CSS sesuai kerangka Bagian 6 (atau desain final kelurahan jika sudah diterima)
- [ ] Pesan error validasi dari backend tampil dengan jelas di form

## 9. Catatan Teknis Tambahan

- Karena `sifat_surat`, `tanggal_diterima`, `asal_instansi`, dan `tujuan_instansi` bersifat kondisional terhadap `jenis_surat`, gunakan `v-if`/`v-show` di `ArsipFormModal` agar staf tidak mengisi kolom yang tidak relevan.
- Gunakan `router.reload({ only: ['arsip'] })` dari Inertia jika ingin me-refresh tabel tanpa mengambil ulang seluruh props halaman.
- Karena tabel data bisa cukup panjang (banyak surat masuk/keluar), pastikan `index()` di Tahap 3 sudah memakai `paginate()`, dan `ArsipTable` menampilkan kontrol pagination di bagian bawah.

## 10. Referensi Command (Ringkasan)

```bash
npm run dev
php artisan serve
```

Tidak ada perintah instalasi baru di tahap ini, seluruh dependency frontend sudah terpasang sejak Tahap 2.

---

## 11. Instruksi Eksekusi untuk Cursor AI

Kerjakan secara berurutan, konfirmasi tiap langkah selesai sebelum lanjut ke langkah berikutnya:

1. Perluas `AuthenticatedLayout.vue` bawaan Breeze menjadi `AppLayout.vue` dengan struktur dua kolom sesuai Bagian 5.1, termasuk membuat komponen `Sidebar.vue`.
2. Tambahkan `seksiList` ke shared props Inertia lewat `HandleInertiaRequests.php` sesuai Bagian 5.6.
3. Buat komponen `ArsipTable.vue` sesuai Bagian 5.3, termasuk badge warna untuk `status_arsip`.
4. Buat komponen `ArsipFormModal.vue` sesuai Bagian 5.4, dengan field kondisional berdasarkan `jenis_surat` dan integrasi `useForm` untuk create maupun edit.
5. Buat komponen `ActionButtons.vue` sesuai Bagian 5.5.
6. Rakit `Arsip/Index.vue` yang menggabungkan `AppLayout`, filter seksi, `ArsipTable`, dan `ArsipFormModal`, menangkap prop `arsip` dari `ArsipController@index`.
7. Terapkan styling Tailwind sesuai kerangka Bagian 6.
8. Uji alur penuh: tambah arsip baru lewat modal, verifikasi muncul di tabel tanpa reload, edit data yang sama, lalu hapus, dan pastikan setiap langkah mengembalikan pesan sukses/gagal yang jelas.
9. Laporkan ringkasan file yang dibuat/diubah di akhir proses.

---

*Dokumen ini adalah bagian dari rangkaian PRD 6 tahap. Setelah Tahap 4 disetujui, lanjutkan ke Tahap 5 — Pemasangan Fitur QR Code.*
