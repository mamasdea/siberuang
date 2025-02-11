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
        Schema::create('belanja_ls_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('belanja_ls_id');
            $table->unsignedBigInteger('rka_id');
            $table->decimal('nilai', 15, 2); // Nilai yang akan mengurangi RKAS tertentu
            $table->timestamps();

            // Relasi
            $table->foreign('belanja_ls_id')->references('id')->on('belanja_ls')->onDelete('cascade');
            // Jika ingin menambahkan foreign key ke tabel RKAS:
            $table->foreign('rka_id')->references('id')->on('rkas')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('belanja_ls_details');
    }
};
