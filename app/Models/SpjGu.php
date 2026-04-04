<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpjGu extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_spj',
        'tanggal_spj',
        'periode_awal',
        'periode_akhir',
        'keterangan',
    ];

    public function belanjas()
    {
        return $this->belongsToMany(Belanja::class, 'spj_gu_belanja')
            ->withTimestamps();
    }

    public function getTotalNilaiAttribute()
    {
        return $this->belanjas->sum('nilai');
    }

    public function getTotalPajakAttribute()
    {
        return $this->belanjas->sum(function ($belanja) {
            return $belanja->pajak->sum('nominal');
        });
    }

    public function getJumlahBelanjaAttribute()
    {
        return $this->belanjas->count();
    }

    public function sppSpmGus()
    {
        return $this->belongsToMany(SppSpmGu::class, 'spp_spm_gu_spj_gu')
            ->withTimestamps();
    }

    public function nihil()
    {
        return $this->hasOne(SppSpmGuNihil::class);
    }
}
