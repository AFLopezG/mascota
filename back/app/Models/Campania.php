<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected $casts = [
        'fec_ini' => 'date',
        'fec_fin' => 'date',
    ];

    protected $appends = [
        'is_expired',
        'is_locked',
    ];

    public function campaniaTipo(): BelongsTo
    {
        return $this->belongsTo(CampaniaTipo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        if (!$this->fec_fin) {
            return false;
        }

        return now()->greaterThan($this->fec_fin->copy()->endOfDay());
    }

    public function isAnulada(): bool
    {
        return strcasecmp(trim((string) $this->estado), 'ANULADA') === 0;
    }

    public function isLocked(): bool
    {
        return $this->isExpired() || $this->isAnulada();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->isExpired();
    }

    public function getIsLockedAttribute(): bool
    {
        return $this->isLocked();
    }
}
