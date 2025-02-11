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
        Schema::create('belanja_ls', function (Blueprint $table) {
            $table->id();
            $table->string('no_bukti')->unique();
            $table->date('tanggal');
            $table->text('uraian')->nullable();
            $table->decimal('total_nilai', 15, 2); // Total nilai transaksi LS
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('belanja_ls');
    }
};
