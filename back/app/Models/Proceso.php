<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proceso extends Model
{
    use HasFactory;

    protected $fillable = [
        'orden',
        'descripcion',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(DenunciaLog::class, 'proceso_id');
    }
}
