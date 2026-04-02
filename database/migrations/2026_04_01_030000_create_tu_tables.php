<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. SPP-SPM TU (header pengajuan)
        Schema::create('spp_spm_tus', function (Blueprint $table) {
            $table->id();
            $table->string('no_bukti');
            $table->string('no_spm_sipd')->nullable();
            $table->date('tanggal');
            $table->integer('tahun_bukti')->nullable();
            $table->foreignId('sub_kegiatan_id')->nullable()->constrained('sub_kegiatans');
            $table->text('uraian')->nullable();
            $table->decimal('total_nilai', 15, 2)->default(0);
            $table->date('tanggal_sp2d')->nullable();
            $table->string('no_sp2d')->nullable();
            $table->timestamps();
        });

        // 2. Detail per rekening
        Schema::create('spp_spm_tu_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spp_spm_tu_id')->constrained('spp_spm_tus')->cascadeOnDelete();
            $table->foreignId('rka_id')->constrained('rkas')->restrictOnDelete();
            $table->decimal('nilai', 15, 2)->default(0);
            $table->timestamps();
        });

        // 3. Belanja TU (realisasi)
        Schema::create('belanja_tus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spp_spm_tu_id')->constrained('spp_spm_tus')->cascadeOnDelete();
            $table->string('no_bukti');
            $table->date('tanggal');
            $table->text('uraian')->nullable();
            $table->decimal('nilai', 15, 2)->default(0);
            $table->foreignId('rka_id')->constrained('rkas');
            $table->boolean('is_transfer')->default(false);
            $table->boolean('is_sipd')->default(false);
            $table->timestamps();
        });

        // 4. Penerimaan TU
        Schema::create('penerimaan_tus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('belanja_tu_id')->constrained('belanja_tus')->cascadeOnDelete();
            $table->foreignId('penerima_id')->nullable()->constrained('penerimas')->nullOnDelete();
            $table->string('uraian')->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();
        });

        // 5. Pajak TU
        Schema::create('pajak_tus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('belanja_tu_id')->constrained('belanja_tus')->cascadeOnDelete();
            $table->string('jenis_pajak');
            $table->string('no_billing');
            $table->string('ntpn')->nullable();
            $table->string('ntb')->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();
        });

        // 6. SPJ TU
        Schema::create('spj_tus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spp_spm_tu_id')->constrained('spp_spm_tus')->cascadeOnDelete();
            $table->string('nomor_spj');
            $table->date('tanggal_spj');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 7. SPP-SPM TU Nihil
        Schema::create('spp_spm_tu_nihils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spp_spm_tu_id')->constrained('spp_spm_tus')->cascadeOnDelete();
            $table->string('no_bukti');
            $table->string('no_spm_sipd')->nullable();
            $table->date('tanggal');
            $table->integer('tahun_bukti')->nullable();
            $table->text('uraian')->nullable();
            $table->decimal('nilai_setor', 15, 2)->default(0);
            $table->date('tanggal_sp2d')->nullable();
            $table->string('no_sp2d')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spp_spm_tu_nihils');
        Schema::dropIfExists('spj_tus');
        Schema::dropIfExists('pajak_tus');
        Schema::dropIfExists('penerimaan_tus');
        Schema::dropIfExists('belanja_tus');
        Schema::dropIfExists('spp_spm_tu_details');
        Schema::dropIfExists('spp_spm_tus');
    }
};
