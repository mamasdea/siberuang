<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spj_gus', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_spj')->unique();
            $table->date('tanggal_spj');
            $table->date('periode_awal');
            $table->date('periode_akhir');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('spj_gu_belanja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spj_gu_id')->constrained('spj_gus')->onDelete('cascade');
            $table->foreignId('belanja_id')->constrained('belanjas')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['spj_gu_id', 'belanja_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spj_gu_belanja');
        Schema::dropIfExists('spj_gus');
    }
};
