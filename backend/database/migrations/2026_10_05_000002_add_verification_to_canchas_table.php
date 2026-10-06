<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            $table->string('estado_verificacion', 20)->default('aprobada');
            $table->text('observaciones_admin')->nullable();
            $table->foreignId('verificado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verificado_en')->nullable();
            $table->index(['estado_verificacion', 'activa']);
        });
    }

    public function down(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            $table->dropIndex(['estado_verificacion', 'activa']);
            $table->dropConstrainedForeignId('verificado_por');
            $table->dropColumn(['estado_verificacion', 'observaciones_admin', 'verificado_en']);
        });
    }
};