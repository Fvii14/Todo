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
        // 1. Primero actualizar los valores existentes a los nuevos valores
        DB::table('contrataciones')->where('doc_fase', 'Solicitud')->update(['doc_fase' => 'solicitud']);
        DB::table('contrataciones')->where('doc_fase', 'Cotejo')->update(['doc_fase' => 'cotejo']);
        DB::table('contrataciones')->where('doc_fase', 'Validación')->update(['doc_fase' => 'validación']);

        // 2. Crear una nueva columna temporal con los nuevos valores usando raw SQL
        DB::statement("ALTER TABLE contrataciones ADD COLUMN fase_temp ENUM('solicitud', 'cotejo', 'validación', 'en tramitación', 'presentada') DEFAULT 'solicitud' AFTER doc_fase");

        // 3. Copiar los datos de doc_fase a fase_temp
        DB::statement('UPDATE contrataciones SET fase_temp = doc_fase');

        // 4. Eliminar la columna antigua
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->dropColumn('doc_fase');
        });

        // 5. Renombrar la nueva columna usando raw SQL
        DB::statement("ALTER TABLE contrataciones CHANGE fase_temp fase ENUM('solicitud', 'cotejo', 'validación', 'en tramitación', 'presentada') DEFAULT 'solicitud'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Crear una columna temporal con los valores originales usando raw SQL
        DB::statement("ALTER TABLE contrataciones ADD COLUMN doc_fase_temp ENUM('Solicitud', 'Cotejo', 'Validación') DEFAULT 'Solicitud' AFTER fase");

        // 2. Copiar y convertir los datos
        DB::table('contrataciones')->where('fase', 'solicitud')->update(['doc_fase_temp' => 'Solicitud']);
        DB::table('contrataciones')->where('fase', 'cotejo')->update(['doc_fase_temp' => 'Cotejo']);
        DB::table('contrataciones')->where('fase', 'validación')->update(['doc_fase_temp' => 'Validación']);
        DB::table('contrataciones')->where('fase', 'en tramitación')->update(['doc_fase_temp' => 'Solicitud']);
        DB::table('contrataciones')->where('fase', 'presentada')->update(['doc_fase_temp' => 'Solicitud']);

        // 3. Eliminar la columna fase
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->dropColumn('fase');
        });

        // 4. Renombrar la columna temporal usando raw SQL
        DB::statement("ALTER TABLE contrataciones CHANGE doc_fase_temp doc_fase ENUM('Solicitud', 'Cotejo', 'Validación') DEFAULT 'Solicitud'");
    }
};
