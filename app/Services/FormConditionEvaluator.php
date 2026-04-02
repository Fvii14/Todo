<?php

namespace App\Services;

/**
 * Servicio centralizado para evaluar condiciones de formularios
 * basadas en operator + value (y arrays de valores).
 *
 * NO conoce de visibilidad ni de negocio, solo de comparar
 * respuesta de usuario vs valor esperado.
 */
class FormConditionEvaluator
{
    /**
     * Evalúa una condición simple: respuesta de usuario vs operador/valor esperado.
     *
     * @param  mixed  $userAnswer  Respuesta del usuario (scalar o array)
     * @param  string|null  $operator  Operador (==, !=, >, >=, <, <=, contains, not_contains, starts_with, ends_with, in, not_in)
     * @param  mixed  $expectedValue  Valor esperado (scalar o array)
     */
    public function evaluate($userAnswer, ?string $operator, $expectedValue): bool
    {
        $operator = $operator ?? '==';

        // Normalizar operador '=' -> '=='
        if ($operator === '=') {
            $operator = '==';
        }

        // Manejar arrays en comparaciones de igualdad
        if ($operator === '==') {
            if (is_array($userAnswer)) {
                // Respuesta array
                if (is_array($expectedValue)) {
                    // Ambos arrays: intersección
                    $userArray = array_map('strval', $userAnswer);
                    $expectedArray = array_map('strval', $expectedValue);
                    $intersection = array_intersect($userArray, $expectedArray);

                    return ! empty($intersection);
                }

                // Respuesta array, esperado escalar
                $userArray = array_map('strval', $userAnswer);
                $expectedStr = (string) $expectedValue;

                return in_array($expectedStr, $userArray, true);
            }

            // Respuesta escalar
            if (is_array($expectedValue)) {
                $userStr = (string) $userAnswer;
                $expectedArray = array_map('strval', $expectedValue);

                return in_array($userStr, $expectedArray, true);
            }

            // Ambos escalares
            $userStr = (string) $userAnswer;
            $expectedStr = (string) $expectedValue;

            return $userStr === $expectedStr;
        }

        // Para otros operadores, normalizar como string
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
                    }

                    $userArray = array_map('strval', $userAnswer);
                    $expectedStr = (string) $expectedValue;

                    return ! in_array($expectedStr, $userArray, true);
                }

                if (is_array($expectedValue)) {
                    $userStr = (string) $userAnswer;
                    $expectedArray = array_map('strval', $expectedValue);

                    return ! in_array($userStr, $expectedArray, true);
                }

                return $userValue != $expected;

            case '>':
            case '<':
            case '>=':
            case '<=':
                // Intentar parsear como fechas primero
                $userStr = is_array($userAnswer) ? (string) ($userAnswer[0] ?? '') : (string) $userAnswer;
                $expectedStr = is_array($expectedValue) ? (string) ($expectedValue[0] ?? '') : (string) $expectedValue;

                $expectedLooksLikeDate = (strpos($expectedStr, '/') !== false || strpos($expectedStr, '-') !== false);

                if ($expectedLooksLikeDate) {
                    $expectedDate = date_create($expectedStr);

                    if ($expectedDate) {
                        $userDate = date_create($userStr);

                        if (! $userDate) {
                            return false;
                        }

                        return match ($operator) {
                            '>' => $userDate > $expectedDate,
                            '<' => $userDate < $expectedDate,
                            '>=' => $userDate >= $expectedDate,
                            '<=' => $userDate <= $expectedDate,
                            default => false,
                        };
                    }
                }

                // Si no son fechas válidas, intentar como números
                $userNum = is_array($userAnswer) ? (float) ($userAnswer[0] ?? 0) : (float) $userAnswer;
                $expectedNum = is_array($expectedValue) ? (float) ($expectedValue[0] ?? 0) : (float) $expectedValue;

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

            case 'contains':
                return mb_stripos((string) $userValue, (string) $expected) !== false;

            case 'not_contains':
                return mb_stripos((string) $userValue, (string) $expected) === false;

            case 'starts_with':
                return mb_stripos((string) $userValue, (string) $expected) === 0;

            case 'ends_with':
                $valueStr = (string) $userValue;
                $expectedStr = (string) $expected;

                return mb_strtolower(mb_substr($valueStr, -mb_strlen($expectedStr))) === mb_strtolower($expectedStr);

            case 'in':
                if (is_array($userAnswer)) {
                    if (is_array($expectedValue)) {
                        return ! empty(array_intersect(
                            array_map('strval', $userAnswer),
                            array_map('strval', $expectedValue)
                        ));
                    }

                    return in_array((string) $expectedValue, array_map('strval', $userAnswer), true);
                }

                if (is_array($expectedValue)) {
                    return in_array((string) $userAnswer, array_map('strval', $expectedValue), true);
                }

                return strpos((string) $userAnswer, (string) $expectedValue) !== false;

            case 'not_in':
                if (is_array($userAnswer)) {
                    if (is_array($expectedValue)) {
                        return empty(array_intersect(
                            array_map('strval', $userAnswer),
                            array_map('strval', $expectedValue)
                        ));
                    }

                    return ! in_array((string) $expectedValue, array_map('strval', $userAnswer), true);
                }

                if (is_array($expectedValue)) {
                    return ! in_array((string) $userAnswer, array_map('strval', $expectedValue), true);
                }

                return strpos((string) $userAnswer, (string) $expectedValue) === false;

            default:
                return false;
        }
    }

    /**
     * Helper para evaluar una estructura tipo QuestionCondition + mapa de answers.
     *
     * @param  array  $condition  ['question_id' => int, 'operator' => string, 'value' => mixed]
     * @param  array  $answers  [question_id => answer]
     */
    public function evaluateConditionArray(array $condition, array $answers): bool
    {
        $questionId = $condition['question_id'] ?? null;

        if ($questionId === null) {
            return false;
        }

        $answer = $answers[$questionId] ?? null;

        if (! array_key_exists('value', $condition) || $condition['value'] === null) {
            return false;
        }

        if ($answer === null || $answer === '') {
            return false;
        }

        $operator = $condition['operator'] ?? '==';
        $expectedValue = $condition['value'];

        return $this->evaluate($answer, $operator, $expectedValue);
    }
}
