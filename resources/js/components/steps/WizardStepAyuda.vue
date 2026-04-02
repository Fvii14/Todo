<template>
  <div class="wizard-step">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center mb-4">
          <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
            <i class="fas fa-hands-helping text-blue-600 text-xl"></i>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Información de la Ayuda</h2>
            <p class="text-gray-600">Define los datos básicos de la ayuda pública</p>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Nombre de la Ayuda -->
        <div>
          <label for="nombre_ayuda" class="block text-sm font-medium text-gray-700 mb-2">
            Nombre de la Ayuda <span class="text-red-500">*</span>
          </label>
          <input 
            type="text" 
            id="nombre_ayuda"
            v-model="formData.nombre_ayuda"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Ej: Ayuda para jóvenes emprendedores"
            required
          >
          <p class="text-sm text-gray-500 mt-1">Un nombre descriptivo que identifique claramente la ayuda</p>
        </div>

        <!-- Sector -->
        <div>
          <label for="sector" class="block text-sm font-medium text-gray-700 mb-2">
            Sector <span class="text-red-500">*</span>
          </label>
          <select 
            id="sector"
            v-model="formData.sector"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            required
          >
            <option value="">Selecciona un sector</option>
            <option v-for="sector in sectores" :key="sector" :value="sector">
              {{ sector }}
            </option>
          </select>
        </div>

        <!-- Órgano -->
        <div>
          <label for="organo_id" class="block text-sm font-medium text-gray-700 mb-2">
            Órgano Responsable <span class="text-red-500">*</span>
          </label>
          <select 
            id="organo_id"
            v-model="formData.organo_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            required
          >
            <option value="">Selecciona un órgano</option>
            <option v-for="organo in organos" :key="organo.id" :value="organo.id">
              {{ organo.nombre }}
            </option>
          </select>
        </div>

        <!-- Presupuesto -->
        <div>
          <label for="presupuesto" class="block text-sm font-medium text-gray-700 mb-2">
            Presupuesto Total (€)
          </label>
          <input 
            type="number" 
            id="presupuesto"
            v-model="formData.presupuesto"
            step="0.01"
            min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="0.00"
          >
          <p class="text-sm text-gray-500 mt-1">Presupuesto total disponible para esta ayuda</p>
        </div>

        <!-- Cuantía por Usuario -->
        <div>
          <label for="cuantia_usuario" class="block text-sm font-medium text-gray-700 mb-2">
            Cuantía por Usuario (€)
          </label>
          <input 
            type="number" 
            id="cuantia_usuario"
            v-model="formData.cuantia_usuario"
            step="0.01"
            min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="0.00"
          >
          <p class="text-sm text-gray-500 mt-1">Cantidad máxima que puede recibir cada beneficiario</p>
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-2">
              Fecha de Inicio
            </label>
            <input 
              type="date" 
              id="fecha_inicio"
              v-model="formData.fecha_inicio"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
          </div>
          
          <div>
            <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-2">
              Fecha de Fin
            </label>
            <input 
              type="date" 
              id="fecha_fin"
              v-model="formData.fecha_fin"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
          </div>
        </div>

        <!-- Estado Activo -->
        <div class="flex items-center">
          <input 
            type="checkbox" 
            id="activo"
            v-model="formData.activo"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          >
          <label for="activo" class="ml-2 block text-sm text-gray-700">
            Ayuda activa (disponible para solicitudes)
          </label>
        </div>

        <!-- Validation Errors -->
        <div v-if="errors.length > 0" class="bg-red-50 border border-red-200 rounded-md p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-triangle text-red-400"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                Hay errores en el formulario:
              </h3>
              <div class="mt-2 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                  <li v-for="error in errors" :key="error">{{ error }}</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between pt-6 border-t border-gray-200">
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
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, watch } from 'vue'

export default {
  name: 'WizardStepAyuda',
  props: {
    data: {
      type: Object,
      default: () => ({})
    },
    organos: {
      type: Array,
      default: () => []
    },
    sectores: {
      type: Array,
      default: () => []
    }
  },
  emits: ['update:data', 'next', 'save-draft'],
  setup(props, { emit }) {
    const errors = ref([])
    
    const formData = reactive({
      nombre_ayuda: props.data.nombre_ayuda || '',
      sector: props.data.sector || '',
      organo_id: props.data.organo_id || '',
      presupuesto: props.data.presupuesto || null,
      cuantia_usuario: props.data.cuantia_usuario || 0,
      fecha_inicio: props.data.fecha_inicio || '',
      fecha_fin: props.data.fecha_fin || '',
      activo: props.data.activo !== undefined ? props.data.activo : true
    })

    // Watch for changes and emit updates
    watch(formData, (newData) => {
      emit('update:data', { ...newData })
    }, { deep: true })

    // Computed properties
    const isFormValid = computed(() => {
      return formData.nombre_ayuda.trim() !== '' &&
             formData.sector !== '' &&
             formData.organo_id !== ''
    })

    // Methods
    const validateForm = () => {
      errors.value = []
      
      if (!formData.nombre_ayuda.trim()) {
        errors.value.push('El nombre de la ayuda es obligatorio')
      }
      
      if (!formData.sector) {
        errors.value.push('Debes seleccionar un sector')
      }
      
      if (!formData.organo_id) {
        errors.value.push('Debes seleccionar un órgano responsable')
      }
      
      if (formData.fecha_fin && formData.fecha_inicio && formData.fecha_fin < formData.fecha_inicio) {
        errors.value.push('La fecha de fin no puede ser anterior a la fecha de inicio')
      }
      
      if (formData.presupuesto && formData.presupuesto < 0) {
        errors.value.push('El presupuesto no puede ser negativo')
      }
      
      if (formData.cuantia_usuario && formData.cuantia_usuario < 0) {
        errors.value.push('La cuantía por usuario no puede ser negativa')
      }
      
      return errors.value.length === 0
    }

    const handleSubmit = () => {
      if (validateForm()) {
        emit('next')
      }
    }

    return {
      formData,
      errors,
      isFormValid,
      validateForm,
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