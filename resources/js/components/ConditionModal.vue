<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div
            class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto"
        >
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">
                    Configurar condiciones para:
                    {{ question?.text }}
                </h3>
                <button
                    @click="closeModal"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        1. Seleccionar persona:
                    </label>
                    <multiselect
                        v-model="selectedPerson"
                        :options="availablePersons"
                        :searchable="false"
                        :close-on-select="true"
                        :show-labels="false"
                        label="name"
                        track-by="id"
                        placeholder="Seleccionar persona..."
                        @select="onPersonSelect"
                        @remove="onPersonRemove"
                    >
                        <template slot="option" slot-scope="{ option }">
                            <div class="flex items-center space-x-2">
                                <i :class="option.icon" class="text-sm"></i>
                                <span>{{ option.name }}</span>
                            </div>
                        </template>
                        <template slot="singleLabel" slot-scope="{ option }">
                            <div class="flex items-center space-x-2">
                                <i :class="option.icon" class="text-sm"></i>
                                <span>{{ option.name }}</span>
                            </div>
                        </template>
                    </multiselect>
                </div>

                <div v-if="selectedPerson">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        2. Seleccionar pregunta:
                    </label>
                    <multiselect
                        v-model="selectedQuestion"
                        :options="questionsForSelectedPerson"
                        :searchable="true"
                        :close-on-select="true"
                        :show-labels="false"
                        :custom-label="customQuestionLabel"
                        track-by="id"
                        placeholder="Seleccionar pregunta..."
                        @select="onQuestionSelect"
                        @remove="onQuestionRemove"
                    >
                        <template #option="{ option }">
                            <div class="flex flex-col space-y-1">
                                <div class="flex items-center space-x-2">
                                    <i :class="option.icon || 'fas fa-question-circle'" class="text-sm text-gray-500"></i>
                                    <span>{{ option.text }}</span>
                                    <span class="text-xs text-gray-500">({{ questionTypes[option.type] || option.type }})</span>
                                </div>
                                <div v-if="option.slug" class="text-xs text-gray-400 ml-6 font-mono">
                                    {{ option.slug }}
                                </div>
                            </div>
                        </template>
                        <template #singleLabel="{ option }">
                            <div class="flex flex-col space-y-1">
                                <div class="flex items-center space-x-2">
                                    <i :class="option.icon || 'fas fa-question-circle'" class="text-sm text-gray-500"></i>
                                    <span>{{ option.text }}</span>
                                </div>
                                <div v-if="option.slug" class="text-xs text-gray-400 ml-6 font-mono">
                                    {{ option.slug }}
                                </div>
                            </div>
                        </template>
                    </multiselect>
                </div>

                <div v-if="selectedQuestion">
                    <label
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        3. Condición:
                    </label>
                    <div class="space-y-3">
                        <div
                            v-if="
                                selectedQuestion?.type ===
                                'boolean'
                            "
                        >
                            <label class="flex items-center space-x-2">
                                <input
                                    type="radio"
                                    v-model="booleanSelection"
                                    value="sí"
                                    class="text-green-600 focus:ring-green-500"
                                />
                                <span>Cuando la respuesta sea "Sí"</span>
                            </label>
                            <label class="flex items-center space-x-2">
                                <input
                                    type="radio"
                                    v-model="booleanSelection"
                                    value="no"
                                    class="text-green-600 focus:ring-green-500"
                                />
                                <span>Cuando la respuesta sea "No"</span>
                            </label>
                        </div>

                        <div
                            v-else-if="
                                selectedQuestion?.type === 'integer'
                            "
                        >
                            <div class="space-y-3">
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="equals"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>Igual a</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="not_equals"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>No igual a</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="greater_than"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>Mayor que</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="less_than"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>Menor que</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="greater_equal"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>Mayor o igual que</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="less_equal"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>Menor o igual que</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="is_null"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>Está vacío (IS NULL)</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="is_not_null"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>No está vacío (IS NOT NULL)</span>
                                </div>
                            </div>
                            <div v-if="!['is_null', 'is_not_null'].includes(form.conditionType)" class="mt-3">
                                <input
                                    v-model="form.expectedValue"
                                    type="number"
                                    placeholder="Introduce el valor numérico"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                            </div>
                        </div>

                        <div
                            v-else-if="
                                selectedQuestion?.type === 'date'
                            "
                        >
                            <div class="space-y-3">
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="equals"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>Igual a</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="not_equals"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>No igual a</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="greater_than"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>Después de</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="less_than"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>Antes de</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="greater_equal"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>Después de o igual a</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="less_equal"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>Antes de o igual a</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="is_null"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>Está vacío (IS NULL)</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        v-model="form.conditionType"
                                        value="is_not_null"
                                        class="text-green-600 focus:ring-green-500"
                                    />
                                    <span>No está vacío (IS NOT NULL)</span>
                                </div>
                            </div>
                            <div v-if="!['is_null', 'is_not_null'].includes(form.conditionType)" class="mt-3">
                                <input
                                    v-model="form.expectedValue"
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                            </div>
                        </div>

                        <div
                            v-else-if="
                                ['select', 'multiple', 'radio'].includes(
                                    selectedQuestion?.type,
                                )
                            "
                        >
                            <div class="flex items-center space-x-2 mb-2">
                                <input
                                    type="radio"
                                    v-model="form.conditionType"
                                    value="equals"
                                    class="text-green-600 focus:ring-green-500"
                                />
                                <span
                                    >Cuando la respuesta sea igual a:</span
                                >
                            </div>
                            <select
                                v-if="
                                    form.conditionType === 'equals'
                                "
                                v-model="form.expectedValue"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                :disabled="loadingOptions"
                            >
                                <option value="">
                                    {{ loadingOptions ? 'Cargando opciones...' : 'Seleccionar valor' }}
                                </option>
                                <option
                                    v-for="option in getDynamicOptions(selectedQuestion)"
                                    :key="option.value || option"
                                    :value="getOptionValue(option, selectedQuestion)"
                                >
                                    {{ option.text || option }}
                                </option>
                            </select>

                            <div class="flex items-center space-x-2 mt-2">
                                <input
                                    type="radio"
                                    v-model="form.conditionType"
                                    value="not_equals"
                                    class="text-green-600 focus:ring-green-500"
                                />
                                <span
                                    >Cuando la respuesta NO sea igual
                                    a:</span
                                >
                            </div>
                            <select
                                v-if="
                                    form.conditionType ===
                                    'not_equals'
                                "
                                v-model="form.expectedValue"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                :disabled="loadingOptions"
                            >
                                <option value="">
                                    {{ loadingOptions ? 'Cargando opciones...' : 'Seleccionar valor' }}
                                </option>
                                <option
                                    v-for="option in getDynamicOptions(selectedQuestion)"
                                    :key="option.value || option"
                                    :value="getOptionValue(option, selectedQuestion)"
                                >
                                    {{ option.text || option }}
                                </option>
                            </select>
                        </div>

                        <div
                            v-else-if="
                                selectedQuestion?.type ===
                                'checkbox'
                            "
                        >
                            <label class="flex items-center space-x-2">
                                <input
                                    type="radio"
                                    v-model="form.conditionType"
                                    value="equals"
                                    class="text-green-600 focus:ring-green-500"
                                />
                                <span>Cuando esté marcada</span>
                            </label>
                            <label class="flex items-center space-x-2">
                                <input
                                    type="radio"
                                    v-model="form.conditionType"
                                    value="not_equals"
                                    class="text-green-600 focus:ring-green-500"
                                />
                                <span>Cuando NO esté marcada</span>
                            </label>
                        </div>

                        <div
                            v-else-if="
                                [
                                    'text',
                                    'string',
                                    'textarea',
                                    'email',
                                    'tel',
                                ].includes(selectedQuestion?.type)
                            "
                        >
                            <div class="flex items-center space-x-2 mb-2">
                                <input
                                    type="radio"
                                    v-model="form.conditionType"
                                    value="equals"
                                    class="text-green-600 focus:ring-green-500"
                                />
                                <span
                                    >Cuando la respuesta sea igual a:</span
                                >
                            </div>
                            <input
                                v-if="
                                    form.conditionType === 'equals'
                                "
                                v-model="form.expectedValue"
                                type="text"
                                placeholder="Valor esperado"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />

                            <div class="flex items-center space-x-2 mt-2">
                                <input
                                    type="radio"
                                    v-model="form.conditionType"
                                    value="not_equals"
                                    class="text-green-600 focus:ring-green-500"
                                />
                                <span
                                    >Cuando la respuesta NO sea igual
                                    a:</span
                                >
                            </div>
                            <input
                                v-if="
                                    form.conditionType ===
                                    'not_equals'
                                "
                                v-model="form.expectedValue"
                                type="text"
                                placeholder="Valor esperado"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />

                            <div class="flex items-center space-x-2 mt-2">
                                <input
                                    type="radio"
                                    v-model="form.conditionType"
                                    value="is_null"
                                    class="text-green-600 focus:ring-green-500"
                                />
                                <span>Cuando esté vacío (IS NULL)</span>
                            </div>
                            <div class="flex items-center space-x-2 mt-2">
                                <input
                                    type="radio"
                                    v-model="form.conditionType"
                                    value="is_not_null"
                                    class="text-green-600 focus:ring-green-500"
                                />
                                <span>Cuando NO esté vacío (IS NOT NULL)</span>
                            </div>

                            <div class="flex items-center space-x-2 mt-2">
                                <input
                                    type="radio"
                                    v-model="form.conditionType"
                                    value="contains"
                                    class="text-green-600 focus:ring-green-500"
                                />
                                <span>Cuando la respuesta contenga:</span>
                            </div>
                            <input
                                v-if="
                                    form.conditionType ===
                                    'contains'
                                "
                                v-model="form.expectedValue"
                                type="text"
                                placeholder="Texto a buscar"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                        </div>

                        <div
                            v-else-if="
                                ['number', 'date'].includes(
                                    selectedQuestion?.type,
                                )
                            "
                        >
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="flex items-center space-x-2 mb-2"
                                    >
                                        <input
                                            type="radio"
                                            v-model="
                                                form.conditionType
                                            "
                                            value="equals"
                                            class="text-green-600 focus:ring-green-500"
                                        />
                                        <span>Igual a:</span>
                                    </label>
                                    <input
                                        v-if="
                                            form.conditionType ===
                                            'equals'
                                        "
                                        v-model="
                                            form.expectedValue
                                        "
                                        :type="
                                            selectedQuestion?.type ===
                                            'date'
                                                ? 'date'
                                                : 'number'
                                        "
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="flex items-center space-x-2 mb-2"
                                    >
                                        <input
                                            type="radio"
                                            v-model="
                                                form.conditionType
                                            "
                                            value="greater_than"
                                            class="text-green-600 focus:ring-green-500"
                                        />
                                        <span>Mayor que:</span>
                                    </label>
                                    <input
                                        v-if="
                                            form.conditionType ===
                                            'greater_than'
                                        "
                                        v-model="
                                            form.expectedValue
                                        "
                                        :type="
                                            selectedQuestion?.type ===
                                            'date'
                                                ? 'date'
                                                : 'number'
                                        "
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="
                        selectedQuestion &&
                        form.conditionType &&
                        (form.expectedValue ||
                            ['boolean', 'checkbox'].includes(
                                selectedQuestion?.type,
                            ) ||
                            ['integer', 'string'].includes(selectedQuestion?.type))
                    "
                    class="bg-blue-50 p-4 rounded-lg"
                >
                    <h4 class="font-medium text-blue-800 mb-2">
                        Resumen de la condición:
                    </h4>
                    <p class="text-sm text-blue-700">
                        Esta pregunta se mostrará cuando
                        <strong>{{
                            selectedQuestion?.text
                        }}</strong>
                        <span
                            v-if="
                                selectedQuestion?.type === 'boolean' &&
                                booleanSelection
                            "
                        >
                            sea "{{ booleanSelection === 'sí' ? 'Sí' : 'No' }}"</span
                        >
                        <span
                            v-else-if="
                                form.conditionType === 'equals' &&
                                selectedQuestion?.type ===
                                    'checkbox'
                            "
                        >
                            esté marcada</span
                        >
                        <span
                            v-else-if="
                                selectedQuestion?.type === 'integer'
                            "
                        >
                            <span v-if="form.conditionType === 'equals'"> sea igual a</span>
                            <span v-else-if="form.conditionType === 'not_equals'">no  sea igual a</span>
                            <span v-else-if="form.conditionType === 'greater_than'"> sea mayor que</span>
                            <span v-else-if="form.conditionType === 'less_than'"> sea menor que</span>
                            <span v-else-if="form.conditionType === 'greater_equal'"> sea mayor o igual que</span>
                            <span v-else-if="form.conditionType === 'less_equal'"> sea menor o igual que</span>
                            <span v-else-if="form.conditionType === 'is_null'"> esté vacío</span>
                            <span v-else-if="form.conditionType === 'is_not_null'"> NO esté vacío</span>
                            <strong v-if="form.expectedValue && !['is_null', 'is_not_null'].includes(form.conditionType)" class="ml-1">{{ form.expectedValue }}</strong>
                        </span>
                        <span
                            v-else-if="
                                selectedQuestion?.type === 'date'
                            "
                        >
                            <span v-if="form.conditionType === 'equals'"> sea igual a</span>
                            <span v-else-if="form.conditionType === 'not_equals'"> NO sea igual a</span>
                            <span v-else-if="form.conditionType === 'greater_than'"> sea después de</span>
                            <span v-else-if="form.conditionType === 'less_than'"> sea antes de</span>
                            <span v-else-if="form.conditionType === 'greater_equal'"> sea después de o igual a</span>
                            <span v-else-if="form.conditionType === 'less_equal'"> sea antes de o igual a</span>
                            <span v-else-if="form.conditionType === 'is_null'"> esté vacío</span>
                            <span v-else-if="form.conditionType === 'is_not_null'"> NO esté vacío</span>
                            <strong
                                v-if="form.expectedValue && !['is_null', 'is_not_null'].includes(form.conditionType)"
                                class="ml-1"
                            >{{ form.expectedValue.split('-').reverse().join('/') }}</strong>
                        </span>
                        <span
                            v-else-if="
                                ['text', 'string', 'textarea', 'email', 'tel'].includes(selectedQuestion?.type)
                            "
                        >
                            <span v-if="form.conditionType === 'equals'"> sea igual a</span>
                            <span v-else-if="form.conditionType === 'not_equals'"> NO sea igual a</span>
                            <span v-else-if="form.conditionType === 'is_null'"> esté vacío</span>
                            <span v-else-if="form.conditionType === 'is_not_null'"> NO esté vacío</span>
                            <strong v-if="form.expectedValue && !['is_null', 'is_not_null'].includes(form.conditionType)" class="ml-1">{{ form.expectedValue }}</strong>
                        </span>
                        <span
                            v-else-if="
                                form.conditionType ===
                                    'not_equals' &&
                                selectedQuestion?.type ===
                                    'checkbox'
                            "
                        >
                            NO esté marcada</span
                        >
                        <span
                            v-else-if="
                                form.conditionType === 'equals'
                            "
                        >
                            sea igual a</span
                        >
                        <span
                            v-else-if="
                                form.conditionType === 'not_equals'
                            "
                        >
                            NO sea igual a </span
                        >
                        <span
                            v-else-if="
                                form.conditionType === 'contains'
                            "
                        >
                            contenga</span
                        >
                        <span
                            v-else-if="
                                form.conditionType ===
                                'greater_than'
                            "
                        >
                            sea mayor que</span
                        >
                        <span
                            v-else-if="
                                form.conditionType === 'is_null'
                            "
                        >
                            esté vacío</span
                        >
                        <span
                            v-else-if="
                                form.conditionType === 'is_not_null'
                            "
                        >
                            NO esté vacío</span
                        >
                        <strong
                            v-if="
                                !['boolean', 'checkbox', 'integer', 'date'].includes(
                                    selectedQuestion?.type,
                                ) &&
                                form.expectedValue &&
                                !['is_null', 'is_not_null'].includes(form.conditionType)
                            "
                            >"{{ form.expectedValue }}"</strong
                        >
                    </p>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button
                    @click="closeModal"
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Cancelar
                </button>
                <button
                    @click="saveCondition"
                    :disabled="
                        !selectedQuestion ||
                        !form.conditionType ||
                        (selectedQuestion?.type === 'boolean' && !booleanSelection) ||
                        (!form.expectedValue &&
                            !['boolean', 'checkbox', 'integer', 'string'].includes(
                                selectedQuestion?.type,
                            ) &&
                            !['is_null', 'is_not_null'].includes(form.conditionType))
                    "
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
                >
                    Guardar Condición
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import Multiselect from 'vue-multiselect';

const components = {
    Multiselect
};

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    question: {
        type: Object,
        default: null
    },
    availableQuestions: {
        type: Array,
        default: () => []
    },
    questionTypes: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['close', 'save']);

const form = ref({
    dependsOnQuestionId: "",
    conditionType: "",
    expectedValue: "",
});

const dynamicOptions = ref([]);
const loadingOptions = ref(false);

const selectedPerson = ref(null);
const selectedQuestion = ref(null);
const booleanSelection = ref(null);

const customQuestionLabel = (question) => {
    if (question.slug) {
        return `${question.text} (${question.slug})`;
    }
    return question.text;
};

const availablePersons = computed(() => {
    const persons = [];
    
    persons.push({
        id: 'solicitante',
        name: 'Solicitante',
        icon: 'fas fa-user',
        type: 'solicitante'
    });
    
    props.availableQuestions.forEach(question => {
        if (question.isConviviente && question.convivienteName) {
            const existingPerson = persons.find(p => p.id === `conviviente_${question.typeIndex}`);
            if (!existingPerson) {
                persons.push({
                    id: `conviviente_${question.typeIndex}`,
                    name: question.convivienteName,
                    icon: 'fas fa-user-friends',
                    type: 'conviviente',
                    typeIndex: question.typeIndex
                });
            }
        }
    });
    
    return persons;
});

const questionsForSelectedPerson = computed(() => {
    if (!selectedPerson.value) return [];
    
    return props.availableQuestions.filter(question => {
        if (selectedPerson.value.type === 'solicitante') {
            return question.isSolicitante;
        } else if (selectedPerson.value.type === 'conviviente') {
            return question.isConviviente && question.typeIndex === selectedPerson.value.typeIndex;
        }
        return false;
    });
});

const groupedQuestions = computed(() => {
    const groups = {};
    
    props.availableQuestions.forEach(question => {
        let groupName = 'Otras preguntas';
        if (question.isSolicitante) {
            groupName = `Solicitante - ${question.sectionName || `Sección ${question.sectionIndex + 1}`}`;
        } else if (question.isConviviente) {
            groupName = `${question.convivienteName} - ${question.sectionName || `Sección ${question.sectionIndex + 1}`}`;
        } else if (question.sectionName) {
            groupName = question.sectionName;
        } else if (question.sectionIndex !== undefined) {
            groupName = `Sección ${question.sectionIndex + 1}`;
        }
        
        if (!groups[groupName]) {
            groups[groupName] = [];
        }
        
        groups[groupName].push(question);
    });
    
    const sortedGroups = {};
    Object.keys(groups)
        .sort((a, b) => {
            const aQuestion = groups[a][0];
            const bQuestion = groups[b][0];

            if (aQuestion.isSolicitante && !bQuestion.isSolicitante) return -1;
            if (!aQuestion.isSolicitante && bQuestion.isSolicitante) return 1;
            
            if (aQuestion.sectionIndex !== undefined && bQuestion.sectionIndex !== undefined) {
                return aQuestion.sectionIndex - bQuestion.sectionIndex;
            }
            
            if (aQuestion.sectionIndex !== undefined) return -1;
            if (bQuestion.sectionIndex !== undefined) return 1;
            
            return a.localeCompare(b);
        })
        .forEach(key => {
            sortedGroups[key] = groups[key];
        });
    
    return sortedGroups;
});

const onPersonSelect = (person) => {
    selectedPerson.value = person;
    selectedQuestion.value = null;
    form.value.dependsOnQuestionId = "";
    form.value.conditionType = "";
    form.value.expectedValue = "";
};

const onPersonRemove = () => {
    selectedPerson.value = null;
    selectedQuestion.value = null;
    form.value.dependsOnQuestionId = "";
    form.value.conditionType = "";
    form.value.expectedValue = "";
};

const onQuestionSelect = (question) => {
    selectedQuestion.value = question;
    form.value.dependsOnQuestionId = question.id;
    form.value.conditionType = "";
    form.value.expectedValue = "";
    booleanSelection.value = null;
    loadDynamicOptions();
};

const onQuestionRemove = () => {
    selectedQuestion.value = null;
    form.value.dependsOnQuestionId = "";
    form.value.conditionType = "";
    form.value.expectedValue = "";
    booleanSelection.value = null;
};

const onDependsOnQuestionChange = () => {
    form.value.conditionType = "";
    form.value.expectedValue = "";
    loadDynamicOptions();
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

const loadDynamicOptions = async () => {
    if (!selectedQuestion.value) {
        dynamicOptions.value = [];
        return;
    }
    
    const question = selectedQuestion.value;
    
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

const closeModal = () => {
    emit('close');
};

const saveCondition = () => {
    let expectedValue = form.value.expectedValue;
    if (['is_null', 'is_not_null'].includes(form.value.conditionType)) {
        expectedValue = null;
    } else if (selectedQuestion.value?.type === "boolean") {
        expectedValue = form.value.expectedValue;
    } else if (selectedQuestion.value?.type === "checkbox") {
        expectedValue =
            form.value.conditionType === "equals" ? "true" : "false";
    }

    const condition = {
        dependsOnQuestionId: selectedQuestion.value.id,
        conditionType: form.value.conditionType,
        expectedValue: expectedValue,
        personType: selectedPerson.value?.type || 'solicitante',
        personIndex: selectedPerson.value?.typeIndex !== undefined ? selectedPerson.value.typeIndex : null,
    };

    emit('save', condition);
};

// Watch for changes in the question prop to reset form
watch(() => props.question, (newQuestion) => {
    if (newQuestion && newQuestion.condition) {
        form.value = { ...newQuestion.condition };
        const dependentQuestion = props.availableQuestions.find(
            q => q.id == newQuestion.condition.dependsOnQuestionId
        );
        
        if (dependentQuestion) {
            if (newQuestion.condition.personType === 'solicitante') {
                selectedPerson.value = {
                    id: 'solicitante',
                    name: 'Solicitante',
                    icon: 'fas fa-user',
                    type: 'solicitante'
                };
            } else if (newQuestion.condition.personType === 'conviviente') {
                const convivienteQuestion = props.availableQuestions.find(q => 
                    q.isConviviente && 
                    q.typeIndex === newQuestion.condition.personIndex
                );
                
                if (convivienteQuestion) {
                    selectedPerson.value = {
                        id: `conviviente_${convivienteQuestion.typeIndex}`,
                        name: convivienteQuestion.convivienteName,
                        icon: 'fas fa-user-friends',
                        type: 'conviviente',
                        typeIndex: convivienteQuestion.typeIndex
                    };
                } else {
                    if (dependentQuestion.isSolicitante) {
                        selectedPerson.value = {
                            id: 'solicitante',
                            name: 'Solicitante',
                            icon: 'fas fa-user',
                            type: 'solicitante'
                        };
                    } else if (dependentQuestion.isConviviente) {
                        selectedPerson.value = {
                            id: `conviviente_${dependentQuestion.typeIndex}`,
                            name: dependentQuestion.convivienteName,
                            icon: 'fas fa-user-friends',
                            type: 'conviviente',
                            typeIndex: dependentQuestion.typeIndex
                        };
                    }
                }
            } else {
                if (dependentQuestion.isSolicitante) {
                    selectedPerson.value = {
                        id: 'solicitante',
                        name: 'Solicitante',
                        icon: 'fas fa-user',
                        type: 'solicitante'
                    };
                } else if (dependentQuestion.isConviviente) {
                    selectedPerson.value = {
                        id: `conviviente_${dependentQuestion.typeIndex}`,
                        name: dependentQuestion.convivienteName,
                        icon: 'fas fa-user-friends',
                        type: 'conviviente',
                        typeIndex: dependentQuestion.typeIndex
                    };
                }
            }

            selectedQuestion.value = dependentQuestion;
        }

        if (
            form.value.expectedValue === "Sí" ||
            form.value.expectedValue === "No"
        ) {
            form.value.conditionType = "equals";
            booleanSelection.value = form.value.expectedValue === "Sí" ? "sí" : "no";
        } else if (
            form.value.expectedValue === "true" ||
            form.value.expectedValue === "false"
        ) {
            form.value.conditionType =
                form.value.expectedValue === "true"
                    ? "equals"
                    : "not_equals";
            form.value.expectedValue = "";
        } else {
            loadDynamicOptions();
        }
    } else {
        form.value = {
            dependsOnQuestionId: "",
            conditionType: "",
            expectedValue: "",
        };
        selectedPerson.value = null;
        selectedQuestion.value = null;
    }
}, { immediate: true });

watch(booleanSelection, (newValue) => {
    if (newValue) {
        form.value.conditionType = "equals";
        form.value.expectedValue = newValue === "sí" ? "Sí" : "No";
    }
});

// Reset form when modal closes
watch(() => props.show, (newShow) => {
    if (!newShow) {
        form.value = {
            dependsOnQuestionId: "",
            conditionType: "",
            expectedValue: "",
        };
        selectedPerson.value = null;
        selectedQuestion.value = null;
        booleanSelection.value = null;
    }
});
</script>

<style>
@import 'vue-multiselect/dist/vue-multiselect.css';
</style>
