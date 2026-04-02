<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VwTransaksiTu extends Model
{
    protected $table = 'vw_transaksi_tu';
    public $timestamps = false;

    public function pajakTu()
    {
        return $this->hasMany(PajakTu::class, 'belanja_tu_id', 'id');
    }
}
