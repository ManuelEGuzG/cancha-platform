<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\ActualizarCanchaRequest;
use App\Http\Requests\Panel\CrearCanchaRequest;
use App\Models\Cancha;
use App\Models\Complejo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class CanchaController extends Controller
{
    public function index(Complejo $complejo): JsonResponse
    {
        Gate::authorize('gestionar', $complejo);

        $canchas = $complejo->canchas()->with('deporte')->get();

        return response()->json(['data' => $canchas]);
    }

    public function store(CrearCanchaRequest $request): JsonResponse
    {
        $complejo = Complejo::findOrFail($request->integer('complejo_id'));

        Gate::authorize('gestionar', $complejo);

        $cancha = Cancha::create($request->validated());

        return response()->json(['data' => $cancha], 201);
    }

    public function update(ActualizarCanchaRequest $request, Cancha $cancha): JsonResponse
    {
        Gate::authorize('gestionar', $cancha->complejo);

        $cancha->update($request->validated());

        return response()->json(['data' => $cancha]);
    }
}