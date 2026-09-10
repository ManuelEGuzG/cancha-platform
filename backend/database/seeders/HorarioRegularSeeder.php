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

        foreach ($canchas as $cancha) {
            for ($diaSemana = 0; $diaSemana <= 6; $diaSemana++) {
                HorarioRegular::create([
                    'cancha_id' => $cancha->id,
                    'dia_semana' => $diaSemana,
                    'hora_apertura' => '16:00:00',
                    'hora_cierre' => '23:00:00',
                ]);
            }
        }
    }
}