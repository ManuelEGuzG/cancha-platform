<?php

namespace App\Http\Controllers\Panel;

use App\Events\DisponibilidadActualizada;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\CrearBloqueoRequest;
use App\Models\Bloqueo;
use App\Models\Cancha;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class BloqueoController extends Controller
{
    public function store(CrearBloqueoRequest $request): JsonResponse
    {
        $cancha = Cancha::findOrFail($request->integer('cancha_id'));

        Gate::authorize('gestionar', $cancha);

        $bloqueo = Bloqueo::create([
            ...$request->validated(),
            'creado_por' => $request->user()->id,
        ]);

        broadcast(new DisponibilidadActualizada(
            complejoId: $cancha->complejo_id,
            canchaId: $cancha->id,
            fecha: Carbon::parse($bloqueo->fecha)->toDateString(),
        ));

        return response()->json(['data' => $bloqueo], 201);
    }

    public function destroy(Bloqueo $bloqueo): JsonResponse
    {
        Gate::authorize('gestionar', $bloqueo->cancha);

        $complejoId = $bloqueo->cancha->complejo_id;
        $canchaId = $bloqueo->cancha_id;
        $fecha = Carbon::parse($bloqueo->fecha)->toDateString();

        $bloqueo->delete();

        broadcast(new DisponibilidadActualizada(
            complejoId: $complejoId,
            canchaId: $canchaId,
            fecha: $fecha,
        ));

        return response()->json(['message' => 'Bloqueo eliminado correctamente.']);
    }
}