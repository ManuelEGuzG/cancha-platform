<?php

namespace Tests\Feature\Public;

use App\Models\Canton;
use App\Models\Complejo;
use App\Models\Distrito;
use App\Models\Pais;
use App\Models\Provincia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplejoPublicoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_complejos_by_provincia_and_returns_provincia_name(): void
    {
        $pais = Pais::create(['nombre' => 'Costa Rica', 'codigo_iso' => 'CR']);
        $provinciaSanJose = Provincia::create(['pais_id' => $pais->id, 'nombre' => 'San José']);
        $provinciaAlajuela = Provincia::create(['pais_id' => $pais->id, 'nombre' => 'Alajuela']);
        $cantonSanJose = Canton::create(['provincia_id' => $provinciaSanJose->id, 'nombre' => 'San José']);
        $cantonAlajuela = Canton::create(['provincia_id' => $provinciaAlajuela->id, 'nombre' => 'Alajuela']);
        $distritoSanJose = Distrito::create(['canton_id' => $cantonSanJose->id, 'nombre' => 'Carmen']);
        $distritoAlajuela = Distrito::create(['canton_id' => $cantonAlajuela->id, 'nombre' => 'Alajuela']);

        Complejo::create([
            'distrito_id' => $distritoSanJose->id,
            'nombre' => 'Complejo San José',
            'slug' => 'complejo-san-jose',
            'latitud' => 9.9333,
            'longitud' => -84.0833,
        ]);
        Complejo::create([
            'distrito_id' => $distritoAlajuela->id,
            'nombre' => 'Complejo Alajuela',
            'slug' => 'complejo-alajuela',
        ]);

        $response = $this->getJson('/api/complejos?provincia_id='.$provinciaSanJose->id);

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Complejo San José')
            ->assertJsonPath('data.0.provincia', 'San José')
            ->assertJsonPath('data.0.latitud', '9.9333000')
            ->assertJsonPath('data.0.longitud', '-84.0833000');
    }
}
