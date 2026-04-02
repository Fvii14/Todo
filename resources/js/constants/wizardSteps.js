export const WIZARD_STEPS = [
    { id: 1, title: 'Información de la Ayuda', icon: 'fas fa-hands-helping' },
    { id: 2, title: 'Cuestionario', icon: 'fas fa-clipboard-list' },
    { id: 3, title: 'Pre-requisitos de la ayuda', icon: 'fas fa-cogs' },
    { id: 4, title: 'Preguntas Formulario Específico', icon: 'fas fa-question-circle' },
    { id: 5, title: 'Condiciones Formulario Específico', icon: 'fas fa-code-branch' },
    { id: 6, title: 'Preguntas Formulario Solicitante', icon: 'fas fa-question-circle' },
    { id: 7, title: 'Condiciones Formulario Solicitante', icon: 'fas fa-code-branch' },
    { id: 8, title: 'Preguntas Formulario Conviviente', icon: 'fas fa-question-circle' },
    { id: 9, title: 'Condiciones Formulario Conviviente', icon: 'fas fa-code-branch' },
    { id: 10, title: 'Documentos de la ayuda', icon: 'fas fa-file-alt' },
    { id: 11, title: 'Lógica de Elegibilidad', icon: 'fas fa-brain' },
    { id: 12, title: 'Productos y servicios', icon: 'fas fa-question-circle' },
    { id: 13, title: 'Revisión', icon: 'fas fa-check-circle' }
];

export const TOTAL_STEPS = WIZARD_STEPS.length;

export const getStepTitle = (stepNumber) => {
    const step = WIZARD_STEPS.find(s => s.id === stepNumber);
    return step ? step.title : `Paso ${stepNumber}`;
};

export const getStepData = (stepNumber) => {
    return WIZARD_STEPS.find(s => s.id === stepNumber);
};

