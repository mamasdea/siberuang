<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function kegiatans()
    {
        return $this->hasMany(Kegiatan::class, 'program_id');
    }
    // public function program()
    // {
    //     return $this->belongsTo(Program::class, 'program_id', 'id');
    // }

    public function kegiatan()
    {
        return $this->hasMany(Kegiatan::class);
    }
    // public function getTotalAnggaran()
    // {
    //     return $this->kegiatan->sum(function ($kegiatan) {
    //         return $kegiatan->getTotalAnggaran();
    //     });
    // }
}
