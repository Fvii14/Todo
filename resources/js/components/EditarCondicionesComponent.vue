<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="w-full min-h-[600px] relative">
            <!-- Breadcrumbs -->
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <button
                            @click="goBack"
                            :class="[
                                'inline-flex items-center text-sm font-medium transition-colors',
                                hasUnsavedChanges
                                    ? 'text-orange-600 hover:text-orange-700'
                                    : 'text-gray-700 hover:text-blue-600',
                            ]"
                        >
                            <svg
                                class="w-4 h-4 mr-2"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                            Volver
                            <span
                                v-if="hasUnsavedChanges"
                                class="ml-1 text-xs bg-orange-100 text-orange-800 px-2 py-0.5 rounded-full"
                            >
                                ⚠️ Sin guardar
                            </span>
                        </button>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg
                                class="w-6 h-6 text-gray-400"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                            <span
                                class="ml-1 text-sm font-medium text-gray-500 md:ml-2"
                                >{{ itemName }}</span
                            >
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Header con información -->
            <div
                class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold"
                    >
                        🔀
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-blue-900">
                            {{ title }}
                        </h3>
                        <p class="text-sm text-blue-700">{{ subtitle }}</p>
                        <p class="text-sm font-medium text-blue-800 mt-1">
                            {{ itemName }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Mensaje de formato legacy -->
            <div
                v-if="isLegacyFormat"
                class="mb-6 p-4 bg-orange-50 border border-orange-200 rounded-xl"
            >
                <div class="flex items-start gap-3">
                    <div
                        class="w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0 mt-0.5"
                    >
                        ⚠️
                    </div>
                    <div>
                        <h4 class="font-semibold text-orange-800 mb-1">
                            Formato Antiguo Detectado
                        </h4>
                        <p class="text-sm text-orange-700">
                            Aunque funciona correctamente, es recomendable crear
                            el cuestionario de 0 usando los wizards para mejor
                            mantenimiento y funcionalidad.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Flujo de condiciones -->
            <div class="mb-6">
                <div
                    class="w-full h-[800px] bg-white rounded-xl border overflow-auto"
                    style="
                        background-image: radial-gradient(
                            circle,
                            #e5e7eb 1px,
                            transparent 1px
                        );
                        background-size: 20px 20px;
                    "
                >
                    <VueFlow
                        v-model:nodes="nodes"
                        v-model:edges="edges"
                        :fit-view="true"
                        :fit-view-options="{
                            padding: 0.2,
                            includeHiddenNodes: false,
                            minZoom: 0.3,
                        }"
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

                                <div class="font-bold text-base mb-2">
                                    {{ data.text }}
                                </div>
                                <div
                                    v-if="id !== 'FIN'"
                                    class="text-xs text-gray-500 mb-2"
                                >
                                    Tipo: {{ data.type }}
                                </div>
                                <div
                                    v-if="
                                        data.options &&
                                        data.options.length &&
                                        id !== 'FIN'
                                    "
                                    class="text-xs text-gray-400 mb-3"
                                >
                                    Opciones: {{ data.options.join(", ") }}
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
                <div
                    v-if="!nodes.length"
                    class="text-gray-400 text-center py-12"
                >
                    No hay preguntas aún.
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-between items-center mb-6">
                <button
                    @click="confirmDeleteAllConditions"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
                >
                    <i class="fas fa-trash mr-2"></i>
                    Eliminar todas las condiciones
                </button>

                <button
                    @click="saveConditions"
                    :disabled="conditions.length === 0"
                    :class="[
                        'px-4 py-2 rounded text-sm font-medium transition-colors',
                        hasUnsavedChanges
                            ? 'bg-orange-600 text-white hover:bg-orange-700'
                            : 'bg-green-600 text-white hover:bg-green-700',
                        conditions.length === 0
                            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                            : '',
                    ]"
                >
                    <i class="fas fa-save mr-2"></i>
                    {{
                        hasUnsavedChanges
                            ? "Guardar Cambios"
                            : "Guardar Condiciones"
                    }}
                    <span
                        v-if="hasUnsavedChanges"
                        class="ml-1 text-xs bg-orange-100 text-orange-800 px-2 py-0.5 rounded-full"
                    >
                        ⚠️
                    </span>
                </button>
            </div>
        </div>

        <!-- Modal de condiciones -->
        <transition name="fade">
            <div
                v-if="showConditionModal"
                class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
                @click.self="closeModals"
            >
                <div
                    class="bg-white rounded-xl shadow-lg p-8 w-full max-w-4xl relative animate-fade-in"
                >
                    <button
                        @click="closeModals"
                        class="absolute top-2 right-2 text-gray-400 hover:text-gray-700"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                    <h3 class="text-lg font-bold mb-4">
                        {{ editingCondition.id ? "Editar" : "Añadir" }} saltos
                    </h3>
                    <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                        <div class="text-sm font-medium text-blue-800">
                            Pregunta origen:
                        </div>
                        <div class="text-sm text-blue-600">
                            {{ getSourceQuestionText() }}
                        </div>
                        <div class="text-xs text-blue-500 mt-1">
                            Tipo: {{ getSourceQuestionType() }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-medium text-gray-700">
                                Saltos configurados:
                            </h4>
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
                                <div
                                    class="flex justify-between items-start mb-3"
                                >
                                    <div class="flex items-center">
                                        <h5 class="font-medium text-gray-700">
                                            Salto {{ index + 1 }}
                                        </h5>
                                        <span
                                            v-if="
                                                condition.rules &&
                                                condition.rules.length > 1
                                            "
                                            class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full"
                                        >
                                            Compuesta ({{
                                                condition.rules.length
                                            }}
                                            reglas)
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
                                    <div
                                        class="flex items-center justify-between mb-2"
                                    >
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Condiciones del salto:</label
                                        >
                                        <button
                                            @click="addConditionRule(condition)"
                                            class="text-blue-600 hover:text-blue-800 text-sm"
                                        >
                                            <i class="fas fa-plus mr-1"></i
                                            >Añadir condición
                                        </button>
                                    </div>

                                    <div
                                        v-if="
                                            condition.rules &&
                                            condition.rules.length > 0
                                        "
                                        class="space-y-3"
                                    >
                                        <div
                                            v-for="(
                                                rule, ruleIndex
                                            ) in condition.rules"
                                            :key="ruleIndex"
                                            class="border-l-4 border-blue-200 pl-3 bg-white rounded p-3"
                                        >
                                            <div
                                                class="flex items-center justify-between mb-2"
                                            >
                                                <span
                                                    class="text-xs font-medium text-gray-600"
                                                    >Condición
                                                    {{ ruleIndex + 1 }}</span
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

                                            <div
                                                class="grid grid-cols-1 md:grid-cols-4 gap-3"
                                            >
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-gray-600 mb-1"
                                                        >Pregunta</label
                                                    >
                                                    <select
                                                        v-model="
                                                            rule.question_id
                                                        "
                                                        class="w-full border rounded px-2 py-1 text-sm"
                                                    >
                                                        <option value="">
                                                            Selecciona pregunta
                                                        </option>
                                                        <option
                                                            v-for="question in questions"
                                                            :key="question.id"
                                                            :value="question.id"
                                                        >
                                                            {{ question.text }}
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
                                                            :key="op.value"
                                                            :value="op.value"
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
                                                        <option :value="1">
                                                            Sí
                                                        </option>
                                                        <option :value="0">
                                                            No
                                                        </option>
                                                    </select>
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
                                                            :key="option"
                                                            :value="option"
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
                                                        <option value="AND">
                                                            Y (AND)
                                                        </option>
                                                        <option value="OR">
                                                            O (OR)
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Destino del salto:</label
                                    >
                                    <select
                                        v-model="condition.next_question_id"
                                        class="w-full border rounded px-3 py-2"
                                    >
                                        <option value="">
                                            Selecciona destino
                                        </option>
                                        <option value="null">
                                            FIN del cuestionario
                                        </option>
                                        <option
                                            v-for="question in questions"
                                            :key="question.id"
                                            :value="question.id"
                                        >
                                            {{ question.text }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button
                            @click="saveConditionModal"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        >
                            Guardar saltos
                        </button>
                        <button
                            @click="closeModals"
                            class="bg-gray-200 px-4 py-2 rounded"
                        >
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Modal de confirmación de eliminación -->
        <transition name="fade">
            <div
                v-if="showDeleteModal"
                class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
            >
                <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
                    <h3 class="text-lg font-bold mb-4">
                        Confirmar eliminación
                    </h3>
                    <p class="text-gray-600 mb-6">
                        {{
                            deleteTarget.type === "all_conditions"
                                ? "¿Estás seguro de que quieres eliminar todas las condiciones? Esta acción no se puede deshacer."
                                : "¿Estás seguro de que quieres eliminar esta condición?"
                        }}
                    </p>
                    <div class="flex justify-end gap-3">
                        <button
                            @click="closeModals"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="deleteConfirmed"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                        >
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Toast de notificación -->
        <div
            v-if="toast.show"
            :class="[
                'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300',
                toast.type === 'success'
                    ? 'bg-green-500 text-white'
                    : toast.type === 'error'
                      ? 'bg-red-500 text-white'
                      : toast.type === 'info'
                        ? 'bg-blue-500 text-white'
                        : 'bg-blue-500 text-white',
            ]"
        >
            <div class="flex items-center">
                <i
                    :class="[
                        'mr-2',
                        toast.type === 'success'
                            ? 'fas fa-check'
                            : toast.type === 'error'
                              ? 'fas fa-exclamation-triangle'
                              : toast.type === 'info'
                                ? 'fas fa-spinner fa-spin'
                                : 'fas fa-info',
                    ]"
                ></i>
                <span>{{ toast.message }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, computed } from "vue";
import { VueFlow, Handle } from "@vue-flow/core";
import "@vue-flow/core/dist/style.css";
import "@vue-flow/core/dist/theme-default.css";
import dagre from "dagre";

const props = defineProps({
    itemId: {
        type: [String, Number],
        required: true,
    },
    itemName: {
        type: String,
        required: true,
    },
    itemType: {
        type: String,
        default: "cuestionario",
    },
    questions: {
        type: Array,
        default: () => [],
    },
    existingConditions: {
        type: Array,
        default: () => [],
    },
    allQuestions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["goBack", "conditionsUpdated"]);

// Variables reactivas
const nodes = ref([]);
const edges = ref([]);
const conditions = ref([]);
const isLegacyFormat = ref(false);
const originalConditions = ref([]);

const showConditionModal = ref(false);
const showDeleteModal = ref(false);
const editingCondition = reactive({});
const conditionList = ref([]);
const deleteTarget = reactive({ type: "", id: null });
const toast = reactive({ show: false, message: "", type: "success" });

// Computed properties
const title = computed(() => {
    return props.itemType === "cuestionario"
        ? "Editar Condiciones del Cuestionario"
        : "Editar Condiciones";
});

const subtitle = computed(() => {
    return props.itemType === "cuestionario"
        ? "Configura los saltos condicionales entre preguntas para crear flujos dinámicos"
        : "Configura las condiciones y validaciones";
});

const hasUnsavedChanges = computed(() => {
    if (originalConditions.value.length !== conditions.value.length) {
        return true;
    }

    for (let i = 0; i < conditions.value.length; i++) {
        const current = conditions.value[i];
        const original = originalConditions.value[i];

        if (!original) return true;

        if (
            current.question_id !== original.question_id ||
            current.next_question_id !== original.next_question_id ||
            current.operator !== original.operator ||
            JSON.stringify(current.value) !== JSON.stringify(original.value) ||
            JSON.stringify(current.rules) !== JSON.stringify(original.rules) ||
            current.is_composite !== original.is_composite ||
            current.composite_logic !== original.composite_logic ||
            JSON.stringify(current.composite_rules) !==
                JSON.stringify(original.composite_rules)
        ) {
            return true;
        }
    }

    return false;
});

// Watchers
watch(
    () => props.existingConditions,
    (newConditions) => {
        if (newConditions && newConditions.length > 0) {
            conditions.value = [...newConditions];
            originalConditions.value = JSON.parse(
                JSON.stringify(conditions.value),
            );
        }
    },
    { immediate: true, deep: true },
);

watch(
    () => props.questions,
    (newQuestions) => {
        if (newQuestions.length > 0) {
            buildTreeNodes();
        }
    },
    { immediate: true, deep: true },
);

// Watcher para reconstruir el flujo cuando cambien las condiciones
watch(
    () => conditions.value,
    () => {
        if (props.questions.length > 0) {
            buildTreeNodes();
        }
    },
    { deep: true },
);

// Métodos

function buildTreeNodes() {
    if (props.questions.length === 0) {
        nodes.value = [];
        edges.value = [];
        return;
    }

    // Crear nodos para cada pregunta
    nodes.value = props.questions.map((question, index) => {
        return {
            id: question.id.toString(),
            type: "default",
            position: { x: 0, y: 0 }, // Se calculará con dagre
            data: {
                text: question.text,
                type: question.type,
                options: question.options || [],
                order: index + 1,
            },
        };
    });

    // Añadir nodo FIN
    nodes.value.push({
        id: "FIN",
        type: "default",
        position: { x: 0, y: 0 },
        data: {
            text: "FIN",
            type: "end",
        },
    });

    // Crear conexiones por defecto (flujo secuencial)
    edges.value = [];
    for (let i = 0; i < nodes.value.length - 2; i++) {
        edges.value.push({
            id: `default-${nodes.value[i].id}-${nodes.value[i + 1].id}`,
            source: nodes.value[i].id,
            target: nodes.value[i + 1].id,
            type: "default",
            style: { stroke: "#3b82f6", strokeWidth: 2 },
        });
    }

    // Conectar última pregunta con FIN
    if (nodes.value.length > 1) {
        edges.value.push({
            id: `default-${nodes.value.length - 2}-FIN`,
            source: nodes.value[nodes.value.length - 2].id,
            target: "FIN",
            type: "default",
            style: { stroke: "#3b82f6", strokeWidth: 2 },
        });
    }

    // Añadir conexiones condicionales (líneas rojas)
    conditions.value.forEach((condition) => {
        const target =
            condition.next_question_id === null
                ? "FIN"
                : condition.next_question_id.toString();
        edges.value.push({
            id: `condition-${condition.question_id}-${target}`,
            source: condition.question_id.toString(),
            target: target,
            type: "default",
            style: { stroke: "#ef4444", strokeWidth: 3 },
        });
    });

    applyDagreLayout();
}

function applyDagreLayout(direction = "TB") {
    if (nodes.value.length === 0) return;

    const g = new dagre.graphlib.Graph();
    g.setDefaultEdgeLabel(() => ({}));
    g.setGraph({
        rankdir: direction,
        nodesep: 120,
        edgesep: 60,
        ranksep: 200,
    });

    nodes.value.forEach((node) => {
        g.setNode(node.id, { width: 280, height: 120 });
    });
    edges.value.forEach((edge) => {
        g.setEdge(edge.source, edge.target);
    });

    dagre.layout(g);

    nodes.value.forEach((node) => {
        const nodeWithPosition = g.node(node.id);
        node.position = {
            x: nodeWithPosition.x - nodeWithPosition.width / 2,
            y: nodeWithPosition.y - nodeWithPosition.height / 2,
        };
    });
}

function openConditionModal(questionId) {
    const question = nodes.value.find((n) => n.id === questionId);
    if (!question) return;

    editingCondition.question_id = questionId;
    conditionList.value = [];

    // Cargar condiciones existentes para esta pregunta
    const existingConditions = conditions.value.filter(
        (c) => c.question_id == questionId,
    );

    existingConditions.forEach((c) => {
        const conditionCopy = { ...c };

        // Si es una condición compuesta, usar composite_rules
        if (conditionCopy.is_composite && conditionCopy.composite_rules) {
            conditionCopy.rules = conditionCopy.composite_rules.map((rule) => ({
                ...rule,
                connector: conditionCopy.composite_logic || "AND",
            }));
        } else if (!conditionCopy.rules) {
            // Condición simple - convertirla al formato de reglas
            conditionCopy.rules = [
                {
                    question_id: conditionCopy.question_id,
                    operator: conditionCopy.operator || "=",
                    value: conditionCopy.value || "",
                    connector: "AND",
                },
            ];
        }

        // Asegurar que next_question_id esté correctamente formateado
        if (conditionCopy.next_question_id === null) {
            conditionCopy.next_question_id = "null"; // Para el select del modal
        }

        // Asegurar que los valores booleanos se manejen correctamente
        if (conditionCopy.rules && conditionCopy.rules.length > 0) {
            conditionCopy.rules.forEach((rule) => {
                if (getQuestionType(rule.question_id) === "boolean") {
                    // Convertir a número para que el select funcione correctamente
                    rule.value =
                        rule.value === true ||
                        rule.value === "1" ||
                        rule.value === 1
                            ? 1
                            : 0;
                } else if (getQuestionType(rule.question_id) === "select" || getQuestionType(rule.question_id) === "multiple") {
                    const question = props.questions.find(q => q.id == rule.question_id);
                    if (question && question.options && Array.isArray(question.options)) {
                        const optionIndex = parseInt(rule.value);
                        if (!isNaN(optionIndex) && optionIndex >= 0 && optionIndex < question.options.length) {
                            rule.value = question.options[optionIndex];
                            rule.originalValue = optionIndex;
                        }
                    }
                }
            });
        }

        conditionList.value.push(conditionCopy);
    });

    showConditionModal.value = true;
}

function closeModals() {
    showConditionModal.value = false;
    showDeleteModal.value = false;
    editingCondition.question_id = null;
    conditionList.value = [];
}

function addNewCondition() {
    conditionList.value.push({
        rules: [
            {
                question_id: "",
                operator: "=",
                value: "",
                connector: "AND",
            },
        ],
        next_question_id: "",
        is_composite: false,
    });
}

function removeCondition(index) {
    conditionList.value.splice(index, 1);
}

function addConditionRule(condition) {
    if (!condition.rules) {
        condition.rules = [];
    }
    condition.rules.push({
        question_id: "",
        operator: "=",
        value: "",
        connector: "AND",
    });
}

function removeConditionRule(condition, ruleIndex) {
    condition.rules.splice(ruleIndex, 1);
}

function getSourceQuestionText() {
    if (!editingCondition.question_id) return "";
    const question = props.questions.find(
        (q) => q.id.toString() === editingCondition.question_id.toString(),
    );
    return question ? question.text : "";
}

function getSourceQuestionType() {
    if (!editingCondition.question_id) return "";
    const question = props.questions.find(
        (q) => q.id.toString() === editingCondition.question_id.toString(),
    );
    return question ? question.type : "";
}

function getQuestionType(questionId) {
    if (!questionId) return "text";

    const nodeQuestion = nodes.value.find((n) => n.id === questionId);
    if (nodeQuestion) {
        return nodeQuestion.data.type;
    }

    const question = props.questions.find(
        (q) => q.id.toString() === questionId.toString(),
    );
    return question ? question.type : "text";
}

function getQuestionOptions(questionId) {
    if (!questionId) return [];

    const nodeQuestion = nodes.value.find((n) => n.id === questionId);
    if (nodeQuestion) {
        return nodeQuestion.data.options || [];
    }

    const question = props.questions.find(
        (q) => q.id.toString() === questionId.toString(),
    );
    return question ? question.options || [] : [];
}

function getOperatorsForQuestion(questionId) {
    const type = getQuestionType(questionId);
    const operators = {
        text: [
            { value: "=", label: "Igual a" },
            { value: "contains", label: "Contiene" },
            { value: "starts_with", label: "Empieza por" },
            { value: "ends_with", label: "Termina por" },
        ],
        number: [
            { value: "=", label: "Igual a" },
            { value: ">", label: "Mayor que" },
            { value: "<", label: "Menor que" },
            { value: ">=", label: "Mayor o igual que" },
            { value: "<=", label: "Menor o igual que" },
        ],
        boolean: [{ value: "=", label: "Igual a" }],
        select: [
            { value: "=", label: "Igual a" },
            { value: "!=", label: "Diferente de" },
        ],
        multiple: [
            { value: "contains", label: "Contiene" },
            { value: "in", label: "Está en" },
            { value: "=", label: "Igual a" },
        ],
        date: [
            { value: "=", label: "Igual a" },
            { value: ">", label: "Después de" },
            { value: "<", label: "Antes de" },
        ],
    };
    return operators[type] || operators.text;
}

async function saveConditionModal() {
    try {
        // Validar condiciones
        for (const condition of conditionList.value) {
            if (!condition.rules || condition.rules.length === 0) {
                showToast(
                    "Por favor añade al menos una regla para cada condición",
                    "error",
                );
                return;
            }

            // Validar cada regla
            for (const rule of condition.rules) {
                if (!rule.question_id || !rule.operator || rule.value === "") {
                    showToast(
                        "Por favor completa todos los campos de las reglas",
                        "error",
                    );
                    return;
                }
            }

            if (condition.next_question_id === "") {
                showToast(
                    "Por favor selecciona un destino para la condición",
                    "error",
                );
                return;
            }
        }

        // Procesar y normalizar valores antes de guardar
        conditionList.value.forEach((condition) => {
            condition.rules.forEach((rule) => {
                if (rule.originalValue !== undefined) {
                    rule.value = rule.originalValue;
                } else if (getQuestionType(rule.question_id) === "select" || getQuestionType(rule.question_id) === "multiple") {
                    const question = props.questions.find(q => q.id == rule.question_id);
                    if (question && question.options && Array.isArray(question.options)) {
                        const optionIndex = question.options.indexOf(rule.value);
                        if (optionIndex !== -1) {
                            rule.value = optionIndex;
                        }
                    }
                }
                
                // Normalizar valores
                if (rule.value === "true" || rule.value === "1") {
                    rule.value = 1;
                } else if (rule.value === "false" || rule.value === "0") {
                    rule.value = 0;
                } else if (!isNaN(rule.value) && rule.value !== "") {
                    rule.value = parseFloat(rule.value);
                }
            });
        });

        // Actualizar condiciones en el componente
        const updatedConditions = [...conditions.value];

        // Eliminar condiciones existentes para esta pregunta
        const filteredConditions = updatedConditions.filter(
            (c) => c.question_id != editingCondition.question_id,
        );

        // Añadir nuevas condiciones
        conditionList.value.forEach((condition) => {
            const newCondition = {
                question_id: editingCondition.question_id,
                next_question_id:
                    condition.next_question_id === "null"
                        ? null
                        : condition.next_question_id,
                is_composite: condition.rules.length > 1,
                composite_logic:
                    condition.rules.length > 1
                        ? condition.rules[0].connector
                        : null,
                composite_rules:
                    condition.rules.length > 1 ? condition.rules : null,
                operator:
                    condition.rules.length === 1
                        ? condition.rules[0].operator
                        : null,
                value:
                    condition.rules.length === 1
                        ? condition.rules[0].value
                        : null,
                questionnaire_id: props.itemId,
                order: condition.order || 1,
                id: condition.id || Date.now() + Math.random(), // ID temporal como en el wizard
            };

            filteredConditions.push(newCondition);
        });

        conditions.value = filteredConditions;
        console.log(
            "🔍 Condiciones actualizadas en el componente:",
            conditions.value,
        );

        // Actualizar edges en el flujo
        updateFlowEdges();

        closeModals();
        showToast("Condiciones guardadas correctamente", "success");
    } catch (error) {
        console.error("Error guardando condiciones:", error);
        showToast("Error al guardar las condiciones", "error");
    }
}

function updateFlowEdges() {
    // Eliminar edges existentes para esta pregunta
    edges.value = edges.value.filter(
        (edge) => !edge.source.startsWith(editingCondition.question_id),
    );

    // Añadir nuevos edges basados en las condiciones
    conditionList.value.forEach((condition) => {
        const target =
            condition.next_question_id === "null"
                ? "FIN"
                : condition.next_question_id;
        edges.value.push({
            id: `condition-${editingCondition.question_id}-${target}`,
            source: editingCondition.question_id,
            target: target,
            type: "default",
            style: { stroke: "#ef4444", strokeWidth: 3 },
        });
    });
}

function confirmDeleteCondition(edgeId) {
    deleteTarget.type = "condition";
    deleteTarget.id = edgeId;
    showDeleteModal.value = true;
}

function confirmDeleteAllConditions() {
    deleteTarget.type = "all_conditions";
    deleteTarget.id = null;
    showDeleteModal.value = true;
}

function deleteConfirmed() {
    try {
        if (deleteTarget.type === "all_conditions") {
            conditions.value = [];
            edges.value = edges.value.filter(
                (edge) => edge.style.stroke === "#3b82f6",
            );
            showToast("Todas las condiciones eliminadas", "success");
        } else if (deleteTarget.type === "condition") {
            // Eliminar edge específico
            edges.value = edges.value.filter(
                (edge) => edge.id !== deleteTarget.id,
            );
            showToast("Condición eliminada", "success");
        }
    } catch (error) {
        console.error("Error eliminando condición:", error);
        showToast("Error al eliminar la condición", "error");
    } finally {
        closeModals();
    }
}

async function saveConditions() {
    if (conditions.value.length === 0) {
        showToast("No hay condiciones para guardar", "warning");
        return;
    }

    try {
        showToast("Creando condiciones...", "info");

        // Preparar las condiciones para enviar al backend
        const conditionsToSave = conditions.value.map((condition) => ({
            question_id: condition.question_id,
            operator: condition.operator,
            value: condition.value,
            next_question_id: condition.next_question_id,
            questionnaire_id: props.itemId,
            order: condition.order || 1,
            is_composite: condition.is_composite || false,
            composite_logic: condition.composite_logic || null,
            composite_rules: condition.composite_rules || null,
        }));

        // Crear las condiciones reales (no draft)
        const response = await fetch(
            `/admin/questionnaires/${props.itemId}/conditions/create`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    conditions: conditionsToSave,
                }),
            },
        );

        if (!response.ok) {
            throw new Error(`Error creando condiciones: ${response.status}`);
        }

        const result = await response.json();
        console.log("✅ Condiciones creadas exitosamente:", result);

        // Actualizar el estado original después de crear exitosamente
        originalConditions.value = JSON.parse(JSON.stringify(conditions.value));

        showToast("Condiciones creadas correctamente", "success");

        // Emitir evento de actualización (NO goBack)
        emit("conditionsUpdated", {
            itemId: props.itemId,
            itemType: props.itemType,
            conditions: conditions.value,
        });
    } catch (error) {
        console.error("❌ Error creando condiciones:", error);
        showToast(`Error al crear: ${error.message}`, "error");
    }
}

function goBack() {
    if (hasUnsavedChanges.value) {
        if (
            confirm(
                "⚠️ Tienes cambios sin guardar. ¿Estás seguro de que quieres salir sin guardar?",
            )
        ) {
            emit("goBack");
        }
    } else {
        emit("goBack");
    }
}

function showToast(message, type = "success") {
    toast.message = message;
    toast.show = true;
    toast.type = type;
    setTimeout(() => (toast.show = false), 3000);
}

// Lifecycle
onMounted(() => {
    if (props.questions.length > 0) {
        buildTreeNodes();
    }
});

// Protección contra cierre de pestaña
window.addEventListener("beforeunload", (event) => {
    if (hasUnsavedChanges.value) {
        event.preventDefault();
        event.returnValue =
            "⚠️ Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?";
        return event.returnValue;
    }
});
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
