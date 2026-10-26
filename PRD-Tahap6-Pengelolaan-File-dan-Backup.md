# PRD — Tahap 6: Pengelolaan File dan Backup
## Sistem Pencarian dan Pengarsipan Dokumen Surat Berbasis Web dengan Teknologi Barcode
**Studi Kasus: Kantor Lurah Grogol Selatan**

---

### Informasi Dokumen

| Item | Keterangan |
|---|---|
| Nama Proyek | Rancang Bangun Sistem Pencarian dan Pengarsipan Dokumen Surat Berbasis Web dengan Teknologi Barcode pada Kelurahan Grogol Selatan |
| Tahapan | Tahap 6 dari 6 — Pengelolaan File dan Backup (tahap terakhir) |
| Prasyarat | Tahap 1 sampai Tahap 5 sudah disetujui |
| Disusun oleh | Rapli — Mahasiswa Sistem Informasi, UNINDRA (Kerja Praktik) |
| Status | Draft, untuk direview sebagai penutup rangkaian PRD proyek |
| Tanggal | 8 September 2026 |

---

## 1. Latar Belakang

Kolom `file_path` pada `dokumen_arsip` sudah dirancang sejak Tahap 1, tapi belum dipakai sampai Tahap 5. Tahap 6 mengaktifkan unggahan file lampiran (PDF atau foto hasil scan surat) dan menambahkan mekanisme backup/restore data lewat file JSON, sebagai tahap penutup dari keseluruhan proyek Kerja Praktik ini.

`php artisan storage:link` sudah dijalankan lebih awal di Tahap 5 untuk kebutuhan gambar QR, sehingga tahap ini tidak perlu menjalankannya lagi, cukup memastikan folder lampiran baru ikut ter-symlink dengan benar.

## 2. Tujuan Tahap 6

1. Menambahkan logika unggah file lampiran (PDF/foto) ke `ArsipController`, disimpan di storage lokal bawaan Laravel.
2. Memastikan file lampiran dapat diakses lewat browser menggunakan symlink storage yang sudah aktif sejak Tahap 5.
3. Membuat fungsi ekspor seluruh data arsip menjadi file JSON yang bisa diunduh sebagai backup.
4. Membuat fungsi impor yang membaca file backup JSON dan memulihkan data ke tabel database.

## 3. Ruang Lingkup

**Termasuk dalam Tahap 6:**
- Validasi dan logika unggah file lampiran pada `store()`/`update()` di `ArsipController`
- Penghapusan file lampiran lama saat diganti dengan file baru
- `BackupController` baru dengan fungsi `export()` dan `import()`
- Halaman/komponen Vue untuk mengunduh backup dan mengunggah file restore

**Tidak termasuk:** tahap ini adalah tahap terakhir dari 6 tahap proyek, sehingga tidak ada ruang lingkup yang sengaja ditunda ke tahap berikutnya. Penyempurnaan lanjutan (versioning backup, penyimpanan cloud, dsb) berada di luar cakupan 6 tahap ini dan bisa jadi topik pengembangan lanjutan yang terpisah dari PRD ini.

## 4. Prasyarat

- Tahap 1 sampai 5 sudah selesai dan disetujui.
- `php artisan storage:link` sudah dijalankan (Tahap 5), tidak perlu diulang di tahap ini.
- Kolom `file_path` sudah ada di tabel `dokumen_arsip` (Tahap 1 dan 3).
- Kolom `role` pada tabel `users` sudah ada (Tahap 3), dipakai untuk membatasi akses fitur backup/restore.

## 5. Logika Upload File Lampiran (Backend)

### 5.1 Perubahan pada `StoreArsipRequest` dan `UpdateArsipRequest`

Tambahkan aturan validasi:
- `lampiran`: nullable, file, mimes:pdf,jpg,jpeg,png, max:5120 (maksimum 5MB)

### 5.2 Logika di `ArsipController@store` dan `@update`

- Jika request memuat file `lampiran`, simpan lewat `$request->file('lampiran')->store('lampiran-arsip', 'public')`, lalu isi `file_path` dengan path hasil penyimpanan tersebut.
- Saat `update()` dan ada file baru diunggah, hapus file lama lebih dulu dengan `Storage::disk('public')->delete($arsip->file_path)` sebelum menyimpan file yang baru, supaya storage tidak menumpuk file lampiran yang sudah tidak terpakai.

### 5.3 Akses File

File lampiran dapat diakses lewat URL `/storage/lampiran-arsip/{nama_file}`, karena symlink storage sudah aktif sejak Tahap 5.

## 6. Komponen Frontend Upload File

Tambahkan field input file pada `ArsipFormModal.vue` (Tahap 4), dengan pratinjau nama file yang dipilih sebelum disimpan. Tampilkan tautan "Lihat Lampiran" pada `ArsipTable.vue` atau di dalam modal detail arsip, muncul hanya jika `file_path` sudah terisi.

## 7. Fungsi Ekspor Data (Backup)

### 7.1 `app/Http/Controllers/BackupController.php` — method `export()`

```php
public function export()
{
    $data = DokumenArsip::withTrashed()
        ->with('seksi', 'riwayatSirkulasi')
        ->get();

    $filename = 'backup-arsip-' . now()->format('Y-m-d_His') . '.json';

    return response()->streamDownload(function () use ($data) {
        echo $data->toJson(JSON_PRETTY_PRINT);
    }, $filename, ['Content-Type' => 'application/json']);
}
```

`withTrashed()` dipakai supaya data yang sudah soft delete tetap ikut ter-backup, sesuai keputusan soft delete yang sudah dirancang sejak Tahap 1.

### 7.2 Routing

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/backup/export', [BackupController::class, 'export'])->name('backup.export');
});
```

Pertimbangkan menambah middleware/pengecekan role `admin` (bukan `staf`), karena backup mencakup seluruh data lintas seksi.

## 8. Fungsi Impor Data (Restore)

### 8.1 `BackupController@import`

```php
public function import(Request $request)
{
    $request->validate([
        'backup_file' => 'required|file|mimes:json',
    ]);

    $content = json_decode(
        file_get_contents($request->file('backup_file')->getRealPath()),
        true
    );

    DB::transaction(function () use ($content) {
        foreach ($content as $row) {
            DokumenArsip::updateOrCreate(
                ['kode_arsip' => $row['kode_arsip']],
                Arr::except($row, ['id', 'seksi', 'riwayat_sirkulasi'])
            );
        }
    });

    return back()->with('success', 'Data berhasil dipulihkan dari file backup.');
}
```

**Catatan keputusan:** proses restore memakai `updateOrCreate` berdasarkan `kode_arsip` (bukan `truncate` lalu insert ulang), supaya data yang belum sempat ter-backup tidak ikut terhapus permanen jika restore dijalankan dari file backup yang lebih lama. Seluruh proses dibungkus `DB::transaction()` agar kegagalan di tengah proses tidak meninggalkan data yang setengah-utuh.

### 8.2 Routing

```php
Route::middleware(['auth'])->group(function () {
    Route::post('/backup/import', [BackupController::class, 'import'])->name('backup.import');
});
```

## 9. Komponen Frontend Backup & Restore

Buat halaman baru `resources/js/Pages/Backup/Index.vue`:
- Tombol "Unduh Backup" berupa tautan `<a :href="route('backup.export')">`, karena ini unduhan file biasa, bukan alur Inertia SPA
- Form unggah file JSON untuk restore, memakai `useForm` dari Inertia, dikirim sebagai `multipart/form-data` ke `backup.import`
- Modal konfirmasi kedua sebelum proses impor benar-benar dijalankan, karena restore berpotensi menimpa data yang sudah ada

## 10. Kriteria Selesai (Definition of Done) Tahap 6

- [ ] File lampiran (PDF/foto) berhasil diunggah saat tambah/edit arsip dan tersimpan di storage lokal
- [ ] File lampiran bisa dibuka lewat tautan dari halaman arsip
- [ ] File lampiran lama otomatis terhapus saat diganti dengan file baru
- [ ] Fungsi ekspor menghasilkan file JSON berisi seluruh data arsip (termasuk yang soft delete) dan langsung terunduh dari browser
- [ ] Fungsi impor berhasil membaca file backup JSON dan mengembalikan data ke database tanpa menghapus data yang belum sempat ter-backup
- [ ] Proses impor dibungkus transaksi database, kegagalan di tengah proses tidak meninggalkan data rusak/duplikat
- [ ] Akses fitur backup/restore terbatas untuk role `admin`

## 11. Catatan Teknis Tambahan

- Batasi ukuran file unggahan lampiran (maksimum 5MB pada contoh di atas) supaya storage lokal tidak cepat penuh, mengingat ini bukan penyimpanan cloud.
- Tampilkan konfirmasi tegas di frontend sebelum proses impor dijalankan, mengingat sifatnya yang berpotensi menimpa data produksi.
- File lampiran dan file backup sebaiknya berada di luar version control (folder `storage` Laravel sudah otomatis diabaikan Git secara default untuk file yang di-generate saat runtime).

## 12. Referensi Command (Ringkasan)

Tidak ada instalasi package baru pada tahap ini, seluruh fungsi memakai fitur bawaan Laravel (`Storage`, `DB::transaction`, `response()->streamDownload`) dan Vue/Inertia yang sudah terpasang sejak tahap-tahap sebelumnya.

---

## 13. Instruksi Eksekusi untuk Cursor AI

Kerjakan secara berurutan, konfirmasi tiap langkah selesai sebelum lanjut ke langkah berikutnya:

1. Tambahkan aturan validasi `lampiran` di `StoreArsipRequest` dan `UpdateArsipRequest` sesuai Bagian 5.1.
2. Perbarui `ArsipController@store` dan `@update` untuk menyimpan file lampiran ke storage lokal dan menghapus file lama saat diganti, sesuai Bagian 5.2.
3. Tambahkan field unggah file di `ArsipFormModal.vue` dan tautan lihat lampiran di `ArsipTable.vue` sesuai Bagian 6.
4. Buat `BackupController` baru dengan method `export()` sesuai Bagian 7.1, daftarkan route-nya sesuai Bagian 7.2.
5. Buat method `import()` di `BackupController` sesuai Bagian 8.1, bungkus dalam `DB::transaction()`, daftarkan route-nya sesuai Bagian 8.2.
6. Buat halaman `Backup/Index.vue` sesuai Bagian 9, lengkap dengan modal konfirmasi sebelum restore dijalankan.
7. Terapkan pembatasan akses fitur backup/restore hanya untuk role `admin`.
8. Uji alur penuh: unggah lampiran saat tambah arsip, unduh file backup, ubah/hapus beberapa data uji, lalu lakukan restore dari file backup tersebut dan pastikan data kembali sesuai.
9. Laporkan ringkasan file yang dibuat/diubah di akhir proses.

---

*Dokumen ini adalah PRD tahap terakhir dari rangkaian 6 tahap proyek. Setelah Tahap 6 disetujui dan diuji end-to-end, seluruh alur sistem (skema database, backend, frontend, QR Code, hingga pengelolaan file dan backup) sudah lengkap sebagai satu kesatuan aplikasi.*
