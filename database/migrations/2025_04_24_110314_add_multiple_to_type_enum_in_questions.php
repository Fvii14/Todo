<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMultipleToTypeEnumInQuestions extends Migration
{
    public function up()
    {
        // Modificar el campo 'type' para agregar 'multiple' al enum
        DB::statement("ALTER TABLE `questions` CHANGE `type` `type` ENUM('integer', 'boolean', 'string', 'date', 'select', 'multiple', 'info') NOT NULL");
    }

    public function down()
    {
        // Si revertimos la migración, eliminamos 'multiple' del enum
        DB::statement("ALTER TABLE `questions` CHANGE `type` `type` ENUM('integer', 'boolean', 'string', 'date', 'select') NOT NULL");
    }
}
