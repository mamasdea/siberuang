<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pajak extends Model
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
