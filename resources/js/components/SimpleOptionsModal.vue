<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div
            class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-hidden flex flex-col"
        >
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">
                        Configurar opciones para la pregunta
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
                            <strong>Selecciona las opciones</strong> que quieres
                            mostrar para esta pregunta en este conviviente
                            específico.
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-lg font-medium text-gray-800">
                            Opciones disponibles
                        </h4>
                        <div class="flex space-x-2">
                            <button
                                @click="selectAllOptions"
                                class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors"
                            >
                                <i class="fas fa-check-double mr-1"></i>
                                Seleccionar todas
                            </button>
                            <button
                                @click="deselectAllOptions"
                                class="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 transition-colors"
                            >
                                <i class="fas fa-times mr-1"></i>
                                Deseleccionar todas
                            </button>
                        </div>
                    </div>

                    <div
                        class="space-y-2 max-h-60 overflow-y-auto border border-gray-200 rounded-lg p-3"
                    >
                        <label
                            v-for="(option, optionIndex) in availableOptions"
                            :key="optionIndex"
                            class="flex items-center p-2 hover:bg-gray-50 cursor-pointer rounded transition-colors"
                            :class="{
                                'bg-green-50 border border-green-200':
                                    selectedOptions.includes(
                                        option.value || option,
                                    ),
                                'bg-white border border-gray-200':
                                    !selectedOptions.includes(
                                        option.value || option,
                                    ),
                            }"
                        >
                            <input
                                type="checkbox"
                                :value="option.value || option"
                                v-model="selectedOptions"
                                class="mr-3 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                            />
                            <div class="flex-1">
                                <span class="text-sm font-medium text-gray-900">
                                    {{ option.text || option }}
                                </span>
                                <span
                                    v-if="option.value !== option.text"
                                    class="text-xs text-gray-500 ml-2"
                                >
                                    ({{ option.value }})
                                </span>
                            </div>
                        </label>
                    </div>

                    <div
                        v-if="availableOptions.length === 0"
                        class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg"
                    >
                        <i
                            class="fas fa-exclamation-triangle text-3xl mb-2"
                        ></i>
                        <p>No hay opciones disponibles para esta pregunta</p>
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
                    @click="saveOptions"
                    :disabled="selectedOptions.length === 0"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <i class="fas fa-check mr-2"></i>
                    Guardar opciones
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
    selectedOptions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["close", "save"]);

const selectedOptions = ref([]);

const questionText = computed(() => {
    return props.question ? props.question.text : "";
});

watch(
    () => props.show,
    (newValue) => {
        if (newValue) {
            // Inicializar con todas las opciones seleccionadas por defecto
            selectedOptions.value = props.availableOptions.map(
                (option) => option.value || option,
            );
        }
    },
);

watch(
    () => props.selectedOptions,
    (newValue) => {
        if (newValue && newValue.length > 0) {
            selectedOptions.value = [...newValue];
        }
    },
    { immediate: true },
);

const selectAllOptions = () => {
    selectedOptions.value = props.availableOptions.map(
        (option) => option.value || option,
    );
};

const deselectAllOptions = () => {
    selectedOptions.value = [];
};

const closeModal = () => {
    selectedOptions.value = [];
    emit("close");
};

const saveOptions = () => {
    emit("save", selectedOptions.value);
    closeModal();
};
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
