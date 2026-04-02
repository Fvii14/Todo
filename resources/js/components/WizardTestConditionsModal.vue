<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div
            class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col"
        >
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">
                        <i class="fas fa-vial text-green-600 mr-2"></i>
                        Probar Condiciones del Formulario
                        {{ formType === 'solicitante' ? 'Solicitante' : 'Conviviente' }}
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Responde las preguntas para ver cómo se muestran y ocultan según las
                        condiciones configuradas
                    </p>
                </div>
                <button
                    @click="$emit('close')"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <div
                    v-if="!questions || questions.length === 0"
                    class="text-center py-8 text-gray-500"
                >
                    <i class="fas fa-info-circle text-4xl mb-4"></i>
                    <p>
                        No hay preguntas configuradas para el formulario
                        {{ formType === 'solicitante' ? 'del solicitante' : 'del conviviente' }}.
                    </p>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="(question, index) in questions"
                        :key="question.id || index"
                        :class="[
                            'p-4 border rounded-lg transition-all duration-300',
                            isQuestionVisible(question)
                                ? 'border-gray-300 bg-white'
                                : 'border-gray-200 bg-gray-50 opacity-50',
                        ]"
                    >
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ question.text || `Pregunta ${index + 1}` }}
                                    <span
                                        v-if="!isQuestionVisible(question)"
                                        class="ml-2 text-xs text-orange-600"
                                    >
                                        (Oculta por condición)
                                    </span>
                                </label>
                                <span class="text-xs text-gray-500">Tipo: {{ question.type }}</span>
                            </div>
                            <div v-if="hasConditionForQuestion(question.id)" class="ml-4">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                >
                                    <i class="fas fa-code-branch mr-1"></i>
                                    Tiene condición
                                </span>
                            </div>
                        </div>

                        <div v-if="isQuestionVisible(question)" class="mt-3">
                            <!-- Input según el tipo de pregunta -->
                            <input
                                v-if="['text', 'string', 'date'].includes(question.type)"
                                :value="answers[question.id]"
                                @input="updateAnswer(question.id, $event.target.value)"
                                :type="question.type === 'date' ? 'date' : 'text'"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :placeholder="`Respuesta para: ${question.text}`"
                            />

                            <input
                                v-else-if="question.type === 'integer'"
                                :value="answers[question.id]"
                                @input="
                                    updateAnswer(question.id, parseFloat($event.target.value) || 0)
                                "
                                type="number"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :placeholder="`Número para: ${question.text}`"
                            />

                            <select
                                v-else-if="question.type === 'select'"
                                :value="answers[question.id]"
                                @change="updateAnswer(question.id, $event.target.value)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Selecciona una opción</option>
                                <option
                                    v-for="(option, optIndex) in getQuestionOptions(question)"
                                    :key="optIndex"
                                    :value="option"
                                >
                                    {{ option }}
                                </option>
                            </select>

                            <div v-else-if="question.type === 'multiple'" class="space-y-2">
                                <label
                                    v-for="(option, optIndex) in getQuestionOptionsForTest(
                                        question,
                                    )"
                                    :key="optIndex"
                                    class="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded"
                                >
                                    <input
                                        v-if="!isNoneOption(option)"
                                        type="checkbox"
                                        :value="option"
                                        :checked="isOptionSelected(question.id, option)"
                                        @change="toggleMultipleAnswer(question.id, option)"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <input
                                        v-else
                                        type="checkbox"
                                        :checked="isNoneSelected(question.id)"
                                        @change="setNoneOfTheAbove(question.id)"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="text-sm text-gray-700">{{ option }}</span>
                                </label>
                            </div>

                            <div
                                v-else-if="question.type === 'boolean'"
                                class="flex items-center space-x-4"
                            >
                                <label class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        :name="`question-${formType}-${question.id}`"
                                        :value="1"
                                        :checked="answers[question.id] === 1"
                                        @change="updateAnswer(question.id, 1)"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="text-sm text-gray-700">Sí</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        :name="`question-${formType}-${question.id}`"
                                        :value="0"
                                        :checked="answers[question.id] === 0"
                                        @change="updateAnswer(question.id, 0)"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="text-sm text-gray-700">No</span>
                                </label>
                            </div>

                            <div v-else class="text-sm text-gray-500 italic">
                                Tipo de pregunta no soportado para prueba: {{ question.type }}
                            </div>
                        </div>

                        <!-- Mostrar condiciones que afectan a esta pregunta (solo saltos que la saltan, no saltos directos hacia ella) -->
                        <div
                            v-if="
                                getConditionsForQuestion(question.id).filter((c) => !c.isDirectJump)
                                    .length > 0
                            "
                            class="mt-3 pt-3 border-t border-gray-200"
                        >
                            <div class="text-xs text-gray-600 space-y-1">
                                <div class="font-medium mb-1">
                                    Condiciones que controlan esta pregunta:
                                </div>
                                <div
                                    v-for="(condition, condIndex) in getConditionsForQuestion(
                                        question.id,
                                    ).filter((c) => !c.isDirectJump)"
                                    :key="condIndex"
                                    :class="[
                                        'text-xs p-2 rounded',
                                        evaluateCondition(condition)
                                            ? 'bg-green-50 text-green-700 border border-green-200'
                                            : 'bg-red-50 text-red-700 border border-red-200',
                                    ]"
                                >
                                    <div class="flex items-center gap-2">
                                        <i
                                            :class="
                                                evaluateCondition(condition)
                                                    ? 'fas fa-check-circle'
                                                    : 'fas fa-times-circle'
                                            "
                                        ></i>
                                        <span>
                                            Pregunta
                                            {{ getQuestionTextById(condition.question_id) }}
                                            {{ getOperatorText(condition.operator) }}
                                            {{ formatConditionValue(condition) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
                <button
                    @click="$emit('close')"
                    class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useConditionEvaluator } from '@/composables/useConditionEvaluator'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    questions: {
        type: Array,
        default: () => [],
    },
    conditions: {
        type: Array,
        default: () => [],
    },
    formType: {
        type: String,
        required: true,
        validator: (value) => ['solicitante', 'conviviente'].includes(value),
    },
})

const emit = defineEmits(['close'])

const answers = ref({})

// Limpiar respuestas cuando se cierra el modal
watch(
    () => props.show,
    (isOpen) => {
        if (!isOpen) {
            answers.value = {}
        }
    },
)

// Inicializar el evaluador de condiciones centralizado
const { evaluateSimple, evaluateFull } = useConditionEvaluator(
    computed(() => props.questions),
    (questionId) => answers.value[questionId],
)

const getQuestionOptions = (question) => {
    if (!question.options) return []
    if (Array.isArray(question.options)) return question.options
    if (typeof question.options === 'string') {
        try {
            return JSON.parse(question.options)
        } catch {
            return []
        }
    }
    return []
}

const NINGUNA_LABEL = 'Ninguna de las anteriores'

const isNoneOption = (option) => {
    if (option == null || option === '') return true
    const text = String(option).toLowerCase()
    return text.includes('ninguna') && (text.includes('anterior') || text.includes('otra'))
}

const getQuestionOptionsForTest = (question) => {
    const options = getQuestionOptions(question)
    if (question.type !== 'multiple' || !Array.isArray(options)) return options
    const hasNone = options.some((opt) => isNoneOption(opt))
    return hasNone ? options : [...options, NINGUNA_LABEL]
}

const updateAnswer = (questionId, value) => {
    answers.value[questionId] = value
}

const toggleMultipleAnswer = (questionId, optionValue) => {
    if (!answers.value[questionId]) {
        answers.value[questionId] = []
    }
    const index = answers.value[questionId].indexOf(optionValue)
    if (index > -1) {
        answers.value[questionId].splice(index, 1)
    } else {
        answers.value[questionId].push(optionValue)
    }
}

const isOptionSelected = (questionId, optionValue) => {
    return (
        answers.value[questionId] &&
        Array.isArray(answers.value[questionId]) &&
        answers.value[questionId].includes(optionValue)
    )
}

const setNoneOfTheAbove = (questionId) => {
    // Dejar explícitamente la respuesta como "ninguna opción seleccionada"
    answers.value[questionId] = []
}

const isNoneSelected = (questionId) => {
    const value = answers.value[questionId]
    return !value || (Array.isArray(value) && value.length === 0)
}

const hasConditionForQuestion = (questionId) => {
    if (!props.conditions) return false
    return props.conditions.some((cond) => cond.next_question_id == questionId)
}

// Obtener el índice de una pregunta en el array que contiene las preguntas oredenadas que forman el formulario
const getQuestionIndex = (questionId) => {
    return props.questions.findIndex((q) => q.id == questionId)
}

/**
 * Obtener el índice de destino de un salto aprtir del indice de la next_question
 * Si el salto es al final del formulario es valor de next_question es NULL y devuelve la logitud del array de las preguntas
 */
const getDestinationIndex = (nextQuestionId) => {
    if (nextQuestionId === null || nextQuestionId === 'FIN' || nextQuestionId === '') {
        return props.questions.length // FIN = después de la última pregunta
    }
    return props.questions.findIndex((q) => q.id == nextQuestionId)
}

// Verificar si un salto "salta sobre" una pregunta (la omite en el flujo)
const isQuestionSkippedByJump = (questionIndex, jump) => {
    const sourceIndex = getQuestionIndex(jump.question_id)
    const destIndex = getDestinationIndex(jump.next_question_id)

    // Si el salto va desde una pregunta anterior a una posterior,
    // y la pregunta actual está entre ellas, entonces el salto la salta
    if (sourceIndex !== -1 && destIndex !== -1) {
        //devuelve true solo si questionIndex está “entre” sourceIndex y destIndex sin incluir los extremos.
        return questionIndex > sourceIndex && questionIndex < destIndex
    }
    return false
}

// Obtener saltos que van directamente a una pregunta (la muestran explícitamente)
const getJumpsToQuestion = (questionId) => {
    if (!props.conditions) return []
    return props.conditions.filter((cond) => cond.next_question_id == questionId)
}

// Obtener saltos que saltan sobre una pregunta (la omiten en el flujo)
const getJumpsSkippingQuestion = (questionIndex) => {
    if (!props.conditions) return []
    return props.conditions.filter((jump) => isQuestionSkippedByJump(questionIndex, jump))
}

/**
 * Traduce los saltos configurados en condiciones de visibilidad para una pregunta.
 * Retorna tanto saltos directos (que la muestran) como saltos que la saltan (que la ocultan).
 */
const getConditionsForQuestion = (questionId) => {
    const questionIndex = getQuestionIndex(questionId)
    if (questionIndex === -1) return []

    const jumpsTo = getJumpsToQuestion(questionId)
    const jumpsSkipping = getJumpsSkippingQuestion(questionIndex)

    const visibilityConditions = []

    // Saltos directos: la pregunta es visible si se cumplen
    jumpsTo.forEach((jump) => {
        visibilityConditions.push({
            ...jump,
            isDirectJump: true,
        })
    })

    // Saltos que la saltan: la pregunta es visible si NO se cumplen (invertir lógica)
    jumpsSkipping.forEach((jump) => {
        visibilityConditions.push({
            ...jump,
            isDirectJump: false,
            inverted: true, // Si el salto se cumple, la pregunta NO es visible
        })
    })

    return visibilityConditions
}

const getQuestionTextById = (questionId) => {
    const question = props.questions.find((q) => q.id == questionId)
    return question ? question.text : `Pregunta ${questionId}`
}

const getOperatorText = (operator) => {
    const operators = {
        '==': 'es igual a',
        '!=': 'no es igual a',
        '>': 'es mayor que',
        '>=': 'es mayor o igual que',
        '<': 'es menor que',
        '<=': 'es menor o igual que',
        contains: 'contiene',
        not_contains: 'no contiene',
        starts_with: 'empieza por',
        ends_with: 'termina por',
    }
    return operators[operator] || operator
}

const formatConditionValue = (condition) => {
    if (Array.isArray(condition.value)) {
        return condition.value.join(', ')
    }
    return condition.value
}

// Usar el evaluador centralizado
const evaluateCondition = evaluateSimple
const evaluateFullCondition = evaluateFull

/**
 * Determina si una pregunta es visible basándose en los saltos configurados.
 *
 * IMPORTANTE: Los saltos DIRECTOS (A → B) solo se usan para "saltar" preguntas intermedias,
 * no para ocultar o mostrar la propia pregunta destino. Es decir:
 *
 * - Si hay un salto A → última_pregunta:
 *   - Las preguntas entre A y última_pregunta pueden ocultarse cuando se cumple la condición.
 *   - La última_pregunta SIEMPRE es visible (no está condicionada por ese salto).
 *
 * Por eso aquí SOLO usamos los saltos que "saltan sobre" la pregunta (skippingJumps) para
 * decidir su visibilidad. Los saltos directos se ignoran a nivel de visibilidad de destino.
 */
const isQuestionVisible = (question) => {
    const questionIndex = getQuestionIndex(question.id)
    if (questionIndex === -1) return true

    const conditions = getConditionsForQuestion(question.id)

    // Si no tiene condiciones ni saltos que la afecten, siempre visible
    if (conditions.length === 0) {
        return true
    }

    const skippingJumps = conditions.filter((c) => !c.isDirectJump)

    // Si hay saltos que la saltan, la pregunta es visible solo si NINGUNO se cumple
    if (skippingJumps.length > 0) {
        // evaluateFullCondition con inverted:true ya invierte el resultado:
        // - Si la condición se cumple: evaluateSimple devuelve true, evaluateFull devuelve !true = false
        // - Si la condición NO se cumple: evaluateSimple devuelve false, evaluateFull devuelve !false = true
        //
        // Para saltos que saltan sobre la pregunta:
        // - Si el salto se cumple (condición verdadera), la pregunta NO es visible
        // - Si el salto NO se cumple (condición falsa), la pregunta SÍ es visible
        //
        // Con inverted:true, evaluateFull devuelve:
        // - false cuando la condición se cumple (salto activo -> pregunta NO visible)
        // - true cuando la condición NO se cumple (salto inactivo -> pregunta visible)
        //
        // Por lo tanto, la pregunta es visible solo si evaluateFull devuelve true (salto inactivo)
        const allSkippingJumpsInactive = skippingJumps.every((jump) => {
            const result = evaluateFullCondition(jump)
            return result
        })
        return allSkippingJumpsInactive
    }

    return true
}
</script>
