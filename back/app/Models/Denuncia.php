<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Denuncia extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'fec_denuncia',
        'direccion',
        'descripcion',
        'zona',
        'color',
        'tamanio',
        'estado',
        'observacion',
        'nom_afectado',
        'edad',
        'telefono',
        'dir_inicidente',
        'tipo_lesion',
        'dias_obser',
        'resultado',
        'obs',
        'raza_id',
        'user_id',
        'persona_id',
        'mascota_id',
    ];

    protected $casts = [
        'fec_denuncia' => 'datetime',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function raza(): BelongsTo
    {
        return $this->belongsTo(Raza::class);
    }

    public function tipos(): BelongsToMany
    {
        return $this->belongsToMany(DenunciaTipo::class, 'denuncia_detalle');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(DenunciaLog::class, 'denuncia_id');
    }
}
