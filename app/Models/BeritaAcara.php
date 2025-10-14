<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BeritaAcara extends Model
{
    use HasFactory;

    protected $fillable = [
        'kontrak_realisasi_id',
        'jenis',
        'nomor',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function realisasi()
    {
        return $this->belongsTo(KontrakRealisasi::class, 'kontrak_realisasi_id');
    }
}
