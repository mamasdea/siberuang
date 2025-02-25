<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenerimaanKkpd extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Relasi ke model Belanja
    public function belanjakkpd()
    {
        return $this->belongsTo(BelanjaKkpd::class, 'belanja_id');
    }

    // Relasi ke model Penerima
    public function penerima()
    {
        return $this->belongsTo(Penerima::class, 'penerima_id');
    }
}
