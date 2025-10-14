<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RincianRealisasiKontrak extends Model
{
    use HasFactory;

    protected $fillable = [
        'kontrak_realisasi_id',
        'rincian_kontrak_id',
        'nama_barang',
        'kuantitas',
        'satuan',
        'harga',
        'total_harga',
    ];

    protected $casts = [
        'kuantitas'   => 'decimal:2',
        'harga'       => 'decimal:2',
        'total_harga' => 'decimal:2',
    ];

    public function realisasi()
    {
        return $this->belongsTo(KontrakRealisasi::class, 'kontrak_realisasi_id');
    }

    public function rincianKontrak()
    {
        return $this->belongsTo(RincianKontrak::class);
    }

    protected static function booted()
    {
        static::saving(function (RincianRealisasiKontrak $r) {
            $r->total_harga = (float)$r->kuantitas * (float)$r->harga;
            if (!$r->nama_barang)  $r->nama_barang = $r->rincianKontrak?->nama_barang ?? '';
            if (!$r->satuan)       $r->satuan      = $r->rincianKontrak?->satuan ?? null;
            if (!(float)$r->harga) $r->harga       = (float)($r->rincianKontrak?->harga ?? 0);
        });

        static::saved(function (RincianRealisasiKontrak $r) {
            $r->realisasi?->recalcNominal();
        });

        static::deleted(function (RincianRealisasiKontrak $r) {
            $r->realisasi?->recalcNominal();
        });
    }
}
