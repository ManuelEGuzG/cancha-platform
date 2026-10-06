<?php

namespace Tests\Feature\Privacy;

use App\Models\Cancha;
use App\Models\Canton;
use App\Models\Complejo;
use App\Models\Deporte;
use App\Models\Distrito;
use App\Models\Pais;
use App\Models\Provincia;
use App\Models\Reserva;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Spatie\Activitylog\Models\Activity;

class ReservationPiiRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_reservations_are_anonymized_but_history_is_preserved(): void
    {
        config(['reservas.pii_retention_days' => 365]);
        [, $cancha] = $this->crearCancha();
        $antigua = Reserva::create([
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Cliente antiguo',
            'cedula_cliente' => '123456789',
            'telefono_cliente' => '88888888',
            'fecha' => now()->subDays(366)->toDateString(),
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'estado' => 'completada',
            'origen' => 'plataforma',
            'observaciones' => 'Nota personal',
        ]);
        $antigua->update(['nombre_cliente' => 'Nombre guardado en evento', 'estado' => 'completada']);
        $reciente = Reserva::create([
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Cliente reciente',
            'cedula_cliente' => '987654321',
            'telefono_cliente' => '87777777',
            'fecha' => now()->subDays(364)->toDateString(),
            'hora_inicio' => '10:00',
            'hora_fin' => '11:00',
            'estado' => 'confirmada',
            'origen' => 'plataforma',
        ]);

        Artisan::call('reservas:purgar-datos-personales');

        $this->assertDatabaseHas('reservas', [
            'id' => $antigua->id,
            'cancha_id' => $cancha->id,
            'estado' => 'completada',
            'nombre_cliente' => null,
            'telefono_cliente' => null,
            'cedula_cliente' => null,
            'observaciones' => null,
        ]);
        $this->assertDatabaseHas('reservas', [
            'id' => $reciente->id,
            'nombre_cliente' => 'Cliente reciente',
            'telefono_cliente' => '87777777',
        ]);

        $actividad = Activity::forSubject($antigua)->latest()->first();
        $this->assertNotNull($actividad);
        $this->assertStringNotContainsString('Nombre guardado en evento', json_encode([
            $actividad->properties?->toArray(),
            $actividad->attribute_changes?->toArray(),
        ]));
    }

    private function crearCancha(): array
    {
        $pais = Pais::create(['nombre' => 'Costa Rica', 'codigo_iso' => 'CR']);
        $provincia = Provincia::create(['pais_id' => $pais->id, 'nombre' => 'San José']);
        $canton = Canton::create(['provincia_id' => $provincia->id, 'nombre' => 'San José']);
        $distrito = Distrito::create(['canton_id' => $canton->id, 'nombre' => 'Carmen']);
        $complejo = Complejo::create([
            'distrito_id' => $distrito->id,
            'nombre' => 'Complejo Retención',
            'slug' => 'complejo-retencion',
        ]);
        $deporte = Deporte::create(['nombre' => 'Fútbol', 'slug' => 'futbol']);
        $cancha = Cancha::create([
            'complejo_id' => $complejo->id,
            'deporte_id' => $deporte->id,
            'nombre' => 'Cancha Retención',
            'precio_hora' => 18000,
        ]);

        return [$complejo, $cancha];
    }
}