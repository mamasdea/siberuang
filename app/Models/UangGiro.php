<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UangGiro extends Model
{
    use HasFactory;
    protected $table = 'uang_giros';

    protected $fillable = ['no_bukti', 'tanggal', 'uraian', 'nominal', 'tipe', 'spp_spm_up_id', 'spp_spm_gu_id', 'spp_spm_tu_id'];

    public function sppSpmUp()
    {
        return $this->belongsTo(SppSpmUp::class, 'spp_spm_up_id');
    }

    public function sppSpmGu()
    {
        return $this->belongsTo(SppSpmGu::class, 'spp_spm_gu_id');
    }

    public function sppSpmTu()
    {
        return $this->belongsTo(SppSpmTu::class, 'spp_spm_tu_id');
    }
}
