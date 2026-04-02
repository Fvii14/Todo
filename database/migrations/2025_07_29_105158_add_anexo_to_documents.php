<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('documents')->where('slug', 'anexo')->delete();
    }
};
