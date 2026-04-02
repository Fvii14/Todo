<?php

namespace App\Http\Controllers;

use App\Models\Contratacion;

class AdminDatosUsuarioController extends Controller
{
    public function show($id)
    {
        $contratacion = Contratacion::with([
            'user.taxInfo',
            'ayuda.questionnaire.questionnaireQuestions.question.answers' => function ($query) use ($id) {
                // Podrías filtrar las respuestas del usuario específico
                $contratacion = Contratacion::findOrFail($id);
                $query->where('user_id', $contratacion->user_id);
            },
        ])->findOrFail($id);

        // Extraer de forma ordenada la información
        $user = $contratacion->user;
        $taxInfo = $user->taxInfo;
        $ayuda = $contratacion->ayuda;
        $questionnaire = $ayuda->questionnaire;
        $questionsWithAnswers = [];

        foreach ($questionnaire->questionnaireQuestions as $qQ) {
            $question = $qQ->question;
            $answers = $question->answers->where('user_id', $user->id);
            $questionsWithAnswers[] = [
                'pregunta' => $question->text,
                'respuesta' => $answers->pluck('value'), // o 'answer' según el campo
            ];
        }

        return response()->json([
            'user' => $user,
            'tax_info' => $taxInfo,
            'ayuda' => $ayuda,
            'questions_with_answers' => $questionsWithAnswers,
        ]);
    }
}
