<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bloqueos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cancha_id')->constrained('canchas')->cascadeOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->enum('motivo', ['mantenimiento', 'evento', 'reparacion', 'uso_interno', 'otro']);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['cancha_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bloqueos');
    }
};