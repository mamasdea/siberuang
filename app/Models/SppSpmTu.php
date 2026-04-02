<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SppSpmTu extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_bukti', 'no_spm_sipd', 'tanggal', 'tahun_bukti',
        'sub_kegiatan_id', 'uraian', 'total_nilai',
        'tanggal_sp2d', 'no_sp2d',
    ];

    public function subKegiatan()
    {
        return $this->belongsTo(SubKegiatan::class);
    }

    public function details()
    {
        return $this->hasMany(SppSpmTuDetail::class);
    }

    public function belanjaTus()
    {
        return $this->hasMany(BelanjaTu::class);
    }

    public function spjTu()
    {
        return $this->hasOne(SpjTu::class);
    }

    public function nihil()
    {
        return $this->hasOne(SppSpmTuNihil::class);
    }

    public function uangGiro()
    {
        return $this->hasOne(UangGiro::class, 'spp_spm_tu_id');
    }

    public function getTotalBelanjaAttribute()
    {
        return $this->belanjaTus->sum('nilai');
    }

    public function getSisaTuAttribute()
    {
        return $this->total_nilai - $this->total_belanja;
    }
}
