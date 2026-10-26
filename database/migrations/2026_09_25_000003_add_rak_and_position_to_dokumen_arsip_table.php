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
        Schema::table('dokumen_arsip', function (Blueprint $table) {
            $table->foreignId('rak_id')->nullable()->after('seksi_id')->constrained('rak_arsip')->nullOnDelete();
            $table->string('nomor_box', 50)->nullable()->after('rak_id');
            $table->string('nomor_map', 50)->nullable()->after('nomor_box');
            $table->string('nomor_slot', 50)->nullable()->after('nomor_map');
            $table->text('keterangan_posisi')->nullable()->after('nomor_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumen_arsip', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rak_id');
            $table->dropColumn(['nomor_box', 'nomor_map', 'nomor_slot', 'keterangan_posisi']);
        });
    }
};
