<template>
  <div class="tester-perfil">
    <!-- Buscador de preguntas -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Buscar y añadir preguntas al perfil
      </label>
      <div class="flex gap-4">
        <input 
          v-model="searchTerm" 
          @input="searchQuestions"
          type="text" 
          placeholder="Buscar por texto de la pregunta..."
          class="flex-1 border border-gray-300 rounded px-3 py-2"
        />
        <button 
          @click="searchQuestions"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          :disabled="searching">
          <i v-if="searching" class="fas fa-spinner fa-spin"></i>
          <i v-else class="fas fa-search"></i>
        </button>
      </div>
    </div>

    <!-- Resultados de búsqueda -->
    <div v-if="searchResults.length > 0" class="mb-6">
      <h4 class="font-semibold text-gray-800 mb-3">Preguntas encontradas:</h4>
      <div class="bg-gray-50 p-4 rounded-lg max-h-64 overflow-y-auto">
        <div v-for="question in searchResults" :key="question.id" class="mb-3 pb-3 border-b border-gray-200 last:border-b-0">
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="font-medium text-sm">{{ question.text }}</div>
              <div class="text-xs text-gray-500">Tipo: {{ question.type }} - ID: {{ question.id }}</div>
            </div>
            <button 
              @click="addQuestionToProfile(question)"
              class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 ml-2">
              <i class="fas fa-plus"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="searchTerm.trim() && !searching && searchResults.length === 0" class="mb-6">
      <div class="bg-gray-50 p-4 rounded-lg text-center">
        <i class="fas fa-search text-gray-400 text-2xl mb-2"></i>
        <p class="text-gray-600">No se encontraron preguntas con ese criterio</p>
        <p class="text-sm text-gray-500 mt-1">Intenta con otro texto de búsqueda</p>
      </div>
    </div>

    <!-- Preguntas añadidas al perfil -->
    <div v-if="selectedQuestions.length > 0" class="mb-6">
      <h4 class="font-semibold text-gray-800 mb-3">Preguntas en el perfil:</h4>
      <div class="space-y-4">
        <div v-for="question in selectedQuestions" :key="question.id" class="bg-blue-50 p-4 rounded-lg">
          <div class="flex justify-between items-start mb-3">
            <div>
              <label class="block text-sm font-medium text-gray-700">
                {{ question.text }}
                <span class="text-xs text-gray-500 ml-1">({{ question.type }})</span>
              </label>
            </div>
            <button 
              @click="removeQuestionFromProfile(question.id)"
              class="text-red-600 hover:text-red-800">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <!-- Input según el tipo de pregunta -->
          <div v-if="question.type === 'text'">
            <input 
              v-model="profile[question.id]" 
              type="text" 
              class="w-full border border-gray-300 rounded px-3 py-2"
              :placeholder="'Respuesta para: ' + question.text"
            />
          </div>
          
          <div v-else-if="question.type === 'number'">
            <input 
              v-model.number="profile[question.id]" 
              type="number" 
              class="w-full border border-gray-300 rounded px-3 py-2"
              :placeholder="'Número para: ' + question.text"
            />
          </div>
          
          <div v-else-if="question.type === 'text'">
            <input 
              v-model="profile[question.id]" 
              type="text" 
              class="w-full border border-gray-300 rounded px-3 py-2"
              placeholder="Escribe tu respuesta..."
            />
          </div>
          
          <div v-else-if="question.type === 'integer'">
            <input 
              v-model.number="profile[question.id]" 
              type="number" 
              class="w-full border border-gray-300 rounded px-3 py-2"
              placeholder="Escribe un número..."
            />
          </div>
          
          <div v-else-if="question.type === 'boolean'">
            <select v-model="profile[question.id]" class="w-full border border-gray-300 rounded px-3 py-2">
              <option :value="null">Selecciona...</option>
              <option :value="true">Sí</option>
              <option :value="false">No</option>
            </select>
          </div>
          
          <div v-else-if="question.type === 'select'">
            <select v-model="profile[question.id]" class="w-full border border-gray-300 rounded px-3 py-2">
              <option :value="null">Selecciona...</option>
              <option v-for="(option, index) in question.options" :key="index" :value="index">
                {{ option }}
              </option>
            </select>
          </div>
          
          <div v-else-if="question.type === 'multiple'">
            <div class="space-y-2">
              <div v-for="(option, index) in question.options" :key="index" class="flex items-center">
                <input 
                  type="checkbox" 
                  :id="`${question.id}-${index}`"
                  :value="index"
                  v-model="profile[question.id]"
                  class="mr-2"
                />
                <label :for="`${question.id}-${index}`" class="text-sm">{{ option }}</label>
              </div>
            </div>
          </div>
          
          <div v-else-if="question.type === 'date'">
            <input 
              v-model="profile[question.id]" 
              type="date" 
              class="w-full border border-gray-300 rounded px-3 py-2"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Botón de prueba -->
    <div class="flex justify-center pt-6">
      <button 
        @click="testProfile" 
        :disabled="Object.keys(profile).length === 0 || testing"
        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
        <i v-if="testing" class="fas fa-spinner fa-spin mr-2"></i>
        <i v-else class="fas fa-play mr-2"></i>
        {{ testing ? 'Probando...' : 'Probar Perfil contra Requisitos' }}
      </button>
    </div>

    <!-- Resultado de la prueba -->
    <div v-if="testResult" class="mt-6 test-result" ref="testResult">
      <div :class="[
        'p-4 rounded-lg border',
        testResult.es_beneficiario ? 'bg-green-50 border-green-200' : 
        (!testResult.puede_determinar ? 'bg-yellow-50 border-yellow-200' : 'bg-red-50 border-red-200')
      ]">
        <h4 class="font-semibold mb-2">
          <i :class="
            testResult.es_beneficiario ? 'fas fa-check-circle text-green-600' : 
            (!testResult.puede_determinar ? 'fas fa-question-circle text-yellow-600' : 'fas fa-times-circle text-red-600')
          " class="mr-2"></i>
          {{ 
            testResult.es_beneficiario ? 'Perfil CUMPLE los requisitos' : 
            (!testResult.puede_determinar ? 'Perfil INCOMPLETO - No se puede determinar' : 'Perfil NO CUMPLE los requisitos')
          }}
        </h4>
        <div v-if="testResult.detalles" class="text-sm text-gray-700">
          <div v-for="(detalle, index) in testResult.detalles" :key="index" class="mb-3 p-3 bg-white rounded border">
            <div class="font-medium">{{ detalle.descripcion }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ detalle.resultado }}</div>
          </div>
        </div>
        <div v-if="testResult.razones_no_cumple && testResult.razones_no_cumple.length > 0" class="mt-3 p-3 bg-red-100 rounded">
          <div class="font-medium text-red-800">Razones por las que no cumple:</div>
          <ul class="list-disc list-inside text-sm text-red-700 mt-1">
            <li v-for="razon in testResult.razones_no_cumple" :key="razon">{{ razon }}</li>
          </ul>
        </div>
        <div v-if="testResult.condiciones_desconocidas && testResult.condiciones_desconocidas.length > 0" class="mt-3 p-3 bg-yellow-100 rounded">
          <div class="font-medium text-yellow-800">Condiciones desconocidas (falta información):</div>
          <ul class="list-disc list-inside text-sm text-yellow-700 mt-1">
            <li v-for="condicion in testResult.condiciones_desconocidas" :key="condicion">{{ condicion }}</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TesterPerfilComponent',
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
      searchTerm: '',
      searchResults: [],
      selectedQuestions: [],
      profile: {},
      testResult: null,
      testing: false,
      searchTimeout: null,
      searching: false
    }
  },
  methods: {
    async searchQuestions() {
      if (this.searchTimeout) {
        clearTimeout(this.searchTimeout)
      }
      if (!this.searchTerm.trim()) {
        this.searchResults = []
        this.searching = false
        return
      }
      
      this.searching = true
      
      this.searchTimeout = setTimeout(async () => {
        try {
          const response = await fetch(`/admin/questions/search?q=${encodeURIComponent(this.searchTerm)}`, {
            headers: {
              'X-CSRF-TOKEN': this.csrf,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          })
          
          if (!response.ok) {
            throw new Error(`Error ${response.status}`)
          }
          
          const data = await response.json()
          this.searchResults = data.questions || []
        } catch (error) {
          console.error('Error searching questions:', error)
          this.searchResults = []
        } finally {
          this.searching = false
        }
      }, 500)
    },
    
    addQuestionToProfile(question) {
      // Evitar duplicados
      if (!this.selectedQuestions.find(q => q.id === question.id)) {
        this.selectedQuestions.push(question)
        // Inicializar el valor en el perfil según el tipo
        if (question.type === 'multiple') {
          this.profile[question.id] = []
        } else if (question.type === 'integer' || question.type === 'number') {
          this.profile[question.id] = null
        } else if (question.type === 'boolean' || question.type === 'select') {
          this.profile[question.id] = null
        } else if (question.type === 'text') {
          this.profile[question.id] = ''
        } else {
          this.profile[question.id] = ''
        }
        // Remover de los resultados de búsqueda
        this.searchResults = this.searchResults.filter(q => q.id !== question.id)
      }
    },
    
    removeQuestionFromProfile(questionId) {
      this.selectedQuestions = this.selectedQuestions.filter(q => q.id !== questionId)
      delete this.profile[questionId]
    },
    
    async testProfile() {
      this.testing = true
      this.testResult = null
      
      try {
        const response = await fetch(`/admin/ayudas/${this.ayudaId}/test-requirements`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': this.csrf,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            profile: this.profile
          })
        })
        
        if (!response.ok) {
          throw new Error(`Error ${response.status}`)
        }
        
        const result = await response.json()
        this.testResult = result
        
        // Scroll suave hacia el resultado
        this.$nextTick(() => {
          if (this.$refs.testResult) {
            this.$refs.testResult.scrollIntoView({ 
              behavior: 'smooth', 
              block: 'end' 
            })
          }
        })
        
      } catch (error) {
        console.error('Error testing profile:', error)
        this.testResult = {
          es_beneficiario: false,
          detalles: [{ descripcion: 'Error', resultado: 'Error al probar el perfil: ' + error.message }],
          razones_no_cumple: ['Error técnico'],
          condiciones_desconocidas: []
        }
      } finally {
        this.testing = false
      }
    }
  }
}
</script> 