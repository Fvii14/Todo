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
            if (Schema::hasColumn('question_conditions', 'condition')) {
                $table->dropColumn('condition');
            }
            if (Schema::hasColumn('question_conditions', 'operador')) {
                $table->dropColumn('operador');
            }
            $table->string('operator', 32)->default('=')->after('question_id');
            $table->json('value')->nullable()->after('operator');
            $table->integer('order')->nullable()->after('questionnaire_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_conditions', function (Blueprint $table) {
            $table->dropColumn(['operator', 'value', 'order']);
            $table->json('condition')->nullable();
            $table->enum('operador', ['==', '!=', '>', '>=', '<', '<='])->default('==');
        });
    }
};
