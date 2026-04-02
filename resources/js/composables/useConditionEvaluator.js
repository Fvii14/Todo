/**
 * Composable centralizado para evaluar condiciones de cuestionarios.
 *
 * Evalúa condiciones en formato nuevo: operator + value, is_composite + composite_rules
 *
 * @param {Array|Ref|Computed} questions - Array de preguntas con {id, type, options} o un ref/computed que lo contiene
 * @param {Function} getAnswerFunction - Función que recibe questionId y devuelve la respuesta
 * @returns {Object} { evaluateSimple, evaluateFull }
 */
export function useConditionEvaluator(questions, getAnswerFunction) {
    /**
     * Obtiene el array de preguntas, manejando refs y computed
     */
    const getQuestionsArray = () => {
        // Si es un ref o computed, obtener su valor
        if (questions && typeof questions === 'object' && 'value' in questions) {
            return questions.value || []
        }
        // Si ya es un array, devolverlo directamente
        return Array.isArray(questions) ? questions : []
    }

    /**
     * Evalúa una condición simple (sin rules anidados)
     *
     * @param {Object} condition - { question_id, operator, value }
     * @returns {boolean}
     */
    const evaluateSimple = (condition) => {
        const questionId = condition.question_id
        const answer = getAnswerFunction(questionId)

        const questionsArrayForCheck = getQuestionsArray()
        const questionForCheck = questionsArrayForCheck.find((q) => q.id == questionId)
        const isMultipleNinguna =
            questionForCheck &&
            questionForCheck.type === 'multiple' &&
            (condition.value === null || condition.value === undefined)

        // Validar que condition.value no esté vacío (salvo "Ninguna de las anteriores" en multiple)
        if (condition.value === '' || condition.value === null || condition.value === undefined) {
            if (!isMultipleNinguna) return false
        }

        if (answer === undefined || answer === null || answer === '') {
            // Si la condición permite valores vacíos o 0, verificar
            if (Array.isArray(condition.value) && condition.value.includes(0)) {
                return true
            }
            return false
        }

        const operator = condition.operator || '=='
        let expectedValue = condition.value

        // Obtener el array de preguntas y buscar la pregunta
        const questionsArray = getQuestionsArray()
        const question = questionsArray.find((q) => q.id == questionId)

        // CRÍTICO: Normalizar el valor esperado INMEDIATAMENTE si viene como string
        // Esto es necesario porque las condiciones pueden venir del backend como strings
        if (typeof expectedValue === 'string') {
            if (expectedValue === '1' || expectedValue === 'true') {
                expectedValue = 1
            } else if (expectedValue === '0' || expectedValue === 'false') {
                expectedValue = 0
            }
        }
        const isSelectOrMultiple = question && ['select', 'multiple'].includes(question.type)

        // Para select/multiple: convertir índices a valores ANTES de normalizar booleanos
        if (isSelectOrMultiple && question && question.options && Array.isArray(question.options)) {
            if (Array.isArray(expectedValue)) {
                // Si es un array de índices legacy, convertir todos a valores
                expectedValue = expectedValue.map((val) => {
                    if (typeof val === 'number' && val >= 0 && val < question.options.length) {
                        return question.options[val]
                    } else if (val === -1 && question.type === 'multiple') {
                        return null // "Ninguna de las anteriores"
                    }
                    return val // Ya es un valor lógico (texto)
                })
            } else {
                // Valor único
                if (
                    typeof expectedValue === 'number' &&
                    expectedValue >= 0 &&
                    expectedValue < question.options.length
                ) {
                    // Es un índice legacy, convertir a valor lógico (texto)
                    expectedValue = question.options[expectedValue]
                } else if (expectedValue === -1 && question.type === 'multiple') {
                    // "Ninguna de las anteriores" en formato legacy
                    expectedValue = null
                }
            }
        }

        // Normalizar valores
        let normalizedAnswer = answer
        let normalizedExpected = expectedValue

        // Normalizar valores booleanos SOLO si NO es select/multiple
        // (para select/multiple, los valores son texto y no deben convertirse a booleanos)
        if (!isSelectOrMultiple) {
            // Normalizar expectedValue
            if (
                normalizedExpected === '1' ||
                normalizedExpected === 1 ||
                normalizedExpected === 'true' ||
                normalizedExpected === true
            ) {
                normalizedExpected = 1
            } else if (
                normalizedExpected === '0' ||
                normalizedExpected === 0 ||
                normalizedExpected === 'false' ||
                normalizedExpected === false ||
                normalizedExpected === null
            ) {
                normalizedExpected = 0
            }

            // Si es un array, normalizar cada elemento
            if (Array.isArray(normalizedExpected)) {
                normalizedExpected = normalizedExpected.map((v) => {
                    if (v === '1' || v === 1 || v === 'true' || v === true) return 1
                    if (v === '0' || v === 0 || v === 'false' || v === false) return 0
                    return v
                })
            }
        }

        // Normalizar answer para comparaciones booleanas SOLO si NO es select/multiple
        if (!isSelectOrMultiple) {
            // CRÍTICO: Normalizar la respuesta INMEDIATAMENTE si viene como string
            // Esto es necesario porque las respuestas pueden venir como strings desde el formulario
            if (typeof answer === 'string') {
                if (answer === '1' || answer === 'true') {
                    normalizedAnswer = 1
                } else if (answer === '0' || answer === 'false') {
                    normalizedAnswer = 0
                }
            } else if (answer === true || answer === 1) {
                normalizedAnswer = 1
            } else if (answer === false || answer === 0 || answer === null) {
                normalizedAnswer = 0
            }
        }

        // Si la respuesta es un array (múltiple)
        if (Array.isArray(answer)) {
            switch (operator) {
                case '==':
                case '=':
                    if (expectedValue === null) {
                        // "Ninguna de las anteriores"
                        return answer.length === 0
                    }
                    if (Array.isArray(expectedValue)) {
                        return expectedValue.some((val) => answer.includes(val))
                    }
                    return answer.includes(expectedValue)
                case '!=':
                    if (expectedValue === null) {
                        return answer.length > 0
                    }
                    if (Array.isArray(expectedValue)) {
                        return !expectedValue.some((val) => answer.includes(val))
                    }
                    return !answer.includes(expectedValue)
                case 'contains':
                case 'in':
                    if (Array.isArray(expectedValue)) {
                        return expectedValue.some((val) => answer.includes(val))
                    }
                    return answer.includes(expectedValue)
                case 'not_contains':
                case 'not_in':
                    if (Array.isArray(expectedValue)) {
                        return !expectedValue.some((val) => answer.includes(val))
                    }
                    return !answer.includes(expectedValue)
                default:
                    return false
            }
        }

        // Si ambos son numéricos (después de normalizar booleanos)
        // PERO NO para select/multiple donde los valores son texto
        if (
            !isSelectOrMultiple &&
            (typeof normalizedAnswer === 'number' || !isNaN(normalizedAnswer))
        ) {
            normalizedAnswer = Number(normalizedAnswer)
            if (Array.isArray(normalizedExpected)) {
                normalizedExpected = normalizedExpected.map((v) => {
                    // Ya debería estar normalizado, pero asegurar conversión a número
                    return typeof v === 'number' || !isNaN(v) ? Number(v) : v
                })
            } else {
                // Asegurar que normalizedExpected sea número si es numérico
                if (typeof normalizedExpected === 'number' || !isNaN(normalizedExpected)) {
                    normalizedExpected = Number(normalizedExpected)
                }
            }
        }

        let result
        switch (operator) {
            case '==':
            case '=':
                // Para select/multiple, comparar como strings (valores lógicos)
                if (isSelectOrMultiple) {
                    if (Array.isArray(normalizedExpected)) {
                        result = normalizedExpected.includes(normalizedAnswer)
                    } else {
                        result = String(normalizedAnswer) === String(normalizedExpected)
                    }
                } else {
                    if (Array.isArray(normalizedExpected)) {
                        result = normalizedExpected.includes(normalizedAnswer)
                    } else {
                        // Usar comparación estricta para evitar problemas de tipo
                        result = normalizedAnswer === normalizedExpected
                    }
                }
                break
            case '!=':
                // Para select/multiple, comparar como strings (valores lógicos)
                if (isSelectOrMultiple) {
                    if (Array.isArray(normalizedExpected)) {
                        result = !normalizedExpected.includes(normalizedAnswer)
                    } else {
                        result = String(normalizedAnswer) !== String(normalizedExpected)
                    }
                } else {
                    if (Array.isArray(normalizedExpected)) {
                        result = !normalizedExpected.includes(normalizedAnswer)
                    } else {
                        // Usar comparación estricta para evitar problemas de tipo
                        result = normalizedAnswer !== normalizedExpected
                    }
                }
                break
            case '>':
                result =
                    normalizedAnswer >
                    (Array.isArray(normalizedExpected) ? normalizedExpected[0] : normalizedExpected)
                break
            case '>=':
                result =
                    normalizedAnswer >=
                    (Array.isArray(normalizedExpected) ? normalizedExpected[0] : normalizedExpected)
                break
            case '<':
                result =
                    normalizedAnswer <
                    (Array.isArray(normalizedExpected) ? normalizedExpected[0] : normalizedExpected)
                break
            case '<=':
                result =
                    normalizedAnswer <=
                    (Array.isArray(normalizedExpected) ? normalizedExpected[0] : normalizedExpected)
                break
            case 'contains':
                result = String(normalizedAnswer)
                    .toLowerCase()
                    .includes(String(normalizedExpected).toLowerCase())
                break
            case 'not_contains':
                result = !String(normalizedAnswer)
                    .toLowerCase()
                    .includes(String(normalizedExpected).toLowerCase())
                break
            case 'starts_with':
                result = String(normalizedAnswer)
                    .toLowerCase()
                    .startsWith(String(normalizedExpected).toLowerCase())
                break
            case 'ends_with':
                result = String(normalizedAnswer)
                    .toLowerCase()
                    .endsWith(String(normalizedExpected).toLowerCase())
                break
            default:
                result = false
        }

        return result
    }

    /**
     * Evalúa una condición completa (simple o compuesta)
     *
     * Soporta:
     * - Condiciones simples: { question_id, operator, value }
     * - Condiciones compuestas del backend: { is_composite: true, composite_rules: [...], composite_logic: 'AND'|'OR' }
     * - Condiciones con rules del wizard: { rules: [...], connector: 'AND'|'OR' }
     * - Flag inverted: si es true, invierte el resultado
     *
     * @param {Object} condition - Condición simple o compuesta
     * @returns {boolean}
     */
    const evaluateFull = (condition) => {
        let result

        // Condición compuesta usando composite_rules del backend
        if (
            condition.is_composite &&
            Array.isArray(condition.composite_rules) &&
            condition.composite_rules.length > 0
        ) {
            const rules = condition.composite_rules
            const logic = condition.composite_logic || 'AND'

            result = evaluateSimple(rules[0])

            for (let i = 1; i < rules.length; i++) {
                const ruleResult = evaluateSimple(rules[i])
                if (logic === 'OR') {
                    result = result || ruleResult
                } else {
                    result = result && ruleResult
                }
            }
        } else if (
            condition.rules &&
            Array.isArray(condition.rules) &&
            condition.rules.length > 0
        ) {
            // Compatibilidad por si viniera en formato de rules (similar al wizard)
            result = evaluateSimple(condition.rules[0])

            for (let i = 1; i < condition.rules.length; i++) {
                const rule = condition.rules[i]
                const ruleResult = evaluateSimple(rule)
                const connector = rule.connector || 'AND'

                if (connector === 'OR') {
                    result = result || ruleResult
                } else {
                    result = result && ruleResult
                }
            }
        } else {
            // Condición simple
            result = evaluateSimple(condition)
        }

        // Si es un salto que salta sobre la pregunta (inverted), invertir el resultado
        // Ejemplo: si el salto se cumple (result = true), la pregunta NO es visible (!true = false)
        if (condition.inverted) {
            return !result
        }

        return result
    }

    return {
        evaluateSimple,
        evaluateFull,
    }
}
