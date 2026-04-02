<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE questionnaires MODIFY COLUMN tipo ENUM('pre','post','conviviente','arrendatario','collector','solicitud') NOT NULL DEFAULT 'pre'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE questionnaires MODIFY COLUMN tipo ENUM('pre','post','conviviente','arrendatario','collector') NOT NULL DEFAULT 'pre'");
    }
};
