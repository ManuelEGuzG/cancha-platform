<?php

namespace Database\Seeders;

use App\Models\Complejo;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsuarioPruebaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Administrador General
        User::updateOrCreate(
            ['email' => 'admin@canchaplatform.test'],
            [
                'name' => 'Admin Plataforma',
                'password' => bcrypt('password'),
                'is_platform_admin' => true,
            ]
        );

        // 2. Propietario Principal asignado a un complejo garantizado
        $propietario = User::updateOrCreate(
            ['email' => 'propietario@soccercenter.test'],
            [
                'name' => 'Propietario Soccer Center',
                'password' => bcrypt('password'),
                'telefono' => '88887777',
            ]
        );

        $soccerCenter = Complejo::where('slug', 'soccer-center')->firstOrFail();
        $rolPropietario = Rol::where('nombre', Rol::PROPIETARIO)->firstOrFail();

        $propietario->complejos()->syncWithoutDetaching([
            $soccerCenter->id => ['rol_id' => $rolPropietario->id],
        ]);
    }
}