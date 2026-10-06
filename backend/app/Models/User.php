<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

   protected $fillable = [
    'name',
    'email',
    'password',
    'telefono',
    'is_platform_admin',
    'activo',
];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_platform_admin' => 'boolean',
        'activo' => 'boolean',
        'two_factor_secret' => 'encrypted',
        'two_factor_enabled_at' => 'datetime',
        'two_factor_recovery_codes' => 'encrypted:array',
    ];
}

    /**
     * Todos los complejos a los que este usuario tiene acceso,
     * incluyendo el nombre del rol vía ->pivot->rol_nombre.
     */
    public function complejos(): BelongsToMany
    {
        return $this->belongsToMany(Complejo::class, 'complejo_user')
            ->withPivot('rol_id')
            ->withTimestamps();
    }

    public function perteneceAComplejo(int $complejoId): bool
    {
        if ($this->is_platform_admin) {
            return true;
        }

        return $this->complejos()->where('complejos.id', $complejoId)->exists();
    }

    public function rolEnComplejo(int $complejoId): ?string
    {
        $pivot = $this->complejos()
            ->where('complejos.id', $complejoId)
            ->first()
            ?->pivot;

        if (!$pivot) {
            return null;
        }

        return Rol::find($pivot->rol_id)?->nombre;
    }

    public function esPropietarioDe(int $complejoId): bool
    {
        return $this->rolEnComplejo($complejoId) === Rol::PROPIETARIO;
    }

    public function esEncargadoDe(int $complejoId): bool
    {
        return $this->rolEnComplejo($complejoId) === Rol::ENCARGADO;
    }
}