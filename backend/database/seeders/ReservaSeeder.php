<?php

namespace Database\Seeders;

use App\Models\Cancha;
use App\Models\Reserva;
use Illuminate\Database\Seeder;

class ReservaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('es_CR');
        $canchas = Cancha::all();

        $horasPosibles = ['14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'];
        $estados = ['confirmada', 'confirmada', 'pendiente', 'cancelada'];

        foreach ($canchas as $cancha) {
            $totalReservas = rand(4, 10);

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
                    'origen' => 'manual',
                    'created_at' => now()->addDays($diasOffset - rand(1, 3)),
                ]);
            }
        }
    }
}