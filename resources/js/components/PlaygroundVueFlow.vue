<template>
  <div class="vueflow-container">
    <VueFlow v-model="elements"
             :default-viewport="{ zoom: 1 }"
             :fit-view-on-init="true"
             class="vueflow-playground"
             style="width: 100%; height: 100%;">
      <template #node-default="{ data, id }">
        <div :class="getNodeClasses({ id, data })" 
             class="custom-node"
             >
          <div class="node-header">
            <span class="node-order">{{ data?.order || '?' }}</span>
            <span class="node-type">{{ data?.type || 'unknown' }}</span>
          </div>
          <div class="node-content">
            {{ data?.text || 'Sin etiqueta' }}
          </div>
        </div>
      </template>
      <template #edge-default="edgeProps">
        <div class="edge-label">
          {{ edgeProps.label }}
        </div>
      </template>
    </VueFlow>
  </div>
</template>

<script>
import { VueFlow } from '@vue-flow/core'
import '@vue-flow/core/dist/style.css'
import '@vue-flow/core/dist/theme-default.css'

export default {
  name: 'PlaygroundVueFlow',
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
    },
    currentQuestionId: {
      type: Number,
      default: null
    }
  },
  data() {
    return {
      elements: [],
      questions: [],
      conditions: [],
      currentQuestionId: null
    }
  },
  emits: ['question-changed'],
  watch: {
    currentQuestionId() {
      this.$nextTick(() => {
        this.elements = [...this.elements]
      })
    }
  },
  async mounted() {
    await this.fetchData()
    this.initializeVueFlow()
  },
  methods: {
    async fetchData() {
      try {
        const response = await fetch(`/admin/ayudas/${this.ayudaId}/questionnaire`, {
          headers: {
            'X-CSRF-TOKEN': this.csrf,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          credentials: 'same-origin'
        })
        
        if (!response.ok) {
          throw new Error(`Error ${response.status}`)
        }
        
        const data = await response.json()
        
        // Ordenar las preguntas por el campo order
        this.questions = (data.questions || []).sort((a, b) => {
          const orderA = a.order || 0
          const orderB = b.order || 0
          return orderA - orderB
        })
        this.conditions = data.conditions || []
        
      } catch (error) {
        console.error('Error fetching VueFlow data:', error)
      }
    },
    
    initializeVueFlow() {
      // Crear nodos para cada pregunta con sus condiciones
      const nodes = this.questions.map((question, index) => {
        const questionConditions = this.conditions.filter(c => c.question_id === question.id)
        
        const nodeData = {
          text: question.text || 'Sin texto',
          order: question.order || index + 1,
          type: question.type || 'unknown',
          questionId: question.id,
          isCurrent: index === 0,
          conditions: questionConditions
        }
        
        return {
          id: `question-${question.id}`,
          type: 'default',
          position: { x: index * 300, y: index * 150 },
          data: nodeData
        }
      })
      
      // Crear edges basados en las condiciones
      const edges = []
      this.conditions.forEach((condition, index) => {
        const sourceId = `question-${condition.question_id}`
        const targetId = condition.next_question_id ? `question-${condition.next_question_id}` : 'fin'
        
        // Verificar que ambos nodos existen
        const sourceExists = nodes.some(n => n.id === sourceId)
        const targetExists = targetId === 'fin' || nodes.some(n => n.id === targetId)
        
        if (sourceExists && targetExists) {
          const sourceQuestion = this.questions.find(q => q.id === condition.question_id)
          let label = `${condition.operator} ${this.formatConditionValue(condition.value)}`
          if (sourceQuestion && sourceQuestion.type === 'select' && sourceQuestion.options) {
            const optionIndex = parseInt(condition.value)
            if (!isNaN(optionIndex) && sourceQuestion.options[optionIndex]) {
              label = `"${sourceQuestion.options[optionIndex]}"`
            }
          }
          else if (sourceQuestion && sourceQuestion.type === 'boolean') {
            label = condition.value == 1 ? 'Sí' : 'No'
          }

          edges.push({
            id: `edge-${condition.id}`,
            source: sourceId,
            target: targetId,
            label: label,
            type: 'smoothstep',
            style: { stroke: '#3b82f6', strokeWidth: 2 }
          })
        }
      })
      
      // Añadir nodo FIN si hay condiciones que van a fin
      if (this.conditions.some(c => !c.next_question_id)) {
        nodes.push({
          id: 'fin',
          type: 'default',
          position: { x: this.questions.length * 300, y: this.questions.length * 150 },
          data: {
            text: '🏁 FIN DEL CUESTIONARIO',
            order: this.questions.length + 1,
            type: 'fin',
            questionId: null,
            isCurrent: false,
            conditions: []
          }
        })
      }
      
      this.elements = [...nodes, ...edges]
    },
    
    getNodeClasses(node) {
      if (!node || !node.data) {
        return 'node-base'
      }
      
      const classes = ['node-base']
      
      if (node.data.questionId === this.currentQuestionId) {
        classes.push('node-current')
      }
      
      if (node.data.type === 'fin') {
        classes.push('node-fin')
      }
      
      return classes.join(' ')
    },
    
    updateCurrentNode(questionId) {
      // Actualizar el estado de los nodos
      this.elements.forEach(element => {
        if (element.type === 'custom') {
          element.data.isCurrent = element.data.questionId === questionId
        }
      })
    },
    
    formatConditionValue(value) {
      if (Array.isArray(value)) {
        return value.join(', ')
      } else if (typeof value === 'object' && value !== null) {
        return JSON.stringify(value)
      } else {
        return String(value)
      }
    }
  }
}
</script>

<style scoped>
.vueflow-container {
  width: 100%;
  height: 100%;
  background: #f8fafc;
}

.vueflow-playground {
  width: 100%;
  height: 100%;
  background: #f8fafc;
}

.custom-node {
  padding: 1rem;
  border-radius: 0.75rem;
  border: 3px solid #e5e7eb;
  background: white;
  min-width: 250px;
  max-width: 300px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.node-current {
  border-color: #3b82f6;
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3), 0 8px 16px rgba(59, 130, 246, 0.2);
  transform: scale(1.08);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3), 0 8px 16px rgba(59, 130, 246, 0.2);
  }
  50% {
    box-shadow: 0 0 0 6px rgba(59, 130, 246, 0.4), 0 12px 24px rgba(59, 130, 246, 0.3);
  }
}

.node-fin {
  border-color: #ef4444;
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
}

.node-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.node-order {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  font-weight: 700;
}

.node-type {
  color: #6b7280;
  text-transform: uppercase;
  font-size: 0.625rem;
  letter-spacing: 0.05em;
}

.node-content {
  font-size: 0.875rem;
  line-height: 1.25rem;
  color: #374151;
  font-weight: 500;
  margin-bottom: 0.5rem;
  word-wrap: break-word;
  overflow-wrap: break-word;
  white-space: normal;
  display: block;
  min-height: 1.25rem;
}

.node-conditions {
  margin-top: 0.5rem;
  padding-top: 0.5rem;
  border-top: 1px solid #e5e7eb;
}

.condition-item {
  font-size: 0.75rem;
  color: #6b7280;
  background: #f3f4f6;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  margin-bottom: 0.25rem;
  font-family: 'Courier New', monospace;
}

.edge-label {
  background: white;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  color: #374151;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}
</style> 