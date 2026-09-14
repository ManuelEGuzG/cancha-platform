<?php

namespace Database\Seeders;

use App\Models\Cancha;
use App\Models\HorarioRegular;
use Illuminate\Database\Seeder;

class HorarioRegularSeeder extends Seeder
{
    public function run(): void
    {
        $canchas = Cancha::all();

        $configuracionesHorarios = [
            ['apertura' => '06:00:00', 'cierre' => '22:00:00'],
            ['apertura' => '14:00:00', 'cierre' => '23:00:00'],
            ['apertura' => '15:00:00', 'cierre' => '00:00:00'],
            ['apertura' => '08:00:00', 'cierre' => '21:00:00'],
        ];

        foreach ($canchas as $cancha) {
            // Asigna un perfil de horario según el ID de la cancha
            $horario = $configuracionesHorarios[$cancha->id % count($configuracionesHorarios)];

            for ($diaSemana = 0; $diaSemana <= 6; $diaSemana++) {
                HorarioRegular::updateOrCreate(
                    [
                        'cancha_id' => $cancha->id,
                        'dia_semana' => $diaSemana,
                    ],
                    [
                        'hora_apertura' => $horario['apertura'],
                        'hora_cierre' => $horario['cierre'],
                    ]
                );
            }
        }
    }
}