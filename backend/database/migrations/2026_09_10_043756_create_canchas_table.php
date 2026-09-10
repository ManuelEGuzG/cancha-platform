<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canchas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complejo_id')->constrained('complejos')->cascadeOnDelete();
            $table->foreignId('deporte_id')->constrained('deportes');
            $table->string('nombre'); // "Cancha 1", "Cancha 2"...
            $table->unsignedInteger('precio_hora'); // en colones, sin decimales
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index('complejo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canchas');
    }
};