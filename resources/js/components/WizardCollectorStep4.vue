<template>
    <div class="space-y-8">
        <div class="text-center">
            <div
                class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4"
            >
                <i class="fas fa-check-circle text-3xl text-green-600"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                Revisión del Wizard
            </h3>
            <p class="text-gray-600">
                Revisa toda la configuración antes de finalizar
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h4
                class="text-lg font-semibold text-gray-900 mb-4 flex items-center"
            >
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                Información del Wizard
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Nombre</label
                    >
                    <p class="text-gray-900 font-medium">
                        {{ wizardData.name || "Sin nombre" }}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Descripción</label
                    >
                    <p class="text-gray-900">
                        {{ wizardData.description || "Sin descripción" }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h4
                class="text-lg font-semibold text-gray-900 mb-4 flex items-center"
            >
                <i class="fas fa-list text-purple-500 mr-2"></i>
                Secciones del Onboarder
                <span
                    class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                >
                    {{ sections.length }} sección(es)
                </span>
            </h4>

            <div
                v-if="sections.length === 0"
                class="text-center py-8 text-gray-500"
            >
                <i class="fas fa-folder-open text-4xl mb-2"></i>
                <p>No hay secciones configuradas</p>
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="(section, index) in sections"
                    :key="index"
                    class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h5 class="font-medium text-gray-900 mb-2">
                                {{ section.name }}
                            </h5>
                            <p
                                v-if="section.description"
                                class="text-sm text-gray-600 mb-3"
                            >
                                {{ section.description }}
                            </p>

                            <div
                                v-if="
                                    section.questions &&
                                    section.questions.length > 0
                                "
                            >
                                <div class="flex items-center mb-2">
                                    <i
                                        class="fas fa-question-circle text-gray-400 mr-2"
                                    ></i>
                                    <span
                                        class="text-sm font-medium text-gray-700"
                                    >
                                        {{
                                            section.questions.length
                                        }}
                                        pregunta(s)
                                    </span>
                                </div>

                                <div class="space-y-2">
                                    <div
                                        v-for="(
                                            question, qIndex
                                        ) in section.questions"
                                        :key="qIndex"
                                        class="flex items-center justify-between p-2 bg-gray-50 rounded border"
                                    >
                                        <div class="flex-1">
                                            <p
                                                class="text-sm font-medium text-gray-800"
                                            >
                                                {{ question.text }}
                                            </p>
                                            <div
                                                class="flex items-center space-x-2 mt-1"
                                            >
                                                <span
                                                    class="text-xs text-gray-500"
                                                    >{{
                                                        questionTypes[
                                                            question.type
                                                        ] || question.type
                                                    }}</span
                                                >
                                                <span v-if="question.slug" class="text-xs text-gray-400">
                                                    • {{ question.slug }}
                                                </span>
                                                <span class="text-xs inline-flex items-center px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 ml-2">
                                                    <i class="fas fa-layer-group mr-1"></i>
                                                    Pantalla {{ (Number(question.screen) || 0) + 1 }}
                                                </span>
                                                <span
                                                    v-if="
                                                        question.type ===
                                                        'builder'
                                                    "
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-purple-500 to-pink-500 text-white"
                                                >
                                                    <i
                                                        class="fas fa-tools mr-1"
                                                    ></i>
                                                    Builder
                                                </span>
                                                <span
                                                    v-if="
                                                        question.blockIfBankflipFilled
                                                    "
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                                >
                                                    <i
                                                        class="fas fa-lock mr-1"
                                                    ></i>
                                                    Bloqueada
                                                </span>
                                                <span
                                                    v-if="
                                                        question.hideIfBankflipFilled
                                                    "
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
                                                >
                                                    <i
                                                        class="fas fa-eye-slash mr-1"
                                                    ></i>
                                                    Ocultada
                                                </span>
                                                <span
                                                    v-if="question.condition"
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                                >
                                                    <i
                                                        class="fas fa-code-branch mr-1"
                                                    ></i>
                                                    Condicionada
                                                </span>
                                                <span
                                                    v-if="question.showIfBankflipFilled === 1"
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                                >
                                                    <i
                                                        class="fas fa-university mr-1"
                                                    ></i>
                                                    Solo Bankflip
                                                </span>
                                                <span
                                                    v-if="question.showIfBankflipFilled === 0"
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                                >
                                                    <i
                                                        class="fas fa-user mr-1"
                                                    ></i>
                                                    Solo Normales
                                                </span>
                                                <span
                                                    v-if="question.showIfBankflipFilled === null || question.showIfBankflipFilled === undefined"
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                                >
                                                    <i
                                                        class="fas fa-globe mr-1"
                                                    ></i>
                                                    Todos
                                                </span>
                                            </div>
                                            
                                            <div 
                                                v-if="(question.type === 'select' || question.type === 'multiple') && question.options && question.options.length > 0"
                                                class="mt-2 flex flex-wrap gap-1"
                                            >
                                                <span
                                                    v-for="(option, optionIndex) in isOptionsExpanded(question, sectionIndex, qIndex) ? getExpandedOptions(question) : getVisibleOptions(question)"
                                                    :key="optionIndex"
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                                >
                                                    {{ option.text || option }}
                                                </span>
                                                <button
                                                    v-if="question.options.length > 3"
                                                    @click="toggleOptionsExpansion(question, sectionIndex, qIndex)"
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors cursor-pointer"
                                                >
                                                    {{ isOptionsExpanded(question, sectionIndex, qIndex) ? 'Ver menos' : `+${question.options.length - 3} más` }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-sm text-gray-500 italic">
                                No hay preguntas en esta sección
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h4
                class="text-lg font-semibold text-gray-900 mb-4 flex items-center"
            >
                <i class="fas fa-users text-orange-500 mr-2"></i>
                Tipos de Convivientes
                <span
                    class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
                >
                    {{ convivienteTypes.length }} tipo(s)
                </span>
            </h4>

            <div
                v-if="convivienteTypes.length === 0"
                class="text-center py-8 text-gray-500"
            >
                <i class="fas fa-user-friends text-4xl mb-2"></i>
                <p>No hay tipos de convivientes configurados</p>
            </div>

            <div v-else class="space-y-6">
                <div
                    v-for="(type, typeIndex) in convivienteTypes"
                    :key="typeIndex"
                    class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center">
                            <i
                                :class="type.icon"
                                class="text-2xl text-orange-500 mr-3"
                            ></i>
                            <div>
                                <h5 class="font-medium text-gray-900">
                                    {{ type.name }}
                                </h5>
                                <p class="text-sm text-gray-600">
                                    {{ type.description }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="type.sections && type.sections.length > 0">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-folder text-gray-400 mr-2"></i>
                            <span class="text-sm font-medium text-gray-700">
                                {{ type.sections.length }} sección(es)
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="(section, sectionIndex) in type.sections"
                                :key="sectionIndex"
                                class="border border-gray-200 rounded p-3 bg-gray-50"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h6
                                            class="font-medium text-gray-800 mb-2 flex items-center"
                                        >
                                            {{ section.name }}
                                            <span
                                                v-if="section.skipCondition"
                                                class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                            >
                                                <i
                                                    class="fas fa-forward mr-1"
                                                ></i>
                                                Skippeable
                                            </span>
                                        </h6>

                                        <div
                                            v-if="
                                                section.questions &&
                                                section.questions.length > 0
                                            "
                                        >
                                            <div class="space-y-2">
                                                <div
                                                    v-for="(
                                                        question, qIndex
                                                    ) in section.questions"
                                                    :key="qIndex"
                                                    class="flex items-center justify-between p-2 bg-white rounded border"
                                                >
                                                    <div class="flex-1">
                                                        <p
                                                            class="text-sm font-medium text-gray-800"
                                                        >
                                                            {{ question.text }}
                                                        </p>
                                                        <div
                                                            class="flex items-center space-x-2 mt-1"
                                                        >
                                                            <span
                                                                class="text-xs text-gray-500"
                                                                >{{
                                                                    questionTypes[
                                                                        question
                                                                            .type
                                                                    ] ||
                                                                    question.type
                                                                }}</span
                                                            >
                                                            <span v-if="question.slug" class="text-xs text-gray-400">
                                                                • {{ question.slug }}
                                                            </span>
                                                            <span class="text-xs inline-flex items-center px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 ml-2">
                                                                <i class="fas fa-layer-group mr-1"></i>
                                                                Pantalla {{ (Number(question.screen) || 0) + 1 }}
                                                            </span>
                                                            <span
                                                                v-if="
                                                                    question.condition
                                                                "
                                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                                            >
                                                                <i
                                                                    class="fas fa-code-branch mr-1"
                                                                ></i>
                                                                Condicionada
                                                            </span>
                                                            <span
                                                                v-if="
                                                                    question.blockIfBankflipFilled
                                                                "
                                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                                            >
                                                                <i
                                                                    class="fas fa-lock mr-1"
                                                                ></i>
                                                                Bloqueada
                                                            </span>
                                                            <span
                                                                v-if="
                                                                    question.hideIfBankflipFilled
                                                                "
                                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
                                                            >
                                                                <i
                                                                    class="fas fa-eye-slash mr-1"
                                                                ></i>
                                                                Ocultada
                                                            </span>
                                                            <span
                                                                v-if="
                                                                    question.requiredCondition ||
                                                                    question.optionalCondition
                                                                "
                                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                                            >
                                                                <i
                                                                    class="fas fa-exclamation-triangle mr-1"
                                                                ></i>
                                                                Obligatoria
                                                            </span>
                                                        </div>
                                                        
                                                        <div 
                                                            v-if="(question.type === 'select' || question.type === 'multiple') && question.options && question.options.length > 0"
                                                            class="mt-2 flex flex-wrap gap-1"
                                                        >
                                                            <span
                                                                v-for="(option, optionIndex) in isOptionsExpanded(question, typeIndex, sectionIndex, qIndex) ? getExpandedOptions(question) : getVisibleOptions(question)"
                                                                :key="optionIndex"
                                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                                            >
                                                                {{ option.text || option }}
                                                            </span>
                                                            <button
                                                                v-if="question.options.length > 3"
                                                                @click="toggleOptionsExpansion(question, typeIndex, sectionIndex, qIndex)"
                                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors cursor-pointer"
                                                            >
                                                                {{ isOptionsExpanded(question, typeIndex, sectionIndex, qIndex) ? 'Ver menos' : `+${question.options.length - 3} más` }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            v-else
                                            class="text-sm text-gray-500 italic"
                                        >
                                            No hay preguntas en esta sección
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-sm text-gray-500 italic">
                        No hay secciones configuradas para este tipo
                    </div>
                </div>
            </div>
        </div>

        <div
            class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 p-6"
        >
            <h4
                class="text-lg font-semibold text-gray-900 mb-4 flex items-center"
            >
                <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                Resumen de Estadísticas
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">
                        {{ sections.length }}
                    </div>
                    <div class="text-sm text-gray-600">
                        Secciones del Onboarder
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">
                        {{ totalQuestions }}
                    </div>
                    <div class="text-sm text-gray-600">Preguntas Totales</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">
                        {{ convivienteTypes.length }}
                    </div>
                    <div class="text-sm text-gray-600">
                        Tipos de Convivientes
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">
                        {{ totalConvivienteQuestions }}
                    </div>
                    <div class="text-sm text-gray-600">
                        Preguntas de Convivientes
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    wizardData: {
        type: Object,
        required: true,
    },
    sections: {
        type: Array,
        required: true,
    },
    convivienteTypes: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(["go-to-step", "show-notification"]);
const expandedOptions = ref(new Set());

const questionTypes = ref({
    text: "Texto",
    textarea: "Área de texto",
    select: "Selección única",
    multiple: "Selección múltiple",
    number: "Número",
    email: "Email",
    tel: "Teléfono",
    date: "Fecha",
    checkbox: "Casilla de verificación",
    radio: "Botón de radio",
    boolean: "Sí/No",
    builder: "Builder",
});

const totalQuestions = computed(() => {
    return props.sections.reduce((total, section) => {
        return total + (section.questions ? section.questions.length : 0);
    }, 0);
});

const totalConvivienteQuestions = computed(() => {
    return props.convivienteTypes.reduce((total, type) => {
        if (type.sections) {
            return (
                total +
                type.sections.reduce((sectionTotal, section) => {
                    return (
                        sectionTotal +
                        (section.questions ? section.questions.length : 0)
                    );
                }, 0)
            );
        }
        return total;
    }, 0);
});

const getQuestionKey = (question, sectionIndex, questionIndex, typeIndex = null) => {
    if (typeIndex !== null) {
        return `step4-conviviente-question-${typeIndex}-${sectionIndex}-${questionIndex}-${question.id}`;
    }
    return `step4-question-${sectionIndex}-${questionIndex}-${question.id}`;
};

const isOptionsExpanded = (question, sectionIndex, questionIndex, typeIndex = null) => {
    return expandedOptions.value.has(getQuestionKey(question, sectionIndex, questionIndex, typeIndex));
};

const toggleOptionsExpansion = (question, sectionIndex, questionIndex, typeIndex = null) => {
    const key = getQuestionKey(question, sectionIndex, questionIndex, typeIndex);
    if (expandedOptions.value.has(key)) {
        expandedOptions.value.delete(key);
    } else {
        expandedOptions.value.add(key);
    }
};

const getVisibleOptions = (question) => {
    if (!question.options || question.options.length === 0) return [];
    
    return question.options.slice(0, 3);
};

const getExpandedOptions = (question) => {
    if (!question.options || question.options.length === 0) return [];
    
    return question.options;
};

</script>
