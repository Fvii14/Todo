<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-7xl h-[90vh] flex flex-col">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
            <i class="fas fa-edit"></i>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-800">Editor de Versión</h2>
            <p class="text-gray-600">{{ versionInfo }}</p>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <button 
            @click="saveDraft"
            :disabled="loading"
            class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
          >
            <i class="fas fa-save"></i>
            Guardar Draft
          </button>
          <button 
            @click="publishVersion"
            :disabled="loading"
            class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
          >
            <i class="fas fa-upload"></i>
            Publicar
          </button>
          <button 
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 flex flex-col overflow-hidden">
                 <!-- Tabs - Solo mostrar si hay múltiples tipos -->
         <div v-if="showTabs" class="flex border-b border-gray-200">
           <button 
             @click="activeTab = 'requisitos'"
             :class="activeTab === 'requisitos' ? 'bg-blue-50 text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-800'"
             class="flex-1 px-6 py-3 font-medium transition-colors"
           >
             <i class="fas fa-list-check mr-2"></i>
             Requisitos
           </button>
           <button 
             @click="activeTab = 'condiciones'"
             :class="activeTab === 'condiciones' ? 'bg-blue-50 text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-800'"
             class="flex-1 px-6 py-3 font-medium transition-colors"
           >
             <i class="fas fa-project-diagram mr-2"></i>
             Condiciones
           </button>
         </div>

                 <!-- Tab Content -->
         <div class="flex-1 overflow-hidden">
           <!-- Requisitos Tab -->
           <div v-if="activeTab === 'requisitos'" class="h-full overflow-auto p-6">
             <VueFlowLogicas
               ref="requisitosEditor"
               :ayuda-id="ayudaId"
               :version-id="versionId"
               :version-type="versionType"
               :csrf="csrf"
               :is-modal="true"
             />
           </div>

           <!-- Condiciones Tab -->
           <div v-if="activeTab === 'condiciones'" class="h-full overflow-auto p-6">
             <QuestionnaireLogicTab
               ref="condicionesEditor"
               :ayuda-id="ayudaId"
               :questionnaire-id="questionnaireId"
               :version-id="versionId"
               :version-type="versionType"
               :csrf="csrf"
               :is-modal="true"
             />
           </div>
         </div>
      </div>

      <!-- Loading Overlay -->
      <div v-if="loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3 shadow-lg">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
          <span class="text-gray-700">Guardando cambios...</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import VueFlowLogicas from './VueFlowLogicas.vue'
import QuestionnaireLogicTab from './QuestionnaireLogicTab.vue'

export default {
  name: 'VersionEditorModal',
  components: {
    VueFlowLogicas,
    QuestionnaireLogicTab
  },
  props: {
    isOpen: {
      type: Boolean,
      default: false
    },
    versionData: {
      type: Object,
      default: () => ({})
    },
    ayudaId: {
      type: Number,
      required: true
    },
         questionnaireId: {
       type: Number,
       required: false
     },
     csrf: {
       type: String,
       required: true
     },
     editorType: {
       type: String,
       default: 'requisitos'
     }
  },
  data() {
    return {
      activeTab: 'requisitos',
      loading: false,
      versionId: null,
      versionType: null
    }
  },
     computed: {
     versionInfo() {
       if (!this.versionData.id) return 'Nuevo Draft'
       
       const type = this.versionData.is_draft ? 'Draft' : 'Versión'
       const number = this.versionData.version_number
       const description = this.versionData.version_description || ''
       
       return `${type} v${number}${description ? ` - ${description}` : ''}`
     },
     showTabs() {
       // Solo mostrar tabs si no hay un tipo específico definido
       return !this.editorType || this.editorType === 'both'
     }
   },
  watch: {
    isOpen(newVal) {
      if (newVal) {
        this.initializeEditor()
      }
    },
    versionData: {
      handler(newVal) {
        this.versionId = newVal.id
        this.versionType = newVal.is_draft ? 'draft' : 'version'
      },
      immediate: true
    }
  },
  methods: {
         async initializeEditor() {
       // Establecer el tab activo basado en el tipo de editor
       this.activeTab = this.editorType || 'requisitos'
     },

         async saveDraft() {
       this.loading = true
       try {
         // Lógica real de guardado según el tipo de editor
         if (this.editorType === 'requisitos') {
           await this.saveRequisitosDraft()
         } else if (this.editorType === 'condiciones') {
           await this.saveConditionsDraft()
         }
         
         this.$emit('saved', {
           type: 'draft',
           versionId: this.versionId,
           editorType: this.editorType
         })
         
         this.showToast('Draft guardado correctamente', 'success')
       } catch (error) {
         console.error('Error guardando draft:', error)
         this.showToast('Error guardando draft', 'error')
       } finally {
         this.loading = false
       }
     },

     async publishVersion() {
       if (!confirm('¿Estás seguro de que quieres publicar esta versión? Esto desactivará la versión actual.')) {
         return
       }

       this.loading = true
       try {
         // Lógica real de publicación según el tipo de editor
         if (this.editorType === 'requisitos') {
           await this.publishRequisitosVersion()
         } else if (this.editorType === 'condiciones') {
           await this.publishConditionsVersion()
         }
         
         this.$emit('published', {
           type: 'version',
           versionId: this.versionId,
           editorType: this.editorType
         })
         
         this.showToast('Versión publicada correctamente', 'success')
         this.closeModal()
       } catch (error) {
         console.error('Error publicando versión:', error)
         this.showToast('Error publicando versión', 'error')
       } finally {
         this.loading = false
       }
     },

     async saveRequisitosDraft() {
       // Obtener los datos actuales del editor de requisitos
       const requisitosData = this.getRequisitosData()
       
       if (this.versionId) {
         // Actualizar draft existente
         const response = await fetch(`/admin/versions/requisitos/${this.versionId}/draft`, {
           method: 'PUT',
           headers: {
             'Content-Type': 'application/json',
             'X-CSRF-TOKEN': this.csrf
           },
           body: JSON.stringify({
             json_regla: requisitosData,
             descripcion: this.versionData.version_description || 'Draft automático'
           })
         })
         
         if (!response.ok) {
           throw new Error('Error actualizando draft de requisitos')
         }
               } else {
          // Crear nuevo draft
          const response = await fetch(`/admin/ayudas/${this.ayudaId}/versions/requisitos/draft`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': this.csrf
            },
            body: JSON.stringify({
              description: this.versionData.version_description || 'Draft automático',
              json_regla: requisitosData // Enviar los datos del draft
            })
          })
         
         if (!response.ok) {
           throw new Error('Error creando draft de requisitos')
         }
         
         const data = await response.json()
         this.versionId = data.draft.id
       }
     },

     async saveConditionsDraft() {
       // Obtener los datos actuales del editor de condiciones
       const conditionsData = this.getConditionsData()
       
       console.log('Guardando draft de condiciones:', {
         versionId: this.versionId,
         conditionsDataLength: conditionsData.length,
         conditionsData: conditionsData
       })
       
       if (this.versionId) {
         // Actualizar draft existente
         console.log('Actualizando draft existente con ID:', this.versionId)
         
                   const requestBody = {
            conditions_data: conditionsData,
            descripcion: this.versionData.version_description || 'Draft automático'
          };
          
          console.log('Enviando datos al servidor:', requestBody);
          
          const response = await fetch(`/admin/versions/conditions/${this.versionId}/draft`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': this.csrf
            },
            body: JSON.stringify(requestBody)
          })
         
         console.log('Respuesta del servidor:', response.status, response.statusText)
         
         if (!response.ok) {
           const errorText = await response.text()
           console.error('Error response:', errorText)
           throw new Error('Error actualizando draft de condiciones')
         }
               } else {
          // Crear nuevo draft
          const requestBody = {
            description: this.versionData.version_description || 'Draft automático',
            conditions_data: conditionsData // Enviar los datos del draft
          };
          
          console.log('Creando nuevo draft con datos:', requestBody);
          
          const response = await fetch(`/admin/questionnaires/${this.questionnaireId}/versions/conditions/draft`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': this.csrf
            },
            body: JSON.stringify(requestBody)
          })
         
         if (!response.ok) {
           throw new Error('Error creando draft de condiciones')
         }
         
         const data = await response.json()
         this.versionId = data.draft.id
       }
     },

     async publishRequisitosVersion() {
       const response = await fetch(`/admin/versions/requisitos/${this.versionId}/publish`, {
         method: 'POST',
         headers: {
           'X-CSRF-TOKEN': this.csrf
         }
       })
       
       if (!response.ok) {
         throw new Error('Error publicando versión de requisitos')
       }
     },

     async publishConditionsVersion() {
       const response = await fetch(`/admin/versions/conditions/${this.versionId}/publish`, {
         method: 'POST',
         headers: {
           'X-CSRF-TOKEN': this.csrf
         }
       })
       
       if (!response.ok) {
         throw new Error('Error publicando versión de condiciones')
       }
     },

     getRequisitosData() {
       // Obtener datos del editor de requisitos usando ref
       if (this.$refs.requisitosEditor) {
         return this.$refs.requisitosEditor.requisitosEdit || []
       }
       return []
     },

     getConditionsData() {
       // Obtener datos del editor de condiciones usando ref
       console.log('Obteniendo datos de condiciones:', {
         hasRef: !!this.$refs.condicionesEditor,
         editorType: this.editorType,
         draftConditions: this.$refs.condicionesEditor ? this.$refs.condicionesEditor.draftConditions : 'No ref',
         conditions: this.$refs.condicionesEditor ? this.$refs.condicionesEditor.conditions : 'No ref'
       })
       
       if (this.$refs.condicionesEditor) {
         // En modo modal, usar el estado local del draft
         if (this.editorType === 'condiciones') {
           // En modo modal, SIEMPRE usar draftConditions, incluso si está vacío
           const data = this.$refs.condicionesEditor.draftConditions || []
           console.log('Usando datos del draft (modal):', data)
           console.log('Datos finales obtenidos:', data)
           return data
         }
       }
       return []
     },

    closeModal() {
      this.$emit('close')
    },

    showToast(message, type = 'info') {
      console.log(`${type.toUpperCase()}: ${message}`)
    }
  }
}
</script>

<style scoped>
/* Estilos específicos del modal */
</style> 