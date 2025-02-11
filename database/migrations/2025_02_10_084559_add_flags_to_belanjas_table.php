<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('belanjas', function (Blueprint $table) {
            $table->boolean('is_persediaan')->default(0)->after('nilai'); // Sesuaikan posisi
            $table->boolean('is_sipd')->default(0)->after('is_persediaan');
            $table->boolean('is_transfer')->default(0)->after('is_sipd');
        });
    }

    public function down()
    {
        Schema::table('belanjas', function (Blueprint $table) {
            $table->dropColumn(['is_persediaan', 'is_sipd', 'is_transfer']);
        });
    }
};
