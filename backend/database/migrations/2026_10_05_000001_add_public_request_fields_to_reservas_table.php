<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->text('cedula_cliente')->nullable();
            $table->uuid('solicitud_id')->nullable()->index();
            $table->timestamp('expira_en')->nullable()->index();
            $table->unsignedInteger('precio_hora_reservado')->nullable();
            $table->string('decision_propietario', 20)->nullable();
            $table->index(['cancha_id', 'fecha', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropIndex(['cancha_id', 'fecha', 'estado']);
            $table->dropColumn([
                'cedula_cliente',
                'solicitud_id',
                'expira_en',
                'precio_hora_reservado',
                'decision_propietario',
            ]);
        });
    }
};