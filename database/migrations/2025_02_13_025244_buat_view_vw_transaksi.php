<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_transaksi');
        DB::statement("
        CREATE VIEW vw_transaksi as
       SELECT '' AS id, tanggal,no_bukti,'' AS rekening,uraian,nominal AS debet, '' AS kredit FROM uang_giros
        UNION ALL SELECT belanjas.id, tanggal,no_bukti,kode_belanja as rekening, uraian, '' AS debet, nilai AS kredit FROM belanjas
        JOIN rkas ON rkas.id = belanjas.rka_id WHERE belanjas.is_transfer = 1
            ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_transaksi');
    }
};
