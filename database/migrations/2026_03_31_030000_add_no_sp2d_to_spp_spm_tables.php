<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spp_spm_ups', function (Blueprint $table) {
            $table->string('no_sp2d')->nullable()->after('tanggal_sp2d');
        });

        Schema::table('spp_spm_gus', function (Blueprint $table) {
            $table->string('no_sp2d')->nullable()->after('tanggal_sp2d');
        });
    }

    public function down(): void
    {
        Schema::table('spp_spm_ups', function (Blueprint $table) {
            $table->dropColumn('no_sp2d');
        });

        Schema::table('spp_spm_gus', function (Blueprint $table) {
            $table->dropColumn('no_sp2d');
        });
    }
};
