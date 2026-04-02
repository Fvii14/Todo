<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionnaireAnswersTable extends Migration
{
    public function up()
    {
        Schema::create('questionnaire_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relación con usuarios
            $table->foreignId('question_id')->constrained()->onDelete('cascade'); // Relación con preguntas
            $table->text('answer'); // Respuesta (texto libre)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('questionnaire_answers');
    }
}
