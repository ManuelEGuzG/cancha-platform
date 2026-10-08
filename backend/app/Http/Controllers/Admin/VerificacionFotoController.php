<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CanchaFoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificacionFotoController extends Controller
{
    public function index(): JsonResponse
    {
        $fotos = CanchaFoto::query()
            ->with(['cancha:id,nombre,complejo_id', 'cancha.complejo:id,nombre'])
            ->where('estado_verificacion', 'pendiente')
            ->orderBy('id')
            ->paginate(50);

        $fotos->getCollection()->transform(fn (CanchaFoto $foto) => [
            'id' => $foto->id,
            'url' => $foto->url,
            'caption' => $foto->caption,
            'cancha' => $foto->cancha,
        ]);

        return response()->json(['data' => $fotos]);
    }

    public function update(Request $request, CanchaFoto $foto): JsonResponse
    {
        $datos = $request->validate([
            'estado_verificacion' => ['required', 'in:aprobada,rechazada'],
            'observaciones_admin' => ['required_if:estado_verificacion,rechazada', 'nullable', 'string', 'max:1000'],
        ]);

        $foto->update([
            ...$datos,
            'verificado_por' => $request->user()->id,
            'verificado_en' => now(),
        ]);

        return response()->json(['data' => $foto->fresh()]);
    }
}