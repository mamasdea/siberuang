<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rka extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function subKegiatan()
    {
        return $this->belongsTo(SubKegiatan::class);
    }

    public function belanjas()
    {
        return $this->hasMany(Belanja::class, 'rka_id');
    }
    public function belanjals()
    {
        return $this->hasMany(BelanjaLs::class, 'rka_id');
    }

    public function getSisaAnggaranAttribute()
    {
        // Total transaksi GU untuk RKAS ini
        $totalGU = \App\Models\Belanja::where('rka_id', $this->id)->sum('nilai');
        // Total transaksi LS untuk RKAS ini
        $totalLS = \App\Models\BelanjaLsDetails::where('rka_id', $this->id)->sum('nilai');
        return $this->anggaran - $totalGU - $totalLS;
    }

    public function belanjaLsDetails()
    {
        return $this->hasMany(BelanjaLsDetails::class, 'rka_id');
    }

    public function getLamaAttribute()
    {
        // gu_lama dan ls_lama sudah di-load melalui withSum()
        return ($this->gu_lama ?? 0) + ($this->ls_lama ?? 0);
    }

    public function getBaruAttribute()
    {
        // gu_baru dan ls_baru sudah di-load melalui withSum()
        return ($this->gu_baru ?? 0) + ($this->ls_baru ?? 0);
    }
}
