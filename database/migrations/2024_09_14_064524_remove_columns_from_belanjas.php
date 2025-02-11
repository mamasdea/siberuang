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
        Schema::table('belanjas', function (Blueprint $table) {
            $table->dropColumn([
                'penerima_id',
                'ppn',
                'pph21',
                'pph22',
                'pph23',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('belanjas', function (Blueprint $table) {
            $table->bigInteger('penerima_id')->nullable(); // Assumes 'penerima' table exists
            $table->decimal('ppn', 10, 2)->default(0);
            $table->decimal('pph21', 10, 2)->default(0);
            $table->decimal('pph22', 10, 2)->default(0);
            $table->decimal('pph23', 10, 2)->default(0);
        });
    }
};
