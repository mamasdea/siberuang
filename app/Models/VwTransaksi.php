<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VwTransaksi extends Model
{
    use HasFactory;


    protected $table = 'vw_transaksi';

    public function pajak()
    {
        return $this->hasMany(Pajak::class, 'belanja_id', 'id');
    }
}
