# cloudflared — akses app via tunnel publik
Status: BERJALAN (setup) · Service: ops · Diperbarui: 2026-10-19

## Sudah terbukti (angka nyata)
- `cloudflared` terinstall via `winget install Cloudflare.cloudflared` v2026.10.1.
- Lokasi biner (hasil `find` di sesi ini): `C:\Program Files (x86)\cloudflared\cloudflared.exe`
- PATH system sudah memuat `C:\Program Files (x86)\cloudflared\` (winget menambahkan otomatis),
  tapi shell/terminal lama tak melihatnya — buka terminal baru atau `refreshenv` dulu.
- Versi terverifikasi: `cloudflared version` → 2026.10.1.
- Quick tunnel dijalankan (`cloudflared tunnel --url http://127.0.0.1:80`):
  - URL sementara: `https://alarm-superintendent-discretion-above.trycloudflare.com`
  - Precheck Cloudflare: UDP QUIC PASS, TCP HTTP/2 PASS, region `sin21`, registered OK.
  - URL sudah di-DNS-register (`2606:4700::6810:e784`) saat proses `cloudflared` hidup.
  - Proses `cloudflared` telah di-kill (PID 9636) setelah ujicoba, jadi URL di atas kini mati.

## Karakter yang WAJIB diingat
- **Quick tunnel bersifat ephemeral** — URL `trycloudflare.com` BERUBAH tiap `cloudflared` di-restart;
  URL lama (`employers-collected-dollars-engaged.trycloudflare.com`) hilang permanen begitu tunnel mati.
- URL hanya hidup selama proses `cloudflared` berjalan; tutup terminal / Ctrl+C = akses publik hilang.
- Target = port `80` (Apache Laragon, `httpd.exe` PID 2292) yang melayani app Laravel.
  Jangan arahkan ke port `5173` (Vite dev) untuk akses publik.

## Langkah menjalankan (copy-paste di PowerShell)
```powershell
# (opsional, kalau PATH lama)  refreshenv
cloudflared tunnel --url http://127.0.0.1:80
# tunggu ~10s, salin URL trycloudflare.com yang tercetak, buka di browser.
#
# WAJIB — sinkronkan .env ke URL baru (URL cepat tunnel berubah tiap restart):
#   buka  .env  →  baris  APP_URL=https://<url-baru.trycloudflare.com>
#   (atau PowerShell):  (Get-Content .env) -replace 'APP_URL=.*','APP_URL=https://<url-baru.trycloudflare.com>' | Set-Content .env
#   lalu jalankan lagi:  php artisan config:clear
#   (APP_URL yang basi membuat link absolut di app — halaman, mail, QR — menunjuk domain mati.)
```

## Peringatan .env
- `.env` baris 5 saat ini masih `APP_URL=https://employers-collected-dollars-engaged.trycloudflare.com`
  — URL ini **sudah mati** (tunnel lama tak berjalan lagi, sudah di-kill).
- Setiap restart `cloudflared` di atas **wajib** sinkronkan `APP_URL` + `php artisan config:clear`.
- Kalau butuh URL **tetap** tanpa sync manual tiap kali → naikkan ke named tunnel
  (`cloudflared tunnel login` + `cloudflared tunnel create <nama>`) dengan akun Cloudflare user;
  `APP_URL` di-set sekali ke URL named tunnel itu. Keputusan naik-tidak milik user.

## Langkah berikutnya
- [ ] Jika user butuh URL TETAP (tidak berganti saat restart) → naikkan ke named tunnel
      (`cloudflared tunnel login` + `cloudflared tunnel create <nama>`) dengan akun Cloudflare user.
      Keputusan naik-tidak milik user; quick tunnel cukup untuk test/demo sementara.

## Jangan lakukan
- Jangan arahkan tunnel ke port 5173 (Vite dev) — gunakan port 80 (Apache).
- Jangan tutup terminal yang menjalankan `cloudflared` selama butuh akses publik.
