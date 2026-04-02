@props(['ayudaSolicitada'])

@php
    $contratacionId = $ayudaSolicitada->id;
    $ayudaId = $ayudaSolicitada->ayuda_id ?? $ayudaSolicitada->ayuda->id ?? $ayudaSolicitada->id;
@endphp

<div class="modal fade" id="modalCrearConviviente-{{ $contratacionId }}" tabindex="-1" aria-labelledby="modalCrearConvivienteLabel-{{ $contratacionId }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCrearConvivienteLabel-{{ $contratacionId }}">Crear nuevo conviviente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalCrearConvivienteBody-{{ $contratacionId }}">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando formulario...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarConviviente-{{ $contratacionId }}">Guardar conviviente</button>
            </div>
        </div>
    </div>
</div>

<style>
    #modalCrearConviviente-{{ $contratacionId }} .modal-content {
        border-radius: 12px;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        border: none;
    }

    #modalCrearConviviente-{{ $contratacionId }} .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #3c3a60;
    }

    #modalCrearConviviente-{{ $contratacionId }} .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    #modalCrearConviviente-{{ $contratacionId }} .btn-primary {
        background-color: #54debd;
        border-color: #54debd;
        font-weight: 600;
    }

    #modalCrearConviviente-{{ $contratacionId }} .btn-primary:hover {
        background-color: #40d4b0;
    }

    #modalCrearConviviente-{{ $contratacionId }} .question-item {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
    }

    #modalCrearConviviente-{{ $contratacionId }} .question-item:last-child {
        border-bottom: none;
    }
</style>

<script>
    (function() {
        const ayudaId = {{ $ayudaId }};
        const contratacionId = {{ $contratacionId }};
        const modalId = 'modalCrearConviviente-' + contratacionId;
        const bodyId = 'modalCrearConvivienteBody-' + contratacionId;
        const btnGuardarId = 'btnGuardarConviviente-' + contratacionId;
        
        const modal = document.getElementById(modalId);
        const modalBody = document.getElementById(bodyId);
        const btnGuardar = document.getElementById(btnGuardarId);
        
        let questions = [];
        let conditions = [];
        let answers = {};
        let hiddenQuestions = [];
        
        // Cuando se abre el modal, cargar las preguntas
        modal.addEventListener('show.bs.modal', function() {
            cargarPreguntas();
        });
        
        // Función para cargar las preguntas
        function cargarPreguntas() {
            modalBody.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando formulario...</p>
                </div>
            `;
            
            fetch(`/api/conviviente-crear-form/${ayudaId}`, {
                // Nota: ayudaId aquí es realmente el ayuda_id, no la contratación_id
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    questions = data.questions || [];
                    conditions = []; // No se usan condiciones para este formulario
                    answers = {};
                    hiddenQuestions = [];
                    renderForm();
                    // No es necesario evaluar condiciones ya que todas las preguntas son visibles
                } else {
                    modalBody.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>Error:</strong> ${data.message || 'No se pudieron cargar las preguntas'}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Error:</strong> No se pudo cargar el formulario. Por favor, intenta de nuevo.
                    </div>
                `;
            });
        }
        
        // Función para evaluar condiciones
        function checkConditions(questionId, answer) {
            const normalizedAnswer = Array.isArray(answer) ? answer : [answer];
            const cleanedAnswer = normalizedAnswer
                .filter(a => typeof a === 'string' || typeof a === 'number')
                .map(a => String(a).trim().replace(/^"|"$/g, ''));

            conditions.forEach(condition => {
                if (condition.question_id == questionId) {
                    const nextQuestionId = parseInt(condition.next_question_id, 10);
                    let conditionValues = [];

                    if (Array.isArray(condition.condition)) {
                        conditionValues = condition.condition.map(String).map(v => v.trim());
                    } else if (typeof condition.condition === 'string') {
                        conditionValues = [condition.condition.trim()];
                    }

                    const matches = cleanedAnswer.some(val => conditionValues.includes(val));

                    if (matches) {
                        // Mostrar pregunta dependiente
                        const index = hiddenQuestions.indexOf(nextQuestionId);
                        if (index !== -1) hiddenQuestions.splice(index, 1);
                    } else {
                        // Ocultar pregunta dependiente
                        if (!hiddenQuestions.includes(nextQuestionId)) {
                            hiddenQuestions.push(nextQuestionId);
                        }
                    }
                }
            });
            
            refreshVisibleQuestions();
        }
        
        // Función para evaluar todas las condiciones al inicio
        function evaluateAllConditions() {
            hiddenQuestions = [];
            
            // Obtener todas las preguntas que tienen condiciones
            const questionsWithConditions = new Set();
            conditions.forEach(condition => {
                if (condition.next_question_id) {
                    questionsWithConditions.add(parseInt(condition.next_question_id, 10));
                }
            });
            
            // Para cada pregunta con condiciones, verificar si se cumple
            questionsWithConditions.forEach(nextQuestionId => {
                const conditionsForQuestion = conditions.filter(c => {
                    const nextId = typeof c.next_question_id === 'string' || typeof c.next_question_id === 'number' 
                        ? parseInt(c.next_question_id, 10) 
                        : null;
                    return nextId === nextQuestionId;
                });
                
                let allConditionsMet = true;
                
                conditionsForQuestion.forEach(condition => {
                    const questionId = condition.question_id;
                    const answer = answers[questionId];
                    const conditionValues = Array.isArray(condition.condition) 
                        ? condition.condition.map(String).map(v => v.trim())
                        : [String(condition.condition).trim()];
                    
                    const normalizedAnswer = Array.isArray(answer) ? answer : [answer];
                    const cleanedAnswer = normalizedAnswer
                        .filter(a => typeof a === 'string' || typeof a === 'number')
                        .map(a => String(a).trim().replace(/^"|"$/g, ''));
                    
                    const matches = cleanedAnswer.some(val => conditionValues.includes(val));
                    
                    if (!matches) {
                        allConditionsMet = false;
                    }
                });
                
                if (!allConditionsMet) {
                    hiddenQuestions.push(nextQuestionId);
                }
            });
            
            refreshVisibleQuestions();
        }
        
        // Función para actualizar la visibilidad de las preguntas
        function refreshVisibleQuestions() {
            document.querySelectorAll(`#formCrearConviviente-${contratacionId} .question-item`).forEach(questionElement => {
                const questionId = parseInt(questionElement.getAttribute('data-question-id'), 10);
                if (hiddenQuestions.includes(questionId)) {
                    questionElement.style.display = 'none';
                } else {
                    questionElement.style.display = 'block';
                }
            });
        }
        
        // Función para renderizar el formulario
        function renderForm() {
            if (questions.length === 0) {
                modalBody.innerHTML = `
                    <div class="alert alert-info">
                        No hay preguntas disponibles para crear el conviviente.
                    </div>
                `;
                return;
            }
            
            let html = '<form id="formCrearConviviente-' + contratacionId + '">';
            
            questions.forEach(question => {
                html += '<div class="question-item" data-question-id="' + question.id + '">';
                html += '<label class="form-label">' + (question.text_conviviente || question.text) + '</label>';
                
                if (question.subtext) {
                    html += '<small class="text-muted d-block mb-2">' + question.subtext + '</small>';
                }
                
                switch(question.type) {
                    case 'text':
                    case 'string':
                    case 'date':
                    case 'integer':
                        html += `<input type="${question.type === 'date' ? 'date' : question.type === 'integer' ? 'number' : 'text'}" 
                                class="form-control" 
                                name="question_${question.id}" 
                                id="question_${question.id}"
                                data-question-id="${question.id}"
                                onchange="handleAnswerChange(${question.id}, this.value)" />`;
                        break;
                        
                    case 'select':
                    case 'radio':
                        if (question.options && Object.keys(question.options).length > 0) {
                            html += '<select class="form-select" name="question_' + question.id + '" id="question_' + question.id + '" data-question-id="' + question.id + '" onchange="handleAnswerChange(' + question.id + ', this.value)">';
                            html += '<option value="">Selecciona una opción</option>';
                            Object.entries(question.options).forEach(([key, value]) => {
                                html += `<option value="${key}">${value}</option>`;
                            });
                            html += '</select>';
                        }
                        break;
                        
                    case 'multiple':
                    case 'checkbox':
                        if (question.options && Object.keys(question.options).length > 0) {
                            Object.entries(question.options).forEach(([key, value]) => {
                                html += `<div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                        name="question_${question.id}[]" 
                                        id="question_${question.id}_${key}"
                                        value="${key}"
                                        data-question-id="${question.id}"
                                        onchange="handleCheckboxChange(${question.id})" />
                                    <label class="form-check-label" for="question_${question.id}_${key}">
                                        ${value}
                                    </label>
                                </div>`;
                            });
                        }
                        break;
                        
                    case 'boolean':
                        html += `<div class="form-check">
                            <input class="form-check-input" type="radio" 
                                name="question_${question.id}" 
                                id="question_${question.id}_si"
                                value="1"
                                data-question-id="${question.id}"
                                onchange="handleAnswerChange(${question.id}, this.value)" />
                            <label class="form-check-label" for="question_${question.id}_si">Sí</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" 
                                name="question_${question.id}" 
                                id="question_${question.id}_no"
                                value="0"
                                data-question-id="${question.id}"
                                onchange="handleAnswerChange(${question.id}, this.value)" />
                            <label class="form-check-label" for="question_${question.id}_no">No</label>
                        </div>`;
                        break;
                }
                
                html += '</div>';
            });
            
            html += '</form>';
            modalBody.innerHTML = html;
        }
        
        // Funciones globales para manejar cambios en las respuestas
        window.handleAnswerChange = function(questionId, answer) {
            answers[questionId] = answer;
            checkConditions(questionId, answer);
        };
        
        window.handleCheckboxChange = function(questionId) {
            const form = document.getElementById('formCrearConviviente-' + contratacionId);
            const checkboxes = form.querySelectorAll(`input[type="checkbox"][data-question-id="${questionId}"]`);
            const checked = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
            answers[questionId] = checked;
            checkConditions(questionId, checked);
        };
        
        // Función para guardar el conviviente
        btnGuardar.addEventListener('click', function() {
            const form = document.getElementById('formCrearConviviente-' + contratacionId);
            if (!form) return;
            
            // Recopilar respuestas
            answers = {};
            console.log('🔍 Recopilando respuestas del formulario...');
            questions.forEach(question => {
                // Buscar solo inputs, selects y textareas (no labels ni otros elementos)
                const allElements = form.querySelectorAll(`[data-question-id="${question.id}"]`);
                const inputs = Array.from(allElements).filter(el => 
                    el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA'
                );
                
                console.log(`  Pregunta ${question.id} (${question.type}):`, {
                    allElementsFound: allElements.length,
                    inputsFound: inputs.length,
                    inputs: inputs,
                    question: question
                });
                
                if (question.type === 'multiple' || question.type === 'checkbox') {
                    const checked = inputs.filter(input => input.checked).map(input => input.value);
                    if (checked.length > 0) {
                        answers[question.id] = checked;
                        console.log(`    ✅ Respuesta múltiple:`, checked);
                    } else {
                        console.log(`    ⚠️ No hay checkboxes seleccionados para pregunta ${question.id}`);
                    }
                } else if (question.type === 'radio' || question.type === 'boolean') {
                    const checked = inputs.find(input => input.checked);
                    if (checked) {
                        answers[question.id] = checked.value;
                        console.log(`    ✅ Respuesta radio/boolean:`, checked.value);
                    } else {
                        console.log(`    ⚠️ No hay radio seleccionado para pregunta ${question.id}`);
                    }
                } else {
                    // Para text, string, date, integer, select
                    const input = inputs[0];
                    if (input) {
                        const value = input.value ? input.value.trim() : '';
                        if (value) {
                            answers[question.id] = value;
                            console.log(`    ✅ Respuesta ${question.type}:`, value);
                        } else {
                            console.log(`    ⚠️ Input encontrado pero vacío para pregunta ${question.id}:`, {
                                input: input,
                                value: input.value,
                                type: input.type,
                                tagName: input.tagName
                            });
                        }
                    } else {
                        console.log(`    ❌ No se encontró input para pregunta ${question.id}`);
                    }
                }
            });
            
            console.log('📦 Respuestas recopiladas:', answers);
            console.log('📦 Número de respuestas:', Object.keys(answers).length);
            console.log('📦 ayudaId:', ayudaId, 'tipo:', typeof ayudaId);
            console.log('📦 contratacionId:', contratacionId);
            
            const payload = {
                ayuda_id: ayudaId,
                contratacion_id: contratacionId,
                answers: answers
            };
            
            console.log('📤 Enviando payload:', payload);
            
            // Deshabilitar botón
            btnGuardar.disabled = true;
            btnGuardar.textContent = 'Guardando...';
            
            // Enviar datos
            fetch('/api/conviviente-crear', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            })
            .then(response => {
                console.log('📥 Respuesta recibida - Status:', response.status);
                return response.json().then(data => {
                    console.log('📥 Respuesta recibida - Data:', data);
                    return { response, data };
                });
            })
            .then(({ response, data }) => {
                if (data.success) {
                    console.log('✅ Conviviente creado exitosamente');
                    // Cerrar modal
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    bsModal.hide();
                    
                    // Recargar la página o actualizar la lista de convivientes
                    location.reload();
                } else {
                    console.error('❌ Error en la respuesta:', data);
                    console.error('❌ Errores de validación:', data.errors);
                    alert('Error: ' + (data.message || 'No se pudo crear el conviviente') + '\n\nErrores: ' + JSON.stringify(data.errors || {}));
                    btnGuardar.disabled = false;
                    btnGuardar.textContent = 'Guardar conviviente';
                }
            })
            .catch(error => {
                console.error('❌ Error en la petición:', error);
                alert('Error al guardar el conviviente. Por favor, intenta de nuevo.');
                btnGuardar.disabled = false;
                btnGuardar.textContent = 'Guardar conviviente';
            });
        });
    })();
</script>

