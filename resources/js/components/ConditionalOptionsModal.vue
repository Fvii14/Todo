<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div
            class="bg-white rounded-lg p-6 w-full max-w-4xl mx-4 max-h-[90vh] overflow-hidden flex flex-col"
        >
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">
                        Configurar opciones condicionadas
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ questionText }}
                    </p>
                </div>
                <button
                    @click="closeModal"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto">
                <div
                    class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg"
                >
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <div class="text-sm text-blue-800">
                            <strong>Opciones por defecto:</strong> Se mostrarán
                            todas las opciones disponibles cuando no se cumplan
                            las condiciones específicas.
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-lg font-medium text-gray-800">
                            Configuraciones condicionadas
                        </h4>
                        <button
                            @click="addConditionalConfig"
                            class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors"
                        >
                            <i class="fas fa-plus mr-1"></i>
                            Añadir condición
                        </button>
                    </div>

                    <div
                        v-if="conditionalConfigs.length === 0"
                        class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg"
                    >
                        <i class="fas fa-code-branch text-3xl mb-2"></i>
                        <p>No hay configuraciones condicionadas</p>
                        <p class="text-sm">
                            Añade una condición para mostrar opciones
                            específicas
                        </p>
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="(config, configIndex) in conditionalConfigs"
                            :key="configIndex"
                            class="border border-gray-200 rounded-lg p-4"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="font-medium text-gray-800">
                                    Condición {{ configIndex + 1 }}
                                </h5>
                                <button
                                    @click="
                                        removeConditionalConfig(configIndex)
                                    "
                                    class="text-red-400 hover:text-red-600 transition-colors"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            <div class="mb-4">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Esta configuración se aplicará cuando:
                                </label>
                                <select
                                    v-model="config.dependsOnQuestionId"
                                    @change="
                                        onDependsOnQuestionChange(configIndex)
                                    "
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">
                                        Seleccionar pregunta...
                                    </option>
                                    <optgroup
                                        v-for="(
                                            group, groupName
                                        ) in groupedQuestions"
                                        :key="groupName"
                                        :label="groupName"
                                    >
                                        <option
                                            v-for="question in group"
                                            :key="question.id"
                                            :value="question.id"
                                        >
                                            {{ question.text }}
                                        </option>
                                    </optgroup>
                                </select>
                            </div>

                            <div v-if="config.dependsOnQuestionId" class="mb-4">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Condición:
                                </label>
                                <div class="flex space-x-4">
                                    <select
                                        v-model="config.conditionType"
                                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <option value="">
                                            Seleccionar condición...
                                        </option>
                                        <option value="equals">
                                            Es igual a
                                        </option>
                                        <option value="not_equals">
                                            No es igual a
                                        </option>
                                        <option value="contains">
                                            Contiene
                                        </option>
                                        <option value="not_contains">
                                            No contiene
                                        </option>
                                    </select>

                                    <div class="flex-1">
                                        <select
                                            v-if="config.conditionType && getDynamicOptions(getSelectedQuestion(config)).length > 0"
                                            v-model="config.expectedValue"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            :disabled="loadingOptions"
                                        >
                                            <option value="">
                                                {{ loadingOptions ? 'Cargando opciones...' : 'Seleccionar valor...' }}
                                            </option>
                                            <option
                                                v-for="option in getDynamicOptions(getSelectedQuestion(config))"
                                                :key="getOptionValue(option, getSelectedQuestion(config))"
                                                :value="getOptionValue(option, getSelectedQuestion(config))"
                                            >
                                                {{ option.text || option }}
                                            </option>
                                        </select>
                                        <input
                                            v-else-if="config.conditionType"
                                            v-model="config.expectedValue"
                                            type="text"
                                            placeholder="Valor esperado..."
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="
                                    config.conditionType && config.expectedValue
                                "
                            >
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Mostrar estas opciones:
                                </label>
                                <div
                                    class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-3"
                                >
                                    <label
                                        v-for="(
                                            option, optionIndex
                                        ) in availableOptions"
                                        :key="optionIndex"
                                        class="flex items-center p-2 hover:bg-gray-50 cursor-pointer rounded transition-colors"
                                        :class="{
                                            'bg-green-50 border border-green-200':
                                                config.options.includes(
                                                    option.value || option,
                                                ),
                                            'bg-white border border-gray-200':
                                                !config.options.includes(
                                                    option.value || option,
                                                ),
                                        }"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="option.value || option"
                                            v-model="config.options"
                                            class="mr-3 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                        />
                                        <div class="flex-1">
                                            <span
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ option.text || option }}
                                            </span>
                                            <span
                                                v-if="
                                                    option.value !== option.text
                                                "
                                                class="text-xs text-gray-500 ml-2"
                                            >
                                                ({{ option.value }})
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200"
            >
                <button
                    @click="closeModal"
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Cancelar
                </button>
                <button
                    @click="saveConfiguration"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                >
                    <i class="fas fa-check mr-2"></i>
                    Guardar configuración
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    question: {
        type: Object,
        default: null,
    },
    availableOptions: {
        type: Array,
        default: () => [],
    },
    availableQuestions: {
        type: Array,
        default: () => [],
    },
});

const dynamicOptions = ref([]);
const loadingOptions = ref(false);

const emit = defineEmits(["close", "save"]);

const defaultOptions = ref([]);
const conditionalConfigs = ref([]);

const questionText = computed(() => {
    return props.question ? props.question.text : "";
});

const groupedQuestions = computed(() => {
    const groups = {};
    props.availableQuestions.forEach((question) => {
        if (question.id === props.question?.id) {
            return;
        }

        const sectionName =
            question.sectionName || `Sección ${question.sectionIndex + 1}`;
        if (!groups[sectionName]) {
            groups[sectionName] = [];
        }
        groups[sectionName].push(question);
    });
    return groups;
});

watch(
    () => props.show,
    (newValue) => {
        if (newValue) {
            if (props.question?.conditionalOptions) {
                const config = props.question.conditionalOptions;
                defaultOptions.value = config.defaultOptions || [];
                conditionalConfigs.value = config.conditionalConfigs || [];
            } else {
                defaultOptions.value = [];
                conditionalConfigs.value = [];
            }
        }
    },
);

watch(
    () => conditionalConfigs.value,
    (newConfigs) => {
        newConfigs.forEach((config, index) => {
            if (config.dependsOnQuestionId) {
                loadDynamicOptions(index);
            }
        });
    },
    { deep: true }
);

const addConditionalConfig = () => {
    conditionalConfigs.value.push({
        dependsOnQuestionId: "",
        conditionType: "",
        expectedValue: "",
        options: [],
    });
};

const removeConditionalConfig = (index) => {
    conditionalConfigs.value.splice(index, 1);
};

const onDependsOnQuestionChange = (configIndex) => {
    const config = conditionalConfigs.value[configIndex];
    config.conditionType = "";
    config.expectedValue = "";
    config.options = [];
    loadDynamicOptions(configIndex);
};

const getDynamicOptions = (question) => {
    if (!question) return [];
 
    if (['comunidad_autonoma', 'provincia', 'municipio'].includes(question.slug)) {
        return dynamicOptions.value;
    }
 
    return question.options || [];
};

const getOptionValue = (option, question) => {
    if (!question) return option.value || option;

    if (['comunidad_autonoma', 'provincia', 'municipio'].includes(question.slug)) {
        return option.text || option;
    }

    return option.value || option;
};

const loadDynamicOptions = async (configIndex) => {
    const config = conditionalConfigs.value[configIndex];
    if (!config || !config.dependsOnQuestionId) {
        dynamicOptions.value = [];
        return;
    }
    
    const question = props.availableQuestions.find(q => q.id == config.dependsOnQuestionId);
    if (!question) {
        dynamicOptions.value = [];
        return;
    }
    
    if (!['comunidad_autonoma', 'provincia', 'municipio'].includes(question.slug)) {
        dynamicOptions.value = question.options || [];
        return;
    }
    
    const loadingTimeout = setTimeout(() => {
        loadingOptions.value = true;
    }, 200);
    
    try {
        let url = '';
        const params = new URLSearchParams();
        
        if (question.slug === 'comunidad_autonoma') {
            url = '/admin/searchCCAA';
        } else if (question.slug === 'provincia') {
            url = '/admin/searchProvincias';
            const ccaaCondition = findConditionBySlug('comunidad_autonoma');
            if (ccaaCondition) {
                params.append('ccaa', ccaaCondition.expectedValue);
            }
        } else if (question.slug === 'municipio') {
            url = '/admin/searchMunicipios';
            const provinciaCondition = findConditionBySlug('provincia');
            if (provinciaCondition) {
                params.append('provincia', provinciaCondition.expectedValue);
            }
        }
        
        if (url) {
            const fullUrl = params.toString() ? `${url}?${params.toString()}` : url;
            const response = await fetch(fullUrl);
            const data = await response.json();
            
            if (Array.isArray(data)) {
                dynamicOptions.value = data.map(item => ({
                    value: item.id || item.value,
                    text: item.nombre || item.text || item
                }));
            } else if (typeof data === 'object') {
                dynamicOptions.value = Object.entries(data).map(([key, value]) => ({
                    value: key,
                    text: value
                }));
            } else {
                dynamicOptions.value = [];
            }
        }
    } catch (error) {
        console.error('Error cargando opciones dinámicas:', error);
        dynamicOptions.value = [];
    } finally {
        clearTimeout(loadingTimeout);
        loadingOptions.value = false;
    }
};

const findConditionBySlug = (slug) => {
    for (const question of props.availableQuestions) {
        if (question.slug === slug && question.condition) {
            return question.condition;
        }
    }
    return null;
};

const getSelectedQuestion = (config) => {
    if (!config || !config.dependsOnQuestionId) return null;
    return props.availableQuestions.find(q => q.id == config.dependsOnQuestionId);
};

const closeModal = () => {
    conditionalConfigs.value = [];
    emit("close");
};

const saveConfiguration = () => {
    const validConfigs = conditionalConfigs.value.filter(
        (config) =>
            config.dependsOnQuestionId &&
            config.conditionType &&
            config.expectedValue &&
            config.options.length > 0,
    );

    if (validConfigs.length === 0) {
        emit("save", null);
    } else {
        const configuration = {
            defaultOptions: props.availableOptions.map(
                (option) => option.value || option,
            ),
            conditionalConfigs: validConfigs,
        };
        emit("save", configuration);
    }
    
    closeModal();
};
</script>

<style scoped>
.max-h-40 {
    max-height: 10rem;
}

.hover\:bg-gray-50:hover {
    background-color: #f9fafb;
}

.bg-blue-50 {
    background-color: #eff6ff;
}

.border-blue-200 {
    border-color: #bfdbfe;
}

.bg-green-50 {
    background-color: #f0fdf4;
}

.border-green-200 {
    border-color: #bbf7d0;
}
</style>
