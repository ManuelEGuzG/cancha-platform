<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\ActualizarHorarioRegularRequest;
use App\Http\Requests\Panel\CrearHorarioExcepcionRequest;
use App\Models\Cancha;
use App\Models\HorarioExcepcion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class HorarioController extends Controller
{
    public function index(Cancha $cancha): JsonResponse
    {
        Gate::authorize('gestionar', $cancha);

        return response()->json([
            'data' => [
                'horarios_regulares' => $cancha->horariosRegulares()->orderBy('dia_semana')->get(),
                'horarios_excepcion' => $cancha->horariosExcepcion()
                    ->where('fecha', '>=', now()->toDateString())
                    ->orderBy('fecha')
                    ->get(),
            ],
        ]);
    }

    /**
     * Reemplaza todo el horario regular semanal de una cancha en una sola operación atómica.
     */
    public function actualizarRegular(ActualizarHorarioRegularRequest $request, Cancha $cancha): JsonResponse
    {
        Gate::authorize('gestionar', $cancha);

        DB::transaction(function () use ($request, $cancha) {
            $cancha->horariosRegulares()->delete();

            foreach ($request->validated('horarios') as $horario) {
                $cancha->horariosRegulares()->create($horario);
            }
        });

        return response()->json([
            'data' => $cancha->horariosRegulares()->orderBy('dia_semana')->get(),
        ]);
    }

    public function crearExcepcion(CrearHorarioExcepcionRequest $request): JsonResponse
    {
        $cancha = Cancha::findOrFail($request->integer('cancha_id'));

        Gate::authorize('gestionar', $cancha);

        $excepcion = HorarioExcepcion::create($request->validated());

        return response()->json(['data' => $excepcion], 201);
    }

    public function eliminarExcepcion(HorarioExcepcion $horarioExcepcion): JsonResponse
    {
        Gate::authorize('gestionar', $horarioExcepcion->cancha);

        $horarioExcepcion->delete();

        return response()->json(['message' => 'Excepción eliminada correctamente.']);
    }
}