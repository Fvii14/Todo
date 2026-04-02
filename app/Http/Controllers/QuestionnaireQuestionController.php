<?php

namespace App\Http\Controllers;

use App\Models\QuestionnaireQuestion;
use Illuminate\Http\Request;

class QuestionnaireQuestionController extends Controller
{
    public function store(Request $request)
    {
        $questionnaireId = (int) $request->input('questionnaire_id');
        $questionId = (int) $request->input('document_id');

        $nextOrden = (int) QuestionnaireQuestion::where('questionnaire_id', $questionnaireId)->max('orden');
        $nextOrden = $nextOrden > 0 ? $nextOrden + 1 : 1;

        QuestionnaireQuestion::create([
            'questionnaire_id' => $questionnaireId,
            'question_id' => $questionId,
            'orden' => $nextOrden,
        ]);

        return redirect()->route('questionnaires.index')->with('success', 'Pregunta añadida al cuestionario correctamente.');
    }

    public function destroy($questionnaire_id, $question_id)
    {
        $qq = QuestionnaireQuestion::where('questionnaire_id', $questionnaire_id)
            ->where('question_id', $question_id)
            ->first();

        if ($qq) {
            $qq->delete();

            return redirect()->route('questionnaires.index')->with('success', 'Pregunta eliminada del cuestionario correctamente.');
        }

        return redirect()->route('questionnaires.index')->with('error', 'La pregunta no estaba asociada a este cuestionario.');
    }
}
