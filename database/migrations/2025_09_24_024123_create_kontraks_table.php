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
        Schema::create('kontraks', function (Blueprint $table) {
            $table->id();

            // Identitas kontrak
            $table->string('nomor_kontrak')->unique();       // 050/263/Diskominfo
            $table->date('tanggal_kontrak');                 // 2024-12-10
            $table->string('jangka_waktu')->nullable();      // contoh: "4 (empat) hari kalender"

            // Relasi sub kegiatan (wajib pilih salah satu sub_kegiatan)
            $table->foreignId('sub_kegiatan_id')
                ->constrained('sub_kegiatans')
                ->cascadeOnDelete();

            // Keperluan & ID LKPP
            $table->text('keperluan')->nullable();
            $table->string('id_kontrak_lkpp')->nullable();

            // Data perusahaan
            $table->string('nama_perusahaan');               // CAHAYA RIZKI
            $table->string('bentuk_perusahaan', 20)->nullable();  // CV, PT, dll
            $table->text('alamat_perusahaan')->nullable();
            $table->string('nama_pimpinan')->nullable();     // MOCHAMAD FUAD
            $table->string('npwp_perusahaan', 30)->nullable();

            // Nilai & perbankan
            $table->decimal('nilai', 15, 2)->default(0);     // 9700000.00
            $table->string('nama_bank')->nullable();
            $table->string('nama_pemilik_rekening')->nullable();
            $table->string('nomor_rekening', 50)->nullable();

            $table->timestamps();

            // Index yang sering dicari
            $table->index('tanggal_kontrak');
            $table->index('nama_perusahaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontraks');
    }
};
