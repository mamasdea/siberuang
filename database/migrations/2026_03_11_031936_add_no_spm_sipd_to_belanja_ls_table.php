<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('belanja_ls', function (Blueprint $table) {
            $table->string('no_spm_sipd')->nullable()->after('no_bukti');
        });
    }

    public function down(): void
    {
        Schema::table('belanja_ls', function (Blueprint $table) {
            $table->dropColumn('no_spm_sipd');
        });
    }
};
