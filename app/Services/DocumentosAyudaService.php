<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Ayuda;
use App\Models\AyudaDocumento;
use App\Models\AyudaDocumentoConviviente;
use App\Models\Contratacion;
use App\Models\DocumentoConfiguracion;
use App\Models\Question;
use App\Models\User;
use App\Models\UserDocument;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentosAyudaService
{
    /**
     * Obtiene toda la información de documentos de una ayuda solicitada.
     *
     * @param  \App\Models\AyudaSolicitada  $ayudaSolicitada
     */
    public function obtenerDocumentosAyuda(int $userId, $contratacion, array $answers, ?string $sectorAyuda = null, $userDocuments = null): array
    {
        // Documentos subidos por el usuario
        if ($userDocuments === null) {
            $userDocuments = UserDocument::where('user_id', $userId)->get();
        }

        // Slugs de documentos subidos
        $documentosSubidos = $userDocuments->pluck('slug')->toArray();

        // Documentos subidos pendientes de validación
        $documentosSubidosPendientes = $userDocuments
            ->whereIn('estado', ['pendiente', 'rechazado'])
            ->whereNull('conviviente_index')
            ->keyBy('slug')
            ->map(function ($doc) {
                return is_object($doc) && method_exists($doc, 'toArray') ? $doc->toArray() : (array) $doc;
            })
            ->toArray();

        $documentosFaltantes = $this->obtenerDocumentosFaltantes(
            $contratacion,
            $answers,
            $documentosSubidos,
            $sectorAyuda
        );

        $contratacionId = $contratacion->id;

        $recibosSubidos = $userDocuments
            ->where('slug', 'like', 'recibo_%')
            ->keyBy('slug')
            ->map(function ($doc) {
                return is_object($doc) && method_exists($doc, 'toArray') ? $doc->toArray() : (array) $doc;
            });

        // Documentos configurados como visibles
        $documentosConfigurados = DocumentoConfiguracion::getDocumentosVisibles($contratacionId)
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'slug' => $doc->slug,
                    'multi_upload' => $doc->multi_upload ?? false,
                    'informative_clickable_text' => $doc->informative_clickable_text ?? null,
                    'informative_header_text' => $doc->informative_header_text ?? null,
                    'informative_link' => $doc->informative_link ?? null,
                    'informative_link_text' => $doc->informative_link_text ?? null,
                ];
            })->toArray();

        return [
            'user_documents' => $userDocuments,
            'documentos_subidos' => $documentosSubidosPendientes,
            'documentos_faltantes' => $documentosFaltantes,
            'recibos_subidos' => $recibosSubidos,
            'documentos_configurados' => $documentosConfigurados,
        ];
    }

    /**
     * Placeholder para el método obtenerDocumentosNoValidados
     * Puedes mover la implementación original aquí.
     */
    protected function obtenerDocumentosNoValidados($userId): array
    {
        return UserDocument::where('user_id', $userId)
            ->whereIn('estado', ['pendiente', 'rechazado']) // solo los que aún no han sido validados
            ->whereNull('conviviente_index') // solo del solicitante
            ->get()                                         // obtenemos la colección
            ->keyBy('slug')                                 // usamos el slug como clave
            ->map(function ($doc) {                         // mapeamos cada documento...
                return is_object($doc) && method_exists($doc, 'toArray')
                    ? $doc->toArray()                       // ...a un array
                    : (array) $doc;                          // por si acaso no es modelo
            })
            ->toArray();                                    // convertimos la colección final a array
    }

    /**
     * Obtiene los documentos faltantes para una ayuda solicitada.
     * Devuelve un array con los documentos faltantes.
     *
     * @param  Contratacion  $contratacion
     * @param  array  $answers
     * @param  array  $documentosSubidos
     * @param  string  $sector_ayuda
     * @return collection
     */
    public function obtenerDocumentosFaltantes($contratacion, $answers, $documentosSubidos, $sector_ayuda)
    {
        $documentosFaltantes = collect();
        $userId = $contratacion->user_id;

        $ayudaId = $contratacion->ayuda_id;

        // Asegurar que la relación ayuda esté cargada
        if (! $contratacion->relationLoaded('ayuda')) {
            $contratacion->load('ayuda');
        }

        $ayuda = $contratacion->ayuda;

        // Comprobar si debe ignorar documentos por no tener contrato
        $ignorarDocumentos = false;
        if ($sector_ayuda === 'vivienda') {
            $respuestaContrato = $answers[1] ?? '';
            if (is_string($respuestaContrato) && trim($respuestaContrato) === 'Todavía no tengo contrato de alquiler firmado.') {
                $ignorarDocumentos = true;
            }
        }

        // Obtener slugs de documentos condicionales especiales
        $slugsEspeciales = $this->obtenerSlugsDocumentosEspecialesCondicionales($ayudaId, $userId);

        // Obtener documentos (obligatorios + opcionales con condiciones cumplidas)
        $documentos = AyudaDocumento::with('documento')
            ->where('ayuda_id', $ayudaId)
            ->get();

        $documentos = $documentos->filter(function ($docRel) use ($slugsEspeciales, $answers) {
            if (! $docRel->documento) {
                return false;
            }

            // Filtrar por tipo de documento
            $tipoValido = $docRel->documento->tipo === 'general' ||
                ($docRel->documento->tipo === 'especial' && in_array($docRel->documento->slug, $slugsEspeciales));

            if (! $tipoValido) {
                return false;
            }

            // Si es obligatorio, siempre incluirlo
            if ($docRel->es_obligatorio) {
                return true;
            }

            // Si es opcional, evaluar requisitos (condiciones)
            if (! empty($docRel->conditions)) {
                // Estructura nueva: { condition: 'AND', requirements: [...] }
                if (is_array($docRel->conditions) && isset($docRel->conditions['condition']) && isset($docRel->conditions['requirements'])) {
                    $logic = $docRel->conditions['condition'] ?? 'AND';
                    $requirements = $docRel->conditions['requirements'];

                    return $this->evaluarRequisitosDocumento($requirements, $answers, $logic);
                }
                // Estructura antigua (legacy): array directo de condiciones
                elseif (is_array($docRel->conditions) && ! isset($docRel->conditions['condition'])) {
                    $logic = $docRel->conditions_logic ?? 'AND';

                    return $this->evaluarRequisitosDocumento($docRel->conditions, $answers, $logic);
                }
            }

            // Si es opcional sin condiciones, no incluirlo
            return false;
        });

        // Recibos mensuales (solo si aplica)
        if (! $ignorarDocumentos && $contratacion->ayuda->fecha_inicio_periodo && $contratacion->ayuda->fecha_fin_periodo && $sector_ayuda === 'vivienda') {
            $documentosRecibos = $this->generarDocumentosRecibos($contratacion->ayuda)
                ->filter(function ($doc) use ($documentosSubidos) {
                    return ! in_array($doc->slug, $documentosSubidos);
                });

            $documentosFaltantes = $documentosFaltantes->merge($documentosRecibos);
        }

        // Agregar documentos no subidos
        foreach ($documentos as $docRel) {
            $slug = $docRel->documento->slug ?? null;

            if (! $slug || in_array($slug, $documentosSubidos)) {
                continue;
            }

            if ($ignorarDocumentos && in_array($slug, ['contrato-alquiler', 'padron-colectivo', 'padron-historico'])) {
                continue;
            }

            $documentosFaltantes->push($docRel->documento);
        }

        return $documentosFaltantes->values()->map(function ($doc) {
            return is_object($doc) && method_exists($doc, 'toArray') ? $doc->toArray() : (array) $doc;
        });
    }

    /**
     * Evalúa si se cumplen los requisitos de un documento opcional
     * Soporta requisitos simples y grupos de requisitos
     *
     * @param  array  $requirements  Array de requisitos del documento (pueden ser simples o grupos)
     * @param  array  $answers  Respuestas del usuario (question_id => answer)
     * @param  string  $logic  Operador lógico entre requisitos: 'AND' u 'OR' (por defecto 'AND')
     * @return bool true si se cumplen los requisitos según el operador lógico, false en caso contrario
     */
    private function evaluarRequisitosDocumento(array $requirements, array $answers, string $logic = 'AND'): bool
    {
        if (empty($requirements)) {
            return false; // Sin requisitos, no mostrar
        }

        $results = [];

        foreach ($requirements as $index => $requirement) {
            $type = $requirement['type'] ?? 'simple';

            if ($type === 'simple') {
                // Requisito simple
                $cumple = $this->evaluarRequisitoSimple($requirement, $answers);
            } elseif ($type === 'group') {
                // Grupo de requisitos
                $groupLogic = $requirement['groupLogic'] ?? 'AND';
                $cumple = $this->evaluarGrupoRequisitos($requirement, $answers, $groupLogic);
            } else {
                // Formato antiguo (legacy): tratar como condición simple
                $cumple = $this->evaluarRequisitoSimple($requirement, $answers);
            }

            $results[] = $cumple;

            // Optimización: si es OR y un requisito se cumple, retornar true inmediatamente
            if ($logic === 'OR' && $cumple) {
                return true;
            }

            // Optimización: si es AND y un requisito no se cumple, retornar false inmediatamente
            if ($logic === 'AND' && ! $cumple) {
                return false;
            }
        }

        // Si llegamos aquí, evaluar según el operador lógico
        if ($logic === 'OR') {
            // OR: al menos un requisito debe cumplirse
            return in_array(true, $results, true);
        } else {
            // AND: todos los requisitos deben cumplirse
            return ! in_array(false, $results, true);
        }
    }

    /**
     * Evalúa un requisito simple
     *
     * @param  array  $requirement  Requisito simple
     * @param  array  $answers  Respuestas del usuario
     */
    private function evaluarRequisitoSimple(array $requirement, array $answers): bool
    {
        $questionId = $requirement['question_id'] ?? null;
        $operator = $requirement['operator'] ?? '==';
        $expectedValue = $requirement['value'] ?? null;

        if (! $questionId) {
            return false; // Requisito inválido
        }

        $userAnswer = $answers[$questionId] ?? null;

        // Decodificar JSON si es necesario
        if (is_string($userAnswer)) {
            $decoded = json_decode($userAnswer, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $userAnswer = $decoded;
            }
        }

        return $this->evaluarCondicion($userAnswer, $operator, $expectedValue);
    }

    /**
     * Evalúa un grupo de requisitos
     *
     * @param  array  $group  Grupo de requisitos
     * @param  array  $answers  Respuestas del usuario
     * @param  string  $groupLogic  Operador lógico del grupo: 'AND' u 'OR'
     */
    private function evaluarGrupoRequisitos(array $group, array $answers, string $groupLogic = 'AND'): bool
    {
        $rules = $group['rules'] ?? [];

        if (empty($rules)) {
            return false; // Grupo sin reglas
        }

        $results = [];

        foreach ($rules as $rule) {
            $questionId = $rule['question_id'] ?? null;
            $operator = $rule['operator'] ?? '==';
            $expectedValue = $rule['value'] ?? null;

            if (! $questionId) {
                continue; // Regla inválida, saltar
            }

            $userAnswer = $answers[$questionId] ?? null;

            // Decodificar JSON si es necesario
            if (is_string($userAnswer)) {
                $decoded = json_decode($userAnswer, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $userAnswer = $decoded;
                }
            }

            $cumple = $this->evaluarCondicion($userAnswer, $operator, $expectedValue);
            $results[] = $cumple;

            // Optimización para grupos
            if ($groupLogic === 'OR' && $cumple) {
                return true;
            }
            if ($groupLogic === 'AND' && ! $cumple) {
                return false;
            }
        }

        // Evaluar según el operador lógico del grupo
        if ($groupLogic === 'OR') {
            return in_array(true, $results, true);
        } else {
            return ! in_array(false, $results, true);
        }
    }

    /**
     * Evalúa una condición individual
     *
     * @param  mixed  $userAnswer  Respuesta del usuario
     * @param  string  $operator  Operador de comparación
     * @param  mixed  $expectedValue  Valor esperado
     */
    private function evaluarCondicion($userAnswer, string $operator, $expectedValue): bool
    {
        // Manejar arrays en comparaciones de igualdad
        if ($operator === '==' || $operator === '=') {
            if (is_array($userAnswer)) {
                // Si la respuesta es un array, verificar si contiene el valor esperado
                if (is_array($expectedValue)) {
                    // Si ambos son arrays, verificar intersección
                    $userArray = array_map('strval', $userAnswer);
                    $expectedArray = array_map('strval', $expectedValue);
                    $intersection = array_intersect($userArray, $expectedArray);

                    return ! empty($intersection);
                } else {
                    // Si la respuesta es array y el esperado es valor único
                    $userArray = array_map('strval', $userAnswer);
                    $expectedStr = (string) $expectedValue;

                    return in_array($expectedStr, $userArray, true);
                }
            } else {
                // Si la respuesta es valor único
                if (is_array($expectedValue)) {
                    // Si el esperado es array, verificar si contiene la respuesta
                    $userStr = (string) $userAnswer;
                    $expectedArray = array_map('strval', $expectedValue);

                    return in_array($userStr, $expectedArray, true);
                } else {
                    // Ambos son valores únicos
                    $userStr = (string) $userAnswer;
                    $expectedStr = (string) $expectedValue;

                    return $userStr === $expectedStr;
                }
            }
        }

        // Para otros operadores, normalizar valores
        $userValue = is_array($userAnswer) ? array_map('strval', $userAnswer) : (string) $userAnswer;
        $expected = is_array($expectedValue) ? array_map('strval', $expectedValue) : (string) $expectedValue;

        switch ($operator) {
            case '!=':
                if (is_array($userAnswer)) {
                    if (is_array($expectedValue)) {
                        $userArray = array_map('strval', $userAnswer);
                        $expectedArray = array_map('strval', $expectedValue);
                        $intersection = array_intersect($userArray, $expectedArray);

                        return empty($intersection);
                    } else {
                        $userArray = array_map('strval', $userAnswer);
                        $expectedStr = (string) $expectedValue;

                        return ! in_array($expectedStr, $userArray, true);
                    }
                } else {
                    if (is_array($expectedValue)) {
                        $userStr = (string) $userAnswer;
                        $expectedArray = array_map('strval', $expectedValue);

                        return ! in_array($userStr, $expectedArray, true);
                    } else {
                        return $userValue != $expected;
                    }
                }
            case '>':
            case '<':
            case '>=':
            case '<=':
                // Intentar parsear como fechas primero
                $userStr = is_array($userAnswer) ? (string) ($userAnswer[0] ?? '') : (string) $userAnswer;
                $expectedStr = is_array($expectedValue) ? (string) ($expectedValue[0] ?? '') : (string) $expectedValue;

                // Detectar si el valor esperado parece una fecha
                $expectedLooksLikeDate = (strpos($expectedStr, '/') !== false || strpos($expectedStr, '-') !== false);
                $userLooksLikeDate = (strpos($userStr, '/') !== false || strpos($userStr, '-') !== false);

                // Si el valor esperado parece una fecha, intentar parsear ambos
                if ($expectedLooksLikeDate) {
                    $expectedDate = $this->parseDate($expectedStr);

                    // Si el valor esperado es una fecha válida
                    if ($expectedDate) {
                        // Intentar parsear la respuesta del usuario como fecha
                        $userDate = $this->parseDate($userStr);

                        // Si la respuesta del usuario también es una fecha válida, compararlas
                        if ($userDate) {
                            return match ($operator) {
                                '>' => $userDate > $expectedDate,
                                '<' => $userDate < $expectedDate,
                                '>=' => $userDate >= $expectedDate,
                                '<=' => $userDate <= $expectedDate,
                                default => false,
                            };
                        } else {
                            // El valor esperado es una fecha válida, pero la respuesta del usuario no
                            return false;
                        }
                    }
                }

                // Si el valor esperado no es una fecha, o si ninguno es fecha válida, intentar como números
                $userNum = is_array($userAnswer) ? (float) ($userAnswer[0] ?? 0) : (float) $userAnswer;
                $expectedNum = is_array($expectedValue) ? (float) ($expectedValue[0] ?? 0) : (float) $expectedValue;

                // Solo comparar si ambos son numéricos válidos
                if (! is_numeric($userStr) || ! is_numeric($expectedStr)) {
                    return false;
                }

                return match ($operator) {
                    '>' => $userNum > $expectedNum,
                    '<' => $userNum < $expectedNum,
                    '>=' => $userNum >= $expectedNum,
                    '<=' => $userNum <= $expectedNum,
                    default => false,
                };
            case 'in':
                if (is_array($userAnswer)) {
                    if (is_array($expectedValue)) {
                        return ! empty(array_intersect(
                            array_map('strval', $userAnswer),
                            array_map('strval', $expectedValue)
                        ));
                    } else {
                        return in_array((string) $expectedValue, array_map('strval', $userAnswer), true);
                    }
                } else {
                    if (is_array($expectedValue)) {
                        return in_array((string) $userAnswer, array_map('strval', $expectedValue), true);
                    } else {
                        return strpos((string) $userAnswer, (string) $expectedValue) !== false;
                    }
                }
            case 'not_in':
                if (is_array($userAnswer)) {
                    if (is_array($expectedValue)) {
                        return empty(array_intersect(
                            array_map('strval', $userAnswer),
                            array_map('strval', $expectedValue)
                        ));
                    } else {
                        return ! in_array((string) $expectedValue, array_map('strval', $userAnswer), true);
                    }
                } else {
                    if (is_array($expectedValue)) {
                        return ! in_array((string) $userAnswer, array_map('strval', $expectedValue), true);
                    } else {
                        return strpos((string) $userAnswer, (string) $expectedValue) === false;
                    }
                }
            case 'contains':
                $userStr = is_array($userAnswer) ? (string) ($userAnswer[0] ?? '') : (string) $userAnswer;
                $expectedStr = is_array($expectedValue) ? (string) ($expectedValue[0] ?? '') : (string) $expectedValue;

                return mb_stripos($userStr, $expectedStr) !== false;
            case 'not_contains':
                $userStr = is_array($userAnswer) ? (string) ($userAnswer[0] ?? '') : (string) $userAnswer;
                $expectedStr = is_array($expectedValue) ? (string) ($expectedValue[0] ?? '') : (string) $expectedValue;

                return mb_stripos($userStr, $expectedStr) === false;
            case 'starts_with':
                $userStr = is_array($userAnswer) ? (string) ($userAnswer[0] ?? '') : (string) $userAnswer;
                $expectedStr = is_array($expectedValue) ? (string) ($expectedValue[0] ?? '') : (string) $expectedValue;

                return mb_stripos($userStr, $expectedStr) === 0;
            case 'ends_with':
                $userStr = is_array($userAnswer) ? (string) ($userAnswer[0] ?? '') : (string) $userAnswer;
                $expectedStr = is_array($expectedValue) ? (string) ($expectedValue[0] ?? '') : (string) $expectedValue;

                return mb_strtolower(mb_substr($userStr, -mb_strlen($expectedStr))) === mb_strtolower($expectedStr);
            default:
                return false;
        }
    }

    /**
     * Obtiene los slugs de los documentos especiales condicionales
     * que el usuario debe subir a partir de sus respuestas en el cuestionario.
     *
     * @param  int  $ayudaId  ID de la ayuda.
     * @param  int  $userId  ID del usuario.
     * @return array Lista de slugs de los documentos especiales.
     */
    public function obtenerSlugsDocumentosEspecialesCondicionales($ayudaId, int $userId)
    {
        // Obtener todas las preguntas directamente desde la tabla questions
        // No dependemos del cuestionario, ya que los slugs objetivo son globales
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
            'grupos_vulnerables_madrid',
        ];

        // Obtener respuestas directamente desde la BD usando los slugs objetivo
        $user = User::find($userId);
        $answersVulnerabilidad = $user->obtenerRespuestasPorSlugs($slugsObjetivo);

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
        $grupoVals = $asArray($answersVulnerabilidad['grupo_considerado_vulnerable'] ?? null);
        $familia = $asArray($answersVulnerabilidad['cual'] ?? null);

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

        // --- GRUPOS VULNERABLES MADRID (específico para ayudas de Madrid)
        $gruposVulnerablesMadrid = $asArray($answersVulnerabilidad['grupos_vulnerables_madrid'] ?? null);

        if (! empty($gruposVulnerablesMadrid)) {
            if (in_array('Persona con discapacidad', $gruposVulnerablesMadrid, true)) {
                $slugs[] = 'certificado-discapacidad';
            }
            if (in_array('Título de familia numerosa', $gruposVulnerablesMadrid, true)) {
                $slugs[] = 'certificado-familia-numerosa';
            }
            if (in_array('Familia monoparental con carga familiar', $gruposVulnerablesMadrid, true)) {
                $slugs[] = 'certificado-familia-monoparental';
            }
            if (in_array('Víctima de terrorismo', $gruposVulnerablesMadrid, true)) {
                $slugs[] = 'certificado-victima-terrorismo';
            }
            if (in_array('Hij@ víctima de violencia de género', $gruposVulnerablesMadrid, true)) {
                $slugs[] = 'certificado-violencia-genero';
            }
        }

        // --- VIOLENCIA / EXCLUSIÓN / EXTUTELADO / EXCONVICTO
        $grupoViogen = in_array(
            'Víctima de violencia de género, trata de explotación sexual, de violencia sexual, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a',
            $grupoVals,
            true
        );
        $viogen = $asArray($answersVulnerabilidad['cual_viogen'] ?? null);

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
        $sitFam = $asArray($answersVulnerabilidad['cual_situacion_familia'] ?? null); // <-- slug correcto

        if ($grupoDesempleo || in_array('Toda la unidad de convivencia está desempleada y hayan agotado las prestaciones', $sitFam, true)) {
            $slugs[] = 'certificado-situacion-desempleo';
        }

        // --- DESAHUCIO / EJECUCIÓN / DACIÓN / CATASTRÓFICA
        $grupoDesahucio = in_array(
            'Desahucio, ejecución hipotecaria o dación en pago de tu vivienda, en los últimos cinco años, o afectado/a por situación catastrófica',
            $grupoVals,
            true
        );
        $desahucio = $asArray($answersVulnerabilidad['cual_desahucio'] ?? null);

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

        // !"Persona que asuma acogimiento familiar permanente de menor",

        // !"¿Estás sujeto al Plan de protección internacional de Catalunya aprobado por el Acuerdo de gobierno de 28 de enero de 2014? (Solicitante de asilo, tarjeta roja…)"

        /**************************************************
         * Comprobamos si es propiertario de una vivienda *
         * y cual es su situación y añadimos el documento *
         * necesarios segun su situación                  *
         * ***********************************************/
        $esPropietario = $answersVulnerabilidad['tiene_viviendas'] ?? null;

        $situacionesPropietario = (array) ($answersVulnerabilidad['situaciones-propietario'] ?? []);

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
     * Obtiene los documentos de convivientes que deben mostrarse según sus condiciones.
     * Evalúa las condiciones de cada documento de conviviente para determinar si debe mostrarse.
     *
     * @param  int  $ayudaId  ID de la ayuda
     * @param  int  $userId  ID del usuario
     * @param  array  $answersSolicitante  Respuestas del solicitante (question_id => answer)
     * @return \Illuminate\Support\Collection Documentos de convivientes que deben mostrarse
     */
    public function obtenerDocumentosConvivientesConCondiciones(int $ayudaId, int $userId, array $answersSolicitante = []): \Illuminate\Support\Collection
    {
        try {
            // Obtener todos los documentos de convivientes configurados para esta ayuda
            $documentosConvivientes = AyudaDocumentoConviviente::with('documento')
                ->where('ayuda_id', $ayudaId)
                ->get();

            // Obtener convivientes del usuario
            $convivientes = \App\Models\Conviviente::where('user_id', $userId)
                ->orderBy('index')
                ->get();

            $documentosAMostrar = collect();

            foreach ($documentosConvivientes as $docConviviente) {
                if (! $docConviviente->documento) {
                    continue;
                }

                $docSlug = $docConviviente->documento->slug;

                // Si es obligatorio, siempre incluirlo
                if ($docConviviente->es_obligatorio) {
                    $documentosAMostrar->push($docConviviente);

                    continue;
                }

                // Si es opcional, evaluar condiciones
                if (! empty($docConviviente->conditions)) {

                    $convivientesQueCumplen = [];

                    // Estructura nueva: { condition: 'AND', requirements: [...] }
                    if (is_array($docConviviente->conditions) && isset($docConviviente->conditions['condition']) && isset($docConviviente->conditions['requirements'])) {
                        $logic = $docConviviente->conditions['condition'] ?? 'AND';
                        $requirements = $docConviviente->conditions['requirements'] ?? [];

                        // Evaluar para cada conviviente
                        foreach ($convivientes as $conviviente) {
                            // Obtener respuestas del conviviente
                            $answersConviviente = \App\Models\Answer::where('user_id', $userId)
                                ->where('conviviente_id', $conviviente->id)
                                ->pluck('answer', 'question_id')
                                ->map(function ($answer) {
                                    $decoded = json_decode($answer, true);

                                    return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $answer;
                                })
                                ->toArray();

                            // Combinar respuestas del solicitante y del conviviente
                            // Las condiciones pueden referirse a preguntas del solicitante o del conviviente
                            // IMPORTANTE: Las respuestas del conviviente tienen prioridad sobre las del solicitante
                            // para evitar que respuestas del solicitante sobrescriban las del conviviente
                            // Usamos el operador + para que las claves del segundo array (conviviente) tengan prioridad
                            $allAnswers = $answersConviviente + $answersSolicitante;

                            $resultadoEvaluacion = $this->evaluarRequisitosDocumento($requirements, $allAnswers, $logic);

                            if ($resultadoEvaluacion) {
                                $convivientesQueCumplen[] = $conviviente->id;
                            }
                        }
                    }
                    // Estructura antigua (legacy): array directo de condiciones
                    elseif (is_array($docConviviente->conditions) && ! isset($docConviviente->conditions['condition'])) {
                        $logic = $docConviviente->conditions_logic ?? 'AND';

                        foreach ($convivientes as $conviviente) {
                            $answersConviviente = \App\Models\Answer::where('user_id', $userId)
                                ->where('conviviente_id', $conviviente->id)
                                ->pluck('answer', 'question_id')
                                ->map(function ($answer) {
                                    $decoded = json_decode($answer, true);

                                    return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $answer;
                                })
                                ->toArray();

                            $allAnswers = $answersConviviente + $answersSolicitante;

                            $resultadoEvaluacion = $this->evaluarRequisitosDocumento($docConviviente->conditions, $allAnswers, $logic);

                            if ($resultadoEvaluacion) {
                                $convivientesQueCumplen[] = $conviviente->id;
                            }
                        }
                    }

                    if (! empty($convivientesQueCumplen)) {
                        // Agregar atributo dinámico con los IDs de los convivientes que deben ver este documento
                        $docConviviente->setAttribute('convivientes_ids', $convivientesQueCumplen);
                        $documentosAMostrar->push($docConviviente);
                    }
                }
                // Si es opcional sin condiciones, no incluirlo
            }

            return $documentosAMostrar;
        } catch (\Exception $e) {
            Log::error('❌ [DocumentosConvivientes] ERROR al evaluar documentos de convivientes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ayuda_id' => $ayudaId,
                'user_id' => $userId,
            ]);

            return collect(); // Retornar colección vacía en caso de error
        }
    }

    /**
     * Genera los documentos de recibos mensuales desde
     * la fecha de inicio del periodo hasta el mes actual.
     *
     * @param  Ayuda  $ayuda
     */
    public function generarDocumentosRecibos($ayuda): Collection
    {
        // Configurar el locale a español
        Carbon::setLocale('es');
        setlocale(LC_TIME, 'es_ES.UTF-8');
        $inicio = Carbon::parse($ayuda->fecha_inicio_periodo)->startOfMonth();
        $fin = Carbon::now()->startOfMonth(); // solo hasta mes actual

        $documentos = collect();

        while ($inicio <= $fin) {
            $documentos->push((object) [
                'id' => 7,
                'name' => 'Recibo de alquiler de '.$inicio->translatedFormat('F Y'),
                'slug' => 'recibo_'.$inicio->format('Y_m'),
                'tipo' => 'mensual',
                'mes' => $inicio->copy(),
                'nombre_personalizado' => 'Recibo de alquiler de '.$inicio->translatedFormat('F Y'),
            ]);

            $inicio->addMonth();
        }

        return $documentos;
    }

    /**
     * Comprueba si el solicitante ha subido todos los
     * documentos obligatorios para la ayuda solicitada.
     * Devuelve true si todos los documentos están subidos,
     * false si falta alguno.
     */
    protected function comprobarDocumentosCompletos(int $userId, Ayuda $ayuda): bool
    {
        $answers = Answer::getColectionAnswersQuestions($userId);
        // todo revisar el tema de preguntas de convivientes
        $slugsEspeciales = $this->obtenerSlugsDocumentosEspecialesCondicionales($ayuda->id, $userId);
        // Obtener los documentos obligatorios de la ayuda
        // Filtramos los documentos obligatorios que son de tipo 'general' o 'especial' y están en los slugs especiales
        // Esto es necesario para que no se rompa
        // si la ayuda no tiene documentos obligatorios o si no hay slugs especiales

        $obligatorios = AyudaDocumento::with('documento')
            ->where('ayuda_id', $ayuda->id)
            ->get()
            ->filter(function ($docRel) use ($slugsEspeciales) {
                return $docRel->documento &&
                    (
                        $docRel->documento->tipo === 'general' ||
                        ($docRel->documento->tipo === 'especial' && in_array($docRel->documento->slug, $slugsEspeciales))
                    );
            });

        $subidos = UserDocument::where('user_id', $userId)
            ->where('estado', 'validado')
            ->pluck('slug')
            ->toArray();

        $slugsObligatorios = $obligatorios->pluck('documento.slug')->toArray();

        // Si se requieren recibos
        if (
            $ayuda->sector === 'vivienda' &&
            ! ($answers[1] ?? '') === 'Todavía no tengo contrato de alquiler firmado.' &&
            $ayuda->fecha_inicio_periodo && $ayuda->fecha_fin_periodo
        ) {
            $recibos = $this->generarDocumentosRecibos($ayuda)->pluck('slug')->toArray();
            $slugsObligatorios = array_merge($slugsObligatorios, $recibos);
        }

        // Verificar que todos los documentos requeridos están subidos
        foreach ($slugsObligatorios as $slug) {
            if (! in_array($slug, $subidos)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Parsea una fecha en formato DD/MM/YYYY o DD-MM-YYYY a un objeto Carbon
     */
    private function parseDate(string $dateStr): ?\Carbon\Carbon
    {
        if (empty($dateStr)) {
            return null;
        }

        // Intentar diferentes formatos de fecha
        $formats = [
            'd/m/Y',      // 01/01/2010
            'd-m-Y',      // 01-01-2010
            'd/m/y',      // 01/01/10
            'd-m-y',      // 01-01-10
            'Y-m-d',      // 2010-01-01
            'Y/m/d',      // 2010/01/01
        ];

        foreach ($formats as $format) {
            try {
                $date = \Carbon\Carbon::createFromFormat($format, $dateStr);
                if ($date) {
                    return $date;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Si no se pudo parsear con formatos específicos, intentar parse automático
        try {
            return \Carbon\Carbon::parse($dateStr);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtiene las condiciones de documentos para evaluación en frontend
     *
     * @param  int  $ayudaId  ID de la ayuda
     * @return array Array con condiciones de documentos del solicitante y convivientes
     */
    public function obtenerCondicionesDocumentos(int $ayudaId): array
    {
        // Obtener documentos del solicitante con sus condiciones
        $documentosSolicitante = AyudaDocumento::with('documento')
            ->where('ayuda_id', $ayudaId)
            ->get()
            ->map(function ($docRel) {
                if (! $docRel->documento) {
                    return null;
                }

                return [
                    'document_id' => $docRel->documento_id,
                    'slug' => $docRel->documento->slug,
                    'es_obligatorio' => $docRel->es_obligatorio,
                    'conditions' => $docRel->conditions,
                    'conditions_logic' => $docRel->conditions_logic ?? 'AND',
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        // Obtener documentos de convivientes con sus condiciones
        $documentosConvivientes = AyudaDocumentoConviviente::with('documento')
            ->where('ayuda_id', $ayudaId)
            ->get()
            ->map(function ($docRel) {
                if (! $docRel->documento) {
                    return null;
                }

                return [
                    'document_id' => $docRel->documento_id,
                    'slug' => $docRel->documento->slug,
                    'es_obligatorio' => $docRel->es_obligatorio,
                    'conditions' => $docRel->conditions,
                    'conditions_logic' => $docRel->conditions_logic ?? 'AND',
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        return [
            'solicitante' => $documentosSolicitante,
            'convivientes' => $documentosConvivientes,
        ];
    }
}
