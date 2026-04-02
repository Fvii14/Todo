<template>
  <div class="form-container">
    <!-- Formulario -->
    <div class="form-section">
            <div v-if="isLoading" class="flex items-center justify-center h-full">
        <div class="text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-2 text-gray-600">Cargando playground...</p>
        </div>
      </div>
      
      <div v-else-if="errorMessage" class="flex items-center justify-center h-full">
        <div class="text-center">
          <div class="text-red-600 text-xl mb-4">⚠️ Error</div>
          <p class="text-gray-600 mb-4">{{ errorMessage }}</p>
          <button @click="fetchData" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Reintentar
          </button>
        </div>
      </div>
      
      <div v-else-if="currentQuestion" class="question-container">
        <h3 class="text-lg font-medium mb-4">{{ currentQuestion.text }}</h3>
        
        <!-- Input según tipo de pregunta -->
        <div class="mb-4">
          <!-- Texto -->
          <input v-if="currentQuestion.type === 'text'" 
                 v-model="answers[currentQuestion.id]" 
                 type="text" 
                 class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                 placeholder="Escribe tu respuesta">
          
          <!-- Número -->
          <input v-if="currentQuestion.type === 'number'" 
                 v-model="answers[currentQuestion.id]" 
                 type="number" 
                 class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                 placeholder="Escribe un número">
          
          <!-- Boolean -->
          <div v-if="currentQuestion.type === 'boolean'" class="flex gap-4">
            <label class="flex items-center">
              <input type="radio" 
                     v-model="answers[currentQuestion.id]" 
                     :value="1" 
                     class="mr-2">
              Sí
            </label>
            <label class="flex items-center">
              <input type="radio" 
                     v-model="answers[currentQuestion.id]" 
                     :value="0" 
                     class="mr-2">
              No
            </label>
          </div>
          
          <!-- Select -->
          <select v-if="currentQuestion.type === 'select'" 
                  v-model="answers[currentQuestion.id]" 
                  class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Selecciona una opción</option>
            <option v-for="(option, index) in currentQuestion.options" 
                    :key="index" 
                    :value="index">{{ option }}</option>
          </select>
          
          <!-- Multiple -->
          <div v-if="currentQuestion.type === 'multiple'" class="space-y-2">
            <label v-for="(option, index) in currentQuestion.options" 
                   :key="index" 
                   class="flex items-center">
              <input type="checkbox" 
                     v-model="answers[currentQuestion.id]" 
                     :value="index" 
                     class="mr-2">
              {{ option }}
            </label>
          </div>
          
          <!-- Date -->
          <input v-if="currentQuestion.type === 'date'" 
                 v-model="answers[currentQuestion.id]" 
                 type="date" 
                 class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        <!-- Botones de navegación -->
        <div class="flex gap-3">
          <button @click="previousQuestion" 
                  :disabled="questionHistory.length === 0"
                  class="px-4 py-2 bg-gray-500 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
            Anterior
          </button>
          <button @click="nextQuestion" 
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            {{ isLastQuestion ? 'Finalizar' : 'Siguiente' }}
          </button>
        </div>
        
        <!-- Información de debug -->
        <div class="mt-4 p-3 bg-gray-100 rounded-lg text-sm">
          <p><strong>Pregunta actual:</strong> {{ currentQuestionIndex + 1 }} de {{ questions.length }}</p>
          <p><strong>ID:</strong> {{ currentQuestion.id }}</p>
          <p><strong>Tipo:</strong> {{ currentQuestion.type }}</p>
          <p><strong>Respuesta:</strong> {{ answers[currentQuestion.id] }}</p>
        </div>
      </div>
      
      <div v-else class="text-center py-8">
        <h3 class="text-xl font-semibold mb-4">🏁 Cuestionario completado</h3>
        <p class="text-gray-600 mb-6">Has llegado al final del cuestionario.</p>
        <div class="flex gap-4 justify-center">
          <button @click="goBack" 
                  :disabled="questionHistory.length === 0"
                  class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver atrás
          </button>
          <button @click="restartForm" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-redo mr-2"></i>
            Reiniciar formulario
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { VueFlow, useVueFlow } from '@vue-flow/core'
import '@vue-flow/core/dist/style.css'
import '@vue-flow/core/dist/theme-default.css'

export default {
  name: 'PlaygroundComponent',
  components: {
    VueFlow
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
      questions: [],
      conditions: [],
      currentQuestionIndex: 0,
      answers: {},
      questionHistory: [],
      isLoading: true,
      errorMessage: null
    }
  },
  watch: {
    // Inicializar arrays para preguntas múltiples
    questions: {
      handler(newQuestions) {
        newQuestions.forEach(question => {
          if (question.type === 'multiple' && !this.answers[question.id]) {
            this.$set(this.answers, question.id, [])
          }
        })
      },
      immediate: true
    }
  },
  computed: {
    currentQuestion() {
      return this.questions[this.currentQuestionIndex] || null
    },
    isLastQuestion() {
      return this.currentQuestionIndex >= this.questions.length - 1
    }
  },
  async mounted() {
    await this.fetchData()
  },
  methods: {
    async fetchData() {
      try {
        // Obtener el questionnaire_id asociado a la ayuda
        const response = await fetch(`/admin/ayudas/${this.ayudaId}/questionnaire`, {
          headers: {
            'X-CSRF-TOKEN': this.csrf,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          credentials: 'same-origin'
        })
        
        if (!response.ok) {
          const errorText = await response.text()
          throw new Error(`Error ${response.status}: ${errorText}`)
        }
        
        const data = await response.json()
        
        // Ordenar las preguntas por el campo order
        this.questions = (data.questions || []).sort((a, b) => {
          const orderA = a.order || 0
          const orderB = b.order || 0
          return orderA - orderB
        })
        this.conditions = data.conditions || []
        
        this.isLoading = false
      } catch (error) {
        console.error('Error fetching playground data:', error)
        this.isLoading = false
        this.errorMessage = error.message
      }
    },
    

    
    nextQuestion() {
      if (this.isLastQuestion) {
        this.currentQuestionIndex = this.questions.length
        this.updateCurrentNode()
        return
      }
      
      // Guardar el índice actual en el historial
      this.questionHistory.push(this.currentQuestionIndex)
      
      // Verificar condiciones para saltos
      const jumpIndex = this.checkConditions()
      
      if (jumpIndex !== null) {
        this.currentQuestionIndex = jumpIndex
      } else {
        this.currentQuestionIndex++
      }
      
      this.updateCurrentNode()
    },
    
    previousQuestion() {
      if (this.questionHistory.length > 0) {
        this.currentQuestionIndex = this.questionHistory.pop()
        this.updateCurrentNode()
      }
    },
    
    checkConditions() {
      const currentQuestion = this.currentQuestion
      if (!currentQuestion) {
        return null
      }
      
      const questionConditions = this.conditions.filter(c => c.question_id === currentQuestion.id)
      
      // Si no hay condiciones, avance secuencial
      if (questionConditions.length === 0) {
        return null
      }
      
      // Evaluar todas las condiciones y tomar la primera que se cumpla
      for (const condition of questionConditions) {
        const answer = this.answers[currentQuestion.id]
        
        if (this.evaluateCondition(answer, condition)) {
          if (!condition.next_question_id) {
            return this.questions.length // Saltar al final
          } else {
            // Encontrar el índice de la pregunta destino
            const targetIndex = this.questions.findIndex(q => q.id === condition.next_question_id)
            return targetIndex !== -1 ? targetIndex : null
          }
        }
      }
      
      return null
    },
    
    evaluateCondition(answer, condition) {
      if (condition.is_composite && condition.composite_rules && condition.composite_rules.length > 0) {
        return this.evaluateCompositeCondition(condition)
      }

      if (answer === null || answer === undefined || answer === '') {
        return false
      }
      
      let result = false
      
      switch (condition.operator) {
        case '=':
          result = String(answer) === String(condition.value)
          break
        case '!=':
          result = String(answer) !== String(condition.value)
          break
        case '>':
          result = parseFloat(answer) > parseFloat(condition.value)
          break
        case '<':
          result = parseFloat(answer) < parseFloat(condition.value)
          break
        case '>=':
          result = parseFloat(answer) >= parseFloat(condition.value)
          break
        case '<=':
          result = parseFloat(answer) <= parseFloat(condition.value)
          break
        case 'in':
          if (Array.isArray(condition.value)) {
            result = condition.value.includes(String(answer))
          } else {
            result = String(condition.value).includes(String(answer))
          }
          break
        case 'contains':
          result = String(answer).includes(String(condition.value))
          break
        default:
          result = false
      }
      
      return result
    },
    
    evaluateCompositeCondition(condition) {
      const rules = condition.composite_rules
      const logic = condition.composite_logic || 'AND'
      
      if (logic === 'AND') {
        for (const rule of rules) {
          const answer = this.answers[rule.question_id]
          if (!this.evaluateSimpleRule(answer, rule)) {
            return false
          }
        }
        return true
      } else if (logic === 'OR') {
        for (const rule of rules) {
          const answer = this.answers[rule.question_id]
          if (this.evaluateSimpleRule(answer, rule)) {
            return true
          }
        }
        return false
      }
      
      return false
    },
    
    evaluateSimpleRule(answer, rule) {
      if (answer === null || answer === undefined || answer === '') {
        return false
      }
      
      let result = false
      
      switch (rule.operator) {
        case '=':
          result = String(answer) === String(rule.value)
          break
        case '!=':
          result = String(answer) !== String(rule.value)
          break
        case '>':
          result = parseFloat(answer) > parseFloat(rule.value)
          break
        case '<':
          result = parseFloat(answer) < parseFloat(rule.value)
          break
        case '>=':
          result = parseFloat(answer) >= parseFloat(rule.value)
          break
        case '<=':
          result = parseFloat(answer) <= parseFloat(rule.value)
          break
        case 'in':
          if (Array.isArray(rule.value)) {
            result = rule.value.includes(String(answer))
          } else {
            result = String(rule.value).includes(String(answer))
          }
          break
        case 'contains':
          result = String(answer).includes(String(rule.value))
          break
        default:
          result = false
      }
      
      return result
    },
    
    updateCurrentNode() {
      // Emitir evento para actualizar el árbol de decisiones
      this.$emit('question-changed', this.currentQuestion?.id || null)
    },
    
    restartForm() {
      this.currentQuestionIndex = 0
      this.answers = {}
      this.questionHistory = []
      this.updateCurrentNode()
    },
    
    goBack() {
      if (this.questionHistory.length > 0) {
        this.currentQuestionIndex = this.questionHistory.pop()
        this.updateCurrentNode()
      }
    }
  }
}
</script>

<style scoped>
.form-container {
  width: 100%;
  height: 100%;
}

.form-section {
  flex: 1;
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
  overflow-y: auto;
}



.question-container {
  max-width: 100%;
}

.question-container h3 {
  color: #1f2937;
  margin-bottom: 1rem;
}

.question-container input,
.question-container select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

.question-container input:focus,
.question-container select:focus {
  outline: none;
  ring: 2px;
  ring-color: #3b82f6;
  border-color: #3b82f6;
}

.question-container label {
  display: flex;
  align-items: center;
  margin-bottom: 0.5rem;
  cursor: pointer;
}

.question-container button {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.question-container button:hover:not(:disabled) {
  transform: translateY(-1px);
}
</style> 