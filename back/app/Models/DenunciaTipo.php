<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DenunciaTipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
    ];

    public function denuncias(): BelongsToMany
    {
        return $this->belongsToMany(Denuncia::class, 'denuncia_detalle');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(DenunciaLog::class, 'denuncia_tipo_id');
    }
}
