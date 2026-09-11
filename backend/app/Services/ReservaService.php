<?php

namespace App\Services;

use App\Events\DisponibilidadActualizada;
use App\Exceptions\HorarioNoDisponibleException;
use App\Models\Bloqueo;
use App\Models\Cancha;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReservaService
{
    public function crear(Cancha $cancha, array $datos): Reserva
    {
        $reserva = DB::transaction(function () use ($cancha, $datos) {
            $fecha = Carbon::parse($datos['fecha'])->toDateString();
            $horaInicio = $datos['hora_inicio'];
            $horaFin = $datos['hora_fin'];

            $reservasExistentes = Reserva::where('cancha_id', $cancha->id)
                ->whereDate('fecha', $fecha)
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->lockForUpdate()
                ->get();

            $bloqueosExistentes = Bloqueo::where('cancha_id', $cancha->id)
                ->whereDate('fecha', $fecha)
                ->lockForUpdate()
                ->get();

            foreach ($reservasExistentes as $reserva) {
                if ($this->seSolapan($horaInicio, $horaFin, $reserva->hora_inicio, $reserva->hora_fin)) {
                    throw new HorarioNoDisponibleException();
                }
            }

            foreach ($bloqueosExistentes as $bloqueo) {
                if ($this->seSolapan($horaInicio, $horaFin, $bloqueo->hora_inicio, $bloqueo->hora_fin)) {
                    throw new HorarioNoDisponibleException('Este horario está bloqueado.');
                }
            }

            return Reserva::create([
                'cancha_id' => $cancha->id,
                'creado_por' => $datos['creado_por'] ?? null,
                'nombre_cliente' => $datos['nombre_cliente'],
                'telefono_cliente' => $datos['telefono_cliente'] ?? null,
                'fecha' => $fecha,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'estado' => $datos['estado'] ?? 'confirmada',
                'origen' => $datos['origen'] ?? 'manual',
                'observaciones' => $datos['observaciones'] ?? null,
            ]);
        });

        broadcast(new DisponibilidadActualizada(
            complejoId: $cancha->complejo_id,
            canchaId: $cancha->id,
            fecha: $reserva->fecha->toDateString(),
        ));

        return $reserva;
    }

    private function seSolapan(string $inicioA, string $finA, string $inicioB, string $finB): bool
    {
        return $inicioA < $finB && $finA > $inicioB;
    }
}