<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permiso extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'permiso_id',
    ];

    public function subPermisos()
    {
        return $this->hasMany(Permiso::class, 'permiso_id');
    }

    public function parentPermiso()
    {
        return $this->belongsTo(Permiso::class, 'permiso_id');
    }
}
