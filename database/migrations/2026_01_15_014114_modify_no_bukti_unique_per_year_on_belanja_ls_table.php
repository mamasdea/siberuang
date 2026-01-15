<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Mengubah constraint unique no_bukti menjadi unique per tahun.
     * Sehingga no_bukti '0001' di tahun 2025 bisa sama dengan '0001' di tahun 2026.
     */
    public function up(): void
    {
        // Hapus unique constraint yang lama pada no_bukti
        Schema::table('belanja_ls', function (Blueprint $table) {
            $table->dropUnique(['no_bukti']);
        });
        
        // Tambahkan kolom tahun_bukti sebagai generated/virtual column
        DB::statement('ALTER TABLE belanja_ls ADD COLUMN tahun_bukti INT GENERATED ALWAYS AS (YEAR(tanggal)) STORED AFTER tanggal');
        
        // Buat composite unique index untuk no_bukti + tahun_bukti
        Schema::table('belanja_ls', function (Blueprint $table) {
            $table->unique(['no_bukti', 'tahun_bukti'], 'belanja_ls_no_bukti_tahun_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus composite unique index
        Schema::table('belanja_ls', function (Blueprint $table) {
            $table->dropUnique('belanja_ls_no_bukti_tahun_unique');
        });
        
        // Hapus kolom tahun_bukti
        Schema::table('belanja_ls', function (Blueprint $table) {
            $table->dropColumn('tahun_bukti');
        });
        
        // Kembalikan unique constraint pada no_bukti saja
        Schema::table('belanja_ls', function (Blueprint $table) {
            $table->unique('no_bukti');
        });
    }
};
