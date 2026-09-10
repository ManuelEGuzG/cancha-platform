<?php

namespace Database\Seeders;

use App\Models\Deporte;
use Illuminate\Database\Seeder;

class DeporteSeeder extends Seeder
{
    public function run(): void
    {
        Deporte::create([
            'nombre' => 'Fútbol',
            'slug' => 'futbol',
            'activo' => true,
        ]);

        // Deportes futuros, creados pero inactivos.
        // Se activarán cuando la plataforma expanda (Roadmap V4).
        Deporte::insert([
            ['nombre' => 'Pádel', 'slug' => 'padel', 'activo' => false, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Tenis', 'slug' => 'tenis', 'activo' => false, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Basketball', 'slug' => 'basketball', 'activo' => false, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Volleyball', 'slug' => 'volleyball', 'activo' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}