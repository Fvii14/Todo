import { getNumericValue } from '../utils/formatters';

/**
 * Composable para validar los pasos del wizard
 */
export function useWizardValidation(formData, showNotification) {
    const validateCurrentStep = (currentStep) => {
        switch (currentStep) {
            case 1:
                if (!formData.ayuda.nombre_ayuda?.trim()) {
                    showNotification('El nombre de la ayuda es obligatorio', 'error');
                    return false;
                }
                if (!formData.ayuda.sector) {
                    showNotification('El sector es obligatorio', 'error');
                    return false;
                }
                if (!formData.ayuda.organo_id) {
                    showNotification('El órgano es obligatorio', 'error');
                    return false;
                }
                if (!getNumericValue(formData.ayuda.presupuesto)) {
                    showNotification('El presupuesto es obligatorio', 'error');
                    return false;
                }
                if (!getNumericValue(formData.ayuda.cuantia_usuario)) {
                    showNotification('La cuantía por usuario es obligatoria', 'error');
                    return false;
                }
                if (!formData.ayuda.fecha_inicio) {
                    showNotification('La fecha de inicio es obligatoria', 'error');
                    return false;
                }
                break;
                
            case 2:
                if (!formData.questionnaire_specific.name?.trim()) {
                    showNotification('El nombre del cuestionario específico es obligatorio', 'error');
                    return false;
                }
                break;
                
            case 3:
                break;
                
            case 4:
                if (formData.questions_specific.length === 0) {
                    showNotification('Debes añadir al menos una pregunta', 'error');
                    return false;
                }
                break;
                
            case 5:
                break;
                
            case 6:
                if (!formData.questionnaire_solicitante.name?.trim()) {
                    showNotification('El nombre del cuestionario solicitante es obligatorio', 'error');
                    return false;
                }
                break;
                
            case 7:
                break;
                
            case 8:
                break;
                
            case 9:
                break;
                
            case 10:
                break;
                
            case 11:
                break;
                
            case 12:
                break;
                
            case 13:
                break;
        }
        
        return true;
    };

    return {
        validateCurrentStep
    };
}

