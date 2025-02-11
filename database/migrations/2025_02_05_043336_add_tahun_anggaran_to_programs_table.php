<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->year('tahun_anggaran')->after('nama')->default(date('Y')); // Menambahkan kolom dengan nilai default tahun sekarang
        });
    }

    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('tahun_anggaran');
        });
    }
};
