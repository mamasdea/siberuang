<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Revert vw_transaksi: hapus belanja_tus, kembali hanya GU
        DB::statement('DROP VIEW IF EXISTS vw_transaksi');
        DB::statement("
            CREATE VIEW vw_transaksi AS
            SELECT '' AS id, tanggal, no_bukti, '' AS rekening, uraian, nominal AS debet, '' AS kredit FROM uang_giros
            WHERE tipe IN ('UP', 'GU')
            UNION ALL
            SELECT belanjas.id, tanggal, no_bukti, kode_belanja AS rekening, uraian, '' AS debet, nilai AS kredit FROM belanjas
            JOIN rkas ON rkas.id = belanjas.rka_id WHERE belanjas.is_transfer = 1
        ");

        // Buat vw_transaksi_tu khusus TU
        DB::statement('DROP VIEW IF EXISTS vw_transaksi_tu');
        DB::statement("
            CREATE VIEW vw_transaksi_tu AS
            SELECT '' AS id, tanggal, no_bukti, '' AS rekening, uraian, nominal AS debet, '' AS kredit, 'sp2d' AS jenis FROM uang_giros
            WHERE tipe = 'TU'
            UNION ALL
            SELECT belanja_tus.id, tanggal, no_bukti, kode_belanja AS rekening, uraian, '' AS debet, nilai AS kredit, 'belanja' AS jenis FROM belanja_tus
            JOIN rkas ON rkas.id = belanja_tus.rka_id
            UNION ALL
            SELECT spp_spm_tu_nihils.id, tanggal, no_bukti, '' AS rekening, uraian, '' AS debet, nilai_setor AS kredit, 'nihil' AS jenis FROM spp_spm_tu_nihils
            UNION ALL
            SELECT pajak_tus.id, belanja_tus.tanggal, belanja_tus.no_bukti, '' AS rekening, CONCAT('Setor ', pajak_tus.jenis_pajak) AS uraian, '' AS debet, pajak_tus.nominal AS kredit, 'pajak_setor' AS jenis FROM pajak_tus JOIN belanja_tus ON belanja_tus.id = pajak_tus.belanja_tu_id
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_transaksi_tu');

        // Restore gabungan
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
};
