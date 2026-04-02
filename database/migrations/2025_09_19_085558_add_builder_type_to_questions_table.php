<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('integer','boolean','string','date','select','multiple','info','builder') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('questions')->whereIn('slug', ['calculadora', 'education-builder'])->delete();

        DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('integer','boolean','string','date','select','multiple','info') NOT NULL");
    }
};
