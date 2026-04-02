<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Asegúrate de tener instalado doctrine/dbal para usar change()
        Schema::table('historial_actividad', function (Blueprint $table) {
            $table->unsignedBigInteger('contratacion_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historial_actividad', function (Blueprint $table) {
            $table->unsignedBigInteger('contratacion_id')->nullable(false)->change();
        });
    }
};
