<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\ActualizarComplejoRequest;
use App\Http\Resources\ComplejoDetalleResource;
use App\Models\Complejo;
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
}