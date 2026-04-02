<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transiciones', function (Blueprint $table) {
            // Agregar campo para el tipo de ayuda
            $table->string('ayuda', 255)->nullable()->collation('utf8mb4_general_ci');

            // Índice para mejorar consultas por ayuda
            $table->index('ayuda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transiciones', function (Blueprint $table) {
            $table->dropIndex(['ayuda']);
            $table->dropColumn('ayuda');
        });
    }
};
