<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KontrakRealisasi extends Model
{
    use HasFactory;

    protected $fillable = ['kontrak_id', 'tipe', 'termin_ke', 'nominal', 'tanggal', 'progres_fisik', 'periode'];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class);
    }

    public function beritaAcaras()
    {
        return $this->hasMany(BeritaAcara::class);
    }

    public function items()
    {
        return $this->hasMany(RincianRealisasiKontrak::class, 'kontrak_realisasi_id');
    }

    /** Hitung ulang nominal = sum(total_harga) items */
    public function recalcNominal(): void
    {
        $total = (float) $this->items()->sum('total_harga');
        $this->forceFill(['nominal' => $total])->saveQuietly();
    }
}
