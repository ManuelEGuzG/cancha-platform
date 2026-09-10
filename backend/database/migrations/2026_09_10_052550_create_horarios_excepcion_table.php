<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_excepcion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cancha_id')->constrained('canchas')->cascadeOnDelete();
            $table->date('fecha');
            $table->time('hora_apertura')->nullable(); // null = cerrado todo el día
            $table->time('hora_cierre')->nullable();
            $table->string('motivo')->nullable();
            $table->timestamps();

            $table->index(['cancha_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_excepcion');
    }
};