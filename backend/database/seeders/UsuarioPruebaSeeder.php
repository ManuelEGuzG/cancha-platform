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
        // Administrador General
        User::updateOrCreate(
            ['email' => 'admin@canchaplatform.test'],
            [
                'name' => 'Admin Plataforma',
                'password' => 'password',
                'is_platform_admin' => true,
            ]
        );

        // Propietario Principal
        $propietario = User::updateOrCreate(
            ['email' => 'propietario@soccercenter.test'],
            [
                'name' => 'Propietario Soccer Center',
                'password' => 'password',
                'telefono' => '88887777',
            ]
        );

        $soccerCenter = Complejo::where('slug', 'soccer-center')->first();
        $rolPropietario = Rol::where('nombre', Rol::PROPIETARIO)->first();

        if ($soccerCenter && $rolPropietario) {
            $propietario->complejos()->syncWithoutDetaching([
                $soccerCenter->id => ['rol_id' => $rolPropietario->id],
            ]);
        }
    }
}