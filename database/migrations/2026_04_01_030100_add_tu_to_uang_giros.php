<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah enum tipe: tambah TU
        DB::statement("ALTER TABLE uang_giros MODIFY COLUMN tipe ENUM('UP', 'GU', 'TU') DEFAULT 'UP'");

        Schema::table('uang_giros', function (Blueprint $table) {
            $table->foreignId('spp_spm_tu_id')->nullable()->after('spp_spm_gu_id')->constrained('spp_spm_tus')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('uang_giros', function (Blueprint $table) {
            $table->dropForeign(['spp_spm_tu_id']);
            $table->dropColumn('spp_spm_tu_id');
        });

        DB::statement("ALTER TABLE uang_giros MODIFY COLUMN tipe ENUM('UP', 'GU') DEFAULT 'UP'");
    }
};
