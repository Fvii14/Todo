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
        Schema::table('educacions', function (Blueprint $table) {
            $table->string('institucion')->nullable()->change();
            $table->string('nombre_estudio')->nullable()->change();
            $table->string('nivel')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('educacions', function (Blueprint $table) {
            $table->string('institucion')->nullable(false)->change();
            $table->string('nombre_estudio')->nullable(false)->change();
            $table->string('nivel')->nullable(false)->change();
        });
    }
};
