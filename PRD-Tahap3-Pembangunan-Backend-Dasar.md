# PRD — Tahap 3: Pembangunan Backend Dasar
## Sistem Pencarian dan Pengarsipan Dokumen Surat Berbasis Web dengan Teknologi Barcode
**Studi Kasus: Kantor Lurah Grogol Selatan**

---

### Informasi Dokumen

| Item | Keterangan |
|---|---|
| Nama Proyek | Rancang Bangun Sistem Pencarian dan Pengarsipan Dokumen Surat Berbasis Web dengan Teknologi Barcode pada Kelurahan Grogol Selatan |
| Tahapan | Tahap 3 dari 6 — Pembangunan Backend Dasar |
| Prasyarat | Tahap 1 (Pemantapan Skema Database) dan Tahap 2 (Persiapan Tumpukan Teknologi) sudah disetujui |
| Disusun oleh | Rapli — Mahasiswa Sistem Informasi, UNINDRA (Kerja Praktik) |
| Status | Draft, untuk direview sebelum lanjut ke Tahap 4 |
| Tanggal | 8 September 2026 |

---

## 1. Latar Belakang

ERD final sudah digambar ulang di draw.io (notasi Chen) dan divalidasi ulang dalam bentuk diagram skema (eraser.io), mencakup empat tabel: `seksi`, `users`, `dokumen_arsip`, dan `riwayat_sirkulasi`. Tahap 3 menerjemahkan skema yang sudah difinalkan tersebut menjadi kode Laravel yang berjalan nyata, mulai dari migration, seeder, model beserta relasinya, hingga controller CRUD dasar untuk dokumen arsip.

Karena skema di Tahap 1 sudah final (termasuk keputusan memisahkan `kode_arsip` dari `nomor_surat`, penggunaan ENUM untuk nilai terbatas, dan soft delete pada `dokumen_arsip`), penulisan migration di tahap ini harus mengikuti struktur tersebut persis, bukan diinterpretasikan ulang, agar relasi yang dipakai controller dan frontend di tahap-tahap berikutnya tidak perlu diubah lagi.

## 2. Tujuan Tahap 3

1. Menerjemahkan skema final Tahap 1 menjadi file migration Laravel yang urut sesuai dependensi foreign key.
2. Menjalankan seeder data awal untuk tabel `seksi`.
3. Membangun model Eloquent untuk `Seksi`, `User`, `DokumenArsip`, dan `RiwayatSirkulasi` beserta relasi antar-model sesuai ERD.
4. Membangun `ArsipController` dengan alur CRUD dasar (create, read, update, delete) yang datanya dialirkan ke halaman frontend melalui Inertia.

## 3. Ruang Lingkup

**Termasuk dalam Tahap 3:**
- Migration untuk tabel `seksi`, penambahan kolom pada tabel `users` bawaan Breeze, tabel `dokumen_arsip`, dan tabel `riwayat_sirkulasi`
- Seeder `SeksiSeeder` dengan tiga data seksi
- Model Eloquent beserta relasi (`hasMany`, `belongsTo`) sesuai ERD
- `ArsipController` dengan method resource lengkap
- Form Request untuk validasi input
- Route resource `arsip` dengan middleware `auth`

**Tidak termasuk (dikerjakan di tahap selanjutnya):**
- Komponen dan tampilan Vue final, termasuk sidebar dan modal (Tahap 4)
- Generate dan pemindaian QR Code (Tahap 5)
- Upload file lampiran, storage link, serta export/import backup JSON (Tahap 6)

## 4. Prasyarat

- Tahap 1 dan Tahap 2 sudah selesai dan disetujui.
- Project Laravel dengan Breeze (stack Vue + Inertia) sudah terpasang, `npm run dev` berjalan tanpa error.
- Koneksi ke database MySQL lokal sudah dikonfigurasi di `.env` dan teruji lewat `php artisan migrate` bawaan Laravel.

## 5. Skema Migration (Sesuai ERD Final)

Urutan pembuatan file migration wajib mengikuti dependensi foreign key di bawah ini.

### 5.1 `create_seksi_table` (dijalankan pertama, tanpa foreign key)

| Kolom | Tipe | Constraint |
|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT |
| nama_seksi | VARCHAR(100) | NOT NULL, UNIQUE |
| keterangan | TEXT | NULLABLE |
| created_at / updated_at | TIMESTAMP | NULLABLE |

### 5.2 `add_seksi_and_role_to_users_table` (migration alter, karena tabel `users` sudah dibuat Breeze di Tahap 2)

| Kolom | Tipe | Constraint |
|---|---|---|
| seksi_id | BIGINT UNSIGNED | FK → seksi.id, NULLABLE |
| role | ENUM('admin','staf') | NOT NULL, DEFAULT 'staf' |

### 5.3 `create_dokumen_arsip_table` (dijalankan setelah `seksi` dan `users`)

| Kolom | Tipe | Constraint |
|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT |
| kode_arsip | VARCHAR(50) | NOT NULL, UNIQUE |
| nomor_surat | VARCHAR(150) | NOT NULL |
| perihal | VARCHAR(255) | NOT NULL |
| jenis_surat | ENUM('masuk','keluar') | NOT NULL |
| sifat_surat | ENUM('biasa','penting','segera','rahasia') | NULLABLE, DEFAULT 'biasa' |
| tanggal_surat | DATE | NOT NULL |
| tanggal_diterima | DATE | NULLABLE |
| asal_instansi | VARCHAR(150) | NULLABLE |
| tujuan_instansi | VARCHAR(150) | NULLABLE |
| seksi_id | BIGINT UNSIGNED | FK → seksi.id, NOT NULL |
| file_path | VARCHAR(255) | NULLABLE |
| qr_code_path | VARCHAR(255) | NULLABLE |
| status_arsip | ENUM('tersedia','dipinjam','hilang') | NOT NULL, DEFAULT 'tersedia' |
| dicatat_oleh | BIGINT UNSIGNED | FK → users.id, NULLABLE |
| catatan | TEXT | NULLABLE |
| created_at / updated_at | TIMESTAMP | NULLABLE |
| deleted_at | TIMESTAMP | NULLABLE (soft delete) |

### 5.4 `create_riwayat_sirkulasi_table` (dijalankan terakhir)

| Kolom | Tipe | Constraint |
|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT |
| dokumen_arsip_id | BIGINT UNSIGNED | FK → dokumen_arsip.id, NOT NULL |
| user_id | BIGINT UNSIGNED | FK → users.id, NOT NULL |
| jenis_aksi | ENUM('dibuat','dipindai','dipinjam','dikembalikan','diarsipkan') | NOT NULL |
| pihak_terkait | VARCHAR(150) | NULLABLE |
| keterangan | TEXT | NULLABLE |
| waktu_aksi | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP |
| created_at / updated_at | TIMESTAMP | NULLABLE |

### 5.5 Seeder `SeksiSeeder`

```php
DB::table('seksi')->insert([
    ['nama_seksi' => 'Pemerintahan', 'keterangan' => 'Mengurus administrasi pemerintahan umum'],
    ['nama_seksi' => 'Ekonomi Pembangunan', 'keterangan' => 'Mengurus urusan ekonomi dan pembangunan wilayah'],
    ['nama_seksi' => 'Kesejahteraan Rakyat', 'keterangan' => 'Mengurus urusan kesejahteraan sosial masyarakat'],
]);
```

Daftarkan `SeksiSeeder` di `DatabaseSeeder`, lalu jalankan `php artisan db:seed`.

## 6. Model Eloquent & Relasi

### 6.1 `Seksi`
- `hasMany(User::class)`
- `hasMany(DokumenArsip::class)`

### 6.2 `User` (extend model bawaan Breeze)
- `belongsTo(Seksi::class)`
- `hasMany(DokumenArsip::class, 'dicatat_oleh')`
- `hasMany(RiwayatSirkulasi::class)`

### 6.3 `DokumenArsip`
- `use SoftDeletes;` (karena kolom `deleted_at` ada di migration)
- `belongsTo(Seksi::class)`
- `belongsTo(User::class, 'dicatat_oleh')`
- `hasMany(RiwayatSirkulasi::class)`
- `$casts`: `tanggal_surat` => `date`, `tanggal_diterima` => `date`
- `$fillable`: seluruh kolom kecuali `id`, `created_at`, `updated_at`, `deleted_at`

### 6.4 `RiwayatSirkulasi`
- `belongsTo(DokumenArsip::class)`
- `belongsTo(User::class)`
- `$casts`: `waktu_aksi` => `datetime`

## 7. Controller & Routing

### 7.1 `ArsipController` (resource controller)

| Method | Tugas |
|---|---|
| `index()` | Ambil `DokumenArsip::with('seksi')->paginate(...)`, kirim ke Inertia page `Arsip/Index` sebagai prop `arsip` |
| `create()` | Kirim daftar `seksi` sebagai prop `seksiList` ke Inertia page `Arsip/Create` |
| `store(StoreArsipRequest $request)` | Validasi, isi `dicatat_oleh` dari `auth()->id()`, simpan record, redirect ke `index` dengan flash message |
| `edit(DokumenArsip $arsip)` | Kirim data arsip dan daftar seksi ke Inertia page `Arsip/Edit` |
| `update(UpdateArsipRequest $request, DokumenArsip $arsip)` | Validasi, update record, redirect ke `index` |
| `destroy(DokumenArsip $arsip)` | Soft delete record (`$arsip->delete()`), redirect ke `index` dengan flash message |

### 7.2 Validasi (`StoreArsipRequest` dan `UpdateArsipRequest`)

Aturan minimal:
- `kode_arsip`: required, string, max:50, unique:dokumen_arsip,kode_arsip (kecualikan record sendiri saat update)
- `nomor_surat`: required, string, max:150
- `perihal`: required, string, max:255
- `jenis_surat`: required, in:masuk,keluar
- `sifat_surat`: nullable, in:biasa,penting,segera,rahasia
- `tanggal_surat`: required, date
- `tanggal_diterima`: nullable, date
- `asal_instansi`: nullable, string, max:150 (relevan untuk `jenis_surat = masuk`)
- `tujuan_instansi`: nullable, string, max:150 (relevan untuk `jenis_surat = keluar`)
- `seksi_id`: required, exists:seksi,id

### 7.3 Routing (`routes/web.php`)

```php
Route::middleware(['auth'])->group(function () {
    Route::resource('arsip', ArsipController::class);
});
```

## 8. Kriteria Selesai (Definition of Done) Tahap 3

- [ ] `php artisan migrate` berjalan tanpa error, urutan tabel sesuai dependensi foreign key di Bagian 5
- [ ] `php artisan db:seed` mengisi tiga baris data seksi sesuai Tahap 1
- [ ] Model `Seksi`, `User`, `DokumenArsip`, `RiwayatSirkulasi` beserta relasinya berfungsi (diuji lewat `php artisan tinker`)
- [ ] Soft delete pada `DokumenArsip` berfungsi, data yang dihapus tidak muncul di `index()` tapi masih ada di database
- [ ] Seluruh endpoint CRUD `arsip` dapat diakses dan mengembalikan response Inertia (bukan JSON API biasa)
- [ ] Validasi berjalan sesuai Bagian 7.2, pesan error tervalidasi tampil dengan benar
- [ ] Tidak ada query N+1 pada `index()` (gunakan eager loading `with('seksi')`)

## 9. Catatan Teknis Tambahan

- `kode_arsip` dan `nomor_surat` sengaja dipisah sesuai keputusan Tahap 1, jangan digabung jadi satu kolom saat menulis migration.
- Kolom `asal_instansi` dan `tujuan_instansi` bersifat kondisional tergantung `jenis_surat`, validasi dasar cukup di level `nullable` untuk tahap ini, aturan kondisional lebih ketat bisa ditambahkan belakangan jika diperlukan.
- Gunakan `Route::resource` daripada mendefinisikan route satu per satu.
- Jangan implementasikan logika QR Code, upload file, maupun export/import pada tahap ini, itu bagian Tahap 5 dan 6.

## 10. Referensi Command (Ringkasan)

```bash
php artisan make:migration create_seksi_table
php artisan make:migration add_seksi_and_role_to_users_table --table=users
php artisan make:migration create_dokumen_arsip_table
php artisan make:migration create_riwayat_sirkulasi_table
php artisan migrate
php artisan make:seeder SeksiSeeder
php artisan db:seed
php artisan make:model Seksi
php artisan make:model DokumenArsip
php artisan make:model RiwayatSirkulasi
php artisan make:controller ArsipController --resource
php artisan make:request StoreArsipRequest
php artisan make:request UpdateArsipRequest
```

---

## 11. Instruksi Eksekusi untuk Cursor AI

Kerjakan secara berurutan, konfirmasi tiap langkah selesai sebelum lanjut ke langkah berikutnya:

1. Buat file migration `create_seksi_table`, `add_seksi_and_role_to_users_table`, `create_dokumen_arsip_table`, `create_riwayat_sirkulasi_table` persis sesuai skema kolom di Bagian 5, dengan urutan timestamp migration yang benar agar foreign key tidak gagal.
2. Jalankan `php artisan migrate` dan laporkan hasilnya.
3. Buat `SeksiSeeder` sesuai Bagian 5.5, daftarkan di `DatabaseSeeder`, jalankan `php artisan db:seed`.
4. Buat model `Seksi`, `DokumenArsip`, `RiwayatSirkulasi`, serta perbarui model `User` bawaan Breeze, dengan relasi sesuai Bagian 6. Tambahkan trait `SoftDeletes` pada `DokumenArsip`.
5. Buat `StoreArsipRequest` dan `UpdateArsipRequest` sesuai Bagian 7.2.
6. Buat `ArsipController` dengan seluruh method resource sesuai Bagian 7.1, gunakan `Inertia::render()` untuk setiap halaman.
7. Daftarkan route resource `arsip` di `routes/web.php` dengan middleware `auth`.
8. Uji seluruh alur CRUD melalui Tinker atau route sementara, pastikan data tersimpan, relasi termuat dengan benar, dan soft delete berfungsi.
9. Laporkan ringkasan file yang dibuat/diubah di akhir proses.

---

*Dokumen ini adalah bagian dari rangkaian PRD 6 tahap. Setelah Tahap 3 disetujui, lanjutkan ke Tahap 4 — Perakitan Frontend dengan Vue.*
