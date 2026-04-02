<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motivos_rechazo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        // Insertar motivos por defecto requeridos en producción
        DB::table('motivos_rechazo')->insert([
            ['nombre' => 'Otro', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Tramitación independiente', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Desistimiento', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('motivos_rechazo');
    }
};
