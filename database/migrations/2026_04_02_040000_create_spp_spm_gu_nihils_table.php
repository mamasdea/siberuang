<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spp_spm_gu_nihils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spj_gu_id')->constrained('spj_gus')->cascadeOnDelete();
            $table->string('no_spp');
            $table->string('no_sts')->nullable();
            $table->string('no_spm_sipd')->nullable();
            $table->string('no_spm_gu_nihil_sipd')->nullable();
            $table->date('tanggal');
            $table->integer('tahun_bukti')->nullable();
            $table->text('uraian')->nullable();
            $table->decimal('nilai_setor', 15, 2)->default(0);
            $table->date('tanggal_sp2d')->nullable();
            $table->string('no_sp2d')->nullable();
            $table->string('bukti_setor')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spp_spm_gu_nihils');
    }
};
