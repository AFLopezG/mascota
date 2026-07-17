<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mascota extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'foto',
        'nombre',
        'tipo',
        'fec_nac',
        'edad',
        'raza',
        'color',
        'tamano',
        'peso',
        'estado',
        'observacion',
        'sexo',
        'categoria',
        'esterilizado',
        'persona_id',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function vacunas()
    {
        return $this->hasMany(Vacuna::class);
    }
}
