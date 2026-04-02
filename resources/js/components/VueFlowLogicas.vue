<template>
  <div class="flex flex-col items-center w-full">
    <div class="w-full flex justify-center bg-gradient-to-b from-white/95 to-white/80 shadow-md border-b border-blue-100 py-4 mb-6">
      <div class="flex flex-col items-center w-full max-w-2xl">
        <h2 class="text-lg font-bold mb-2 text-blue-900">Añadir nuevo requisito</h2>
        <div class="flex flex-col gap-2 items-center mb-2 w-full">
          <input v-model="newReqDescripcion" :disabled="addingReq" placeholder="Descripción del nuevo requisito" class="border rounded px-3 py-2 flex-1" />
          <div class="flex gap-2 w-full">
            <Multiselect
              v-model="newReqQuestion"
              :options="questions"
              label="text"
              track-by="id"
              placeholder="Selecciona pregunta"
              :searchable="true"
              :close-on-select="true"
              :allow-empty="false"
              class="flex-1"
            />
            <select v-model="newReqOperator" class="border rounded px-2 py-1">
              <option v-for="op in operadores" :value="op.value">{{ op.label }}</option>
            </select>
            <input v-if="getNewReqQuestionType()==='text' || getNewReqQuestionType()==='number'" v-model="newReqValue" class="border rounded px-2 py-1 flex-1" />
            <select v-else-if="getNewReqQuestionType()==='select'" v-model="newReqValue" class="border rounded px-2 py-1 flex-1">
              <option v-for="(opt, i) in getNewReqQuestionOptions()" :value="i">{{ opt }}</option>
            </select>
            <Multiselect
              v-else-if="getNewReqQuestionType()==='multiple'"
              v-model="newReqValue"
              :options="getNewReqQuestionOptions()"
              :multiple="true"
              placeholder="Selecciona opción(es)"
              class="flex-1"
            />
            <select v-else-if="getNewReqQuestionType()==='boolean'" v-model="newReqValue" class="border rounded px-2 py-1 flex-1">
              <option :value="1">Sí</option>
              <option :value="0">No</option>
            </select>
            <input v-else v-model="newReqValue" class="border rounded px-2 py-1 flex-1" />
          </div>
          <button @click="addNewRequisito" :disabled="addingReq" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition-all duration-150 w-full">
            <span v-if="addingReq" class="animate-pulse">Añadiendo...</span>
            <span v-else>Añadir</span>
          </button>
        </div>
        <div v-if="addReqError" class="text-red-600 text-sm mt-1">{{ addReqError }}</div>
      </div>
    </div>
    <div class="bg-white rounded-3xl shadow-2xl border border-gray-200 p-6 w-full max-w-[1800px] mx-auto overflow-x-auto backdrop-blur-md" style="backdrop-filter: blur(8px);">
      <div style="width: 100%; min-width: 900px; height: 80vh;">
        <VueFlow :nodes="nodes" :edges="edges" fit-view-on-init :zoom-on-scroll="false" :zoom-on-double-click="false" :default-zoom="1"
          :min-zoom="0.2" :max-zoom="2" :pan-on-drag="true" :pan-on-scroll="true" :snap-to-grid="true" :snap-grid="[20,20]"
          class="vueflow-custom" @node-click="handleNodeClick">
          <background gap="32" size="1" color="#dbeafe" />
          <template #node-label="{ data }">
            <div :class="
              data.type === 'main' ? 'glass-main' :
              data.type === 'req' ? 'glass-req' :
              data.type === 'cond' ? 'glass-cond' :
              'glass-rule'"
              class="node-glass flex items-center gap-3 px-7 py-4 border-2 rounded-2xl shadow-xl min-w-[300px] max-w-[480px] transition-all duration-300 hover:scale-105 hover:shadow-2xl">
              <span v-if="data.type === 'main'" class="text-3xl">🏆</span>
              <span v-else-if="data.type === 'req'" class="text-3xl">📋</span>
              <span v-else-if="data.type === 'cond'" class="text-3xl">🔗</span>
              <span v-else class="text-2xl">📝</span>
              <div class="flex-1">
                <div v-if="data.type === 'main'">
                  <b class="text-2xl font-extrabold tracking-tight text-blue-900 drop-shadow">{{ data.label }}</b>
                </div>
                <div v-else-if="data.type === 'req'">
                  <div class="flex items-center justify-between">
                    <div class="font-bold text-lg mb-1 text-green-900">{{ data.label }}</div>
                    <button @click.stop="deleteRequisito(data.label)" class="ml-2 text-red-600 hover:text-red-800 text-xl" title="Eliminar requisito">🗑️</button>
                  </div>
                  <div class="text-xs text-green-700 font-semibold mb-1">{{ data.condition }}</div>
                  <div v-if="data._debug_no_rules" class="text-xs text-red-600">(No se detectaron reglas para este requisito)</div>
                </div>
                <div v-else-if="data.type === 'cond'">
                  <div class="text-xs text-purple-700 font-semibold mb-1">{{ data.condition }}</div>
                  <div class="text-xs text-gray-600">({{ data.numRules }} reglas)</div>
                </div>
                <div v-else-if="data.type === 'rule'">
                  <span class="font-semibold text-base">{{ decodeText(data.question_text) }}</span>
                  <span class="text-gray-600">
                    — {{ humanOperator(data.operator) }}
                    <b class="text-blue-900">
                      {{ Array.isArray(data.value_text) ? data.value_text.join(', ') : (data.value_text ?? data.value) }}
                    </b>
                  </span>
                </div>
              </div>
            </div>
          </template>
        </VueFlow>
      </div>
    </div>
    <transition name="fade">
      <div v-if="modalVisible" class="modal fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg relative animate-fadein">
          <button @click="closeModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
          <div v-if="modalType==='req'">
            <h2 class="text-xl font-bold mb-4">Editar requisito</h2>
            <input v-model="modalData.descripcion" class="border rounded px-3 py-2 w-full mb-4" />
            <button @click="deleteRequisitoModal" class="absolute top-2 left-2 text-red-600 hover:text-red-800 text-2xl" title="Eliminar requisito">🗑️</button>
            <div v-if="!modalData.rules || !modalData.rules.length" class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
              <h3 class="font-semibold mb-2 text-blue-900">Este requisito no tiene reglas. Añade la primera:</h3>
              <div class="flex flex-col gap-2">
                <select v-model="newRule.question_id" class="border rounded px-2 py-1">
                  <option value="">Selecciona pregunta</option>
                  <option v-for="q in questions" :value="q.id">{{ q.text }}</option>
                </select>
                <select v-model="newRule.operator" class="border rounded px-2 py-1">
                  <option v-for="op in operadores" :value="op.value">{{ op.label }}</option>
                </select>
                <input v-if="getQuestionType(newRule.question_id)==='text' || getQuestionType(newRule.question_id)==='number'" v-model="newRule.value" class="border rounded px-2 py-1" />
                <select v-else-if="getQuestionType(newRule.question_id)==='select'" v-model="newRule.value" class="border rounded px-2 py-1">
                  <option v-for="(opt, i) in getQuestionOptions(newRule.question_id)" :value="i">{{ opt }}</option>
                </select>
                <select v-else-if="getQuestionType(newRule.question_id)==='multiple'" v-model="newRule.value" multiple class="border rounded px-2 py-1">
                  <option v-for="(opt, i) in getQuestionOptions(newRule.question_id)" :value="i">{{ opt }}</option>
                </select>
                <select v-else-if="getQuestionType(newRule.question_id)==='boolean'" v-model="newRule.value" class="border rounded px-2 py-1">
                  <option :value="1">Sí</option>
                  <option :value="0">No</option>
                </select>
                <input v-else v-model="newRule.value" class="border rounded px-2 py-1" />
                <button @click="addFirstRuleToReq()" class="bg-blue-600 text-white px-4 py-1 rounded shadow hover:bg-blue-700 mt-2">Añadir regla</button>
              </div>
            </div>
            <button @click="saveModal" :disabled="modalLoading" class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">Guardar</button>
            <div v-if="modalError" class="text-red-600 mt-2">{{ modalError }}</div>
          </div>
          <div v-else-if="modalType==='rule'">
            <h2 class="text-xl font-bold mb-4">Editar regla</h2>
            <div class="mb-4">
              <label class="block font-semibold mb-1">Pregunta:</label>
              <select v-model="modalData.question_id" class="border rounded px-1 py-0.5 w-full mb-2">
                <option v-for="q in questions" :value="q.id">{{ q.text }}</option>
              </select>
              <label class="block font-semibold mb-1">Operador:</label>
              <select v-model="modalData.operator" class="border rounded px-1 py-0.5 w-full mb-2">
                <option v-for="op in operadores" :value="op.value">{{ op.label }}</option>
              </select>
              <label class="block font-semibold mb-1">Valor:</label>
              <input v-if="getQuestionType(modalData.question_id)==='text' || getQuestionType(modalData.question_id)==='number'" v-model="modalData.value" class="border rounded px-1 py-0.5 w-full" />
              <select v-else-if="getQuestionType(modalData.question_id)==='select'" v-model="modalData.value" class="border rounded px-1 py-0.5 w-full">
                <option v-for="(opt, i) in getQuestionOptions(modalData.question_id)" :value="i">{{ opt }}</option>
              </select>
              <select v-else-if="getQuestionType(modalData.question_id)==='multiple'" v-model="modalData.value" multiple class="border rounded px-1 py-0.5 w-full">
                <option v-for="(opt, i) in getQuestionOptions(modalData.question_id)" :value="i">{{ opt }}</option>
              </select>
              <select v-else-if="getQuestionType(modalData.question_id)==='boolean'" v-model="modalData.value" class="border rounded px-1 py-0.5 w-full">
                <option :value="1">Sí</option>
                <option :value="0">No</option>
              </select>
              <input v-else v-model="modalData.value" class="border rounded px-1 py-0.5 w-full" />
            </div>
            <button @click="saveModal" :disabled="modalLoading" class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">Guardar</button>
            <div v-if="modalError" class="text-red-600 mt-2">{{ modalError }}</div>
          </div>
        </div>
      </div>
    </transition>
    <!-- FIN MODAL -->
    <div v-if="editMode" class="mt-8">
      <h2 class="text-xl font-bold mb-4">Editor de reglas</h2>
      <div v-for="(req, idx) in requisitosEdit" :key="idx" class="mb-8 border-b pb-6">
        <div class="flex items-center gap-2 mb-2">
          <input v-model="req.descripcion" class="border rounded px-2 py-1 w-full max-w-md" />
          <button @click="addRule(idx)" class="ml-2 bg-blue-600 text-white px-2 py-1 rounded">+ Añadir regla</button>
          <button @click="deleteRequisito(req.descripcion)" class="ml-2 text-red-600 hover:text-red-800 text-xl" title="Eliminar requisito">🗑️</button>
        </div>
        <table class="w-full text-sm mb-2">
          <thead>
            <tr class="bg-gray-100">
              <th>Pregunta</th>
              <th>Operador</th>
              <th>Valor</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(rule, rIdx) in req.rules" :key="rIdx">
              <td>
                <select v-model="rule.question_id" class="border rounded px-1 py-0.5">
                  <option v-for="q in questions" :value="q.id">{{ q.text }}</option>
                </select>
              </td>
              <td>
                <select v-model="rule.operator" class="border rounded px-1 py-0.5">
                  <option v-for="op in operadores" :value="op.value">{{ op.label }}</option>
                </select>
              </td>
              <td>
                <input v-if="getQuestionType(rule.question_id)==='text' || getQuestionType(rule.question_id)==='number'" v-model="rule.value" class="border rounded px-1 py-0.5" />
                <select v-else-if="getQuestionType(rule.question_id)==='select'" v-model="rule.value" class="border rounded px-1 py-0.5">
                  <option v-for="(opt, i) in getQuestionOptions(rule.question_id)" :value="i">{{ opt }}</option>
                </select>
                <select v-else-if="getQuestionType(rule.question_id)==='multiple'" v-model="rule.value" multiple class="border rounded px-1 py-0.5">
                  <option v-for="(opt, i) in getQuestionOptions(rule.question_id)" :value="i">{{ opt }}</option>
                </select>
                <input v-else v-model="rule.value" class="border rounded px-1 py-0.5" />
              </td>
              <td>
                <button @click="removeRule(idx, rIdx)" class="text-red-600 font-bold">Eliminar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="flex gap-2 mt-4">
        <button @click="saveEdit" class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">Guardar</button>
        <button @click="cancelEdit" class="bg-gray-400 text-white px-4 py-2 rounded shadow hover:bg-gray-500">Cancelar</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import { VueFlow } from '@vue-flow/core'
import '@vue-flow/core/dist/style.css'
import dagre from 'dagre'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'

// Props
const props = defineProps({
  ayudaId: {
    type: Number,
    required: true
  },
  versionId: {
    type: Number,
    required: false
  },
  versionType: {
    type: String,
    required: false
  },
  csrf: {
    type: String,
    required: true
  },
  isModal: {
    type: Boolean,
    default: false
  }
})

const nodes = ref([])
const edges = ref([])
const editMode = ref(false)
const requisitosEdit = ref([])
const ayudaId = ref(props.ayudaId)
const questions = ref([])
const operadores = [
  { value: '==', label: 'Igual a' },
  { value: '!=', label: 'Distinto de' },
  { value: '<', label: 'Menor que' },
  { value: '<=', label: 'Menor o igual que' },
  { value: '>', label: 'Mayor que' },
  { value: '>=', label: 'Mayor o igual que' },
  { value: 'in', label: 'Está en' },
  { value: 'not_in', label: 'No está en' },
  { value: 'less_than_years', label: 'Menor de X años' },
  { value: 'greater_than_years', label: 'Mayor o igual de X años' },
  { value: 'born_in_year', label: 'Nacido en el año' }
]

// Modal state
const modalVisible = ref(false)
const modalData = ref(null)
const modalType = ref('') // 'main', 'req', 'rule'
const modalReqIdx = ref(null)
const modalRuleIdx = ref(null)
const modalRulePath = ref([]) // For nested rules
const modalLoading = ref(false)
const modalError = ref('')

function openModal(type, data, reqIdx = null, ruleIdx = null, rulePath = []) {
  modalType.value = type
  modalData.value = JSON.parse(JSON.stringify(data))
  modalReqIdx.value = reqIdx
  modalRuleIdx.value = ruleIdx
  modalRulePath.value = rulePath
  modalVisible.value = true
  modalError.value = ''
  nextTick(() => {
    const input = document.querySelector('.modal input, .modal textarea')
    if (input) input.focus()
  })
}
function closeModal() {
  modalVisible.value = false
  modalData.value = null
  modalType.value = ''
  modalReqIdx.value = null
  modalRuleIdx.value = null
  modalRulePath.value = []
  modalLoading.value = false
  modalError.value = ''
}

function handleNodeClick({ node }) {
  if (node.id === 'ayuda') {
    // No editable
    return;
  } else if (node.data.type === 'req') {
    // Find req index
    const idx = requisitosEdit.value.findIndex(r => r.descripcion === node.data.label)
    if (idx >= 0) {
      openModal('req', requisitosEdit.value[idx], idx)
    }
  } else if (node.data.type === 'rule') {
    // Find req and rule index
    let found = false
    requisitosEdit.value.forEach((req, reqIdx) => {
      const path = findRulePath(req.rules, node.data.question_text, node.data.operator, node.data.value)
      if (path.length && !found) {
        // Para edición, aseguramos que la regla tiene question_id, operator y value
        let rule = getRuleByPath(req.rules, path)
        // Si no tiene question_id pero sí question_text, buscar el id
        if (!rule.question_id && rule.question_text) {
          const q = questions.value.find(q => q.text === rule.question_text)
          if (q) rule = { ...rule, question_id: q.id }
        }
        openModal('rule', rule, reqIdx, path[path.length-1], path)
        found = true
      }
    })
  }
}

function findRulePath(rules, question_text, operator, value, path = []) {
  for (let i = 0; i < rules.length; i++) {
    const rule = rules[i]
    if (rule.rules) {
      const sub = findRulePath(rule.rules, question_text, operator, value, [...path, i, 'rules'])
      if (sub.length) return sub
    } else if (
      rule.question_text === question_text &&
      rule.operator === operator &&
      (JSON.stringify(rule.value) === JSON.stringify(value))
    ) {
      return [...path, i]
    }
  }
  return []
}
function getRuleByPath(rules, path) {
  let current = rules
  for (let i = 0; i < path.length; i++) {
    current = current[path[i]]
  }
  return current
}
function setRuleByPath(rules, path, newRule) {
  let current = rules
  for (let i = 0; i < path.length - 1; i++) {
    current = current[path[i]]
  }
  current[path[path.length-1]] = newRule
}

async function saveModal() {
  modalLoading.value = true
  modalError.value = ''
  try {
    // Sincronizar cambios del modal en requisitosEdit antes de guardar
    if (modalType.value === 'req' && modalReqIdx.value != null) {
      requisitosEdit.value[modalReqIdx.value].descripcion = modalData.value.descripcion
      // Si se editan reglas desde el modal de requisito, también actualiza las reglas aquí si es necesario
      if (modalData.value.rules) {
        requisitosEdit.value[modalReqIdx.value].rules = modalData.value.rules
        requisitosEdit.value[modalReqIdx.value].condition = modalData.value.condition
      }
    }
    if (modalType.value === 'rule' && modalReqIdx.value != null && modalRulePath.value.length) {
      setRuleByPath(requisitosEdit.value[modalReqIdx.value].rules, modalRulePath.value, {
        ...modalData.value,
        value_text: computeValueText(modalData.value.question_id, modalData.value.value)
      })
    }
    // Transformar los requisitos para que cada uno tenga descripcion y json_regla (string)
    const requisitosParaGuardar = requisitosEdit.value.map(req => ({
      descripcion: req.descripcion,
      json_regla: JSON.stringify({
        condition: req.condition,
        rules: serializeRules(req.rules)
      })
    }))
    const res = await fetch(`/admin/ayudas/${ayudaId.value}/requisitos-json`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
      body: JSON.stringify({ requisitos: requisitosParaGuardar })
    })
    if (!res.ok) throw new Error('Error al guardar en backend')
    // Refresca el diagrama...
    const { nodes: n, edges: e } = parseRequisitos(nodes.value[0].data.label, requisitosEdit.value)
    nodes.value = n
    edges.value = e
    closeModal()
    return
  } catch (e) {
    modalError.value = e.message || 'Error desconocido'
  } finally {
    modalLoading.value = false
  }
}

function humanOperator(op) {
  return {
    '==': 'igual a',
    '!=': 'distinto de',
    'not_in': 'no está en',
    'in': 'está en',
    '>': 'mayor que',
    '<': 'menor que',
    '>=': 'mayor o igual que',
    '<=': 'menor o igual que',
    'less_than_years': 'menor de X años',
    'greater_than_years': 'mayor o igual de X años',
    'born_in_year': 'nacido en el año',
  }[op] || op || ''
}

function decodeText(text) {
  try {
    return decodeURIComponent(escape(text));
  } catch {
    return text;
  }
}

function getOptionText(question_id, value) {
  const q = questions.value.find(q => q.id == question_id)
  if (!q) {
    console.log('[getOptionText] No question found', { question_id, value })
    return value
  }

  if (q.type === 'boolean') {
    if (value === true || value === '1' || value === 1) return 'Sí'
    if (value === false || value === '0' || value === 0) return 'No'
    return value
  }

  if ((q.type === 'select' || q.type === 'multiple') && Array.isArray(q.options)) {
    const getText = v => {
      const idx = Number(v)
      let result = v
      if (!isNaN(idx) && q.options[idx] !== undefined) result = q.options[idx]
      console.log('[getOptionText] Select/Multiple', { question_id, value: v, idx, options: q.options, result })
      return result
    }
    if (Array.isArray(value)) {
      return value.map(getText).join(', ')
    }
    return getText(value)
  }

  console.log('[getOptionText] Default', { question_id, value, type: q.type })
  return value
}

function logAndGetOptionText(question_id, value) {
  console.log('[logAndGetOptionText] called', { question_id, value })
  return getOptionText(question_id, value)
}

function computeValueText(question_id, value) {
  const q = questions.value.find(q => q.id == question_id)
  if (!q) return value
  if (q.type === 'boolean') {
    if (value === true || value === '1' || value === 1) return 'Sí'
    if (value === false || value === '0' || value === 0) return 'No'
    return value
  }
  if ((q.type === 'select' || q.type === 'multiple') && Array.isArray(q.options)) {
    const getText = v => {
      const idx = Number(v)
      if (!isNaN(idx) && q.options[idx] !== undefined) return q.options[idx]
      return v
    }
    if (Array.isArray(value)) return value.map(getText).join(', ')
    return getText(value)
  }
  return value
}

function normalizeRuleValue(question, value) {
  if (!question) return value
  if (question.type === 'number' || question.type === 'select' || question.type === 'boolean') {
    if (Array.isArray(value)) {
      return value.map(v => isNaN(v) ? v : Number(v))
    }
    return isNaN(value) ? value : Number(value)
  }
  if (question.type === 'multiple') {
    return Array.isArray(value) ? value.map(v => isNaN(v) ? v : Number(v)) : [isNaN(value) ? value : Number(value)]
  }
  return value
}

let nodeIdCounter = 0;
function getNodeId(prefix = 'n') {
  nodeIdCounter++;
  return `${prefix}-${nodeIdCounter}`;
}

function getLayoutedElements(nodesArr, edgesArr, direction = 'LR') {
  const dagreGraph = new dagre.graphlib.Graph()
  dagreGraph.setDefaultEdgeLabel(() => ({}))
  dagreGraph.setGraph({ rankdir: direction, nodesep: 80, ranksep: 120 })

  nodesArr.forEach((node) => {
    dagreGraph.setNode(node.id, { width: 320, height: 120 })
  })
  edgesArr.forEach((edge) => {
    dagreGraph.setEdge(edge.source, edge.target)
  })

  dagre.layout(dagreGraph)

  return nodesArr.map((node) => {
    const n = dagreGraph.node(node.id)
    return {
      ...node,
      position: { x: n.x - 160, y: n.y - 60 },
      sourcePosition: direction === 'LR' ? 'right' : 'bottom',
      targetPosition: direction === 'LR' ? 'left' : 'top',
    }
  })
}

function addRuleNode(rule, parentId, nodesArr, edgesArr) {
  // Esta función NO debe retornar nada
  if (rule && rule.condition && Array.isArray(rule.rules)) {
    const condId = getNodeId('cond')
    nodesArr.push({
      id: condId,
      label: `${rule.condition ? rule.condition.toUpperCase() : ''} (${rule.rules.length} reglas)` || 'Condición',
      data: {
        type: 'cond',
        condition: rule.condition ? rule.condition.toUpperCase() : '',
        numRules: rule.rules.length
      }
    })
    edgesArr.push({ id: `e-${parentId}-${condId}`, source: parentId, target: condId })
    rule.rules.forEach(subRule => {
      addRuleNode(subRule, condId, nodesArr, edgesArr)
    })
  } else if (rule) {
    const ruleId = getNodeId('rule')
    let questionText = rule.question_text
    if ((!questionText || questionText === 'Regla') && rule.question_id && questions.value) {
      const q = questions.value.find(q => q.id == rule.question_id)
      if (q) questionText = q.text
    }
    let valueText = rule.value_text
    if (valueText === undefined && rule.question_id && questions.value) {
      valueText = computeValueText(rule.question_id, rule.value)
    }
    if (valueText === undefined) valueText = rule.value
    nodesArr.push({
      id: ruleId,
      label: `${questionText || 'Regla'} — ${humanOperator(rule.operator)} ${Array.isArray(valueText) ? valueText.join(', ') : valueText}`,
      data: {
        type: 'rule',
        question_id: rule.question_id,
        question_text: questionText || 'Regla',
        operator: rule.operator,
        value: rule.value,
        value_text: valueText
      }
    })
    edgesArr.push({ id: `e-${parentId}-${ruleId}`, source: parentId, target: ruleId })
  }
  // No return aquí
}

function parseRequisitos(ayuda, requisitos) {
  nodeIdCounter = 0;
  let nodesArr = []
  let edgesArr = []
  if (!Array.isArray(nodesArr)) {
    console.error('nodesArr no es un array al iniciar');
    nodesArr = [];
  }
  if (!Array.isArray(edgesArr)) {
    console.error('edgesArr no es un array al iniciar');
    edgesArr = [];
  }
  // Nodo principal
  nodesArr.push({
    id: 'ayuda',
    label: ayuda || 'Ayuda',
    data: { label: ayuda || 'Ayuda', type: 'main' }
  })

  if (!Array.isArray(requisitos)) {
    console.error('requisitos no es un array:', requisitos);
    requisitos = [];
  }
  requisitos.forEach((req, i) => {
    const reqId = getNodeId('req')
    const cond = req && req.condition ? req.condition.toUpperCase() : ''
    const rules = req && Array.isArray(req.rules) ? req.rules : []
    const hasRules = rules.length > 0
    rules.forEach(rule => {
      if (!rule.question_id && rule.question_text && questions.value) {
        const q = questions.value.find(q => q.text === rule.question_text)
        if (q) rule.question_id = q.id
      }
    })
    nodesArr.push({
      id: reqId,
      label: req && req.descripcion ? req.descripcion : 'Requisito',
      data: { label: req && req.descripcion ? req.descripcion : 'Requisito', type: 'req', condition: cond, _debug_no_rules: !hasRules }
    })
    edgesArr.push({ id: `e-ayuda-${reqId}`, source: 'ayuda', target: reqId })
    // Añadir reglas recursivamente
    rules.forEach(rule => {
      addRuleNode(rule, reqId, nodesArr, edgesArr)
    })
  })
  if (!Array.isArray(nodesArr)) {
    console.error('nodesArr no es un array después de procesar:', nodesArr);
    nodesArr = [];
  }
  if (!Array.isArray(edgesArr)) {
    console.error('edgesArr no es un array después de procesar:', edgesArr);
    edgesArr = [];
  }
  window.VUEFLOW_DEBUG_NODES = nodesArr
  window.VUEFLOW_DEBUG_EDGES = edgesArr
  // Layout automático tipo árbol
  const layouted = getLayoutedElements(nodesArr, edgesArr, 'LR') // Horizontal
  return { nodes: layouted, edges: edgesArr }
}

const newReqDescripcion = ref('')
const newReqQuestion = ref(null)
const newReqOperator = ref('==')
const newReqValue = ref('')
const addingReq = ref(false)
const addReqError = ref('')

function getNewReqQuestionType() {
  return newReqQuestion.value ? newReqQuestion.value.type : ''
}
function getNewReqQuestionOptions() {
  return newReqQuestion.value && newReqQuestion.value.options ? newReqQuestion.value.options : []
}

async function reloadRequisitosFromBackend() {
  const res = await fetch(`/admin/logicas/${ayudaId.value}`)
  const html = await res.text()
  const match = html.match(/data-requisitos='([^']+)'/)
  if (match) {
    const requisitos = JSON.parse(match[1].replace(/&quot;/g, '"'))
    requisitosEdit.value = JSON.parse(JSON.stringify(requisitos))
    const { nodes: n, edges: e } = parseRequisitos(nodes.value[0].data.label, requisitosEdit.value)
    nodes.value = n
    edges.value = e
  }
}

function serializeRules(rules) {
  return rules.map(rule => {
    if (rule.condition && Array.isArray(rule.rules)) {
      return {
        condition: rule.condition,
        rules: serializeRules(rule.rules)
      }
    } else {
      let question_id = rule.question_id
      if ((!question_id || question_id === '') && rule.question_text && questions.value) {
        const q = questions.value.find(q => q.text === rule.question_text)
        if (q) question_id = q.id
      }
      const { operator, value } = rule
      return { question_id, operator, value }
    }
  })
}

async function updateAllRequisitosBackend() {
  if (props.isModal) {
    // En modo modal, solo actualizar el estado local
    // No hacer llamadas al backend
    return
  }
  
  const requisitosParaGuardar = requisitosEdit.value.map(req => ({
    descripcion: req.descripcion,
    json_regla: JSON.stringify({
      condition: req.condition,
      rules: serializeRules(req.rules)
    })
  }))
  await fetch(`/admin/ayudas/${ayudaId.value}/requisitos-json`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
    body: JSON.stringify({ requisitos: requisitosParaGuardar })
  })
  await reloadRequisitosFromBackend()
}

async function addNewRequisito() {
  if (!newReqDescripcion.value.trim()) {
    addReqError.value = 'La descripción es obligatoria.'
    return
  }
  if (!newReqQuestion.value) {
    addReqError.value = 'Debes seleccionar una pregunta.'
    return
  }
  addingReq.value = true
  addReqError.value = ''
  try {
    const rule = {
      question_id: newReqQuestion.value.id,
      operator: newReqOperator.value,
      value: normalizeRuleValue(newReqQuestion.value, newReqValue.value)
    }
    requisitosEdit.value.push({
      descripcion: newReqDescripcion.value,
      condition: 'AND',
      rules: [rule]
    })
    
    if (props.isModal) {
      // En modo modal, solo actualizar el diagrama
      const { nodes: n, edges: e } = parseRequisitos(ayudaId.value, requisitosEdit.value)
      nodes.value = n
      edges.value = e
    } else {
      // En modo normal, actualizar backend
      await updateAllRequisitosBackend()
    }
    
    newReqDescripcion.value = ''
    newReqQuestion.value = null
    newReqOperator.value = '=='
    newReqValue.value = ''
  } catch (e) {
    addReqError.value = e.message || 'Error desconocido'
  } finally {
    addingReq.value = false
  }
}

onMounted(() => {
  const el = document.getElementById('vueflow-app')
  const ayuda = el.dataset.ayuda
  ayudaId.value = el.dataset.ayudaId
  let requisitos = []
  try {
    requisitos = JSON.parse(el.dataset.requisitos)
  } catch (e) {
    console.error('Error al parsear requisitos:', e, el.dataset.requisitos)
    requisitos = []
  }
  requisitosEdit.value = JSON.parse(JSON.stringify(requisitos))
  const { nodes: n, edges: e } = parseRequisitos(ayuda, requisitos)
  nodes.value = n
  edges.value = e

  try {
    questions.value = JSON.parse(el.dataset.questions)
  } catch {
    questions.value = []
  }

  window.addEventListener('activar-edicion-logica', () => {
    editMode.value = true
  })

  // Añadir listener para clicks en nodos
  setTimeout(() => {
    const flow = document.querySelector('.vueflow-custom')
    if (flow && flow.__vue_app__) {
      // Si se usa composition API, mejor usar el evento de VueFlow
    }
  }, 1000)
})

function cancelEdit() {
  editMode.value = false
}
async function saveEdit() {
  await updateAllRequisitosBackend()
  editMode.value = false
}

function indexOfReq(label) {
  return requisitosEdit.value.findIndex(r => r.descripcion === label)
}

function updateDescripcion(idx, value) {
  if (idx >= 0 && requisitosEdit.value[idx]) {
    requisitosEdit.value[idx].descripcion = value
  }
}

function getQuestionType(qid) {
  const q = questions.value.find(q => q.id == qid)
  return q ? q.type : 'text'
}
function getQuestionOptions(qid) {
  const q = questions.value.find(q => q.id == qid)
  return q && q.options ? q.options : []
}
function addRule(reqIdx) {
  requisitosEdit.value[reqIdx].rules.push({ question_id: '', operator: '==', value: '' })
}
function removeRule(reqIdx, ruleIdx) {
  requisitosEdit.value[reqIdx].rules.splice(ruleIdx, 1)
}

const newRule = ref({ question_id: '', operator: '==', value: '' })

function resetNewRule() {
  newRule.value = { question_id: '', operator: '==', value: '' }
}

async function deleteRequisitoModal() {
  const idx = modalReqIdx.value
  if (idx == null) return
  
  if (props.isModal) {
    // En modo modal, solo eliminar del estado local
    requisitosEdit.value.splice(idx, 1)
    // Actualizar el diagrama
    const { nodes: n, edges: e } = parseRequisitos(ayudaId.value, requisitosEdit.value)
    nodes.value = n
    edges.value = e
  } else {
    // En modo normal, eliminar y actualizar backend
    requisitosEdit.value.splice(idx, 1)
    await updateAllRequisitosBackend()
  }
  
  closeModal()
}

async function addFirstRuleToReq() {
  if (!newRule.value.question_id || !newRule.value.operator) return
  const idx = modalReqIdx.value
  if (idx == null) return
  if (!requisitosEdit.value[idx].rules) requisitosEdit.value[idx].rules = []
  const qObj = questions.value.find(q => q.id == newRule.value.question_id)
  requisitosEdit.value[idx].rules.push({
    question_id: newRule.value.question_id,
    operator: newRule.value.operator,
    value: normalizeRuleValue(qObj, newRule.value.value)
  })
  resetNewRule()
  
  if (props.isModal) {
    // En modo modal, solo actualizar el diagrama
    const { nodes: n, edges: e } = parseRequisitos(ayudaId.value, requisitosEdit.value)
    nodes.value = n
    edges.value = e
  } else {
    // En modo normal, actualizar backend
    await updateAllRequisitosBackend()
  }
}
</script>

<style scoped>
.vueflow-custom {
  background: linear-gradient(90deg, #f8fafc 0%, #e0e7ef 100%);
  border-radius: 2rem;
  box-shadow: 0 8px 32px 0 rgba(0,0,0,0.10);
  transition: box-shadow 0.3s;
}
.vueflow-custom:focus-within {
  box-shadow: 0 16px 48px 0 rgba(0,0,0,0.16);
}
.sticky {
  position: sticky;
  top: 0;
  background: linear-gradient(180deg, #fff 95%, #f8fafc 100%);
  z-index: 30;
}
.node-glass {
  background: rgba(255,255,255,0.85);
  border-radius: 2rem;
  box-shadow: 0 6px 32px 0 rgba(0,0,0,0.10);
  border: 2.5px solid #e0e7ef;
  backdrop-filter: blur(8px);
}
.glass-main {
  background: linear-gradient(120deg, #dbeafe 60%, #f0f9ff 100%);
  border-color: #60a5fa;
}
.glass-req {
  background: linear-gradient(120deg, #bbf7d0 60%, #f0fdf4 100%);
  border-color: #34d399;
}
.glass-cond {
  background: linear-gradient(120deg, #e9d5ff 60%, #f3e8ff 100%);
  border-color: #a78bfa;
}
.glass-rule {
  background: linear-gradient(120deg, #fef9c3 60%, #fefce8 100%);
  border-color: #fde047;
}
.min-w-\[260px\] { min-width: 260px; }
.max-w-\[420px\] { max-width: 420px; }
ul { margin: 0; padding-left: 1.2em; }
li { margin-bottom: 0.2em; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.animate-fadein { animation: fadein 0.2s; }
@keyframes fadein { from { opacity: 0; transform: scale(0.95);} to { opacity: 1; transform: scale(1);} }
.modal { z-index: 1000; }
</style> 