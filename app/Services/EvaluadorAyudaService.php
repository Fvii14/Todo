<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\AyudaRequisitoJson;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvaluadorAyudaService
{
    public function evaluarJson(int $ayudaId, int $userId): array
    {
        $registros = AyudaRequisitoJson::where('ayuda_id', $ayudaId)->get();

        if ($registros->isEmpty()) {
            return [
                'es_beneficiario' => false,
                'detalles' => [],
                'razones_no_cumple' => ['Esta ayuda no tiene lógica de elegibilidad configurada'],
            ];
        }

        $answers = Answer::where('user_id', $userId)
            ->where('conviviente_id', null)
            ->get()
            ->keyBy('question_id');

        $resultados = [];
        $cumpleGlobal = true;
        $razonesNoCumple = [];

        if ($registros->count() === 1) {
            $registro = $registros->first();
            $reglas = $registro->json_regla;

            if (is_array($reglas) && array_is_list($reglas)) {
                foreach ($reglas as $reglaItem) {
                    $regla = is_string($reglaItem['json_regla'])
                        ? json_decode($reglaItem['json_regla'], true)
                        : $reglaItem['json_regla'];

                    if (! $regla) {
                        continue;
                    }

                    $resultado = $this->evaluarRegla($regla, $answers, $ayudaId, $userId);
                    $esPorRespuestaNull = $this->esFalloPorRespuestaNull($regla, $answers, $ayudaId, $userId);

                    $resultados[] = [
                        'descripcion' => $reglaItem['descripcion'] ?? 'Sin descripción',
                        'regla' => $reglaItem['json_regla'],
                        'resultado' => $resultado ? '✅ CUMPLE' : '❌ NO CUMPLE',
                        'formato' => 'antiguo',
                    ];
                    if (! $resultado && ! $esPorRespuestaNull) {
                        $razonesNoCumple[] = $reglaItem['descripcion'] ?? 'Sin descripción';
                        $cumpleGlobal = false;
                    }
                }
            } else {
                $regla = $reglas;

                if ($regla) {
                    $resultado = $this->evaluarRegla($regla, $answers, $ayudaId, $userId);
                    $esPorRespuestaNull = $this->esFalloPorRespuestaNull($regla, $answers, $ayudaId, $userId);

                    $resultados[] = [
                        'descripcion' => $registro->descripcion ?? 'Sin descripción',
                        'regla' => json_encode($regla),
                        'resultado' => $resultado ? '✅ CUMPLE' : '❌ NO CUMPLE',
                        'formato' => 'nuevo',
                    ];
                    if (! $resultado && ! $esPorRespuestaNull) {
                        $razonesNoCumple[] = $registro->descripcion ?? 'Sin descripción';
                        $cumpleGlobal = false;
                    }
                }
            }
        } else {
            foreach ($registros as $registro) {
                $regla = $registro->json_regla;

                if (! $regla) {
                    continue;
                }

                $resultado = $this->evaluarRegla($regla, $answers, $ayudaId, $userId);
                $esPorRespuestaNull = $this->esFalloPorRespuestaNull($regla, $answers, $ayudaId, $userId);

                $resultados[] = [
                    'descripcion' => $registro->descripcion ?? 'Sin descripción',
                    'regla' => json_encode($regla),
                    'resultado' => $resultado ? '✅ CUMPLE' : '❌ NO CUMPLE',
                    'formato' => 'nuevo',
                ];
                if (! $resultado && ! $esPorRespuestaNull) {
                    $razonesNoCumple[] = $registro->descripcion ?? 'Sin descripción';
                    $cumpleGlobal = false;
                }
            }
        }

        return [
            'es_beneficiario' => $cumpleGlobal,
            'detalles' => $resultados,
            'razones_no_cumple' => $razonesNoCumple,
        ];
    }

    private function evaluarRegla(array $regla, $answers, int $ayudaId, int $userId): bool
    {
        // 1) Caso precio_alquiler_limite
        if (isset($regla['tipo']) && $regla['tipo'] === 'precio_alquiler_limite') {
            return $this->evaluarPrecioConJson($regla, $answers, $userId, $ayudaId);
        }

        $condicion = $regla['condition'] ?? 'AND';
        $rules = $regla['rules'] ?? [];

        if (isset($regla['subgroups']) && is_array($regla['subgroups'])) {
            $reglasPrincipalesCumplen = true;
            if ($condicion === 'AND') {
                foreach ($rules as $rule) {
                    $ok = $this->evaluarRuleOrGroup($rule, $answers, $ayudaId, $userId);
                    if (! $ok) {
                        $reglasPrincipalesCumplen = false;
                        break;
                    }
                }
            } else {
                $reglasPrincipalesCumplen = false;
                foreach ($rules as $rule) {
                    $ok = $this->evaluarRuleOrGroup($rule, $answers, $ayudaId, $userId);
                    if ($ok) {
                        $reglasPrincipalesCumplen = true;
                        break;
                    }
                }
            }

            if (! $reglasPrincipalesCumplen) {
                return false;
            }

            $subgroups = $regla['subgroups'];
            $subgroupLogic = $regla['subgroupLogic'] ?? 'OR';

            $subgrupoExcluyente = $this->encontrarSubgrupoExcluyente($subgroups, $answers);
            if ($subgrupoExcluyente) {
                return $this->evaluarRegla($subgrupoExcluyente, $answers, $ayudaId, $userId);
            }

            if ($subgroupLogic === 'AND') {
                foreach ($subgroups as $subgroup) {
                    $ok = $this->evaluarRegla($subgroup, $answers, $ayudaId, $userId);
                    if (! $ok) {
                        return false;
                    }
                }

                return true;
            } else {
                foreach ($subgroups as $subgroup) {
                    $ok = $this->evaluarRegla($subgroup, $answers, $ayudaId, $userId);
                    if ($ok) {
                        return true;
                    }
                }

                return false;
            }
        }

        if ($condicion === 'AND') {
            // AND: si alguna regla falla, retorno false inmediatamente
            foreach ($rules as $rule) {
                $ok = $this->evaluarRuleOrGroup($rule, $answers, $ayudaId, $userId);
                if (! $ok) {
                    return false;
                }
            }

            return true;
        } else {
            // OR: si alguna regla pasa, retorno true inmediatamente
            foreach ($rules as $rule) {
                $ok = $this->evaluarRuleOrGroup($rule, $answers, $ayudaId, $userId);
                if ($ok) {
                    return true;
                }
            }

            return false;
        }
    }

    /**
     * Evalúa un rule simple o un grupo anidado.
     */
    private function evaluarRuleOrGroup(array $rule, $answers, int $ayudaId, int $userId): bool
    {
        if (isset($rule['condition'])) {
            // Grupo anidado: recursión
            return $this->evaluarRegla($rule, $answers, $ayudaId, $userId);
        }
        // TODO: ESTO ESTA COMO PARCHE PERO TENEMOS QUE MODIFICAR TODAS LAS LOGICAS================================
        $questionId = $rule['question_id'] ?? null;
        $personType = $rule['personType'] ?? 'solicitante';

        // Soporte de unidad de convivencia: evaluar TODAS las respuestas (solicitante + convivientes)
        if ($personType === 'conviviente' && $questionId) {
            $allAnswerRows = Answer::where('question_id', $questionId)
                ->where('user_id', $userId)
                ->get();

            if ($allAnswerRows->isEmpty()) {
                if ($ayudaId === 7) {
                    return false;
                }

                return true;
            }

            foreach ($allAnswerRows as $row) {
                $raw = $row->answer;
                $decoded = json_decode($raw, true);
                $answer = (json_last_error() === JSON_ERROR_NONE && $decoded !== null) ? $decoded : $raw;
                if (! $this->compararValor($answer, $rule)) {
                    return false; // si alguno NO cumple, todo el grupo no cumple
                }
            }

            return true; // todos cumplen
        }

        if (! $questionId || ! isset($answers[$questionId])) {
            if ($ayudaId === 7) {
                return false;
            }

            return true;
        }
        // TODO=====================================================================================================

        $raw = $answers[$questionId]->answer;
        $decoded = json_decode($raw, true);
        $answer = (json_last_error() === JSON_ERROR_NONE && $decoded !== null) ? $decoded : $raw;

        return $this->compararValor($answer, $rule);
    }

    private function compararValor($answer, array $rule): bool
    {
        $operator = $rule['operator'];
        $value = $rule['value'];

        if (isset($rule['valueType']) && $rule['valueType'] !== 'exact') {
            return $this->evaluarFechaDinamica($answer, $rule);
        }

        switch ($operator) {
            case '=':
            case '==':
                if (is_array($answer)) {
                    return in_array($value, $answer);
                }

                return $answer == $value;
            case '!=':
                if (is_array($answer)) {
                    return ! in_array($value, $answer);
                }

                return $answer != $value;
            case '<':
                return $answer < $value;
            case '<=':
                return $answer <= $value;
            case '>':
                return $answer > $value;
            case '>=':
                return $answer >= $value;
            case 'between':
                $v1 = $value;
                $v2 = $rule['value2'] ?? null;
                if ($v2 === null) {
                    return false;
                }
                // Intentar como fecha primero
                $aDate = self::parseFechaFlexible($answer);
                $v1Date = self::parseFechaFlexible($v1);
                $v2Date = self::parseFechaFlexible($v2);
                if ($aDate && $v1Date && $v2Date) {
                    return ($aDate >= $v1Date) && ($aDate <= $v2Date);
                }
                // Fallback numérico
                if (is_numeric($answer) && is_numeric($v1) && is_numeric($v2)) {
                    return ($answer >= $v1) && ($answer <= $v2);
                }

                return false;
            case 'in':
                return is_array($answer) ? count(array_intersect($answer, $value)) > 0 : in_array($answer, $value);
            case 'not_in':
                return is_array($answer) ? count(array_intersect($answer, $value)) === 0 : ! in_array($answer, $value);
            case 'less_than_years':
                if (! $answer) {
                    return false;
                }
                $nacimiento = self::parseFechaFlexible($answer);
                if (! $nacimiento) {
                    return false;
                }
                $hoy = new \DateTime;
                $edad = $hoy->diff($nacimiento)->y;

                return $edad < $value;
            case 'greater_than_years':
                if (! $answer) {
                    return false;
                }
                $nacimiento = self::parseFechaFlexible($answer);
                if (! $nacimiento) {
                    return false;
                }
                $hoy = new \DateTime;
                $edad = $hoy->diff($nacimiento)->y;

                return $edad >= $value;
            case 'born_in_year':
                if (! $answer) {
                    return false;
                }
                $nacimiento = self::parseFechaFlexible($answer);
                if (! $nacimiento) {
                    return false;
                }
                $anio_nacimiento = $nacimiento->format('Y');

                return $anio_nacimiento == $value;

            default:
                return false;
        }
    }

    private function evaluarFechaDinamica($fechaRespuesta, array $rule): bool
    {
        if (! $fechaRespuesta) {
            return false;
        }

        $valueType = $rule['valueType'];
        $ageUnit = $rule['ageUnit'] ?? 'years';
        $expectedAge = $rule['value'];

        $fechaNacimiento = self::parseFechaFlexible($fechaRespuesta);
        if (! $fechaNacimiento) {
            return false;
        }

        $hoy = new DateTime;
        $edad = $fechaNacimiento->diff($hoy);

        $edadEnUnidad = 0;
        switch ($ageUnit) {
            case 'years':
                $edadEnUnidad = $edad->y;
                break;
            case 'months':
                $edadEnUnidad = ($edad->y * 12) + $edad->m;
                break;
            case 'days':
                $edadEnUnidad = $edad->days;
                break;
        }

        switch ($valueType) {
            case 'age_minimum':
                return $edadEnUnidad >= $expectedAge;
            case 'age_maximum':
                return $edadEnUnidad <= $expectedAge;
            case 'age_range':
                $expectedAge2 = $rule['value2'] ?? null;
                if ($expectedAge2 === null) {
                    return false;
                }

                return $edadEnUnidad >= $expectedAge && $edadEnUnidad <= $expectedAge2;
            default:
                return false;
        }
    }

    private function evaluarPrecioConJson(array $regla, $answers, $userId, int $ayudaId): bool
    {
        $precio = DB::table('answers')
            ->where('user_id', $userId)
            ->where('question_id', 31)
            ->value('answer');
        $tipo = DB::table('answers')
            ->where('user_id', $userId)
            ->where('question_id', 1)
            ->value('answer');

        $grupoVulnerableRaw = DB::table('answers')
            ->where('user_id', $userId)
            ->where('question_id', 9)
            ->value('answer');

        $grupoVulnerable = json_decode($grupoVulnerableRaw, true) ?? []; // asegura que sea array

        $esFamiliaNumerosaGeneral = collect($grupoVulnerable)->intersect([
            'Familia numerosa',
            'Familia monoparental',
            'Persona con discapacidad reconocida inferior o igual al 33%',
        ])->isNotEmpty();

        $esFamiliaNumerosaEspecial = collect($grupoVulnerable)->intersect([
            'Familia numerosa especial',
            'Familia monoparental especial',
            'Persona con discapacidad reconocida superior al 33%',
        ])->isNotEmpty();

        if ($tipo === 'Vivo de alquiler en una vivienda completa y vivo sola/o.' || $tipo === 'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.' || $tipo === 'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.') {
            $tipo = 'piso_completo';
        } elseif ($tipo === 'Tengo un contrato por habitación.') {
            $tipo = 'habitacion';
        } else {
            return true;
        }
        $conceptos = DB::table('answers')
            ->where('user_id', $userId)
            ->where('question_id', 32)
            ->value('answer');
        $municipioNombre = DB::table('answers')
            ->where('user_id', $userId)
            ->where('question_id', 37)
            ->value('answer');

        if (! $precio || ! $tipo || ! $municipioNombre) {
            return false;
        }

        $precioFloat = floatval($precio);
        $descuentoTotal = 0;

        if ($conceptos) {
            $items = json_decode($conceptos, true);

            if (is_array($items)) {
                $descuentosAplicables = [];

                foreach ($items as $c) {
                    $clave = trim($c);
                    if (isset($regla['ajustes_extra'][$clave])) {
                        $descuentosAplicables[] = floatval($regla['ajustes_extra'][$clave]);
                    }
                }

                if ($ayudaId === 17) {
                    // Para la ayuda 17, máximo 15% de descuento total
                    $descuentoTotal = ! empty($descuentosAplicables) ? 15 : 0;
                } else {
                    $descuentoTotal = array_sum($descuentosAplicables);
                }
            }
        }

        $precioAjustado = $precioFloat * (1 - ($descuentoTotal / 100));

        $limite = $regla['default'][$tipo] ?? 0;
        if (! empty($regla['grupos']) && is_array($regla['grupos'])) {
            foreach ($regla['grupos'] as $grupo) {
                if (in_array($municipioNombre, $grupo['municipios'])) {
                    $limiteBase = $grupo[$tipo] ?? $limite;

                    // Evaluamos grupo vulnerable
                    if ($esFamiliaNumerosaEspecial && isset($grupo['familia_numerosa_especial'])) {
                        $limite = $grupo['familia_numerosa_especial'];
                    } elseif ($esFamiliaNumerosaGeneral && isset($grupo['familia_numerosa_general'])) {
                        $limite = $grupo['familia_numerosa_general'];
                    } else {
                        $limite = $limiteBase;
                    }

                    break;
                }
            }
        } else {
            if ($esFamiliaNumerosaEspecial && isset($regla['familia_numerosa_especial'])) {
                $limite = $regla['familia_numerosa_especial'];
            } elseif ($esFamiliaNumerosaGeneral && isset($regla['familia_numerosa_general'])) {
                $limite = $regla['familia_numerosa_general'];
            }
        }

        return $precioAjustado <= $limite;
    }

    private function getRespuestaPorSlug($answers, string $slug): ?string
    {
        $questionId = DB::table('questions')
            ->where('slug', $slug)
            ->value('id');

        return $answers[$questionId]->answer ?? null;
    }

    /**
     * Determina si un usuario puede ser potencialmente beneficiario de una ayuda,
     * evaluando tanto requisitos antiguos (un solo registro plano en json_regla)
     * como requisitos nuevos (varios registros estructurados en la tabla).
     *
     * La lógica considera que si no hay regla para la ayuda, se asume que la ayuda ES posible.
     * También, si alguna regla se evalúa como falsa, se marca esa ayuda como NO posible.
     * En caso de incertidumbre (el usuario aún no respondió alguna de los requisitos de la ayuda),
     * se asume que la ayuda ES posible.
     *
     * @param  int  $ayudaId  ID de la ayuda para la que se evalúa la elegibilidad.
     * @param  int  $userId  ID del usuario cuyo perfil y respuestas se evalúan.
     * @return bool
     *              - true: El usuario puede cumplir las condiciones o hay incertidumbre (sin evidencia de incumplimiento).
     *              - false: El usuario incumple al menos una condición explícita.
     */
    public function posiblesAyudas(int $ayudaId, int $userId): bool
    {
        $registros = AyudaRequisitoJson::where('ayuda_id', $ayudaId)->get();

        if ($registros->isEmpty()) {
            return true;
        }

        $answers = Answer::where('user_id', $userId)->where('conviviente_id', null)->get()->keyBy('question_id');

        return $this->evaluarPosiblesAyudasConDatos($registros, $answers, $userId, $ayudaId);
    }

    public function posiblesAyudasBatch(array $ayudaIds, int $userId): array
    {
        if (empty($ayudaIds)) {
            return [];
        }

        $registrosByAyuda = AyudaRequisitoJson::whereIn('ayuda_id', $ayudaIds)->get()->groupBy('ayuda_id');
        $answers = Answer::where('user_id', $userId)->where('conviviente_id', null)->get()->keyBy('question_id');

        $result = [];
        foreach ($ayudaIds as $ayudaId) {
            $registros = $registrosByAyuda->get($ayudaId, collect());
            $result[$ayudaId] = $this->evaluarPosiblesAyudasConDatos($registros, $answers, $userId, $ayudaId);
        }

        return $result;
    }

    private function evaluarPosiblesAyudasConDatos($registros, $answers, int $userId, int $ayudaId): bool
    {
        if ($registros->isEmpty()) {
            return true;
        }

        if ($registros->count() === 1) {
            $registro = $registros->first();
            $reglas = $registro->json_regla;

            if (is_array($reglas) && array_is_list($reglas)) {
                if (count($reglas) === 0) {
                    return true;
                }

                foreach ($reglas as $index => $reglaItem) {
                    $regla = is_string($reglaItem['json_regla'] ?? null)
                        ? json_decode($reglaItem['json_regla'], true)
                        : ($reglaItem['json_regla'] ?? $reglaItem);

                    if (! $regla) {
                        continue;
                    }

                    $resultado = $this->evalMaybeRegla($regla, $answers, $userId, $ayudaId);

                    if ($resultado === false) {
                        return false;
                    }
                }

                return true;
            }

            return true;
        }

        foreach ($registros as $index => $registro) {
            $regla = $registro->json_regla;

            if (! $regla) {
                continue;
            }

            $resultado = $this->evalMaybeRegla($regla, $answers, $userId, $ayudaId);

            if ($resultado === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evalúa recursivamente una regla o conjunto de reglas JSON,
     * considerando las respuestas del usuario y la lógica booleana (AND / OR).
     * En caso de que el usuario no haya respondido a una de las preguntas de la lógica,
     * se considerará true
     *
     * - Si alguna regla retorna false en una condición AND, se devuelve false.
     * - Si alguna regla retorna true en una condición OR, se devuelve true.
     * - Si falta información para decidir (regla con null), se propaga la incertidumbre.
     * - Devuelve null si no se puede determinar con certeza la evaluación final.
     *
     * @param  array  $regla  Array asociativo que representa la regla JSON con estructura lógica.
     * @param  App\Models\Answer  $answers  Colección de respuestas del usuario, indexadas por question_id.
     * @param  int  $userId  ID del usuario para posibles evaluaciones específicas (ej: precio alquiler).
     * @return bool|null
     *                   - true: La regla se cumple con certeza.
     *                   - false: La regla no se cumple con certeza.
     *                   - null: Incertidumbre por falta de datos para determinar.
     */
    private function evalMaybeRegla(array $regla, $answers, int $userId, int $ayudaId): ?bool
    {
        if (isset($regla['tipo']) && $regla['tipo'] === 'precio_alquiler_limite') {
            return $this->evaluarPrecioConJson($regla, $answers, $userId, $ayudaId);
        }

        $condicion = $regla['condition'] ?? 'AND';
        $rules = $regla['rules'] ?? [];

        $resultados = [];
        $hayIncertidumbre = false;

        foreach ($rules as $rule) {
            $resultado = null;

            if (isset($rule['condition'])) {
                $resultado = $this->evalMaybeRegla($rule, $answers, $userId, $ayudaId);
            } else {
                $questionId = $rule['question_id'] ?? null;
                if (! $questionId) {
                    $resultado = null;
                } else {
                    $personType = $rule['personType'] ?? 'solicitante';
                    if ($personType === 'conviviente') {
                        $rows = Answer::where('question_id', $questionId)
                            ->where('user_id', $userId)
                            ->get();
                        $convIds = Answer::where('user_id', $userId)
                            ->whereNotNull('conviviente_id')
                            ->distinct()
                            ->pluck('conviviente_id')
                            ->toArray();
                        $members = array_merge([null], $convIds);

                        $rowsByMember = $rows->groupBy(function ($r) {
                            return $r->conviviente_id ?? 'solicitante';
                        });

                        $anyNull = false;
                        foreach ($members as $memberId) {
                            $key = $memberId === null ? 'solicitante' : $memberId;
                            $memberRow = ($rowsByMember[$key] ?? null)?->first();
                            if (! $memberRow) {
                                $anyNull = true;

                                continue;
                            }
                            $raw = $memberRow->answer;
                            $dec = json_decode($raw, true);
                            $ans = (json_last_error() === JSON_ERROR_NONE && $dec !== null) ? $dec : $raw;
                            $ok = $this->compararValor($ans, $rule);
                            if ($ok === false) {
                                $resultado = false;
                                break;
                            }
                        }
                        if (! isset($resultado)) {
                            $resultado = $anyNull ? null : true;
                        }
                    } else {
                        if (! isset($answers[$questionId])) {
                            $resultado = null;
                        } else {
                            $answerRaw = $answers[$questionId]->answer ?? null;
                            $parsedAnswer = json_decode($answerRaw, true);
                            $resultado = $this->compararValor($parsedAnswer ?? $answerRaw, $rule);
                        }
                    }
                }
            }

            $resultados[] = $resultado;
            if ($resultado === null) {
                $hayIncertidumbre = true;
            }
        }

        if ($condicion === 'AND') {
            $hayFalse = in_array(false, $resultados, true);
            if ($hayFalse && ! $hayIncertidumbre) {
                return false;
            }

            return $hayIncertidumbre ? null : true;
        }

        if ($condicion === 'OR') {
            $hayTrue = in_array(true, $resultados, true);
            if ($hayTrue) {
                return true;
            }

            return $hayIncertidumbre ? null : false;
        }

        return null;
    }

    public function debugEvaluarRegla(array $regla, $answers, int $ayudaId, int $userId): bool
    {
        return $this->evaluarRegla($regla, $answers, $ayudaId, $userId);
    }

    public function evaluarParaTester(int $ayudaId, int $userId): array
    {
        $registros = AyudaRequisitoJson::where('ayuda_id', $ayudaId)->get();

        if ($registros->isEmpty()) {
            return [
                'es_beneficiario' => false,
                'detalles' => [],
                'razones_no_cumple' => ['No hay requisitos definidos para esta ayuda'],
                'condiciones_desconocidas' => [],
            ];
        }

        $answers = Answer::where('user_id', $userId)
            ->get()
            ->keyBy('question_id');

        $resultados = [];
        $cumpleGlobal = true;
        $razonesNoCumple = [];
        $condicionesDesconocidas = [];

        if ($registros->count() === 1) {
            $registro = $registros->first();
            $reglas = $registro->json_regla;

            if (is_array($reglas) && array_is_list($reglas)) {
                foreach ($reglas as $reglaItem) {
                    $regla = is_string($reglaItem['json_regla'])
                        ? json_decode($reglaItem['json_regla'], true)
                        : $reglaItem['json_regla'];

                    if (! $regla) {
                        $resultados[] = [
                            'descripcion' => $reglaItem['descripcion'] ?? 'Sin descripción',
                            'regla' => json_encode($reglaItem),
                            'resultado' => '❌ NO CUMPLE',
                            'detalles' => ['No se pudo decodificar la regla ANTIGUA'],
                        ];
                        $razonesNoCumple[] = $reglaItem['descripcion'] ?? 'Sin descripción';
                        $cumpleGlobal = false;

                        continue;
                    }

                    $resultado = $this->evaluarReglaParaTester($regla, $answers, $ayudaId, $condicionesDesconocidas);

                    $resultados[] = [
                        'descripcion' => $reglaItem['descripcion'] ?? 'Sin descripción',
                        'regla' => $reglaItem['json_regla'],
                        'resultado' => $resultado['cumple'] ? '✅ CUMPLE' : ($resultado['desconocida'] ? '❓ DESCONOCIDA' : '❌ NO CUMPLE'),
                        'detalles' => $resultado['detalles'] ?? [],
                    ];

                    if ($resultado['desconocida']) {
                        $condicionesDesconocidas[] = $reglaItem['descripcion'] ?? 'Sin descripción';
                    } elseif (! $resultado['cumple']) {
                        $razonesNoCumple[] = $reglaItem['descripcion'] ?? 'Sin descripción';
                        $cumpleGlobal = false;
                    }
                }
            } else {
                return [
                    'es_beneficiario' => false,
                    'detalles' => [],
                    'razones_no_cumple' => ['Formato de reglas antiguas no válido'],
                    'condiciones_desconocidas' => [],
                ];
            }
        } else {
            foreach ($registros as $registro) {
                $regla = $registro->json_regla;

                if (! $regla) {
                    $resultados[] = [
                        'descripcion' => $registro->descripcion ?? 'Sin descripción',
                        'regla' => 'null',
                        'resultado' => '❌ NO CUMPLE',
                        'detalles' => ['Regla NUEVA vacía'],
                    ];
                    $razonesNoCumple[] = $registro->descripcion ?? 'Sin descripción';
                    $cumpleGlobal = false;

                    continue;
                }

                $resultado = $this->evaluarReglaParaTester($regla, $answers, $ayudaId, $condicionesDesconocidas);

                $resultados[] = [
                    'descripcion' => $registro->descripcion ?? 'Sin descripción',
                    'regla' => json_encode($regla),
                    'resultado' => $resultado['cumple'] ? '✅ CUMPLE' : ($resultado['desconocida'] ? '❓ DESCONOCIDA' : '❌ NO CUMPLE'),
                    'detalles' => $resultado['detalles'] ?? [],
                ];

                if ($resultado['desconocida']) {
                    $condicionesDesconocidas[] = $registro->descripcion ?? 'Sin descripción';
                } elseif (! $resultado['cumple']) {
                    $razonesNoCumple[] = $registro->descripcion ?? 'Sin descripción';
                    $cumpleGlobal = false;
                }
            }
        }

        return [
            'es_beneficiario' => $cumpleGlobal,
            'puede_determinar' => empty($condicionesDesconocidas),
            'detalles' => $resultados,
            'razones_no_cumple' => $razonesNoCumple,
            'condiciones_desconocidas' => $condicionesDesconocidas,
        ];
    }

    public function evaluarJsonStrict(int $ayudaId, int $userId): array
    {
        $registros = AyudaRequisitoJson::where('ayuda_id', $ayudaId)->get();

        if ($registros->isEmpty()) {
            return [
                'es_beneficiario' => false,
                'detalles' => [],
                'razones_no_cumple' => ['No se encontraron requisitos de elegibilidad'],
            ];
        }

        $answers = Answer::where('user_id', $userId)->get()->keyBy('question_id');

        $detalles = [];
        $cumpleGlobal = true;
        $razonesNoCumple = [];

        foreach ($registros as $registro) {
            $regla = $registro->json_regla;
            if (! $regla) {
                $cumpleGlobal = false;
                $razonesNoCumple[] = $registro->descripcion ?? 'Regla vacía';
                $detalles[] = [
                    'descripcion' => $registro->descripcion ?? 'Sin descripción',
                    'resultado' => '❌ NO CUMPLE',
                    'detalles' => ['Regla vacía'],
                ];

                continue;
            }

            $resultado = $this->evalMaybeRegla($regla, $answers, $userId, $ayudaId);

            if ($resultado !== true) {
                $cumpleGlobal = false;
                $razonesNoCumple[] = $registro->descripcion ?? 'No cumple requisito';
            }

            $detalles[] = [
                'descripcion' => $registro->descripcion ?? 'Sin descripción',
                'resultado' => $resultado === true ? '✅ CUMPLE' : ($resultado === null ? '❓ DESCONOCIDA' : '❌ NO CUMPLE'),
                'detalles' => [],
            ];
        }

        return [
            'es_beneficiario' => $cumpleGlobal,
            'detalles' => $detalles,
            'razones_no_cumple' => $razonesNoCumple,
        ];
    }

    private function evaluarReglaParaTester(array $regla, $answers, int $ayudaId, &$condicionesDesconocidas): array
    {
        if (isset($regla['tipo']) && $regla['tipo'] === 'precio_alquiler_limite') {
            $resultado = $this->evaluarPrecioConJson($regla, $answers, Auth::id(), $ayudaId);

            return [
                'cumple' => $resultado,
                'desconocida' => false,
                'detalles' => ['Evaluación de precio de alquiler'],
            ];
        }

        $condicion = $regla['condition'] ?? 'AND';
        $rules = $regla['rules'] ?? [];

        $resultados = [];
        $hayDesconocidas = false;

        foreach ($rules as $rule) {
            $resultado = $this->evaluarRuleParaTester($rule, $answers, $ayudaId);
            $resultados[] = $resultado;

            if ($resultado['desconocida']) {
                $hayDesconocidas = true;
            }
        }

        if ($hayDesconocidas) {
            return [
                'cumple' => false,
                'desconocida' => true,
                'detalles' => $resultados,
            ];
        }

        $cumple = $condicion === 'AND'
            ? ! in_array(false, array_column($resultados, 'cumple'), true)
            : in_array(true, array_column($resultados, 'cumple'), true);

        return [
            'cumple' => $cumple,
            'desconocida' => false,
            'detalles' => $resultados,
        ];
    }

    private function evaluarRuleParaTester(array $rule, $answers, int $ayudaId): array
    {
        if (isset($rule['condition'])) {
            return $this->evaluarReglaParaTester($rule, $answers, $ayudaId, $condicionesDesconocidas);
        }

        $questionId = $rule['question_id'] ?? null;
        if (! $questionId) {
            return [
                'cumple' => false,
                'desconocida' => false,
                'detalles' => ['Pregunta no especificada'],
            ];
        }

        if (! isset($answers[$questionId])) {
            return [
                'cumple' => false,
                'desconocida' => true,
                'detalles' => ["Pregunta ID $questionId no respondida"],
            ];
        }

        $raw = $answers[$questionId]->answer;
        $decoded = json_decode($raw, true);
        $answer = (json_last_error() === JSON_ERROR_NONE && $decoded !== null) ? $decoded : $raw;

        $ok = $this->compararValor($answer, $rule);

        return [
            'cumple' => $ok,
            'desconocida' => false,
            'detalles' => [
                'pregunta_id' => $questionId,
                'respuesta' => $answer,
                'operador' => $rule['operator'] ?? '=',
                'valor_esperado' => $rule['value'] ?? null,
            ],
        ];
    }

    private function encontrarSubgrupoExcluyente(array $subgroups, $answers): ?array
    {
        if (count($subgroups) < 2) {
            return null;
        }

        $preguntasExcluyentes = $this->detectarPreguntasExcluyentes($subgroups);

        if (empty($preguntasExcluyentes)) {
            return null;
        }

        foreach ($preguntasExcluyentes as $preguntaId) {
            $respuestaUsuario = $answers[$preguntaId]->answer ?? null;
            if (! $respuestaUsuario) {
                continue;
            }

            foreach ($subgroups as $subgroup) {
                if ($this->subgrupoCumpleRespuesta($subgroup, $preguntaId, $respuestaUsuario)) {
                    return $subgroup;
                }
            }
        }

        return null;
    }

    private function detectarPreguntasExcluyentes(array $subgroups): array
    {
        $preguntasExcluyentes = [];

        $reglasPorPregunta = [];
        foreach ($subgroups as $subgroup) {
            $rules = $subgroup['rules'] ?? [];
            foreach ($rules as $rule) {
                $questionId = $rule['question_id'] ?? null;
                if ($questionId) {
                    $reglasPorPregunta[$questionId][] = $rule;
                }
            }
        }

        foreach ($reglasPorPregunta as $questionId => $reglas) {
            if (count($reglas) >= 2) {
                $tieneIgual = false;
                $tieneDistinto = false;
                $mismoValor = null;

                foreach ($reglas as $regla) {
                    $operator = $regla['operator'] ?? '';
                    $value = $regla['value'] ?? '';

                    if ($operator === '==' || $operator === '=') {
                        $tieneIgual = true;
                        $mismoValor = $value;
                    } elseif ($operator === '!=') {
                        $tieneDistinto = true;
                        if ($mismoValor === null) {
                            $mismoValor = $value;
                        }
                    }
                }

                if ($tieneIgual && $tieneDistinto) {
                    $preguntasExcluyentes[] = $questionId;
                }
            }
        }

        return $preguntasExcluyentes;
    }

    private function subgrupoCumpleRespuesta(array $subgroup, int $questionId, $respuestaUsuario): bool
    {
        $rules = $subgroup['rules'] ?? [];

        foreach ($rules as $rule) {
            if (($rule['question_id'] ?? null) === $questionId) {
                $operator = $rule['operator'] ?? '';
                $value = $rule['value'] ?? '';

                switch ($operator) {
                    case '==':
                    case '=':
                        return $respuestaUsuario == $value;
                    case '!=':
                        return $respuestaUsuario != $value;
                    case 'in':
                        return is_array($value) ? in_array($respuestaUsuario, $value) : $respuestaUsuario == $value;
                    case 'not_in':
                        return is_array($value) ? ! in_array($respuestaUsuario, $value) : $respuestaUsuario != $value;
                }
            }
        }

        return false;
    }

    private function esFalloPorRespuestaNull(array $regla, $answers, int $ayudaId, int $userId): bool
    {
        if (isset($regla['tipo']) && $regla['tipo'] === 'precio_alquiler_limite') {
            return false;
        }

        $condicion = $regla['condition'] ?? 'AND';
        $rules = $regla['rules'] ?? [];

        if (isset($regla['subgroups']) && is_array($regla['subgroups'])) {
            $reglasPrincipalesCumplen = true;
            if ($condicion === 'AND') {
                foreach ($rules as $rule) {
                    $ok = $this->evaluarRuleOrGroup($rule, $answers, $ayudaId, $userId);
                    if (! $ok) {
                        $reglasPrincipalesCumplen = false;
                        break;
                    }
                }
            } else {
                $reglasPrincipalesCumplen = false;
                foreach ($rules as $rule) {
                    $ok = $this->evaluarRuleOrGroup($rule, $answers, $ayudaId, $userId);
                    if ($ok) {
                        $reglasPrincipalesCumplen = true;
                        break;
                    }
                }
            }

            if (! $reglasPrincipalesCumplen) {
                return $this->esFalloPorRespuestaNullEnRules($rules, $answers, $ayudaId, $userId);
            }

            $subgroups = $regla['subgroups'];
            $subgroupLogic = $regla['subgroupLogic'] ?? 'OR';

            $subgrupoExcluyente = $this->encontrarSubgrupoExcluyente($subgroups, $answers);
            if ($subgrupoExcluyente) {
                return $this->esFalloPorRespuestaNullEnRules($subgrupoExcluyente['rules'] ?? [], $answers, $ayudaId, $userId);
            }

            if ($subgroupLogic === 'AND') {
                foreach ($subgroups as $subgroup) {
                    if ($this->esFalloPorRespuestaNullEnRules($subgroup['rules'] ?? [], $answers, $ayudaId, $userId)) {
                        return true;
                    }
                }

                return false;
            } else {
                foreach ($subgroups as $subgroup) {
                    if (! $this->esFalloPorRespuestaNullEnRules($subgroup['rules'] ?? [], $answers, $ayudaId, $userId)) {
                        return false;
                    }
                }

                return true;
            }
        }

        return $this->esFalloPorRespuestaNullEnRules($rules, $answers, $ayudaId, $userId);
    }

    private function esFalloPorRespuestaNullEnRules(array $rules, $answers, int $ayudaId, int $userId): bool
    {
        foreach ($rules as $rule) {
            if (isset($rule['condition'])) {
                if ($this->esFalloPorRespuestaNull($rule, $answers, $ayudaId, $userId)) {
                    return true;
                }
            } else {
                $questionId = $rule['question_id'] ?? null;
                $personType = $rule['personType'] ?? 'solicitante';

                if ($personType === 'conviviente' && $questionId) {
                    $allAnswerRows = Answer::where('question_id', $questionId)
                        ->where('user_id', $userId)
                        ->get();

                    if ($allAnswerRows->isEmpty()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private static function parseFechaFlexible($fecha)
    {
        if ($fecha instanceof \DateTime) {
            return $fecha;
        }
        $formatos = [
            'Y-m-d',
            'd/m/Y',
            'd-m-Y',
            'Y/m/d',
            'm/d/Y',
            'd.m.Y',
        ];
        foreach ($formatos as $formato) {
            $dt = \DateTime::createFromFormat($formato, $fecha);
            if ($dt && $dt->format($formato) === $fecha) {
                return $dt;
            }
        }
        try {
            return new \DateTime($fecha);
        } catch (\Exception $e) {
            return false;
        }
    }
}
