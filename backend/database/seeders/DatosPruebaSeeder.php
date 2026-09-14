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
        $distritos = Distrito::all();
        $deportes = Deporte::where('activo', true)->get();
        $rolPropietario = Rol::where('nombre', Rol::PROPIETARIO)->firstOrFail();

        $nombresComplejos = [
            'Cancha La Trinidad', 'Polideportivo San Miguel', 'Estadio Los Ángeles',
            'Cancha El Roble', 'Sporting Club Tres Ríos', 'Cancha Municipal Concepción',
            'Complejo La Aurora', 'Cancha Río Grande', 'Polideportivo Dulce Nombre',
            'Cancha El Guarco', 'Fútbol 5 La Cima', 'Complejo Deportivo Tejar',
            'Cancha Los Pinos', 'Cancha Villa Real', 'Polideportivo San Rafael Norte',
            'Padel Zone Escazú', 'Arena Metropolitana', 'Plaza Sports Curridabat'
        ];

        foreach ($nombresComplejos as $index => $nombre) {
            $distrito = $distritos->random();

            $complejo = Complejo::create([
                'distrito_id' => $distrito->id,
                'nombre' => $nombre,
                'slug' => Str::slug($nombre) . '-' . ($index + 100),
                'descripcion' => $faker->paragraph(2),
                'direccion_texto' => $faker->address(),
                'latitud' => $faker->latitude(9.8, 10.1),
                'longitud' => $faker->longitude(-84.2, -83.8),
                'telefono' => '2' . $faker->numerify('###-####'),
                'whatsapp_numero' => '506' . $faker->randomElement(['8', '6', '7']) . $faker->numerify('#######'),
                'activo' => true,
                'suscripcion_activa' => $faker->boolean(85),
            ]);

            $totalCanchas = rand(2, 5);

            for ($i = 1; $i <= $totalCanchas; $i++) {
                $deporte = $deportes->random();
                
                $cancha = Cancha::create([
                    'complejo_id' => $complejo->id,
                    'deporte_id' => $deporte->id,
                    'nombre' => "{$deporte->nombre} {$i}",
                    'precio_hora' => $faker->randomElement([12000, 15000, 16000, 18000, 22000, 25000]),
                    'activa' => true,
                ]);

                // Horarios por cancha
                $apertura = $faker->randomElement(['06:00', '14:00', '15:00']);
                $cierre = $faker->randomElement(['22:00', '23:00', '23:59']);

                for ($dia = 0; $dia <= 6; $dia++) {
                    HorarioRegular::create([
                        'cancha_id' => $cancha->id,
                        'dia_semana' => $dia,
                        'hora_apertura' => $apertura,
                        'hora_cierre' => $cierre,
                    ]);
                }

                // Generar reservas pasadas, actuales y futuras
                $this->crearReservasMasivas($cancha, $faker);

                // Bloqueos ocasionales
                if ($faker->boolean(40)) {
                    Bloqueo::create([
                        'cancha_id' => $cancha->id,
                        'fecha' => now()->addDays(rand(-2, 7))->toDateString(),
                        'hora_inicio' => $faker->randomElement(['14:00', '16:00', '18:00']),
                        'hora_fin' => $faker->randomElement(['16:00', '18:00', '20:00']),
                        'motivo' => $faker->randomElement(['mantenimiento', 'evento', 'reparacion', 'uso_interno']),
                        'notas' => $faker->optional(0.7)->sentence(),
                    ]);
                }
            }

            // Propietarios de prueba
            $propietario = User::create([
                'name' => $faker->name(),
                'email' => 'propietario' . ($index + 1) . '@test.com',
                'password' => 'password',
                'telefono' => $faker->phoneNumber(),
            ]);

            $propietario->complejos()->attach($complejo->id, ['rol_id' => $rolPropietario->id]);
        }
    }

    private function crearReservasMasivas(Cancha $cancha, $faker): void
    {
        $horasPosibles = ['14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'];
        $estados = ['confirmada', 'confirmada', 'pendiente', 'cancelada'];

        $totalReservas = rand(5, 12);

        for ($i = 0; $i < $totalReservas; $i++) {
            $diasOffset = rand(-10, 10);
            $fecha = now()->addDays($diasOffset)->toDateString();
            $horaInicio = $faker->randomElement($horasPosibles);
            $horaFin = date('H:i', strtotime($horaInicio) + 3600);

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
                'telefono_cliente' => '506' . $faker->randomElement(['8', '6', '7']) . $faker->numerify('#######'),
                'fecha' => $fecha,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'estado' => $faker->randomElement($estados),
                'origen' => 'manual', // Fijado estrictamente a 'manual' para evitar conflictos de truncado
                'created_at' => now()->addDays($diasOffset - rand(1, 3)),
            ]);
        }
    }
}