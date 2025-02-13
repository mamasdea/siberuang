<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement("drop view if exist vw_transaksi");
        DB::statement("CREATE VIEW vw_transaksi as
SELECT '' AS id, tanggal,no_bukti,'' AS rekening,uraian,nominal AS debet, '' AS kredit FROM uang_giros
UNION ALL SELECT belanjas.id, tanggal,no_bukti,kode_belanja as rekening, uraian, '' AS debet, nilai AS kredit FROM belanjas
JOIN rkas ON rkas.id = belanjas.rka_id;");
    }
}
