<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE reservas MODIFY estado ENUM(
            'pendiente',
            'aceptada',
            'confirmada',
            'completada',
            'cancelada',
            'rechazada',
            'expirada'
        ) NOT NULL DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        // Ajusta a los valores originales de tu enum si necesitas revertir
    }
};