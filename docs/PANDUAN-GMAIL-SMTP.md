# Panduan Setup Gmail SMTP untuk Arsip Dokumen Grogol Selatan

Panduan ini membantu Anda mengatur pengiriman email melalui Gmail pada aplikasi Arsip Dokumen Grogol Selatan. Cocok untuk pemula yang baru pertama kali melakukan konfigurasi SMTP.

---

## Prasyarat

- Akun Gmail yang sudah ada
- Akses ke pengaturan akun Google
- Aplikasi sudah terinstall di server lokal/production

---

## Langkah 1: Aktifkan Two-Factor Authentication (2FA) di Akun Google

1. Buka [myaccount.google.com](https://myaccount.google.com/)
2. Pilih menu **Security** (Keamanan) di panel kiri
3. Di bagian "How you sign in to Google", klik **2-Step Verification**
4. Klik **Get started**
5. Ikuti petunjuk untuk mengaktifkan 2FA (bisa pakai SMS, Google Authenticator, atau Phone call)
6. Selesaikan verifikasi hingga muncul centang hijau ✅

> **Penting:** App Password hanya bisa dibuat jika 2FA sudah aktif.

---

## Langkah 2: Buat App Password

1. Masih di halaman [myaccount.google.com](https://myaccount.google.com/)
2. Di kolom pencarian di bagian atas, ketik **App Passwords** lalu tekan Enter
3. Atau buka langsung: [https://myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
4. Di bagian **Select app**, pilih **Mail** dari dropdown
5. Di bagian **Select device**, pilih **Other (Custom name)**
6. Ketik nama perangkat, misalnya: `Arsip Grogol`
7. Klik **Generate**
8. Google akan menampilkan **App Password** berupa 16 karakter (contoh: `abcd efgh ijkl mnop`)
9. **Salin password tersebut** — Anda hanya akan melihatnya sekali!

> ⚠️ **Jangan bagikan App Password ini kepada siapapun.** Treat seperti password biasa.

---

## Langkah 3: Edit File `.env`

1. Buka proyek di text editor (VS Code, dll)
2. Buka file `.env` di root folder project
3. Cari bagian konfigurasi mail (sekitar baris 30-40)
4. Isi dengan data berikut:

```env
MAIL_MAILER=smtp
MAIL_ENCRYPT=tls
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=alamatgmailAnda@gmail.com
MAIL_PASSWORD=abcdefghijkllmnop   # ← ganti dengan App Password 16 karakter
MAIL_FROM_ADDRESS=alamatgmailAnda@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

> **Catatan:**
> - `MAIL_USERNAME` = alamat Gmail lengkap (contoh: `siregar@gmail.com`)
> - `MAIL_PASSWORD` = App Password 16 karakter (tanpa spasi, contoh: `abcd efgh ijkl mnop` → `abcdefghijklmnop`)
> - `MAIL_FROM_ADDRESS` = sama dengan `MAIL_USERNAME`

---

## Langkah 4: Clear Cache Configuration

Buka terminal di folder project, jalankan:

```bash
php artisan config:clear
php artisan cache:clear
```

Ini memastikan Laravel membaca ulang konfigurasi baru dari `.env`.

---

## Langkah 5: Test Pengiriman Email

### Cara cepat via tinker:

```bash
php artisan tinker
```

Lalu jalankan perintah ini di tinker:

```php
\Mail::raw('Test dari Arsip Grogol Selatan', function($message) {
    $message->to('email Tujuan@gmail.com')
            ->subject('Test Email');
});
```

Ganti `email Tujuan@gmail.com` dengan alamat email asli Anda untuk menerima test.

### Cara alternatif via command:

```bash
php artisan mail:test --email=alamat@email.com
```

---

## Langkah 6: Verifikasi

Cek apakah email test berhasil masuk ke inbox (atau folder spam).

Jika berhasil, konfigurasi SMTP Gmail Anda sudah siap digunakan! 🎉

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `Connection refused` | Pastikan port 587 tidak diblokir firewall |
| `Authentication failed` | Cek ulang App Password dan pastikan 2FA sudah aktif |
| `SMTP server does not support authentication` | Pastikan `MAIL_USERNAME` dan `MAIL_PASSWORD` terisi |
| Email masuk ke spam | Tambahkan domain Anda ke contact di Gmail tujuan |
| `Invalid credentials` | Buat ulang App Password, mungkin salah salin |

---

## Catatan Keamanan

- **Jangan commit file `.env` ke Git** — file ini sudah ada di `.gitignore`
- App Password berbeda dengan password Gmail biasa
- Jika curiga bocor, segera hapus App Password di [Security → Apps with account access](https://myaccount.google.com/permissions)
- Gunakan email Gmail khusus untuk aplikasi (jangan email pribadi utama)

---

*Dibuat untuk proyek Arsip Dokumen Grogol Selatan*
