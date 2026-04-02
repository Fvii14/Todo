<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $servicios = [
            [
                'nombre' => 'Preparación completa de la solicitud',
                'descripcion' => 'Preparación completa y detallada de toda la documentación necesaria para la solicitud',
                'icono' => 'fas fa-file-alt',
                'color' => '#10b981', // Verde
                'orden' => 1,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Revisión de documentos',
                'descripcion' => 'Revisión exhaustiva de todos los documentos para asegurar su correcta presentación',
                'icono' => 'fas fa-check-circle',
                'color' => '#10b981', // Verde
                'orden' => 2,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Presentación de la ayuda',
                'descripcion' => 'Presentación formal de la solicitud ante el organismo correspondiente',
                'icono' => 'fas fa-paper-plane',
                'color' => '#10b981', // Verde
                'orden' => 3,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Seguimiento del proceso',
                'descripcion' => 'Seguimiento continuo del estado de la solicitud y gestión de trámites adicionales',
                'icono' => 'fas fa-clipboard-check',
                'color' => '#10b981', // Verde
                'orden' => 4,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar solo si no existen ya
        foreach ($servicios as $servicio) {
            $exists = DB::table('servicios')
                ->where('nombre', $servicio['nombre'])
                ->exists();

            if (!$exists) {
                DB::table('servicios')->insert($servicio);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $nombres = [
            'Preparación completa de la solicitud',
            'Revisión de documentos',
            'Presentación de la ayuda',
            'Seguimiento del proceso',
        ];

        DB::table('servicios')
            ->whereIn('nombre', $nombres)
            ->delete();
    }
};
