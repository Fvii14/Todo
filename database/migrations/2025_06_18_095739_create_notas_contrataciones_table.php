<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotasContratacionesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notas_contrataciones', function (Blueprint $table) {
            $table->id();
            // clave foránea a contrataciones.id
            $table->foreignId('contratacion_id')
                ->constrained('contrataciones')
                ->onDelete('cascade');
            // clave foránea a users.id
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            // campo de texto para la nota
            $table->text('nota');
            // solo created_at
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_contrataciones');
    }
}
