<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioExcepcion extends Model
{
    protected $table = 'horarios_excepcion';

    protected $fillable = [
        'cancha_id',
        'fecha',
        'hora_apertura',
        'hora_cierre',
        'motivo',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function cancha(): BelongsTo
    {
        return $this->belongsTo(Cancha::class);
    }
}