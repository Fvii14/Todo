<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use App\Models\QuestionnaireAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QuestionnaireAnswerController extends Controller
{
    public function store(Request $request)
    {
        // Validación básica
        $request->validate([
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.value' => 'required',
        ]);

        $userId = Auth::id() ?? 11;

        try {
            foreach ($request->input('answers') as $answerData) {
                QuestionnaireAnswer::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'question_id' => $answerData['question_id'],
                    ],
                    [
                        'answer' => $answerData['value'],
                    ]
                );
            }

            $questionnaireId = $request->segment(2);
            $questionnaire = Questionnaire::findOrFail($questionnaireId);

            return redirect($questionnaire->redirect_url)->with('success', 'Respuestas guardadas correctamente');

        } catch (\Exception $e) {
            Log::error('Error al guardar respuestas: '.$e->getMessage());

            return redirect()->route('dashboard')->with('error', 'Ocurrió un error al guardar las respuestas');
        }
    }
}
