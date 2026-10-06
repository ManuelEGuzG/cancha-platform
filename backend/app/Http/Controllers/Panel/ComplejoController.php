<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\ActualizarComplejoRequest;
use App\Http\Resources\ComplejoDetalleResource;
use App\Models\Complejo;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ComplejoController extends Controller
{
    /**
     * Lista los complejos administrables por el usuario autenticado.
     * Útil para el selector inicial si el usuario tiene más de uno.
     */
    public function misComplejos(Request $request): JsonResponse
    {
        $user = $request->user();

        $complejos = $user->is_platform_admin
            ? Complejo::withCount('canchas')->get()
            : $user->complejos()->withCount('canchas')->get();

        return response()->json(['data' => $complejos]);
    }

    public function show(Complejo $complejo): JsonResponse
    {
        Gate::authorize('gestionar', $complejo);

        $complejo->load(['distrito.canton.provincia', 'canchas.deporte']);

        return response()->json(['data' => new ComplejoDetalleResource($complejo)]);
    }

    public function update(ActualizarComplejoRequest $request, Complejo $complejo): JsonResponse
    {
        Gate::authorize('gestionar', $complejo);

        $complejo->update($request->validated());

        return response()->json(['data' => new ComplejoDetalleResource($complejo)]);
    }

    public function agenda(Request $request, Complejo $complejo): JsonResponse
    {
        Gate::authorize('gestionar', $complejo);

        $request->validate([
            'fecha' => ['nullable', 'date'],
        ]);

        $fecha = $request->filled('fecha')
            ? Carbon::parse($request->string('fecha')->toString())
            : Carbon::today();

        $canchas = $complejo->canchas()->where('activa', true)->with('deporte')->get();

        $agenda = $canchas->map(function ($cancha) use ($fecha) {
            $reservas = $cancha->reservas()
                ->whereDate('fecha', $fecha->toDateString())
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->orderBy('hora_inicio')
                ->get(['id', 'nombre_cliente', 'telefono_cliente', 'hora_inicio', 'hora_fin', 'estado', 'origen', 'solicitud_id', 'expira_en']);

            $bloqueos = $cancha->bloqueos()
                ->whereDate('fecha', $fecha->toDateString())
                ->orderBy('hora_inicio')
                ->get(['id', 'hora_inicio', 'hora_fin', 'motivo', 'notas']);

            return [
                'cancha_id' => $cancha->id,
                'nombre' => $cancha->nombre,
                'deporte' => $cancha->deporte->nombre,
                'reservas' => $reservas,
                'bloqueos' => $bloqueos,
            ];
        });

        return response()->json([
            'data' => [
                'fecha' => $fecha->toDateString(),
                'canchas' => $agenda,
            ],
        ]);
    }
    public function store(\App\Http\Requests\Admin\CrearComplejoRequest $request): JsonResponse
{
    abort_unless($request->user()->is_platform_admin, 403);

    $complejo = Complejo::create([
        ...$request->validated(),
        'slug' => \Illuminate\Support\Str::slug($request->string('nombre')->toString()) . '-' . uniqid(),
        'activo' => true,
        'suscripcion_activa' => false,
    ]);

    return response()->json(['data' => $complejo], 201);
}
}