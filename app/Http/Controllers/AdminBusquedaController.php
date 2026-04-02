<?php

namespace App\Http\Controllers;

use App\Models\Ayuda;
use App\Models\Ccaa;
use App\Models\Contratacion;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminBusquedaController extends Controller
{
    public function index(Request $request)
    {
        $queryTexto = $request->input('q');
        $estadoFiltro = $request->input('estado');
        $estadoOpxFiltro = $request->input('estado_opx');
        $ayudaFiltro = $request->input('ayuda');
        $activoFiltro = $request->input('activo');
        $faseFiltro = $request->input('fase');
        $ccaaFiltro = $request->input('ccaa');

        $contrataciones = Contratacion::with(['user.answers', 'ayuda'])
            ->when($queryTexto, function ($query) use ($queryTexto) {
                $query->whereHas('user', function ($q) use ($queryTexto) {
                    $q->where('email', 'like', "%$queryTexto%");
                });
            })
            ->when($estadoFiltro, function ($query) use ($estadoFiltro) {
                $query->where('estado', $estadoFiltro);
            })
            ->when($estadoOpxFiltro, function ($query) use ($estadoOpxFiltro) {
                $query->whereHas('estadosContratacion', function ($q) use ($estadoOpxFiltro) {
                    $q->where('codigo', $estadoOpxFiltro);
                });
            })
            ->when($ayudaFiltro, function ($query) use ($ayudaFiltro) {
                $query->whereHas('ayuda', function ($q) use ($ayudaFiltro) {
                    $q->where('nombre_ayuda', $ayudaFiltro);
                });
            })
            ->when(isset($activoFiltro), function ($query) use ($activoFiltro) {
                $query->whereHas('ayuda', function ($q) use ($activoFiltro) {
                    $q->where('activo', $activoFiltro);
                });
            })
            ->when(isset($faseFiltro), function ($query) use ($faseFiltro) {
                $query->where('fase', $faseFiltro);
            })
            ->when($ccaaFiltro, function ($query) use ($ccaaFiltro) {
                $query->whereHas('user.answers', function ($q) use ($ccaaFiltro) {
                    $q->whereRaw('LOWER(value) LIKE ?', ['%'.strtolower($ccaaFiltro).'%']);
                });
            })
            ->get();

        // Obtener valores ENUM del campo 'estado'
        $enumRaw = DB::selectOne("SHOW COLUMNS FROM contrataciones WHERE Field = 'estado'")->Type;
        preg_match('/enum\((.*)\)/', $enumRaw, $matches);
        $estados = isset($matches[1])
            ? array_map(fn ($val) => trim($val, "'"), explode(',', $matches[1]))
            : [];

        // Obtener valores ENUM del campo 'fase'
        $enumRaw = DB::selectOne("SHOW COLUMNS FROM contrataciones WHERE Field = 'fase'")->Type;
        preg_match('/enum\((.*)\)/', $enumRaw, $matches);
        $fases = isset($matches[1])
            ? array_map(fn ($val) => trim($val, "'"), explode(',', $matches[1]))
            : [];

        // Obtener nombres de ayudas
        $ayudas = Ayuda::select('nombre_ayuda')->distinct()->pluck('nombre_ayuda');

        // Obtener lista de comunidades autónomas para el selector
        $ccaas = Ccaa::pluck('nombre_ccaa');

        return view('admin.tramites', compact(
            'contrataciones',
            'queryTexto',
            'estados',
            'ayudas',
            'activoFiltro',
            'fases',
            'faseFiltro',
            'ccaaFiltro',
            'ccaas',
            'estadoOpxFiltro'
        ));
    }

    public function verUsuario($user_id, $contratacion_id)
    {
        $user = User::with('taxInfo')->findOrFail($user_id);
        $contrataciones = Contratacion::where('user_id', $user_id)->get();
        $documentos = UserDocument::where('user_id', $user_id)->get();

        $contratacionSeleccionada = Contratacion::findOrFail($contratacion_id);
        $ayudaId = $contratacionSeleccionada->ayuda_id;

        return view('admin.panel-usuario', compact(
            'user',
            'contrataciones',
            'documentos',
            'contratacion_id',
            'ayudaId'
        ));
    }

    public function verDatosUsuario($user_id, $contratacion_id)
    {
        $user = User::findOrFail($user_id);
        $contratacion = Contratacion::with('ayuda')->findOrFail($contratacion_id);
        $ayudaId = $contratacion->ayuda->id;

        // Obtener los cuestionarios: el del collector (id 1) y el de la ayuda contratada
        $cuestionariosRelacionados = DB::table('questionnaires')
            ->where('ayuda_id', $ayudaId)
            ->orWhere('id', 1) // ID del formulario del collector
            ->pluck('id');

        // Obtener preguntas vinculadas a esos cuestionarios
        $respuestas = DB::table('questions')
            ->join('questionnaire_questions', 'questions.id', '=', 'questionnaire_questions.question_id')
            ->join('questionnaires', 'questionnaire_questions.questionnaire_id', '=', 'questionnaires.id')
            ->whereIn('questionnaire_questions.questionnaire_id', $cuestionariosRelacionados)
            ->leftJoin('answers', function ($join) use ($user_id) {
                $join->on('questions.id', '=', 'answers.question_id')
                    ->where('answers.user_id', '=', $user_id);
            })
            ->select(
                'questions.id as question_id',
                'questions.text as pregunta',
                'questions.type as tipo',
                'questions.options',
                'questionnaires.id as questionnaire_id',
                'answers.answer as respuesta'
            )
            ->orderBy('questionnaires.id')
            ->get();

        return view('admin.datos-usuario', compact('user', 'contratacion', 'respuestas'));
    }

    /* public function verDatosUsuario($user_id)
     {
         $user = User::findOrFail($user_id);

         return view('admin.datos-usuario', compact('user'));
     }*/

    public function update(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|string',
        ]);

        $contratacion = Contratacion::with(['user.answers', 'ayuda'])->findOrFail($id);
        $contratacion->estado = $request->input('estado');
        $contratacion->save(); // Usa Eloquent para actualizar y permitir observers para el email

        return redirect()->back()->with('success', 'Estado actualizado con éxito. Se ha notificado al usuario por email.');
    }
}
