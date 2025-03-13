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

    public function belanjaLsDetails()
    {
        return $this->hasMany(BelanjaLsDetails::class, 'rka_id');
    }

    public function belanjaKkpds()
    {
        return $this->hasMany(BelanjaKkpd::class, 'rka_id');
    }

    public function belanjals()
    {
        return $this->hasMany(BelanjaLs::class, 'rka_id');
    }

    public function getSisaAnggaranAttribute()
    {
        $totalGU = $this->belanjas()->sum('nilai');
        $totalKkpd = $this->belanjaKkpds()->sum('nilai');
        $totalLS = $this->belanjaLsDetails()->sum('nilai');

        return $this->anggaran - $totalGU - $totalKkpd - $totalLS;
    }

    public function getLamaAttribute()
    {
        return ($this->gu_lama ?? 0) + ($this->kkpd_lama ?? 0) + ($this->ls_lama ?? 0);
    }

    public function getBaruAttribute()
    {
        return ($this->gu_baru ?? 0) + ($this->kkpd_baru ?? 0) + ($this->ls_baru ?? 0);
    }
}
