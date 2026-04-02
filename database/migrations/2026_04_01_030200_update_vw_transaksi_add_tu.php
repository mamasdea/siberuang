<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_transaksi');
        DB::statement("
            CREATE VIEW vw_transaksi AS
            SELECT '' AS id, tanggal, no_bukti, '' AS rekening, uraian, nominal AS debet, '' AS kredit FROM uang_giros
            UNION ALL
            SELECT belanjas.id, tanggal, no_bukti, kode_belanja AS rekening, uraian, '' AS debet, nilai AS kredit FROM belanjas
            JOIN rkas ON rkas.id = belanjas.rka_id WHERE belanjas.is_transfer = 1
            UNION ALL
            SELECT belanja_tus.id, tanggal, no_bukti, kode_belanja AS rekening, uraian, '' AS debet, nilai AS kredit FROM belanja_tus
            JOIN rkas ON rkas.id = belanja_tus.rka_id WHERE belanja_tus.is_transfer = 1
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_transaksi');
        DB::statement("
            CREATE VIEW vw_transaksi AS
            SELECT '' AS id, tanggal, no_bukti, '' AS rekening, uraian, nominal AS debet, '' AS kredit FROM uang_giros
            UNION ALL
            SELECT belanjas.id, tanggal, no_bukti, kode_belanja AS rekening, uraian, '' AS debet, nilai AS kredit FROM belanjas
            JOIN rkas ON rkas.id = belanjas.rka_id WHERE belanjas.is_transfer = 1
        ");
    }
};
