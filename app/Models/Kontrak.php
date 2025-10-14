<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Kontrak extends Model
{
    use HasFactory;

    // sesuaikan $fillable Anda; contoh minimal:
    protected $fillable = [
        'nomor_kontrak',
        'tanggal_kontrak',
        'jangka_waktu',
        'sub_kegiatan',
        'keperluan',
        'id_kontrak_lkpp',
        'nama_perusahaan',
        'bentuk_perusahaan',
        'alamat_perusahaan',
        'nama_pimpinan',
        'npwp_perusahaan',
        'nilai', // tetap ada, tapi akan diisi otomatis
        'nama_bank',
        'nama_pemilik_rekening',
        'nomor_rekening',
        'sub_kegiatan_id', // jika Anda pakai FK sub_kegiatan
    ];

    protected $casts = [
        'tanggal_kontrak' => 'date',
        'nilai'           => 'decimal:2',
    ];

    public function rincians()
    {
        return $this->hasMany(RincianKontrak::class);
    }


    public function realisasis()
    {
        return $this->hasMany(KontrakRealisasi::class);
    }


    public function subKegiatan()
    {
        // tabel: sub_kegiatans, PK: id, FK di kontraks: sub_kegiatan_id
        return $this->belongsTo(SubKegiatan::class, 'sub_kegiatan_id');
    }
    /**
     * Hitung ulang nilai kontrak = sum(total_harga) rincian.
     */
    public function recalcNilai(): void
    {
        $total = (float) $this->rincians()->sum('total_harga');

        // update kolom nilai tanpa memicu event berat
        $this->forceFill(['nilai' => $total])->saveQuietly();
    }
}
