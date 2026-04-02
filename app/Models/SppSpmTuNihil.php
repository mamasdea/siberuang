<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SppSpmTuNihil extends Model
{
    use HasFactory;

    protected $fillable = [
        'spp_spm_tu_id', 'no_bukti', 'no_spm_sipd', 'tanggal',
        'tahun_bukti', 'uraian', 'nilai_setor',
        'tanggal_sp2d', 'no_sp2d',
    ];

    public function sppSpmTu()
    {
        return $this->belongsTo(SppSpmTu::class);
    }
}
