<template>
    <div class="space-y-4">
        <h4 class="font-medium text-gray-800 mb-3">
            Configuración del grupo de requisitos
        </h4>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Lógica del grupo <span class="text-red-500">*</span>
            </label>
            <select
                :value="groupLogic"
                @input="$emit('update:groupLogic', $event.target.value)"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                required
            >
                <option value="AND">TODOS deben cumplirse (AND)</option>
                <option value="OR">AL MENOS UNO debe cumplirse (OR)</option>
            </select>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h5 class="font-medium text-blue-900 mb-2 flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                Reglas del grupo
            </h5>
            <p class="text-sm text-blue-700">
                {{
                    groupLogic === "AND"
                        ? "Todas las reglas deben cumplirse para que el grupo sea válido."
                        : "Al menos una regla debe cumplirse para que el grupo sea válido."
                }}
            </p>
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h5 class="font-medium text-gray-700">
                    Reglas ({{ rules.length }})
                </h5>
                <div class="flex gap-2">
                    <button
                        @click="addRule"
                        type="button"
                        class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                    >
                        <i class="fas fa-plus mr-2"></i>Añadir regla
                    </button>
                    <button
                        @click="addNestedGroup"
                        type="button"
                        class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                    >
                        <i class="fas fa-layer-group mr-2"></i>Grupo anidado
                    </button>
                </div>
            </div>

            <div
                v-if="rules.length === 0"
                class="text-center py-8 text-gray-500"
            >
                <i class="fas fa-list text-2xl mb-2"></i>
                <p>No hay reglas configuradas</p>
                <p class="text-sm">Añade al menos una regla para el grupo</p>
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="(rule, index) in rules"
                    :key="index"
                    :class="[
                        'bg-white border rounded-lg p-4',
                        rule.type === 'group'
                            ? 'border-blue-300 bg-blue-50'
                            : 'border-gray-200',
                    ]"
                >
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <h6 class="font-medium text-gray-800">
                                <i
                                    v-if="rule.type === 'group'"
                                    class="fas fa-layer-group mr-2 text-blue-600"
                                ></i>
                                {{
                                    rule.type === "group"
                                        ? `Grupo ${index + 1}`
                                        : `Regla ${index + 1}`
                                }}
                            </h6>
                            <span
                                v-if="rule.type === 'group'"
                                class="px-2 py-0.5 text-xs rounded-full"
                                :class="
                                    (rule.group_logic || 'AND') === 'AND'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-amber-100 text-amber-800'
                                "
                            >
                                {{
                                    (rule.group_logic || "AND") === "AND"
                                        ? "TODOS"
                                        : "AL MENOS UNO"
                                }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="hidden md:inline text-xs text-gray-500 max-w-[260px] truncate"
                                >{{ summary(rule) }}</span
                            >
                            <button
                                @click="duplicateRule(index)"
                                type="button"
                                class="p-1 text-gray-500 hover:text-gray-700"
                                title="Duplicar"
                            >
                                <i class="fas fa-copy"></i>
                            </button>
                            <button
                                @click="removeRule(index)"
                                type="button"
                                class="p-1 text-red-500 hover:text-red-700"
                                :title="
                                    rule.type === 'group'
                                        ? 'Eliminar grupo'
                                        : 'Eliminar regla'
                                "
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Grupo anidado -->
                    <div v-if="rule.type === 'group'" class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Lógica del grupo anidado
                                </label>
                                <select
                                    :value="rule.group_logic || 'AND'"
                                    @input="
                                        updateRule(
                                            index,
                                            'group_logic',
                                            $event.target.value,
                                        )
                                    "
                                    class="w-full px-3 py-2 border border-blue-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent bg-white"
                                >
                                    <option value="AND">
                                        TODOS deben cumplirse (AND)
                                    </option>
                                    <option value="OR">
                                        AL MENOS UNO debe cumplirse (OR)
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div
                            class="ml-3 md:ml-4 border-l-2 border-blue-200 pl-3 md:pl-4"
                        >
                            <GroupPreRequisitoForm
                                :group-logic="rule.group_logic || 'AND'"
                                :rules="rule.rules || []"
                                :available-questions="availableQuestions"
                                @update:groupLogic="
                                    (val) =>
                                        updateRule(index, 'group_logic', val)
                                "
                                @update:rules="
                                    (val) => updateRule(index, 'rules', val)
                                "
                            />
                        </div>
                    </div>

                    <!-- Regla simple -->
                    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Pregunta <span class="text-red-500">*</span>
                            </label>
                            <Multiselect
                                :key="`question-select-${index}-${rule.question_id || 'empty'}`"
                                v-model="selectedQuestions[index]"
                                @select="onSelectQuestion(index, $event)"
                                @remove="onRemoveQuestion(index)"
                                :options="availableQuestions"
                                :searchable="true"
                                :close-on-select="true"
                                :show-labels="false"
                                :multiple="false"
                                :allow-empty="true"
                                placeholder="Buscar y seleccionar pregunta..."
                                label="text"
                                track-by="id"
                                :loading="false"
                                :internal-search="true"
                                :clear-on-select="false"
                                :preserve-search="true"
                                :preserve-placeholder="true"
                                :select-label="'Presiona Enter para seleccionar'"
                                :selected-label="'Seleccionado'"
                                :deselect-label="'Presiona Enter para quitar'"
                                :no-options-label="'No hay preguntas disponibles'"
                                :no-results-label="'No se encontraron preguntas'"
                                class="multiselect-custom"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Operador <span class="text-red-500">*</span>
                            </label>
                            <select
                                :value="rule.operator"
                                @input="
                                    updateRule(
                                        index,
                                        'operator',
                                        $event.target.value,
                                    )
                                "
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

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Valor <span class="text-red-500">*</span>
                            </label>
                            <div
                                v-if="
                                    ['exists', 'not_exists'].includes(
                                        rule.operator,
                                    )
                                "
                                class="text-sm text-gray-500"
                            >
                                Este operador no requiere valor.
                            </div>
                            <template v-else>
                                <div
                                    v-if="
                                        ruleQuestion(index)?.type === 'boolean'
                                    "
                                    class="flex gap-4"
                                >
                                    <label class="flex items-center">
                                        <input
                                            type="radio"
                                            :name="`rule-${index}-bool`"
                                            value="1"
                                            :checked="rule.value === '1'"
                                            @change="
                                                updateRule(index, 'value', '1')
                                            "
                                            class="mr-2"
                                        />
                                        Sí
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            type="radio"
                                            :name="`rule-${index}-bool`"
                                            value="0"
                                            :checked="rule.value === '0'"
                                            @change="
                                                updateRule(index, 'value', '0')
                                            "
                                            class="mr-2"
                                        />
                                        No
                                    </label>
                                </div>
                                <select
                                    v-else-if="
                                        ruleQuestion(index)?.type ===
                                            'select' &&
                                        Array.isArray(
                                            ruleQuestion(index)?.options,
                                        )
                                    "
                                    :value="
                                        getDisplayValue(
                                            rule,
                                            ruleQuestion(index),
                                        )
                                    "
                                    @input="
                                        updateRule(
                                            index,
                                            'value',
                                            $event.target.value,
                                        )
                                    "
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">
                                        Selecciona una opción
                                    </option>
                                    <option
                                        v-for="(opt, idx) in ruleQuestion(index)
                                            .options"
                                        :key="idx"
                                        :value="idx.toString()"
                                    >
                                        {{ opt }}
                                    </option>
                                </select>
                                <div
                                    v-else-if="
                                        ruleQuestion(index)?.type ===
                                            'multiple' &&
                                        Array.isArray(
                                            ruleQuestion(index)?.options,
                                        )
                                    "
                                    class="space-y-2"
                                >
                                    <div
                                        class="border border-gray-300 rounded-md p-2 max-h-36 overflow-y-auto"
                                    >
                                        <label
                                            v-for="(opt, idx) in ruleQuestion(
                                                index,
                                            ).options"
                                            :key="idx"
                                            class="flex items-center space-x-2 py-1"
                                        >
                                            <input
                                                type="checkbox"
                                                :value="idx.toString()"
                                                :checked="
                                                    Array.isArray(rule.value)
                                                        ? rule.value.includes(
                                                              idx.toString(),
                                                          )
                                                        : false
                                                "
                                                @change="
                                                    onToggleMulti(
                                                        index,
                                                        idx.toString(),
                                                        $event.target.checked,
                                                    )
                                                "
                                                class="rounded text-blue-600 border-gray-300"
                                            />
                                            <span
                                                class="text-sm text-gray-700"
                                                >{{ opt }}</span
                                            >
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        Usa operadores como "in" o "contains"
                                        para listas.
                                    </p>
                                </div>
                                <div
                                    v-else-if="
                                        ruleQuestion(index)?.type === 'date'
                                    "
                                    class="space-y-2"
                                >
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-3 gap-2"
                                    >
                                        <select
                                            :value="rule.value_type || 'exact'"
                                            @input="
                                                updateRule(
                                                    index,
                                                    'value_type',
                                                    $event.target.value,
                                                )
                                            "
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        >
                                            <option value="exact">
                                                Fecha exacta
                                            </option>
                                            <option value="relative_date">
                                                Fecha relativa (hace X)
                                            </option>
                                            <option value="age_minimum">
                                                Edad mínima
                                            </option>
                                            <option value="age_maximum">
                                                Edad máxima
                                            </option>
                                            <option value="age_range">
                                                Rango de edad
                                            </option>
                                        </select>
                                        <select
                                            v-if="
                                                [
                                                    'age_minimum',
                                                    'age_maximum',
                                                    'age_range',
                                                ].includes(rule.value_type)
                                            "
                                            :value="rule.age_unit || 'years'"
                                            @input="
                                                updateRule(
                                                    index,
                                                    'age_unit',
                                                    $event.target.value,
                                                )
                                            "
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        >
                                            <option value="years">años</option>
                                            <option value="months">
                                                meses
                                            </option>
                                            <option value="days">días</option>
                                        </select>
                                    </div>
                                    <input
                                        v-if="
                                            (rule.value_type || 'exact') ===
                                            'exact'
                                        "
                                        type="date"
                                        :value="rule.value || ''"
                                        @input="
                                            updateRule(
                                                index,
                                                'value',
                                                $event.target.value,
                                            )
                                        "
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                    <div
                                        v-else-if="
                                            rule.value_type === 'relative_date'
                                        "
                                        class="grid grid-cols-3 gap-2"
                                    >
                                        <input
                                            type="number"
                                            min="0"
                                            :value="rule.relative_amount || ''"
                                            @input="
                                                updateRule(
                                                    index,
                                                    'relative_amount',
                                                    $event.target.value,
                                                )
                                            "
                                            class="col-span-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            placeholder="Cantidad"
                                        />
                                        <select
                                            :value="
                                                rule.relative_unit || 'years'
                                            "
                                            @input="
                                                updateRule(
                                                    index,
                                                    'relative_unit',
                                                    $event.target.value,
                                                )
                                            "
                                            class="col-span-2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        >
                                            <option value="years">años</option>
                                            <option value="months">
                                                meses
                                            </option>
                                            <option value="days">días</option>
                                        </select>
                                    </div>
                                    <div
                                        v-else-if="
                                            [
                                                'age_minimum',
                                                'age_maximum',
                                            ].includes(rule.value_type)
                                        "
                                        class="grid grid-cols-1 md:grid-cols-3 gap-2"
                                    >
                                        <input
                                            type="number"
                                            min="0"
                                            :value="rule.value || ''"
                                            @input="
                                                updateRule(
                                                    index,
                                                    'value',
                                                    $event.target.value,
                                                )
                                            "
                                            class="col-span-2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            :placeholder="
                                                rule.value_type ===
                                                'age_minimum'
                                                    ? 'Edad mínima'
                                                    : 'Edad máxima'
                                            "
                                        />
                                    </div>
                                    <div
                                        v-else-if="
                                            rule.value_type === 'age_range'
                                        "
                                        class="space-y-2"
                                    >
                                        <div class="grid grid-cols-3 gap-2">
                                            <input
                                                type="number"
                                                min="0"
                                                :value="rule.value || ''"
                                                @input="
                                                    updateRule(
                                                        index,
                                                        'value',
                                                        $event.target.value,
                                                    )
                                                "
                                                class="col-span-2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                placeholder="Edad mínima"
                                            />
                                        </div>
                                        <div class="grid grid-cols-3 gap-2">
                                            <input
                                                type="number"
                                                min="0"
                                                :value="rule.value2 || ''"
                                                @input="
                                                    updateRule(
                                                        index,
                                                        'value2',
                                                        $event.target.value,
                                                    )
                                                "
                                                class="col-span-2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                placeholder="Edad máxima"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <input
                                    v-else-if="
                                        ruleQuestion(index)?.type === 'integer'
                                    "
                                    type="number"
                                    :value="rule.value || ''"
                                    @input="
                                        updateRule(
                                            index,
                                            'value',
                                            $event.target.value,
                                        )
                                    "
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Número"
                                />
                                <input
                                    v-else
                                    type="text"
                                    :value="rule.value || ''"
                                    @input="
                                        updateRule(
                                            index,
                                            'value',
                                            $event.target.value,
                                        )
                                    "
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :placeholder="
                                        getValuePlaceholder(rule.question_id)
                                    "
                                />
                            </template>
                        </div>
                    </div>

                    <div
                        v-if="
                            rule.operator === 'between' &&
                            rule.value_type !== 'age_range'
                        "
                        class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3"
                    >
                        <div class="md:col-start-3">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                                >Segundo valor</label
                            >
                            <input
                                type="text"
                                :value="rule.value2 || ''"
                                @input="
                                    updateRule(
                                        index,
                                        'value2',
                                        $event.target.value,
                                    )
                                "
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Valor máximo"
                            />
                        </div>
                    </div>

                    <div
                        v-if="ruleQuestion(index)"
                        class="mt-3 p-2 bg-gray-50 rounded text-sm text-gray-600"
                    >
                        <strong>Tipo de pregunta:</strong>
                        {{ getQuestionTypeText(ruleQuestion(index).type) }}
                        <div
                            v-if="
                                ruleQuestion(index).options &&
                                ruleQuestion(index).options.length > 0
                            "
                            class="mt-1"
                        >
                            <strong>Opciones:</strong>
                            <span class="ml-1">
                                {{
                                    ruleQuestion(index)
                                        .options.map((opt, i) => `${i}: ${opt}`)
                                        .join(", ")
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, ref, watch } from "vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.min.css";

export default {
    name: "GroupPreRequisitoForm",
    components: {
        Multiselect,
    },
    props: {
        groupLogic: {
            type: String,
            default: "AND",
        },
        rules: {
            type: Array,
            default: () => [],
        },
        availableQuestions: {
            type: Array,
            default: () => [],
        },
    },
    emits: ["update:groupLogic", "update:rules"],
    setup(props, { emit }) {
        const selectedQuestions = ref({});

        const getRuleQuestionById = (questionId) =>
            props.availableQuestions.find((q) => q.id == questionId);

        const getSelectValueIndex = (question, value) => {
            if (
                !question ||
                !question.options ||
                !Array.isArray(question.options)
            ) {
                return value;
            }

            if (typeof value === "string" && /^\d+$/.test(value)) {
                return value;
            }

            const index = question.options.findIndex(
                (option) => option === value,
            );
            return index >= 0 ? index.toString() : value;
        };

        const getDisplayValue = (rule, question) => {
            if (!rule.value) return "";

            if (question && question.type === "select") {
                return getSelectValueIndex(question, rule.value);
            }

            return rule.value;
        };

        const initializeSelectedQuestions = () => {
            const selected = {};
            props.rules.forEach((rule, index) => {
                if (rule.question_id) {
                    selected[index] = getRuleQuestionById(rule.question_id);
                }
            });
            selectedQuestions.value = selected;
        };

        initializeSelectedQuestions();

        watch(
            () => props.rules,
            () => {
                initializeSelectedQuestions();
            },
            { deep: true },
        );

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
        const ruleQuestion = (index) => {
            const question = getRuleQuestionById(
                props.rules[index]?.question_id,
            );
            return question;
        };

        const getSelectedQuestion = (questionId) => {
            if (!questionId) return null;
            const question = getRuleQuestionById(questionId);
            return question;
        };

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

        const getValuePlaceholder = (questionId) => {
            const question = getRuleQuestionById(questionId);
            if (!question) return "Valor esperado";

            switch (question.type) {
                case "boolean":
                    return "1 (Sí) o 0 (No)";
                case "integer":
                    return "Número entero";
                case "date":
                    return "YYYY-MM-DD";
                case "select":
                    return "Selecciona una opción";
                default:
                    return "Valor esperado";
            }
        };

        const addRule = () => {
            const newRules = [
                ...props.rules,
                {
                    type: "simple",
                    question_id: "",
                    operator: "",
                    value: "",
                    value2: null,
                    value_type: "exact",
                    age_unit: "years",
                    order: props.rules.length,
                },
            ];
            emit("update:rules", newRules);
        };

        const addNestedGroup = () => {
            const newRules = [
                ...props.rules,
                {
                    type: "group",
                    group_logic: "AND",
                    rules: [],
                    order: props.rules.length,
                },
            ];
            emit("update:rules", newRules);
        };

        const removeRule = (index) => {
            const newRules = props.rules.filter((_, i) => i !== index);
            emit("update:rules", newRules);
        };

        const updateRule = (index, field, value) => {
            const newRules = [...props.rules];
            newRules[index] = { ...newRules[index], [field]: value };
            emit("update:rules", newRules);
        };

        // Resumen compacto para cabecera
        const summary = (rule) => {
            if (rule?.type === "group") {
                const count = Array.isArray(rule.rules) ? rule.rules.length : 0;
                const logic =
                    (rule.group_logic || "AND") === "AND"
                        ? "TODOS"
                        : "AL MENOS UNO";
                return `${logic} · ${count} regla(s)`;
            }
            const q = getRuleQuestionById(rule?.question_id);
            if (!q) return "";
            const op = rule.operator || "";
            let val = rule.value;
            if (q.type === "multiple" && Array.isArray(val)) {
                val = `${val.length} seleccionada(s)`;
            }
            if (q.type === "date" && rule.value_type) {
                if (rule.value_type === "age_range") {
                    val = `${rule.value || "?"}-${rule.value2 || "?"} ${rule.age_unit || ""}`;
                } else if (
                    rule.value_type === "age_minimum" ||
                    rule.value_type === "age_maximum"
                ) {
                    val = `${rule.value || "?"} ${rule.age_unit || ""}`;
                }
            }
            return `${q.text} ${op} ${val ?? ""}`.trim();
        };

        // Duplicar regla/grupo
        const duplicateRule = (index) => {
            const source = props.rules[index];
            if (!source) return;
            const copy = JSON.parse(JSON.stringify(source));
            const newRules = [...props.rules];
            newRules.splice(index + 1, 0, copy);
            emit("update:rules", newRules);
        };

        const onSelectQuestion = (index, question) => {
            const questionId = question?.id || question;
            const q =
                typeof question === "object"
                    ? question
                    : getRuleQuestionById(questionId);

            selectedQuestions.value[index] = q;

            const base = { question_id: questionId };
            if (q) {
                if (q.type === "multiple") {
                    base.value = Array.isArray(props.rules[index]?.value)
                        ? props.rules[index]?.value
                        : [];
                } else if (q.type === "boolean") {
                    base.value = props.rules[index]?.value === "0" ? "0" : "1";
                } else if (q.type === "select") {
                    base.value = props.rules[index]?.value || "0";
                } else if (q.type === "date") {
                    base.value_type = props.rules[index]?.value_type || "exact";
                    base.age_unit = props.rules[index]?.age_unit || "years";
                    base.value = props.rules[index]?.value || "";
                    base.value2 = props.rules[index]?.value2 || null;
                } else {
                    base.value = props.rules[index]?.value || "";
                }
            }
            const newRules = [...props.rules];
            newRules[index] = { ...newRules[index], ...base };
            emit("update:rules", newRules);
        };

        const onRemoveQuestion = (index) => {
            selectedQuestions.value[index] = null;

            const newRules = [...props.rules];
            newRules[index] = { ...newRules[index], question_id: "" };
            emit("update:rules", newRules);
        };

        const onToggleMulti = (index, optValue, checked) => {
            const current = Array.isArray(props.rules[index]?.value)
                ? [...props.rules[index].value]
                : [];
            const exists = current.includes(optValue);
            let next = current;
            if (checked && !exists) next.push(optValue);
            if (!checked && exists)
                next = current.filter((v) => v !== optValue);
            updateRule(index, "value", next);
        };

        return {
            selectedQuestions,
            operatorOptions,
            ruleQuestion,
            getQuestionTypeText,
            getValuePlaceholder,
            getSelectedQuestion,
            getDisplayValue,
            addRule,
            addNestedGroup,
            removeRule,
            updateRule,
            summary,
            duplicateRule,
            onSelectQuestion,
            onRemoveQuestion,
            onToggleMulti,
        };
    },
};
</script>

<style scoped>
.multiselect-custom {
    min-height: 42px;
}

.multiselect-custom :deep(.multiselect__tags) {
    min-height: 42px;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 2.5rem 0.5rem 0.75rem;
}

.multiselect-custom :deep(.multiselect__tags:focus-within) {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.multiselect-custom :deep(.multiselect__placeholder) {
    color: #6b7280;
    padding-top: 0.25rem;
    margin-bottom: 0;
}

.multiselect-custom :deep(.multiselect__single) {
    background: transparent;
    padding: 0;
    margin-bottom: 0;
    line-height: 1.5;
}

.multiselect-custom :deep(.multiselect__input) {
    background: transparent;
    border: none;
    padding: 0;
    margin: 0;
    min-height: auto;
    line-height: 1.5;
}

.multiselect-custom :deep(.multiselect__input:focus) {
    outline: none;
    box-shadow: none;
}

.multiselect-custom :deep(.multiselect__select) {
    height: 40px;
    right: 1px;
    top: 1px;
}

.multiselect-custom :deep(.multiselect__select:before) {
    border-color: #6b7280 transparent transparent;
    border-width: 5px 5px 0;
    top: 50%;
    transform: translateY(-50%);
}

.multiselect-custom :deep(.multiselect__content-wrapper) {
    border: 1px solid #d1d5db;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.multiselect-custom :deep(.multiselect__option) {
    padding: 0.5rem 0.75rem;
    min-height: auto;
}

.multiselect-custom :deep(.multiselect__option--highlight) {
    background: #3b82f6;
    color: white;
}

.multiselect-custom :deep(.multiselect__option--selected) {
    background: #f3f4f6;
    color: #374151;
    font-weight: 500;
}

.multiselect-custom
    :deep(.multiselect__option--selected.multiselect__option--highlight) {
    background: #3b82f6;
    color: white;
}
</style>
