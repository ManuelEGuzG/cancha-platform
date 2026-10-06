<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cancha;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteMensualController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $mes = $this->validarMes($request);

        return response()->json([
            'mes' => $mes,
            'data' => $this->construirReporte($mes),
        ]);
    }

    public function exportar(Request $request): StreamedResponse
    {
        $mes = $this->validarMes($request);
        $reporte = $this->construirReporte($mes);

        return response()->streamDownload(function () use ($reporte, $mes) {
            $salida = fopen('php://output', 'w');
            fputcsv($salida, ['Mes', 'Complejo', 'Cancha', 'Solicitudes recibidas', 'Aceptadas', 'Rechazadas', 'Vencidas', 'Horas confirmadas', 'Ingreso bruto referencial'], ';');

            foreach ($reporte as $fila) {
                fputcsv($salida, [
                    $mes,
                    $this->protegerFormulaCsv($fila['complejo']),
                    $fila['cancha'],
                    $fila['solicitudes_recibidas'],
                    $fila['aceptadas'],
                    $fila['rechazadas'],
                    $fila['vencidas'],
                    $fila['horas_confirmadas'],
                    $fila['ingreso_bruto_reservas'],
                ], ';');
            }

            fclose($salida);
        }, "sportra-reporte-{$mes}.csv", [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validarMes(Request $request): string
    {
        $request->merge(['mes' => $request->input('mes', now()->format('Y-m'))]);

        return $request->validate([
            'mes' => ['required', 'date_format:Y-m'],
        ])['mes'];
    }

    private function construirReporte(string $mes): array
    {
        $inicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
        $fin = $inicio->copy()->endOfMonth();
        $reporte = Cancha::withoutGlobalScopes()
            ->with('complejo:id,nombre')
            ->orderBy('nombre')
            ->get(['id', 'complejo_id', 'nombre'])
            ->mapWithKeys(fn (Cancha $cancha) => [$cancha->id => [
                'cancha_id' => $cancha->id,
                'cancha' => $cancha->nombre,
                'complejo_id' => $cancha->complejo_id,
                'complejo' => $cancha->complejo->nombre,
                'solicitudes_recibidas' => 0,
                'aceptadas' => 0,
                'rechazadas' => 0,
                'vencidas' => 0,
                'horas_confirmadas' => 0,
                'ingreso_bruto_reservas' => 0,
            ]])
            ->all();

        $totales = DB::table('reservas')
            ->join('canchas', 'reservas.cancha_id', '=', 'canchas.id')
            ->join('complejos', 'canchas.complejo_id', '=', 'complejos.id')
            ->whereDate('reservas.fecha', '>=', $inicio->toDateString())
            ->whereDate('reservas.fecha', '<=', $fin->toDateString())
            ->where('reservas.origen', 'plataforma')
            ->groupBy('canchas.id', 'canchas.nombre', 'complejos.id', 'complejos.nombre')
            ->select('canchas.id as cancha_id', 'complejos.id as complejo_id', 'complejos.nombre as complejo', 'canchas.nombre as cancha')
            ->selectRaw('COUNT(DISTINCT reservas.solicitud_id) as solicitudes_recibidas')
            ->selectRaw("COUNT(DISTINCT CASE WHEN reservas.decision_propietario = 'aceptada' THEN reservas.solicitud_id END) as aceptadas")
            ->selectRaw("COUNT(DISTINCT CASE WHEN reservas.decision_propietario = 'rechazada' THEN reservas.solicitud_id END) as rechazadas")
            ->selectRaw("COUNT(DISTINCT CASE WHEN reservas.decision_propietario = 'expirada' THEN reservas.solicitud_id END) as vencidas")
            ->selectRaw("SUM(CASE WHEN reservas.estado IN ('confirmada', 'completada') THEN 1 ELSE 0 END) as horas_confirmadas")
            ->get();

        foreach ($totales as $total) {
            if (isset($reporte[$total->cancha_id])) {
                foreach (['solicitudes_recibidas', 'aceptadas', 'rechazadas', 'vencidas', 'horas_confirmadas'] as $campo) {
                    $reporte[$total->cancha_id][$campo] = (int) $total->{$campo};
                }
            }
        }

        Reserva::query()
            ->with('cancha:id,complejo_id,precio_hora')
            ->whereDate('fecha', '>=', $inicio->toDateString())
            ->whereDate('fecha', '<=', $fin->toDateString())
            ->where('origen', 'plataforma')
            ->whereIn('estado', ['confirmada', 'completada'])
            ->chunkById(500, function ($reservas) use (&$reporte) {
                foreach ($reservas as $reserva) {
                    $canchaId = $reserva->cancha_id;
                    if (!isset($reporte[$canchaId])) {
                        continue;
                    }

                    $precioHora = $reserva->precio_hora_reservado ?? $reserva->cancha->precio_hora;
                    $minutos = Carbon::parse($reserva->hora_inicio)->diffInMinutes(Carbon::parse($reserva->hora_fin));
                    $reporte[$canchaId]['ingreso_bruto_reservas'] += (int) round($precioHora * $minutos / 60);
                }
            });

        return array_values($reporte);
    }

    private function protegerFormulaCsv(string $valor): string
    {
        return preg_match('/^[\x00-\x20]*[=+\-@]/', $valor) ? "'{$valor}" : $valor;
    }
}