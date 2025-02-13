<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UangGiro extends Model
{
    use HasFactory;
    protected $table = 'uang_giros';

    protected $fillable = ['no_bukti', 'tanggal', 'uraian', 'nominal'];
}
