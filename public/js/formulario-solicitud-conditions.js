// Estado global para el traductor de saltos a visibilidad en el formulario de solicitud
// Para depuración se puede activar window.DEBUG_SOLICITUD_CONDITIONS = true en consola.
window.DEBUG_SOLICITUD_CONDITIONS = window.DEBUG_SOLICITUD_CONDITIONS || false;
window.hiddenQuestions = []; // compatibilidad antigua
window.solicitudConditions = [];
window.solicitudFormSelector = '.datosSolicitante';
window.solicitudOrder = [];
window.solicitudAnswers = {};
window.solicitudVisibility = {};

window.refreshSolicitudVisibleQuestions = function (formSelector = '.datosSolicitante') {
    // Aceptar tanto selectores de formulario como de contenedor
    const container = document.querySelector(formSelector);
    if (!container) {
        console.warn('No se encontró el contenedor:', formSelector);
        return;
    }
    
    container.querySelectorAll('.question-item').forEach(questionElement => {
        const questionId = Number(questionElement.getAttribute('data-id'));

        // Si tenemos mapa de visibilidad calculado con el nuevo traductor, usarlo
        if (window.solicitudVisibility && Object.prototype.hasOwnProperty.call(window.solicitudVisibility, questionId)) {
            const isVisible = !!window.solicitudVisibility[questionId];
            questionElement.style.display = isVisible ? 'block' : 'none';
        } else {
            // Fallback: comportamiento antiguo basado en hiddenQuestions
            if (window.hiddenQuestions.includes(questionId)) {
                questionElement.style.display = 'none';
            } else {
                questionElement.style.display = 'block';
            }
        }
    });
};

/**
 * Determina si un salto "salta sobre" una pregunta (la omite en el flujo),
 * replicando la lógica de isQuestionSkippedByJump del wizard.
 */
function solicitudSaltoSaltaPregunta(questionIndex, jump, order, totalQuestions) {
    const sourceId = jump.question_id ?? null;
    const nextId = jump.next_question_id ?? null;

    if (sourceId === null) {
        return false;
    }

    const sourceIndex = order[sourceId] ?? -1;

    let destIndex;
    if (nextId === null || nextId === 'FIN' || nextId === '') {
        destIndex = totalQuestions;
    } else {
        destIndex = order[nextId] ?? -1;
    }

    if (sourceIndex === -1 || destIndex === -1) {
        return false;
    }

    return questionIndex > sourceIndex && questionIndex < destIndex;
}

/**
 * Evalúa una condición simple (question_id, operator, value) contra las
 * respuestas actuales del usuario en el formulario de solicitud.
 * Es equivalente a evaluarCondicionSimpleSolicitud en PHP.
 */
function solicitudEvaluarCondicionSimple(condition, answers) {
    const questionId = condition.question_id ?? null;
    if (questionId === null) {
        console.warn('[SolicitudConditions] condición sin question_id', condition);
        return false;
    }

    const answer = answers[questionId] ?? null;

    if (!Object.prototype.hasOwnProperty.call(condition, 'value') || condition.value === null) {
        // Falta value → no evaluamos
        return false;
    }

    if (answer === null || answer === '') {
        // Sin respuesta → no se cumple
        return false;
    }

    const operator = condition.operator || '==';
    let expectedValue = condition.value;

    // El getter de condiciones puede devolver arrays; si es de un solo elemento, simplificamos
    if (Array.isArray(expectedValue) && expectedValue.length === 1) {
        expectedValue = expectedValue[0];
    }

    // Normalizar booleanos esperados
    if (expectedValue === '1' || expectedValue === 1 || expectedValue === 'true' || expectedValue === true) {
        expectedValue = 1;
    } else if (expectedValue === '0' || expectedValue === 0 || expectedValue === 'false' || expectedValue === false) {
        expectedValue = 0;
    }

    let normalizedAnswer = answer;

    // Normalizar respuesta booleana
    if (answer === true || answer === 'true' || answer === '1') {
        normalizedAnswer = 1;
    } else if (answer === false || answer === 'false' || answer === '0') {
        normalizedAnswer = 0;
    }

    // Si la respuesta es array (multiple)
    if (Array.isArray(answer)) {
        switch (operator) {
            case '==':
            case '=':
                return answer.includes(expectedValue);
            case '!=':
                return !answer.includes(expectedValue);
            default:
                return false;
        }
    }

    // Comparaciones numéricas si ambos son numéricos
    if (!isNaN(normalizedAnswer) && !isNaN(expectedValue)) {
        normalizedAnswer = Number(normalizedAnswer);
        expectedValue = Number(expectedValue);
    }

    let result = false;

    switch (operator) {
        case '==':
        case '=':
            result = normalizedAnswer == expectedValue;
            break;
        case '!=':
            result = normalizedAnswer != expectedValue;
            break;
        case '>':
            result = normalizedAnswer > expectedValue;
            break;
        case '>=':
            result = normalizedAnswer >= expectedValue;
            break;
        case '<':
            result = normalizedAnswer < expectedValue;
            break;
        case '<=':
            result = normalizedAnswer <= expectedValue;
            break;
        case 'contains':
            result = String(normalizedAnswer).toLowerCase().includes(String(expectedValue).toLowerCase());
            break;
        case 'not_contains':
            result = !String(normalizedAnswer).toLowerCase().includes(String(expectedValue).toLowerCase());
            break;
        default:
            result = false;
    }

    if (window.DEBUG_SOLICITUD_CONDITIONS) {
        console.log('[SolicitudConditions] evaluar condición simple', {
            question_id: questionId,
            operator,
            answer: normalizedAnswer,
            expected: expectedValue,
            result,
        });
    }

    return result;
}

/**
 * Recalcula la visibilidad de TODAS las preguntas del formulario de solicitud
 * aplicando el TRADUCTOR DE SALTOS A VISIBILIDAD (misma lógica que en el wizard).
 */
window.recalculateSolicitudVisibility = function () {
    const conditions = window.solicitudConditions || [];
    const answers = window.solicitudAnswers || {};
    const ids = window.solicitudOrder || [];
    const totalQuestions = ids.length;

    const order = {};
    ids.forEach((id, idx) => {
        order[id] = idx;
    });

    const visibility = {};

    ids.forEach((questionId) => {
        const questionIndex = order[questionId] ?? -1;
        if (questionIndex === -1) {
            visibility[questionId] = true;
            return;
        }

        const jumpsTo = conditions.filter((c) => (c.next_question_id ?? null) == questionId);
        const jumpsSkipping = conditions.filter((jump) =>
            solicitudSaltoSaltaPregunta(questionIndex, jump, order, totalQuestions),
        );

        if (window.DEBUG_SOLICITUD_CONDITIONS) {
            console.log('[SolicitudConditions] Visibilidad pregunta (JS)', {
                question_id: questionId,
                question_index: questionIndex,
                jumps_to: jumpsTo,
                jumps_skipping: jumpsSkipping,
            });
        }

        // Sin saltos que la afecten → visible
        if (jumpsTo.length === 0 && jumpsSkipping.length === 0) {
            visibility[questionId] = true;
            return;
        }

        // NOTA: los saltos DIRECTOS (A → B) NO condicionan la visibilidad de B.
        // Solo se usan para "saltar" preguntas intermedias (las que quedan entre A y B).

        // Saltos que la saltan: visible solo si NINGUNO se cumple
        if (jumpsSkipping.length > 0) {
            let allSkippingInactive = true;
            for (const jump of jumpsSkipping) {
                const r = solicitudEvaluarCondicionSimple(jump, answers);
                if (window.DEBUG_SOLICITUD_CONDITIONS) {
                    console.log('[SolicitudConditions] Evaluar salto que salta pregunta (JS)', {
                        question_id: questionId,
                        jump,
                        result: r,
                    });
                }
                if (r) {
                    allSkippingInactive = false;
                    break;
                }
            }
            visibility[questionId] = allSkippingInactive;
            return;
        }

        // Si solo tiene saltos directos (ninguno la "salta"), la pregunta es siempre visible.
        visibility[questionId] = true;
    });

    if (window.DEBUG_SOLICITUD_CONDITIONS) {
        console.log('[SolicitudConditions] Visibilidad final (JS)', visibility);
    }
    window.solicitudVisibility = visibility;
    window.refreshSolicitudVisibleQuestions(window.solicitudFormSelector);
};

window.initSolicitudConditions = function (myConditions, formSelector = '.datosSolicitante') {
    if (!myConditions || !Array.isArray(myConditions)) {
        console.warn('⚠️ No se encontraron condiciones o no es un array:', myConditions);
        return;
    }

    window.hiddenQuestions = [];
    window.solicitudConditions = myConditions;
    window.solicitudFormSelector = formSelector;
    window.solicitudAnswers = {};

    // Aceptar tanto selectores de formulario como de contenedor
    const container = document.querySelector(formSelector);
    if (!container) {
        console.warn('⚠️ No se encontró el contenedor del formulario:', formSelector);
        return;
    }

    // Construir orden de preguntas según aparecen en el DOM
    const questionElements = container.querySelectorAll('.question-item[data-id]');
    window.solicitudOrder = Array.from(questionElements).map(el => Number(el.getAttribute('data-id')));

    container.querySelectorAll('input, select').forEach(el => {
        const questionElement = el.closest('.question-item');
        if (!questionElement) return;

        const qId = Number(questionElement.getAttribute('data-id'));
        let answer = null;

        if (el.type === 'checkbox') {
            if (el.name.includes('[]')) {
                const checkboxes = container.querySelectorAll(`[name="answers[${qId}][]"]`);
                answer = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
            } else {
                answer = el.checked ? '1' : '0';
            }
        } else if (el.multiple) {
            answer = Array.from(el.selectedOptions).map(o => o.value);
        } else {
            answer = el.value;
        }

        window.solicitudAnswers[qId] = answer;

        ['change', 'input'].forEach(eventType => {
            el.addEventListener(eventType, () => {
                let updatedAnswer = null;

                if (el.type === 'checkbox') {
                    if (el.name.includes('[]')) {
                        const checkboxes = container.querySelectorAll(`[name="answers[${qId}][]"]`);
                        updatedAnswer = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
                    } else {
                        updatedAnswer = el.checked ? '1' : '0';
                    }
                } else if (el.multiple) {
                    updatedAnswer = Array.from(el.selectedOptions).map(o => o.value);
                } else {
                    updatedAnswer = el.value;
                }

                window.solicitudAnswers[qId] = updatedAnswer;
                if (window.DEBUG_SOLICITUD_CONDITIONS) {
                    console.log('[SolicitudConditions] Cambio detectado', {
                        question_id: qId,
                        answer: updatedAnswer,
                    });
                }
                window.recalculateSolicitudVisibility();
            });
        });
    });

    // Calcular visibilidad inicial
    window.recalculateSolicitudVisibility();
};
