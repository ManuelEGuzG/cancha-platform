<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('nombre_cliente')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('reservas')->whereNull('nombre_cliente')->update(['nombre_cliente' => '[datos eliminados]']);

        Schema::table('reservas', function (Blueprint $table) {
            $table->string('nombre_cliente')->nullable(false)->change();
        });
    }
};