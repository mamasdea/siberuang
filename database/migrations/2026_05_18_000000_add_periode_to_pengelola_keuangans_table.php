<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengelola_keuangans', function (Blueprint $table) {
            $table->year('tahun_anggaran')->nullable()->after('bidang');
            $table->date('tanggal_mulai')->nullable()->after('tahun_anggaran');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
        });
    }

    public function down(): void
    {
        Schema::table('pengelola_keuangans', function (Blueprint $table) {
            $table->dropColumn(['tahun_anggaran', 'tanggal_mulai', 'tanggal_selesai']);
        });
    }
};
