<?php

namespace App\Services;

use App\Events\DisponibilidadActualizada;
use App\Exceptions\HorarioNoDisponibleException;
use App\Models\Bloqueo;
use App\Models\Cancha;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservaService
{
    public function crear(Cancha $cancha, array $datos): Reserva
    {
        $reserva = DB::transaction(function () use ($cancha, $datos) {
            Cancha::query()
                ->whereKey($cancha->getKey())
                ->lockForUpdate()
                ->firstOrFail();

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
                'precio_hora_reservado' => $cancha->precio_hora,
            ]);
        }, attempts: 5);

        broadcast(new DisponibilidadActualizada(
            complejoId: $cancha->complejo_id,
            canchaId: $cancha->id,
            fecha: $reserva->fecha->toDateString(),
        ));

        return $reserva;
    }

    public function crearSolicitud(Cancha $cancha, array $datos): Collection
    {
        $fecha = Carbon::parse($datos['fecha']);
        $solicitudId = (string) Str::uuid();
        $expiraEn = now()->addMinutes(config('reservas.hold_minutes', 15));

        $reservas = DB::transaction(function () use ($cancha, $datos, $fecha, $solicitudId, $expiraEn) {
            $canchaBloqueada = Cancha::query()
                ->whereKey($cancha->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $bloquesDisponibles = app(CalculadorDisponibilidadService::class)
                ->calcularParaCancha($canchaBloqueada, $fecha);

            $horas = collect($datos['horas'])->sort()->values();
            $finAnterior = null;

            foreach ($horas as $hora) {
                $bloque = $bloquesDisponibles->firstWhere('hora_inicio', $hora);

                if (!$bloque || $bloque['estado'] !== 'disponible') {
                    throw new HorarioNoDisponibleException();
                }

                if ($finAnterior !== null && $hora < $finAnterior) {
                    throw new HorarioNoDisponibleException('Las horas seleccionadas se solapan.');
                }

                $finAnterior = Carbon::createFromFormat('H:i', $hora)->addHour()->format('H:i');
            }

            return $horas->map(function (string $hora) use ($canchaBloqueada, $datos, $fecha, $solicitudId, $expiraEn) {
                $horaFin = Carbon::createFromFormat('H:i', $hora)->addHour()->format('H:i');

                return Reserva::create([
                    'cancha_id' => $canchaBloqueada->id,
                    'nombre_cliente' => $datos['nombre_cliente'],
                    'cedula_cliente' => $datos['cedula_cliente'],
                    'telefono_cliente' => $datos['telefono_cliente'],
                    'fecha' => $fecha->toDateString(),
                    'hora_inicio' => $hora,
                    'hora_fin' => $horaFin,
                    'estado' => 'pendiente',
                    'origen' => 'plataforma',
                    'observaciones' => $datos['observaciones'] ?? null,
                    'solicitud_id' => $solicitudId,
                    'expira_en' => $expiraEn,
                    'precio_hora_reservado' => $canchaBloqueada->precio_hora,
                ]);
            });
        }, attempts: 5);

        broadcast(new DisponibilidadActualizada(
            complejoId: $cancha->complejo_id,
            canchaId: $cancha->id,
            fecha: $fecha->toDateString(),
        ));

        return $reservas;
    }

    private function seSolapan(string $inicioA, string $finA, string $inicioB, string $finB): bool
    {
        return $inicioA < $finB && $finA > $inicioB;
    }
}