<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Salin anggaran ke penetapan untuk record yang penetapan masih 0 tapi anggaran sudah terisi
        DB::table('rkas')
            ->where('penetapan', 0)
            ->where('anggaran', '>', 0)
            ->update(['penetapan' => DB::raw('anggaran')]);
    }

    public function down(): void
    {
        // Rollback: kembalikan penetapan ke 0 untuk record yang penetapan = anggaran dan perubahan = 0
        // (artinya belum pernah ada perubahan, jadi penetapan pasti hasil dari migration ini)
        DB::table('rkas')
            ->where('perubahan', 0)
            ->whereColumn('penetapan', 'anggaran')
            ->update(['penetapan' => 0]);
    }
};
