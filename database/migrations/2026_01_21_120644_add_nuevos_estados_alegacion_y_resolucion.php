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
        // Insertar nuevos estados
        $estados = [
            [
                'nombre' => 'Alegación/Aportación',
                'slug' => 'alegacion-aportacion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Resolución',
                'slug' => 'resolucion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Subsanación',
                'slug' => 'subsanacion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Justificación',
                'slug' => 'justificacion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Solicitud',
                'slug' => 'solicitud',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Renuncia',
                'slug' => 'renuncia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Recurso de reposición',
                'slug' => 'recurso-reposicion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('estados')->insert($estados);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar los estados añadidos
        DB::table('estados')->whereIn('slug', [
            'alegacion-aportacion',
            'resolucion',
            'subsanacion',
            'justificacion',
            'solicitud',
            'renuncia',
            'recurso-reposicion',
        ])->delete();
    }
};
