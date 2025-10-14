<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('kontraks', 'nilai')) {
            Schema::table('kontraks', function (Blueprint $table) {
                $table->decimal('nilai', 15, 2)->default(0)->after('npwp_perusahaan');
            });
        }
    }

    public function down(): void
    {
        // Jangan di-drop kalau Anda ingin mempertahankan; aman untuk rollback:
        if (Schema::hasColumn('kontraks', 'nilai')) {
            Schema::table('kontraks', function (Blueprint $table) {
                $table->dropColumn('nilai');
            });
        }
    }
};
