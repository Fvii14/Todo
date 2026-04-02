<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Ccaa;
use App\Models\ComunicacionOperativa;
use App\Models\Municipio;
use App\Models\Provincia;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminUserPanelController extends Controller
{
    public function show(User $user)
    {
        // 1️⃣ Mapear respuestas genéricas
        $answers = $user->answers()->with('question')->get();
        $userDetails = $answers->map(fn ($answer) => [
            'slug' => $answer->question->slug,
            'question' => $answer->question->text,
            'type' => $answer->question->type,
            'answer' => $answer->getFormattedAnswer(),
        ]);

        // 2️⃣ Todas las solicitudes de ayuda
        $ayudasSolicitadas = $user
            ->ayudasSolicitadas()
            ->with(['ayuda.questionnaire.questions' => function ($query) {
                $query->orderBy('questionnaire_questions.orden');
            }, 'tramite'])
            ->get();

        // 3️⃣ IDs de ayuda que ya tienen una contratación
        $contrataciones = $user->contrataciones()->get();
        $contratacionAyudaIds = $contrataciones->pluck('ayuda_id')->toArray();

        // Obtener historial de actividad
        $historialActividad = $user->has('historialActividad') ? $user->historialActividad()->orderByDesc('fecha_inicio')->get() : collect();

        // 👉 1. Lista de question_id que queremos mostrar
        $slugs = [
            'solo_nombre',
            'primer_apellido',
            'segundo_apellido',
            'dni_nie',
            'telefono',
            'domicilio',
            'provincia',
            'municipio',
            'comunidad_autonoma',
            'estado_civil',
        ];

        $questionsBySlug = Question::whereIn('slug', $slugs)->get()->keyBy('slug');

        $questionIds = $questionsBySlug->pluck('id')->toArray();

        $profileAnswers = $user
            ->answers()
            ->whereIn('question_id', $questionIds)
            ->get()
            ->keyBy('question_id');

        $userData = collect($slugs)->mapWithKeys(function ($slug) use ($questionsBySlug, $profileAnswers) {
            $questionId = $questionsBySlug[$slug]->id;
            $answer = $profileAnswers[$questionId]->answer ?? '—';

            return [$slug => $answer];
        })->toArray();

        // Preguntas y respuestas del questionnaire Collector (id 1)
        $collectorQuestionnaire = \App\Models\Questionnaire::with(['questions' => function ($q) {
            $q->orderBy('questionnaire_questions.orden');
        }])->find(1);
        $collectorQuestions = $collectorQuestionnaire ? $collectorQuestionnaire->questions : collect();
        $collectorAnswers = $user->answers()->whereIn('question_id', $collectorQuestions->pluck('id'))->get()->keyBy('question_id');

        $datosEconomicosQuestions = Question::where('categoria', 'datos-economicos')->get();
        $datosEconomicosAnswers = $user->answers()->whereIn('question_id', $datosEconomicosQuestions->pluck('id'))->get()->keyBy('question_id');

        // Obtener ayudas con estados comerciales
        $ayudasConEstados = $user->ayudas()
            ->with('ayuda')
            ->whereNotNull('estado_comercial')
            ->whereNotNull('ayuda_id') // Solo ayudas con ayuda_id válido
            ->orderBy('estado_comercial')
            ->get();

        // Obtener fechas de solicitud de ayudas_solicitadas
        $ayudasSolicitadas = $user->ayudasSolicitadas()
            ->select('ayuda_id', 'fecha_solicitud')
            ->get()
            ->keyBy('ayuda_id');

        // Combinar los datos y agrupar por estado comercial
        $ayudasConEstados = $ayudasConEstados->map(function ($userAyuda) use ($ayudasSolicitadas) {
            if ($userAyuda->ayuda) {
                $userAyuda->fecha_solicitud = $ayudasSolicitadas->get($userAyuda->ayuda_id)?->fecha_solicitud;

                return $userAyuda;
            }

            return null;
        })->filter() // Eliminar elementos null
            ->groupBy('estado_comercial');

        // Agrupar por estado comercial
        $estadosComerciales = [
            'caliente' => collect($ayudasConEstados->get('caliente', [])),
            'tibio' => collect($ayudasConEstados->get('tibio', [])),
            'frio' => collect($ayudasConEstados->get('frio', [])),
        ];

        $userGeneralHistory = \App\Models\CrmStateHistory::where('user_id', $user->id)
            ->whereNull('ayuda_id')
            ->orderBy('created_at')
            ->get();

        $estadosComerciales['caliente']->each(function ($userAyuda) use ($user, $userGeneralHistory) {
            $ayudaHistory = \App\Models\CrmStateHistory::where('user_id', $user->id)
                ->where('ayuda_id', $userAyuda->ayuda_id)
                ->orderBy('created_at')
                ->get();

            $userAyuda->crm_history = $ayudaHistory->concat($userGeneralHistory)->sortBy('created_at');
        });

        $estadosComerciales['tibio']->each(function ($userAyuda) use ($user, $userGeneralHistory) {
            $ayudaHistory = \App\Models\CrmStateHistory::where('user_id', $user->id)
                ->where('ayuda_id', $userAyuda->ayuda_id)
                ->orderBy('created_at')
                ->get();

            $userAyuda->crm_history = $ayudaHistory->concat($userGeneralHistory)->sortBy('created_at');
        });

        $estadosComerciales['frio']->each(function ($userAyuda) use ($user, $userGeneralHistory) {
            $ayudaHistory = \App\Models\CrmStateHistory::where('user_id', $user->id)
                ->where('ayuda_id', $userAyuda->ayuda_id)
                ->orderBy('created_at')
                ->get();

            $userAyuda->crm_history = $ayudaHistory->concat($userGeneralHistory)->sortBy('created_at');
        });

        $comunicacionesOperativas = ComunicacionOperativa::where('user_id', $user->id)
            ->with('tramitador')
            ->orderBy('fecha_hora', 'desc')
            ->get();

        return view('admin.panel-usuario', compact(
            'user',
            'userDetails',
            'ayudasSolicitadas',
            'contratacionAyudaIds',
            'userData',
            'contrataciones',
            'historialActividad',
            'collectorQuestions',
            'collectorAnswers',
            'datosEconomicosQuestions',
            'datosEconomicosAnswers',
            'estadosComerciales',
            'comunicacionesOperativas'
        ));
    }

    public function showPartial(User $user, Request $request)
    {
        try {
            $answers = $user->answers()->with('question')->get();
            $userDetails = $answers->map(fn ($answer) => [
                'slug' => $answer->question->slug,
                'question' => $answer->question->text,
                'type' => $answer->question->type,
                'answer' => $answer->getFormattedAnswer(),
            ]);
            $ayudasSolicitadas = $user
                ->ayudasSolicitadas()
                ->with(['ayuda.questionnaire.questions' => function ($query) {
                    $query->orderBy('questionnaire_questions.orden');
                }, 'tramite'])
                ->get();

            $contrataciones = $user->contrataciones()->get();
            $contratacionAyudaIds = $contrataciones->pluck('ayuda_id')->toArray();
            $historialActividad = $user->has('historialActividad') ? $user->historialActividad()->orderByDesc('fecha_inicio')->get() : collect();
            $slugs = [
                'nombre_completo',
                'dni_nie',
                'telefono',
                'domicilio',
                'provincia',
                'municipio',
                'comunidad_autonoma',
                'estado_civil',
            ];
            $questionsBySlug = Question::whereIn('slug', $slugs)->get()->keyBy('slug');
            $questionIds = $questionsBySlug->pluck('id')->toArray();
            $profileAnswers = $user
                ->answers()
                ->whereIn('question_id', $questionIds)
                ->get()
                ->keyBy('question_id');
            $userData = collect($slugs)->mapWithKeys(function ($slug) use ($questionsBySlug, $profileAnswers) {
                $questionId = $questionsBySlug[$slug]->id;
                $answer = $profileAnswers[$questionId]->answer ?? '—';

                return [$slug => $answer];
            })->toArray();
            $partial = true;
            $collectorQuestionnaire = \App\Models\Questionnaire::with(['questions' => function ($q) {
                $q->orderBy('questionnaire_questions.orden');
            }])->find(1);
            $collectorQuestions = $collectorQuestionnaire ? $collectorQuestionnaire->questions : collect();
            $collectorAnswers = $user->answers()->whereIn('question_id', $collectorQuestions->pluck('id'))->get()->keyBy('question_id');

            $datosEconomicosQuestions = Question::where('categoria', 'datos-economicos')->get();
            $datosEconomicosAnswers = $user->answers()->whereIn('question_id', $datosEconomicosQuestions->pluck('id'))->get()->keyBy('question_id');

            $ayudaId1 = $request->query('ayuda_id_1');
            $ayudaId2 = $request->query('ayuda_id_2');

            // Si hay filtro de ayuda, filtrar solo por esa ayuda
            $ayudaFiltrada = $request->query('ayuda_id');

            if ($ayudaFiltrada) {
                // Cuando se filtra por ayuda, buscar en ambas tablas
                $ayudaSolicitada = $user->ayudasSolicitadas()
                    ->where('ayuda_id', $ayudaFiltrada)
                    ->first();

                $ayudaConEstado = $user->ayudas()
                    ->with('ayuda')
                    ->where('ayuda_id', $ayudaFiltrada)
                    ->whereNotNull('ayuda_id')
                    ->first();

                if ($ayudaSolicitada || $ayudaConEstado) {
                    // Si existe en cualquiera de las dos tablas
                    if ($ayudaConEstado) {
                        // Si tiene estado comercial, usar esa información
                        $ayudasConEstados = collect([$ayudaConEstado]);
                    } else {
                        // Si no tiene estado comercial pero existe en ayudas_solicitadas
                        $ayudaTemporal = (object) [
                            'ayuda_id' => $ayudaFiltrada,
                            'estado_comercial' => null,
                            'ayuda' => $ayudaSolicitada->ayuda,
                            'fecha_solicitud' => $ayudaSolicitada->fecha_solicitud,
                            'tags' => null,
                        ];
                        $ayudasConEstados = collect([$ayudaTemporal]);
                    }
                } else {
                    // Si no existe en ninguna tabla, colección vacía
                    $ayudasConEstados = collect([]);
                }
            } else {
                // Sin filtro, mostrar todas las ayudas con estado comercial
                $ayudasConEstados = $user->ayudas()
                    ->with('ayuda')
                    ->whereNotNull('estado_comercial')
                    ->whereNotNull('ayuda_id') // Solo ayudas con ayuda_id válido
                    ->orderBy('estado_comercial')
                    ->get();
            }

            $ayudasSolicitadasFechas = $ayudasSolicitadas->keyBy('ayuda_id');

            // Si se está filtrando por ayuda, no necesitamos agrupar por estado comercial
            if ($ayudaFiltrada) {
                // Para ayudas filtradas, mantener la estructura original
                $ayudasConEstados = $ayudasConEstados->map(function ($userAyuda) use ($ayudasSolicitadasFechas) {
                    if ($userAyuda->ayuda) {
                        // Si no tiene fecha_solicitud, obtenerla de ayudas_solicitadas
                        if (! isset($userAyuda->fecha_solicitud)) {
                            $userAyuda->fecha_solicitud = $ayudasSolicitadasFechas->get($userAyuda->ayuda_id)?->fecha_solicitud;
                        }

                        return $userAyuda;
                    }

                    return null;
                })->filter(); // Eliminar elementos null
            } else {
                // Sin filtro, agrupar por estado comercial como antes
                $ayudasConEstados = $ayudasConEstados->map(function ($userAyuda) use ($ayudasSolicitadasFechas) {
                    if ($userAyuda->ayuda) {
                        $userAyuda->fecha_solicitud = $ayudasSolicitadasFechas->get($userAyuda->ayuda_id)?->fecha_solicitud;

                        return $userAyuda;
                    }

                    return null;
                })->filter() // Eliminar elementos null
                    ->groupBy('estado_comercial');
            }

            // Si se está filtrando por ayuda, crear colecciones que incluyan ayudas sin estado comercial
            if ($ayudaFiltrada) {
                // Para ayudas filtradas, agrupar por estado comercial
                $ayudasAgrupadas = $ayudasConEstados->groupBy('estado_comercial');

                $estadosComerciales = [
                    'caliente' => collect($ayudasAgrupadas->get('caliente', [])),
                    'tibio' => collect($ayudasAgrupadas->get('tibio', [])),
                    'frio' => collect($ayudasAgrupadas->get('frio', [])),
                    'sin_estado' => collect($ayudasAgrupadas->get(null, [])), // Ayudas sin estado comercial
                ];
            } else {
                $estadosComerciales = [
                    'caliente' => collect($ayudasConEstados->get('caliente', [])),
                    'tibio' => collect($ayudasConEstados->get('tibio', [])),
                    'frio' => collect($ayudasConEstados->get('frio', [])),
                ];
            }

            $userGeneralHistory = \App\Models\CrmStateHistory::where('user_id', $user->id)
                ->whereNull('ayuda_id')
                ->orderBy('created_at')
                ->get();

            $estadosComerciales['caliente']->each(function ($userAyuda) use ($user, $userGeneralHistory) {
                $ayudaHistory = \App\Models\CrmStateHistory::where('user_id', $user->id)
                    ->where('ayuda_id', $userAyuda->ayuda_id)
                    ->orderBy('created_at')
                    ->get();

                $userAyuda->crm_history = $ayudaHistory->concat($userGeneralHistory)->sortBy('created_at');
            });

            $estadosComerciales['tibio']->each(function ($userAyuda) use ($user, $userGeneralHistory) {
                $ayudaHistory = \App\Models\CrmStateHistory::where('user_id', $user->id)
                    ->where('ayuda_id', $userAyuda->ayuda_id)
                    ->orderBy('created_at')
                    ->get();

                $userAyuda->crm_history = $ayudaHistory->concat($userGeneralHistory)->sortBy('created_at');
            });

            $estadosComerciales['frio']->each(function ($userAyuda) use ($user, $userGeneralHistory) {
                $ayudaHistory = \App\Models\CrmStateHistory::where('user_id', $user->id)
                    ->where('ayuda_id', $userAyuda->ayuda_id)
                    ->orderBy('created_at')
                    ->get();

                $userAyuda->crm_history = $ayudaHistory->concat($userGeneralHistory)->sortBy('created_at');
            });

            // Añadir historial CRM para ayudas sin estado comercial (solo cuando se filtra por ayuda)
            if (isset($estadosComerciales['sin_estado'])) {
                $estadosComerciales['sin_estado']->each(function ($userAyuda) use ($user, $userGeneralHistory) {
                    $ayudaHistory = \App\Models\CrmStateHistory::where('user_id', $user->id)
                        ->where('ayuda_id', $userAyuda->ayuda_id)
                        ->orderBy('created_at')
                        ->get();

                    $userAyuda->crm_history = $ayudaHistory->concat($userGeneralHistory)->sortBy('created_at');
                });
            }

            $comunicacionesOperativas = ComunicacionOperativa::where('user_id', $user->id)
                ->with('tramitador')
                ->orderBy('fecha_hora', 'desc')
                ->get();

            // Cargar relaciones adicionales para evitar errores de count()
            $user->load(['ayudas', 'userDocuments', 'historialActividad']);

            return view('admin.panel-usuario', compact(
                'user',
                'userDetails',
                'ayudasSolicitadas',
                'contratacionAyudaIds',
                'userData',
                'contrataciones',
                'partial',
                'historialActividad',
                'collectorQuestions',
                'collectorAnswers',
                'datosEconomicosQuestions',
                'datosEconomicosAnswers',
                'ayudaId1',
                'ayudaId2',
                'estadosComerciales',
                'comunicacionesOperativas'
            ));
        } catch (\Exception $e) {
            \Log::error('AdminUserPanelController::showPartial - Error: '.$e->getMessage());
            \Log::error('AdminUserPanelController::showPartial - Stack trace: '.$e->getTraceAsString());

            return response()->json([
                'error' => 'Error al cargar el panel de usuario: '.$e->getMessage(),
            ], 500);
        }
    }

    /*
    Listar en base la tabla question (da problemas con null)

    public function editarUsuario($user_id)
    {
        $user = User::findOrFail($user_id);

        $respuestas = DB::table('questions')
    ->join('questionnaire_questions', 'questions.id', '=', 'questionnaire_questions.question_id')
    ->join('questionnaires', 'questionnaire_questions.questionnaire_id', '=', 'questionnaires.id')
    ->leftJoin('answers', function ($join) use ($user_id) {
        $join->on('questions.id', '=', 'answers.question_id')
             ->where('answers.user_id', '=', $user_id);
    })
    ->where(function($query) {
        $query->where('questions.sector', 'collector')
              ->orWhere('questions.categoria', 'datos-personales');
    })
    ->select(
        'questions.id as question_id',
        'questions.text as pregunta',
        'questions.type as tipo',
        'questions.options',
        'questions.sector',
        'questions.categoria',
        'questionnaires.id as questionnaire_id',
        'answers.answer as respuesta'
    )
    ->distinct('questions.id') // <-- Aquí
    ->orderBy('questionnaires.id')
    ->get();


            return view('admin.editar-usuario', compact('user', 'respuestas'));
    }*/

    // Lista en base la tabla answer
    public function editarUsuario($user_id)
    {
        $user = User::findOrFail($user_id);

        $respuestas = DB::table('answers')
            ->where('answers.user_id', $user_id)
            ->join('questions', 'answers.question_id', '=', 'questions.id')
            ->join('questionnaire_questions', 'questions.id', '=', 'questionnaire_questions.question_id')
            ->join('questionnaires', 'questionnaire_questions.questionnaire_id', '=', 'questionnaires.id')
            ->where(function ($query) {
                $query->where('questions.sector', 'collector')
                    ->orWhere('questions.categoria', 'datos-personales');
            })
            ->select(
                'questions.id as question_id',
                'questions.text as pregunta',
                'questions.type as tipo',
                'questions.options',
                'questions.sector',
                'questions.categoria',
                'questionnaires.id as questionnaire_id',
                'answers.answer as respuesta'
            )
            ->orderBy('questionnaires.id')
            ->get();

        return view('admin.editar-usuario', compact('user', 'respuestas'));
    }

    public function actualizarRespuestas(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);

        $answers = $request->input('answers', []);

        foreach ($answers as $question_id => $answer) {
            // Para respuestas múltiples, almacenamos como cadena separada por comas
            if (is_array($answer)) {
                $answer = implode(',', $answer);
            }

            // Actualizar o crear la respuesta
            \App\Models\Answer::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'question_id' => $question_id,
                ],
                [
                    'answer' => $answer,
                ]
            );
        }

        return redirect()->route('admin.editar-usuario', [$user->id])
            ->with('success', 'Respuestas actualizadas correctamente.');
    }

    /**
     * Registrar comunicación operativa (WhatsApp o Llamada)
     */
    public function storeComunicacionOperativa(Request $request, $userId)
    {
        $request->validate([
            'tipo_comunicacion' => 'required|in:WhatsApp,Llamada',
        ]);
        $user = \App\Models\User::findOrFail($userId);
        $tramitadorId = Auth::id();
        if (! $tramitadorId) {
            return response()->json([
                'success' => false,
                'message' => 'No hay tramitador autenticado. Por favor, vuelve a iniciar sesión.',
            ], 401);
        }
        $com = ComunicacionOperativa::create([
            'user_id' => $user->id,
            'tramitador_id' => $tramitadorId,
            'tipo_comunicacion' => $request->input('tipo_comunicacion'),
            'fecha_hora' => now(),
        ]);
        // Registrar en historial de actividad
        \App\Models\HistorialActividad::create([
            'user_id' => $user->id,
            'contratacion_id' => null,
            'actividad' => 'Comunicación operativa',
            'observaciones' => $request->input('tipo_comunicacion'),
        ]);
        $com->load('tramitador');

        return response()->json([
            'success' => true,
            'message' => 'Comunicación operativa registrada correctamente',
            'comunicacion' => [
                'id' => $com->id,
                'tipo_comunicacion' => $com->tipo_comunicacion,
                'fecha_hora' => $com->fecha_hora,
                'direction' => 'out',
                'subject' => null,
                'auto' => true,
                'tramitador_email' => $com->tramitador->email ?? 'N/A',
            ],
        ]);
    }

    public function updateAnswer(Request $request, User $user, $answerId)
    {
        $request->validate([
            'answer' => 'required|string',
        ]);

        $answer = null;
        $answerValue = $request->input('answer');

        if ($answerId === 'new') {
            $questionSlug = $request->input('question_slug');
            if (! $questionSlug) {
                return response()->json(['error' => 'Slug de pregunta requerido'], 400);
            }

            $question = Question::where('slug', $questionSlug)->first();
            if (! $question) {
                return response()->json(['error' => 'Pregunta no encontrada'], 404);
            }

            $processedAnswer = $this->processAnswerValue($questionSlug, $answerValue);
            $answer = Answer::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'question_id' => $question->id,
                ],
                [
                    'answer' => $processedAnswer,
                ]
            );
        } else {
            $answer = Answer::findOrFail($answerId);

            if ($answer->user_id !== $user->id) {
                return response()->json(['error' => 'Respuesta no válida'], 403);
            }
            $processedAnswer = $this->processAnswerValue($answer->question->slug, $answerValue);

            $answer->update([
                'answer' => $processedAnswer,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Respuesta actualizada correctamente',
            'formatted_answer' => $answer->getFormattedAnswer(),
            'answer_id' => $answer->id,
        ]);
    }

    private function processAnswerValue($questionSlug, $answerValue)
    {
        switch ($questionSlug) {
            case 'comunidad_autonoma':
                if (is_numeric($answerValue)) {
                    $ccaa = Ccaa::find($answerValue);

                    return $ccaa ? $ccaa->id : $answerValue;
                }
                $ccaa = Ccaa::where('nombre_ccaa', $answerValue)->first();

                return $ccaa ? $ccaa->id : $answerValue;

            case 'provincia':
                if (is_numeric($answerValue)) {
                    $provincia = Provincia::find($answerValue);

                    return $provincia ? $provincia->id : $answerValue;
                }
                $provincia = Provincia::where('nombre_provincia', $answerValue)->first();

                return $provincia ? $provincia->id : $answerValue;

            case 'municipio':
                if (is_numeric($answerValue)) {
                    $municipio = Municipio::find($answerValue);

                    return $municipio ? $municipio->id : $answerValue;
                }
                $municipio = Municipio::where('nombre_municipio', $answerValue)->first();

                return $municipio ? $municipio->id : $answerValue;

            default:
                return $answerValue;
        }
    }

    public function getOptions(Request $request)
    {
        $type = $request->input('type');
        $parentId = $request->input('parent_id');

        switch ($type) {
            case 'comunidad_autonoma':
                $options = Ccaa::orderBy('nombre_ccaa')->get()->mapWithKeys(function ($ccaa) {
                    return [$ccaa->id => $ccaa->nombre_ccaa];
                });
                break;

            case 'provincia':
                if ($parentId && $parentId !== '') {
                    $options = Provincia::where('id_ccaa', $parentId)
                        ->orderBy('nombre_provincia')
                        ->get()
                        ->mapWithKeys(function ($provincia) {
                            return [$provincia->id => $provincia->nombre_provincia];
                        });
                } else {
                    $options = Provincia::orderBy('nombre_provincia')->get()->mapWithKeys(function ($provincia) {
                        return [$provincia->id => $provincia->nombre_provincia];
                    });
                }
                break;

            case 'municipio':
                if ($parentId && $parentId !== '') {
                    $options = Municipio::where('provincia_id', $parentId)
                        ->orderBy('nombre_municipio')
                        ->get()
                        ->mapWithKeys(function ($municipio) {
                            return [$municipio->id => $municipio->nombre_municipio];
                        });
                } else {
                    $options = Municipio::orderBy('nombre_municipio')->get()->mapWithKeys(function ($municipio) {
                        return [$municipio->id => $municipio->nombre_municipio];
                    });
                }
                break;

            default:
                return response()->json(['error' => 'Tipo no válido'], 400);
        }

        return response()->json(['options' => $options]);
    }

    public function deleteAnswer(Request $request, User $user, $answerId)
    {
        if ($answerId === 'new') {
            return response()->json(['error' => 'No se puede eliminar una respuesta que no existe'], 400);
        }

        $answer = Answer::findOrFail($answerId);

        if ($answer->user_id !== $user->id) {
            return response()->json(['error' => 'Respuesta no válida'], 403);
        }

        $answer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Respuesta eliminada correctamente',
        ]);
    }

    /**
     * Almacena una nueva comunicación operativa manual
     */
    public function storeComunicacion(Request $request, User $user)
    {
        $request->validate([
            'tipo_comunicacion' => 'required|in:WhatsApp,Email,Llamada',
            'fecha_hora' => 'required|date',
            'direction' => 'required|in:in,out',
            'subject' => 'nullable|string|max:255',
        ]);

        try {
            $comunicacion = ComunicacionOperativa::create([
                'user_id' => $user->id,
                'tramitador_id' => Auth::id(),
                'tipo_comunicacion' => $request->tipo_comunicacion,
                'fecha_hora' => $request->fecha_hora,
                'auto' => false, // Comunicación manual
                'subject' => $request->subject,
                'direction' => $request->direction,
            ]);

            $comunicacion->load('tramitador');

            return response()->json([
                'success' => true,
                'message' => 'Comunicación operativa creada correctamente',
                'comunicacion' => [
                    'id' => $comunicacion->id,
                    'tipo_comunicacion' => $comunicacion->tipo_comunicacion,
                    'fecha_hora' => $comunicacion->fecha_hora,
                    'direction' => $comunicacion->direction,
                    'subject' => $comunicacion->subject,
                    'auto' => $comunicacion->auto,
                    'tramitador_email' => $comunicacion->tramitador->email ?? 'N/A',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la comunicación: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Elimina una comunicación operativa
     */
    public function deleteComunicacion(Request $request, User $user, $comunicacionId)
    {
        try {
            $comunicacion = ComunicacionOperativa::where('id', $comunicacionId)
                ->where('user_id', $user->id)
                ->first();

            if (! $comunicacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comunicación no encontrada',
                ], 404);
            }

            if ($comunicacion->tramitador_id !== Auth::id() && ! Auth::user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar esta comunicación',
                ], 403);
            }

            $comunicacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comunicación eliminada correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la comunicación: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Registra una comunicación operativa automática (para los botones de WhatsApp y llamada)
     */
    public function registrarComunicacionOperativa(Request $request, User $user)
    {
        $request->validate([
            'tipo_comunicacion' => 'required|in:WhatsApp,Llamada',
        ]);

        try {
            $comunicacion = ComunicacionOperativa::create([
                'user_id' => $user->id,
                'tramitador_id' => Auth::id(),
                'tipo_comunicacion' => $request->tipo_comunicacion,
                'fecha_hora' => now(),
                'auto' => true, // Comunicación automática
                'subject' => 'Comunicación automática - '.$request->tipo_comunicacion,
                'direction' => 'out', // Siempre saliente para comunicaciones automáticas
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comunicación operativa registrada correctamente',
                'comunicacion' => $comunicacion,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la comunicación: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar usuarios por nombre, teléfono o email
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('q');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $telefonoQuestion = Question::where('slug', 'telefono')->first();
        $nifQuestion = Question::where('slug', 'dni_nie')->first();

        if (! $telefonoQuestion || ! $nifQuestion) {
            \Log::error('No se encontraron las preguntas de teléfono o NIF');

            return response()->json([]);
        }

        $users = User::where(function ($q) use ($query, $telefonoQuestion, $nifQuestion) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhereHas('answers', function ($subQ) use ($query, $telefonoQuestion) {
                    $subQ->where('question_id', $telefonoQuestion->id)
                        ->where('answer', 'like', "%{$query}%");
                })
                ->orWhereHas('answers', function ($subQ) use ($query, $nifQuestion) {
                    $subQ->where('question_id', $nifQuestion->id)
                        ->where('answer', 'like', "%{$query}%");
                });
        })
            ->select('id', 'name', 'email')
            ->with(['answers' => function ($q) use ($telefonoQuestion, $nifQuestion) {
                $q->whereIn('question_id', [$telefonoQuestion->id, $nifQuestion->id]);
            }])
            ->limit(20)
            ->get();

        $mappedUsers = $users->map(function ($user) use ($telefonoQuestion, $nifQuestion) {
            $telefono = $user->answers->where('question_id', $telefonoQuestion->id)->first()?->answer;
            $nif = $user->answers->where('question_id', $nifQuestion->id)->first()?->answer;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $telefono,
                'nif' => $nif,
            ];
        })->values();

        return response()->json($mappedUsers);
    }

    /**
     * Método de prueba para verificar que el controlador funciona
     */
    public function testSearch()
    {
        $user = User::whereHas('answers', function ($q) {
            $q->where('question_id', 45); // Teléfono
        })->with(['answers' => function ($q) {
            $q->where('question_id', 45);
        }])->first();

        if ($user) {
            $telefono = $user->answers->where('question_id', 45)->first()?->answer;

            return response()->json([
                'message' => 'Test exitoso',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $telefono,
                    'answers_count' => $user->answers->count(),
                ],
                'timestamp' => now()->toISOString(),
            ]);
        }

        return response()->json([
            'message' => 'No se encontró ningún usuario con teléfono',
            'timestamp' => now()->toISOString(),
        ]);
    }
}
