<?php

namespace App\Console\Commands;

use App\Events\DisponibilidadActualizada;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExpirarSolicitudesReserva extends Command
{
    protected $signature = 'reservas:expirar';
    protected $description = 'Libera las solicitudes de reserva que superaron su plazo de respuesta';

    public function handle(): int
    {
        $expiradas = DB::transaction(function () {
            /** @var Collection<int, Reserva> $reservas */
            $reservas = Reserva::query()
                ->with('cancha')
                ->where('estado', 'pendiente')
                ->whereNotNull('solicitud_id')
                ->whereNotNull('expira_en')
                ->where('expira_en', '<=', now())
                ->orderBy('expira_en')
                ->limit(500)
                ->lockForUpdate()
                ->get();

            foreach ($reservas as $reserva) {
                $reserva->update([
                    'estado' => 'cancelada',
                    'decision_propietario' => 'expirada',
                ]);
            }

            return $reservas;
        });

        $expiradas
            ->groupBy(fn (Reserva $reserva) => $reserva->cancha_id.'|'.Carbon::parse($reserva->fecha)->toDateString())
            ->each(function ($reservas) {
                $primera = $reservas->first();
                broadcast(new DisponibilidadActualizada(
                    complejoId: $primera->cancha->complejo_id,
                    canchaId: $primera->cancha_id,
                    fecha: Carbon::parse($primera->fecha)->toDateString(),
                ));
            });

        $this->info('Solicitudes vencidas liberadas: '.$expiradas->count());

        return self::SUCCESS;
    }
}