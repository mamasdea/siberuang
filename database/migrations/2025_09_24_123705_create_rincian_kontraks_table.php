<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rincian_kontraks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kontrak_id')->constrained('kontraks')->cascadeOnDelete();

            $table->string('nama_barang');
            $table->decimal('kuantitas', 15, 2)->default(0);
            $table->string('satuan', 50)->nullable();
            $table->decimal('harga', 15, 2)->default(0);

            // Diset otomatis = kuantitas * harga (untuk performa dan pelaporan cepat)
            $table->decimal('total_harga', 15, 2)->default(0);

            $table->timestamps();

            $table->index(['kontrak_id', 'nama_barang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rincian_kontraks');
    }
};
