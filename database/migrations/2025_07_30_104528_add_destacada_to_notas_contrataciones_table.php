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
        Schema::table('notas_contrataciones', function (Blueprint $table) {
            $table->boolean('destacada')->default(false)->after('nota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_contrataciones', function (Blueprint $table) {
            $table->dropColumn('destacada');
        });
    }
};
