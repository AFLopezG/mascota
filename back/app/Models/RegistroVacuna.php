<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Models\Campania;
use App\Models\Especie;
use App\Models\HealthCenter;
use App\Models\Place;
use App\Models\Raza;
use App\Models\User;

class RegistroVacuna extends Model
{
    use HasFactory;

    protected $fillable = [
        'estado',
        'cedula',
        'nombre',
        'domicilio',
        'celular',
        'nombre_mascota',
        'raza',
        'menor',
        'foto',
        'lat',
        'lng',
        'fecha_vacuna',
        'campania_id',
        'especie_id',
        'raza_id',
        'place_id',
        'health_center_id',
        'user_id',
    ];

    protected $casts = [
        'menor' => 'boolean',
        'fecha_vacuna' => 'datetime',
    ];

    protected $appends = [
        'foto_url',
    ];

    public function campania(): BelongsTo
    {
        return $this->belongsTo(Campania::class);
    }

    public function especie(): BelongsTo
    {
        return $this->belongsTo(Especie::class);
    }

    public function raza(): BelongsTo
    {
        return $this->belongsTo(Raza::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function healthCenter(): BelongsTo
    {
        return $this->belongsTo(HealthCenter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (empty($this->foto)) {
            return null;
        }

        return Storage::disk('public')->url($this->foto);
    }

    public function isAnulado(): bool
    {
        return strcasecmp(trim((string) $this->estado), 'ANULADO') === 0;
    }
}
