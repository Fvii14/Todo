<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade la columna codigo_hubspot (Ref. HS) a ayudas para identificar de forma
     * sencilla y rápida desde el código en HubSpot (ej: A1P-UNK-25, BAJ-And-25).
     * Nombre explícito para no confundir con otros "codigo" (p. ej. estados_contratacion).
     */
    public function up(): void
    {
        Schema::table('ayudas', function (Blueprint $table) {
            $table->string('codigo_hubspot', 50)->nullable()->after('nombre_ayuda');
        });
    }

    public function down(): void
    {
        Schema::table('ayudas', function (Blueprint $table) {
            $table->dropColumn('codigo_hubspot');
        });
    }
};
