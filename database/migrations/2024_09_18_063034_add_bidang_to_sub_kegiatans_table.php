<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sub_kegiatans', function (Blueprint $table) {
            // Periksa apakah kolom 'pptk_id' sudah ada, jika tidak, tambahkan kolom dan kunci asing
            if (!Schema::hasColumn('sub_kegiatans', 'pptk_id')) {
                $table->unsignedBigInteger('pptk_id')->nullable()->after('nama');
                $table->foreign('pptk_id')->references('id')->on('pengelola_keuangans');
            }
        });
    }

    public function down()
    {
        Schema::table('sub_kegiatans', function (Blueprint $table) {
            // Hapus foreign key terlebih dahulu jika ada
            $table->dropForeign(['pptk_id']);

            // Kemudian hapus kolom pptk_id
            $table->dropColumn('pptk_id');
        });
    }
};
