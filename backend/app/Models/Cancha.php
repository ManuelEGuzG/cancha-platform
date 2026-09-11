<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Cancha extends Model
{
    use LogsActivity;

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

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'precio_hora', 'activa'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('cancha');
    }

    public function complejo(): BelongsTo
    {
        return $this->belongsTo(Complejo::class);
    }

    public function deporte(): BelongsTo
    {
        return $this->belongsTo(Deporte::class);
    }

    public function horariosRegulares(): HasMany
    {
        return $this->hasMany(HorarioRegular::class);
    }

    public function horariosExcepcion(): HasMany
    {
        return $this->hasMany(HorarioExcepcion::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function bloqueos(): HasMany
    {
        return $this->hasMany(Bloqueo::class);
    }
}