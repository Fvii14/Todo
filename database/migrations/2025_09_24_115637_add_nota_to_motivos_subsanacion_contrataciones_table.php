<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('motivos_subsanacion_contrataciones', function (Blueprint $table) {
            $table->text('nota')->nullable()->after('estado_subsanacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('motivos_subsanacion_contrataciones', function (Blueprint $table) {
            $table->dropColumn('nota');
        });
    }
};
