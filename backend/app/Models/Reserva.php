<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Reserva extends Model
{
    use LogsActivity;

    protected $fillable = [
        'cancha_id',
        'creado_por',
        'nombre_cliente',
        'cedula_cliente',
        'telefono_cliente',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'origen',
        'observaciones',
        'solicitud_id',
        'expira_en',
        'precio_hora_reservado',
        'decision_propietario',
    ];

    protected $casts = [
        'fecha' => 'date',
        'nombre_cliente' => 'string',
        'cedula_cliente' => 'encrypted',
        'expira_en' => 'datetime',
    ];

    protected $hidden = ['cedula_cliente'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['fecha', 'hora_inicio', 'hora_fin', 'estado'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('reserva');
    }

    public function cancha(): BelongsTo
    {
        return $this->belongsTo(Cancha::class);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}