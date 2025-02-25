<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BelanjaKkpd extends Model
{
    use HasFactory;
    protected $table = 'belanja_kkpds'; // Nama tabel

    protected $fillable = [
        'no_bukti',
        'tanggal',
        'uraian',
        'rka_id',
        'nilai',
        'is_sipd',
        'is_transfer'
    ];

    public function rka()
    {
        return $this->belongsTo(Rka::class);
    }

    public function penerima()
    {
        return $this->belongsTo(Penerima::class);
    }

    public function penerimaankkpd()
    {
        return $this->hasMany(PenerimaanKkpd::class, 'belanja_id');
    }


    public function pajakkkpd()
    {
        return $this->hasMany(PajakKkpd::class, 'belanja_id');
    }
}
