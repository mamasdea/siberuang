<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('belanja_tus', function (Blueprint $table) {
            $table->string('arsip')->nullable()->after('is_sipd');
        });
    }

    public function down(): void
    {
        Schema::table('belanja_tus', function (Blueprint $table) {
            $table->dropColumn('arsip');
        });
    }
};
