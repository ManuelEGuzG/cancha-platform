<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cancha_id')->constrained('canchas');
            $table->foreignId('creado_por')->nullable()->constrained('users');
            $table->string('nombre_cliente');
            $table->string('telefono_cliente')->nullable();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada', 'completada', 'no_presentada'])
                ->default('confirmada');
            $table->enum('origen', ['manual', 'plataforma'])->default('manual');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['cancha_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};