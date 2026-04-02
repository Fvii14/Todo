<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insertar tareas del 3 de septiembre de 2025 en adelante
        $tareas = [
            [
                'nombre' => 'Solicitud',
                'slug' => 'solicitud',
                'descripcion' => 'Tarea de solicitud de ayuda',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Cotejo',
                'slug' => 'cotejo',
                'descripcion' => 'Tarea de cotejo de documentación',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Elaborar…',
                'slug' => 'elaborar',
                'descripcion' => 'Tarea de elaboración de documentación',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Tramitación',
                'slug' => 'tramitacion',
                'descripcion' => 'Tarea de tramitación',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Registro',
                'slug' => 'registro',
                'descripcion' => 'Tarea de registro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar solo si no existen
        foreach ($tareas as $tarea) {
            DB::table('tareas')->updateOrInsert(
                ['slug' => $tarea['slug']],
                $tarea
            );
        }

        // Insertar opciones de tareas del 3 de septiembre de 2025 en adelante
        $opcionesTareas = [
            // Opciones para Solicitud
            [
                'nombre' => 'Documentación',
                'descripcion' => 'Opción de documentación para solicitud',
                'tarea' => 'solicitud',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Seguimiento',
                'descripcion' => 'Opción de seguimiento para solicitud',
                'tarea' => 'solicitud',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Subsanación',
                'descripcion' => 'Opción de subsanación para solicitud',
                'tarea' => 'solicitud',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Justificación',
                'descripcion' => 'Opción de justificación para solicitud',
                'tarea' => 'solicitud',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Cobro',
                'descripcion' => 'Opción de cobro para solicitud',
                'tarea' => 'solicitud',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Reseña',
                'descripcion' => 'Opción de reseña para solicitud',
                'tarea' => 'solicitud',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Feedback',
                'descripcion' => 'Opción de feedback para solicitud',
                'tarea' => 'solicitud',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Opciones para Cotejo
            [
                'nombre' => 'Documentación',
                'descripcion' => 'Opción de documentación para cotejo',
                'tarea' => 'cotejo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Seguimiento',
                'descripcion' => 'Opción de seguimiento para cotejo',
                'tarea' => 'cotejo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Subsanación',
                'descripcion' => 'Opción de subsanación para cotejo',
                'tarea' => 'cotejo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Justificación',
                'descripcion' => 'Opción de justificación para cotejo',
                'tarea' => 'cotejo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Cobro',
                'descripcion' => 'Opción de cobro para cotejo',
                'tarea' => 'cotejo',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Opciones para Elaborar…
            [
                'nombre' => 'Documentación',
                'descripcion' => 'Opción de documentación para elaborar',
                'tarea' => 'elaborar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Seguimiento',
                'descripcion' => 'Opción de seguimiento para elaborar',
                'tarea' => 'elaborar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Subsanación',
                'descripcion' => 'Opción de subsanación para elaborar',
                'tarea' => 'elaborar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Justificación',
                'descripcion' => 'Opción de justificación para elaborar',
                'tarea' => 'elaborar',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Opciones para Tramitación
            [
                'nombre' => 'Solicitud',
                'descripcion' => 'Opción de solicitud para tramitación',
                'tarea' => 'tramitacion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Subsanación',
                'descripcion' => 'Opción de subsanación para tramitación',
                'tarea' => 'tramitacion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Justificación',
                'descripcion' => 'Opción de justificación para tramitación',
                'tarea' => 'tramitacion',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Opciones para Registro
            [
                'nombre' => 'Subsanación',
                'descripcion' => 'Opción de subsanación para registro',
                'tarea' => 'registro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Resolución',
                'descripcion' => 'Opción de resolución para registro',
                'tarea' => 'registro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Pago',
                'descripcion' => 'Opción de pago para registro',
                'tarea' => 'registro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar solo si no existen
        foreach ($opcionesTareas as $opcion) {
            DB::table('opciones_tareas')->updateOrInsert(
                [
                    'nombre' => $opcion['nombre'],
                    'tarea' => $opcion['tarea'],
                ],
                $opcion
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar opciones de tareas primero (por las foreign keys)
        DB::table('opciones_tareas')->whereIn('tarea', [
            'solicitud', 'cotejo', 'elaborar', 'tramitacion', 'registro',
        ])->delete();

        // Eliminar tareas
        DB::table('tareas')->whereIn('slug', [
            'solicitud', 'cotejo', 'elaborar', 'tramitacion', 'registro',
        ])->delete();
    }
};
