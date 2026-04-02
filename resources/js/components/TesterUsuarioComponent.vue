<template>
  <div class="tester-usuario">
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      <p class="mt-2 text-gray-600">Cargando usuarios...</p>
    </div>

    <div v-else>
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Buscar Usuario
        </label>
        <div class="flex gap-4">
          <input 
            v-model="searchTerm" 
            @input="searchUsers"
            type="text" 
            placeholder="Buscar por nombre, email o ID..."
            class="flex-1 border border-gray-300 rounded px-3 py-2"
          />
          <button 
            @click="searchUsers"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
            :disabled="searching">
            <i v-if="searching" class="fas fa-spinner fa-spin"></i>
            <i v-else class="fas fa-search"></i>
          </button>
        </div>
      </div>

      <!-- Resultados de búsqueda -->
      <div v-if="searchResults.length > 0" class="mb-6">
        <h4 class="font-semibold text-gray-800 mb-3">Usuarios encontrados:</h4>
        <div class="bg-gray-50 p-4 rounded-lg max-h-64 overflow-y-auto">
          <div v-for="user in searchResults" :key="user.id" class="mb-3 pb-3 border-b border-gray-200 last:border-b-0">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="font-medium text-sm">{{ user.name }}</div>
                <div class="text-xs text-gray-500">{{ user.email }} - ID: {{ user.id }}</div>
                <div class="text-xs text-gray-400">Registrado: {{ formatDate(user.created_at) }}</div>
              </div>
              <button 
                @click="selectUser(user)"
                class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 ml-2">
                <i class="fas fa-check"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
      <div v-else-if="searchTerm.trim() && !searching && searchResults.length === 0" class="mb-6">
        <div class="bg-gray-50 p-4 rounded-lg text-center">
          <i class="fas fa-search text-gray-400 text-2xl mb-2"></i>
          <p class="text-gray-600">No se encontraron usuarios con ese criterio</p>
          <p class="text-sm text-gray-500 mt-1">Intenta con otro nombre, email o ID</p>
        </div>
      </div>

      <div v-if="selectedUser" class="bg-blue-50 p-4 rounded-lg mb-6">
        <h4 class="font-semibold text-blue-800 mb-2">Usuario Seleccionado:</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div>
            <strong>Nombre:</strong> {{ selectedUser.name }}
          </div>
          <div>
            <strong>Email:</strong> {{ selectedUser.email }}
          </div>
          <div>
            <strong>ID:</strong> {{ selectedUser.id }}
          </div>
          <div>
            <strong>Registrado:</strong> {{ formatDate(selectedUser.created_at) }}
          </div>
        </div>
      </div>

      <div v-if="selectedUser && userAnswers.length > 0" class="mb-6">
        <div class="flex items-center justify-between mb-3">
          <h4 class="font-semibold text-gray-800">Respuestas del Usuario:</h4>
          <button 
            @click="toggleAnswers"
            class="text-blue-600 hover:text-blue-800 text-sm flex items-center">
            <i :class="showAnswers ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="mr-1"></i>
            {{ showAnswers ? 'Ocultar' : 'Mostrar' }} respuestas
          </button>
        </div>
        <div v-show="showAnswers" class="bg-gray-50 p-4 rounded-lg max-h-64 overflow-y-auto">
          <div v-for="answer in userAnswers" :key="answer.question_id" class="mb-3 pb-3 border-b border-gray-200 last:border-b-0">
            <div class="text-sm">
              <strong>{{ answer.question_text }}:</strong>
              <span class="ml-2 text-gray-600">{{ answer.formatted_answer }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-center pt-6">
        <button 
          @click="testUser" 
          :disabled="!selectedUserId || testing"
          class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
          <i v-if="testing" class="fas fa-spinner fa-spin mr-2"></i>
          <i v-else class="fas fa-play mr-2"></i>
          {{ testing ? 'Probando...' : 'Probar Usuario contra Requisitos' }}
        </button>
      </div>

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
                testResult.es_beneficiario ? 'Usuario CUMPLE los requisitos' : 
                (!testResult.puede_determinar ? 'Usuario INCOMPLETO - No se puede determinar' : 'Usuario NO CUMPLE los requisitos')
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
  </div>
</template>

<script>
export default {
  name: 'TesterUsuarioComponent',
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
      loading: false,
      searchTerm: '',
      searchResults: [],
      selectedUserId: '',
      selectedUser: null,
      userAnswers: [],
      testResult: null,
      testing: false,
      showAnswers: false,
      searchTimeout: null,
      searching: false
    }
  },
  methods: {
    async searchUsers() {
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
          const response = await fetch(`/admin/users/search?q=${encodeURIComponent(this.searchTerm)}`, {
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
          this.searchResults = data.users || []
        } catch (error) {
          console.error('Error searching users:', error)
          this.searchResults = []
        } finally {
          this.searching = false
        }
      }, 500)
    },
    
    selectUser(user) {
      this.selectedUserId = user.id
      this.selectedUser = user
      this.searchResults = [] // Limpiar resultados de búsqueda
      this.searchTerm = '' // Limpiar término de búsqueda
      this.userAnswers = []
      this.testResult = null
      this.fetchUserDetails()
    },
    

    
    async fetchUserDetails() {
      try {
        const response = await fetch(`/admin/users/${this.selectedUserId}/answers`, {
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
        this.selectedUser = data.user
        this.userAnswers = data.answers || []
      } catch (error) {
        console.error('Error fetching user details:', error)
      }
    },
    
    async testUser() {
      this.testing = true
      this.testResult = null
      
      try {
        const response = await fetch(`/admin/ayudas/${this.ayudaId}/test-user-requirements`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': this.csrf,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            user_id: this.selectedUserId
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
        console.error('Error testing user:', error)
        this.testResult = {
          es_beneficiario: false,
          detalles: [{ descripcion: 'Error', resultado: 'Error al probar el usuario: ' + error.message }],
          razones_no_cumple: ['Error técnico'],
          condiciones_desconocidas: []
        }
      } finally {
        this.testing = false
      }
    },
    
    toggleAnswers() {
      this.showAnswers = !this.showAnswers
    },

    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('es-ES')
    }
  }
}
</script> 