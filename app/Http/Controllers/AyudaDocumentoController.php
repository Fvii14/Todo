<?php

namespace App\Http\Controllers;

use App\Models\AyudaDocumento;
use Illuminate\Http\Request;

class AyudaDocumentoController extends Controller
{
    public function store(Request $request)
    {
        AyudaDocumento::create([
            'ayuda_id' => $request->input('ayuda_id'),
            'documento_id' => $request->input('document_id'),
            'es_obligatorio' => $request->input('es_obligatorio'),
        ]);

        return redirect()->route('ayudas.index')->with('success', 'Documento acoplado correctamente.');
    }

    public function destroy($id)
    {
        $question = AyudaDocumento::findOrFail($id);
        $question->delete();

        return redirect()->route('ayudas.index')->with('success', 'Documento desacoplado correctamente.');
    }
}
