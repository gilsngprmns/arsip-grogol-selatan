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
        Schema::create('lokasi_arsip', function (Blueprint $table) {
            $table->id();
            $table->string('kode_lokasi', 50)->unique();
            $table->string('nama_lokasi', 150);
            $table->string('gedung', 150)->nullable();
            $table->string('lantai', 50)->nullable();
            $table->string('ruangan', 100)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokasi_arsip');
    }
};
