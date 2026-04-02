<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arrendatarios', function (Blueprint $table) {
            $table->dropColumn(['tipo_persona', 'documento_identidad', 'nombre_completo_razon_social']);
        });
    }

    public function down(): void
    {
        Schema::table('arrendatarios', function (Blueprint $table) {
            $table->enum('tipo_persona', ['Fisica', 'Juridica']);
            $table->string('documento_identidad', 50);
            $table->string('nombre_completo_razon_social', 255);
        });
    }
};
