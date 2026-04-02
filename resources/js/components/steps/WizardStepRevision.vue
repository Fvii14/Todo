<template>
    <div>
        <h3 class="text-xl font-semibold text-gray-900 mb-6">
            <i class="fas fa-check-circle text-blue-600 mr-2"></i>
            Revisión Final
        </h3>
        
        <div class="bg-gray-50 rounded-lg p-6">
            <h4 class="font-semibold text-gray-900 mb-4">Resumen de la configuración:</h4>
            
            <div class="space-y-6">
                <div class="bg-white rounded-lg p-4 border">
                    <h5 class="font-medium text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-hands-helping text-blue-600 mr-2"></i>
                        Información de la Ayuda
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div><strong>Nombre:</strong> {{ formData.ayuda.nombre_ayuda || 'No especificado' }}</div>
                        <div><strong>Slug:</strong> {{ formData.ayuda.slug || 'No especificado' }}</div>
                        <div><strong>Sector:</strong> {{ formData.ayuda.sector || 'No especificado' }}</div>
                        <div><strong>Órgano:</strong> {{ getOrganoName(formData.ayuda.organo_id) }}</div>
                        <div><strong>Presupuesto:</strong> {{ formData.ayuda.presupuesto ? '€' + formData.ayuda.presupuesto : 'No especificado' }}</div>
                        <div><strong>Cuantía por usuario:</strong> {{ formData.ayuda.cuantia_usuario ? '€' + formData.ayuda.cuantia_usuario : 'No especificado' }}</div>
                        <div><strong>Fecha inicio:</strong> {{ formData.ayuda.fecha_inicio || 'No especificado' }}</div>
                        <div><strong>Fecha fin:</strong> {{ formData.ayuda.fecha_fin || 'No especificado' }}</div>
                        <div><strong>Estado:</strong> <span :class="formData.ayuda.activo ? 'text-green-600' : 'text-red-600'">{{ formData.ayuda.activo ? 'Activa' : 'Inactiva' }}</span></div>
                        <div v-if="formData.ayuda.description" class="md:col-span-2"><strong>Descripción:</strong> {{ formData.ayuda.description }}</div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-4 border">
                    <h5 class="font-medium text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-clipboard-list text-blue-600 mr-2"></i>
                        Cuestionario Específico
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div><strong>Nombre:</strong> {{ formData.questionnaire_specific.name || 'No especificado' }}</div>
                        <div><strong>Estado:</strong> <span :class="formData.questionnaire_specific.active ? 'text-green-600' : 'text-red-600'">{{ formData.questionnaire_specific.active ? 'Activo' : 'Inactivo' }}</span></div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-4 border">
                    <h5 class="font-medium text-gray-800 mb-3 flex items-center justify-between cursor-pointer" @click="toggleSection('prerequisites')">
                        <div class="flex items-center">
                            <i class="fas fa-cogs text-blue-600 mr-2"></i>
                            Pre-requisitos de la ayuda ({{ formData.preRequisitos.length }})
                        </div>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': openSections.prerequisites }"></i>
                    </h5>
                    <div v-if="openSections.prerequisites">
                        <div v-if="formData.preRequisitos.length > 0" class="space-y-3">
                            <div 
                                v-for="(preReq, index) in formData.preRequisitos" 
                                :key="index"
                                class="p-3 bg-gray-50 rounded-lg border-l-4 border-blue-500"
                            >
                                <div class="text-sm text-gray-800">{{ preReq.name || preReq.description || 'Sin descripción' }}</div>
                            </div>
                        </div>
                        <div v-else class="text-center py-6 text-gray-500">
                            <p class="text-sm">No hay pre-requisitos configurados</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-4 border">
                    <h5 class="font-medium text-gray-800 mb-3 flex items-center justify-between cursor-pointer" @click="toggleSection('questions')">
                        <div class="flex items-center">
                            <i class="fas fa-question-circle text-blue-600 mr-2"></i>
                            Preguntas Específico ({{ formData.questions_specific.length }})
                        </div>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': openSections.questions }"></i>
                    </h5>
                    <div v-if="openSections.questions">
                        <div v-if="formData.questions_specific.length > 0" class="space-y-2">
                            <div 
                                v-for="(question, index) in formData.questions_specific" 
                                :key="index"
                                class="flex items-center justify-between p-2 bg-gray-50 rounded"
                            >
                                <div>
                                    <p class="font-medium text-sm">{{ question.text }}</p>
                                    <p class="text-xs text-gray-500">{{ question.slug }} • {{ questionTypes[question.type] || question.type }}</p>
                                </div>
                                <span class="text-xs text-gray-400">#{{ index + 1 }}</span>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-500">
                            No hay preguntas añadidas
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-4 border">
                    <h5 class="font-medium text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                        Documentos ({{ formData.documents.length }} generales, {{ formData.documents_convivientes.length }} convivientes)
                    </h5>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';

export default {
    name: 'WizardStepRevision',
    props: {
        formData: {
            type: Object,
            required: true
        },
        organos: {
            type: Array,
            default: () => []
        },
        questionTypes: {
            type: Object,
            default: () => ({})
        }
    },
    setup(props) {
        const openSections = ref({
            prerequisites: false,
            questions: false,
            conditions: false,
            documents: false
        });

        const toggleSection = (section) => {
            openSections.value[section] = !openSections.value[section];
        };

        const getOrganoName = (organoId) => {
            const organo = props.organos.find(o => o.id == organoId);
            return organo ? organo.nombre_organismo : 'No especificado';
        };

        return {
            openSections,
            toggleSection,
            getOrganoName
        };
    }
};
</script>
