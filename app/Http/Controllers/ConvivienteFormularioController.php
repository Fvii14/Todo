<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Conviviente;
use App\Models\Question;
use App\Models\QuestionCondition;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ConvivienteFormularioController extends Controller
{
    // Genera el conviviente (si no existe) y devuelve el enlace
    public function generarEnlace(Request $request)
    {
        Log::info('Petición recibida para generar enlace:', [
            'user_id' => Auth::id(),
            'index' => $request->input('index'),
            'questionnaire_id' => $request->input('questionnaire_id'),
        ]);
        if (! Auth::check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
        $userId = Auth::id();
        $index = $request->input('index');
        $questionnaireId = $request->input('questionnaire_id');

        $conviviente = Conviviente::firstOrCreate(
            ['user_id' => $userId, 'index' => $index],
            [
                'token' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        if (! $conviviente->token) {
            $conviviente->token = Str::uuid();
            $conviviente->save();
        }

        return response()->json([
            'url' => route('conviviente.public.form', ['token' => $conviviente->token]).'?questionnaire_id='.$questionnaireId,
        ]);
    }

    public function showPublicConviviente($token, Request $request)
    {

        // Recuperar el questionnaire_id desde la URL
        $questionnaireId = $request->query('questionnaire_id');

        if (! $questionnaireId) {
            Log::error('❌ Falta el questionnaire_id en la URL');
            abort(400, 'Falta el questionnaire_id en la URL');
        }

        $conviviente = Conviviente::where('token', trim($token))->first();
        Log::info('🧍 Conviviente buscado por token', ['conviviente' => $conviviente]);

        if (! $conviviente) {
            Log::error('❌ Conviviente no encontrado con token', ['token' => $token]);
            abort(404, 'Conviviente no encontrado con este token');
        }

        $userId = $conviviente->user_id;
        $index = $conviviente->index;

        // Calcular preguntas finales
        $preguntasObligatorias = [33, 34, 40, 42, 117, 118, 119, 147, 145, 85, 127, 142, 143, 144, 145, 152, 153, 154, 156, 157, 158, 168, 173, 177, 178, 180];
        $preguntasFormulario = DB::table('questionnaire_questions')
            ->where('questionnaire_id', $questionnaireId)
            ->pluck('question_id')
            ->toArray();
        $preguntasFinales = array_intersect($preguntasObligatorias, $preguntasFormulario);

        // Cargar cuestionario
        $questionnaire = Questionnaire::with('ayuda')
            ->where('id', $questionnaireId)
            ->where('tipo', 'conviviente')
            ->firstOrFail();
        $ayuda = $questionnaire->ayuda;

        $questionIds = DB::table('questionnaire_questions')
            ->where('questionnaire_id', $questionnaireId)
            ->pluck('question_id');

        // Buscar conviviente (si existe)
        $conviviente = Conviviente::where('user_id', $userId)
            ->where('index', $index)
            ->first();

        if ($conviviente) {
            $answers = Answer::where('user_id', $userId)
                ->where('conviviente_id', $conviviente->id)
                ->whereIn('question_id', $questionIds)
                ->pluck('answer', 'question_id');
        } else {
            $answers = collect(); // No existe conviviente → respuestas vacías
        }

        // Obtener las preguntas ordenadas
        $questions = Question::whereIn('questions.id', $questionIds)
            ->join('questionnaire_questions', 'questions.id', '=', 'questionnaire_questions.question_id')
            ->where('questionnaire_questions.questionnaire_id', $questionnaireId)
            ->orderBy('questionnaire_questions.orden')
            ->select('questions.*', 'questionnaire_questions.orden')
            ->get();

        // Usar el método unificado del modelo (formato nuevo: operator + value)
        $conditions = QuestionCondition::getConditions($questionnaireId);

        // Validaciones regex
        $regex = DB::table('regex')
            ->join('questions', 'questions.regex_id', '=', 'regex.id')
            ->whereIn('questions.id', $questionIds)
            ->select('questions.id as question_id', 'regex.pattern', 'regex.error_message')
            ->get()
            ->keyBy('question_id');

        $respuestasGrupos = Answer::where('user_id', $userId)
            ->whereIn('question_id', [9, 10, 11])
            ->whereNull('conviviente_id')
            ->pluck('answer', 'question_id');

        if ($respuestasGrupos->isEmpty()) {
            $questions = $questions->filter(function ($q) {
                // Lista de slugs que solo deben mostrarse si pertenece a un grupo vulnerable
                $slugsSoloParaVulnerables = [
                    'grupo-vulnerable-conviviente',
                    'pertenece-grupo-vulnerable-conviviente',
                    'porcentaje_discapacidad',
                    'movilidad_reducida',
                ];

                // Mantener solo las preguntas que NO están en esa lista
                return ! in_array($q->slug, $slugsSoloParaVulnerables);
            });
        }
        $gruposVulnerablesSeleccionados = [];

        foreach ($respuestasGrupos as $questionIds => $respuesta) {
            $valores = json_decode($respuesta, true);
            if (is_array($valores)) {
                $gruposVulnerablesSeleccionados = array_merge($gruposVulnerablesSeleccionados, $valores);
            } elseif (! empty($respuesta)) {
                $gruposVulnerablesSeleccionados[] = $respuesta;
            }
        }

        // Eliminar duplicados
        $gruposVulnerablesSeleccionados = array_unique($gruposVulnerablesSeleccionados);

        // Mapear preguntas
        $mappedQuestions = $questions->map(function ($q) use ($answers, $regex, $gruposVulnerablesSeleccionados) {
            $options = [];

            if ($q->slug === 'grupo-vulnerable-conviviente') {
                $todasLasOpciones = is_array($q->options) ? $q->options : json_decode($q->options, true);
                $options = [];

                foreach ($todasLasOpciones as $index => $label) {
                    if (in_array($label, $gruposVulnerablesSeleccionados)) {
                        $options[$index] = $label;
                    }
                }
            } elseif (in_array($q->type, ['select', 'multiple', 'radio', 'checkbox'])) {
                // Decodificar siempre que sea string JSON
                $options = is_string($q->options) ? json_decode($q->options, true) ?? [] : $q->options;
            } else {
                // Para otros tipos no se necesita options
                $options = [];
            }

            return [
                'id' => $q->id,
                'slug' => $q->slug,
                'text' => $q->text,
                'subtext' => $q->sub_text,
                'type' => $q->type,
                'options' => $options,
                'answer' => $answers[$q->id] ?? null,
                'disable_answer' => $q->disable_answer,
                'validation' => [
                    'pattern' => $regex[$q->id]->pattern ?? null,
                    'error_message' => $regex[$q->id]->error_message ?? null,
                ],
            ];
        });

        // Renderizar vista
        return view('conviviente.public-form', [
            'questions' => $mappedQuestions,
            'answers' => $answers,
            'conditions' => $conditions,
            'convivienteIndex' => $index,
            'questionnaireId' => $questionnaireId,
            'ayuda' => $ayuda,
            'preguntasFinales' => $preguntasFinales,
        ]);
    }
}
