<template>
  <div class="w-full min-h-[600px] relative">
    <div class="flex justify-between mb-4">
      <h2 class="text-xl font-semibold flex items-center gap-2">
        <i class="fas fa-sitemap text-indigo-500"></i>
        Árbol de decisiones del cuestionario
      </h2>
    </div>
    <div class="w-full h-[800px] bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border overflow-auto">
      <VueFlow
        v-model:nodes="nodes"
        v-model:edges="edges"
        :fit-view="true"
        :fit-view-options="{ padding: 0.2, includeHiddenNodes: false, minZoom: 0.3 }"
        :min-zoom="0.2"
        :max-zoom="2.0"
        class="w-full h-full"
        :nodes-draggable="true"
        :nodes-connectable="false"
        :elements-selectable="true"
        :zoom-on-scroll="true"
        :zoom-on-double-click="true"
        :pan-on-drag="true"
      >
        <template #node-default="{ data, id }">
          <div :class="[
            'rounded-xl shadow-lg p-4 min-w-[280px] text-center border-2 transition cursor-pointer group relative',
            id === 'FIN' 
              ? 'bg-red-50 border-red-200 text-red-800' 
              : 'bg-white border-indigo-100 hover:border-indigo-400'
          ]">
            <div v-if="id !== 'FIN'" class="absolute -top-3 -left-3 w-8 h-8 bg-indigo-500 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg">
              {{ data.order }}
            </div>
            
            <div class="font-bold text-base mb-2">{{ data.text }}</div>
            <div v-if="id !== 'FIN'" class="text-xs text-gray-500 mb-2">Tipo: {{ data.type }}</div>
            <div v-if="data.options && data.options.length && id !== 'FIN'" class="text-xs text-gray-400 mb-3">Opciones: {{ data.options.join(', ') }}</div>
            <button v-if="id !== 'FIN'" @click.stop="openConditionModal(id)" class="mt-3 bg-blue-100 text-blue-700 px-3 py-2 rounded text-sm hover:bg-blue-200 font-medium">+ Añadir salto</button>
            <Handle v-if="id !== 'FIN'" type="source" position="bottom" :id="`source-${id}`" />
            <Handle v-if="id !== 'FIN'" type="target" position="top" :id="`target-${id}`" />
          </div>
        </template>
        <template #edge-default="{ id, sourceX, sourceY, targetX, targetY, sourcePosition, targetPosition, style }">
          <g>
            <path
              :d="`M ${sourceX} ${sourceY} L ${targetX} ${targetY}`"
              :stroke="style.stroke"
              :stroke-width="style.strokeWidth"
              fill="none"
              marker-end="url(#arrowhead)"
              class="transition-all duration-200"
            />
            <circle
              :cx="(sourceX + targetX) / 2"
              :cy="(sourceY + targetY) / 2"
              r="16"
              fill="white"
              stroke="#ef4444"
              stroke-width="2"
              class="cursor-pointer hover:fill-red-50 transition-colors shadow-sm"
              @click="confirmDeleteCondition(id)"
            />
            <text
              :x="(sourceX + targetX) / 2"
              :y="(sourceY + targetY) / 2"
              text-anchor="middle"
              dominant-baseline="middle"
              class="text-sm font-bold fill-red-600"
              style="pointer-events: none;"
            >
              ×
            </text>
          </g>
        </template>
      </VueFlow>
      <defs>
        <marker
          id="arrowhead"
          markerWidth="12"
          markerHeight="8"
          refX="10"
          refY="4"
          orient="auto"
        >
          <polygon
            points="0 0, 12 4, 0 8"
            fill="#3b82f6"
            stroke="#1e40af"
            stroke-width="1"
          />
        </marker>
        <marker
          id="arrowclosed-red"
          markerWidth="12"
          markerHeight="8"
          refX="10"
          refY="4"
          orient="auto"
        >
          <polygon
            points="0 0, 12 4, 0 8"
            fill="#ef4444"
            stroke="#dc2626"
            stroke-width="1"
          />
        </marker>
      </defs>
    </div>
    <div v-if="!nodes.length" class="text-gray-400 text-center py-12">No hay preguntas aún. Añade la primera pregunta.</div>

    <!-- Modales de pregunta -->
    <transition name="fade">
      <div v-if="showQuestionModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md relative animate-fade-in">
          <button @click="closeModals" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
          <h3 class="text-lg font-bold mb-4">{{ editingQuestion.id ? 'Editar' : 'Añadir' }} pregunta</h3>
          <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Texto</label>
            <input v-model="editingQuestion.text" class="w-full border rounded px-3 py-2" />
          </div>
          <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Tipo</label>
            <select v-model="editingQuestion.type" class="w-full border rounded px-3 py-2">
              <option value="text">Texto</option>
              <option value="number">Número</option>
              <option value="boolean">Sí/No</option>
              <option value="select">Selección</option>
              <option value="multiple">Selección múltiple</option>
              <option value="date">Fecha</option>
            </select>
          </div>
          <div v-if="['select','multiple'].includes(editingQuestion.type)" class="mb-3">
            <label class="block text-sm font-medium mb-1">Opciones (separadas por coma)</label>
            <input v-model="editingQuestion.optionsString" class="w-full border rounded px-3 py-2" placeholder="Ej: Opción 1, Opción 2" />
          </div>
          <div class="flex justify-end gap-2 mt-4">
            <button @click="saveQuestion" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Guardar</button>
            <button @click="closeModals" class="bg-gray-200 px-4 py-2 rounded">Cancelar</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Modales de condición -->
    <transition name="fade">
      <div v-if="showConditionModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50" @click.self="closeModals">
        <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-2xl relative animate-fade-in">
          <button @click="closeModals" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
          <h3 class="text-lg font-bold mb-4">{{ editingCondition.id ? 'Editar' : 'Añadir' }} saltos</h3>
          <div class="mb-4 p-3 bg-blue-50 rounded-lg">
            <div class="text-sm font-medium text-blue-800">Pregunta origen:</div>
            <div class="text-sm text-blue-600">{{ getSourceQuestionText() }}</div>
            <div class="text-xs text-blue-500 mt-1">Tipo: {{ getSourceQuestionType() }}</div>
          </div>
          <div class="mb-4">
            <div class="flex justify-between items-center mb-3">
              <h4 class="font-medium text-gray-700">Saltos configurados:</h4>
              <button @click="addNewCondition" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                <i class="fas fa-plus mr-1"></i>Añadir salto
              </button>
            </div>
            
            <div v-if="conditionList.length === 0" class="text-gray-500 text-center py-4 border-2 border-dashed border-gray-300 rounded">
              No hay saltos configurados. Añade el primero.
            </div>
            
            <div v-else class="space-y-3">
              <div v-for="(condition, index) in conditionList" :key="index" class="border rounded-lg p-4 bg-gray-50">
                <div class="flex justify-between items-start mb-3">
                  <h5 class="font-medium text-gray-700">Salto {{ index + 1 }}</h5>
                  <button @click="removeCondition(index)" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                  <div>
                    <label class="block text-sm font-medium mb-1">Operador</label>
                    <select v-model="condition.operator" class="w-full border rounded px-3 py-2 text-sm">
                      <option v-for="op in getAvailableOperators()" :value="op.value" :key="op.value">{{ op.label }}</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1">Valor</label>
                    <select v-if="getSourceQuestionType() === 'boolean'" v-model="condition.value" class="w-full border rounded px-3 py-2 text-sm">
                      <option value="1">Sí</option>
                      <option value="0">No</option>
                    </select>
                    <select v-else-if="getSourceQuestionType() === 'select'" v-model="condition.value" class="w-full border rounded px-3 py-2 text-sm">
                      <option value="">Selecciona una opción</option>
                      <option v-for="(option, index) in getSourceQuestionOptions()" :value="index" :key="option">{{ option }}</option>
                    </select>
                    <div v-else-if="getSourceQuestionType() === 'multiple'" class="space-y-2">
                      <div class="text-xs text-gray-600 mb-1">Selecciona las opciones:</div>
                      <div v-for="(option, index) in getSourceQuestionOptions()" :key="option" class="flex items-center">
                        <input 
                          type="checkbox" 
                          :id="`${index}-${option}`" 
                          :value="index" 
                          v-model="condition.multipleValues"
                          class="mr-2"
                        />
                        <label :for="`${index}-${option}`" class="text-sm">{{ option }}</label>
                      </div>
                      <input 
                        v-model="condition.value" 
                        class="w-full border rounded px-3 py-2 text-sm mt-2" 
                        placeholder="O escribe valores manualmente"
                        readonly
                      />
                    </div>

                    <input v-else-if="getSourceQuestionType() === 'number'" v-model="condition.value" type="number" class="w-full border rounded px-3 py-2 text-sm" />

                    <input v-else-if="getSourceQuestionType() === 'date'" v-model="condition.value" type="date" class="w-full border rounded px-3 py-2 text-sm" />

                    <input v-else v-model="condition.value" class="w-full border rounded px-3 py-2 text-sm" placeholder="Introduce el valor" />
                  </div>

                  <div>
                    <label class="block text-sm font-medium mb-1">Pregunta destino</label>
                    <select v-model="condition.next_question_id" class="w-full border rounded px-3 py-2 text-sm">
                      <option value="">Selecciona destino</option>
                      <option v-for="q in getAvailableDestinations()" :value="q.id" :key="q.id">{{ q.text }}</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-2 mt-4">
            <button @click="saveAllConditions" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Guardar</button>
            <button @click="closeModals" class="bg-gray-200 px-4 py-2 rounded">Cancelar</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Confirmación de borrado -->
    <transition name="fade">
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-sm relative animate-fade-in">
          <button @click="closeModals" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
          <h3 class="text-lg font-bold mb-4">¿Seguro que quieres eliminar?</h3>
          <p class="mb-4 text-gray-600">
            {{ deleteTarget.type === 'question' ? 'Esta pregunta se eliminará permanentemente.' : 'Este salto se eliminará permanentemente.' }}
          </p>
          <div class="flex justify-end gap-2">
            <button @click="deleteConfirmed" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Eliminar</button>
            <button @click="closeModals" class="bg-gray-200 px-4 py-2 rounded">Cancelar</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Toasts -->
    <transition name="fade">
      <div v-if="toast.show" class="fixed bottom-6 right-6 z-50">
        <div :class="['px-4 py-3 rounded shadow-lg text-white', toast.type === 'success' ? 'bg-green-500' : 'bg-red-500']">
          {{ toast.message }}
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { VueFlow, Handle } from '@vue-flow/core';
import '@vue-flow/core/dist/style.css';
import '@vue-flow/core/dist/theme-default.css';
import dagre from 'dagre';

const props = defineProps({
  questionnaireId: [String, Number],
  ayudaId: [String, Number],
  csrf: String,
});

const nodes = ref([]);
const edges = ref([]);
const questions = ref([]);
const conditions = ref([]);
const initialLayoutApplied = ref(false);

const showQuestionModal = ref(false);
const showConditionModal = ref(false);
const showDeleteModal = ref(false);
const editingQuestion = reactive({});
const editingCondition = reactive({});
const conditionList = ref([]);
const deleteTarget = reactive({ type: '', id: null });
const toast = reactive({ show: false, message: '', type: 'success' });
watch(() => editingCondition.multipleValues, (newValues) => {
  if (getSourceQuestionType() === 'multiple') {
    editingCondition.value = newValues.join(', ');
  }
}, { deep: true });

watch(conditionList, (newList) => {
  newList.forEach((condition, index) => {
    if (getSourceQuestionType() === 'multiple' && condition.multipleValues) {
      condition.value = condition.multipleValues.join(', ');
    }
  });
}, { deep: true });

function showToast(message, type = 'success') {
  toast.message = message;
  toast.type = type;
  toast.show = true;
  setTimeout(() => (toast.show = false), 2500);
}

function fetchData() {
  fetch(`/admin/questionnaires/${props.questionnaireId}/questions`)
    .then(r => r.json())
    .then(data => {
      questions.value = data.questions || data;
      fetchConditions();
    });
}
function fetchConditions() {
  fetch(`/admin/questionnaires/${props.questionnaireId}/conditions`)
    .then(r => r.json())
    .then(data => {
      conditions.value = data.conditions || data;
      if (!initialLayoutApplied.value) {
        buildTreeNodes();
        buildTreeEdges();
        initialLayoutApplied.value = true;
      } else {
        buildTreeEdges();
      }
    });
}

function applyDagreLayout(nodesArr, edgesArr, direction = 'TB') {
  const g = new dagre.graphlib.Graph();
  g.setDefaultEdgeLabel(() => ({}));
  g.setGraph({ 
    rankdir: direction,
    nodesep: 120,
    edgesep: 60,
    ranksep: 200
  });

  nodesArr.forEach(node => {
    g.setNode(node.id, { width: 280, height: 120 });
  });
  edgesArr.forEach(edge => {
    g.setEdge(edge.source, edge.target);
  });

  dagre.layout(g);

  return nodesArr.map(node => {
    const pos = g.node(node.id);
    return {
      ...node,
      position: { x: pos.x, y: pos.y }
    };
  });
}

function buildTreeNodes() {
  const sortedQuestions = [...questions.value].sort((a, b) => {
    const orderA = a.order || a.questionnaire_questions?.orden || 0;
    const orderB = b.order || b.questionnaire_questions?.orden || 0;
    return orderA - orderB;
  });

  const baseNodes = sortedQuestions.map((q, index) => ({
    id: String(q.id),
    type: 'default',
    position: { x: 0, y: 0 },
    data: {
      text: q.text,
      type: q.type,
      options: q.options || [],
      order: (q.order || q.questionnaire_questions?.orden || index) + 1,
    },
  }));

  const finConditions = conditions.value.filter(c => c.next_question_id === null);
  const hasFinConditions = finConditions.length > 0;  
  if (hasFinConditions) {
    const finNode = {
      id: 'FIN',
      type: 'default',
      position: { x: 0, y: 0 },
      data: {
        text: '🏁 FIN DEL CUESTIONARIO',
        type: 'fin',
        options: [],
        order: 9999,
      },
    };
    baseNodes.push(finNode);
  }
  
  nodes.value = applyDagreLayout(baseNodes, edges.value, 'TB');
}
function buildTreeEdges() {
  const questionIds = nodes.value.map(n => n.id);
  edges.value = conditions.value
    .filter(c => {
      const hasSource = questionIds.includes(String(c.question_id));
      const hasTarget = c.next_question_id ? questionIds.includes(String(c.next_question_id)) : true;
      const notSelf = String(c.question_id) !== String(c.next_question_id);
      return hasSource && (hasTarget || c.next_question_id === null) && notSelf;
    })
    .map((c, idx) => {
      let displayValue = c.value;
      if (Array.isArray(c.value)) {
        displayValue = c.value.join(', ');
      } else if (typeof c.value === 'object' && c.value !== null) {
        displayValue = JSON.stringify(c.value);
      }
      
      let target = String(c.next_question_id);
      let label = `${c.operator} ${displayValue}`;
      let style = { stroke: '#3b82f6', strokeWidth: 3 };
      
      if (c.next_question_id === null) {
        target = 'FIN';
        label = `${c.operator} ${displayValue} → FIN`;
        style = { stroke: '#ef4444', strokeWidth: 4, strokeDasharray: '8,4' };
      }
      
      return {
        id: String(c.id),
        source: String(c.question_id),
        target: target,
        type: 'default',
        label: label,
        animated: true,
        style: style,
        markerEnd: c.next_question_id === null ? 'arrowclosed-red' : 'arrowhead',
      };
    });
}

function openQuestionModal(q = null, id = null) {
  if (q) {
    Object.assign(editingQuestion, { ...q, optionsString: (q.options || []).join(', ') });
  } else {
    Object.assign(editingQuestion, { id: null, text: '', type: 'text', optionsString: '' });
  }
  showQuestionModal.value = true;
}
function saveQuestion() {
  const options = editingQuestion.optionsString.split(',').map(o => o.trim()).filter(Boolean);
  const payload = {
    text: editingQuestion.text,
    type: editingQuestion.type,
    options,
  };
  if (editingQuestion.id) {
    fetch(`/admin/questions/${editingQuestion.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': props.csrf },
      body: JSON.stringify(payload),
    })
      .then(r => r.json())
      .then(() => {
        showToast('Pregunta actualizada');
        fetchData();
        closeModals();
      });
  } else {
    fetch(`/admin/questionnaires/${props.questionnaireId}/questions`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': props.csrf },
      body: JSON.stringify(payload),
    })
      .then(r => r.json())
      .then(() => {
        showToast('Pregunta creada');
        fetchData();
        closeModals();
      });
  }
}
function confirmDeleteQuestion(id) {
  deleteTarget.type = 'question';
  deleteTarget.id = id;
  showDeleteModal.value = true;
}
function deleteConfirmed() {
  if (deleteTarget.type === 'question') {
    fetch(`/admin/questions/${deleteTarget.id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': props.csrf },
    })
      .then(r => r.json())
      .then(() => {
        showToast('Pregunta eliminada');
        fetchData();
        closeModals();
      });
  } else if (deleteTarget.type === 'condition') {
    fetch(`/admin/conditions/${deleteTarget.id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': props.csrf },
    })
      .then(r => r.json())
      .then(() => {
        showToast('Salto eliminado');
        fetchData();
        closeModals();
      });
  }
}

function openConditionModal(questionId) {
  Object.assign(editingCondition, { id: null, question_id: questionId, operator: '=', value: '', multipleValues: [], next_question_id: '', order: null });
  const existingConditions = conditions.value.filter(c => String(c.question_id) === String(questionId));
  conditionList.value = existingConditions.map(cond => ({
    id: cond.id,
    question_id: cond.question_id,
    operator: cond.operator,
    value: cond.value,
    multipleValues: Array.isArray(cond.value) ? cond.value : (typeof cond.value === 'string' && cond.value.includes(',')) ? cond.value.split(',').map(v => v.trim()) : [],
    next_question_id: cond.next_question_id,
    order: cond.order,
  }));
  
  showConditionModal.value = true;
}

function editConditionModal(conditionId) {
  const cond = conditions.value.find(c => String(c.id) === String(conditionId));
  if (cond) {
    let multipleValues = [];
    if (getSourceQuestionType() === 'multiple' && cond.value) {
      if (Array.isArray(cond.value)) {
        multipleValues = cond.value;
      } else if (typeof cond.value === 'string') {
        multipleValues = cond.value.split(',').map(v => v.trim());
      }
    }
    
    Object.assign(editingCondition, { 
      ...cond, 
      multipleValues: multipleValues 
    });
    showConditionModal.value = true;
  }
}

function saveAllConditions() {
  const invalidConditions = conditionList.value.map((cond, idx) => {
    if (cond.next_question_id === '' || cond.next_question_id === undefined || cond.next_question_id === null) {
      return { index: idx + 1, message: 'Debes seleccionar una pregunta destino' };
    }
    if (
      (getSourceQuestionType() === 'boolean' || getSourceQuestionType() === 'select') &&
      (cond.value === '' || cond.value === undefined || cond.value === null || isNaN(cond.value))
    ) {
      return { index: idx + 1, message: 'Debes seleccionar un valor' };
    }
    if (getSourceQuestionType() === 'multiple' && (!cond.multipleValues || cond.multipleValues.length === 0)) {
      return { index: idx + 1, message: 'Debes seleccionar al menos una opción' };
    }
    return null;
  }).filter(Boolean);

  if (invalidConditions.length > 0) {
    const firstError = invalidConditions[0];
    showToast(`Salto ${firstError.index}: ${firstError.message}`, 'error');
    return;
  }

  const processedConditions = conditionList.value.map(cond => {
    let finalValue = cond.value;
    if (getSourceQuestionType() === 'multiple' && cond.multipleValues && cond.multipleValues.length > 0) {
      finalValue = cond.multipleValues.map(v => parseInt(v, 10));
    } else if (getSourceQuestionType() === 'select' || getSourceQuestionType() === 'boolean') {
      finalValue = parseInt(cond.value, 10);
    }
    return {
      ...cond,
      value: finalValue
    };
  });

  const payload = {
    question_id: editingCondition.question_id,
    conditions: processedConditions
  };
  fetch(`/admin/questionnaires/${props.questionnaireId}/conditions/batch`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': props.csrf },
    body: JSON.stringify(payload),
  })
    .then(r => {
      if (!r.ok) {
        return r.text().then(text => {
          throw new Error(`HTTP ${r.status}: ${text}`);
        });
      }
      return r.json();
    })
    .then(() => {
      showToast('Saltos actualizados');
      fetchData();
      closeModals();
    })
    .catch(error => {
      console.error('Error updating conditions:', error); // Debug
      showToast('Error al actualizar: ' + error.message, 'error');
    });
}

function addNewCondition() {
  conditionList.value.push({
    id: null,
    question_id: editingCondition.question_id,
    operator: '=',
    value: '',
    multipleValues: [],
    next_question_id: '',
    order: null,
  });
}

function removeCondition(index) {
  conditionList.value.splice(index, 1);
}

function confirmDeleteCondition(conditionId) {
  deleteTarget.type = 'condition';
  deleteTarget.id = conditionId;
  showDeleteModal.value = true;
}

function closeModals() {
  showQuestionModal.value = false;
  showConditionModal.value = false;
  showDeleteModal.value = false;
  Object.assign(editingQuestion, {});
  Object.assign(editingCondition, {});
  deleteTarget.type = '';
  deleteTarget.id = null;
}

function getSourceQuestionText() {
  const sourceNode = nodes.value.find(n => n.id === String(editingCondition.question_id));
  return sourceNode ? sourceNode.data.text : 'Desconocida';
}

function getSourceQuestionType() {
  const sourceNode = nodes.value.find(n => n.id === String(editingCondition.question_id));
  return sourceNode ? sourceNode.data.type : 'text';
}

function getSourceQuestionOptions() {
  const sourceNode = nodes.value.find(n => n.id === String(editingCondition.question_id));
  return sourceNode ? sourceNode.data.options : [];
}

function getAvailableDestinations() {
  const destinations = questions.value.filter(q => String(q.id) !== String(editingCondition.question_id));
  destinations.unshift({
    id: null,
    text: '🏁 FIN DEL CUESTIONARIO'
  });
  
  return destinations;
}

function getAvailableOperators() {
  const sourceType = getSourceQuestionType();
  const operators = [];

  if (sourceType === 'boolean') {
    operators.push({ value: '=', label: '=' });
    operators.push({ value: '!=', label: '!=' });
  } else if (sourceType === 'select') {
    operators.push({ value: '=', label: '=' });
    operators.push({ value: '!=', label: '!=' });
  } else if (sourceType === 'multiple') {
    operators.push({ value: '=', label: '=' });
    operators.push({ value: '!=', label: '!=' });
    operators.push({ value: 'in', label: 'Incluye' });
    operators.push({ value: 'contains', label: 'Contiene' });
  } else if (sourceType === 'number') {
    operators.push({ value: '=', label: '=' });
    operators.push({ value: '!=', label: '!=' });
    operators.push({ value: '>', label: '>' });
    operators.push({ value: '>=', label: '>=' });
    operators.push({ value: '<', label: '<' });
    operators.push({ value: '<=', label: '<=' });
  } else if (sourceType === 'date') {
    operators.push({ value: '=', label: '=' });
    operators.push({ value: '!=', label: '!=' });
    operators.push({ value: '>', label: '>' });
    operators.push({ value: '>=', label: '>=' });
    operators.push({ value: '<', label: '<' });
    operators.push({ value: '<=', label: '<=' });
  } else {
    operators.push({ value: '=', label: '=' });
    operators.push({ value: '!=', label: '!=' });
    operators.push({ value: 'contains', label: 'Contiene' });
  }
  return operators;
}
onMounted(fetchData);
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.animate-fade-in { animation: fadeIn 0.3s; }
@keyframes fadeIn { from { opacity: 0; transform: scale(0.97); } to { opacity: 1; transform: scale(1); } }
</style> 