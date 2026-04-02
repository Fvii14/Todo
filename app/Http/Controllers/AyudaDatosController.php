<?php

namespace App\Http\Controllers;

use App\Models\Ayuda;
use App\Models\AyudaDato;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AyudaDatosController extends Controller
{
    /** Muestra el formulario */
    public function create()
    {
        $ayudas = Ayuda::pluck('nombre_ayuda', 'id');

        $questions = Question::select('id', 'slug', 'text', 'type', 'options')->get();

        $questionAssociations = DB::table('questionnaire_questions as qq')
            ->join('questionnaires as q', 'qq.questionnaire_id', '=', 'q.id')
            ->join('questions as qu', 'qq.question_id', '=', 'qu.id')
            ->whereNotNull('q.ayuda_id')
            ->select('qu.slug', 'q.ayuda_id')
            ->get()
            ->groupBy('ayuda_id')
            ->map(function ($group) {
                return $group->pluck('slug')->toArray();
            });

        $questionsWithAssociation = $questions->map(function ($question) use ($questionAssociations) {
            $questionArray = $question->toArray();
            $questionArray['is_associated'] = false;
            $questionArray['associated_ayudas'] = [];

            foreach ($questionAssociations as $ayudaId => $questionSlugs) {
                if (in_array($questionArray['slug'], $questionSlugs)) {
                    $questionArray['is_associated'] = true;
                    $questionArray['associated_ayudas'][] = (int) $ayudaId;
                }
            }

            return $questionArray;
        });

        return view('admin.ayuda_datos_form', compact('ayudas', 'questionsWithAssociation'));
    }

    /** Valida y guarda los datos enviados */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'ayuda_id' => 'required|exists:ayudas,id',
                'datos' => 'required|array|min:1',
                'datos.*.question_slug' => 'required|exists:questions,slug',
                'datos.*.tipo_dato' => 'required|in:solicitante,hijo,contrato,conviviente,arrendador,direccion',

                'datos.*.condiciones' => 'array',
                'datos.*.condiciones.*.question_slug' => 'required_with:datos.*.condiciones|string',
                'datos.*.condiciones.*.operador' => 'required_with:datos.*.condiciones|string',
                'datos.*.condiciones.*.valor' => 'required_with:datos.*.condiciones',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        // Normalizar: asegurar que todos los datos tienen la clave 'condiciones'
        foreach ($data['datos'] as $k => $dato) {
            if (! isset($dato['condiciones'])) {
                $data['datos'][$k]['condiciones'] = [];
            }
        }

        try {
            DB::transaction(function () use ($data) {
                // Obtener los tipos de dato que se están enviando
                $tiposEnviados = collect($data['datos'])->pluck('tipo_dato')->unique();

                // Obtener datos existentes para comparar
                $datosExistentes = AyudaDato::where('ayuda_id', $data['ayuda_id'])
                    ->whereIn('tipo_dato', $tiposEnviados)
                    ->with('condiciones')
                    ->get()
                    ->keyBy(function ($item) {
                        return $item->question_slug.'_'.$item->tipo_dato;
                    });

                foreach ($data['datos'] as $d) {
                    $questionSlug = $d['question_slug'];
                    $claveBusqueda = $questionSlug.'_'.$d['tipo_dato'];
                    $datoExistente = $datosExistentes->get($claveBusqueda);

                    if ($datoExistente) {
                        // Actualizar dato existente si es necesario
                        $datoExistente->update([
                            'tipo_dato' => $d['tipo_dato'],
                        ]);

                        // Eliminar condiciones existentes para recrearlas
                        $datoExistente->condiciones()->delete();
                    } else {
                        // Crear nuevo dato
                        $datoExistente = AyudaDato::create([
                            'ayuda_id' => $data['ayuda_id'],
                            'question_slug' => $questionSlug,
                            'tipo_dato' => $d['tipo_dato'],
                        ]);
                    }

                    // Guardar condiciones si existen
                    $condiciones = isset($d['condiciones']) ? $d['condiciones'] : [];
                    if (! empty($condiciones) && is_array($condiciones)) {
                        foreach ($condiciones as $cond) {
                            if (! empty($cond['question_slug']) && isset($cond['operador']) && isset($cond['valor'])) {
                                // Normalizar operador: si es '==', guardar '='
                                $operador = $cond['operador'] === '==' ? '=' : $cond['operador'];
                                // Si el valor es array (multiple), lo guardamos como JSON
                                $valor = is_array($cond['valor']) ? json_encode($cond['valor']) : $cond['valor'];
                                $datoExistente->condiciones()->create([
                                    'question_slug' => $cond['question_slug'],
                                    'operador' => $operador,
                                    'valor' => $valor,
                                ]);
                            }
                        }
                    }
                }

                // Eliminar datos que ya no están en la lista enviada
                $questionSlugsEnviados = collect($data['datos'])->pluck('question_slug')->toArray();
                AyudaDato::where('ayuda_id', $data['ayuda_id'])
                    ->whereIn('tipo_dato', $tiposEnviados)
                    ->whereNotIn('question_slug', $questionSlugsEnviados)
                    ->delete();
            });
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar los datos: '.$e->getMessage(),
                ], 500);
            }
            throw $e;
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Datos de la ayuda actualizados correctamente.',
            ]);
        }

        return redirect()
            ->route('ayuda_datos.create')
            ->with('success', 'Datos de la ayuda actualizados.');
    }

    public function datos(Request $request, Ayuda $ayuda)
    {
        $query = AyudaDato::with(['question', 'condiciones'])
            ->where('ayuda_id', $ayuda->id);

        $datos = $query->get()
            ->map(fn ($d) => [
                'question_slug' => $d->question_slug,
                'question_text' => $d->question->text,
                'tipo_dato' => $d->tipo_dato,
                'condiciones' => $d->condiciones->map(function ($c) {
                    return [
                        'question_slug' => $c->question_slug,
                        'operador' => $c->operador,
                        'valor' => json_decode($c->valor, true) ?? $c->valor,
                    ];
                })->toArray(),
            ]);

        return response()->json($datos);
    }

    /**
     * Copiar ayuda_datos de una ayuda a otras
     */
    public function copiarAyudaDatos(Request $request)
    {
        $request->validate([
            'ayuda_origen_id' => 'required|exists:ayudas,id',
            'ayudas_destino_ids' => 'required|array|min:1',
            'ayudas_destino_ids.*' => 'exists:ayudas,id',
            'sobrescribir' => 'boolean',
            'filtros' => 'nullable|array',
            'filtros.tipo_dato' => 'nullable|string',
            'filtros.fase' => 'nullable|string',
        ]);

        $ayudaOrigenId = $request->ayuda_origen_id;
        $ayudasDestinoIds = $request->ayudas_destino_ids;
        $sobrescribir = $request->boolean('sobrescribir', false);
        $filtros = $request->filtros ?? [];

        // Construir query para obtener datos de la ayuda origen
        $query = AyudaDato::where('ayuda_id', $ayudaOrigenId)
            ->with(['question', 'condiciones']);
        if (! empty($filtros['tipo_dato'])) {
            $query->where('tipo_dato', $filtros['tipo_dato']);
        }
        if (! empty($filtros['fase'])) {
            $query->where('fase', $filtros['fase']);
        }

        $datosOrigen = $query->get();

        if ($datosOrigen->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron datos de ayuda que coincidan con los filtros especificados',
            ], 400);
        }

        $resultados = [];
        $errores = [];

        foreach ($ayudasDestinoIds as $ayudaDestinoId) {
            $datosCreados = 0;
            $datosExistentes = 0;
            $erroresAyuda = [];

            foreach ($datosOrigen as $datoOrigen) {
                try {
                    // Verificar si ya existe el dato en la ayuda destino
                    $existente = AyudaDato::where('ayuda_id', $ayudaDestinoId)
                        ->where('question_slug', $datoOrigen->question_slug)
                        ->where('tipo_dato', $datoOrigen->tipo_dato)
                        ->where('fase', $datoOrigen->fase)
                        ->first();

                    if ($existente) {
                        if ($sobrescribir) {
                            // Eliminar el existente y sus condiciones
                            $existente->condiciones()->delete();
                            $existente->delete();
                        } else {
                            $datosExistentes++;

                            continue;
                        }
                    }

                    // Crear el nuevo dato
                    $nuevoDato = AyudaDato::create([
                        'ayuda_id' => $ayudaDestinoId,
                        'question_slug' => $datoOrigen->question_slug,
                        'tipo_dato' => $datoOrigen->tipo_dato,
                        'fase' => $datoOrigen->fase,
                    ]);

                    // Copiar las condiciones si existen
                    foreach ($datoOrigen->condiciones as $condicion) {
                        $nuevoDato->condiciones()->create([
                            'question_slug' => $condicion->question_slug,
                            'operador' => $condicion->operador,
                            'valor' => $condicion->valor,
                        ]);
                    }

                    $datosCreados++;

                } catch (\Exception $e) {
                    $erroresAyuda[] = "Error al copiar dato '{$datoOrigen->question_slug}': ".$e->getMessage();
                }
            }

            $resultados[] = [
                'ayuda_id' => $ayudaDestinoId,
                'datos_creados' => $datosCreados,
                'datos_existentes' => $datosExistentes,
                'errores' => $erroresAyuda,
            ];

            if (! empty($erroresAyuda)) {
                $errores = array_merge($errores, $erroresAyuda);
            }
        }

        $totalCreados = array_sum(array_column($resultados, 'datos_creados'));
        $totalExistentes = array_sum(array_column($resultados, 'datos_existentes'));

        return response()->json([
            'success' => true,
            'message' => "Copia completada. {$totalCreados} datos creados, {$totalExistentes} ya existían.",
            'resultados' => $resultados,
            'errores' => $errores,
        ]);
    }

    /**
     * Obtener datos de ayuda para vista previa
     */
    public function getDatosParaVistaPrevia(Request $request)
    {
        $request->validate([
            'ayuda_id' => 'required|exists:ayudas,id',
            'filtros' => 'nullable|array',
        ]);

        $ayudaId = $request->ayuda_id;
        $filtros = $request->filtros ?? [];

        $query = AyudaDato::where('ayuda_id', $ayudaId)
            ->with(['question', 'condiciones']);

        // Aplicar filtros
        if (! empty($filtros['tipo_dato'])) {
            $query->where('tipo_dato', $filtros['tipo_dato']);
        }
        if (! empty($filtros['fase'])) {
            $query->where('fase', $filtros['fase']);
        }

        $datos = $query->get();

        return response()->json([
            'success' => true,
            'datos' => $datos->map(function ($dato) {
                return [
                    'id' => $dato->id,
                    'question_slug' => $dato->question_slug,
                    'question_text' => $dato->question->text ?? $dato->question_slug,
                    'tipo_dato' => $dato->tipo_dato,
                    'fase' => $dato->fase,
                    'condiciones_count' => $dato->condiciones->count(),
                ];
            }),
        ]);
    }

    /**
     * Obtener opciones de filtros disponibles
     */
    public function getOpcionesFiltros(Request $request)
    {
        $request->validate([
            'ayuda_id' => 'required|exists:ayudas,id',
        ]);

        $ayudaId = $request->ayuda_id;

        $opciones = [
            'tipos_dato' => AyudaDato::where('ayuda_id', $ayudaId)
                ->distinct()
                ->pluck('tipo_dato'),
            'fases' => AyudaDato::where('ayuda_id', $ayudaId)
                ->whereNotNull('fase')
                ->distinct()
                ->pluck('fase'),
        ];

        return response()->json([
            'success' => true,
            'opciones' => $opciones,
        ]);
    }

    /**
     * Obtener datos iniciales para el modal de configuración
     */
    public function getDatosIniciales()
    {
        try {
            $ayudas = Ayuda::pluck('nombre_ayuda', 'id');

            $questions = Question::select('id', 'slug', 'text', 'type', 'options')->get();

            $questionAssociations = DB::table('questionnaire_questions as qq')
                ->join('questionnaires as q', 'qq.questionnaire_id', '=', 'q.id')
                ->join('questions as qu', 'qq.question_id', '=', 'qu.id')
                ->whereNotNull('q.ayuda_id')
                ->select('qu.slug', 'q.ayuda_id')
                ->get()
                ->groupBy('ayuda_id')
                ->map(function ($group) {
                    return $group->pluck('slug')->toArray();
                });

            $questionsWithAssociation = $questions->map(function ($question) use ($questionAssociations) {
                $questionArray = $question->toArray();
                $questionArray['is_associated'] = false;
                $questionArray['associated_ayudas'] = [];

                foreach ($questionAssociations as $ayudaId => $questionSlugs) {
                    if (in_array($questionArray['slug'], $questionSlugs)) {
                        $questionArray['is_associated'] = true;
                        $questionArray['associated_ayudas'][] = (int) $ayudaId;
                    }
                }

                return $questionArray;
            });

            return response()->json([
                'success' => true,
                'ayudas' => $ayudas,
                'questions' => $questionsWithAssociation,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cargar datos iniciales para configuración de ayuda: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los datos iniciales',
            ], 500);
        }
    }
}
