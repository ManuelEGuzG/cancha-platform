<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cancha_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cancha_id')->constrained('canchas')->cascadeOnDelete();
            $table->string('disk', 40);
            $table->string('path', 500)->unique();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->string('caption', 160)->nullable();
            $table->enum('estado_verificacion', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->text('observaciones_admin')->nullable();
            $table->foreignId('verificado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verificado_en')->nullable();
            $table->timestamps();
            $table->index(['cancha_id', 'estado_verificacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancha_fotos');
    }
};