<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, crear las nuevas columnas como nullable
        Schema::table('comunicaciones_operativa', function (Blueprint $table) {
            $table->enum('tipo_comunicacion_new', ['WhatsApp', 'Email', 'Llamada'])->nullable()->after('tramitador_id');
            $table->dateTime('fecha_hora_new')->nullable()->after('tipo_comunicacion_new');
            $table->boolean('auto')->default(false)->after('fecha_hora_new');
            $table->string('subject')->nullable()->after('auto');
            $table->enum('direction', ['in', 'out'])->after('subject');
        });

        // Copiar datos existentes a las nuevas columnas con valores por defecto seguros
        DB::statement("UPDATE comunicaciones_operativa SET 
            tipo_comunicacion_new = COALESCE(tipo_comunicacion, 'WhatsApp'),
            fecha_hora_new = COALESCE(fecha_hora, NOW())
        ");

        // Eliminar las columnas antiguas
        Schema::table('comunicaciones_operativa', function (Blueprint $table) {
            $table->dropColumn(['tipo_comunicacion', 'fecha_hora']);
        });

        // Renombrar las nuevas columnas
        Schema::table('comunicaciones_operativa', function (Blueprint $table) {
            $table->renameColumn('tipo_comunicacion_new', 'tipo_comunicacion');
            $table->renameColumn('fecha_hora_new', 'fecha_hora');
        });

        // Hacer las columnas NOT NULL después de asegurar que tienen datos válidos
        Schema::table('comunicaciones_operativa', function (Blueprint $table) {
            $table->enum('tipo_comunicacion', ['WhatsApp', 'Email', 'Llamada'])->nullable(false)->change();
            $table->dateTime('fecha_hora')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comunicaciones_operativa', function (Blueprint $table) {
            $table->dropColumn(['auto', 'subject', 'direction']);
        });

        // Revertir el enum a su estado original
        Schema::table('comunicaciones_operativa', function (Blueprint $table) {
            $table->enum('tipo_comunicacion', ['WhatsApp', 'Llamada'])->change();
        });
    }
};
