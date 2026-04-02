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
                class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold"
                    >
                        🎯
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-green-900">
                            {{ title }}
                        </h3>
                        <p class="text-sm text-green-700">{{ subtitle }}</p>
                        <p class="text-sm font-medium text-green-800 mt-1">
                            {{ itemName }}
                        </p>
                    </div>
                </div>
            </div>

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
                            la ayuda de 0 usando los wizards para mejor
                            mantenimiento y funcionalidad.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Formulario para añadir requisitos -->
            <div
                class="mb-6 p-4 bg-white rounded-xl border border-gray-200 shadow-sm"
            >
                <h4 class="font-semibold text-gray-800 mb-3">
                    Añadir nuevo requisito
                </h4>

                <!-- Selector de tipo de requisito -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2"
                        >Tipo de requisito:</label
                    >
                    <select
                        v-model="newRequirement.type"
                        class="border rounded px-3 py-2 text-sm w-full"
                    >
                        <option value="simple">
                            Requisito simple (una pregunta)
                        </option>
                        <option value="group">
                            Grupo de requisitos (múltiples preguntas)
                        </option>
                    </select>
                </div>

                <!-- Requisito simple -->
                <div
                    v-if="newRequirement.type === 'simple'"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3"
                >
                    <input
                        v-model="newRequirement.description"
                        placeholder="Descripción del requisito"
                        class="border rounded px-3 py-2 text-sm"
                    />

                    <!-- Selector de pregunta con búsqueda -->
                    <div class="relative">
                        <div
                            @click="toggleQuestionSearch('simple')"
                            class="border rounded px-3 py-2 text-sm cursor-pointer bg-white flex items-center justify-between"
                        >
                            <span
                                v-if="newRequirement.question_id"
                                class="text-gray-900"
                            >
                                {{
                                    getQuestionText(newRequirement.question_id)
                                }}
                            </span>
                            <span v-else class="text-gray-500"
                                >Selecciona pregunta</span
                            >
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>

                        <!-- Dropdown de búsqueda -->
                        <div
                            v-if="showQuestionSearch === 'simple'"
                            class="absolute z-50 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-hidden"
                        >
                            <div class="p-2 border-b">
                                <input
                                    v-model="questionSearchTerm"
                                    placeholder="Buscar pregunta..."
                                    class="w-full px-3 py-2 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    @input="filterQuestions"
                                />
                            </div>
                            <div class="max-h-48 overflow-y-auto">
                                <div
                                    v-for="question in filteredQuestions"
                                    :key="question.id"
                                    @click="
                                        selectQuestion(question.id, 'simple')
                                    "
                                    class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-100"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div class="flex-1">
                                            <div
                                                class="font-medium text-gray-900"
                                            >
                                                {{ question.text }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ question.type }}
                                            </div>
                                        </div>
                                        <div
                                            v-if="
                                                isCurrentQuestion(question.id)
                                            "
                                            class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded"
                                        >
                                            Actual
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-if="filteredQuestions.length === 0"
                                    class="px-3 py-2 text-sm text-gray-500"
                                >
                                    No se encontraron preguntas
                                </div>
                            </div>
                        </div>
                    </div>

                    <select
                        v-model="newRequirement.operator"
                        class="border rounded px-3 py-2 text-sm"
                    >
                        <option
                            v-for="op in getAvailableOperators()"
                            :key="op.value"
                            :value="op.value"
                        >
                            {{ op.label }}
                        </option>
                    </select>
                    <div class="flex gap-2">
                        <input
                            v-if="
                                getQuestionType() === 'text' ||
                                getQuestionType() === 'number'
                            "
                            v-model="newRequirement.value"
                            :type="
                                getQuestionType() === 'number'
                                    ? 'number'
                                    : 'text'
                            "
                            :placeholder="
                                getQuestionType() === 'number'
                                    ? 'Número'
                                    : 'Texto'
                            "
                            class="border rounded px-3 py-2 text-sm flex-1"
                        />
                        <select
                            v-else-if="getQuestionType() === 'select'"
                            v-model="newRequirement.value"
                            class="border rounded px-3 py-2 text-sm flex-1"
                        >
                            <option value="">Selecciona opción</option>
                            <option
                                v-for="(option, index) in dynamicOptions"
                                :key="index"
                                :value="option"
                            >
                                {{ option }}
                            </option>
                        </select>
                        <select
                            v-else-if="getQuestionType() === 'boolean'"
                            v-model="newRequirement.value"
                            class="border rounded px-3 py-2 text-sm flex-1"
                        >
                            <option value="">Selecciona</option>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                        <input
                            v-else
                            v-model="newRequirement.value"
                            placeholder="Valor"
                            class="border rounded px-3 py-2 text-sm flex-1"
                        />
                        <button
                            @click="addRequirement"
                            :disabled="!canAddSimpleRequirement"
                            class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
                        >
                            +
                        </button>
                    </div>
                </div>

                <!-- Grupo de requisitos -->
                <div v-if="newRequirement.type === 'group'" class="space-y-4">
                    <div class="flex items-center gap-3">
                        <input
                            v-model="newRequirement.description"
                            placeholder="Descripción del grupo de requisitos"
                            class="border rounded px-3 py-2 text-sm flex-1"
                        />
                        <select
                            v-model="newRequirement.groupLogic"
                            class="border rounded px-3 py-2 text-sm"
                        >
                            <option value="AND">
                                TODOS deben cumplirse (AND)
                            </option>
                            <option value="OR">
                                AL MENOS UNO debe cumplirse (OR)
                            </option>
                        </select>
                    </div>

                    <!-- Reglas del grupo -->
                    <div class="bg-gray-50 p-3 rounded border">
                        <h5 class="font-medium text-gray-800 mb-3">
                            Reglas del grupo:
                        </h5>
                        <div
                            v-if="newRequirement.rules.length === 0"
                            class="text-sm text-gray-500 mb-3"
                        >
                            Añade al menos una regla al grupo
                        </div>
                        <div v-else class="space-y-2 mb-3">
                            <div
                                v-for="(
                                    rule, ruleIndex
                                ) in newRequirement.rules"
                                :key="ruleIndex"
                                class="flex items-center gap-2 p-2 bg-white rounded border"
                            >
                                <!-- Selector de pregunta con búsqueda para reglas -->
                                <div class="relative flex-1">
                                    <div
                                        @click="
                                            toggleQuestionSearch(
                                                `group-${ruleIndex}`,
                                            )
                                        "
                                        class="border rounded px-2 py-1 text-sm cursor-pointer bg-white flex items-center justify-between"
                                    >
                                        <span
                                            v-if="rule.question_id"
                                            class="text-gray-900"
                                        >
                                            {{
                                                getQuestionText(
                                                    rule.question_id,
                                                )
                                            }}
                                        </span>
                                        <span v-else class="text-gray-500"
                                            >Selecciona pregunta</span
                                        >
                                        <i
                                            class="fas fa-chevron-down text-gray-400"
                                        ></i>
                                    </div>

                                    <!-- Dropdown de búsqueda para reglas -->
                                    <div
                                        v-if="
                                            showQuestionSearch ===
                                            `group-${ruleIndex}`
                                        "
                                        class="absolute z-50 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-hidden"
                                    >
                                        <div class="p-2 border-b">
                                            <input
                                                v-model="questionSearchTerm"
                                                placeholder="Buscar pregunta..."
                                                class="w-full px-3 py-2 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                @input="filterQuestions"
                                            />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto">
                                            <div
                                                v-for="question in filteredQuestions"
                                                :key="question.id"
                                                @click="
                                                    selectQuestion(
                                                        question.id,
                                                        `group-${ruleIndex}`,
                                                    )
                                                "
                                                class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-100"
                                            >
                                                <div
                                                    class="flex items-center justify-between"
                                                >
                                                    <div class="flex-1">
                                                        <div
                                                            class="font-medium text-gray-900"
                                                        >
                                                            {{ question.text }}
                                                        </div>
                                                        <div
                                                            class="text-xs text-gray-500"
                                                        >
                                                            {{ question.type }}
                                                        </div>
                                                    </div>
                                                    <div
                                                        v-if="
                                                            isCurrentQuestion(
                                                                question.id,
                                                            )
                                                        "
                                                        class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded"
                                                    >
                                                        Actual
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                v-if="
                                                    filteredQuestions.length ===
                                                    0
                                                "
                                                class="px-3 py-2 text-sm text-gray-500"
                                            >
                                                No se encontraron preguntas
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <select
                                    v-model="rule.operator"
                                    class="border rounded px-2 py-1 text-sm"
                                >
                                    <option
                                        v-for="op in getAvailableOperatorsForRule(
                                            rule,
                                        )"
                                        :key="op.value"
                                        :value="op.value"
                                    >
                                        {{ op.label }}
                                    </option>
                                </select>
                                <div class="flex gap-2 flex-1">
                                    <input
                                        v-if="
                                            getQuestionTypeForRule(rule) ===
                                                'text' ||
                                            getQuestionTypeForRule(rule) ===
                                                'number'
                                        "
                                        v-model="rule.value"
                                        :type="
                                            getQuestionTypeForRule(rule) ===
                                            'number'
                                                ? 'number'
                                                : 'text'
                                        "
                                        :placeholder="
                                            getQuestionTypeForRule(rule) ===
                                            'number'
                                                ? 'Número'
                                                : 'Texto'
                                        "
                                        class="border rounded px-2 py-1 text-sm flex-1"
                                    />
                                    <select
                                        v-else-if="
                                            getQuestionTypeForRule(rule) ===
                                            'select'
                                        "
                                        v-model="rule.value"
                                        class="border rounded px-2 py-1 text-sm flex-1"
                                    >
                                        <option value="">
                                            Selecciona opción
                                        </option>
                                        <option
                                            v-for="(
                                                option, index
                                            ) in getDynamicOptionsForRule(rule)"
                                            :key="index"
                                            :value="option"
                                        >
                                            {{ option }}
                                        </option>
                                    </select>
                                    <select
                                        v-else-if="
                                            getQuestionTypeForRule(rule) ===
                                            'multiple'
                                        "
                                        v-model="rule.value"
                                        class="border rounded px-2 py-1 text-sm flex-1"
                                    >
                                        <option value="">
                                            Selecciona opción
                                        </option>
                                        <option
                                            v-for="(
                                                option, index
                                            ) in getDynamicOptionsForRule(rule)"
                                            :key="index"
                                            :value="option"
                                        >
                                            {{ option }}
                                        </option>
                                    </select>
                                    <select
                                        v-else-if="
                                            getQuestionTypeForRule(rule) ===
                                            'boolean'
                                        "
                                        v-model="rule.value"
                                        class="border rounded px-2 py-1 text-sm flex-1"
                                    >
                                        <option value="">Selecciona</option>
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                    <input
                                        v-else
                                        v-model="rule.value"
                                        placeholder="Valor"
                                        class="border rounded px-2 py-1 text-sm flex-1"
                                    />
                                </div>
                                <button
                                    @click="removeRuleFromGroup(ruleIndex)"
                                    class="text-red-500 hover:text-red-700 p-1"
                                    title="Eliminar regla"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button
                            @click="addRuleToGroup"
                            class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700"
                        >
                            + Añadir regla
                        </button>
                    </div>

                    <button
                        @click="addRequirement"
                        :disabled="!canAddGroupRequirement"
                        class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
                    >
                        + Añadir grupo
                    </button>
                </div>
            </div>

            <!-- Lista de requisitos -->
            <div
                class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
            >
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h4 class="font-semibold text-gray-800">
                        Requisitos configurados
                    </h4>
                    <p class="text-sm text-gray-600">
                        Todos estos requisitos deben cumplirse para ser
                        beneficiario
                    </p>
                </div>

                <div
                    v-if="requirements.length === 0"
                    class="p-8 text-center text-gray-500"
                >
                    <div class="text-4xl mb-2">📋</div>
                    <p>No hay requisitos configurados</p>
                    <p class="text-sm">
                        Añade el primer requisito usando el formulario de arriba
                    </p>
                </div>

                <div v-else class="divide-y divide-gray-200">
                    <div
                        v-for="(requirement, index) in requirements"
                        :key="index"
                        class="p-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span
                                        class="text-sm font-medium text-gray-900"
                                        >{{ requirement.description }}</span
                                    >
                                    <span
                                        class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded"
                                    >
                                        {{
                                            requirement.type === "simple"
                                                ? "Requisito"
                                                : "Grupo"
                                        }}
                                        {{ index + 1 }}
                                    </span>
                                    <span
                                        v-if="requirement.type === 'group'"
                                        class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded"
                                    >
                                        {{ requirement.groupLogic }}
                                    </span>
                                </div>

                                <!-- Requisito simple -->
                                <div
                                    v-if="requirement.type === 'simple'"
                                    class="text-sm text-gray-600"
                                >
                                    <span class="font-medium">{{
                                        getQuestionText(requirement.question_id)
                                    }}</span>
                                    <span class="mx-2">{{
                                        getOperatorText(requirement.operator)
                                    }}</span>
                                    <span class="font-medium text-green-700">{{
                                        formatValue(requirement)
                                    }}</span>
                                </div>

                                <!-- Grupo de requisitos -->
                                <div
                                    v-if="requirement.type === 'group'"
                                    class="text-sm text-gray-600"
                                >
                                    <div class="space-y-1">
                                        <div
                                            v-for="(
                                                rule, ruleIndex
                                            ) in requirement.rules"
                                            :key="ruleIndex"
                                            class="flex items-center gap-2"
                                        >
                                            <span class="text-gray-400">•</span>
                                            <span class="font-medium">{{
                                                getQuestionText(
                                                    rule.question_id,
                                                )
                                            }}</span>
                                            <span>{{
                                                getOperatorText(rule.operator)
                                            }}</span>
                                            <span
                                                class="font-medium text-green-700"
                                                >{{
                                                    formatRuleValue(rule)
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    @click="editRequirement(index)"
                                    class="text-blue-500 hover:text-blue-700 p-1"
                                    title="Editar requisito"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    @click="removeRequirement(index)"
                                    class="text-red-500 hover:text-red-700 p-1"
                                    title="Eliminar requisito"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista previa del JSON -->
            <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-semibold text-gray-800">
                        Vista previa del JSON
                    </h4>
                    <div class="flex gap-2">
                        <button
                            @click="saveRequirements"
                            :disabled="requirements.length === 0"
                            :class="[
                                'px-4 py-2 rounded text-sm font-medium transition-colors',
                                hasUnsavedChanges
                                    ? 'bg-orange-600 text-white hover:bg-orange-700'
                                    : 'bg-green-600 text-white hover:bg-green-700',
                                requirements.length === 0
                                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                    : '',
                            ]"
                        >
                            <i class="fas fa-save mr-2"></i>
                            {{
                                hasUnsavedChanges
                                    ? "Guardar Cambios"
                                    : "Guardar Requisitos"
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
                <pre
                    class="bg-white p-3 rounded border text-xs overflow-x-auto"
                    >{{ jsonPreview }}</pre
                >
            </div>

            <!-- Toasts -->
            <transition name="fade">
                <div v-if="toast.show" class="fixed bottom-6 right-6 z-50">
                    <div
                        :class="[
                            'px-4 py-3 rounded shadow-lg text-white',
                            toast.type === 'success'
                                ? 'bg-green-500'
                                : 'bg-red-500',
                        ]"
                    >
                        {{ toast.message }}
                    </div>
                </div>
            </transition>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onUnmounted } from "vue";

const props = defineProps({
    itemId: {
        type: [String, Number],
        required: true,
    },
    itemType: {
        type: String,
        required: true,
        validator: (value) => ["ayuda", "cuestionario"].includes(value),
    },
    itemName: {
        type: String,
        default: "Sin nombre",
    },
    questions: {
        type: Array,
        default: () => [],
    },
    allQuestions: {
        type: Array,
        default: () => [],
    },
    existingRequirements: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["goBack", "requirementsUpdated"]);

// Computed properties
const title = computed(() => {
    return props.itemType === "ayuda"
        ? "Editar Requisitos de Ayuda"
        : "Editar Condiciones de Cuestionario";
});

const subtitle = computed(() => {
    return props.itemType === "ayuda"
        ? "Configura los requisitos que debe cumplir el usuario para ser beneficiario"
        : "Configura las condiciones y validaciones para este cuestionario";
});

const requirements = ref([]);
const originalRequirements = ref([]);
const toast = reactive({ show: false, message: "", type: "success" });
const isLegacyFormat = ref(false);
const allQuestions = computed(() => {
    return props.allQuestions || [];
});
const filteredQuestions = ref([]);
const questionSearchTerm = ref("");
const showQuestionSearch = ref(null);
const dynamicOptions = ref([]);
const editingIndex = ref(-1);

const newRequirement = reactive({
    type: "simple",
    description: "",
    question_id: "",
    operator: "==",
    value: "",
    groupLogic: "AND",
    rules: [],
});

// Computed properties
const canAddSimpleRequirement = computed(() => {
    return (
        newRequirement.description.trim() &&
        newRequirement.question_id &&
        newRequirement.operator &&
        newRequirement.value !== ""
    );
});

const canAddGroupRequirement = computed(() => {
    return (
        newRequirement.description.trim() &&
        newRequirement.rules.length > 0 &&
        newRequirement.rules.every(
            (rule) => rule.question_id && rule.operator && rule.value !== "",
        )
    );
});

const jsonPreview = computed(() => {
    if (requirements.value.length === 0) {
        return "{}";
    }

    const jsonData = {
        descripcion: "Requisitos de elegibilidad",
        json_regla: {
            condition: "AND",
            rules: requirements.value.map((req) => {
                if (req.type === "simple") {
                    return {
                        type: "simple",
                        question_id: req.question_id,
                        operator: req.operator,
                        value: req.value,
                    };
                } else {
                    return {
                        type: "group",
                        condition: req.groupLogic,
                        rules: req.rules.map((rule) => ({
                            question_id: rule.question_id,
                            operator: rule.operator,
                            value: rule.value,
                        })),
                    };
                }
            }),
        },
    };

    return JSON.stringify(jsonData, null, 2);
});

const hasUnsavedChanges = computed(() => {
    if (originalRequirements.value.length !== requirements.value.length) {
        return true;
    }

    for (let i = 0; i < requirements.value.length; i++) {
        const current = requirements.value[i];
        const original = originalRequirements.value[i];

        if (!original) return true;

        if (
            current.type !== original.type ||
            current.description !== original.description ||
            current.question_id !== original.question_id ||
            current.operator !== original.operator ||
            current.value !== original.value ||
            current.groupLogic !== original.groupLogic
        ) {
            return true;
        }

        if (current.type === "group" && original.type === "group") {
            if (current.rules.length !== original.rules.length) return true;

            for (let j = 0; j < current.rules.length; j++) {
                const currentRule = current.rules[j];
                const originalRule = original.rules[j];

                if (!originalRule) return true;

                if (
                    currentRule.question_id !== originalRule.question_id ||
                    currentRule.operator !== originalRule.operator ||
                    currentRule.value !== originalRule.value
                ) {
                    return true;
                }
            }
        }
    }

    return false;
});

// Methods
function showToast(message, type = "success") {
    toast.message = message;
    toast.type = type;
    toast.show = true;
    setTimeout(() => (toast.show = false), 3000);
}

function loadExistingRequirements() {
    console.log("=== INICIO loadExistingRequirements ===");
    console.log("Props existingRequirements:", props.existingRequirements);
    console.log(
        "Tipo de existingRequirements:",
        typeof props.existingRequirements,
    );
    console.log("Es array:", Array.isArray(props.existingRequirements));

    if (props.existingRequirements && props.existingRequirements.length > 0) {
        console.log("Procesando requisitos existentes...");

        // Convertir requisitos existentes al formato del componente
        requirements.value = props.existingRequirements
            .map((req, index) => {
                console.log(`--- Procesando requisito ${index + 1} ---`);
                console.log("Requisito completo:", req);
                console.log("req.descripcion:", req.descripcion);
                console.log("req.json_regla:", req.json_regla);
                console.log("Tipo de json_regla:", typeof req.json_regla);
                console.log(
                    "Es array json_regla:",
                    Array.isArray(req.json_regla),
                );

                let jsonRegla = req.json_regla;
                if (jsonRegla && Array.isArray(jsonRegla)) {
                    isLegacyFormat.value = true;
                    const transformedRequirements = jsonRegla.map(
                        (bloque, bloqueIndex) => {
                            const descripcion =
                                bloque.descripcion ||
                                req.descripcion ||
                                `Requisito ${bloqueIndex + 1}`;
                            const regla = bloque.json_regla;

                            if (typeof regla === "string") {
                                try {
                                    const jsonData = JSON.parse(regla);
                                    const rules = jsonData.rules || [];
                                    const isGroup = rules.length > 1;

                                    if (isGroup) {
                                        return {
                                            description: descripcion,
                                            type: "group",
                                            groupLogic:
                                                jsonData.condition || "AND",
                                            rules: rules.map((rule) => ({
                                                question_id: rule.question_id,
                                                operator: rule.operator,
                                                value: rule.value,
                                            })),
                                        };
                                    } else if (rules.length === 1) {
                                        const rule = rules[0];
                                        return {
                                            description: descripcion,
                                            type: "simple",
                                            question_id: rule.question_id,
                                            operator: rule.operator,
                                            value: rule.value,
                                        };
                                    } else {
                                        return {
                                            description: descripcion,
                                            type: "simple",
                                            question_id: "",
                                            operator: "==",
                                            value: "",
                                        };
                                    }
                                } catch (e) {
                                    console.error(
                                        "Error parsing JSON string en formato antiguo:",
                                        e,
                                    );
                                    return {
                                        description: descripcion,
                                        type: "simple",
                                        question_id: "",
                                        operator: "==",
                                        value: "",
                                    };
                                }
                            } else if (
                                typeof regla === "object" &&
                                regla !== null
                            ) {
                                const rules = regla.rules || [];
                                const isGroup = rules.length > 1;

                                if (isGroup) {
                                    return {
                                        description: descripcion,
                                        type: "group",
                                        groupLogic: regla.condition || "AND",
                                        rules: rules.map((rule) => ({
                                            question_id: rule.question_id,
                                            operator: rule.operator,
                                            value: rule.value,
                                        })),
                                    };
                                } else if (rules.length === 1) {
                                    const rule = rules[0];
                                    return {
                                        description: descripcion,
                                        type: "simple",
                                        question_id: rule.question_id,
                                        operator: rule.operator,
                                        value: rule.value,
                                    };
                                } else {
                                    return {
                                        description: descripcion,
                                        type: "simple",
                                        question_id: "",
                                        operator: "==",
                                        value: "",
                                    };
                                }
                            } else {
                                return {
                                    description: descripcion,
                                    type: "simple",
                                    question_id: "",
                                    operator: "==",
                                    value: "",
                                };
                            }
                        },
                    );
                    return transformedRequirements;
                }
                if (
                    jsonRegla &&
                    typeof jsonRegla === "object" &&
                    !Array.isArray(jsonRegla)
                ) {
                    const rules = jsonRegla.rules || [];
                    const isGroup = rules.length > 1;

                    if (isGroup) {
                        const processed = {
                            description: req.descripcion,
                            type: "group",
                            groupLogic: jsonRegla.condition || "AND",
                            rules: rules.map((rule) => ({
                                question_id: rule.question_id,
                                operator: rule.operator,
                                value: rule.value,
                            })),
                        };
                        console.log("✅ Requisito GRUPO procesado:", processed);
                        return processed;
                    } else if (rules.length === 1) {
                        // Es un requisito simple
                        const rule = rules[0];
                        const processed = {
                            description: req.descripcion,
                            type: "simple",
                            question_id: rule.question_id,
                            operator: rule.operator,
                            value: rule.value,
                        };
                        console.log(
                            "✅ Requisito SIMPLE procesado:",
                            processed,
                        );
                        return processed;
                    } else {
                        // No hay reglas, crear un requisito básico
                        const processed = {
                            description: req.descripcion,
                            type: "simple",
                            question_id: "",
                            operator: "==",
                            value: "",
                        };
                        console.log(
                            "⚠️ Requisito básico creado (sin reglas):",
                            processed,
                        );
                        return processed;
                    }
                }

                // Si es string, intentar parsearlo
                if (jsonRegla && typeof jsonRegla === "string") {
                    console.log("json_regla es string, parseando...");
                    try {
                        const jsonData = JSON.parse(jsonRegla);

                        // Aplicar la misma lógica de detección de tipo
                        const rules = jsonData.rules || [];
                        const isGroup = rules.length > 1;

                        if (isGroup) {
                            // Es un grupo de requisitos
                            const processed = {
                                description: req.descripcion,
                                type: "group",
                                groupLogic: jsonData.condition || "AND",
                                rules: rules.map((rule) => ({
                                    question_id: rule.question_id,
                                    operator: rule.operator,
                                    value: rule.value,
                                })),
                            };
                            console.log("Requisito grupo parseado:", processed);
                            return processed;
                        } else if (rules.length === 1) {
                            // Es un requisito simple
                            const rule = rules[0];
                            const processed = {
                                description: req.descripcion,
                                type: "simple",
                                question_id: rule.question_id,
                                operator: rule.operator,
                                value: rule.value,
                            };
                            console.log(
                                "Requisito simple parseado:",
                                processed,
                            );
                            return processed;
                        } else {
                            // No hay reglas, crear un requisito básico
                            const processed = {
                                description: req.descripcion,
                                type: "simple",
                                question_id: "",
                                operator: "==",
                                value: "",
                            };
                            console.log(
                                "Requisito básico parseado:",
                                processed,
                            );
                            return processed;
                        }
                    } catch (e) {
                        console.error("Error parsing JSON string:", e);
                        return null;
                    }
                }

                // Si no hay json_regla, crear un requisito básico
                if (req.descripcion) {
                    console.log("Creando requisito básico...");
                    const basic = {
                        description: req.descripcion,
                        type: "simple",
                        question_id: "",
                        operator: "==",
                        value: "",
                    };
                    console.log("Requisito básico creado:", basic);
                    return basic;
                }

                console.log("Requisito no procesado, retornando null");
                return null;
            })
            .filter(Boolean);
        if (requirements.value.some((req) => Array.isArray(req))) {
            requirements.value = requirements.value.flat();
        }

        originalRequirements.value = JSON.parse(
            JSON.stringify(requirements.value),
        );
        showToast(
            `Cargados ${requirements.value.length} requisitos existentes`,
            "success",
        );
    } else {
        originalRequirements.value = [];
    }
}

function loadAllQuestions() {
    console.log("Organizando preguntas...");
    console.log("Preguntas totales recibidas:", props.allQuestions.length);
    console.log("Preguntas del cuestionario actual:", props.questions.length);

    // Organizar preguntas: primero las del cuestionario actual, luego todas las demás
    const currentQuestionIds = props.questions.map((q) => q.id);
    const currentQuestions = props.allQuestions.filter((q) =>
        currentQuestionIds.includes(q.id),
    );
    const otherQuestions = props.allQuestions.filter(
        (q) => !currentQuestionIds.includes(q.id),
    );

    console.log("Preguntas actuales filtradas:", currentQuestions.length);
    console.log("Otras preguntas:", otherQuestions.length);

    filteredQuestions.value = [...currentQuestions, ...otherQuestions];

    console.log(
        "Total de preguntas organizadas:",
        filteredQuestions.value.length,
    );
}

function toggleQuestionSearch(type) {
    if (showQuestionSearch.value === type) {
        showQuestionSearch.value = null;
    } else {
        showQuestionSearch.value = type;
        questionSearchTerm.value = "";
        loadAllQuestions();
    }
}

function filterQuestions() {
    if (!questionSearchTerm.value.trim()) {
        loadAllQuestions();
        return;
    }

    const term = questionSearchTerm.value.toLowerCase();
    filteredQuestions.value = allQuestions.value.filter(
        (question) =>
            question.text.toLowerCase().includes(term) ||
            question.type.toLowerCase().includes(term),
    );
}

function selectQuestion(questionId, type) {
    if (type === "simple") {
        newRequirement.question_id = questionId;
    } else if (type.startsWith("group-")) {
        const ruleIndex = parseInt(type.split("-")[1]);
        newRequirement.rules[ruleIndex].question_id = questionId;
    }

    showQuestionSearch.value = null;
    questionSearchTerm.value = "";
}

function handleClickOutside(event) {
    if (!event.target.closest(".relative")) {
        showQuestionSearch.value = null;
    }
}

function isCurrentQuestion(questionId) {
    return props.questions.some((q) => q.id == questionId);
}

function getAvailableOperators() {
    const type = getQuestionType();
    return getOperatorsForType(type);
}

function getAvailableOperatorsForRule(rule) {
    const question = allQuestions.value.find((q) => q.id == rule.question_id);
    const type = question ? question.type : "text";
    return getOperatorsForType(type);
}

function getOperatorsForType(type) {
    const operators = {
        text: [
            { value: "==", label: "Igual a" },
            { value: "!=", label: "Distinto de" },
            { value: "contains", label: "Contiene" },
            { value: "starts_with", label: "Empieza por" },
            { value: "ends_with", label: "Termina por" },
        ],
        number: [
            { value: "==", label: "Igual a" },
            { value: "!=", label: "Distinto de" },
            { value: ">", label: "Mayor que" },
            { value: ">=", label: "Mayor o igual que" },
            { value: "<", label: "Menor que" },
            { value: "<=", label: "Menor o igual que" },
        ],
        boolean: [{ value: "==", label: "Igual a" }],
        select: [
            { value: "==", label: "Igual a" },
            { value: "!=", label: "Distinto de" },
        ],
        multiple: [
            { value: "contains", label: "Contiene" },
            { value: "not_contains", label: "No contiene" },
            { value: "==", label: "Igual a" },
        ],
        date: [
            { value: "==", label: "Igual a" },
            { value: ">", label: "Después de" },
            { value: "<", label: "Antes de" },
        ],
    };
    return operators[type] || operators.text;
}

function getQuestionType() {
    if (!newRequirement.question_id) return "text";
    const question = allQuestions.value.find(
        (q) => q.id == newRequirement.question_id,
    );
    return question ? question.type : "text";
}

function getQuestionTypeForRule(rule) {
    if (!rule.question_id) return "text";
    const question = allQuestions.value.find((q) => q.id == rule.question_id);
    return question ? question.type : "text";
}

async function loadDynamicOptions() {
    if (!newRequirement.question_id) return;

    const question = allQuestions.value.find(
        (q) => q.id == newRequirement.question_id,
    );
    if (!question) return;
    if (!question.slug) {
        if (question.options && question.options.length > 0) {
            dynamicOptions.value = question.options;
        } else {
            dynamicOptions.value = [];
        }
        return;
    }

    if (question.slug === "comunidad_autonoma") {
        try {
            const response = await fetch("/admin/searchCCAA");
            if (!response.ok) {
                throw new Error(
                    `HTTP ${response.status}: ${response.statusText}`,
                );
            }
            const data = await response.json();
            dynamicOptions.value = data;
        } catch (error) {
            dynamicOptions.value = [];
        }
    } else if (question.slug === "provincia") {
        try {
            const ccaaRequisito = requirements.value.find((req) => {
                if (req.type === "simple") {
                    const reqQuestion = allQuestions.value.find(
                        (q) => q.id == req.question_id,
                    );
                    return (
                        reqQuestion && reqQuestion.slug === "comunidad_autonoma"
                    );
                } else if (req.type === "group") {
                    return req.rules.some((rule) => {
                        const ruleQuestion = allQuestions.value.find(
                            (q) => q.id == rule.question_id,
                        );
                        return (
                            ruleQuestion &&
                            ruleQuestion.slug === "comunidad_autonoma"
                        );
                    });
                }
                return false;
            });

            let url = "/admin/searchProvincias";
            if (ccaaRequisito) {
                const ccaaValue =
                    ccaaRequisito.type === "simple"
                        ? ccaaRequisito.value
                        : ccaaRequisito.rules.find((r) => {
                              const rQuestion = allQuestions.value.find(
                                  (q) => q.id == r.question_id,
                              );
                              return (
                                  rQuestion &&
                                  rQuestion.slug === "comunidad_autonoma"
                              );
                          })?.value;
                if (ccaaValue) {
                    url += `?ccaa=${encodeURIComponent(ccaaValue)}`;
                }
            }

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(
                    `HTTP ${response.status}: ${response.statusText}`,
                );
            }
            const data = await response.json();
            dynamicOptions.value = data;
        } catch (error) {
            dynamicOptions.value = [];
        }
    } else if (question.slug === "municipio") {
        try {
            const provinciaRequisito = requirements.value.find((req) => {
                if (req.type === "simple") {
                    const reqQuestion = allQuestions.value.find(
                        (q) => q.id == req.question_id,
                    );
                    return reqQuestion && reqQuestion.slug === "provincia";
                } else if (req.type === "group") {
                    return req.rules.some((rule) => {
                        const ruleQuestion = allQuestions.value.find(
                            (q) => q.id == rule.question_id,
                        );
                        return (
                            ruleQuestion && ruleQuestion.slug === "provincia"
                        );
                    });
                }
                return false;
            });

            let url = "/admin/searchMunicipios";
            if (provinciaRequisito) {
                const provinciaValue =
                    provinciaRequisito.type === "simple"
                        ? provinciaRequisito.value
                        : provinciaRequisito.rules.find((r) => {
                              const rQuestion = allQuestions.value.find(
                                  (q) => q.id == r.question_id,
                              );
                              return (
                                  rQuestion && rQuestion.slug === "provincia"
                              );
                          })?.value;
                if (provinciaValue) {
                    url += `?provincia=${encodeURIComponent(provinciaValue)}`;
                }
            }

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(
                    `HTTP ${response.status}: ${response.statusText}`,
                );
            }
            const data = await response.json();
            dynamicOptions.value = data;
        } catch (error) {
            dynamicOptions.value = [];
        }
    } else if (question.options && question.options.length > 0) {
        dynamicOptions.value = question.options;
        return;
    } else {
        dynamicOptions.value = [];
    }
}

function getDynamicOptionsForRule(rule) {
    if (!rule.question_id) return [];

    const question = allQuestions.value.find((q) => q.id == rule.question_id);
    if (!question) return [];

    if (question.slug === "comunidad_autonoma") {
        return dynamicOptions.value;
    } else if (question.slug === "provincia") {
        const ccaaRequisito = requirements.value.find((req) => {
            if (req.type === "simple") {
                const reqQuestion = allQuestions.value.find(
                    (q) => q.id == req.question_id,
                );
                return reqQuestion && reqQuestion.slug === "comunidad_autonoma";
            } else if (req.type === "group") {
                return req.rules.some((rule) => {
                    const ruleQuestion = allQuestions.value.find(
                        (q) => q.id == rule.question_id,
                    );
                    return (
                        ruleQuestion &&
                        ruleQuestion.slug === "comunidad_autonoma"
                    );
                });
            }
            return false;
        });
        if (!ccaaRequisito) return [];
        return dynamicOptions.value;
    } else if (question.slug === "municipio") {
        return dynamicOptions.value;
    } else if (question.options && question.options.length > 0) {
        return question.options;
    }

    return [];
}

function getQuestionText(questionId) {
    const question = allQuestions.value.find((q) => q.id == questionId);
    return question ? question.text : "Pregunta no encontrada";
}

function getOperatorText(operator) {
    const operatorMap = {
        "==": "igual a",
        "!=": "distinto de",
        ">": "mayor que",
        ">=": "mayor o igual que",
        "<": "menor que",
        "<=": "menor o igual que",
        contains: "contiene",
        not_contains: "no contiene",
        starts_with: "empieza por",
        ends_with: "termina por",
    };
    return operatorMap[operator] || operator;
}

function formatValue(requirement) {
    const question = allQuestions.value.find(
        (q) => q.id == requirement.question_id,
    );
    if (!question) return requirement.value;

    if (question.type === "boolean") {
        return requirement.value === "1" ? "Sí" : "No";
    }

    if (question.type === "select" && question.options) {
        const index = question.options.indexOf(requirement.value);
        return index >= 0 ? question.options[index] : requirement.value;
    }

    return requirement.value;
}

function formatRuleValue(rule) {
    const question = allQuestions.value.find((q) => q.id == rule.question_id);
    if (!question) return rule.value;

    if (question.type === "boolean") {
        return rule.value === "1" ? "Sí" : "No";
    }

    if (question.type === "select" && question.options) {
        const index = question.options.indexOf(rule.value);
        return index >= 0 ? question.options[index] : rule.value;
    }

    return rule.value;
}

function addRuleToGroup() {
    newRequirement.rules.push({
        question_id: "",
        operator: "==",
        value: "",
    });
}

function removeRuleFromGroup(index) {
    newRequirement.rules.splice(index, 1);
}

function editRequirement(index) {
    const req = requirements.value[index];
    editingIndex.value = index;

    // Copiar datos al formulario
    newRequirement.type = req.type || "simple";
    newRequirement.description = req.description || "";
    newRequirement.question_id = req.question_id || "";
    newRequirement.operator = req.operator || "==";
    newRequirement.value = req.value || "";
    newRequirement.groupLogic = req.groupLogic || "AND";
    newRequirement.rules = req.rules ? [...req.rules] : [];

    // Eliminar el requisito original
    requirements.value.splice(index, 1);

    showToast("Requisito cargado para edición", "success");
}

function addRequirement() {
    if (newRequirement.type === "simple") {
        if (!canAddSimpleRequirement.value) {
            showToast("Por favor completa todos los campos", "error");
            return;
        }

        requirements.value.push({
            type: "simple",
            description: newRequirement.description,
            question_id: newRequirement.question_id,
            operator: newRequirement.operator,
            value: newRequirement.value,
        });
    } else {
        if (!canAddGroupRequirement.value) {
            showToast("Por favor completa todos los campos del grupo", "error");
            return;
        }

        requirements.value.push({
            type: "group",
            description: newRequirement.description,
            groupLogic: newRequirement.groupLogic,
            rules: [...newRequirement.rules],
        });
    }

    // Reset form
    newRequirement.type = "simple";
    newRequirement.description = "";
    newRequirement.question_id = "";
    newRequirement.operator = "==";
    newRequirement.value = "";
    newRequirement.groupLogic = "AND";
    newRequirement.rules = [];
    editingIndex.value = -1;

    showToast("Requisito añadido correctamente", "success");
}

function removeRequirement(index) {
    requirements.value.splice(index, 1);
    showToast("Requisito eliminado", "success");
}

async function saveRequirements() {
    try {
        console.log("Guardando requisitos...", requirements.value);

        // Preparar los datos para guardar
        const requisitosData = requirements.value.map((req) => {
            if (req.type === "simple") {
                return {
                    descripcion: req.description,
                    json_regla: {
                        condition: "AND",
                        rules: [
                            {
                                question_id: req.question_id,
                                operator: req.operator,
                                value: req.value,
                            },
                        ],
                    },
                };
            } else if (req.type === "group") {
                return {
                    descripcion: req.description,
                    json_regla: {
                        condition: req.groupLogic,
                        rules: req.rules.map((rule) => ({
                            question_id: rule.question_id,
                            operator: rule.operator,
                            value: rule.value,
                        })),
                    },
                };
            }
        });

        console.log("Datos preparados para guardar:", requisitosData);

        // Guardar en el backend usando updateAllJson
        const response = await fetch(
            `/admin/ayudas/${props.itemId}/requisitos-json`,
            {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
                body: JSON.stringify({
                    requisitos: requisitosData,
                }),
            },
        );

        console.log(
            "Respuesta del servidor:",
            response.status,
            response.statusText,
        );

        if (response.ok) {
            try {
                const result = await response.json();
                console.log("Requisitos guardados exitosamente:", result);
                showToast("✅ Requisitos guardados correctamente", "success");
                originalRequirements.value = JSON.parse(
                    JSON.stringify(requirements.value),
                );

                // Emitir evento de actualización
                emit("requirementsUpdated", {
                    itemId: props.itemId,
                    itemType: props.itemType,
                    requirements: requirements.value,
                });
            } catch (parseError) {
                console.error("Error parseando respuesta exitosa:", parseError);
                showToast("✅ Requisitos guardados correctamente", "success");
                originalRequirements.value = JSON.parse(
                    JSON.stringify(requirements.value),
                );
            }
        } else {
            // Intentar obtener el error del servidor
            let errorMessage = `Error ${response.status}: ${response.statusText}`;

            try {
                const errorData = await response.json();
                if (errorData.message) {
                    errorMessage = errorData.message;
                }
            } catch (parseError) {
                console.log(
                    "No se pudo parsear el error del servidor, usando respuesta por defecto",
                );
            }

            console.error(
                "Error guardando requisitos:",
                response.status,
                errorMessage,
            );
            showToast(
                `❌ Error guardando requisitos: ${errorMessage}`,
                "error",
            );
        }
    } catch (error) {
        console.error("Error en saveRequirements:", error);
        showToast(`❌ Error de conexión: ${error.message}`, "error");
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

// Watchers
watch(
    () => props.existingRequirements,
    (newRequirements) => {
        console.log("Props existingRequirements cambiaron:", newRequirements);
        if (newRequirements && newRequirements.length > 0) {
            loadExistingRequirements();
        }
    },
    { immediate: true },
);

watch(
    () => newRequirement.question_id,
    async (newQuestionId) => {
        console.log("🔄 Pregunta seleccionada cambió:", newQuestionId);
        if (newQuestionId) {
            const question = allQuestions.value.find(
                (q) => q.id == newQuestionId,
            );
            console.log("📋 Pregunta encontrada:", question);
            console.log("🏷️ Slug de la pregunta:", question?.slug);
            console.log("📝 Tipo de la pregunta:", question?.type);
            console.log("📋 Opciones de la pregunta:", question?.options);
            console.log(
                "🔍 ¿Es pregunta especial?",
                question?.slug === "comunidad_autonoma" ||
                    question?.slug === "provincia" ||
                    question?.slug === "municipio",
            );

            if (question?.slug) {
                console.log(
                    "✅ Pregunta tiene slug, cargando opciones dinámicas...",
                );
                await loadDynamicOptions();
            } else {
                console.log(
                    "❌ Pregunta no tiene slug, usando opciones estáticas si existen",
                );
                if (question?.options && question.options.length > 0) {
                    dynamicOptions.value = question.options;
                    console.log(
                        "✅ Opciones estáticas cargadas:",
                        question.options,
                    );
                } else {
                    dynamicOptions.value = [];
                    console.log("❌ No hay opciones disponibles");
                }
            }
        } else {
            console.log("Limpiando opciones dinámicas...");
            dynamicOptions.value = [];
        }
    },
);

watch(
    requirements,
    async (newRequirements) => {
        console.log(
            "Requisitos cambiaron, verificando si recargar opciones dinámicas...",
        );
        if (newRequirement.question_id) {
            const question = allQuestions.value.find(
                (q) => q.id == newRequirement.question_id,
            );
            if (
                question &&
                (question.slug === "provincia" || question.slug === "municipio")
            ) {
                console.log(
                    "Recargando opciones dinámicas por cambio en requisitos...",
                );
                await loadDynamicOptions();
            }
        }
    },
    { deep: true },
);

watch(
    () => newRequirement.rules,
    async (newRules) => {
        console.log(
            "Reglas del grupo cambiaron, verificando si recargar opciones dinámicas...",
        );
        for (const rule of newRules) {
            if (rule.question_id) {
                const question = allQuestions.value.find(
                    (q) => q.id == rule.question_id,
                );
                if (
                    question &&
                    (question.slug === "provincia" ||
                        question.slug === "municipio")
                ) {
                    console.log(
                        "Recargando opciones dinámicas por cambio en regla del grupo...",
                    );
                    await loadDynamicOptions();
                }
            }
        }
    },
    { deep: true },
);

// Lifecycle
onMounted(() => {
    loadAllQuestions();
    loadExistingRequirements();
    document.addEventListener("click", handleClickOutside);
    window.addEventListener("beforeunload", handleBeforeUnload);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
    window.removeEventListener("beforeunload", handleBeforeUnload);
});
function handleBeforeUnload(event) {
    if (hasUnsavedChanges.value) {
        event.preventDefault();
        event.returnValue =
            "⚠️ Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?";
        return event.returnValue;
    }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
