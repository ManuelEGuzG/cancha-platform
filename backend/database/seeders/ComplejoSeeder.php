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
        $distritos = Distrito::all();
        $futbol = Deporte::where('slug', 'futbol')->first();
        $padel = Deporte::where('slug', 'padel')->first();
        $tenis = Deporte::where('slug', 'tenis')->first();

        $complejosBase = [
            [
                'nombre' => 'Soccer Center',
                'direccion' => '50m Este del Parque Central',
                'lat' => 9.9333,
                'lng' => -84.0833,
                'telefono' => '2222-1111',
                'canchas' => [
                    ['nombre' => 'Cancha 1 (Fútbol 5)', 'deporte' => $futbol, 'precio' => 18000],
                    ['nombre' => 'Cancha 2 (Fútbol 5)', 'deporte' => $futbol, 'precio' => 18000],
                    ['nombre' => 'Cancha 3 (Fútbol 7)', 'deporte' => $futbol, 'precio' => 25000],
                    ['nombre' => 'Cancha 4 (Fútbol 8)', 'deporte' => $futbol, 'precio' => 30000],
                ]
            ],
            [
                'nombre' => 'Padel & Tennis Club',
                'direccion' => 'Costado Norte del Estadio',
                'lat' => 9.9833,
                'lng' => -84.1333,
                'telefono' => '2430-5555',
                'canchas' => [
                    ['nombre' => 'Pádel Glass 1', 'deporte' => $padel, 'precio' => 16000],
                    ['nombre' => 'Pádel Glass 2', 'deporte' => $padel, 'precio' => 16000],
                    ['nombre' => 'Cancha Tenis Arcilla', 'deporte' => $tenis, 'precio' => 14000],
                ]
            ],
            [
                'nombre' => 'Cancha Oij',
                'direccion' => 'Frente a las oficinas principales',
                'lat' => 9.8667,
                'lng' => -83.9167,
                'telefono' => '2551-9999',
                'canchas' => [
                    ['nombre' => 'Sintética Sintética 5', 'deporte' => $futbol, 'precio' => 15000],
                ]
            ],
            [
                'nombre' => 'Soccer Conejo',
                'direccion' => '100m Sur de la Iglesia',
                'lat' => 10.0167,
                'lng' => -84.2167,
                'telefono' => '2441-2233',
                'canchas' => [
                    ['nombre' => 'La Conejera (Techada)', 'deporte' => $futbol, 'precio' => 16000],
                ]
            ],
        ];

        foreach ($complejosBase as $index => $data) {
            $distrito = $distritos->random();

            $complejo = Complejo::create([
                'distrito_id' => $distrito->id,
                'nombre' => $data['nombre'],
                'slug' => Str::slug($data['nombre']),
                'descripcion' => "Complejo deportivo de alto rendimiento ubicado en {$distrito->nombre}.",
                'direccion_texto' => $data['direccion'],
                'latitud' => $data['lat'],
                'longitud' => $data['lng'],
                'telefono' => $data['telefono'],
                'whatsapp_numero' => '5068' . rand(1000000, 9999999),
                'activo' => true,
                'suscripcion_activa' => true,
            ]);

            foreach ($data['canchas'] as $canchaData) {
                Cancha::create([
                    'complejo_id' => $complejo->id,
                    'deporte_id' => $canchaData['deporte']?->id ?? $futbol->id,
                    'nombre' => $canchaData['nombre'],
                    'precio_hora' => $canchaData['precio'],
                    'activa' => true,
                ]);
            }
        }
    }
}