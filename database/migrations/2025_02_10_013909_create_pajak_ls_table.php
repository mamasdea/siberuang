<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pajak_ls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('belanja_ls_id');
            $table->string('jenis_pajak');
            $table->string('no_billing');
            $table->decimal('nominal', 15, 2);
            $table->timestamps();

            // Relasi ke tabel belanja_ls
            $table->foreign('belanja_ls_id')
                ->references('id')->on('belanja_ls')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pajak_ls');
    }
};
