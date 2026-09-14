<?php

namespace Database\Seeders;

use App\Models\Deporte;
use Illuminate\Database\Seeder;

class DeporteSeeder extends Seeder
{
    public function run(): void
    {
        $deportes = [
            ['nombre' => 'Fútbol', 'slug' => 'futbol', 'activo' => true],
            ['nombre' => 'Pádel', 'slug' => 'padel', 'activo' => true],
            ['nombre' => 'Tenis', 'slug' => 'tenis', 'activo' => true],
            ['nombre' => 'Basketball', 'slug' => 'basketball', 'activo' => true],
            ['nombre' => 'Volleyball', 'slug' => 'volleyball', 'activo' => false],
        ];

        foreach ($deportes as $deporte) {
            Deporte::updateOrCreate(['slug' => $deporte['slug']], $deporte);
        }
    }
}