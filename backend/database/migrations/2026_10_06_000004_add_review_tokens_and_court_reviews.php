<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->char('token_revision_hash', 64)->nullable()->index();
            $table->timestamp('token_revision_expira_en')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['token_revision_hash', 'token_revision_expira_en']);
        });
    }
};