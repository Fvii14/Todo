/**
 * Sistema de evaluación dinámica de condiciones de documentos
 * Actualiza la visibilidad de inputs de archivos según las respuestas del usuario
 */

(function() {
    'use strict';

    // Almacenar condiciones de documentos y respuestas actuales
    let documentosConditions = {};
    let currentAnswers = {};
    let documentElements = new Map(); // Mapa de slug -> elementos DOM

    /**
     * Inicializa el sistema de condiciones de documentos
     * @param {Object} condiciones - Condiciones de documentos del backend
     * @param {Object} respuestasIniciales - Respuestas iniciales del usuario
     */
    window.initDocumentosConditions = function(condiciones, respuestasIniciales = {}) {
        documentosConditions = condiciones || {};
        currentAnswers = respuestasIniciales || {};
        
        // Registrar todos los elementos de documentos
        registerDocumentElements();
        
        // Evaluar condiciones iniciales
        evaluateAllDocumentConditions();
        
        // Escuchar cambios en respuestas
        setupAnswerListeners();
    };

    /**
     * Registra todos los elementos de documentos en el DOM
     */
    function registerDocumentElements() {
        documentElements.clear();
        
        // Buscar elementos con data-document-slug (solicitante)
        document.querySelectorAll('[data-document-slug]').forEach(el => {
            const slug = el.getAttribute('data-document-slug');
            if (slug) {
                if (!documentElements.has(slug)) {
                    documentElements.set(slug, []);
                }
                documentElements.get(slug).push(el);
            }
        });
        
        // Buscar elementos con data-document-slug-conviviente (convivientes)
        document.querySelectorAll('[data-document-slug-conviviente]').forEach(el => {
            const slug = el.getAttribute('data-document-slug-conviviente');
            const convivienteIndex = el.getAttribute('data-conviviente-index');
            if (slug) {
                const key = `${slug}_conviviente_${convivienteIndex || 'all'}`;
                if (!documentElements.has(key)) {
                    documentElements.set(key, []);
                }
                documentElements.get(key).push(el);
            }
        });
    }

    /**
     * Configura listeners para cambios en respuestas
     */
    function setupAnswerListeners() {
        // Escuchar cambios en inputs/selects que afectan condiciones
        document.addEventListener('change', function(e) {
            const target = e.target;
            const questionId = getQuestionIdFromElement(target);
            
            if (questionId) {
                const answer = getAnswerFromElement(target);
                updateAnswer(questionId, answer);
            }
        });
        
        // Escuchar cambios en inputs (para campos de texto)
        document.addEventListener('input', function(e) {
            const target = e.target;
            const questionId = getQuestionIdFromElement(target);
            
            if (questionId && target.type === 'text') {
                const answer = target.value;
                updateAnswer(questionId, answer);
            }
        });
        
        // Integración con sistemas existentes de condiciones
        // Si existe window.checkConditions, también actualizar documentos
        const originalCheckConditions = window.checkConditions;
        if (originalCheckConditions) {
            window.checkConditions = function(myConditions, questionId, answer) {
                originalCheckConditions(myConditions, questionId, answer);
                updateAnswer(questionId, answer);
            };
        }
        
        // Integración con sistemas de formularios
        if (window.recalculateSolicitudVisibility) {
            const originalRecalculate = window.recalculateSolicitudVisibility;
            window.recalculateSolicitudVisibility = function() {
                originalRecalculate();
                // Re-evaluar documentos después de recalcular visibilidad
                setTimeout(() => {
                    evaluateAllDocumentConditions();
                }, 100);
            };
        }
    }

    /**
     * Obtiene el ID de pregunta desde un elemento
     */
    function getQuestionIdFromElement(element) {
        // Buscar en el elemento o en sus padres
        let el = element;
        for (let i = 0; i < 5; i++) {
            if (!el) break;
            
            const questionId = el.getAttribute('data-question-id') || 
                             el.getAttribute('name')?.match(/answers\[(\d+)\]/)?.[1];
            
            if (questionId) {
                return parseInt(questionId, 10);
            }
            
            el = el.parentElement;
        }
        return null;
    }

    /**
     * Obtiene la respuesta desde un elemento
     */
    function getAnswerFromElement(element) {
        if (element.type === 'checkbox') {
            if (element.name && element.name.includes('[]')) {
                // Checkbox múltiple
                const checkboxes = document.querySelectorAll(`[name="${element.name}"]:checked`);
                return Array.from(checkboxes).map(cb => cb.value);
            } else {
                return element.checked ? '1' : '0';
            }
        } else if (element.type === 'radio') {
            const radios = document.querySelectorAll(`[name="${element.name}"]:checked`);
            return radios.length > 0 ? radios[0].value : null;
        } else if (element.multiple) {
            return Array.from(element.selectedOptions).map(opt => opt.value);
        } else {
            return element.value;
        }
    }

    /**
     * Actualiza una respuesta y re-evalúa condiciones
     */
    function updateAnswer(questionId, answer) {
        if (questionId) {
            currentAnswers[questionId] = answer;
            evaluateAllDocumentConditions();
        }
    }

    /**
     * Evalúa todas las condiciones de documentos
     */
    function evaluateAllDocumentConditions() {
        // Evaluar documentos del solicitante
        if (documentosConditions.solicitante) {
            documentosConditions.solicitante.forEach(doc => {
                const shouldShow = evaluateDocumentConditions(doc, currentAnswers);
                toggleDocumentVisibility(doc.slug, shouldShow, 'solicitante');
            });
        }
        
        // Evaluar documentos de convivientes
        if (documentosConditions.convivientes) {
            documentosConditions.convivientes.forEach(doc => {
                // Para convivientes, necesitamos evaluar por cada conviviente
                const convivientes = document.querySelectorAll('[data-conviviente-index]');
                const convivienteIndices = new Set();
                convivientes.forEach(el => {
                    const index = el.getAttribute('data-conviviente-index');
                    if (index) convivienteIndices.add(index);
                });
                
                // Si no hay convivientes específicos, evaluar para todos
                if (convivienteIndices.size === 0) {
                    const shouldShow = evaluateDocumentConditions(doc, currentAnswers, null);
                    toggleDocumentVisibility(doc.slug, shouldShow, 'conviviente');
                } else {
                    // Evaluar para cada conviviente
                    convivienteIndices.forEach(index => {
                        const convivienteAnswers = getConvivienteAnswers(parseInt(index, 10));
                        const allAnswers = { ...currentAnswers, ...convivienteAnswers };
                        const shouldShow = evaluateDocumentConditions(doc, allAnswers, parseInt(index, 10));
                        toggleDocumentVisibility(doc.slug, shouldShow, 'conviviente', parseInt(index, 10));
                    });
                }
            });
        }
    }

    /**
     * Obtiene respuestas de un conviviente específico
     */
    function getConvivienteAnswers(convivienteIndex) {
        const answers = {};
        const convivienteElements = document.querySelectorAll(`[data-conviviente-index="${convivienteIndex}"][data-question-id]`);
        
        convivienteElements.forEach(el => {
            const questionId = parseInt(el.getAttribute('data-question-id'), 10);
            const answer = getAnswerFromElement(el);
            if (questionId && answer !== null) {
                answers[questionId] = answer;
            }
        });
        
        return answers;
    }

    /**
     * Evalúa las condiciones de un documento
     */
    function evaluateDocumentConditions(doc, answers, convivienteIndex = null) {
        // Si es obligatorio, siempre mostrar
        if (doc.es_obligatorio) {
            return true;
        }
        
        // Si no tiene condiciones, no mostrar (es opcional sin condiciones)
        if (!doc.conditions || (Array.isArray(doc.conditions) && doc.conditions.length === 0)) {
            return false;
        }
        
        // Obtener requisitos según el formato
        let requirements = [];
        let logic = 'AND';
        
        // Estructura nueva: { condition: 'AND', requirements: [...] }
        if (doc.conditions && typeof doc.conditions === 'object' && 
            doc.conditions.condition && doc.conditions.requirements) {
            logic = doc.conditions.condition || 'AND';
            requirements = doc.conditions.requirements || [];
        }
        // Estructura antigua (legacy): array directo de condiciones
        else if (Array.isArray(doc.conditions)) {
            logic = doc.conditions_logic || 'AND';
            requirements = doc.conditions;
        }
        
        if (requirements.length === 0) {
            return false;
        }
        
        // Evaluar requisitos
        return evaluateRequirements(requirements, answers, logic);
    }

    /**
     * Evalúa un conjunto de requisitos
     */
    function evaluateRequirements(requirements, answers, logic) {
        const results = [];
        
        for (const requirement of requirements) {
            const type = requirement.type || 'simple';
            let cumple = false;
            
            if (type === 'simple') {
                cumple = evaluateSimpleRequirement(requirement, answers);
            } else if (type === 'group') {
                const groupLogic = requirement.groupLogic || 'AND';
                cumple = evaluateGroupRequirements(requirement, answers, groupLogic);
            } else {
                // Formato legacy: tratar como simple
                cumple = evaluateSimpleRequirement(requirement, answers);
            }
            
            results.push(cumple);
            
            // Optimización: si es OR y un requisito se cumple, retornar true
            if (logic === 'OR' && cumple) {
                return true;
            }
            
            // Optimización: si es AND y un requisito no se cumple, retornar false
            if (logic === 'AND' && !cumple) {
                return false;
            }
        }
        
        // Evaluar según el operador lógico
        if (logic === 'OR') {
            return results.some(r => r === true);
        } else {
            return results.every(r => r === true);
        }
    }

    /**
     * Evalúa un requisito simple
     */
    function evaluateSimpleRequirement(requirement, answers) {
        const questionId = requirement.question_id;
        const operator = requirement.operator || '==';
        const expectedValue = requirement.value;
        
        if (!questionId) {
            return false;
        }
        
        const userAnswer = answers[questionId];
        if (userAnswer === undefined || userAnswer === null) {
            return false;
        }

        // Soporte para reglas de fecha dinámica (edad mínima / máxima / rango)
        if (requirement.valueType && requirement.valueType !== 'exact') {
            const dynamicResult = evaluateDynamicDateCondition(userAnswer, requirement);
            if (dynamicResult !== null) {
                return dynamicResult;
            }
        }
        
        return evaluateCondition(userAnswer, operator, expectedValue);
    }

    /**
     * Evalúa un grupo de requisitos
     */
    function evaluateGroupRequirements(group, answers, groupLogic) {
        const rules = group.rules || [];
        
        if (rules.length === 0) {
            return false;
        }
        
        const results = [];
        
        for (const rule of rules) {
            const questionId = rule.question_id;
            const operator = rule.operator || '==';
            const expectedValue = rule.value;
            
            if (!questionId) {
                continue;
            }
            
            const userAnswer = answers[questionId];
            if (userAnswer === undefined || userAnswer === null) {
                results.push(false);
                continue;
            }

            let cumple;

            // Soporte para reglas de fecha dinámica (edad mínima / máxima / rango)
            if (rule.valueType && rule.valueType !== 'exact') {
                const dynamicResult = evaluateDynamicDateCondition(userAnswer, rule);
                if (dynamicResult !== null) {
                    cumple = dynamicResult;
                } else {
                    cumple = evaluateCondition(userAnswer, operator, expectedValue);
                }
            } else {
                cumple = evaluateCondition(userAnswer, operator, expectedValue);
            }

            results.push(cumple);
            
            // Optimización para grupos
            if (groupLogic === 'OR' && cumple) {
                return true;
            }
            if (groupLogic === 'AND' && !cumple) {
                return false;
            }
        }
        
        if (groupLogic === 'OR') {
            return results.some(r => r === true);
        } else {
            return results.every(r => r === true);
        }
    }

    /**
     * Evalúa condiciones de tipo fecha dinámica (edad mínima / máxima / rango).
     * Devuelve true/false si se puede evaluar o null si no aplica.
     */
    function evaluateDynamicDateCondition(userAnswer, rule) {
        const valueType = rule.valueType;
        if (!valueType || valueType === 'exact') {
            return null;
        }

        const birthDate = parseFlexibleDate(userAnswer);
        if (!birthDate) {
            return false;
        }

        const ageUnit = rule.ageUnit || 'years';
        const expectedAge = parseFloat(rule.value);
        if (isNaN(expectedAge)) {
            return false;
        }

        const now = new Date();
        let ageInUnit;

        if (ageUnit === 'years') {
            ageInUnit = now.getFullYear() - birthDate.getFullYear();
            const m = now.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && now.getDate() < birthDate.getDate())) {
                ageInUnit--;
            }
        } else if (ageUnit === 'months') {
            const yearsDiff = now.getFullYear() - birthDate.getFullYear();
            let monthsDiff = yearsDiff * 12 + (now.getMonth() - birthDate.getMonth());
            if (now.getDate() < birthDate.getDate()) {
                monthsDiff--;
            }
            ageInUnit = monthsDiff;
        } else if (ageUnit === 'days') {
            const diffMs = now.getTime() - birthDate.getTime();
            ageInUnit = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        } else {
            return false;
        }

        if (valueType === 'age_minimum') {
            return ageInUnit >= expectedAge;
        }
        if (valueType === 'age_maximum') {
            return ageInUnit <= expectedAge;
        }
        if (valueType === 'age_range') {
            const expectedAge2 = parseFloat(rule.value2);
            if (isNaN(expectedAge2)) {
                return false;
            }
            return ageInUnit >= expectedAge && ageInUnit <= expectedAge2;
        }

        return null;
    }

    /**
     * Intenta parsear una fecha en varios formatos comunes.
     * Para cadenas ISO (YYYY-MM-DD) usa año/mes/día en hora local para evitar
     * desfases de un día en zonas horarias al oeste de UTC (p. ej. América).
     */
    function parseFlexibleDate(value) {
        if (!value) return null;

        if (value instanceof Date) {
            return isNaN(value.getTime()) ? null : value;
        }

        const str = String(value).trim();
        if (!str) return null;

        // Formato ISO YYYY-MM-DD: parsear como fecha local (evita off-by-one en UTC-)
        const isoMatch = str.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
        if (isoMatch) {
            const year = parseInt(isoMatch[1], 10);
            const month = parseInt(isoMatch[2], 10) - 1;
            const day = parseInt(isoMatch[3], 10);
            const d = new Date(year, month, day);
            if (
                d.getFullYear() === year &&
                d.getMonth() === month &&
                d.getDate() === day
            ) {
                return d;
            }
        }

        // Otras cadenas (p. ej. con hora): usar Date nativo
        const parsed = new Date(str);
        if (!isNaN(parsed.getTime())) {
            return parsed;
        }

        // Formato DD/MM/YYYY o DD-MM-YYYY (siempre en hora local)
        const m = str.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
        if (m) {
            const day = parseInt(m[1], 10);
            const month = parseInt(m[2], 10) - 1;
            const year = parseInt(m[3], 10);
            const dd = new Date(year, month, day);
            if (
                dd.getFullYear() === year &&
                dd.getMonth() === month &&
                dd.getDate() === day
            ) {
                return dd;
            }
        }

        return null;
    }

    /**
     * Evalúa una condición individual
     */
    function evaluateCondition(userAnswer, operator, expectedValue) {
        // Normalizar operador
        if (operator === '=') {
            operator = '==';
        }
        
        // Manejar arrays en comparaciones de igualdad
        if (operator === '==') {
            if (Array.isArray(userAnswer)) {
                if (Array.isArray(expectedValue)) {
                    // Ambos son arrays, verificar intersección
                    const userArray = userAnswer.map(String);
                    const expectedArray = expectedValue.map(String);
                    return userArray.some(val => expectedArray.includes(val));
                } else {
                    // Respuesta es array, esperado es valor único
                    return userAnswer.map(String).includes(String(expectedValue));
                }
            } else {
                if (Array.isArray(expectedValue)) {
                    // Respuesta es valor único, esperado es array
                    return expectedValue.map(String).includes(String(userAnswer));
                } else {
                    // Ambos son valores únicos
                    return String(userAnswer) === String(expectedValue);
                }
            }
        }
        
        // Operadores de comparación numérica
        if (['>', '>=', '<', '<='].includes(operator)) {
            const userNum = parseFloat(userAnswer);
            const expectedNum = parseFloat(Array.isArray(expectedValue) ? expectedValue[0] : expectedValue);
            
            if (isNaN(userNum) || isNaN(expectedNum)) {
                return false;
            }
            
            switch (operator) {
                case '>': return userNum > expectedNum;
                case '>=': return userNum >= expectedNum;
                case '<': return userNum < expectedNum;
                case '<=': return userNum <= expectedNum;
                default: return false;
            }
        }
        
        // Operador !=
        if (operator === '!=') {
            if (Array.isArray(userAnswer)) {
                if (Array.isArray(expectedValue)) {
                    return !userAnswer.map(String).some(val => expectedValue.map(String).includes(val));
                } else {
                    return !userAnswer.map(String).includes(String(expectedValue));
                }
            } else {
                if (Array.isArray(expectedValue)) {
                    return !expectedValue.map(String).includes(String(userAnswer));
                } else {
                    return String(userAnswer) !== String(expectedValue);
                }
            }
        }
        
        return false;
    }

    /**
     * Muestra u oculta un documento
     */
    function toggleDocumentVisibility(slug, shouldShow, type, convivienteIndex = null) {
        let key = slug;
        if (type === 'conviviente' && convivienteIndex !== null) {
            key = `${slug}_conviviente_${convivienteIndex}`;
        }
        
        const elements = documentElements.get(key) || [];
        
        // También buscar por slug directo si no se encontró con el key completo
        if (elements.length === 0) {
            const directElements = document.querySelectorAll(`[data-document-slug="${slug}"], [data-document-slug-conviviente="${slug}"]`);
            directElements.forEach(el => {
                // Si es conviviente, verificar el índice
                if (type === 'conviviente' && convivienteIndex !== null) {
                    const elIndex = el.getAttribute('data-conviviente-index');
                    if (elIndex && parseInt(elIndex, 10) === convivienteIndex) {
                        elements.push(el);
                    }
                } else if (type === 'solicitante') {
                    const elIndex = el.getAttribute('data-conviviente-index');
                    if (!elIndex) {
                        elements.push(el);
                    }
                }
            });
        }
        
        elements.forEach(el => {
            if (shouldShow) {
                el.style.display = '';
                el.style.visibility = 'visible';
                el.classList.remove('document-hidden-by-condition');
            } else {
                el.style.display = 'none';
                el.style.visibility = 'hidden';
                el.classList.add('document-hidden-by-condition');
            }
        });
    }

    /**
     * Función pública para actualizar respuestas manualmente
     */
    window.updateDocumentosAnswer = function(questionId, answer) {
        updateAnswer(questionId, answer);
    };

    /**
     * Función pública para re-evaluar todas las condiciones
     */
    window.refreshDocumentosConditions = function() {
        registerDocumentElements();
        evaluateAllDocumentConditions();
    };
})();

