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
        Schema::create('dokumen_arsip', function (Blueprint $table) {
            $table->id();
            $table->string('kode_arsip', 50)->unique();
            $table->string('nomor_surat', 150);
            $table->string('perihal', 255);
            $table->enum('jenis_surat', ['masuk', 'keluar']);
            $table->enum('sifat_surat', ['biasa', 'penting', 'segera', 'rahasia'])->nullable()->default('biasa');
            $table->date('tanggal_surat');
            $table->date('tanggal_diterima')->nullable();
            $table->string('asal_instansi', 150)->nullable();
            $table->string('tujuan_instansi', 150)->nullable();
            $table->foreignId('seksi_id')->constrained('seksi')->cascadeOnDelete();
            $table->string('file_path', 255)->nullable();
            $table->string('qr_code_path', 255)->nullable();
            $table->enum('status_arsip', ['tersedia', 'dipinjam', 'hilang'])->default('tersedia');
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_arsip');
    }
};
