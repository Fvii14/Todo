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
        DB::statement("ALTER TABLE recursos MODIFY tipo ENUM('texto', 'video', 'imagen', 'enlace') DEFAULT 'texto'");
        DB::statement('ALTER TABLE recursos ADD COLUMN url_enlace VARCHAR(255) AFTER url_imagen');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE recursos MODIFY tipo ENUM('texto', 'video', 'imagen') DEFAULT 'texto'");
        DB::statement('ALTER TABLE recursos DROP COLUMN url_enlace');
    }
};
