/**
 * Obtiene el texto de una pregunta por su ID
 */
export const getQuestionTextById = (questionId, allQuestions) => {
    const question = allQuestions.find(q => q.id == questionId);
    return question ? question.text : `Pregunta ${questionId}`;
};

/**
 * Obtiene las opciones de una pregunta
 */
export const getQuestionOptions = (question) => {
    if (!question.options) return [];
    if (Array.isArray(question.options)) return question.options;
    if (typeof question.options === 'string') {
        try {
            return JSON.parse(question.options);
        } catch {
            return [];
        }
    }
    return [];
};

/**
 * Obtiene el texto del operador
 */
export const getOperatorText = (operator) => {
    const operators = {
        'equals': 'Igual a',
        'not_equals': 'Diferente de',
        'greater_than': 'Mayor que',
        'less_than': 'Menor que',
        'greater_or_equal': 'Mayor o igual que',
        'less_or_equal': 'Menor o igual que',
        'contains': 'Contiene',
        'not_contains': 'No contiene',
        'in': 'En',
        'not_in': 'No en'
    };
    return operators[operator] || operator;
};

/**
 * Formatea el valor de una condición para mostrar
 */
export const formatConditionValue = (value, questionType) => {
    if (Array.isArray(value)) {
        return value.join(', ');
    }
    if (questionType === 'boolean') {
        return value ? 'Sí' : 'No';
    }
    return value;
};

