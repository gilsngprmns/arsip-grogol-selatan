# PRD — Tahap 7: Sistem Keamanan dan Hak Akses Pengguna
## Sistem Pencarian dan Pengarsipan Dokumen Surat Berbasis Web dengan Teknologi Barcode
**Studi Kasus: Kantor Lurah Grogol Selatan**

---

### Informasi Dokumen

| Item | Keterangan |
|---|---|
| Nama Proyek | Rancang Bangun Sistem Pencarian dan Pengarsipan Dokumen Surat Berbasis Web dengan Teknologi Barcode pada Kelurahan Grogol Selatan |
| Tahapan | Tahap 7 — Sistem Keamanan dan Hak Akses Pengguna (tambahan di luar 6 tahap awal) |
| Prasyarat | Tahap 1 sampai Tahap 6 sudah selesai dan sistem berjalan normal |
| Disusun oleh | Rapli — Mahasiswa Sistem Informasi, UNINDRA (Kerja Praktik) |
| Status | Draft, untuk direview sebelum lanjut ke Tahap 8 |
| Tanggal | 13 Oktober 2026 |

---

## 0. Prinsip Utama: Non-Destruktif

Seluruh pekerjaan di tahap ini dan tahap-tahap tambahan berikutnya (8-11) **wajib memeriksa kondisi yang sudah ada sebelum membuat sesuatu yang baru**, dan **tidak boleh mengubah atau merusak fitur yang sudah berjalan** dari Tahap 1-6. Setiap bagian teknis di bawah ini secara eksplisit menandai apakah suatu komponen sudah ada (tinggal diperluas) atau benar-benar baru, supaya Cursor AI tidak menduplikasi atau menimpa pekerjaan yang sudah selesai.

## 1. Latar Belakang

Sampai Tahap 6, seluruh akun yang login (lewat Breeze) punya akses CRUD penuh terhadap arsip, karena kolom `role` pada tabel `users` (ditambahkan di Tahap 3 lewat migration `add_seksi_and_role_to_users_table`) baru membedakan `admin` dan `staf`, dan belum ada middleware yang benar-benar membatasi halaman berdasarkan role tersebut. Di instansi pemerintahan, pemisahan peran ini krusial, staf tata usaha yang mencatat arsip sehari-hari berbeda kebutuhannya dengan pimpinan (lurah/kepala seksi) yang hanya perlu memantau laporan tanpa risiko tidak sengaja mengubah atau menghapus dokumen resmi.

**Penting:** kolom `role` **sudah ada** sejak Tahap 3. Tahap 7 tidak membuat kolom baru dari nol, melainkan menambah satu nilai baru (`pimpinan`) ke pilihan yang sudah ada, dan baru benar-benar menerapkan middleware yang sebelumnya belum dipasang.

## 2. Tujuan Tahap 7

1. Memperluas nilai kolom `role` yang sudah ada agar mencakup peran `pimpinan`, tanpa mengubah data akun admin/staf yang sudah tersimpan.
2. Menerapkan middleware pada rute Laravel sehingga hanya `admin` dan `staf` yang punya hak tambah/ubah/hapus arsip, sementara `pimpinan` dibatasi hanya baca (read-only).
3. Membatasi rute backup/restore (Tahap 6) agar hanya bisa diakses `admin`.
4. Menyembunyikan tombol aksi (Rekam Berkas Baru, Edit, Hapus) di komponen Vue saat yang login adalah akun `pimpinan`.

## 3. Ruang Lingkup

**Termasuk dalam Tahap 7:**
- Migration kecil untuk menambah nilai `pimpinan` ke ENUM `role` yang sudah ada
- Middleware baru untuk pembatasan akses berbasis role
- Penyesuaian `routes/web.php` agar rute ditata ulang dengan middleware per kelompok, bukan satu `Route::resource` tanpa pembedaan
- Penyesuaian komponen Vue (`Index.vue`, `ActionButtons.vue`) agar tombol aksi hanya tampil untuk role yang berhak

**Tidak termasuk (dikerjakan di tahap selanjutnya):**
- Dashboard analitik untuk pimpinan (Tahap 8)
- Filter pencarian lanjutan (Tahap 9)
- Logika tenggat peminjaman (Tahap 10)
- Modul rekam jejak/timeline (Tahap 11)

## 4. Prasyarat

- Tahap 1 sampai 6 sudah selesai dan seluruh fitur (CRUD arsip, QR Code, upload lampiran, backup/restore) berjalan normal.
- Kolom `role` sudah ada di tabel `users` dengan nilai `admin` dan `staf` (Tahap 3).
- **Sebelum memulai, periksa dulu:** isi `database/migrations/` untuk memastikan migration kolom `role` memang sudah ada dan belum ada migration lain yang mengubah ENUM ini sebelumnya, serta periksa `app/Http/Kernel.php` (Laravel 10 ke bawah) atau `bootstrap/app.php` (Laravel 11 ke atas) untuk melihat middleware apa saja yang sudah terdaftar, supaya tidak membuat alias middleware yang bentrok nama.

## 5. Perluasan Kolom Role (Bukan Pembuatan Baru)

### 5.1 Migration `add_pimpinan_role_to_users_table`

```php
public function up(): void
{
    DB::statement("ALTER TABLE users MODIFY role ENUM('admin','staf','pimpinan') NOT NULL DEFAULT 'staf'");
}

public function down(): void
{
    DB::statement("ALTER TABLE users MODIFY role ENUM('admin','staf') NOT NULL DEFAULT 'staf'");
}
```

Migration ini hanya menambah satu nilai baru ke ENUM yang sudah ada, data `role` akun admin/staf yang sudah tersimpan tidak tersentuh sama sekali.

### 5.2 Catatan soal struktur role

Struktur role yang berlaku setelah Tahap 7:

| Role | Hak Akses |
|---|---|
| `admin` | Akses penuh, termasuk backup/restore (Tahap 6) |
| `staf` | Hak penuh tambah dan ubah data arsip (setara "staf tata usaha" pada deskripsi tahap ini) |
| `pimpinan` | Hanya baca (read-only): lihat daftar arsip dan laporan, tidak bisa tambah/ubah/hapus |

## 6. Middleware Pembatasan Akses

### 6.1 Buat middleware baru (komponen baru, belum ada di tahap manapun)

```bash
php artisan make:middleware EnsureUserHasRole
```

```php
// app/Http/Middleware/EnsureUserHasRole.php
public function handle(Request $request, Closure $next, string ...$roles)
{
    if (!$request->user() || !in_array($request->user()->role, $roles, true)) {
        abort(403, 'Anda tidak memiliki akses untuk tindakan ini.');
    }

    return $next($request);
}
```

### 6.2 Registrasi alias middleware

**Periksa dulu struktur project** (Laravel 11+ memakai `bootstrap/app.php`, versi lebih lama memakai `app/Http/Kernel.php`) sebelum menambahkan alias berikut, agar tidak membuat pendaftaran ganda:

Untuk Laravel 11 ke atas (`bootstrap/app.php`):
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\EnsureUserHasRole::class,
    ]);
})
```

Untuk Laravel 10 ke bawah (`app/Http/Kernel.php`), tambahkan ke array `$middlewareAliases` yang sudah ada (jangan menimpa alias lain yang sudah terdaftar):
```php
'role' => \App\Http\Middleware\EnsureUserHasRole::class,
```

## 7. Penataan Ulang Routing (`routes/web.php`)

Rute `arsip` di Tahap 3 memakai satu `Route::resource` di bawah middleware `auth` saja. Rute ini perlu dipecah agar middleware `role` bisa diterapkan berbeda per aksi, tanpa mengubah nama route (`arsip.index`, `arsip.store`, dst.) supaya pemanggilan `route('arsip.store')` dkk di frontend (Tahap 4-6) tetap berfungsi tanpa perubahan.

**Sebelum (Tahap 3-6, jangan dihapus dulu sampai penggantian di bawah diuji berhasil):**
```php
Route::middleware(['auth'])->group(function () {
    Route::resource('arsip', ArsipController::class);
});
```

**Sesudah (Tahap 7):**
```php
Route::middleware(['auth'])->group(function () {
    // Bisa diakses semua role yang login, termasuk pimpinan (read-only)
    Route::get('/arsip', [ArsipController::class, 'index'])->name('arsip.index');
    Route::get('/arsip/{arsip}', [ArsipController::class, 'show'])->name('arsip.show');

    // Hanya admin dan staf
    Route::middleware(['role:admin,staf'])->group(function () {
        Route::get('/arsip/create', [ArsipController::class, 'create'])->name('arsip.create');
        Route::post('/arsip', [ArsipController::class, 'store'])->name('arsip.store');
        Route::get('/arsip/{arsip}/edit', [ArsipController::class, 'edit'])->name('arsip.edit');
        Route::put('/arsip/{arsip}', [ArsipController::class, 'update'])->name('arsip.update');
        Route::delete('/arsip/{arsip}', [ArsipController::class, 'destroy'])->name('arsip.destroy');
    });

    // Backup/restore (Tahap 6), sekarang dibatasi khusus admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/backup/export', [BackupController::class, 'export'])->name('backup.export');
        Route::post('/backup/import', [BackupController::class, 'import'])->name('backup.import');
    });

    // Pemindaian QR (Tahap 5) tetap untuk admin dan staf, bukan untuk pimpinan
    Route::middleware(['role:admin,staf'])->get('/api/arsip/scan/{kode_arsip}', [ArsipController::class, 'scanByKode']);
});
```

**Catatan penting:** rute `arsip.show` perlu ditambahkan terlebih dahulu di `ArsipController` jika memang belum ada (periksa dulu, karena Tahap 3 tidak mewajibkan method `show`, hanya `index`, `create`, `store`, `edit`, `update`, `destroy`). Jika `show()` belum ada, buat versi sederhana yang mengirim satu record arsip ke halaman detail atau dipakai bersama modal detail dari Tahap 11.

## 8. Penyesuaian Frontend

### 8.1 Sumber data role di sisi Vue

Breeze sudah membagikan data user yang login lewat shared props Inertia (`$page.props.auth.user`), tidak perlu middleware atau perubahan baru di `HandleInertiaRequests.php` untuk ini, cukup dipakai langsung di komponen.

### 8.2 `resources/js/Pages/Arsip/Index.vue`

**Sebelum:**
```vue
<button @click="showModal = true">Tambah Arsip</button>
```

**Sesudah:**
```vue
<script setup>
import { usePage } from '@inertiajs/vue3';
const page = usePage();
const isPimpinan = computed(() => page.props.auth.user.role === 'pimpinan');
</script>

<template>
  <button v-if="!isPimpinan" @click="showModal = true">Tambah Arsip</button>
</template>
```

### 8.3 `resources/js/Components/ActionButtons.vue`

**Sebelum:**
```vue
<button @click="$emit('edit', arsip)">Edit</button>
<button @click="$emit('delete', arsip)">Hapus</button>
```

**Sesudah:**
```vue
<script setup>
import { usePage, computed } from '@inertiajs/vue3';
const page = usePage();
const isPimpinan = computed(() => page.props.auth.user.role === 'pimpinan');
</script>

<template>
  <button v-if="!isPimpinan" @click="$emit('edit', arsip)">Edit</button>
  <button v-if="!isPimpinan" @click="$emit('delete', arsip)">Hapus</button>
  <!-- Tombol cetak label (Tahap 5) tetap tampil untuk semua role, karena mencetak bukan aksi mengubah data -->
</template>
```

Penyembunyian tombol ini murni soal pengalaman pengguna, bukan pengganti middleware di Bagian 6-7. Middleware di backend tetap jadi lapisan pertahanan utama.

## 9. Kriteria Selesai (Definition of Done) Tahap 7

- [ ] Nilai `pimpinan` berhasil ditambahkan ke ENUM `role`, data akun admin/staf yang sudah ada tidak berubah
- [ ] Middleware `role` berhasil dibuat dan terdaftar tanpa bentrok dengan alias middleware lain yang sudah ada
- [ ] Akun `pimpinan` bisa mengakses `arsip.index` dan `arsip.show`, tapi mendapat 403 saat mencoba mengakses `arsip.store`/`update`/`destroy` langsung lewat URL atau Postman (bukan hanya disembunyikan di UI)
- [ ] Rute backup/restore (Tahap 6) kini menolak akses dari role selain `admin`
- [ ] Tombol "Tambah Arsip", "Edit", dan "Hapus" otomatis tersembunyi saat login sebagai `pimpinan`
- [ ] Seluruh fitur Tahap 1-6 (CRUD arsip, QR Code, upload lampiran, backup/restore) tetap berfungsi normal untuk role `admin` dan `staf` setelah middleware diterapkan, diuji ulang satu per satu

## 10. Catatan Teknis Tambahan

- Jangan hapus route resource lama di Tahap 3 sebelum penggantian di Bagian 7 sudah diuji berhasil, supaya ada jalur rollback cepat jika terjadi kesalahan.
- Pertimbangkan membuat akun uji dengan role `pimpinan` lewat `php artisan tinker` atau seeder khusus untuk pengujian, jangan mengubah role akun admin/staf yang sudah dipakai untuk demo.
- Nama middleware alias `role` perlu dipastikan belum dipakai oleh package lain yang mungkin sudah terpasang di project (misalnya Spatie Permission), periksa `composer.json` terlebih dahulu.

## 11. Referensi Command (Ringkasan)

```bash
php artisan make:migration add_pimpinan_role_to_users_table
php artisan make:middleware EnsureUserHasRole
php artisan migrate
```

---

## 12. Instruksi Eksekusi untuk Cursor AI

Kerjakan secara berurutan. **Sebelum tiap langkah, periksa dulu apakah komponen terkait sudah ada** (migration, middleware, atau kode yang disebutkan), dan jangan menimpa fungsi yang sudah berjalan kecuali memang bagian dari penggantian yang diminta di langkah tersebut:

1. Periksa `database/migrations/` untuk memastikan migration kolom `role` (Tahap 3) memang sudah ada. Jika sudah, buat migration baru `add_pimpinan_role_to_users_table` sesuai Bagian 5.1, jangan mengedit file migration Tahap 3 yang lama.
2. Jalankan `php artisan migrate` dan laporkan hasilnya.
3. Periksa apakah ada middleware role serupa yang sudah ada. Jika belum, buat `EnsureUserHasRole` sesuai Bagian 6.1.
4. Periksa apakah `bootstrap/app.php` atau `app/Http/Kernel.php` yang dipakai project ini (cek versi Laravel), lalu daftarkan alias `role` sesuai Bagian 6.2 tanpa menghapus alias middleware lain yang sudah ada.
5. Periksa apakah `ArsipController` sudah punya method `show()`. Jika belum, tambahkan versi sederhana sebelum melanjutkan ke langkah berikutnya.
6. Ganti struktur `routes/web.php` sesuai Bagian 7, pastikan nama route (`arsip.index`, `arsip.store`, dst.) tetap sama persis seperti sebelumnya agar tidak merusak pemanggilan `route(...)` di frontend yang sudah ada.
7. Perbarui `Index.vue` dan `ActionButtons.vue` sesuai Bagian 8, dengan pendekatan before/after yang ditunjukkan, tanpa mengubah bagian lain dari komponen tersebut.
8. Uji ulang seluruh fitur Tahap 1-6 dengan akun `admin` dan `staf` untuk memastikan tidak ada yang rusak akibat penataan ulang middleware/route.
9. Buat satu akun uji dengan role `pimpinan`, uji akses read-only-nya sesuai kriteria di Bagian 9.
10. Laporkan ringkasan file yang dibuat/diubah di akhir proses, termasuk konfirmasi bahwa tidak ada migration atau route lama yang terhapus secara tidak sengaja.

---

*Dokumen ini adalah PRD tahap tambahan (di luar 6 tahap awal). Setelah Tahap 7 disetujui, lanjutkan ke Tahap 8 — Dashboard Analitik Kepemimpinan.*
