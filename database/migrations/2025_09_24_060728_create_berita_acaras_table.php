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
        Schema::create('berita_acaras', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kontrak_realisasi_id')
                ->constrained('kontrak_realisasis')
                ->cascadeOnDelete();

            // opsi jenis BA
            $table->enum('jenis', [
                'pemeriksaan',   // Berita Acara Pemeriksaan
                'serah_terima',  // Berita Acara Serah Terima Barang/Jasa
                'penerimaan',    // Berita Acara Penerimaan
                'administratif', // Berita Acara Administratif
                'pembayaran'     // Berita Acara Pembayaran
            ]);

            $table->string('nomor');
            $table->date('tanggal');

            $table->timestamps();

            $table->index(['kontrak_realisasi_id', 'jenis']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita_acaras');
    }
};
