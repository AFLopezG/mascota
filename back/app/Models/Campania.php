<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campania extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'fec_ini',
        'fec_fin',
        'lugar',
        'descripcion',
        'estado',
        'campania_tipo_id',
        'user_id',
    ];
}
