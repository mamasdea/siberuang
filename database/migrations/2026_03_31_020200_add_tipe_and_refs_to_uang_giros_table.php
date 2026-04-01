<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uang_giros', function (Blueprint $table) {
            $table->enum('tipe', ['UP', 'GU'])->default('UP')->after('id');
            $table->foreignId('spp_spm_up_id')->nullable()->after('tipe')->constrained('spp_spm_ups')->nullOnDelete();
            $table->foreignId('spp_spm_gu_id')->nullable()->after('spp_spm_up_id')->constrained('spp_spm_gus')->nullOnDelete();
        });

        // Set data lama: semua record yang sudah ada di-set tipe UP
        DB::table('uang_giros')->whereNull('spp_spm_up_id')->whereNull('spp_spm_gu_id')->update(['tipe' => 'UP']);
    }

    public function down(): void
    {
        Schema::table('uang_giros', function (Blueprint $table) {
            $table->dropForeign(['spp_spm_up_id']);
            $table->dropForeign(['spp_spm_gu_id']);
            $table->dropColumn(['tipe', 'spp_spm_up_id', 'spp_spm_gu_id']);
        });
    }
};
