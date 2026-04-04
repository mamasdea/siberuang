<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SppSpmGuNihil extends Model
{
    use HasFactory;

    protected $fillable = [
        'spj_gu_id', 'no_spp', 'no_sts', 'no_spm_sipd', 'no_spm_gu_nihil_sipd',
        'tanggal', 'tahun_bukti', 'uraian', 'nilai_setor',
        'tanggal_sp2d', 'no_sp2d', 'bukti_setor',
    ];

    public function spjGu()
    {
        return $this->belongsTo(SpjGu::class);
    }
}
