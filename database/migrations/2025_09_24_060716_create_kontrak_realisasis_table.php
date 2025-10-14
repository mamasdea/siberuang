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
        Schema::create('kontrak_realisasis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kontrak_id')
                ->constrained('kontraks')
                ->cascadeOnDelete();

            // jenis realisasi: termin / sekaligus
            $table->enum('tipe', ['termin', 'sekaligus'])->default('sekaligus');

            // diisi hanya jika tipe = termin
            $table->unsignedInteger('termin_ke')->nullable();

            $table->decimal('nominal', 15, 2);
            $table->date('tanggal');

            $table->timestamps();

            $table->index(['kontrak_id', 'tipe', 'termin_ke']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrak_realisasis');
    }
};
