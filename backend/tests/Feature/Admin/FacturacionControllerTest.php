<?php

namespace Tests\Feature\Admin;

use App\Models\Canton;
use App\Models\Complejo;
use App\Models\Distrito;
use App\Models\Pais;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacturacionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_registers_payment_and_extends_subscription(): void
    {
        $complejo = $this->crearComplejo();
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $token = $admin->createToken('admin-pago-test')->plainTextToken;

        $this->withToken($token)
            ->postJson("/api/panel/admin/complejos/{$complejo->id}/pagos", [
                'monto' => 30000,
                'periodo_desde' => '2026-10-01',
                'periodo_hasta' => '2026-11-01',
                'fecha_pago' => '2026-10-06',
                'metodo' => 'sinpe',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('pagos_suscripcion', [
            'complejo_id' => $complejo->id,
            'monto' => 30000,
            'metodo' => 'sinpe',
        ]);

        $complejo->refresh();
        $this->assertTrue($complejo->suscripcion_activa);
        $this->assertSame('2026-11-01', $complejo->suscripcion_vence_en->toDateString());
    }

    public function test_admin_toggles_complex_active_state_by_id(): void
    {
        $complejo = $this->crearComplejo();
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $token = $admin->createToken('admin-toggle-test')->plainTextToken;

        $this->assertTrue($complejo->activo);

        $this->withToken($token)
            ->patchJson("/api/panel/admin/complejos/{$complejo->id}/estado")
            ->assertOk()
            ->assertJsonPath('data.activo', false);

        $this->assertFalse($complejo->fresh()->activo);

        $this->withToken($token)
            ->patchJson("/api/panel/admin/complejos/{$complejo->id}/estado")
            ->assertOk()
            ->assertJsonPath('data.activo', true);
    }

    public function test_non_admin_cannot_use_billing_routes(): void
    {
        $complejo = $this->crearComplejo();
        $usuario = User::factory()->create(['is_platform_admin' => false]);
        $token = $usuario->createToken('no-admin-test')->plainTextToken;

        $this->withToken($token)
            ->postJson("/api/panel/admin/complejos/{$complejo->id}/pagos", [
                'monto' => 30000,
                'periodo_desde' => '2026-10-01',
                'periodo_hasta' => '2026-11-01',
                'fecha_pago' => '2026-10-06',
            ])
            ->assertForbidden();

        $this->withToken($token)
            ->patchJson("/api/panel/admin/complejos/{$complejo->id}/estado")
            ->assertForbidden();
    }

    private function crearComplejo(): Complejo
    {
        $pais = Pais::create(['nombre' => 'Costa Rica', 'codigo_iso' => 'CR']);
        $provincia = Provincia::create(['pais_id' => $pais->id, 'nombre' => 'San José']);
        $canton = Canton::create(['provincia_id' => $provincia->id, 'nombre' => 'San José']);
        $distrito = Distrito::create(['canton_id' => $canton->id, 'nombre' => 'Carmen']);

        return Complejo::create([
            'distrito_id' => $distrito->id,
            'nombre' => 'Complejo Facturación',
            'slug' => 'complejo-facturacion',
            'activo' => true,
        ]);
    }
}
