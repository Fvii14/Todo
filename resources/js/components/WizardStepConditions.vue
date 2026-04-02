<template>
    <div class="w-full min-h-[600px] relative">
        <div
            class="w-full h-[800px] bg-white rounded-xl border overflow-auto"
            style="
                background-image: radial-gradient(circle, #e5e7eb 1px, transparent 1px);
                background-size: 20px 20px;
            "
        >
            <VueFlow
                v-model:nodes="nodes"
                v-model:edges="edges"
                :fit-view="true"
                :fit-view-options="{ padding: 0.2, includeHiddenNodes: false, minZoom: 0.3 }"
                :min-zoom="0.2"
                :max-zoom="2.0"
                class="w-full h-full"
                :nodes-draggable="true"
                :nodes-connectable="false"
                :elements-selectable="true"
                :zoom-on-scroll="true"
                :zoom-on-double-click="true"
                :pan-on-drag="true"
            >
                <template #node-default="{ data, id }">
                    <div
                        :class="[
                            'rounded-xl shadow-lg p-4 min-w-[280px] text-center border-2 transition cursor-pointer group relative',
                            id === 'FIN'
                                ? 'bg-red-50 border-red-200 text-red-800'
                                : 'bg-white border-indigo-100 hover:border-indigo-400',
                        ]"
                    >
                        <!-- Indicador de orden -->
                        <div
                            v-if="id !== 'FIN'"
                            class="absolute -top-3 -left-3 w-8 h-8 bg-indigo-500 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg"
                        >
                            {{ data.order }}
                        </div>

                        <div class="font-bold text-base mb-2">{{ data.text }}</div>
                        <div v-if="id !== 'FIN'" class="text-xs text-gray-500 mb-2">
                            Tipo: {{ data.type }}
                        </div>
                        <div
                            v-if="data.options && data.options.length && id !== 'FIN'"
                            class="text-xs text-gray-400 mb-3"
                        >
                            Opciones: {{ data.options.join(', ') }}
                        </div>
                        <button
                            v-if="id !== 'FIN'"
                            @click.stop="openConditionModal(id)"
                            class="mt-3 bg-blue-100 text-blue-700 px-3 py-2 rounded text-sm hover:bg-blue-200 font-medium"
                        >
                            + Añadir salto
                        </button>
                        <Handle
                            v-if="id !== 'FIN'"
                            type="source"
                            position="bottom"
                            :id="`source-${id}`"
                        />
                        <Handle
                            v-if="id !== 'FIN'"
                            type="target"
                            position="top"
                            :id="`target-${id}`"
                        />
                    </div>
                </template>
                <template
                    #edge-default="{
                        id,
                        sourceX,
                        sourceY,
                        targetX,
                        targetY,
                        sourcePosition,
                        targetPosition,
                        style,
                    }"
                >
                    <g>
                        <path
                            :d="`M ${sourceX} ${sourceY} L ${targetX} ${targetY}`"
                            :stroke="style.stroke"
                            :stroke-width="style.strokeWidth"
                            fill="none"
                            marker-end="url(#arrowhead)"
                            class="transition-all duration-200"
                        />
                        <circle
                            :cx="(sourceX + targetX) / 2"
                            :cy="(sourceY + targetY) / 2"
                            r="16"
                            fill="white"
                            stroke="#ef4444"
                            stroke-width="2"
                            class="cursor-pointer hover:fill-red-50 transition-colors shadow-sm"
                            @click="confirmDeleteCondition(id)"
                        />
                        <text
                            :x="(sourceX + targetX) / 2"
                            :y="(sourceY + targetY) / 2"
                            text-anchor="middle"
                            dominant-baseline="middle"
                            class="text-sm font-bold fill-red-600"
                            style="pointer-events: none"
                        >
                            ×
                        </text>
                    </g>
                </template>
            </VueFlow>
            <defs>
                <marker
                    id="arrowhead"
                    markerWidth="12"
                    markerHeight="8"
                    refX="10"
                    refY="4"
                    orient="auto"
                >
                    <polygon
                        points="0 0, 12 4, 0 8"
                        fill="#3b82f6"
                        stroke="#1e40af"
                        stroke-width="1"
                    />
                </marker>
                <marker
                    id="arrowclosed-red"
                    markerWidth="12"
                    markerHeight="8"
                    refX="10"
                    refY="4"
                    orient="auto"
                >
                    <polygon
                        points="0 0, 12 4, 0 8"
                        fill="#ef4444"
                        stroke="#dc2626"
                        stroke-width="1"
                    />
                </marker>
            </defs>
        </div>
        <div v-if="!nodes.length" class="text-gray-400 text-center py-12">
            No hay preguntas aún. Añade la primera pregunta.
        </div>

        <!-- Modales de pregunta -->
        <transition name="fade">
            <div
                v-if="showQuestionModal"
                class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
            >
                <div
                    class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md relative animate-fade-in"
                >
                    <button
                        @click="closeModals"
                        class="absolute top-2 right-2 text-gray-400 hover:text-gray-700"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                    <h3 class="text-lg font-bold mb-4">
                        {{ editingQuestion.id ? 'Editar' : 'Añadir' }} pregunta
                    </h3>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Texto</label>
                        <input
                            v-model="editingQuestion.text"
                            class="w-full border rounded px-3 py-2"
                        />
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Tipo</label>
                        <select
                            v-model="editingQuestion.type"
                            class="w-full border rounded px-3 py-2"
                        >
                            <option value="text">Texto</option>
                            <option value="number">Número</option>
                            <option value="boolean">Sí/No</option>
                            <option value="select">Selección</option>
                            <option value="multiple">Selección múltiple</option>
                            <option value="date">Fecha</option>
                        </select>
                    </div>
                    <div v-if="['select', 'multiple'].includes(editingQuestion.type)" class="mb-3">
                        <label class="block text-sm font-medium mb-1"
                            >Opciones (separadas por coma)</label
                        >
                        <input
                            v-model="editingQuestion.optionsString"
                            class="w-full border rounded px-3 py-2"
                            placeholder="Ej: Opción 1, Opción 2"
                        />
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button
                            @click="saveQuestion"
                            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
                        >
                            Guardar
                        </button>
                        <button @click="closeModals" class="bg-gray-200 px-4 py-2 rounded">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Modales de condición -->
        <transition name="fade">
            <div
                v-if="showConditionModal"
                class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
                @click.self="closeModals"
            >
                <div
                    class="bg-white rounded-xl shadow-lg w-full max-w-4xl relative animate-fade-in max-h-[90vh] flex flex-col"
                >
                    <div class="flex-shrink-0 p-8 pb-4">
                        <button
                            @click="closeModals"
                            class="absolute top-2 right-2 text-gray-400 hover:text-gray-700"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                        <h3 class="text-lg font-bold mb-4">
                            {{ editingCondition.id ? 'Editar' : 'Añadir' }} saltos
                        </h3>
                    </div>
                    <div class="flex-1 overflow-y-auto px-8 pb-8">
                        <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                            <div class="text-sm font-medium text-blue-800">Pregunta origen:</div>
                            <div class="text-sm text-blue-600">{{ getSourceQuestionText() }}</div>
                            <div class="text-xs text-blue-500 mt-1">
                                Tipo: {{ getSourceQuestionType() }}
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-medium text-gray-700">Saltos configurados:</h4>
                                <button
                                    @click="addNewCondition"
                                    class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700"
                                >
                                    <i class="fas fa-plus mr-1"></i>Añadir salto
                                </button>
                            </div>

                            <div
                                v-if="conditionList.length === 0"
                                class="text-gray-500 text-center py-4 border-2 border-dashed border-gray-300 rounded"
                            >
                                No hay saltos configurados. Añade el primero.
                            </div>

                            <div v-else class="space-y-4">
                                <div
                                    v-for="(condition, index) in conditionList"
                                    :key="index"
                                    class="border rounded-lg p-4 bg-gray-50"
                                >
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex items-center">
                                            <h5 class="font-medium text-gray-700">
                                                Salto {{ index + 1 }}
                                            </h5>
                                            <span
                                                v-if="condition.rules && condition.rules.length > 1"
                                                class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full"
                                            >
                                                Compuesta ({{ condition.rules.length }} reglas)
                                            </span>
                                            <span
                                                v-else
                                                class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full"
                                            >
                                                Simple
                                            </span>
                                        </div>
                                        <button
                                            @click="removeCondition(index)"
                                            class="text-red-500 hover:text-red-700"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="mb-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-sm font-medium text-gray-700"
                                                >Condiciones del salto:</label
                                            >
                                            <button
                                                @click="addConditionRule(condition)"
                                                class="text-blue-600 hover:text-blue-800 text-sm"
                                            >
                                                <i class="fas fa-plus mr-1"></i>Añadir condición
                                            </button>
                                        </div>

                                        <div
                                            v-if="condition.rules && condition.rules.length > 0"
                                            class="space-y-3"
                                        >
                                            <div
                                                v-for="(rule, ruleIndex) in condition.rules"
                                                :key="ruleIndex"
                                                class="border-l-4 border-blue-200 pl-3 bg-white rounded p-3"
                                            >
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-xs font-medium text-gray-600"
                                                        >Condición {{ ruleIndex + 1 }}</span
                                                    >
                                                    <button
                                                        @click="
                                                            removeConditionRule(
                                                                condition,
                                                                ruleIndex,
                                                            )
                                                        "
                                                        class="text-red-400 hover:text-red-600 text-sm"
                                                    >
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                                    <div>
                                                        <label
                                                            class="block text-xs font-medium text-gray-600 mb-1"
                                                            >Pregunta</label
                                                        >
                                                        <select
                                                            v-model="rule.question_id"
                                                            class="w-full border rounded px-2 py-1 text-sm"
                                                        >
                                                            <option value="">
                                                                Selecciona pregunta
                                                            </option>
                                                            <option
                                                                v-for="q in getAvailableQuestionsForCondition()"
                                                                :value="q.id"
                                                                :key="q.id"
                                                            >
                                                                {{ q.order }}. {{ q.text }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label
                                                            class="block text-xs font-medium text-gray-600 mb-1"
                                                            >Operador</label
                                                        >
                                                        <select
                                                            v-model="rule.operator"
                                                            class="w-full border rounded px-2 py-1 text-sm"
                                                        >
                                                            <option value="">
                                                                Selecciona operador
                                                            </option>
                                                            <option
                                                                v-for="op in getOperatorsForQuestion(
                                                                    rule.question_id,
                                                                )"
                                                                :value="op.value"
                                                                :key="op.value"
                                                            >
                                                                {{ op.label }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label
                                                            class="block text-xs font-medium text-gray-600 mb-1"
                                                            >Valor</label
                                                        >
                                                        <select
                                                            v-if="
                                                                getQuestionType(
                                                                    rule.question_id,
                                                                ) === 'boolean'
                                                            "
                                                            v-model="rule.value"
                                                            class="w-full border rounded px-2 py-1 text-sm"
                                                        >
                                                            <option value="">
                                                                Selecciona valor
                                                            </option>
                                                            <option value="1">Sí</option>
                                                            <option value="0">No</option>
                                                        </select>
                                                        <div
                                                            v-else-if="
                                                                getQuestionType(
                                                                    rule.question_id,
                                                                ) === 'select' &&
                                                                getQuestionSlug(
                                                                    rule.question_id,
                                                                ) === 'municipio'
                                                            "
                                                            class="w-full"
                                                        >
                                                            <div class="relative">
                                                                <input
                                                                    v-model="municipioSearchTerm"
                                                                    @input="
                                                                        handleMunicipioSearch(
                                                                            $event.target.value,
                                                                            rule,
                                                                        )
                                                                    "
                                                                    type="text"
                                                                    placeholder="Escribe para buscar municipios..."
                                                                    class="w-full border rounded px-2 py-1 text-sm pr-8"
                                                                />
                                                                <div
                                                                    v-if="isSearchingMunicipios"
                                                                    class="absolute right-2 top-1/2 transform -translate-y-1/2"
                                                                >
                                                                    <div
                                                                        class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"
                                                                    ></div>
                                                                </div>
                                                            </div>
                                                            <div
                                                                v-if="
                                                                    municipioSearchResults.length >
                                                                    0
                                                                "
                                                                class="mt-1 max-h-32 overflow-y-auto border rounded bg-white"
                                                            >
                                                                <div
                                                                    v-for="option in municipioSearchResults"
                                                                    :key="option"
                                                                    @click="
                                                                        () => {
                                                                            rule.value = option
                                                                            municipioSearchTerm.value =
                                                                                option
                                                                        }
                                                                    "
                                                                    class="px-2 py-1 text-sm hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                                                >
                                                                    {{ option }}
                                                                </div>
                                                            </div>
                                                            <div
                                                                v-else-if="
                                                                    !municipioSearchTerm &&
                                                                    municipioSearchResults.length ===
                                                                        0
                                                                "
                                                                class="mt-1 text-xs text-gray-400"
                                                            >
                                                                Escribe al menos 2 letras para
                                                                buscar municipios
                                                            </div>
                                                        </div>
                                                        <select
                                                            v-else-if="
                                                                getQuestionType(
                                                                    rule.question_id,
                                                                ) === 'select'
                                                            "
                                                            v-model="rule.value"
                                                            class="w-full border rounded px-2 py-1 text-sm"
                                                        >
                                                            <option value="">
                                                                Selecciona valor
                                                            </option>
                                                            <option
                                                                v-for="option in getQuestionOptions(
                                                                    rule.question_id,
                                                                )"
                                                                :value="option"
                                                                :key="option"
                                                            >
                                                                {{ option }}
                                                            </option>
                                                        </select>
                                                        <select
                                                            v-else-if="
                                                                getQuestionType(
                                                                    rule.question_id,
                                                                ) === 'multiple'
                                                            "
                                                            v-model="rule.value"
                                                            class="w-full border rounded px-2 py-1 text-sm"
                                                        >
                                                            <option value="">
                                                                Selecciona valor
                                                            </option>
                                                            <option
                                                                v-for="option in getQuestionOptions(
                                                                    rule.question_id,
                                                                )"
                                                                :value="option"
                                                                :key="option"
                                                            >
                                                                {{ option }}
                                                            </option>
                                                        </select>
                                                        <input
                                                            v-else-if="
                                                                getQuestionType(
                                                                    rule.question_id,
                                                                ) === 'number'
                                                            "
                                                            v-model="rule.value"
                                                            type="number"
                                                            class="w-full border rounded px-2 py-1 text-sm"
                                                            placeholder="Número"
                                                        />
                                                        <input
                                                            v-else-if="
                                                                getQuestionType(
                                                                    rule.question_id,
                                                                ) === 'date'
                                                            "
                                                            v-model="rule.value"
                                                            type="date"
                                                            class="w-full border rounded px-2 py-1 text-sm"
                                                        />
                                                        <input
                                                            v-else
                                                            v-model="rule.value"
                                                            class="w-full border rounded px-2 py-1 text-sm"
                                                            placeholder="Valor"
                                                        />
                                                    </div>

                                                    <div>
                                                        <label
                                                            class="block text-xs font-medium text-gray-600 mb-1"
                                                            >Conectivo</label
                                                        >
                                                        <select
                                                            v-model="rule.connector"
                                                            class="w-full border rounded px-2 py-1 text-sm"
                                                        >
                                                            <option value="AND">Y (AND)</option>
                                                            <option value="OR">O (OR)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            v-else
                                            class="text-center py-4 border-2 border-dashed border-gray-300 rounded bg-white"
                                        >
                                            <p class="text-sm text-gray-500">
                                                No hay condiciones configuradas
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                Añade al menos una condición para este salto
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Destino del salto -->
                                    <div class="border-t pt-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-2"
                                            >Pregunta destino:</label
                                        >
                                        <select
                                            v-model="condition.next_question_id"
                                            class="w-full border rounded px-3 py-2 text-sm"
                                        >
                                            <option value="">Selecciona destino</option>
                                            <option
                                                v-for="q in getAvailableDestinations()"
                                                :value="q.id"
                                                :key="q.id"
                                            >
                                                {{ q.text }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex-shrink-0 px-8 pb-8 pt-4 border-t border-gray-200">
                        <div class="flex justify-end gap-2">
                            <button
                                @click="saveAllConditions"
                                class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
                            >
                                Guardar
                            </button>
                            <button @click="closeModals" class="bg-gray-200 px-4 py-2 rounded">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Confirmación de borrado -->
        <transition name="fade">
            <div
                v-if="showDeleteModal"
                class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
            >
                <div
                    class="bg-white rounded-xl shadow-lg p-8 w-full max-w-sm relative animate-fade-in"
                >
                    <button
                        @click="closeModals"
                        class="absolute top-2 right-2 text-gray-400 hover:text-gray-700"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                    <h3 class="text-lg font-bold mb-4">¿Seguro que quieres eliminar?</h3>
                    <p class="mb-4 text-gray-600">
                        {{
                            deleteTarget.type === 'question'
                                ? 'Esta pregunta se eliminará permanentemente.'
                                : deleteTarget.type === 'all_conditions'
                                  ? 'Todas las condiciones se eliminarán permanentemente.'
                                  : 'Este salto se eliminará permanentemente.'
                        }}
                    </p>
                    <div class="flex justify-end gap-2">
                        <button
                            @click="deleteConfirmed"
                            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
                        >
                            Eliminar
                        </button>
                        <button @click="closeModals" class="bg-gray-200 px-4 py-2 rounded">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Toasts -->
        <transition name="fade">
            <div v-if="toast.show" class="fixed bottom-6 right-6 z-50">
                <div
                    :class="[
                        'px-4 py-3 rounded shadow-lg text-white',
                        toast.type === 'success' ? 'bg-green-500' : 'bg-red-500',
                    ]"
                >
                    {{ toast.message }}
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, computed } from 'vue'
import { VueFlow, Handle } from '@vue-flow/core'
import '@vue-flow/core/dist/style.css'
import '@vue-flow/core/dist/theme-default.css'
import dagre from 'dagre'

const props = defineProps({
    questions: {
        type: Array,
        default: () => [],
    },
    conditions: {
        type: Array,
        default: () => [],
    },
    csrf: {
        type: String,
        required: true,
    },
})

const emit = defineEmits(['update:conditions'])

const nodes = ref([])
const edges = ref([])
const questions = ref([])
const conditions = ref([])
const initialLayoutApplied = ref(false)

const showQuestionModal = ref(false)
const showConditionModal = ref(false)
const showDeleteModal = ref(false)
const editingQuestion = reactive({})
const editingCondition = reactive({})
const conditionList = ref([])
const deleteTarget = reactive({ type: '', id: null })
const toast = reactive({ show: false, message: '', type: 'success' })

const dynamicOptions = ref([])
const currentQuestionId = ref(null)
const municipioSearchTerm = ref('')
const municipioSearchResults = ref([])
const isSearchingMunicipios = ref(false)
const municipioSearchTimeout = ref(null)

// Watchers para sincronizar props con estado interno
watch(
    () => props.questions,
    (newQuestions) => {
        questions.value = newQuestions
        if (questions.value.length > 0) {
            buildTreeNodes()
        }
    },
    { immediate: true, deep: true },
)

watch(
    () => props.conditions,
    (newConditions) => {
        conditions.value = newConditions
        if (questions.value.length > 0) {
            buildTreeNodes()
        }
    },
    { immediate: true, deep: true },
)

watch(
    () => conditionList.value,
    async (newConditionList) => {
        for (const condition of newConditionList) {
            if (condition.rules && Array.isArray(condition.rules)) {
                for (const rule of condition.rules) {
                    if (rule.question_id) {
                        await loadDynamicOptions(rule.question_id)
                    }
                }
            }
        }
    },
    { deep: true },
)

watch(
    () => conditionList.value,
    async (newConditionList) => {
        for (const condition of newConditionList) {
            if (condition.rules && Array.isArray(condition.rules)) {
                for (const rule of condition.rules) {
                    if (rule.question_id && rule.question_id !== currentQuestionId.value) {
                        currentQuestionId.value = rule.question_id
                        await loadDynamicOptions(rule.question_id)
                    }
                }
            }
        }
    },
    { deep: true },
)

watch(
    () => conditionList.value,
    async (newConditionList) => {
        for (const condition of newConditionList) {
            if (condition.rules && Array.isArray(condition.rules)) {
                for (const rule of condition.rules) {
                    if (rule.question_id) {
                        const question = questions.value.find((q) => q.id == rule.question_id)
                        if (
                            question &&
                            question.slug &&
                            ['comunidad_autonoma', 'provincia', 'municipio'].includes(question.slug)
                        ) {
                            await loadDynamicOptions(rule.question_id)
                        }
                    }
                }
            }
        }
    },
    { deep: true },
)

watch(
    dynamicOptions,
    (newOptions) => {
        if (newOptions.length > 0) {
            municipioSearchResults.value = []
        }
    },
    { deep: true },
)

function showToast(message, type = 'success') {
    toast.message = message
    toast.type = type
    toast.show = true
    setTimeout(() => (toast.show = false), 2500)
}

async function loadDynamicOptions(questionId) {
    if (!questionId) return

    const question = questions.value.find((q) => q.id == questionId)
    if (!question) return

    if (question.slug === 'comunidad_autonoma') {
        try {
            const response = await fetch('/admin/searchCCAA')
            const data = await response.json()
            dynamicOptions.value = data
        } catch (error) {
            dynamicOptions.value = []
        }
    } else if (question.slug === 'provincia') {
        try {
            const ccaaCondition = conditionList.value.find((condition) => {
                if (condition.rules && Array.isArray(condition.rules)) {
                    return condition.rules.some((rule) => {
                        const ruleQuestion = questions.value.find((q) => q.id == rule.question_id)
                        return ruleQuestion && ruleQuestion.slug === 'comunidad_autonoma'
                    })
                }
                return false
            })

            let url = '/admin/searchProvincias'
            if (ccaaCondition) {
                const ccaaRule = ccaaCondition.rules.find((rule) => {
                    const ruleQuestion = questions.value.find((q) => q.id == rule.question_id)
                    return ruleQuestion && ruleQuestion.slug === 'comunidad_autonoma'
                })
                if (ccaaRule) {
                    url += `?ccaa=${encodeURIComponent(ccaaRule.value)}`
                }
            }

            const response = await fetch(url)
            const data = await response.json()
            dynamicOptions.value = data
        } catch (error) {
            dynamicOptions.value = []
        }
    } else if (question.slug === 'municipio') {
        try {
            const provinciaCondition = conditionList.value.find((condition) => {
                if (condition.rules && Array.isArray(condition.rules)) {
                    return condition.rules.some((rule) => {
                        const ruleQuestion = questions.value.find((q) => q.id == rule.question_id)
                        return ruleQuestion && ruleQuestion.slug === 'provincia'
                    })
                }
                return false
            })

            let url = '/admin/searchMunicipios'
            if (provinciaCondition) {
                const provinciaRule = provinciaCondition.rules.find((rule) => {
                    const ruleQuestion = questions.value.find((q) => q.id == rule.question_id)
                    return ruleQuestion && ruleQuestion.slug === 'provincia'
                })
                if (provinciaRule) {
                    url += `?provincia=${encodeURIComponent(provinciaRule.value)}`
                }
            }

            url += (url.includes('?') ? '&' : '?') + 'limit=100'

            const response = await fetch(url)
            const data = await response.json()
            dynamicOptions.value = data
        } catch (error) {
            dynamicOptions.value = []
        }
    } else if (question.options && Array.isArray(question.options)) {
        dynamicOptions.value = question.options
    } else {
        dynamicOptions.value = []
    }
}

function getDynamicOptionsForRule(rule) {
    if (!rule.question_id) return []

    const question = questions.value.find((q) => q.id == rule.question_id)
    if (!question) return []

    if (question.slug === 'comunidad_autonoma') {
        return dynamicOptions.value
    } else if (question.slug === 'provincia') {
        const ccaaRule = conditionList.value.find((condition) => {
            if (condition.rules && Array.isArray(condition.rules)) {
                return condition.rules.some((r) => {
                    const rQuestion = questions.value.find((q) => q.id == r.question_id)
                    return rQuestion && rQuestion.slug === 'comunidad_autonoma'
                })
            }
            return false
        })
        if (!ccaaRule) return []
        return dynamicOptions.value
    } else if (question.slug === 'municipio') {
        return municipioSearchResults.value.length > 0
            ? municipioSearchResults.value
            : dynamicOptions.value
    } else if (question.options && Array.isArray(question.options)) {
        return question.options
    }

    return []
}

async function searchMunicipios(searchTerm, provincia = null) {
    if (!searchTerm || searchTerm.length < 2) {
        return []
    }

    try {
        let url = '/admin/searchMunicipios'
        const params = new URLSearchParams()

        if (provincia) {
            params.append('provincia', provincia)
        }
        params.append('search', searchTerm)
        params.append('limit', '50')

        const fullUrl = `${url}?${params.toString()}`
        const response = await fetch(fullUrl)

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }

        const data = await response.json()
        return data
    } catch (error) {
        return []
    }
}

async function handleMunicipioSearch(searchTerm, rule) {
    if (municipioSearchTimeout.value) {
        clearTimeout(municipioSearchTimeout.value)
    }

    if (!searchTerm || searchTerm.length < 2) {
        municipioSearchResults.value = []
        return
    }

    municipioSearchTimeout.value = setTimeout(async () => {
        isSearchingMunicipios.value = true

        try {
            const localResults = dynamicOptions.value.filter((option) =>
                option.toLowerCase().includes(searchTerm.toLowerCase()),
            )

            municipioSearchResults.value = localResults
        } catch (error) {
            console.error('Error en búsqueda de municipios:', error)
            municipioSearchResults.value = []
        } finally {
            isSearchingMunicipios.value = false
        }
    }, 300)
}

function buildTreeNodes() {
    if (questions.value.length === 0) {
        nodes.value = []
        edges.value = []
        return
    }

    // Crear nodos para cada pregunta
    nodes.value = questions.value.map((question, index) => {
        return {
            id: question.id.toString(),
            type: 'default',
            position: { x: 0, y: 0 }, // Se calculará con dagre
            data: {
                text: question.text,
                type: question.type,
                options: question.options || [],
                slug: question.slug || null,
                order: index + 1,
            },
        }
    })

    // Añadir nodo FIN
    nodes.value.push({
        id: 'FIN',
        type: 'default',
        position: { x: 0, y: 0 },
        data: {
            text: 'FIN',
            type: 'end',
        },
    })

    // Crear conexiones por defecto (flujo secuencial)
    edges.value = []
    for (let i = 0; i < nodes.value.length - 2; i++) {
        edges.value.push({
            id: `default-${nodes.value[i].id}-${nodes.value[i + 1].id}`,
            source: nodes.value[i].id,
            target: nodes.value[i + 1].id,
            type: 'default',
            style: { stroke: '#3b82f6', strokeWidth: 2 },
        })
    }

    // Conectar última pregunta con FIN
    if (nodes.value.length > 1) {
        edges.value.push({
            id: `default-${nodes.value[nodes.value.length - 2].id}-FIN`,
            source: nodes.value[nodes.value.length - 2].id,
            target: 'FIN',
            type: 'default',
            style: { stroke: '#3b82f6', strokeWidth: 2 },
        })
    }

    // Añadir conexiones condicionales
    conditions.value.forEach((condition) => {
        const target =
            condition.next_question_id === null ? 'FIN' : condition.next_question_id.toString()
        edges.value.push({
            id: `condition-${condition.question_id}-${target}`,
            source: condition.question_id.toString(),
            target: target,
            type: 'default',
            style: { stroke: '#ef4444', strokeWidth: 3 },
        })
    })

    applyDagreLayout()
}

function applyDagreLayout(direction = 'TB') {
    if (nodes.value.length === 0) return

    const g = new dagre.graphlib.Graph()
    g.setDefaultEdgeLabel(() => ({}))
    g.setGraph({
        rankdir: direction,
        nodesep: 120,
        edgesep: 60,
        ranksep: 200,
    })

    nodes.value.forEach((node) => {
        g.setNode(node.id, { width: 280, height: 120 })
    })
    edges.value.forEach((edge) => {
        g.setEdge(edge.source, edge.target)
    })

    dagre.layout(g)

    nodes.value.forEach((node) => {
        const nodeWithPosition = g.node(node.id)
        node.position = {
            x: nodeWithPosition.x - nodeWithPosition.width / 2,
            y: nodeWithPosition.y - nodeWithPosition.height / 2,
        }
    })
}

async function openConditionModal(questionId) {
    const question = nodes.value.find((n) => n.id === questionId)
    if (!question) return

    editingCondition.question_id = questionId
    conditionList.value = []

    // Cargar condiciones existentes para esta pregunta
    const existingConditions = conditions.value.filter((c) => c.question_id == questionId)

    existingConditions.forEach((c) => {
        const conditionCopy = { ...c }

        // Si es una condición compuesta, usar composite_rules
        if (conditionCopy.is_composite && conditionCopy.composite_rules) {
            conditionCopy.rules = conditionCopy.composite_rules.map((rule) => {
                let displayValue = rule.value
                // Normalizar operador
                let operator = rule.operator || '=='
                if (operator === '=') {
                    operator = '=='
                }

                if (rule.question_id) {
                    const question = questions.value.find((q) => q.id == rule.question_id)
                    if (
                        question &&
                        ['select', 'multiple'].includes(question.type) &&
                        question.options &&
                        Array.isArray(question.options)
                    ) {
                        // Si el valor es un número (índice legacy), convertir a texto
                        // Si ya es texto (formato nuevo), mantenerlo
                        const optionIndex =
                            typeof rule.value === 'number' ? rule.value : parseInt(rule.value)
                        if (
                            question.type === 'multiple' &&
                            (optionIndex === -1 || rule.value === null)
                        ) {
                            displayValue = 'Ninguna de las anteriores'
                        } else if (
                            !isNaN(optionIndex) &&
                            optionIndex >= 0 &&
                            optionIndex < question.options.length
                        ) {
                            // Es un índice legacy, convertir a texto
                            displayValue = question.options[optionIndex]
                        }
                        // Si no es un índice válido, mantener el valor tal cual (ya es texto en formato nuevo)
                    }
                }

                // Normalizar valores booleanos al cargar
                let finalValue = displayValue
                if (rule.question_id) {
                    const question = questions.value.find((q) => q.id == rule.question_id)
                    if (question && question.type === 'boolean') {
                        // Normalizar valores booleanos: "1" o 1 -> 1, "0" o 0 -> 0
                        if (
                            finalValue === '1' ||
                            finalValue === 1 ||
                            finalValue === 'true' ||
                            finalValue === true
                        ) {
                            finalValue = 1
                        } else if (
                            finalValue === '0' ||
                            finalValue === 0 ||
                            finalValue === 'false' ||
                            finalValue === false
                        ) {
                            finalValue = 0
                        }
                    }
                }

                return {
                    ...rule,
                    operator: operator,
                    value: finalValue,
                    originalValue: rule.value,
                    connector: conditionCopy.composite_logic || 'AND',
                }
            })
        } else if (!conditionCopy.rules) {
            let displayValue = conditionCopy.value
            // Normalizar operador
            let operator = conditionCopy.operator || '=='
            if (operator === '=') {
                operator = '=='
            }

            if (conditionCopy.question_id) {
                const question = questions.value.find((q) => q.id == conditionCopy.question_id)
                if (
                    question &&
                    ['select', 'multiple'].includes(question.type) &&
                    question.options &&
                    Array.isArray(question.options)
                ) {
                    // Si el valor es un número (índice legacy), convertir a texto
                    const optionIndex =
                        typeof conditionCopy.value === 'number'
                            ? conditionCopy.value
                            : parseInt(conditionCopy.value)
                    if (
                        question.type === 'multiple' &&
                        (optionIndex === -1 || conditionCopy.value === null)
                    ) {
                        displayValue = 'Ninguna de las anteriores'
                    } else if (
                        !isNaN(optionIndex) &&
                        optionIndex >= 0 &&
                        optionIndex < question.options.length
                    ) {
                        // Es un índice legacy, convertir a texto
                        displayValue = question.options[optionIndex]
                    }
                    // Si no es un índice válido, mantener el valor tal cual (ya es texto en formato nuevo)
                }
            }

            // Normalizar valores booleanos al cargar
            let finalValue = displayValue
            if (conditionCopy.question_id) {
                const question = questions.value.find((q) => q.id == conditionCopy.question_id)
                if (question && question.type === 'boolean') {
                    // Normalizar valores booleanos: "1" o 1 -> 1, "0" o 0 -> 0
                    if (
                        finalValue === '1' ||
                        finalValue === 1 ||
                        finalValue === 'true' ||
                        finalValue === true
                    ) {
                        finalValue = 1
                    } else if (
                        finalValue === '0' ||
                        finalValue === 0 ||
                        finalValue === 'false' ||
                        finalValue === false
                    ) {
                        finalValue = 0
                    }
                }
            }

            conditionCopy.rules = [
                {
                    question_id: conditionCopy.question_id,
                    operator: operator,
                    value: finalValue,
                    originalValue: conditionCopy.value,
                    connector: 'AND',
                },
            ]
        } else {
            conditionCopy.rules = conditionCopy.rules.map((rule) => {
                let displayValue = rule.value
                // Normalizar operador
                let operator = rule.operator || '=='
                if (operator === '=') {
                    operator = '=='
                }

                if (rule.question_id) {
                    const question = questions.value.find((q) => q.id == rule.question_id)
                    if (
                        question &&
                        ['select', 'multiple'].includes(question.type) &&
                        question.options &&
                        Array.isArray(question.options)
                    ) {
                        // Si el valor es un número (índice legacy), convertir a texto
                        const optionIndex =
                            typeof rule.value === 'number' ? rule.value : parseInt(rule.value)
                        if (
                            question.type === 'multiple' &&
                            (optionIndex === -1 || rule.value === null)
                        ) {
                            displayValue = 'Ninguna de las anteriores'
                        } else if (
                            !isNaN(optionIndex) &&
                            optionIndex >= 0 &&
                            optionIndex < question.options.length
                        ) {
                            // Es un índice legacy, convertir a texto
                            displayValue = question.options[optionIndex]
                        }
                        // Si no es un índice válido, mantener el valor tal cual (ya es texto en formato nuevo)
                    }
                }

                // Normalizar valores booleanos al cargar
                let finalValue = displayValue
                if (rule.question_id) {
                    const question = questions.value.find((q) => q.id == rule.question_id)
                    if (question && question.type === 'boolean') {
                        // Normalizar valores booleanos: "1" o 1 -> 1, "0" o 0 -> 0
                        if (
                            finalValue === '1' ||
                            finalValue === 1 ||
                            finalValue === 'true' ||
                            finalValue === true
                        ) {
                            finalValue = 1
                        } else if (
                            finalValue === '0' ||
                            finalValue === 0 ||
                            finalValue === 'false' ||
                            finalValue === false
                        ) {
                            finalValue = 0
                        }
                    }
                }

                return {
                    ...rule,
                    operator: operator,
                    value: finalValue,
                    originalValue: rule.value,
                }
            })
        }

        conditionList.value.push(conditionCopy)
    })

    for (const condition of conditionList.value) {
        if (condition.rules && Array.isArray(condition.rules)) {
            for (const rule of condition.rules) {
                if (rule.question_id) {
                    await loadDynamicOptions(rule.question_id)
                }
            }
        }
    }

    municipioSearchTerm.value = ''
    municipioSearchResults.value = []
    isSearchingMunicipios.value = false
    if (municipioSearchTimeout.value) {
        clearTimeout(municipioSearchTimeout.value)
        municipioSearchTimeout.value = null
    }

    for (const condition of conditionList.value) {
        if (condition.rules && Array.isArray(condition.rules)) {
            for (const rule of condition.rules) {
                if (rule.question_id) {
                    const question = questions.value.find((q) => q.id == rule.question_id)
                    if (question && question.slug === 'municipio' && rule.value) {
                        municipioSearchTerm.value = rule.value
                        municipioSearchResults.value = [rule.value]
                        break
                    }
                }
            }
        }
    }

    showConditionModal.value = true
}

function closeModals() {
    showQuestionModal.value = false
    showConditionModal.value = false
    showDeleteModal.value = false
    conditionList.value = []
    editingCondition.question_id = null
    municipioSearchTerm.value = ''
    municipioSearchResults.value = []
    isSearchingMunicipios.value = false
    if (municipioSearchTimeout.value) {
        clearTimeout(municipioSearchTimeout.value)
        municipioSearchTimeout.value = null
    }
}

function getSourceQuestionText() {
    const question = nodes.value.find((n) => n.id === editingCondition.question_id)
    return question ? question.data.text : ''
}

function getSourceQuestionType() {
    const question = nodes.value.find((n) => n.id === editingCondition.question_id)
    return question ? question.data.type : ''
}

function getSourceQuestionOptions() {
    const question = nodes.value.find((n) => n.id === editingCondition.question_id)
    return question ? question.data.options || [] : []
}

function getAvailableDestinations() {
    const destinations = nodes.value
        .filter((n) => n.id !== editingCondition.question_id && n.id !== 'FIN')
        .map((n) => ({ id: n.id, text: n.data.text }))

    // Añadir el nodo FIN como opción (solo una vez)
    destinations.push({ id: null, text: 'FIN del cuestionario' })

    return destinations
}

function addNewCondition() {
    conditionList.value.push({
        question_id: editingCondition.question_id,
        next_question_id: '',
        rules: [], // Array de reglas para condiciones compuestas
    })
}

function removeCondition(index) {
    conditionList.value.splice(index, 1)
}

function addConditionRule(condition) {
    if (!condition.rules) {
        condition.rules = []
    }
    condition.rules.push({
        question_id: '',
        operator: '==',
        value: '',
        connector: 'AND',
    })
}

function removeConditionRule(condition, ruleIndex) {
    condition.rules.splice(ruleIndex, 1)
}

function getAvailableQuestionsForCondition() {
    // Obtener todas las preguntas hasta la pregunta actual (incluyendo la actual)
    const currentQuestionIndex = questions.value.findIndex(
        (q) => q.id.toString() === editingCondition.question_id.toString(),
    )
    if (currentQuestionIndex === -1) return []

    return questions.value.slice(0, currentQuestionIndex + 1).map((q, index) => ({
        id: q.id,
        text: q.text,
        order: index + 1,
    }))
}

function getQuestionType(questionId) {
    const nodeQuestion = nodes.value.find((n) => n.id == questionId)
    if (nodeQuestion) {
        return nodeQuestion.data.type
    }

    const question = questions.value.find((q) => q.id.toString() === questionId.toString())
    return question ? question.type : ''
}

function getQuestionSlug(questionId) {
    const nodeQuestion = nodes.value.find((n) => n.id == questionId)
    if (nodeQuestion) {
        return nodeQuestion.data.slug
    }

    const question = questions.value.find((q) => q.id.toString() === questionId.toString())
    return question ? question.slug : null
}

function getQuestionOptions(questionId) {
    let options = []
    const questionType = getQuestionType(questionId)

    const nodeQuestion = nodes.value.find((n) => n.id == questionId)
    if (nodeQuestion) {
        if (
            nodeQuestion.data.slug &&
            ['comunidad_autonoma', 'provincia', 'municipio'].includes(nodeQuestion.data.slug)
        ) {
            options = dynamicOptions.value
        } else {
            options = nodeQuestion.data.options || []
        }
    } else {
        const question = questions.value.find((q) => q.id.toString() === questionId.toString())
        if (question) {
            if (
                question.slug &&
                ['comunidad_autonoma', 'provincia', 'municipio'].includes(question.slug)
            ) {
                options = dynamicOptions.value
            } else {
                options = question.options || []
            }
        }
    }

    if (questionType === 'multiple' && Array.isArray(options)) {
        const hasNoneOption = options.some((opt) => {
            const text = typeof opt === 'string' ? opt : opt.text || opt
            if (!text) return false
            const lower = String(text).toLowerCase()
            return (
                lower.includes('ninguna') && (lower.includes('anterior') || lower.includes('otra'))
            )
        })

        if (!hasNoneOption) {
            options = [...options, 'Ninguna de las anteriores']
        }
    }

    return options
}

function getOperatorsForQuestion(questionId) {
    let type = getQuestionType(questionId)
    const operators = {
        string: [
            { value: '==', label: 'Igual a' },
            { value: 'contains', label: 'Contiene' },
            { value: 'starts_with', label: 'Empieza por' },
            { value: 'ends_with', label: 'Termina por' },
        ],
        text: [
            { value: '==', label: 'Igual a' },
            { value: 'contains', label: 'Contiene' },
            { value: 'starts_with', label: 'Empieza por' },
            { value: 'ends_with', label: 'Termina por' },
        ],
        integer: [
            { value: '==', label: 'Igual a' },
            { value: '>', label: 'Mayor que' },
            { value: '<', label: 'Menor que' },
            { value: '>=', label: 'Mayor o igual que' },
            { value: '<=', label: 'Menor o igual que' },
        ],
        number: [
            { value: '==', label: 'Igual a' },
            { value: '>', label: 'Mayor que' },
            { value: '<', label: 'Menor que' },
            { value: '>=', label: 'Mayor o igual que' },
            { value: '<=', label: 'Menor o igual que' },
        ],
        boolean: [{ value: '==', label: 'Igual a' }],
        select: [
            { value: '==', label: 'Igual a' },
            { value: '!=', label: 'Diferente de' },
        ],
        multiple: [
            { value: '==', label: 'Igual a' },
            { value: '!=', label: 'Distinto de' },
        ],
        date: [
            { value: '==', label: 'Igual a' },
            { value: '>', label: 'Después de' },
            { value: '<', label: 'Antes de' },
        ],
    }
    if (operators[type]) return operators[type]
    if (!type && questionId) {
        const question = questions.value.find((q) => q.id.toString() === questionId.toString())
        if (question && Array.isArray(question.options) && question.options.length > 1) {
            return operators.multiple
        }
    }
    return operators.string
}

function saveAllConditions() {
    try {
        // Validar condiciones
        for (const condition of conditionList.value) {
            if (!condition.rules || condition.rules.length === 0) {
                showToast('Por favor añade al menos una regla para cada condición', 'error')
                return
            }

            // Validar cada regla
            for (const rule of condition.rules) {
                if (!rule.question_id || !rule.operator || rule.value === '') {
                    showToast('Por favor completa todos los campos de las reglas', 'error')
                    return
                }
            }

            // next_question_id puede ser null cuando va a FIN
            if (condition.next_question_id === '') {
                showToast('Por favor selecciona un destino para la condición', 'error')
                return
            }
        }

        // Procesar y normalizar valores antes de guardar
        // IMPORTANTE: Guardamos valores lógicos (texto/clave), NO índices de posición
        conditionList.value.forEach((condition) => {
            condition.rules.forEach((rule) => {
                if (rule.question_id) {
                    const question = questions.value.find((q) => q.id == rule.question_id)

                    // Para select y multiple: guardar el valor lógico (texto/clave), no el índice
                    // El valor ya viene del select como texto, así que lo mantenemos tal cual
                    if (
                        question &&
                        ['select', 'multiple'].includes(question.type) &&
                        question.options &&
                        Array.isArray(question.options)
                    ) {
                        // Si el valor es "Ninguna de las anteriores" en multiple, usar null o un marcador especial
                        if (question.type === 'multiple') {
                            const ruleValueStr = String(rule.value).toLowerCase()
                            const isNoneOption =
                                ruleValueStr.includes('ninguna') &&
                                (ruleValueStr.includes('anterior') || ruleValueStr.includes('otra'))

                            if (isNoneOption) {
                                // Guardar como null para representar "ninguna opción seleccionada"
                                rule.value = null
                            }
                            // Si no es "ninguna", mantener el valor tal cual (ya es el texto de la opción)
                        }
                        // Para select: mantener el valor tal cual (ya es el texto de la opción)
                    }
                }

                // Normalizar valores booleanos y numéricos
                if (rule.value === 'true' || rule.value === '1') {
                    rule.value = 1
                } else if (rule.value === 'false' || rule.value === '0') {
                    rule.value = 0
                } else if (rule.value !== null && !isNaN(rule.value) && rule.value !== '') {
                    // Solo convertir a número si no es un string de texto (para evitar convertir "Madrid" a NaN)
                    const numValue = parseFloat(rule.value)
                    if (!isNaN(numValue) && String(rule.value).trim() === String(numValue)) {
                        rule.value = numValue
                    }
                }

                // Normalizar operador: convertir '=' a '==' para consistencia
                if (rule.operator === '=') {
                    rule.operator = '=='
                }
            })
        })

        // Actualizar condiciones en el componente padre
        const updatedConditions = [...conditions.value]

        // Eliminar condiciones existentes para esta pregunta
        const filteredConditions = updatedConditions.filter(
            (c) => c.question_id != editingCondition.question_id,
        )

        // Añadir nuevas condiciones
        conditionList.value.forEach((condition) => {
            filteredConditions.push({
                ...condition,
                id: condition.id || Date.now() + Math.random(), // ID temporal
            })
        })

        conditions.value = filteredConditions
        emit('update:conditions', filteredConditions)

        // Actualizar edges en el flujo
        updateFlowEdges()

        closeModals()
        showToast('Condiciones guardadas correctamente', 'success')
    } catch (error) {
        console.error('Error guardando condiciones:', error)
        showToast('Error al guardar las condiciones', 'error')
    }
}

function updateFlowEdges() {
    // Eliminar edges existentes para esta pregunta
    edges.value = edges.value.filter(
        (edge) => !edge.source.startsWith(editingCondition.question_id),
    )

    // Añadir nuevos edges basados en las condiciones
    conditionList.value.forEach((condition) => {
        const target = condition.next_question_id === null ? 'FIN' : condition.next_question_id
        edges.value.push({
            id: `condition-${editingCondition.question_id}-${target}`,
            source: editingCondition.question_id,
            target: target,
            type: 'default',
            style: { stroke: '#ef4444', strokeWidth: 3 },
        })
    })
}

function confirmDeleteCondition(edgeId) {
    deleteTarget.type = 'condition'
    deleteTarget.id = edgeId
    showDeleteModal.value = true
}

function confirmDeleteAllConditions() {
    deleteTarget.type = 'all_conditions'
    deleteTarget.id = null
    showDeleteModal.value = true
}

function deleteConfirmed() {
    try {
        if (deleteTarget.type === 'all_conditions') {
            conditions.value = []
            edges.value = edges.value.filter((edge) => edge.style.stroke === '#3b82f6')
            emit('update:conditions', [])
            showToast('Todas las condiciones eliminadas', 'success')
        } else if (deleteTarget.type === 'condition') {
            // Eliminar edge específico
            edges.value = edges.value.filter((edge) => edge.id !== deleteTarget.id)
            showToast('Condición eliminada', 'success')
        }
    } catch (error) {
        console.error('Error eliminando condición:', error)
        showToast('Error al eliminar la condición', 'error')
    } finally {
        closeModals()
    }
}

// Lifecycle
onMounted(() => {
    if (questions.value.length > 0) {
        buildTreeNodes()
    }
})

defineExpose({
    handleMunicipioSearch,
    searchMunicipios,
    municipioSearchTerm,
    municipioSearchResults,
    isSearchingMunicipios,
    getQuestionSlug,
})
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
