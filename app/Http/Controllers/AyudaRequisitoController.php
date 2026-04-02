<?php

namespace App\Http\Controllers;

use App\Models\Ayuda;
use App\Models\AyudaRequisito;
use App\Models\Question;
use Illuminate\Http\Request;

class AyudaRequisitoController extends Controller
{
    public function index()
    {
        $ayudas = Ayuda::all();
        $questions = Question::all();

        return view('admin.requisitos', compact('ayudas', 'questions'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'ayuda_id' => 'required|exists:ayudas,id',
            'question_id' => 'required|exists:questions,id',
            'respuesta_expected' => 'required',
        ]);

        $ayudaRequisito = AyudaRequisito::create([
            'ayuda_id' => $request->input('ayuda_id'),
            'question_id' => $request->input('question_id'),
            'respuesta_expected' => $request->input('respuesta_expected'),
        ]);

        return redirect()->route('ayudas.index')->with('success', 'Requisito añadido correctamente.');
    }

    public function destroy($id)
    {
        $question = AyudaRequisito::findOrFail($id);
        $question->delete();

        return redirect()->route('ayudas.index')->with('success', 'Requisito eliminado correctamente.');
    }
}
