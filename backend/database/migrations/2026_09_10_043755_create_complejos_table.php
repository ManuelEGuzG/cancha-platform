<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complejos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distrito_id')->constrained('distritos');
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();
            $table->string('direccion_texto')->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->string('telefono')->nullable();
            $table->string('whatsapp_numero')->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('suscripcion_activa')->default(false);
            $table->timestamps();

            $table->index('distrito_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complejos');
    }
};