<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE user_documents MODIFY COLUMN estado ENUM('pendiente', 'validado', 'rechazado') DEFAULT 'pendiente'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE user_documents MODIFY COLUMN estado ENUM('pendiente', 'validado') DEFAULT 'pendiente'");
    }
};
