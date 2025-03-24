<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BelanjaLs extends Model
{
    use HasFactory;
    protected $fillable = [
        'no_bukti',
        'tanggal',
        'uraian',
        'total_nilai',
    ];


    public function rka()
    {
        return $this->belongsTo(Rka::class);
    }

    public function details()
    {
        return $this->hasMany(BelanjaLsDetails::class);
    }

    public function pajakLs()
    {
        return $this->hasMany(\App\Models\PajakLs::class, 'belanja_ls_id');
    }
}
