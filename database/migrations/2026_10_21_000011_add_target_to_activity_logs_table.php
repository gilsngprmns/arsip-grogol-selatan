<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah objek target (mis. dokumen, user) pada baris log aktivitas.
 * Kolom nullable — baris lama tetap valid dan menampilkan "—" di UI.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('target_type')->nullable()->after('new_values');
            $table->unsignedBigInteger('target_id')->nullable()->after('target_type');
            $table->string('target_label')->nullable()->after('target_id');
            $table->index(['target_type', 'target_id'], 'activity_logs_target_index');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_target_index');
            $table->dropColumn(['target_type', 'target_id', 'target_label']);
        });
    }
};
