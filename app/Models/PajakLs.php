<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PajakLs extends Model
{
    use HasFactory;
    protected $table = 'pajak_ls';

    protected $fillable = [
        'belanja_ls_id',
        'jenis_pajak',
        'no_billing',
        'nominal',
    ];

    public function belanjaLs()
    {
        return $this->belongsTo(BelanjaLs::class, 'belanja_ls_id');
    }
}
