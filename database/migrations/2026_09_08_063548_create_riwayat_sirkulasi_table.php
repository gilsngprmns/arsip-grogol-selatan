<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('riwayat_sirkulasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_arsip_id')->constrained('dokumen_arsip')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('jenis_aksi', ['dibuat', 'dipindai', 'dipinjam', 'dikembalikan', 'diarsipkan', 'ditemukan']);
            $table->string('pihak_terkait', 150)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamp('waktu_aksi')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_sirkulasi');
    }
};
