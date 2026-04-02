<template>
    <div
        v-if="show"
        class="modal fade"
        :class="{ show: show, 'd-block': show }"
        tabindex="-1"
        role="dialog"
        style="background-color: rgba(0, 0, 0, 0.5)"
        @click.self="close"
    >
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div style="flex: 1">
                        <h5 class="modal-title">
                            {{
                                convivienteNombre
                                    ? `Añadir datos ${convivienteNombre}`
                                    : `Añadir datos conviviente #${convivienteIndex}`
                            }}
                        </h5>
                        <div v-if="!isFormComplete" class="mt-2">
                            <span class="badge bg-warning text-dark">
                                ⚠️ Pendiente - Faltan preguntas por completar
                            </span>
                        </div>
                        <div v-else class="mt-2">
                            <span class="badge bg-success"> ✅ Completado </span>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="btn-close"
                        @click="close"
                        aria-label="Cerrar"
                    ></button>
                </div>

                <div class="modal-body" style="max-height: 70vh; overflow-y: auto">
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>

                    <div v-else-if="error" class="alert alert-danger">
                        {{ error }}
                    </div>

                    <div v-else>
                        <!-- Preguntas normales (no builder) -->
                        <div
                            v-for="(question, index) in regularQuestions"
                            :key="question.id"
                            v-show="isQuestionVisible(question.id)"
                            :class="[
                                'question-item mb-3',
                                isQuestionUnanswered(question) ? 'bg-warning bg-opacity-25' : '',
                            ]"
                        >
                            <label class="form-label">
                                {{ question.text_conviviente || question.text }}
                                <span v-if="isRequired(question)" class="text-danger">*</span>
                            </label>
                            <div v-if="question.subtext" class="text-muted small mb-1">
                                {{ question.subtext }}
                            </div>

                            <!-- Renderizar según tipo -->
                            <input
                                v-if="question.type === 'string'"
                                v-model="answers[question.id]"
                                type="text"
                                class="form-control"
                                :pattern="question.validation?.pattern"
                                :title="question.validation?.error_message"
                                @input="evaluateAllConditions"
                            />
                            <input
                                v-else-if="question.type === 'date'"
                                v-model="answers[question.id]"
                                type="date"
                                class="form-control"
                                @change="evaluateAllConditions"
                            />
                            <input
                                v-else-if="question.type === 'integer'"
                                v-model.number="answers[question.id]"
                                type="number"
                                class="form-control"
                                min="0"
                                step="1"
                                @input="evaluateAllConditions"
                            />
                            <div
                                v-else-if="question.type === 'boolean'"
                                class="form-check form-switch"
                            >
                                <input
                                    v-model="answers[question.id]"
                                    type="checkbox"
                                    class="form-check-input"
                                    :true-value="1"
                                    :false-value="0"
                                    @change="evaluateAllConditions"
                                />
                                <label class="form-check-label">No / Sí</label>
                            </div>
                            <select
                                v-else-if="question.type === 'select' && question.options"
                                v-model="answers[question.id]"
                                class="form-select"
                                @change="evaluateAllConditions"
                            >
                                <option value="-1">Seleccione una opción</option>
                                <option
                                    v-for="(option, key) in question.options"
                                    :key="key"
                                    :value="isNumericKey(key) ? option : key"
                                >
                                    {{ option }}
                                </option>
                            </select>
                            <div
                                v-else-if="question.type === 'multiple' && question.options"
                                class="form-check-group"
                            >
                                <div
                                    v-for="(option, key) in question.options"
                                    :key="key"
                                    class="form-check"
                                >
                                    <input
                                        :checked="
                                            isMultipleSelected(
                                                question.id,
                                                isNumericKey(key) ? option : key,
                                            )
                                        "
                                        type="checkbox"
                                        class="form-check-input"
                                        :value="isNumericKey(key) ? option : key"
                                        :disabled="isNoneSelected(question.id)"
                                        @change="handleMultipleChange(question.id, $event)"
                                    />
                                    <label class="form-check-label">{{ option }}</label>
                                </div>
                                <!-- Opción "Ninguna de las anteriores" -->
                                <div
                                    class="form-check mt-2"
                                    style="border-top: 1px solid #dee2e6; padding-top: 0.5rem"
                                >
                                    <input
                                        :checked="isNoneSelected(question.id)"
                                        type="checkbox"
                                        class="form-check-input"
                                        :value="-1"
                                        @change="handleNoneOptionChange(question.id, $event)"
                                    />
                                    <label class="form-check-label fw-semibold"
                                        >Ninguna de las anteriores</label
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Preguntas Builder -->
                        <div
                            v-for="(builderQuestion, index) in builderQuestions"
                            :key="builderQuestion.id"
                            v-show="isQuestionVisible(builderQuestion.id)"
                            :class="[
                                'mb-4',
                                isBuilderQuestionUnanswered(builderQuestion.id)
                                    ? 'bg-warning bg-opacity-25 p-3 rounded'
                                    : '',
                            ]"
                        >
                            <BuilderQuestion
                                :question="builderQuestion"
                                :question-index="index"
                                :value="builderAnswers[builderQuestion.id]"
                                :blocked="false"
                                @update="handleBuilderUpdate"
                            />
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="close">Cancelar</button>
                    <button type="button" class="btn btn-primary" @click="save" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                        Guardar datos
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'
import BuilderQuestion from './onboarder/BuilderQuestion.vue'
import { useConditionEvaluator } from '@/composables/useConditionEvaluator'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    questionnaireId: {
        type: Number,
        required: true,
    },
    convivienteIndex: {
        type: Number,
        required: true,
    },
    convivienteNombre: {
        type: String,
        default: null,
    },
})

const emit = defineEmits(['close', 'saved'])

const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const questions = ref([])
const answers = ref({})
const builderAnswers = ref({})
const conditions = ref([])
const hiddenQuestions = ref([])

const regularQuestions = computed(() => {
    return questions.value.filter((q) => q.type !== 'builder')
})

const builderQuestions = computed(() => {
    return questions.value
        .filter((q) => q.type === 'builder')
        .map((q) => ({
            id: q.id,
            question: {
                id: q.id,
                slug: q.slug,
                text: q.text_conviviente || q.text,
                type: 'builder',
            },
            questionIndex: 0,
        }))
})

const isRequired = (question) => {
    // Lógica para determinar si es obligatoria
    // Por ahora asumimos que todas son obligatorias si no tienen disable_answer
    return !question.disable_answer
}

const isNumericKey = (key) => {
    return !isNaN(key)
}

const isMultipleSelected = (questionId, value) => {
    const answer = answers.value[questionId]
    if (!answer) return false

    // Si la respuesta es -1 (Ninguna de las anteriores), ninguna opción está seleccionada
    if (answer === -1 || (Array.isArray(answer) && answer.includes(-1))) {
        return false
    }

    if (Array.isArray(answer)) {
        return answer.includes(value)
    }
    return answer === value
}

const isNoneSelected = (questionId) => {
    const answer = answers.value[questionId]
    if (!answer) return false

    if (Array.isArray(answer)) {
        return answer.includes(-1)
    }
    return answer === -1
}

const handleNoneOptionChange = (questionId, event) => {
    if (event.target.checked) {
        // Si se marca "Ninguna de las anteriores", limpiar todas las demás opciones
        answers.value[questionId] = [-1]
    } else {
        // Si se desmarca, limpiar la respuesta
        answers.value[questionId] = []
    }
    // Re-evaluar condiciones
    evaluateAllConditions()
}

const handleMultipleChange = (questionId, event) => {
    if (!Array.isArray(answers.value[questionId])) {
        answers.value[questionId] = []
    }

    const value = event.target.value

    // Si se marca una opción normal, desmarcar "Ninguna de las anteriores" si estaba marcada
    if (event.target.checked) {
        // Remover -1 si existe
        answers.value[questionId] = answers.value[questionId].filter((v) => v !== -1)

        if (!answers.value[questionId].includes(value)) {
            answers.value[questionId].push(value)
        }
    } else {
        // Si se desmarca una opción, simplemente removerla
        answers.value[questionId] = answers.value[questionId].filter((v) => v !== value)
    }

    // Re-evaluar condiciones cuando cambia una respuesta múltiple
    evaluateAllConditions()
}

const handleBuilderUpdate = (questionId, data) => {
    builderAnswers.value[questionId] = data
    // Re-evaluar condiciones cuando cambia un builder
    evaluateAllConditions()
}

// Función para obtener la respuesta actual de una pregunta
const getCurrentAnswer = (questionId) => {
    // Las preguntas builder no se evalúan en condiciones (son objetos complejos)
    // Solo evaluamos respuestas normales
    const answer = answers.value[questionId]

    if (answer === null || answer === undefined || answer === '' || answer === '-1') {
        return null
    }

    return answer
}

// Inicializar el evaluador de condiciones centralizado
const { evaluateSimple, evaluateFull } = useConditionEvaluator(questions, getCurrentAnswer)

// Función para evaluar todas las condiciones usando el formato nuevo (operator + value, composite_rules)
const evaluateAllConditions = () => {
    if (!conditions.value || conditions.value.length === 0) {
        hiddenQuestions.value = []
        return
    }

    // Obtener todas las preguntas que tienen condiciones (next_question_id)
    const questionsWithConditions = new Set()
    conditions.value.forEach((condition) => {
        if (condition.next_question_id) {
            questionsWithConditions.add(parseInt(condition.next_question_id, 10))
        }
    })

    const newHiddenQuestions = []

    // Para cada pregunta que tiene condiciones, verificar si TODAS se cumplen
    questionsWithConditions.forEach((nextQuestionId) => {
        // Obtener todas las condiciones que afectan a esta pregunta
        const conditionsForQuestion = conditions.value.filter((c) => {
            const nextId =
                typeof c.next_question_id === 'string' || typeof c.next_question_id === 'number'
                    ? parseInt(c.next_question_id, 10)
                    : null
            return nextId === nextQuestionId
        })

        let allConditionsMet = true

        // Verificar que TODAS las condiciones se cumplan (lógica AND)
        conditionsForQuestion.forEach((condition) => {
            // Usar el evaluador centralizado basado en operator + value / composite_rules
            const matches = evaluateFull(condition)

            if (!matches) {
                allConditionsMet = false
            }
        })

        // Si no se cumplen todas las condiciones, ocultar la pregunta
        if (!allConditionsMet) {
            newHiddenQuestions.push(nextQuestionId)
        }
    })

    hiddenQuestions.value = newHiddenQuestions
}

// Función para verificar si una pregunta es visible
const isQuestionVisible = (questionId) => {
    return !hiddenQuestions.value.includes(questionId)
}

// Función para verificar si una pregunta está sin contestar
const isQuestionUnanswered = (question) => {
    // Solo verificar si es obligatoria
    if (question.disable_answer) {
        return false
    }

    const answer = getCurrentAnswer(question.id)

    // Para preguntas boolean, considerar faltante solo si es null, false, o string vacío
    if (question.type === 'boolean') {
        return answer === null || answer === '' || answer === false || answer === undefined
    }

    // Para preguntas múltiples, verificar si el array está vacío o solo tiene -1 sin otras opciones
    if (question.type === 'multiple') {
        if (!answer || (Array.isArray(answer) && answer.length === 0)) {
            return true
        }
        // Si solo tiene -1, se considera contestada (es una respuesta válida)
        if (Array.isArray(answer) && answer.length === 1 && answer[0] === -1) {
            return false
        }
        // Si tiene -1 junto con otras opciones, se considera contestada pero hay que limpiar
        if (Array.isArray(answer) && answer.includes(-1) && answer.length > 1) {
            return false // Técnicamente está contestada, pero la lógica de exclusión debería prevenir esto
        }
        return false
    }

    // Para otras preguntas
    return answer === null || answer === '' || answer === undefined || answer === -1
}

// Función para verificar si un builder está sin contestar
const isBuilderQuestionUnanswered = (questionId) => {
    const builderData = builderAnswers.value[questionId]

    if (!builderData) {
        return true
    }

    // Para calculadora, verificar si hay ingresos
    const question = questions.value.find((q) => q.id === questionId)
    if (question && question.slug === 'calculadora') {
        return !builderData.incomes || builderData.incomes.length === 0
    }

    // Para education-builder, verificar si hay estudios
    if (question && question.slug === 'education-builder') {
        return !builderData.studies || builderData.studies.length === 0
    }

    return false
}

// Computed para verificar si el formulario está completo
const isFormComplete = computed(() => {
    // Verificar todas las preguntas visibles y obligatorias
    const visibleQuestions = questions.value.filter((q) => isQuestionVisible(q.id))

    const unanswered = []

    for (const question of visibleQuestions) {
        // Solo verificar preguntas obligatorias
        if (!question.disable_answer) {
            let isUnanswered = false

            if (question.type === 'builder') {
                isUnanswered = isBuilderQuestionUnanswered(question.id)
            } else {
                isUnanswered = isQuestionUnanswered(question)
            }

            if (isUnanswered) {
                unanswered.push({
                    id: question.id,
                    text: question.text,
                    type: question.type,
                    answer: getCurrentAnswer(question.id),
                })
            }
        }
    }

    // LOG: Mostrar preguntas sin contestar
    if (unanswered.length > 0) {
        console.log('⚠️ Preguntas sin contestar:', unanswered)
    } else {
        console.log('✅ Todas las preguntas obligatorias están contestadas')
    }

    return unanswered.length === 0
})

const loadQuestions = async () => {
    loading.value = true
    error.value = null

    try {
        const response = await axios.get(
            `/api/conviviente-builder-form/${props.questionnaireId}/${props.convivienteIndex}`,
        )

        questions.value = response.data.questions || []
        conditions.value = response.data.conditions || []

        // LOG: Ver qué datos recibimos
        console.log('📥 Datos recibidos del backend:', {
            questions: response.data.questions,
            answers: response.data.answers,
            conditions: response.data.conditions,
            convivienteNombre: response.data.convivienteNombre,
        })

        // Inicializar respuestas existentes
        if (response.data.answers) {
            Object.keys(response.data.answers).forEach((key) => {
                const questionId = parseInt(key)
                const answer = response.data.answers[key]

                // Detectar si es una respuesta de builder
                const question = questions.value.find((q) => q.id === questionId)
                if (question && question.type === 'builder') {
                    // Intentar parsear si es JSON
                    try {
                        builderAnswers.value[questionId] =
                            typeof answer === 'string' ? JSON.parse(answer) : answer
                    } catch {
                        builderAnswers.value[questionId] = answer
                    }
                } else if (question && question.type === 'multiple') {
                    // Para preguntas múltiples, parsear JSON si es string
                    try {
                        let parsedAnswer = typeof answer === 'string' ? JSON.parse(answer) : answer

                        // Si la respuesta es -1 (Ninguna de las anteriores), mantenerla como array con -1
                        if (parsedAnswer === -1) {
                            answers.value[questionId] = [-1]
                        } else if (Array.isArray(parsedAnswer)) {
                            answers.value[questionId] = parsedAnswer
                        } else {
                            // Si viene como string y es "-1", convertir a array
                            if (parsedAnswer === '-1' || parsedAnswer === -1) {
                                answers.value[questionId] = [-1]
                            } else {
                                answers.value[questionId] = []
                            }
                        }
                    } catch {
                        // Si falla el parseo, verificar si es -1
                        if (answer === -1 || answer === '-1') {
                            answers.value[questionId] = [-1]
                        } else {
                            answers.value[questionId] = Array.isArray(answer) ? answer : []
                        }
                    }
                } else {
                    answers.value[questionId] = answer
                }
            })
        }

        // LOG: Ver respuestas inicializadas
        console.log('📝 Respuestas inicializadas:', {
            answers: answers.value,
            builderAnswers: builderAnswers.value,
        })

        // LOG: Verificar estado de completitud
        setTimeout(() => {
            console.log('✅ Estado del formulario:', {
                isComplete: isFormComplete.value,
                visibleQuestions: questions.value.filter((q) => isQuestionVisible(q.id)),
                unansweredQuestions: questions.value
                    .filter((q) => {
                        if (!isQuestionVisible(q.id)) return false
                        if (q.disable_answer) return false
                        if (q.type === 'builder') {
                            return isBuilderQuestionUnanswered(q.id)
                        }
                        return isQuestionUnanswered(q)
                    })
                    .map((q) => ({ id: q.id, text: q.text, type: q.type })),
            })
            evaluateAllConditions()
        }, 100)
    } catch (err) {
        console.error('Error cargando preguntas:', err)
        error.value = 'Error al cargar el formulario. Por favor, intenta de nuevo.'
    } finally {
        loading.value = false
    }
}

const save = async () => {
    saving.value = true
    error.value = null

    try {
        // Preparar respuestas de builders como JSON
        const builderAnswersJson = {}
        Object.keys(builderAnswers.value).forEach((questionId) => {
            builderAnswersJson[questionId] = JSON.stringify(builderAnswers.value[questionId])
        })

        // Preparar respuestas múltiples como JSON
        const processedAnswers = {}
        Object.keys(answers.value).forEach((questionId) => {
            const question = questions.value.find((q) => q.id === parseInt(questionId))
            if (question && question.type === 'multiple') {
                const answer = answers.value[questionId]
                if (Array.isArray(answer)) {
                    // Si solo tiene -1, guardarlo como -1 directamente (no como array)
                    if (answer.length === 1 && answer[0] === -1) {
                        processedAnswers[questionId] = -1
                    } else {
                        // Remover -1 si hay otras opciones seleccionadas (por seguridad)
                        const cleanedAnswer = answer.filter((v) => v !== -1)
                        if (cleanedAnswer.length > 0) {
                            processedAnswers[questionId] = JSON.stringify(cleanedAnswer)
                        } else {
                            processedAnswers[questionId] = -1
                        }
                    }
                } else {
                    processedAnswers[questionId] = answer
                }
            } else {
                processedAnswers[questionId] = answers.value[questionId]
            }
        })

        // Combinar todas las respuestas
        const allAnswers = {
            ...processedAnswers,
            ...builderAnswersJson,
        }

        const response = await axios.post(
            '/convivientes',
            {
                index: props.convivienteIndex,
                questionnaire_id: props.questionnaireId,
                answers: allAnswers,
            },
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            },
        )

        // Verificar si la respuesta tiene success o si el status es exitoso
        if (response.data?.success || response.status === 200) {
            emit('saved')
            close()
        } else {
            error.value = response.data?.message || 'Error al guardar los datos'
        }
    } catch (err) {
        console.error('Error guardando:', err)
        error.value =
            err.response?.data?.message ||
            'Error al guardar los datos. Por favor, intenta de nuevo.'
    } finally {
        saving.value = false
    }
}

const close = () => {
    emit('close')
}

watch(
    () => props.show,
    (newVal) => {
        if (newVal) {
            loadQuestions()
        } else {
            // Limpiar datos al cerrar
            questions.value = []
            answers.value = {}
            builderAnswers.value = {}
            conditions.value = []
            hiddenQuestions.value = []
            error.value = null
        }
    },
)

// Watch para re-evaluar condiciones cuando cambian las respuestas
watch(
    answers,
    () => {
        evaluateAllConditions()
    },
    { deep: true },
)

onMounted(() => {
    if (props.show) {
        loadQuestions()
    }
})
</script>

<style scoped>
.question-item {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 12px;
    margin-bottom: 16px;
}

.form-label {
    font-weight: 600;
    color: #333;
}

.modal.show {
    display: block !important;
}
</style>
