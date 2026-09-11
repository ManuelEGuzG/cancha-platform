<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Complejo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class AuditoriaController extends Controller
{
    public function index(Complejo $complejo): JsonResponse
    {
        Gate::authorize('gestionar', $complejo);

        $canchaIds = $complejo->canchas()->pluck('id');

        $actividad = Activity::query()
            ->where(function ($query) use ($canchaIds) {
                $query->whereIn('log_name', ['reserva', 'bloqueo', 'cancha'])
                    ->where(function ($q) use ($canchaIds) {
                        $q->whereHasMorph('subject', ['App\Models\Reserva', 'App\Models\Bloqueo'], function ($q) use ($canchaIds) {
                            $q->whereIn('cancha_id', $canchaIds);
                        })->orWhereHasMorph('subject', ['App\Models\Cancha'], function ($q) use ($canchaIds) {
                            $q->whereIn('id', $canchaIds);
                        });
                    });
            })
            ->with('causer')
            ->latest()
            ->paginate(30);

        return response()->json(['data' => $actividad]);
    }
}