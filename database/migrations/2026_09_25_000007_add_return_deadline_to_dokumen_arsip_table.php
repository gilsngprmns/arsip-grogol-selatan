<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokumen_arsip', function (Blueprint $table) {
            $table->timestamp('batas_pengembalian')->nullable()->after('status_arsip');
        });
    }

    public function down(): void
    {
        Schema::table('dokumen_arsip', function (Blueprint $table) {
            $table->dropColumn('batas_pengembalian');
        });
    }
};