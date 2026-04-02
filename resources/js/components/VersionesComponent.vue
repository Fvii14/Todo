<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-800 mb-2">Sistema de Versiones</h2>
      <p class="text-gray-600">Gestiona las versiones de requisitos y condiciones de esta ayuda</p>
    </div>

    <div class="mb-6">
      <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
        <button 
          @click="activeTab = 'requisitos'"
          :class="activeTab === 'requisitos' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-800'"
          class="flex-1 px-4 py-2 rounded-md font-medium transition-all duration-200"
        >
          Versiones de Requisitos
        </button>
        <button 
          @click="activeTab = 'condiciones'"
          :class="activeTab === 'condiciones' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-800'"
          class="flex-1 px-4 py-2 rounded-md font-medium transition-all duration-200"
        >
          Versiones de Condiciones
        </button>
      </div>
    </div>

    <div v-if="activeTab === 'requisitos'" class="space-y-6">
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-blue-800 mb-2">Estado Actual</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-white rounded-lg p-3 border">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-600">Versión Activa:</span>
              <span v-if="requisitosData.active_version" class="text-sm font-bold text-green-600">
                v{{ requisitosData.active_version.version_number }}
              </span>
              <span v-else class="text-sm text-gray-500">Ninguna</span>
            </div>
            <div v-if="requisitosData.active_version" class="mt-2 text-xs text-gray-500">
              Publicada: {{ formatDate(requisitosData.active_version.published_at) }}
            </div>
          </div>
          <div class="bg-white rounded-lg p-3 border">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-600">Draft Actual:</span>
              <span v-if="requisitosData.current_draft" class="text-sm font-bold text-orange-600">
                v{{ requisitosData.current_draft.version_number }}
              </span>
              <span v-else class="text-sm text-gray-500">Ninguno</span>
            </div>
            <div v-if="requisitosData.current_draft" class="mt-2 text-xs text-gray-500">
              Creado: {{ formatDate(requisitosData.current_draft.created_at) }}
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap gap-3">
        <button 
          @click="openEditor({})"
          :disabled="loading"
          class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
        >
          <i class="fas fa-plus"></i>
          Crear Nuevo Draft
        </button>
      </div>

      <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Historial de Versiones</h3>
        <div v-if="requisitosData.versions && requisitosData.versions.length > 0" class="space-y-3">
          <div 
            v-for="version in requisitosData.versions" 
            :key="version.id"
            class="bg-white rounded-lg p-4 border hover:shadow-md transition-shadow"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2">
                  <span class="text-lg font-bold text-gray-800">v{{ version.version_number }}</span>
                  <span v-if="version.is_active" class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Activa</span>
                  <span v-if="version.is_draft" class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">Draft</span>
                </div>
                <div class="text-sm text-gray-600">
                  <div>Creado por: {{ version.created_by?.name || 'Admin' }}</div>
                  <div>{{ formatDate(version.created_at) }}</div>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button 
                  @click="openEditor(version)"
                  :disabled="loading"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  Editar
                </button>
                <button 
                  v-if="!version.is_active"
                  @click="deleteVersion('requisitos', version.id)"
                  :disabled="loading"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                >
                  Eliminar
                </button>
              </div>
            </div>
            <div v-if="version.version_description" class="mt-2 text-sm text-gray-600">
              {{ version.version_description }}
            </div>
          </div>
        </div>
        <div v-else class="text-center text-gray-500 py-8">
          No hay versiones disponibles
        </div>
      </div>
    </div>

    <div v-if="activeTab === 'condiciones'" class="space-y-6">
      <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-purple-800 mb-2">Estado Actual</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-white rounded-lg p-3 border">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-600">Versión Activa:</span>
              <span v-if="condicionesData.active_version" class="text-sm font-bold text-green-600">
                v{{ condicionesData.active_version.version_number }}
              </span>
              <span v-else class="text-sm text-gray-500">Ninguna</span>
            </div>
            <div v-if="condicionesData.active_version" class="mt-2 text-xs text-gray-500">
              Publicada: {{ formatDate(condicionesData.active_version.published_at) }}
            </div>
          </div>
          <div class="bg-white rounded-lg p-3 border">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-600">Draft Actual:</span>
              <span v-if="condicionesData.current_draft" class="text-sm font-bold text-orange-600">
                v{{ condicionesData.current_draft.version_number }}
              </span>
              <span v-else class="text-sm text-gray-500">Ninguno</span>
            </div>
            <div v-if="condicionesData.current_draft" class="mt-2 text-xs text-gray-500">
              Creado: {{ formatDate(condicionesData.current_draft.created_at) }}
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap gap-3">
        <button 
          @click="openEditor({}, 'condiciones')"
          :disabled="loading"
          class="bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
        >
          <i class="fas fa-plus"></i>
          Crear Nuevo Draft
        </button>
      </div>

      <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Historial de Versiones</h3>
        <div v-if="condicionesData.versions && condicionesData.versions.length > 0" class="space-y-3">
          <div 
            v-for="version in condicionesData.versions" 
            :key="version.id"
            class="bg-white rounded-lg p-4 border hover:shadow-md transition-shadow"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2">
                  <span class="text-lg font-bold text-gray-800">v{{ version.version_number }}</span>
                  <span v-if="version.is_active" class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Activa</span>
                  <span v-if="version.is_draft" class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">Draft</span>
                </div>
                <div class="text-sm text-gray-600">
                  <div>Creado por: {{ version.created_by?.name || 'Admin' }}</div>
                  <div>{{ formatDate(version.created_at) }}</div>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button 
                  @click="openEditor(version, 'condiciones')"
                  :disabled="loading"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  Editar
                </button>
                <button 
                  v-if="!version.is_active"
                  @click="deleteVersion('condiciones', version.id)"
                  :disabled="loading"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                >
                  Eliminar
                </button>
              </div>
            </div>
            <div v-if="version.version_description" class="mt-2 text-sm text-gray-600">
              {{ version.version_description }}
            </div>
          </div>
        </div>
        <div v-else class="text-center text-gray-500 py-8">
          No hay versiones disponibles
        </div>
      </div>
    </div>

    <!-- Version Editor Modal -->
                 <VersionEditorModal
               :is-open="editorModalOpen"
               :version-data="selectedVersion"
               :ayuda-id="ayudaId"
               :questionnaire-id="questionnaireId"
               :csrf="csrf"
               :editor-type="editorType"
               @close="closeEditor"
               @saved="onVersionSaved"
               @published="onVersionPublished"
             />

    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
        <span class="text-gray-700">Procesando...</span>
      </div>
    </div>
  </div>
</template>

<script>
import VersionEditorModal from './VersionEditorModal.vue'

export default {
  name: 'VersionesComponent',
  components: {
    VersionEditorModal
  },
  props: {
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
    }
  },
  data() {
    return {
      activeTab: 'requisitos',
      loading: false,
      editorModalOpen: false,
      selectedVersion: {},
      editorType: 'requisitos', // Tipo de editor por defecto
      requisitosData: {
        versions: [],
        active_version: null,
        current_draft: null
      },
      condicionesData: {
        versions: [],
        active_version: null,
        current_draft: null
      }
    }
  },
  mounted() {
    this.loadData()
  },
  methods: {
    async loadData() {
      this.loading = true
      try {
        await Promise.all([
          this.loadRequisitosVersions(),
          this.loadConditionsVersions()
        ])
      } catch (error) {
        console.error('Error cargando datos:', error)
        this.showToast('Error cargando datos', 'error')
      } finally {
        this.loading = false
      }
    },

    async loadRequisitosVersions() {
      try {
        const response = await fetch(`/admin/ayudas/${this.ayudaId}/versions/requisitos`)
        const data = await response.json()
        if (data.success) {
          this.requisitosData = data
        }
      } catch (error) {
        console.error('Error cargando versiones de requisitos:', error)
      }
    },

    async loadConditionsVersions() {
      if (!this.questionnaireId) return
      
      try {
        const response = await fetch(`/admin/questionnaires/${this.questionnaireId}/versions/conditions`)
        const data = await response.json()
        if (data.success) {
          this.condicionesData = data
        }
      } catch (error) {
        console.error('Error cargando versiones de condiciones:', error)
      }
    },

         openEditor(version, type = 'requisitos') {
       this.selectedVersion = version
       this.editorType = type // Añadir el tipo de editor
       this.editorModalOpen = true
     },

    closeEditor() {
      this.editorModalOpen = false
      this.selectedVersion = {}
    },

    async onVersionSaved(data) {
      this.showToast('Versión guardada correctamente', 'success')
      await this.loadData()
    },

    async onVersionPublished(data) {
      this.showToast('Versión publicada correctamente', 'success')
      await this.loadData()
    },

    async publishRequisitosVersion(versionId) {
      if (!confirm('¿Estás seguro de que quieres publicar esta versión? Esto desactivará la versión actual.')) {
        return
      }

      this.loading = true
      try {
        const response = await fetch(`/admin/versions/requisitos/${versionId}/publish`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': this.csrf
          }
        })
        
        const data = await response.json()
        if (data.success) {
          this.showToast('Versión publicada correctamente', 'success')
          await this.loadRequisitosVersions()
        } else {
          this.showToast(data.error || 'Error publicando versión', 'error')
        }
      } catch (error) {
        console.error('Error publicando versión:', error)
        this.showToast('Error publicando versión', 'error')
      } finally {
        this.loading = false
      }
    },

    async publishConditionsVersion(versionId) {
      if (!confirm('¿Estás seguro de que quieres publicar esta versión? Esto desactivará la versión actual.')) {
        return
      }

      this.loading = true
      try {
        const response = await fetch(`/admin/versions/conditions/${versionId}/publish`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': this.csrf
          }
        })
        
        const data = await response.json()
        if (data.success) {
          this.showToast('Versión publicada correctamente', 'success')
          await this.loadConditionsVersions()
        } else {
          this.showToast(data.error || 'Error publicando versión', 'error')
        }
      } catch (error) {
        console.error('Error publicando versión:', error)
        this.showToast('Error publicando versión', 'error')
      } finally {
        this.loading = false
      }
    },

    async deleteVersion(type, versionId) {
      if (!confirm('¿Estás seguro de que quieres eliminar esta versión? Esta acción no se puede deshacer.')) {
        return
      }

      this.loading = true
      try {
        const response = await fetch(`/admin/versions/${type}/${versionId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': this.csrf
          }
        })
        
        const data = await response.json()
        if (data.success) {
          this.showToast('Versión eliminada correctamente', 'success')
          if (type === 'requisitos') {
            await this.loadRequisitosVersions()
          } else {
            await this.loadConditionsVersions()
          }
        } else {
          this.showToast(data.error || 'Error eliminando versión', 'error')
        }
      } catch (error) {
        console.error('Error eliminando versión:', error)
        this.showToast('Error eliminando versión', 'error')
      } finally {
        this.loading = false
      }
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },

    showToast(message, type = 'info') {
      console.log(`${type.toUpperCase()}: ${message}`)
    }
  }
}
</script>

<style scoped></style> 