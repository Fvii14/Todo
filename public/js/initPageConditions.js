// Variable global que guarda las IDs de las preguntas que deben ocultarse
let hiddenQuestions = [];

// Detecta si las condiciones usan formato legacy o nuevo
function detectConditionFormat(conditions) {
    if (!conditions || conditions.length === 0) return 'NEW';
    const firstCondition = conditions[0];
    // Si tiene condition (no null/undefined), es legacy
    if (firstCondition.condition !== null && firstCondition.condition !== undefined) {
        return 'OLD';
    }
    // Si tiene operator/value, es nuevo
    if (firstCondition.operator !== null && firstCondition.operator !== undefined) {
        return 'NEW';
    }
    return 'OLD'; // Por defecto legacy para compatibilidad
}

// Evalúa una condición simple con el nuevo formato (operator + value)
function evaluateSimpleCondition(condition, cleanedAnswer) {
    // Normalizar operador: convertir '=' a '=='
    let operator = condition.operator || '==';
    if (operator === '=') {
        operator = '==';
    }
    
    let conditionValue = condition.value;
    
    // Si conditionValue es un array, trabajar con él directamente
    // Si es un valor único, convertirlo a array para comparación
    const expectedValues = Array.isArray(conditionValue) ? conditionValue : [conditionValue];

    switch (operator) {
        case '==':
        case '=':
            // Comparar si algún valor de la respuesta coincide con algún valor esperado
            return cleanedAnswer.some(val => expectedValues.some(exp => val == exp));
        case '!=':
            // Verificar que ningún valor de la respuesta coincida con los esperados
            return !cleanedAnswer.some(val => expectedValues.some(exp => val == exp));
        case '>':
            return cleanedAnswer.some(val => {
                const numVal = parseFloat(val);
                return !isNaN(numVal) && expectedValues.some(exp => numVal > parseFloat(exp));
            });
        case '>=':
            return cleanedAnswer.some(val => {
                const numVal = parseFloat(val);
                return !isNaN(numVal) && expectedValues.some(exp => numVal >= parseFloat(exp));
            });
        case '<':
            return cleanedAnswer.some(val => {
                const numVal = parseFloat(val);
                return !isNaN(numVal) && expectedValues.some(exp => numVal < parseFloat(exp));
            });
        case '<=':
            return cleanedAnswer.some(val => {
                const numVal = parseFloat(val);
                return !isNaN(numVal) && expectedValues.some(exp => numVal <= parseFloat(exp));
            });
        case 'in':
            return cleanedAnswer.some(val => expectedValues.includes(val));
        case 'not_in':
            return !cleanedAnswer.some(val => expectedValues.includes(val));
        case 'contains':
            return cleanedAnswer.some(val => 
                expectedValues.some(exp => String(val).toLowerCase().includes(String(exp).toLowerCase()))
            );
        case 'not_contains':
            return !cleanedAnswer.some(val => 
                expectedValues.some(exp => String(val).toLowerCase().includes(String(exp).toLowerCase()))
            );
        case 'starts_with':
            return cleanedAnswer.some(val => 
                expectedValues.some(exp => String(val).toLowerCase().startsWith(String(exp).toLowerCase()))
            );
        case 'ends_with':
            return cleanedAnswer.some(val => 
                expectedValues.some(exp => String(val).toLowerCase().endsWith(String(exp).toLowerCase()))
            );
        default:
            return false;
    }
}

// Evalúa condiciones y oculta/muestra preguntas
window.checkConditions = function(myConditions, questionId, answer) {
    const normalizedAnswer = Array.isArray(answer) ? answer : [answer];
    const cleanedAnswer = normalizedAnswer
        .filter(a => typeof a === 'string' || typeof a === 'number')
        .map(a => String(a).replace(/^"|"$/g, ''));

    // Detectar formato de condiciones
    const format = detectConditionFormat(myConditions);

    myConditions.forEach(condition => {
        if (condition.question_id == questionId) {
            const nextQuestionId = parseInt(condition.next_question_id, 10);
            let matches = false;

            if (format === 'OLD') {
                // Formato legacy: condition.condition (array de valores)
                let conditionValues = Array.isArray(condition.condition)
                    ? condition.condition.map(String)
                    : [];
                matches = cleanedAnswer.some(val => conditionValues.includes(val));
            } else {
                // Formato nuevo: operator + value
                if (condition.is_composite && condition.composite_rules) {
                    // Condición compuesta: evaluar todas las reglas
                    const rules = condition.composite_rules;
                    const logic = condition.composite_logic || 'AND';
                    
                    const ruleResults = rules.map(rule => {
                        // Obtener respuesta de la pregunta de la regla
                        const ruleQuestionElement = document.querySelector(`[data-id="${rule.question_id}"]`);
                        if (!ruleQuestionElement) return false;
                        
                        // Extraer respuesta (simplificado, asumiendo que está en un input/select)
                        let ruleAnswer = null;
                        const input = ruleQuestionElement.querySelector('input, select');
                        if (input) {
                            if (input.type === 'checkbox') {
                                ruleAnswer = input.checked ? '1' : '0';
                            } else if (input.type === 'radio') {
                                const checked = ruleQuestionElement.querySelector(`input[name="${input.name}"]:checked`);
                                ruleAnswer = checked ? checked.value : '0';
                            } else {
                                ruleAnswer = input.value || '';
                            }
                        }
                        
                        const normalizedRuleAnswer = Array.isArray(ruleAnswer) ? ruleAnswer : [ruleAnswer];
                        const cleanedRuleAnswer = normalizedRuleAnswer
                            .filter(a => typeof a === 'string' || typeof a === 'number')
                            .map(a => String(a).replace(/^"|"$/g, ''));
                        
                        return evaluateSimpleCondition(rule, cleanedRuleAnswer);
                    });
                    
                    if (logic === 'AND') {
                        matches = ruleResults.every(result => result === true);
                    } else if (logic === 'OR') {
                        matches = ruleResults.some(result => result === true);
                    }
                } else {
                    // Condición simple
                    matches = evaluateSimpleCondition(condition, cleanedAnswer);
                }
            }

            if (matches) {
                hiddenQuestions = hiddenQuestions.filter(id => id !== nextQuestionId);
            } else {
                if (!hiddenQuestions.includes(nextQuestionId)) {
                    hiddenQuestions.push(nextQuestionId);
                }
            }
        }
    });
};

// Aplica visibilidad a todas las preguntas según hiddenQuestions
window.refreshVisibleQuestions = function() {
    document.querySelectorAll('.question-item').forEach(questionElement => {
        const questionId = Number(questionElement.getAttribute('data-id'));
        questionElement.style.display = hiddenQuestions.includes(questionId) ? 'none' : 'block';
    });
};

// Inicializa sistema de condiciones para formularios en páginas completas
window.initPageConditions = function(myConditions) {
    if (!myConditions || !Array.isArray(myConditions)) return;

    hiddenQuestions = [];

    document.querySelectorAll('.question-item input, .question-item select').forEach(el => {
    const questionElement = el.closest('.question-item');
    if (!questionElement) return;

    const qId = questionElement.getAttribute('data-id');
    let answer = null;

    // ✅ Para radio buttons tipo booleano (sí/no)
    if (el.type === 'radio') {
        const radios = document.querySelectorAll(`input[name="answers[${qId}]"]`);
        const checked = Array.from(radios).find(r => r.checked);
        answer = checked ? checked.value : '0';

        // Añadir listener a todos los radios
        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                const selected = document.querySelector(`input[name="answers[${qId}]"]:checked`);
                const updatedAnswer = selected ? selected.value : '0';
                window.checkConditions(myConditions, qId, updatedAnswer);
                window.refreshVisibleQuestions();
            });
        });

    } else if (el.type === 'checkbox') {
        if (el.name.includes('[]')) {
            const checkboxes = document.querySelectorAll(`[name="answers[${qId}][]"]`);
            answer = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);

            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    const updated = Array.from(checkboxes).filter(c => c.checked).map(c => c.value);
                    window.checkConditions(myConditions, qId, updated);
                    window.refreshVisibleQuestions();
                });
            });
        } else {
            answer = el.checked ? '1' : '0';
            el.addEventListener('change', () => {
                const updated = el.checked ? '1' : '0';
                window.checkConditions(myConditions, qId, updated);
                window.refreshVisibleQuestions();
            });
        }

    } else if (el.multiple) {
        answer = Array.from(el.selectedOptions).map(o => o.value);
        el.addEventListener('change', () => {
            const updated = Array.from(el.selectedOptions).map(o => o.value);
            window.checkConditions(myConditions, qId, updated);
            window.refreshVisibleQuestions();
        });

    } else {
        answer = el.value || '';
        el.addEventListener('input', () => {
            window.checkConditions(myConditions, qId, el.value || '');
            window.refreshVisibleQuestions();
        });
    }

    // Evaluamos condiciones al cargar
    window.checkConditions(myConditions, qId, answer);
    });

    setTimeout(() => {
        window.refreshVisibleQuestions();
    }, 50);
};
