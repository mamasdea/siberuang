<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PenerimaanTu extends Model
{
    use HasFactory;

    protected $fillable = ['belanja_tu_id', 'penerima_id', 'uraian', 'nominal'];

    public function belanjaTu()
    {
        return $this->belongsTo(BelanjaTu::class);
    }

    public function penerima()
    {
        return $this->belongsTo(Penerima::class);
    }
}
