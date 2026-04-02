<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE questions 
            MODIFY categoria ENUM(
                'vivienda',
                'deudas',
                'datos-economicos',
                'convivientes',
                'grupo-vulnerable',
                'datos-personales',
                'hijos',
                'arrendador'
            ) NOT NULL"
        );
    }

    public function down(): void
    {
        // Volver al enum anterior sin 'arrendador'
        DB::statement("ALTER TABLE questions 
            MODIFY categoria ENUM(
                'vivienda',
                'deudas',
                'datos-economicos',
                'convivientes',
                'grupo-vulnerable',
                'datos-personales',
                'hijos'
            ) NOT NULL"
        );
    }
};
