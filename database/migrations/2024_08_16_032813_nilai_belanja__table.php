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
            if (!Schema::hasColumn('belanjas', 'nilai')) {
                $table->decimal('nilai', 10, 2)->default(0)->after('rka_id');
            }

            if (!Schema::hasColumn('belanjas', 'penerima_id')) {
                $table->foreignId('penerima_id')->constrained('penerimas')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('belanjas', function (Blueprint $table) {
            if (Schema::hasColumn('belanjas', 'penerima_id')) {
                // Safely attempt to drop the foreign key if it exists
                try {
                    $table->dropForeign(['penerima_id']);
                } catch (\Exception $e) {
                    // Log the exception or handle the error gracefully
                }
                $table->dropColumn('penerima_id');
            }

            if (Schema::hasColumn('belanjas', 'nilai')) {
                $table->dropColumn('nilai');
            }
        });
    }
};
