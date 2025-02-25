<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VwTransaksiKkpd extends Model
{
    use HasFactory;
    protected $table = 'vw_transaksi_kkpd';

    public function pajakkkpd()
    {
        return $this->hasMany(PajakKkpd::class, 'belanja_id', 'id');
    }
}
