<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->decimal('monto_ayuda_original', 10, 2)
                ->nullable()
                ->after('monto_total_ayuda')
                ->comment('Importe original concedido en resolución; no se modifica posteriormente');
        });

        // Backfill opcional: copia el total actual como “original” si existe y aún es NULL
        DB::statement('
            UPDATE contrataciones
            SET monto_ayuda_original = monto_total_ayuda
            WHERE monto_ayuda_original IS NULL AND monto_total_ayuda IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->dropColumn('monto_ayuda_original');
        });
    }
};
