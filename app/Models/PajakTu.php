<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PajakTu extends Model
{
    use HasFactory;

    protected $fillable = [
        'belanja_tu_id', 'jenis_pajak', 'no_billing', 'ntpn', 'ntb', 'nominal',
    ];

    public function belanjaTu()
    {
        return $this->belongsTo(BelanjaTu::class);
    }
}
