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

        // ===== Provincias =====
        $sanJose = Provincia::create([
            'pais_id' => $costaRica->id,
            'nombre' => 'San José',
        ]);

        $cartago = Provincia::create([
            'pais_id' => $costaRica->id,
            'nombre' => 'Cartago',
        ]);

        $alajuela = Provincia::create([
            'pais_id' => $costaRica->id,
            'nombre' => 'Alajuela',
        ]);

        $heredia = Provincia::create([
            'pais_id' => $costaRica->id,
            'nombre' => 'Heredia',
        ]);

        $guanacaste = Provincia::create([
            'pais_id' => $costaRica->id,
            'nombre' => 'Guanacaste',
        ]);

        $puntarenas = Provincia::create([
            'pais_id' => $costaRica->id,
            'nombre' => 'Puntarenas',
        ]);

        $limon = Provincia::create([
            'pais_id' => $costaRica->id,
            'nombre' => 'Limón',
        ]);

        // ===== Cantones y Distritos =====
        // San José
        $sjCanton = Canton::create([
            'provincia_id' => $sanJose->id,
            'nombre' => 'San José',
        ]);
        Distrito::create(['canton_id' => $sjCanton->id, 'nombre' => 'Carmen']);
        Distrito::create(['canton_id' => $sjCanton->id, 'nombre' => 'Merced']);
        Distrito::create(['canton_id' => $sjCanton->id, 'nombre' => 'Hospital']);

        // Cartago
        $union = Canton::create([
            'provincia_id' => $cartago->id,
            'nombre' => 'La Unión',
        ]);
        Distrito::create(['canton_id' => $union->id, 'nombre' => 'Tres Ríos']);
        Distrito::create(['canton_id' => $union->id, 'nombre' => 'San Juan']);
        Distrito::create(['canton_id' => $union->id, 'nombre' => 'Concepción']);

        // Alajuela
        $alajuelaCanton = Canton::create([
            'provincia_id' => $alajuela->id,
            'nombre' => 'Alajuela',
        ]);
        Distrito::create(['canton_id' => $alajuelaCanton->id, 'nombre' => 'Alajuela']);
        Distrito::create(['canton_id' => $alajuelaCanton->id, 'nombre' => 'San José']);
        Distrito::create(['canton_id' => $alajuelaCanton->id, 'nombre' => 'Carrizal']);

        // Heredia
        $herediaCanton = Canton::create([
            'provincia_id' => $heredia->id,
            'nombre' => 'Heredia',
        ]);
        Distrito::create(['canton_id' => $herediaCanton->id, 'nombre' => 'Heredia']);
        Distrito::create(['canton_id' => $herediaCanton->id, 'nombre' => 'Mercedes']);
        Distrito::create(['canton_id' => $herediaCanton->id, 'nombre' => 'San Francisco']);

        // Guanacaste
        $liberia = Canton::create([
            'provincia_id' => $guanacaste->id,
            'nombre' => 'Liberia',
        ]);
        Distrito::create(['canton_id' => $liberia->id, 'nombre' => 'Liberia']);
        Distrito::create(['canton_id' => $liberia->id, 'nombre' => 'Cañas Dulces']);
        Distrito::create(['canton_id' => $liberia->id, 'nombre' => 'Mayorga']);

        // Puntarenas
        $puntarenasCanton = Canton::create([
            'provincia_id' => $puntarenas->id,
            'nombre' => 'Puntarenas',
        ]);
        Distrito::create(['canton_id' => $puntarenasCanton->id, 'nombre' => 'Puntarenas']);
        Distrito::create(['canton_id' => $puntarenasCanton->id, 'nombre' => 'Chacarita']);
        Distrito::create(['canton_id' => $puntarenasCanton->id, 'nombre' => 'Barranca']);

        // Limón
        $limonCanton = Canton::create([
            'provincia_id' => $limon->id,
            'nombre' => 'Limón',
        ]);
        Distrito::create(['canton_id' => $limonCanton->id, 'nombre' => 'Limón']);
        Distrito::create(['canton_id' => $limonCanton->id, 'nombre' => 'Valle La Estrella']);
        Distrito::create(['canton_id' => $limonCanton->id, 'nombre' => 'Matama']);
    }
}
