<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Complejo;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EstadisticaController extends Controller
{
    public function resumen(Request $request, Complejo $complejo): JsonResponse
    {
        Gate::authorize('gestionar', $complejo);

        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $canchaIds = $complejo->canchas()->pluck('id');

        $reservasDelMes = Reserva::whereIn('cancha_id', $canchaIds)
            ->whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->with('cancha:id,precio_hora')
            ->get();

        $reservasHoy = Reserva::whereIn('cancha_id', $canchaIds)
            ->whereDate('fecha', Carbon::today()->toDateString())
            ->whereIn('estado', ['pendiente', 'aceptada', 'confirmada'])
            ->count();

        $solicitudes = $reservasDelMes
            ->whereNotNull('solicitud_id')
            ->groupBy('solicitud_id');
        $demandaPorHora = $reservasDelMes
            ->groupBy(fn (Reserva $reserva) => substr($reserva->hora_inicio, 0, 5))
            ->map(fn ($reservas, $hora) => ['hora' => $hora, 'reservas' => $reservas->count()])
            ->sortByDesc('reservas')
            ->take(5)
            ->values();

        return response()->json([
            'data' => [
                'total_canchas' => $complejo->canchas()->where('activa', true)->count(),
                'reservas_hoy' => $reservasHoy,
                'reservas_mes' => [
                    'total' => $reservasDelMes->count(),
                    'confirmadas' => $reservasDelMes->where('estado', 'confirmada')->count(),
                    'canceladas' => $reservasDelMes->where('estado', 'cancelada')->count(),
                    'completadas' => $reservasDelMes->where('estado', 'completada')->count(),
                    'no_presentadas' => $reservasDelMes->where('estado', 'no_presentada')->count(),
                ],
                'solicitudes_mes' => [
                    'recibidas' => $solicitudes->count(),
                    'aceptadas' => $solicitudes->filter(fn ($horas) => $horas->first()->decision_propietario === 'aceptada')->count(),
                    'rechazadas' => $solicitudes->filter(fn ($horas) => $horas->first()->decision_propietario === 'rechazada')->count(),
                    'vencidas' => $solicitudes->filter(fn ($horas) => $horas->first()->decision_propietario === 'expirada')->count(),
                ],
                'demanda_por_hora' => $demandaPorHora,
                'ingreso_estimado_mes' => $this->calcularIngresoEstimado($reservasDelMes),
            ],
        ]);
    }

    private function calcularIngresoEstimado($reservas): int
    {
        return $reservas
            ->whereIn('estado', ['confirmada', 'completada'])
            ->sum(function ($reserva) {
                $cancha = $reserva->cancha;
                $horas = Carbon::parse($reserva->hora_inicio)->diffInHours(Carbon::parse($reserva->hora_fin));

                $precioHora = $reserva->precio_hora_reservado ?? $cancha->precio_hora;

                return $precioHora * $horas;
            });
    }
}