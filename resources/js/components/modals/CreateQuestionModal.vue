<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="$emit('close')">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Crear nueva pregunta</h3>
                <button 
                    @click="$emit('close')"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form @submit.prevent="handleSubmit">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Texto de la pregunta <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            v-model="questionData.text"
                            @input="generateSlug"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="¿Cuál es tu edad?"
                            required
                        ></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Slug (se genera automáticamente)
                        </label>
                        <input 
                            v-model="questionData.slug"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Se generará automáticamente"
                            readonly
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Texto adicional (opcional)
                        </label>
                        <textarea 
                            v-model="questionData.sub_text"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Texto adicional o explicación de la pregunta"
                        ></textarea>
                    </div>
                    
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Categorías <span class="text-red-500">*</span>
                            </label>
                            <div class="flex space-x-2">
                                <button type="button" 
                                        @click="selectAllCategories"
                                        class="text-xs text-blue-600 hover:text-blue-800 transition-colors">
                                    <i class="fas fa-check-double mr-1"></i>Seleccionar todo
                                </button>
                                <button type="button" 
                                        @click="deselectAllCategories"
                                        class="text-xs text-gray-600 hover:text-gray-800 transition-colors">
                                    <i class="fas fa-times mr-1"></i>Limpiar
                                </button>
                            </div>
                        </div>
                        <div class="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto bg-white">
                            <div v-if="availableCategories.length === 0" class="text-gray-500 text-sm">
                                No hay categorías disponibles
                            </div>
                            <div v-else class="space-y-2">
                                <label v-for="category in availableCategories" :key="category.id" 
                                       :class="'flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded transition-colors ' + (category.level > 0 ? 'ml-4' : '')">
                                    <input type="checkbox" 
                                           :value="category.id"
                                           v-model="questionData.category_ids"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                                    <span class="text-sm text-gray-700 flex-1" 
                                          :style="{ paddingLeft: (category.level * 20) + 'px' }">
                                        {{ category.level > 0 ? '└─ '.repeat(category.level) : '' }}{{ category.name }}
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div v-if="questionData.category_ids.length > 0" class="mt-2 text-xs text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-1"></i>
                            {{ questionData.category_ids.length }} categoría(s) seleccionada(s)
                        </div>
                    </div>
                     
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de pregunta <span class="text-red-500">*</span>
                        </label>
                        <select 
                            v-model="questionData.type"
                            @change="handleTypeChange"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required
                        >
                            <option value="">Seleccionar tipo</option>
                            <option v-for="(label, value) in questionTypes" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>
                     
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Finalidad <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-300 rounded-md p-3">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       :checked="questionData.purpose_ids.length === 0"
                                       @change="selectAllPurposes"
                                       class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">Sin finalidad</span>
                            </label>
                            <label v-for="purpose in availablePurposes" 
                                   :key="purpose.id"
                                   class="flex items-center">
                                <input type="checkbox" 
                                       :value="purpose.id"
                                       v-model="questionData.purpose_ids"
                                       class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">{{ purpose.name }}</span>
                            </label>
                        </div>
                    </div>
                     
                    <div v-if="['select', 'multiple'].includes(questionData.type)">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Opciones <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <div 
                                v-for="(option, index) in questionData.options" 
                                :key="index"
                                class="flex items-center space-x-2"
                            >
                                <input 
                                    v-model="questionData.options[index]"
                                    type="text"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :placeholder="`Opción ${index + 1}`"
                                    required
                                >
                                <button 
                                    @click="removeOption(index)"
                                    type="button"
                                    class="p-2 text-red-400 hover:text-red-600"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <button 
                                @click="addOption"
                                type="button"
                                class="px-3 py-2 text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                <i class="fas fa-plus mr-1"></i>Añadir opción
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button 
                        @click="$emit('close')"
                        type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit"
                        :disabled="creating"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        <i v-if="creating" class="fas fa-spinner fa-spin mr-2"></i>
                        {{ creating ? 'Creando...' : 'Crear pregunta' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { reactive, watch } from 'vue';
import { generateSlug } from '../../utils/formatters';

export default {
    name: 'CreateQuestionModal',
    props: {
        show: {
            type: Boolean,
            default: false
        },
        questionTypes: {
            type: Object,
            required: true
        },
        availableCategories: {
            type: Array,
            default: () => []
        },
        availablePurposes: {
            type: Array,
            default: () => []
        },
        creating: {
            type: Boolean,
            default: false
        }
    },
    emits: ['close', 'create'],
    setup(props, { emit }) {
        const questionData = reactive({
            slug: '',
            text: '',
            sub_text: '',
            type: '',
            options: [],
            category_ids: [],
            purpose_ids: []
        });

        const generateSlugFromText = () => {
            if (questionData.text) {
                questionData.slug = generateSlug(questionData.text);
            }
        };

        const handleTypeChange = () => {
            if (['select', 'multiple'].includes(questionData.type) && questionData.options.length === 0) {
                questionData.options = ['', ''];
            } else if (!['select', 'multiple'].includes(questionData.type)) {
                questionData.options = [];
            }
        };

        const addOption = () => {
            questionData.options.push('');
        };

        const removeOption = (index) => {
            questionData.options.splice(index, 1);
        };

        const selectAllCategories = () => {
            questionData.category_ids = props.availableCategories.map(cat => cat.id);
        };

        const deselectAllCategories = () => {
            questionData.category_ids = [];
        };

        const selectAllPurposes = () => {
            questionData.purpose_ids = [];
        };

        const handleSubmit = () => {
            emit('create', { ...questionData });
        };

        // Reset form when modal closes
        watch(() => props.show, (isOpen) => {
            if (!isOpen) {
                questionData.slug = '';
                questionData.text = '';
                questionData.sub_text = '';
                questionData.type = '';
                questionData.options = [];
                questionData.category_ids = [];
                questionData.purpose_ids = [];
            }
        });

        return {
            questionData,
            generateSlug: generateSlugFromText,
            handleTypeChange,
            addOption,
            removeOption,
            selectAllCategories,
            deselectAllCategories,
            selectAllPurposes,
            handleSubmit
        };
    }
};
</script>

