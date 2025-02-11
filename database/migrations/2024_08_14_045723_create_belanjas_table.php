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
        Schema::create('belanjas', function (Blueprint $table) {
            $table->id();
            $table->string('no_bukti');
            $table->date('tanggal');
            $table->text('uraian');
            $table->foreignId('rka_id')->constrained('rkas')->onDelete('cascade');
            $table->decimal('ppn', 10, 2)->default(0);
            $table->decimal('pph21', 10, 2)->default(0);
            $table->decimal('pph22', 10, 2)->default(0);
            $table->decimal('pph23', 10, 2)->default(0);
            $table->bigInteger('penerima_id')->nullable(); // Assumes 'penerima' table exists
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('belanjas');
    }
};
