<?php

return [

    /*
    |--------------------------------------------------------------------
    | Direktori cadangan terjadwal
    |--------------------------------------------------------------------
    | Lokasi berkas ZIP hasil command arsip:cadangkan (baik manual maupun
    | terjadwal). Dipakai juga daftar "Cadangan Tersimpan" di halaman
    | /backup. Simpan di luar document root agar tak dapat diakses anonim.
    */
    'path' => env('BACKUP_PATH', storage_path('backups')),

    /*
    |--------------------------------------------------------------------
    | Retensi cadangan
    |--------------------------------------------------------------------
    | Jumlah hari cadangan yang dipertahankan; cadangan lebih tua dari
    | itu dihapus otomatis setiap kali command arsip:cadangkan berjalan.
    | Ubah lewat variable BACKUP_RETENTION_DAYS di .env bila perlu.
    */
    'retention_days' => env('BACKUP_RETENTION_DAYS', 14),
];
