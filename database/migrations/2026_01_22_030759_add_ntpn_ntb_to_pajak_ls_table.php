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
        Schema::table('pajak_ls', function (Blueprint $table) {
            $table->string('ntpn')->nullable()->after('no_billing');
            $table->string('ntb')->nullable()->after('ntpn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pajak_ls', function (Blueprint $table) {
            $table->dropColumn(['ntpn', 'ntb']);
        });
    }
};
