<?php

namespace App\Console\Commands;

use App\Models\Reserva;
use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;

class PurgarDatosPersonalesReservas extends Command
{
    protected $signature = 'reservas:purgar-datos-personales';
    protected $description = 'Elimina datos personales de reservas fuera del periodo de retención';

    public function handle(): int
    {
        $limite = now()->subDays(config('reservas.pii_retention_days', 365))->toDateString();
        $ids = DB::table('reservas')
            ->whereDate('fecha', '<', $limite)
            ->where(function ($query) {
                $query->whereNotNull('nombre_cliente')
                    ->orWhereNotNull('telefono_cliente')
                    ->orWhereNotNull('cedula_cliente')
                    ->orWhereNotNull('observaciones');
            })
            ->orderBy('id')
            ->limit(1000)
            ->pluck('id');

        if ($ids->isEmpty()) {
            $this->info('No hay datos personales fuera del periodo de retención.');

            return self::SUCCESS;
        }

        $eliminados = DB::table('reservas')->whereIn('id', $ids)->update([
            'nombre_cliente' => null,
            'telefono_cliente' => null,
            'cedula_cliente' => null,
            'observaciones' => null,
            'updated_at' => now(),
        ]);

        $tokensEliminados = 0;

        if ($ids->isNotEmpty()) {
            Activity::query()
                ->where('subject_type', (new Reserva)->getMorphClass())
                ->whereIn('subject_id', $ids)
                ->get()
                ->each(function (Activity $activity) {
                    $activity->properties = $this->eliminarNombreCliente($activity->properties?->toArray() ?? []);
                    $activity->attribute_changes = $this->eliminarNombreCliente($activity->attribute_changes?->toArray() ?? []);
                    $activity->save();
                });
        }

        $this->info("Reservas anonimizadas: {$eliminados}");

        return self::SUCCESS;
    }

    private function eliminarNombreCliente(array $valores): array
    {
        foreach ($valores as $clave => $valor) {
            if ($clave === 'nombre_cliente') {
                unset($valores[$clave]);
            } elseif (is_array($valor)) {
                $valores[$clave] = $this->eliminarNombreCliente($valor);
            }
        }

        return $valores;
    }
}