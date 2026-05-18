<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengelola_keuangans', function (Blueprint $table) {
            $table->string('keterangan')->nullable()->after('tanggal_selesai');
        });
    }

    public function down(): void
    {
        Schema::table('pengelola_keuangans', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};
