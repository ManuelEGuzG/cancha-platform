<?php

namespace Database\Seeders;

use App\Models\Canton;
use App\Models\Distrito;
use App\Models\Pais;
use App\Models\Provincia;
use Illuminate\Database\Seeder;

class GeografiaSeeder extends Seeder
{
    public function run(): void
    {
        $costaRica = Pais::create([
            'nombre' => 'Costa Rica',
            'codigo_iso' => 'CR',
        ]);

        $cartago = Provincia::create([
            'pais_id' => $costaRica->id,
            'nombre' => 'Cartago',
        ]);

        $canton = Canton::create([
            'provincia_id' => $cartago->id,
            'nombre' => 'La Unión',
        ]);

        Distrito::create([
            'canton_id' => $canton->id,
            'nombre' => 'San Rafael',
        ]);
    }
}