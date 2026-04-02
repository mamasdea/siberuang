<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SppSpmTuDetail extends Model
{
    use HasFactory;

    protected $fillable = ['spp_spm_tu_id', 'rka_id', 'nilai'];

    public function sppSpmTu()
    {
        return $this->belongsTo(SppSpmTu::class);
    }

    public function rka()
    {
        return $this->belongsTo(Rka::class);
    }
}
