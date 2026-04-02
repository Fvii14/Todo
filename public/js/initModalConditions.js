// Variable global que guarda las IDs de las preguntas que deben ocultarse
let hiddenQuestions = [];
// Variable global que guarda todas las respuestas actuales
let currentAnswers = {};

// Función que obtiene la respuesta actual de una pregunta
function getCurrentAnswer(questionId) {
    const questionElement = document.querySelector(`#convivienteModalBody .question-item[data-id="${questionId}"]`);
    if (!questionElement) {
        return null;
    }

    // Buscar primero el checkbox (si existe), luego otros inputs o selects
    let input = questionElement.querySelector('input[type="checkbox"]:not([type="hidden"])');
    
    if (!input) {
        input = questionElement.querySelector('select');
    }
    
    if (!input) {
        input = questionElement.querySelector('input:not([type="hidden"])');
    }
    
    if (!input) {
        return null;
    }

    let answer = null;

    if (input.type === 'checkbox') {
        if (input.name && input.name.includes('[]')) {
            // Checkboxes múltiples
            const checkboxes = document.querySelectorAll(`[name="answers[${questionId}][]"]`);
            answer = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
            // Si no hay checkboxes seleccionados, devolver null
            if (answer.length === 0) {
                answer = null;
            }
        } else {
            // Checkbox único (sí/no) - leer el estado del checkbox directamente
            answer = input.checked ? '1' : '0';
        }
    } else if (input.multiple) {
        // Select multiple
        answer = Array.from(input.selectedOptions).map(o => o.value);
        if (answer.length === 0) {
            answer = null;
        }
    } else if (input.tagName === 'SELECT') {
        // Select simple - si el valor es -1 (opción por defecto), tratarlo como null
        answer = input.value;
        if (answer === '-1' || answer === '' || answer === null) {
            answer = null;
        }
    } else {
        // Input de texto, número, fecha, etc.
        answer = input.value || null;
        if (answer === '') {
            answer = null;
        }
    }

    return answer;
}

// Función que evalúa TODAS las condiciones para todas las preguntas dependientes
window.evaluateAllConditions = function(myConditions) {
    // Obtener todas las preguntas que tienen condiciones (next_question_id)
    const questionsWithConditions = new Set();
    myConditions.forEach(condition => {
        if (condition.next_question_id) {
            questionsWithConditions.add(parseInt(condition.next_question_id, 10));
        }
    });

    // Para cada pregunta que tiene condiciones, verificar si TODAS se cumplen
    questionsWithConditions.forEach(nextQuestionId => {
        // Obtener todas las condiciones que afectan a esta pregunta
        const conditionsForQuestion = myConditions.filter(c => {
            const nextId = typeof c.next_question_id === 'string' || typeof c.next_question_id === 'number' 
                ? parseInt(c.next_question_id, 10) 
                : null;
            return nextId === nextQuestionId;
        });

        let allConditionsMet = true;

        // Verificar que TODAS las condiciones se cumplan (lógica AND)
        conditionsForQuestion.forEach(condition => {
            const questionId = condition.question_id;
            const answer = getCurrentAnswer(questionId);

            // Valores esperados - normalizar a strings para comparación
            let conditionValues = [];
            if (Array.isArray(condition.condition)) {
                conditionValues = condition.condition.map(v => String(v));
            } else if (condition.condition !== null && condition.condition !== undefined) {
                // Si es un objeto (puede venir como {0: 1} en lugar de [1]), convertirlo a array
                if (typeof condition.condition === 'object') {
                    conditionValues = Object.values(condition.condition).map(v => String(v));
                } else {
                    // Si es un valor primitivo, convertirlo a array
                    conditionValues = [String(condition.condition)];
                }
            }

            // Manejar respuestas null, vacías o undefined
            let matches = false;
            
            if (answer === null || answer === undefined || answer === '' || answer === '-1') {
                // Si la respuesta es null/vacía, solo coincide si la condición incluye '0' o '-1'
                matches = conditionValues.includes('0') || conditionValues.includes('-1');
            } else {
                // Normalizar la respuesta
                const normalizedAnswer = Array.isArray(answer) ? answer : [answer];
                const cleanedAnswer = normalizedAnswer
                    .filter(a => a !== null && a !== undefined && a !== '')
                    .map(a => String(a).replace(/^"|"$/g, ''));

                // Verificar si la respuesta coincide
                // Comparar tanto como string como número
                matches = cleanedAnswer.length > 0 && cleanedAnswer.some(val => {
                    const valStr = String(val);
                    // Comparar como string (primero)
                    if (conditionValues.includes(valStr)) {
                        return true;
                    }
                    // Comparar como número si es posible
                    const numVal = Number(val);
                    if (!isNaN(numVal) && !isNaN(val)) {
                        const numMatch = conditionValues.some(cv => {
                            const cvNum = Number(cv);
                            return !isNaN(cvNum) && cvNum === numVal;
                        });
                        if (numMatch) {
                            return true;
                        }
                    }
                    return false;
                });
            }

            if (!matches) {
                allConditionsMet = false;
            }
        });

        // Si todas las condiciones se cumplen, mostrar la pregunta; si no, ocultarla
        if (allConditionsMet) {
            if (hiddenQuestions.includes(nextQuestionId)) {
                hiddenQuestions.splice(hiddenQuestions.indexOf(nextQuestionId), 1);
            }
        } else {
            if (!hiddenQuestions.includes(nextQuestionId)) {
                hiddenQuestions.push(nextQuestionId);
            }
        }
    });
};

// Función que evalúa si se deben mostrar u ocultar preguntas según las condiciones configuradas
window.checkConditions = function(myConditions, questionId, answer) {
    // Actualizar la respuesta en el objeto global
    currentAnswers[questionId] = answer;
    
    // Re-evaluar TODAS las condiciones cuando cambia cualquier respuesta
    window.evaluateAllConditions(myConditions);
};

// Refresca la visibilidad de las preguntas según el array hiddenQuestions
window.refreshVisibleQuestions = function() {
    const modalBody = document.getElementById('convivienteModalBody');
    if (!modalBody) {
        return;
    }
    
    // Seleccionamos todas las preguntas dentro del modal del conviviente
    const allQuestions = modalBody.querySelectorAll('.question-item');

    allQuestions.forEach(questionElement => {
        const questionId = Number(questionElement.getAttribute('data-id')); // ID de la pregunta
        
        if (!questionId || isNaN(questionId)) {
            return;
        }

        const shouldHide = hiddenQuestions.includes(questionId);
        
        if (shouldHide) {
            questionElement.style.display = 'none'; // Ocultar pregunta
            questionElement.style.visibility = 'hidden';
            questionElement.setAttribute('data-hidden-by-condition', 'true');
        } else {
            questionElement.style.display = 'block'; // Mostrar pregunta
            questionElement.style.visibility = 'visible';
            questionElement.removeAttribute('data-hidden-by-condition');
        }
    });
};

// Inicializa el sistema de condiciones cuando se abre el modal del conviviente
window.initModalConditions = function(myConditions) {
    if (!myConditions) {
        return;
    }
    
    // Convertir objeto a array si es necesario (puede venir como {0: {...}, 1: {...}})
    let conditionsArray = myConditions;
    if (!Array.isArray(myConditions)) {
        if (typeof myConditions === 'object') {
            // Convertir objeto a array
            conditionsArray = Object.values(myConditions);
        } else {
            return;
        }
    }
    
    if (conditionsArray.length === 0) {
        return;
    }

    // Reseteamos preguntas ocultas y respuestas al iniciar
    hiddenQuestions = [];
    currentAnswers = {};

    // Primero, obtener todas las preguntas del modal para recopilar sus respuestas
    const allQuestionElements = document.querySelectorAll('#convivienteModalBody .question-item');
    const questionIds = Array.from(allQuestionElements).map(el => 
        parseInt(el.getAttribute('data-id'), 10)
    );

    // Recopilar todas las respuestas actuales usando getCurrentAnswer
    questionIds.forEach(qId => {
        const answer = getCurrentAnswer(qId);
        currentAnswers[qId] = answer;
    });

    // Añadir listeners a cada input para detectar cambios en tiempo real
    document.querySelectorAll('#convivienteModalBody input, #convivienteModalBody select').forEach(el => {
        const questionElement = el.closest('.question-item'); // El contenedor de la pregunta
        if (!questionElement) return;

        const qId = questionElement.getAttribute('data-id'); // ID de la pregunta

        // Añadir listeners a cada input para detectar cambios en tiempo real
        if (el.type === 'checkbox' && el.name.includes('[]')) {
            // Para grupos de checkboxes múltiples (checkbox[]), añadimos a todos
            const checkboxes = document.querySelectorAll(`[name="answers[${qId}][]"]`);
            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    // Usar getCurrentAnswer para obtener la respuesta actualizada
                    const updatedAnswer = getCurrentAnswer(qId);
                    window.checkConditions(conditionsArray, qId, updatedAnswer);
                    window.refreshVisibleQuestions();
                });
            });
        } else {
            // Para campos individuales (input, select normal, checkbox simple...)
            ['change', 'input'].forEach(eventType => {
                el.addEventListener(eventType, () => {
                    // Usar getCurrentAnswer para obtener la respuesta actualizada
                    const updatedAnswer = getCurrentAnswer(qId);
                    window.checkConditions(conditionsArray, qId, updatedAnswer);
                    window.refreshVisibleQuestions();
                });
            });
        }
    });

    // Evaluar todas las condiciones con las respuestas iniciales
    // Usar un pequeño delay para asegurar que el DOM esté completamente renderizado
    setTimeout(() => {
        window.evaluateAllConditions(conditionsArray);
        // Refrescar visibilidad inmediatamente después de evaluar
        window.refreshVisibleQuestions();
    }, 50);

    // Una vez montado todo, refrescamos visibilidad de nuevo (con un delay adicional por seguridad)
    setTimeout(() => {
        // Re-evaluar condiciones por si acaso
        window.evaluateAllConditions(conditionsArray);
        window.refreshVisibleQuestions();
    }, 200);
};
