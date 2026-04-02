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
        Schema::table('conviviente_type_questions', function (Blueprint $table) {
            $table->json('conditional_options')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conviviente_type_questions', function (Blueprint $table) {
            $table->dropColumn('conditional_options');
        });
    }
};
