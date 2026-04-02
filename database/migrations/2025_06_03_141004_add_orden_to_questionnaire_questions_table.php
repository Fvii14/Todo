<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrdenToQuestionnaireQuestionsTable extends Migration
{
    public function up()
    {
        Schema::table('questionnaire_questions', function (Blueprint $table) {
            $table->unsignedInteger('orden')->default(0)->after('question_id');
        });
    }

    public function down()
    {
        Schema::table('questionnaire_questions', function (Blueprint $table) {
            $table->dropColumn('orden');
        });
    }
}
