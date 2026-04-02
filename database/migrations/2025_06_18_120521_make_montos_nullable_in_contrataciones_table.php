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
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->decimal('monto_comision', 10, 2)->nullable()->change();
            $table->decimal('monto_total_ayuda', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->decimal('monto_comision', 10, 2)->nullable(false)->change();
            $table->decimal('monto_total_ayuda', 10, 2)->nullable(false)->change();
        });
    }
};
