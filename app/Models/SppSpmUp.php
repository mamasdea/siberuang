<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SppSpmUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_bukti',
        'no_spm_sipd',
        'tanggal',
        'tahun_bukti',
        'uraian',
        'total_nilai',
        'tanggal_sp2d',
        'no_sp2d',
    ];

    public function uangGiro()
    {
        return $this->hasOne(UangGiro::class, 'spp_spm_up_id');
    }
}
