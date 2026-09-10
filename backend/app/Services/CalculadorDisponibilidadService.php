<?php

namespace App\Services;

use App\Enums\EstadoDisponibilidad;
use App\Models\Cancha;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalculadorDisponibilidadService
{
    public function calcularParaCancha(Cancha $cancha, Carbon $fecha): Collection
    {
        [$horaApertura, $horaCierre] = $this->resolverHorarioDelDia($cancha, $fecha);

        if (!$horaApertura || !$horaCierre) {
            return collect();
        }

        $bloqueos = $cancha->bloqueos()
            ->whereDate('fecha', $fecha->toDateString())
            ->get();

        $reservas = $cancha->reservas()
            ->whereDate('fecha', $fecha->toDateString())
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->get();

        $bloques = collect();
        $inicio = $fecha->copy()->setTimeFromTimeString($horaApertura);
        $fin = $fecha->copy()->setTimeFromTimeString($horaCierre);

        while ($inicio->lt($fin)) {
            $finBloque = $inicio->copy()->addHour();

            $bloques->push([
                'hora_inicio' => $inicio->format('H:i'),
                'hora_fin' => $finBloque->format('H:i'),
                'estado' => $this->determinarEstado($inicio, $finBloque, $bloqueos, $reservas)->value,
            ]);

            $inicio = $finBloque;
        }

        return $bloques;
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function resolverHorarioDelDia(Cancha $cancha, Carbon $fecha): array
    {
        $excepcion = $cancha->horariosExcepcion()
            ->whereDate('fecha', $fecha->toDateString())
            ->first();

        if ($excepcion) {
            return [$excepcion->hora_apertura, $excepcion->hora_cierre];
        }

        $regular = $cancha->horariosRegulares()
            ->where('dia_semana', $fecha->dayOfWeek)
            ->first();

        if (!$regular) {
            return [null, null];
        }

        return [$regular->hora_apertura, $regular->hora_cierre];
    }

    private function determinarEstado(
        Carbon $inicioBloque,
        Carbon $finBloque,
        Collection $bloqueos,
        Collection $reservas,
    ): EstadoDisponibilidad {
        foreach ($bloqueos as $bloqueo) {
            if ($this->seSolapan($inicioBloque, $finBloque, $bloqueo->hora_inicio, $bloqueo->hora_fin)) {
                return EstadoDisponibilidad::BLOQUEADA;
            }
        }

        foreach ($reservas as $reserva) {
            if ($this->seSolapan($inicioBloque, $finBloque, $reserva->hora_inicio, $reserva->hora_fin)) {
                return $reserva->estado === 'pendiente'
                    ? EstadoDisponibilidad::PENDIENTE
                    : EstadoDisponibilidad::OCUPADA;
            }
        }

        return EstadoDisponibilidad::DISPONIBLE;
    }

    /**
     * Compara si el bloque [inicioBloque, finBloque) se solapa con
     * un rango [horaInicioStr, horaFinStr) guardado como string "HH:MM:SS".
     */
    private function seSolapan(Carbon $inicioBloque, Carbon $finBloque, string $horaInicioStr, string $horaFinStr): bool
    {
        $inicioRango = $inicioBloque->copy()->setTimeFromTimeString($horaInicioStr);
        $finRango = $inicioBloque->copy()->setTimeFromTimeString($horaFinStr);

        return $inicioBloque->lt($finRango) && $finBloque->gt($inicioRango);
    }
}