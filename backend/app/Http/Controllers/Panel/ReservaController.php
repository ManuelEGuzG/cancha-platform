<?php

namespace App\Http\Controllers\Panel;

use App\Events\DisponibilidadActualizada;
use App\Exceptions\HorarioNoDisponibleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\CrearReservaRequest;
use App\Models\Cancha;
use App\Models\Reserva;
use App\Services\ReservaService;
use App\Services\WhatsAppLinkService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReservaController extends Controller
{
    public function __construct(
        private readonly ReservaService $reservaService,
        private readonly WhatsAppLinkService $whatsAppLinkService,
    ) {
    }

    public function solicitudes(Request $request): JsonResponse
    {
        abort_if($request->user()->is_platform_admin, 403, 'Use los reportes administrativos para consultar solicitudes.');

        $filtros = $request->validate([
            'estado' => ['nullable', 'in:pendiente,aceptada,confirmada,cancelada,completada,no_presentada'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $estado = $filtros['estado'] ?? null;
        $estados = $estado ? [$estado] : ['pendiente', 'aceptada'];

        $paginador = Reserva::query()
            ->where('origen', 'plataforma')
            ->whereNotNull('solicitud_id')
            ->whereIn('estado', $estados)
            ->where(fn ($query) => $query
                ->where('expira_en', '>', now())
                ->orWhere('estado', 'aceptada'),
            )
            ->select('solicitud_id')
            ->selectRaw('MIN(fecha) as fecha_orden')
            ->groupBy('solicitud_id')
            ->orderByDesc('fecha_orden')
            ->paginate(30);

        $solicitudIds = $paginador->getCollection()->pluck('solicitud_id');
        $grupos = Reserva::query()
            ->with('cancha.complejo')
            ->whereIn('solicitud_id', $solicitudIds)
            ->whereIn('estado', $estados)
            ->orderBy('hora_inicio')
            ->get()
            ->groupBy('solicitud_id')
            ->map(function ($horas) {
                $primera = $horas->first();
                $cancha = $primera->cancha;
                $datos = [
                    'nombre_cliente' => $primera->nombre_cliente,
                    'cedula_cliente' => $primera->cedula_cliente,
                    'telefono_cliente' => $primera->telefono_cliente,
                    'fecha' => $primera->fecha->toDateString(),
                    'horas' => $horas->map(fn (Reserva $reserva) => $reserva->hora_inicio)->all(),
                    'observaciones' => $primera->observaciones,
                ];

                return [
                    'solicitud_id' => $primera->solicitud_id,
                    'reserva_id' => $primera->id,
                    'estado' => $primera->estado,
                    'cancha_id' => $cancha->id,
                    'cancha' => $cancha->nombre,
                    'complejo' => $cancha->complejo->nombre,
                    'fecha' => $datos['fecha'],
                    'nombre_cliente' => $primera->nombre_cliente,
                    'cedula_cliente' => $primera->cedula_cliente,
                    'telefono_cliente' => $primera->telefono_cliente,
                    'observaciones' => $primera->observaciones,
                    'expira_en' => $primera->expira_en?->toIso8601String(),
                    'horas' => $horas->map(fn (Reserva $reserva) => [
                        'inicio' => substr($reserva->hora_inicio, 0, 5),
                        'fin' => substr($reserva->hora_fin, 0, 5),
                    ])->values(),
                    'whatsapp_url' => $this->whatsAppLinkService->generarEnlaceSolicitud(
                        $cancha->complejo,
                        $cancha,
                        $datos,
                    ),
                ];
            })
            ->values();

        return response()->json([
            'data' => $grupos,
            'meta' => [
                'current_page' => $paginador->currentPage(),
                'last_page' => $paginador->lastPage(),
                'total' => $paginador->total(),
            ],
        ])->header('Cache-Control', 'no-store, private');
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
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
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

    public function confirmarPago(Reserva $reserva): JsonResponse
    {
        Gate::authorize('gestionar', $reserva->cancha);

        if (!$reserva->solicitud_id) {
            return response()->json(['message' => 'La reserva no pertenece a una solicitud pública.'], 409);
        }

        $reservas = DB::transaction(function () use ($reserva) {
            Cancha::query()
                ->whereKey($reserva->cancha_id)
                ->lockForUpdate()
                ->firstOrFail();

            $aceptadas = Reserva::query()
                ->where('solicitud_id', $reserva->solicitud_id)
                ->where('estado', 'aceptada')
                ->lockForUpdate()
                ->get();

            if ($aceptadas->isEmpty()) {
                abort(409, 'La solicitud debe estar aceptada y pendiente de pago para confirmarse.');
            }

            foreach ($aceptadas as $aceptada) {
                $aceptada->update(['estado' => 'confirmada']);
            }

            return $aceptadas;
        }, attempts: 5);

        broadcast(new DisponibilidadActualizada(
            complejoId: $reserva->cancha->complejo_id,
            canchaId: $reserva->cancha_id,
            fecha: Carbon::parse($reserva->fecha)->toDateString(),
        ));

        return response()->json(['message' => 'Pago confirmado. La cancha quedó reservada.', 'data' => $reservas]);
    }

    public function completar(Reserva $reserva): JsonResponse
    {
        Gate::authorize('gestionar', $reserva->cancha);
        abort_unless(in_array($reserva->estado, ['confirmada', 'completada'], true), 409, 'Solo se pueden completar reservas confirmadas.');
        abort_if(
            Carbon::parse($reserva->fecha)->setTimeFromTimeString($reserva->hora_fin)->isFuture(),
            409,
            'La hora de la reserva todavía no termina.',
        );

        $completadas = DB::transaction(function () use ($reserva) {
            Cancha::query()->whereKey($reserva->cancha_id)->lockForUpdate()->firstOrFail();

            $reservas = $reserva->solicitud_id
                ? Reserva::query()->where('solicitud_id', $reserva->solicitud_id)->lockForUpdate()->get()
                : collect([$reserva]);

            if ($reservas->contains(fn (Reserva $item) => !in_array($item->estado, ['confirmada', 'completada'], true))) {
                abort(409, 'No todas las horas de la solicitud están confirmadas.');
            }

            foreach ($reservas as $item) {
                if ($item->estado === 'confirmada') {
                    $item->update(['estado' => 'completada']);
                }
            }

            return $reservas;
        }, attempts: 5);

        broadcast(new DisponibilidadActualizada(
            complejoId: $reserva->cancha->complejo_id,
            canchaId: $reserva->cancha_id,
            fecha: Carbon::parse($reserva->fecha)->toDateString(),
        ));

        return response()->json(['message' => 'Alquiler marcado como completado.', 'data' => $completadas]);
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
                    'estado' => $decision === 'aceptada' ? 'aceptada' : 'cancelada',
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
            'message' => $decision === 'aceptada' ? 'Solicitud aceptada. Contacta al cliente para coordinar el pago.' : 'Solicitud rechazada.',
            'data' => $reservas,
        ]);
    }
}