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
        'telefono_cliente',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'origen',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre_cliente', 'fecha', 'hora_inicio', 'hora_fin', 'estado'])
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