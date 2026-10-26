<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan 'ditemukan' ke enum jenis_aksi untuk mendukung aksi "Tandai Tersedia"
        Schema::table('riwayat_sirkulasi', function ($table) {
            $table->string('jenis_aksi', 50)->nullable()->default(null)->change();
        });

        // Backfill: set default jika ada NULL
        DB::table('riwayat_sirkulasi')->whereNull('jenis_aksi')->update(['jenis_aksi' => 'dibuat']);

        Schema::table('riwayat_sirkulasi', function ($table) {
            $table->enum('jenis_aksi', ['dibuat', 'dipindai', 'dipinjam', 'dikembalikan', 'diarsipkan', 'ditemukan'])->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_sirkulasi', function ($table) {
            $table->enum('jenis_aksi', ['dibuat', 'dipindai', 'dipinjam', 'dikembalikan', 'diarsipkan'])->change();
        });
    }
};
