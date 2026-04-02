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
        // 1. Limpiar datos inconsistentes antes de crear foreign keys
        $this->limpiarDatosInconsistentes();

        // 2. Añadir foreign key para estado
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->foreign('estado')
                ->references('slug')
                ->on('estados')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        // 3. Añadir foreign key para fase
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->foreign('fase')
                ->references('slug')
                ->on('fase')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Limpiar datos inconsistentes antes de crear foreign keys
     */
    private function limpiarDatosInconsistentes(): void
    {
        // Obtener fases válidas de la tabla fase
        $fasesValidas = DB::table('fase')->pluck('slug')->toArray();

        // Obtener estados válidos de la tabla estados
        $estadosValidos = DB::table('estados')->pluck('slug')->toArray();

        // Actualizar fases que no existen en la tabla fase
        $fasesInvalidas = DB::table('contrataciones')
            ->whereNotIn('fase', $fasesValidas)
            ->whereNotNull('fase')
            ->distinct()
            ->pluck('fase')
            ->toArray();

        if (! empty($fasesInvalidas)) {
            echo 'Fases inválidas encontradas: '.implode(', ', $fasesInvalidas)."\n";

            // Mapear fases inválidas a fases válidas
            $mapeoFases = [
                'solicitud' => 'documentacion', // Mapear solicitud a documentacion
            ];

            foreach ($fasesInvalidas as $faseInvalida) {
                $faseValida = $mapeoFases[$faseInvalida] ?? 'documentacion';

                echo "Mapeando fase '{$faseInvalida}' a '{$faseValida}'\n";

                DB::table('contrataciones')
                    ->where('fase', $faseInvalida)
                    ->update(['fase' => $faseValida]);
            }
        }

        // Actualizar estados que no existen en la tabla estados
        $estadosInvalidos = DB::table('contrataciones')
            ->whereNotIn('estado', $estadosValidos)
            ->whereNotNull('estado')
            ->distinct()
            ->pluck('estado')
            ->toArray();

        if (! empty($estadosInvalidos)) {
            echo 'Estados inválidos encontrados: '.implode(', ', $estadosInvalidos)."\n";

            // Mapear estados inválidos a estados válidos
            $mapeoEstados = [
                // Agregar mapeos según sea necesario
            ];

            foreach ($estadosInvalidos as $estadoInvalido) {
                $estadoValido = $mapeoEstados[$estadoInvalido] ?? 'documentacion';

                echo "Mapeando estado '{$estadoInvalido}' a '{$estadoValido}'\n";

                DB::table('contrataciones')
                    ->where('estado', $estadoInvalido)
                    ->update(['estado' => $estadoValido]);
            }
        }

        // Establecer valores NULL para fases/estados que no se pueden mapear
        DB::table('contrataciones')
            ->whereNotIn('fase', $fasesValidas)
            ->whereNotNull('fase')
            ->update(['fase' => null]);

        DB::table('contrataciones')
            ->whereNotIn('estado', $estadosValidos)
            ->whereNotNull('estado')
            ->update(['estado' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            // Eliminar foreign keys
            $table->dropForeign(['estado']);
            $table->dropForeign(['fase']);
        });
    }
};
