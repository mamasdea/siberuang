<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RincianKontrak extends Model
{
    use HasFactory;

    protected $fillable = [
        'kontrak_id',
        'nama_barang',
        'kuantitas',
        'satuan',
        'harga',
        'total_harga',
        'periode',
        'progres_fisik',
    ];

    protected $casts = [
        'kuantitas'   => 'decimal:2',
        'harga'       => 'decimal:2',
        'total_harga' => 'decimal:2',
    ];

    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class);
    }

    protected static function booted()
    {
        // Sebelum simpan: pastikan total_harga = qty * harga
        static::saving(function (RincianKontrak $r) {
            $qty = (float) $r->kuantitas;
            $harga = (float) $r->harga;
            $r->total_harga = $qty * $harga;
        });

        // Setelah commit: hitung ulang nilai kontrak
        static::saved(function (RincianKontrak $r) {
            $r->kontrak?->recalcNilai();
        });

        static::deleted(function (RincianKontrak $r) {
            $r->kontrak?->recalcNilai();
        });
    }
}
