<?php

namespace App\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Filtra automáticamente los modelos de un tenant (complejo) según
 * el usuario autenticado, para las rutas del panel privado.
 *
 * NO se aplica en endpoints públicos (búsqueda, disponibilidad pública),
 * esos consultan explícitamente sin este scope.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth('sanctum')->user();

        if (!$user instanceof User) {
            return;
        }

        if ($user->is_platform_admin) {
            return;
        }

        $complejoIds = $user->complejos()->pluck('complejos.id');

        if ($model->getTable() === 'canchas') {
            $builder->whereIn('canchas.complejo_id', $complejoIds);
            return;
        }

        // Modelos que llegan a su complejo a través de la cancha (reservas, bloqueos, horarios)
        $builder->whereHas('cancha', function ($query) use ($complejoIds) {
            $query->whereIn('canchas.complejo_id', $complejoIds);
        });
    }
}