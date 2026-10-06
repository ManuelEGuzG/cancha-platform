<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complejo;
use App\Models\PagoSuscripcion;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturacionController extends Controller
{
    public function resumen(): JsonResponse
    {
        $inicioMes = Carbon::now()->startOfMonth()->toDateString();
        $finMes = Carbon::now()->endOfMonth()->toDateString();
        $hoy = Carbon::today();

        $complejos = Complejo::withCount(['canchas' => fn ($query) => $query->where('activa', true)])
            ->orderBy('nombre')
            ->get();
        $totales = DB::table('reservas')
            ->join('canchas', 'reservas.cancha_id', '=', 'canchas.id')
            ->whereDate('reservas.fecha', '>=', $inicioMes)
            ->whereDate('reservas.fecha', '<=', $finMes)
            ->groupBy('canchas.complejo_id')
            ->select('canchas.complejo_id')
            ->selectRaw('COUNT(*) as reservas_mes')
            ->get()
            ->keyBy('complejo_id');
        $ingresos = [];

        Reserva::query()
            ->with('cancha:id,complejo_id,precio_hora')
            ->whereDate('fecha', '>=', $inicioMes)
            ->whereDate('fecha', '<=', $finMes)
            ->whereIn('estado', ['confirmada', 'completada'])
            ->chunkById(500, function ($reservas) use (&$ingresos) {
                foreach ($reservas as $reserva) {
                    $cancha = $reserva->cancha;
                    $precioHora = $reserva->precio_hora_reservado ?? $cancha->precio_hora;
                    $minutos = Carbon::parse($reserva->hora_inicio)->diffInMinutes(Carbon::parse($reserva->hora_fin));
                    $ingresos[$cancha->complejo_id] = ($ingresos[$cancha->complejo_id] ?? 0)
                        + (int) round($precioHora * $minutos / 60);
                }
            });

        $resumen = $complejos->map(function ($complejo) use ($hoy, $totales, $ingresos) {
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
                'reservas_mes' => (int) ($totales->get($complejo->id)->reservas_mes ?? 0),
                'ingreso_estimado_mes' => $ingresos[$complejo->id] ?? 0,
            ];
        });

        return response()->json(['data' => $resumen]);
    }

    public function movimientos(Request $request): JsonResponse
    {
        $request->validate([
            'complejo_id' => ['nullable', 'integer', 'exists:complejos,id'],
            'cancha_id' => ['nullable', 'integer', 'exists:canchas,id'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
        ]);

        $reservas = Reserva::query()
            ->select(['id', 'cancha_id', 'nombre_cliente', 'fecha', 'hora_inicio', 'hora_fin', 'estado', 'origen', 'created_at'])
            ->with(['cancha.complejo'])
            ->when($request->filled('complejo_id'), function ($q) use ($request) {
                $q->whereHas('cancha', fn ($q2) => $q2->where('complejo_id', $request->integer('complejo_id')));
            })
            ->when($request->filled('cancha_id'), fn ($q) => $q->where('cancha_id', $request->integer('cancha_id')))
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