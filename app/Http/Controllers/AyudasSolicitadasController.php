<?php

namespace App\Http\Controllers;

use App\Helpers\SimulationHelper;
use App\Models\Answer;
use App\Models\Ayuda;
use App\Models\AyudaDocumento;
use App\Models\AyudaSolicitada;
use App\Models\Contratacion;
use App\Models\Conviviente;
use App\Models\Question;
use App\Models\QuestionCondition;
use App\Models\Questionnaire;
use App\Models\QuestionnaireQuestion;
use App\Models\User;
use App\Models\UserDocument;
use App\Services\ConvivientesDatosService;
use App\Services\CuestionarioCompletoService;
use App\Services\DocumentosAyudaService;
use App\Services\EstadoContratacionService;
use App\Services\EvaluadorAyudaService;
use App\Services\GcsUploaderService;
use App\Services\SolicitudFormularioService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AyudasSolicitadasController extends Controller
{
    protected DocumentosAyudaService $documentosAyudaService;

    protected SolicitudFormularioService $solicitudFormularioService;

    protected CuestionarioCompletoService $cuestionarioCompletoService;

    protected ConvivientesDatosService $ConvivientesDatosService;

    public function __construct(CuestionarioCompletoService $cuestionarioCompletoService, ConvivientesDatosService $convivientesDatosService, DocumentosAyudaService $documentosAyudaService, SolicitudFormularioService $solicitudFormularioService)
    {
        $this->cuestionarioCompletoService = $cuestionarioCompletoService;
        $this->ConvivientesDatosService = $convivientesDatosService;
        $this->solicitudFormularioService = $solicitudFormularioService;
        $this->documentosAyudaService = $documentosAyudaService;
    }

    /**
     * Rellena estado/fase en la contratación para la vista cuando vienen vacíos,
     * derivándolos de estados OPx (para compatibilidad con ayuda-card y ayuda-solicitada-detalle).
     */
    private function normalizarEstadoFaseParaVista(Contratacion $contratacion): void
    {
        if ($contratacion->estado !== null && $contratacion->fase !== null) {
            return;
        }
        $contratacion->load('estadosContratacion');
        $codigos = $contratacion->estadosContratacion->pluck('codigo')->all();
        if (empty($codigos)) {
            return;
        }
        if (in_array('OP1-Resolucion', $codigos, true)) {
            $contratacion->estado = $contratacion->estado ?? 'cierre';
            $contratacion->fase = $contratacion->fase ?? 'resolucion';
        } elseif (in_array('OP1-Tramitacion', $codigos, true)) {
            $contratacion->estado = $contratacion->estado ?? 'tramitacion';
            $contratacion->fase = $contratacion->fase ?? 'en_seguimiento';
        } elseif (in_array('OP1-Documentacion', $codigos, true)) {
            $contratacion->estado = $contratacion->estado ?? 'documentacion';
            $contratacion->fase = $contratacion->fase ?? 'documentacion';
        }
    }

    public function index()
    {
        $user = SimulationHelper::getCurrentUser();

        $ayudasSolicitadas = Contratacion::with([
            'ayuda.organo',
            'ayuda.enlaces',
            'estadosContratacion',
            'subsanacionDocumentos.document',
            'motivosSubsanacionContrataciones.motivo.document',
            'ayuda.questionnaires' => function ($query) {
                $query->whereIn('tipo', ['conviviente', 'solicitud'])
                    ->with('questionConditions');
            },
        ])
            ->where('user_id', $user->id)
            ->orderBy('fecha_contratacion', 'desc')
            ->get();

        $documentacionCount = $ayudasSolicitadas
            ->filter(fn ($c) => $c->estadosContratacion->contains('codigo', 'OP1-Documentacion'))
            ->count();

        // Usar el método del modelo User para obtener las respuestas
        $answers = $user->obtenerRespuestas();
        $userDocuments = UserDocument::where('user_id', $user->id)->get();

        $ayudaIds = $ayudasSolicitadas->pluck('ayuda_id')->unique()->toArray();
        $questionnairesByAyuda = [];
        if (! empty($ayudaIds)) {
            $questionnaires = Questionnaire::whereIn('ayuda_id', $ayudaIds)
                ->whereIn('tipo', ['conviviente', 'solicitud'])
                ->get()
                ->groupBy('ayuda_id');

            foreach ($questionnaires as $ayudaId => $qList) {
                $questionnairesByAyuda[$ayudaId] = $qList->keyBy('tipo');
            }
        }

        $nConvivientes = Conviviente::countByUser($user->id);
        $estadoPrincipal = [];
        $datosConvivientes = [
            'convivientes' => [],
            'estadoConvivientes' => [],
        ];

        $sector_ayuda = $ayudasSolicitadas->first()?->ayuda?->sector ?? null;

        $convivienteConditionsPorQuestionnaire = [];

        foreach ($ayudasSolicitadas as $ayudaSolicitada) {
            $questionnairesAyuda = $questionnairesByAyuda[$ayudaSolicitada->ayuda_id] ?? collect();
            $convivienteQId = $questionnairesAyuda->get('conviviente')?->id;
            $solicitudQId = $questionnairesAyuda->get('solicitud')?->id;

            $this->evaluarYActualizarEstadoContratacion($ayudaSolicitada, $user, $answers, $userDocuments, $solicitudQId, $convivienteQId);
            // Las respuestas ya están en el formato correcto con question_id como claves
            $answersArray = $answers;

            // obtenemos el id del fomulario convivientes de la ayuda
            $convivienteQuestionnaire = $ayudaSolicitada->ayuda->questionnaires
                ->firstWhere('tipo', 'conviviente');

            // Obtener condiciones de convivientes para este questionnaire
            $convivienteQuestionnaireId = $convivienteQuestionnaire?->id;
            $ayudaSolicitada->formConvivientesId = $convivienteQuestionnaireId;

            if ($convivienteQuestionnaireId && $convivienteQuestionnaire) {
                $conditions = $convivienteQuestionnaire->questionConditions
                    ->map(function ($condition) {
                        // Usar el nuevo formato (operator + value) si existe, sino usar el formato antiguo (condition como JSON)
                        $conditionData = null;

                        if ($condition->operator && $condition->value !== null) {
                            // Nuevo formato: convertir operator y value a formato de condición
                            $value = is_array($condition->value) ? $condition->value : [$condition->value];
                            $conditionData = $value;
                        } elseif ($condition->condition) {
                            // Formato antiguo: condition como JSON
                            $conditionData = json_decode($condition->condition, true);
                        }

                        return [
                            'question_id' => $condition->question_id,
                            'condition' => $conditionData,
                            'operator' => $condition->operator ?? null,
                            'value' => $condition->value ?? null,
                            'next_question_id' => $condition->next_question_id,
                        ];
                    })
                    ->toArray();

                // Usar el questionnaire_id como string para asegurar que se convierta a objeto en JSON
                $convivienteConditionsPorQuestionnaire[(string) $convivienteQuestionnaireId] = $conditions;
            }

            // ---------------Documentos------------------------------------------------------------------------
            $documentosDatos = $this->documentosAyudaService->obtenerDocumentosAyuda($user->id, $ayudaSolicitada, $answersArray, $sector_ayuda);
            $ayudaSolicitada->user_documents = $documentosDatos['user_documents'];
            $ayudaSolicitada->documentos_subidos = $documentosDatos['documentos_subidos'];
            $ayudaSolicitada->documentos_faltantes = $documentosDatos['documentos_faltantes'];
            $ayudaSolicitada->recibos_subidos = $documentosDatos['recibos_subidos'];
            $ayudaSolicitada->documentos_configurados = $documentosDatos['documentos_configurados'];

            // Obtener documentos de convivientes que cumplen condiciones
            $ayudaSolicitada->documentos_convivientes_con_condiciones = $this->documentosAyudaService->obtenerDocumentosConvivientesConCondiciones(
                $ayudaSolicitada->ayuda->id,
                $user->id,
                $answersArray
            );

            // Obtener condiciones de documentos para evaluación dinámica en frontend
            $ayudaSolicitada->condiciones_documentos = $this->documentosAyudaService->obtenerCondicionesDocumentos(
                $ayudaSolicitada->ayuda->id
            );

            // -------------------------CONVIVIENTES--------------------------------
            $datosConvivientes = $this->ConvivientesDatosService->obtenerDatosConvivientes(
                $user->id,
                $ayudaSolicitada,
                $convivienteQuestionnaire
            );

            // Asignar convivientes con su estado de completitud a esta ayuda específica
            $ayudaSolicitada->convivientes = $datosConvivientes['convivientes'];

            // -----------------------------Solicitud----------------------------------------------------

            $gruposVulnerablesSeleccionados = $this->obtenerGruposVulnerables($user->id);
            $solicitudQuestionnaire = $ayudaSolicitada->ayuda->questionnaires
                ->firstWhere('tipo', 'solicitud');

            $datosSolicitud = $this->solicitudFormularioService->obtenerDatosSolicitud(
                $user->id,
                $ayudaSolicitada,
                $gruposVulnerablesSeleccionados,
                $solicitudQuestionnaire
            );
            if ($datosSolicitud) {
                $ayudaSolicitada->questions_solicitud = $datosSolicitud['preguntas'];
                $questionnaire_id = $datosSolicitud['solicitudQuestionnaireId'] ?? 0;
                // Usar el ID de la ayuda solicitada como índice para que coincida con la vista
                $estadoPrincipal[$ayudaSolicitada->id] = $datosSolicitud['estado'];
            }
        }

        // CCAA del usuario (pregunta 38)
        $user_ccaa = Answer::where('user_id', $user->id)
            ->whereNull('conviviente_id')
            ->where('question_id', 38)
            ->value('answer');

        $today = Carbon::today()->toDateString();

        // Fetch de todas las ayudas activas + filtro por CCAA + orden por fechas
        $ayudas = Ayuda::with(['cuestionarioPrincipal.questions'])
            ->where('activo', 1)
            ->when($user_ccaa, function ($q) use ($user_ccaa) {
                $q->where(function ($q2) use ($user_ccaa) {
                    $q2->where('ccaa_id', $user_ccaa)
                        ->orWhereNull('ccaa_id');
                });
            })
            ->orderByRaw('(fecha_inicio_periodo <= ? AND fecha_fin_periodo >= ?) DESC', [$today, $today])
            ->orderByRaw('CASE WHEN fecha_fin_periodo IS NULL THEN 1 ELSE 0 END')
            ->orderBy('fecha_fin_periodo', 'asc')
            ->get();

        // IDs de ayudas ya contratadas por el usuario
        $contratacionesIds = Contratacion::where('user_id', $user->id)
            ->pluck('ayuda_id')
            ->toArray();

        // Filtrar con el servicio EvaluadorAyudaService
        $evaluator = app(EvaluadorAyudaService::class);

        $ayudasFiltradas = $ayudas
            ->filter(function ($ayuda) use ($evaluator, $user, $contratacionesIds) {
                return $evaluator->posiblesAyudas($ayuda->id, $user->id)
                    && ! in_array($ayuda->id, $contratacionesIds);
            })
            ->map(function ($ayuda) use ($contratacionesIds) {
                // igual que en tu controlador principal
                $ayuda->yaComenzada = in_array($ayuda->id, $contratacionesIds);

                return $ayuda;
            });

        // ----------------------------------Preform conviviente----------------------------------------------
        $answerQuestionSituacionAlquiler = $answers[1] ?? null;
        $preFormConviviente = false;
        $preguntasPreForm = [];
        $ultimaAyudaSolicitada = $ayudasSolicitadas->last();
        $ayudaTieneFormConvivientes = $ultimaAyudaSolicitada
            ? $ultimaAyudaSolicitada->ayuda->questionnaires()->where('tipo', 'conviviente')->exists()
            : false;

        if (($answerQuestionSituacionAlquiler === 'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato' ||
            $answerQuestionSituacionAlquiler === 'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato'
            || $answerQuestionSituacionAlquiler === 'No soy parte del contrato, pero vivo de alquiler en una vivienda completa') && $nConvivientes == 0) {

            $preFormConviviente = true;
            $slugs = [
                'personas-vivienda',
            ];

            $preguntasPreForm = Question::whereIn('slug', $slugs)
                ->get()
                ->sortBy(function ($item) use ($slugs) {
                    return array_search($item->slug, $slugs);
                })
                ->values();
        }

        // Para la vista de la tarjeta (ayuda-card) derivar estado/fase desde OPx si vienen vacíos
        foreach ($ayudasSolicitadas as $c) {
            $this->normalizarEstadoFaseParaVista($c);
        }

        return view('user.ayudas-solicitadas', [
            'ayudasSolicitadas' => $ayudasSolicitadas,
            'ayudas' => $ayudasFiltradas,
            'nConvivientes' => $nConvivientes,
            'convivientes' => $datosConvivientes['convivientes'],
            'sector_ayuda' => $sector_ayuda,
            'convivienteConditions' => $convivienteConditionsPorQuestionnaire,
            'estadoPrincipal' => $estadoPrincipal,
            'estadoConvivientes' => $datosConvivientes['estadoConvivientes'],
            'documentacionCount' => $documentacionCount,
            'ayudaTieneFormConvivientes' => $ayudaTieneFormConvivientes,
            'preguntasPreForm' => $preguntasPreForm,
            'preFormConviviente' => $preFormConviviente,
        ]);
    }

    /**
     * Obtiene los slugs de los documentos especiales condicionales
     * que el usuario debe subir a partir de sus respuestas en el cuestionario.
     *
     * @param  int  $ayudaId  ID de la ayuda.
     * @param  array  $answers  Respuestas del cuestionario.
     * @return array Lista de slugs de los documentos especiales.
     */
    private function obtenerSlugsDocumentosEspecialesCondicionales($ayudaId, $answers)
    {

        $ayuda = Ayuda::with('questionnaire.questions')->find($ayudaId);

        if (! $ayuda || ! $ayuda->questionnaire) {
            return [];
        }

        $preguntas = $ayuda->questionnaire->questions;
        // Añadirlas a la colección original
        $slugsFaltantes = ['propietario-vivienda', 'situaciones-propietario'];

        $slugsExistentes = $preguntas->pluck('slug')->all();

        $slugsQueFaltan = array_diff($slugsFaltantes, $slugsExistentes);

        if (! empty($slugsQueFaltan)) {
            $preguntasFaltantes = Question::whereIn('slug', $slugsQueFaltan)->get();
            $preguntas = $preguntas->concat($preguntasFaltantes);
        }

        // aqui obtener las preguntas de vulnerabilidad
        // filtramos las pregunta relacionadas con la vulnerabilidad que nos interesan desde el punto de vista de los documentos especiales condicionales
        // y estas son seleccionadas desde el cuestionario
        $slugsObjetivo = [
            'grupo_considerado_vulnerable',
            'cual',
            'cual_desahucio',
            'cual_viogen',
            'cual_situacion_familia',
            'situaciones-propietario',
            'situaciones-conviviente-propietario',
            'tiene_viviendas',
            'propietario-vivienda',
        ];

        // Mapeo slug -> question_id desde la tabla global `questions`
        $pregsNecesarias = Question::query()
            ->whereIn(DB::raw('TRIM(slug)'), $slugsObjetivo)
            ->get(['id', 'slug']);

        $slugToId = $pregsNecesarias
            ->mapWithKeys(fn ($q) => [trim($q->slug) => (int) $q->id])
            ->all();

        // Construir answers por slug a partir del $answers que recibes (keyed por question_id)
        $answersVulnerabilidad = collect($slugToId)->mapWithKeys(function ($qid, $slug) use ($answers) {
            $val = $answers instanceof Collection
                ? $answers->get($qid)
                : ($answers[$qid] ?? null);

            return [$slug => $val];
        });

        $documentosEspeciales = DB::table('ayuda_documentos')
            ->where('ayuda_id', $ayudaId)
            ->join('documents', 'ayuda_documentos.documento_id', '=', 'documents.id')
            ->where('documents.tipo', 'especial')
            ->select('documents.slug')
            ->get()
            ->pluck('slug')
            ->toArray();

        $slugs = [];

        $asArray = function ($v): array {
            if ($v === null || $v === '') {
                return [];
            }
            if (is_array($v)) {
                return $v;
            }
            if (is_string($v)) {
                $d = json_decode($v, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($d)) {
                    return $d;
                }

                return [$v];
            }

            return [$v];
        };

        // comprobamos si en answersVulnerabilidad hay algun grupo vulnerable del
        // tipo familia,otros,situacion,especial ha sido seleccionado y añadimos el slug del domcumento necesario
        // Normaliza antes de comparar
        $grupoVals = $asArray($answersVulnerabilidad->get('grupo_considerado_vulnerable') ?? null);
        $familia = $asArray($answersVulnerabilidad->get('cual') ?? null);

        // Si el grupo incluye la categoría de familia/discapacidad O si en "cual" ya aparecen las opciones
        if (
            in_array('Familia numerosa, monoparental, persona con discapacidad ±33%', $grupoVals, true) ||
            ! empty(array_intersect($familia, [
                'Familia numerosa',
                'Familia numerosa especial',
                'Persona con discapacidad reconocida inferior o igual al 33%',
                'Persona con discapacidad reconocida superior al 33%',
                'Familia monoparental',
                'Familia monoparental especial',
            ]))
        ) {
            if (in_array('Familia numerosa', $familia, true)) {
                $slugs[] = 'certificado-familia-numerosa';
            }
            if (in_array('Familia numerosa especial', $familia, true)) {
                $slugs[] = 'certificado-familia-numerosa'; // cambia si tenéis slug específico
            }
            if (
                in_array('Persona con discapacidad reconocida inferior o igual al 33%', $familia, true) ||
                in_array('Persona con discapacidad reconocida superior al 33%', $familia, true)
            ) {
                $slugs[] = 'certificado-discapacidad';
            }
            if (
                in_array('Familia monoparental', $familia, true) ||
                in_array('Familia monoparental especial', $familia, true)
            ) {
                $slugs[] = 'certificado-familia-monoparental';
            }
        }

        // --- VIOLENCIA / EXCLUSIÓN / EXTUTELADO / EXCONVICTO
        $grupoViogen = in_array(
            'Víctima de violencia de género, trata de explotación sexual, de violencia sexual, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a',
            $grupoVals,
            true
        );
        $viogen = $asArray($answersVulnerabilidad->get('cual_viogen') ?? null);

        if ($grupoViogen || ! empty($viogen)) {
            if (in_array('He sido víctima de violencia de género', $viogen, true)) {
                $slugs[] = 'certificado-violencia-genero';
            }
            if (in_array('He sido víctima de terrorismo', $viogen, true)) {
                $slugs[] = 'certificado-victima-terrorismo';
            }
            if (in_array('Estoy en riesgo de exclusión social', $viogen, true)) {
                $slugs[] = 'certificado-riesgo-exclusion-social';
            }
            if (in_array('Soy joven extutelado/a', $viogen, true)) {
                $slugs[] = 'certificado-centro-residencial-menores';
            }
            if (in_array('He estado en prisión (exconvicto/a)', $viogen, true)) {
                $slugs[] = 'certificado-exconvicto';
            }
        }

        // --- UNIDAD EN DESEMPLEO CON PRESTACIONES AGOTADAS
        $grupoDesempleo = in_array(
            'Toda la unidad de convivencia está desempleada y hayan agotado las prestaciones',
            $grupoVals,
            true
        );
        $sitFam = $asArray($answersVulnerabilidad->get('cual_situacion_familia') ?? null); // <-- slug correcto

        if ($grupoDesempleo || in_array('Toda la unidad de convivencia está desempleada y hayan agotado las prestaciones', $sitFam, true)) {
            $slugs[] = 'certificado-situacion-desempleo';
        }

        // --- DESAHUCIO / EJECUCIÓN / DACIÓN / CATASTRÓFICA
        $grupoDesahucio = in_array(
            'Desahucio, ejecución hipotecaria o dación en pago de tu vivienda, en los últimos cinco años, o afectado/a por situación catastrófica',
            $grupoVals,
            true
        );
        $desahucio = $asArray($answersVulnerabilidad->get('cual_desahucio') ?? null);

        if ($grupoDesahucio || ! empty($desahucio)) {
            if (in_array('He sido desahuciado/a de mi vivienda habitual', $desahucio, true)) {
                $slugs[] = 'certificado-desahucio';
            }
            if (in_array('Perdí mi vivienda por una ejecución hipotecaria o porque la entregué al banco en los últimos cinco años', $desahucio, true)) {
                $slugs[] = 'certificado-dacion-pago';
            }
            if (in_array('He sido afectado/a por una situación catastrófica (inundación, incendio, terremoto, etc.)', $desahucio, true)) {
                $slugs[] = 'certificado-situacion-catastrofica';
            }
        }

        // !Quizas falta unos documentos para las opciones de la question  grupo_considerado_vulnerable

        // !"Fallecimiento de ambos padres, personas sin hogar, en trámites de separación o divorcio",

        // !"Toda la unidad de convivencia está desempleada y hayan agotado las prestaciones",

        // !"Toda la unidad de convivencia está desempleada y hayan agotado las prestaciones",

        // !"Persona que asuma acogimiento familiar permanente de menor",

        // !"¿Estás sujeto al Plan de protección internacional de Catalunya aprobado por el Acuerdo de gobierno de 28 de enero de 2014? (Solicitante de asilo, tarjeta roja…)"

        /**************************************************
         * Comprobamos si es propiertario de una vivienda *
         * y cual es su situación y añadimos el documento *
         * necesarios segun su situación                  *
         * ***********************************************/
        $esPropietario = $answersVulnerabilidad->get('tiene_viviendas');

        $situacionesPropietario = (array) $answersVulnerabilidad->get('situaciones-propietario');

        if ((int) $esPropietario === 1 && ! empty($situacionesPropietario)) {

            if (! in_array('Ninguna de las anteriores', $situacionesPropietario)) {
                if (in_array('Separación o divorcio', $situacionesPropietario)) {
                    $slugs[] = 'resolucion_divorcio_separacion';
                }
                if (in_array('Propietario por herencia de una parte de la casa', $situacionesPropietario)) {
                    $slugs[] = 'nota-simple';
                }
                if (
                    in_array('Propiedad inaccesible por discapacidad tuya o de algún miembro de tu unidad de convivencia', $situacionesPropietario) ||
                    in_array('No puedes acceder a casa por cualquier causa ajena a tu voluntad', $situacionesPropietario)
                ) {
                    $slugs[] = 'justificante_imposibilidad_habitar_vivienda';
                }
            }
        }

        return array_values(array_intersect($slugs, $documentosEspeciales));
    }

    /**
     * Muestra el cuestionario de convivientes para
     * un conviviente específico basado en su índice.
     * Las preguntas obligatorias se definen en el array
     * preguntasObligatorias y las preguntasFormulario
     * se obtienen de la base de datos. Se realiza una
     * intersección para determinar las preguntas.
     *
     * @param  int  $questionnaireId
     * @param  int  $index
     * @return view
     */
    public function showConvivientes($questionnaireId, $index): \Illuminate\Contracts\View\View
    {
        $userId = Auth::id();

        // Buscar el cuestionario de tipo 'conviviente' y su ayuda asociada
        $questionnaire = Questionnaire::with('ayuda')
            ->where('id', $questionnaireId)
            ->where('tipo', 'conviviente')
            ->firstOrFail();

        $ayuda = $questionnaire->ayuda;

        // Obtener los IDs de preguntas del cuestionario
        $questionIds = DB::table('questionnaire_questions')
            ->where('questionnaire_id', $questionnaireId)
            ->pluck('question_id');

        // Buscar conviviente (si existe)
        $conviviente = Conviviente::where('user_id', $userId)
            ->where('index', $index)
            ->first();

        // Obtener respuestas del conviviente
        if ($conviviente) {
            $answersConviviente = Answer::where('user_id', $userId)
                ->where('conviviente_id', $conviviente->id)
                ->whereIn('question_id', $questionIds)
                ->get()
                ->keyBy('question_id');
        } else {
            $answersConviviente = collect(); // No existe conviviente → respuestas vacías
        }

        // Obtener respuestas del solicitante (para condiciones que puedan referenciarlas)
        $answersSolicitante = Answer::where('user_id', $userId)
            ->whereNull('conviviente_id')
            ->get()
            ->keyBy('question_id');

        // Combinar respuestas (conviviente tiene prioridad)
        $answers = $answersConviviente->merge($answersSolicitante);

        // Calcular preguntas finales: solo las que son visibles tras aplicar condiciones
        $preguntasFinales = $this->obtenerPreguntasVisiblesObligatorias($questionnaireId, $answers);

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

        // Convertir respuestas del conviviente a array para uso en el map y en la vista
        // IMPORTANTE: Solo usar respuestas del conviviente, no del solicitante
        $answersArray = $answersConviviente->pluck('answer', 'question_id')->toArray();

        // Mapear preguntas
        $mappedQuestions = $questions->map(function ($q) use ($answersArray, $regex, $gruposVulnerablesSeleccionados) {
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
                'text' => $q->text,
                'subtext' => $q->sub_text,
                'type' => $q->type,
                'slug' => $q->slug,
                'options' => $options,
                'answer' => $answersArray[$q->id] ?? null,
                'disable_answer' => $q->disable_answer,
                'validation' => [
                    'pattern' => $regex[$q->id]->pattern ?? null,
                    'error_message' => $regex[$q->id]->error_message ?? null,
                ],
            ];
        });

        // Renderizar vista
        return view('components.ayuda-card.conviviente-modal', [
            'questions' => $mappedQuestions,
            'answers' => $answersArray,
            'conditions' => $conditions,
            'convivienteIndex' => $index,
            'questionnaireId' => $questionnaireId,
            'ayuda' => $ayuda,
            'preguntasFinales' => $preguntasFinales,
            // Si existe el conviviente, usamos su nombre completo para mostrarlo en el modal
            'convivienteNombre' => $conviviente ? $conviviente->nombre() : null,
        ]);
    }

    /**
     * Obtiene las preguntas obligatorias que son visibles tras aplicar las condiciones.
     *
     * @param  int  $questionnaireId  ID del cuestionario
     * @param  Collection  $answers  Respuestas del usuario (conviviente + solicitante)
     * @return array Array de IDs de preguntas que son visibles y obligatorias
     */
    private function obtenerPreguntasVisiblesObligatorias(int $questionnaireId, Collection $answers): array
    {
        // Obtener todas las preguntas del cuestionario que están marcadas como visibles
        $preguntas = QuestionnaireQuestion::where('questionnaire_id', $questionnaireId)
            ->where('is_visible', 1)
            ->with(['question'])
            ->get();

        $preguntasVisibles = [];

        foreach ($preguntas as $qq) {
            $question = $qq->question;

            // Omitir si no requiere respuesta
            if ($question->type === 'info' || $question->disable_answer) {
                continue;
            }

            // Comprobar condiciones de visibilidad
            $conditions = QuestionCondition::where('questionnaire_id', $questionnaireId)
                ->where('next_question_id', $question->id)
                ->get();

            $mostrar = true;

            // Si tiene condiciones, evaluarlas
            if ($conditions->isNotEmpty()) {
                foreach ($conditions as $cond) {
                    // Usar el nuevo formato (operator + value) si existe, sino usar el formato antiguo (condition como JSON)
                    $expectedValues = null;

                    if ($cond->operator && $cond->value !== null) {
                        // Nuevo formato: usar operator y value
                        $value = is_array($cond->value) ? $cond->value : [$cond->value];
                        $expectedValues = $value;
                    } elseif ($cond->condition) {
                        // Formato antiguo: condition como JSON
                        $expectedValues = json_decode($cond->condition, true);
                        if (! is_array($expectedValues)) {
                            continue;
                        }
                    } else {
                        continue;
                    }

                    $answerModel = $answers->get($cond->question_id);
                    $respuesta = $answerModel?->answer;

                    // Decodificar JSON si es necesario
                    if (is_string($respuesta)) {
                        $decoded = json_decode($respuesta, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $respuesta = $decoded;
                        }
                    }

                    // Normalizar la respuesta
                    $respuestaNormalizada = null;

                    if ($respuesta === null || $respuesta === '') {
                        if (is_array($expectedValues) && in_array(0, $expectedValues, true)) {
                            $respuestaNormalizada = 0;
                        } else {
                            $mostrar = false;
                            break;
                        }
                    } elseif (is_numeric($respuesta)) {
                        $respuestaNormalizada = (int) $respuesta;
                    } elseif (is_array($respuesta)) {
                        // Si la respuesta es un array, usar el primer valor para comparaciones numéricas
                        // Para comparaciones de igualdad, se manejará en el switch
                        $respuestaNormalizada = $respuesta;
                    } else {
                        $respuestaNormalizada = $respuesta;
                    }

                    $expectedValuesNormalizados = array_map(function ($val) {
                        return is_numeric($val) ? (int) $val : $val;
                    }, $expectedValues);

                    $operador = $cond->operator ?? '==';

                    switch ($operador) {
                        case '==':
                        case '=':
                            if (is_array($respuestaNormalizada)) {
                                // Si la respuesta es un array, verificar si contiene alguno de los valores esperados
                                $mostrar = ! empty(array_intersect($respuestaNormalizada, $expectedValuesNormalizados));
                            } else {
                                $mostrar = in_array($respuestaNormalizada, $expectedValuesNormalizados, true);
                            }
                            break;
                        case '!=':
                            if (is_array($respuestaNormalizada)) {
                                // Si la respuesta es un array, verificar que NO contenga ninguno de los valores esperados
                                $mostrar = empty(array_intersect($respuestaNormalizada, $expectedValuesNormalizados));
                            } else {
                                $mostrar = ! in_array($respuestaNormalizada, $expectedValuesNormalizados, true);
                            }
                            break;
                        case '>':
                        case '>=':
                        case '<':
                        case '<=':
                            // Para comparaciones numéricas, usar el primer valor si es array
                            $valorComparar = is_array($respuestaNormalizada) ? ($respuestaNormalizada[0] ?? null) : $respuestaNormalizada;
                            if ($valorComparar === null || ! is_numeric($valorComparar)) {
                                $mostrar = false;
                                break;
                            }
                            $valorComparar = (int) $valorComparar;
                            $valorEsperado = (int) $expectedValuesNormalizados[0];

                            $mostrar = match ($operador) {
                                '>' => $valorComparar > $valorEsperado,
                                '>=' => $valorComparar >= $valorEsperado,
                                '<' => $valorComparar < $valorEsperado,
                                '<=' => $valorComparar <= $valorEsperado,
                                default => false,
                            };
                            break;
                        default:
                            if (is_array($respuestaNormalizada)) {
                                $mostrar = ! empty(array_intersect($respuestaNormalizada, $expectedValuesNormalizados));
                            } else {
                                $mostrar = in_array($respuestaNormalizada, $expectedValuesNormalizados, true);
                            }
                    }

                    // Si la condición no se cumple, salir del bucle (lógica AND: todas deben cumplirse)
                    if (! $mostrar) {
                        break;
                    }
                }
            }

            // Si la pregunta es visible, agregarla a la lista
            if ($mostrar) {
                $preguntasVisibles[] = $question->id;
            }
        }

        return $preguntasVisibles;
    }

    /**
     * Devuelve los datos del formulario de conviviente con builders para Vue
     */
    public function getConvivienteBuilderForm($questionnaireId, $index)
    {
        $userId = Auth::id();

        // Obtener los IDs de preguntas del cuestionario
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
            $answers = collect();
        }

        // Obtener las preguntas ordenadas
        $questions = Question::whereIn('questions.id', $questionIds)
            ->join('questionnaire_questions', 'questions.id', '=', 'questionnaire_questions.question_id')
            ->where('questionnaire_questions.questionnaire_id', $questionnaireId)
            ->orderBy('questionnaire_questions.orden')
            ->select('questions.*', 'questionnaire_questions.orden')
            ->get();

        // Validaciones regex
        $regex = DB::table('regex')
            ->join('questions', 'questions.regex_id', '=', 'regex.id')
            ->whereIn('questions.id', $questionIds)
            ->select('questions.id as question_id', 'regex.pattern', 'regex.error_message')
            ->get()
            ->keyBy('question_id');

        // Obtener condiciones
        // Usar el método unificado del modelo (formato nuevo: operator + value)
        $conditions = QuestionCondition::getConditions($questionnaireId);

        // Mapear preguntas
        $mappedQuestions = $questions->map(function ($q) use ($answers, $regex) {
            $options = [];

            if (in_array($q->type, ['select', 'multiple', 'radio', 'checkbox'])) {
                $options = is_string($q->options) ? json_decode($q->options, true) ?? [] : $q->options;
            }

            return [
                'id' => $q->id,
                'slug' => $q->slug,
                'text' => $q->text,
                'text_conviviente' => $q->text_conviviente,
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

        return response()->json([
            'questions' => $mappedQuestions,
            'answers' => $answers->toArray(),
            'conditions' => $conditions,
            'convivienteNombre' => $conviviente ? $conviviente->nombre() : null,
        ]);
    }

    /**
     * Guarda las respuestas del conviviente y la firma
     * del formulario de convivientes de ayudas solicitadas
     * o del formulario de conviviente del enlace temporal
     * enviado por parte del usuario.
     *
     * @return Response
     */
    public function storeConviviente(Request $request)
    {

        $request->validate([
            'index' => 'required|integer',
            'questionnaire_id' => 'required|integer',
            'answers' => 'required|array',
            'firma_base64' => 'nullable|string',
            'document_id' => 'nullable|integer',
        ]);

        $userId = Auth::id();
        $index = $request->input('index');
        $answers = $request->input('answers');

        // 1️⃣ Crear o actualizar el conviviente
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

        // No filtrar -1 aquí, se manejará por tipo de pregunta
        // Solo filtrar valores completamente vacíos
        $filteredAnswers = array_filter($answers, function ($value) {
            return $value !== null && $value !== '';
        });

        foreach ($filteredAnswers as $questionId => $answerValue) {
            // Eliminar respuestas anteriores
            Answer::where('user_id', $userId)
                ->where('conviviente_id', $conviviente->id)
                ->where('question_id', $questionId)
                ->delete();

            // Obtener la pregunta
            $question = Question::find($questionId);

            if (! $question) {
                continue;
            }

            // Preparar la respuesta
            $answerToSave = null;

            if ($question->type == 'select') {
                // Para select, guardar como JSON si es array, sino como string
                $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                if (is_array($answerValue)) {
                    $answerToSave = array_map(function ($id) use ($options) {
                        return $options[$id] ?? $id;
                    }, $answerValue);
                    $answerToSave = json_encode($answerToSave);
                } else {
                    $answerToSave = $options[$answerValue] ?? $answerValue;
                    // Si el valor es un array después de mapear, convertir a JSON
                    if (is_array($answerToSave)) {
                        $answerToSave = json_encode($answerToSave);
                    }
                }
            } elseif ($question->type == 'multiple') {
                // Para multiple, evitar doble encoding
                // -1 es válido porque representa "Ninguna de las anteriores" y se guarda directamente como -1
                $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                if (is_array($answerValue)) {
                    // Filtrar solo valores nulos o vacíos (permitir -1)
                    $filtered = array_filter($answerValue, fn ($v) => $v !== null && $v !== '');
                    if (empty($filtered)) {
                        continue; // No guardar si está vacío
                    }

                    // Si contiene -1, guardar solo -1 directamente
                    if (in_array(-1, $filtered) || in_array('-1', $filtered)) {
                        $answerToSave = '-1';
                    } else {
                        // Mapear keys a valores de opciones
                        $mappedValues = [];
                        foreach ($filtered as $val) {
                            // Si el valor ya es un JSON string, decodificarlo primero para evitar doble encoding
                            if (is_string($val) && strlen($val) > 0 && ($val[0] === '[' || $val[0] === '{')) {
                                $decoded = json_decode($val, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    // Si ya es un array JSON válido, agregar sus valores directamente
                                    $mappedValues = array_merge($mappedValues, $decoded);

                                    continue;
                                }
                            }

                            // Si el valor es un key numérico, buscar en options
                            if (isset($options[$val])) {
                                $mappedValues[] = $options[$val];
                            } elseif (in_array($val, $options, true)) {
                                // Si el valor ya es un texto de opción, usarlo directamente
                                $mappedValues[] = $val;
                            } else {
                                // Si no se encuentra, usar el valor tal cual
                                $mappedValues[] = $val;
                            }
                        }
                        // Solo hacer json_encode una vez, sin doble encoding
                        $answerToSave = json_encode($mappedValues);
                    }
                } else {
                    if ($answerValue === null || $answerValue === '') {
                        continue; // No guardar si está vacío
                    }
                    // Si es -1, guardarlo directamente
                    if ($answerValue === -1 || $answerValue === '-1') {
                        $answerToSave = '-1';
                    } else {
                        // Si el valor ya es un JSON string, usarlo directamente sin volver a codificar
                        if (is_string($answerValue) && strlen($answerValue) > 0 && ($answerValue[0] === '[' || $answerValue[0] === '{')) {
                            $answerToSave = $answerValue;
                        } else {
                            // Si no, crear JSON array con el valor (solo una vez)
                            $answerToSave = json_encode([$answerValue]);
                        }
                    }
                }
            } elseif ($question->type == 'boolean') {
                $answerToSave = ($answerValue == '1' || $answerValue === 1 || $answerValue === true) ? '1' : '0';
            } else {
                // Para text, date, integer, string: extraer el valor del array si viene como array
                if (is_array($answerValue)) {
                    // Filtrar valores nulos o vacíos
                    $filtered = array_filter($answerValue, fn ($v) => $v !== null && $v !== '' && $v !== -1);
                    if (empty($filtered)) {
                        continue; // No guardar si está vacío
                    }
                    // Tomar el primer valor válido como string
                    $answerToSave = (string) reset($filtered);
                } else {
                    $answerToSave = $answerValue;
                }
            }

            // No guardar si es null, vacío, o -1 (excepto para integer donde 0 es válido y multiple donde -1 es válido)
            $shouldSkip = is_null($answerToSave) ||
                (is_string($answerToSave) && trim($answerToSave) === '') ||
                $answerToSave === -1 ||
                $answerToSave === '-1' ||
                (is_string($answerToSave) && trim($answerToSave) === 'null');

            // Para integer, permitir 0 como valor válido
            // Para multiple, permitir -1 como valor válido (se guarda directamente como -1)
            if ($shouldSkip && ! ($question->type === 'integer' && $answerToSave === 0) && ! ($question->type === 'multiple' && $answerToSave === '-1')) {
                continue;
            }

            // Guardar la respuesta
            Answer::create([
                'user_id' => $userId,
                'conviviente_id' => $conviviente->id,
                'question_id' => $questionId,
                'answer' => $answerToSave,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3️⃣ Guardar firma si existe
        if ($request->filled('firma_base64')) {
            $userDocumentController = app(UserDocumentController::class);

            $subRequest = Request::create(
                route('documentos.subir'),
                'POST',
                [
                    'document_id' => $request->input('document_id') ?? 9999,
                    'firma_base64' => $request->input('firma_base64'),
                    'slug' => 'firma_conviviente',
                    'conviviente_index' => $index,
                    'nombre_personalizado' => "Firma conviviente {$index}",
                ]
            );

            $gcs = app(GcsUploaderService::class);

            $userDocumentController->store($subRequest, $gcs);
        }

        // Si es una petición AJAX, devolver JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Conviviente y respuestas guardados correctamente.',
            ]);
        }

        return back()->with('success', 'Conviviente y respuestas guardados correctamente.');
    }

    /**
     * Guarda las respuestas del formulario de solicitud
     * de ayudas solicitadas. Siempre se borran las
     * respuestas anteriores antes de guardar las nuevas.
     *
     * @return Response
     */
    public function storeSolicitud(Request $request)
    {

        $request->validate([
            'questionnaire_id' => 'required|integer',
            'answers' => 'required|array',
        ]);

        $userId = Auth::id();
        $questionnaireId = $request->input('questionnaire_id');
        $answers = $request->input('answers');

        $filteredAnswers = [];

        foreach ($answers as $questionId => $value) {
            // Si es array (multiple), filtrar los valores -1 o vacíos
            if (is_array($value)) {
                $cleaned = array_filter($value, fn ($v) => $v !== null && $v !== '' && $v !== -1);
                if (! empty($cleaned)) {
                    $filteredAnswers[$questionId] = $cleaned;
                }
            } else {
                // Si es escalar
                if ($value !== null && $value !== '' && $value !== -1) {
                    $filteredAnswers[$questionId] = $value;
                }
            }
        }

        // Guardar respuestas
        foreach ($filteredAnswers as $questionId => $answerValue) {

            // Eliminar respuesta anterior
            Answer::where('user_id', $userId)
                ->where('conviviente_id', null)
                ->where('question_id', $questionId)
                ->delete();

            // Obtener la pregunta
            $question = Question::find($questionId);

            if (! $question) {
                continue;
            }

            // Preparar la respuesta
            $answerToSave = null;

            if ($question->type == 'select') {
                // Para select, guardar como JSON si es array, sino como string
                $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                if (is_array($answerValue)) {
                    $answerToSave = array_map(function ($id) use ($options) {
                        return $options[$id] ?? $id;
                    }, $answerValue);
                    $answerToSave = json_encode($answerToSave);
                } else {
                    $answerToSave = $options[$answerValue] ?? $answerValue;
                    // Si el valor es un array después de mapear, convertir a JSON
                    if (is_array($answerToSave)) {
                        $answerToSave = json_encode($answerToSave);
                    }
                }
            } elseif ($question->type == 'multiple') {
                // Para multiple, siempre guardar como JSON
                // -1 es válido porque representa "Ninguna de las anteriores"
                $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                if (is_array($answerValue)) {
                    // Filtrar solo valores nulos o vacíos (permitir -1)
                    $filtered = array_filter($answerValue, fn ($v) => $v !== null && $v !== '');
                    if (empty($filtered)) {
                        continue; // No guardar si está vacío
                    }
                    $answerToSave = array_map(function ($id) use ($options) {
                        return $options[$id] ?? $id;
                    }, $filtered);
                    $answerToSave = json_encode($answerToSave);
                } else {
                    if ($answerValue === null || $answerValue === '') {
                        continue; // No guardar si está vacío (permitir -1)
                    }
                    $answerToSave = json_encode([$answerValue]);
                }
            } elseif ($question->type == 'boolean') {
                $answerToSave = ($answerValue == '1' || $answerValue === 1 || $answerValue === true) ? '1' : '0';
            } else {
                // Para text, date, integer, string: extraer el valor del array si viene como array
                if (is_array($answerValue)) {
                    // Filtrar valores nulos o vacíos
                    $filtered = array_filter($answerValue, fn ($v) => $v !== null && $v !== '' && $v !== -1);
                    if (empty($filtered)) {
                        continue; // No guardar si está vacío
                    }
                    // Tomar el primer valor válido como string
                    $answerToSave = (string) reset($filtered);
                } else {
                    $answerToSave = $answerValue;
                }
            }

            // No guardar si es null, vacío, o -1 (excepto para integer donde 0 es válido y multiple donde -1 es válido)
            $shouldSkip = is_null($answerToSave) ||
                (is_string($answerToSave) && trim($answerToSave) === '') ||
                $answerToSave === -1 ||
                $answerToSave === '-1' ||
                (is_string($answerToSave) && trim($answerToSave) === 'null');

            // Para integer, permitir 0 como valor válido
            // Para multiple, permitir -1 como valor válido (ya está en JSON)
            if ($shouldSkip && ! ($question->type === 'integer' && $answerToSave === 0)) {
                continue;
            }

            // Guardar la respuesta
            Answer::create([
                'user_id' => $userId,
                'conviviente_id' => null, // Solicitud del solicitante
                'question_id' => $questionId,
                'answer' => $answerToSave,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Datos de solicitud guardados correctamente.');
    }

    /**
     * Comprueba si el solicitante ha completado las preguntas
     * del formulario de solicitud definidas en el array
     * preguntasFormulario. Devuelve true si todas las
     * preguntas tienen respuesta, false si falta alguna.
     *
     * @param  int  $userId
     * @param  array  $preguntasFormulario
     * @return bool
     */
    public function comprobarSolicitudCompleta($userId, $preguntasFormulario)
    {
        if (empty($preguntasFormulario)) {

            return true;
        }

        $respuestas = Answer::where('user_id', $userId)
            ->whereNull('conviviente_id') // Es del solicitante
            ->whereIn('question_id', $preguntasFormulario)
            ->pluck('answer', 'question_id');

        $faltan = [];

        foreach ($preguntasFormulario as $questionId) {
            if (! isset($respuestas[$questionId]) || $respuestas[$questionId] === null || $respuestas[$questionId] === '') {
                $faltan[] = $questionId;
            }
        }

        if (! empty($faltan)) {
            return false;
        }

        return true;
    }

    /**
     * Obtiene los grupos vulnerables seleccionados por el usuario
     * en las preguntas 9, 10 y 11 del cuestionario.
     * Devuelve un array con los grupos seleccionados.
     *
     * @param  int  $userId
     * @return array
     */
    private function obtenerGruposVulnerables($userId)
    {
        $respuestas = Answer::where('user_id', $userId)
            ->whereIn('question_id', [9, 10, 11])
            ->whereNull('conviviente_id')
            ->pluck('answer', 'question_id');

        $grupos = [];
        foreach ($respuestas as $respuesta) {
            $valores = json_decode($respuesta, true);
            if (is_array($valores)) {
                $grupos = array_merge($grupos, $valores);
            } elseif (! empty($respuesta)) {
                $grupos[] = $respuesta;
            }
        }

        return array_unique($grupos);
    }

    /**
     * Muestra los detalles de una ayuda solicitada
     * con documentos faltantes y subidos a partir del id de la contratación
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        $user = SimulationHelper::getCurrentUser();

        $contratacion = Contratacion::with([
            'ayuda.organo',
            'ayuda.enlaces',
            'subsanacionDocumentos.document',
            'motivosSubsanacionContrataciones.motivo.document',
            'ayuda.documentos',
            'ayuda.questionnaires' => function ($query) {
                $query->whereIn('tipo', ['conviviente', 'solicitud'])
                    ->with('questionConditions');
            },
        ])
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $rawAnswers = DB::table('answers')
            ->where('user_id', $user->id)
            ->pluck('answer', 'question_id');

        $answers = $rawAnswers->map(function ($answer) {
            $decoded = json_decode($answer, true);

            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $answer;
        });

        // Asegurar que las claves (question_id) se mantengan al convertir a array
        // Usar all() en lugar de toArray() para preservar las claves
        $answersArray = [];
        foreach ($answers as $questionId => $answer) {
            $answersArray[$questionId] = $answer;
        }

        $userDocuments = UserDocument::where('user_id', $user->id)->get();

        $questionnaires = Questionnaire::where('ayuda_id', $contratacion->ayuda->id)
            ->whereIn('tipo', ['conviviente', 'solicitud'])
            ->get()
            ->keyBy('tipo');

        $convivienteQuestionnaireId = $questionnaires->get('conviviente')?->id;
        $solicitudQuestionnaireId = $questionnaires->get('solicitud')?->id;

        $questionnaireIds = $questionnaires->pluck('id')->filter()->toArray();
        $allConditions = [];
        if (! empty($questionnaireIds)) {
            // Usar el método unificado del modelo para cada cuestionario
            foreach ($questionnaireIds as $qId) {
                $allConditions[$qId] = QuestionCondition::getConditions($qId);
            }
        }

        $this->evaluarYActualizarEstadoContratacion(
            $contratacion,
            $user,
            $answersArray,
            $userDocuments,
            $solicitudQuestionnaireId,
            $convivienteQuestionnaireId
        );

        $sector_ayuda = $contratacion->ayuda->sector ?? null;

        $documentosDatos = $this->documentosAyudaService->obtenerDocumentosAyuda(
            $user->id,
            $contratacion,
            $answersArray,
            $sector_ayuda,
            $userDocuments
        );
        $contratacion->user_documents = $documentosDatos['user_documents'];
        $contratacion->documentos_subidos = $documentosDatos['documentos_subidos'];
        $contratacion->documentos_faltantes = $documentosDatos['documentos_faltantes'];
        $contratacion->recibos_subidos = $documentosDatos['recibos_subidos'];
        $contratacion->documentos_configurados = $documentosDatos['documentos_configurados'];

        // Obtener documentos de convivientes que cumplen condiciones
        $contratacion->documentos_convivientes_con_condiciones = $this->documentosAyudaService->obtenerDocumentosConvivientesConCondiciones(
            $contratacion->ayuda->id,
            $user->id,
            $answersArray
        );

        // Obtener condiciones de documentos para evaluación dinámica en frontend
        $contratacion->condiciones_documentos = $this->documentosAyudaService->obtenerCondicionesDocumentos(
            $contratacion->ayuda->id
        );

        $convivienteQuestionnaire = $contratacion->ayuda->questionnaires
            ->firstWhere('tipo', 'conviviente');

        $convivienteQuestionnaireId = $convivienteQuestionnaire?->id;
        // Obtener ID del formulario de convivientes
        $contratacion->formConvivientesId = $convivienteQuestionnaireId;

        // Obtener datos de convivientes
        $datosConvivientes = $this->ConvivientesDatosService->obtenerDatosConvivientes(
            $user->id,
            $contratacion,
            $convivienteQuestionnaire
        );
        $contratacion->convivientes = $datosConvivientes['convivientes'];
        $estadoConvivientes = $datosConvivientes['estadoConvivientes'];

        // Obtener condiciones de convivientes
        $convivienteConditions = [];
        if ($convivienteQuestionnaireId && $convivienteQuestionnaire) {
            $conditions = $convivienteQuestionnaire->questionConditions
                ->map(function ($condition) {
                    // Usar el nuevo formato (operator + value) si existe, sino usar el formato antiguo (condition como JSON)
                    $conditionData = null;

                    if ($condition->operator && $condition->value !== null) {
                        // Nuevo formato: convertir operator y value a formato de condición
                        $value = is_array($condition->value) ? $condition->value : [$condition->value];
                        $conditionData = $value;
                    } elseif ($condition->condition) {
                        // Formato antiguo: condition como JSON
                        $conditionData = json_decode($condition->condition, true);
                    }

                    return [
                        'question_id' => $condition->question_id,
                        'condition' => $conditionData,
                        'operator' => $condition->operator ?? null,
                        'value' => $condition->value ?? null,
                        'next_question_id' => $condition->next_question_id,
                    ];
                })
                ->toArray();
            $convivienteConditions[(string) $convivienteQuestionnaireId] = $conditions;
        }

        // Obtener datos de solicitud
        $gruposVulnerablesSeleccionados = $this->obtenerGruposVulnerables($user->id);
        $solicitudQuestionnaire = $contratacion->ayuda->questionnaires
            ->firstWhere('tipo', 'solicitud');

        $datosSolicitud = $this->solicitudFormularioService->obtenerDatosSolicitud(
            $user->id,
            $contratacion,
            $gruposVulnerablesSeleccionados,
            $solicitudQuestionnaire
        );

        $estadoPrincipal = [];
        if ($datosSolicitud) {
            $contratacion->questions_solicitud = $datosSolicitud['preguntas'];
            $contratacion->solicitud_questionnaire_id = $datosSolicitud['solicitudQuestionnaireId'] ?? null;
            $estadoPrincipal[$contratacion->id] = $datosSolicitud['estado'];
            $contratacion->conditions_solicitud = $datosSolicitud['conditions'] ?? [];
        }

        $nConvivientes = Conviviente::countByUser($user->id);
        $ayudaTieneFormConvivientes = $contratacion->ayuda->questionnaires()->where('tipo', 'conviviente')->exists();

        // Calcular preFormConviviente y preguntasPreForm
        $preFormConviviente = false;
        $preguntasPreForm = [];

        // Obtener respuesta de la pregunta sobre situación de alquiler (question_id = 1)
        $answerQuestionSituacionAlquiler = Answer::where('user_id', $user->id)
            ->where('question_id', 1)
            ->whereNull('conviviente_id')
            ->value('answer');

        if (($answerQuestionSituacionAlquiler === 'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato' ||
            $answerQuestionSituacionAlquiler === 'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato'
            || $answerQuestionSituacionAlquiler === 'No soy parte del contrato, pero vivo de alquiler en una vivienda completa') && $nConvivientes == 0) {

            $preFormConviviente = true;
            $slugs = [
                'personas-vivienda',
            ];

            $preguntasPreForm = Question::whereIn('slug', $slugs)
                ->get()
                ->sortBy(function ($item) use ($slugs) {
                    return array_search($item->slug, $slugs);
                })
                ->values();
        }

        $this->normalizarEstadoFaseParaVista($contratacion);

        return view('user.ayuda-solicitada-detalle', [
            'ayudaSolicitada' => $contratacion,
            'nConvivientes' => $nConvivientes,
            'convivientes' => $datosConvivientes['convivientes'] ?? [],
            'estadoPrincipal' => $estadoPrincipal,
            'estadoConvivientes' => $estadoConvivientes,
            'sector_ayuda' => $sector_ayuda,
            'convivienteConditions' => $convivienteConditions,
            'ayudaTieneFormConvivientes' => $ayudaTieneFormConvivientes,
            'preFormConviviente' => $preFormConviviente,
            'preguntasPreForm' => $preguntasPreForm,
        ]);
    }

    public function storeSolicitudAjax(Request $request)
    {
        try {
            $user = Auth::user();
            $questionnaireId = $request->input('questionnaire_id');

            // 1) Respuestas desde 'answers' (preferido)
            $answers = $request->input('answers', []);

            // --- NOMBRE y APELLIDOS: construir 'nombre_completo' y 'Apellidos_completos' ---
            $slugMap = Question::whereIn('slug', [
                'solo_nombre',          // 177
                'primer_apellido',      // 170
                'segundo_apellido',     // 171
                'nombre_completo',      // 33
                'Apellidos_completos',  // 214 (ojo mayúscula inicial)
            ])->pluck('id', 'slug');

            // IDs (pueden ser null si no existen)
            $idSolo = $slugMap['solo_nombre'] ?? null;
            $idAp1 = $slugMap['primer_apellido'] ?? null;
            $idAp2 = $slugMap['segundo_apellido'] ?? null;
            $idCompleto = $slugMap['nombre_completo'] ?? null;
            $idApellComp = $slugMap['Apellidos_completos'] ?? null;

            // Valores recibidos en esta petición (si existen)
            // Helper para convertir a string, manejando arrays
            $toString = function ($value) {
                if (is_array($value)) {
                    return '';
                }

                return (string) ($value ?? '');
            };

            $solo = $idSolo !== null ? trim($toString($answers[$idSolo] ?? '')) : '';
            $ap1 = $idAp1 !== null ? trim($toString($answers[$idAp1] ?? '')) : '';
            $ap2 = $idAp2 !== null ? trim($toString($answers[$idAp2] ?? '')) : '';

            // a) Generar nombre_completo si no viene
            if ($idCompleto) {
                $completoValue = $answers[$idCompleto] ?? '';
                $yaTraeCompleto = array_key_exists($idCompleto, $answers) && ! is_array($completoValue) && trim((string) $completoValue) !== '';
                if (! $yaTraeCompleto) {
                    $fullName = trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([$solo, $ap1, $ap2], fn ($v) => $v !== ''))));
                    if ($fullName !== '') {
                        $answers[$idCompleto] = $fullName;
                    }
                }
            }

            // b) Generar Apellidos_completos (214) si no viene
            if ($idApellComp) {
                $apellValue = $answers[$idApellComp] ?? '';
                $yaTraeApell = array_key_exists($idApellComp, $answers) && ! is_array($apellValue) && trim((string) $apellValue) !== '';
                if (! $yaTraeApell) {
                    $apellConcat = trim(preg_replace('/\s+/', ' ', trim($ap1.' '.$ap2)));
                    if ($apellConcat !== '') {
                        $answers[$idApellComp] = $apellConcat;
                    }
                }
            }

            // 2) Si no hay 'answers', leer question_XX del request
            if (empty($answers)) {
                $formData = $request->all();
                foreach ($formData as $key => $value) {
                    if (strpos($key, 'question_') === 0) {
                        $questionId = str_replace('question_', '', $key);
                        $answers[$questionId] = $value;
                    }
                }
            }

            // Validaciones base
            $questionnaire = Questionnaire::findOrFail($questionnaireId);

            // Preguntas involucradas en esta operación
            $questions = Question::whereIn('id', array_keys($answers))->get();

            if (! empty($answers)) {
                // Borrado previo de las respuestas del usuario para estas preguntas
                $deleted = Answer::where('user_id', $user->id)
                    ->whereNull('conviviente_id')
                    ->whereIn('question_id', array_keys($answers))
                    ->delete();
                // Guardado
                foreach ($answers as $questionId => $answerValue) {
                    // Saltar nulos/vacíos
                    if (is_null($answerValue) || (is_string($answerValue) && trim($answerValue) === '')) {
                        continue;
                    }

                    $question = $questions->find($questionId);
                    if (! $question) {
                        continue;
                    }

                    // Formateo según tipo
                    $answerToSave = null;
                    if ($question->type == 'select') {
                        // Para select, guardar como JSON si es array, sino como string
                        $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                        if (is_array($answerValue)) {
                            $answerToSave = array_map(fn ($id) => $options[$id] ?? $id, $answerValue);
                            $answerToSave = json_encode($answerToSave);
                        } else {
                            $answerToSave = $options[$answerValue] ?? $answerValue;
                            // Si el valor es un array después de mapear, convertir a JSON
                            if (is_array($answerToSave)) {
                                $answerToSave = json_encode($answerToSave);
                            }
                        }
                    } elseif ($question->type == 'multiple') {
                        // Para multiple, siempre guardar como JSON
                        // -1 es válido porque representa "Ninguna de las anteriores"
                        $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                        if (is_array($answerValue)) {
                            // Filtrar solo valores nulos o vacíos (permitir -1)
                            $filtered = array_filter($answerValue, fn ($v) => $v !== null && $v !== '');
                            if (empty($filtered)) {
                                continue; // No guardar si está vacío
                            }
                            $answerToSave = array_map(fn ($id) => $options[$id] ?? $id, $filtered);
                            $answerToSave = json_encode($answerToSave);
                        } else {
                            if ($answerValue === null || $answerValue === '') {
                                continue; // No guardar si está vacío (permitir -1)
                            }
                            $answerToSave = json_encode([$answerValue]);
                        }
                    } elseif ($question->type == 'boolean') {
                        $answerToSave = ($answerValue == '1' || $answerValue === 1 || $answerValue === true) ? '1' : '0';
                    } else {
                        // Para text, date, integer, string: extraer el valor del array si viene como array
                        if (is_array($answerValue)) {
                            // Filtrar valores nulos o vacíos
                            $filtered = array_filter($answerValue, fn ($v) => $v !== null && $v !== '' && $v !== -1);
                            if (empty($filtered)) {
                                continue; // No guardar si está vacío
                            }
                            // Tomar el primer valor válido como string
                            $answerToSave = (string) reset($filtered);
                        } else {
                            $answerToSave = $answerValue;
                        }
                    }

                    // No guardar si es null, vacío, o -1 (excepto para integer donde 0 es válido)
                    $shouldSkip = is_null($answerToSave) ||
                        (is_string($answerToSave) && trim($answerToSave) === '') ||
                        $answerToSave === -1 ||
                        $answerToSave === '-1' ||
                        (is_string($answerToSave) && trim($answerToSave) === 'null');

                    // Para integer, permitir 0 como valor válido
                    if ($shouldSkip && ! ($question->type === 'integer' && $answerToSave === 0)) {
                        continue;
                    }

                    $created = Answer::create([
                        'user_id' => $user->id,
                        'conviviente_id' => null,
                        'question_id' => $questionId,
                        'answer' => $answerToSave,
                    ]);
                }

                // Verificación de persistencia específica para 214 si aplica
                if ($idApellComp) {
                    $persist214 = Answer::where('user_id', $user->id)
                        ->whereNull('conviviente_id')
                        ->where('question_id', $idApellComp)
                        ->value('answer');
                }
            }

            // === (Resto) Respuesta con ayuda y documentos faltantes (tal cual tenías) ===
            $ayudaSolicitada = Contratacion::with(['ayuda.organo', 'ayuda.enlaces'])
                ->where('user_id', $user->id)
                ->where('ayuda_id', $questionnaire->ayuda_id)
                ->first();

            if ($ayudaSolicitada) {
                $answersDb = DB::table('answers')
                    ->where('user_id', $user->id)
                    ->pluck('answer', 'question_id')
                    ->map(function ($answer) {
                        $decoded = json_decode($answer, true);

                        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $answer;
                    });

                $slugsEspeciales = $this->obtenerSlugsDocumentosEspecialesCondicionales($ayudaSolicitada->ayuda->id, $answersDb);

                $obligatorios = AyudaDocumento::with('documento')
                    ->where('ayuda_id', $ayudaSolicitada->ayuda->id)
                    ->get()
                    ->filter(function ($docRel) use ($slugsEspeciales) {
                        return $docRel->documento &&
                            (
                                $docRel->documento->tipo === 'general' ||
                                ($docRel->documento->tipo === 'especial' && in_array($docRel->documento->slug, $slugsEspeciales))
                            );
                    });

                $documentosSubidos = UserDocument::where('user_id', $user->id)
                    ->pluck('slug')
                    ->toArray();

                $documentosFaltantes = collect();
                $sector_ayuda = $ayudaSolicitada->ayuda->sector ?? null;
                $ignorarDocumentos = false;

                if ($sector_ayuda === 'vivienda') {
                    $respuestaContrato = $answersDb[1] ?? '';
                    if (is_string($respuestaContrato) && trim($respuestaContrato) === 'Todavía no tengo contrato de alquiler firmado.') {
                        $ignorarDocumentos = true;
                    }
                }

                if (
                    ! $ignorarDocumentos &&
                    $ayudaSolicitada->ayuda->fecha_inicio_periodo &&
                    $ayudaSolicitada->ayuda->fecha_fin_periodo &&
                    $sector_ayuda === 'vivienda'
                ) {
                    $documentosRecibos = $this->documentosAyudaService->generarDocumentosRecibos($ayudaSolicitada->ayuda);
                    $documentosRecibos = $documentosRecibos->filter(fn ($doc) => ! in_array($doc->slug, $documentosSubidos));
                    $documentosFaltantes = $documentosFaltantes->merge($documentosRecibos);
                }

                foreach ($obligatorios as $docRel) {
                    if (
                        $docRel->documento &&
                        ! in_array($docRel->documento->slug, $documentosSubidos)
                    ) {
                        if ($ignorarDocumentos && in_array($docRel->documento->slug, ['contrato-alquiler', 'padron-colectivo', 'padron-historico'])) {
                            continue;
                        }
                        $documentosFaltantes->push($docRel->documento);
                    }
                }

                $ayudaSolicitada->documentos_faltantes = $documentosFaltantes->values()->map(function ($doc) {
                    return is_object($doc) && method_exists($doc, 'toArray') ? $doc->toArray() : (array) $doc;
                })->all();

                $recibosSubidos = UserDocument::where('user_id', $user->id)
                    ->where('slug', 'like', 'recibo_%')
                    ->get()
                    ->keyBy('slug')
                    ->map(fn ($doc) => is_object($doc) && method_exists($doc, 'toArray') ? $doc->toArray() : (array) $doc);

                $ayudaSolicitada->recibos_subidos = $recibosSubidos;
            }

            return response()->json([
                'success' => true,

                'ayudaSolicitada' => $ayudaSolicitada,
            ]);
        } catch (\Exception $e) {
            Log::error('[storeSolicitudAjax] EXCEPCIÓN', [
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar los datos: '.$e->getMessage(),
            ], 500);
        }
    }

    public function solicitarRevision(Request $request)
    {
        $ayudaId = $request->input('ayuda_id');
        $userId = SimulationHelper::getCurrentUserId();
        $comentario = $request->input('comentario');

        if (! $ayudaId) {
            return redirect()->route('user.home')->with('error', 'Parámetro ayuda_id es requerido.');
        }

        $updated = AyudaSolicitada::where('user_id', $userId)
            ->where('ayuda_id', $ayudaId)
            ->update(['solicitada_revision' => 1, 'info_revision' => $comentario]);

        if ($updated) {
            return redirect()->route('user.home')->with('success', 'Revisión solicitada correctamente.');
        } else {
            return redirect()->route('user.home')->with('error', 'No se puedo solicitar la revisión.');
        }
    }

    /**
     * Evalúa y actualiza el estado de una contratación según
     * documentos y formularios completados y fechas de la ayuda.
     * Devuelve true si se actualizó el estado, false si no.
     */
    // !!Tenemos que revisar si las estados de las contrataciones siguen siendo los mismos

    public function evaluarYActualizarEstadoContratacion(Contratacion $contratacion, ?User $user = null, ?array $answersArray = null, $userDocuments = null, ?int $questionnaireSolicitudId = null, ?int $questionnaireConvivienteId = null): bool
    {
        $ayuda = $contratacion->ayuda;
        $hoy = Carbon::today();

        if ($user === null) {
            $user = User::find($contratacion->user_id);
        }

        if ($answersArray === null) {
            $answersArray = $user->obtenerRespuestas();
        }

        if ($userDocuments === null) {
            $userDocuments = UserDocument::where('user_id', $user->id)->get();
        }

        if ($questionnaireSolicitudId === null || $questionnaireConvivienteId === null) {
            $questionnaires = Questionnaire::where('ayuda_id', $ayuda->id)
                ->whereIn('tipo', ['conviviente', 'solicitud'])
                ->get()
                ->keyBy('tipo');

            if ($questionnaireSolicitudId === null) {
                $questionnaireSolicitudId = $questionnaires->get('solicitud')?->id;
            }
            if ($questionnaireConvivienteId === null) {
                $questionnaireConvivienteId = $questionnaires->get('conviviente')?->id;
            }
        }

        $documentosSubidos = $userDocuments->whereNull('conviviente_index')->pluck('slug')->toArray();

        $documentosFaltantes = collect($this->documentosAyudaService->obtenerDocumentosFaltantes(
            $contratacion,
            $answersArray,
            $documentosSubidos,
            $ayuda->sector
        ));
        $hayDocumentosFaltantes = $documentosFaltantes->isNotEmpty();

        $hayDocumentosPendientes = $userDocuments
            ->whereIn('estado', ['pendiente', 'rechazado'])
            ->isNotEmpty();

        $cuestionarioService = app(CuestionarioCompletoService::class);

        $solicitudCompleta = true;
        if ($questionnaireSolicitudId !== null) {
            $solicitudCompleta = $cuestionarioService
                ->usuarioPrincipalTieneCuestionarioCompleto($user->id, $questionnaireSolicitudId)['completo'] ?? false;
        }

        $convivientesCompletos = true;
        $hayDocumentosConvivientesFaltantes = false;

        // Solo verificar convivientes si hay formulario de convivientes (significa que son necesarios para esta ayuda)
        if ($questionnaireConvivienteId !== null) {
            $convivientesCompletos = $cuestionarioService
                ->convivientesTienenCuestionarioCompleto($user->id, $questionnaireConvivienteId)['completo'] ?? false;

            // Verificar documentos de convivientes obligatorios
            $hayDocumentosConvivientesFaltantes = $this->verificarDocumentosConvivientesCompletos(
                $contratacion,
                $user,
                $answersArray,
                $userDocuments
            );
        }

        // Si hay algo pendiente, faltante o incompleto → NO cambiamos el estado
        if ($hayDocumentosFaltantes || $hayDocumentosPendientes || $hayDocumentosConvivientesFaltantes || ! $solicitudCompleta || ! $convivientesCompletos) {
            return false;
        }

        // Si está en documentación (OPx), añadir OP1-Tramitacion
        $tieneDocumentacion = $contratacion->estadosContratacion()->where('codigo', 'OP1-Documentacion')->exists();
        if ($tieneDocumentacion) {
            app(EstadoContratacionService::class)->syncEstadosByCodigos(
                $contratacion,
                ['OP1-Tramitacion'],
                false
            );
        }

        return true;
    }

    /**
     * Verifica si todos los documentos de convivientes obligatorios están subidos y validados
     *
     * @param  Collection  $userDocuments
     * @return bool true si faltan documentos, false si están todos completos
     */
    private function verificarDocumentosConvivientesCompletos(Contratacion $contratacion, User $user, array $answersArray, $userDocuments): bool
    {
        try {
            $ayuda = $contratacion->ayuda;

            // Obtener documentos de convivientes que deben mostrarse (obligatorios + opcionales con condiciones)
            $documentosConvivientesNecesarios = $this->documentosAyudaService->obtenerDocumentosConvivientesConCondiciones(
                $ayuda->id,
                $user->id,
                $answersArray
            );

            if ($documentosConvivientesNecesarios->isEmpty()) {
                // No hay documentos de convivientes requeridos
                return false;
            }

            // Obtener todos los convivientes del usuario
            $convivientes = Conviviente::where('user_id', $user->id)
                ->orderBy('index')
                ->get();

            if ($convivientes->isEmpty()) {
                // Si no hay convivientes pero hay documentos requeridos, verificar si realmente se requieren
                // (puede ser que las condiciones no se cumplan para ningún conviviente)
                // Por ahora, si hay documentos obligatorios sin condiciones, se consideran faltantes
                $documentosObligatoriosSinCondiciones = $documentosConvivientesNecesarios
                    ->filter(function ($doc) {
                        return $doc->es_obligatorio && empty($doc->conditions);
                    });

                return $documentosObligatoriosSinCondiciones->isNotEmpty();
            }

            // Para cada documento necesario, verificar que esté subido y validado para los convivientes requeridos
            foreach ($documentosConvivientesNecesarios as $docConviviente) {
                if (! $docConviviente->documento) {
                    continue;
                }

                $docSlug = $docConviviente->documento->slug;
                $docId = $docConviviente->documento->id;

                // Determinar para qué convivientes se requiere este documento
                $convivientesIdsRequeridos = [];

                if ($docConviviente->es_obligatorio && empty($docConviviente->conditions)) {
                    // Documento obligatorio sin condiciones: requerido para todos los convivientes
                    $convivientesIdsRequeridos = $convivientes->pluck('id')->toArray();
                } elseif (! empty($docConviviente->conditions) && isset($docConviviente->convivientes_ids)) {
                    // Documento con condiciones: usar los IDs precalculados por el servicio
                    $convivientesIdsRequeridos = $docConviviente->convivientes_ids;
                } else {
                    // Si no tiene convivientes_ids precalculado, requerir para todos (caso conservador)
                    $convivientesIdsRequeridos = $convivientes->pluck('id')->toArray();
                }

                // Verificar que cada conviviente requerido tenga el documento subido y validado
                foreach ($convivientesIdsRequeridos as $convivienteId) {
                    $conviviente = $convivientes->firstWhere('id', $convivienteId);
                    if (! $conviviente) {
                        continue;
                    }

                    // Buscar el documento subido para este conviviente
                    $documentoSubido = $userDocuments
                        ->where('document_id', $docId)
                        ->where('conviviente_index', $conviviente->index)
                        ->where('estado', 'validado')
                        ->first();

                    if (! $documentoSubido) {
                        // Falta el documento para este conviviente
                        Log::debug('[evaluarYActualizarEstadoContratacion] Falta documento de conviviente', [
                            'documento_slug' => $docSlug,
                            'documento_id' => $docId,
                            'conviviente_id' => $convivienteId,
                            'conviviente_index' => $conviviente->index,
                        ]);

                        return true; // Hay documentos faltantes
                    }
                }
            }

            return false; // Todos los documentos de convivientes están completos
        } catch (\Exception $e) {
            Log::error('[evaluarYActualizarEstadoContratacion] Error al verificar documentos de convivientes', [
                'contratacion_id' => $contratacion->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // En caso de error, ser conservador y no cambiar el estado
            return true;
        }
    }

    public function subsanacionView($id)
    {
        $ayudaSolicitada = Contratacion::with('subsanacionDocumentos.document')->findOrFail($id);

        // Devolver solo la vista de los elementos, SIN el div padre
        return view('components.ayuda-card.partials.subsanacion-content', compact('ayudaSolicitada'));
    }

    /**
     * Devuelve el HTML del componente documentos para actualización AJAX
     */
    public function documentosView($id)
    {
        $user = SimulationHelper::getCurrentUser();

        $contratacion = Contratacion::with([
            'ayuda.organo',
            'ayuda.enlaces',
            'subsanacionDocumentos.document',
            'motivosSubsanacionContrataciones.motivo.document',
            'ayuda.documentos',
        ])
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $rawAnswers = DB::table('answers')
            ->where('user_id', $user->id)
            ->pluck('answer', 'question_id');

        $answers = $rawAnswers->map(function ($answer) {
            $decoded = json_decode($answer, true);

            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $answer;
        });

        $answersArray = [];
        foreach ($answers as $questionId => $answer) {
            $answersArray[$questionId] = $answer;
        }

        $userDocuments = UserDocument::where('user_id', $user->id)->get();

        $questionnaires = Questionnaire::where('ayuda_id', $contratacion->ayuda->id)
            ->whereIn('tipo', ['conviviente', 'solicitud'])
            ->get()
            ->keyBy('tipo');

        $solicitudQId = $questionnaires->get('solicitud')?->id;
        $convivienteQId = $questionnaires->get('conviviente')?->id;

        $this->evaluarYActualizarEstadoContratacion($contratacion, $user, $answersArray, $userDocuments, $solicitudQId, $convivienteQId);

        // Obtener ayuda solicitada
        $ayudaSolicitada = AyudaSolicitada::where('user_id', $user->id)
            ->where('ayuda_id', $contratacion->ayuda->id)
            ->first();

        // Si no existe AyudaSolicitada, usar la Contratacion directamente
        // Ambos tienen ayuda_id y user_id, así que el servicio puede trabajar con cualquiera
        $objetoParaDocumentos = $ayudaSolicitada ?: $contratacion;

        // Obtener datos de documentos
        $sector_ayuda = $contratacion->ayuda->sector ?? null;
        $documentosDatos = $this->documentosAyudaService->obtenerDocumentosAyuda($user->id, $objetoParaDocumentos, $answersArray, $sector_ayuda);

        // Asignar los datos al objeto que se pasará a la vista
        $objetoParaDocumentos->user_documents = $documentosDatos['user_documents'];
        $objetoParaDocumentos->documentos_subidos = $documentosDatos['documentos_subidos'];
        $objetoParaDocumentos->documentos_faltantes = $documentosDatos['documentos_faltantes'];
        $objetoParaDocumentos->recibos_subidos = $documentosDatos['recibos_subidos'];
        $objetoParaDocumentos->documentos_configurados = $documentosDatos['documentos_configurados'];

        // Asegurar que el objeto tenga el ID correcto para el componente
        // El componente necesita un ID para generar el contenedor único
        if (! isset($objetoParaDocumentos->id)) {
            $objetoParaDocumentos->id = $contratacion->id;
        }

        // Devolver solo el componente documentos
        return view('components.ayuda-card.documentos', ['ayudaSolicitada' => $objetoParaDocumentos]);
    }

    /**
     * Devuelve el HTML del componente documentos-estadisticas para actualización AJAX
     */
    public function documentosEstadisticasView($id)
    {
        $user = SimulationHelper::getCurrentUser();

        $contratacion = Contratacion::with([
            'ayuda.organo',
            'ayuda.enlaces',
            'subsanacionDocumentos.document',
            'motivosSubsanacionContrataciones.motivo.document',
            'ayuda.documentos',
        ])
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $rawAnswers = DB::table('answers')
            ->where('user_id', $user->id)
            ->pluck('answer', 'question_id');

        $answers = $rawAnswers->map(function ($answer) {
            $decoded = json_decode($answer, true);

            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $answer;
        });

        $answersArray = [];
        foreach ($answers as $questionId => $answer) {
            $answersArray[$questionId] = $answer;
        }

        $userDocuments = UserDocument::where('user_id', $user->id)->get();

        $questionnaires = Questionnaire::where('ayuda_id', $contratacion->ayuda->id)
            ->whereIn('tipo', ['conviviente', 'solicitud'])
            ->get()
            ->keyBy('tipo');

        $solicitudQId = $questionnaires->get('solicitud')?->id;
        $convivienteQId = $questionnaires->get('conviviente')?->id;

        $this->evaluarYActualizarEstadoContratacion($contratacion, $user, $answersArray, $userDocuments, $solicitudQId, $convivienteQId);

        // Obtener ayuda solicitada
        $ayudaSolicitada = AyudaSolicitada::where('user_id', $user->id)
            ->where('ayuda_id', $contratacion->ayuda->id)
            ->first();

        // Si no existe AyudaSolicitada, usar la Contratacion directamente
        $objetoParaEstadisticas = $ayudaSolicitada ?: $contratacion;

        // Obtener datos de documentos
        $sector_ayuda = $contratacion->ayuda->sector ?? null;
        $documentosDatos = $this->documentosAyudaService->obtenerDocumentosAyuda($user->id, $objetoParaEstadisticas, $answersArray, $sector_ayuda);

        // Asignar los datos al objeto que se pasará a la vista
        $objetoParaEstadisticas->user_documents = $documentosDatos['user_documents'];
        $objetoParaEstadisticas->documentos_subidos = $documentosDatos['documentos_subidos'];
        $objetoParaEstadisticas->documentos_faltantes = $documentosDatos['documentos_faltantes'];
        $objetoParaEstadisticas->recibos_subidos = $documentosDatos['recibos_subidos'];
        $objetoParaEstadisticas->documentos_configurados = $documentosDatos['documentos_configurados'];

        // Asegurar que el objeto tenga el ID correcto para el componente
        if (! isset($objetoParaEstadisticas->id)) {
            $objetoParaEstadisticas->id = $contratacion->id;
        }

        // Devolver solo el componente documentos-estadisticas
        return view('components.documentos-estadisticas', ['ayudaSolicitada' => $objetoParaEstadisticas]);
    }

    public function destroySolicitud($userId, $solicitudId)
    {
        try {
            $ayudaSolicitada = AyudaSolicitada::where('id', $solicitudId)
                ->where('user_id', $userId)
                ->first();

            if (! $ayudaSolicitada) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solicitud no encontrada',
                ], 404);
            }

            if ($ayudaSolicitada->estado !== 'Rechazado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden eliminar solicitudes rechazadas',
                ], 403);
            }

            $ayudaSolicitada->delete();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud eliminada correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la solicitud: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Guarda las respuestas del formulario de preConvivientes y crea convivientes
     * según estas respuesta
     *
     * @response Response
     */
    public function storeQuestionPreConviviente(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $answers = $request->input('answers');

            $filteredAnswers = [];

            foreach ($answers as $questionId => $value) {
                $filteredAnswers[$questionId] = $value;
            }

            foreach ($filteredAnswers as $questionId => $answerValue) {

                // Eliminar respuesta anterior
                Answer::where('user_id', $user->id)
                    ->where('conviviente_id', null)
                    ->where('question_id', $questionId)
                    ->delete();

                // Obtener la pregunta
                $question = Question::find($questionId);
                if ($answerValue != null) {
                    // Procesar la respuesta según el tipo de pregunta
                    $answerToSave = null;

                    if ($question) {

                        // Para text, date, integer, string: extraer el valor del array si viene como array
                        if (is_array($answerValue)) {
                            // Filtrar valores nulos o vacíos
                            $filtered = array_filter($answerValue, fn ($v) => $v !== null && $v !== '' && $v !== -1);
                            if (empty($filtered)) {
                                continue; // No guardar si está vacío
                            }
                            // Tomar el primer valor válido como string
                            $answerToSave = (string) reset($filtered);
                        } else {
                            $answerToSave = $answerValue;
                        }

                    } else {
                        // Si no hay pregunta, extraer el valor del array si viene como array
                        if (is_array($answerValue)) {
                            // Filtrar valores nulos o vacíos
                            $filtered = array_filter($answerValue, fn ($v) => $v !== null && $v !== '' && $v !== -1);
                            if (empty($filtered)) {
                                continue; // No guardar si está vacío
                            }
                            // Tomar el primer valor válido como string
                            $answerToSave = (string) reset($filtered);
                        } else {
                            $answerToSave = $answerValue;
                        }
                    }

                    // No guardar si es null, vacío, o -1
                    if (
                        is_null($answerToSave) ||
                        (is_string($answerToSave) && trim($answerToSave) === '') ||
                        $answerToSave === -1 ||
                        $answerToSave === '-1' ||
                        (is_string($answerToSave) && trim($answerToSave) === 'null')
                    ) {
                        continue;
                    }

                    // Guardar la respuesta
                    Answer::create([
                        'user_id' => $user->id,
                        'conviviente_id' => null, // Solicitud del solicitante
                        'question_id' => $questionId,
                        'answer' => $answerToSave,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    continue;
                }
                // Recogemos cuantos convivientes son en la vivienda contando al solicitante
                if ($question->slug == 'personas-vivienda') {
                    $num_convivientes = (int) $answerValue - 1;
                    for ($i = 0; $i < $num_convivientes; $i++) {
                        $user->convivientes()->create([
                            'index' => $user->convivientes()->count() + 1,
                            'token' => Str::random(36),
                            'tipo' => 'conviviente',
                        ]);
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Error in storeQuestionPreConviviente: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las respuestas: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => '✅ Respuestas guardadas correctamente.',
            'ayudaSolicitada' => $request->ayuda_solicitada_id,
        ]);
    }

    /**
     * Recarga el componente de datos de convivientes tras realizar
     * el fromulario preConvivientes
     */
    public function refresh($ayudaId)
    {
        $user = Auth::user();
        $ayudaSolicitada = Contratacion::with('ayuda')->find($ayudaId);

        if (! $ayudaSolicitada) {
            return response()->json(['error' => 'Contratación no encontrada'], 404);
        }

        if (! $ayudaSolicitada->ayuda) {
            return response()->json(['error' => 'Ayuda no encontrada'], 404);
        }

        $convivientes = Conviviente::byUser($user->id)->orderBy('index')->get();

        // obtenemos el id del fomulario convivientes de la ayuda
        $ayudaSolicitada->formConvivientesId = Ayuda::getConvivienteQuestionnaireId($ayudaSolicitada->ayuda_id);

        $convivienteQuestionnaireId = Questionnaire::where('ayuda_id', $ayudaSolicitada->ayuda->id)
            ->where('tipo', 'conviviente')
            ->value('id');

        // Comprobamos si los convivientes tienen el formulario completo
        if ($convivienteQuestionnaireId) {
            // Usar el método unificado del modelo (formato nuevo: operator + value)
            $conditions = QuestionCondition::getConditions($convivienteQuestionnaireId);

            // Usar el questionnaire_id como string para asegurar que se convierta a objeto en JSON
            $convivienteConditionsPorQuestionnaire[(string) $convivienteQuestionnaireId] = $conditions;
            // todo podemos hacerlo en una cosulta para no tener que modificar el codigo en un futuro
            $preguntasObligatorias = [33, 34, 40, 42, 117, 118, 147, 145, 85, 127, 142, 143, 144, 152, 153, 154, 157, 156, 158];

            $preguntasFormulario = DB::table('questionnaire_questions')
                ->where('questionnaire_id', $convivienteQuestionnaireId)
                ->pluck('question_id')
                ->toArray();

            $preguntasFinales = array_intersect($preguntasObligatorias, $preguntasFormulario);

            foreach ($convivientes as $conviviente) {
                $conviviente->completo = $this->cuestionarioCompletoService->comprobarConvivienteCompleto(
                    $user->id,
                    $conviviente->id,
                    $preguntasFinales
                );
            }
        }

        $nConvivientes = $convivientes->count();
        $sectorAyuda = $ayudaSolicitada->ayuda->sector ?? null;

        $ayudaTieneFormConvivientes = $ayudaSolicitada->ayuda->questionnaires()->where('tipo', 'conviviente')->exists();
        Log::info('Ayuda tiene form convivientes: '.$ayudaTieneFormConvivientes);

        $preFormConviviente = $nConvivientes === 0 && $ayudaTieneFormConvivientes;
        $preguntasPreForm = Question::whereIn('slug', [
            'personas-vivienda',
        ])->get();

        if ($nConvivientes > 0 && $ayudaTieneFormConvivientes) {
            return view('components.ayuda-card.ayuda-convivientes-list', compact(
                'nConvivientes',
                'sectorAyuda',
                'convivientes',
                'ayudaSolicitada'
            ));
        } elseif ($preFormConviviente) {
            return view('components.ayuda-card.ayuda-convivientes-preform', compact(
                'preguntasPreForm',
                'ayudaSolicitada'
            ));
        } else {
            return view('components.ayuda-card.ayuda-convivientes-empty');
        }
    }

    /**
     * Obtiene las preguntas necesarias para crear un nuevo conviviente
     *
     * @param  int  $ayudaId  ID de la ayuda
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConvivienteCrearForm($ayudaId)
    {
        try {
            $user = Auth::user();

            // Primero intentar buscar por Contratacion (puede ser que $ayudaId sea realmente una contratacion_id)
            $contratacion = Contratacion::where('user_id', $user->id)
                ->where('id', $ayudaId)
                ->first();

            if ($contratacion) {
                $realAyudaId = $contratacion->ayuda_id;
            } else {
                // Si no es una contratación, buscar por AyudaSolicitada
                $ayudaSolicitada = AyudaSolicitada::where('user_id', $user->id)
                    ->where('ayuda_id', $ayudaId)
                    ->first();

                if (! $ayudaSolicitada) {
                    // Último intento: buscar por Contratacion usando ayuda_id
                    $contratacion = Contratacion::where('user_id', $user->id)
                        ->where('ayuda_id', $ayudaId)
                        ->first();

                    if (! $contratacion) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No tienes acceso a esta ayuda',
                        ], 403);
                    }
                    $realAyudaId = $contratacion->ayuda_id;
                } else {
                    $realAyudaId = $ayudaId;
                }
            }

            // Obtener las preguntas y condiciones usando el método del modelo
            $data = Questionnaire::getPreguntasCreacionConviviente($realAyudaId);
            $questions = $data['questions'];
            $conditions = $data['conditions'];

            if ($questions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay preguntas disponibles para crear el conviviente',
                ], 404);
            }

            // Mapear las preguntas al formato esperado
            $mappedQuestions = $questions->map(function ($question) {
                $options = [];
                if (in_array($question->type, ['select', 'multiple', 'radio', 'checkbox'])) {
                    $options = is_string($question->options)
                        ? json_decode($question->options, true) ?? []
                        : ($question->options ?? []);
                }

                return [
                    'id' => $question->id,
                    'slug' => $question->slug,
                    'text' => $question->text,
                    'text_conviviente' => $question->text_conviviente,
                    'subtext' => $question->sub_text,
                    'type' => $question->type,
                    'options' => $options,
                    'disable_answer' => $question->disable_answer ?? false,
                ];
            });

            return response()->json([
                'success' => true,
                'questions' => $mappedQuestions,
                'conditions' => $conditions,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener preguntas para crear conviviente: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el formulario',
            ], 500);
        }
    }

    /**
     * Crea un nuevo conviviente con sus respuestas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeConvivienteCrear(Request $request)
    {
        try {
            // Log de lo que llega al servidor
            Log::info('storeConvivienteCrear - Datos recibidos:', [
                'all' => $request->all(),
                'ayuda_id' => $request->input('ayuda_id'),
                'ayuda_id_type' => gettype($request->input('ayuda_id')),
                'answers' => $request->input('answers'),
                'answers_type' => gettype($request->input('answers')),
                'answers_count' => is_array($request->input('answers')) ? count($request->input('answers')) : 'not_array',
                'user_id' => Auth::id(),
            ]);

            $request->validate([
                // Ya comprobamos más abajo que el usuario tenga acceso a esa ayuda,
                // así que no necesitamos validar que exista en la tabla ayudas aquí.
                'ayuda_id' => 'required|integer',
                'answers' => 'required|array|min:1',
            ]);

            $user = Auth::user();
            $ayudaId = $request->input('ayuda_id');
            $answers = $request->input('answers');

            Log::info('storeConvivienteCrear - Después de validación:', [
                'ayuda_id' => $ayudaId,
                'answers_count' => count($answers),
                'answers_keys' => array_keys($answers),
            ]);

            // Verificar que el usuario tiene acceso a esta ayuda
            // Primero intentar buscar por Contratacion (puede ser que $ayudaId sea realmente una contratacion_id)
            $contratacion = Contratacion::where('user_id', $user->id)
                ->where('id', $ayudaId)
                ->first();

            $realAyudaId = null;

            if ($contratacion) {
                $realAyudaId = $contratacion->ayuda_id;
            } else {
                // Si no es una contratación, buscar por AyudaSolicitada
                $ayudaSolicitada = AyudaSolicitada::where('user_id', $user->id)
                    ->where('ayuda_id', $ayudaId)
                    ->first();

                if ($ayudaSolicitada) {
                    $realAyudaId = $ayudaId;
                } else {
                    // Último intento: buscar por Contratacion usando ayuda_id
                    $contratacion = Contratacion::where('user_id', $user->id)
                        ->where('ayuda_id', $ayudaId)
                        ->first();

                    if ($contratacion) {
                        $realAyudaId = $contratacion->ayuda_id;
                    }
                }
            }

            if (! $realAyudaId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta ayuda',
                ], 403);
            }

            DB::beginTransaction();

            // Calcular el siguiente índice para el conviviente
            $nextIndex = Conviviente::where('user_id', $user->id)->max('index') + 1;

            // Crear el conviviente
            $conviviente = Conviviente::create([
                'user_id' => $user->id,
                'index' => $nextIndex,
                'token' => Str::uuid(),
            ]);

            // Guardar las respuestas
            foreach ($answers as $questionId => $answerValue) {
                if ($answerValue === null || $answerValue === '') {
                    continue;
                }

                $question = Question::find($questionId);
                if (! $question) {
                    continue;
                }

                // Preparar la respuesta según el tipo de pregunta
                $answerToSave = null;

                if ($question->type == 'select') {
                    $options = is_string($question->options)
                        ? json_decode($question->options, true) ?? []
                        : ($question->options ?? []);

                    if (is_array($answerValue)) {
                        $answerToSave = json_encode(array_map(function ($id) use ($options) {
                            return $options[$id] ?? $id;
                        }, $answerValue));
                    } else {
                        $answerToSave = $options[$answerValue] ?? $answerValue;
                        if (is_array($answerToSave)) {
                            $answerToSave = json_encode($answerToSave);
                        }
                    }
                } elseif ($question->type == 'multiple') {
                    $options = is_string($question->options)
                        ? json_decode($question->options, true) ?? []
                        : ($question->options ?? []);

                    if (is_array($answerValue)) {
                        $filtered = array_filter($answerValue, fn ($v) => $v !== null && $v !== '');
                        if (empty($filtered)) {
                            continue;
                        }

                        if (in_array(-1, $filtered) || in_array('-1', $filtered)) {
                            $answerToSave = '-1';
                        } else {
                            $mappedValues = array_map(function ($val) use ($options) {
                                return $options[$val] ?? $val;
                            }, $filtered);
                            $answerToSave = json_encode($mappedValues);
                        }
                    } else {
                        $answerToSave = json_encode([$answerValue]);
                    }
                } elseif ($question->type == 'boolean') {
                    $answerToSave = ($answerValue == '1' || $answerValue === 1 || $answerValue === true) ? '1' : '0';
                } else {
                    // Para text, date, integer, string
                    if (is_array($answerValue)) {
                        $answerToSave = $answerValue[0] ?? $answerValue;
                    } else {
                        $answerToSave = $answerValue;
                    }
                }

                // Guardar la respuesta
                Answer::create([
                    'user_id' => $user->id,
                    'question_id' => $questionId,
                    'conviviente_id' => $conviviente->id,
                    'answer' => $answerToSave,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Conviviente creado correctamente',
                'conviviente_id' => $conviviente->id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('storeConvivienteCrear - Error de validación:', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'ayuda_id' => $request->input('ayuda_id'),
                'answers' => $request->input('answers'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error de validación!!!!!!!!!',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear conviviente: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el conviviente: '.$e->getMessage(),
            ], 500);
        }
    }
}
