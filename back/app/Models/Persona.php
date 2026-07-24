<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $fillable = [
        'complemento',
        'cinit',
        'nombre',
        'paterno',
        'materno',
        'direccion',
        'telefono',
        'emergencia',
        'lat',
        'lng',
        'luz_agua',
        'correo',
        'zona',
        'distrito',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function mascotas()
    {
        return $this->hasMany(Mascota::class);
    }
}
