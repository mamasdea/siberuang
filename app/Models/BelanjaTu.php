<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BelanjaTu extends Model
{
    use HasFactory;

    protected $fillable = [
        'spp_spm_tu_id', 'no_bukti', 'tanggal', 'uraian',
        'nilai', 'rka_id', 'is_transfer', 'is_sipd',
    ];

    public function sppSpmTu()
    {
        return $this->belongsTo(SppSpmTu::class);
    }

    public function rka()
    {
        return $this->belongsTo(Rka::class);
    }

    public function pajakTus()
    {
        return $this->hasMany(PajakTu::class);
    }

    public function penerimaanTus()
    {
        return $this->hasMany(PenerimaanTu::class);
    }
}
