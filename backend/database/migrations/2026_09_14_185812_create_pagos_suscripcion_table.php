<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_suscripcion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complejo_id')->constrained('complejos')->cascadeOnDelete();
            $table->foreignId('registrado_por')->nullable()->constrained('users');
            $table->unsignedInteger('monto');
            $table->date('periodo_desde');
            $table->date('periodo_hasta');
            $table->date('fecha_pago');
            $table->string('metodo')->nullable(); // sinpe, transferencia, efectivo...
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('complejo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_suscripcion');
    }
};