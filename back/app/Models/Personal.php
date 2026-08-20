<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DenunciaLog;

class Personal extends Model
{
    use HasFactory;

    protected $fillable = [
        'cedula',
        'nombre',
        'celular',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(DenunciaLog::class, 'personal_id');
    }
}
