<template>
    <div class="space-y-6">
        <div class="bg-gray-50 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-medium text-gray-800">
                    Secciones del onboarder
                </h4>
                <button
                    @click="openCreateSectionModal"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                >
                    <i class="fas fa-plus mr-2"></i>Crear nueva sección
                </button>
            </div>

            <div
                v-if="sections.length === 0"
                class="text-center py-8 text-gray-500"
            >
                <i class="fas fa-folder-open text-4xl mb-2"></i>
                <p>No hay secciones creadas</p>
                <p class="text-sm">
                    Crea una sección para comenzar a añadir preguntas
                </p>
            </div>

            <div
                v-else
                class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                :style="`grid-template-columns: repeat(${sectionsGridLayout.columns}, 1fr);`"
            >
                <div
                    v-for="(section, sectionIndex) in sections"
                    :key="sectionIndex"
                    :draggable="true"
                    @dragstart="handleSectionDragStart(sectionIndex)"
                    @dragover="handleSectionDragOver($event, sectionIndex)"
                    @drop="handleSectionDrop(sectionIndex)"
                    @dragend="handleSectionDragEnd"
                    :class="[
                        'bg-white border border-gray-200 rounded-lg p-4 transition-all duration-200',
                        draggedSectionIndex === sectionIndex
                            ? 'opacity-50 transform rotate-2 bg-blue-50 border-blue-300'
                            : dragOverSectionIndex === sectionIndex
                              ? 'bg-blue-100 border-blue-400 shadow-md'
                              : 'hover:shadow-md',
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3 flex-1">
                            <div
                                class="text-gray-400 hover:text-gray-600 cursor-move"
                            >
                                <i class="fas fa-grip-vertical"></i>
                            </div>
                            <div class="flex-1">
                                <h5 class="font-medium text-gray-900">
                                    {{ section.name }}
                                </h5>
                                <p class="text-sm text-gray-500">
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
                        </div>
                        <div class="flex items-center space-x-2">
                            <button
                                @click="editSection(sectionIndex)"
                                class="p-2 text-gray-400 hover:text-blue-600 transition-colors"
                                title="Editar sección"
                            >
                                <i class="fas fa-edit"></i>
                            </button>
                            <button
                                @click="deleteSection(sectionIndex)"
                                class="p-2 text-gray-400 hover:text-red-600 transition-colors"
                                title="Eliminar sección"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="sections.length > 0" class="bg-blue-50 rounded-lg p-6">
            <h5 class="font-medium text-blue-800 mb-4 flex items-center">
                <i class="fas fa-search mr-2"></i>
                Añadir preguntas a las secciones
            </h5>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div class="md:col-span-2">
                    <input
                        v-model="questionSearch"
                        @input="handleSearchInput"
                        type="text"
                        placeholder="Buscar preguntas..."
                        class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
                <div>
                    <div class="relative">
                        <button
                            type="button"
                            @click="toggleCategoryDropdown"
                            class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-left flex justify-between items-center"
                        >
                            <span v-if="categoryFilter.length === 0"
                                >Todas las categorías</span
                            >
                            <span v-else-if="categoryFilter.length === 1">
                                {{
                                    categoryFilter[0] === "no_category"
                                        ? "Sin categoría"
                                        : getCategoryName(categoryFilter[0])
                                }}
                            </span>
                            <span v-else
                                >{{ categoryFilter.length }} categorías</span
                            >
                            <i class="fas fa-chevron-down"></i>
                        </button>

                        <div
                            v-if="showCategoryDropdown"
                            class="absolute z-10 w-full mt-1 bg-white border border-blue-300 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                        >
                            <div class="p-2">
                                <div class="flex gap-2 mb-2">
                                    <button
                                        type="button"
                                        @click="selectAllCategories"
                                        class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                                    >
                                        Seleccionar todo
                                    </button>
                                    <button
                                        type="button"
                                        @click="clearCategories"
                                        class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                                    >
                                        Limpiar
                                    </button>
                                </div>
                                <label
                                    class="flex items-center p-2 hover:bg-blue-50 cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        value="no_category"
                                        v-model="categoryFilter"
                                        @change="handleCategoryChange"
                                        class="mr-2 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span
                                        class="text-sm font-medium text-blue-800"
                                        >Sin categoría</span
                                    >
                                </label>
                                <label
                                    v-for="category in availableCategories"
                                    :key="category.id"
                                    class="flex items-center p-2 hover:bg-blue-50 cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :value="category.id"
                                        v-model="categoryFilter"
                                        @change="handleCategoryChange"
                                        class="mr-2 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="text-sm">{{
                                        category.name
                                    }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <select
                        v-model="selectedSection"
                        class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">Seleccionar sección</option>
                        <option
                            v-for="(section, index) in sections"
                            :key="index"
                            :value="index"
                        >
                            {{ section.name }}
                        </option>
                    </select>
                </div>
            </div>

            <div v-if="searching" class="text-center py-2">
                <div
                    class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 mx-auto"
                ></div>
            </div>

            <div
                v-if="searchResults.length > 0"
                class="bg-white border border-blue-200 rounded-lg max-h-48 overflow-y-auto"
            >
                <div
                    v-for="question in searchResults"
                    :key="question.id"
                    class="p-3 hover:bg-blue-50 border-b border-blue-100 last:border-b-0 transition-colors"
                >
                    <div class="flex items-center justify-between">
                        <div
                            @click="addQuestionToSection(question)"
                            class="flex-1 cursor-pointer"
                        >
                            <p class="font-medium text-gray-800">
                                {{ question.text }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ question.slug }} •
                                {{
                                    questionTypes[question.type] ||
                                    question.type
                                }}
                                <span
                                    v-if="
                                        question.categories &&
                                        question.categories.length > 0
                                    "
                                    class="ml-2"
                                >
                                    <i
                                        class="fas fa-tag mr-1 text-blue-500"
                                    ></i>
                                    {{
                                        question.categories
                                            .map((c) => c.name)
                                            .join(", ")
                                    }}
                                </span>
                                <span v-else class="ml-2">
                                    <i
                                        class="fas fa-tag mr-1 text-gray-400"
                                    ></i>
                                    <span class="text-gray-400"
                                        >Sin categoría</span
                                    >
                                </span>
                            </p>
                        </div>
                        <button
                            @click="addQuestionToSection(question)"
                            class="p-1 text-green-600 hover:text-green-700 transition-colors"
                            title="Añadir a la sección seleccionada"
                        >
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div
                v-else-if="questionSearch && !searching"
                class="text-center py-4 text-gray-500"
            >
                <i class="fas fa-search text-2xl mb-2"></i>
                <p class="text-sm">No se encontraron preguntas</p>
            </div>
        </div>

        <div
            class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-6 mb-6"
        >
            <h5 class="font-medium text-purple-800 mb-4 flex items-center">
                <i class="fas fa-tools mr-2"></i>
                Añadir builders (preguntas vitaminadas)
            </h5>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                    v-for="builder in builders"
                    :key="builder.id"
                    @click="addBuilderToSection(builder)"
                    class="bg-white border border-purple-200 rounded-lg p-4 hover:shadow-lg transition-all duration-200 cursor-pointer group"
                >
                    <div class="flex items-start space-x-3">
                        <div
                            :class="[
                                'flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center',
                                `bg-${builder.color}-100`,
                            ]"
                        >
                            <i
                                :class="[
                                    builder.icon,
                                    `text-${builder.color}-600`,
                                ]"
                            ></i>
                        </div>

                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <h6
                                    class="font-medium text-gray-900 group-hover:text-purple-700"
                                >
                                    {{ builder.text }}
                                </h6>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-purple-500 to-pink-500 text-white"
                                >
                                    <i class="fas fa-tools mr-1"></i>
                                    Builder
                                </span>
                            </div>

                            <p class="text-sm text-gray-600 mb-3">
                                {{ builder.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="sections.length > 0"
            class="bg-white border border-gray-200 rounded-lg p-6"
        >
            <div class="flex items-center justify-between mb-4">
                <h5 class="font-medium text-gray-800">
                    Resumen de preguntas por sección
                </h5>
                <button
                    @click="toggleAllSections"
                    class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-md transition-colors"
                >
                    <i
                        :class="[
                            'fas transition-transform duration-200',
                            allSectionsCollapsed
                                ? 'fa-chevron-down'
                                : 'fa-chevron-up',
                        ]"
                    ></i>
                    <span>{{
                        allSectionsCollapsed
                            ? "Expandir todas"
                            : "Colapsar todas"
                    }}</span>
                </button>
            </div>
            <div class="space-y-4">
                <div
                    v-for="(section, sectionIndex) in sections"
                    :key="sectionIndex"
                    class="border border-gray-200 rounded-lg"
                >
                    <div
                        @click="toggleSectionCollapse(sectionIndex)"
                        class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center space-x-3">
                            <h6 class="font-medium text-gray-900">
                                {{ section.name }}
                            </h6>
                            <div
                                v-if="
                                    getSectionDependencies(section).length > 0
                                "
                                class="text-xs text-gray-500"
                            >
                                <i class="fas fa-link mr-1"></i>
                                {{ getSectionDependencies(section).length }}
                                dependencia(s)
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-500">
                                {{
                                    section.questions
                                        ? section.questions.length
                                        : 0
                                }}
                                pregunta(s)
                            </span>
                            <i
                                :class="[
                                    'fas transition-transform duration-200',
                                    collapsedSections.includes(sectionIndex)
                                        ? 'fa-chevron-down'
                                        : 'fa-chevron-up',
                                ]"
                            ></i>
                        </div>
                    </div>

                    <div
                        v-if="!collapsedSections.includes(sectionIndex)"
                        class="px-4 pb-4"
                    >
                        <div
                            v-if="getSectionDependencies(section).length > 0"
                            class="mb-3 p-2 bg-purple-50 rounded text-xs"
                        >
                            <div class="font-medium text-purple-800 mb-1">
                                Dependencias:
                            </div>
                            <div class="space-y-1">
                                <div
                                    v-for="dep in getSectionDependencies(
                                        section,
                                    )"
                                    :key="dep.questionId"
                                    class="text-purple-700"
                                >
                                    <i class="fas fa-arrow-right mr-1"></i>
                                    {{ dep.questionText }} →
                                    {{ dep.dependsOnText }}
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="
                                section.questions &&
                                section.questions.length > 0
                            "
                            class="space-y-2"
                        >
                            <div
                                v-for="(question, qIndex) in section.questions"
                                :key="`${sectionIndex}-${qIndex}`"
                                :draggable="true"
                                @dragstart="
                                    handleDragStart(sectionIndex, qIndex)
                                "
                                @dragover="
                                    handleDragOver($event, sectionIndex, qIndex)
                                "
                                @drop="handleDrop(sectionIndex, qIndex)"
                                @dragend="handleDragEnd"
                                :class="[
                                    question.type === 'builder'
                                        ? 'bg-gradient-to-r from-green-100 to-green-200'
                                        : '',
                                    'flex items-center justify-between p-3 rounded border transition-all duration-200',
                                    draggedFromSection === sectionIndex &&
                                    draggedFromIndex === qIndex
                                        ? 'opacity-50 transform rotate-2 bg-blue-50 border-blue-300'
                                        : dragOverSection === sectionIndex &&
                                            dragOverIndex === qIndex
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
                                                v-if="
                                                    question.hideIfBankflipFilled
                                                "
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
                                            >
                                                <i class="fas fa-eye-slash mr-1"></i>
                                                Ocultada
                                            </span>
                                            <span
                                                v-if="
                                                    question.conditionalOptions
                                                "
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                            >
                                                <i class="fas fa-filter mr-1"></i>
                                                Opciones condicionadas
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            {{
                                                questionTypes[question.type] ||
                                                question.type
                                            }}
                                            <span v-if="question.type !== 'builder' && question.slug" class="text-gray-400">
                                                • {{ question.slug }}
                                            </span>
                                        </p>
                                        
                                        <div 
                                            v-if="(question.type === 'select' || question.type === 'multiple') && question.options && question.options.length > 0"
                                            class="mt-2 flex flex-wrap gap-1"
                                        >
                                            <span
                                                v-for="(option, optionIndex) in getVisibleOptions(question)"
                                                :key="optionIndex"
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                            >
                                                {{ option.text || option }}
                                            </span>
                                            <button
                                                v-if="question.options.length > 3"
                                                @click="toggleOptionsExpansion(question)"
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors cursor-pointer"
                                            >
                                                {{ isOptionsExpanded(question) ? 'Ver menos' : `+${question.options.length - 3} más` }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center flex-wrap gap-2 mr-2">
                                    <span class="text-[11px] text-gray-500 font-medium">Pantalla</span>
                                    <div class="inline-flex items-center bg-gray-100 rounded-md border border-gray-200 overflow-hidden">
                                        <button
                                            type="button"
                                            @click="setScreenDisplay(question, Math.max(1, getScreenDisplay(question) - 1), sectionIndex, qIndex)"
                                            class="px-2 py-1 text-gray-600 hover:bg-gray-200 text-xs"
                                            title="Anterior"
                                        >
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <div class="px-2 py-1 text-gray-800 text-xs min-w-[2rem] text-center">
                                            {{ getScreenDisplay(question) }}
                                        </div>
                                        <button
                                            type="button"
                                            @click="setScreenDisplay(question, getScreenDisplay(question) + 1, sectionIndex, qIndex)"
                                            class="px-2 py-1 text-gray-600 hover:bg-gray-200 text-xs"
                                            title="Siguiente"
                                        >
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <div v-if="question.type !== 'builder'" class="flex flex-col space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <label class="flex items-center space-x-1 text-xs text-gray-600">
                                            <input
                                                type="checkbox"
                                                v-model="
                                                    question.blockIfBankflipFilled
                                                "
                                                @change="
                                                    updateQuestionBlockStatus(
                                                        sectionIndex,
                                                        qIndex,
                                                        question.blockIfBankflipFilled,
                                                    )
                                                "
                                                class="w-3 h-3 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                                            />
                                            <span class="text-red-600 font-medium">Bloquear</span>
                                            <span class="text-xs text-gray-500 ml-2">si viene relleno de bankflip</span>
                                        </label>
                                        <div class="flex items-center space-x-1 text-xs text-gray-600">
                                            <span class="text-gray-500 font-medium mr-2">Mostrar:</span>
                                            <select
                                                :value="getBankflipDisplayMode(question.showIfBankflipFilled)"
                                                @change="
                                                    updateQuestionBankflipStatus(
                                                        sectionIndex,
                                                        qIndex,
                                                        $event.target.value
                                                    )
                                                "
                                                class="text-xs border border-gray-300 rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500"
                                            >
                                                <option value="normal">Manual</option>
                                                <option value="bankflip">Bankflip</option>
                                                <option value="universal">Todos</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <button
                                        v-if="(question.type === 'select' || question.type === 'multiple') && question.options && question.options.length > 0"
                                        @click="
                                            openConditionalOptionsModal(
                                                sectionIndex,
                                                qIndex,
                                            )
                                        "
                                        class="p-2 text-orange-400 hover:text-orange-600 transition-colors"
                                        title="Configurar opciones condicionadas"
                                    >
                                        <i class="fas fa-filter"></i>
                                    </button>
                                    <button
                                        @click="
                                            openConditionModal(
                                                sectionIndex,
                                                qIndex,
                                            )
                                        "
                                        class="p-2 text-purple-400 hover:text-purple-600 transition-colors"
                                        title="Configurar condiciones"
                                    >
                                        <i class="fas fa-code-branch"></i>
                                    </button>

                                    <div
                                        v-if="question.condition"
                                        class="flex items-center space-x-1"
                                    >
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                            title="Esta pregunta tiene condiciones configuradas"
                                        >
                                            <i class="fas fa-link mr-1"></i>
                                            Condicionada
                                        </span>
                                        <button
                                            @click="
                                                removeCondition(
                                                    sectionIndex,
                                                    qIndex,
                                                )
                                            "
                                            class="p-1 text-purple-400 hover:text-purple-600 transition-colors"
                                            title="Eliminar condición"
                                        >
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <button
                                    @click="
                                        removeQuestionFromSection(
                                            sectionIndex,
                                            qIndex,
                                        )
                                    "
                                    class="p-2 text-red-400 hover:text-red-600 transition-colors ml-2"
                                    title="Eliminar pregunta"
                                >
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-500 italic">
                            No hay preguntas en esta sección
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ConditionModal
            :show="showConditionModal"
            :question="conditionQuestion"
            :available-questions="availableQuestionsToCondition"
            :question-types="questionTypes"
            @close="closeConditionModal"
            @save="saveCondition"
        />

        <ConditionalOptionsModal
            :show="showConditionalOptionsModal"
            :question="conditionalOptionsQuestion"
            :available-options="conditionalOptionsAvailableOptions"
            :available-questions="conditionalOptionsAvailableQuestions"
            @close="closeConditionalOptionsModal"
            @save="saveConditionalOptions"
        />

        <div
            v-if="showCreateSectionModal || showEditSectionModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-900">
                        {{
                            showCreateSectionModal
                                ? "Crear nueva sección"
                                : "Editar sección"
                        }}
                    </h3>
                    <button
                        @click="closeSectionModal"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="saveSection">
                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Nombre de la sección
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="sectionForm.name"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Ej: Información Personal"
                                required
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Descripción (opcional)
                            </label>
                            <textarea
                                v-model="sectionForm.description"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Describe el propósito de esta sección"
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button
                            @click="closeSectionModal"
                            type="button"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="savingSection"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
                        >
                            <i
                                v-if="savingSection"
                                class="fas fa-spinner fa-spin mr-2"
                            ></i>
                            {{
                                savingSection
                                    ? "Guardando..."
                                    : showCreateSectionModal
                                      ? "Crear sección"
                                      : "Actualizar sección"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import ConditionModal from "./ConditionModal.vue";
import ConditionalOptionsModal from "./ConditionalOptionsModal.vue";

const props = defineProps({
    sections: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(["update:sections", "show-notification"]);

const showCreateSectionModal = ref(false);
const showEditSectionModal = ref(false);
const savingSection = ref(false);
const editingSectionIndex = ref(-1);
const sectionForm = ref({
    name: "",
    description: "",
});

const questionSearch = ref("");
const searchResults = ref([]);
const searching = ref(false);
const categoryFilter = ref([]);
const showCategoryDropdown = ref(false);
const selectedSection = ref("");
const availableCategories = ref([]);
const questionTypes = ref({});

const builders = ref([
    {
        id: "education-builder",
        slug: "education-builder",
        text: "Constructor de educación",
        description:
            "El usuario podrá introducir sus estudios cursados y en curso",
        type: "builder",
        icon: "fas fa-graduation-cap",
        color: "purple",
    },
    {
        id: "calculadora",
        slug: "calculadora",
        text: "Calculadora de ingresos",
        description:
            "El usuario podrá introducir el tipo de ingreso percibido, la cantidad, el importe medio, etc. Para devolverle unas deducciones estimadas",
        type: "builder",
        icon: "fas fa-calculator",
        color: "green",
    },
]);
const getScreenDisplay = (q) => {
    const raw = q?.screen;
    const n = Number(raw);
    if (!Number.isFinite(n) || n < 0) return 1;
    return n + 1;
};

const setScreenDisplay = (q, screenNum, sectionIndex = null, questionIndex = null) => {
    const beforeDisplay = getScreenDisplay(q);
    const clamped = Math.max(1, Math.min(9999, Number(screenNum) || 1));
    const newZeroBased = clamped - 1;
    q.screen = newZeroBased;

    try {
        if (sectionIndex !== null && questionIndex !== null) {
            const section = props.sections?.[sectionIndex];
            if (section && Array.isArray(section.questions)) {
                for (let i = 0; i < questionIndex; i++) {
                    const prev = section.questions[i];
                    if (!prev) continue;
                    const prevDisplay = getScreenDisplay(prev);
                    if (prevDisplay > clamped) {
                        prev.screen = clamped - 1;
                    }
                }
                for (let j = questionIndex + 1; j < section.questions.length; j++) {
                    const nxt = section.questions[j];
                    if (!nxt) continue;
                    const nxtDisplay = getScreenDisplay(nxt);
                    if (nxtDisplay < clamped) {
                        nxt.screen = newZeroBased;
                    }
                }
            }
        }
    } catch (e) {
    }

    try {
        const newSections = props.sections.map((s, idx) => {
            if (idx !== sectionIndex || !s?.questions) return s;
            const cloned = { ...s, questions: s.questions.map(qx => ({ ...qx })) };
            return cloned;
        });
        emit("update:sections", newSections);
    } catch (e2) {
        console.log('[Step2] setScreenDisplay emit error', e2);
    }
};

const activeQuestionMenu = ref(null);
const toggleQuestionMenu = (sectionIndex, questionIndex) => {
    if (activeQuestionMenu.value && activeQuestionMenu.value.sectionIndex === sectionIndex && activeQuestionMenu.value.questionIndex === questionIndex) {
        activeQuestionMenu.value = null;
        return;
    }
    activeQuestionMenu.value = { sectionIndex, questionIndex };
};
const closeQuestionMenu = () => { activeQuestionMenu.value = null; };

const draggedQuestion = ref(null);
const draggedFromSection = ref(null);
const draggedFromIndex = ref(null);
const dragOverSection = ref(null);
const dragOverIndex = ref(null);
const draggedSection = ref(null);
const draggedSectionIndex = ref(null);
const dragOverSectionIndex = ref(null);

// Collapsible sections
const collapsedSections = ref([]);
const allSectionsCollapsed = ref(true);

const expandedOptions = ref(new Set());

const showConditionModal = ref(false);
const conditionQuestion = ref(null);
const conditionSectionIndex = ref(null);
const conditionQuestionIndex = ref(null);
const conditionForm = ref({
    dependsOnQuestionId: "",
    conditionType: "",
    expectedValue: "",
});

const showConditionalOptionsModal = ref(false);
const conditionalOptionsQuestion = ref(null);
const conditionalOptionsAvailableOptions = ref([]);
const conditionalOptionsAvailableQuestions = ref([]);
const pendingConditionalOptionsData = ref(null);

const openCreateSectionModal = () => {
    sectionForm.value = { name: "", description: "" };
    showCreateSectionModal.value = true;
};

const editSection = (index) => {
    editingSectionIndex.value = index;
    sectionForm.value = { ...props.sections[index] };
    showEditSectionModal.value = true;
};

const closeSectionModal = () => {
    showCreateSectionModal.value = false;
    showEditSectionModal.value = false;
    editingSectionIndex.value = -1;
    sectionForm.value = { name: "", description: "" };
};

const saveSection = () => {
    if (!sectionForm.value.name.trim()) {
        emit(
            "show-notification",
            "error",
            "Error",
            "El nombre de la sección es obligatorio",
        );
        return;
    }

    savingSection.value = true;

    const newSections = [...props.sections];

    if (showCreateSectionModal.value) {
        newSections.push({
            name: sectionForm.value.name.trim(),
            description: sectionForm.value.description.trim(),
            questions: [],
        });
        emit(
            "show-notification",
            "success",
            "Éxito",
            "Sección creada correctamente",
        );
    } else {
        newSections[editingSectionIndex.value] = {
            ...newSections[editingSectionIndex.value],
            name: sectionForm.value.name.trim(),
            description: sectionForm.value.description.trim(),
        };
        emit(
            "show-notification",
            "success",
            "Éxito",
            "Sección actualizada correctamente",
        );
    }

    emit("update:sections", newSections);
    closeSectionModal();
    savingSection.value = false;
};

const deleteSection = (index) => {
    if (
        confirm(
            "¿Estás seguro de que quieres eliminar esta sección? Se perderán todas las preguntas asociadas.",
        )
    ) {
        const newSections = [...props.sections];
        newSections.splice(index, 1);
        emit("update:sections", newSections);
        emit(
            "show-notification",
            "success",
            "Éxito",
            "Sección eliminada correctamente",
        );
    }
};

const handleSearchInput = () => {
    if (questionSearch.value.trim().length < 2) {
        searchResults.value = [];
        return;
    }

    searching.value = true;
    searchQuestions();
};

const searchQuestions = async () => {
    try {
        const params = new URLSearchParams();
        params.append("search", questionSearch.value);

        if (categoryFilter.value.length > 0) {
            categoryFilter.value.forEach((categoryId) => {
                params.append("categories[]", categoryId);
            });
        }

        params.append("limit", "20");

        const response = await fetch(
            `/admin/wizards/questions/search?${params.toString()}`,
            {
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                    Accept: "application/json",
                },
            },
        );

        if (response.ok) {
            const data = await response.json();
            searchResults.value = data.questions || [];
        }
    } catch (error) {
        console.error("Error buscando preguntas:", error);
        emit(
            "show-notification",
            "error",
            "Error",
            "Error al buscar preguntas",
        );
    } finally {
        searching.value = false;
    }
};

const addQuestionToSection = (question) => {
    if (selectedSection.value === "") {
        emit(
            "show-notification",
            "warning",
            "Atención",
            "Selecciona una sección antes de añadir la pregunta",
        );
        return;
    }

    const sectionIndex = parseInt(selectedSection.value);
    const newSections = [...props.sections];
    const section = newSections[sectionIndex];

    if (!section.questions) {
        section.questions = [];
    }

    const exists = section.questions.some((q) => q.id === question.id);
    if (exists) {
        emit(
            "show-notification",
            "warning",
            "Atención",
            "Esta pregunta ya está en la sección",
        );
        return;
    }

    addQuestionDirectly(question, sectionIndex, newSections);
};

const addQuestionDirectly = (question, sectionIndex, newSections) => {
    const section = newSections[sectionIndex];
    const lastScreenIndex = (section.questions && section.questions.length > 0)
        ? Math.max(
            ...section.questions.map(q => {
                const n = Number(q?.screen);
                return Number.isFinite(n) && n >= 0 ? n : 0;
            })
        )
        : 0;

    section.questions.push({
        id: question.id,
        text: question.text,
        slug: question.slug,
        type: question.type,
        options: question.options || [],
        categories: question.categories || [],
        purposes: question.purposes || [],
        screen: lastScreenIndex,
        blockIfBankflipFilled: false,
        hideIfBankflipFilled: false,
        showIfBankflipFilled: null,
    });

    emit("update:sections", newSections);
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Pregunta añadida a la sección",
    );

    questionSearch.value = "";
    searchResults.value = [];
};

const addBuilderToSection = (builder) => {
    if (selectedSection.value === "") {
        emit(
            "show-notification",
            "warning",
            "Atención",
            "Selecciona una sección antes de añadir el builder",
        );
        return;
    }

    const sectionIndex = parseInt(selectedSection.value);
    const newSections = [...props.sections];
    const section = newSections[sectionIndex];

    if (!section.questions) {
        section.questions = [];
    }

    const exists = section.questions.some((q) => q.id === builder.id);
    if (exists) {
        emit(
            "show-notification",
            "warning",
            "Atención",
            "Este builder ya está en la sección",
        );
        return;
    }

    const lastScreenIndex = (section.questions && section.questions.length > 0)
        ? Math.max(
            ...section.questions.map(q => {
                const n = Number(q?.screen);
                return Number.isFinite(n) && n >= 0 ? n : 0;
            })
        )
        : 0;

    section.questions.push({
        id: builder.id,
        text: builder.text,
        slug: builder.slug,
        type: builder.type,
        description: builder.description,
        icon: builder.icon,
        color: builder.color,
        screen: lastScreenIndex,
        blockIfBankflipFilled: false,
        hideIfBankflipFilled: false,
        showIfBankflipFilled: null,
    });

    emit("update:sections", newSections);
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Builder añadido a la sección",
    );
};


const openConditionalOptionsModal = (sectionIndex, questionIndex) => {
    const question = props.sections[sectionIndex].questions[questionIndex];
    conditionalOptionsQuestion.value = question;
    conditionalOptionsAvailableOptions.value = question.options || [];
    conditionalOptionsAvailableQuestions.value = availableQuestionsForCondition.value;
    pendingConditionalOptionsData.value = {
        sectionIndex,
        questionIndex,
        question
    };
    showConditionalOptionsModal.value = true;
};

const closeConditionalOptionsModal = () => {
    showConditionalOptionsModal.value = false;
    conditionalOptionsQuestion.value = null;
    conditionalOptionsAvailableOptions.value = [];
    conditionalOptionsAvailableQuestions.value = [];
    pendingConditionalOptionsData.value = null;
};


const saveConditionalOptions = (configuration) => {
    if (!pendingConditionalOptionsData.value) return;

    const { sectionIndex, questionIndex } = pendingConditionalOptionsData.value;
    const newSections = [...props.sections];
    const question = newSections[sectionIndex].questions[questionIndex];

    question.conditionalOptions = configuration;

    emit("update:sections", newSections);
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Configuración de opciones condicionadas guardada",
    );

    closeConditionalOptionsModal();
};

const removeQuestionFromSection = (sectionIndex, questionIndex) => {
    const newSections = [...props.sections];
    newSections[sectionIndex].questions.splice(questionIndex, 1);
    emit("update:sections", newSections);
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Pregunta eliminada de la sección",
    );
};

const toggleCategoryDropdown = () => {
    showCategoryDropdown.value = !showCategoryDropdown.value;
};

const handleCategoryChange = () => {
    searchQuestions();
};

const selectAllCategories = () => {
    categoryFilter.value = availableCategories.value.map((c) => c.id);
    handleCategoryChange();
};

const clearCategories = () => {
    categoryFilter.value = [];
    handleCategoryChange();
};

const getCategoryName = (categoryId) => {
    const category = availableCategories.value.find((c) => c.id == categoryId);
    return category ? category.name : categoryId;
};

const loadCategories = async () => {
    try {
        const response = await fetch(
            "/admin/question-categories?hierarchical=true",
            {
                headers: {
                    Accept: "application/json",
                },
            },
        );
        if (response.ok) {
            const data = await response.json();
            const flatCategories = [];

            const flattenCategory = (category, level = 0) => {
                flatCategories.push({
                    id: category.id,
                    name: category.name,
                    description: category.description,
                    level: level,
                });

                if (category.children && category.children.length > 0) {
                    category.children.forEach((child) => {
                        flattenCategory(child, level + 1);
                    });
                }
            };

            data.categories.forEach((parent) => {
                flattenCategory(parent);
            });
            availableCategories.value = flatCategories;
        }
    } catch (error) {
        console.error("Error cargando categorías:", error);
    }
};

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

const handleDragStart = (sectionIndex, questionIndex) => {
    draggedQuestion.value =
        props.sections[sectionIndex].questions[questionIndex];
    draggedFromSection.value = sectionIndex;
    draggedFromIndex.value = questionIndex;
};

const handleDragOver = (event, sectionIndex, questionIndex) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = "move";
    dragOverSection.value = sectionIndex;
    dragOverIndex.value = questionIndex;
};

const handleDragEnd = () => {
    draggedQuestion.value = null;
    draggedFromSection.value = null;
    draggedFromIndex.value = null;
    dragOverSection.value = null;
    dragOverIndex.value = null;
};

const handleSectionDragStart = (sectionIndex) => {
    draggedSection.value = props.sections[sectionIndex];
    draggedSectionIndex.value = sectionIndex;
};

const handleSectionDragOver = (event, sectionIndex) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = "move";
    dragOverSectionIndex.value = sectionIndex;
};

const handleSectionDrop = (targetSectionIndex) => {
    event.preventDefault();

    if (draggedSectionIndex.value === targetSectionIndex) {
        return;
    }

    const newSections = [...props.sections];
    const draggedItem = newSections.splice(draggedSectionIndex.value, 1)[0];
    newSections.splice(targetSectionIndex, 0, draggedItem);

    emit("update:sections", newSections);
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Sección reordenada correctamente",
    );
};

const handleSectionDragEnd = () => {
    draggedSection.value = null;
    draggedSectionIndex.value = null;
    dragOverSectionIndex.value = null;
};

const handleDrop = (targetSectionIndex, targetQuestionIndex) => {
    if (draggedQuestion.value === null) return;

    const newSections = [...props.sections];

    if (draggedFromSection.value === targetSectionIndex) {
        const questions = newSections[targetSectionIndex].questions;
        const draggedItem = questions.splice(draggedFromIndex.value, 1)[0];
        questions.splice(targetQuestionIndex, 0, draggedItem);
    } else {
        const draggedItem = newSections[
            draggedFromSection.value
        ].questions.splice(draggedFromIndex.value, 1)[0];
        newSections[targetSectionIndex].questions.splice(
            targetQuestionIndex,
            0,
            draggedItem,
        );
    }

    emit("update:sections", newSections);
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Pregunta reordenada correctamente",
    );
};

const updateQuestionBlockStatus = (
    sectionIndex,
    questionIndex,
    blockStatus,
) => {
    const newSections = [...props.sections];
    newSections[sectionIndex].questions[questionIndex].blockIfBankflipFilled =
        blockStatus;
    emit("update:sections", newSections);
};

const updateQuestionHideStatus = (
    sectionIndex,
    questionIndex,
    hideStatus,
) => {
    const newSections = [...props.sections];
    newSections[sectionIndex].questions[questionIndex].hideIfBankflipFilled =
        hideStatus;
    emit("update:sections", newSections);
};

const getBankflipDisplayMode = (showIfBankflipFilled) => {
    if (showIfBankflipFilled === 1) return 'bankflip';
    if (showIfBankflipFilled === 0) return 'normal';
    return 'universal';
};

const updateQuestionBankflipStatus = (
    sectionIndex,
    questionIndex,
    displayMode,
) => {
    const newSections = [...props.sections];
    
    let bankflipStatus;
    switch (displayMode) {
        case 'normal':
            bankflipStatus = 0;
            break;
        case 'bankflip':
            bankflipStatus = 1;
            break;
        case 'universal':
            bankflipStatus = null;
            break;
        default:
            bankflipStatus = null;
    }
    
    newSections[sectionIndex].questions[questionIndex].showIfBankflipFilled = bankflipStatus;
    emit("update:sections", newSections);
};

const openConditionModal = (sectionIndex, questionIndex) => {
    const question = props.sections[sectionIndex].questions[questionIndex];
    conditionQuestion.value = {
        ...question,
        sectionIndex,
        questionIndex,
        sectionName: props.sections[sectionIndex].name
    };
    conditionSectionIndex.value = sectionIndex;
    conditionQuestionIndex.value = questionIndex;

    if (conditionQuestion.value.condition) {
        conditionForm.value = { ...conditionQuestion.value.condition };

        if (
            conditionForm.value.expectedValue === "Sí" ||
            conditionForm.value.expectedValue === "No"
        ) {
            conditionForm.value.conditionType =
                conditionForm.value.expectedValue === "Sí"
                    ? "equals"
                    : "not_equals";
            conditionForm.value.expectedValue = "";
        } else if (
            conditionForm.value.expectedValue === "true" ||
            conditionForm.value.expectedValue === "false"
        ) {
            conditionForm.value.conditionType =
                conditionForm.value.expectedValue === "true"
                    ? "equals"
                    : "not_equals";
            conditionForm.value.expectedValue = "";
        }
    } else {
        conditionForm.value = {
            dependsOnQuestionId: "",
            conditionType: "",
            expectedValue: "",
        };
    }

    showConditionModal.value = true;
};

const closeConditionModal = () => {
    showConditionModal.value = false;
    conditionQuestion.value = null;
    conditionSectionIndex.value = null;
    conditionQuestionIndex.value = null;
    conditionForm.value = {
        dependsOnQuestionId: "",
        conditionType: "",
        expectedValue: "",
    };
};

const onDependsOnQuestionChange = () => {
    conditionForm.value.conditionType = "";
    conditionForm.value.expectedValue = "";
};

const saveCondition = (condition) => {
    const newSections = [...props.sections];
    const question =
        newSections[conditionSectionIndex.value].questions[
            conditionQuestionIndex.value
        ];

    question.condition = condition;

    emit("update:sections", newSections);
    emit(
        "show-notification",
        "success",
        "Éxito",
        "Condición configurada correctamente",
    );
    closeConditionModal();
};

const availableQuestionsForCondition = computed(() => {
    const allQuestions = [];
    const currentSectionIndex = conditionSectionIndex.value;
    const currentQuestionIndex = conditionQuestionIndex.value;    
    props.sections.forEach((section, sectionIndex) => {
        section.questions.forEach((question, questionIndex) => {
            const isBeforeCurrent = 
                sectionIndex < currentSectionIndex || 
                (sectionIndex === currentSectionIndex && questionIndex < currentQuestionIndex);
            
            if (isBeforeCurrent) {
                allQuestions.push({
                    ...question,
                    sectionIndex,
                    questionIndex,
                    sectionName: section.name,
                    isSolicitante: true,
                    isConviviente: false,
                });
            }
        });
    });
    return allQuestions;
});

const availableQuestionsToCondition = computed(() => {
    const allQuestions = availableQuestionsForCondition.value.filter(
        (q) => q.type !== "builder",
    );

    return allQuestions;
});

const sectionsGridLayout = computed(() => {
    const totalSections = props.sections.length;
    if (totalSections === 0) return { columns: 1, rows: 0 };

    let columns = 1;

    if (totalSections <= 2) {
        columns = 2;
    } else if (totalSections <= 4) {
        columns = 2;
    } else if (totalSections <= 6) {
        columns = 3;
    } else if (totalSections <= 8) {
        columns = 4;
    } else if (totalSections <= 12) {
        columns = 4;
    } else {
        columns = 4;
    }

    const rows = Math.ceil(totalSections / columns);

    return { columns, rows, totalSections };
});

const selectedDependsOnQuestion = computed(() => {
    return availableQuestionsForCondition.value.find(
        (q) => q.id == conditionForm.value.dependsOnQuestionId,
    );
});

const removeCondition = (sectionIndex, questionIndex) => {
    if (
        confirm(
            "¿Estás seguro de que quieres eliminar la condición de esta pregunta?",
        )
    ) {
        const newSections = [...props.sections];
        delete newSections[sectionIndex].questions[questionIndex].condition;
        emit("update:sections", newSections);
        emit(
            "show-notification",
            "success",
            "Éxito",
            "Condición eliminada correctamente",
        );
    }
};

const getSectionDependencies = (section) => {
    const dependencies = [];

    section.questions.forEach((question) => {
        if (question.condition) {
            let dependsOnQuestion = null;
            props.sections.forEach((s) => {
                s.questions.forEach((q) => {
                    if (q.id == question.condition.dependsOnQuestionId) {
                        dependsOnQuestion = q;
                    }
                });
            });

            if (dependsOnQuestion) {
                dependencies.push({
                    questionId: question.id,
                    questionText: question.text,
                    dependsOnText: dependsOnQuestion.text,
                    conditionType: question.condition.conditionType,
                    expectedValue: question.condition.expectedValue,
                });
            }
        }
    });

    return dependencies;
};

const toggleSectionCollapse = (sectionIndex) => {
    const index = collapsedSections.value.indexOf(sectionIndex);
    if (index > -1) {
        collapsedSections.value.splice(index, 1);
    } else {
        collapsedSections.value.push(sectionIndex);
    }
    updateAllSectionsCollapsedState();
};

const toggleAllSections = () => {
    if (allSectionsCollapsed.value) {
        // Expandir todas
        collapsedSections.value = [];
        allSectionsCollapsed.value = false;
    } else {
        // Colapsar todas
        collapsedSections.value = props.sections.map((_, index) => index);
        allSectionsCollapsed.value = true;
    }
};

const updateAllSectionsCollapsedState = () => {
    allSectionsCollapsed.value =
        collapsedSections.value.length === props.sections.length;
};

const getQuestionKey = (question) => {
    return `question-${question.id}`;
};

const isOptionsExpanded = (question) => {
    return expandedOptions.value.has(getQuestionKey(question));
};

const toggleOptionsExpansion = (question) => {
    const key = getQuestionKey(question);
    if (expandedOptions.value.has(key)) {
        expandedOptions.value.delete(key);
    } else {
        expandedOptions.value.add(key);
    }
};

const getVisibleOptions = (question) => {
    if (!question.options || question.options.length === 0) return [];
    
    if (isOptionsExpanded(question)) {
        return question.options;
    }
    
    return question.options.slice(0, 3);
};

const initializeSectionSelection = () => {

    if (props.sections.length === 1) {
        selectedSection.value = "0";
    }

    else if (props.sections.length === 0) {
        selectedSection.value = "";
    }

    else if (selectedSection.value === "") {
        selectedSection.value = "";
    }
};

watch(() => props.sections.length, (newLength, oldLength) => {
    if (newLength === 1 && oldLength === 0) {
        selectedSection.value = "0";
    }

    else if (newLength === 0) {
        selectedSection.value = "";
    }

    else if (selectedSection.value !== "" && parseInt(selectedSection.value) >= newLength) {
        selectedSection.value = "";
    }
});

onMounted(() => {
    loadCategories();
    loadQuestionTypes();
    if (props.sections.length > 0) {
        collapsedSections.value = props.sections.map((_, index) => index);
        allSectionsCollapsed.value = true;
    }

    initializeSectionSelection();

    document.addEventListener("click", (e) => {
        if (!e.target.closest(".relative")) {
            showCategoryDropdown.value = false;
        }
    });
});
</script>

<style scoped>
/* Drag & Drop styles */
.dragging {
    opacity: 0.5;
    transform: rotate(5deg);
}

.drag-over {
    background-color: #dbeafe;
    border-color: #3b82f6;
}

/* Smooth transitions */
.transition-all {
    transition: all 0.2s ease-in-out;
}

/* Drag handle hover effect */
.cursor-move:hover {
    color: #6b7280;
}

/* Checkbox styling */
input[type="checkbox"]:checked {
    background-color: #3b82f6;
    border-color: #3b82f6;
}
</style>
