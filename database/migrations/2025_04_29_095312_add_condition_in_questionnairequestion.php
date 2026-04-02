<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('questionnaire_questions', function (Blueprint $table) {
            $table->text('condition')->nullable()->after('question_id');
            $table->unsignedBigInteger('next_question_id')->nullable()->after('condition');

            $table->foreign('next_question_id')
                ->references('id')
                ->on('questions')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('questionnaire_questions', function (Blueprint $table) {
            $table->dropForeign(['next_question_id']);
            $table->dropColumn(['condition', 'next_question_id']);
        });
    }
};
