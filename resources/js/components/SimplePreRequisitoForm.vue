<template>
    <div class="space-y-4">
        <h4 class="font-medium text-gray-800 mb-3">
            Configuración del requisito simple
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Pregunta <span class="text-red-500">*</span>
                </label>
                <select
                    :value="questionId"
                    @input="$emit('update:questionId', $event.target.value)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                >
                    <option value="">Seleccionar pregunta</option>
                    <option
                        v-for="question in availableQuestions"
                        :key="question.id"
                        :value="question.id"
                    >
                        {{ question.text }}
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Operador <span class="text-red-500">*</span>
                </label>
                <select
                    :value="operator"
                    @input="$emit('update:operator', $event.target.value)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                >
                    <option value="">Seleccionar operador</option>
                    <option
                        v-for="(label, value) in operatorOptions"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </select>
            </div>
        </div>

        <div v-if="selectedQuestion" class="bg-gray-50 p-4 rounded-lg">
            <h5 class="font-medium text-gray-700 mb-2">
                Información de la pregunta:
            </h5>
            <div class="text-sm text-gray-600">
                <p>
                    <strong>Tipo:</strong>
                    {{ getQuestionTypeText(selectedQuestion.type) }}
                </p>
                <p v-if="selectedQuestion.sub_text">
                    <strong>Descripción:</strong>
                    {{ selectedQuestion.sub_text }}
                </p>
                <div
                    v-if="
                        selectedQuestion.options &&
                        selectedQuestion.options.length > 0
                    "
                >
                    <strong>Opciones disponibles:</strong>
                    <ul class="mt-1 ml-4 list-disc">
                        <li
                            v-for="(option, index) in selectedQuestion.options"
                            :key="index"
                        >
                            {{ index }}: {{ option }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Valor esperado <span class="text-red-500">*</span>
                </label>
                <input
                    v-if="!isMultipleValueOperator"
                    :value="value"
                    @input="handleValueChange"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    :placeholder="getValuePlaceholder()"
                />
                <div v-else class="space-y-2">
                    <input
                        v-for="(val, index) in valueArray"
                        :key="index"
                        :value="val"
                        @input="updateValueArray(index, $event.target.value)"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :placeholder="`Valor ${index + 1}`"
                    />
                    <button
                        @click="addValue"
                        type="button"
                        class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                    >
                        <i class="fas fa-plus mr-1"></i>Añadir valor
                    </button>
                </div>
            </div>

            <div v-if="needsSecondValue">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Segundo valor (para operador "entre")
                </label>
                <input
                    :value="value2"
                    @input="$emit('update:value2', $event.target.value)"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Valor máximo"
                />
            </div>
        </div>

        <div
            v-if="selectedQuestion && selectedQuestion.type === 'boolean'"
            class="bg-yellow-50 border border-yellow-200 rounded-lg p-3"
        >
            <div class="flex">
                <i class="fas fa-info-circle text-yellow-600 mt-1 mr-3"></i>
                <div>
                    <h5 class="font-medium text-yellow-800">
                        Pregunta de tipo Sí/No
                    </h5>
                    <p class="text-sm text-yellow-700 mt-1">
                        Para preguntas booleanas, usa "1" o "true" para "Sí" y
                        "0" o "false" para "No"
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, ref } from "vue";

export default {
    name: "SimplePreRequisitoForm",
    props: {
        questionId: {
            type: [String, Number],
            default: null,
        },
        operator: {
            type: String,
            default: "",
        },
        value: {
            type: [String, Number, Array],
            default: null,
        },
        value2: {
            type: [String, Number],
            default: null,
        },
        availableQuestions: {
            type: Array,
            default: () => [],
        },
    },
    emits: [
        "update:questionId",
        "update:operator",
        "update:value",
        "update:value2",
    ],
    setup(props, { emit }) {
        const valueArray = ref([]);

        const operatorOptions = {
            "==": "Igual a",
            "!=": "Distinto de",
            ">": "Mayor que",
            ">=": "Mayor o igual que",
            "<": "Menor que",
            "<=": "Menor o igual que",
            contains: "Contiene",
            not_contains: "No contiene",
            between: "Entre",
            in: "En la lista",
            not_in: "No en la lista",
            exists: "Existe",
            not_exists: "No existe",
        };

        const selectedQuestion = computed(() => {
            return props.availableQuestions.find(
                (q) => q.id == props.questionId,
            );
        });

        const isMultipleValueOperator = computed(() => {
            return ["in", "not_in"].includes(props.operator);
        });

        const needsSecondValue = computed(() => {
            return props.operator === "between";
        });

        const getQuestionTypeText = (type) => {
            const types = {
                string: "Texto",
                integer: "Número",
                boolean: "Sí / No",
                select: "Selección",
                multiple: "Selección múltiple",
                date: "Fecha",
                info: "Informativa",
            };
            return types[type] || type;
        };

        const getValuePlaceholder = () => {
            if (!selectedQuestion.value) return "Valor esperado";

            switch (selectedQuestion.value.type) {
                case "boolean":
                    return "1 (Sí) o 0 (No)";
                case "integer":
                    return "Número entero";
                case "date":
                    return "YYYY-MM-DD";
                case "select":
                    return "Índice de la opción (0, 1, 2...)";
                default:
                    return "Valor esperado";
            }
        };

        const handleValueChange = (event) => {
            emit("update:value", event.target.value);
        };

        const updateValueArray = (index, newValue) => {
            valueArray.value[index] = newValue;
            emit(
                "update:value",
                valueArray.value.filter((v) => v !== ""),
            );
        };

        const addValue = () => {
            valueArray.value.push("");
        };

        // Inicializar valueArray si value es un array
        if (Array.isArray(props.value)) {
            valueArray.value = [...props.value];
        }

        return {
            valueArray,
            operatorOptions,
            selectedQuestion,
            isMultipleValueOperator,
            needsSecondValue,
            getQuestionTypeText,
            getValuePlaceholder,
            handleValueChange,
            updateValueArray,
            addValue,
        };
    },
};
</script>
