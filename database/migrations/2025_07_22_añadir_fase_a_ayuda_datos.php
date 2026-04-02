<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ayuda_datos', function (Blueprint $table) {
            $table->enum('fase', [
                'documentación',
                'tramitación',
                'resolución',
                'rechazada',
                'subsanación',
                'concesión',
            ])->after('tipo_dato')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ayuda_datos', function (Blueprint $table) {
            $table->dropColumn('fase');
        });
    }
};
