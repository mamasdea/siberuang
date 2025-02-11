<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('rkas', function (Blueprint $table) {
            $table->decimal('penetapan', 15, 2)->default(0)->after('nama_belanja'); // Menambahkan setelah ID
            $table->decimal('perubahan', 15, 2)->default(0)->after('penetapan'); // Setelah penetapan
            $table->decimal('selisih', 15, 2)->default(0)->after('perubahan'); // Setelah perubahan
        });
    }

    public function down()
    {
        Schema::table('rkas', function (Blueprint $table) {
            $table->dropColumn(['penetapan', 'perubahan', 'selisih']);
        });
    }
};
