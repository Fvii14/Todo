<template>
  <div class="wizard-step">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center mb-4">
          <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
            <i class="fas fa-user-check text-red-600 text-xl"></i>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Lógica de Elegibilidad</h2>
            <p class="text-gray-600">Define las reglas para determinar si un usuario es elegible</p>
          </div>
        </div>
      </div>

      <!-- Placeholder Content -->
      <div class="bg-yellow-50 border border-yellow-200 rounded-md p-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="fas fa-info-circle text-yellow-400"></i>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800">
              Funcionalidad en desarrollo
            </h3>
            <div class="mt-2 text-sm text-yellow-700">
              <p>El editor de lógica de elegibilidad está siendo implementado. Por ahora, puedes continuar al siguiente paso.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-between pt-6 border-t border-gray-200 mt-6">
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
            type="button"
            @click="$emit('next')"
            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            Continuar <i class="fas fa-arrow-right ml-2"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'WizardStepElegibilidad',
  props: {
    data: {
      type: Array,
      default: () => []
    }
  },
  emits: ['update:data', 'next', 'previous', 'save-draft']
}
</script>

<style scoped>
.wizard-step {
  min-height: 100%;
}
</style> 