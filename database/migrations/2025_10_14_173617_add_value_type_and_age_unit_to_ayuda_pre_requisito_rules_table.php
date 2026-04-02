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
        Schema::table('ayuda_pre_requisito_rules', function (Blueprint $table) {
            $table->string('value_type')->default('exact')->after('value2');
            $table->string('age_unit')->nullable()->after('value_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ayuda_pre_requisito_rules', function (Blueprint $table) {
            $table->dropColumn(['value_type', 'age_unit']);
        });
    }
};
