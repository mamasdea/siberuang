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
        DB::statement('DROP VIEW IF EXISTS vw_transaksi_kkpd');
        DB::statement("
        CREATE VIEW vw_transaksi_kkpd AS
        SELECT '' AS id, tanggal, no_bukti, '' AS rekening, uraian, nominal AS debet, '' AS kredit FROM uang_kkpds
        UNION ALL
        SELECT belanja_kkpds.id, tanggal, no_bukti, kode_belanja AS rekening, uraian, '' AS debet, nilai AS kredit
        FROM belanja_kkpds
        JOIN rkas ON rkas.id = belanja_kkpds.rka_id
        WHERE belanja_kkpds.is_transfer = 1
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_transaksi_kkpd');
    }
};
