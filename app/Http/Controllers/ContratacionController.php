<?php

namespace App\Http\Controllers;

use App\Events\EventContratacionCierreRechazada;
use App\Events\EventContratacionCierreResolucion;
use App\Events\EventUserContracted;
use App\Helpers\AnswerNormalizer;
use App\Helpers\SimulationHelper;
use App\Models\Answer;
use App\Models\Ayuda;
use App\Models\Ccaa;
use App\Models\Contratacion;
use App\Models\Document;
use App\Models\DocumentoConfiguracion;
use App\Models\EstadoContratacion;
use App\Models\HistorialActividad;
use App\Models\MotivoSubsanacionAyuda;
use App\Models\MotivoSubsanacionContratacion;
use App\Models\Provincia;
use App\Models\Question;
use App\Models\Transicion;
use App\Models\User;
/* use App\Models\UserAyuda; */
use App\Models\UserDocument;
use App\Services\ContratacionEstadoService;
use App\Services\DocumentosAyudaService;
use App\Services\GcsUploaderService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContratacionController extends Controller
{
    protected ContratacionEstadoService $contratacionEstadoService;

    public function __construct(ContratacionEstadoService $contratacionEstadoService)
    {
        $this->contratacionEstadoService = $contratacionEstadoService;
    }

    /**
     * Esta funcion devuelve las contrataciones de un usuario para el panel de admin.
     *
     * @return JsonResponse
     */
    public function listByUser(User $user)
    {
        if (! Auth::user() || ! Auth::user()->is_admin) {
            abort(403);
        }
        $contrataciones = Contratacion::with(['ayuda:id,nombre_ayuda', 'estadosContratacion'])
            ->select('id', 'user_id', 'ayuda_id')
            ->where('user_id', $user->id)
            ->orderByDesc('fecha_contratacion')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'estados_opx' => $c->estadosContratacion->pluck('codigo')->values()->all(),
                    'ayuda_nombre' => $c->ayuda->nombre_ayuda ?? '',
                ];
            });

        return response()->json(['contrataciones' => $contrataciones]);
    }

    public function index(Request $request)
    {
        // Filtros (igual que antes)...
        $sector = $request->input('sector');
        $ccaa_id = $request->input('ccaa_id');
        $search = $request->input('search');
        $ccaas = Ccaa::orderBy('nombre_ccaa')->pluck('nombre_ccaa', 'id');
        $provincias = Provincia::orderBy('nombre_provincia')->pluck('nombre_provincia', 'id')->toArray();

        $order = $request->input('order', 'asc');
        if (! in_array($order, ['asc', 'desc'])) {
            $order = 'asc';
        }

        $ayudasPorCcaa = Ayuda::when($ccaa_id, fn ($q) => $q->where('ccaa_id', $ccaa_id))
            ->orderBy('nombre_ayuda')
            ->pluck('nombre_ayuda', 'id');

        // 🧹 1) Selecciona columnas mínimas y elimina cargas pesadas
        $query = Contratacion::query()
            ->select(
                'contrataciones.id',
                'contrataciones.user_id',
                'contrataciones.fecha_contratacion',
                'contrataciones.ayuda_id'
            )
            ->addSelect([
                // última respuesta para cada slug (si hubiera varias)
                'ans_nombre_completo' => Answer::select('answer')
                    ->join('questions', 'questions.id', '=', 'answers.question_id')
                    ->whereColumn('answers.user_id', 'contrataciones.user_id')
                    ->where('questions.slug', 'nombre_completo')
                    ->latest('answers.id')->limit(1),

                'ans_solo_nombre' => Answer::select('answer')
                    ->join('questions', 'questions.id', '=', 'answers.question_id')
                    ->whereColumn('answers.user_id', 'contrataciones.user_id')
                    ->where('questions.slug', 'solo_nombre')
                    ->latest('answers.id')->limit(1),

                'ans_primer_apellido' => Answer::select('answer')
                    ->join('questions', 'questions.id', '=', 'answers.question_id')
                    ->whereColumn('answers.user_id', 'contrataciones.user_id')
                    ->where('questions.slug', 'primer_apellido')
                    ->latest('answers.id')->limit(1),

                'ans_segundo_apellido' => Answer::select('answer')
                    ->join('questions', 'questions.id', '=', 'answers.question_id')
                    ->whereColumn('answers.user_id', 'contrataciones.user_id')
                    ->where('questions.slug', 'segundo_apellido')
                    ->latest('answers.id')->limit(1),
            ])
            ->with([
                // Solo lo necesario para la lista
                'user:id,name,email',
                'user.userDocuments:id,user_id,estado',    // para contar (sin blobs ni paths)
                'ayuda:id,nombre_ayuda,ccaa_id,sector',    // datos básicos
                'ayuda.documentos' => fn ($q) => $q->select('documents.id', 'documents.tipo', 'documents.slug'),
                'estadosContratacion:id,codigo,grupo',
                // Historial corto (evita traer todo)
                'historial' => fn ($q) => $q->orderBy('fecha_inicio', 'desc')
                    ->limit(5)
                    ->select('id', 'contratacion_id', 'actividad', 'observaciones', 'fecha_inicio'),
            ])
            ->orderBy('fecha_contratacion', $order);

        // 3) Filtros básicos
        if ($sector) {
            $query->whereHas('ayuda', fn ($q) => $q->where('sector', $sector));
        }
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereExists(function ($sub) use ($search) {
                        $sub->select(DB::raw(1))
                            ->from('answers')
                            ->join('questions', 'answers.question_id', '=', 'questions.id')
                            ->whereRaw('answers.user_id = users.id')
                            ->where('questions.slug', 'telefono')
                            ->where('answers.answer', 'like', "%{$search}%");
                    })
                    // DNI (question_id 34 o slug dni_nie)
                    ->orWhereExists(function ($sub) use ($search) {
                        $sub->select(DB::raw(1))
                            ->from('answers')
                            ->leftJoin('questions', 'answers.question_id', '=', 'questions.id')
                            ->whereRaw('answers.user_id = users.id')
                            ->where(function ($w) {
                                $w->where('answers.question_id', 34)
                                    ->orWhere('questions.slug', 'dni_nie');
                            })
                            ->where('answers.answer', 'like', "%{$search}%");
                    })
                    // Nombre completo directo
                    ->orWhereExists(function ($sub) use ($search) {
                        $sub->select(DB::raw(1))
                            ->from('answers')
                            ->join('questions', 'answers.question_id', '=', 'questions.id')
                            ->whereRaw('answers.user_id = users.id')
                            ->where('questions.slug', 'nombre_completo')
                            ->where('answers.answer', 'like', "%{$search}%");
                    })
                    // Búsqueda por partes del nombre
                    ->orWhereExists(function ($sub) use ($search) {
                        $sub->select(DB::raw(1))
                            ->from('answers')
                            ->join('questions', 'answers.question_id', '=', 'questions.id')
                            ->whereRaw('answers.user_id = users.id')
                            ->whereIn('questions.slug', ['solo_nombre', 'primer_apellido', 'segundo_apellido'])
                            ->where('answers.answer', 'like', "%{$search}%");
                    });
            });
        }
        if ($request->filled('estado_opx')) {
            $query->whereHas('estadosContratacion', fn ($q) => $q->where('codigo', $request->estado_opx));
        }

        // 4) Filtros universales dinámicos
        $this->aplicarFiltrosUniversales($query, $request);

        // 📨 4) Conteo de comunicaciones SIN N+1 (subquery select)
        $query->addSelect([
            'communications_count' => \App\Models\MailTracking::selectRaw('COUNT(*)')
                ->whereColumn('user_id', 'contrataciones.user_id')
                ->whereNotIn('mail_class', [
                    'App\\Mail\\UserNoBeneficiarioMail',
                    'App\\Mail\\UserBeneficiarioMail',
                    'App\\Mail\\FirstVisitMail',
                    'App\\Mail\\WelcomeMail',
                ]),
        ]);

        $expedientes = $query->paginate(12)->appends($request->only([
            'sector',
            'estado_opx',
            'search',
            'filtros',
        ]));

        // 🧮 5) Transform MUY LIGERO: nada de answers/preguntas aquí
        $expedientes->getCollection()->transform(function ($exp) {
            // Contar documentos requeridos SOLO generales en la lista
            $docs = optional($exp->ayuda->documentos) ?? collect();
            $exp->documentosGenerales = $docs->where('tipo', '!=', 'especial')->values();
            $exp->documentosEspeciales = collect(); // en lista no calculamos condicionales

            // Contar user docs (ya están cargados con columnas mínimas)
            $userDocs = optional($exp->user->userDocuments) ?? collect();
            $exp->uploaded = $userDocs->count();
            $exp->validated = $userDocs->where('estado', 'validado')->count();

            // Historial ya viene limitado (nada que hacer)
            // Métrica de datos contestados/total → si pesa, quítala de la lista o calcúlala en showJson
            $exp->totalDatos = 0;
            $exp->totalDatosContestados = 0;

            // nombre y apellidos
            $exp->nombre_mostrado =
                $exp->ans_nombre_completo
                ?: trim(collect([
                    $exp->ans_solo_nombre,
                    $exp->ans_primer_apellido,
                    $exp->ans_segundo_apellido,
                ])->filter()->implode(' '));

            // Estados OPx de la contratación
            $exp->estados_opx = $exp->estadosContratacion->pluck('codigo')->values()->all();

            return $exp;
        });

        // (Opcional) Si quieres que los contadores globales respeten el tramitador, replica filtros aquí.
        $sectorCounts = [
            'vivienda' => Contratacion::whereHas('ayuda', fn ($q) => $q->where('sector', 'vivienda'))->count(),
            'hijos' => Contratacion::whereHas('ayuda', fn ($q) => $q->where('sector', 'hijos'))->count(),
            'familia' => Contratacion::whereHas('ayuda', fn ($q) => $q->where('sector', 'familia'))->count(),
        ];

        // Estados OPx para filtros y vista (agrupados por grupo, con conteos)
        $estadosOPx = EstadoContratacion::orderBy('grupo')->orderBy('codigo')->get();
        $estadosOPxCounts = DB::table('contratacion_estado_contratacion')
            ->join('estados_contratacion', 'estados_contratacion.id', '=', 'contratacion_estado_contratacion.estado_contratacion_id')
            ->select('estados_contratacion.codigo', DB::raw('count(*) as total'))
            ->groupBy('estados_contratacion.codigo')
            ->pluck('total', 'codigo')
            ->toArray();
        $estadosOPxAgrupados = $estadosOPx->groupBy('grupo');

        return view('admin.historial-expedientes', compact(
            'expedientes',
            'ccaas',
            'ayudasPorCcaa',
            'sectorCounts',
            'provincias',
            'estadosOPx',
            'estadosOPxAgrupados',
            'estadosOPxCounts'
        ));
    }

    /**
     * Función reutilizada para obtener slugs de documentos especiales condicionales
     */
    /**
     * Devuelve los slugs de documentos "especiales" que requiere el usuario
     * según sus respuestas (incluyendo multiple-select en JSON).
     */
    private function obtenerSlugsDocumentosEspecialesCondicionales($ayudaId, $answers)
    {
        // Log::info('Obteniendo slugs de documentos especiales condicionales', [
        //     'ayuda_id' => $ayudaId,
        //     'answers'  => $answers
        // ]);

        $ayuda = Ayuda::with('questionnaire.questions')->find($ayudaId);
        if (! $ayuda || ! $ayuda->questionnaire) {
            return [];
        }

        // 1. Obtener todas las preguntas del cuestionario (añadiendo faltantes)
        $preguntas = $ayuda->questionnaire->questions;
        $slugsFaltantes = ['propietario-vivienda', 'situaciones-propietario'];
        $slugsExistentes = $preguntas->pluck('slug')->all();
        $faltan = array_diff($slugsFaltantes, $slugsExistentes);
        if ($faltan) {
            $extra = Question::whereIn('slug', $faltan)->get();
            $preguntas = $preguntas->concat($extra);
        }

        // 2. Filtrar solo preguntas de vulnerabilidad
        $pregVul = $preguntas->filter(fn ($q) => in_array($q->slug, [
            'grupo-vulnerable',
            'familia-vulnerable',
            'situacion-especial',
            'situacion-especial-2',
            'situaciones-propietario',
            'situaciones-conviviente-propietario',
            'propietario-vivienda',
            'andalucia-grupo-vulnerable',
            'andalucia-grupo-vulnerable2',
        ]));

        // 3. Mapear respuestas (slug => valor)
        $ansVul = $pregVul->mapWithKeys(fn ($q) => [
            $q->slug => $answers[$q->id] ?? null,
        ]);

        // 4. Decodificar posibles JSON de multiple-select
        $decode = function ($val) {
            if (is_string($val)) {
                $json = json_decode($val, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $json;
                }
            }

            return $val;
        };
        $familiaVul = (array) $decode($ansVul->get('familia-vulnerable', null));
        $casosEspecial2 = (array) $decode($ansVul->get('situacion-especial-2', null));
        $situacionesEspecial = (array) $decode($ansVul->get('situacion-especial', null));
        $sitPropietario = (array) $decode($ansVul->get('situaciones-propietario', null));

        // 5. Todos los slugs de documentos "especiales" de esta ayuda
        $documentosEspeciales = DB::table('ayuda_documentos')
            ->where('ayuda_id', $ayudaId)
            ->join('documents', 'ayuda_documentos.documento_id', '=', 'documents.id')
            ->where('documents.tipo', 'especial')
            ->pluck('documents.slug')
            ->toArray();

        $slugs = [];

        // 6. Lógica condicional usando arrays decodificados
        if ($ansVul->get('grupo-vulnerable') === 'Familia numerosa, monoparental, persona con discapacidad ±33%') {
            in_array('Familia numerosa', $familiaVul) && $slugs[] = 'certificado-familia-numerosa';
            in_array('Persona con discapacidad ≥ 33%', $familiaVul) && $slugs[] = 'certificado-discapacidad';
            in_array('Familia monoparental', $familiaVul) && $slugs[] = 'certificado-familia-monoparental';
        }

        if ($ansVul->get('grupo-vulnerable') === 'Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a') {
            in_array('He sido víctima de violencia de género', $casosEspecial2) && $slugs[] = 'certificado-violencia-genero';
            in_array('He sido víctima de terrorismo', $casosEspecial2) && $slugs[] = 'certificado-victima-terrorismo';
            in_array('Estoy en riesgo de exclusión social', $casosEspecial2) && $slugs[] = 'certificado-riesgo-exclusion-social';
            in_array('Soy joven extutelado/a', $casosEspecial2) && $slugs[] = 'certificado-centro-residencial-menores';
            in_array('He estado en prisión (exconvicto/a)', $casosEspecial2) && $slugs[] = 'certificado-exconvicto';
        }

        if ($ansVul->get('grupo-vulnerable') === 'Toda la unidad de convivencia está desempleada y hayan agotado las prestaciones') {
            in_array('Toda la unidad de convivencia está desempleada y hayan agotado las prestaciones', $situacionesEspecial)
                && $slugs[] = 'certificado-situacion-desempleo';
        }

        if ($ansVul->get('grupo-vulnerable') === 'Desahucio, ejecución hipotecaria o dación en pago de tu vivienda, en los últimos cinco años, o afectado/a por situación catastrófica') {
            in_array('Desahucio o ejecución hipotecaria de mi vivienda habitual en los últimos cinco años', $situacionesEspecial)
                && $slugs[] = 'certificado-desahucio';
            in_array('Dación en pago de mi vivienda habitual en los últimos cinco años', $situacionesEspecial)
                && $slugs[] = 'certificado-dacion-pago';
            in_array('Situación catastrófica que afecte a mi vivienda habitual', $situacionesEspecial)
                && $slugs[] = 'certificado-situacion-catastrofica';
        }

        if ((int) $ansVul->get('propietario-vivienda') === 1 && ! in_array('Ninguna de las anteriores', $sitPropietario)) {
            in_array('Separación o divorcio', $sitPropietario) && $slugs[] = 'resolucion_divorcio_separacion';
            in_array('Propietario por herencia de una parte de la casa', $sitPropietario) && $slugs[] = 'nota-simple';
            (
                in_array('Propiedad inaccesible por discapacidad tuya o de algún miembro de tu unidad de convivencia', $sitPropietario)
                || in_array('No puedes acceder a casa por cualquier causa ajena a tu voluntad', $sitPropietario)
            ) && $slugs[] = 'justificante_imposibilidad_habitar_vivienda';
        }

        // 7. Devolver solo los slugs válidos para esta ayuda
        return array_values(array_intersect($slugs, $documentosEspeciales));
    }

    private function obtenerSlugsDocumentosEspecialesCondicionalesPorUsuario(int $ayudaId, int $userId)
    {
        $answersByQid = Answer::where('user_id', $userId)->pluck('answer', 'question_id')->toArray();

        return $this->obtenerSlugsDocumentosEspecialesCondicionales($ayudaId, $answersByQid);
    }

    /**
     * Evalúa si un AyudaDato cumple sus condiciones según las respuestas del usuario
     */
    private function cumpleCondiciones($dato, $answersBySlug)
    {
        if (empty($dato->condiciones) || count($dato->condiciones) === 0) {
            return true;
        }
        foreach ($dato->condiciones as $cond) {
            $valorUsuario = $answersBySlug[$cond->question_slug] ?? null;
            $valorCondicion = $cond->valor;
            $valorCondicionDecoded = json_decode($valorCondicion, true);
            if (is_array($valorCondicionDecoded)) {
                $valorCondicion = $valorCondicionDecoded;
            }
            // Normaliza a string para evitar problemas con 0/"0"
            if (is_array($valorCondicion)) {
                $valorCondicion = array_map('strval', $valorCondicion);
            }
            // Normaliza la respuesta del usuario
            if (is_string($valorUsuario)) {
                $decoded = json_decode($valorUsuario, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $valorUsuario = $decoded;
                }
            }
            if (is_array($valorUsuario)) {
                $valorUsuarioStr = array_map('strval', $valorUsuario);
            } else {
                $valorUsuarioStr = is_null($valorUsuario) ? null : (string) $valorUsuario;
            }

            // DEBUG: Log de la condición evaluada
            Log::debug('[cumpleCondiciones] Evaluando condición', [
                'question_slug' => $cond->question_slug,
                'operador' => $cond->operador,
                'valorCondicion' => $valorCondicion,
                'valorUsuario' => $valorUsuarioStr,
                'dato_slug' => $dato->question_slug,
                'dato_text' => $dato->question->text,
            ]);

            $resultado = true;
            switch ($cond->operador) {
                case '=':
                    if (is_array($valorUsuarioStr)) {
                        $resultado = in_array((string) $valorCondicion, $valorUsuarioStr, true);
                    } else {
                        $resultado = ($valorUsuarioStr === (string) $valorCondicion);
                    }
                    if (! $resultado) {
                        Log::debug('[cumpleCondiciones] FALLO operador =', compact('valorUsuarioStr', 'valorCondicion'));

                        return false;
                    }
                    break;
                case '!=':
                    if (is_array($valorUsuarioStr)) {
                        $resultado = ! in_array((string) $valorCondicion, $valorUsuarioStr, true);
                    } else {
                        $resultado = ($valorUsuarioStr !== (string) $valorCondicion);
                    }
                    if (! $resultado) {
                        Log::debug('[cumpleCondiciones] FALLO operador !=', compact('valorUsuarioStr', 'valorCondicion'));

                        return false;
                    }
                    break;
                case 'in':
                    if (is_array($valorCondicion)) {
                        if (is_array($valorUsuarioStr)) {
                            $resultado = (bool) array_intersect($valorUsuarioStr, $valorCondicion);
                        } else {
                            $resultado = in_array($valorUsuarioStr, $valorCondicion, true);
                        }
                    } else {
                        if (is_array($valorUsuarioStr)) {
                            $resultado = in_array((string) $valorCondicion, $valorUsuarioStr, true);
                        } else {
                            $resultado = ($valorUsuarioStr === (string) $valorCondicion);
                        }
                    }
                    if (! $resultado) {
                        Log::debug('[cumpleCondiciones] FALLO operador in', compact('valorUsuarioStr', 'valorCondicion'));

                        return false;
                    }
                    break;
                case 'not in':
                    if (is_array($valorCondicion)) {
                        if (is_array($valorUsuarioStr)) {
                            $resultado = ! array_intersect($valorUsuarioStr, $valorCondicion);
                        } else {
                            $resultado = ! in_array($valorUsuarioStr, $valorCondicion, true);
                        }
                    } else {
                        if (is_array($valorUsuarioStr)) {
                            $resultado = ! in_array((string) $valorCondicion, $valorUsuarioStr, true);
                        } else {
                            $resultado = ($valorUsuarioStr !== (string) $valorCondicion);
                        }
                    }
                    if (! $resultado) {
                        Log::debug('[cumpleCondiciones] FALLO operador not in', compact('valorUsuarioStr', 'valorCondicion'));

                        return false;
                    }
                    break;
                default:
                    Log::debug('[cumpleCondiciones] Operador desconocido', ['operador' => $cond->operador]);

                    return false;
            }
        }
        Log::debug('[cumpleCondiciones] Todas las condiciones OK para', ['dato_slug' => $dato->question_slug]);

        return true;
    }

    /**
     * Formatea la respuesta según el tipo de pregunta
     */
    private function formatearRespuesta($question, $answer)
    {
        if (! $question) {
            return $answer;
        }

        switch ($question->type) {
            case 'boolean':
                if ($answer === '1' || $answer === 1 || $answer === true) {
                    return '1';
                } elseif ($answer === '0' || $answer === 0 || $answer === false) {
                    return '0';
                }

                return '';

            case 'select':
                $options = $question->options ?? [];
                if (is_numeric($answer) && isset($options[$answer])) {
                    return (string) $answer; // Mantener el índice para el select
                } elseif (is_string($answer)) {
                    // Si la respuesta es una etiqueta, buscar su índice
                    $key = array_search($answer, $options);
                    if ($key !== false) {
                        return (string) $key;
                    }
                }

                // Si no hay respuesta, devolver string vacío
                return '';

            case 'multiple':
                $options = $question->options ?? [];
                if (is_string($answer)) {
                    $decoded = json_decode($answer, true);
                    if (is_array($decoded)) {
                        $answer = $decoded;
                    }
                }
                if (is_array($answer)) {
                    // Para checkboxes, necesitamos devolver los índices seleccionados
                    $selectedIndices = [];
                    foreach ($answer as $idx) {
                        if (is_numeric($idx) && isset($options[$idx])) {
                            $selectedIndices[] = $idx;
                        } else {
                            // Si es una etiqueta, buscar su índice
                            $key = array_search($idx, $options);
                            if ($key !== false) {
                                $selectedIndices[] = $key;
                            }
                        }
                    }

                    return $selectedIndices; // Devolver array de índices para checkboxes
                }

                return [];

            default:
                return $answer;
        }
    }

    public function addConviviente(int $id): JsonResponse
    {
        try {
            $contr = Contratacion::findOrFail($id);

            // Calcula el siguiente índice (máximo + 1)
            $nextIndex = $contr->user
                ->convivientes()
                ->max('index') ?? 0;

            // Crea el nuevo conviviente con el index
            $conv = $contr->user
                ->convivientes()
                ->create([
                    'index' => $nextIndex + 1,
                ]);

            // Prepara las preguntas
            $pregs = $contr->ayuda
                ->datos()
                ->where('tipo_dato', 'conviviente')
                ->get()
                ->map(fn ($d) => [
                    'question_id' => $d->question_id,
                    'slug' => $d->question?->slug ?? '',
                    'text' => $d->question?->text ?? 'Pregunta no encontrada',
                    'type' => $d->question?->type ?? 'text',
                    'answer' => '',
                ]);

            return response()->json([
                'conviviente' => $conv,   // incluye $conv->id y $conv->index
                'datos' => $pregs,
            ], 201, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            Log::error('addConviviente error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['error' => true, 'message' => $e->getMessage()], 500, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Elimina un conviviente de la contratación.
     */
    public function removeConviviente(int $id, int $conviviente): JsonResponse
    {
        $contr = Contratacion::findOrFail($id);
        $conv = $contr->user
            ->convivientes()
            ->where('id', $conviviente)
            ->firstOrFail();

        // Opcional: borra respuestas vinculadas
        Answer::where('conviviente_id', $conv->id)
            ->where('user_id', $contr->user_id)
            ->delete();

        $conv->delete();

        return response()->json(['deleted_id' => $conv->id], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Elimina un arrendador y sus respuestas asociadas
     */
    public function removeArrendador($contratacionId, $arrendadorId)
    {
        $contr = Contratacion::findOrFail($contratacionId);
        $arr = $contr->user->arrendatarios()->where('id', $arrendadorId)->firstOrFail();
        // Elimina respuestas asociadas
        Answer::where('arrendador_id', $arr->id)->delete();
        $arr->delete();

        return response()->json(['success' => true], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Actualizar estado de la contratación usando códigos OPx.
     * Acepta 'codigo' (un código OPx) y reemplaza los estados actuales por ese código.
     */
    public function updateStatus(Request $request, Contratacion $contratacion)
    {
        try {
            $codigosValidos = EstadoContratacion::pluck('codigo')->toArray();
            $data = $request->validate([
                'codigo' => ['required', 'string', 'in:'.implode(',', $codigosValidos)],
            ]);

            $codigosAnteriores = $contratacion->estadosContratacion->pluck('codigo')->values()->all();
            $this->contratacionEstadoService->cambiarEstadosOPx($contratacion, [$data['codigo']], true);
            $contratacion->load('estadosContratacion');
            $estados_opx = $contratacion->estadosContratacion->pluck('codigo')->values()->all();

            return response()->json([
                'estados_opx' => $estados_opx,
                'codigo' => $data['codigo'],
                'codigos_anteriores' => $codigosAnteriores,
            ], 200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Código OPx no válido',
                'errors' => $e->errors(),
            ], 422, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('Error en updateStatus: '.$e->getMessage(), [
                'contratacion_id' => $contratacion->id,
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'error' => 'Error al cambiar el estado',
            ], 500, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Actualizar fase usando código OPx (misma lógica que updateStatus con OPx).
     * Acepta 'codigo' (código OPx) y reemplaza los estados actuales por ese código.
     */
    public function updateFase(Request $request, Contratacion $contratacion)
    {
        $codigosValidos = EstadoContratacion::pluck('codigo')->toArray();
        $data = $request->validate([
            'codigo' => ['required', 'string', 'in:'.implode(',', $codigosValidos)],
        ]);

        $codigosAnteriores = $contratacion->estadosContratacion->pluck('codigo')->values()->all();
        $this->contratacionEstadoService->cambiarEstadosOPx($contratacion, [$data['codigo']], true);
        $contratacion->load('estadosContratacion');
        $estados_opx = $contratacion->estadosContratacion->pluck('codigo')->values()->all();

        return response()->json([
            'estados_opx' => $estados_opx,
            'codigo' => $data['codigo'],
            'codigos_anteriores' => $codigosAnteriores,
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Actualizar estados OPx de la contratación (varios códigos a la vez).
     * Acepta 'codigos' (array de códigos OPx) y reemplaza los estados actuales.
     */
    public function updateEstadoYFase(Request $request, Contratacion $contratacion)
    {
        $codigosValidos = EstadoContratacion::pluck('codigo')->toArray();
        $data = $request->validate([
            'codigos' => ['required', 'array'],
            'codigos.*' => ['string', 'in:'.implode(',', $codigosValidos)],
        ]);

        $codigos = array_values(array_filter(array_map('trim', $data['codigos'])));
        $codigosAnteriores = $contratacion->estadosContratacion->pluck('codigo')->values()->all();

        $this->contratacionEstadoService->cambiarEstadosOPx($contratacion, $codigos, true);
        $contratacion->load('estadosContratacion');
        $estados_opx = $contratacion->estadosContratacion->pluck('codigo')->values()->all();

        // Disparar eventos según códigos OPx actuales
        if (in_array('OP5-Rechazado', $estados_opx, true)) {
            event(new EventContratacionCierreRechazada($contratacion));
        }
        if (in_array('OP1-Resolucion', $estados_opx, true)) {
            event(new EventContratacionCierreResolucion($contratacion));
        }

        return response()->json([
            'estados_opx' => $estados_opx,
            'codigos' => $codigos,
            'codigos_anteriores' => $codigosAnteriores,
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
        ], JSON_UNESCAPED_UNICODE);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ayuda_id' => 'required|exists:ayudas,id',
        ]);

        $ayuda = Ayuda::findOrFail($validated['ayuda_id']);
        $currentUserId = SimulationHelper::getCurrentUserId();
        $user = Auth::user();

        // Validar si el usuario ya ha contratado esta ayuda
        $yaContratada = Contratacion::where('user_id', $currentUserId)
            ->where('ayuda_id', $ayuda->id)
            ->exists();

        if ($yaContratada) {
            return redirect()->route('user.home')
                ->with('error', 'Ya tienes una contratacion solicitada para esta ayuda.')
                ->cookie('ayuda_duplicada', '1', 1);
        }

        if ($ayuda->pago == 1) {
            $producto = $ayuda->productos->first();

            if (! $producto) {
                Log::error("ContratacionController: No se encontró un producto para la ayuda con pago (ID: {$ayuda->id})");

                return redirect()->route('user.home')->with('error', 'No hay productos disponibles para esta ayuda. Por favor, contacta con soporte.');
            }

            $productIdParaStripe = $producto->id;
            $stripeRequest = new \Illuminate\Http\Request;
            $stripeRequest->replace([
                'ayuda_id' => $ayuda->id,
                'product_id' => $productIdParaStripe,
            ]);

            $stripeController = app(\App\Http\Controllers\StripeController::class);

            return $stripeController->createCheckoutSession($stripeRequest);
        } else {
            // Indentificamos el producto sin pago para cada ayuda
            // Lógica directa para asignar el product_id
            if ($ayuda->sector === 'vivienda') {
                $productId = 5; // Producto gratuito para ayudas de alquiler
            } elseif ($ayuda->slug === 'ingreso_minimo_vital' || $ayuda->slug === 'complemento_de_ayuda_para_la_infancia_capi') {
                $productId = 6; // Producto gratuito para IMV
            } else {
                // Usar la relación many-to-many a través de la tabla pivote ayuda_producto
                $product = $ayuda->productos->first();
                $productId = $product?->id;
            }
            // 2) Creamos la contratación y la capturamos
            $contratacion = Contratacion::create([
                'user_id' => $currentUserId,
                'product_id' => $productId,
                'stripe_payment_method' => null,
                'card_last4' => null,
                'card_brand' => null,
                'card_exp_month' => null,
                'card_exp_year' => null,
                'card_funding' => null,
                'fecha_contratacion' => now(),
                'ayuda_id' => $validated['ayuda_id'],
            ]);
            // Lanzamos el evento de UserContracted
            event(new EventUserContracted($ayuda, $user));
            app(\App\Services\EstadoContratacionService::class)->syncEstadosByCodigos($contratacion, ['OP1-Documentacion'], false);

            // 2) Registramos el evento en el historial de actividad
            HistorialActividad::create([
                'contratacion_id' => $contratacion->id,
                'actividad' => 'Contratación realizada',
                'observaciones' => null,
            ]);

        }

        return redirect()->route('user.AyudasSolicitadas')->with('success', 'Contratación creada correctamente.');
    }

    public function updateDatos(Request $r, $id)
    {
        $data = $r->validate([
            // Solicitante
            'solicitanteDatos' => 'array',
            'solicitanteDatos.*.question_slug' => 'required|exists:questions,slug',
            'solicitanteDatos.*.answer' => 'nullable',

            // Hijo
            'hijoDatos' => 'array',
            'hijoDatos.*.question_slug' => 'required|exists:questions,slug',
            'hijoDatos.*.answer' => 'nullable',

            // Contrato
            'contratoDatos' => 'array',
            'contratoDatos.*.question_slug' => 'required|exists:questions,slug',
            'contratoDatos.*.answer' => 'nullable',

            // Dirección
            'direccionDatos' => 'array',
            'direccionDatos.*.question_slug' => 'required|exists:questions,slug',
            'direccionDatos.*.answer' => 'nullable',

            // Convivientes (conviviente_id SIEMPRE requerido por bloque)
            'convivienteDatos' => 'array',
            'convivienteDatos.*.conviviente_id' => 'required|exists:convivientes,id',
            'convivienteDatos.*.datos' => 'nullable|array',
            'convivienteDatos.*.datos.*.question_slug' => 'required_with:convivienteDatos.*.datos|exists:questions,slug',
            'convivienteDatos.*.datos.*.answer' => 'nullable',

            // Arrendadores (arrendador_id SIEMPRE requerido por bloque)
            'arrendadorDatos' => 'array',
            'arrendadorDatos.*.arrendador_id' => 'required|exists:arrendatarios,id',
            'arrendadorDatos.*.preguntas' => 'array',
            'arrendadorDatos.*.preguntas.*.question_slug' => 'required|exists:questions,slug',
            'arrendadorDatos.*.preguntas.*.answer' => 'nullable',
        ]);

        $contr = Contratacion::findOrFail($id);
        $userId = $contr->user_id;

        // Pre-cargamos todos los slugs → IDs para hacer un solo query
        $allSlugs = collect($data['solicitanteDatos'] ?? [])
            ->pluck('question_slug')
            ->merge(collect($data['hijoDatos'] ?? [])->pluck('question_slug'))
            ->merge(collect($data['contratoDatos'] ?? [])->pluck('question_slug'))
            ->merge(collect($data['direccionDatos'] ?? [])->pluck('question_slug'))
            // Aplanar los slugs de todas las preguntas de todos los convivientes:
            ->merge(
                collect($data['convivienteDatos'] ?? [])
                    ->flatMap(function ($conv) {
                        $preguntas = $conv['datos'] ?? [];

                        return collect($preguntas)->pluck('question_slug');
                    })
            )
            // Aplanar los slugs de todas las preguntas de todos los arrendadores:
            ->merge(
                collect($data['arrendadorDatos'] ?? [])
                    ->flatMap(function ($arr) {
                        return collect($arr['preguntas'] ?? [])->pluck('question_slug');
                    })
            )
            ->unique()
            ->values()
            ->toArray();

        $slugToId = Question::whereIn('slug', $allSlugs)
            ->pluck('id', 'slug')
            ->toArray();

        DB::transaction(function () use ($data, $userId, $slugToId) {
            // 1) Solicitante
            foreach ($data['solicitanteDatos'] ?? [] as $item) {
                $slug = $item['question_slug'] ?? null;
                $qid = $slug ? ($slugToId[$slug] ?? null) : null;
                if (! $qid) {
                    continue;
                }

                $question = Question::where('slug', $slug)->first();
                $ans = $item['answer'] ?? '';

                if ($question && $question->type === 'select') {
                    $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                    if (isset($options[$ans])) {
                        $ans = $options[$ans];
                    }
                } elseif ($question && $question->type === 'multiple' && is_array($ans)) {
                    $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                    $ans = array_map(fn ($id) => $options[$id] ?? $id, $ans);
                    $ans = json_encode($ans);
                }
                $ans = trim($ans);

                if ($ans === '') {
                    continue;
                }

                try {
                    Answer::where([
                        'user_id' => $userId,
                        'question_id' => $qid,
                        'conviviente_id' => null,
                        'arrendador_id' => null,
                    ])->delete();

                    Answer::create([
                        'user_id' => $userId,
                        'question_id' => $qid,
                        'conviviente_id' => null,
                        'arrendador_id' => null,
                        'answer' => $ans,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al guardar respuesta solicitante:', [
                        'error' => $e->getMessage(),
                        'user_id' => $userId,
                        'question_id' => $qid,
                        'slug' => $slug,
                        'answer' => $ans,
                    ]);
                }
            }

            // 2) Hijos
            foreach ($data['hijoDatos'] ?? [] as $item) {
                $slug = $item['question_slug'] ?? null;
                $qid = $slug ? ($slugToId[$slug] ?? null) : null;
                if (! $qid) {
                    continue;
                }

                $question = Question::where('slug', $slug)->first();
                $ans = $item['answer'] ?? '';

                if ($question && $question->type === 'select') {
                    $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                    if (isset($options[$ans])) {
                        $ans = $options[$ans];
                    }
                } elseif ($question && $question->type === 'multiple' && is_array($ans)) {
                    $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                    $ans = array_map(fn ($id) => $options[$id] ?? $id, $ans);
                    $ans = json_encode($ans);
                }
                $ans = trim($ans);
                if ($ans === '') {
                    continue;
                }

                Answer::updateOrCreate(
                    ['user_id' => $userId, 'question_id' => $qid],
                    ['answer' => $ans]
                );
            }

            // 3) Contrato
            foreach ($data['contratoDatos'] ?? [] as $item) {
                $slug = $item['question_slug'] ?? null;
                $qid = $slug ? ($slugToId[$slug] ?? null) : null;
                if (! $qid) {
                    continue;
                }

                $question = Question::where('slug', $slug)->first();
                $ans = $item['answer'] ?? '';

                if ($question && $question->type === 'select') {
                    $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                    if (isset($options[$ans])) {
                        $ans = $options[$ans];
                    }
                } elseif ($question && $question->type === 'multiple' && is_array($ans)) {
                    $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                    $ans = array_map(fn ($id) => $options[$id] ?? $id, $ans);
                    $ans = json_encode($ans);
                }
                $ans = trim($ans);
                if ($ans === '') {
                    continue;
                }

                Answer::updateOrCreate(
                    ['user_id' => $userId, 'question_id' => $qid],
                    ['answer' => $ans]
                );
            }

            // 4) Dirección
            foreach ($data['direccionDatos'] ?? [] as $item) {
                $slug = $item['question_slug'] ?? null;
                $qid = $slug ? ($slugToId[$slug] ?? null) : null;
                if (! $qid) {
                    continue;
                }

                $question = Question::where('slug', $slug)->first();
                $ans = $item['answer'] ?? '';

                if ($question && $question->type === 'select') {
                    $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                    if (isset($options[$ans])) {
                        $ans = $options[$ans];
                    }
                } elseif ($question && $question->type === 'multiple' && is_array($ans)) {
                    $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                    $ans = array_map(fn ($id) => $options[$id] ?? $id, $ans);
                    $ans = json_encode($ans);
                }
                $ans = trim($ans);
                if ($ans === '') {
                    continue;
                }

                Answer::updateOrCreate(
                    ['user_id' => $userId, 'question_id' => $qid],
                    ['answer' => $ans]
                );
            }

            // 5) Convivientes
            foreach ($data['convivienteDatos'] ?? [] as $convItem) {
                $convivienteId = $convItem['conviviente_id'] ?? null;
                if (! $convivienteId) {
                    continue;
                }

                $preguntas = $convItem['datos'] ?? [];

                foreach ($preguntas as $preg) {
                    $slug = $preg['question_slug'] ?? null;
                    $qid = $slug ? ($slugToId[$slug] ?? null) : null;
                    if (! $qid) {
                        continue;
                    }

                    $question = Question::where('slug', $slug)->first();
                    $ans = $preg['answer'] ?? '';

                    if ($question && $question->type === 'select') {
                        $options = is_string($question->options)
                            ? json_decode($question->options, true)
                            : ($question->options ?? []);
                        if (is_scalar($ans) && array_key_exists((string) $ans, $options)) {
                            $ans = $options[(string) $ans];
                        }
                    } elseif ($question && $question->type === 'multiple' && is_array($ans)) {
                        $options = is_string($question->options)
                            ? json_decode($question->options, true)
                            : ($question->options ?? []);
                        $ans = array_map(fn ($id) => $options[$id] ?? $id, $ans);
                        $ans = json_encode($ans);
                    }
                    $ans = trim($ans);
                    if ($ans === '') {
                        continue;
                    }

                    try {
                        Answer::where([
                            'user_id' => $userId,
                            'question_id' => $qid,
                            'conviviente_id' => $convivienteId,
                            'arrendador_id' => null,
                        ])->delete();

                        // Luego crear la nueva respuesta
                        Answer::create([
                            'user_id' => $userId,
                            'question_id' => $qid,
                            'conviviente_id' => $convivienteId,
                            'arrendador_id' => null,
                            'answer' => $ans,
                        ]);

                        Log::info('INSERT conviviente', [
                            'user_id' => $userId,
                            'question_id' => $qid,
                            'conviviente_id' => $convivienteId,
                            'slug' => $slug,
                            'answer' => $ans,
                            'answer_original' => $preg['answer'],
                            'answer_convertida' => $ans,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error al guardar respuesta conviviente:', [
                            'error' => $e->getMessage(),
                            'user_id' => $userId,
                            'question_id' => $qid,
                            'conviviente_id' => $convivienteId,
                            'slug' => $slug,
                            'answer' => $ans,
                        ]);
                    }
                }
            }

            // 6) Arrendadores
            foreach ($data['arrendadorDatos'] ?? [] as $arrItem) {
                $arrendadorId = $arrItem['arrendador_id'] ?? null;
                if (! $arrendadorId) {
                    continue;
                }

                $preguntas = $arrItem['preguntas'] ?? [];

                foreach ($preguntas as $preg) {
                    $slug = $preg['question_slug'] ?? null;
                    $qid = $slug ? ($slugToId[$slug] ?? null) : null;
                    if (! $qid) {
                        continue;
                    }

                    $question = Question::where('slug', $slug)->first();
                    $ans = $preg['answer'] ?? '';

                    if ($question && $question->type === 'select') {
                        $options = is_string($question->options)
                            ? json_decode($question->options, true)
                            : ($question->options ?? []);
                        if (is_scalar($ans) && array_key_exists((string) $ans, $options)) {
                            $ans = $options[(string) $ans];
                        }
                    } elseif ($question && $question->type === 'multiple' && is_array($ans)) {
                        $options = is_string($question->options)
                            ? json_decode($question->options, true)
                            : ($question->options ?? []);
                        $ans = array_map(fn ($id) => $options[$id] ?? $id, $ans);
                        $ans = json_encode($ans);
                    }

                    if ($ans === null || (is_string($ans) && trim($ans) === '')) {
                        continue;
                    }
                    if (is_string($ans)) {
                        $ans = trim($ans);
                    }

                    try {
                        Answer::where([
                            'user_id' => $userId,
                            'question_id' => $qid,
                            'conviviente_id' => null,
                            'arrendador_id' => $arrendadorId,
                        ])->delete();

                        Answer::create([
                            'user_id' => $userId,
                            'question_id' => $qid,
                            'conviviente_id' => null,
                            'arrendador_id' => $arrendadorId,
                            'answer' => $ans,
                        ]);

                        Log::info('INSERT arrendador', [
                            'user_id' => $userId,
                            'question_id' => $qid,
                            'arrendador_id' => $arrendadorId,
                            'slug' => $slug,
                            'answer' => $ans,
                            'answer_original' => $preg['answer'],
                            'answer_convertida' => $ans,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error al guardar respuesta arrendador:', [
                            'error' => $e->getMessage(),
                            'user_id' => $userId,
                            'question_id' => $qid,
                            'arrendador_id' => $arrendadorId,
                            'slug' => $slug,
                            'answer' => $ans,
                        ]);
                    }
                }
            }
        });

        if ($r->expectsJson()) {
            return response()->json(['message' => 'Datos actualizados correctamente']);
        }

        return back()->with('success', 'Datos actualizados correctamente');
    }

    /**
     * Crea un nuevo arrendador vacío para la contratación
     */
    public function addArrendador(int $id): JsonResponse
    {
        try {
            $contr = Contratacion::findOrFail($id);

            // Calcula el siguiente índice (máximo + 1)
            $nextIndex = $contr->user
                ->arrendatarios()
                ->max('index') ?? 0;

            // Crea el nuevo arrendador con el index
            $arr = $contr->user
                ->arrendatarios()
                ->create([
                    'index' => $nextIndex + 1,
                ]);

            // Prepara las preguntas
            $pregs = $contr->ayuda
                ->datos()
                ->where('tipo_dato', 'arrendador')
                ->get()
                ->map(fn ($d) => [
                    'question_id' => $d->question_id,
                    'slug' => $d->question?->slug ?? '',
                    'text' => $d->question?->text ?? 'Pregunta no encontrada',
                    'type' => $d->question?->type ?? 'text',
                    'options' => $d->question?->options ?? [],
                    'answer' => '',
                ]);

            return response()->json([
                'arrendador' => $arr,   // incluye $arr->id y $arr->index
                'preguntas' => $pregs,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('addArrendador error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['error' => true, 'message' => $e->getMessage()], 500, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Añade un documento de tramitación personalizado
     */
    public function addDocumentoTramitacion(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255',
            'nombre_personalizado' => 'required|string|max:255',
        ]);

        try {
            $contratacion = Contratacion::findOrFail($id);

            $documento = Document::where('slug', $validated['slug'])
                ->where('tipo', 'interno')
                ->firstOrFail();

            $nextOrden = $contratacion->documentosTramitacionPersonalizados()->max('orden') + 1;

            $docTramitacion = $contratacion->documentosTramitacionPersonalizados()->create([
                'slug' => $validated['slug'],
                'nombre_personalizado' => $validated['nombre_personalizado'],
                'orden' => $nextOrden,
            ]);

            return response()->json([
                'success' => true,
                'documento' => [
                    'id' => $docTramitacion->id,
                    'slug' => $docTramitacion->slug,
                    'nombre_personalizado' => $docTramitacion->nombre_personalizado,
                    'orden' => $docTramitacion->orden,
                    'document_id' => $documento->id,
                ],
                'message' => 'Documento de tramitación añadido correctamente',
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => true,
                'message' => 'El documento no existe o no es de tipo interno',
            ], 404);
        } catch (\Throwable $e) {
            Log::error('addDocumentoTramitacion error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['error' => true, 'message' => 'Error inesperado'], 500);
        }
    }

    /**
     * Elimina un documento de tramitación personalizado
     */
    public function removeDocumentoTramitacion(int $contratacionId, int $documentoId): JsonResponse
    {
        try {
            $contratacion = Contratacion::findOrFail($contratacionId);

            $documento = $contratacion->documentosTramitacionPersonalizados()
                ->findOrFail($documentoId);

            // Verificar que no hay documentos subidos para este documento de tramitación
            $hayDocumentos = UserDocument::where([
                ['user_id', $contratacion->user_id],
                ['slug', $documento->slug],
                ['nombre_personalizado', $documento->nombre_personalizado],
            ])->exists();

            if ($hayDocumentos) {
                return response()->json([
                    'error' => true,
                    'message' => 'No se puede eliminar el documento porque ya hay archivos subidos',
                ], 400);
            }

            $documento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Documento de tramitación eliminado correctamente',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Documento o contratación no encontrados',
            ], 404);
        } catch (\Throwable $e) {
            Log::error('removeDocumentoTramitacion error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['error' => true, 'message' => $e->getMessage()], 500, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Obtiene la lista de documentos internos disponibles
     */
    public function getDocumentosInternos(): JsonResponse
    {
        try {
            $documentos = Document::where('tipo', 'interno')
                ->orderBy('name')
                ->get(['id', 'slug', 'name']);

            return response()->json([
                'success' => true,
                'documentos' => $documentos,
            ]);
        } catch (\Throwable $e) {
            Log::error('getDocumentosInternos error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['error' => true, 'message' => 'Error inesperado'], 500);
        }
    }

    /**
     * Crea un nuevo documento interno
     */
    public function createDocumentoInterno(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:documents,name',
            'slug' => 'required|string|max:255|unique:documents,slug',
            'description' => 'nullable|string',
            'allowed_types' => 'nullable|array',
            'multi_upload' => 'sometimes|boolean',
        ]);

        try {
            $document = Document::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'allowed_types' => isset($data['allowed_types']) ? implode(', ', $data['allowed_types']) : null,
                'multi_upload' => $data['multi_upload'] ?? false,
                'tipo' => 'interno',
            ]);

            return response()->json([
                'success' => true,
                'documento' => [
                    'id' => $document->id,
                    'name' => $document->name,
                    'slug' => $document->slug,
                ],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('createDocumentoInterno error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['error' => true, 'message' => 'Error inesperado'], 500);
        }
    }

    public function getAyudasPorCcaa($ccaa_id): JsonResponse
    {
        $ayudas = Ayuda::where('ccaa_id', $ccaa_id)
            ->where('activo', 1)
            ->orderBy('nombre_ayuda')
            ->get(['id', 'nombre_ayuda']);

        return response()->json($ayudas);
    }

    public function getContratacionesUsuario($user_id, Request $request): JsonResponse
    {
        $excludeAyuda1 = $request->query('exclude_ayuda_1');
        $excludeAyuda2 = $request->query('exclude_ayuda_2');

        $query = Contratacion::with(['ayuda', 'estadosContratacion'])
            ->where('user_id', $user_id);

        if ($excludeAyuda1 && $excludeAyuda2) {
            $query->whereNotIn('ayuda_id', [$excludeAyuda1, $excludeAyuda2]);
        }

        $contrataciones = $query->get(['id', 'ayuda_id', 'estado', 'fase', 'created_at'])
            ->map(function ($contratacion) {
                return [
                    'id' => $contratacion->id,
                    'ayuda_nombre' => $contratacion->ayuda->nombre_ayuda ?? 'Sin nombre',
                    'estado' => $contratacion->estado,
                    'fase' => $contratacion->fase,
                    'estados_opx' => $contratacion->estadosContratacion->pluck('codigo')->values()->all(),
                    'fecha_contratacion' => $contratacion->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json(['contrataciones' => $contrataciones]);
    }

    /**
     * Esta funcion devuelve los datos de una contratación para el panel de admin.Los datos que devuelve son:
     * - Ayuda
     * - Documentos de la ayuda
     * - Preguntas y respuestas del usuario
     * - Convivientes del usuario
     * - Arrendatarios del usuario
     * - Historial de actividad
     * - Documentos tramitados
     * - Motivos de subsanación
     * - Plazo de la ayuda
     * - Usuario
     * - User documents
     * - User documents con temporary_url
     * - User documents con temporary_url
     */
    public function showJson(Contratacion $contratacion): JsonResponse
    {
        try {

            // Cargar de una vez todo lo necesario
            try {
                $contratacion->load([
                    'ayuda.ayudaDocumentos.documento',
                    'ayuda.datos.question',
                    'ayuda.questionnaires.questions',
                    'ayuda.motivosSubsanacionAyuda.document',
                    'user.answers.question',
                    'user.convivientes',
                    'user.convivientes.answers.question',
                    'user.arrendatarios.answers.question',
                    'historial',
                    'user.userDocuments.convivienteByIndex',
                    'motivosSubsanacionContrataciones.motivo.document',
                    'documentosTramitacionPersonalizados.documento',
                    'motivosSubsanacionContrataciones.motivo.document',
                    'estadosContratacion',
                ]);

            } catch (\Exception $e) {

                throw $e;
            }

            // ⏳ Plazo: desde AHORA hasta ayuda.fecha_fin
            $finRaw = optional($contratacion->ayuda)->fecha_fin;
            $fin = null;
            if ($finRaw instanceof \Carbon\Carbon) {
                $fin = $finRaw->copy();
            } elseif (! empty($finRaw)) {
                $fin = Carbon::parse($finRaw);
            }

            $now = Carbon::now();
            $plazoVencido = false;
            $plazoFinTs = null;
            $plazoRestSeg = null;

            if ($fin) {
                $plazoFinTs = $fin->timestamp * 1000;
                $plazoVencido = $now->greaterThanOrEqualTo($fin);
                $plazoRestSeg = $plazoVencido ? 0 : $now->diffInSeconds($fin);
            }

            // --------- Usuario + respuestas ----------
            $user = [
                'id' => $contratacion->user->id,
                'name' => $contratacion->user->name,
                'email' => $contratacion->user->email,
                'dni' => $contratacion->user->dni ?? null,
                'telefono' => $contratacion->user->telefono ?? null,
                'answers' => $contratacion->user->answers->map(function ($ans) {
                    return [
                        'id' => $ans->id,
                        'answer' => $ans->answer,
                        'user_id' => $ans->user_id,
                        'conviviente_id' => $ans->conviviente_id,
                        'arrendador_id' => $ans->arrendador_id,
                        'question_id' => $ans->question_id,
                        'question' => [
                            'id' => $ans->question->id,
                            'slug' => $ans->question->slug,
                            'text' => $ans->question->text,
                            'type' => $ans->question->type,
                            'options' => $ans->question->options ?? [],
                        ],
                    ];
                })->values(),
                'convivientes' => $contratacion->user->convivientes
                    ->map(fn ($c) => ['id' => $c->id, 'index' => $c->index, 'token' => $c->token])
                    ->values(),
            ];

            // --------- User documents con temporary_url ----------
            $gcs = app(GcsUploaderService::class);

            $userDocuments = ($contratacion->user->userDocuments ?? collect())->map(function ($ud) use ($gcs) {
                $tmp = null;
                try {
                    if (! empty($ud->file_path)) {
                        $mimeType = $ud->file_type ?? 'application/pdf';
                        $overrides = ['responseType' => $mimeType];
                        $tmp = $gcs->getTemporaryUrl($ud->file_path, 60, $overrides);
                    }
                } catch (\Throwable $e) {
                    Log::warning('No se pudo generar temporary_url', [
                        'id' => $ud->id,
                        'path' => $ud->file_path,
                        'e' => $e->getMessage(),
                    ]);
                }

                $convivienteInfo = null;
                if ($ud->conviviente_index !== null) {
                    $conviviente = $ud->convivienteByIndex;
                    if ($conviviente) {
                        $convivienteInfo = [
                            'id' => $conviviente->id,
                            'index' => $conviviente->index,
                            'token' => $conviviente->token,
                            'name' => $conviviente->name ?? null,
                        ];
                    }
                }

                $downloadUrl = null;
                try {
                    if (! empty($ud->file_path)) {
                        $gcsDownload = app(GcsUploaderService::class);
                        $filename = $ud->nombre_personalizado ?: $ud->file_name;
                        $downloadUrl = $gcsDownload->getDownloadUrl($ud->file_path, $filename, $ud->file_type ?? 'application/pdf', 60);
                    }
                } catch (\Throwable $e) {
                    Log::warning('No se pudo generar download_url', [
                        'id' => $ud->id,
                        'path' => $ud->file_path,
                        'e' => $e->getMessage(),
                    ]);
                }

                return [
                    'id' => $ud->id,
                    'user_id' => $ud->user_id,
                    'document_id' => $ud->document_id,
                    'slug' => $ud->slug,
                    'estado' => $ud->estado,
                    'nota_rechazo' => $ud->nota_rechazo,
                    'nombre_personalizado' => $ud->nombre_personalizado,
                    'conviviente_index' => $ud->conviviente_index, // Campo directo de la tabla
                    'conviviente' => $convivienteInfo, // Información del conviviente
                    'temporary_url' => $tmp,
                    'download_url' => $downloadUrl,
                ];
            })->values();

            // inyectamos en user para que el front lo lea en data.user.user_documents
            $user['user_documents'] = $userDocuments;

            // Para cruzar rápido
            $udByDocId = $userDocuments->groupBy('document_id');
            $udBySlug = $userDocuments->groupBy('slug');

            // --------- Documentos de la ayuda ----------
            $docsAyuda = $contratacion->ayuda->ayudaDocumentos ?? collect();

            $mapDoc = function ($ayudaDoc) {
                $doc = $ayudaDoc->documento;

                // Verificar que el documento existe
                if (! $doc) {
                    return null;
                }

                return [
                    'id' => $doc->id,
                    'slug' => $doc->slug,
                    'name' => $doc->name ?? $doc->nombre ?? $doc->titulo ?? Str::headline($doc->slug),
                    'tipo' => $doc->tipo,
                    'es_personalizado' => (bool) ($doc->es_personalizado ?? false),
                    'nombre_personalizado' => $doc->nombre_personalizado ?? null,
                    'es_obligatorio' => (bool) ($ayudaDoc->es_obligatorio ?? false),
                ];
            };

            $documentosGenerales = $docsAyuda->filter(function ($ayudaDoc) {
                return $ayudaDoc->documento && $ayudaDoc->documento->tipo === 'general';
            })->map(function ($ayudaDoc) use ($mapDoc, $udByDocId) {
                $doc = $ayudaDoc->documento;
                $docData = $mapDoc($ayudaDoc);

                // Si el documento es null, devolver null
                if (! $docData) {
                    return null;
                }

                $uploads = ($udByDocId[$doc->id] ?? collect())->whereNull('conviviente_index')->values();

                return array_merge($docData, [
                    'subido' => $uploads->isNotEmpty(),
                    'uploads' => $uploads,
                ]);
            })->filter()->values();

            $slugsEspeciales = $this->obtenerSlugsDocumentosEspecialesCondicionalesPorUsuario(
                (int) $contratacion->ayuda_id,
                (int) $contratacion->user_id
            );
            // -------- Documentos especiales (condicionales) ----------
            $documentosEspeciales = $docsAyuda->filter(function ($ayudaDoc) use ($slugsEspeciales) {
                return $ayudaDoc->documento &&
                       $ayudaDoc->documento->tipo === 'especial' &&
                       in_array($ayudaDoc->documento->slug, $slugsEspeciales);
            })->map(function ($ayudaDoc) use ($mapDoc, $udByDocId) {
                $doc = $ayudaDoc->documento;
                $docData = $mapDoc($ayudaDoc);

                // Si el documento es null, devolver null
                if (! $docData) {
                    return null;
                }

                $uploads = ($udByDocId[$doc->id] ?? collect())->whereNull('conviviente_index')->values();

                return array_merge($docData, [
                    'subido' => $uploads->isNotEmpty(),
                    'uploads' => $uploads,
                ]);
            })->filter()->values();

            // Documentos de tramitación de la ayuda
            $documentosTramitacionAyuda = $docsAyuda->filter(function ($ayudaDoc) {
                return $ayudaDoc->documento && $ayudaDoc->documento->tipo === 'tramitacion';
            })->map(function ($ayudaDoc) use ($mapDoc, $udBySlug) {
                $doc = $ayudaDoc->documento;
                $docData = $mapDoc($ayudaDoc);

                // Si el documento es null, devolver null
                if (! $docData) {
                    return null;
                }

                $uploads = ($udBySlug[$doc->slug] ?? collect())->whereNull('conviviente_index')->values();

                return array_merge($docData, [
                    'subido' => $uploads->isNotEmpty(),
                    'uploads' => $uploads,
                ]);
            })->filter()->values();

            // Documentos de tramitación personalizados
            $documentosTramitacionPersonalizados = $contratacion->documentosTramitacionPersonalizados->map(function ($docTramitacion) use ($udBySlug) {
                $uploads = ($udBySlug[$docTramitacion->slug] ?? collect())
                    ->whereNull('conviviente_index')
                    ->where('nombre_personalizado', $docTramitacion->nombre_personalizado)
                    ->values();

                return [
                    'id' => $docTramitacion->id,
                    'slug' => $docTramitacion->slug,
                    'name' => $docTramitacion->nombre_personalizado,
                    'tipo' => 'tramitacion',
                    'es_personalizado' => true,
                    'nombre_personalizado' => $docTramitacion->nombre_personalizado,
                    'orden' => $docTramitacion->orden,
                    'document_id' => $docTramitacion->documento->id ?? null,
                    'subido' => $uploads->isNotEmpty(),
                    'uploads' => $uploads,
                ];
            });

            // Combinar documentos de tramitación de la ayuda y personalizados
            $documentosTramitacion = $documentosTramitacionAyuda->concat($documentosTramitacionPersonalizados);

            // Documentos de tramitación personalizados
            $documentosTramitacionPersonalizados = $contratacion->documentosTramitacionPersonalizados->map(function ($docTramitacion) use ($udBySlug) {
                $uploads = ($udBySlug[$docTramitacion->slug] ?? collect())
                    ->whereNull('conviviente_index')
                    ->where('nombre_personalizado', $docTramitacion->nombre_personalizado)
                    ->values();

                return [
                    'id' => $docTramitacion->id,
                    'slug' => $docTramitacion->slug,
                    'name' => $docTramitacion->nombre_personalizado,
                    'tipo' => 'tramitacion',
                    'es_personalizado' => true,
                    'nombre_personalizado' => $docTramitacion->nombre_personalizado,
                    'orden' => $docTramitacion->orden,
                    'document_id' => $docTramitacion->documento->id ?? null,
                    'subido' => $uploads->isNotEmpty(),
                    'uploads' => $uploads,
                ];
            });

            // Combinar documentos de tramitación de la ayuda y personalizados
            $documentosTramitacion = $documentosTramitacionAyuda->concat($documentosTramitacionPersonalizados);

            // --------- Recibos mensuales generados ----------
            $documentosRecibos = collect();
            $recibosSubidos = collect();

            if ($contratacion->ayuda->sector === 'vivienda' &&
                $contratacion->ayuda->fecha_inicio_periodo &&
                $contratacion->ayuda->fecha_fin_periodo) {

                $documentosAyudaService = app(DocumentosAyudaService::class);
                $recibosGenerados = $documentosAyudaService->generarDocumentosRecibos($contratacion->ayuda);

                $documentosRecibos = $recibosGenerados->map(function ($recibo) use ($udBySlug) {
                    $slug = $recibo->slug;
                    $uploads = ($udBySlug[$slug] ?? collect())->whereNull('conviviente_index')->values();

                    return [
                        'id' => $recibo->id ?? null,
                        'slug' => $slug,
                        'name' => $recibo->name ?? $recibo->nombre_personalizado,
                        'tipo' => 'mensual',
                        'es_personalizado' => false,
                        'nombre_personalizado' => $recibo->nombre_personalizado ?? $recibo->name,
                        'mes' => $recibo->mes ? (is_object($recibo->mes) && method_exists($recibo->mes, 'format') ? $recibo->mes->format('Y-m') : $recibo->mes) : null,
                        'subido' => $uploads->isNotEmpty(),
                        'uploads' => $uploads,
                    ];
                })->values();

                $recibosSubidos = $userDocuments
                    ->filter(fn ($ud) => Str::contains($ud['slug'], 'recibo'))
                    ->keyBy('slug');
            }

            // --------- Selector de documentos internos (para tramitación personalizada) ----------
            $docsInternos = Document::where('tipo', 'interno')->get();
            $documentosInternosDisponibles = $docsInternos->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'slug' => $doc->slug,
                    'name' => $doc->name ?? $doc->nombre ?? $doc->titulo ?? Str::headline($doc->slug),
                    'tipo' => $doc->tipo,
                    'es_personalizado' => (bool) ($doc->es_personalizado ?? false),
                    'nombre_personalizado' => $doc->nombre_personalizado ?? null,
                    'es_obligatorio' => false, // Los documentos internos no son obligatorios
                ];
            })->values();
            $documentosInternosMap = $docsInternos->pluck('id', 'slug'); // slug => id

            // --------- Construcción de bloques desde ayuda_datos ----------
            $datosAyuda = $contratacion->ayuda->datos ?? collect();

            // Respuestas del TITULAR por slug (sin conviviente/arrendador)
            $bySlugTitular = $contratacion->user->answers
                ->filter(fn ($a) => $a->question && ! empty($a->question->slug) && $a->conviviente_id === null && $a->arrendador_id === null)
                ->keyBy(fn ($a) => $a->question->slug);

            $byKeyTitular = $contratacion->user->answers
                ->filter(fn ($a) => $a->question && ! empty($a->question->slug) && $a->conviviente_id === null && $a->arrendador_id === null)
                ->keyBy(fn ($a) => sprintf('%s|c:0|a:0', $a->question->slug));

            $solicitanteDatos = $datosAyuda->where('tipo_dato', 'solicitante')
                ->unique(fn ($ad) => $ad->question_slug)
                ->map(function ($ad) use ($bySlugTitular) {
                    $q = $ad->question;
                    $slug = $q?->slug ?? $ad->question_slug;

                    $opts = $q?->options ?? [];
                    if (is_string($opts)) {
                        $dec = json_decode($opts, true);
                        $opts = json_last_error() === JSON_ERROR_NONE ? $dec : [];
                    }

                    $ans = $bySlugTitular->get($slug);
                    $answerRaw = $ans?->answer ?? '';

                    $answer = AnswerNormalizer::normalize($answerRaw, $q?->type, $opts);

                    Log::info("Procesando campo SOLICITANTE: {$slug}", [
                        'question_type' => $q?->type,
                        'question_text' => $q?->text,
                        'options' => $opts,
                        'key_buscada' => $slug,
                        'respuesta_encontrada' => $ans ? [
                            'id' => $ans->id,
                            'answer' => $ans->answer,
                            'conviviente_id' => $ans->conviviente_id,
                            'arrendador_id' => $ans->arrendador_id,
                        ] : null,
                        'answer_raw' => $answerRaw,
                        'answer_normalizada' => $answer,
                    ]);

                    return [
                        'question_id' => $q?->id,
                        'slug' => (string) $slug,
                        'text' => $q?->text ?? Str::headline(str_replace('_', ' ', $slug)),
                        'type' => $q?->type ?? 'string',
                        'options' => $opts,
                        'answer' => $answer,
                        'fase' => $ad->fase,
                        'conviviente_id' => null, // Datos del solicitante siempre tienen conviviente_id = null
                        'arrendador_id' => null, // Datos del solicitante siempre tienen arrendador_id = null
                        '_key' => $slug, // clave simple para Alpine
                    ];
                })
                ->values();

            Log::info('Solicitante datos construidos: '.json_encode($solicitanteDatos));

            $contratoDatos = $this->buildDatosFromAyudaSimple(
                $datosAyuda->where('tipo_dato', 'contrato'),
                $bySlugTitular
            );

            $direccionDatos = $this->buildDatosFromAyudaSimple(
                $datosAyuda->where('tipo_dato', 'direccion'),
                $bySlugTitular
            );

            $hijoDatos = $this->buildDatosFromAyudaSimple(
                $datosAyuda->where('tipo_dato', 'hijo'),
                $bySlugTitular
            );

            // Convivientes: preguntas definidas en ayuda_datos (tipo_dato=conviviente)
            $convQuestions = $datosAyuda
                ->where('tipo_dato', 'conviviente')
                ->map(fn ($ad) => [
                    'id' => $ad->question?->id,
                    'slug' => $ad->question?->slug ?? $ad->question_slug, // fallback
                    'text' => $ad->question?->text ?? Str::headline(str_replace('_', ' ', $ad->question_slug)),
                    'type' => $ad->question?->type ?? 'string',
                    'options' => (function ($o) {
                        if (is_string($o)) {
                            $d = json_decode($o, true);

                            return json_last_error() ? [] : $d;
                        }

                        return $o ?: [];
                    })($ad->question?->options),
                ])
                ->values();
            Log::info('Conviviente questions: '.json_encode($convQuestions->toArray()));
            $convivienteDatos = $contratacion->user->convivientes->map(function ($conv) use ($convQuestions, $contratacion) {
                $convAnsBySlug = $contratacion->user->answers
                    ->where('conviviente_id', $conv->id)
                    ->filter(fn ($a) => $a->question && ! empty($a->question->slug))
                    ->keyBy(fn ($a) => $a->question->slug);

                return [
                    'conviviente_id' => $conv->id,
                    'index' => $conv->index,
                    'datos' => $convQuestions->map(function ($q) use ($convAnsBySlug, $conv) {
                        $ansObj = $convAnsBySlug[$q['slug']] ?? null;
                        $answerRaw = $ansObj?->answer ?? '';

                        $answer = AnswerNormalizer::normalize($answerRaw, $q['type'], $q['options']);

                        return [
                            'question_id' => $q['id'],
                            'slug' => (string) $q['slug'],
                            'text' => $q['text'],
                            'type' => $q['type'],
                            'options' => $q['options'],
                            'answer' => $answer,
                            'conviviente_id' => $conv->id,
                            'arrendador_id' => null,
                        ];
                    })->values(),
                ];
            })->values();

            Log::info('Conviviente datos: '.json_encode($convivienteDatos->toArray()));
            $mostrarConvivientes = $convQuestions->isNotEmpty();

            // --------- Arrendadores (si usas este bloque con answers por arrendador) ----------
            $arrendadorQuestions = $datosAyuda
                ->where('tipo_dato', 'arrendador')
                ->map(function ($ad) {
                    $q = $ad->question;
                    $opts = $q?->options ?? [];
                    if (is_string($opts)) {
                        $dec = json_decode($opts, true);
                        $opts = json_last_error() === JSON_ERROR_NONE ? $dec : $opts;
                    }

                    return [
                        'id' => $q?->id,
                        'slug' => $q?->slug ?? $ad->question_slug,
                        'text' => $q?->text ?? Str::headline(str_replace('_', ' ', $ad->question_slug)),
                        'type' => $q?->type ?? 'string',
                        'options' => $opts,
                    ];
                });

            $arrendadoresDatos = $contratacion->user->arrendatarios
                ->map(function ($arr, $idx) use ($arrendadorQuestions) {
                    $preguntas = $arrendadorQuestions->map(function ($q) use ($arr) {
                        $existingAnswer = $arr->answers->where('question_id', $q['id'])->first();

                        $answer = $existingAnswer ?
                            AnswerNormalizer::normalize($existingAnswer->answer, $q['type'], $q['options']) :
                            '';

                        return [
                            'question_id' => $q['id'],
                            'slug' => $q['slug'],
                            'text' => $q['text'],
                            'type' => $q['type'],
                            'options' => $q['options'],
                            'answer' => $answer,
                        ];
                    });

                    return [
                        'arrendador_id' => $arr->id,
                        'index' => $idx + 1,
                        'preguntas' => $preguntas,
                    ];
                })->values();

            // --------- Historial ----------
            $historial = ($contratacion->historial ?? collect())->map(function ($h) {
                return [
                    'id' => $h->id,
                    'actividad' => $h->actividad,
                    'observaciones' => $h->observaciones,
                    'fecha_inicio' => optional($h->fecha_inicio)->toIso8601String(),
                    'fecha_ts' => optional($h->fecha_inicio)->getTimestampMs(),
                ];
            })->values();

            // --------- Totales (ajusta si necesitas) ----------
            $totalDatos = 134;
            $totalDatosContestados = $contratacion->user->answers->count();

            // --------- Salida ----------
            $out = [
                'id' => $contratacion->id,
                'user_id' => $contratacion->user_id,
                'product_id' => $contratacion->product_id,
                'fecha_contratacion' => optional($contratacion->fecha_contratacion)->format('Y-m-d H:i:s'),
                'estado' => $contratacion->estado,
                'fase' => $contratacion->fase,
                'estados_opx' => $contratacion->estadosContratacion->pluck('codigo')->values()->all(),
                'monto_comision' => $contratacion->monto_comision,
                'monto_total_ayuda' => $contratacion->monto_total_ayuda,
                'ayuda_id' => $contratacion->ayuda_id,
                'nombre_ayuda' => $contratacion->ayuda->nombre_ayuda ?? '',

                // ayuda con documentos
                'ayuda' => [
                    'id' => $contratacion->ayuda->id,
                    'nombre_ayuda' => $contratacion->ayuda->nombre_ayuda,
                    'slug' => $contratacion->ayuda->slug,
                    'description' => $contratacion->ayuda->description,
                    'sector' => $contratacion->ayuda->sector,
                    'fecha_inicio' => $contratacion->ayuda->fecha_inicio ? (is_string($contratacion->ayuda->fecha_inicio) ? $contratacion->ayuda->fecha_inicio : $contratacion->ayuda->fecha_inicio->format('Y-m-d')) : null,
                    'fecha_fin' => $contratacion->ayuda->fecha_fin ? (is_string($contratacion->ayuda->fecha_fin) ? $contratacion->ayuda->fecha_fin : $contratacion->ayuda->fecha_fin->format('Y-m-d')) : null,
                    'fecha_inicio_periodo' => $contratacion->ayuda->fecha_inicio_periodo ? (is_string($contratacion->ayuda->fecha_inicio_periodo) ? $contratacion->ayuda->fecha_inicio_periodo : $contratacion->ayuda->fecha_inicio_periodo->format('Y-m-d')) : null,
                    'fecha_fin_periodo' => $contratacion->ayuda->fecha_fin_periodo ? (is_string($contratacion->ayuda->fecha_fin_periodo) ? $contratacion->ayuda->fecha_fin_periodo : $contratacion->ayuda->fecha_fin_periodo->format('Y-m-d')) : null,
                    'presupuesto' => $contratacion->ayuda->presupuesto,
                    'cuantia_usuario' => $contratacion->ayuda->cuantia_usuario,
                    'activo' => $contratacion->ayuda->activo,
                    'organo_id' => $contratacion->ayuda->organo_id,
                    'questionnaire_id' => $contratacion->ayuda->questionnaire_id,
                    'documentos' => array_merge(
                        $documentosGenerales->toArray(),
                        $documentosEspeciales->toArray(),
                        $documentosTramitacion->toArray()
                    ),
                    'datos' => [], // Se llena desde solicitanteDatos, etc.
                ],

                // documentos
                'documentosGenerales' => $documentosGenerales,
                'documentosEspeciales' => $documentosEspeciales,
                'documentosTramitacion' => $documentosTramitacion,
                'documentosRecibos' => $documentosRecibos,
                'recibosSubidos' => $recibosSubidos,

                // bloques de datos desde ayuda_datos
                'solicitanteDatos' => $solicitanteDatos,
                'hijoDatos' => $hijoDatos,
                'contratoDatos' => $contratoDatos,
                'direccionDatos' => $direccionDatos,
                'convivienteDatos' => $convivienteDatos,
                'mostrarConvivientes' => $mostrarConvivientes,

                // arrendadores
                'arrendadoresDatos' => $arrendadoresDatos,
                'arrendadorPreguntas' => [],
                'mostrarArrendadores' => $arrendadoresDatos->isNotEmpty(),

                // user + docs
                'user' => $user,

                // historial
                'historial' => $historial,

                // Motivos de subsanación para la ayuda
                'motivos_subsanacion' => $contratacion->ayuda->motivosSubsanacionAyuda->map(function ($motivo) {
                    return [
                        'id' => $motivo->id,
                        'descripcion' => $motivo->descripcion,
                        'motivo' => $motivo->motivo,
                        'document_id' => $motivo->document_id,
                        'document' => $motivo->document ? [
                            'id' => $motivo->document->id,
                            'name' => $motivo->document->name,
                            'slug' => $motivo->document->slug,
                        ] : null,
                    ];
                }),

                // Motivos de subsanación seleccionados para esta contratación
                'motivos_subsanacion_seleccionados' => $contratacion->motivosSubsanacionContrataciones->map(function ($motivoContratacion) {
                    return [
                        'id' => $motivoContratacion->id,
                        'motivo_id' => $motivoContratacion->motivo_id,
                        'estado_subsanacion' => $motivoContratacion->estado_subsanacion,
                        'nota' => $motivoContratacion->nota,
                        'motivo' => $motivoContratacion->motivo ? [
                            'id' => $motivoContratacion->motivo->id,
                            'descripcion' => $motivoContratacion->motivo->descripcion,
                            'motivo' => $motivoContratacion->motivo->motivo,
                            'document_id' => $motivoContratacion->motivo->document_id,
                            'document' => $motivoContratacion->motivo->document ? [
                                'id' => $motivoContratacion->motivo->document->id,
                                'name' => $motivoContratacion->motivo->document->name,
                                'slug' => $motivoContratacion->motivo->document->slug,
                            ] : null,
                        ] : null,
                    ];
                }),

                // countdown
                'plazo_fin_ts' => $plazoFinTs,
                'plazo_vencido' => $plazoVencido,
                'plazo_restante_segundos' => $plazoRestSeg,

                // selector de internos (para modal añadir doc tramitación)
                'documentosInternosDisponibles' => $documentosInternosDisponibles,
                'documentosInternosMap' => $documentosInternosMap,
            ];

            return response()->json($out, 200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {

            // Re-throw para que Laravel genere el 500
            throw $e;
        }
    }

    /**
     * Guarda los motivos de subsanación seleccionados para una contratación
     */
    public function guardarMotivosSubsanacion(Request $request, Contratacion $contratacion): JsonResponse
    {
        try {
            $request->validate([
                'motivos' => 'required|array',
                'motivos.*.motivo_id' => 'required|integer|exists:motivos_subsanacion_ayuda,id',
                'motivos.*.nota' => 'nullable|string|max:1000',
            ]);

            // Extraer los IDs de motivos para validar
            $motivoIds = collect($request->motivos)->pluck('motivo_id')->toArray();

            // Verificar que los motivos pertenecen a la ayuda de la contratación
            $motivosValidos = MotivoSubsanacionAyuda::where('ayuda_id', $contratacion->ayuda_id)
                ->whereIn('id', $motivoIds)
                ->pluck('id')
                ->toArray();

            if (count($motivosValidos) !== count($motivoIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Algunos motivos no son válidos para esta ayuda',
                ], 422);
            }

            // Eliminar motivos existentes para esta contratación
            MotivoSubsanacionContratacion::where('contratacion_id', $contratacion->id)->delete();

            // Crear nuevos registros para los motivos seleccionados
            foreach ($request->motivos as $motivoData) {
                MotivoSubsanacionContratacion::create([
                    'contratacion_id' => $contratacion->id,
                    'motivo_id' => $motivoData['motivo_id'],
                    'estado_subsanacion' => MotivoSubsanacionContratacion::ESTADO_PENDIENTE,
                    'nota' => $motivoData['nota'] ?? null,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Motivos de subsanación guardados correctamente',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al guardar motivos de subsanación: '.$e->getMessage(), [
                'contratacion_id' => $contratacion->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Crea un nuevo motivo de subsanación para la ayuda de la contratación
     */
    public function crearMotivoSubsanacion(Request $request, Contratacion $contratacion): JsonResponse
    {
        try {
            $request->validate([
                'descripcion' => 'required|string|max:1000',
                'motivo' => 'required|in:Padrón,Contrato,Recibos',
                'document_id' => 'nullable|integer|exists:documents,id',
            ]);

            // Crear el motivo de subsanación
            $motivo = MotivoSubsanacionAyuda::create([
                'ayuda_id' => $contratacion->ayuda_id,
                'descripcion' => $request->descripcion,
                'motivo' => $request->motivo,
                'document_id' => $request->document_id,
                'index' => MotivoSubsanacionAyuda::where('ayuda_id', $contratacion->ayuda_id)->max('index') + 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Motivo de subsanación creado correctamente',
                'motivo' => [
                    'id' => $motivo->id,
                    'descripcion' => $motivo->descripcion,
                    'motivo' => $motivo->motivo,
                    'document_id' => $motivo->document_id,
                    'document' => $motivo->document ? [
                        'id' => $motivo->document->id,
                        'name' => $motivo->document->name,
                        'slug' => $motivo->document->slug,
                    ] : null,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al crear motivo de subsanación: '.$e->getMessage(), [
                'contratacion_id' => $contratacion->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Elimina un motivo de subsanación asociado a la ayuda de la contratación
     */
    public function eliminarMotivoSubsanacion(Request $request, Contratacion $contratacion, MotivoSubsanacionAyuda $motivo): JsonResponse
    {
        try {
            // Validar que el motivo pertenece a la misma ayuda de la contratación
            if ((int) $motivo->ayuda_id !== (int) $contratacion->ayuda_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El motivo no pertenece a la ayuda de esta contratación',
                ], 422);
            }

            // Eliminar relaciones con contrataciones (si existen)
            MotivoSubsanacionContratacion::where('motivo_id', $motivo->id)->delete();

            // Eliminar motivo
            $motivo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Motivo de subsanación eliminado correctamente',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar motivo de subsanación: '.$e->getMessage(), [
                'contratacion_id' => $contratacion->id,
                'motivo_id' => $motivo->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Obtiene los documentos disponibles para asociar a motivos de subsanación
     */
    public function getDocumentosDisponibles(): JsonResponse
    {
        try {
            $documentos = Document::where('tipo', '!=', 'interno')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'tipo']);

            return response()->json([
                'success' => true,
                'documentos' => $documentos,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al obtener documentos disponibles: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    private function buildDatosFromAyuda($ayudaDatos, $answersByKey)
    {
        return $ayudaDatos
            // ÚNICO por combinación (slug + conv + arr)
            ->unique(fn ($ad) => implode('|', [
                $ad->question_slug,
                $ad->conviviente_id ?? '0',
                $ad->arrendador_id ?? '0',
            ]))
            ->map(function ($ad) use ($answersByKey) {
                $q = $ad->question;
                $slug = $q?->slug ?? $ad->question_slug;

                // normaliza options
                $opts = $q?->options ?? [];
                if (is_string($opts)) {
                    $dec = json_decode($opts, true);
                    $opts = json_last_error() === JSON_ERROR_NONE ? $dec : [];
                }

                // clave compuesta estable
                $key = sprintf('%s|c:%s|a:%s', $slug, $ad->conviviente_id ?? '0', $ad->arrendador_id ?? '0');

                $ans = $answersByKey->get($key);

                return [
                    'question_id' => $q?->id,
                    'slug' => (string) $slug,
                    'text' => $q?->text ?? Str::headline(str_replace('_', ' ', $slug)),
                    'type' => $q?->type ?? 'string',
                    'options' => $opts,
                    'answer' => $ans?->answer ?? '',
                    'fase' => $ad->fase,
                    'conviviente_id' => $ad->conviviente_id ?? null,
                    'arrendador_id' => $ad->arrendador_id ?? null,
                    '_key' => $key, // para Alpine
                ];
            })
            ->values();
    }

    /**
     * Esta funcion construye los datos de la ayuda simple desde la tabla ayuda_datos.
     *
     * @return array
     */
    private function buildDatosFromAyudaSimple($ayudaDatos, $answersByKey)
    {
        return $ayudaDatos
            ->map(function ($ad) use ($answersByKey) {
                $q = $ad->question;
                $slug = $q?->slug ?? $ad->question_slug;

                $opts = $q?->options ?? [];
                if (is_string($opts)) {
                    $dec = json_decode($opts, true);
                    $opts = json_last_error() === JSON_ERROR_NONE ? $dec : [];
                }

                $key = $slug;

                $ans = $answersByKey->get($key);

                $answerRaw = $ans?->answer ?? '';
                $answer = AnswerNormalizer::normalize($answerRaw, $q?->type ?? 'string', $opts);

                return [
                    'question_id' => $q?->id,
                    'slug' => (string) $slug,
                    'text' => $q?->text ?? Str::headline(str_replace('_', ' ', $slug)),
                    'type' => $q?->type ?? 'string',
                    'options' => $opts,
                    'answer' => $answer,
                    'fase' => $ad->fase,
                    'conviviente_id' => null, // Datos del solicitante siempre tienen conviviente_id = null
                    'arrendador_id' => null, // Datos del solicitante siempre tienen arrendador_id = null
                    '_key' => $key, // clave simple para Alpine
                ];
            })
            ->values();
    }

    private function buildDatos($bySlug, array $slugs, array $extraMeta = [])
    {
        return collect($slugs)->map(function ($slug) use ($bySlug, $extraMeta) {
            $ans = $bySlug->get($slug);
            $q = $ans?->question;

            // Normalizar options a array indexado 0..n
            $optsRaw = $q?->options ?? [];
            if (is_string($optsRaw)) {
                $decoded = json_decode($optsRaw, true);
                $options = is_array($decoded) ? array_values($decoded) : [];
            } elseif (is_array($optsRaw)) {
                $options = array_values($optsRaw);
            } else {
                $options = [];
            }

            // Normalizar answer según tipo
            $answerRaw = $ans?->answer;
            if (($q?->type) === 'select') {
                // En BD guardaste el label; el front usa value=index
                $idx = null;
                if ($answerRaw !== null && $answerRaw !== '') {
                    $found = array_search($answerRaw, $options, true);
                    if ($found !== false) {
                        $idx = (string) $found;
                    }
                }
                $answer = $idx ?? '';
            } elseif (($q?->type) === 'multiple') {
                // Decodificar si es string JSON
                if (is_string($answerRaw)) {
                    $decoded = json_decode($answerRaw, true);
                    $answerRaw = is_array($decoded) ? $decoded : [];
                }

                // Si no es un array, retornar array vacío
                if (! is_array($answerRaw)) {
                    $answer = [];
                } else {
                    // Convertir textos de opciones a índices numéricos
                    $selectedIndices = [];
                    foreach ($answerRaw as $item) {
                        if (is_numeric($item) && isset($options[$item])) {
                            // Ya es un índice numérico válido
                            $selectedIndices[] = (int) $item;
                        } else {
                            // Es un texto, buscar su índice en las opciones
                            $key = array_search($item, $options, true);
                            if ($key !== false) {
                                $selectedIndices[] = (int) $key;
                            }
                        }
                    }
                    $answer = $selectedIndices;
                }
            } else {
                $answer = $answerRaw;
            }

            return array_merge([
                'question_id' => $q?->id,
                'slug' => $slug,
                'text' => $q?->text ?? \Illuminate\Support\Str::headline(str_replace('_', ' ', $slug)),
                'type' => $q?->type ?? 'string',
                'options' => $options,  // siempre array
                'answer' => $answer,   // índice(string) para select, array para multiple
            ], $extraMeta[$slug] ?? []);
        })->values();
    }

    /**
     * Obtener todos los estados OPx disponibles (sustituye el antiguo flujo de estados).
     */
    public function getFlujosDisponibles(Contratacion $contratacion)
    {
        try {
            $estados = EstadoContratacion::orderBy('grupo')->orderBy('codigo')->get();

            $estadosFormateados = $estados->map(fn ($e) => [
                'id' => $e->id,
                'codigo' => $e->codigo,
                'grupo' => $e->grupo,
            ]);

            return response()->json([
                'success' => true,
                'estados' => $estadosFormateados,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estados OPx: '.$e->getMessage(), [
                'contratacion_id' => $contratacion->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los estados disponibles',
            ], 500);
        }
    }

    /**
     * Actualizar estados OPx de una contratación (sustituye el antiguo updateStatus por estado legacy).
     */
    public function updateEstadosOPx(Request $request, Contratacion $contratacion): JsonResponse
    {
        try {
            $data = $request->validate([
                'codigos' => 'required|array',
                'codigos.*' => 'string|exists:estados_contratacion,codigo',
                'replace' => 'sometimes|boolean',
            ]);

            $codigos = array_values(array_filter(array_map('trim', $data['codigos'])));
            $replace = $data['replace'] ?? true;

            $resultado = $this->contratacionEstadoService->cambiarEstadosOPx(
                $contratacion,
                $codigos,
                $replace
            );

            $contratacion->load('estadosContratacion');
            $estados_opx = $contratacion->estadosContratacion->pluck('codigo')->values()->all();

            return response()->json([
                'success' => true,
                'estados_opx' => $estados_opx,
                'codigos' => $resultado['codigos'],
            ], 200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos no válidos',
                'errors' => $e->errors(),
            ], 422, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('Error en updateEstadosOPx: '.$e->getMessage(), [
                'contratacion_id' => $contratacion->id,
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar los estados OPx',
            ], 500, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Aplicar una transición a una contratación
     */
    public function aplicarTransicion(Request $request, Contratacion $contratacion)
    {
        try {
            $request->validate([
                'transicion_id' => 'required|exists:transiciones,id',
                // Para rechazo (estado cierre + fase rechazada)
                'rechazo.motivo_ids' => 'nullable|array',
                'rechazo.motivo_ids.*' => 'integer|exists:motivos_rechazo,id',
                'rechazo.descripcion' => 'nullable|string|max:2000',
            ]);

            $transicion = Transicion::findOrFail($request->transicion_id);

            // Verificar que la transición es válida para esta contratación
            $esValida = Transicion::esTransicionValida(
                $contratacion->estado,
                $contratacion->fase,
                $transicion->estado_destino,
                $transicion->fase_destino
            );

            if (! $esValida) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta transición no es válida para el estado actual',
                ], 400);
            }

            // Guardar el estado anterior para poder deshacer
            $estadoAnterior = $contratacion->estado;
            $faseAnterior = $contratacion->fase;

            // Obtener el nombre de la fase anterior
            $faseAnteriorNombre = null;
            if ($faseAnterior) {
                $faseAnteriorObj = \App\Models\Fase::where('slug', $faseAnterior)->first();
                $faseAnteriorNombre = $faseAnteriorObj ? $faseAnteriorObj->nombre : null;
            }

            // Si destino es cierre/rechazada, requerir motivos/descripcion y guardar registro
            if ($transicion->estado_destino === 'cierre' && $transicion->fase_destino === 'rechazada') {
                $motivoIds = collect($request->input('rechazo.motivo_ids', []))
                    ->filter(fn ($id) => ! is_null($id))
                    ->values();
                $descripcionRechazo = $request->input('rechazo.descripcion');

                if ($motivoIds->isEmpty() && empty($descripcionRechazo)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debe seleccionar al menos un motivo de rechazo o escribir una descripción.',
                    ], 422);
                }

                // Crear registro de rechazo guardando motivo_ids como JSON
                DB::table('rechazos_contrataciones')->insert([
                    'contratacion_id' => $contratacion->id,
                    'motivo_ids' => $motivoIds->isNotEmpty() ? json_encode($motivoIds->values()->all()) : null,
                    'descripcion' => $descripcionRechazo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Sincronizar estados OPx según destino de la transición
            $codigosOPx = $this->mapEstadoFaseToCodigosOPx($transicion->estado_destino, $transicion->fase_destino);
            $this->contratacionEstadoService->cambiarEstadosOPx($contratacion, $codigosOPx, true);

            // Mantener estado/fase en el modelo por compatibilidad
            $contratacion->estado = $transicion->estado_destino;
            $contratacion->fase = $transicion->fase_destino ?? $contratacion->fase;
            $contratacion->save();

            $contratacion->load('estadosContratacion');
            $estados_opx = $contratacion->estadosContratacion->pluck('codigo')->values()->all();

            // Disparar eventos cuando se cierra la contratación
            if ($transicion->estado_destino === 'cierre' && $transicion->fase_destino === 'rechazada') {
                event(new EventContratacionCierreRechazada($contratacion));
            } elseif ($transicion->estado_destino === 'cierre' && $transicion->fase_destino === 'resolucion') {
                event(new EventContratacionCierreResolucion($contratacion));
            }

            // Registrar en el historial (incluye OPx)
            $descripcion = $transicion->descripcion ?: 'Transición aplicada';
            $descripcion .= ' - OPx: '.implode(', ', $codigosOPx);

            \App\Models\HistorialActividad::create([
                'contratacion_id' => $contratacion->id,
                'actividad' => $descripcion,
                'fecha_inicio' => now(),
                'observaciones' => 'Transición aplicada desde el sistema de flujos',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transición aplicada correctamente',
                'estados_opx' => $estados_opx,
                'estado' => $contratacion->estado,
                'fase' => $contratacion->fase,
                'estado_anterior' => $estadoAnterior,
                'fase_anterior' => $faseAnterior,
                'fase_anterior_nombre' => $faseAnteriorNombre,
                'transicion_id' => $transicion->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al aplicar transición: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al aplicar la transición',
            ], 500);
        }
    }

    /**
     * Obtiene los documentos configurados como visibles para una contratación
     */
    public function getDocumentosConfigurados(Contratacion $contratacion): JsonResponse
    {
        try {
            // Log para debug
            Log::info('Debug getDocumentosConfigurados', [
                'auth_id' => Auth::id(),
                'contratacion_user_id' => $contratacion->user_id,
                'contratacion_id' => $contratacion->id,
                'is_admin' => Auth::user()->is_admin ?? false,
                'user_email' => Auth::user()->email ?? 'N/A',
            ]);

            // Verificar que el usuario autenticado tenga acceso a esta contratación
            // Permitir acceso si es admin o si es el propietario
            $user = Auth::user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado',
                ], 401);
            }

            if (! $user->is_admin && $user->id !== $contratacion->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta contratación',
                ], 403);
            }

            $documentosVisibles = DocumentoConfiguracion::getDocumentosVisibles($contratacion->id);

            return response()->json([
                'success' => true,
                'documentos' => $documentosVisibles->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'name' => $doc->name,
                        'slug' => $doc->slug,
                        'multi_upload' => $doc->multi_upload ?? false,
                    ];
                }),
            ]);

            $documentosVisibles = \App\Models\DocumentoConfiguracion::getDocumentosVisibles($contratacion->id);

            return response()->json([
                'success' => true,
                'documentos' => $documentosVisibles->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'name' => $doc->name,
                        'slug' => $doc->slug,
                        'multi_upload' => $doc->multi_upload ?? false,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener documentos configurados', [
                'contratacion_id' => $contratacion->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Configura los documentos visibles para una contratación
     */
    public function configurarDocumentos(Request $request, Contratacion $contratacion): JsonResponse
    {
        try {
            // Verificar que el usuario autenticado tenga acceso a esta contratación
            $user = Auth::user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado',
                ], 401);
            }

            if (! $user->is_admin && $user->id !== $contratacion->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta contratación',
                ], 403);
            }

            $request->validate([
                'document_ids' => 'required|array',
                'document_ids.*' => 'integer|exists:documents,id',
            ]);

            DocumentoConfiguracion::configurarDocumentos(
                $contratacion->id,
                $request->document_ids
            );

            return response()->json([
                'success' => true,
                'message' => 'Configuración de documentos guardada correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al configurar documentos', [
                'contratacion_id' => $contratacion->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Restablecer configuración de documentos (borrar todos los registros)
     */
    public function restablecerConfiguracionDocumentos(Contratacion $contratacion): JsonResponse
    {
        try {
            // Log para debug
            Log::info('Debug restablecerConfiguracionDocumentos', [
                'auth_id' => Auth::id(),
                'contratacion_user_id' => $contratacion->user_id,
                'contratacion_id' => $contratacion->id,
                'is_admin' => Auth::user()->is_admin ?? false,
                'user_email' => Auth::user()->email ?? 'N/A',
            ]);

            // Verificar que el usuario autenticado tenga acceso a esta contratación
            $user = Auth::user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado',
                ], 401);
            }

            if (! $user->is_admin && $user->id !== $contratacion->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta contratación',
                ], 403);
            }

            // Borrar todos los registros de documento_configuraciones para esta contratación
            $deletedCount = DocumentoConfiguracion::where('contratacion_id', $contratacion->id)->delete();

            Log::info('Debug restablecerConfiguracionDocumentos - registros eliminados', [
                'contratacion_id' => $contratacion->id,
                'deleted_count' => $deletedCount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuración restablecida correctamente',
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al restablecer configuración de documentos', [
                'contratacion_id' => $contratacion->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al restablecer configuración: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener datos actualizados de una contratación para actualizar el modal
     */
    public function getDatosActualizados(Contratacion $contratacion)
    {
        try {
            // Cargar la contratación con todas las relaciones necesarias
            $contratacion->load([
                'user.answers.question',
                'user.userDocuments.document',
                'ayuda',
            ]);

            $solicitanteDatos = $this->obtenerDatosSolicitante($contratacion);

            $contratoDatos = $this->obtenerDatosContrato($contratacion);

            $direccionDatos = $this->obtenerDatosDireccion($contratacion);

            $hijoDatos = $this->obtenerDatosHijos($contratacion);

            $convivienteDatos = $this->obtenerDatosConvivientes($contratacion);

            $arrendadorDatos = $this->obtenerDatosArrendadores($contratacion);

            return response()->json([
                'success' => true,
                'solicitanteDatos' => $solicitanteDatos,
                'contratoDatos' => $contratoDatos,
                'direccionDatos' => $direccionDatos,
                'hijoDatos' => $hijoDatos,
                'convivienteDatos' => $convivienteDatos,
                'arrendadorDatos' => $arrendadorDatos,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener datos actualizados: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos actualizados',
            ], 500);
        }
    }

    private function obtenerDatosSolicitante($contratacion)
    {
        // Obtener respuestas del usuario que no son de convivientes ni arrendadores
        $answers = $contratacion->user->answers()
            ->whereNull('conviviente_id')
            ->whereNull('arrendador_id')
            ->with('question')
            ->get();

        return $answers->map(function ($answer) {
            return [
                'slug' => $answer->question->slug,
                'text' => $answer->question->text,
                'type' => $answer->question->type,
                'options' => $answer->question->options,
                'answer' => $answer->answer,
            ];
        })->toArray();
    }

    private function obtenerDatosContrato($contratacion)
    {
        // Obtener respuestas relacionadas con contrato
        $answers = $contratacion->user->answers()
            ->whereHas('question', function ($query) {
                $query->where('slug', 'like', '%contrato%');
            })
            ->whereNull('conviviente_id')
            ->whereNull('arrendador_id')
            ->with('question')
            ->get();

        return $answers->map(function ($answer) {
            return [
                'slug' => $answer->question->slug,
                'text' => $answer->question->text,
                'type' => $answer->question->type,
                'options' => $answer->question->options,
                'answer' => $answer->answer,
            ];
        })->toArray();
    }

    private function obtenerDatosDireccion($contratacion)
    {
        // Obtener respuestas relacionadas con dirección
        $answers = $contratacion->user->answers()
            ->whereHas('question', function ($query) {
                $query->where('slug', 'like', '%direccion%')
                    ->orWhere('slug', 'like', '%domicilio%')
                    ->orWhere('slug', 'like', '%residencia%');
            })
            ->whereNull('conviviente_id')
            ->whereNull('arrendador_id')
            ->with('question')
            ->get();

        return $answers->map(function ($answer) {
            return [
                'slug' => $answer->question->slug,
                'text' => $answer->question->text,
                'type' => $answer->question->type,
                'options' => $answer->question->options,
                'answer' => $answer->answer,
            ];
        })->toArray();
    }

    private function obtenerDatosHijos($contratacion)
    {
        // Obtener respuestas relacionadas con hijos
        $answers = $contratacion->user->answers()
            ->whereHas('question', function ($query) {
                $query->where('slug', 'like', '%hijo%')
                    ->orWhere('slug', 'like', '%menor%');
            })
            ->whereNull('conviviente_id')
            ->whereNull('arrendador_id')
            ->with('question')
            ->get();

        return $answers->map(function ($answer) {
            return [
                'slug' => $answer->question->slug,
                'text' => $answer->question->text,
                'type' => $answer->question->type,
                'options' => $answer->question->options,
                'answer' => $answer->answer,
            ];
        })->toArray();
    }

    private function obtenerDatosConvivientes($contratacion)
    {
        // Obtener datos de convivientes
        $convivientes = \App\Models\Conviviente::where('user_id', $contratacion->user_id)
            ->with(['answers.question'])
            ->get();

        return $convivientes->map(function ($conviviente) {
            $answers = $conviviente->answers->map(function ($answer) {
                return [
                    'slug' => $answer->question->slug,
                    'text' => $answer->question->text,
                    'type' => $answer->question->type,
                    'options' => $answer->question->options,
                    'answer' => $answer->answer,
                ];
            })->toArray();

            return [
                'conviviente_id' => $conviviente->id,
                'datos' => $answers,
            ];
        })->toArray();
    }

    private function obtenerDatosArrendadores($contratacion)
    {
        // Obtener datos de arrendadores
        $arrendadores = \App\Models\Arrendatario::where('user_id', $contratacion->user_id)
            ->with(['answers.question'])
            ->get();

        return $arrendadores->map(function ($arrendador) {
            $answers = $arrendador->answers->map(function ($answer) {
                return [
                    'slug' => $answer->question->slug,
                    'text' => $answer->question->text,
                    'type' => $answer->question->type,
                    'options' => $answer->question->options,
                    'answer' => $answer->answer,
                ];
            })->toArray();

            return [
                'arrendador_id' => $arrendador->id,
                'datos' => $answers,
            ];
        })->toArray();
    }

    /**
     * Aplica los filtros universales dinámicos a la consulta
     */
    private function aplicarFiltrosUniversales($query, Request $request)
    {
        $filtros = $request->input('filtros', []);

        if (empty($filtros)) {
            return;
        }

        foreach ($filtros as $filtro) {
            if (empty($filtro['campo']) || empty($filtro['operador']) || empty($filtro['valor'])) {
                continue;
            }

            $campo = $filtro['campo'];
            $operador = $filtro['operador'];
            $valor = $filtro['valor'];

            switch ($campo) {
                case 'ccaa_id':
                    $this->aplicarFiltroCCAA($query, $operador, $valor);
                    break;
                case 'ayuda_id':
                    $this->aplicarFiltroAyuda($query, $operador, $valor);
                    break;
            }
        }
    }

    /**
     * Aplica filtro de Comunidad Autónoma
     */
    private function aplicarFiltroCCAA($query, $operador, $valor)
    {
        switch ($operador) {
            case 'igual_a':
                $query->whereHas('ayuda', fn ($q) => $q->where('ccaa_id', $valor));
                break;
            case 'diferente_de':
                $query->whereHas('ayuda', fn ($q) => $q->where('ccaa_id', '!=', $valor));
                break;
        }
    }

    /**
     * Aplica filtro de Ayuda
     */
    private function aplicarFiltroAyuda($query, $operador, $valor)
    {
        switch ($operador) {
            case 'igual_a':
                $query->where('ayuda_id', $valor);
                break;
            case 'diferente_de':
                $query->where('ayuda_id', '!=', $valor);
                break;
        }
    }

    /**
     * Mapea estado/fase (legacy) a códigos OPx para sincronizar con estados_contratacion.
     *
     * @return array<string>
     */
    private function mapEstadoFaseToCodigosOPx(string $estadoDestino, ?string $faseDestino): array
    {
        if ($estadoDestino === 'documentacion') {
            return ['OP1-Documentacion'];
        }
        if ($estadoDestino === 'tramitacion') {
            return ['OP1-Tramitacion'];
        }
        if ($estadoDestino === 'cierre') {
            if ($faseDestino === 'rechazada') {
                return ['OP5-Rechazado'];
            }
            if ($faseDestino === 'resolucion') {
                return ['OP1-Resolucion'];
            }

            return ['OP1-Cierre'];
        }

        return ['OP1-Documentacion'];
    }
}
