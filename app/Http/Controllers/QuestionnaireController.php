<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\Questionnaire;
use App\Models\QuestionnaireQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestionnaireController extends Controller
{
    public function showQuestionnaire($id)
    {
        $questionnaire = Questionnaire::with('questions')->findOrFail($id);

        $questions = $questionnaire->questions;
        $username = auth()->user()->name ?? null;

        return view('questionnaire', compact('questions', 'username', 'questionnaire'));
    }

    public function completeQuestionnaire($id)
    {
        $questionnaire = Questionnaire::findOrFail($id);

        return redirect($questionnaire->redirect_url);
    }

    public function index()
    {
        $questionnaires = Questionnaire::with(['questions'])->withCount('questions')->get();
        $allQuestions = Question::with(['category'])->get();
        $categorias = QuestionCategory::getOrdered();

        return view('admin.questionnaires', compact('questionnaires', 'allQuestions', 'categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Log::info($request->all());

        // Crear el cuestionario
        $questionnaire = Questionnaire::create([
            'name' => $request->name,
            'active' => $request->active ?: false,
        ]);

        // Si hay preguntas, asociarlas al cuestionario
        if ($request->has('questions') && is_array($request->questions)) {
            foreach ($request->questions as $index => $questionId) {
                QuestionnaireQuestion::create([
                    'questionnaire_id' => $questionnaire->id,
                    'question_id' => $questionId,
                ]);
            }
        }

        return redirect()->route('questionnaires.index')->with('success', 'Cuestionario creado correctamente.');
    }

    public function destroy($id)
    {
        $questionnaire = Questionnaire::findOrFail($id);
        $questionnaire->questions()->detach();
        $questionnaire->delete();

        return redirect()->route('questionnaires.index')->with('success', 'Cuestionario eliminado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $questionnaire = Questionnaire::findOrFail($id);
        $questionnaire->update([
            'name' => $request->name,
            'active' => $request->active ?: false,
        ]);

        return redirect()->route('questionnaires.index')->with('success', 'Cuestionario actualizado correctamente.');
    }
}
