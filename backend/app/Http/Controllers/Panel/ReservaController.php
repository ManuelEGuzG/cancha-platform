<?php

namespace App\Http\Controllers\Panel;

use App\Events\DisponibilidadActualizada;
use App\Exceptions\HorarioNoDisponibleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\CrearReservaRequest;
use App\Models\Cancha;
use App\Models\Reserva;
use App\Services\ReservaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReservaController extends Controller
{
    public function __construct(
        private readonly ReservaService $reservaService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        abort_if($request->user()->is_platform_admin, 403, 'Use los reportes administrativos para consultar reservas.');

        $request->validate([
            'fecha' => ['nullable', 'date'],
            'cancha_id' => ['nullable', 'integer', 'exists:canchas,id'],
        ]);

        $reservas = Reserva::query()
            ->with('cancha')
            ->when($request->filled('fecha'), fn ($q) => $q->whereDate('fecha', $request->string('fecha')->toString()))
            ->when($request->filled('cancha_id'), fn ($q) => $q->where('cancha_id', $request->integer('cancha_id')))
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->paginate(20);

        return response()->json(['data' => $reservas]);
    }

    public function store(CrearReservaRequest $request): JsonResponse
    {
        $cancha = Cancha::findOrFail($request->integer('cancha_id'));

        Gate::authorize('gestionar', $cancha);

        try {
            $reserva = $this->reservaService->crear($cancha, [
                ...$request->validated(),
                'creado_por' => $request->user()->id,
                'origen' => 'manual',
            ]);
        } catch (HorarioNoDisponibleException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json(['data' => $reserva], 201);
    }

    public function destroy(Reserva $reserva): JsonResponse
    {
        Gate::authorize('gestionar', $reserva->cancha);

        $reserva->update(['estado' => 'cancelada']);

        broadcast(new DisponibilidadActualizada(
            complejoId: $reserva->cancha->complejo_id,
            canchaId: $reserva->cancha_id,
            fecha: Carbon::parse($reserva->fecha)->toDateString(),
        ));

        return response()->json(['message' => 'Reserva cancelada correctamente.']);
    }

    public function aceptar(Reserva $reserva): JsonResponse
    {
        return $this->responderSolicitud($reserva, 'aceptada');
    }

    public function rechazar(Reserva $reserva): JsonResponse
    {
        return $this->responderSolicitud($reserva, 'rechazada');
    }

    private function responderSolicitud(Reserva $reserva, string $decision): JsonResponse
    {
        Gate::authorize('gestionar', $reserva->cancha);

        if (!$reserva->solicitud_id) {
            return response()->json(['message' => 'La reserva no pertenece a una solicitud pública.'], 409);
        }

        $reservas = DB::transaction(function () use ($reserva, $decision) {
            Cancha::query()
                ->whereKey($reserva->cancha_id)
                ->lockForUpdate()
                ->firstOrFail();

            $pendientes = Reserva::query()
                ->where('solicitud_id', $reserva->solicitud_id)
                ->where('estado', 'pendiente')
                ->where('expira_en', '>', now())
                ->lockForUpdate()
                ->get();

            if ($pendientes->isEmpty()) {
                abort(409, 'La solicitud ya fue resuelta o venció.');
            }

            foreach ($pendientes as $pendiente) {
                $pendiente->update([
                    'estado' => $decision === 'aceptada' ? 'confirmada' : 'cancelada',
                    'decision_propietario' => $decision,
                ]);
            }

            return $pendientes;
        }, attempts: 5);

        broadcast(new DisponibilidadActualizada(
            complejoId: $reserva->cancha->complejo_id,
            canchaId: $reserva->cancha_id,
            fecha: Carbon::parse($reserva->fecha)->toDateString(),
        ));

        return response()->json([
            'message' => $decision === 'aceptada' ? 'Solicitud aceptada.' : 'Solicitud rechazada.',
            'data' => $reservas,
        ]);
    }
}