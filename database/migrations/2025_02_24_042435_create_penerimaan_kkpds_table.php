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
        Schema::create('penerimaan_kkpds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('belanja_id')->constrained('belanja_kkpds')->onDelete('cascade');
            $table->foreignId('penerima_id')->constrained('penerimas')->onDelete('cascade');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_kkpds');
    }
};
