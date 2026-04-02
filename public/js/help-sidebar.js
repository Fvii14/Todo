// Componente Vue para el sidebar de ayuda
const { createApp, ref, onMounted, onUnmounted } = Vue;

const HelpSidebar = {
    template: `
        <div>
            <button 
                @click="toggleSidebar"
                class="fixed top-1/2 right-0 transform -translate-y-1/2 bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-l-lg shadow-lg z-50 help-button focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                :title="isOpen ? 'Cerrar ayuda' : 'Abrir ayuda'"
            >
                <i :class="isOpen ? 'bx bx-x' : 'bx bx-help-circle'" class="text-xl"></i>
            </button>

            <div 
                v-if="isOpen" 
                @click="closeSidebar"
                class="fixed inset-0 bg-black bg-opacity-50 z-40"
            ></div>

            <div 
                :class="['help-sidebar fixed top-0 right-0 h-full w-96 bg-white shadow-2xl z-50 overflow-y-auto', isOpen ? 'open' : 'closed']"
            >
                <div class="bg-blue-600 text-white p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <i class="bx bx-help-circle text-2xl"></i>
                            <h2 class="text-xl font-bold">{{ title }}</h2>
                        </div>
                        <button 
                            @click="closeSidebar"
                            class="text-white hover:text-gray-200 transition-colors"
                        >
                            <i class="bx bx-x text-2xl"></i>
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <div v-if="mainDescription">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">
                            {{ mainTitle }}
                        </h3>
                        <p class="text-gray-600 leading-relaxed mb-4">
                            {{ mainDescription }}
                        </p>
                    </div>

                    <div v-if="features && features.length > 0">
                        <h4 class="text-md font-semibold text-gray-700 mb-2">
                            Funcionalidades principales
                        </h4>
                        <ul class="space-y-2 text-gray-600">
                            <li 
                                v-for="(feature, index) in features" 
                                :key="index"
                                class="flex items-start space-x-2"
                            >
                                <i class="bx bx-check-circle text-green-500 mt-0.5"></i>
                                <span>{{ feature }}</span>
                            </li>
                        </ul>
                    </div>

                    <div v-if="steps && steps.length > 0">
                        <h4 class="text-md font-semibold text-gray-700 mb-2">
                            Cómo usar esta sección
                        </h4>
                        <div class="space-y-3">
                            <div 
                                v-for="(step, index) in steps" 
                                :key="index"
                                class="bg-gray-50 p-3 rounded-lg"
                            >
                                <p class="text-sm text-gray-600">
                                    <strong>Paso {{ index + 1 }}:</strong> {{ step }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="additionalInfo">
                        <h4 class="text-md font-semibold text-gray-700 mb-2">
                            Información adicional
                        </h4>
                        <div 
                            v-if="isHtml(additionalInfo)" 
                            class="text-gray-600 text-sm leading-relaxed"
                            v-html="additionalInfo"
                        ></div>
                        <p 
                            v-else 
                            class="text-gray-600 text-sm leading-relaxed"
                        >
                            {{ additionalInfo }}
                        </p>
                    </div>

                    <div v-if="importantNote" class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <i class="bx bx-info-circle text-yellow-400 text-xl mr-2"></i>
                            <div>
                                <h5 class="text-sm font-semibold text-yellow-800 mb-1">
                                    Nota importante
                                </h5>
                                <p class="text-sm text-yellow-700">
                                    {{ importantNote }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <slot></slot>
                </div>
            </div>
        </div>
    `,
    props: {
        title: {
            type: String,
            default: 'Ayuda'
        },
        mainTitle: {
            type: String,
            default: ''
        },
        mainDescription: {
            type: String,
            default: ''
        },
        features: {
            type: Array,
            default: () => []
        },
        steps: {
            type: Array,
            default: () => []
        },
        additionalInfo: {
            type: String,
            default: ''
        },
        importantNote: {
            type: String,
            default: ''
        }
    },
    setup() {
        const isOpen = ref(false);

        const toggleSidebar = () => {
            isOpen.value = !isOpen.value;
        };

        const closeSidebar = () => {
            isOpen.value = false;
        };

        const isHtml = (text) => {
            if (!text) return false;
            const htmlRegex = /<[^>]*>/;
            return htmlRegex.test(text);
        };

        const handleKeydown = (e) => {
            if (e.key === 'Escape' && isOpen.value) {
                closeSidebar();
            }
        };

        onMounted(() => {
            document.addEventListener('keydown', handleKeydown);
        });

        onUnmounted(() => {
            document.removeEventListener('keydown', handleKeydown);
        });

        return {
            isOpen,
            toggleSidebar,
            closeSidebar,
            isHtml
        };
    }
};

function initHelpSidebar(containerId = 'help-sidebar-app') {
    const app = createApp({
        components: {
            'help-sidebar': HelpSidebar
        }
    });

    app.mount(`#${containerId}`);
}

window.HelpSidebar = {
    component: HelpSidebar,
    init: initHelpSidebar
}; 