<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BelanjaLsDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'belanja_ls_id',
        'rka_id',
        'nilai',
    ];

    public function belanjaLs()
    {
        return $this->belongsTo(BelanjaLs::class);
    }

    public function rka()
    {
        return $this->belongsTo(Rka::class);
    }
}
