<template>
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">{{ wizard.title || 'Wizard de Ayuda' }}</h2>
                    <p class="text-blue-100 mt-1">
                        <i :class="currentStepData.icon" class="mr-2"></i>
                        {{ currentStepData.title }} - Paso {{ currentStep }} de {{ totalSteps }}
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <button
                        @click="handleSaveDraft"
                        :disabled="saving"
                        class="px-4 py-2 bg-blue-700 hover:bg-blue-800 rounded-md text-sm font-medium transition-colors disabled:opacity-50"
                    >
                        <i class="fas fa-save mr-2"></i>
                        {{ saving ? 'Guardando...' : 'Guardar borrador' }}
                    </button>
                </div>
            </div>

            <div class="mt-4">
                <div class="w-full bg-blue-700 rounded-full h-2">
                    <div
                        class="bg-green-400 h-2 rounded-full transition-all duration-500 ease-out"
                        :style="{ width: progressPercentage + '%' }"
                    ></div>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 p-4 border-b">
            <div class="overflow-x-auto">
                <div class="flex flex-wrap gap-2 justify-center">
                    <button
                        v-for="step in stepsArray"
                        :key="step"
                        @click="goToStep(step)"
                        :class="[
                            'px-3 py-2 rounded-md text-xs md:text-sm font-medium transition-colors whitespace-nowrap flex-shrink-0',
                            currentStep === step
                                ? 'bg-blue-600 text-white shadow-md'
                                : 'bg-white text-gray-600 hover:bg-gray-100',
                        ]"
                        :title="getStepTitle(step)"
                    >
                        <i
                            class="fas fa-circle mr-1 md:mr-2"
                            :class="currentStep >= step ? 'text-green-400' : 'text-gray-400'"
                        ></i>
                        <span class="hidden sm:inline">{{ step }} {{ getStepTitle(step) }}</span>
                        <span class="sm:hidden">{{ step }}</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div v-if="loading" class="text-center py-12">
                <div
                    class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"
                ></div>
                <p class="mt-4 text-gray-600">Cargando...</p>
            </div>

            <div v-else>
                <!-- PASO 1 -->
                <div v-if="Number(currentStep) === 1">
                    <WizardStepInformacionAyuda
                        :form-data="formData"
                        :organos="organos"
                        :sectores="sectores"
                        @update:form-data="updateFormData"
                    />
                </div>
                <!-- PASO 2 -->
                <div v-if="Number(currentStep) === 2">
                    <WizardStepCuestionarioEspecifico
                        :form-data="formData"
                        @update:form-data="updateFormData"
                    />
                </div>
                <!-- PASO 3 -->
                <div v-if="Number(currentStep) === 3">
                    <WizardStepPreRequisites
                        :csrf="csrf"
                        :questionnaire-name="formData.questionnaire.name"
                        :questionnaire-active="formData.questionnaire.active"
                        :pre-requisitos="formData.preRequisitos"
                        @update:pre-requisitos="updatePreRequisitos"
                    />
                </div>
                <!-- PASO 4 -->
                <div v-if="Number(currentStep) === 4">
                    <WizardStepPreguntasEspecifico
                        ref="preguntasEspecificoRef"
                        :questions="formData.questions_specific"
                        :question-types="questionTypes"
                        :available-categories="availableCategories"
                        :available-purposes="availablePurposes"
                        :csrf="csrf"
                        :is-dragging="isDragging"
                        :dragged-index="draggedIndex"
                        :drag-over-index="dragOverIndex"
                        :multi-select-mode="multiSelectMode"
                        :selected-questions="selectedQuestions"
                        @create-question="showCreateQuestionModal = true"
                        @edit-question="openEditQuestionModal"
                        @remove-question="removeQuestionSpecific"
                        @add-question="addQuestionToSpecific"
                        @drag-start="handleDragStartSpecific"
                        @drag-end="handleDragEnd"
                        @drop="handleDropSpecific"
                        @question-click="handleQuestionClickSpecific"
                        @drag-over="dragOverIndex = $event"
                        @drag-leave="dragOverIndex = null"
                    />
                </div>
                <!-- PASO 5 -->
                <div v-if="Number(currentStep) === 5">
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">
                            <i class="fas fa-code-branch text-blue-600 mr-2"></i>
                            Condiciones del Formulario Específico
                        </h3>
                        <p class="text-gray-600">
                            Define los saltos condicionales entre preguntas del formulario
                            específico. Arrastra las preguntas para reorganizarlas y crea conexiones
                            para establecer la lógica del flujo.
                        </p>
                    </div>

                    <WizardStepConditions
                        :questions="formData.questions_specific"
                        :conditions="formData.questionConditions_specific"
                        :csrf="csrf"
                        @update:conditions="updateConditionsSpecific"
                    />
                </div>
                <!-- PASO 6 -->
                <div v-if="Number(currentStep) === 6">
                    <WizardStepPreguntasSolicitante
                        :form-data="formData"
                        :questions="formData.questions_solicitante"
                        :question-types="questionTypes"
                        :available-categories="availableCategories"
                        :available-purposes="availablePurposes"
                        :csrf="csrf"
                        :is-dragging="isDragging"
                        :dragged-index="draggedIndex"
                        :drag-over-index="dragOverIndex"
                        :multi-select-mode="multiSelectMode"
                        :selected-questions="selectedQuestions"
                        @update:form-data="updateFormData"
                        @create-question="showCreateQuestionModal = true"
                        @edit-question="openEditQuestionModal"
                        @remove-question="removeQuestionSolicitante"
                        @add-question="addQuestionToSolicitante"
                        @drag-start="handleDragStartSolicitante"
                        @drag-end="handleDragEnd"
                        @drop="handleDropSolicitante"
                        @question-click="handleQuestionClickSolicitante"
                    />
                </div>
                <!-- PASO 7 -->
                <div v-if="Number(currentStep) === 7">
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                    <i class="fas fa-code-branch text-blue-600 mr-2"></i>
                                    Condiciones del Formulario Solicitante
                                </h3>
                                <p class="text-gray-600">
                                    Define los saltos condicionales entre preguntas del formulario
                                    del solicitante.
                                </p>
                            </div>
                            <button
                                @click="showTestModalSolicitante = true"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2"
                                :disabled="
                                    !formData.questions_solicitante ||
                                    formData.questions_solicitante.length === 0
                                "
                            >
                                <i class="fas fa-vial"></i>
                                Probar Condiciones
                            </button>
                        </div>
                    </div>

                    <WizardStepConditions
                        :questions="formData.questions_solicitante"
                        :conditions="formData.questionConditions_solicitante"
                        :csrf="csrf"
                        @update:conditions="updateConditionsSolicitante"
                    />
                </div>

                <!-- PASO 8 -->
                <div v-if="Number(currentStep) === 8">
                    <WizardStepPreguntasConviviente
                        :questions="formData.questions_conviviente"
                        :available-categories="availableCategories"
                        :available-purposes="availablePurposes"
                        :question-types="questionTypes"
                        :csrf="csrf"
                        @update:questions="updateQuestionsConviviente"
                        @create-question="showCreateQuestionModal = true"
                        @edit-question="openEditQuestionModal"
                    />
                </div>

                <!-- PASO 9 -->
                <div v-if="Number(currentStep) === 9">
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                    <i class="fas fa-code-branch text-blue-600 mr-2"></i>
                                    Condiciones del Formulario Conviviente
                                </h3>
                                <p class="text-gray-600">
                                    Define los saltos condicionales entre preguntas del formulario
                                    del conviviente.
                                </p>
                            </div>
                            <button
                                @click="showTestModalConviviente = true"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2"
                                :disabled="
                                    !formData.questions_conviviente ||
                                    formData.questions_conviviente.length === 0
                                "
                            >
                                <i class="fas fa-vial"></i>
                                Probar Condiciones
                            </button>
                        </div>
                    </div>

                    <WizardStepConditions
                        :questions="formData.questions_conviviente"
                        :conditions="formData.questionConditions_conviviente"
                        :csrf="csrf"
                        @update:conditions="updateConditionsConviviente"
                    />
                </div>

                <!-- PASO 10 -->
                <div v-if="Number(currentStep) === 10">
                    <WizardStepDocumentos
                        :documents="formData.documents"
                        :documents-convivientes="formData.documents_convivientes"
                        :all-documents="allDocuments"
                        @add-document="addDocument"
                        @remove-document="removeDocument"
                        @add-document-conviviente="addDocumentConviviente"
                        @remove-document-conviviente="removeDocumentConviviente"
                        @update-document="updateDocument"
                        @update-document-conviviente="updateDocumentConviviente"
                        @open-conditions="openDocumentConditionsModal"
                    />
                </div>

                <!-- PASO 11 -->
                <div v-if="Number(currentStep) === 11">
                    <WizardStepEligibility
                        :questions="formData.questions"
                        :all-questions="allQuestions"
                        :csrf="csrf"
                        :data="formData"
                        @update:requirements="updateEligibilityRequirements"
                        @save-requirements="saveEligibilityRequirements"
                    />
                </div>

                <!-- PASO 12 -->
                <div v-if="Number(currentStep) === 12">
                    <ProductCarousel
                        :selected-product-ids="formData.selected_product_ids || []"
                        :csrf="csrf"
                        @update:selected-product-ids="updateSelectedProducts"
                    />
                </div>

                <!-- PASO 13 -->
                <div v-if="Number(currentStep) === 13">
                    <WizardStepRevision
                        :form-data="formData"
                        :organos="organos"
                        :question-types="questionTypes"
                    />
                </div>
            </div>
        </div>

        <div class="bg-gray-50 px-6 py-4">
            <div class="flex justify-between items-center">
                <button
                    @click="previousStep"
                    :disabled="currentStep === 1 || saving"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <i class="fas fa-arrow-left mr-2"></i>Anterior
                </button>

                <div class="flex space-x-3">
                    <button
                        @click="handleSaveDraft"
                        :disabled="saving"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 disabled:opacity-50 transition-colors"
                    >
                        <i class="fas fa-save mr-2"></i>
                        {{ saving ? 'Guardando...' : 'Guardar borrador' }}
                    </button>

                    <button
                        v-if="currentStep < totalSteps"
                        @click="nextStep"
                        :disabled="saving"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 transition-colors"
                    >
                        Siguiente<i class="fas fa-arrow-right ml-2"></i>
                    </button>

                    <button
                        v-if="currentStep === totalSteps"
                        @click="handleCompleteWizard"
                        :disabled="saving"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 transition-colors"
                    >
                        <i class="fas fa-check mr-2"></i>Completar wizard
                    </button>
                </div>
            </div>
        </div>
    </div>

    <CreateQuestionModal
        :show="showCreateQuestionModal"
        :question-types="questionTypes"
        :available-categories="availableCategories"
        :available-purposes="availablePurposes"
        :creating="creatingQuestion"
        @close="showCreateQuestionModal = false"
        @create="handleCreateQuestion"
    />

    <EditQuestionModal
        :show="showEditQuestionModal"
        :question="editingQuestionData.id ? editingQuestionData : null"
        :question-types="questionTypes"
        :available-categories="availableCategories"
        :available-purposes="availablePurposes"
        :updating="editingQuestion"
        @close="closeEditQuestionModal"
        @update="handleUpdateQuestion"
    />

    <!-- Modal de condiciones de documentos -->
    <div
        v-if="showDocumentConditionsModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div class="bg-white rounded-lg p-6 w-full max-w-5xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-code-branch mr-2"></i>
                    Condiciones para mostrar documento:
                    <span class="text-blue-600">{{ currentEditingDocumentName }}</span>
                </h3>
                <button
                    @click="closeDocumentConditionsModal"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    Este documento solo aparecerá en Ayudas Solicitadas cuando se cumplan los
                    requisitos configurados. Puedes crear requisitos simples o grupos de requisitos.
                </p>
            </div>

            <!-- Formulario para añadir requisitos -->
            <div class="mb-6 bg-white rounded-xl border border-gray-200 shadow-sm">
                <div
                    class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-bold"
                        >
                            +
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Añadir nuevo requisito</h4>
                            <p class="text-sm text-gray-600">
                                Configura las condiciones para mostrar este documento
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="mb-6">
                        <h5 class="text-sm font-medium text-gray-700 mb-4 flex items-center gap-2">
                            <i class="fas fa-cog text-blue-500"></i>
                            Configuración básica
                        </h5>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700"
                                    >Tipo de requisito</label
                                >
                                <select
                                    v-model="newDocumentRequirement.type"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                >
                                    <option value="simple">Requisito simple (una pregunta)</option>
                                    <option value="group">
                                        Grupo de requisitos (múltiples preguntas)
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Requisito simple -->
                    <div v-if="newDocumentRequirement.type === 'simple'" class="space-y-6">
                        <div>
                            <h5
                                class="text-sm font-medium text-gray-700 mb-4 flex items-center gap-2"
                            >
                                <i class="fas fa-list-ul text-green-500"></i>
                                Requisito simple
                            </h5>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2"
                                    >Descripción del requisito</label
                                >
                                <input
                                    v-model="newDocumentRequirement.description"
                                    placeholder="Ej: Tener ingresos superiores a 1000€"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                />
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2"
                                    >Pregunta a evaluar</label
                                >
                                <div class="relative dropdown-container">
                                    <div
                                        @click="toggleDocumentQuestionSearch('simple')"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg cursor-pointer bg-white flex items-center justify-between hover:border-blue-400 transition-colors"
                                    >
                                        <span
                                            v-if="newDocumentRequirement.question_id"
                                            class="text-gray-900 truncate"
                                        >
                                            {{
                                                getDocumentQuestionText(
                                                    newDocumentRequirement.question_id,
                                                )
                                            }}
                                        </span>
                                        <span v-else class="text-gray-500"
                                            >Selecciona una pregunta</span
                                        >
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>

                                    <div
                                        v-if="showDocumentQuestionSearch === 'simple'"
                                        class="dropdown-menu"
                                    >
                                        <div class="p-3 border-b border-gray-200 bg-gray-50">
                                            <input
                                                v-model="documentQuestionSearchTerm"
                                                placeholder="Buscar pregunta..."
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                @input="filterDocumentQuestions"
                                            />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto">
                                            <div
                                                v-for="question in filteredDocumentQuestions"
                                                :key="question.id"
                                                @click="selectDocumentQuestionForNew(question.id)"
                                                class="px-3 py-3 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-100 last:border-b-0 transition-colors"
                                            >
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1 min-w-0">
                                                        <div
                                                            class="font-medium text-gray-900 truncate"
                                                        >
                                                            {{ question.text }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 mt-1">
                                                            <span
                                                                class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-600"
                                                            >
                                                                {{ question.type }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                v-if="filteredDocumentQuestions.length === 0"
                                                class="px-3 py-4 text-sm text-gray-500 text-center"
                                            >
                                                No se encontraron preguntas
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2"
                                        >Operador</label
                                    >
                                    <select
                                        v-model="newDocumentRequirement.operator"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                    >
                                        <option
                                            v-for="op in getDocumentAvailableOperatorsForNew()"
                                            :key="op.value"
                                            :value="op.value"
                                        >
                                            {{ op.label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2"
                                        >Valor esperado</label
                                    >

                                    <!-- Preguntas de tipo fecha: permitir edad mínima / máxima / rango como en elegibilidad -->
                                    <div
                                        v-if="getDocumentQuestionTypeForNew() === 'date'"
                                        class="space-y-3"
                                    >
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-2"
                                                >Tipo de valor</label
                                            >
                                            <div class="grid grid-cols-2 gap-2">
                                                <label
                                                    class="flex items-center p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50"
                                                >
                                                    <input
                                                        v-model="newDocumentRequirement.valueType"
                                                        type="radio"
                                                        value="exact"
                                                        name="docValueTypeSimple"
                                                        class="mr-2"
                                                    />
                                                    <span class="text-sm">Fecha exacta</span>
                                                </label>
                                                <label
                                                    class="flex items-center p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50"
                                                >
                                                    <input
                                                        v-model="newDocumentRequirement.valueType"
                                                        type="radio"
                                                        value="age_minimum"
                                                        name="docValueTypeSimple"
                                                        class="mr-2"
                                                    />
                                                    <span class="text-sm">Edad mínima</span>
                                                </label>
                                                <label
                                                    class="flex items-center p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50"
                                                >
                                                    <input
                                                        v-model="newDocumentRequirement.valueType"
                                                        type="radio"
                                                        value="age_maximum"
                                                        name="docValueTypeSimple"
                                                        class="mr-2"
                                                    />
                                                    <span class="text-sm">Edad máxima</span>
                                                </label>
                                                <label
                                                    class="flex items-center p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50"
                                                >
                                                    <input
                                                        v-model="newDocumentRequirement.valueType"
                                                        type="radio"
                                                        value="age_range"
                                                        name="docValueTypeSimple"
                                                        class="mr-2"
                                                    />
                                                    <span class="text-sm">Rango de edad</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div v-if="newDocumentRequirement.valueType === 'exact'">
                                            <input
                                                v-model="newDocumentRequirement.value"
                                                type="date"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                            />
                                        </div>

                                        <div
                                            v-else-if="
                                                newDocumentRequirement.valueType === 'age_minimum'
                                            "
                                            class="grid grid-cols-3 gap-2"
                                        >
                                            <input
                                                v-model="newDocumentRequirement.value"
                                                type="number"
                                                min="0"
                                                placeholder="Edad"
                                                class="col-span-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                            />
                                            <select
                                                v-model="newDocumentRequirement.ageUnit"
                                                class="col-span-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                            >
                                                <option value="years">años</option>
                                                <option value="months">meses</option>
                                                <option value="days">días</option>
                                            </select>
                                        </div>

                                        <div
                                            v-else-if="
                                                newDocumentRequirement.valueType === 'age_maximum'
                                            "
                                            class="grid grid-cols-3 gap-2"
                                        >
                                            <input
                                                v-model="newDocumentRequirement.value"
                                                type="number"
                                                min="0"
                                                placeholder="Edad"
                                                class="col-span-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                            />
                                            <select
                                                v-model="newDocumentRequirement.ageUnit"
                                                class="col-span-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                            >
                                                <option value="years">años</option>
                                                <option value="months">meses</option>
                                                <option value="days">días</option>
                                            </select>
                                        </div>

                                        <div
                                            v-else-if="
                                                newDocumentRequirement.valueType === 'age_range'
                                            "
                                            class="space-y-2"
                                        >
                                            <div class="grid grid-cols-3 gap-2">
                                                <input
                                                    v-model="newDocumentRequirement.value"
                                                    type="number"
                                                    min="0"
                                                    placeholder="Edad mínima"
                                                    class="col-span-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                                />
                                                <select
                                                    v-model="newDocumentRequirement.ageUnit"
                                                    class="col-span-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                                >
                                                    <option value="years">años</option>
                                                    <option value="months">meses</option>
                                                    <option value="days">días</option>
                                                </select>
                                            </div>
                                            <div class="grid grid-cols-3 gap-2">
                                                <input
                                                    v-model="newDocumentRequirement.value2"
                                                    type="number"
                                                    min="0"
                                                    placeholder="Edad máxima"
                                                    class="col-span-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                                />
                                                <div
                                                    class="col-span-1 px-3 py-2 text-xs text-gray-500 flex items-center"
                                                >
                                                    {{
                                                        getAgeUnitText(
                                                            newDocumentRequirement.ageUnit ||
                                                                'years',
                                                        )
                                                    }}
                                                </div>
                                            </div>
                                        </div>

                                        <p class="text-xs text-gray-500">
                                            <span
                                                v-if="newDocumentRequirement.valueType === 'exact'"
                                                >Se evaluará la fecha exacta</span
                                            >
                                            <span
                                                v-else-if="
                                                    newDocumentRequirement.valueType ===
                                                    'age_minimum'
                                                "
                                                >Se evaluará si la edad es mayor o igual a
                                                {{ newDocumentRequirement.value || 'X' }}
                                                {{
                                                    getAgeUnitText(
                                                        newDocumentRequirement.ageUnit || 'years',
                                                    )
                                                }}</span
                                            >
                                            <span
                                                v-else-if="
                                                    newDocumentRequirement.valueType ===
                                                    'age_maximum'
                                                "
                                                >Se evaluará si la edad es menor o igual a
                                                {{ newDocumentRequirement.value || 'X' }}
                                                {{
                                                    getAgeUnitText(
                                                        newDocumentRequirement.ageUnit || 'years',
                                                    )
                                                }}</span
                                            >
                                            <span
                                                v-else-if="
                                                    newDocumentRequirement.valueType === 'age_range'
                                                "
                                                >Se evaluará si la edad está entre
                                                {{ newDocumentRequirement.value || 'X' }} y
                                                {{ newDocumentRequirement.value2 || 'Y' }}
                                                {{
                                                    getAgeUnitText(
                                                        newDocumentRequirement.ageUnit || 'years',
                                                    )
                                                }}</span
                                            >
                                        </p>
                                    </div>

                                    <!-- Tipos texto / número / fecha simple -->
                                    <input
                                        v-else-if="
                                            getDocumentQuestionTypeForNew() === 'text' ||
                                            getDocumentQuestionTypeForNew() === 'number' ||
                                            getDocumentQuestionTypeForNew() === 'date'
                                        "
                                        v-model="newDocumentRequirement.value"
                                        :type="
                                            getDocumentQuestionTypeForNew() === 'number'
                                                ? 'number'
                                                : getDocumentQuestionTypeForNew() === 'date'
                                                  ? 'date'
                                                  : 'text'
                                        "
                                        :placeholder="
                                            getDocumentQuestionTypeForNew() === 'number'
                                                ? 'Ingresa un número'
                                                : getDocumentQuestionTypeForNew() === 'date'
                                                  ? 'Selecciona una fecha'
                                                  : 'Ingresa texto'
                                        "
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                    />

                                    <select
                                        v-else-if="getDocumentQuestionTypeForNew() === 'select'"
                                        v-model="newDocumentRequirement.value"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                    >
                                        <option value="">Selecciona una opción</option>
                                        <option
                                            v-for="(option, index) in documentDynamicOptions"
                                            :key="index"
                                            :value="option"
                                        >
                                            {{ option }}
                                        </option>
                                    </select>

                                    <select
                                        v-else-if="getDocumentQuestionTypeForNew() === 'multiple'"
                                        v-model="newDocumentRequirement.value"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                    >
                                        <option value="">Selecciona una opción</option>
                                        <option
                                            v-for="(option, index) in documentDynamicOptions"
                                            :key="index"
                                            :value="option"
                                        >
                                            {{ option }}
                                        </option>
                                    </select>

                                    <select
                                        v-else-if="getDocumentQuestionTypeForNew() === 'boolean'"
                                        v-model="newDocumentRequirement.value"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                    >
                                        <option value="">Selecciona una opción</option>
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>

                                    <input
                                        v-else
                                        v-model="newDocumentRequirement.value"
                                        placeholder="Ingresa el valor"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button
                                @click="addDocumentRequirement"
                                :disabled="!canAddDocumentSimpleRequirement"
                                class="inline-flex items-center gap-2 bg-green-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
                            >
                                <i class="fas fa-plus text-xs"></i>
                                Añadir requisito
                            </button>
                        </div>
                    </div>

                    <!-- Grupo de requisitos -->
                    <div v-if="newDocumentRequirement.type === 'group'" class="space-y-6">
                        <div>
                            <h5
                                class="text-sm font-medium text-gray-700 mb-4 flex items-center gap-2"
                            >
                                <i class="fas fa-layer-group text-orange-500"></i>
                                Grupo de requisitos
                            </h5>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2"
                                        >Descripción del grupo</label
                                    >
                                    <input
                                        v-model="newDocumentRequirement.description"
                                        placeholder="Ej: Requisitos de ingresos familiares"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2"
                                        >Lógica del grupo</label
                                    >
                                    <select
                                        v-model="newDocumentRequirement.groupLogic"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                    >
                                        <option value="AND">TODOS deben cumplirse (AND)</option>
                                        <option value="OR">AL MENOS UNO debe cumplirse (OR)</option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{
                                            newDocumentRequirement.groupLogic === 'AND'
                                                ? 'Todas las reglas deben cumplirse simultáneamente'
                                                : 'Al menos una regla debe cumplirse'
                                        }}
                                    </p>
                                </div>
                            </div>

                            <!-- Reglas del grupo -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2"
                                    >Reglas del grupo</label
                                >
                                <div
                                    v-if="newDocumentRequirement.rules.length === 0"
                                    class="text-sm text-gray-500 mb-3"
                                >
                                    Añade al menos una regla al grupo
                                </div>
                                <div v-else class="space-y-2 mb-3">
                                    <div
                                        v-for="(rule, ruleIndex) in newDocumentRequirement.rules"
                                        :key="ruleIndex"
                                        class="flex items-center gap-2 p-3 bg-gray-50 rounded border"
                                    >
                                        <!-- Selector de pregunta -->
                                        <div class="relative flex-1">
                                            <div
                                                @click="
                                                    toggleDocumentQuestionSearch(
                                                        `group-${ruleIndex}`,
                                                    )
                                                "
                                                class="border rounded px-2 py-1 text-sm cursor-pointer bg-white flex items-center justify-between"
                                            >
                                                <span
                                                    v-if="rule.question_id"
                                                    class="text-gray-900 truncate"
                                                >
                                                    {{ getDocumentQuestionText(rule.question_id) }}
                                                </span>
                                                <span v-else class="text-gray-500"
                                                    >Selecciona pregunta</span
                                                >
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>

                                            <!-- Dropdown de búsqueda -->
                                            <div
                                                v-if="
                                                    showDocumentQuestionSearch ===
                                                    `group-${ruleIndex}`
                                                "
                                                class="absolute z-50 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-hidden"
                                            >
                                                <div class="p-2 border-b">
                                                    <input
                                                        v-model="documentQuestionSearchTerm"
                                                        placeholder="Buscar pregunta..."
                                                        class="w-full px-3 py-2 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                        @input="filterDocumentQuestions"
                                                    />
                                                </div>
                                                <div class="max-h-48 overflow-y-auto">
                                                    <div
                                                        v-for="question in filteredDocumentQuestions"
                                                        :key="question.id"
                                                        @click="
                                                            selectDocumentQuestionForGroup(
                                                                question.id,
                                                                ruleIndex,
                                                            )
                                                        "
                                                        class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-100"
                                                    >
                                                        <div
                                                            class="flex items-center justify-between"
                                                        >
                                                            <div class="flex-1">
                                                                <div
                                                                    class="font-medium text-gray-900"
                                                                >
                                                                    {{ question.text }}
                                                                </div>
                                                                <div class="text-xs text-gray-500">
                                                                    {{ question.type }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div
                                                        v-if="
                                                            filteredDocumentQuestions.length === 0
                                                        "
                                                        class="px-3 py-2 text-sm text-gray-500"
                                                    >
                                                        No se encontraron preguntas
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <select
                                            v-model="rule.operator"
                                            class="border rounded px-2 py-1 text-sm"
                                        >
                                            <option
                                                v-for="op in getDocumentAvailableOperatorsForRule(
                                                    rule,
                                                )"
                                                :key="op.value"
                                                :value="op.value"
                                            >
                                                {{ op.label }}
                                            </option>
                                        </select>
                                        <div class="flex gap-2 flex-1">
                                            <!-- Reglas de tipo fecha: edad mínima / máxima / rango -->
                                            <div
                                                v-if="
                                                    getDocumentQuestionTypeForRule(rule) === 'date'
                                                "
                                                class="space-y-2 flex-1"
                                            >
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-gray-600 mb-1"
                                                        >Tipo de valor</label
                                                    >
                                                    <div class="grid grid-cols-2 gap-1">
                                                        <label
                                                            class="flex items-center p-1 border border-gray-300 rounded cursor-pointer hover:bg-gray-50"
                                                        >
                                                            <input
                                                                v-model="rule.valueType"
                                                                type="radio"
                                                                value="exact"
                                                                :name="`docRule-${ruleIndex}-valueType`"
                                                                class="mr-1 text-xs"
                                                            />
                                                            <span class="text-xs">Exacta</span>
                                                        </label>
                                                        <label
                                                            class="flex items-center p-1 border border-gray-300 rounded cursor-pointer hover:bg-gray-50"
                                                        >
                                                            <input
                                                                v-model="rule.valueType"
                                                                type="radio"
                                                                value="age_minimum"
                                                                :name="`docRule-${ruleIndex}-valueType`"
                                                                class="mr-1 text-xs"
                                                            />
                                                            <span class="text-xs">Edad mín.</span>
                                                        </label>
                                                        <label
                                                            class="flex items-center p-1 border border-gray-300 rounded cursor-pointer hover:bg-gray-50"
                                                        >
                                                            <input
                                                                v-model="rule.valueType"
                                                                type="radio"
                                                                value="age_maximum"
                                                                :name="`docRule-${ruleIndex}-valueType`"
                                                                class="mr-1 text-xs"
                                                            />
                                                            <span class="text-xs">Edad máx.</span>
                                                        </label>
                                                        <label
                                                            class="flex items-center p-1 border border-gray-300 rounded cursor-pointer hover:bg-gray-50"
                                                        >
                                                            <input
                                                                v-model="rule.valueType"
                                                                type="radio"
                                                                value="age_range"
                                                                :name="`docRule-${ruleIndex}-valueType`"
                                                                class="mr-1 text-xs"
                                                            />
                                                            <span class="text-xs">Rango</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div v-if="rule.valueType === 'exact'">
                                                    <input
                                                        v-model="rule.value"
                                                        type="date"
                                                        class="w-full border rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                                    />
                                                </div>

                                                <div
                                                    v-else-if="rule.valueType === 'age_minimum'"
                                                    class="grid grid-cols-3 gap-1"
                                                >
                                                    <input
                                                        v-model="rule.value"
                                                        type="number"
                                                        min="0"
                                                        placeholder="Edad"
                                                        class="col-span-2 border rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                                    />
                                                    <select
                                                        v-model="rule.ageUnit"
                                                        class="col-span-1 border rounded px-1 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                                    >
                                                        <option value="years">años</option>
                                                        <option value="months">meses</option>
                                                        <option value="days">días</option>
                                                    </select>
                                                </div>

                                                <div
                                                    v-else-if="rule.valueType === 'age_maximum'"
                                                    class="grid grid-cols-3 gap-1"
                                                >
                                                    <input
                                                        v-model="rule.value"
                                                        type="number"
                                                        min="0"
                                                        placeholder="Edad"
                                                        class="col-span-2 border rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                                    />
                                                    <select
                                                        v-model="rule.ageUnit"
                                                        class="col-span-1 border rounded px-1 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                                    >
                                                        <option value="years">años</option>
                                                        <option value="months">meses</option>
                                                        <option value="days">días</option>
                                                    </select>
                                                </div>

                                                <div
                                                    v-else-if="rule.valueType === 'age_range'"
                                                    class="space-y-1"
                                                >
                                                    <div class="grid grid-cols-3 gap-1">
                                                        <input
                                                            v-model="rule.value"
                                                            type="number"
                                                            min="0"
                                                            placeholder="Mín."
                                                            class="col-span-2 border rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                                        />
                                                        <select
                                                            v-model="rule.ageUnit"
                                                            class="col-span-1 border rounded px-1 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                                        >
                                                            <option value="years">años</option>
                                                            <option value="months">meses</option>
                                                            <option value="days">días</option>
                                                        </select>
                                                    </div>
                                                    <div class="grid grid-cols-3 gap-1">
                                                        <input
                                                            v-model="rule.value2"
                                                            type="number"
                                                            min="0"
                                                            placeholder="Máx."
                                                            class="col-span-2 border rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                                        />
                                                        <div
                                                            class="col-span-1 px-2 py-1 text-xs text-gray-500 flex items-center"
                                                        >
                                                            {{
                                                                getAgeUnitText(
                                                                    rule.ageUnit || 'years',
                                                                )
                                                            }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tipos texto / número -->
                                            <input
                                                v-else-if="
                                                    getDocumentQuestionTypeForRule(rule) ===
                                                        'text' ||
                                                    getDocumentQuestionTypeForRule(rule) ===
                                                        'number'
                                                "
                                                v-model="rule.value"
                                                :type="
                                                    getDocumentQuestionTypeForRule(rule) ===
                                                    'number'
                                                        ? 'number'
                                                        : 'text'
                                                "
                                                :placeholder="
                                                    getDocumentQuestionTypeForRule(rule) ===
                                                    'number'
                                                        ? 'Número'
                                                        : 'Texto'
                                                "
                                                class="border rounded px-2 py-1 text-sm flex-1"
                                            />
                                            <select
                                                v-else-if="
                                                    getDocumentQuestionTypeForRule(rule) ===
                                                        'select' ||
                                                    getDocumentQuestionTypeForRule(rule) ===
                                                        'multiple'
                                                "
                                                v-model="rule.value"
                                                class="border rounded px-2 py-1 text-sm flex-1"
                                            >
                                                <option value="">Selecciona opción</option>
                                                <option
                                                    v-for="(
                                                        option, optIndex
                                                    ) in getDocumentQuestionOptionsForRule(rule)"
                                                    :key="optIndex"
                                                    :value="option"
                                                >
                                                    {{ option }}
                                                </option>
                                            </select>
                                            <select
                                                v-else-if="
                                                    getDocumentQuestionTypeForRule(rule) ===
                                                    'boolean'
                                                "
                                                v-model="rule.value"
                                                class="border rounded px-2 py-1 text-sm flex-1"
                                            >
                                                <option value="">Selecciona</option>
                                                <option value="1">Sí</option>
                                                <option value="0">No</option>
                                            </select>
                                            <input
                                                v-else
                                                v-model="rule.value"
                                                placeholder="Valor"
                                                class="border rounded px-2 py-1 text-sm flex-1"
                                            />
                                        </div>
                                        <button
                                            @click="removeDocumentRuleFromGroup(ruleIndex)"
                                            class="text-red-500 hover:text-red-700 p-1"
                                            title="Eliminar regla"
                                        >
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <button
                                    @click="addDocumentRuleToGroup"
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700"
                                >
                                    + Añadir regla
                                </button>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button
                                    @click="addDocumentRequirement"
                                    :disabled="!canAddDocumentGroupRequirement"
                                    class="inline-flex items-center gap-2 bg-green-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
                                >
                                    <i class="fas fa-plus text-xs"></i>
                                    Añadir grupo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de requisitos configurados -->
            <div class="mb-6 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h4 class="font-semibold text-gray-800">
                        Requisitos configurados ({{ editingDocumentConditions.length }})
                    </h4>
                    <p class="text-sm text-gray-600">
                        Estos requisitos determinan cuándo se mostrará este documento
                    </p>
                </div>

                <div
                    v-if="editingDocumentConditions.length === 0"
                    class="p-8 text-center text-gray-500"
                >
                    <div class="text-4xl mb-2">📋</div>
                    <p>No hay requisitos configurados</p>
                    <p class="text-sm">Añade el primer requisito usando el formulario de arriba</p>
                </div>

                <div v-else class="divide-y divide-gray-200">
                    <div
                        v-for="(requirement, index) in editingDocumentConditions"
                        :key="index"
                        class="p-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-sm font-medium text-gray-900">{{
                                        requirement.description
                                    }}</span>
                                    <span
                                        class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded"
                                    >
                                        {{ requirement.type === 'simple' ? 'Requisito' : 'Grupo' }}
                                        {{ index + 1 }}
                                    </span>
                                    <span
                                        v-if="requirement.type === 'group'"
                                        class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded"
                                    >
                                        {{ requirement.groupLogic }}
                                    </span>
                                </div>

                                <!-- Requisito simple -->
                                <div
                                    v-if="requirement.type === 'simple'"
                                    class="text-sm text-gray-600"
                                >
                                    <span class="font-medium">{{
                                        getDocumentQuestionText(requirement.question_id)
                                    }}</span>
                                    <span class="mx-2">{{
                                        getDocumentOperatorText(requirement.operator)
                                    }}</span>
                                    <span class="font-medium text-green-700">{{
                                        formatDocumentValue(requirement)
                                    }}</span>
                                </div>

                                <!-- Grupo de requisitos -->
                                <div
                                    v-if="requirement.type === 'group'"
                                    class="text-sm text-gray-600"
                                >
                                    <div class="space-y-1">
                                        <div
                                            v-for="(rule, ruleIndex) in requirement.rules"
                                            :key="ruleIndex"
                                            class="flex items-center gap-2"
                                        >
                                            <span class="text-gray-400">•</span>
                                            <span class="font-medium">{{
                                                getDocumentQuestionText(rule.question_id)
                                            }}</span>
                                            <span>{{
                                                getDocumentOperatorText(rule.operator)
                                            }}</span>
                                            <span class="font-medium text-green-700">{{
                                                formatDocumentRuleValue(rule)
                                            }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    @click="editDocumentRequirement(index)"
                                    class="text-blue-500 hover:text-blue-700 p-1"
                                    title="Editar requisito"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    @click="removeDocumentRequirement(index)"
                                    class="text-red-500 hover:text-red-700 p-1"
                                    title="Eliminar requisito"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button
                    @click="closeDocumentConditionsModal"
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Cancelar
                </button>
                <button
                    @click="saveDocumentConditions"
                    type="button"
                    :disabled="editingDocumentConditions.length === 0"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
                >
                    Guardar condiciones
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de prueba de condiciones del formulario solicitante -->
    <WizardTestConditionsModal
        :show="showTestModalSolicitante"
        :questions="formData.questions_solicitante"
        :conditions="formData.questionConditions_solicitante"
        form-type="solicitante"
        @close="showTestModalSolicitante = false"
    />

    <!-- Modal de prueba de condiciones del formulario conviviente -->
    <WizardTestConditionsModal
        :show="showTestModalConviviente"
        :questions="formData.questions_conviviente"
        :conditions="formData.questionConditions_conviviente"
        form-type="conviviente"
        @close="showTestModalConviviente = false"
    />
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import WizardStepConditions from './WizardStepConditions.vue'
import WizardStepEligibility from './WizardStepEligibility.vue'
import WizardStepPreRequisites from './WizardStepPreRequisites.vue'
import WizardStepPreguntasConviviente from './steps/WizardStepPreguntasConviviente.vue'
import ProductCarousel from './ProductCarousel.vue'
import WizardTestConditionsModal from './WizardTestConditionsModal.vue'
import WizardStepInformacionAyuda from './steps/WizardStepInformacionAyuda.vue'
import WizardStepCuestionarioEspecifico from './steps/WizardStepCuestionarioEspecifico.vue'
import WizardStepPreguntasEspecifico from './steps/WizardStepPreguntasEspecifico.vue'
import WizardStepPreguntasSolicitante from './steps/WizardStepPreguntasSolicitante.vue'
import WizardStepDocumentos from './steps/WizardStepDocumentos.vue'
import WizardStepRevision from './steps/WizardStepRevision.vue'
import CreateQuestionModal from './modals/CreateQuestionModal.vue'
import EditQuestionModal from './modals/EditQuestionModal.vue'
import { useWizardNavigation } from '../composables/useWizardNavigation'
import { useWizardData } from '../composables/useWizardData'
import { useNotifications } from '../composables/useNotifications'
import { useWizardValidation } from '../composables/useWizardValidation'
import { useConditionEvaluator } from '../composables/useConditionEvaluator'
import { getStepTitle } from '../constants/wizardSteps'
import {
    generateSlug,
    formatCurrency as formatCurrencyUtil,
    getNumericValue,
    capitalizeFirst,
} from '../utils/formatters'

export default {
    components: {
        WizardStepConditions,
        WizardStepEligibility,
        WizardStepPreRequisites,
        WizardStepPreguntasConviviente,
        ProductCarousel,
        WizardTestConditionsModal,
        WizardStepInformacionAyuda,
        WizardStepCuestionarioEspecifico,
        WizardStepPreguntasEspecifico,
        WizardStepPreguntasSolicitante,
        WizardStepDocumentos,
        WizardStepRevision,
        CreateQuestionModal,
        EditQuestionModal,
    },
    name: 'WizardAyuda',
    props: {
        wizard: {
            type: Object,
            required: true,
        },
        organos: {
            type: Array,
            default: () => [],
        },
        sectores: {
            type: Array,
            default: () => [],
        },
        questionTypes: {
            type: Array,
            default: () => [],
        },
        questionSectores: {
            type: Object,
            default: () => ({}),
        },
        questionCategorias: {
            type: Object,
            default: () => ({}),
        },
        allDocuments: {
            type: Array,
            default: () => [],
        },
        csrf: {
            type: String,
            required: true,
        },
    },
    setup(props) {
        // Composables
        const { showNotification } = useNotifications()
        const {
            currentStep,
            totalSteps,
            stepsArray,
            currentStepData,
            progressPercentage,
            nextStep: navNextStep,
            previousStep: navPreviousStep,
            goToStep: navGoToStep,
        } = useWizardNavigation(props.wizard.current_step || 1)

        const {
            loading,
            saving,
            formData,
            loadWizardData: loadData,
            saveWizard,
            saveDraft,
            completeWizard,
        } = useWizardData()

        const { validateCurrentStep } = useWizardValidation(formData, showNotification)

        // Función para actualizar formData manteniendo la reactividad
        const updateFormData = (newFormData) => {
            if (newFormData.ayuda) {
                Object.assign(formData.ayuda, newFormData.ayuda)
            }
            Object.keys(newFormData).forEach((key) => {
                if (key !== 'ayuda' && formData.hasOwnProperty(key)) {
                    if (Array.isArray(newFormData[key])) {
                        formData[key] = [...newFormData[key]]
                    } else if (typeof newFormData[key] === 'object' && newFormData[key] !== null) {
                        Object.assign(formData[key], newFormData[key])
                    } else {
                        formData[key] = newFormData[key]
                    }
                }
            })
        }

        const collectorQuestionSearch = ref('')
        const collectorSearchResults = ref([])
        const collectorSearching = ref(false)
        const collectorCategoryFilter = ref([])
        const collectorPurposeFilter = ref([])

        const generalQuestionSearch = ref('')
        const generalSearchResults = ref([])
        const generalSearching = ref(false)
        const generalCategoryFilter = ref([])
        const generalPurposeFilter = ref([])

        const availableCategories = ref([])
        const availablePurposes = ref([])
        const allQuestions = ref([])
        const showCreateQuestionModal = ref(false)
        const creatingQuestion = ref(false)

        // Dropdown states
        const showCollectorPurposeDropdown = ref(false)
        const showGeneralPurposeDropdown = ref(false)

        // Edit question modal
        const showEditQuestionModal = ref(false)
        const editingQuestion = ref(false)
        const editingQuestionData = ref({
            id: null,
            slug: '',
            text: '',
            sub_text: '',
            type: 'text',
            options: [],
            category_ids: [],
            purpose_id: '',
        })

        // Dropdown states
        const showCollectorCategoryDropdown = ref(false)
        const showGeneralCategoryDropdown = ref(false)
        let searchInputTimeout = null
        const openSections = ref({
            questions: false,
            conditions: false,
            prerequisites: false,
            eligibility: false,
            documents: false,
            products: false,
        })

        const isDragging = ref(false)
        const draggedIndex = ref(null)
        const dragOverIndex = ref(null)
        const multiSelectMode = ref(false)
        const selectedQuestions = ref([])
        const questionsContainer = ref(null)
        const preguntasEspecificoRef = ref(null)
        const newQuestion = reactive({
            slug: '',
            text: '',
            sub_text: '',
            type: '',
            options: [],
            category_ids: [],
            purpose_ids: [],
        })

        // Métodos de navegación con validación
        const nextStep = async () => {
            if (currentStep.value < totalSteps.value) {
                if (!validateCurrentStep(currentStep.value)) {
                    return
                }

                if (currentStep.value === 1) {
                    const slugExists = await fetch(`/ayudas/slug-exists/${formData.ayuda.slug}`)
                    const data = await slugExists.json()
                    if (data) {
                        showNotification('El slug ya existe', 'error')
                        return
                    }
                }

                await saveWizard(props.wizard.id, props.csrf, currentStep.value)
                navNextStep()
            }
        }

        const previousStep = async () => {
            if (currentStep.value > 1) {
                await saveWizard(props.wizard.id, props.csrf, currentStep.value)
                navPreviousStep()
            }
        }

        const goToStep = async (step) => {
            if (step >= 1 && step <= totalSteps.value) {
                if (step > currentStep.value && !validateCurrentStep(currentStep.value)) {
                    return
                }

                await saveWizard(props.wizard.id, props.csrf, currentStep.value)
                navGoToStep(step)
            }
        }

        const getOrganoName = (organoId) => {
            const organo = props.organos.find((o) => o.id == organoId)
            return organo ? organo.nombre_organismo : 'No especificado'
        }

        const handleSaveDraft = async () => {
            if (saving.value) return

            saving.value = true
            try {
                const dataToSend = JSON.parse(JSON.stringify(formData))
                dataToSend.ayuda.presupuesto = getNumericValue('presupuesto')
                dataToSend.ayuda.cuantia_usuario = getNumericValue('cuantia_usuario')

                const updateResponse = await fetch(`/admin/wizards/${props.wizard.id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': props.csrf,
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        data: dataToSend,
                        current_step: currentStep.value,
                        status: 'draft',
                    }),
                })

                if (!updateResponse.ok) {
                    throw new Error('Error al guardar los datos del wizard')
                }
                await fetch(`/admin/wizards/${props.wizard.id}/draft`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': props.csrf,
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        current_step: currentStep.value,
                    }),
                })

                showNotification('Borrador guardado correctamente', 'success')
            } catch (error) {
                console.error('Error:', error)
                showNotification('Error al guardar el borrador', 'error')
            } finally {
                saving.value = false
            }
        }

        const handleCompleteWizard = async () => {
            if (
                !confirm(
                    '¿Estás seguro de que quieres completar el wizard? Esta acción no se puede deshacer.',
                )
            ) {
                return
            }

            if (saving.value) return

            saving.value = true
            try {
                const dataToSend = JSON.parse(JSON.stringify(formData))

                dataToSend.ayuda.presupuesto = getNumericValue('presupuesto')
                dataToSend.ayuda.cuantia_usuario = getNumericValue('cuantia_usuario')

                const response = await fetch(`/admin/wizards/${props.wizard.id}/complete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': props.csrf,
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        data: dataToSend,
                    }),
                })

                const result = await response.json()

                if (result.success) {
                    showNotification('¡Wizard completado correctamente!', 'success')
                    setTimeout(() => {
                        window.location.href = '/admin/wizards'
                    }, 2000)
                } else {
                    throw new Error(result.message || 'Error al completar el wizard')
                }
            } catch (error) {
                console.error('Error al completar el wizard:', error)

                // Si hay un error, intentar guardar como borrador
                try {
                    const dataToSend = JSON.parse(JSON.stringify(formData))
                    dataToSend.ayuda.presupuesto = getNumericValue('presupuesto')
                    dataToSend.ayuda.cuantia_usuario = getNumericValue('cuantia_usuario')

                    const updateResponse = await fetch(`/admin/wizards/${props.wizard.id}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': props.csrf,
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            data: dataToSend,
                            current_step: currentStep.value,
                            status: 'draft',
                        }),
                    })

                    if (updateResponse.ok) {
                        await fetch(`/admin/wizards/${props.wizard.id}/draft`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': props.csrf,
                                Accept: 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                current_step: currentStep.value,
                            }),
                        })

                        showNotification(
                            'Error al completar el wizard. Se ha guardado como borrador automáticamente. ' +
                                error.message,
                            'warning',
                        )
                    } else {
                        showNotification(
                            'Error al completar el wizard y no se pudo guardar como borrador: ' +
                                error.message,
                            'error',
                        )
                    }
                } catch (draftError) {
                    console.error('Error al guardar como borrador:', draftError)
                    showNotification(
                        'Error al completar el wizard y no se pudo guardar como borrador: ' +
                            error.message,
                        'error',
                    )
                }
            } finally {
                saving.value = false
            }
        }

        /**
         * Carga los datos del wizard
         * @returns {void}
         */
        const loadWizardData = () => {
            if (props.wizard.data) {
                const wizardData = props.wizard.data

                if (wizardData.ayuda) {
                    Object.assign(formData.ayuda, wizardData.ayuda)
                }
                if (wizardData.questionnaire_specific) {
                    Object.assign(
                        formData.questionnaire_specific,
                        wizardData.questionnaire_specific,
                    )
                }
                if (wizardData.questionnaire_solicitante) {
                    Object.assign(
                        formData.questionnaire_solicitante,
                        wizardData.questionnaire_solicitante,
                    )
                }
                if (wizardData.questionnaire_conviviente) {
                    Object.assign(
                        formData.questionnaire_conviviente,
                        wizardData.questionnaire_conviviente,
                    )
                }
                if (wizardData.preRequisitos) {
                    formData.preRequisitos = [...wizardData.preRequisitos]
                }
                if (wizardData.questions_specific) {
                    formData.questions_specific = [...wizardData.questions_specific]
                }
                if (wizardData.questionConditions_specific) {
                    formData.questionConditions_specific = [
                        ...wizardData.questionConditions_specific,
                    ]
                }
                if (wizardData.questions_solicitante) {
                    formData.questions_solicitante = [...wizardData.questions_solicitante]
                }
                if (wizardData.questionConditions_solicitante) {
                    formData.questionConditions_solicitante = [
                        ...wizardData.questionConditions_solicitante,
                    ]
                }
                if (wizardData.questions_conviviente) {
                    formData.questions_conviviente = [...wizardData.questions_conviviente]
                }
                if (wizardData.questionConditions_conviviente) {
                    formData.questionConditions_conviviente = [
                        ...wizardData.questionConditions_conviviente,
                    ]
                }
                if (wizardData.eligibilityLogic) {
                    formData.eligibilityLogic = [...wizardData.eligibilityLogic]
                }
                if (wizardData.documents) {
                    formData.documents = [...wizardData.documents]
                }
                if (wizardData.documents_convivientes) {
                    formData.documents_convivientes = [...wizardData.documents_convivientes]
                }
                if (wizardData.products_services) {
                    formData.products_services = [...wizardData.products_services]
                }
                if (wizardData.selected_product_ids) {
                    formData.selected_product_ids = [...wizardData.selected_product_ids]
                }
                if (wizardData.questions) {
                    formData.questions = [...wizardData.questions]
                }
                if (wizardData.questionConditions) {
                    formData.questionConditions = [...wizardData.questionConditions]
                }

                if (formData.ayuda.presupuesto) {
                    const number = parseInt(formData.ayuda.presupuesto)
                    const formatted = number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                    formData.ayuda.presupuesto = formatted
                }

                if (formData.ayuda.cuantia_usuario) {
                    const number = parseInt(formData.ayuda.cuantia_usuario)
                    const formatted = number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                    formData.ayuda.cuantia_usuario = formatted
                }
            }
        }

        // Métodos para el paso 3 (preguntas)
        const searchCollectorQuestions = async () => {
            // Solo limpiar resultados si no hay ningún filtro activo
            if (
                !collectorQuestionSearch.value.trim() &&
                collectorCategoryFilter.value.length === 0 &&
                collectorPurposeFilter.value.length === 0
            ) {
                collectorSearchResults.value = []
                return
            }

            const query = collectorQuestionSearch.value.trim()
            collectorSearching.value = true
            try {
                const params = new URLSearchParams()
                if (collectorQuestionSearch.value.trim()) {
                    params.append('search', collectorQuestionSearch.value)
                }
                if (collectorCategoryFilter.value.length > 0) {
                    collectorCategoryFilter.value.forEach((categoryId) => {
                        params.append('categories[]', categoryId)
                    })
                }
                if (collectorPurposeFilter.value.length > 0) {
                    collectorPurposeFilter.value.forEach((purposeId) => {
                        params.append('purposes[]', purposeId)
                    })
                }
                // Solo agregar type y limit si hay al menos un filtro
                if (
                    collectorQuestionSearch.value.trim() ||
                    collectorCategoryFilter.value.length > 0 ||
                    collectorPurposeFilter.value.length > 0
                ) {
                    params.append('type', 'collector')
                    params.append('limit', '10')
                }

                const response = await fetch(
                    `/admin/wizards/questions/search?${params.toString()}`,
                    {
                        headers: {
                            'X-CSRF-TOKEN': props.csrf,
                            Accept: 'application/json',
                        },
                    },
                )

                if (response.ok) {
                    const data = await response.json()
                    if (collectorQuestionSearch.value.trim() === query) {
                        collectorSearchResults.value = data.questions || []
                    }
                }
            } catch (error) {
                console.error('Error buscando preguntas de Collector:', error)
                showNotification('Error al buscar preguntas de Collector', 'error')
            } finally {
                collectorSearching.value = false
            }
        }

        const searchGeneralQuestions = async () => {
            // Solo limpiar resultados si no hay ningún filtro activo
            if (
                !generalQuestionSearch.value.trim() &&
                generalCategoryFilter.value.length === 0 &&
                !generalPurposeFilter.value
            ) {
                generalSearchResults.value = []
                return
            }

            const query = generalQuestionSearch.value.trim()
            generalSearching.value = true
            try {
                const params = new URLSearchParams()
                if (generalQuestionSearch.value.trim()) {
                    params.append('search', generalQuestionSearch.value)
                }
                if (generalCategoryFilter.value.length > 0) {
                    generalCategoryFilter.value.forEach((categoryId) => {
                        params.append('categories[]', categoryId)
                    })
                }
                if (generalPurposeFilter.value.length > 0) {
                    generalPurposeFilter.value.forEach((purposeId) => {
                        params.append('purposes[]', purposeId)
                    })
                }
                // Solo agregar type y limit si hay al menos un filtro
                if (
                    generalQuestionSearch.value.trim() ||
                    generalCategoryFilter.value.length > 0 ||
                    generalPurposeFilter.value.length > 0
                ) {
                    params.append('type', 'non-collector')
                    params.append('limit', '10')
                }

                const response = await fetch(
                    `/admin/wizards/questions/search?${params.toString()}`,
                    {
                        headers: {
                            'X-CSRF-TOKEN': props.csrf,
                            Accept: 'application/json',
                        },
                    },
                )

                if (response.ok) {
                    const data = await response.json()
                    if (generalQuestionSearch.value.trim() === query) {
                        generalSearchResults.value = data.questions || []
                    }
                }
            } catch (error) {
                showNotification('Error al buscar otras preguntas', 'error')
            } finally {
                generalSearching.value = false
            }
        }

        const handleCollectorSearchInput = () => {
            if (searchInputTimeout) {
                clearTimeout(searchInputTimeout)
            }
            // Solo limpiar si no hay ningún filtro activo
            if (
                !collectorQuestionSearch.value.trim() &&
                collectorCategoryFilter.value.length === 0 &&
                collectorPurposeFilter.value.length === 0
            ) {
                collectorSearchResults.value = []
                collectorSearching.value = false
                return
            }
            collectorSearching.value = true
            collectorSearchResults.value = []
            searchInputTimeout = setTimeout(() => {
                searchCollectorQuestions()
            }, 400)
        }

        const handleGeneralSearchInput = () => {
            if (searchInputTimeout) {
                clearTimeout(searchInputTimeout)
            }
            // Solo limpiar si no hay ningún filtro activo
            if (
                !generalQuestionSearch.value.trim() &&
                generalCategoryFilter.value.length === 0 &&
                !generalPurposeFilter.value
            ) {
                generalSearchResults.value = []
                generalSearching.value = false
                return
            }
            generalSearching.value = true
            generalSearchResults.value = []
            searchInputTimeout = setTimeout(() => {
                searchGeneralQuestions()
            }, 400)
        }

        // Funciones para manejar cambios de categoría
        const handleCollectorCategoryChange = () => {
            searchCollectorQuestions()
        }

        const handleGeneralCategoryChange = () => {
            searchGeneralQuestions()
        }

        const handleCollectorPurposeChange = () => {
            searchCollectorQuestions()
        }

        const handleGeneralPurposeChange = () => {
            searchGeneralQuestions()
        }

        // Dropdown toggle functions
        const toggleCollectorCategoryDropdown = () => {
            showCollectorCategoryDropdown.value = !showCollectorCategoryDropdown.value
            showGeneralCategoryDropdown.value = false
        }

        const toggleGeneralCategoryDropdown = () => {
            showGeneralCategoryDropdown.value = !showGeneralCategoryDropdown.value
            showCollectorCategoryDropdown.value = false
        }

        // Category selection functions for collector
        const selectAllCollectorCategories = () => {
            collectorCategoryFilter.value = availableCategories.value.map((c) => c.id)
            handleCollectorCategoryChange()
        }

        const clearCollectorCategories = () => {
            collectorCategoryFilter.value = []
            handleCollectorCategoryChange()
        }

        // Category selection functions for general
        const selectAllGeneralCategories = () => {
            generalCategoryFilter.value = availableCategories.value.map((c) => c.id)
            handleGeneralCategoryChange()
        }

        const clearGeneralCategories = () => {
            generalCategoryFilter.value = []
            handleGeneralCategoryChange()
        }

        // Funciones para manejo de finalidades
        const toggleCollectorPurposeDropdown = () => {
            showCollectorPurposeDropdown.value = !showCollectorPurposeDropdown.value
            showGeneralPurposeDropdown.value = false
        }

        const toggleGeneralPurposeDropdown = () => {
            showGeneralPurposeDropdown.value = !showGeneralPurposeDropdown.value
            showCollectorPurposeDropdown.value = false
        }

        const toggleCollectorPurpose = (purposeId) => {
            const index = collectorPurposeFilter.value.indexOf(purposeId)
            if (index > -1) {
                collectorPurposeFilter.value.splice(index, 1)
            } else {
                collectorPurposeFilter.value.push(purposeId)
            }
            handleCollectorPurposeChange()
        }

        const toggleGeneralPurpose = (purposeId) => {
            const index = generalPurposeFilter.value.indexOf(purposeId)
            if (index > -1) {
                generalPurposeFilter.value.splice(index, 1)
            } else {
                generalPurposeFilter.value.push(purposeId)
            }
            handleGeneralPurposeChange()
        }

        const selectAllCollectorPurposes = () => {
            collectorPurposeFilter.value = []
            handleCollectorPurposeChange()
        }

        const selectAllGeneralPurposes = () => {
            generalPurposeFilter.value = []
            handleGeneralPurposeChange()
        }

        const getPurposeName = (purposeId) => {
            const purpose = availablePurposes.value.find((p) => p.id.toString() === purposeId)
            return purpose ? purpose.name : purposeId
        }

        const selectAllPurposesForNewQuestion = () => {
            newQuestion.purpose_ids = []
        }

        const selectAllPurposesForEditingQuestion = () => {
            editingQuestionData.value.purpose_ids = []
        }

        // Funciones para manejo de categorías en el wizard
        const selectAllWizardCategories = () => {
            newQuestion.category_ids = availableCategories.value.map((cat) => cat.id)
        }

        const deselectAllWizardCategories = () => {
            newQuestion.category_ids = []
        }

        // Edit question functions
        const openEditQuestionModal = (question) => {
            editingQuestionData.value = {
                id: question.id,
                slug: question.slug,
                text: question.text,
                sub_text: question.sub_text || '',
                type: question.type,
                options: question.options ? [...question.options] : [],
                category_ids: question.categories ? question.categories.map((c) => c.id) : [],
                purpose_ids: question.purposes ? question.purposes.map((p) => p.id) : [],
            }
            showEditQuestionModal.value = true
        }

        const closeEditQuestionModal = () => {
            showEditQuestionModal.value = false
            editingQuestionData.value = {
                id: null,
                slug: '',
                text: '',
                sub_text: '',
                type: 'text',
                options: [],
                category_ids: [],
                purpose_id: '',
            }
        }

        const handleUpdateQuestion = async (questionData) => {
            if (!questionData.text || !questionData.type) {
                showNotification('Por favor completa todos los campos requeridos', 'error')
                return
            }

            if (!questionData.category_ids || questionData.category_ids.length === 0) {
                showNotification('Por favor selecciona al menos una categoría', 'error')
                return
            }

            if (
                ['select', 'multiple'].includes(questionData.type) &&
                questionData.options.length < 2
            ) {
                showNotification(
                    'Se requieren al menos 2 opciones para este tipo de pregunta',
                    'error',
                )
                return
            }

            editingQuestion.value = true
            try {
                const dataToSend = {
                    slug: questionData.slug,
                    text: questionData.text,
                    sub_text: questionData.sub_text,
                    type: questionData.type,
                    options: questionData.options,
                    category_ids: questionData.category_ids,
                    purpose_ids: questionData.purpose_ids || [],
                }

                const response = await fetch(`/admin/questions/${questionData.id}`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': props.csrf,
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(dataToSend),
                })

                const data = await response.json()

                if (data.success) {
                    showNotification('Pregunta actualizada correctamente', 'success')
                    closeEditQuestionModal()

                    const updateQuestionInResults = (results) => {
                        const index = results.findIndex((q) => q.id === questionData.id)
                        if (index !== -1) {
                            results[index] = { ...results[index], ...data.question }
                        }
                    }

                    updateQuestionInResults(collectorSearchResults.value)
                    updateQuestionInResults(generalSearchResults.value)
                } else {
                    showNotification(data.message || 'Error al actualizar la pregunta', 'error')
                }
            } catch (error) {
                console.error('Error updating question:', error)
                showNotification('Error al actualizar la pregunta', 'error')
            } finally {
                editingQuestion.value = false
            }
        }

        const updateQuestion = handleUpdateQuestion

        const selectAllEditCategories = () => {
            editingQuestionData.value.category_ids = availableCategories.value.map((cat) => cat.id)
        }

        const deselectAllEditCategories = () => {
            editingQuestionData.value.category_ids = []
        }

        // Cargar todas las preguntas del sistema
        const loadAllQuestions = async () => {
            try {
                const response = await fetch('/admin/wizards/questions/search?search=&limit=1000', {
                    headers: {
                        'X-CSRF-TOKEN': props.csrf,
                        Accept: 'application/json',
                    },
                })

                if (response.ok) {
                    const data = await response.json()
                    allQuestions.value = data.questions || []
                }
            } catch (error) {
                console.error('Error cargando todas las preguntas:', error)
            }
        }

        const addQuestionToWizard = (question) => {
            // Verificar si la pregunta ya está añadida
            const exists = formData.questions.some((q) => q.id === question.id)
            if (exists) {
                showNotification('Esta pregunta ya está añadida', 'error')
                return
            }

            const questionToAdd = {
                id: question.id,
                slug: question.slug,
                text: question.text,
                type: question.type,
                options: question.options || [],
                in_collector: !!question.in_collector,
                questionnaires: question.questionnaires || [],
                categories: question.categories || [],
                purposes: question.purposes || [],
            }
            formData.questions.push(questionToAdd)

            // Limpiar búsqueda
            collectorQuestionSearch.value = ''
            collectorSearchResults.value = []
            generalQuestionSearch.value = ''
            generalSearchResults.value = []

            showNotification('Pregunta añadida correctamente', 'success')
        }

        const removeQuestion = (index) => {
            formData.questions.splice(index, 1)
            showNotification('Pregunta eliminada', 'success')
        }

        // Funciones para preguntas específicas
        const addQuestionToSpecific = (question) => {
            const exists = formData.questions_specific.some((q) => q.id === question.id)
            if (exists) {
                showNotification('Esta pregunta ya está añadida', 'error')
                return
            }

            const questionToAdd = {
                id: question.id,
                slug: question.slug,
                text: question.text,
                type: question.type,
                options: question.options || [],
                in_collector: !!question.in_collector,
                questionnaires: question.questionnaires || [],
                categories: question.categories || [],
                purposes: question.purposes || [],
            }

            formData.questions_specific.push(questionToAdd)
            collectorQuestionSearch.value = ''
            collectorSearchResults.value = []
            generalQuestionSearch.value = ''
            generalSearchResults.value = []
            showNotification('Pregunta añadida correctamente', 'success')
        }

        const removeQuestionSpecific = (index) => {
            formData.questions_specific.splice(index, 1)
            showNotification('Pregunta eliminada', 'success')
        }

        const handleDragStartSpecific = (event, index) => {
            if (multiSelectMode.value) return
            isDragging.value = true
            draggedIndex.value = index
            event.dataTransfer.effectAllowed = 'move'
            event.dataTransfer.setData('text/html', event.target.outerHTML)
            event.target.style.opacity = '0.5'
        }

        const handleDropSpecific = (data) => {
            // El componente hijo ahora pasa un objeto con event y container
            const event = data.event || data
            const container =
                data.container ||
                preguntasEspecificoRef.value?.questionsContainer ||
                questionsContainer.value

            event.preventDefault()
            event.stopPropagation()

            if (multiSelectMode.value || draggedIndex.value === null) return

            if (!container) {
                dragOverIndex.value = null
                return
            }

            const dropIndex = getDropIndex(event, formData.questions_specific, container)

            if (dropIndex === null || dropIndex === draggedIndex.value) {
                dragOverIndex.value = null
                return
            }

            // Ajustar el índice si estamos moviendo hacia abajo
            let adjustedDropIndex = dropIndex
            if (draggedIndex.value < dropIndex) {
                adjustedDropIndex = dropIndex - 1
            }

            const questionToMove = formData.questions_specific[draggedIndex.value]
            formData.questions_specific.splice(draggedIndex.value, 1)
            formData.questions_specific.splice(adjustedDropIndex, 0, questionToMove)
            showNotification('Pregunta reordenada', 'success')
            isDragging.value = false
            draggedIndex.value = null
            dragOverIndex.value = null
        }

        const handleQuestionClickSpecific = (index) => {
            if (!multiSelectMode.value) return
            const selectedIndex = selectedQuestions.value.indexOf(index)
            if (selectedIndex > -1) {
                selectedQuestions.value.splice(selectedIndex, 1)
            } else {
                selectedQuestions.value.push(index)
            }
        }

        // Funciones para preguntas del solicitante
        const addQuestionToSolicitante = (question) => {
            const exists = formData.questions_solicitante.some((q) => q.id === question.id)
            if (exists) {
                showNotification('Esta pregunta ya está añadida', 'error')
                return
            }

            const questionToAdd = {
                id: question.id,
                slug: question.slug,
                text: question.text,
                type: question.type,
                options: question.options || [],
                in_collector: !!question.in_collector,
                questionnaires: question.questionnaires || [],
                categories: question.categories || [],
                purposes: question.purposes || [],
            }

            formData.questions_solicitante.push(questionToAdd)
            collectorQuestionSearch.value = ''
            collectorSearchResults.value = []
            generalQuestionSearch.value = ''
            generalSearchResults.value = []
            showNotification('Pregunta añadida correctamente', 'success')
        }

        const removeQuestionSolicitante = (index) => {
            formData.questions_solicitante.splice(index, 1)
            showNotification('Pregunta eliminada', 'success')
        }

        const handleDragStartSolicitante = (event, index) => {
            if (multiSelectMode.value) return
            isDragging.value = true
            draggedIndex.value = index
            event.dataTransfer.effectAllowed = 'move'
            event.dataTransfer.setData('text/html', event.target.outerHTML)
            event.target.style.opacity = '0.5'
        }

        const handleDropSolicitante = (event) => {
            event.preventDefault()
            if (multiSelectMode.value || draggedIndex.value === null) return

            // Usar el contenedor donde se ha hecho drop (lista de preguntas de solicitante)
            const container = event.currentTarget
            const dropIndex = getDropIndex(event, formData.questions_solicitante, container)

            if (dropIndex === null || dropIndex === draggedIndex.value) {
                dragOverIndex.value = null
                return
            }

            // Ajustar el índice si estamos moviendo hacia abajo
            let adjustedDropIndex = dropIndex
            if (draggedIndex.value < dropIndex) {
                adjustedDropIndex = dropIndex - 1
            }

            const questionToMove = formData.questions_solicitante[draggedIndex.value]
            formData.questions_solicitante.splice(draggedIndex.value, 1)
            formData.questions_solicitante.splice(adjustedDropIndex, 0, questionToMove)
            showNotification('Pregunta reordenada', 'success')
            isDragging.value = false
            draggedIndex.value = null
            dragOverIndex.value = null
        }

        const handleQuestionClickSolicitante = (index) => {
            if (!multiSelectMode.value) return
            const selectedIndex = selectedQuestions.value.indexOf(index)
            if (selectedIndex > -1) {
                selectedQuestions.value.splice(selectedIndex, 1)
            } else {
                selectedQuestions.value.push(index)
            }
        }

        // Funciones para preguntas del conviviente
        const addQuestionToConviviente = (question) => {
            const exists = formData.questions_conviviente.some((q) => q.id === question.id)
            if (exists) {
                showNotification('Esta pregunta ya está añadida', 'error')
                return
            }

            const questionToAdd = {
                id: question.id,
                slug: question.slug,
                text: question.text,
                type: question.type,
                options: question.options || [],
                in_collector: !!question.in_collector,
                questionnaires: question.questionnaires || [],
                categories: question.categories || [],
                purposes: question.purposes || [],
            }

            formData.questions_conviviente.push(questionToAdd)
            collectorQuestionSearch.value = ''
            collectorSearchResults.value = []
            generalQuestionSearch.value = ''
            generalSearchResults.value = []
            showNotification('Pregunta añadida correctamente', 'success')
        }

        const removeQuestionConviviente = (index) => {
            formData.questions_conviviente.splice(index, 1)
            showNotification('Pregunta eliminada', 'success')
        }

        const handleDragStartConviviente = (event, index) => {
            if (multiSelectMode.value) return
            isDragging.value = true
            draggedIndex.value = index
            event.dataTransfer.effectAllowed = 'move'
            event.dataTransfer.setData('text/html', event.target.outerHTML)
            event.target.style.opacity = '0.5'
        }

        const handleDropConviviente = (event) => {
            event.preventDefault()
            if (multiSelectMode.value || draggedIndex.value === null) return

            const dropIndex = getDropIndex(event, formData.questions_conviviente)
            if (dropIndex === null || dropIndex === draggedIndex.value) {
                dragOverIndex.value = null
                return
            }

            const questionToMove = formData.questions_conviviente[draggedIndex.value]
            formData.questions_conviviente.splice(draggedIndex.value, 1)
            formData.questions_conviviente.splice(dropIndex, 0, questionToMove)
            showNotification('Pregunta reordenada', 'success')
            isDragging.value = false
            draggedIndex.value = null
            dragOverIndex.value = null
        }

        const handleQuestionClickConviviente = (index) => {
            if (!multiSelectMode.value) return
            const selectedIndex = selectedQuestions.value.indexOf(index)
            if (selectedIndex > -1) {
                selectedQuestions.value.splice(selectedIndex, 1)
            } else {
                selectedQuestions.value.push(index)
            }
        }

        // Funciones para condiciones
        const updateConditionsSpecific = (newConditions) => {
            formData.questionConditions_specific = newConditions
        }

        const updateConditionsSolicitante = (newConditions) => {
            formData.questionConditions_solicitante = newConditions
        }

        const updateConditionsConviviente = (newConditions) => {
            formData.questionConditions_conviviente = newConditions
        }

        // Funciones para el modal de prueba de condiciones
        const getQuestionOptions = (question) => {
            if (!question.options) return []
            if (Array.isArray(question.options)) return question.options
            if (typeof question.options === 'string') {
                try {
                    return JSON.parse(question.options)
                } catch {
                    return []
                }
            }
            return []
        }

        const hasConditionForQuestion = (questionId) => {
            if (!formData.questionConditions_solicitante) return false
            return formData.questionConditions_solicitante.some(
                (cond) => cond.next_question_id == questionId,
            )
        }

        /**
         * TRADUCTOR DE SALTOS A VISIBILIDAD (SOLICITANTE)
         *
         * Traduce los saltos condicionales configurados en Vue Flow a lógica de visibilidad
         * para el formulario del solicitante. Ver comentarios en WizardTestConditionsModal.vue
         * para más detalles sobre cómo funciona el traductor.
         */

        // Obtener el índice de una pregunta en el array (orden del formulario)
        const getQuestionIndexSolicitante = (questionId) => {
            return formData.questions_solicitante.findIndex((q) => q.id == questionId)
        }

        // Obtener el índice de destino de un salto (null = FIN = después de la última pregunta)
        const getDestinationIndexSolicitante = (nextQuestionId) => {
            if (nextQuestionId === null || nextQuestionId === 'FIN' || nextQuestionId === '') {
                return formData.questions_solicitante.length
            }
            return formData.questions_solicitante.findIndex((q) => q.id == nextQuestionId)
        }

        // Verificar si un salto "salta sobre" una pregunta (la omite en el flujo)
        const isQuestionSkippedByJumpSolicitante = (questionIndex, jump) => {
            const sourceIndex = getQuestionIndexSolicitante(jump.question_id)
            const destIndex = getDestinationIndexSolicitante(jump.next_question_id)

            if (sourceIndex !== -1 && destIndex !== -1) {
                return questionIndex > sourceIndex && questionIndex < destIndex
            }
            return false
        }

        // Obtener saltos que van directamente a una pregunta (la muestran explícitamente)
        const getJumpsToQuestionSolicitante = (questionId) => {
            if (!formData.questionConditions_solicitante) return []
            return formData.questionConditions_solicitante.filter(
                (cond) => cond.next_question_id == questionId,
            )
        }

        // Obtener saltos que saltan sobre una pregunta (la omiten en el flujo)
        const getJumpsSkippingQuestionSolicitante = (questionIndex) => {
            if (!formData.questionConditions_solicitante) return []
            return formData.questionConditions_solicitante.filter((jump) =>
                isQuestionSkippedByJumpSolicitante(questionIndex, jump),
            )
        }

        /**
         * Traduce los saltos configurados en condiciones de visibilidad para una pregunta.
         * Retorna tanto saltos directos (que la muestran) como saltos que la saltan (que la ocultan).
         */
        const getConditionsForQuestion = (questionId) => {
            const questionIndex = getQuestionIndexSolicitante(questionId)
            if (questionIndex === -1) return []

            const jumpsTo = getJumpsToQuestionSolicitante(questionId)
            const jumpsSkipping = getJumpsSkippingQuestionSolicitante(questionIndex)

            const visibilityConditions = []

            // Saltos directos: la pregunta es visible si se cumplen
            jumpsTo.forEach((jump) => {
                visibilityConditions.push({
                    ...jump,
                    isDirectJump: true,
                })
            })

            // Saltos que la saltan: la pregunta es visible si NO se cumplen (invertir lógica)
            jumpsSkipping.forEach((jump) => {
                visibilityConditions.push({
                    ...jump,
                    isDirectJump: false,
                    inverted: true, // Si el salto se cumple, la pregunta NO es visible
                })
            })

            return visibilityConditions
        }

        const getQuestionTextById = (questionId) => {
            const question = formData.questions_solicitante.find((q) => q.id == questionId)
            return question ? question.text : `Pregunta ${questionId}`
        }

        const getOperatorText = (operator) => {
            const operators = {
                '==': 'es igual a',
                '!=': 'no es igual a',
                '>': 'es mayor que',
                '>=': 'es mayor o igual que',
                '<': 'es menor que',
                '<=': 'es menor o igual que',
                contains: 'contiene',
                not_contains: 'no contiene',
            }
            return operators[operator] || operator
        }

        const formatConditionValue = (condition) => {
            if (Array.isArray(condition.value)) {
                return condition.value.join(', ')
            }
            return condition.value
        }

        /**
         * Determina si una pregunta es visible basándose en los saltos configurados.
         * Ver comentarios en WizardTestConditionsModal.vue para más detalles sobre la lógica.
         */
        const isQuestionVisibleInTest = (question) => {
            const questionIndex = getQuestionIndexSolicitante(question.id)
            if (questionIndex === -1) return true

            const conditions = getConditionsForQuestion(question.id)

            if (conditions.length === 0) {
                return true
            }

            const directJumps = conditions.filter((c) => c.isDirectJump)
            const skippingJumps = conditions.filter((c) => !c.isDirectJump)

            // Si hay saltos directos, la pregunta es visible si alguno se cumple
            if (directJumps.length > 0) {
                const hasDirectJumpActive = directJumps.some((jump) =>
                    evaluateFullConditionInTest(jump),
                )
                if (hasDirectJumpActive) {
                    return true
                }
            }

            // Si hay saltos que la saltan, la pregunta es visible solo si NINGUNO se cumple
            if (skippingJumps.length > 0) {
                const allSkippingJumpsInactive = skippingJumps.every(
                    (jump) => !evaluateFullConditionInTest(jump),
                )
                return allSkippingJumpsInactive
            }

            // Si solo hay saltos directos y ninguno se cumple, la pregunta NO es visible
            if (directJumps.length > 0) {
                return false
            }

            return true
        }

        /**
         * TRADUCTOR DE SALTOS A VISIBILIDAD (CONVIVIENTE)
         *
         * Traduce los saltos condicionales configurados en Vue Flow a lógica de visibilidad
         * para el formulario del conviviente. Misma lógica que para solicitante.
         */
        const hasConditionForQuestionConviviente = (questionId) => {
            if (!formData.questionConditions_conviviente) return false
            return formData.questionConditions_conviviente.some(
                (cond) => cond.next_question_id == questionId,
            )
        }

        // Obtener el índice de una pregunta en el array (orden del formulario)
        const getQuestionIndexConviviente = (questionId) => {
            return formData.questions_conviviente.findIndex((q) => q.id == questionId)
        }

        // Obtener el índice de destino de un salto (null = FIN = después de la última pregunta)
        const getDestinationIndexConviviente = (nextQuestionId) => {
            if (nextQuestionId === null || nextQuestionId === 'FIN' || nextQuestionId === '') {
                return formData.questions_conviviente.length
            }
            return formData.questions_conviviente.findIndex((q) => q.id == nextQuestionId)
        }

        // Verificar si un salto "salta sobre" una pregunta (la omite en el flujo)
        const isQuestionSkippedByJumpConviviente = (questionIndex, jump) => {
            const sourceIndex = getQuestionIndexConviviente(jump.question_id)
            const destIndex = getDestinationIndexConviviente(jump.next_question_id)

            if (sourceIndex !== -1 && destIndex !== -1) {
                return questionIndex > sourceIndex && questionIndex < destIndex
            }
            return false
        }

        // Obtener saltos que van directamente a una pregunta (la muestran explícitamente)
        const getJumpsToQuestionConviviente = (questionId) => {
            if (!formData.questionConditions_conviviente) return []
            return formData.questionConditions_conviviente.filter(
                (cond) => cond.next_question_id == questionId,
            )
        }

        // Obtener saltos que saltan sobre una pregunta (la omiten en el flujo)
        const getJumpsSkippingQuestionConviviente = (questionIndex) => {
            if (!formData.questionConditions_conviviente) return []
            return formData.questionConditions_conviviente.filter((jump) =>
                isQuestionSkippedByJumpConviviente(questionIndex, jump),
            )
        }

        /**
         * Traduce los saltos configurados en condiciones de visibilidad para una pregunta.
         * Retorna tanto saltos directos (que la muestran) como saltos que la saltan (que la ocultan).
         */
        const getConditionsForQuestionConviviente = (questionId) => {
            const questionIndex = getQuestionIndexConviviente(questionId)
            if (questionIndex === -1) return []

            const jumpsTo = getJumpsToQuestionConviviente(questionId)
            const jumpsSkipping = getJumpsSkippingQuestionConviviente(questionIndex)

            const visibilityConditions = []

            // Saltos directos: la pregunta es visible si se cumplen
            jumpsTo.forEach((jump) => {
                visibilityConditions.push({
                    ...jump,
                    isDirectJump: true,
                })
            })

            // Saltos que la saltan: la pregunta es visible si NO se cumplen (invertir lógica)
            jumpsSkipping.forEach((jump) => {
                visibilityConditions.push({
                    ...jump,
                    isDirectJump: false,
                    inverted: true, // Si el salto se cumple, la pregunta NO es visible
                })
            })

            return visibilityConditions
        }

        const getQuestionTextByIdConviviente = (questionId) => {
            const question = formData.questions_conviviente.find((q) => q.id == questionId)
            return question ? question.text : `Pregunta ${questionId}`
        }

        /**
         * Determina si una pregunta es visible basándose en los saltos configurados (conviviente).
         * Ver comentarios en WizardTestConditionsModal.vue para más detalles sobre la lógica.
         */
        const isQuestionVisibleInTestConviviente = (question) => {
            const questionIndex = getQuestionIndexConviviente(question.id)
            if (questionIndex === -1) return true

            const conditions = getConditionsForQuestionConviviente(question.id)

            if (conditions.length === 0) {
                return true
            }

            const directJumps = conditions.filter((c) => c.isDirectJump)
            const skippingJumps = conditions.filter((c) => !c.isDirectJump)

            // Si hay saltos directos, la pregunta es visible si alguno se cumple
            if (directJumps.length > 0) {
                const hasDirectJumpActive = directJumps.some((jump) =>
                    evaluateFullConditionInTestConviviente(jump),
                )
                if (hasDirectJumpActive) {
                    return true
                }
            }

            // Si hay saltos que la saltan, la pregunta es visible solo si NINGUNO se cumple
            if (skippingJumps.length > 0) {
                const allSkippingJumpsInactive = skippingJumps.every(
                    (jump) => !evaluateFullConditionInTestConviviente(jump),
                )
                return allSkippingJumpsInactive
            }

            // Si solo hay saltos directos y ninguno se cumple, la pregunta NO es visible
            if (directJumps.length > 0) {
                return false
            }

            return true
        }

        // Computed para documentos disponibles (excluyendo los ya seleccionados)
        const availableDocuments = computed(() => {
            return props.allDocuments || []
        })

        const getAvailableDocumentsForIndex = (index, type = 'general') => {
            const currentDoc =
                type === 'general'
                    ? formData.documents[index]
                    : formData.documents_convivientes[index]
            const currentDocId = currentDoc ? Number(currentDoc.document_id) : null

            const selectedGeneralIds = formData.documents
                .map((doc, idx) => (idx !== index ? Number(doc.document_id) : null))
                .filter((id) => id && !isNaN(id))
            const selectedConvivienteIds = formData.documents_convivientes
                .map((doc, idx) =>
                    idx !== index || type !== 'conviviente' ? Number(doc.document_id) : null,
                )
                .filter((id) => id && !isNaN(id))
            const allSelectedIds = [...selectedGeneralIds, ...selectedConvivienteIds]

            return props.allDocuments.filter((doc) => {
                const docId = Number(doc.id)
                // Incluir el documento si no está seleccionado en otros lugares, o si es el documento actual
                return !allSelectedIds.includes(docId) || docId === currentDocId
            })
        }

        // Modal de condiciones de documentos
        const showDocumentConditionsModal = ref(false)
        const showTestModalSolicitante = ref(false)
        const showTestModalConviviente = ref(false)
        const testAnswers = reactive({}) // Respuestas para el modal de prueba del solicitante
        const testAnswersConviviente = reactive({}) // Respuestas para el modal de prueba del conviviente

        // Inicializar evaluadores de condiciones centralizados
        // Para solicitante: combinar todas las preguntas disponibles
        const allQuestionsForSolicitante = computed(() => {
            return [
                ...(formData.questions_solicitante || []),
                ...(formData.questions_specific || []),
                ...(allQuestions.value || []),
            ]
        })
        const { evaluateSimple: evaluateSimpleSolicitante, evaluateFull: evaluateFullSolicitante } =
            useConditionEvaluator(
                allQuestionsForSolicitante,
                (questionId) => testAnswers[questionId],
            )

        // Para conviviente: combinar todas las preguntas disponibles
        const allQuestionsForConviviente = computed(() => {
            return [
                ...(formData.questions_conviviente || []),
                ...(formData.questions_solicitante || []),
                ...(formData.questions_specific || []),
                ...(allQuestions.value || []),
            ]
        })
        const { evaluateSimple: evaluateSimpleConviviente, evaluateFull: evaluateFullConviviente } =
            useConditionEvaluator(
                allQuestionsForConviviente,
                (questionId) => testAnswersConviviente[questionId],
            )

        // Usar el evaluador centralizado para solicitante
        const evaluateConditionInTest = evaluateSimpleSolicitante
        const evaluateFullConditionInTest = evaluateFullSolicitante

        // Usar el evaluador centralizado para conviviente
        const evaluateConditionInTestConviviente = evaluateSimpleConviviente
        const evaluateFullConditionInTestConviviente = evaluateFullConviviente

        const editingDocumentConditions = ref([]) // Array de requisitos (simple o group)
        const editingDocumentIndex = ref(null)
        const editingDocumentType = ref('general') // 'general' o 'conviviente'
        const documentConditionsLogic = ref('AND') // Operador lógico entre requisitos: 'AND' u 'OR'
        const showDocumentQuestionSearch = ref(null)
        const documentQuestionSearchTerm = ref('')
        const filteredDocumentQuestions = ref([])
        const documentDynamicOptions = ref([])
        const editingDocumentRequirementIndex = ref(-1)

        // Nuevo requisito para documentos (similar a WizardStepEligibility)
        const newDocumentRequirement = reactive({
            type: 'simple',
            description: '',
            question_id: '',
            operator: '==',
            value: '',
            // Soporte avanzado para fechas (edad mínima/máxima/rango)
            valueType: 'exact',
            ageUnit: 'years',
            value2: '',
            groupLogic: 'AND',
            rules: [],
        })

        // Funciones para documentos generales
        const updateQuestionsConviviente = (newQuestions) => {
            formData.questions_conviviente = newQuestions
        }

        // Funciones para documentos
        const addDocument = () => {
            formData.documents.push({
                document_id: null,
                es_obligatorio: true,
                conditions: [],
                name: '',
                required: false,
            })
        }

        const removeDocument = (index) => {
            formData.documents.splice(index, 1)
        }

        const updateDocument = ({ index, field, value }) => {
            if (formData.documents[index]) {
                formData.documents[index][field] = value
            }
        }

        // Funciones para documentos de convivientes
        const addDocumentConviviente = () => {
            formData.documents_convivientes.push({
                document_id: null,
                es_obligatorio: true,
                conditions: [],
            })
        }

        const removeDocumentConviviente = (index) => {
            formData.documents_convivientes.splice(index, 1)
        }

        const updateDocumentConviviente = ({ index, field, value }) => {
            if (formData.documents_convivientes[index]) {
                formData.documents_convivientes[index][field] = value
            }
        }

        // Función helper para obtener el nombre del documento
        const getDocumentName = (documentId) => {
            const document = props.allDocuments.find((d) => d.id == documentId)
            return document ? document.name : `Documento ${documentId}`
        }

        // Computed para obtener el documento actual en el modal de condiciones
        const currentEditingDocument = computed(() => {
            if (editingDocumentIndex.value === null) return null
            return editingDocumentType.value === 'general'
                ? formData.documents[editingDocumentIndex.value]
                : formData.documents_convivientes[editingDocumentIndex.value]
        })

        // Computed para obtener el nombre del documento actual
        const currentEditingDocumentName = computed(() => {
            if (!currentEditingDocument.value || !currentEditingDocument.value.document_id) {
                return 'Documento'
            }
            return getDocumentName(currentEditingDocument.value.document_id)
        })

        // Funciones para condiciones de documentos
        const openDocumentConditionsModal = (index, type) => {
            editingDocumentIndex.value = index
            editingDocumentType.value = type

            const doc =
                type === 'general'
                    ? formData.documents[index]
                    : formData.documents_convivientes[index]

            // Cargar requisitos - nueva estructura: { condition: 'AND', requirements: [...] }
            if (doc.conditions) {
                // Estructura nueva con wrapper
                if (doc.conditions.condition && Array.isArray(doc.conditions.requirements)) {
                    editingDocumentConditions.value = JSON.parse(
                        JSON.stringify(doc.conditions.requirements),
                    )
                    documentConditionsLogic.value = doc.conditions.condition || 'AND'
                }
                // Estructura antigua: array directo (legacy)
                else if (Array.isArray(doc.conditions)) {
                    // Si ya tiene estructura de requisitos (type: 'simple' o 'group')
                    if (doc.conditions.length > 0 && doc.conditions[0].type) {
                        editingDocumentConditions.value = JSON.parse(JSON.stringify(doc.conditions))
                        documentConditionsLogic.value = 'AND' // Por defecto, se puede mejorar leyendo de metadata si existe
                    } else {
                        // Formato muy antiguo: convertir condiciones simples a requisitos simples
                        editingDocumentConditions.value = doc.conditions.map((c) => ({
                            type: 'simple',
                            description: `Condición: ${getDocumentQuestionText(c.question_id)}`,
                            question_id: c.question_id || c.questionId,
                            operator: c.operator || '==',
                            value: c.value || '',
                        }))
                        documentConditionsLogic.value = 'AND' // Por defecto
                    }
                } else {
                    documentConditionsLogic.value = 'AND'
                    editingDocumentConditions.value = []
                }
            } else {
                documentConditionsLogic.value = 'AND'
                editingDocumentConditions.value = []
            }

            // Resetear formulario de nuevo requisito
            resetDocumentRequirementForm()
            showDocumentConditionsModal.value = true
        }

        const closeDocumentConditionsModal = () => {
            showDocumentConditionsModal.value = false
            editingDocumentConditions.value = []
            editingDocumentIndex.value = null
            editingDocumentType.value = 'general'
            documentConditionsLogic.value = 'AND'
            showDocumentQuestionSearch.value = null
            documentQuestionSearchTerm.value = ''
            editingDocumentRequirementIndex.value = -1
            resetDocumentRequirementForm()
        }

        const resetDocumentRequirementForm = () => {
            newDocumentRequirement.type = 'simple'
            newDocumentRequirement.description = ''
            newDocumentRequirement.question_id = ''
            newDocumentRequirement.operator = '=='
            newDocumentRequirement.value = ''
            newDocumentRequirement.value2 = ''
            newDocumentRequirement.valueType = 'exact'
            newDocumentRequirement.ageUnit = 'years'
            newDocumentRequirement.groupLogic = 'AND'
            newDocumentRequirement.rules = []
            documentDynamicOptions.value = []
        }

        // Computed para validar si se puede añadir un requisito simple
        const canAddDocumentSimpleRequirement = computed(() => {
            return (
                newDocumentRequirement.description.trim() &&
                newDocumentRequirement.question_id &&
                newDocumentRequirement.operator &&
                newDocumentRequirement.value !== ''
            )
        })

        // Computed para validar si se puede añadir un grupo de requisitos
        const canAddDocumentGroupRequirement = computed(() => {
            return (
                newDocumentRequirement.description.trim() &&
                newDocumentRequirement.rules.length > 0 &&
                newDocumentRequirement.rules.every(
                    (rule) => rule.question_id && rule.operator && rule.value !== '',
                )
            )
        })

        const addDocumentRequirement = () => {
            if (newDocumentRequirement.type === 'simple') {
                if (!canAddDocumentSimpleRequirement.value) {
                    showNotification('Por favor completa todos los campos', 'error')
                    return
                }

                editingDocumentConditions.value.push({
                    type: 'simple',
                    description: newDocumentRequirement.description,
                    question_id: newDocumentRequirement.question_id,
                    operator: newDocumentRequirement.operator,
                    value: newDocumentRequirement.value,
                    value2: newDocumentRequirement.value2,
                    valueType: newDocumentRequirement.valueType,
                    ageUnit: newDocumentRequirement.ageUnit,
                })
            } else {
                if (!canAddDocumentGroupRequirement.value) {
                    showNotification('Por favor completa todos los campos del grupo', 'error')
                    return
                }

                editingDocumentConditions.value.push({
                    type: 'group',
                    description: newDocumentRequirement.description,
                    groupLogic: newDocumentRequirement.groupLogic,
                    rules: [...newDocumentRequirement.rules],
                })
            }

            resetDocumentRequirementForm()
            editingDocumentRequirementIndex.value = -1
            showNotification('Requisito añadido correctamente', 'success')
        }

        const removeDocumentRequirement = (index) => {
            editingDocumentConditions.value.splice(index, 1)
            showNotification('Requisito eliminado', 'success')
        }

        const editDocumentRequirement = (index) => {
            const req = editingDocumentConditions.value[index]
            editingDocumentRequirementIndex.value = index

            // Copiar datos al formulario
            newDocumentRequirement.type = req.type || 'simple'
            newDocumentRequirement.description = req.description || ''
            newDocumentRequirement.question_id = req.question_id || ''
            newDocumentRequirement.operator = req.operator || '=='
            newDocumentRequirement.value = req.value || ''
            newDocumentRequirement.value2 = req.value2 || ''
            newDocumentRequirement.valueType = req.valueType || 'exact'
            newDocumentRequirement.ageUnit = req.ageUnit || 'years'
            newDocumentRequirement.groupLogic = req.groupLogic || 'AND'
            newDocumentRequirement.rules = req.rules ? [...req.rules] : []

            // Eliminar el requisito original
            editingDocumentConditions.value.splice(index, 1)

            showNotification('Requisito cargado para edición', 'success')
        }

        const addDocumentRuleToGroup = () => {
            newDocumentRequirement.rules.push({
                question_id: '',
                operator: '==',
                value: '',
                value2: '',
                valueType: 'exact',
                ageUnit: 'years',
            })
        }

        const removeDocumentRuleFromGroup = (index) => {
            newDocumentRequirement.rules.splice(index, 1)
        }

        const saveDocumentConditions = () => {
            if (editingDocumentIndex.value === null) return

            const doc =
                editingDocumentType.value === 'general'
                    ? formData.documents[editingDocumentIndex.value]
                    : formData.documents_convivientes[editingDocumentIndex.value]

            // Guardar en nueva estructura: wrapper con condition y requirements (como elegibilidad)
            if (editingDocumentConditions.value.length > 0) {
                doc.conditions = {
                    condition:
                        editingDocumentConditions.value.length > 1
                            ? documentConditionsLogic.value
                            : 'AND',
                    requirements: JSON.parse(JSON.stringify(editingDocumentConditions.value)),
                }
            } else {
                doc.conditions = null
            }

            closeDocumentConditionsModal()
            showNotification('Condiciones guardadas correctamente', 'success')
        }

        // Funciones para búsqueda de preguntas en condiciones de documentos
        const toggleDocumentQuestionSearch = (index) => {
            if (showDocumentQuestionSearch.value === index) {
                showDocumentQuestionSearch.value = null
            } else {
                showDocumentQuestionSearch.value = index
                documentQuestionSearchTerm.value = ''
                loadDocumentQuestions()
            }
        }

        const loadDocumentQuestions = () => {
            // Organizar preguntas: primero las del cuestionario actual, luego todas las demás
            const currentQuestionIds = [
                ...formData.questions_specific.map((q) => q.id),
                ...formData.questions_solicitante.map((q) => q.id),
                ...formData.questions_conviviente.map((q) => q.id),
            ]
            const currentQuestions = allQuestions.value.filter((q) =>
                currentQuestionIds.includes(q.id),
            )
            const otherQuestions = allQuestions.value.filter(
                (q) => !currentQuestionIds.includes(q.id),
            )
            filteredDocumentQuestions.value = [...currentQuestions, ...otherQuestions]
        }

        const filterDocumentQuestions = () => {
            if (!documentQuestionSearchTerm.value.trim()) {
                loadDocumentQuestions()
                return
            }

            const term = documentQuestionSearchTerm.value.toLowerCase()
            filteredDocumentQuestions.value = allQuestions.value.filter(
                (question) =>
                    question.text.toLowerCase().includes(term) ||
                    question.type.toLowerCase().includes(term),
            )
        }

        const selectDocumentQuestionForNew = (questionId) => {
            newDocumentRequirement.question_id = questionId
            showDocumentQuestionSearch.value = null
            documentQuestionSearchTerm.value = ''
            loadDocumentDynamicOptions()
        }

        const selectDocumentQuestionForGroup = (questionId, ruleIndex) => {
            newDocumentRequirement.rules[ruleIndex].question_id = questionId
            showDocumentQuestionSearch.value = null
            documentQuestionSearchTerm.value = ''
        }

        const getDocumentQuestionText = (questionId) => {
            const question = allQuestions.value.find((q) => q.id == questionId)
            return question ? question.text : 'Pregunta no encontrada'
        }

        const getDocumentQuestionTypeForNew = () => {
            if (!newDocumentRequirement.question_id) return 'text'
            const question = allQuestions.value.find(
                (q) => q.id == newDocumentRequirement.question_id,
            )
            return question ? question.type : 'text'
        }

        const getDocumentQuestionTypeForRule = (rule) => {
            if (!rule.question_id) return 'text'
            const question = allQuestions.value.find((q) => q.id == rule.question_id)
            return question ? question.type : 'text'
        }

        const getDocumentQuestionOptionsForRule = (rule) => {
            if (!rule.question_id) return []
            const question = allQuestions.value.find((q) => q.id == rule.question_id)
            if (!question) return []
            return question.options || []
        }

        const loadDocumentDynamicOptions = async () => {
            if (!newDocumentRequirement.question_id) return

            const question = allQuestions.value.find(
                (q) => q.id == newDocumentRequirement.question_id,
            )
            if (!question) return

            if (question.options && question.options.length > 0) {
                documentDynamicOptions.value = question.options
            } else {
                documentDynamicOptions.value = []
            }
        }

        const getDocumentAvailableOperatorsForNew = () => {
            const type = getDocumentQuestionTypeForNew()
            return getDocumentOperatorsForType(type)
        }

        const getDocumentAvailableOperatorsForRule = (rule) => {
            const type = getDocumentQuestionTypeForRule(rule)
            return getDocumentOperatorsForType(type)
        }

        const getDocumentOperatorsForType = (type) => {
            const operators = {
                text: [
                    { value: '==', label: 'Igual a' },
                    { value: '!=', label: 'Distinto de' },
                    { value: 'in', label: 'Contiene' },
                ],
                number: [
                    { value: '==', label: 'Igual a' },
                    { value: '!=', label: 'Distinto de' },
                    { value: '>', label: 'Mayor que' },
                    { value: '>=', label: 'Mayor o igual que' },
                    { value: '<', label: 'Menor que' },
                    { value: '<=', label: 'Menor o igual que' },
                ],
                // Soportar preguntas con type = "integer" usando los mismos operadores que "number"
                integer: [
                    { value: '==', label: 'Igual a' },
                    { value: '!=', label: 'Distinto de' },
                    { value: '>', label: 'Mayor que' },
                    { value: '>=', label: 'Mayor o igual que' },
                    { value: '<', label: 'Menor que' },
                    { value: '<=', label: 'Menor o igual que' },
                ],
                boolean: [{ value: '==', label: 'Igual a' }],
                select: [
                    { value: '==', label: 'Igual a' },
                    { value: '!=', label: 'Distinto de' },
                ],
                // Incluir 'in' y 'not_in' para poder ver/editar condiciones legacy; == y != son los preferidos
                multiple: [
                    { value: '==', label: 'Igual a' },
                    { value: '!=', label: 'Distinto de' },
                    { value: 'in', label: 'Contiene' },
                    { value: 'not_in', label: 'No contiene' },
                ],
                date: [
                    { value: '==', label: 'Igual a' },
                    { value: '>', label: 'Después de' },
                    { value: '<', label: 'Antes de' },
                ],
            }
            return operators[type] || operators.text
        }

        const getDocumentOperatorText = (operator) => {
            const operatorMap = {
                '==': 'igual a',
                '!=': 'distinto de',
                '>': 'mayor que',
                '>=': 'mayor o igual que',
                '<': 'menor que',
                '<=': 'menor o igual que',
                in: 'contiene',
                not_in: 'no contiene',
            }
            return operatorMap[operator] || operator
        }

        const formatDocumentValue = (requirement) => {
            const question = allQuestions.value.find((q) => q.id == requirement.question_id)

            // Preguntas tipo fecha con valor de edad: mostrar texto claro
            if (requirement.valueType) {
                const unit = getAgeUnitText(requirement.ageUnit || 'years')
                if (requirement.valueType === 'age_minimum') {
                    return `Edad mínima: ${requirement.value ?? '—'} ${unit}`
                }
                if (requirement.valueType === 'age_maximum') {
                    return `Edad máxima: ${requirement.value ?? '—'} ${unit}`
                }
                if (requirement.valueType === 'age_range') {
                    return `Rango de edad: entre ${requirement.value ?? '—'} y ${requirement.value2 ?? '—'} ${unit}`
                }
                if (requirement.valueType === 'exact' && requirement.value) {
                    return `Fecha exacta: ${requirement.value}`
                }
            }

            if (!question) return requirement.value

            if (question.type === 'boolean') {
                return requirement.value === '1' ? 'Sí' : 'No'
            }

            if (question.type === 'select' && question.options) {
                const index = question.options.indexOf(requirement.value)
                return index >= 0 ? question.options[index] : requirement.value
            }

            return requirement.value
        }

        const formatDocumentRuleValue = (rule) => {
            const question = allQuestions.value.find((q) => q.id == rule.question_id)

            // Preguntas tipo fecha con valor de edad: mostrar texto claro
            if (rule.valueType) {
                const unit = getAgeUnitText(rule.ageUnit || 'years')
                if (rule.valueType === 'age_minimum') {
                    return `Edad mínima: ${rule.value ?? '—'} ${unit}`
                }
                if (rule.valueType === 'age_maximum') {
                    return `Edad máxima: ${rule.value ?? '—'} ${unit}`
                }
                if (rule.valueType === 'age_range') {
                    return `Rango de edad: entre ${rule.value ?? '—'} y ${rule.value2 ?? '—'} ${unit}`
                }
                if (rule.valueType === 'exact' && rule.value) {
                    return `Fecha exacta: ${rule.value}`
                }
            }

            if (!question) return rule.value

            if (question.type === 'boolean') {
                return rule.value === '1' ? 'Sí' : 'No'
            }

            if (question.type === 'select' && question.options) {
                const index = question.options.indexOf(rule.value)
                return index >= 0 ? question.options[index] : rule.value
            }

            return rule.value
        }

        // Funciones helper para obtener requisitos y lógica de documentos (compatibilidad con nueva estructura)
        const getDocumentRequirements = (doc) => {
            if (!doc.conditions) return []
            // Nueva estructura: { condition: 'AND', requirements: [...] }
            if (doc.conditions.condition && Array.isArray(doc.conditions.requirements)) {
                return doc.conditions.requirements
            }
            // Estructura antigua: array directo
            if (Array.isArray(doc.conditions)) {
                return doc.conditions
            }
            return []
        }

        const getDocumentConditionsLogic = (doc) => {
            if (!doc.conditions) return 'AND'
            // Nueva estructura: { condition: 'AND', requirements: [...] }
            if (doc.conditions.condition) {
                return doc.conditions.condition
            }
            // Estructura antigua (legacy): array directo - por defecto AND
            return 'AND'
        }

        const handleDocumentClickOutside = (event) => {
            if (!event.target.closest('.relative')) {
                showDocumentQuestionSearch.value = null
            }
        }

        // Funciones para productos y servicios
        const addProductService = () => {
            formData.products_services.push({
                name: '',
                type: 'product',
                description: '',
            })
        }

        const removeProductService = (index) => {
            formData.products_services.splice(index, 1)
        }

        const updateSelectedProducts = (productIds) => {
            formData.selected_product_ids = [...productIds]
        }

        const handleDragStart = (event, index) => {
            if (multiSelectMode.value) return

            isDragging.value = true
            draggedIndex.value = index

            event.dataTransfer.effectAllowed = 'move'
            event.dataTransfer.setData('text/html', event.target.outerHTML)

            event.target.style.opacity = '0.5'
        }

        const handleDragEnd = (event) => {
            isDragging.value = false
            draggedIndex.value = null
            dragOverIndex.value = null

            event.target.style.opacity = ''
        }

        const handleDrop = (event) => {
            event.preventDefault()

            if (multiSelectMode.value || draggedIndex.value === null) return

            const dropIndex = getDropIndex(event)
            if (dropIndex === null || dropIndex === draggedIndex.value) {
                dragOverIndex.value = null
                return
            }

            const questionToMove = formData.questions[draggedIndex.value]
            formData.questions.splice(draggedIndex.value, 1)
            formData.questions.splice(dropIndex, 0, questionToMove)

            showNotification('Pregunta reordenada', 'success')

            isDragging.value = false
            draggedIndex.value = null
            dragOverIndex.value = null
        }

        const getDropIndex = (event, questionsList = null, containerOverride = null) => {
            const container = containerOverride || questionsContainer.value
            if (!container) {
                console.warn('Container is null in getDropIndex')
                return null
            }

            const questions = container.querySelectorAll('[draggable="true"]')
            const list = questionsList || formData.questions

            if (questions.length === 0) {
                console.warn('No draggable questions found')
                return null
            }

            let dropIndex = null
            let closestDistance = Infinity
            const y = event.clientY

            for (let i = 0; i < questions.length; i++) {
                const rect = questions[i].getBoundingClientRect()

                if (y >= rect.top && y <= rect.bottom) {
                    const midY = rect.top + rect.height / 2
                    dropIndex = y < midY ? i : i + 1
                    break
                }

                const distance = Math.min(Math.abs(y - rect.top), Math.abs(y - rect.bottom))

                if (distance < closestDistance) {
                    closestDistance = distance
                    dropIndex = y < rect.top ? i : i + 1
                }
            }

            // Si no encontramos un índice válido, usar el último
            if (dropIndex === null) {
                dropIndex = questions.length
            }

            return Math.min(Math.max(0, dropIndex), list.length)
        }

        const toggleMultiSelect = () => {
            multiSelectMode.value = !multiSelectMode.value
            selectedQuestions.value = []

            if (!multiSelectMode.value) {
                isDragging.value = false
                draggedIndex.value = null
                dragOverIndex.value = null
            }
        }

        const handleQuestionClick = (index) => {
            if (!multiSelectMode.value) return

            const selectedIndex = selectedQuestions.value.indexOf(index)
            if (selectedIndex > -1) {
                selectedQuestions.value.splice(selectedIndex, 1)
            } else {
                selectedQuestions.value.push(index)
            }
        }

        const deleteSelectedQuestions = () => {
            if (selectedQuestions.value.length === 0) return

            const sortedIndices = [...selectedQuestions.value].sort((a, b) => b - a)

            sortedIndices.forEach((index) => {
                formData.questions.splice(index, 1)
            })

            showNotification(
                `${selectedQuestions.value.length} pregunta(s) eliminada(s)`,
                'success',
            )
            selectedQuestions.value = []
        }

        const moveQuestion = (index, direction) => {
            if (direction === 'up' && index > 0) {
                const temp = formData.questions[index]
                formData.questions[index] = formData.questions[index - 1]
                formData.questions[index - 1] = temp
            } else if (direction === 'down' && index < formData.questions.length - 1) {
                const temp = formData.questions[index]
                formData.questions[index] = formData.questions[index + 1]
                formData.questions[index + 1] = temp
            }
        }

        const handleQuestionTypeChange = () => {
            if (['select', 'multiple'].includes(newQuestion.type)) {
                if (newQuestion.options.length === 0) {
                    newQuestion.options = ['', ''] // Al menos 2 opciones
                }
            } else {
                newQuestion.options = []
            }
        }

        const addOption = () => {
            newQuestion.options.push('')
        }

        const removeOption = (index) => {
            newQuestion.options.splice(index, 1)
        }

        const generateSlug = () => {
            if (newQuestion.text) {
                let slug = newQuestion.text
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '') // Remove accents
                    .replace(/[^a-z0-9\s]/g, '') // Remove special characters except spaces
                    .replace(/\s+/g, '_') // Replace spaces with underscores
                    .replace(/_+/g, '_') // Replace multiple underscores with single
                    .replace(/^_|_$/g, '') // Remove leading/trailing underscores

                newQuestion.slug = slug
            }
        }

        const generateAyudaSlug = () => {
            if (formData.ayuda.nombre_ayuda) {
                formData.ayuda.slug = generateSlug(formData.ayuda.nombre_ayuda)
            }
        }

        const formatCurrency = (field) => {
            const value = formData.ayuda[field]
            formData.ayuda[field] = formatCurrencyUtil(value)
        }

        // Funciones auxiliares para mostrar requisitos de elegibilidad
        const getQuestionTextForEligibility = (questionId) => {
            const question = allQuestions.value.find((q) => q.id == questionId)
            return question ? question.text : `Pregunta ${questionId}`
        }

        const getOperatorTextForEligibility = (operator) => {
            const operatorMap = {
                '==': 'igual a',
                '!=': 'distinto de',
                '>': 'mayor que',
                '>=': 'mayor o igual que',
                '<': 'menor que',
                '<=': 'menor o igual que',
                contains: 'contiene',
                not_contains: 'no contiene',
                starts_with: 'empieza por',
                ends_with: 'termina por',
                between: 'entre',
            }
            return operatorMap[operator] || operator
        }

        const formatValueForEligibility = (requirement) => {
            if (requirement.operator === 'between' && requirement.value2) {
                return `${requirement.value} y ${requirement.value2}`
            }

            if (requirement.valueType) {
                if (requirement.valueType === 'age_minimum') {
                    const unit = getAgeUnitText(requirement.ageUnit || 'years')
                    return `Mayor de ${requirement.value} ${unit}`
                }
                if (requirement.valueType === 'age_maximum') {
                    const unit = getAgeUnitText(requirement.ageUnit || 'years')
                    return `Menor de ${requirement.value} ${unit}`
                }
                if (requirement.valueType === 'age_range') {
                    const unit = getAgeUnitText(requirement.ageUnit || 'years')
                    return `Entre ${requirement.value} y ${requirement.value2} ${unit}`
                }
            }

            const question = allQuestions.value.find((q) => q.id == requirement.question_id)
            if (question) {
                if (question.type === 'boolean') {
                    return requirement.value === '1' ? 'Sí' : 'No'
                }

                if (question.type === 'multiple') {
                    if (typeof requirement.value === 'string') {
                        try {
                            const values = JSON.parse(requirement.value)
                            if (Array.isArray(values)) {
                                return values.join(', ')
                            }
                        } catch (e) {}
                    }
                }
            }

            return requirement.value
        }

        const formatRuleValueForEligibility = (rule) => {
            const question = allQuestions.value.find((q) => q.id == rule.question_id)
            if (question) {
                if (question.type === 'boolean') {
                    return rule.value === '1' ? 'Sí' : 'No'
                }
                if (question.type === 'multiple') {
                    if (typeof rule.value === 'string') {
                        try {
                            const values = JSON.parse(rule.value)
                            if (Array.isArray(values)) {
                                return values.join(', ')
                            }
                        } catch (e) {}
                    }
                }

                if (question.type === 'date' && rule.valueType) {
                    if (rule.valueType === 'age_minimum') {
                        const unit = getAgeUnitText(rule.ageUnit || 'years')
                        return `Mayor de ${rule.value} ${unit}`
                    }
                    if (rule.valueType === 'age_maximum') {
                        const unit = getAgeUnitText(rule.ageUnit || 'years')
                        return `Menor de ${rule.value} ${unit}`
                    }
                    if (rule.valueType === 'age_range') {
                        const unit = getAgeUnitText(rule.ageUnit || 'years')
                        return `Entre ${rule.value} y ${rule.value2} ${unit}`
                    }
                }
            }

            return rule.value
        }

        const getPreRequisiteQuestionText = (preReq) => {
            const question = allQuestions.value.find((q) => q.id == preReq.question_id)
            return question ? question.text : `Pregunta ${preReq.question_id}`
        }

        const getPreRequisiteOperatorText = (operator) => {
            const operatorMap = {
                '==': 'igual a',
                '!=': 'distinto de',
                '>': 'mayor que',
                '>=': 'mayor o igual que',
                '<': 'menor que',
                '<=': 'menor o igual que',
                contains: 'contiene',
                not_contains: 'no contiene',
                starts_with: 'empieza por',
                ends_with: 'termina por',
                between: 'entre',
                exists: 'existe',
                not_exists: 'no existe',
            }
            return operatorMap[operator] || operator
        }

        const getPreRequisiteValueText = (preReq) => {
            if (preReq.operator === 'between' && preReq.value2) {
                return `${preReq.value} y ${preReq.value2}`
            }

            if (preReq.value_type) {
                if (preReq.value_type === 'age_minimum') {
                    const unit = getAgeUnitText(preReq.age_unit || 'years')
                    return `Mayor de ${preReq.value} ${unit}`
                }
                if (preReq.value_type === 'age_maximum') {
                    const unit = getAgeUnitText(preReq.age_unit || 'years')
                    return `Menor de ${preReq.value} ${unit}`
                }
                if (preReq.value_type === 'age_range') {
                    const unit = getAgeUnitText(preReq.age_unit || 'years')
                    return `Entre ${preReq.value} y ${preReq.value2} ${unit}`
                }
            }

            const question = allQuestions.value.find((q) => q.id == preReq.question_id)
            if (question) {
                if (question.type === 'boolean') {
                    return preReq.value === '1' ? 'Sí' : 'No'
                }
                if (question.type === 'multiple') {
                    if (typeof preReq.value === 'string') {
                        try {
                            const values = JSON.parse(preReq.value)
                            if (Array.isArray(values)) {
                                return values.join(', ')
                            }
                        } catch (e) {}
                    }
                }
            }

            return preReq.value
        }

        const getAgeUnitText = (unit) => {
            const unitMap = {
                years: 'años',
                months: 'meses',
                days: 'días',
            }
            return unitMap[unit] || unit
        }

        const getPreRequisiteTargetText = (preReq) => {
            const targetTexts = {
                solicitante: 'Solicitante',
                conviviente: `Conviviente (${getConvivienteTypeText(preReq.conviviente_type)})`,
                unidad_convivencia_completa: 'Unidad de Convivencia (Completa)',
                unidad_convivencia_sin_solicitante: 'Unidad de Convivencia (Sin Solicitante)',
                unidad_familiar_completa: 'Unidad Familiar (Completa)',
                unidad_familiar_sin_solicitante: 'Unidad Familiar (Sin Solicitante)',
                any_conviviente: 'Cualquier Conviviente',
                any_familiar: 'Cualquier Familiar',
                any_persona_unidad: 'Cualquier Persona de la Unidad',
            }
            return targetTexts[preReq.target_type] || preReq.target_type
        }

        const getConvivienteTypeText = (type) => {
            const typeTexts = {
                conyuge: 'Cónyuge',
                hijo: 'Hijo/a',
                padre: 'Padre/Madre',
                otro: 'Otro familiar',
                no_familiar: 'No familiar',
            }
            return typeTexts[type] || type
        }

        const getPreRequisiteTargetClass = (targetType) => {
            const classes = {
                solicitante: 'bg-green-100 text-green-800',
                conviviente: 'bg-orange-100 text-orange-800',
                unidad_convivencia_completa: 'bg-purple-100 text-purple-800',
                unidad_convivencia_sin_solicitante: 'bg-purple-100 text-purple-800',
                unidad_familiar_completa: 'bg-indigo-100 text-indigo-800',
                unidad_familiar_sin_solicitante: 'bg-indigo-100 text-indigo-800',
                any_conviviente: 'bg-yellow-100 text-yellow-800',
                any_familiar: 'bg-yellow-100 text-yellow-800',
                any_persona_unidad: 'bg-yellow-100 text-yellow-800',
            }
            return classes[targetType] || 'bg-gray-100 text-gray-800'
        }

        const getPreRequisiteBorderClass = (targetType) => {
            const classes = {
                solicitante: 'border-green-400',
                conviviente: 'border-orange-400',
                unidad_convivencia_completa: 'border-purple-400',
                unidad_convivencia_sin_solicitante: 'border-purple-400',
                unidad_familiar_completa: 'border-indigo-400',
                unidad_familiar_sin_solicitante: 'border-indigo-400',
                any_conviviente: 'border-yellow-400',
                any_familiar: 'border-yellow-400',
                any_persona_unidad: 'border-yellow-400',
            }
            return classes[targetType] || 'border-gray-400'
        }

        const getPreRequisiteIcon = (targetType) => {
            const icons = {
                solicitante: 'fas fa-user',
                conviviente: 'fas fa-user-tag',
                unidad_convivencia_completa: 'fas fa-home',
                unidad_convivencia_sin_solicitante: 'fas fa-home',
                unidad_familiar_completa: 'fas fa-users',
                unidad_familiar_sin_solicitante: 'fas fa-users',
                any_conviviente: 'fas fa-question-circle',
                any_familiar: 'fas fa-question-circle',
                any_persona_unidad: 'fas fa-question-circle',
            }
            return icons[targetType] || 'fas fa-question'
        }

        const getEligibilityPersonTypeText = (requirement) => {
            const baseTexts = {
                solicitante: 'Solicitante',
                unidad_convivencia_completa: 'Unidad Completa',
                unidad_convivencia_sin_solicitante: 'Unidad Sin Solicitante',
                unidad_familiar_completa: 'Familia Completa',
                unidad_familiar_sin_solicitante: 'Familia Sin Solicitante',
                conviviente: 'Conviviente Específico',
                any_conviviente: 'Cualquier Conviviente',
                any_familiar: 'Cualquier Familiar',
                any_persona_unidad: 'Cualquier Persona',
            }

            let text = baseTexts[requirement.personType] || requirement.personType

            if (requirement.personType === 'conviviente' && requirement.convivienteType) {
                text += ` (${getConvivienteTypeText(requirement.convivienteType)})`
            }

            return text
        }

        const getEligibilityPersonTypeClass = (personType) => {
            const classes = {
                solicitante: 'bg-green-100 text-green-800',
                unidad_convivencia_completa: 'bg-purple-100 text-purple-800',
                unidad_convivencia_sin_solicitante: 'bg-purple-100 text-purple-800',
                unidad_familiar_completa: 'bg-indigo-100 text-indigo-800',
                unidad_familiar_sin_solicitante: 'bg-indigo-100 text-indigo-800',
                conviviente: 'bg-orange-100 text-orange-800',
                any_conviviente: 'bg-yellow-100 text-yellow-800',
                any_familiar: 'bg-yellow-100 text-yellow-800',
                any_persona_unidad: 'bg-yellow-100 text-yellow-800',
            }
            return classes[personType] || 'bg-gray-100 text-gray-800'
        }

        const getEligibilityPersonTypeIcon = (personType) => {
            const icons = {
                solicitante: 'fas fa-user',
                unidad_convivencia_completa: 'fas fa-home',
                unidad_convivencia_sin_solicitante: 'fas fa-home',
                unidad_familiar_completa: 'fas fa-users',
                unidad_familiar_sin_solicitante: 'fas fa-users',
                conviviente: 'fas fa-user-tag',
                any_conviviente: 'fas fa-question-circle',
                any_familiar: 'fas fa-question-circle',
                any_persona_unidad: 'fas fa-question-circle',
            }
            return icons[personType] || 'fas fa-question'
        }

        const handleCreateQuestion = async (questionData) => {
            if (!questionData.text || !questionData.type) {
                showNotification('Por favor completa todos los campos requeridos', 'error')
                return
            }

            if (!questionData.category_ids || questionData.category_ids.length === 0) {
                showNotification('Por favor selecciona al menos una categoría', 'error')
                return
            }

            if (
                ['select', 'multiple'].includes(questionData.type) &&
                questionData.options.length < 2
            ) {
                showNotification(
                    'Se requieren al menos 2 opciones para este tipo de pregunta',
                    'error',
                )
                return
            }

            creatingQuestion.value = true
            try {
                const dataToSend = {
                    slug: questionData.slug,
                    text: questionData.text,
                    sub_text: questionData.sub_text,
                    type: questionData.type,
                    options: questionData.options,
                    category_ids: questionData.category_ids,
                    purpose_ids: questionData.purpose_ids || [],
                }

                const response = await fetch('/admin/wizards/questions', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': props.csrf,
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(dataToSend),
                })

                const data = await response.json()

                if (data.success) {
                    if (currentStep.value === 4) {
                        addQuestionToSpecific(data.question)
                    } else if (currentStep.value === 6) {
                        addQuestionToSolicitante(data.question)
                    } else if (currentStep.value === 8) {
                        addQuestionToConviviente(data.question)
                    } else {
                        addQuestionToWizard(data.question)
                    }

                    showCreateQuestionModal.value = false
                    showNotification('Pregunta creada y añadida correctamente', 'success')
                } else {
                    throw new Error(data.message || 'Error al crear la pregunta')
                }
            } catch (error) {
                console.error('Error creando pregunta:', error)
                showNotification('Error al crear la pregunta: ' + error.message, 'error')
            } finally {
                creatingQuestion.value = false
            }
        }

        const createNewQuestion = handleCreateQuestion

        const updateConditions = (newConditions) => {
            const processedConditions = newConditions.map((condition) => {
                if (condition.rules && Array.isArray(condition.rules)) {
                    const processedRules = condition.rules.map((rule) => {
                        const question = formData.questions.find((q) => q.id == rule.question_id)

                        if (
                            question &&
                            ['select', 'multiple'].includes(question.type) &&
                            question.options &&
                            Array.isArray(question.options)
                        ) {
                            const optionIndex = question.options.indexOf(rule.value)
                            if (optionIndex !== -1) {
                                return {
                                    ...rule,
                                    value: optionIndex,
                                }
                            }
                        }

                        return rule
                    })

                    return {
                        ...condition,
                        rules: processedRules,
                    }
                }

                return condition
            })

            formData.questionConditions = processedConditions
        }

        const updateEligibilityRequirements = (newRequirements) => {
            formData.eligibilityLogic = newRequirements
        }

        const saveEligibilityRequirements = async () => {
            if (formData.eligibilityLogic.length === 0) {
                showNotification('No hay requisitos para guardar', 'warning')
                return
            }

            showNotification('Requisitos de elegibilidad guardados en el wizard', 'success')
        }

        const toggleSection = (section) => {
            openSections.value[section] = !openSections.value[section]
        }

        const loadCategories = async () => {
            try {
                const response = await fetch('/admin/question-categories?hierarchical=true', {
                    headers: {
                        Accept: 'application/json',
                    },
                })
                if (response.ok) {
                    const data = await response.json()
                    // Aplanar la estructura jerárquica para los selects (recursivo)
                    const flatCategories = []

                    const flattenCategory = (category, level = 0) => {
                        flatCategories.push({
                            id: category.id,
                            name: category.name,
                            description: category.description,
                            is_parent: level === 0,
                            parent_name: level > 0 ? category.parent_name : null,
                            level: level,
                        })

                        if (category.children && category.children.length > 0) {
                            category.children.forEach((child) => {
                                flattenCategory(child, level + 1)
                            })
                        }
                    }

                    data.categories.forEach((parent) => {
                        flattenCategory(parent)
                    })
                    availableCategories.value = flatCategories
                }
            } catch (error) {
                console.error('Error cargando categorías:', error)
            }
        }

        const loadPurposes = async () => {
            try {
                const response = await fetch('/admin/question-purposes', {
                    headers: {
                        Accept: 'application/json',
                    },
                })
                if (response.ok) {
                    const data = await response.json()
                    if (data.success) {
                        availablePurposes.value = data.purposes || []
                    }
                }
            } catch (error) {
                console.error('Error cargando finalidades:', error)
            }
        }

        const updatePreRequisitos = (preRequisitos) => {
            formData.preRequisitos = preRequisitos
        }

        // Watcher para cargar opciones dinámicas cuando se selecciona una pregunta
        // Limpiar respuestas cuando se cierra el modal de prueba
        watch(showTestModalSolicitante, (isOpen) => {
            if (!isOpen) {
                Object.keys(testAnswers).forEach((key) => {
                    delete testAnswers[key]
                })
            }
        })

        watch(showTestModalConviviente, (isOpen) => {
            if (!isOpen) {
                Object.keys(testAnswersConviviente).forEach((key) => {
                    delete testAnswersConviviente[key]
                })
            }
        })

        watch(
            () => newDocumentRequirement.question_id,
            async (newQuestionId) => {
                if (newQuestionId) {
                    await loadDocumentDynamicOptions()
                } else {
                    documentDynamicOptions.value = []
                }
            },
        )

        onMounted(() => {
            loadWizardData()
            loadAllQuestions()
            loadCategories()
            loadPurposes()

            // Close dropdowns when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.relative')) {
                    showCollectorCategoryDropdown.value = false
                    showGeneralCategoryDropdown.value = false
                    showDocumentQuestionSearch.value = null
                }
            })
        })

        return {
            currentStep,
            loading,
            saving,
            formData,
            updateFormData,
            currentStepData,
            progressPercentage,
            totalSteps,
            stepsArray,
            getStepTitle,
            getOrganoName,
            nextStep,
            previousStep,
            goToStep,
            saveWizard,
            handleSaveDraft,
            handleCompleteWizard,
            updatePreRequisitos,
            showNotification,
            collectorQuestionSearch,
            collectorSearchResults,
            collectorSearching,
            collectorCategoryFilter,
            collectorPurposeFilter,
            generalQuestionSearch,
            generalSearchResults,
            generalSearching,
            generalCategoryFilter,
            generalPurposeFilter,
            availableCategories,
            availablePurposes,
            allQuestions,
            openSections,
            showCreateQuestionModal,
            creatingQuestion,
            newQuestion,
            showEditQuestionModal,
            editingQuestion,
            editingQuestionData,
            searchCollectorQuestions,
            searchGeneralQuestions,
            handleCollectorSearchInput,
            handleGeneralSearchInput,
            handleCollectorCategoryChange,
            handleGeneralCategoryChange,
            handleCollectorPurposeChange,
            handleGeneralPurposeChange,
            toggleCollectorCategoryDropdown,
            toggleGeneralCategoryDropdown,
            selectAllCollectorCategories,
            clearCollectorCategories,
            selectAllGeneralCategories,
            clearGeneralCategories,
            showCollectorCategoryDropdown,
            showGeneralCategoryDropdown,
            toggleCollectorPurposeDropdown,
            toggleGeneralPurposeDropdown,
            toggleCollectorPurpose,
            toggleGeneralPurpose,
            selectAllCollectorPurposes,
            selectAllGeneralPurposes,
            getPurposeName,
            selectAllPurposesForNewQuestion,
            selectAllPurposesForEditingQuestion,
            showCollectorPurposeDropdown,
            showGeneralPurposeDropdown,
            selectAllWizardCategories,
            deselectAllWizardCategories,
            openEditQuestionModal,
            closeEditQuestionModal,
            updateQuestion: handleUpdateQuestion,
            handleCreateQuestion,
            handleUpdateQuestion,
            updateDocument,
            updateDocumentConviviente,
            selectAllEditCategories,
            deselectAllEditCategories,
            addQuestionToWizard,
            removeQuestion,
            moveQuestion,
            handleQuestionTypeChange,
            isDragging,
            draggedIndex,
            dragOverIndex,
            multiSelectMode,
            selectedQuestions,
            questionsContainer,
            handleDragStart,
            handleDragEnd,
            handleDrop,
            getDropIndex,
            toggleMultiSelect,
            handleQuestionClick,
            deleteSelectedQuestions,
            addOption,
            removeOption,
            generateSlug,
            createNewQuestion,
            updateConditions,
            updateEligibilityRequirements,
            saveEligibilityRequirements,
            toggleSection,
            // Funciones para preguntas específicas
            addQuestionToSpecific,
            removeQuestionSpecific,
            handleDragStartSpecific,
            handleDropSpecific,
            handleQuestionClickSpecific,
            // Funciones para preguntas del solicitante
            addQuestionToSolicitante,
            removeQuestionSolicitante,
            handleDragStartSolicitante,
            handleDropSolicitante,
            handleQuestionClickSolicitante,
            // Funciones para preguntas del conviviente
            addQuestionToConviviente,
            removeQuestionConviviente,
            handleDragStartConviviente,
            handleDropConviviente,
            handleQuestionClickConviviente,
            // Funciones para condiciones
            updateConditionsSpecific,
            updateConditionsSolicitante,
            updateConditionsConviviente,
            updateQuestionsConviviente,
            // Funciones para documentos
            availableDocuments,
            getAvailableDocumentsForIndex,
            addDocument,
            removeDocument,
            addDocumentConviviente,
            removeDocumentConviviente,
            getDocumentName,
            // Modal de condiciones de documentos
            currentEditingDocument,
            currentEditingDocumentName,
            showDocumentConditionsModal,
            openDocumentConditionsModal,
            closeDocumentConditionsModal,
            showTestModalSolicitante,
            showTestModalConviviente,
            testAnswers,
            testAnswersConviviente,
            getQuestionOptions,
            hasConditionForQuestion,
            getConditionsForQuestion,
            getQuestionTextById,
            getOperatorText,
            formatConditionValue,
            evaluateConditionInTest,
            isQuestionVisibleInTest,
            hasConditionForQuestionConviviente,
            getConditionsForQuestionConviviente,
            getQuestionTextByIdConviviente,
            evaluateConditionInTestConviviente,
            isQuestionVisibleInTestConviviente,
            editingDocumentConditions,
            documentConditionsLogic,
            newDocumentRequirement,
            addDocumentRequirement,
            removeDocumentRequirement,
            editDocumentRequirement,
            addDocumentRuleToGroup,
            removeDocumentRuleFromGroup,
            saveDocumentConditions,
            canAddDocumentSimpleRequirement,
            canAddDocumentGroupRequirement,
            // Búsqueda de preguntas para condiciones de documentos
            showDocumentQuestionSearch,
            documentQuestionSearchTerm,
            filteredDocumentQuestions,
            documentDynamicOptions,
            toggleDocumentQuestionSearch,
            filterDocumentQuestions,
            selectDocumentQuestionForNew,
            selectDocumentQuestionForGroup,
            getDocumentQuestionText,
            getDocumentQuestionTypeForNew,
            getDocumentQuestionTypeForRule,
            getDocumentQuestionOptionsForRule,
            getDocumentAvailableOperatorsForNew,
            getDocumentAvailableOperatorsForRule,
            getDocumentOperatorText,
            formatDocumentValue,
            formatDocumentRuleValue,
            getDocumentRequirements,
            getDocumentConditionsLogic,
            // Funciones para productos y servicios
            addProductService,
            removeProductService,
            addDocument,
            removeDocument,
            updateSelectedProducts,
            generateAyudaSlug,
            capitalizeFirst,
            formatCurrency,
            getNumericValue,
            getQuestionTextForEligibility,
            getOperatorTextForEligibility,
            formatValueForEligibility,
            formatRuleValueForEligibility,
            getPreRequisiteQuestionText,
            getPreRequisiteOperatorText,
            getPreRequisiteValueText,
            getPreRequisiteTargetText,
            getPreRequisiteTargetClass,
            getPreRequisiteBorderClass,
            getPreRequisiteIcon,
            getConvivienteTypeText,
            getAgeUnitText,
            getEligibilityPersonTypeText,
            getEligibilityPersonTypeClass,
            getEligibilityPersonTypeIcon,
        }
    },
}
</script>

<style scoped>
.wizard-container {
    min-height: 100vh;
    background-color: #f9fafb;
}

.wizard-steps {
    border-bottom: 1px solid #e5e7eb;
}

.wizard-content {
    background-color: #ffffff;
}

.drag-preview {
    transform: rotate(5deg);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.drag-over {
    border-color: #3b82f6 !important;
    background-color: #eff6ff !important;
    transform: scale(1.02);
}

.drag-placeholder {
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    border-radius: 2px;
    margin: 2px 0;
    opacity: 0.8;
}

.multi-select-item {
    transition: all 0.2s ease;
}

.multi-select-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.multi-select-item.selected {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
}

.question-item {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.question-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.drag-handle {
    transition: all 0.2s ease;
}

.drag-handle:hover {
    color: #6b7280;
    transform: scale(1.1);
}

.drag-handle:active {
    transform: scale(0.95);
}

.selection-checkbox {
    transition: all 0.2s ease;
}

.selection-checkbox:checked {
    background-color: #3b82f6;
    border-color: #3b82f6;
}

.drop-zone {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 0.6;
    }
    50% {
        opacity: 1;
    }
}
</style>
