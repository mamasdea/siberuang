<?php

namespace App\Models;

use App\Models\Belanja;
use App\Models\Penerima;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penerimaan extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Relasi ke model Belanja
    public function belanja()
    {
        return $this->belongsTo(Belanja::class, 'belanja_id');
    }

    // Relasi ke model Penerima
    public function penerima()
    {
        return $this->belongsTo(Penerima::class, 'penerima_id');
    }
}
