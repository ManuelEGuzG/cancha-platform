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
        [$horaApertura, $horaCierre, $cerradaTodoElDia] = $this->resolverHorarioDelDia($cancha, $fecha);

        if (!$horaApertura || !$horaCierre) {
            return collect();
        }

        $bloqueos = $cancha->relationLoaded('bloqueos')
            ? $cancha->getRelation('bloqueos')
            : $cancha->bloqueos()->whereDate('fecha', $fecha->toDateString())->get();

        $reservas = $cancha->relationLoaded('reservas')
            ? $cancha->getRelation('reservas')
            : $cancha->reservas()
                ->whereDate('fecha', $fecha->toDateString())
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->get();

        $bloques = collect();
        $inicio = $fecha->copy()->setTimeFromTimeString($horaApertura);
        $fin = $fecha->copy()->setTimeFromTimeString($horaCierre);

        while ($inicio->copy()->addHour()->lte($fin)) {
            $finBloque = $inicio->copy()->addHour();
            $estado = $cerradaTodoElDia
                ? EstadoDisponibilidad::CERRADA
                : ($fecha->isToday() && $inicio->lte(now())
                ? EstadoDisponibilidad::CERRADA
                : $this->determinarEstado($inicio, $finBloque, $bloqueos, $reservas));

            $bloques->push([
                'hora_inicio' => $inicio->format('H:i'),
                'hora_fin' => $finBloque->format('H:i'),
                'estado' => $estado->value,
            ]);

            $inicio = $finBloque;
        }

        return $bloques;
    }

    /**
    * @return array{0: ?string, 1: ?string, 2: bool}
     */
    private function resolverHorarioDelDia(Cancha $cancha, Carbon $fecha): array
    {
        $excepcion = $cancha->relationLoaded('horariosExcepcion')
            ? $cancha->getRelation('horariosExcepcion')->first()
            : $cancha->horariosExcepcion()->whereDate('fecha', $fecha->toDateString())->first();

        if ($excepcion) {
            if (!$excepcion->hora_apertura || !$excepcion->hora_cierre) {
                $regular = $cancha->relationLoaded('horariosRegulares')
                    ? $cancha->getRelation('horariosRegulares')->firstWhere('dia_semana', $fecha->dayOfWeek)
                    : $cancha->horariosRegulares()->where('dia_semana', $fecha->dayOfWeek)->first();

                return [$regular?->hora_apertura, $regular?->hora_cierre, true];
            }

            return [$excepcion->hora_apertura, $excepcion->hora_cierre, false];
        }

        $regular = $cancha->relationLoaded('horariosRegulares')
            ? $cancha->getRelation('horariosRegulares')->firstWhere('dia_semana', $fecha->dayOfWeek)
            : $cancha->horariosRegulares()->where('dia_semana', $fecha->dayOfWeek)->first();

        if (!$regular) {
            return [null, null, false];
        }

        return [$regular->hora_apertura, $regular->hora_cierre, false];
    }

    private function determinarEstado(
        Carbon $inicioBloque,
        Carbon $finBloque,
        Collection $bloqueos,
        Collection $reservas,
    ): EstadoDisponibilidad {
        foreach ($bloqueos as $bloqueo) {
            if ($this->seSolapan($inicioBloque, $finBloque, $bloqueo->hora_inicio, $bloqueo->hora_fin)) {
                return in_array($bloqueo->motivo, ['mantenimiento', 'reparacion'], true)
                    ? EstadoDisponibilidad::EN_MANTENIMIENTO
                    : EstadoDisponibilidad::CERRADA;
            }
        }

        foreach ($reservas as $reserva) {
            if ($this->seSolapan($inicioBloque, $finBloque, $reserva->hora_inicio, $reserva->hora_fin)) {
                return $reserva->estado === 'pendiente'
                    ? EstadoDisponibilidad::EN_TRAMITE
                    : EstadoDisponibilidad::RESERVADO;
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