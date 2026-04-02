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
        Schema::create('estados_contratacion', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // OP1-Documentacion, etc.
            $table->string('grupo')->nullable(); // OP1, OP2...
            $table->timestamps();
        });

        // Insertar los estados OPx iniciales
        $now = now();

        $rows = [
            // OP1
            ['codigo' => 'OP1-Documentacion',       'grupo' => 'OP1'],
            ['codigo' => 'OP1-Tramitacion',         'grupo' => 'OP1'],
            ['codigo' => 'OP1-Alegacion/Aportacion', 'grupo' => 'OP1'],
            ['codigo' => 'OP1-Subsanacion',         'grupo' => 'OP1'],
            ['codigo' => 'OP1-Resolucion',          'grupo' => 'OP1'],
            ['codigo' => 'OP1-Cierre',              'grupo' => 'OP1'],

            // OP2
            ['codigo' => 'OP2-Documentacion',       'grupo' => 'OP2'],
            ['codigo' => 'OP2-PendienteDeCobro',    'grupo' => 'OP2'],
            ['codigo' => 'OP2-Tramitacion',         'grupo' => 'OP2'],
            ['codigo' => 'OP2-Renuncia',            'grupo' => 'OP2'],
            ['codigo' => 'OP2-Cierre',              'grupo' => 'OP2'],

            // OP3
            ['codigo' => 'OP3-Documentacion',       'grupo' => 'OP3'],
            ['codigo' => 'OP3-Tramitacion',         'grupo' => 'OP3'],
            ['codigo' => 'OP3-Cierre',              'grupo' => 'OP3'],

            // OP4
            ['codigo' => 'OP4-Pagando',             'grupo' => 'OP4'],
            ['codigo' => 'OP4-Cobrando',            'grupo' => 'OP4'],
            ['codigo' => 'OP4-Moroso',              'grupo' => 'OP4'],
            ['codigo' => 'OP4-Cobrado',             'grupo' => 'OP4'],

            // OP5
            ['codigo' => 'OP5-Desistido',           'grupo' => 'OP5'],
            ['codigo' => 'OP5-Rechazado',           'grupo' => 'OP5'],
            ['codigo' => 'OP5-FueraDePlazo',        'grupo' => 'OP5'],
        ];

        DB::table('estados_contratacion')->insert(
            array_map(function ($row) use ($now) {
                return array_merge($row, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }, $rows)
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estados_contratacion');
    }
};
