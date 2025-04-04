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
        Schema::create('pajak_kkpds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('belanja_id')->constrained('belanja_kkpds')->onDelete('cascade');
            $table->string('jenis_pajak');
            $table->string('no_billing');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pajak_kkpds');
    }
};
