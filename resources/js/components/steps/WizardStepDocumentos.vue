<template>
    <div>
        <h3 class="text-xl font-semibold text-gray-900 mb-6">
            <i class="fas fa-file-alt text-blue-600 mr-2"></i>
            Documentos de la Ayuda
        </h3>
        
        <div class="space-y-8">
            <!-- Documentos Generales -->
            <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                <h4 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                    <i class="fas fa-file text-blue-600 mr-2"></i>
                    Documentos Generales de la Ayuda
                </h4>
                <p class="text-gray-600 mb-4 text-sm">
                    Configura los documentos requeridos para esta ayuda. Los usuarios deberán subir estos documentos para completar su solicitud.
                </p>
                
                <div class="space-y-4">
                    <div v-for="(doc, index) in documents" :key="index" class="bg-white p-4 rounded-lg border">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Selecciona un documento <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    :value="doc.document_id"
                                    @change="updateDocument(index, 'document_id', $event.target.value)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option :value="null">Seleccionar documento</option>
                                    <option v-for="document in getAvailableDocuments(index, 'general')" :key="document.id" :value="Number(document.id)">
                                        {{ document.name }}
                                    </option>
                                </select>
                            </div>
                            <button 
                                @click="$emit('remove-document', index)"
                                class="p-2 text-red-600 hover:text-red-700 flex-shrink-0"
                                title="Eliminar documento"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="mt-3">
                            <label class="flex items-center">
                                <input 
                                    :checked="doc.es_obligatorio"
                                    @change="updateDocument(index, 'es_obligatorio', true)"
                                    type="radio"
                                    class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="text-sm text-gray-700 mr-4">Obligatorio</span>
                            </label>
                            <label class="flex items-center">
                                <input 
                                    :checked="!doc.es_obligatorio"
                                    @change="updateDocument(index, 'es_obligatorio', false)"
                                    type="radio"
                                    class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="text-sm text-gray-700">Opcional</span>
                            </label>
                        </div>
                        
                        <!-- Condiciones para documentos opcionales -->
                        <div v-if="!doc.es_obligatorio" class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-medium text-gray-700">
                                    <i class="fas fa-code-branch text-blue-500 mr-1"></i>
                                    Requisitos para mostrar este documento
                                </label>
                                <button 
                                    @click="$emit('open-conditions', index, 'general')"
                                    type="button"
                                    class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors"
                                >
                                    <i class="fas fa-cog mr-1"></i>Configurar
                                </button>
                            </div>
                            <div v-if="getDocumentRequirements(doc).length > 0" class="text-xs text-gray-600 bg-blue-50 p-2 rounded">
                                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                                {{ getDocumentRequirements(doc).length }} requisito(s) configurado(s)
                            </div>
                            <div v-else class="text-xs text-gray-500 italic">
                                Sin requisitos. El documento aparecerá siempre.
                            </div>
                        </div>
                    </div>
                    
                    <button 
                        @click="$emit('add-document')"
                        class="w-full px-4 py-2 border-2 border-dashed border-blue-300 rounded-lg text-blue-600 hover:border-blue-500 hover:bg-blue-50 transition-colors"
                    >
                        <i class="fas fa-plus mr-2"></i>Añadir documento general
                    </button>
                </div>
            </div>

            <!-- Documentos de Convivientes -->
            <div class="bg-purple-50 rounded-lg p-6 border border-purple-200">
                <h4 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                    <i class="fas fa-user-tag text-purple-600 mr-2"></i>
                    Documentos de Convivientes
                </h4>
                <p class="text-gray-600 mb-4 text-sm">
                    Configura los documentos requeridos específicamente para los convivientes. Estos documentos se solicitarán a cada conviviente de la unidad de convivencia.
                </p>
                
                <div class="space-y-4">
                    <div v-for="(doc, index) in documentsConvivientes" :key="index" class="bg-white p-4 rounded-lg border">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Selecciona un documento <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    :value="doc.document_id"
                                    @change="updateDocumentConviviente(index, 'document_id', $event.target.value)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                >
                                    <option :value="null">Seleccionar documento</option>
                                    <option v-for="document in getAvailableDocuments(index, 'conviviente')" :key="document.id" :value="Number(document.id)">
                                        {{ document.name }}
                                    </option>
                                </select>
                            </div>
                            <button 
                                @click="$emit('remove-document-conviviente', index)"
                                class="p-2 text-red-600 hover:text-red-700 flex-shrink-0"
                                title="Eliminar documento"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="mt-3">
                            <label class="flex items-center">
                                <input 
                                    :checked="doc.es_obligatorio"
                                    @change="updateDocumentConviviente(index, 'es_obligatorio', true)"
                                    type="radio"
                                    class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                >
                                <span class="text-sm text-gray-700 mr-4">Obligatorio</span>
                            </label>
                            <label class="flex items-center">
                                <input 
                                    :checked="!doc.es_obligatorio"
                                    @change="updateDocumentConviviente(index, 'es_obligatorio', false)"
                                    type="radio"
                                    class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                >
                                <span class="text-sm text-gray-700">Opcional</span>
                            </label>
                        </div>
                        
                        <!-- Condiciones para documentos opcionales -->
                        <div v-if="!doc.es_obligatorio" class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-medium text-gray-700">
                                    <i class="fas fa-code-branch text-purple-500 mr-1"></i>
                                    Requisitos para mostrar este documento
                                </label>
                                <button 
                                    @click="$emit('open-conditions', index, 'conviviente')"
                                    type="button"
                                    class="text-xs px-3 py-1 bg-purple-100 text-purple-700 rounded hover:bg-purple-200 transition-colors"
                                >
                                    <i class="fas fa-cog mr-1"></i>Configurar
                                </button>
                            </div>
                            <div v-if="getDocumentRequirements(doc).length > 0" class="text-xs text-gray-600 bg-purple-50 p-2 rounded">
                                <i class="fas fa-info-circle text-purple-500 mr-1"></i>
                                {{ getDocumentRequirements(doc).length }} requisito(s) configurado(s)
                            </div>
                            <div v-else class="text-xs text-gray-500 italic">
                                Sin requisitos. El documento aparecerá siempre.
                            </div>
                        </div>
                    </div>
                    
                    <button 
                        @click="$emit('add-document-conviviente')"
                        class="w-full px-4 py-2 border-2 border-dashed border-purple-300 rounded-lg text-purple-600 hover:border-purple-500 hover:bg-purple-50 transition-colors"
                    >
                        <i class="fas fa-plus mr-2"></i>Añadir documento de conviviente
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'WizardStepDocumentos',
    props: {
        documents: {
            type: Array,
            default: () => []
        },
        documentsConvivientes: {
            type: Array,
            default: () => []
        },
        allDocuments: {
            type: Array,
            default: () => []
        }
    },
    emits: ['add-document', 'remove-document', 'add-document-conviviente', 'remove-document-conviviente', 'update-document', 'update-document-conviviente', 'open-conditions'],
    setup(props, { emit }) {
        const getAvailableDocuments = (index, type) => {
            const currentDoc = type === 'general' 
                ? props.documents[index] 
                : props.documentsConvivientes[index];
            const currentDocId = currentDoc ? Number(currentDoc.document_id) : null;
            
            const selectedGeneralIds = props.documents
                .map((doc, idx) => idx !== index ? Number(doc.document_id) : null)
                .filter(id => id && !isNaN(id));
            const selectedConvivienteIds = props.documentsConvivientes
                .map((doc, idx) => idx !== index || type !== 'conviviente' ? Number(doc.document_id) : null)
                .filter(id => id && !isNaN(id));
            const allSelectedIds = [...selectedGeneralIds, ...selectedConvivienteIds];
            
            return props.allDocuments.filter(doc => {
                const docId = Number(doc.id);
                return !allSelectedIds.includes(docId) || docId === currentDocId;
            });
        };

        const updateDocument = (index, field, value) => {
            emit('update-document', { index, field, value, type: 'general' });
        };

        const updateDocumentConviviente = (index, field, value) => {
            emit('update-document-conviviente', { index, field, value, type: 'conviviente' });
        };

        const getDocumentRequirements = (doc) => {
            return doc.conditions || [];
        };

        return {
            getAvailableDocuments,
            updateDocument,
            updateDocumentConviviente,
            getDocumentRequirements
        };
    }
};
</script>

