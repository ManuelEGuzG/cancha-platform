<?php

namespace App\Services;

use App\Enums\EstadoDisponibilidad;
use App\Models\Cancha;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalculadorDisponibilidadService
{
    /**
     * Genera los bloques de disponibilidad de una cancha para una fecha dada,
     * en intervalos de una hora, dentro del horario de operación.
     */
    public function calcularParaCancha(Cancha $cancha, Carbon $fecha): Collection
    {
        [$horaApertura, $horaCierre] = $this->resolverHorarioDelDia($cancha, $fecha);

        if (!$horaApertura || !$horaCierre) {
            return collect(); // la cancha no opera ese día
        }

        $bloques = collect();
        $inicio = $fecha->copy()->setTimeFromTimeString($horaApertura);
        $fin = $fecha->copy()->setTimeFromTimeString($horaCierre);

        while ($inicio->lt($fin)) {
            $finBloque = $inicio->copy()->addHour();

            $bloques->push([
                'hora_inicio' => $inicio->format('H:i'),
                'hora_fin' => $finBloque->format('H:i'),
                'estado' => $this->determinarEstado($cancha, $inicio, $finBloque)->value,
            ]);

            $inicio = $finBloque;
        }

        return $bloques;
    }

    /**
     * Determina el horario de apertura/cierre de una cancha para un día específico,
     * dando prioridad a excepciones (feriados, eventos) sobre el horario regular.
     *
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

    /**
     * Determina el estado de un bloque de hora específico.
     *
     * NOTA: en esta fase todavía no existen `reservas` ni `bloqueos`,
     * así que siempre devuelve DISPONIBLE dentro del horario de operación.
     * Este método se completará en la fase de RESERVAS con las consultas reales.
     */
    private function determinarEstado(Cancha $cancha, Carbon $inicio, Carbon $fin): EstadoDisponibilidad
    {
        return EstadoDisponibilidad::DISPONIBLE;
    }
}