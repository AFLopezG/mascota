<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacuna extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'tipo',
        'lugar',
        'observacion',
        'mascota_id',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }
}
