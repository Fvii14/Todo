<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ayuda_datos MODIFY `fase` ENUM(
            'documentación',
            'tramitación',
            'resolución',
            'rechazada',
            'subsanación',
            'concesión',
            'concedida'
        )");

        DB::table('ayuda_datos')
            ->where('fase', 'concesión')
            ->update(['fase' => 'concedida']);

        DB::statement("ALTER TABLE ayuda_datos MODIFY `fase` ENUM(
            'documentación',
            'tramitación',
            'resolución',
            'rechazada',
            'subsanación',
            'concedida'
        )");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE ayuda_datos MODIFY `fase` ENUM(
            'documentación',
            'tramitación',
            'resolución',
            'rechazada',
            'subsanación',
            'concesión',
            'concedida'
        )");

        DB::table('ayuda_datos')
            ->where('fase', 'concedida')
            ->update(['fase' => 'concesión']);

        DB::statement("ALTER TABLE ayuda_datos MODIFY `fase` ENUM(
            'documentación',
            'tramitación',
            'resolución',
            'rechazada',
            'subsanación',
            'concesión'
        )");
    }
};
