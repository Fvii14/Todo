<template>
    <div class="space-y-4">
        <div
            v-if="normalizedOptions.length > 0"
            class="space-y-2"
        >
            <div
                v-for="(option, index) in normalizedOptions"
                :key="index"
                @click="toggleOption(option)"
                :class="[
                    'w-full p-4 rounded-lg border-2 cursor-pointer transition-all duration-200 hover:shadow-md',
                    isSelected(option)
                        ? isNoneOfTheAbove(option)
                            ? 'border-orange-500 bg-orange-100 text-orange-900'
                            : 'border-blue-500 bg-blue-50 text-blue-900'
                        : isNoneOfTheAbove(option)
                            ? 'border-orange-300 bg-orange-50 text-orange-700 hover:border-orange-400'
                            : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300',
                    blocked ? 'opacity-50 cursor-not-allowed' : 'hover:scale-[1.02]'
                ]"
                :disabled="blocked"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div
                            :class="[
                                'w-5 h-5 rounded-full border-2 mr-3 flex items-center justify-center transition-all duration-200',
                                isSelected(option)
                                    ? isNoneOfTheAbove(option)
                                        ? 'border-orange-500 bg-orange-500'
                                        : 'border-blue-500 bg-blue-500'
                                    : 'border-gray-300'
                            ]"
                        >
                            <i
                                v-if="isSelected(option)"
                                class="fas fa-check text-white text-xs"
                            ></i>
                        </div>
                        <span class="text-sm font-medium">
                            {{ option.text || option }}
                        </span>
                    </div>
                    <div
                        v-if="isSelected(option)"
                        :class="isNoneOfTheAbove(option) ? 'text-orange-500' : 'text-blue-500'"
                    >
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-gray-500 text-sm">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            No hay opciones disponibles para esta pregunta
        </div>

        <div
            v-if="question.question?.max_selections || question.question?.min_selections"
            class="bg-gray-50 rounded-lg p-3"
        >
            <div
                v-if="question.question?.max_selections"
                class="text-xs text-gray-600 mb-1"
            >
                <i class="fas fa-info-circle mr-1"></i>
                Máximo {{ question.question.max_selections }} opción(es) seleccionable(s)
            </div>
            <div
                v-if="question.question?.min_selections"
                class="text-xs text-gray-600"
            >
                <i class="fas fa-info-circle mr-1"></i>
                Mínimo {{ question.question.min_selections }} opción(es) requerida(s)
            </div>
        </div>

        <div
            v-if="selectedValues.length > 0"
            class="text-sm text-gray-600 bg-blue-50 rounded-lg p-2"
        >
            <i class="fas fa-check-circle mr-1 text-blue-500"></i>
            {{ selectedValues.length }} opción(es) seleccionada(s)
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    questionIndex: {
        type: Number,
        required: true,
    },
    value: {
        type: [Array, String],
        default: () => [],
    },
    answers: {
        type: Object,
        default: () => ({}),
    },
    blocked: {
        type: Boolean,
        default: false,
    },
    options: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["update"]);

const selectedValues = ref([]);

// Convertir opciones a formato estándar si vienen como strings
const normalizedOptions = computed(() => {
    const optionsToUse = props.options.length > 0 ? props.options : (props.question.question?.options || []);

    const mappedOptions = optionsToUse.map((option) => {
        if (typeof option === "string") {
            return {
                value: option,
                text: option,
            };
        }
        return option;
    });

    const hasNoneOption = mappedOptions.some((opt) => {
        const text = opt.text || opt;
        if (!text) return false;
        const lower = String(text).toLowerCase();
        return lower.includes('ninguna') && (lower.includes('anterior') || lower.includes('otra'));
    });

    if (!hasNoneOption) {
        mappedOptions.push({ value: 'Ninguna de las anteriores', text: 'Ninguna de las anteriores' });
    }

    return mappedOptions;
});

const isSelected = (option) => {
    return selectedValues.value.some(selected => {
        const optionValue = option.value || option;
        const selectedValue = selected.value || selected;
        return optionValue === selectedValue;
    });
};

const isNoneOfTheAbove = (option) => {
    const optionText = option.text || option;
    return optionText.toLowerCase().includes('ninguna') && 
           (optionText.toLowerCase().includes('anterior') || optionText.toLowerCase().includes('otra'));
};

const toggleOption = (option) => {
    if (props.blocked) return;

    const optionValue = option.value || option;
    const optionText = option.text || option;
    const isCurrentlySelected = isSelected(option);
    const isNoneOfTheAbove = optionText.toLowerCase().includes('ninguna') && 
                            (optionText.toLowerCase().includes('anterior') || optionText.toLowerCase().includes('otra'));

    if (isCurrentlySelected) {
        selectedValues.value = selectedValues.value.filter(selected => {
            const selectedValue = selected.value || selected;
            return selectedValue !== optionValue;
        });
    } else {
        if (isNoneOfTheAbove) {
            selectedValues.value = [option];
        } else {
            const hasNoneSelected = selectedValues.value.some(selected => {
                const selectedText = selected.text || selected;
                return selectedText.toLowerCase().includes('ninguna') && 
                       (selectedText.toLowerCase().includes('anterior') || selectedText.toLowerCase().includes('otra'));
            });

            if (hasNoneSelected) {
                selectedValues.value = [option];
            } else {
                const maxSelections = props.question.question?.max_selections;
                if (maxSelections && selectedValues.value.length >= maxSelections) {
                    return;
                }

                selectedValues.value.push(option);
            }
        }
    }

    updateValue();
};

// Inicializar valor desde props
const initializeValue = () => {
    if (props.value) {
        if (Array.isArray(props.value)) {
            selectedValues.value = props.value.map((item) => {
                if (typeof item === "string") {
                    return { value: item, text: item };
                }
                return item;
            });
        } else if (typeof props.value === "string") {
            // Si viene como string, intentar parsear como JSON
            try {
                const parsed = JSON.parse(props.value);
                if (Array.isArray(parsed)) {
                    selectedValues.value = parsed.map((item) => ({
                        value: item,
                        text: item,
                    }));
                } else {
                    selectedValues.value = [
                        {
                            value: props.value,
                            text: props.value,
                        },
                    ];
                }
            } catch {
                // Si no se puede parsear, tratarlo como string simple
                selectedValues.value = [
                    {
                        value: props.value,
                        text: props.value,
                    },
                ];
            }
        }
    } else {
        selectedValues.value = [];
    }
};

// Actualizar valor cuando cambien las props
watch(
    () => props.value,
    () => {
        initializeValue();
    },
    { immediate: true },
);

// Emitir cambios
const updateValue = () => {
    // Convertir a array de strings para el backend
    const arrayValue = selectedValues.value.map((item) => {
        if (typeof item === "string") {
            return item;
        }
        return item.text || item.value || item;
    });

    // Enviar como JSON string para que el backend lo guarde como array literal
    emit("update", props.question.question.id, JSON.stringify(arrayValue));
};

// Validación
const isValid = computed(() => {
    const min = props.question.question?.min_selections || 0;
    const max = props.question.question?.max_selections;

    if (selectedValues.value.length < min) return false;
    if (max && selectedValues.value.length > max) return false;

    return true;
});

// Exponer validación al componente padre
defineExpose({
    isValid,
});

onMounted(() => {
    initializeValue();
});

watch(
    () => props.question.question?.id,
    () => {
        console.log("Question ID cambió, reinicializando");
        initializeValue();
    }
);
</script>

<style scoped>
.space-y-2 > div {
    transition: all 0.2s ease;
}

.space-y-2 > div:hover {
    transform: translateY(-1px);
}

.space-y-2 > div:active {
    transform: translateY(0);
}

.w-5.h-5 {
    transition: all 0.2s ease;
}

.space-y-2 > div:not(.opacity-50):hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.bg-blue-50 {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
