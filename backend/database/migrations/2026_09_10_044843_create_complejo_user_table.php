<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complejo_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complejo_id')->constrained('complejos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rol_id')->constrained('roles');
            $table->timestamps();

            $table->unique(['complejo_id', 'user_id']); // un usuario, un rol por complejo
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complejo_user');
    }
};