<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan 'dihilangkan' ke enum jenis_aksi untuk mendukung aksi "Tandai Hilang"
        Schema::table('riwayat_sirkulasi', function ($table) {
            $table->string('jenis_aksi', 50)->nullable()->default(null)->change();
        });

        Schema::table('riwayat_sirkulasi', function ($table) {
            $table->enum('jenis_aksi', ['dibuat', 'dipindai', 'dipinjam', 'dikembalikan', 'diarsipkan', 'ditemukan', 'dihilangkan'])->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_sirkulasi', function ($table) {
            $table->enum('jenis_aksi', ['dibuat', 'dipindai', 'dipinjam', 'dikembalikan', 'diarsipkan', 'ditemukan'])->change();
        });
    }
};
