<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Cadangan harian basis data arsip
|--------------------------------------------------------------------------
| Menulis bundel ZIP lengkap (data + referensi + file fisik) ke
| storage/backups lalu menerapkan retensi (lihat config/backup.php).
| Di host Windows, jalankan Task Scheduler "php artisan schedule:run"
| tiap 15 menit agar jadwal ini tereksekusi (PC harus menyala di jam itu).
*/
Schedule::command('arsip:cadangkan')->dailyAt('02:00');
