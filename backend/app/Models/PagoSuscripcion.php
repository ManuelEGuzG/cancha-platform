<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoSuscripcion extends Model
{
    protected $table = 'pagos_suscripcion';

    protected $fillable = [
        'complejo_id',
        'registrado_por',
        'monto',
        'periodo_desde',
        'periodo_hasta',
        'fecha_pago',
        'metodo',
        'notas',
    ];

    protected $casts = [
        'periodo_desde' => 'date',
        'periodo_hasta' => 'date',
        'fecha_pago' => 'date',
    ];

    public function complejo(): BelongsTo
    {
        return $this->belongsTo(Complejo::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}