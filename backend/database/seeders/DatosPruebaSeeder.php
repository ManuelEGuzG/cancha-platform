<?php

namespace Database\Seeders;

use App\Models\Bloqueo;
use App\Models\Cancha;
use App\Models\Complejo;
use App\Models\Deporte;
use App\Models\Distrito;
use App\Models\HorarioRegular;
use App\Models\Reserva;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('es_CR');
        $distrito = Distrito::first();
        $futbol = Deporte::where('slug', 'futbol')->firstOrFail();
        $rolPropietario = Rol::where('nombre', Rol::PROPIETARIO)->firstOrFail();

        $nombresComplejos = [
            'Cancha La Trinidad', 'Polideportivo San Miguel', 'Estadio Los Ángeles',
            'Cancha El Roble', 'Sporting Club Tres Ríos', 'Cancha Municipal Concepción',
            'Complejo La Aurora', 'Cancha Río Grande', 'Polideportivo Dulce Nombre',
            'Cancha El Guarco', 'Fútbol 5 La Cima', 'Complejo Deportivo Tejar',
            'Cancha Los Pinos', 'Cancha Villa Real', 'Polideportivo San Rafael Norte',
        ];

        foreach ($nombresComplejos as $index => $nombre) {
            $complejo = Complejo::create([
                'distrito_id' => $distrito->id,
                'nombre' => $nombre,
                'slug' => Str::slug($nombre) . '-' . ($index + 100), // evita choque de slugs
                'descripcion' => $faker->sentence(12),
                'direccion_texto' => $faker->address(),
                'latitud' => $faker->latitude(9.8, 10.0),
                'longitud' => $faker->longitude(-84.1, -83.9),
                'telefono' => '2' . $faker->numerify('###-####'),
                'whatsapp_numero' => '506' . $faker->numerify('########'),
                'activo' => true,
                'suscripcion_activa' => $faker->boolean(85), // 85% activas, algunas vencidas
            ]);

            // Entre 1 y 4 canchas por complejo
            $totalCanchas = rand(1, 4);

            for ($i = 1; $i <= $totalCanchas; $i++) {
                $cancha = Cancha::create([
                    'complejo_id' => $complejo->id,
                    'deporte_id' => $futbol->id,
                    'nombre' => "Cancha {$i}",
                    'precio_hora' => $faker->randomElement([12000, 14000, 15000, 16000, 18000, 20000]),
                    'activa' => true,
                ]);

                // Horario regular: todos los días, con pequeñas variaciones
                $apertura = $faker->randomElement(['14:00', '15:00', '16:00']);
                $cierre = $faker->randomElement(['21:00', '22:00', '23:00']);

                for ($dia = 0; $dia <= 6; $dia++) {
                    HorarioRegular::create([
                        'cancha_id' => $cancha->id,
                        'dia_semana' => $dia,
                        'hora_apertura' => $apertura,
                        'hora_cierre' => $cierre,
                    ]);
                }

                // Reservas de prueba: algunas hoy, mañana y próximos días
                $this->crearReservasDePrueba($cancha, $faker);

                // Algún bloqueo ocasional (30% de probabilidad por cancha)
                if ($faker->boolean(30)) {
                    Bloqueo::create([
                        'cancha_id' => $cancha->id,
                        'fecha' => now()->addDays(rand(0, 5))->toDateString(),
                        'hora_inicio' => $faker->randomElement(['16:00', '17:00', '18:00']),
                        'hora_fin' => $faker->randomElement(['18:00', '19:00', '20:00']),
                        'motivo' => $faker->randomElement(['mantenimiento', 'evento', 'reparacion', 'uso_interno']),
                        'notas' => $faker->optional()->sentence(),
                    ]);
                }
            }

            // Usuario propietario para este complejo (útil para probar login con varios)
            $email = 'propietario' . ($index + 1) . '@test.com';
            $propietario = User::create([
                'name' => $faker->name(),
                'email' => $email,
                'password' => 'password',
                'telefono' => $faker->phoneNumber(),
            ]);

            $propietario->complejos()->attach($complejo->id, ['rol_id' => $rolPropietario->id]);
        }
    }

    private function crearReservasDePrueba(Cancha $cancha, $faker): void
    {
        $horasPosibles = ['16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00'];

        // Entre 0 y 3 reservas por cancha, repartidas en los próximos 5 días
        $totalReservas = rand(0, 3);

        for ($i = 0; $i < $totalReservas; $i++) {
            $fecha = now()->addDays(rand(0, 5))->toDateString();
            $horaInicio = $faker->randomElement($horasPosibles);
            $horaFin = date('H:i', strtotime($horaInicio) + 3600);

            // Evita duplicar el mismo horario en la misma cancha/fecha
            $yaExiste = Reserva::where('cancha_id', $cancha->id)
                ->where('fecha', $fecha)
                ->where('hora_inicio', $horaInicio)
                ->exists();

            if ($yaExiste) {
                continue;
            }

            Reserva::create([
                'cancha_id' => $cancha->id,
                'nombre_cliente' => $faker->name(),
                'telefono_cliente' => $faker->numerify('8-###-####'),
                'fecha' => $fecha,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'estado' => $faker->randomElement(['confirmada', 'confirmada', 'confirmada', 'pendiente']),
                'origen' => 'manual',
            ]);
        }
    }
}