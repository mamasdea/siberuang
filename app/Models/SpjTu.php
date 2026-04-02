<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpjTu extends Model
{
    use HasFactory;

    protected $fillable = ['spp_spm_tu_id', 'nomor_spj', 'tanggal_spj', 'keterangan'];

    public function sppSpmTu()
    {
        return $this->belongsTo(SppSpmTu::class);
    }
}
