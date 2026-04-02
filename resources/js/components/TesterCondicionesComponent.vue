<template>
    <div class="space-y-6">
        <!-- Header con información -->
        <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-lg p-6 border border-orange-200">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold mr-4">
                    🔍
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Validador de Flujo de Preguntas</h3>
                    <p class="text-gray-600">Comprueba que el cuestionario tenga una lógica correcta</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="bg-white rounded-lg p-3 border">
                    <div class="font-semibold text-gray-800">Total Preguntas</div>
                    <div class="text-2xl font-bold text-blue-600">{{ questions.length }}</div>
                </div>
                <div class="bg-white rounded-lg p-3 border">
                    <div class="font-semibold text-gray-800">Total Condiciones</div>
                    <div class="text-2xl font-bold text-green-600">{{ conditions.length }}</div>
                </div>
                <div class="bg-white rounded-lg p-3 border">
                    <div class="font-semibold text-gray-800">Versión</div>
                    <div class="text-2xl font-bold" :class="versionClass">{{ questionnaireVersion }}</div>
                </div>
            </div>
        </div>

        <!-- Botón de validación -->
        <div class="text-center">
            <button 
                @click="validateFlow"
                :disabled="validating"
                class="bg-orange-600 hover:bg-orange-700 disabled:bg-gray-400 text-white px-8 py-3 rounded-lg transition-colors flex items-center gap-3 mx-auto">
                <i v-if="validating" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-search"></i>
                {{ validating ? 'Validando...' : 'Validar Flujo' }}
            </button>
        </div>

        <!-- Resultados de validación -->
        <div v-if="validationResult" class="space-y-4">
            <!-- Resumen general -->
            <div class="bg-white rounded-lg p-6 border" :class="summaryClass">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold mr-3" :class="summaryIconClass">
                        {{ summaryIcon }}
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-gray-800">{{ validationResult.summary }}</h4>
                        <p class="text-gray-600">{{ validationResult.description }}</p>
                    </div>
                </div>
            </div>

            <!-- Detalles de validación -->
            <div v-if="validationResult.details && validationResult.details.length > 0" class="bg-white rounded-lg p-6 border">
                <h4 class="text-lg font-bold text-gray-800 mb-4">Detalles de la Validación</h4>
                <div class="space-y-3">
                    <div 
                        v-for="(detail, index) in validationResult.details" 
                        :key="index"
                        class="flex items-start p-3 rounded-lg border"
                        :class="getDetailClass(detail.type)">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-sm font-bold mr-3 flex-shrink-0" :class="getDetailIconClass(detail.type)">
                            {{ getDetailIcon(detail.type) }}
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-800">{{ detail.title }}</div>
                            <div class="text-sm text-gray-600">{{ detail.message }}</div>
                            <div v-if="detail.items && detail.items.length > 0" class="mt-2">
                                <div class="text-xs font-medium text-gray-500 mb-1">Elementos afectados:</div>
                                <div class="text-xs text-gray-600">
                                    {{ detail.items.join(', ') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico de flujo (opcional) -->
            <div v-if="validationResult.flowDiagram" class="bg-white rounded-lg p-6 border">
                <h4 class="text-lg font-bold text-gray-800 mb-4">Diagrama de Flujo</h4>
                <div class="bg-gray-50 rounded-lg p-4 text-center">
                    <div class="text-gray-500">Visualización del flujo del cuestionario</div>
                    <!-- Aquí se podría integrar un diagrama visual -->
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TesterCondicionesComponent',
    props: {
        ayudaId: {
            type: Number,
            required: true
        },
        csrf: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            questions: [],
            conditions: [],
            questionnaireVersion: null,
            validating: false,
            validationResult: null
        }
    },
    computed: {
        versionClass() {
            return this.questionnaireVersion === 'OLD' 
                ? 'text-red-600' 
                : 'text-green-600';
        },
        summaryClass() {
            if (!this.validationResult) return '';
            return this.validationResult.isValid 
                ? 'border-green-200 bg-green-50' 
                : 'border-red-200 bg-red-50';
        },
        summaryIconClass() {
            if (!this.validationResult) return '';
            return this.validationResult.isValid 
                ? 'bg-green-500' 
                : 'bg-red-500';
        },
        summaryIcon() {
            if (!this.validationResult) return '';
            return this.validationResult.isValid ? '✅' : '❌';
        }
    },
    mounted() {
        this.fetchData();
    },
    methods: {
        async fetchData() {
            try {
                const response = await fetch(`/admin/ayudas/${this.ayudaId}/questionnaire-data`, {
                    headers: {
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.questions = data.questions || [];
                    this.conditions = data.conditions || [];
                    this.questionnaireVersion = this.detectVersion();
                }
            } catch (error) {
                console.error('Error fetching questionnaire data:', error);
            }
        },

        detectVersion() {
            if (this.conditions.length === 0) return 'UNKNOWN';
            
            const firstCondition = this.conditions[0];
            if (firstCondition.condition !== null && firstCondition.condition !== undefined) {
                return 'OLD';
            }
            if (firstCondition.condition === null || firstCondition.condition === undefined) {
                return 'NEW';
            }
            
            return 'UNKNOWN';
        },

        async validateFlow() {
            this.validating = true;
            this.validationResult = null;

            try {
                const response = await fetch(`/admin/ayudas/${this.ayudaId}/validate-flow`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrf,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        questions: this.questions,
                        conditions: this.conditions,
                        version: this.questionnaireVersion
                    })
                });

                if (response.ok) {
                    this.validationResult = await response.json();
                } else {
                    throw new Error('Error en la validación');
                }
            } catch (error) {
                console.error('Error validating flow:', error);
                this.validationResult = {
                    isValid: false,
                    summary: 'Error en la validación',
                    description: 'No se pudo completar la validación del flujo',
                    details: [{
                        type: 'error',
                        title: 'Error del sistema',
                        message: 'Ocurrió un error durante la validación'
                    }]
                };
            } finally {
                this.validating = false;
            }
        },

        getDetailClass(type) {
            switch (type) {
                case 'error': return 'border-red-200 bg-red-50';
                case 'warning': return 'border-yellow-200 bg-yellow-50';
                case 'success': return 'border-green-200 bg-green-50';
                case 'info': return 'border-blue-200 bg-blue-50';
                default: return 'border-gray-200 bg-gray-50';
            }
        },

        getDetailIconClass(type) {
            switch (type) {
                case 'error': return 'bg-red-500';
                case 'warning': return 'bg-yellow-500';
                case 'success': return 'bg-green-500';
                case 'info': return 'bg-blue-500';
                default: return 'bg-gray-500';
            }
        },

        getDetailIcon(type) {
            switch (type) {
                case 'error': return '❌';
                case 'warning': return '⚠️';
                case 'success': return '✅';
                case 'info': return 'ℹ️';
                default: return '•';
            }
        }
    }
}
</script> 