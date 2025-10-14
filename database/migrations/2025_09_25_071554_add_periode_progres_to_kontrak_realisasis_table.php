<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kontrak_realisasis', function (Blueprint $table) {
            // periode bebas sesuai kebutuhan (mis. "2025-09", "Triwulan II 2025", "September 2025", dsb)
            $table->string('periode', 50)->nullable()->after('tanggal');

            // progres fisik dalam persen (0–100), boleh desimal, nullable
            $table->decimal('progres_fisik', 5, 2)->nullable()->after('periode');
        });
    }

    public function down(): void
    {
        Schema::table('kontrak_realisasis', function (Blueprint $table) {
            $table->dropColumn(['periode', 'progres_fisik']);
        });
    }
};
