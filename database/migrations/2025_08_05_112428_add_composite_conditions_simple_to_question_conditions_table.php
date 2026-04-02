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
        Schema::table('question_conditions', function (Blueprint $table) {
            $table->json('composite_rules')->nullable()->after('condition');
            $table->string('composite_logic')->default('AND')->after('composite_rules');
            $table->boolean('is_composite')->default(false)->after('composite_logic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_conditions', function (Blueprint $table) {
            $table->dropColumn(['composite_rules', 'composite_logic', 'is_composite']);
        });
    }
};
