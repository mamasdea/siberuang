<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UangKkpd extends Model
{
    use HasFactory;
    protected $table = 'uang_kkpds';

    protected $fillable = ['no_bukti', 'tanggal', 'uraian', 'nominal'];
}
