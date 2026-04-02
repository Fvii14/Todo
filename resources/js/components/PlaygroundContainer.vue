<template>
  <div class="playground-container">
    <div class="flex gap-6 h-[600px]">
        <!-- Formulario de Prueba -->
        <div class="w-1/2 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Formulario de Prueba</h2>
                <PlaygroundComponent 
                    :ayuda-id="ayudaId" 
                    :csrf="csrf"
                    @question-changed="handleQuestionChanged" />
        </div>
      
      <!-- Árbol de Decisiones -->
      <div class="w-1/2 bg-white rounded-lg shadow">
        <div class="p-4 border-b">
          <h2 class="text-xl font-semibold">Árbol de Decisiones</h2>
        </div>
        <div class="h-[520px] w-full">
          <PlaygroundVueFlow 
            :ayuda-id="ayudaId" 
            :csrf="csrf"
            :current-question-id="currentQuestionId"
            ref="vueFlowRef" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import PlaygroundComponent from './PlaygroundComponent.vue'
import PlaygroundVueFlow from './PlaygroundVueFlow.vue'

export default {
  name: 'PlaygroundContainer',
  components: {
    PlaygroundComponent,
    PlaygroundVueFlow
  },
  props: {
    ayudaId: {
      type: Number,
      required: true
    },
    csrf: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      currentQuestionId: null
    }
  },
  methods: {
    handleQuestionChanged(questionId) {
      this.currentQuestionId = questionId
      if (this.$refs.vueFlowRef) {
        this.$refs.vueFlowRef.updateCurrentNode(questionId)
      }
    }
  }
}
</script>

<style scoped>
.playground-container {
  width: 100%;
  height: 100%;
}
</style> 