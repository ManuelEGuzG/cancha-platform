<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        Rol::create(['nombre' => Rol::PROPIETARIO]);
        Rol::create(['nombre' => Rol::ENCARGADO]);
    }
}