<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\CrearBloqueoRequest;
use App\Models\Bloqueo;
use App\Models\Cancha;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // 1. Importar el trait
use Illuminate\Http\JsonResponse;

class BloqueoController extends Controller
{
    use AuthorizesRequests; // 2. Usar el trait aquí

    public function store(CrearBloqueoRequest $request): JsonResponse
    {
        $cancha = Cancha::findOrFail($request->integer('cancha_id'));

        $this->authorize('gestionar', $cancha);

        $bloqueo = Bloqueo::create([
            ...$request->validated(),
            'creado_por' => $request->user()->id,
        ]);

        return response()->json(['data' => $bloqueo], 201);
    }

    public function destroy(Bloqueo $bloqueo): JsonResponse
    {
        $this->authorize('gestionar', $bloqueo->cancha);

        $bloqueo->delete();

        return response()->json(['message' => 'Bloqueo eliminado correctamente.']);
    }
}