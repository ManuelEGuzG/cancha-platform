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
        $admin = User::create([
            'name' => 'Admin Plataforma',
            'email' => 'admin@canchaplatform.test',
            'password' => 'password', // se hashea automáticamente por el cast 'hashed'
            'is_platform_admin' => true,
        ]);

        $propietario = User::create([
            'name' => 'Propietario Soccer Center',
            'email' => 'propietario@soccercenter.test',
            'password' => 'password',
            'telefono' => '88887777',
        ]);

        $soccerCenter = Complejo::where('slug', 'soccer-center')->firstOrFail();
        $rolPropietario = Rol::where('nombre', Rol::PROPIETARIO)->firstOrFail();

        $propietario->complejos()->attach($soccerCenter->id, [
            'rol_id' => $rolPropietario->id,
        ]);
    }
}