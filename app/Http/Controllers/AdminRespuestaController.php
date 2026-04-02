<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;

class AdminRespuestaController extends Controller
{
    public function actualizar(Request $request, $userId, $contratacionId)
    {
        $respuestas = $request->input('answers', []);

        foreach ($respuestas as $questionId => $valor) {
            Answer::updateOrCreate(
                ['user_id' => $userId, 'question_id' => $questionId],
                ['answer' => $valor]
            );
        }

        return redirect()->back()->with('success', 'Datos actualizados con éxito.');
    }
}
