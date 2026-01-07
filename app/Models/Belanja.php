<?php

namespace App\Models;

use App\Models\Pajak;
use App\Models\Penerimaan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Belanja extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_bukti',
        'tanggal',
        'uraian',
        'nilai',
        'rka_id',
        'is_persediaan',
        'is_sipd',
        'is_transfer',
        'arsip', // Added arsip
    ];

    public function rka()
    {
        return $this->belongsTo(Rka::class);
    }
    public function penerima()
    {
        return $this->belongsTo(Penerima::class);
    }

    public function penerimaan()
    {
        return $this->hasMany(Penerimaan::class, 'belanja_id');
    }


    public function pajak()
    {
        return $this->hasMany(Pajak::class, 'belanja_id');
    }
}
