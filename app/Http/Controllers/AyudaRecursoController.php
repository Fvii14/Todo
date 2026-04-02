<?php

namespace App\Http\Controllers;

use App\Models\Ayuda;
use App\Models\AyudaRecurso;
use App\Models\Recurso;
use Illuminate\Http\Request;

class AyudaRecursoController extends Controller
{
    public function index()
    {
        $ayudas = Ayuda::all();

        return view('admin.ayudas-recursos', compact('ayudas'));
    }

    public function edit($id)
    {
        $ayuda = Ayuda::findOrFail($id);
        $recursos = $ayuda->recursos()->withPivot('orden', 'activo')->get();

        return view('admin.ayudas-recursos-edit', compact('ayuda', 'recursos'));
    }

    public function create($id)
    {
        $ayuda = Ayuda::findOrFail($id);
        $recursos_disponibles = Recurso::all();

        return view('admin.ayudas-recursos-create', compact('ayuda', 'recursos_disponibles'));
    }

    public function store(Request $request, $id)
    {
        // Crear o seleccionar un recurso reutilizable
        if ($request->filled('recurso_id')) {
            $recurso = Recurso::findOrFail($request->recurso_id);
        } else {
            $recurso = new Recurso;
            $recurso->titulo = $request->titulo;
            $recurso->descripcion = $request->descripcion;
            $recurso->tipo = $request->tipo;
            $recurso->contenido_texto = $request->contenido_texto;
            if ($request->tipo == 'video') {
                $recurso->url_video = $request->url_archivo;
            } elseif ($request->tipo == 'imagen') {
                $recurso->url_imagen = $request->url_archivo;
            } elseif ($request->tipo == 'enlace') {
                $recurso->url_enlace = $request->url_archivo;
            }
            $recurso->save();
        }
        // Asociar el recurso a la ayuda
        $ayuda = Ayuda::findOrFail($id);
        $ayuda->recursos()->attach($recurso->id, [
            'orden' => $request->orden ?? 0,
            'activo' => true,
        ]);

        return redirect()->route('ayudas.recursos.edit', $id);
    }

    public function update(Request $request, $pivot_id)
    {
        $pivot = AyudaRecurso::findOrFail($pivot_id);
        $pivot->orden = $request->orden;
        $pivot->activo = $request->activo ? true : false;
        $pivot->save();
        $recurso = $pivot->recurso;
        $recurso->titulo = $request->titulo;
        $recurso->descripcion = $request->descripcion;
        $recurso->tipo = $request->tipo;
        $recurso->contenido_texto = $request->contenido_texto;
        $recurso->url_enlace = $request->url_enlace;
        $recurso->url_imagen = $request->url_imagen;
        if ($request->tipo == 'video') {
            $recurso->url_video = $request->url_video;
            $recurso->url_imagen = null;
            $recurso->url_enlace = null;
        } elseif ($request->tipo == 'enlace') {
            $recurso->url_imagen = null;
            $recurso->url_video = null;
            $recurso->url_imagen = null;
        } else {
            $recurso->url_video = null;
            $recurso->url_imagen = $request->url_imagen;
            $recurso->url_enlace = null;
        }
        $recurso->save();

        return response()->json(['success' => true, 'recurso' => $recurso, 'pivot' => $pivot]);
    }

    public function desasociar($pivot_id)
    {
        try {
            $pivot = AyudaRecurso::findOrFail($pivot_id);
            $pivot->delete();

            return response()->json(['success' => true, 'message' => 'Recurso desasociado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al desasociar el recurso: '.$e->getMessage()], 500);
        }
    }

    public function eliminar($pivot_id)
    {
        try {
            $pivot = AyudaRecurso::findOrFail($pivot_id);
            $recurso = $pivot->recurso;
            $recurso->ayudas()->detach();
            $recurso->delete();

            return response()->json(['success' => true, 'message' => 'Recurso eliminado completamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar el recurso: '.$e->getMessage()], 500);
        }
    }
}
