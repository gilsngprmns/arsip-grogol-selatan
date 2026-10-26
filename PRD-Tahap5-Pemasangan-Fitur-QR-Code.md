# PRD — Tahap 5: Pemasangan Fitur QR Code
## Sistem Pencarian dan Pengarsipan Dokumen Surat Berbasis Web dengan Teknologi Barcode
**Studi Kasus: Kantor Lurah Grogol Selatan**

---

### Informasi Dokumen

| Item | Keterangan |
|---|---|
| Nama Proyek | Rancang Bangun Sistem Pencarian dan Pengarsipan Dokumen Surat Berbasis Web dengan Teknologi Barcode pada Kelurahan Grogol Selatan |
| Tahapan | Tahap 5 dari 6 — Pemasangan Fitur QR Code |
| Prasyarat | Tahap 1 (Skema Database), Tahap 2 (Tumpukan Teknologi), Tahap 3 (Backend Dasar), dan Tahap 4 (Frontend Vue) sudah disetujui |
| Disusun oleh | Rapli — Mahasiswa Sistem Informasi, UNINDRA (Kerja Praktik) |
| Status | Draft, untuk direview sebelum lanjut ke Tahap 6 |
| Tanggal | 8 September 2026 |

---

## 1. Latar Belakang

Kolom `kode_arsip` (unik) dan `qr_code_path` sudah dirancang sejak Tahap 1, tapi sampai Tahap 4 keduanya belum benar-benar dipakai. Tahap 5 mengaktifkan fungsi inti sistem yang menjadi ciri khas proyek ini yaitu teknologi barcode/QR: setiap arsip baru otomatis mendapat gambar QR dari `kode_arsip`-nya, staf bisa mencetak stiker label berisi QR tersebut, dan stiker fisik itu bisa dipindai kembali lewat webcam untuk langsung menemukan data arsipnya di sistem.

## 2. Tujuan Tahap 5

1. Memasang package pembuat QR Code di ekosistem Laravel.
2. Membuat logika otomatis yang mengubah `kode_arsip` menjadi gambar QR setiap kali arsip baru disimpan, dan meregenerasi gambar bila `kode_arsip` diubah.
3. Merancang modal cetak stiker label berisi gambar QR beserta rincian singkat surat.
4. Memasang pembaca QR di sisi frontend Vue agar staf bisa memindai stiker fisik lewat webcam dan langsung menemukan arsip yang cocok.

## 3. Ruang Lingkup

**Termasuk dalam Tahap 5:**
- Instalasi package QR Code generator via Composer
- Observer/logika otomatis generate & regenerate gambar QR
- Endpoint pencarian arsip berdasarkan `kode_arsip` hasil pindai
- Modal cetak stiker label (`PrintLabelModal.vue`)
- Komponen pemindai QR berbasis webcam (`QrScanner.vue`)

**Tidak termasuk (dikerjakan di tahap selanjutnya):**
- Upload file lampiran PDF/foto hasil scan surat (Tahap 6)
- Fungsi export dan import backup data (Tahap 6)

**Catatan lingkup:** `php artisan storage:link` seharusnya baru dijalankan resmi di Tahap 6, tapi karena gambar QR di tahap ini perlu diakses lewat browser (ditampilkan di modal cetak), perintah ini perlu dijalankan lebih awal di Tahap 5. Ini satu-satunya tumpang tindih dengan Tahap 6.

## 4. Prasyarat

- Tahap 1 sampai 4 sudah selesai dan disetujui.
- Kolom `kode_arsip` dan `qr_code_path` sudah ada di tabel `dokumen_arsip` (Tahap 1 dan 3).
- `ArsipController@store` dan `@update` sudah berfungsi (Tahap 3).
- `ArsipFormModal.vue` dan `ActionButtons.vue` sudah tersedia (Tahap 4) sebagai tempat menambahkan tombol cetak label.

## 5. Instalasi Package QR Code

```bash
composer require simplesoftwareio/simple-qrcode
php artisan storage:link
```

Laravel modern (Laravel 11 ke atas) sudah memakai auto-discovery paket, jadi tidak perlu registrasi manual facade di `config/app.php`.

## 6. Logika Generate QR Code (Backend)

Gunakan Observer, bukan menaruh logika langsung di controller, agar generate QR tetap berjalan meski record dibuat lewat jalur lain (seeder, tinker, dsb).

### 6.1 `app/Observers/DokumenArsipObserver.php`

- Method `created(DokumenArsip $arsip)`: generate gambar QR dari `$arsip->kode_arsip` menggunakan `QrCode::format('png')->size(300)->generate(...)`, simpan ke `storage/app/public/qrcodes/{kode_arsip}.png`, lalu update kolom `qr_code_path` dengan path relatif tersebut.
- Method `updated(DokumenArsip $arsip)`: jika `kode_arsip` berubah (`$arsip->isDirty('kode_arsip')`), hapus file QR lama, generate ulang QR untuk `kode_arsip` baru, dan perbarui `qr_code_path`.

### 6.2 Registrasi Observer

Di `app/Providers/AppServiceProvider.php`, method `boot()`:

```php
DokumenArsip::observe(DokumenArsipObserver::class);
```

## 7. Modal Cetak Stiker Label — `resources/js/Components/PrintLabelModal.vue`

Props: `show` (boolean), `arsip` (data satu dokumen arsip, termasuk `qr_code_path`)

Isi modal:
- Gambar QR dari `qr_code_path` (diakses lewat `/storage/qrcodes/{kode_arsip}.png`)
- `kode_arsip`, `nomor_surat`, `perihal`, `tanggal_surat`, dan nama seksi
- Tombol "Cetak" yang memicu `window.print()`, dengan CSS `@media print` khusus supaya hanya area stiker yang tercetak, disesuaikan untuk ukuran label kecil (misalnya 5x3 cm)

Tambahkan tombol ikon cetak baru pada `ActionButtons.vue` (Tahap 4) di tiap baris arsip untuk membuka modal ini.

## 8. Pembaca QR Code (Frontend) — `resources/js/Components/QrScanner.vue`

### 8.1 Instalasi

```bash
npm install html5-qrcode
```

### 8.2 Alur Kerja Komponen

- Mengaktifkan kamera perangkat lewat `html5-qrcode`, menampilkan area pemindaian di layar
- Setiap hasil decode QR (berupa string `kode_arsip`) dikirim lewat `axios.get('/api/arsip/scan/' + kode)`
- Jika ditemukan, tampilkan detail arsip yang cocok langsung di layar (kode, nomor surat, perihal, status)
- Jika tidak ditemukan, tampilkan pesan "Kode arsip tidak ditemukan" tanpa menghentikan sesi pemindaian, supaya staf bisa langsung mencoba stiker lain

### 8.3 Endpoint Pencarian

Tambahkan method baru di `ArsipController`:

```php
public function scanByKode(string $kodeArsip)
{
    $arsip = DokumenArsip::with('seksi')->where('kode_arsip', $kodeArsip)->first();

    if (!$arsip) {
        return response()->json(['message' => 'Arsip tidak ditemukan'], 404);
    }

    return response()->json($arsip);
}
```

Endpoint ini sengaja memakai response JSON biasa (bukan Inertia), karena `QrScanner.vue` butuh hasil instan tanpa reload halaman.

### 8.4 Routing

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/api/arsip/scan/{kode_arsip}', [ArsipController::class, 'scanByKode']);
});
```

## 9. Kriteria Selesai (Definition of Done) Tahap 5

- [ ] Package QR Code terpasang dan berhasil generate gambar dari string `kode_arsip`
- [ ] Setiap arsip baru otomatis mendapat file QR tersimpan, `qr_code_path` terisi dengan benar
- [ ] Perubahan `kode_arsip` lewat form edit meregenerasi gambar QR yang sesuai dan menghapus file lama
- [ ] Modal cetak label menampilkan QR dan rincian singkat surat, hasil cetak rapi sesuai ukuran stiker
- [ ] `QrScanner.vue` berhasil membaca QR fisik lewat webcam dan menampilkan detail arsip yang cocok
- [ ] Pesan jelas ditampilkan jika kode hasil pindai tidak cocok dengan data manapun

## 10. Catatan Teknis Tambahan

- Akses kamera browser memerlukan konteks aman (HTTPS) atau `localhost`. Untuk pengujian di perangkat lain dalam satu jaringan, pertimbangkan Laravel Herd/Valet dengan sertifikat lokal, karena akses HTTP biasa dari IP lokal biasanya diblokir browser untuk fitur kamera.
- Ukuran QR 300x300 piksel cukup untuk tetap terbaca ulang tanpa membuat ukuran file terlalu besar.
- Karena `kode_arsip` dipakai sebagai isi QR, pastikan validasi `unique` pada kolom ini (sudah ada sejak Tahap 3) tetap dipertahankan, karena QR yang mengarah ke kode duplikat akan membingungkan saat pemindaian.

## 11. Referensi Command (Ringkasan)

```bash
composer require simplesoftwareio/simple-qrcode
php artisan storage:link
npm install html5-qrcode
```

---

## 12. Instruksi Eksekusi untuk Cursor AI

Kerjakan secara berurutan, konfirmasi tiap langkah selesai sebelum lanjut ke langkah berikutnya:

1. Install package `simplesoftwareio/simple-qrcode` via Composer, lalu jalankan `php artisan storage:link`.
2. Buat `DokumenArsipObserver` sesuai Bagian 6.1, daftarkan di `AppServiceProvider` sesuai Bagian 6.2.
3. Tambahkan method `scanByKode` di `ArsipController` sesuai Bagian 8.3, daftarkan route-nya sesuai Bagian 8.4.
4. Buat `PrintLabelModal.vue` sesuai Bagian 7, tambahkan tombol cetak baru di `ActionButtons.vue`.
5. Install `html5-qrcode` via npm, buat `QrScanner.vue` sesuai Bagian 8.2, hubungkan ke endpoint `scanByKode` lewat axios.
6. Uji alur penuh: tambah arsip baru, pastikan QR otomatis muncul, cetak label, lalu pindai label tersebut lewat webcam dan pastikan detail arsip yang cocok tampil.
7. Uji juga kasus gagal: pindai kode yang tidak ada di database, pastikan pesan error tampil tanpa menghentikan sesi pemindaian.
8. Laporkan ringkasan file yang dibuat/diubah di akhir proses.

---

*Dokumen ini adalah bagian dari rangkaian PRD 6 tahap. Setelah Tahap 5 disetujui, lanjutkan ke Tahap 6 — Pengelolaan File dan Backup.*
