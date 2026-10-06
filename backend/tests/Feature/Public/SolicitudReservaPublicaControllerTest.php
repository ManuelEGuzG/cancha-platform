<?php

namespace Tests\Feature\Public;

use App\Models\Cancha;
use App\Models\Canton;
use App\Models\Complejo;
use App\Models\Deporte;
use App\Models\Distrito;
use App\Models\Pais;
use App\Models\Provincia;
use App\Models\Reserva;
use App\Models\Rol;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SolicitudReservaPublicaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_request_holds_multiple_hours_and_returns_whatsapp_link(): void
    {
        [$complejo, $cancha, $fecha] = $this->crearCanchaConHorario();

        $response = $this->postJson("/api/complejos/{$complejo->slug}/solicitudes", [
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Ana Cliente',
            'cedula_cliente' => '123456789',
            'telefono_cliente' => '88888888',
            'fecha' => $fecha,
            'horas' => ['09:00', '10:00'],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.solicitud_id', fn ($id) => is_string($id) && $id !== '')
            ->assertJsonPath('data.whatsapp_url', fn ($url) => str_starts_with($url, 'https://wa.me/50688888888?text='));

        $reservas = Reserva::withoutGlobalScopes()->get();
        $this->assertCount(2, $reservas);
        $this->assertSame(['pendiente'], $reservas->pluck('estado')->unique()->values()->all());
        $this->assertSame(18000, $reservas->first()->precio_hora_reservado);
        $this->assertNotSame('123456789', $reservas->first()->getRawOriginal('cedula_cliente'));
        $this->assertSame($reservas->first()->solicitud_id, $reservas->last()->solicitud_id);
    }

    public function test_public_request_rejects_an_already_occupied_hour(): void
    {
        [$complejo, $cancha, $fecha] = $this->crearCanchaConHorario();
        Reserva::create([
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Reserva existente',
            'fecha' => $fecha,
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'estado' => 'confirmada',
            'origen' => 'manual',
        ]);

        $this->postJson("/api/complejos/{$complejo->slug}/solicitudes", [
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Ana Cliente',
            'cedula_cliente' => '123456789',
            'telefono_cliente' => '88888888',
            'fecha' => $fecha,
            'horas' => ['09:00'],
        ])->assertStatus(409);

        $this->assertCount(1, Reserva::withoutGlobalScopes()->get());
    }

    public function test_expired_public_request_releases_its_hours(): void
    {
        [$complejo, $cancha, $fecha] = $this->crearCanchaConHorario();
        $this->postJson("/api/complejos/{$complejo->slug}/solicitudes", [
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Ana Cliente',
            'cedula_cliente' => '123456789',
            'telefono_cliente' => '88888888',
            'fecha' => $fecha,
            'horas' => ['09:00'],
        ])->assertCreated();

        Carbon::setTestNow(now()->addMinutes(16));
        Artisan::call('reservas:expirar');

        $this->assertDatabaseHas('reservas', [
            'cancha_id' => $cancha->id,
            'estado' => 'cancelada',
            'decision_propietario' => 'expirada',
        ]);

        Carbon::setTestNow();
    }

    public function test_owner_accepts_every_hour_in_a_public_request(): void
    {
        [$complejo, $cancha, $fecha] = $this->crearCanchaConHorario();
        $rol = Rol::create(['nombre' => Rol::PROPIETARIO]);
        $propietario = User::factory()->create();
        $propietario->complejos()->attach($complejo->id, ['rol_id' => $rol->id]);

        $this->postJson("/api/complejos/{$complejo->slug}/solicitudes", [
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Ana Cliente',
            'cedula_cliente' => '123456789',
            'telefono_cliente' => '88888888',
            'fecha' => $fecha,
            'horas' => ['09:00', '10:00'],
        ])->assertCreated();

        $reserva = Reserva::withoutGlobalScopes()->firstOrFail();
        $token = $propietario->createToken('owner-response-test')->plainTextToken;

        $this->withToken($token)
            ->postJson("/api/panel/reservas/{$reserva->id}/aceptar")
            ->assertOk();

        $this->assertSame(
            ['confirmada'],
            Reserva::withoutGlobalScopes()->pluck('estado')->unique()->values()->all(),
        );
        $this->assertSame(
            ['aceptada'],
            Reserva::withoutGlobalScopes()->pluck('decision_propietario')->unique()->values()->all(),
        );
    }

    public function test_owner_rejection_frees_every_hour_in_a_public_request(): void
    {
        [$complejo, $cancha, $fecha] = $this->crearCanchaConHorario();
        $rol = Rol::create(['nombre' => Rol::PROPIETARIO]);
        $propietario = User::factory()->create();
        $propietario->complejos()->attach($complejo->id, ['rol_id' => $rol->id]);

        $this->postJson("/api/complejos/{$complejo->slug}/solicitudes", [
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Ana Cliente',
            'cedula_cliente' => '123456789',
            'telefono_cliente' => '88888888',
            'fecha' => $fecha,
            'horas' => ['09:00', '10:00'],
        ])->assertCreated();

        $reserva = Reserva::withoutGlobalScopes()->firstOrFail();
        $token = $propietario->createToken('owner-response-test')->plainTextToken;

        $this->withToken($token)
            ->postJson("/api/panel/reservas/{$reserva->id}/rechazar")
            ->assertOk();

        $this->assertSame(
            ['cancelada'],
            Reserva::withoutGlobalScopes()->pluck('estado')->unique()->values()->all(),
        );
        $this->assertSame(
            ['rechazada'],
            Reserva::withoutGlobalScopes()->pluck('decision_propietario')->unique()->values()->all(),
        );
        $this->getJson("/api/complejos/{$complejo->slug}/disponibilidad?fecha={$fecha}")
            ->assertOk()
            ->assertJsonPath('data.canchas.0.bloques.0.estado', 'disponible');
    }

    public function test_availability_does_not_offer_partial_or_past_hours(): void
    {
        [$complejo, $cancha, $fecha] = $this->crearCanchaConHorario();
        $cancha->horariosRegulares()->update(['hora_cierre' => '09:30:00']);

        $this->getJson("/api/complejos/{$complejo->slug}/disponibilidad?fecha={$fecha}")
            ->assertOk()
            ->assertJsonPath('data.canchas.0.bloques', []);
    }

    public function test_full_day_exception_is_exposed_as_closed_slots(): void
    {
        [$complejo, $cancha, $fecha] = $this->crearCanchaConHorario();
        DB::table('horarios_excepcion')->insert([
            'cancha_id' => $cancha->id,
            'fecha' => $fecha,
            'hora_apertura' => null,
            'hora_cierre' => null,
            'motivo' => 'Cerrado',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson("/api/complejos/{$complejo->slug}/disponibilidad?fecha={$fecha}")
            ->assertOk()
            ->assertJsonPath('data.canchas.0.bloques.0.estado', 'cerrada')
            ->assertJsonCount(3, 'data.canchas.0.bloques');
    }

    public function test_owner_cannot_block_an_existing_reservation(): void
    {
        [$complejo, $cancha, $fecha] = $this->crearCanchaConHorario();
        $rol = Rol::create(['nombre' => Rol::PROPIETARIO]);
        $propietario = User::factory()->create();
        $propietario->complejos()->attach($complejo->id, ['rol_id' => $rol->id]);
        Reserva::create([
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Reserva existente',
            'fecha' => $fecha,
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'estado' => 'confirmada',
            'origen' => 'manual',
        ]);

        $this->assertDatabaseCount('reservas', 1);

        $token = $propietario->createToken('owner-block-test')->plainTextToken;
        $this->withToken($token)->postJson('/api/panel/bloqueos', [
            'cancha_id' => $cancha->id,
            'fecha' => $fecha,
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'motivo' => 'mantenimiento',
        ])->assertStatus(409);

        $this->assertDatabaseCount('bloqueos', 0);
    }

    public function test_admin_can_review_courts_and_export_monthly_report(): void
    {
        [$complejo, $cancha, $fecha] = $this->crearCanchaConHorario();
        $cancha->update(['estado_verificacion' => 'pendiente']);
        Reserva::create([
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Solicitud aceptada',
            'fecha' => $fecha,
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'estado' => 'confirmada',
            'origen' => 'plataforma',
            'solicitud_id' => '00000000-0000-4000-8000-000000000001',
            'decision_propietario' => 'aceptada',
            'precio_hora_reservado' => 18000,
        ]);
        Reserva::create([
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Solicitud rechazada',
            'fecha' => $fecha,
            'hora_inicio' => '10:00',
            'hora_fin' => '11:00',
            'estado' => 'cancelada',
            'origen' => 'plataforma',
            'solicitud_id' => '00000000-0000-4000-8000-000000000002',
            'decision_propietario' => 'rechazada',
        ]);

        $admin = User::factory()->create(['is_platform_admin' => true]);
        $token = $admin->createToken('admin-report-test')->plainTextToken;
        $mes = Carbon::parse($fecha)->format('Y-m');

        $this->withToken($token)
            ->getJson("/api/panel/admin/canchas/verificacion?estado=pendiente")
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $cancha->id);

        $this->withToken($token)
            ->patchJson("/api/panel/admin/canchas/{$cancha->id}/verificacion", [
                'estado_verificacion' => 'rechazada',
                'observaciones_admin' => 'Falta completar la señalización.',
            ])
            ->assertOk()
            ->assertJsonPath('data.estado_verificacion', 'rechazada');

        $this->withToken($token)
            ->getJson("/api/panel/admin/reportes/mensual?mes={$mes}")
            ->assertOk()
            ->assertJsonPath('data.0.complejo_id', $complejo->id)
            ->assertJsonPath('data.0.solicitudes_recibidas', 2)
            ->assertJsonPath('data.0.aceptadas', 1)
            ->assertJsonPath('data.0.rechazadas', 1)
            ->assertJsonPath('data.0.ingreso_bruto_reservas', 18000);

        $this->withToken($token)
            ->getJson('/api/panel/admin/facturacion/resumen')
            ->assertOk()
            ->assertJsonPath('data.0.reservas_mes', 2)
            ->assertJsonPath('data.0.ingreso_estimado_mes', 18000);

        $complejo->update(['nombre' => '=SUM(1+1)']);
        $exportacion = $this->withToken($token)
            ->get("/api/panel/admin/reportes/mensual/exportar?mes={$mes}");
        $exportacion->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString("'=SUM(1+1)", $exportacion->streamedContent());

        $this->getJson("/api/complejos/{$complejo->slug}/disponibilidad?fecha={$fecha}")
            ->assertOk()
            ->assertJsonPath('data.canchas', []);
    }

    public function test_platform_admin_cannot_modify_court_through_owner_routes(): void
    {
        [, $cancha] = $this->crearCanchaConHorario();
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $token = $admin->createToken('admin-write-boundary-test')->plainTextToken;

        $this->withToken($token)
            ->putJson("/api/panel/canchas/{$cancha->id}", ['nombre' => 'Cambio no autorizado'])
            ->assertForbidden();

        $this->assertDatabaseHas('canchas', ['id' => $cancha->id, 'nombre' => 'Cancha 1']);
    }

    public function test_owner_cannot_access_another_complex_agenda_or_modify_its_court(): void
    {
        [$complejoPropio, $canchaPropia] = $this->crearCanchaConHorario();
        $complejoAjeno = Complejo::create([
            'distrito_id' => $complejoPropio->distrito_id,
            'nombre' => 'Complejo Ajeno',
            'slug' => 'complejo-ajeno',
        ]);
        $canchaAjena = Cancha::create([
            'complejo_id' => $complejoAjeno->id,
            'deporte_id' => $canchaPropia->deporte_id,
            'nombre' => 'Cancha Ajena',
            'precio_hora' => 22000,
        ]);
        $rol = Rol::create(['nombre' => Rol::PROPIETARIO]);
        $propietario = User::factory()->create();
        $propietario->complejos()->attach($complejoPropio->id, ['rol_id' => $rol->id]);
        $token = $propietario->createToken('tenant-isolation-test')->plainTextToken;

        $this->withToken($token)
            ->getJson("/api/panel/complejos/{$complejoAjeno->id}/agenda")
            ->assertForbidden();

        $respuesta = $this->withToken($token)
            ->putJson("/api/panel/canchas/{$canchaAjena->id}", ['nombre' => 'Cancha alterada']);
        $this->assertContains($respuesta->status(), [403, 404]);
        $this->assertDatabaseHas('canchas', ['id' => $canchaAjena->id, 'nombre' => 'Cancha Ajena']);
    }

    public function test_admin_history_filters_by_court_and_omits_customer_contact_details(): void
    {
        [, $cancha, $fecha] = $this->crearCanchaConHorario();
        Reserva::create([
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Ana Cliente',
            'cedula_cliente' => '123456789',
            'telefono_cliente' => '88888888',
            'fecha' => $fecha,
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'estado' => 'confirmada',
            'origen' => 'plataforma',
            'observaciones' => 'Dato que no requiere facturación',
        ]);

        $admin = User::factory()->create(['is_platform_admin' => true]);
        $token = $admin->createToken('admin-history-test')->plainTextToken;
        $this->withToken($token)
            ->getJson("/api/panel/admin/facturacion/movimientos?cancha_id={$cancha->id}")
            ->assertOk()
            ->assertJsonPath('data.data.0.cancha_id', $cancha->id)
            ->assertJsonMissingPath('data.data.0.cedula_cliente')
            ->assertJsonMissingPath('data.data.0.telefono_cliente')
            ->assertJsonMissingPath('data.data.0.observaciones');
    }

    public function test_public_request_rejects_invalid_turnstile_token(): void
    {
        [$complejo, $cancha, $fecha] = $this->crearCanchaConHorario();
        config(['services.turnstile.secret' => 'test-secret']);
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false], 200),
        ]);

        $this->postJson("/api/complejos/{$complejo->slug}/solicitudes", [
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Ana Cliente',
            'cedula_cliente' => '123456789',
            'telefono_cliente' => '88888888',
            'fecha' => $fecha,
            'horas' => ['09:00'],
            'captcha_token' => 'invalid-token',
        ])->assertStatus(422);

        $this->assertDatabaseCount('reservas', 0);
    }

    public function test_public_request_accepts_verified_turnstile_token(): void
    {
        [$complejo, $cancha, $fecha] = $this->crearCanchaConHorario();
        config([
            'services.turnstile.secret' => 'test-secret',
            'services.turnstile.hostname' => 'sportra.example',
        ]);
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
                'hostname' => 'sportra.example',
                'action' => 'reserva',
            ], 200),
        ]);

        $this->postJson("/api/complejos/{$complejo->slug}/solicitudes", [
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Ana Cliente',
            'cedula_cliente' => '123456789',
            'telefono_cliente' => '88888888',
            'fecha' => $fecha,
            'horas' => ['09:00'],
            'captcha_token' => 'verified-token',
        ])->assertCreated();

        Http::assertSent(fn ($request) => $request['secret'] === 'test-secret'
            && $request['response'] === 'verified-token');
    }

    public function test_owner_statistics_include_request_outcomes_demand_and_reserved_price(): void
    {
        [$complejo, $cancha, $fecha] = $this->crearCanchaConHorario();
        $rol = Rol::create(['nombre' => Rol::PROPIETARIO]);
        $propietario = User::factory()->create();
        $propietario->complejos()->attach($complejo->id, ['rol_id' => $rol->id]);
        Reserva::create([
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Solicitud aceptada',
            'fecha' => $fecha,
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'estado' => 'confirmada',
            'origen' => 'plataforma',
            'solicitud_id' => '00000000-0000-4000-8000-000000000001',
            'decision_propietario' => 'aceptada',
            'precio_hora_reservado' => 18000,
        ]);
        Reserva::create([
            'cancha_id' => $cancha->id,
            'nombre_cliente' => 'Solicitud rechazada',
            'fecha' => $fecha,
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'estado' => 'cancelada',
            'origen' => 'plataforma',
            'solicitud_id' => '00000000-0000-4000-8000-000000000002',
            'decision_propietario' => 'rechazada',
        ]);
        $token = $propietario->createToken('owner-statistics-test')->plainTextToken;

        $this->withToken($token)
            ->getJson("/api/panel/complejos/{$complejo->id}/estadisticas")
            ->assertOk()
            ->assertJsonPath('data.solicitudes_mes.recibidas', 2)
            ->assertJsonPath('data.solicitudes_mes.aceptadas', 1)
            ->assertJsonPath('data.solicitudes_mes.rechazadas', 1)
            ->assertJsonPath('data.demanda_por_hora.0.hora', '09:00')
            ->assertJsonPath('data.ingreso_estimado_mes', 18000);
    }

    private function crearCanchaConHorario(): array
    {
        $pais = Pais::create(['nombre' => 'Costa Rica', 'codigo_iso' => 'CR']);
        $provincia = Provincia::create(['pais_id' => $pais->id, 'nombre' => 'San José']);
        $canton = Canton::create(['provincia_id' => $provincia->id, 'nombre' => 'San José']);
        $distrito = Distrito::create(['canton_id' => $canton->id, 'nombre' => 'Carmen']);
        $complejo = Complejo::create([
            'distrito_id' => $distrito->id,
            'nombre' => 'Complejo Prueba',
            'slug' => 'complejo-prueba',
            'whatsapp_numero' => '88888888',
        ]);
        $deporte = Deporte::create(['nombre' => 'Fútbol', 'slug' => 'futbol']);
        $cancha = Cancha::create([
            'complejo_id' => $complejo->id,
            'deporte_id' => $deporte->id,
            'nombre' => 'Cancha 1',
            'precio_hora' => 18000,
        ]);
        $fecha = now()->addDay()->toDateString();

        DB::table('horarios_regulares')->insert([
            'cancha_id' => $cancha->id,
            'dia_semana' => Carbon::parse($fecha)->dayOfWeek,
            'hora_apertura' => '09:00:00',
            'hora_cierre' => '12:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$complejo, $cancha, $fecha];
    }
}