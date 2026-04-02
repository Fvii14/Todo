<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Elimina el registro 'anexo' creado por la migración anterior
        DB::table('documents')->where('slug', 'anexo')->delete();

        DB::statement('ALTER TABLE documents AUTO_INCREMENT = 1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Vuelve a insertarlo por si se revierte esta migración
        DB::table('documents')->insert([
            'tipo' => 'interno',
            'multi_upload' => 1,
            'por_conviviente' => 0,
            'name' => 'Anexo',
            'slug' => 'anexo',
            'description' => 'Documento anexo adicional',
            'allowed_types' => 'application/pdf, image/jpeg, image/png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
