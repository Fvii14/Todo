<template>
  <div class="flex flex-col items-center w-full">
    <div class="bg-white rounded-3xl shadow-2xl border border-gray-200 p-6 w-full max-w-[1800px] mx-auto overflow-x-auto backdrop-blur-md" style="backdrop-filter: blur(8px);">
      <div style="width: 100%; min-width: 900px; height: 80vh;">
        <VueFlow ref="vueFlowInstance" :nodes="nodes" :edges="edges" fit-view-on-init :zoom-on-scroll="false" :zoom-on-double-click="false" :default-zoom="1"
          :min-zoom="0.2" :max-zoom="2" :pan-on-drag="true" :pan-on-scroll="true" :snap-to-grid="true" :snap-grid="[20,20]"
          class="vueflow-custom">
          <background gap="32" size="1" color="#dbeafe" />
          <template #node-label="{ data }">
            <div :class="data.type === 'answer' ? 'node-answer' : data.type === 'answer-final' ? 'node-answer-final' : data.type === 'fin' ? 'node-fin' : (data.isFinal ? 'node-final' : 'node-glass')" class="flex flex-col items-center px-7 py-4 border-2 rounded-2xl shadow-xl min-w-[180px] max-w-[480px] transition-all duration-300 hover:scale-105 hover:shadow-2xl">
              <b v-if="data.type === 'question'" class="text-lg font-bold text-blue-900">{{ data.text }}</b>
              <span v-if="data.type === 'question' && data.subtext" class="text-xs text-gray-500 mt-1">{{ data.subtext }}</span>
              <span v-if="data.type === 'question'" class="text-xs text-blue-700 mt-1">({{ data.type }})</span>
              <span v-if="data.type === 'answer' || data.type === 'answer-final'" class="text-base font-semibold text-green-800">{{ data.answer }}</span>
              <span v-if="data.isFinal" class="text-xs font-bold text-red-700 mt-2">[FINAL]</span>
              <span v-if="data.type === 'fin'" class="text-2xl font-bold text-red-700">⛔ FIN</span>
            </div>
          </template>
          <template #edge-label="{ data }">
            <span v-if="data.label" class="text-xs bg-blue-100 px-2 py-1 rounded shadow border border-blue-200">{{ data.label }}</span>
          </template>
        </VueFlow>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { VueFlow, useVueFlow } from '@vue-flow/core'
import '@vue-flow/core/dist/style.css'
import dagre from 'dagre'

const nodes = ref([])
const edges = ref([])
const vueFlowInstance = ref(null)

function getLayoutedElements(nodesArr, edgesArr, direction = 'TB') {
  const dagreGraph = new dagre.graphlib.Graph()
  dagreGraph.setDefaultEdgeLabel(() => ({}))
  dagreGraph.setGraph({ rankdir: direction, nodesep: 300, ranksep: 300 }) // Mucha separación
  nodesArr.forEach((node) => {
    // Nodos de respuesta más altos
    const isAnswer = node.data && node.data.type === 'answer';
    dagreGraph.setNode(node.id, { width: 320, height: isAnswer ? 180 : 120 })
  })
  edgesArr.forEach((edge) => {
    dagreGraph.setEdge(edge.source, edge.target)
  })
  dagre.layout(dagreGraph)
  return nodesArr.map((node) => {
    const n = dagreGraph.node(node.id)
    return {
      ...node,
      position: { x: n.x - 160, y: n.y - (node.data && node.data.type === 'answer' ? 90 : 60) },
      sourcePosition: direction === 'TB' ? 'bottom' : 'right',
      targetPosition: direction === 'TB' ? 'top' : 'left',
    }
  })
}

function getConditionText(condition, question, operador) {
  let cond = condition;
  try {
    cond = JSON.parse(condition);
  } catch {}
  if (!Array.isArray(cond)) cond = [cond];
  let text = '';
  if (question && Array.isArray(question.options)) {
    text = cond.map(v => {
      const idx = Number(v);
      if (!isNaN(idx) && question.options[idx] !== undefined) return question.options[idx];
      return v;
    }).join(', ');
  } else if (question && question.type === 'boolean') {
    text = cond.map(v => (v === true || v === '1' || v === 1) ? 'Sí' : (v === false || v === '0' || v === 0) ? 'No' : v).join(', ');
  } else {
    text = cond.join(', ');
  }
  // Mostrar el operador
  if (operador && operador !== '==') {
    return `${operador} ${text}`;
  }
  return text;
}

function buildGraph(questions, conditions) {
  console.log('[VUEFLOW CONDICIONES] TODAS LAS PREGUNTAS:', questions);
  // La raíz es el question_id de la condición con el id más bajo
  let rootId = null;
  if (conditions.length > 0) {
    let minCond = conditions[0];
    for (let i = 1; i < conditions.length; i++) {
      if (conditions[i].id && minCond.id && conditions[i].id < minCond.id) {
        minCond = conditions[i];
      }
    }
    rootId = minCond.question_id;
  }
  const qMap = {};
  questions.forEach(q => { qMap[q.id] = q; });
  const nodesArr = [];
  const edgesArr = [];
  const nodeIds = new Set();
  const answerNodeIds = new Set();
  const finalNodeIds = new Set();
  const finNodeId = 'fin';
  let finNodeAdded = false;
  // Agrupar condiciones por (question_id, condition, operador)
  const groupKey = cond => `${cond.question_id}|${cond.condition}|${cond.operador}`;
  const grouped = {};
  conditions.forEach(cond => {
    const key = groupKey(cond);
    if (!grouped[key]) grouped[key] = [];
    grouped[key].push(cond);
  });
  // Recorrido desde la raíz, solo caminos alcanzables, cadenas secuenciales
  function visitChain(qid, visited = new Set()) {
    if (!qid || visited.has(qid)) return;
    visited.add(qid);
    // Buscar todos los grupos que empiezan en esta pregunta
    Object.values(grouped).forEach(group => {
      if (group.length === 0) return;
      const first = group[0];
      if (first.question_id !== qid) return;
      group.sort((a, b) => (a.id || 0) - (b.id || 0));
      let prevQ = null;
      let prevA = null;
      group.forEach((cond, idx) => {
        const q = qMap[cond.question_id];
        const answerText = getConditionText(cond.condition, q, cond.operador);
        const answerNodeId = `a-${cond.question_id}-${cond.operador}-${answerText}`;
        // Nodo pregunta único
        if (!nodeIds.has(cond.question_id)) {
          nodeIds.add(cond.question_id);
          nodesArr.push({ id: String(cond.question_id), label: q.text, data: { ...q, type: 'question' } });
        }
        // Nodo respuesta/condición único por (question_id, operador, condición)
        if (!answerNodeIds.has(answerNodeId)) {
          answerNodeIds.add(answerNodeId);
          nodesArr.push({ id: answerNodeId, label: answerText, data: { type: 'answer', question_id: cond.question_id, answer: answerText, operador: cond.operador } });
        }
        // Nodo pregunta destino único
        const destQ = qMap[cond.next_question_id];
        if (destQ && !nodeIds.has(cond.next_question_id)) {
          nodeIds.add(cond.next_question_id);
          nodesArr.push({ id: String(cond.next_question_id), label: destQ.text, data: { ...destQ, type: 'question' } });
        }
        // Edges secuenciales
        if (idx === 0) {
          edgesArr.push({
            id: `e-${cond.question_id}-${answerNodeId}`,
            source: String(cond.question_id),
            target: answerNodeId,
            data: { label: '' },
            type: 'straight',
          });
        } else if (prevA) {
          edgesArr.push({
            id: `e-${prevA}-${answerNodeId}`,
            source: prevA,
            target: answerNodeId,
            data: { label: '' },
            type: 'straight',
          });
        }
        edgesArr.push({
          id: `e-${answerNodeId}-${cond.next_question_id}`,
          source: answerNodeId,
          target: String(cond.next_question_id),
          data: { label: '' },
          type: 'straight',
        });
        prevQ = cond.next_question_id;
        prevA = answerNodeId;
      });
      // Continuar desde el último destino de la cadena
      if (prevQ) visitChain(prevQ, new Set(visited));
    });
  }
  visitChain(rootId);
  // Opciones de fin para preguntas con opciones no cubiertas
  questions.forEach(q => {
    if (!Array.isArray(q.options)) return;
    const salida = conditions.filter(c => c.question_id == q.id);
    let coveredIndexes = new Set();
    salida.forEach(cond => {
      let condVals = cond.condition;
      try { condVals = JSON.parse(cond.condition); } catch {}
      if (!Array.isArray(condVals)) condVals = [condVals];
      condVals.forEach(v => {
        const idx = Number(v);
        if (!isNaN(idx)) coveredIndexes.add(idx);
      });
    });
    q.options.forEach((opt, idx) => {
      if (!coveredIndexes.has(idx)) {
        const answerNodeId = `a-${q.id}-final-${idx}`;
        if (!answerNodeIds.has(answerNodeId)) {
          answerNodeIds.add(answerNodeId);
          nodesArr.push({ id: answerNodeId, label: opt, data: { type: 'answer-final', question_id: q.id, answer: opt } });
        }
        if (!finNodeAdded) {
          nodesArr.push({ id: finNodeId, label: 'FIN', data: { type: 'fin' } });
          finNodeAdded = true;
        }
        edgesArr.push({
          id: `e-${q.id}-${answerNodeId}`,
          source: String(q.id),
          target: answerNodeId,
          data: { label: '' },
          type: 'straight',
        });
        edgesArr.push({
          id: `e-${answerNodeId}-${finNodeId}`,
          source: answerNodeId,
          target: finNodeId,
          data: { label: '' },
          type: 'straight',
        });
      }
    });
  });
  nodesArr.forEach(n => {
    if (finalNodeIds.has(n.id)) {
      n.data.isFinal = true;
    }
  });
  console.log('[VUEFLOW CONDICIONES] NODES:', nodesArr);
  console.log('[VUEFLOW CONDICIONES] EDGES:', edgesArr);
  return { nodes: getLayoutedElements(nodesArr, edgesArr, 'TB'), edges: edgesArr, rootId };
}

onMounted(async () => {
  const el = document.getElementById('vueflow-condiciones-app')
  const ayudaId = el.dataset.ayudaId
  const res = await fetch(`/admin/ayudas/${ayudaId}/condiciones-cuestionario`)
  const data = await res.json()
  if (!data.questions || !data.conditions) return
  const { nodes: n, edges: e, rootId } = buildGraph(data.questions, data.conditions)
  nodes.value = n
  edges.value = e
  await nextTick()
  // Zoom inicial al nodo raíz
  if (vueFlowInstance.value && rootId) {
    const rootNode = n.find(node => node.id === String(rootId))
    if (rootNode) {
      vueFlowInstance.value.fitView({ nodes: [rootNode], minZoom: 0.5, maxZoom: 2, duration: 500 })
    }
  }
})
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
.node-glass {
  background: rgba(255,255,255,0.85);
  border-radius: 2rem;
  box-shadow: 0 6px 32px 0 rgba(0,0,0,0.10);
  border: 2.5px solid #e0e7ef;
  backdrop-filter: blur(8px);
}
.node-answer {
  background: linear-gradient(120deg, #fef9c3 60%, #f0fdf4 100%);
  border-color: #34d399;
  border-radius: 2rem;
  box-shadow: 0 6px 32px 0 rgba(0,0,0,0.10);
  border: 2.5px solid #bbf7d0;
  color: #166534;
  font-weight: bold;
}
.node-answer-final {
  background: linear-gradient(120deg, #f3f4f6 60%, #fee2e2 100%);
  border-color: #ef4444;
  border-radius: 2rem;
  box-shadow: 0 6px 32px 0 rgba(239,68,68,0.10);
  border: 2.5px solid #fecaca;
  color: #991b1b;
  font-weight: bold;
}
.node-final {
  background: linear-gradient(120deg, #fee2e2 60%, #fef2f2 100%);
  border-color: #ef4444;
  border-radius: 2rem;
  box-shadow: 0 6px 32px 0 rgba(239,68,68,0.10);
  border: 2.5px solid #fecaca;
  color: #991b1b;
  font-weight: bold;
}
.node-fin {
  background: linear-gradient(120deg, #f87171 60%, #fef2f2 100%);
  border-color: #b91c1c;
  border-radius: 2rem;
  box-shadow: 0 6px 32px 0 rgba(239,68,68,0.20);
  border: 2.5px solid #fecaca;
  color: #991b1b;
  font-weight: bold;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style> 