<?php

namespace App\Http\Controllers\Panel;

use App\Events\DisponibilidadActualizada;
use App\Exceptions\HorarioNoDisponibleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\CrearBloqueoRequest;
use App\Models\Bloqueo;
use App\Models\Cancha;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class BloqueoController extends Controller
{
    public function store(CrearBloqueoRequest $request): JsonResponse
    {
        $cancha = Cancha::findOrFail($request->integer('cancha_id'));

        Gate::authorize('gestionar', $cancha);

        try {
            $bloqueo = DB::transaction(function () use ($request, $cancha): Bloqueo {
                Cancha::query()
                    ->whereKey($cancha->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $datos = $request->validated();
                $rangoOcupado = Reserva::query()
                    ->where('cancha_id', $cancha->id)
                    ->whereDate('fecha', $datos['fecha'])
                    ->whereIn('estado', ['pendiente', 'confirmada'])
                    ->where('hora_inicio', '<', $datos['hora_fin'])
                    ->where('hora_fin', '>', $datos['hora_inicio'])
                    ->lockForUpdate()
                    ->exists();

                $bloqueoSolapado = Bloqueo::query()
                    ->where('cancha_id', $cancha->id)
                    ->whereDate('fecha', $datos['fecha'])
                    ->where('hora_inicio', '<', $datos['hora_fin'])
                    ->where('hora_fin', '>', $datos['hora_inicio'])
                    ->lockForUpdate()
                    ->exists();

                if ($rangoOcupado || $bloqueoSolapado) {
                    throw new HorarioNoDisponibleException('El horario se solapa con una reserva o un bloqueo existente.');
                }

                return Bloqueo::create([
                    ...$datos,
                    'creado_por' => $request->user()->id,
                ]);
            }, attempts: 5);
        } catch (HorarioNoDisponibleException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }

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