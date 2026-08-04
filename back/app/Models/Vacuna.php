<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacuna extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'fecha_prox',
        'tipo',
        'lugar',
        'num_lote',
        'observacion',
        'mascota_id',
        'campania_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_prox' => 'date',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function campania()
    {
        return $this->belongsTo(Campania::class);
    }
}
