<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complejos', function (Blueprint $table) {
            $table->date('suscripcion_vence_en')->nullable()->after('suscripcion_activa');
        });
    }

    public function down(): void
    {
        Schema::table('complejos', function (Blueprint $table) {
            $table->dropColumn('suscripcion_vence_en');
        });
    }
};