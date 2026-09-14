<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Complejo extends Model
{
    protected $fillable = [
        'distrito_id',
        'nombre',
        'slug',
        'descripcion',
        'direccion_texto',
        'latitud',
        'longitud',
        'telefono',
        'whatsapp_numero',
        'logo_url',
        'activo',
        'suscripcion_activa',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'suscripcion_activa' => 'boolean',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
    ];

    public function distrito(): BelongsTo
    {
        return $this->belongsTo(Distrito::class);
    }

    public function canchas(): HasMany
    {
        return $this->hasMany(Cancha::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug'; // permite /api/complejos/{slug} en vez de {id}
    }
    public function pagosSuscripcion(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(PagoSuscripcion::class);
}
}