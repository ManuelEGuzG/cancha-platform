<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cancha extends Model
{
    protected $fillable = [
        'complejo_id',
        'deporte_id',
        'nombre',
        'precio_hora',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function complejo(): BelongsTo
    {
        return $this->belongsTo(Complejo::class);
    }

    public function deporte(): BelongsTo
    {
        return $this->belongsTo(Deporte::class);
    }
}