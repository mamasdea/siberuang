<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SppSpmGu extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_bukti',
        'no_spm_sipd',
        'tanggal',
        'tahun_bukti',
        'uraian',
        'total_nilai',
    ];

    public function spjGus()
    {
        return $this->belongsToMany(SpjGu::class, 'spp_spm_gu_spj_gu')
            ->withTimestamps();
    }
}
