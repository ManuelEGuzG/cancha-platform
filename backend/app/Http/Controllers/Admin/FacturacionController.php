<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complejo;
use App\Models\PagoSuscripcion;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacturacionController extends Controller
{
    public function resumen(): JsonResponse
    {
        $inicioMes = Carbon::now()->startOfMonth()->toDateString();
        $finMes = Carbon::now()->endOfMonth()->toDateString();
        $hoy = Carbon::today();

        $complejos = Complejo::withCount(['canchas' => fn ($q) => $q->where('activa', true)])->get();

        $resumen = $complejos->map(function ($complejo) use ($inicioMes, $finMes, $hoy) {
            $canchaIds = $complejo->canchas()->pluck('id');

            $reservasMes = Reserva::whereIn('cancha_id', $canchaIds)
                ->whereBetween('fecha', [$inicioMes, $finMes])
                ->whereIn('estado', ['confirmada', 'completada'])
                ->get();

            $ingresoEstimado = $reservasMes->sum(function ($reserva) {
                $cancha = $reserva->cancha;
                $horas = Carbon::parse($reserva->hora_inicio)->diffInHours(Carbon::parse($reserva->hora_fin));
                return $cancha->precio_hora * $horas;
            });

            $vencimiento = $complejo->suscripcion_vence_en;
            $estadoSuscripcion = 'sin_registro';

            if ($vencimiento) {
                $diasParaVencer = $hoy->diffInDays($vencimiento, false);
                if ($diasParaVencer < 0) {
                    $estadoSuscripcion = 'vencida';
                } elseif ($diasParaVencer <= 7) {
                    $estadoSuscripcion = 'por_vencer';
                } else {
                    $estadoSuscripcion = 'al_dia';
                }
            }

            return [
                'complejo_id' => $complejo->id,
                'nombre' => $complejo->nombre,
                'activo' => $complejo->activo,
                'suscripcion_activa' => $complejo->suscripcion_activa,
                'suscripcion_vence_en' => $vencimiento?->toDateString(),
                'estado_suscripcion' => $estadoSuscripcion,
                'total_canchas' => $complejo->canchas_count,
                'reservas_mes' => $reservasMes->count(),
                'ingreso_estimado_mes' => $ingresoEstimado,
            ];
        });

        return response()->json(['data' => $resumen]);
    }

    public function movimientos(Request $request): JsonResponse
    {
        $request->validate([
            'complejo_id' => ['nullable', 'integer', 'exists:complejos,id'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
        ]);

        $reservas = Reserva::with(['cancha.complejo'])
            ->when($request->filled('complejo_id'), function ($q) use ($request) {
                $q->whereHas('cancha', fn ($q2) => $q2->where('complejo_id', $request->integer('complejo_id')));
            })
            ->when($request->filled('fecha_desde'), fn ($q) => $q->whereDate('fecha', '>=', $request->string('fecha_desde')->toString()))
            ->when($request->filled('fecha_hasta'), fn ($q) => $q->whereDate('fecha', '<=', $request->string('fecha_hasta')->toString()))
            ->orderByDesc('fecha')
            ->paginate(50);

        return response()->json(['data' => $reservas]);
    }

    public function registrarPago(Request $request, Complejo $complejo): JsonResponse
    {
        $request->validate([
            'monto' => ['required', 'integer', 'min:0'],
            'periodo_desde' => ['required', 'date'],
            'periodo_hasta' => ['required', 'date', 'after:periodo_desde'],
            'fecha_pago' => ['required', 'date'],
            'metodo' => ['nullable', 'string', 'max:50'],
            'notas' => ['nullable', 'string', 'max:500'],
        ]);

        $pago = PagoSuscripcion::create([
            ...$request->all(),
            'complejo_id' => $complejo->id,
            'registrado_por' => $request->user()->id,
        ]);

        $complejo->update([
            'suscripcion_activa' => true,
            'suscripcion_vence_en' => $request->date('periodo_hasta'),
        ]);

        return response()->json(['data' => $pago], 201);
    }

    public function historialPagos(Complejo $complejo): JsonResponse
    {
        $pagos = $complejo->pagosSuscripcion()->with('registradoPor')->latest('fecha_pago')->get();

        return response()->json(['data' => $pagos]);
    }

    public function toggleEstadoComplejo(Complejo $complejo): JsonResponse
    {
        $complejo->update(['activo' => !$complejo->activo]);

        return response()->json(['data' => $complejo]);
    }
}