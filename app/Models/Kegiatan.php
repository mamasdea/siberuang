<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function subKegiatans()
    {
        return $this->hasMany(SubKegiatan::class);
    }

    public function sub_kegiatans()
    {
        return $this->hasMany(SubKegiatan::class, 'kegiatan_id');
    }
}
