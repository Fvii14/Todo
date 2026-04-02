<template>
    <div>
        <h3 class="text-xl font-semibold text-gray-900 mb-6">
            <i class="fas fa-clipboard-list text-blue-600 mr-2"></i>
            Configuración del Cuestionario Específico
        </h3>
        
        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre del cuestionario específico <span class="text-red-500">*</span>
                </label>
                <input 
                    :value="formData.questionnaire_specific.name"
                    @input="updateField('name', $event.target.value)"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Ej: Bono Alquiler Joven Cataluña"
                >
            </div>
            
            <div>
                <label class="flex items-center">
                    <input 
                        :checked="formData.questionnaire_specific.active"
                        @change="updateField('active', $event.target.checked)"
                        type="checkbox"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700">Cuestionario activo</span>
                </label>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'WizardStepCuestionarioEspecifico',
    props: {
        formData: {
            type: Object,
            required: true
        }
    },
    emits: ['update:formData'],
    setup(props, { emit }) {
        const updateField = (field, value) => {
            emit('update:formData', {
                ...props.formData,
                questionnaire_specific: {
                    ...props.formData.questionnaire_specific,
                    [field]: value
                }
            });
        };

        return {
            updateField
        };
    }
};
</script>

