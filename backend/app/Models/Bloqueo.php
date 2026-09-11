<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Bloqueo extends Model
{
    use LogsActivity;

    protected $fillable = [
        'cancha_id',
        'creado_por',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'motivo',
        'notas',
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
            ->logOnly(['fecha', 'hora_inicio', 'hora_fin', 'motivo'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('bloqueo');
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