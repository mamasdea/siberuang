<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubKegiatan extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function Rka()
    {
        return $this->hasMany(Rka::class);
    }

    public function rkas()
    {
        return $this->hasMany(Rka::class);
    }

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function kegiatan_id()
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }

    public function rkas_id()
    {
        return $this->hasMany(Rka::class, 'sub_kegiatan_id');
    }
    public function pptk()
    {
        return $this->belongsTo(PengelolaKeuangan::class, 'pptk_id');
    }

}
