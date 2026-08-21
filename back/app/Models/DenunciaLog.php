<?php

namespace App\Models;

use App\Models\Personal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DenunciaLog extends Model
{
    use HasFactory;

    protected $table = 'logs';

    protected $fillable = [
        'fecha_hora',
        'actividad',
        'resultado',
        'obser',
        'denuncia_id',
        'user_id',
        'denuncia_tipo_id',
        'proceso_id',
        'personal_id',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function denuncia(): BelongsTo
    {
        return $this->belongsTo(Denuncia::class, 'denuncia_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function denunciaTipo(): BelongsTo
    {
        return $this->belongsTo(DenunciaTipo::class, 'denuncia_tipo_id');
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class, 'proceso_id');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }
}
