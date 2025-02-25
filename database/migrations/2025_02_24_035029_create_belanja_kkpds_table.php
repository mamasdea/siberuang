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
        Schema::create('belanja_kkpds', function (Blueprint $table) {
            $table->id();
            $table->string('no_bukti')->unique();
            $table->date('tanggal');
            $table->text('uraian');
            $table->foreignId('rka_id')->constrained()->onDelete('cascade');
            $table->decimal('nilai', 15, 2);
            $table->boolean('is_sipd')->default(false);
            $table->boolean('is_transfer')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('belanja_kkpds');
    }
};
