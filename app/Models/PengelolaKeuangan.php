<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengelolaKeuangan extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function scopeAktifPadaTanggal($query, $tanggal)
    {
        // Hanya record yang sudah punya tanggal_mulai (exclude data lama tanpa range)
        return $query->whereNotNull('tanggal_mulai')
            ->where('tanggal_mulai', '<=', $tanggal)
            ->where(function ($q) use ($tanggal) {
                $q->whereNull('tanggal_selesai')
                  ->orWhere('tanggal_selesai', '>=', $tanggal);
            });
    }
}
