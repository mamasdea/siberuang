<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spp_spm_ups', function (Blueprint $table) {
            $table->id();
            $table->string('no_bukti');
            $table->string('no_spm_sipd')->nullable();
            $table->date('tanggal');
            $table->integer('tahun_bukti')->nullable();
            $table->text('uraian')->nullable();
            $table->decimal('total_nilai', 15, 2)->default(0);
            $table->date('tanggal_sp2d')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spp_spm_ups');
    }
};
