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
        // Insertar estados
        $estados = [
            [
                'nombre' => 'Documentación',
                'slug' => 'documentacion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Tramitación',
                'slug' => 'tramitacion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Cierre',
                'slug' => 'cierre',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('estados')->insert($estados);

        // Insertar fases
        $fases = [
            // Fases para Documentación
            [
                'nombre' => 'Documentación',
                'slug' => 'documentacion',
                'estado' => 'documentacion',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Fases para Tramitación
            [
                'nombre' => 'En seguimiento',
                'slug' => 'en_seguimiento',
                'estado' => 'tramitacion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Apertura',
                'slug' => 'apertura',
                'estado' => 'tramitacion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Presentada',
                'slug' => 'presentada',
                'estado' => 'tramitacion',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Fases para Cierre
            [
                'nombre' => 'Resolución',
                'slug' => 'resolucion',
                'estado' => 'cierre',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Rechazada',
                'slug' => 'rechazada',
                'estado' => 'cierre',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('fase')->insert($fases);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar fases primero (por las foreign keys)
        DB::table('fase')->whereIn('estado', [
            'documentacion', 'tramitacion', 'cierre',
        ])->delete();

        // Eliminar estados
        DB::table('estados')->whereIn('slug', [
            'documentacion', 'tramitacion', 'cierre',
        ])->delete();
    }
};
