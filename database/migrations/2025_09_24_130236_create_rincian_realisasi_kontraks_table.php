<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rincian_realisasi_kontraks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kontrak_realisasi_id')
                ->constrained('kontrak_realisasis')
                ->cascadeOnDelete();

            $table->foreignId('rincian_kontrak_id')
                ->constrained('rincian_kontraks')
                ->cascadeOnDelete();

            // Snapshot saat realisasi
            $table->string('nama_barang');
            $table->decimal('kuantitas', 15, 2)->default(0);
            $table->string('satuan', 50)->nullable();
            $table->decimal('harga', 15, 2)->default(0);
            $table->decimal('total_harga', 15, 2)->default(0);

            $table->timestamps();

            // PENTING: nama index pendek agar <64 chars
            $table->unique(['kontrak_realisasi_id', 'rincian_kontrak_id'], 'rrk_realisasi_rincian_uq');
            $table->index('kontrak_realisasi_id', 'rrk_realisasi_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rincian_realisasi_kontraks');
    }
};
