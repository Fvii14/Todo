<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormPostCollectorController extends Controller
{
    public function store(Request $request)
    {
        $userId = Auth::id(); // O reemplaza por el ID de usuario según tu lógica

        $data = $request->validate([
            'answers.24' => 'required|in:0,1',
            'answers.25' => 'nullable|string|max:255',
            'answers.41' => 'required|string|max:255',
        ]);

        // Guardar respuesta a la 24
        Answer::updateOrCreate(
            ['user_id' => $userId, 'question_id' => 24, 'conviviente_id' => null],
            ['answer' => $data['answers'][24]]
        );

        // Guardar la 25 solo si 24 es "No"
        if ($data['answers'][24] == 0 && isset($data['answers'][25])) {
            Answer::updateOrCreate(
                ['user_id' => $userId, 'question_id' => 24, 'conviviente_id' => null],
                ['answer' => $data['answers'][25]]
            );
        } else {
            Answer::where('user_id', $userId)
                ->where('question_id', 25)
                ->whereNull('conviviente_id') // <-- SOLO titular
                ->delete();
        }
        Answer::updateOrCreate(
            [
                'user_id' => $userId,
                'question_id' => 41,
                'conviviente_id' => null, // aseguramos que es del titular
            ],
            ['answer' => $data['answers'][41]]
        );

        return redirect()->route('user.home')->with('success', 'Tus respuestas han sido guardadas correctamente.');
    }

    public function showForm()
    {
        $userId = Auth::id();
        $questions = Question::whereIn('id', [24, 25, 41])
            ->get()
            ->map(function ($question) use ($userId) {
                // Intentamos traer la respuesta previa del usuario si existe
                $answer = $question->answers()
                    ->where('user_id', $userId)
                    ->first();

                $question->answer = $answer ? $answer->answer : null;

                return $question;
            });

        return view('user.form-postcollector', compact('questions'));
    }
}
