<?php

namespace App\Http\Controllers\Admin;

use App\Events\DisponibilidadActualizada;
use App\Http\Controllers\Controller;
use App\Models\Cancha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificacionCanchaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'estado' => ['nullable', 'in:pendiente,aprobada,rechazada'],
        ]);

        $canchas = Cancha::withoutGlobalScopes()
            ->with(['complejo:id,nombre', 'deporte:id,nombre'])
            ->when($request->filled('estado'), fn ($query) => $query->where('estado_verificacion', $request->string('estado')->toString()))
            ->orderByRaw("CASE estado_verificacion WHEN 'pendiente' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->paginate(50);

        return response()->json(['data' => $canchas]);
    }

    public function update(Request $request, Cancha $cancha): JsonResponse
    {
        $datos = $request->validate([
            'estado_verificacion' => ['required', 'in:aprobada,rechazada'],
            'observaciones_admin' => ['required_if:estado_verificacion,rechazada', 'nullable', 'string', 'max:1000'],
        ]);

        $cancha->update([
            ...$datos,
            'verificado_por' => $request->user()->id,
            'verificado_en' => now(),
        ]);

        broadcast(new DisponibilidadActualizada(
            complejoId: $cancha->complejo_id,
            canchaId: $cancha->id,
            fecha: now()->toDateString(),
        ));

        return response()->json(['data' => $cancha->fresh(['complejo', 'deporte'])]);
    }
}