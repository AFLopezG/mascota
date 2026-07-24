<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mascota extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'fec_reg',
        'foto',
        'nombre',
        'especie',
        'fec_nac',
        'edad',
        'color_principal',
        'color_secundario',
        'tamano',
        'peso',
        'estado',
        'particular',
        'observacion',
        'sexo',
        'esterilizado',
        'fec_esterilizacion',
        'campania_id',
        'categoria_id',
        'persona_id',
        'raza_id',
    ];

    protected $casts = [
        'fec_reg' => 'date',
        'fec_nac' => 'date',
        'fec_esterilizacion' => 'date',
        'esterilizado' => 'boolean',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function vacunas()
    {
        return $this->hasMany(Vacuna::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function raza()
    {
        return $this->belongsTo(Raza::class);
    }

    public function campania()
    {
        return $this->belongsTo(Campania::class);
    }
}
