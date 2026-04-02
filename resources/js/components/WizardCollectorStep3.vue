<template>
    <div class="bg-gray-50 rounded-lg p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h4 class="text-lg font-medium text-gray-800">
                Tipos de convivientes
            </h4>
            <div class="flex items-center space-x-3">
                <button
                    @click="showDuplicateConvivienteTypeModal = true"
                    :disabled="convivienteTypes.length === 0"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <i class="fas fa-copy mr-2"></i>
                    Duplicar como base
                </button>
                <button
                    @click="showCreateConvivienteTypeModal = true"
                    class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition-colors"
                >
                    <i class="fas fa-plus mr-2"></i>
                    Añadir tipo de conviviente
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-if="convivienteTypes.length === 0"
            class="text-center py-8 text-gray-500"
        >
            <i class="fas fa-users text-4xl mb-4 text-orange-300"></i>
            <p class="text-lg font-medium mb-2">
                No hay tipos de convivientes configurados
            </p>
            <p class="text-sm">
                Añade el primer tipo de conviviente para comenzar
            </p>
        </div>

        <!-- Conviviente Types List -->
        <div v-else class="space-y-6">
            <div
                v-for="(convivienteType, index) in convivienteTypes"
                :key="convivienteType.id"
                :draggable="true"
                @dragstart="handleConvivienteTypeDragStart(index)"
                @dragover="handleConvivienteTypeDragOver($event, index)"
                @drop="handleConvivienteTypeDrop(index)"
                @dragend="handleConvivienteTypeDragEnd"
                :class="[
                    'bg-white border border-orange-200 rounded-lg p-4 transition-all duration-200',
                    draggedFromConvivienteTypeIndex === index
                        ? 'opacity-50 transform rotate-1 bg-blue-50 border-blue-300'
                        : dragOverConvivienteTypeIndex === index
                        ? 'bg-blue-100 border-blue-400 shadow-md'
                        : 'hover:shadow-md hover:border-orange-300'
                ]"
            >
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center"
                        >
                            <i
                                :class="convivienteType.icon"
                                class="text-orange-600"
                            ></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">
                                {{ convivienteType.name }}
                            </h4>
                            <p class="text-sm text-gray-500">
                                {{ convivienteType.sections.length }}
                                sección(es) configurada(s)
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div
                            class="text-gray-400 hover:text-gray-600 cursor-move p-1"
                            title="Arrastrar para reordenar"
                        >
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                        <button
                            v-if="convivienteType.sections.length > 0"
                            @click="toggleAllConvivienteSections(index)"
                            class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-md transition-colors"
                            title="Expandir/Colapsar todas las secciones"
                        >
                            <i
                                :class="[
                                    'fas transition-transform duration-200',
                                    allConvivienteSectionsCollapsed[index]
                                        ? 'fa-chevron-down'
                                        : 'fa-chevron-up',
                                ]"
                            ></i>
                            <span>{{
                                allConvivienteSectionsCollapsed[index]
                                    ? "Expandir todas"
                                    : "Colapsar todas"
                            }}</span>
                        </button>
                        <button
                            @click="deleteConvivienteType(index)"
                            class="p-2 text-red-400 hover:text-red-600 transition-colors"
                            title="Eliminar tipo"
                        >
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <div
                    v-if="convivienteType.sections.length > 0"
                    class="space-y-3"
                >
                    <div
                        v-for="(
                            section, sectionIndex
                        ) in convivienteType.sections"
                        :key="sectionIndex"
                        class="border border-gray-200 rounded-lg"
                    >
                        <div
                            @click="
                                toggleConvivienteSectionCollapse(
                                    index,
                                    sectionIndex,
                                )
                            "
                            class="flex items-center justify-between p-3 bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors"
                        >
                            <div class="flex items-center space-x-3">
                                <i
                                    :class="[
                                        'fas transition-transform duration-200',
                                        collapsedConvivienteSections.includes(
                                            `${index}-${sectionIndex}`,
                                        )
                                            ? 'fa-chevron-down'
                                            : 'fa-chevron-up',
                                    ]"
                                ></i>
                                <h6 class="font-medium text-gray-900">
                                    {{ section.name }}
                                </h6>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-gray-500">
                                        {{
                                            section.questions
                                                ? section.questions.length
                                                : 0
                                        }}
                                        pregunta(s)
                                    </span>
                                    <span
                                        v-if="section.skipCondition"
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                        title="Esta sección se puede saltar"
                                    >
                                        <i class="fas fa-forward mr-1"></i>
                                        Skippeable
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button
                                    @click.stop="
                                        configureConvivienteSectionSkip(
                                            index,
                                            sectionIndex,
                                        )
                                    "
                                    class="p-1 text-purple-400 hover:text-purple-600 transition-colors"
                                    title="Configurar salto de sección"
                                >
                                    <i class="fas fa-forward text-xs"></i>
                                </button>
                                <button
                                    @click.stop="
                                        editConvivienteSection(
                                            index,
                                            sectionIndex,
                                        )
                                    "
                                    class="p-1 text-orange-400 hover:text-orange-600 transition-colors"
                                    title="Editar sección"
                                >
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <button
                                    @click.stop="
                                        showDuplicateBetweenConvivientesModal = true;
                                        selectedConvivienteTypeIndex = index;
                                        selectedConvivienteSectionIndex =
                                            sectionIndex;
                                    "
                                    class="p-1 text-blue-400 hover:text-blue-600 transition-colors"
                                    title="Duplicar a otros tipos"
                                >
                                    <i class="fas fa-copy text-xs"></i>
                                </button>
                                <button
                                    @click.stop="
                                        deleteConvivienteSection(
                                            index,
                                            sectionIndex,
                                        )
                                    "
                                    class="p-1 text-red-400 hover:text-red-600 transition-colors"
                                    title="Eliminar sección"
                                >
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Questions with Drag & Drop and Conditions -->
                        <div
                            v-if="
                                !collapsedConvivienteSections.includes(
                                    `${index}-${sectionIndex}`,
                                ) &&
                                section.questions &&
                                section.questions.length > 0
                            "
                            class="p-3 space-y-2"
                        >
                            <div
                                v-for="(question, qIndex) in section.questions"
                                :key="`${index}-${sectionIndex}-${qIndex}`"
                                :draggable="true"
                                @dragstart="
                                    handleConvivienteDragStart(
                                        index,
                                        sectionIndex,
                                        qIndex,
                                    )
                                "
                                @dragover="
                                    handleConvivienteDragOver(
                                        $event,
                                        index,
                                        sectionIndex,
                                        qIndex,
                                    )
                                "
                                @drop="
                                    handleConvivienteDrop(
                                        index,
                                        sectionIndex,
                                        qIndex,
                                    )
                                "
                                @dragend="handleConvivienteDragEnd"
                                :class="[
                                    'flex items-center justify-between p-3 rounded border transition-all duration-200',
                                    draggedFromConvivienteType === index &&
                                    draggedFromConvivienteSection ===
                                        sectionIndex &&
                                    draggedFromConvivienteIndex === qIndex
                                        ? 'opacity-50 transform rotate-2 bg-blue-50 border-blue-300'
                                        : dragOverConvivienteType === index &&
                                            dragOverConvivienteSection ===
                                                sectionIndex &&
                                            dragOverConvivienteIndex === qIndex
                                          ? 'bg-blue-100 border-blue-400 shadow-md'
                                          : 'bg-gray-50 border-gray-200 hover:bg-gray-100 hover:border-gray-300',
                                ]"
                            >
                                <div class="flex items-center space-x-3 flex-1">
                                    <div
                                        class="text-gray-400 hover:text-gray-600 cursor-move"
                                    >
                                        <i class="fas fa-grip-vertical"></i>
                                    </div>

                                    <div class="flex-1">
                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <p
                                                class="text-sm font-medium text-gray-800"
                                            >
                                                {{ question.text }}
                                            </p>
                                            <span
                                                v-if="
                                                    question.blockIfBankflipFilled
                                                "
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                            >
                                                <i class="fas fa-lock mr-1"></i>
                                                Bloqueada
                                            </span>
                                            <span
                                                v-if="hasConditionalOptions(question)"
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                            >
                                                <i class="fas fa-filter mr-1"></i>
                                                Opciones condicionadas
                                            </span>
                                            <span
                                                v-if="hasSelectedOptions(question)"
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                            >
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Opciones específicas
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            {{
                                                questionTypes[question.type] ||
                                                question.type
                                            }}
                                            <span v-if="question.slug" class="text-gray-400">
                                                • {{ question.slug }}
                                            </span>
                                        </p>
                                        
                                        <div 
                                            v-if="(question.type === 'select' || question.type === 'multiple') && question.options && question.options.length > 0"
                                            class="mt-2"
                                        >
                                            <div class="flex flex-wrap gap-1">
                                                <span
                                                    v-for="(option, optionIndex) in getConvivienteVisibleOptions(question)"
                                                    :key="optionIndex"
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium transition-colors"
                                                    :class="{
                                                        'bg-green-100 text-green-800 border border-green-200': isOptionSelected(question, option),
                                                        'bg-gray-100 text-gray-500 border border-gray-200 opacity-60': !isOptionSelected(question, option)
                                                    }"
                                                >
                                                    <i 
                                                        v-if="isOptionSelected(question, option)"
                                                        class="fas fa-check-circle mr-1 text-xs"
                                                    ></i>
                                                    <i 
                                                        v-else
                                                        class="fas fa-circle mr-1 text-xs opacity-40"
                                                    ></i>
                                                    {{ option.text || option }}
                                                </span>
                                                <button
                                                    v-if="question.options.length > 3"
                                                    @click="toggleConvivienteOptionsExpansion(question)"
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors cursor-pointer"
                                                >
                                                    {{ isConvivienteOptionsExpanded(question) ? 'Ver menos' : `+${question.options.length - 3} más` }}
                                                </button>
                                            </div>
                                            <div 
                                                v-if="hasSelectedOptions(question)"
                                                class="mt-2 text-xs text-gray-600"
                                            >
                                                <i class="fas fa-info-circle mr-1"></i>
                                                <span class="font-medium text-green-600">{{ getSelectedOptionsCount(question) }}</span> de {{ question.options.length }} opciones seleccionadas
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center flex-wrap gap-2">
                                        <span class="text-[11px] text-gray-500 font-medium">Pantalla</span>
                                        <div class="inline-flex items-center bg-gray-100 rounded-md border border-gray-200 overflow-hidden">
                                            <button
                                                type="button"
                                                @click="setConvScreenDisplay(question, Math.max(1, getConvScreenDisplay(question) - 1), index, sectionIndex, qIndex)"
                                                class="px-2 py-1 text-gray-600 hover:bg-gray-200 text-xs"
                                                title="Anterior"
                                            >
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <div class="px-2 py-1 text-gray-800 text-xs min-w-[2rem] text-center">
                                                {{ getConvScreenDisplay(question) }}
                                            </div>
                                            <button
                                                type="button"
                                                @click="setConvScreenDisplay(question, getConvScreenDisplay(question) + 1, index, sectionIndex, qIndex)"
                                                class="px-2 py-1 text-gray-600 hover:bg-gray-200 text-xs"
                                                title="Siguiente"
                                            >
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div
                                        class="relative"
                                        ref="conditionDropdown"
                                    >
                                        <button
                                            @click="
                                                toggleConditionDropdown(
                                                    index,
                                                    sectionIndex,
                                                    qIndex,
                                                )
                                            "
                                            class="p-2 text-gray-400 hover:text-gray-600 transition-colors relative"
                                            title="Configurar condiciones"
                                        >
                                            <i class="fas fa-cog"></i>
                                            <div
                                                v-if="
                                                    question.condition ||
                                                    question.isRequired ||
                                                    question.requiredCondition ||
                                                    question.optionalCondition
                                                "
                                                class="absolute -top-1 -right-1 w-3 h-3 bg-blue-500 rounded-full border-2 border-white"
                                            ></div>
                                        </button>

                                        <div
                                            v-if="
                                                activeConditionDropdown ===
                                                `${index}-${sectionIndex}-${qIndex}`
                                            "
                                            class="absolute right-0 top-full mt-1 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50"
                                        >
                                            <div class="py-1">
                                                <button
                                                    @click="
                                                        openConvivienteConditionModal(
                                                            index,
                                                            sectionIndex,
                                                            qIndex,
                                                        );
                                                        closeConditionDropdown();
                                                    "
                                                    class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                                                >
                                                    <i
                                                        class="fas fa-code-branch text-purple-500 mr-3"
                                                    ></i>
                                                    <span
                                                        >Configurar
                                                        visibilidad</span
                                                    >
                                                    <span
                                                        v-if="
                                                            question.condition
                                                        "
                                                        class="ml-auto text-xs text-purple-600 font-medium"
                                                        >✓</span
                                                    >
                                                </button>

                                                <button
                                                    v-if="(question.type === 'select' || question.type === 'multiple') && question.options && question.options.length > 0"
                                                    @click="
                                                        openConvivienteConditionalOptionsModal(
                                                            index,
                                                            sectionIndex,
                                                            qIndex,
                                                        );
                                                        closeConditionDropdown();
                                                    "
                                                    class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                                                >
                                                    <i
                                                        class="fas fa-filter text-orange-500 mr-3"
                                                    ></i>
                                                    <span
                                                        >Configurar opciones condicionadas</span
                                                    >
                                                    <span
                                                        v-if="
                                                            question.conditionalOptions
                                                        "
                                                        class="ml-auto text-xs text-orange-600 font-medium"
                                                        >✓</span
                                                    >
                                                </button>

                                                <button
                                                    v-if="(question.type === 'select' || question.type === 'multiple') && question.options && question.options.length > 0"
                                                    @click="
                                                        openSimpleOptionsModalForExistingQuestion(
                                                            index,
                                                            sectionIndex,
                                                            qIndex,
                                                        );
                                                        closeConditionDropdown();
                                                    "
                                                    class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                                                >
                                                    <i
                                                        class="fas fa-check-circle text-green-500 mr-3"
                                                    ></i>
                                                    <span
                                                        >Configurar opciones específicas</span
                                                    >
                                                    <span
                                                        v-if="
                                                            hasSelectedOptions(question)
                                                        "
                                                        class="ml-auto text-xs text-green-600 font-medium"
                                                        >✓</span
                                                    >
                                                </button>

                                                <button
                                                    @click="
                                                        openConvivienteRequiredConditionModal(
                                                            index,
                                                            sectionIndex,
                                                            qIndex,
                                                        );
                                                        closeConditionDropdown();
                                                    "
                                                    class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                                                >
                                                    <i
                                                        class="fas fa-exclamation-triangle text-green-500 mr-3"
                                                    ></i>
                                                    <span
                                                        >Configurar
                                                        obligatoriedad</span
                                                    >
                                                    <span
                                                        v-if="
                                                            question.isRequired ||
                                                            question.requiredCondition ||
                                                            question.optionalCondition
                                                        "
                                                        class="ml-auto text-xs text-green-600 font-medium"
                                                        >✓</span
                                                    >
                                                </button>

                                                <div
                                                    v-if="
                                                        question.condition ||
                                                        question.requiredCondition ||
                                                        question.optionalCondition
                                                    "
                                                    class="border-t border-gray-200 my-1"
                                                ></div>

                                                <div
                                                    v-if="
                                                        question.condition ||
                                                        question.requiredCondition ||
                                                        question.optionalCondition
                                                    "
                                                >
                                                    <button
                                                        v-if="
                                                            question.condition
                                                        "
                                                        @click="
                                                            removeConvivienteCondition(
                                                                index,
                                                                sectionIndex,
                                                                qIndex,
                                                            );
                                                            closeConditionDropdown();
                                                        "
                                                        class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center"
                                                    >
                                                        <i
                                                            class="fas fa-times text-red-500 mr-3"
                                                        ></i>
                                                        <span
                                                            >Eliminar
                                                            visibilidad</span
                                                        >
                                                    </button>
                                                    <button
                                                        v-if="
                                                            question.requiredCondition ||
                                                            question.optionalCondition
                                                        "
                                                        @click="
                                                            removeConvivienteRequiredCondition(
                                                                index,
                                                                sectionIndex,
                                                                qIndex,
                                                            );
                                                            closeConditionDropdown();
                                                        "
                                                        class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center"
                                                    >
                                                        <i
                                                            class="fas fa-times text-red-500 mr-3"
                                                        ></i>
                                                        <span
                                                            >Eliminar
                                                            obligatoriedad</span
                                                        >
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-1">
                                        <span
                                            v-if="question.condition"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                            title="Esta pregunta tiene condiciones de visibilidad"
                                        >
                                            <i class="fas fa-link mr-1"></i>
                                            Visibilidad
                                        </span>

                                        <span
                                            v-if="
                                                question.isRequired ||
                                                question.requiredCondition ||
                                                question.optionalCondition
                                            "
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                            :title="
                                                question.isRequired && !question.requiredCondition && !question.optionalCondition
                                                    ? 'Pregunta obligatoria por defecto'
                                                    : 'Esta pregunta tiene condiciones de obligatoriedad'
                                            "
                                        >
                                            <i
                                                class="fas fa-exclamation-triangle mr-1"
                                            ></i>
                                            {{ 
                                                question.isRequired && !question.requiredCondition && !question.optionalCondition
                                                    ? 'Obligatoria'
                                                    : 'Obligatoriedad'
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <button
                                    @click="
                                        removeQuestionFromConvivienteSection(
                                            index,
                                            sectionIndex,
                                            question.id,
                                        )
                                    "
                                    class="p-2 text-red-400 hover:text-red-600 transition-colors ml-2"
                                    title="Eliminar pregunta"
                                >
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <div
                            v-else-if="
                                !collapsedConvivienteSections.includes(
                                    `${index}-${sectionIndex}`,
                                )
                            "
                            class="p-3 text-sm text-gray-500 italic"
                        >
                            No hay preguntas en esta sección
                        </div>
                    </div>
                </div>

                <div v-else class="text-sm text-gray-500 italic">
                    No hay secciones configuradas para este tipo
                </div>

                <div class="mt-4 pt-3 border-t border-gray-200">
                    <div class="flex items-center space-x-3">
                        <button
                            @click="
                                showDuplicateSectionModal = true;
                                selectedConvivienteTypeIndex = index;
                            "
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                        >
                            <i class="fas fa-copy mr-1"></i>
                            Duplicar sección del solicitante
                        </button>
                        <button
                            @click="
                                showCreateConvivienteSectionModal = true;
                                selectedConvivienteTypeIndex = index;
                            "
                            class="text-sm text-orange-600 hover:text-orange-700 font-medium"
                        >
                            <i class="fas fa-plus mr-1"></i>
                            Añadir sección
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear/editar tipo de conviviente -->
    <div
        v-if="showCreateConvivienteTypeModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Añadir tipo de conviviente
                </h3>
                <button
                    @click="closeConvivienteTypeModal"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de conviviente
                    </label>
                    <select
                        v-model="convivienteTypeForm.type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                    >
                        <option value="">Seleccionar tipo</option>
                        <option value="conyuge">
                            Cónyuge o pareja de hecho registrada
                        </option>
                        <option value="hijo">Hijo/a</option>
                        <option value="padre">Padre/Madre</option>
                        <option value="familiar">Otro familiar</option>
                        <option value="otro">
                            No familiar (novio/a, amigo/a, compañero/a de piso)
                        </option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button
                    @click="closeConvivienteTypeModal"
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Cancelar
                </button>
                <button
                    @click="saveConvivienteType"
                    :disabled="!convivienteTypeForm.type"
                    class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 disabled:opacity-50"
                >
                    Añadir
                </button>
            </div>
        </div>
    </div>

    <div
        v-if="showDuplicateConvivienteTypeModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div
            class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto"
        >
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Duplicar tipo de conviviente como base
                </h3>
                <button
                    @click="closeDuplicateConvivienteTypeModal"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <p class="text-sm text-gray-600 mb-4">
                    Selecciona un tipo de conviviente existente para usar como
                    base y crear uno nuevo. Se copiarán todas las secciones y
                    preguntas configuradas.
                </p>

                <div
                    v-if="convivienteTypes.length === 0"
                    class="text-center py-8 text-gray-500"
                >
                    <i class="fas fa-users text-3xl mb-2"></i>
                    <p>No hay tipos de convivientes para duplicar</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="(convivienteType, index) in convivienteTypes"
                        :key="index"
                        class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors cursor-pointer"
                        @click="selectConvivienteTypeForDuplication(index)"
                        :class="{
                            'border-blue-500 bg-blue-50':
                                selectedConvivienteTypeForDuplication === index,
                        }"
                    >
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center"
                            >
                                <i
                                    :class="convivienteType.icon"
                                    class="text-orange-600"
                                ></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">
                                    {{ convivienteType.name }}
                                </h4>
                                <p class="text-sm text-gray-500">
                                    {{ convivienteType.sections?.length || 0 }}
                                    sección(es) configurada(s)
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ getTotalQuestions(convivienteType) }}
                                    pregunta(s) en total
                                </p>
                            </div>
                            <div
                                v-if="
                                    selectedConvivienteTypeForDuplication ===
                                    index
                                "
                                class="text-blue-600"
                            >
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="selectedConvivienteTypeForDuplication !== null"
                    class="mt-6 p-4 bg-blue-50 rounded-lg"
                >
                    <h4 class="font-medium text-blue-900 mb-2">
                        Nuevo tipo de conviviente
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Tipo de conviviente
                            </label>
                            <select
                                v-model="duplicateConvivienteTypeForm.type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">Seleccionar tipo</option>
                                <option value="conyuge">
                                    Cónyuge o pareja de hecho registrada
                                </option>
                                <option value="hijo">Hijo/a</option>
                                <option value="padre">Padre/Madre</option>
                                <option value="otro">Otro familiar</option>
                                <option value="no_familiar">No familiar</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button
                    @click="closeDuplicateConvivienteTypeModal"
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Cancelar
                </button>
                <button
                    @click="duplicateConvivienteType"
                    :disabled="
                        selectedConvivienteTypeForDuplication === null ||
                        !duplicateConvivienteTypeForm.type
                    "
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                >
                    <i class="fas fa-copy mr-2"></i>
                    Duplicar como base
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para crear sección de conviviente -->
    <div
        v-if="showCreateConvivienteSectionModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div
            class="bg-white rounded-lg p-6 w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto"
        >
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Añadir Sección a:
                    {{ convivienteTypes[selectedConvivienteTypeIndex]?.name }}
                </h3>
                <button
                    @click="closeCreateConvivienteSectionModal"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Reutilizar nombre de sección existente o crear nueva
                    </label>
                    <p class="text-xs text-gray-500 mb-2">
                        Solo se reutiliza el nombre de la sección, las preguntas
                        serán específicas para este tipo de conviviente
                    </p>
                    <Multiselect
                        v-model="convivienteSectionForm.selectedSection"
                        :options="availableSectionsForConviviente"
                        :searchable="true"
                        :allow-empty="false"
                        :taggable="true"
                        :create-option="true"
                        placeholder="Buscar nombre de sección existente o crear nueva..."
                        :custom-label="sectionLabel"
                        :close-on-select="true"
                        @select="onConvivienteSectionSelect"
                        @search-change="onConvivienteSectionSearchChange"
                        @tag="onConvivienteSectionTag"
                    >
                        <template #option="{ option }">
                            <div class="flex items-center justify-between">
                                <span>{{ option.name }}</span>
                                <span
                                    v-if="option.isNew"
                                    class="text-xs text-orange-600 font-medium"
                                    >Nueva</span
                                >
                            </div>
                        </template>
                    </Multiselect>
                </div>

                <div
                    v-if="
                        convivienteSectionForm.selectedSection &&
                        convivienteSectionForm.selectedSection.isNew
                    "
                >
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre de la nueva sección
                    </label>
                    <input
                        v-model="convivienteSectionForm.name"
                        type="text"
                        placeholder="Ej: Datos personales, Información laboral..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Buscar preguntas
                    </label>
                    <div class="relative">
                        <input
                            v-model="convivienteQuestionSearch"
                            @input="debouncedConvivienteSearch"
                            type="text"
                            placeholder="Buscar preguntas..."
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        />
                        <div
                            v-if="convivienteSearching"
                            class="absolute inset-y-0 right-0 flex items-center pr-3"
                        >
                            <div
                                class="animate-spin rounded-full h-4 w-4 border-b-2 border-orange-600"
                            ></div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium text-gray-800 mb-3">
                            Preguntas disponibles
                        </h4>
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            <div
                                v-for="question in filteredConvivienteQuestions"
                                :key="question.id"
                                class="flex items-center justify-between p-3 border border-gray-200 rounded hover:bg-gray-50"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-800"
                                    >
                                        {{ question.text }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{
                                            questionTypes[question.type] ||
                                            question.type
                                        }}
                                        <span v-if="question.slug" class="text-gray-400">
                                            • {{ question.slug }}
                                        </span>
                                    </p>
                                    
                                    <div 
                                        v-if="(question.type === 'select' || question.type === 'multiple') && question.options && question.options.length > 0"
                                        class="mt-2 flex flex-wrap gap-1"
                                    >
                                        <span
                                            v-for="(option, optionIndex) in getConvivienteVisibleOptions(question)"
                                            :key="optionIndex"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                        >
                                            {{ option.text || option }}
                                        </span>
                                        <button
                                            v-if="question.options.length > 3"
                                            @click="toggleConvivienteOptionsExpansion(question)"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors cursor-pointer"
                                        >
                                            {{ isConvivienteOptionsExpanded(question) ? 'Ver menos' : `+${question.options.length - 3} más` }}
                                        </button>
                                    </div>
                                </div>
                                <button
                                    @click="
                                        addQuestionToConvivienteSection(
                                            question,
                                        )
                                    "
                                    class="px-3 py-1 bg-orange-600 text-white text-xs rounded hover:bg-orange-700"
                                >
                                    Añadir
                                </button>
                            </div>
                            <div
                                v-if="
                                    filteredConvivienteQuestions.length === 0 &&
                                    !convivienteSearching
                                "
                                class="text-center py-4 text-gray-500"
                            >
                                No se encontraron preguntas
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-medium text-gray-800 mb-3">
                            Preguntas seleccionadas
                        </h4>
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            <div
                                v-for="question in convivienteSectionForm.questions"
                                :key="question.id"
                                class="flex items-center justify-between p-3 bg-orange-50 border border-orange-200 rounded"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-800"
                                    >
                                        {{ question.text }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{
                                            questionTypes[question.type] ||
                                            question.type
                                        }}
                                        <span v-if="question.slug" class="text-gray-400">
                                            • {{ question.slug }}
                                        </span>
                                    </p>
                                    
                                    <div 
                                        v-if="(question.type === 'select' || question.type === 'multiple') && question.options && question.options.length > 0"
                                        class="mt-2 flex flex-wrap gap-1"
                                    >
                                        <span
                                            v-for="(option, optionIndex) in getConvivienteVisibleOptions(question)"
                                            :key="optionIndex"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                        >
                                            {{ option.text || option }}
                                        </span>
                                        <button
                                            v-if="question.options.length > 3"
                                            @click="toggleConvivienteOptionsExpansion(question)"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors cursor-pointer"
                                        >
                                            {{ isConvivienteOptionsExpanded(question) ? 'Ver menos' : `+${question.options.length - 3} más` }}
                                        </button>
                                    </div>
                                </div>
                                <button
                                    @click="
                                        removeQuestionFromConvivienteSectionForm(
                                            question.id,
                                        )
                                    "
                                    class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700"
                                >
                                    Quitar
                                </button>
                            </div>
                            <div
                                v-if="
                                    convivienteSectionForm.questions.length ===
                                    0
                                "
                                class="text-center py-4 text-gray-500"
                            >
                                No hay preguntas seleccionadas
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button
                    @click="closeCreateConvivienteSectionModal"
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Cancelar
                </button>
                <button
                    @click="saveConvivienteSection"
                    :disabled="!canSaveConvivienteSection"
                    class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 disabled:opacity-50"
                >
                    {{
                        convivienteSectionForm.selectedSection?.isNew
                            ? "Crear"
                            : "Añadir"
                    }}
                    Sección
                </button>
            </div>
        </div>
    </div>

    <div
        v-if="showDuplicateSectionModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div
            class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto"
        >
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Duplicar secciones del solicitante a:
                    {{ convivienteTypes[selectedConvivienteTypeIndex]?.name }}
                </h3>
                <button
                    @click="closeDuplicateSectionModal"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <p class="text-sm text-gray-600 mb-4">
                    Selecciona las secciones del Step 2 que quieres duplicar
                    como base para este tipo de conviviente. Podrás modificar
                    las preguntas después de duplicar.
                </p>

                <div
                    v-if="sections.length === 0"
                    class="text-center py-8 text-gray-500"
                >
                    <i class="fas fa-folder-open text-3xl mb-2"></i>
                    <p>No hay secciones en el Step 2 para duplicar</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="(section, index) in sections"
                        :key="index"
                        class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">
                                    {{ section.name }}
                                </h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{
                                        section.description || "Sin descripción"
                                    }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{
                                        section.questions
                                            ? section.questions.length
                                            : 0
                                    }}
                                    pregunta(s)
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button
                                    @click="
                                        duplicateSectionToConviviente(index)
                                    "
                                    class="px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors"
                                >
                                    <i class="fas fa-copy mr-1"></i>
                                    Duplicar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button
                    @click="closeDuplicateSectionModal"
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <div
        v-if="showDuplicateBetweenConvivientesModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div
            class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto"
        >
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Duplicar sección a otros tipos de convivientes
                </h3>
                <button
                    @click="closeDuplicateBetweenConvivientesModal"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="font-medium text-blue-900 mb-2">
                        Sección a duplicar:
                    </h4>
                    <p class="text-sm text-blue-800">
                        {{ getSelectedSection()?.name }}
                    </p>
                    <p class="text-xs text-blue-600 mt-1">
                        {{ getSelectedSection()?.questions?.length || 0 }}
                        pregunta(s)
                    </p>
                </div>

                <p class="text-sm text-gray-600">
                    Selecciona los tipos de convivientes a los que quieres
                    duplicar esta sección.
                </p>

                <div
                    v-if="getAvailableConvivienteTypes().length === 0"
                    class="text-center py-8 text-gray-500"
                >
                    <i class="fas fa-users text-3xl mb-2"></i>
                    <p>No hay otros tipos de convivientes disponibles</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="(
                            convivienteType, index
                        ) in getAvailableConvivienteTypes()"
                        :key="index"
                        class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center"
                                >
                                    <i
                                        :class="convivienteType.icon"
                                        class="text-orange-600"
                                    ></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">
                                        {{ convivienteType.name }}
                                    </h4>
                                    <p class="text-sm text-gray-500">
                                        {{
                                            convivienteType.sections?.length ||
                                            0
                                        }}
                                        sección(es)
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="
                                    duplicateSectionBetweenConvivientes(index)
                                "
                                class="px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors"
                            >
                                <i class="fas fa-copy mr-1"></i>
                                Duplicar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button
                    @click="closeDuplicateBetweenConvivientesModal"
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <div
        v-if="showEditConvivienteSectionModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div
            class="bg-white rounded-lg p-6 w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto"
        >
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Editar Sección: {{ editConvivienteSectionForm.name }}
                </h3>
                <button
                    @click="closeEditConvivienteSectionModal"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre de la sección
                    </label>
                    <input
                        v-model="editConvivienteSectionForm.name"
                        type="text"
                        placeholder="Ej: Datos personales, Información laboral..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Buscar preguntas
                    </label>
                    <div class="relative">
                        <input
                            v-model="convivienteQuestionSearch"
                            @input="debouncedConvivienteSearch"
                            type="text"
                            placeholder="Buscar preguntas..."
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        />
                        <div
                            v-if="convivienteSearching"
                            class="absolute inset-y-0 right-0 flex items-center pr-3"
                        >
                            <div
                                class="animate-spin rounded-full h-4 w-4 border-b-2 border-orange-600"
                            ></div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium text-gray-800 mb-3">
                            Preguntas disponibles
                        </h4>
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            <div
                                v-for="question in filteredConvivienteQuestions"
                                :key="question.id"
                                class="flex items-center justify-between p-3 border border-gray-200 rounded hover:bg-gray-50"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-800"
                                    >
                                        {{ question.text }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{
                                            questionTypes[question.type] ||
                                            question.type
                                        }}
                                        <span v-if="question.slug" class="text-gray-400">
                                            • {{ question.slug }}
                                        </span>
                                    </p>
                                    
                                    <div 
                                        v-if="(question.type === 'select' || question.type === 'multiple') && question.options && question.options.length > 0"
                                        class="mt-2 flex flex-wrap gap-1"
                                    >
                                        <span
                                            v-for="(option, optionIndex) in getConvivienteVisibleOptions(question)"
                                            :key="optionIndex"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                        >
                                            {{ option.text || option }}
                                        </span>
                                        <button
                                            v-if="question.options.length > 3"
                                            @click="toggleConvivienteOptionsExpansion(question)"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors cursor-pointer"
                                        >
                                            {{ isConvivienteOptionsExpanded(question) ? 'Ver menos' : `+${question.options.length - 3} más` }}
                                        </button>
                                    </div>
                                </div>
                                <button
                                    @click="
                                        addQuestionToEditConvivienteSection(
                                            question,
                                        )
                                    "
                                    class="px-3 py-1 bg-orange-600 text-white text-xs rounded hover:bg-orange-700"
                                >
                                    Añadir
                                </button>
                            </div>
                            <div
                                v-if="
                                    filteredConvivienteQuestions.length === 0 &&
                                    !convivienteSearching
                                "
                                class="text-center py-4 text-gray-500"
                            >
                                No se encontraron preguntas
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-medium text-gray-800 mb-3">
                            Preguntas de la sección
                        </h4>
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            <div
                                v-for="question in editConvivienteSectionForm.questions"
                                :key="question.id"
                                class="flex items-center justify-between p-3 bg-orange-50 border border-orange-200 rounded"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-800"
                                    >
                                        {{ question.text }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{
                                            questionTypes[question.type] ||
                                            question.type
                                        }}
                                        <span v-if="question.slug" class="text-gray-400">
                                            • {{ question.slug }}
                                        </span>
                                    </p>
                                    
                                    <div 
                                        v-if="(question.type === 'select' || question.type === 'multiple') && question.options && question.options.length > 0"
                                        class="mt-2 flex flex-wrap gap-1"
                                    >
                                        <span
                                            v-for="(option, optionIndex) in getConvivienteVisibleOptions(question)"
                                            :key="optionIndex"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                        >
                                            {{ option.text || option }}
                                        </span>
                                        <button
                                            v-if="question.options.length > 3"
                                            @click="toggleConvivienteOptionsExpansion(question)"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors cursor-pointer"
                                        >
                                            {{ isConvivienteOptionsExpanded(question) ? 'Ver menos' : `+${question.options.length - 3} más` }}
                                        </button>
                                    </div>
                                </div>
                                <button
                                    @click="
                                        removeQuestionFromEditConvivienteSection(
                                            question.id,
                                        )
                                    "
                                    class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700"
                                >
                                    Quitar
                                </button>
                            </div>
                            <div
                                v-if="
                                    editConvivienteSectionForm.questions
                                        .length === 0
                                "
                                class="text-center py-4 text-gray-500"
                            >
                                No hay preguntas en esta sección
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button
                    @click="closeEditConvivienteSectionModal"
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Cancelar
                </button>
                <button
                    @click="saveEditConvivienteSection"
                    :disabled="
                        !editConvivienteSectionForm.name ||
                        editConvivienteSectionForm.questions.length === 0
                    "
                    class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 disabled:opacity-50"
                >
                    Guardar Cambios
                </button>
            </div>
        </div>
    </div>

    <ConditionModal
        :show="showConvivienteConditionModal"
        :question="convivienteConditionQuestion"
        :available-questions="getAvailableQuestionsForCondition()"
        :question-types="questionTypes"
        @close="closeConvivienteConditionModal"
        @save="saveConvivienteCondition"
    />

    <ConditionalOptionsModal
        :show="showConvivienteConditionalOptionsModal"
        :question="convivienteConditionalOptionsQuestion"
        :available-options="convivienteConditionalOptionsAvailableOptions"
        :available-questions="convivienteConditionalOptionsAvailableQuestions"
        @close="closeConvivienteConditionalOptionsModal"
        @save="saveConvivienteConditionalOptions"
    />

    <SimpleOptionsModal
        :show="showSimpleOptionsModal"
        :question="simpleOptionsQuestion"
        :available-options="simpleOptionsAvailableOptions"
        :selected-options="simpleOptionsSelectedOptions"
        @close="closeSimpleOptionsModal"
        @save="saveSimpleOptions"
    />

    <div
        v-if="showConvivienteRequiredConditionModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Configurar Condición
                </h3>
                <button
                    @click="closeConvivienteConditionModal"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pregunta actual
                    </label>
                    <div class="p-3 bg-gray-50 rounded border">
                        <p class="font-medium text-gray-800">
                            {{ convivienteConditionQuestion?.text }}
                        </p>
                        <p class="text-sm text-gray-500">
                            {{
                                questionTypes[
                                    convivienteConditionQuestion?.type
                                ] || convivienteConditionQuestion?.type
                            }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Depende de la pregunta
                    </label>
                    <multiselect
                        v-model="convivienteConditionForm.dependsOnQuestionId"
                        :options="getAvailableQuestionsForCondition()"
                        :custom-label="getQuestionDisplayLabel"
                        placeholder="Seleccionar pregunta..."
                        :searchable="true"
                        :close-on-select="true"
                        :show-labels="false"
                        :allow-empty="true"
                        :preserve-search="false"
                        class="w-full"
                    >
                        <template slot="option" slot-scope="{ option }">
                            <div
                                class="flex items-center justify-between w-full"
                            >
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">
                                        {{ option.text }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mr-2"
                                        >
                                            <i
                                                :class="
                                                    convivienteTypes[
                                                        option.typeIndex
                                                    ].icon
                                                "
                                                class="mr-1"
                                            ></i>
                                            {{
                                                convivienteTypes[
                                                    option.typeIndex
                                                ].name
                                            }}
                                        </span>
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                        >
                                            <i class="fas fa-folder mr-1"></i>
                                            {{
                                                convivienteTypes[
                                                    option.typeIndex
                                                ].sections[option.sectionIndex]
                                                    .name
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-400 ml-2">
                                    {{
                                        questionTypes[option.type] ||
                                        option.type
                                    }}
                                </div>
                            </div>
                        </template>
                        <template slot="singleLabel" slot-scope="{ option }">
                            <div v-if="option" class="flex items-center">
                                <span class="font-medium">{{
                                    option.text
                                }}</span>
                                <span class="ml-2 text-sm text-gray-500">
                                    ({{
                                        convivienteTypes[option.typeIndex].name
                                    }}
                                    -
                                    {{
                                        convivienteTypes[option.typeIndex]
                                            .sections[option.sectionIndex].name
                                    }})
                                </span>
                            </div>
                        </template>
                    </multiselect>
                </div>

                <div v-if="convivienteConditionForm.dependsOnQuestionId">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de condición
                    </label>
                    <select
                        v-model="convivienteConditionForm.conditionType"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    >
                        <option value="">Seleccionar tipo...</option>
                        <option value="equals">Es igual a</option>
                        <option value="not_equals">No es igual a</option>
                        <option value="contains">Contiene</option>
                        <option value="not_contains">No contiene</option>
                        <option value="greater_than">Mayor que</option>
                        <option value="less_than">Menor que</option>
                        <option value="is_empty">Está vacío</option>
                        <option value="is_not_empty">No está vacío</option>
                        <option value="age_less_than">
                            Tiene menos de X años
                        </option>
                        <option value="age_greater_than">
                            Tiene más de X años
                        </option>
                        <option value="age_between">
                            Tiene entre X y Y años
                        </option>
                        <option value="date_before">Fecha antes de</option>
                        <option value="date_after">Fecha después de</option>
                        <option value="date_between">Fecha entre</option>
                        <option value="is_today">Es hoy</option>
                        <option value="is_this_year">Es de este año</option>
                        <option value="is_this_month">Es de este mes</option>
                    </select>
                </div>

                <div
                    v-if="
                        convivienteConditionForm.conditionType &&
                        ![
                            'is_empty',
                            'is_not_empty',
                            'is_today',
                            'is_this_year',
                            'is_this_month',
                        ].includes(convivienteConditionForm.conditionType)
                    "
                >
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Valor esperado
                    </label>
                    <input
                        v-if="
                            getConditionQuestionType() === 'text' ||
                            getConditionQuestionType() === 'textarea' ||
                            getConditionQuestionType() === 'email' ||
                            getConditionQuestionType() === 'tel'
                        "
                        v-model="convivienteConditionForm.expectedValue"
                        type="text"
                        placeholder="Valor esperado..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    />
                    <input
                        v-else-if="getConditionQuestionType() === 'number'"
                        v-model="convivienteConditionForm.expectedValue"
                        type="number"
                        placeholder="Valor numérico..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    />
                    <div
                        v-if="
                            ['age_less_than', 'age_greater_than'].includes(
                                convivienteConditionForm.conditionType,
                            )
                        "
                        class="space-y-2"
                    >
                        <input
                            v-model="convivienteConditionForm.expectedValue"
                            type="number"
                            min="0"
                            max="150"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            :placeholder="
                                convivienteConditionForm.conditionType ===
                                'age_less_than'
                                    ? 'Edad máxima (ej: 18)'
                                    : 'Edad mínima (ej: 21)'
                            "
                        />
                        <p class="text-xs text-gray-500">
                            {{
                                convivienteConditionForm.conditionType ===
                                "age_less_than"
                                    ? "La persona debe tener menos de esta edad"
                                    : "La persona debe tener más de esta edad"
                            }}
                        </p>
                    </div>

                    <div
                        v-else-if="
                            convivienteConditionForm.conditionType ===
                            'age_between'
                        "
                        class="space-y-2"
                    >
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1"
                                    >Edad mínima</label
                                >
                                <input
                                    v-model="
                                        convivienteConditionForm.expectedValue
                                    "
                                    type="number"
                                    min="0"
                                    max="150"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="18"
                                />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1"
                                    >Edad máxima</label
                                >
                                <input
                                    v-model="
                                        convivienteConditionForm.expectedValue2
                                    "
                                    type="number"
                                    min="0"
                                    max="150"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="65"
                                />
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">
                            La persona debe tener entre estas edades
                        </p>
                    </div>

                    <div
                        v-else-if="
                            ['date_before', 'date_after'].includes(
                                convivienteConditionForm.conditionType,
                            )
                        "
                        class="space-y-2"
                    >
                        <input
                            v-model="convivienteConditionForm.expectedValue"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        />
                        <p class="text-xs text-gray-500">
                            {{
                                convivienteConditionForm.conditionType ===
                                "date_before"
                                    ? "La fecha debe ser anterior a esta"
                                    : "La fecha debe ser posterior a esta"
                            }}
                        </p>
                    </div>

                    <div
                        v-else-if="
                            convivienteConditionForm.conditionType ===
                            'date_between'
                        "
                        class="space-y-2"
                    >
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1"
                                    >Fecha desde</label
                                >
                                <input
                                    v-model="
                                        convivienteConditionForm.expectedValue
                                    "
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1"
                                    >Fecha hasta</label
                                >
                                <input
                                    v-model="
                                        convivienteConditionForm.expectedValue2
                                    "
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                />
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">
                            La fecha debe estar entre estos dos valores
                        </p>
                    </div>

                    <div v-else>
                        <input
                            v-if="
                                getConditionQuestionType() === 'text' ||
                                getConditionQuestionType() === 'textarea' ||
                                getConditionQuestionType() === 'email' ||
                                getConditionQuestionType() === 'tel'
                            "
                            v-model="convivienteConditionForm.expectedValue"
                            type="text"
                            placeholder="Valor esperado..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        />
                        <input
                            v-else-if="getConditionQuestionType() === 'number'"
                            v-model="convivienteConditionForm.expectedValue"
                            type="number"
                            placeholder="Valor numérico..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        />
                        <input
                            v-else-if="getConditionQuestionType() === 'date'"
                            v-model="convivienteConditionForm.expectedValue"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        />
                        <div
                            v-else-if="
                                getConditionQuestionType() === 'boolean' ||
                                getConditionQuestionType() === 'checkbox'
                            "
                        >
                            <select
                                v-model="convivienteConditionForm.expectedValue"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            >
                                <option value="">Seleccionar...</option>
                                <option
                                    :value="
                                        getConditionQuestionType() === 'boolean'
                                            ? 'Sí'
                                            : 'true'
                                    "
                                >
                                    Sí
                                </option>
                                <option
                                    :value="
                                        getConditionQuestionType() === 'boolean'
                                            ? 'No'
                                            : 'false'
                                    "
                                >
                                    No
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div
                    v-if="
                        convivienteConditionForm.dependsOnQuestionId &&
                        convivienteConditionForm.conditionType
                    "
                >
                    <div
                        class="p-3 bg-purple-50 border border-purple-200 rounded"
                    >
                        <h4 class="font-medium text-purple-800 mb-2">
                            Resumen de la condición:
                        </h4>
                        <p class="text-sm text-purple-700">
                            Esta pregunta se mostrará solo si la pregunta
                            <strong>{{ getConditionQuestionText() }}</strong>
                            <span
                                v-if="
                                    convivienteConditionForm.conditionType ===
                                    'equals'
                                "
                            >
                                es igual a
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'not_equals'
                                "
                            >
                                no es igual a
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'contains'
                                "
                            >
                                contiene
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'not_contains'
                                "
                            >
                                no contiene
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'greater_than'
                                "
                            >
                                es mayor que
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'less_than'
                                "
                            >
                                es menor que
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'is_empty'
                                "
                            >
                                está vacío
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'is_not_empty'
                                "
                            >
                                no está vacío
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'age_less_than'
                                "
                            >
                                indica que la persona tiene menos de
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'age_greater_than'
                                "
                            >
                                indica que la persona tiene más de
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'age_between'
                                "
                            >
                                indica que la persona tiene entre
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'date_before'
                                "
                            >
                                es anterior a
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'date_after'
                                "
                            >
                                es posterior a
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'date_between'
                                "
                            >
                                está entre
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'is_today'
                                "
                            >
                                es hoy
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'is_this_year'
                                "
                            >
                                es de este año
                            </span>
                            <span
                                v-else-if="
                                    convivienteConditionForm.conditionType ===
                                    'is_this_month'
                                "
                            >
                                es de este mes
                            </span>
                            <span
                                v-if="
                                    convivienteConditionForm.expectedValue &&
                                    ![
                                        'is_empty',
                                        'is_not_empty',
                                        'is_today',
                                        'is_this_year',
                                        'is_this_month',
                                    ].includes(
                                        convivienteConditionForm.conditionType,
                                    )
                                "
                            >
                                <strong
                                    v-if="
                                        [
                                            'age_less_than',
                                            'age_greater_than',
                                        ].includes(
                                            convivienteConditionForm.conditionType,
                                        )
                                    "
                                >
                                    {{ convivienteConditionForm.expectedValue }}
                                    años
                                </strong>
                                <strong
                                    v-else-if="
                                        convivienteConditionForm.conditionType ===
                                        'age_between'
                                    "
                                >
                                    {{ convivienteConditionForm.expectedValue }}
                                    y
                                    {{
                                        convivienteConditionForm.expectedValue2
                                    }}
                                    años
                                </strong>
                                <strong
                                    v-else-if="
                                        ['date_before', 'date_after'].includes(
                                            convivienteConditionForm.conditionType,
                                        )
                                    "
                                >
                                    {{ convivienteConditionForm.expectedValue }}
                                </strong>
                                <strong
                                    v-else-if="
                                        convivienteConditionForm.conditionType ===
                                        'date_between'
                                    "
                                >
                                    {{ convivienteConditionForm.expectedValue }}
                                    y
                                    {{
                                        convivienteConditionForm.expectedValue2
                                    }}
                                </strong>
                                <strong v-else>
                                    "{{
                                        convivienteConditionForm.expectedValue
                                    }}"
                                </strong>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button
                    @click="closeConvivienteConditionModal"
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Cancelar
                </button>
                <button
                    @click="saveConvivienteCondition"
                    :disabled="!canSaveConvivienteCondition"
                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50"
                >
                    Guardar Condición
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para configurar condiciones de obligatoriedad -->
    <div
        v-if="showConvivienteRequiredConditionModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Configurar condiciones de obligatoriedad
                </h3>
                <button
                    @click="closeConvivienteRequiredConditionModal"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pregunta actual
                    </label>
                    <div class="p-3 bg-gray-50 rounded border">
                        <p class="font-medium text-gray-800">
                            {{ convivienteRequiredConditionQuestion?.text }}
                        </p>
                        <p class="text-sm text-gray-500">
                            {{
                                questionTypes[
                                    convivienteRequiredConditionQuestion?.type
                                ] || convivienteRequiredConditionQuestion?.type
                            }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Obligatoriedad por defecto
                    </label>
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input
                                v-model="
                                    convivienteRequiredConditionForm.isRequired
                                "
                                type="checkbox"
                                class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                            />
                            <span class="ml-2 text-sm text-gray-700"
                                >Pregunta obligatoria por defecto</span
                            >
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Hacer obligatoria cuando
                    </label>
                    <div class="space-y-3">
                        <multiselect
                            v-model="
                                convivienteRequiredConditionForm.requiredDependsOnQuestionId
                            "
                            :options="
                                getAvailableQuestionsForRequiredCondition()
                            "
                            :custom-label="getQuestionDisplayLabel"
                            placeholder="Seleccionar pregunta..."
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            :allow-empty="true"
                            :preserve-search="false"
                            class="w-full"
                        >
                            <template slot="option" slot-scope="{ option }">
                                <div
                                    class="flex items-center justify-between w-full"
                                >
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">
                                            {{ option.text }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <span
                                                v-if="option.isSolicitante"
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2"
                                            >
                                                <i class="fas fa-user mr-1"></i>
                                                Solicitante
                                            </span>
                                            <span
                                                v-else-if="option.isConviviente"
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mr-2"
                                            >
                                                <i
                                                    :class="
                                                        convivienteTypes[
                                                            option.typeIndex
                                                        ].icon
                                                    "
                                                    class="mr-1"
                                                ></i>
                                                {{
                                                    convivienteTypes[
                                                        option.typeIndex
                                                    ].name
                                                }}
                                            </span>
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                            >
                                                <i
                                                    class="fas fa-folder mr-1"
                                                ></i>
                                                {{ option.sectionName }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-400 ml-2">
                                        {{
                                            questionTypes[option.type] ||
                                            option.type
                                        }}
                                    </div>
                                </div>
                            </template>
                            <template
                                slot="singleLabel"
                                slot-scope="{ option }"
                            >
                                <div v-if="option" class="flex items-center">
                                    <span class="font-medium">{{
                                        option.text
                                    }}</span>
                                    <span class="ml-2 text-sm text-gray-500">
                                        <span v-if="option.isSolicitante">
                                            (Solicitante - {{ option.sectionName }})
                                        </span>
                                        <span v-else-if="option.isConviviente">
                                            ({{ convivienteTypes[option.typeIndex].name }} - {{ option.sectionName }})
                                        </span>
                                        <span v-else>
                                            ({{ option.sectionName }})
                                        </span>
                                    </span>
                                </div>
                            </template>
                        </multiselect>

                        <select
                            v-if="
                                convivienteRequiredConditionForm.requiredDependsOnQuestionId
                            "
                            v-model="
                                convivienteRequiredConditionForm.requiredConditionType
                            "
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                            <option value="">Seleccionar tipo...</option>
                            <option value="equals">Es igual a</option>
                            <option value="not_equals">No es igual a</option>
                            <option value="contains">Contiene</option>
                            <option value="not_contains">No contiene</option>
                            <option value="greater_than">Mayor que</option>
                            <option value="less_than">Menor que</option>
                            <option value="is_empty">Está vacío</option>
                            <option value="is_not_empty">No está vacío</option>
                            <option value="age_less_than">
                                Tiene menos de X años
                            </option>
                            <option value="age_greater_than">
                                Tiene más de X años
                            </option>
                            <option value="age_between">
                                Tiene entre X y Y años
                            </option>
                            <option value="date_before">Fecha antes de</option>
                            <option value="date_after">Fecha después de</option>
                            <option value="date_between">Fecha entre</option>
                            <option value="is_today">Es hoy</option>
                            <option value="is_this_year">Es de este año</option>
                            <option value="is_this_month">
                                Es de este mes
                            </option>
                        </select>

                        <div
                            v-if="
                                convivienteRequiredConditionForm.requiredConditionType &&
                                ![
                                    'is_empty',
                                    'is_not_empty',
                                    'is_today',
                                    'is_this_year',
                                    'is_this_month',
                                ].includes(
                                    convivienteRequiredConditionForm.requiredConditionType,
                                )
                            "
                        >
                            <div
                                v-if="
                                    [
                                        'age_less_than',
                                        'age_greater_than',
                                    ].includes(
                                        convivienteRequiredConditionForm.requiredConditionType,
                                    )
                                "
                                class="space-y-2"
                            >
                                <input
                                    v-model="
                                        convivienteRequiredConditionForm.requiredExpectedValue
                                    "
                                    type="number"
                                    min="0"
                                    max="150"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    :placeholder="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'age_less_than'
                                            ? 'Edad máxima (ej: 18)'
                                            : 'Edad mínima (ej: 21)'
                                    "
                                />
                                <p class="text-xs text-gray-500">
                                    {{
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        "age_less_than"
                                            ? "La pregunta será obligatoria si la persona tiene menos de esta edad"
                                            : "La pregunta será obligatoria si la persona tiene más de esta edad"
                                    }}
                                </p>
                            </div>

                            <div
                                v-else-if="
                                    convivienteRequiredConditionForm.requiredConditionType ===
                                    'age_between'
                                "
                                class="space-y-2"
                            >
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label
                                            class="block text-xs text-gray-600 mb-1"
                                            >Edad mínima</label
                                        >
                                        <input
                                            v-model="
                                                convivienteRequiredConditionForm.requiredExpectedValue
                                            "
                                            type="number"
                                            min="0"
                                            max="150"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            placeholder="18"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs text-gray-600 mb-1"
                                            >Edad máxima</label
                                        >
                                        <input
                                            v-model="
                                                convivienteRequiredConditionForm.requiredExpectedValue2
                                            "
                                            type="number"
                                            min="0"
                                            max="150"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            placeholder="65"
                                        />
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">
                                    La pregunta será obligatoria si la persona
                                    tiene entre estas edades
                                </p>
                            </div>

                            <div
                                v-else-if="
                                    ['date_before', 'date_after'].includes(
                                        convivienteRequiredConditionForm.requiredConditionType,
                                    )
                                "
                                class="space-y-2"
                            >
                                <input
                                    v-model="
                                        convivienteRequiredConditionForm.requiredExpectedValue
                                    "
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                                <p class="text-xs text-gray-500">
                                    {{
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        "date_before"
                                            ? "La pregunta será obligatoria si la fecha es anterior a esta"
                                            : "La pregunta será obligatoria si la fecha es posterior a esta"
                                    }}
                                </p>
                            </div>

                            <div
                                v-else-if="
                                    convivienteRequiredConditionForm.requiredConditionType ===
                                    'date_between'
                                "
                                class="space-y-2"
                            >
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label
                                            class="block text-xs text-gray-600 mb-1"
                                            >Fecha desde</label
                                        >
                                        <input
                                            v-model="
                                                convivienteRequiredConditionForm.requiredExpectedValue
                                            "
                                            type="date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs text-gray-600 mb-1"
                                            >Fecha hasta</label
                                        >
                                        <input
                                            v-model="
                                                convivienteRequiredConditionForm.requiredExpectedValue2
                                            "
                                            type="date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        />
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">
                                    La pregunta será obligatoria si la fecha
                                    está entre estos dos valores
                                </p>
                            </div>

                            <div v-else>
                                <input
                                    v-if="
                                        getRequiredConditionQuestionType() ===
                                            'text' ||
                                        getRequiredConditionQuestionType() ===
                                            'textarea' ||
                                        getRequiredConditionQuestionType() ===
                                            'email' ||
                                        getRequiredConditionQuestionType() ===
                                            'tel'
                                    "
                                    v-model="
                                        convivienteRequiredConditionForm.requiredExpectedValue
                                    "
                                    type="text"
                                    placeholder="Valor esperado..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                                <input
                                    v-else-if="
                                        getRequiredConditionQuestionType() ===
                                        'number'
                                    "
                                    v-model="
                                        convivienteRequiredConditionForm.requiredExpectedValue
                                    "
                                    type="number"
                                    placeholder="Valor numérico..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                                <input
                                    v-else-if="
                                        getRequiredConditionQuestionType() ===
                                        'date'
                                    "
                                    v-model="
                                        convivienteRequiredConditionForm.requiredExpectedValue
                                    "
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                                <div
                                    v-else-if="
                                        getRequiredConditionQuestionType() ===
                                            'boolean' ||
                                        getRequiredConditionQuestionType() ===
                                            'checkbox' ||
                                        getRequiredConditionQuestionType() ===
                                            'select' ||
                                        getRequiredConditionQuestionType() ===
                                            'multiple'
                                    "
                                >
                                    <select
                                        v-model="
                                            convivienteRequiredConditionForm.requiredExpectedValue
                                        "
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    >
                                        <option value="">Seleccionar...</option>
                                        <option
                                            v-for="option in getRequiredConditionQuestionOptions()"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.text }}
                                        </option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Selecciona el valor que debe cumplir la condición
                                    </p>
                                </div>
                                <div v-else>
                                    <input
                                        v-model="
                                            convivienteRequiredConditionForm.requiredExpectedValue
                                        "
                                        type="text"
                                        placeholder="Valor esperado..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">
                                        Ingresa el valor que debe cumplir la condición
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Hacer opcional cuando
                    </label>
                    <div class="space-y-3">
                        <multiselect
                            v-model="
                                convivienteRequiredConditionForm.optionalDependsOnQuestionId
                            "
                            :options="
                                getAvailableQuestionsForRequiredCondition()
                            "
                            :custom-label="getQuestionDisplayLabel"
                            placeholder="Seleccionar pregunta..."
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            :allow-empty="true"
                            :preserve-search="false"
                            class="w-full"
                        >
                            <template slot="option" slot-scope="{ option }">
                                <div
                                    class="flex items-center justify-between w-full"
                                >
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">
                                            {{ option.text }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <span
                                                v-if="option.isSolicitante"
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2"
                                            >
                                                <i class="fas fa-user mr-1"></i>
                                                Solicitante
                                            </span>
                                            <span
                                                v-else-if="option.isConviviente"
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mr-2"
                                            >
                                                <i
                                                    :class="
                                                        convivienteTypes[
                                                            option.typeIndex
                                                        ].icon
                                                    "
                                                    class="mr-1"
                                                ></i>
                                                {{
                                                    convivienteTypes[
                                                        option.typeIndex
                                                    ].name
                                                }}
                                            </span>
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                            >
                                                <i
                                                    class="fas fa-folder mr-1"
                                                ></i>
                                                {{ option.sectionName }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-400 ml-2">
                                        {{
                                            questionTypes[option.type] ||
                                            option.type
                                        }}
                                    </div>
                                </div>
                            </template>
                            <template
                                slot="singleLabel"
                                slot-scope="{ option }"
                            >
                                <div v-if="option" class="flex items-center">
                                    <span class="font-medium">{{
                                        option.text
                                    }}</span>
                                    <span class="ml-2 text-sm text-gray-500">
                                        <span v-if="option.isSolicitante">
                                            (Solicitante - {{ option.sectionName }})
                                        </span>
                                        <span v-else-if="option.isConviviente">
                                            ({{ convivienteTypes[option.typeIndex].name }} - {{ option.sectionName }})
                                        </span>
                                        <span v-else>
                                            ({{ option.sectionName }})
                                        </span>
                                    </span>
                                </div>
                            </template>
                        </multiselect>

                        <select
                            v-if="
                                convivienteRequiredConditionForm.optionalDependsOnQuestionId
                            "
                            v-model="
                                convivienteRequiredConditionForm.optionalConditionType
                            "
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                            <option value="">Seleccionar tipo...</option>
                            <option value="equals">Es igual a</option>
                            <option value="not_equals">No es igual a</option>
                            <option value="contains">Contiene</option>
                            <option value="not_contains">No contiene</option>
                            <option value="greater_than">Mayor que</option>
                            <option value="less_than">Menor que</option>
                            <option value="is_empty">Está vacío</option>
                            <option value="is_not_empty">No está vacío</option>
                            <option value="age_less_than">
                                Tiene menos de X años
                            </option>
                            <option value="age_greater_than">
                                Tiene más de X años
                            </option>
                            <option value="age_between">
                                Tiene entre X y Y años
                            </option>
                            <option value="date_before">Fecha antes de</option>
                            <option value="date_after">Fecha después de</option>
                            <option value="date_between">Fecha entre</option>
                            <option value="is_today">Es hoy</option>
                            <option value="is_this_year">Es de este año</option>
                            <option value="is_this_month">
                                Es de este mes
                            </option>
                        </select>

                        <div
                            v-if="
                                convivienteRequiredConditionForm.optionalConditionType &&
                                ![
                                    'is_empty',
                                    'is_not_empty',
                                    'is_today',
                                    'is_this_year',
                                    'is_this_month',
                                ].includes(
                                    convivienteRequiredConditionForm.optionalConditionType,
                                )
                            "
                        >
                            <div
                                v-if="
                                    [
                                        'age_less_than',
                                        'age_greater_than',
                                    ].includes(
                                        convivienteRequiredConditionForm.optionalConditionType,
                                    )
                                "
                                class="space-y-2"
                            >
                                <input
                                    v-model="
                                        convivienteRequiredConditionForm.optionalExpectedValue
                                    "
                                    type="number"
                                    min="0"
                                    max="150"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    :placeholder="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'age_less_than'
                                            ? 'Edad máxima (ej: 18)'
                                            : 'Edad mínima (ej: 21)'
                                    "
                                />
                                <p class="text-xs text-gray-500">
                                    {{
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        "age_less_than"
                                            ? "La pregunta será opcional si la persona tiene menos de esta edad"
                                            : "La pregunta será opcional si la persona tiene más de esta edad"
                                    }}
                                </p>
                            </div>

                            <div
                                v-else-if="
                                    convivienteRequiredConditionForm.optionalConditionType ===
                                    'age_between'
                                "
                                class="space-y-2"
                            >
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label
                                            class="block text-xs text-gray-600 mb-1"
                                            >Edad mínima</label
                                        >
                                        <input
                                            v-model="
                                                convivienteRequiredConditionForm.optionalExpectedValue
                                            "
                                            type="number"
                                            min="0"
                                            max="150"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            placeholder="18"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs text-gray-600 mb-1"
                                            >Edad máxima</label
                                        >
                                        <input
                                            v-model="
                                                convivienteRequiredConditionForm.optionalExpectedValue2
                                            "
                                            type="number"
                                            min="0"
                                            max="150"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            placeholder="65"
                                        />
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">
                                    La pregunta será opcional si la persona
                                    tiene entre estas edades
                                </p>
                            </div>

                            <div
                                v-else-if="
                                    ['date_before', 'date_after'].includes(
                                        convivienteRequiredConditionForm.optionalConditionType,
                                    )
                                "
                                class="space-y-2"
                            >
                                <input
                                    v-model="
                                        convivienteRequiredConditionForm.optionalExpectedValue
                                    "
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                                <p class="text-xs text-gray-500">
                                    {{
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        "date_before"
                                            ? "La pregunta será opcional si la fecha es anterior a esta"
                                            : "La pregunta será opcional si la fecha es posterior a esta"
                                    }}
                                </p>
                            </div>

                            <div
                                v-else-if="
                                    convivienteRequiredConditionForm.optionalConditionType ===
                                    'date_between'
                                "
                                class="space-y-2"
                            >
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label
                                            class="block text-xs text-gray-600 mb-1"
                                            >Fecha desde</label
                                        >
                                        <input
                                            v-model="
                                                convivienteRequiredConditionForm.optionalExpectedValue
                                            "
                                            type="date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs text-gray-600 mb-1"
                                            >Fecha hasta</label
                                        >
                                        <input
                                            v-model="
                                                convivienteRequiredConditionForm.optionalExpectedValue2
                                            "
                                            type="date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        />
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">
                                    La pregunta será opcional si la fecha está
                                    entre estos dos valores
                                </p>
                            </div>

                            <div v-else>
                                <input
                                    v-if="
                                        getOptionalConditionQuestionType() ===
                                            'text' ||
                                        getOptionalConditionQuestionType() ===
                                            'textarea' ||
                                        getOptionalConditionQuestionType() ===
                                            'email' ||
                                        getOptionalConditionQuestionType() ===
                                            'tel'
                                    "
                                    v-model="
                                        convivienteRequiredConditionForm.optionalExpectedValue
                                    "
                                    type="text"
                                    placeholder="Valor esperado..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                                <input
                                    v-else-if="
                                        getOptionalConditionQuestionType() ===
                                        'number'
                                    "
                                    v-model="
                                        convivienteRequiredConditionForm.optionalExpectedValue
                                    "
                                    type="number"
                                    placeholder="Valor numérico..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                                <input
                                    v-else-if="
                                        getOptionalConditionQuestionType() ===
                                        'date'
                                    "
                                    v-model="
                                        convivienteRequiredConditionForm.optionalExpectedValue
                                    "
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                                <div
                                    v-else-if="
                                        getOptionalConditionQuestionType() ===
                                            'boolean' ||
                                        getOptionalConditionQuestionType() ===
                                            'checkbox' ||
                                        getOptionalConditionQuestionType() ===
                                            'select' ||
                                        getOptionalConditionQuestionType() ===
                                            'multiple'
                                    "
                                >
                                    <select
                                        v-model="
                                            convivienteRequiredConditionForm.optionalExpectedValue
                                        "
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    >
                                        <option value="">Seleccionar...</option>
                                        <option
                                            v-for="option in getOptionalConditionQuestionOptions()"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.text }}
                                        </option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Selecciona el valor que debe cumplir la condición
                                    </p>
                                </div>
                                <div v-else>
                                    <input
                                        v-model="
                                            convivienteRequiredConditionForm.optionalExpectedValue
                                        "
                                        type="text"
                                        placeholder="Valor esperado..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">
                                        Ingresa el valor que debe cumplir la condición
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="
                        convivienteRequiredConditionForm.isRequired ||
                        convivienteRequiredConditionForm.requiredDependsOnQuestionId ||
                        convivienteRequiredConditionForm.optionalDependsOnQuestionId
                    "
                >
                    <div
                        class="p-3 bg-green-50 border border-green-200 rounded"
                    >
                        <h4 class="font-medium text-green-800 mb-2">
                            Resumen de obligatoriedad:
                        </h4>
                        <p class="text-sm text-green-700">
                            <span
                                v-if="
                                    convivienteRequiredConditionForm.isRequired
                                "
                                class="font-medium"
                                >Obligatoria por defecto</span
                            >
                            <span
                                v-if="
                                    convivienteRequiredConditionForm.requiredDependsOnQuestionId
                                "
                                class="font-medium"
                            >
                                <br />Obligatoria si:
                                {{ getRequiredConditionQuestionText() }}
                                <span
                                    v-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'equals'
                                    "
                                >
                                    es igual a
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'not_equals'
                                    "
                                >
                                    no es igual a
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'contains'
                                    "
                                >
                                    contiene
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'not_contains'
                                    "
                                >
                                    no contiene
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'greater_than'
                                    "
                                >
                                    es mayor que
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'less_than'
                                    "
                                >
                                    es menor que
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'is_empty'
                                    "
                                >
                                    está vacío
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'is_not_empty'
                                    "
                                >
                                    no está vacío
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'age_less_than'
                                    "
                                >
                                    indica que la persona tiene menos de
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'age_greater_than'
                                    "
                                >
                                    indica que la persona tiene más de
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'age_between'
                                    "
                                >
                                    indica que la persona tiene entre
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'date_before'
                                    "
                                >
                                    es anterior a
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'date_after'
                                    "
                                >
                                    es posterior a
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'date_between'
                                    "
                                >
                                    está entre
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'is_today'
                                    "
                                >
                                    es hoy
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'is_this_year'
                                    "
                                >
                                    es de este año
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.requiredConditionType ===
                                        'is_this_month'
                                    "
                                >
                                    es de este mes
                                </span>
                                <span
                                    v-if="
                                        convivienteRequiredConditionForm.requiredExpectedValue &&
                                        ![
                                            'is_empty',
                                            'is_not_empty',
                                            'is_today',
                                            'is_this_year',
                                            'is_this_month',
                                        ].includes(
                                            convivienteRequiredConditionForm.requiredConditionType,
                                        )
                                    "
                                >
                                    <strong
                                        v-if="
                                            [
                                                'age_less_than',
                                                'age_greater_than',
                                            ].includes(
                                                convivienteRequiredConditionForm.requiredConditionType,
                                            )
                                        "
                                    >
                                        {{
                                            convivienteRequiredConditionForm.requiredExpectedValue
                                        }}
                                        años
                                    </strong>
                                    <strong
                                        v-else-if="
                                            convivienteRequiredConditionForm.requiredConditionType ===
                                            'age_between'
                                        "
                                    >
                                        {{
                                            convivienteRequiredConditionForm.requiredExpectedValue
                                        }}
                                        y
                                        {{
                                            convivienteRequiredConditionForm.requiredExpectedValue2
                                        }}
                                        años
                                    </strong>
                                    <strong
                                        v-else-if="
                                            [
                                                'date_before',
                                                'date_after',
                                            ].includes(
                                                convivienteRequiredConditionForm.requiredConditionType,
                                            )
                                        "
                                    >
                                        {{
                                            convivienteRequiredConditionForm.requiredExpectedValue
                                        }}
                                    </strong>
                                    <strong
                                        v-else-if="
                                            convivienteRequiredConditionForm.requiredConditionType ===
                                            'date_between'
                                        "
                                    >
                                        {{
                                            convivienteRequiredConditionForm.requiredExpectedValue
                                        }}
                                        y
                                        {{
                                            convivienteRequiredConditionForm.requiredExpectedValue2
                                        }}
                                    </strong>
                                    <strong v-else>
                                        "{{
                                            convivienteRequiredConditionForm.requiredExpectedValue
                                        }}"
                                    </strong>
                                </span>
                            </span>
                            <span
                                v-if="
                                    convivienteRequiredConditionForm.optionalDependsOnQuestionId
                                "
                                class="font-medium"
                            >
                                <br />Opcional si:
                                {{ getOptionalConditionQuestionText() }}
                                <span
                                    v-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'equals'
                                    "
                                >
                                    es igual a
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'not_equals'
                                    "
                                >
                                    no es igual a
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'contains'
                                    "
                                >
                                    contiene
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'not_contains'
                                    "
                                >
                                    no contiene
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'greater_than'
                                    "
                                >
                                    es mayor que
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'less_than'
                                    "
                                >
                                    es menor que
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'is_empty'
                                    "
                                >
                                    está vacío
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'is_not_empty'
                                    "
                                >
                                    no está vacío
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'age_less_than'
                                    "
                                >
                                    indica que la persona tiene menos de
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'age_greater_than'
                                    "
                                >
                                    indica que la persona tiene más de
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'age_between'
                                    "
                                >
                                    indica que la persona tiene entre
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'date_before'
                                    "
                                >
                                    es anterior a
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'date_after'
                                    "
                                >
                                    es posterior a
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'date_between'
                                    "
                                >
                                    está entre
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'is_today'
                                    "
                                >
                                    es hoy
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'is_this_year'
                                    "
                                >
                                    es de este año
                                </span>
                                <span
                                    v-else-if="
                                        convivienteRequiredConditionForm.optionalConditionType ===
                                        'is_this_month'
                                    "
                                >
                                    es de este mes
                                </span>
                                <span
                                    v-if="
                                        convivienteRequiredConditionForm.optionalExpectedValue &&
                                        ![
                                            'is_empty',
                                            'is_not_empty',
                                            'is_today',
                                            'is_this_year',
                                            'is_this_month',
                                        ].includes(
                                            convivienteRequiredConditionForm.optionalConditionType,
                                        )
                                    "
                                >
                                    <strong
                                        v-if="
                                            [
                                                'age_less_than',
                                                'age_greater_than',
                                            ].includes(
                                                convivienteRequiredConditionForm.optionalConditionType,
                                            )
                                        "
                                    >
                                        {{
                                            convivienteRequiredConditionForm.optionalExpectedValue
                                        }}
                                        años
                                    </strong>
                                    <strong
                                        v-else-if="
                                            convivienteRequiredConditionForm.optionalConditionType ===
                                            'age_between'
                                        "
                                    >
                                        {{
                                            convivienteRequiredConditionForm.optionalExpectedValue
                                        }}
                                        y
                                        {{
                                            convivienteRequiredConditionForm.optionalExpectedValue2
                                        }}
                                        años
                                    </strong>
                                    <strong
                                        v-else-if="
                                            [
                                                'date_before',
                                                'date_after',
                                            ].includes(
                                                convivienteRequiredConditionForm.optionalConditionType,
                                            )
                                        "
                                    >
                                        {{
                                            convivienteRequiredConditionForm.optionalExpectedValue
                                        }}
                                    </strong>
                                    <strong
                                        v-else-if="
                                            convivienteRequiredConditionForm.optionalConditionType ===
                                            'date_between'
                                        "
                                    >
                                        {{
                                            convivienteRequiredConditionForm.optionalExpectedValue
                                        }}
                                        y
                                        {{
                                            convivienteRequiredConditionForm.optionalExpectedValue2
                                        }}
                                    </strong>
                                    <strong v-else>
                                        "{{
                                            convivienteRequiredConditionForm.optionalExpectedValue
                                        }}"
                                    </strong>
                                </span>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button
                    @click="closeConvivienteRequiredConditionModal"
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Cancelar
                </button>
                <button
                    @click="saveConvivienteRequiredCondition"
                    :disabled="!canSaveConvivienteRequiredCondition"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
                >
                    Guardar Condiciones
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para configurar salto de sección -->
    <div
        v-if="showConvivienteSectionSkipModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Configurar salto de sección
                </h3>
                <button
                    @click="closeConvivienteSectionSkipModal"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div class="p-4 bg-purple-50 rounded-lg">
                    <h4 class="font-medium text-purple-800 mb-2">Sección:</h4>
                    <p class="text-sm text-purple-700">
                        {{ convivienteSectionSkipForm.sectionName }}
                    </p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Esta sección se saltará cuando:
                        </label>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-600 mb-2">
                                1. Seleccionar persona:
                            </label>
                            <multiselect
                                v-model="selectedPersonForSkip"
                                :options="getAvailablePersonsForSkip()"
                                :custom-label="(person) => person.name"
                                placeholder="Seleccionar persona..."
                                :searchable="true"
                                :close-on-select="true"
                                :show-labels="false"
                                :allow-empty="true"
                                :preserve-search="false"
                                class="w-full"
                            >
                                <template #option="{ option }">
                                    <div class="flex items-center space-x-2">
                                        <i :class="option.icon" class="text-gray-400"></i>
                                        <span class="font-medium">{{ option.name }}</span>
                                    </div>
                                </template>
                            </multiselect>
                        </div>

                        <div v-if="selectedPersonForSkip">
                            <label class="block text-sm font-medium text-gray-600 mb-2">
                                2. Seleccionar pregunta:
                            </label>
                            <multiselect
                                v-model="selectedQuestionForSkip"
                                :options="getQuestionsForSelectedPerson()"
                                :custom-label="(question) => question.text"
                                placeholder="Seleccionar pregunta..."
                                :searchable="true"
                                :close-on-select="true"
                                :show-labels="false"
                                :allow-empty="true"
                                :preserve-search="false"
                                class="w-full"
                            >
                                <template #option="{ option }">
                                    <div class="flex items-center space-x-2">
                                        <div class="flex-1">
                                            <div class="font-medium">{{ option.text }}</div>
                                            <div class="text-sm text-gray-500">{{ option.sectionName }}</div>
                                        </div>
                                    </div>
                                </template>
                            </multiselect>
                        </div>
                    </div>
                </div>

                <div v-if="convivienteSectionSkipForm.dependsOnQuestionId">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Condición:
                    </label>

                    <div
                        v-if="
                            getConvivienteSectionSkipQuestionType() ===
                            'boolean'
                        "
                    >
                        <select
                            v-model="convivienteSectionSkipForm.conditionType"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                            <option value="">Seleccionar condición...</option>
                            <option value="is_true">Es Sí</option>
                            <option value="is_false">Es No</option>
                        </select>
                    </div>

                    <div
                        v-else-if="
                            getConvivienteSectionSkipQuestionType() === 'select'
                        "
                    >
                        <select
                            v-model="convivienteSectionSkipForm.conditionType"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                            <option value="">Seleccionar condición...</option>
                            <option value="equals">Es igual a</option>
                            <option value="not_equals">No es igual a</option>
                        </select>
                    </div>

                    <div
                        v-else-if="
                            getConvivienteSectionSkipQuestionType() ===
                            'checkbox'
                        "
                    >
                        <select
                            v-model="convivienteSectionSkipForm.conditionType"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                            <option value="">Seleccionar condición...</option>
                            <option value="is_checked">Está marcado</option>
                            <option value="is_not_checked">
                                No está marcado
                            </option>
                        </select>
                    </div>

                    <div
                        v-else-if="
                            getConvivienteSectionSkipQuestionType() === 'text'
                        "
                    >
                        <select
                            v-model="convivienteSectionSkipForm.conditionType"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                            <option value="">Seleccionar condición...</option>
                            <option value="equals">Es igual a</option>
                            <option value="not_equals">No es igual a</option>
                            <option value="contains">Contiene</option>
                            <option value="not_contains">No contiene</option>
                            <option value="is_empty">Está vacío</option>
                            <option value="is_not_empty">No está vacío</option>
                        </select>
                    </div>

                    <div
                        v-else-if="
                            getConvivienteSectionSkipQuestionType() === 'number'
                        "
                    >
                        <select
                            v-model="convivienteSectionSkipForm.conditionType"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                            <option value="">Seleccionar condición...</option>
                            <option value="equals">Es igual a</option>
                            <option value="not_equals">No es igual a</option>
                            <option value="greater_than">Mayor que</option>
                            <option value="less_than">Menor que</option>
                            <option value="greater_than_or_equal">
                                Mayor o igual que
                            </option>
                            <option value="less_than_or_equal">
                                Menor o igual que
                            </option>
                        </select>
                    </div>

                    <div
                        v-else-if="
                            getConvivienteSectionSkipQuestionType() === 'date'
                        "
                    >
                        <select
                            v-model="convivienteSectionSkipForm.conditionType"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                            <option value="">Seleccionar condición...</option>
                            <option value="equals">Es igual a</option>
                            <option value="not_equals">No es igual a</option>
                            <option value="before">Antes de</option>
                            <option value="after">Después de</option>
                            <option value="between">Entre</option>
                            <option value="is_today">Es hoy</option>
                            <option value="is_this_year">Es de este año</option>
                            <option value="is_this_month">
                                Es de este mes
                            </option>
                            <option value="age_less_than">
                                Tiene menos de X años
                            </option>
                            <option value="age_greater_than">
                                Tiene más de X años
                            </option>
                            <option value="age_between">
                                Tiene entre X y Y años
                            </option>
                        </select>
                    </div>

                    <div v-else>
                        <select
                            v-model="convivienteSectionSkipForm.conditionType"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                            <option value="">Seleccionar condición...</option>
                            <option value="equals">Es igual a</option>
                            <option value="not_equals">No es igual a</option>
                            <option value="is_empty">Está vacío</option>
                            <option value="is_not_empty">No está vacío</option>
                        </select>
                    </div>
                </div>

                <div
                    v-if="
                        convivienteSectionSkipForm.conditionType &&
                        ![
                            'is_empty',
                            'is_not_empty',
                            'is_today',
                            'is_this_year',
                            'is_this_month',
                            'is_checked',
                            'is_not_checked',
                            'is_true',
                            'is_false',
                        ].includes(convivienteSectionSkipForm.conditionType)
                    "
                >
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Valor esperado:
                    </label>
                    <div
                        v-if="
                            getConvivienteSectionSkipQuestionType() ===
                                'select' &&
                            getConvivienteSectionSkipQuestionOptions().length >
                                0
                        "
                    >
                        <select
                            v-model="convivienteSectionSkipForm.expectedValue"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                            <option value="">Seleccionar opción...</option>
                            <option
                                v-for="option in getConvivienteSectionSkipQuestionOptions()"
                                :key="option"
                                :value="option"
                            >
                                {{ option }}
                            </option>
                        </select>
                    </div>
                    <div
                        v-else-if="
                            getConvivienteSectionSkipQuestionType() === 'number'
                        "
                    >
                        <input
                            v-model="convivienteSectionSkipForm.expectedValue"
                            type="number"
                            placeholder="Ingrese el número"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        />
                    </div>
                    <div
                        v-else-if="
                            getConvivienteSectionSkipQuestionType() === 'date'
                        "
                    >
                        <div
                            v-if="
                                convivienteSectionSkipForm.conditionType ===
                                'between'
                            "
                        >
                            <div class="grid grid-cols-2 gap-4">
                                <input
                                    v-model="
                                        convivienteSectionSkipForm.expectedValue
                                    "
                                    type="date"
                                    placeholder="Fecha desde"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                />
                                <input
                                    v-model="
                                        convivienteSectionSkipForm.expectedValue2
                                    "
                                    type="date"
                                    placeholder="Fecha hasta"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                />
                            </div>
                        </div>
                        <div
                            v-else-if="
                                ['age_less_than', 'age_greater_than'].includes(
                                    convivienteSectionSkipForm.conditionType,
                                )
                            "
                        >
                            <input
                                v-model="
                                    convivienteSectionSkipForm.expectedValue
                                "
                                type="number"
                                :placeholder="
                                    convivienteSectionSkipForm.conditionType ===
                                    'age_less_than'
                                        ? 'Edad máxima (ej: 18)'
                                        : 'Edad mínima (ej: 21)'
                                "
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            />
                            <p class="text-xs text-gray-500 mt-1">
                                {{
                                    convivienteSectionSkipForm.conditionType ===
                                    "age_less_than"
                                        ? "La sección se saltará si la persona tiene menos de esta edad"
                                        : "La sección se saltará si la persona tiene más de esta edad"
                                }}
                            </p>
                        </div>
                        <div
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'age_between'
                            "
                        >
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Edad mínima
                                    </label>
                                    <input
                                        v-model="
                                            convivienteSectionSkipForm.expectedValue
                                        "
                                        type="number"
                                        placeholder="18"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Edad máxima
                                    </label>
                                    <input
                                        v-model="
                                            convivienteSectionSkipForm.expectedValue2
                                        "
                                        type="number"
                                        placeholder="65"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    />
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                La sección se saltará si la persona tiene entre
                                estas edades
                            </p>
                        </div>
                        <div v-else>
                            <input
                                v-model="
                                    convivienteSectionSkipForm.expectedValue
                                "
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            />
                        </div>
                    </div>
                    <div v-else>
                        <input
                            v-model="convivienteSectionSkipForm.expectedValue"
                            type="text"
                            placeholder="Valor esperado"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        />
                    </div>
                </div>

                <div
                    v-if="
                        convivienteSectionSkipForm.dependsOnQuestionId &&
                        convivienteSectionSkipForm.conditionType
                    "
                    class="bg-purple-50 p-4 rounded-lg"
                >
                    <h4 class="font-medium text-purple-800 mb-2">
                        Resumen de la condición:
                    </h4>
                    <p class="text-sm text-purple-700">
                        Esta sección se saltará cuando
                        <strong>{{
                            getConvivienteSectionSkipQuestionText()
                        }}</strong>
                        <span
                            v-if="
                                convivienteSectionSkipForm.conditionType ===
                                'is_true'
                            "
                        >
                            sea Sí
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'is_false'
                            "
                        >
                            sea No
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'is_checked'
                            "
                        >
                            esté marcado
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'is_not_checked'
                            "
                        >
                            no esté marcado
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'equals'
                            "
                        >
                            sea igual a
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'not_equals'
                            "
                        >
                            no sea igual a
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'contains'
                            "
                        >
                            contenga
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'not_contains'
                            "
                        >
                            no contenga
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'greater_than'
                            "
                        >
                            sea mayor que
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'less_than'
                            "
                        >
                            sea menor que
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'is_empty'
                            "
                        >
                            esté vacío
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'is_not_empty'
                            "
                        >
                            no esté vacío
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'age_less_than'
                            "
                        >
                            tenga menos de
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'age_greater_than'
                            "
                        >
                            tenga más de
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'age_between'
                            "
                        >
                            tenga entre
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'date_before'
                            "
                        >
                            sea anterior a
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'date_after'
                            "
                        >
                            sea posterior a
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'date_between'
                            "
                        >
                            esté entre
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'is_today'
                            "
                        >
                            sea hoy
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'is_this_year'
                            "
                        >
                            sea de este año
                        </span>
                        <span
                            v-else-if="
                                convivienteSectionSkipForm.conditionType ===
                                'is_this_month'
                            "
                        >
                            sea de este mes
                        </span>
                        <strong
                            v-if="
                                convivienteSectionSkipForm.expectedValue &&
                                ![
                                    'is_empty',
                                    'is_not_empty',
                                    'is_today',
                                    'is_this_year',
                                    'is_this_month',
                                ].includes(
                                    convivienteSectionSkipForm.conditionType,
                                )
                            "
                        >
                            {{
                                convivienteSectionSkipForm.conditionType ===
                                    "age_between" ||
                                convivienteSectionSkipForm.conditionType ===
                                    "date_between"
                                    ? `${convivienteSectionSkipForm.expectedValue} y ${convivienteSectionSkipForm.expectedValue2}`
                                    : convivienteSectionSkipForm.expectedValue
                            }}
                        </strong>
                    </p>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button
                    @click="closeConvivienteSectionSkipModal"
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Cancelar
                </button>
                <button
                    @click="removeConvivienteSectionSkip"
                    v-if="convivienteSectionSkipForm.dependsOnQuestionId"
                    type="button"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                >
                    Eliminar Salto
                </button>
                <button
                    @click="saveConvivienteSectionSkip"
                    :disabled="!canSaveConvivienteSectionSkip"
                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50"
                >
                    Guardar Configuración
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import Multiselect from "vue-multiselect";
import ConditionModal from "./ConditionModal.vue";
import ConditionalOptionsModal from "./ConditionalOptionsModal.vue";
import SimpleOptionsModal from "./SimpleOptionsModal.vue";
import "vue-multiselect/dist/vue-multiselect.min.css";

const props = defineProps({
    convivienteTypes: {
        type: Array,
        required: true,
    },
    sections: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["update:convivienteTypes", "show-notification"]);

// Reactive variables
const convivienteTypes = ref([]);
const showCreateConvivienteTypeModal = ref(false);
const showDuplicateConvivienteTypeModal = ref(false);
const showCreateConvivienteSectionModal = ref(false);
const showEditConvivienteSectionModal = ref(false);
const showDuplicateSectionModal = ref(false);
const showDuplicateBetweenConvivientesModal = ref(false);
const selectedConvivienteTypeIndex = ref(null);
const selectedConvivienteSectionIndex = ref(null);
const selectedConvivienteTypeForDuplication = ref(null);
const duplicateConvivienteTypeForm = ref({
    type: "",
});
const editingConvivienteSectionIndex = ref(null);
const convivienteQuestionSearch = ref("");
const filteredConvivienteQuestions = ref([]);
const convivienteSearching = ref(false);
const convivienteSearchTimeout = ref(null);

// Forms
const convivienteTypeForm = ref({
    type: "",
});
const convivienteSectionForm = ref({
    selectedSection: null,
    name: "",
    questions: [],
});

const editConvivienteSectionForm = ref({
    name: "",
    questions: [],
});

// Drag & Drop for conviviente questions
const draggedConvivienteQuestion = ref(null);
const draggedFromConvivienteType = ref(null);
const draggedFromConvivienteSection = ref(null);
const draggedFromConvivienteIndex = ref(null);
const dragOverConvivienteType = ref(null);
const dragOverConvivienteSection = ref(null);
const dragOverConvivienteIndex = ref(null);

const draggedConvivienteType = ref(null);
const draggedFromConvivienteTypeIndex = ref(null);
const dragOverConvivienteTypeIndex = ref(null);

// Condition modal
const showConvivienteConditionModal = ref(false);
const convivienteConditionQuestion = ref(null);
const convivienteConditionTypeIndex = ref(null);
const convivienteConditionSectionIndex = ref(null);
const convivienteConditionQuestionIndex = ref(null);
const convivienteConditionForm = ref({
    dependsOnQuestionId: "",
    conditionType: "",
    expectedValue: "",
    expectedValue2: "",
});

const showConvivienteRequiredConditionModal = ref(false);
const convivienteRequiredConditionQuestion = ref(null);
const convivienteRequiredConditionTypeIndex = ref(null);
const convivienteRequiredConditionSectionIndex = ref(null);
const convivienteRequiredConditionQuestionIndex = ref(null);

// Section skip modal
const showConvivienteSectionSkipModal = ref(false);
const convivienteSectionSkipTypeIndex = ref(null);
const convivienteSectionSkipSectionIndex = ref(null);
const convivienteSectionSkipForm = ref({
    sectionName: "",
    dependsOnQuestionId: "",
    conditionType: "",
    expectedValue: "",
    expectedValue2: "",
});

const selectedPersonForSkip = ref(null);
const selectedQuestionForSkip = ref(null);
const skipPrefillLock = ref(false);

const showConvivienteConditionalOptionsModal = ref(false);
const convivienteConditionalOptionsQuestion = ref(null);
const convivienteConditionalOptionsAvailableOptions = ref([]);
const convivienteConditionalOptionsAvailableQuestions = ref([]);
const convivientePendingConditionalOptionsData = ref(null);

const showSimpleOptionsModal = ref(false);
const simpleOptionsQuestion = ref(null);
const simpleOptionsAvailableOptions = ref([]);
const simpleOptionsSelectedOptions = ref([]);
const simpleOptionsPendingData = ref(null);
const expandedConvivienteOptions = ref(new Set());

const convivienteRequiredConditionForm = ref({
    isRequired: false,
    requiredDependsOnQuestionId: "",
    requiredConditionType: "",
    requiredExpectedValue: "",
    requiredExpectedValue2: "",
    optionalDependsOnQuestionId: "",
    optionalConditionType: "",
    optionalExpectedValue: "",
    optionalExpectedValue2: "",
});

// Other data
const questionTypes = ref({});
const allQuestions = ref([]);
const collapsedConvivienteSections = ref([]);
const allConvivienteSectionsCollapsed = ref({});

const activeConditionDropdown = ref(null);
const conditionDropdown = ref(null);

// Computed properties
const availableSectionsForConviviente = computed(() => {
    const existingSections = props.sections.map((section) => ({
        id: section.id,
        name: section.name,
        isNew: false,
    }));

    const newSectionOption = {
        id: "new",
        name: convivienteSectionForm.value.name || "Crear nueva sección...",
        isNew: true,
    };

    return [...existingSections, newSectionOption];
});

const canSaveConvivienteSection = computed(() => {
    if (!convivienteSectionForm.value.selectedSection) return false;
    if (convivienteSectionForm.value.selectedSection.isNew) {
        return (
            convivienteSectionForm.value.name.trim() !== "" &&
            convivienteSectionForm.value.questions.length > 0
        );
    }
    return convivienteSectionForm.value.questions.length > 0;
});

const canSaveConvivienteCondition = computed(() => {
    if (
        !convivienteConditionForm.value.dependsOnQuestionId ||
        !convivienteConditionForm.value.conditionType
    ) {
        return false;
    }

    if (
        [
            "is_empty",
            "is_not_empty",
            "is_today",
            "is_this_year",
            "is_this_month",
        ].includes(convivienteConditionForm.value.conditionType)
    ) {
        return true;
    }

    if (
        [
            "age_less_than",
            "age_greater_than",
            "date_before",
            "date_after",
        ].includes(convivienteConditionForm.value.conditionType)
    ) {
        return convivienteConditionForm.value.expectedValue !== "";
    }

    if (
        ["age_between", "date_between"].includes(
            convivienteConditionForm.value.conditionType,
        )
    ) {
        return (
            convivienteConditionForm.value.expectedValue !== "" &&
            convivienteConditionForm.value.expectedValue2 !== ""
        );
    }

    return convivienteConditionForm.value.expectedValue !== "";
});

const sectionLabel = (option) => {
    return option.name;
};

const toggleConvivienteSectionCollapse = (typeIndex, sectionIndex) => {
    const sectionKey = `${typeIndex}-${sectionIndex}`;
    const index = collapsedConvivienteSections.value.indexOf(sectionKey);
    if (index > -1) {
        collapsedConvivienteSections.value.splice(index, 1);
    } else {
        collapsedConvivienteSections.value.push(sectionKey);
    }
    updateAllConvivienteSectionsCollapsedState(typeIndex);
};

const toggleAllConvivienteSections = (typeIndex) => {
    const convivienteType = convivienteTypes.value[typeIndex];
    if (!convivienteType || !convivienteType.sections) return;

    const isCurrentlyCollapsed =
        allConvivienteSectionsCollapsed.value[typeIndex];

    if (isCurrentlyCollapsed) {
        convivienteType.sections.forEach((_, sectionIndex) => {
            const sectionKey = `${typeIndex}-${sectionIndex}`;
            const index =
                collapsedConvivienteSections.value.indexOf(sectionKey);
            if (index > -1) {
                collapsedConvivienteSections.value.splice(index, 1);
            }
        });
        allConvivienteSectionsCollapsed.value[typeIndex] = false;
    } else {
        convivienteType.sections.forEach((_, sectionIndex) => {
            const sectionKey = `${typeIndex}-${sectionIndex}`;
            if (!collapsedConvivienteSections.value.includes(sectionKey)) {
                collapsedConvivienteSections.value.push(sectionKey);
            }
        });
        allConvivienteSectionsCollapsed.value[typeIndex] = true;
    }
};

const updateAllConvivienteSectionsCollapsedState = (typeIndex) => {
    const convivienteType = convivienteTypes.value[typeIndex];
    if (!convivienteType || !convivienteType.sections) return;

    const totalSections = convivienteType.sections.length;
    const collapsedSections = convivienteType.sections.filter(
        (_, sectionIndex) => {
            const sectionKey = `${typeIndex}-${sectionIndex}`;
            return collapsedConvivienteSections.value.includes(sectionKey);
        },
    ).length;

    allConvivienteSectionsCollapsed.value[typeIndex] =
        collapsedSections === totalSections;
};

// Methods
const loadQuestionTypes = () => {
    questionTypes.value = {
        text: "Texto",
        textarea: "Área de texto",
        select: "Selección única",
        multiple: "Selección múltiple",
        number: "Número",
        email: "Email",
        tel: "Teléfono",
        date: "Fecha",
        checkbox: "Casilla de verificación",
        radio: "Botón de radio",
    };
};

const performConvivienteQuestionSearch = async (searchTerm) => {
    convivienteSearching.value = true;
    try {
        const params = new URLSearchParams({
            search: searchTerm,
            categories: "",
        });

        const response = await fetch(
            `/admin/wizards/questions/search?${params.toString()}`,
        );
        if (response.ok) {
            const data = await response.json();
            filteredConvivienteQuestions.value = (data.questions || []).filter(
                (q) => !isQuestionInConvivienteSectionForm(q.id),
            );
        }
    } catch (error) {
        console.error("Error buscando preguntas para conviviente:", error);
        filteredConvivienteQuestions.value = [];
    } finally {
        convivienteSearching.value = false;
    }
};

const searchConvivienteQuestions = async () => {
    if (!convivienteQuestionSearch.value.trim()) {
        await performConvivienteQuestionSearch("");
        return;
    }
    await performConvivienteQuestionSearch(convivienteQuestionSearch.value);
};

const debouncedConvivienteSearch = () => {
    if (convivienteSearchTimeout.value) {
        clearTimeout(convivienteSearchTimeout.value);
    }
    convivienteSearchTimeout.value = setTimeout(() => {
        searchConvivienteQuestions();
    }, 300);
};

// Conviviente Type methods

const saveConvivienteType = () => {
    if (!convivienteTypeForm.value.type) {
        return;
    }

    const typeConfig = {
        conyuge: {
            name: "Cónyuge o pareja de hecho registrada",
            icon: "fas fa-heart",
        },
        hijo: { name: "Hijo/a", icon: "fas fa-child" },
        padre: { name: "Padre/Madre", icon: "fas fa-male" },
        familiar: { name: "Otro familiar", icon: "fas fa-user" },
        otro: {
            name: "No familiar (novio/a, amigo/a, compañero/a de piso)",
            icon: "fas fa-home",
        },
    };

    const config = typeConfig[convivienteTypeForm.value.type];
    const newConvivienteType = {
        id: Date.now(),
        type: convivienteTypeForm.value.type,
        name: config.name,
        icon: config.icon,
        sections: [],
    };

    convivienteTypes.value.push(newConvivienteType);
    const newIndex = convivienteTypes.value.length - 1;
    allConvivienteSectionsCollapsed.value[newIndex] = true;

    closeConvivienteTypeModal();
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Tipo de conviviente guardado correctamente",
    );
};

const deleteConvivienteType = (index) => {
    if (
        confirm(
            "¿Estás seguro de que quieres eliminar este tipo de conviviente?",
        )
    ) {
        convivienteTypes.value.splice(index, 1);
        emit(
            "show-notification",
            "success",
            "Éxito",
            "Tipo de conviviente eliminado correctamente",
        );
    }
};

const closeConvivienteTypeModal = () => {
    showCreateConvivienteTypeModal.value = false;
    convivienteTypeForm.value = {
        type: "",
    };
};

// Section methods
const closeCreateConvivienteSectionModal = () => {
    showCreateConvivienteSectionModal.value = false;
    selectedConvivienteTypeIndex.value = null;
    convivienteQuestionSearch.value = "";
    filteredConvivienteQuestions.value = [];
    convivienteSectionForm.value = {
        selectedSection: null,
        name: "",
        questions: [],
    };
    if (convivienteSearchTimeout.value) {
        clearTimeout(convivienteSearchTimeout.value);
    }
};

const closeDuplicateSectionModal = () => {
    showDuplicateSectionModal.value = false;
    selectedConvivienteTypeIndex.value = null;
};

const duplicateSectionToConviviente = (sectionIndex) => {
    const sectionToDuplicate = props.sections[sectionIndex];
    const convivienteTypeIndex = selectedConvivienteTypeIndex.value;

    if (!convivienteTypes.value[convivienteTypeIndex].sections) {
        convivienteTypes.value[convivienteTypeIndex].sections = [];
    }

    const duplicatedSection = {
        name: sectionToDuplicate.name,
        description: sectionToDuplicate.description || "",
        questions: sectionToDuplicate.questions
            ? sectionToDuplicate.questions.map((question) => ({
                  id: question.id,
                  text: question.text,
                  slug: question.slug,
                  type: question.type,
                  options: question.options || [],
                  categories: question.categories || [],
                  purposes: question.purposes || [],
                  condition: question.condition || null,
                  requiredCondition: question.requiredCondition || null,
                  optionalCondition: question.optionalCondition || null,
              }))
            : [],
    };

    convivienteTypes.value[convivienteTypeIndex].sections.push(
        duplicatedSection,
    );

    emit("update:convivienteTypes", convivienteTypes.value);
    emit(
        "show-notification",
        "success",
        "Éxito",
        `Sección "${sectionToDuplicate.name}" duplicada correctamente`,
    );
    closeDuplicateSectionModal();
};

const closeDuplicateBetweenConvivientesModal = () => {
    showDuplicateBetweenConvivientesModal.value = false;
    selectedConvivienteTypeIndex.value = null;
    selectedConvivienteSectionIndex.value = null;
};

const getSelectedSection = () => {
    if (
        selectedConvivienteTypeIndex.value !== null &&
        selectedConvivienteSectionIndex.value !== null
    ) {
        return convivienteTypes.value[selectedConvivienteTypeIndex.value]
            ?.sections?.[selectedConvivienteSectionIndex.value];
    }
    return null;
};

const getAvailableConvivienteTypes = () => {
    return convivienteTypes.value
        .map((convivienteType, index) => ({
            ...convivienteType,
            originalIndex: index,
        }))
        .filter((_, index) => index !== selectedConvivienteTypeIndex.value);
};

const duplicateSectionBetweenConvivientes = (targetConvivienteTypeIndex) => {
    const sourceSection = getSelectedSection();
    const availableTypes = getAvailableConvivienteTypes();
    const targetConvivienteType =
        convivienteTypes.value[
            availableTypes[targetConvivienteTypeIndex].originalIndex
        ];

    if (!sourceSection) {
        emit(
            "show-notification",
            "error",
            "Error",
            "No se pudo obtener la sección a duplicar",
        );
        return;
    }

    if (!targetConvivienteType.sections) {
        targetConvivienteType.sections = [];
    }

    const duplicatedSection = {
        name: sourceSection.name,
        description: sourceSection.description || "",
        questions: sourceSection.questions
            ? sourceSection.questions.map((question) => ({
                  id: question.id,
                  text: question.text,
                  slug: question.slug,
                  type: question.type,
                  options: question.options || [],
                  categories: question.categories || [],
                  purposes: question.purposes || [],
                  condition: question.condition || null,
                  requiredCondition: question.requiredCondition || null,
                  optionalCondition: question.optionalCondition || null,
              }))
            : [],
    };

    targetConvivienteType.sections.push(duplicatedSection);
    emit("update:convivienteTypes", convivienteTypes.value);
    emit(
        "show-notification",
        "success",
        "Éxito",
        `Sección "${sourceSection.name}" duplicada a ${targetConvivienteType.name}`,
    );
};

const closeDuplicateConvivienteTypeModal = () => {
    showDuplicateConvivienteTypeModal.value = false;
    selectedConvivienteTypeForDuplication.value = null;
    duplicateConvivienteTypeForm.value = { type: "" };
};

const selectConvivienteTypeForDuplication = (index) => {
    selectedConvivienteTypeForDuplication.value = index;
};

const getTotalQuestions = (convivienteType) => {
    if (!convivienteType.sections) return 0;
    return convivienteType.sections.reduce((total, section) => {
        return total + (section.questions ? section.questions.length : 0);
    }, 0);
};

const duplicateConvivienteType = () => {
    if (selectedConvivienteTypeForDuplication.value === null) return;

    const sourceConvivienteType =
        convivienteTypes.value[selectedConvivienteTypeForDuplication.value];
    const newType = duplicateConvivienteTypeForm.value.type;

    const typeExists = convivienteTypes.value.some((ct) => ct.type === newType);
    if (typeExists) {
        emit(
            "show-notification",
            "error",
            "Error",
            "Este tipo de conviviente ya existe",
        );
        return;
    }

    const newConvivienteType = {
        id: `conviviente-${Date.now()}`,
        type: newType,
        name: getConvivienteTypeName(newType),
        icon: getConvivienteTypeIcon(newType),
        sections: sourceConvivienteType.sections
            ? sourceConvivienteType.sections.map((section) => ({
                  ...section,
                  questions: section.questions ? [...section.questions] : [],
              }))
            : [],
    };

    convivienteTypes.value.push(newConvivienteType);
    const newIndex = convivienteTypes.value.length - 1;
    allConvivienteSectionsCollapsed.value[newIndex] = true;

    emit("update:convivienteTypes", convivienteTypes.value);
    emit(
        "show-notification",
        "success",
        "Éxito",
        `Tipo "${newConvivienteType.name}" creado como base de "${sourceConvivienteType.name}"`,
    );
    closeDuplicateConvivienteTypeModal();
};

const getConvivienteTypeName = (type) => {
    const names = {
        conyuge: "Cónyuge o pareja de hecho registrada",
        hijo: "Hijo/a",
        padre: "Padre/Madre",
        otro: "Otro familiar",
        no_familiar: "No familiar",
    };
    return names[type] || type;
};

const getConvivienteTypeIcon = (type) => {
    const icons = {
        conyuge: "fas fa-heart",
        hijo: "fas fa-child",
        padre: "fas fa-male",
        madre: "fas fa-female",
        abuelo: "fas fa-user-tie",
        hermano: "fas fa-users",
        otro: "fas fa-user-friends",
        no_familiar: "fas fa-user",
    };
    return icons[type] || "fas fa-user";
};

const onConvivienteSectionSelect = (selectedOption) => {
    if (selectedOption.isNew) {
        convivienteSectionForm.value.name = selectedOption.name;
    } else {
        convivienteSectionForm.value.name = selectedOption.name;
    }
};

const onConvivienteSectionSearchChange = (searchTerm) => {
    if (
        searchTerm &&
        !availableSectionsForConviviente.value.some((s) =>
            s.name.toLowerCase().includes(searchTerm.toLowerCase()),
        )
    ) {
        convivienteSectionForm.value.name = searchTerm;
    }
};

const onConvivienteSectionTag = (tag) => {
    const newSection = {
        id: "new",
        name: tag,
        isNew: true,
    };
    convivienteSectionForm.value.selectedSection = newSection;
    convivienteSectionForm.value.name = tag;

    setTimeout(() => {
        convivienteSectionForm.value.name = tag;
    }, 0);
};

const isQuestionInConvivienteSectionForm = (questionId) => {
    return (
        convivienteSectionForm.value.questions.some(
            (q) => q.id === questionId,
        ) ||
        editConvivienteSectionForm.value.questions.some(
            (q) => q.id === questionId,
        )
    );
};

const addQuestionToConvivienteSection = (question) => {
    addQuestionToConvivienteSectionDirectly(question);
};

const removeQuestionFromConvivienteSectionForm = (questionId) => {
    const questionIndex = convivienteSectionForm.value.questions.findIndex(
        (q) => q.id === questionId,
    );
    if (questionIndex > -1) {
        convivienteSectionForm.value.questions.splice(questionIndex, 1);
        performConvivienteQuestionSearch(convivienteQuestionSearch.value);
    }
};

const saveConvivienteSection = () => {
    if (!canSaveConvivienteSection.value) {
        return;
    }

    const selectedSection = convivienteSectionForm.value.selectedSection;
    let sectionName = "";
    let sectionQuestions = [...convivienteSectionForm.value.questions];

    if (selectedSection.isNew) {
        sectionName = convivienteSectionForm.value.name;
    } else {
        sectionName = selectedSection.name;
        sectionQuestions = [...convivienteSectionForm.value.questions];
    }

    const newSection = {
        id: Date.now(),
        name: sectionName,
        questions: sectionQuestions,
    };

    convivienteTypes.value[selectedConvivienteTypeIndex.value].sections.push(
        newSection,
    );
    closeCreateConvivienteSectionModal();
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Sección añadida correctamente",
    );
};

const editConvivienteSection = (typeIndex, sectionIndex) => {
    const section = convivienteTypes.value[typeIndex].sections[sectionIndex];
    editConvivienteSectionForm.value = {
        name: section.name,
        questions: [...(section.questions || [])],
    };
    selectedConvivienteTypeIndex.value = typeIndex;
    editingConvivienteSectionIndex.value = sectionIndex;
    showEditConvivienteSectionModal.value = true;

    searchConvivienteQuestions();
};

const closeEditConvivienteSectionModal = () => {
    showEditConvivienteSectionModal.value = false;
    selectedConvivienteTypeIndex.value = null;
    editingConvivienteSectionIndex.value = null;
    editConvivienteSectionForm.value = {
        name: "",
        questions: [],
    };
};

const addQuestionToConvivienteSectionDirectly = (question) => {
    const questionWithDefaults = {
        ...question,
        isRequired: question.isRequired || false,
        requiredCondition: question.requiredCondition || null,
        optionalCondition: question.optionalCondition || null,
        screen: 0,
        selectedOptions: question.selectedOptions || null, // Para opciones específicas
    };
    convivienteSectionForm.value.questions.push(questionWithDefaults);
    performConvivienteQuestionSearch(convivienteQuestionSearch.value);

    // Si es una pregunta de tipo select o multiple con opciones, abrir modal simple
    if ((question.type === 'select' || question.type === 'multiple') && question.options && question.options.length > 0) {
        const questionIndex = convivienteSectionForm.value.questions.length - 1;
        openSimpleOptionsModalForNewQuestion(questionIndex);
    }
};

const addQuestionToEditConvivienteSection = (question) => {
    addQuestionToEditConvivienteSectionDirectly(question);
};

const addQuestionToEditConvivienteSectionDirectly = (question) => {
    const questionWithDefaults = {
        ...question,
        isRequired: question.isRequired || false,
        requiredCondition: question.requiredCondition || null,
        optionalCondition: question.optionalCondition || null,
        screen: 0,
        selectedOptions: question.selectedOptions || null,
    };
    editConvivienteSectionForm.value.questions.push(questionWithDefaults);
    performConvivienteQuestionSearch(convivienteQuestionSearch.value);

    if ((question.type === 'select' || question.type === 'multiple') && question.options && question.options.length > 0) {
        const questionIndex = editConvivienteSectionForm.value.questions.length - 1;
        openSimpleOptionsModalForEditQuestion(questionIndex);
    }
};

const removeQuestionFromEditConvivienteSection = (questionId) => {
    const questionIndex = editConvivienteSectionForm.value.questions.findIndex(
        (q) => q.id === questionId,
    );
    if (questionIndex > -1) {
        editConvivienteSectionForm.value.questions.splice(questionIndex, 1);
        performConvivienteQuestionSearch(convivienteQuestionSearch.value);
    }
};

const saveEditConvivienteSection = () => {
    if (
        !editConvivienteSectionForm.value.name ||
        editConvivienteSectionForm.value.questions.length === 0
    ) {
        return;
    }

    const newConvivienteTypes = [...convivienteTypes.value];
    const section =
        newConvivienteTypes[selectedConvivienteTypeIndex.value].sections[
            editingConvivienteSectionIndex.value
        ];

    section.name = editConvivienteSectionForm.value.name;
    section.questions = [...editConvivienteSectionForm.value.questions];

    convivienteTypes.value = newConvivienteTypes;
    closeEditConvivienteSectionModal();
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Sección actualizada correctamente",
    );
};

const deleteConvivienteSection = (typeIndex, sectionIndex) => {
    if (confirm("¿Estás seguro de que quieres eliminar esta sección?")) {
        convivienteTypes.value[typeIndex].sections.splice(sectionIndex, 1);
        emit(
            "show-notification",
            "success",
            "Éxito",
            "Sección eliminada correctamente",
        );
    }
};

const removeQuestionFromConvivienteSection = (
    typeIndex,
    sectionIndex,
    questionId,
) => {
    const section = convivienteTypes.value[typeIndex].sections[sectionIndex];
    const questionIndex = section.questions.findIndex(
        (q) => q.id === questionId,
    );
    if (questionIndex > -1) {
        section.questions.splice(questionIndex, 1);
        emit(
            "show-notification",
            "success",
            "Éxito",
            "Pregunta removida correctamente",
        );
    }
};

// Drag & Drop methods for conviviente questions
const handleConvivienteDragStart = (typeIndex, sectionIndex, questionIndex) => {
    draggedConvivienteQuestion.value =
        convivienteTypes.value[typeIndex].sections[sectionIndex].questions[
            questionIndex
        ];
    draggedFromConvivienteType.value = typeIndex;
    draggedFromConvivienteSection.value = sectionIndex;
    draggedFromConvivienteIndex.value = questionIndex;
};

const handleConvivienteDragOver = (
    event,
    typeIndex,
    sectionIndex,
    questionIndex,
) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = "move";
    dragOverConvivienteType.value = typeIndex;
    dragOverConvivienteSection.value = sectionIndex;
    dragOverConvivienteIndex.value = questionIndex;
};

const handleConvivienteDrop = (
    targetTypeIndex,
    targetSectionIndex,
    targetQuestionIndex,
) => {
    event.preventDefault();

    if (
        draggedFromConvivienteType.value === targetTypeIndex &&
        draggedFromConvivienteSection.value === targetSectionIndex &&
        draggedFromConvivienteIndex.value === targetQuestionIndex
    ) {
        return;
    }

    const newConvivienteTypes = [...convivienteTypes.value];

    if (draggedFromConvivienteSection.value === targetSectionIndex) {
        const questions =
            newConvivienteTypes[targetTypeIndex].sections[targetSectionIndex]
                .questions;
        const draggedItem = questions.splice(
            draggedFromConvivienteIndex.value,
            1,
        )[0];
        questions.splice(targetQuestionIndex, 0, draggedItem);
    } else {
        const draggedItem = newConvivienteTypes[
            draggedFromConvivienteType.value
        ].sections[draggedFromConvivienteSection.value].questions.splice(
            draggedFromConvivienteIndex.value,
            1,
        )[0];
        newConvivienteTypes[targetTypeIndex].sections[
            targetSectionIndex
        ].questions.splice(targetQuestionIndex, 0, draggedItem);
    }

    convivienteTypes.value = newConvivienteTypes;
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Pregunta reordenada correctamente",
    );
};

const handleConvivienteDragEnd = () => {
    draggedConvivienteQuestion.value = null;
    draggedFromConvivienteType.value = null;
    draggedFromConvivienteSection.value = null;
    draggedFromConvivienteIndex.value = null;
    dragOverConvivienteType.value = null;
    dragOverConvivienteSection.value = null;
    dragOverConvivienteIndex.value = null;
};

const handleConvivienteTypeDragStart = (typeIndex) => {
    draggedConvivienteType.value = convivienteTypes.value[typeIndex];
    draggedFromConvivienteTypeIndex.value = typeIndex;
};

const handleConvivienteTypeDragOver = (event, typeIndex) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = "move";
    dragOverConvivienteTypeIndex.value = typeIndex;
};

const handleConvivienteTypeDrop = (targetTypeIndex) => {
    event.preventDefault();

    if (draggedFromConvivienteTypeIndex.value === targetTypeIndex) {
        return;
    }

    const newConvivienteTypes = [...convivienteTypes.value];
    const draggedItem = newConvivienteTypes.splice(
        draggedFromConvivienteTypeIndex.value,
        1
    )[0];
    newConvivienteTypes.splice(targetTypeIndex, 0, draggedItem);

    convivienteTypes.value = newConvivienteTypes;
    emit("update:convivienteTypes", convivienteTypes.value);
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Tipo de conviviente reordenado correctamente"
    );
};

const handleConvivienteTypeDragEnd = () => {
    draggedConvivienteType.value = null;
    draggedFromConvivienteTypeIndex.value = null;
    dragOverConvivienteTypeIndex.value = null;
};

// Block status methods
const updateConvivienteQuestionBlockStatus = (
    typeIndex,
    sectionIndex,
    questionIndex,
    blockStatus,
) => {
    const newConvivienteTypes = [...convivienteTypes.value];
    newConvivienteTypes[typeIndex].sections[sectionIndex].questions[
        questionIndex
    ].blockIfBankflipFilled = blockStatus;
    convivienteTypes.value = newConvivienteTypes;
};

const openConvivienteConditionModal = (
    typeIndex,
    sectionIndex,
    questionIndex,
) => {
    const question =
        convivienteTypes.value[typeIndex].sections[sectionIndex].questions[
            questionIndex
        ];
    convivienteConditionQuestion.value = question;
    convivienteConditionTypeIndex.value = typeIndex;
    convivienteConditionSectionIndex.value = sectionIndex;
    convivienteConditionQuestionIndex.value = questionIndex;

    if (question.condition) {
        convivienteConditionForm.value = {
            dependsOnQuestionId: question.condition.dependsOnQuestionId || "",
            conditionType: question.condition.conditionType || "",
            expectedValue: question.condition.expectedValue || "",
            expectedValue2: question.condition.expectedValue2 || "",
        };
    } else {
        convivienteConditionForm.value = {
            dependsOnQuestionId: "",
            conditionType: "",
            expectedValue: "",
            expectedValue2: "",
        };
    }

    showConvivienteConditionModal.value = true;
};

const closeConvivienteConditionModal = () => {
    showConvivienteConditionModal.value = false;
    convivienteConditionQuestion.value = null;
    convivienteConditionTypeIndex.value = null;
    convivienteConditionSectionIndex.value = null;
    convivienteConditionQuestionIndex.value = null;
    convivienteConditionForm.value = {
        dependsOnQuestionId: "",
        conditionType: "",
        expectedValue: "",
        expectedValue2: "",
    };
};

const saveConvivienteCondition = (condition) => {
    const newConvivienteTypes = [...convivienteTypes.value];
    const question =
        newConvivienteTypes[convivienteConditionTypeIndex.value].sections[
            convivienteConditionSectionIndex.value
        ].questions[convivienteConditionQuestionIndex.value];

    question.condition = condition;

    convivienteTypes.value = newConvivienteTypes;
    closeConvivienteConditionModal();
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Condición guardada correctamente",
    );
};

const getAvailableQuestionsForCondition = () => {
    const allQuestions = [];

    if (props.sections) {
        props.sections.forEach((section, sectionIndex) => {
            if (section.questions) {
                section.questions.forEach((question, questionIndex) => {
                    allQuestions.push({
                        ...question,
                        sectionIndex,
                        questionIndex,
                        sectionName: section.name,
                        isSolicitante: true,
                    });
                });
            }
        });
    }

    convivienteTypes.value.forEach((type, typeIndex) => {
        type.sections.forEach((section, sectionIndex) => {
            if (section.questions) {
                section.questions.forEach((question, questionIndex) => {
                    if (
                        !(
                            typeIndex === convivienteConditionTypeIndex.value &&
                            sectionIndex ===
                                convivienteConditionSectionIndex.value &&
                            questionIndex ===
                                convivienteConditionQuestionIndex.value
                        )
                    ) {
                        allQuestions.push({
                            ...question,
                            typeIndex,
                            sectionIndex,
                            questionIndex,
                            convivienteName: type.name,
                            sectionName: section.name,
                            convivienteIcon: type.icon,
                            isConviviente: true,
                        });
                    }
                });
            }
        });
    });

    return allQuestions;
};

const getQuestionDisplayLabel = (question) => {
    if (!question) return "";
    
    if (question.isSolicitante) {
        return `${question.text} (Solicitante - ${question.sectionName})`;
    } else if (question.isConviviente) {
        return `${question.text} (${question.convivienteName} - ${question.sectionName})`;
    }
    
    return question.text;
};

const hasConditionalOptions = (question) => {
    if (!question.conditionalOptions) return false;

    const configs = question.conditionalOptions.conditionalConfigs || [];
    return configs.length > 0 && configs.some(config => 
        config.dependsOnQuestionId && 
        config.conditionType && 
        config.expectedValue !== undefined && 
        config.expectedValue !== ""
    );
};

const hasSelectedOptions = (question) => {
    return question.selectedOptions && question.selectedOptions.length > 0;
};

const isOptionSelected = (question, option) => {
    if (!question.selectedOptions || question.selectedOptions.length === 0) {
        return true; // Si no hay opciones específicas configuradas, todas están "seleccionadas" por defecto
    }
    const optionValue = option.value || option;
    return question.selectedOptions.includes(optionValue);
};

const getSelectedOptionsCount = (question) => {
    if (!question.selectedOptions || question.selectedOptions.length === 0) {
        return question.options ? question.options.length : 0;
    }
    return question.selectedOptions.length;
};

const getConditionQuestionType = () => {
    const question = getAvailableQuestionsForCondition().find(
        (q) => q.id === convivienteConditionForm.value.dependsOnQuestionId,
    );
    return question ? question.type : "";
};

const getConditionQuestionText = () => {
    const question = getAvailableQuestionsForCondition().find(
        (q) => q.id === convivienteConditionForm.value.dependsOnQuestionId,
    );
    return question ? question.text : "";
};

const removeConvivienteCondition = (typeIndex, sectionIndex, questionIndex) => {
    if (
        confirm(
            "¿Estás seguro de que quieres eliminar la condición de esta pregunta?",
        )
    ) {
        const newConvivienteTypes = [...convivienteTypes.value];
        delete newConvivienteTypes[typeIndex].sections[sectionIndex].questions[
            questionIndex
        ].condition;
        convivienteTypes.value = newConvivienteTypes;
        emit(
            "show-notification",
            "success",
            "Éxito",
            "Condición eliminada correctamente",
        );
    }
};

const openConvivienteRequiredConditionModal = (
    typeIndex,
    sectionIndex,
    questionIndex,
) => {
    const question =
        convivienteTypes.value[typeIndex].sections[sectionIndex].questions[
            questionIndex
        ];
    convivienteRequiredConditionQuestion.value = question;
    convivienteRequiredConditionTypeIndex.value = typeIndex;
    convivienteRequiredConditionSectionIndex.value = sectionIndex;
    convivienteRequiredConditionQuestionIndex.value = questionIndex;

    convivienteRequiredConditionForm.value = {
        isRequired: question.isRequired || false,
        requiredDependsOnQuestionId:
            question.requiredCondition?.dependsOnQuestionId || "",
        requiredConditionType: question.requiredCondition?.conditionType || "",
        requiredExpectedValue: question.requiredCondition?.expectedValue || "",
        requiredExpectedValue2:
            question.requiredCondition?.expectedValue2 || "",
        optionalDependsOnQuestionId:
            question.optionalCondition?.dependsOnQuestionId || "",
        optionalConditionType: question.optionalCondition?.conditionType || "",
        optionalExpectedValue: question.optionalCondition?.expectedValue || "",
        optionalExpectedValue2:
            question.optionalCondition?.expectedValue2 || "",
    };

    showConvivienteRequiredConditionModal.value = true;
};

const closeConvivienteRequiredConditionModal = () => {
    showConvivienteRequiredConditionModal.value = false;
    convivienteRequiredConditionQuestion.value = null;
    convivienteRequiredConditionTypeIndex.value = null;
    convivienteRequiredConditionSectionIndex.value = null;
    convivienteRequiredConditionQuestionIndex.value = null;
    convivienteRequiredConditionForm.value = {
        isRequired: false,
        requiredDependsOnQuestionId: "",
        requiredConditionType: "",
        requiredExpectedValue: "",
        requiredExpectedValue2: "",
        optionalDependsOnQuestionId: "",
        optionalConditionType: "",
        optionalExpectedValue: "",
        optionalExpectedValue2: "",
    };
};

const canSaveConvivienteRequiredCondition = computed(() => {
    return (
        convivienteRequiredConditionForm.value.isRequired ||
        convivienteRequiredConditionForm.value.requiredDependsOnQuestionId ||
        convivienteRequiredConditionForm.value.optionalDependsOnQuestionId
    );
});

const saveConvivienteRequiredCondition = () => {
    if (!canSaveConvivienteRequiredCondition.value) {
        return;
    }

    const newConvivienteTypes = [...convivienteTypes.value];
    const question =
        newConvivienteTypes[convivienteRequiredConditionTypeIndex.value]
            .sections[convivienteRequiredConditionSectionIndex.value].questions[
            convivienteRequiredConditionQuestionIndex.value
        ];

    question.isRequired = convivienteRequiredConditionForm.value.isRequired;

    if (
        convivienteRequiredConditionForm.value.requiredDependsOnQuestionId &&
        convivienteRequiredConditionForm.value.requiredConditionType
    ) {
        question.requiredCondition = {
            dependsOnQuestionId:
                convivienteRequiredConditionForm.value
                    .requiredDependsOnQuestionId,
            conditionType:
                convivienteRequiredConditionForm.value.requiredConditionType,
            expectedValue:
                convivienteRequiredConditionForm.value.requiredExpectedValue,
            expectedValue2:
                convivienteRequiredConditionForm.value.requiredExpectedValue2,
        };
    } else {
        delete question.requiredCondition;
    }

    if (
        convivienteRequiredConditionForm.value.optionalDependsOnQuestionId &&
        convivienteRequiredConditionForm.value.optionalConditionType
    ) {
        question.optionalCondition = {
            dependsOnQuestionId:
                convivienteRequiredConditionForm.value
                    .optionalDependsOnQuestionId,
            conditionType:
                convivienteRequiredConditionForm.value.optionalConditionType,
            expectedValue:
                convivienteRequiredConditionForm.value.optionalExpectedValue,
            expectedValue2:
                convivienteRequiredConditionForm.value.optionalExpectedValue2,
        };
    } else {
        delete question.optionalCondition;
    }

    convivienteTypes.value = newConvivienteTypes;
    closeConvivienteRequiredConditionModal();
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Condiciones de obligatoriedad guardadas correctamente",
    );
};

const getAvailableQuestionsForRequiredCondition = () => {
    const allQuestions = [];

    if (props.sections) {
        props.sections.forEach((section, sectionIndex) => {
            if (section.questions) {
                section.questions.forEach((question, questionIndex) => {
                    allQuestions.push({
                        ...question,
                        sectionIndex,
                        questionIndex,
                        sectionName: section.name,
                        isSolicitante: true,
                    });
                });
            }
        });
    }

    convivienteTypes.value.forEach((type, typeIndex) => {
        type.sections.forEach((section, sectionIndex) => {
            if (section.questions) {
                section.questions.forEach((question, questionIndex) => {
                    if (
                        !(
                            typeIndex ===
                                convivienteRequiredConditionTypeIndex.value &&
                            sectionIndex ===
                                convivienteRequiredConditionSectionIndex.value &&
                            questionIndex ===
                                convivienteRequiredConditionQuestionIndex.value
                        )
                    ) {
                        allQuestions.push({
                            ...question,
                            typeIndex,
                            sectionIndex,
                            questionIndex,
                            convivienteName: type.name,
                            sectionName: section.name,
                            convivienteIcon: type.icon,
                            isConviviente: true,
                        });
                    }
                });
            }
        });
    });

    return allQuestions;
};

const getRequiredConditionQuestionType = () => {
    const selectedItem = convivienteRequiredConditionForm.value.requiredDependsOnQuestionId;
    const selectedId = selectedItem && typeof selectedItem === 'object' ? selectedItem.id : selectedItem;
    const allQuestions = getAvailableQuestionsForRequiredCondition();
    const question = allQuestions.find((q) => q.id === selectedId);
    return question ? question.type : "";
};

const getRequiredConditionQuestion = () => {
    const selectedItem = convivienteRequiredConditionForm.value.requiredDependsOnQuestionId;
    const selectedId = selectedItem && typeof selectedItem === 'object' ? selectedItem.id : selectedItem;
    const allQuestions = getAvailableQuestionsForRequiredCondition();
    const question = allQuestions.find((q) => q.id === selectedId);
    
    return question;
};

const getRequiredConditionQuestionOptions = () => {
    const question = getRequiredConditionQuestion();
    if (!question) {
        return [];
    }
    
    if (question.type === 'boolean' || question.type === 'checkbox') {
        return [
            { value: question.type === 'boolean' ? 'Sí' : 'true', text: 'Sí' },
            { value: question.type === 'boolean' ? 'No' : 'false', text: 'No' }
        ];
    }
    
    if ((question.type === 'select' || question.type === 'multiple') && question.options) {
        const options = question.options.map(option => ({
            value: option.value || option,
            text: option.text || option
        }));
        return options;
    }

    return [];
};

const getRequiredConditionQuestionText = () => {
    const selectedItem = convivienteRequiredConditionForm.value.requiredDependsOnQuestionId;
    const selectedId = selectedItem && typeof selectedItem === 'object' ? selectedItem.id : selectedItem;
    const question = getAvailableQuestionsForRequiredCondition().find((q) => q.id === selectedId);
    return question ? question.text : "";
};

const getOptionalConditionQuestionType = () => {
    const selectedItem = convivienteOptionalConditionForm.value.optionalDependsOnQuestionId;
    const selectedId = selectedItem && typeof selectedItem === 'object' ? selectedItem.id : selectedItem;
    const question = getAvailableQuestionsForOptionalCondition().find((q) => q.id === selectedId);
    return question ? question.type : "";
};

const getOptionalConditionQuestion = () => {
    const selectedItem = convivienteOptionalConditionForm.value.optionalDependsOnQuestionId;
    const selectedId = selectedItem && typeof selectedItem === 'object' ? selectedItem.id : selectedItem;
    return getAvailableQuestionsForOptionalCondition().find((q) => q.id === selectedId);
};

const getOptionalConditionQuestionOptions = () => {
    const question = getOptionalConditionQuestion();
    if (!question) return [];
    
    if (question.type === 'boolean' || question.type === 'checkbox') {
        return [
            { value: question.type === 'boolean' ? 'Sí' : 'true', text: 'Sí' },
            { value: question.type === 'boolean' ? 'No' : 'false', text: 'No' }
        ];
    }
    
    if ((question.type === 'select' || question.type === 'multiple') && question.options) {
        return question.options.map(option => ({
            value: option.value || option,
            text: option.text || option
        }));
    }
    
    return [];
};

const getOptionalConditionQuestionText = () => {
    const selectedItem = convivienteOptionalConditionForm.value.optionalDependsOnQuestionId;
    const selectedId = selectedItem && typeof selectedItem === 'object' ? selectedItem.id : selectedItem;
    const question = getAvailableQuestionsForOptionalCondition().find((q) => q.id === selectedId);
    return question ? question.text : "";
};

const removeConvivienteRequiredCondition = (
    typeIndex,
    sectionIndex,
    questionIndex,
) => {
    if (
        confirm(
            "¿Estás seguro de que quieres eliminar las condiciones de obligatoriedad de esta pregunta?",
        )
    ) {
        const newConvivienteTypes = [...convivienteTypes.value];
        const question =
            newConvivienteTypes[typeIndex].sections[sectionIndex].questions[
                questionIndex
            ];
        delete question.isRequired;
        delete question.requiredCondition;
        delete question.optionalCondition;
        convivienteTypes.value = newConvivienteTypes;
        emit(
            "show-notification",
            "success",
            "Éxito",
            "Condiciones de obligatoriedad eliminadas correctamente",
        );
    }
};

// Section skip functions
const configureConvivienteSectionSkip = (typeIndex, sectionIndex) => {
    const section = convivienteTypes.value[typeIndex].sections[sectionIndex];
    convivienteSectionSkipTypeIndex.value = typeIndex;
    convivienteSectionSkipSectionIndex.value = sectionIndex;

    convivienteSectionSkipForm.value = {
        sectionName: section.name,
        dependsOnQuestionId: section.skipCondition?.dependsOnQuestionId?.id || section.skipCondition?.dependsOnQuestionId || "",
        conditionType: section.skipCondition?.conditionType || "",
        expectedValue: section.skipCondition?.expectedValue || "",
        expectedValue2: section.skipCondition?.expectedValue2 || "",
    };

    skipPrefillLock.value = true;
    
    try {
        if (section.skipCondition?.dependsOnQuestionId) {
            const question = section.skipCondition.dependsOnQuestionId;
            
            if (question.isSolicitante) {
                selectedPersonForSkip.value = {
                    id: 'solicitante',
                    name: 'Solicitante',
                    icon: 'fas fa-user',
                    type: 'solicitante',
                    typeIndex: -1,
                };
            } else if (typeof question.typeIndex === 'number') {
                const convivienteType = convivienteTypes.value[question.typeIndex];
                selectedPersonForSkip.value = {
                    id: `conviviente_${question.typeIndex}`,
                    name: convivienteType?.name || 'Conviviente',
                    icon: convivienteType?.icon || 'fas fa-user-friends',
                    type: 'conviviente',
                    typeIndex: question.typeIndex,
                };
            } else {
                selectedPersonForSkip.value = null;
            }

            if (selectedPersonForSkip.value) {
                const questions = getQuestionsForSelectedPerson();
                
                const matched = questions.find((q) => q.id === question.id);
                
                selectedQuestionForSkip.value = matched || null;
                
                if (matched) {
                    convivienteSectionSkipForm.value.dependsOnQuestionId = matched;
                }
            } else {
                selectedQuestionForSkip.value = null;
            }
        } else {
            selectedPersonForSkip.value = null;
            selectedQuestionForSkip.value = null;
        }
    } finally {
        setTimeout(() => {
            skipPrefillLock.value = false;
        }, 100);
    }

    showConvivienteSectionSkipModal.value = true;
};

const closeConvivienteSectionSkipModal = () => {
    showConvivienteSectionSkipModal.value = false;
    convivienteSectionSkipTypeIndex.value = null;
    convivienteSectionSkipSectionIndex.value = null;
    convivienteSectionSkipForm.value = {
        sectionName: "",
        dependsOnQuestionId: "",
        conditionType: "",
        expectedValue: "",
        expectedValue2: "",
    };
    
    // Limpiar los selectores separados
    selectedPersonForSkip.value = null;
    selectedQuestionForSkip.value = null;
};

const getAvailableQuestionsForSectionSkip = () => {
    const allQuestions = [];

    props.sections.forEach((section, sectionIndex) => {
        if (section.questions) {
            section.questions.forEach((question, questionIndex) => {
                allQuestions.push({
                    ...question,
                    convivienteName: "Solicitante",
                    sectionName: section.name,
                    convivienteIcon: "fas fa-user",
                    typeIndex: -1,
                    sectionIndex,
                    questionIndex,
                    isSolicitante: true,
                });
            });
        }
    });

    convivienteTypes.value.forEach((type, typeIndex) => {
        type.sections.forEach((section, sectionIndex) => {
            if (section.questions) {
                section.questions.forEach((question, questionIndex) => {
                    // Solo incluir preguntas que estén en secciones anteriores
                    // o en la misma sección pero con índice menor
                    if (
                        typeIndex < convivienteSectionSkipTypeIndex.value ||
                        (typeIndex === convivienteSectionSkipTypeIndex.value &&
                            sectionIndex <
                                convivienteSectionSkipSectionIndex.value)
                    ) {
                        allQuestions.push({
                            ...question,
                            convivienteName: type.name,
                            sectionName: section.name,
                            convivienteIcon: type.icon,
                            typeIndex,
                            sectionIndex,
                            questionIndex,
                            isSolicitante: false,
                        });
                    }
                });
            }
        });
    });

    return allQuestions;
};

const getAvailablePersonsForSkip = () => {
    const persons = [];

    persons.push({
        id: 'solicitante',
        name: 'Solicitante',
        icon: 'fas fa-user',
        type: 'solicitante',
        typeIndex: -1
    });

    convivienteTypes.value.forEach((type, typeIndex) => {
        persons.push({
            id: `conviviente_${typeIndex}`,
            name: type.name,
            icon: type.icon,
            type: 'conviviente',
            typeIndex: typeIndex
        });
    });

    return persons;
};

const getQuestionsForSelectedPerson = () => {
    if (!selectedPersonForSkip.value) return [];

    const questions = [];

    if (selectedPersonForSkip.value.type === 'solicitante') {
        props.sections.forEach((section, sectionIndex) => {
            if (section.questions) {
                section.questions.forEach((question, questionIndex) => {
                    questions.push({
                        ...question,
                        sectionName: section.name,
                        sectionIndex,
                        questionIndex,
                        isSolicitante: true,
                    });
                });
            }
        });
    } else {
        const typeIndex = selectedPersonForSkip.value.typeIndex;
        const type = convivienteTypes.value[typeIndex];
        
        if (type && type.sections) {
            type.sections.forEach((section, sectionIndex) => {
                if (section.questions) {
                    section.questions.forEach((question, questionIndex) => {
                        questions.push({
                            ...question,
                            sectionName: section.name,
                            sectionIndex,
                            questionIndex,
                            isSolicitante: false,
                            typeIndex,
                        });
                    });
                }
            });
        }
    }

    return questions;
};

const getConvivienteSectionSkipQuestionText = () => {
    const dependsOnQuestionId =
        convivienteSectionSkipForm.value.dependsOnQuestionId;

    const questionId =
        typeof dependsOnQuestionId === "object" && dependsOnQuestionId !== null
            ? dependsOnQuestionId.id
            : dependsOnQuestionId;

    const question = getAvailableQuestionsForSectionSkip().find(
        (q) => q.id === questionId,
    );
    return question ? question.text : "";
};

const getConvivienteSectionSkipQuestionType = () => {
    const dependsOnQuestionId =
        convivienteSectionSkipForm.value.dependsOnQuestionId;

    const questionId =
        typeof dependsOnQuestionId === "object" && dependsOnQuestionId !== null
            ? dependsOnQuestionId.id
            : dependsOnQuestionId;

    const question = getAvailableQuestionsForSectionSkip().find(
        (q) => q.id === questionId,
    );

    return question ? question.type : null;
};

const getConvivienteSectionSkipQuestionOptions = () => {
    const dependsOnQuestionId =
        convivienteSectionSkipForm.value.dependsOnQuestionId;

    const questionId =
        typeof dependsOnQuestionId === "object" && dependsOnQuestionId !== null
            ? dependsOnQuestionId.id
            : dependsOnQuestionId;

    const question = getAvailableQuestionsForSectionSkip().find(
        (q) => q.id === questionId,
    );

    if (!question) return [];

    if (question.type === "boolean") {
        return ["Sí", "No"];
    }

    if (question.options && question.options.length > 0) {
        return question.options;
    }

    return [];
};

watch(selectedPersonForSkip, () => {
    if (skipPrefillLock.value) {
        return;
    }
    selectedQuestionForSkip.value = null;
    convivienteSectionSkipForm.value.dependsOnQuestionId = "";
    convivienteSectionSkipForm.value.conditionType = "";
    convivienteSectionSkipForm.value.expectedValue = "";
    convivienteSectionSkipForm.value.expectedValue2 = "";
});

watch(selectedQuestionForSkip, (newQuestion) => {
    if (skipPrefillLock.value) {
        return;
    }
    if (newQuestion) {
        convivienteSectionSkipForm.value.dependsOnQuestionId = newQuestion;
    } else {
        convivienteSectionSkipForm.value.dependsOnQuestionId = "";
    }
    convivienteSectionSkipForm.value.conditionType = "";
    convivienteSectionSkipForm.value.expectedValue = "";
    convivienteSectionSkipForm.value.expectedValue2 = "";
});

watch(
    () => convivienteSectionSkipForm.value.dependsOnQuestionId,
    () => {
        if (skipPrefillLock.value) {
            return;
        }
        convivienteSectionSkipForm.value.conditionType = "";
        convivienteSectionSkipForm.value.expectedValue = "";
        convivienteSectionSkipForm.value.expectedValue2 = "";
    },
);

const canSaveConvivienteSectionSkip = computed(() => {
    if (!convivienteSectionSkipForm.value.dependsOnQuestionId) {
        return false;
    }

    if (!convivienteSectionSkipForm.value.conditionType) {
        return false;
    }

    // Para algunos tipos de condición, el valor esperado es opcional
    const optionalValueTypes = [
        "is_empty",
        "is_not_empty",
        "is_today",
        "is_this_year",
        "is_this_month",
        "is_true",
        "is_false",
        "is_checked",
        "is_not_checked",
    ];
    if (
        optionalValueTypes.includes(
            convivienteSectionSkipForm.value.conditionType,
        )
    ) {
        return true;
    }

    return convivienteSectionSkipForm.value.expectedValue !== "";
});

const saveConvivienteSectionSkip = () => {
    if (!canSaveConvivienteSectionSkip.value) {
        return;
    }

    const newConvivienteTypes = [...convivienteTypes.value];
    const section =
        newConvivienteTypes[convivienteSectionSkipTypeIndex.value].sections[
            convivienteSectionSkipSectionIndex.value
        ];

    let expectedValue = convivienteSectionSkipForm.value.expectedValue;
    if (
        convivienteSectionSkipForm.value.conditionType === "age_between" ||
        convivienteSectionSkipForm.value.conditionType === "date_between"
    ) {
        expectedValue = `${convivienteSectionSkipForm.value.expectedValue} - ${convivienteSectionSkipForm.value.expectedValue2}`;
    }

    section.skipCondition = {
        dependsOnQuestionId:
            convivienteSectionSkipForm.value.dependsOnQuestionId,
        conditionType: convivienteSectionSkipForm.value.conditionType,
        expectedValue: expectedValue,
        expectedValue2: convivienteSectionSkipForm.value.expectedValue2,
    };

    convivienteTypes.value = newConvivienteTypes;
    closeConvivienteSectionSkipModal();
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Configuración de salto guardada correctamente",
    );
};

const removeConvivienteSectionSkip = () => {
    const newConvivienteTypes = [...convivienteTypes.value];
    const section =
        newConvivienteTypes[convivienteSectionSkipTypeIndex.value].sections[
            convivienteSectionSkipSectionIndex.value
        ];

    delete section.skipCondition;

    convivienteTypes.value = newConvivienteTypes;
    closeConvivienteSectionSkipModal();
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Configuración de salto eliminada correctamente",
    );
};


const openConvivienteConditionalOptionsModal = (typeIndex, sectionIndex, questionIndex) => {
    const question = convivienteTypes.value[typeIndex].sections[sectionIndex].questions[questionIndex];
    convivienteConditionalOptionsQuestion.value = question;
    convivienteConditionalOptionsAvailableOptions.value = question.options || [];
    if (question.conditionalOptions) {
        convivienteConditionalOptionsQuestion.value = {
            ...question,
            conditionalOptions: question.conditionalOptions
        };
    }
    
    const allQuestions = [];
    props.sections.forEach((section, sIndex) => {
        if (section.questions) {
            section.questions.forEach((q, qIndex) => {
                allQuestions.push({
                    ...q,
                    sectionIndex: sIndex,
                    questionIndex: qIndex,
                    sectionName: section.name,
                });
            });
        }
    });
    convivienteTypes.value.forEach((type, tIndex) => {
        if (type.sections) {
            type.sections.forEach((section, sIndex) => {
                if (section.questions) {
                    section.questions.forEach((q, qIndex) => {
                        if (tIndex < typeIndex || 
                            (tIndex === typeIndex && sIndex < sectionIndex) ||
                            (tIndex === typeIndex && sIndex === sectionIndex && qIndex < questionIndex)) {
                            allQuestions.push({
                                ...q,
                                sectionIndex: sIndex,
                                questionIndex: qIndex,
                                sectionName: section.name,
                                typeIndex: tIndex,
                            });
                        }
                    });
                }
            });
        }
    });
    
    convivienteConditionalOptionsAvailableQuestions.value = allQuestions;
    convivientePendingConditionalOptionsData.value = {
        typeIndex,
        sectionIndex,
        questionIndex,
        question
    };
    showConvivienteConditionalOptionsModal.value = true;
};

const closeConvivienteConditionalOptionsModal = () => {
    showConvivienteConditionalOptionsModal.value = false;
    convivienteConditionalOptionsQuestion.value = null;
    convivienteConditionalOptionsAvailableOptions.value = [];
    convivienteConditionalOptionsAvailableQuestions.value = [];
    convivientePendingConditionalOptionsData.value = null;
};

const openSimpleOptionsModalForNewQuestion = (questionIndex) => {
    const question = convivienteSectionForm.value.questions[questionIndex];
    simpleOptionsQuestion.value = question;
    simpleOptionsAvailableOptions.value = question.options || [];
    simpleOptionsSelectedOptions.value = question.selectedOptions || question.options?.map(opt => opt.value || opt) || [];
    
    simpleOptionsPendingData.value = {
        typeIndex: -1,
        sectionIndex: -1,
        questionIndex: questionIndex,
        question
    };
    showSimpleOptionsModal.value = true;
};

const openSimpleOptionsModalForEditQuestion = (questionIndex) => {
    const question = editConvivienteSectionForm.value.questions[questionIndex];
    simpleOptionsQuestion.value = question;
    simpleOptionsAvailableOptions.value = question.options || [];
    simpleOptionsSelectedOptions.value = question.selectedOptions || question.options?.map(opt => opt.value || opt) || [];
    
    simpleOptionsPendingData.value = {
        typeIndex: -2,
        sectionIndex: -1,
        questionIndex: questionIndex,
        question
    };
    showSimpleOptionsModal.value = true;
};

const closeSimpleOptionsModal = () => {
    showSimpleOptionsModal.value = false;
    simpleOptionsQuestion.value = null;
    simpleOptionsAvailableOptions.value = [];
    simpleOptionsSelectedOptions.value = [];
    simpleOptionsPendingData.value = null;
};

const saveSimpleOptions = (selectedOptions) => {
    if (!simpleOptionsPendingData.value) return;

    const { typeIndex, questionIndex } = simpleOptionsPendingData.value;
    
    if (typeIndex === -1) {
        const question = convivienteSectionForm.value.questions[questionIndex];
        question.selectedOptions = selectedOptions;
        emit("show-notification", "success", "Éxito", "Opciones configuradas correctamente");
    } else if (typeIndex === -2) {
        const question = editConvivienteSectionForm.value.questions[questionIndex];
        question.selectedOptions = selectedOptions;
        emit("show-notification", "success", "Éxito", "Opciones configuradas correctamente");
    } else {
        const newConvivienteTypes = [...convivienteTypes.value];
        const question = newConvivienteTypes[typeIndex].sections[questionIndex.sectionIndex].questions[questionIndex.questionIndex];
        question.selectedOptions = selectedOptions;
        emit("update:convivienteTypes", newConvivienteTypes);
        emit("show-notification", "success", "Éxito", "Opciones configuradas correctamente");
    }
    
    closeSimpleOptionsModal();
};

const openSimpleOptionsModalForExistingQuestion = (typeIndex, sectionIndex, questionIndex) => {
    const question = convivienteTypes.value[typeIndex].sections[sectionIndex].questions[questionIndex];
    simpleOptionsQuestion.value = question;
    simpleOptionsAvailableOptions.value = question.options || [];
    simpleOptionsSelectedOptions.value = question.selectedOptions || question.options?.map(opt => opt.value || opt) || [];
    
    simpleOptionsPendingData.value = {
        typeIndex: typeIndex,
        sectionIndex: sectionIndex,
        questionIndex: { sectionIndex, questionIndex },
        question
    };
    showSimpleOptionsModal.value = true;
};

const getConvivienteQuestionKey = (question) => {
    return `conviviente-question-${question.id}`;
};

const isConvivienteOptionsExpanded = (question) => {
    return expandedConvivienteOptions.value.has(getConvivienteQuestionKey(question));
};

const toggleConvivienteOptionsExpansion = (question) => {
    const key = getConvivienteQuestionKey(question);
    if (expandedConvivienteOptions.value.has(key)) {
        expandedConvivienteOptions.value.delete(key);
    } else {
        expandedConvivienteOptions.value.add(key);
    }
};

const getConvivienteVisibleOptions = (question) => {
    if (!question.options || question.options.length === 0) return [];
    
    const sortedOptions = [...question.options].sort((a, b) => {
        const aSelected = isOptionSelected(question, a);
        const bSelected = isOptionSelected(question, b);
        
        if (aSelected && !bSelected) return -1;
        if (!aSelected && bSelected) return 1;
        return 0;
    });
    
    if (isConvivienteOptionsExpanded(question)) {
        return sortedOptions;
    }
    
    return sortedOptions.slice(0, 3);
};


const saveConvivienteConditionalOptions = (configuration) => {
    if (!convivientePendingConditionalOptionsData.value) return;

    const { typeIndex, sectionIndex, questionIndex } = convivientePendingConditionalOptionsData.value;
    const newConvivienteTypes = [...convivienteTypes.value];
    const question = newConvivienteTypes[typeIndex].sections[sectionIndex].questions[questionIndex];

    if (configuration === null) {
        delete question.conditionalOptions;
        emit("update:convivienteTypes", newConvivienteTypes);
        emit("show-notification", "success", "Éxito", "Configuración de opciones condicionadas eliminada");
    } else {
        question.conditionalOptions = configuration;
        emit("update:convivienteTypes", newConvivienteTypes);
        emit("show-notification", "success", "Éxito", "Configuración de opciones condicionadas guardada");
    }

    closeConvivienteConditionalOptionsModal();
};

const toggleConditionDropdown = (typeIndex, sectionIndex, questionIndex) => {
    const dropdownId = `${typeIndex}-${sectionIndex}-${questionIndex}`;
    if (activeConditionDropdown.value === dropdownId) {
        closeConditionDropdown();
    } else {
        activeConditionDropdown.value = dropdownId;
    }
};

const closeConditionDropdown = () => {
    activeConditionDropdown.value = null;
};

// Watchers
watch(
    convivienteTypes,
    (newTypes) => {
        emit("update:convivienteTypes", newTypes);
    },
    { deep: true },
);

// Lifecycle
onMounted(() => {
    loadQuestionTypes();
    searchConvivienteQuestions();
    convivienteTypes.value = Array.isArray(props.convivienteTypes)
        ? props.convivienteTypes.slice()
        : [];

    convivienteTypes.value.forEach((_, typeIndex) => {
        allConvivienteSectionsCollapsed.value[typeIndex] = true;
        updateAllConvivienteSectionsCollapsedState(typeIndex);
    });

    document.addEventListener("click", (event) => {
        if (
            conditionDropdown.value &&
            !conditionDropdown.value.contains(event.target)
        ) {
            closeConditionDropdown();
        }
    });
});

const getConvScreenDisplay = (q) => {
    const raw = q?.screen;
    const n = Number(raw);
    if (!Number.isFinite(n) || n < 0) return 1;
    return n + 1;
};

const setConvScreenDisplay = (q, screenNum, typeIndex = null, sectionIndex = null, questionIndex = null) => {
    const beforeDisplay = getConvScreenDisplay(q);
    const clamped = Math.max(1, Math.min(9999, Number(screenNum) || 1));
    const newZeroBased = clamped - 1;
    q.screen = newZeroBased;

    try {
        if (typeIndex !== null && sectionIndex !== null && questionIndex !== null) {
            const type = convivienteTypes.value?.[typeIndex];
            const section = type?.sections?.[sectionIndex];
            if (section && Array.isArray(section.questions)) {
                for (let i = 0; i < questionIndex; i++) {
                    const prev = section.questions[i];
                    if (!prev) continue;
                    const prevDisp = getConvScreenDisplay(prev);
                    if (prevDisp > clamped) prev.screen = clamped - 1;
                }
                for (let j = questionIndex + 1; j < section.questions.length; j++) {
                    const nxt = section.questions[j];
                    if (!nxt) continue;
                    const nxtDisp = getConvScreenDisplay(nxt);
                    if (nxtDisp < clamped) nxt.screen = newZeroBased;
                }
            }
        }
    } catch (e) {}

    try {
        const newTypes = convivienteTypes.value.map((t, tIdx) => {
            if (tIdx !== typeIndex || !t?.sections) return t;
            const clonedSections = t.sections.map((s, sIdx) => {
                if (sIdx !== sectionIndex || !s?.questions) return s;
                return { ...s, questions: s.questions.map(qx => ({ ...qx })) };
            });
            return { ...t, sections: clonedSections };
        });
        convivienteTypes.value = newTypes;
    } catch (e2) {}
};
</script>
