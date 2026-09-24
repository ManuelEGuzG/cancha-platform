<?php

namespace Database\Seeders;

use App\Models\Bloqueo;
use App\Models\Cancha;
use App\Models\Complejo;
use App\Models\Deporte;
use App\Models\Distrito;
use App\Models\HorarioRegular;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComplejoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('es_CR');
        $distritos = Distrito::all();
        $deportes = Deporte::where('activo', true)->get();
        $rolPropietario = Rol::where('nombre', Rol::PROPIETARIO)->firstOrFail();

        $futbol = $deportes->where('slug', 'futbol')->first() ?? $deportes->first();
        $padel = $deportes->where('slug', 'padel')->first() ?? $deportes->first();
        $tenis = $deportes->where('slug', 'tenis')->first() ?? $deportes->first();

        // 1. Complejos Base Fijos con Slug explícito
        $complejosBase = [
            [
                'nombre' => 'Soccer Center',
                'slug' => 'soccer-center',
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
                'slug' => 'padel-tennis-club',
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
        ];

        foreach ($complejosBase as $index => $data) {
            $distrito = $distritos->random();

            $complejo = Complejo::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'distrito_id' => $distrito->id,
                    'nombre' => $data['nombre'],
                    'descripcion' => "Complejo deportivo ubicado en {$distrito->nombre}.",
                    'direccion_texto' => $data['direccion'],
                    'latitud' => $data['lat'],
                    'longitud' => $data['lng'],
                    'telefono' => $data['telefono'],
                    'whatsapp_numero' => '5068' . rand(1000000, 9999999),
                    'activo' => true,
                    'suscripcion_activa' => true,
                ]
            );

            foreach ($data['canchas'] as $canchaData) {
                $cancha = Cancha::create([
                    'complejo_id' => $complejo->id,
                    'deporte_id' => $canchaData['deporte']->id,
                    'nombre' => $canchaData['nombre'],
                    'precio_hora' => $canchaData['precio'],
                    'activa' => true,
                ]);

                $this->crearHorariosPorCancha($cancha->id);
            }
        }

        // 2. Complejos de Prueba Aleatorios
        $nombresDinamicos = [
            'Cancha La Trinidad', 'Polideportivo San Miguel', 'Estadio Los Ángeles',
            'Cancha El Roble', 'Sporting Club Tres Ríos', 'Cancha Municipal Concepción',
            'Complejo La Aurora', 'Cancha Río Grande', 'Polideportivo Dulce Nombre',
            'Fútbol 5 La Cima', 'Complejo Deportivo Tejar', 'Padel Zone Escazú'
        ];

        foreach ($nombresDinamicos as $index => $nombre) {
            $distrito = $distritos->random();
            $slug = Str::slug($nombre) . '-' . ($index + 100);

            $complejo = Complejo::create([
                'distrito_id' => $distrito->id,
                'nombre' => $nombre,
                'slug' => $slug,
                'descripcion' => $faker->paragraph(2),
                'direccion_texto' => $faker->address(),
                'latitud' => $faker->latitude(9.8, 10.1),
                'longitud' => $faker->longitude(-84.2, -83.8),
                'telefono' => '2' . $faker->numerify('###-####'),
                'whatsapp_numero' => '506' . $faker->randomElement(['8', '6', '7']) . $faker->numerify('#######'),
                'activo' => true,
                'suscripcion_activa' => $faker->boolean(85),
            ]);

            $totalCanchas = rand(2, 4);

            for ($i = 1; $i <= $totalCanchas; $i++) {
                $deporte = $deportes->random();
                
                $cancha = Cancha::create([
                    'complejo_id' => $complejo->id,
                    'deporte_id' => $deporte->id,
                    'nombre' => "{$deporte->nombre} {$i}",
                    'precio_hora' => $faker->randomElement([12000, 15000, 18000, 22000]),
                    'activa' => true,
                ]);

                $this->crearHorariosPorCancha($cancha->id, $faker);

                if ($faker->boolean(30)) {
                    Bloqueo::create([
                        'cancha_id' => $cancha->id,
                        'fecha' => now()->addDays(rand(-2, 7))->toDateString(),
                        'hora_inicio' => $faker->randomElement(['14:00', '16:00', '18:00']),
                        'hora_fin' => $faker->randomElement(['16:00', '18:00', '20:00']),
                        'motivo' => $faker->randomElement(['mantenimiento', 'evento', 'reparacion']),
                        'notas' => $faker->optional(0.7)->sentence(),
                    ]);
                }
            }

            // Crear y asociar propietario
            $propietario = User::create([
                'name' => $faker->name(),
                'email' => 'propietario' . ($index + 1) . '@test.com',
                'password' => bcrypt('password'),
                'telefono' => '506' . $faker->randomElement(['8', '6', '7']) . $faker->numerify('#######'),
            ]);

            $propietario->complejos()->attach($complejo->id, ['rol_id' => $rolPropietario->id]);
        }
    }

    private function crearHorariosPorCancha(int $canchaId, $faker = null): void
    {
        $apertura = $faker ? $faker->randomElement(['06:00:00', '08:00:00', '14:00:00']) : '06:00:00';
        $cierre = $faker ? $faker->randomElement(['22:00:00', '23:00:00', '23:59:00']) : '22:00:00';

        for ($dia = 0; $dia <= 6; $dia++) {
            HorarioRegular::create([
                'cancha_id' => $canchaId,
                'dia_semana' => $dia,
                'hora_apertura' => $apertura,
                'hora_cierre' => $cierre,
            ]);
        }
    }
}