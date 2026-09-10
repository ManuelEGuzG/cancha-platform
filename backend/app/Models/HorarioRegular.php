<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioRegular extends Model
{
    protected $table = 'horarios_regulares';

    protected $fillable = [
        'cancha_id',
        'dia_semana',
        'hora_apertura',
        'hora_cierre',
    ];

    public function cancha(): BelongsTo
    {
        return $this->belongsTo(Cancha::class);
    }
}