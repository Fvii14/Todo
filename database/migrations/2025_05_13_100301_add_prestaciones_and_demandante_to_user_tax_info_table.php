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
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->boolean('tienePrestaciones')->nullable();
            $table->boolean('is_demandante_empleo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->dropColumn(['tienePrestaciones', 'is_demandante_empleo']);
        });
    }
};
