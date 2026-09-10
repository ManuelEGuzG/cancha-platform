<?php

namespace Database\Seeders;

use App\Models\Cancha;
use App\Models\Complejo;
use App\Models\Deporte;
use App\Models\Distrito;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComplejoSeeder extends Seeder
{
    public function run(): void
    {
        $distrito = Distrito::first();
        $futbol = Deporte::where('slug', 'futbol')->firstOrFail();

        $complejos = [
            ['nombre' => 'Soccer Center', 'canchas' => 4, 'precio' => 18000],
            ['nombre' => 'Cancha Oij', 'canchas' => 1, 'precio' => 15000],
            ['nombre' => 'Cancha Coach', 'canchas' => 1, 'precio' => 15000],
            ['nombre' => 'Soccer Conejo', 'canchas' => 1, 'precio' => 16000],
        ];

        foreach ($complejos as $data) {
            $complejo = Complejo::create([
                'distrito_id' => $distrito->id,
                'nombre' => $data['nombre'],
                'slug' => Str::slug($data['nombre']),
                'activo' => true,
                'suscripcion_activa' => true,
            ]);

            for ($i = 1; $i <= $data['canchas']; $i++) {
                Cancha::create([
                    'complejo_id' => $complejo->id,
                    'deporte_id' => $futbol->id,
                    'nombre' => "Cancha {$i}",
                    'precio_hora' => $data['precio'],
                    'activa' => true,
                ]);
            }
        }
    }
}