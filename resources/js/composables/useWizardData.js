import { reactive, ref } from 'vue';
import { getNumericValue } from '../utils/formatters';

/**
 * Composable para manejar los datos del wizard
 */
export function useWizardData(initialData = null) {
    const loading = ref(false);
    const saving = ref(false);

    const formData = reactive({
        ayuda: {
            nombre_ayuda: '',
            sector: '',
            presupuesto: null,
            fecha_inicio: '',
            fecha_fin: '',
            organo_id: '',
            cuantia_usuario: 0,
            activo: true,
            slug: '',
            description: '',
            fecha_inicio_periodo: '',
            fecha_fin_periodo: ''
        },
        questionnaire: {
            name: '',
            active: true
        },
        questionnaire_specific: {
            name: '',
            active: true
        },
        questionnaire_solicitante: {
            name: '',
            active: true
        },
        questionnaire_conviviente: {
            name: '',
            active: true
        },
        preRequisitos: [],
        questions: [],
        questionConditions: [],
        questions_specific: [],
        questionConditions_specific: [],
        questions_solicitante: [],
        questionConditions_solicitante: [],
        questions_conviviente: [],
        questionConditions_conviviente: [],
        documents: [],
        documents_convivientes: [],
        eligibilityLogic: [],
        products_services: [],
        selected_product_ids: []
    });

    /**
     * Carga los datos del wizard
     */
    const loadWizardData = (wizardData) => {
        if (!wizardData) return;
        
        if (wizardData.ayuda) {
            Object.assign(formData.ayuda, wizardData.ayuda);
        }
        if (wizardData.questionnaire_specific) {
            Object.assign(formData.questionnaire_specific, wizardData.questionnaire_specific);
        }
        if (wizardData.questionnaire_solicitante) {
            Object.assign(formData.questionnaire_solicitante, wizardData.questionnaire_solicitante);
        }
        if (wizardData.questionnaire_conviviente) {
            Object.assign(formData.questionnaire_conviviente, wizardData.questionnaire_conviviente);
        }
        if (wizardData.preRequisitos) {
            formData.preRequisitos = [...wizardData.preRequisitos];
        }
        if (wizardData.questions_specific) {
            formData.questions_specific = [...wizardData.questions_specific];
        }
        if (wizardData.questionConditions_specific) {
            formData.questionConditions_specific = [...wizardData.questionConditions_specific];
        }
        if (wizardData.questions_solicitante) {
            formData.questions_solicitante = [...wizardData.questions_solicitante];
        }
        if (wizardData.questionConditions_solicitante) {
            formData.questionConditions_solicitante = [...wizardData.questionConditions_solicitante];
        }
        if (wizardData.questions_conviviente) {
            formData.questions_conviviente = [...wizardData.questions_conviviente];
        }
        if (wizardData.questionConditions_conviviente) {
            formData.questionConditions_conviviente = [...wizardData.questionConditions_conviviente];
        }
        if (wizardData.eligibilityLogic) {
            formData.eligibilityLogic = [...wizardData.eligibilityLogic];
        }
        if (wizardData.documents) {
            formData.documents = [...wizardData.documents];
        }
        if (wizardData.documents_convivientes) {
            formData.documents_convivientes = [...wizardData.documents_convivientes];
        }
        if (wizardData.products_services) {
            formData.products_services = [...wizardData.products_services];
        }
        if (wizardData.selected_product_ids) {
            formData.selected_product_ids = [...wizardData.selected_product_ids];
        }
    };

    /**
     * Prepara los datos para enviar al servidor
     */
    const prepareDataForSave = () => {
        const dataToSend = JSON.parse(JSON.stringify(formData));
        dataToSend.ayuda.presupuesto = getNumericValue(formData.ayuda.presupuesto);
        dataToSend.ayuda.cuantia_usuario = getNumericValue(formData.ayuda.cuantia_usuario);
        return dataToSend;
    };

    /**
     * Guarda el wizard
     */
    const saveWizard = async (wizardId, csrf, currentStep) => {
        if (saving.value) return;
        
        saving.value = true;
        try {
            const dataToSend = prepareDataForSave();
            
            const response = await fetch(`/admin/wizards/${wizardId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    data: dataToSend,
                    current_step: currentStep
                })
            });
            
            if (!response.ok) {
                throw new Error('Error al guardar el wizard');
            }
        } catch (error) {
            console.error('Error:', error);
            throw error;
        } finally {
            saving.value = false;
        }
    };

    /**
     * Guarda el wizard como borrador
     */
    const saveDraft = async (wizardId, csrf, currentStep, showNotification) => {
        if (saving.value) return;
        
        saving.value = true;
        try {
            const dataToSend = prepareDataForSave();

            const updateResponse = await fetch(`/admin/wizards/${wizardId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    data: dataToSend,
                    current_step: currentStep,
                    status: 'draft'
                })
            });

            if (!updateResponse.ok) {
                throw new Error('Error al guardar los datos del wizard');
            }
            
            await fetch(`/admin/wizards/${wizardId}/draft`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    current_step: currentStep
                })
            });
            
            showNotification('Borrador guardado correctamente', 'success');
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al guardar el borrador', 'error');
            throw error;
        } finally {
            saving.value = false;
        }
    };

    /**
     * Completa el wizard
     */
    const completeWizard = async (wizardId, csrf, showNotification) => {
        if (!confirm('¿Estás seguro de que quieres completar el wizard? Esta acción no se puede deshacer.')) {
            return;
        }

        if (saving.value) return;
        
        saving.value = true;
        try {
            const dataToSend = prepareDataForSave();
            
            const response = await fetch(`/admin/wizards/${wizardId}/complete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    data: dataToSend
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('¡Wizard completado correctamente!', 'success');
                setTimeout(() => {
                    window.location.href = '/admin/wizards';
                }, 2000);
            } else {
                throw new Error(result.message || 'Error al completar el wizard');
            }
        } catch (error) {
            console.error('Error al completar el wizard:', error);
            
            // Si hay un error, intentar guardar como borrador
            try {
                const dataToSend = prepareDataForSave();
                const currentStep = 1; // TODO: obtener del contexto

                const updateResponse = await fetch(`/admin/wizards/${wizardId}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        data: dataToSend,
                        current_step: currentStep,
                        status: 'draft'
                    })
                });

                if (updateResponse.ok) {
                    await fetch(`/admin/wizards/${wizardId}/draft`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            current_step: currentStep
                        })
                    });
                    
                    showNotification('Error al completar el wizard. Se ha guardado como borrador automáticamente. ' + error.message, 'warning');
                } else {
                    showNotification('Error al completar el wizard y no se pudo guardar como borrador: ' + error.message, 'error');
                }
            } catch (draftError) {
                console.error('Error al guardar como borrador:', draftError);
                showNotification('Error al completar el wizard y no se pudo guardar como borrador: ' + error.message, 'error');
            }
        } finally {
            saving.value = false;
        }
    };

    return {
        loading,
        saving,
        formData,
        loadWizardData,
        saveWizard,
        saveDraft,
        completeWizard,
        prepareDataForSave
    };
}

