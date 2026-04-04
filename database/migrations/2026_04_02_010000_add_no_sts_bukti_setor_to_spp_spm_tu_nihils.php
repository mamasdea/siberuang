<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spp_spm_tu_nihils', function (Blueprint $table) {
            $table->string('no_sts')->nullable()->after('no_bukti');
            $table->string('bukti_setor')->nullable()->after('no_sp2d');
        });
    }

    public function down(): void
    {
        Schema::table('spp_spm_tu_nihils', function (Blueprint $table) {
            $table->dropColumn(['no_sts', 'bukti_setor']);
        });
    }
};
