<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spp_spm_gus', function (Blueprint $table) {
            $table->date('tanggal_sp2d')->nullable()->after('total_nilai');
        });
    }

    public function down(): void
    {
        Schema::table('spp_spm_gus', function (Blueprint $table) {
            $table->dropColumn('tanggal_sp2d');
        });
    }
};
