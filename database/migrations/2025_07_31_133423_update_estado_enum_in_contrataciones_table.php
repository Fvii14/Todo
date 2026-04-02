<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        DB::statement("ALTER TABLE contrataciones MODIFY estado 
            ENUM('concedida','rechazada','documentación','resolución','subsanación','tramitación','','pendiente apertura','justificación','finalizada') DEFAULT ''");
    }

    public function down(): void
    {

        DB::statement("ALTER TABLE contrataciones MODIFY estado 
            ENUM('concedida','rechazada','documentación','resolución','subsanación','tramitación','','pendiente apertura') DEFAULT ''");
    }
};
