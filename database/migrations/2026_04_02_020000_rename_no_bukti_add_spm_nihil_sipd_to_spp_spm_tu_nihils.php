<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spp_spm_tu_nihils', function (Blueprint $table) {
            $table->renameColumn('no_bukti', 'no_spp');
            $table->string('no_spm_tu_nihil_sipd')->nullable()->after('no_spm_sipd');
        });
    }

    public function down(): void
    {
        Schema::table('spp_spm_tu_nihils', function (Blueprint $table) {
            $table->renameColumn('no_spp', 'no_bukti');
            $table->dropColumn('no_spm_tu_nihil_sipd');
        });
    }
};
