<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="$emit('close')">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Editar pregunta</h3>
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
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="¿Cuál es tu edad?"
                            required
                        ></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Texto secundario (opcional)
                        </label>
                        <textarea 
                            v-model="questionData.sub_text"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Información adicional o ayuda..."
                        ></textarea>
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
                            <option value="">Selecciona un tipo</option>
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
                                >
                                <button 
                                    @click="removeOption(index)"
                                    type="button"
                                    class="p-2 text-red-600 hover:text-red-700"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <button 
                                @click="addOption"
                                type="button"
                                class="px-3 py-2 border border-dashed border-gray-300 rounded-md text-gray-600 hover:border-gray-400 hover:text-gray-700"
                            >
                                <i class="fas fa-plus mr-2"></i>Añadir opción
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Categorías <span class="text-red-500">*</span>
                        </label>
                        <div class="border border-gray-300 rounded-md p-3 max-h-32 overflow-y-auto">
                            <div class="flex gap-2 mb-2">
                                <button 
                                    type="button"
                                    @click="selectAllCategories"
                                    class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                                >
                                    Seleccionar todo
                                </button>
                                <button 
                                    type="button"
                                    @click="deselectAllCategories"
                                    class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                                >
                                    Limpiar
                                </button>
                            </div>
                            <div class="space-y-1">
                                <label 
                                    v-for="category in availableCategories" 
                                    :key="category.id"
                                    :class="'flex items-center ' + (category.level > 0 ? 'ml-4' : '')"
                                >
                                    <input 
                                        type="checkbox"
                                        :value="category.id"
                                        v-model="questionData.category_ids"
                                        class="mr-2 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span class="text-sm" 
                                          :style="{ paddingLeft: (category.level * 20) + 'px' }">
                                        {{ category.level > 0 ? '└─ '.repeat(category.level) : '' }}{{ category.name }}
                                    </span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                {{ questionData.category_ids.length }} categoría(s) seleccionada(s)
                            </p>
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
                        :disabled="updating"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        <i v-if="updating" class="fas fa-spinner fa-spin mr-2"></i>
                        {{ updating ? 'Actualizando...' : 'Actualizar pregunta' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { reactive, watch } from 'vue';

export default {
    name: 'EditQuestionModal',
    props: {
        show: {
            type: Boolean,
            default: false
        },
        question: {
            type: Object,
            default: null
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
        updating: {
            type: Boolean,
            default: false
        }
    },
    emits: ['close', 'update'],
    setup(props, { emit }) {
        const questionData = reactive({
            id: null,
            slug: '',
            text: '',
            sub_text: '',
            type: 'text',
            options: [],
            category_ids: [],
            purpose_ids: []
        });

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
            emit('update', { ...questionData });
        };

        // Load question data when modal opens
        watch(() => props.question, (newQuestion) => {
            if (newQuestion) {
                questionData.id = newQuestion.id;
                questionData.slug = newQuestion.slug || '';
                questionData.text = newQuestion.text || '';
                questionData.sub_text = newQuestion.sub_text || '';
                questionData.type = newQuestion.type || 'text';
                questionData.options = newQuestion.options ? [...newQuestion.options] : [];
                questionData.category_ids = newQuestion.categories ? newQuestion.categories.map(c => c.id) : [];
                questionData.purpose_ids = newQuestion.purposes ? newQuestion.purposes.map(p => p.id) : [];
            }
        }, { immediate: true });

        return {
            questionData,
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

