import { createApp, ref } from 'vue';
import ConvivienteBuilderModal from './components/ConvivienteBuilderModal.vue';

let vueApp = null;
let modalState = null;

/**
 * Inicializa el modal Vue para convivientes con builders
 */
function initConvivienteBuilderModal() {
    const container = document.getElementById('modalConvivienteContainer');
    if (!container) {
        console.error('No se encontró el contenedor modalConvivienteContainer');
        return;
    }

    // Si ya existe una instancia, destruirla
    if (vueApp) {
        vueApp.unmount();
        vueApp = null;
        modalState = null;
    }

    // Estado reactivo
    const showModal = ref(false);
    const questionnaireId = ref(null);
    const convivienteIndex = ref(null);
    const convivienteNombre = ref(null);

    // Guardar referencia al estado para acceso externo
    modalState = {
        showModal,
        questionnaireId,
        convivienteIndex,
        convivienteNombre,
    };

    // Crear nueva instancia Vue
    vueApp = createApp({
        components: {
            ConvivienteBuilderModal,
        },
        setup() {
            return {
                showModal,
                questionnaireId,
                convivienteIndex,
                convivienteNombre,
                onSaved() {
                    // Recargar la página o actualizar la tarjeta
                    if (window.updateAyudaSolicitadaCard) {
                        const ayudaCard = document.querySelector('[data-ayuda-id]');
                        if (ayudaCard) {
                            const ayudaId = ayudaCard.getAttribute('data-ayuda-id');
                            if (ayudaId) {
                                window.updateAyudaSolicitadaCard(parseInt(ayudaId));
                            }
                        }
                    }
                },
            };
        },
        template: `
            <ConvivienteBuilderModal
                :show="showModal"
                :questionnaire-id="questionnaireId"
                :conviviente-index="convivienteIndex"
                :conviviente-nombre="convivienteNombre"
                @close="showModal = false"
                @saved="onSaved"
            />
        `,
    });

    vueApp.mount(container);

    // Exponer función global para abrir el modal
    window.openConvivienteBuilderModal = function(qId, index, nombre = null) {
        if (modalState) {
            modalState.questionnaireId.value = qId;
            modalState.convivienteIndex.value = index;
            modalState.convivienteNombre.value = nombre;
            modalState.showModal.value = true;
        }
    };
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initConvivienteBuilderModal);
} else {
    initConvivienteBuilderModal();
}

// Exportar para uso en otros archivos
export { initConvivienteBuilderModal };

