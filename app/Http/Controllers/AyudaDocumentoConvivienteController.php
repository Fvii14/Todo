<?php

namespace App\Http\Controllers;

use App\Models\AyudaDocumentoConviviente;
use Illuminate\Http\Request;

class AyudaDocumentoConvivienteController extends Controller
{
    public function store(Request $request)
    {
        $documentoConviviente = AyudaDocumentoConviviente::create([
            'ayuda_id' => $request->input('ayuda_id'),
            'documento_id' => $request->input('document_id'),
            'es_obligatorio' => $request->input('es_obligatorio'),
        ]);

        // Cargar la relación documento
        $documentoConviviente->load('documento');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Documento de conviviente acoplado correctamente.',
                'documento' => [
                    'id' => $documentoConviviente->id,
                    'documento_id' => $documentoConviviente->documento->id,
                    'name' => $documentoConviviente->documento->name,
                    'description' => $documentoConviviente->documento->description,
                    'es_obligatorio' => $documentoConviviente->es_obligatorio,
                ],
            ]);
        }

        return redirect()->route('ayudas.index')->with('success', 'Documento de conviviente acoplado correctamente.');
    }

    public function destroy($id)
    {
        $documentoConviviente = AyudaDocumentoConviviente::findOrFail($id);
        $ayudaId = $documentoConviviente->ayuda_id;
        $documentoConviviente->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Documento de conviviente desacoplado correctamente.',
                'ayuda_id' => $ayudaId,
            ]);
        }

        return redirect()->route('ayudas.index')->with('success', 'Documento de conviviente desacoplado correctamente.');
    }

    public function getByAyuda($ayudaId)
    {
        $documentos = AyudaDocumentoConviviente::with('documento')
            ->where('ayuda_id', $ayudaId)
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'documento_id' => $doc->documento->id,
                    'name' => $doc->documento->name,
                    'description' => $doc->documento->description,
                    'es_obligatorio' => $doc->es_obligatorio,
                ];
            });

        return response()->json([
            'success' => true,
            'documentos' => $documentos,
        ]);
    }
}
