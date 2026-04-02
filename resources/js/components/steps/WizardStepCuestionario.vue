<template>
  <div class="wizard-step">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center mb-4">
          <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
            <i class="fas fa-clipboard-list text-green-600 text-xl"></i>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Configuración del Cuestionario</h2>
            <p class="text-gray-600">Define las características del cuestionario asociado</p>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Nombre del Cuestionario -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            Nombre del Cuestionario <span class="text-red-500">*</span>
          </label>
          <input 
            type="text" 
            id="name"
            v-model="formData.name"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Ej: Cuestionario de elegibilidad"
            required
          >
        </div>

        <!-- Tipo -->
        <div>
          <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
            Tipo de Cuestionario
          </label>
          <select 
            id="tipo"
            v-model="formData.tipo"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="pre">Pre-evaluación</option>
            <option value="post">Post-evaluación</option>
          </select>
        </div>

        <!-- URL de Redirección -->
        <div>
          <label for="redirect_url" class="block text-sm font-medium text-gray-700 mb-2">
            URL de Redirección (opcional)
          </label>
          <input 
            type="url" 
            id="redirect_url"
            v-model="formData.redirect_url"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="https://ejemplo.com/redireccion"
          >
          <p class="text-sm text-gray-500 mt-1">URL a la que redirigir después de completar el cuestionario</p>
        </div>

        <!-- Estado Activo -->
        <div class="flex items-center">
          <input 
            type="checkbox" 
            id="active"
            v-model="formData.active"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          >
          <label for="active" class="ml-2 block text-sm text-gray-700">
            Cuestionario activo
          </label>
        </div>

        <!-- Actions -->
        <div class="flex justify-between pt-6 border-t border-gray-200">
          <button 
            type="button"
            @click="$emit('previous')"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <i class="fas fa-arrow-left mr-2"></i>Anterior
          </button>
          
          <div class="flex space-x-3">
            <button 
              type="button"
              @click="$emit('save-draft')"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <i class="fas fa-save mr-2"></i>Guardar Borrador
            </button>
            
            <button 
              type="submit"
              :disabled="!isFormValid"
              class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Continuar <i class="fas fa-arrow-right ml-2"></i>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, watch } from 'vue'

export default {
  name: 'WizardStepCuestionario',
  props: {
    data: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ['update:data', 'next', 'previous', 'save-draft'],
  setup(props, { emit }) {
    const formData = reactive({
      name: props.data.name || '',
      tipo: props.data.tipo || 'pre',
      redirect_url: props.data.redirect_url || '',
      active: props.data.active !== undefined ? props.data.active : true
    })

    // Watch for changes and emit updates
    watch(formData, (newData) => {
      emit('update:data', { ...newData })
    }, { deep: true })

    // Computed properties
    const isFormValid = computed(() => {
      return formData.name.trim() !== ''
    })

    const handleSubmit = () => {
      if (isFormValid.value) {
        emit('next')
      }
    }

    return {
      formData,
      isFormValid,
      handleSubmit
    }
  }
}
</script>

<style scoped>
.wizard-step {
  min-height: 100%;
}
</style> 