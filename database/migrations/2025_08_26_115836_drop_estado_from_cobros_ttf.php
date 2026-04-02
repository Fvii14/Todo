<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('cobros_ttf', 'estado')) {
            Schema::table('cobros_ttf', function (Blueprint $table) {
                $table->dropColumn('estado');
            });
        }
    }

    public function down(): void
    {
        // Restaurar la columna si hiciera falta (mismo enum que tenías)
        Schema::table('cobros_ttf', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'cobrada'])->default('pendiente')->after('cantidad_comision');
        });
    }
};
