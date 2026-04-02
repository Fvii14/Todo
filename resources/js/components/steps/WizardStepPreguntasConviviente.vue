<template>
    <div class="wizard-step-preguntas-conviviente">
        <h3 class="text-xl font-semibold text-gray-900 mb-6">
            <i class="fas fa-question-circle text-blue-600 mr-2"></i>
            Preguntas del Formulario Conviviente
        </h3>

        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-medium text-gray-800">Añadir preguntas</h4>
                <button
                    @click="$emit('create-question')"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                >
                    <i class="fas fa-plus mr-2"></i>Crear nueva pregunta
                </button>
            </div>

            <!-- Búsqueda de preguntas de Collector -->
            <div class="mb-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <h5 class="font-medium text-purple-800 mb-3 flex items-center">
                    <i class="fas fa-star mr-2"></i>
                    Preguntas de Collector
                </h5>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div class="md:col-span-2">
                        <input
                            v-model="collectorQuestionSearch"
                            @input="handleCollectorSearchInput"
                            type="text"
                            placeholder="Buscar preguntas de Collector..."
                            class="w-full px-4 py-3 border border-purple-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        />
                    </div>
                    <div>
                        <div class="relative">
                            <button
                                type="button"
                                @click="toggleCollectorCategoryDropdown"
                                class="w-full px-4 py-3 border border-purple-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-left flex justify-between items-center"
                            >
                                <span v-if="collectorCategoryFilter.length === 0"
                                    >Todas las categorías</span
                                >
                                <span v-else-if="collectorCategoryFilter.length === 1">
                                    {{
                                        collectorCategoryFilter[0] === 'no_category'
                                            ? 'Sin categoría'
                                            : (() => {
                                                  const category = availableCategories.find(
                                                      (c) => c.id === collectorCategoryFilter[0],
                                                  )
                                                  return category
                                                      ? category.is_parent
                                                          ? category.name
                                                          : `${category.parent_name} > ${category.name}`
                                                      : 'Categoría desconocida'
                                              })()
                                    }}
                                </span>
                                <span v-else
                                    >{{ collectorCategoryFilter.length }} categorías
                                    seleccionadas</span
                                >
                                <i class="fas fa-chevron-down"></i>
                            </button>

                            <div
                                v-if="showCollectorCategoryDropdown"
                                class="absolute z-10 w-full mt-1 bg-white border border-purple-300 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                            >
                                <div class="p-2">
                                    <div class="flex gap-2 mb-2">
                                        <button
                                            type="button"
                                            @click="selectAllCollectorCategories"
                                            class="text-xs px-2 py-1 bg-purple-100 text-purple-700 rounded hover:bg-purple-200"
                                        >
                                            Seleccionar todo
                                        </button>
                                        <button
                                            type="button"
                                            @click="clearCollectorCategories"
                                            class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                                        >
                                            Limpiar
                                        </button>
                                    </div>
                                    <label
                                        class="flex items-center p-2 hover:bg-purple-50 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            value="no_category"
                                            v-model="collectorCategoryFilter"
                                            @change="handleCollectorCategoryChange"
                                            class="mr-2 text-purple-600 focus:ring-purple-500"
                                        />
                                        <span class="text-sm font-medium text-purple-800"
                                            >Sin categoría</span
                                        >
                                    </label>
                                    <label
                                        v-for="category in availableCategories"
                                        :key="category.id"
                                        :class="
                                            'flex items-center p-2 hover:bg-purple-50 cursor-pointer ' +
                                            (category.level > 0 ? 'ml-4' : '')
                                        "
                                    >
                                        <input
                                            type="checkbox"
                                            :value="category.id"
                                            v-model="collectorCategoryFilter"
                                            @change="handleCollectorCategoryChange"
                                            class="mr-2 text-purple-600 focus:ring-purple-500"
                                        />
                                        <span
                                            class="text-sm"
                                            :class="category.is_parent ? 'font-semibold' : ''"
                                            :style="{ paddingLeft: category.level * 20 + 'px' }"
                                        >
                                            {{
                                                category.level > 0
                                                    ? '└─ '.repeat(category.level)
                                                    : ''
                                            }}{{ category.name }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="relative">
                            <div
                                class="border border-purple-300 rounded-lg p-3 min-h-[48px] cursor-pointer"
                                @click="toggleCollectorPurposeDropdown"
                                :class="{
                                    'ring-2 ring-purple-500 border-purple-500':
                                        showCollectorPurposeDropdown,
                                }"
                            >
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-if="collectorPurposeFilter.length === 0"
                                        class="text-gray-500 text-sm"
                                    >
                                        Todas las finalidades
                                    </span>
                                    <span
                                        v-else-if="
                                            collectorPurposeFilter.includes('no_purpose') &&
                                            collectorPurposeFilter.length === 1
                                        "
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                    >
                                        Sin finalidad
                                    </span>
                                    <span
                                        v-else-if="collectorPurposeFilter.includes('no_purpose')"
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                    >
                                        Sin finalidad
                                    </span>
                                    <span
                                        v-for="purposeId in collectorPurposeFilter.filter(
                                            (id) => id !== 'no_purpose',
                                        )"
                                        :key="purposeId"
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                    >
                                        {{ getPurposeName(purposeId) }}
                                    </span>
                                </div>
                                <i
                                    class="fas fa-chevron-down absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400"
                                ></i>
                            </div>

                            <div
                                v-if="showCollectorPurposeDropdown"
                                class="absolute z-10 w-full mt-1 bg-white border border-purple-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                            >
                                <div class="p-2">
                                    <label
                                        class="flex items-center p-2 hover:bg-gray-50 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="collectorPurposeFilter.length === 0"
                                            @change="selectAllCollectorPurposes"
                                            class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                        />
                                        <span class="text-sm text-gray-700"
                                            >Todas las finalidades</span
                                        >
                                    </label>
                                    <label
                                        class="flex items-center p-2 hover:bg-gray-50 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="collectorPurposeFilter.includes('no_purpose')"
                                            @change="toggleCollectorPurpose('no_purpose')"
                                            class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                        />
                                        <span class="text-sm text-gray-700">Sin finalidad</span>
                                    </label>
                                    <label
                                        v-for="purpose in availablePurposes"
                                        :key="purpose.id"
                                        class="flex items-center p-2 hover:bg-gray-50 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="
                                                collectorPurposeFilter.includes(
                                                    purpose.id.toString(),
                                                )
                                            "
                                            @change="toggleCollectorPurpose(purpose.id.toString())"
                                            class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                        />
                                        <span class="text-sm text-gray-700">{{
                                            purpose.name
                                        }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="collectorSearching" class="text-center py-2">
                    <div
                        class="animate-spin rounded-full h-5 w-5 border-b-2 border-purple-600 mx-auto"
                    ></div>
                </div>

                <div
                    v-if="collectorSearchResults.length > 0"
                    class="bg-white border border-purple-200 rounded-lg max-h-48 overflow-y-auto"
                >
                    <div
                        v-for="question in collectorSearchResults"
                        :key="question.id"
                        class="p-3 hover:bg-purple-50 border-b border-purple-100 last:border-b-0 transition-colors"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div
                                @click="addQuestion(question)"
                                class="flex-1 min-w-0 cursor-pointer"
                            >
                                <p
                                    class="font-medium text-gray-800 flex items-center space-x-2 truncate pr-2"
                                >
                                    <span class="truncate">{{ question.text }}</span>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <span class="break-all">{{ question.slug }}</span> •
                                    {{ questionTypes[question.type] || question.type }}
                                    <span
                                        v-if="question.categories && question.categories.length > 0"
                                        class="ml-2"
                                    >
                                        <i class="fas fa-tag mr-1 text-blue-500"></i>
                                        {{ question.categories.map((c) => c.name).join(', ') }}
                                    </span>
                                    <span v-else class="ml-2">
                                        <i class="fas fa-tag mr-1 text-gray-400"></i>
                                        <span class="text-gray-400">Sin categoría</span>
                                    </span>
                                    <span
                                        v-if="question.purposes && question.purposes.length > 0"
                                        class="ml-2"
                                    >
                                        <i class="fas fa-bullseye mr-1 text-purple-500"></i>
                                        <span class="text-purple-600 font-medium">{{
                                            question.purposes.map((p) => p.name).join(', ')
                                        }}</span>
                                    </span>
                                    <span v-else class="ml-2">
                                        <i class="fas fa-bullseye mr-1 text-gray-400"></i>
                                        <span class="text-gray-400">Sin finalidad</span>
                                    </span>
                                </p>
                            </div>
                            <div class="flex items-center space-x-2 flex-shrink-0 ml-3">
                                <button
                                    @click.stop="$emit('edit-question', question)"
                                    class="p-1 text-gray-500 hover:text-blue-600 transition-colors"
                                    title="Editar pregunta"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    @click="addQuestion(question)"
                                    class="p-1 text-green-600 hover:text-green-700 transition-colors"
                                    title="Añadir al cuestionario"
                                >
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else-if="collectorQuestionSearch && !collectorSearching"
                    class="text-center py-4 text-gray-500"
                >
                    <i class="fas fa-search text-2xl mb-2"></i>
                    <p class="text-sm">No se encontraron preguntas de Collector</p>
                </div>
            </div>

            <!-- Búsqueda de otras preguntas -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h5 class="font-medium text-blue-800 mb-3 flex items-center">
                    <i class="fas fa-question-circle mr-2"></i>
                    Resto de preguntas
                </h5>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div class="md:col-span-2">
                        <input
                            v-model="generalQuestionSearch"
                            @input="handleGeneralSearchInput"
                            type="text"
                            placeholder="Buscar otras preguntas..."
                            class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    <div>
                        <div class="relative">
                            <button
                                type="button"
                                @click="toggleGeneralCategoryDropdown"
                                class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-left flex justify-between items-center"
                            >
                                <span v-if="generalCategoryFilter.length === 0"
                                    >Todas las categorías</span
                                >
                                <span v-else-if="generalCategoryFilter.length === 1">
                                    {{
                                        generalCategoryFilter[0] === 'no_category'
                                            ? 'Sin categoría'
                                            : (() => {
                                                  const category = availableCategories.find(
                                                      (c) => c.id === generalCategoryFilter[0],
                                                  )
                                                  return category
                                                      ? category.is_parent
                                                          ? category.name
                                                          : `${category.parent_name} > ${category.name}`
                                                      : 'Categoría desconocida'
                                              })()
                                    }}
                                </span>
                                <span v-else
                                    >{{ generalCategoryFilter.length }} categorías
                                    seleccionadas</span
                                >
                                <i class="fas fa-chevron-down"></i>
                            </button>

                            <div
                                v-if="showGeneralCategoryDropdown"
                                class="absolute z-10 w-full mt-1 bg-white border border-blue-300 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                            >
                                <div class="p-2">
                                    <div class="flex gap-2 mb-2">
                                        <button
                                            type="button"
                                            @click="selectAllGeneralCategories"
                                            class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                                        >
                                            Seleccionar todo
                                        </button>
                                        <button
                                            type="button"
                                            @click="clearGeneralCategories"
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
                                            v-model="generalCategoryFilter"
                                            @change="handleGeneralCategoryChange"
                                            class="mr-2 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="text-sm font-medium text-blue-800"
                                            >Sin categoría</span
                                        >
                                    </label>
                                    <label
                                        v-for="category in availableCategories"
                                        :key="category.id"
                                        :class="
                                            'flex items-center p-2 hover:bg-blue-50 cursor-pointer ' +
                                            (category.level > 0 ? 'ml-4' : '')
                                        "
                                    >
                                        <input
                                            type="checkbox"
                                            :value="category.id"
                                            v-model="generalCategoryFilter"
                                            @change="handleGeneralCategoryChange"
                                            class="mr-2 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span
                                            class="text-sm"
                                            :class="category.is_parent ? 'font-semibold' : ''"
                                            :style="{ paddingLeft: category.level * 20 + 'px' }"
                                        >
                                            {{
                                                category.level > 0
                                                    ? '└─ '.repeat(category.level)
                                                    : ''
                                            }}{{ category.name }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="relative">
                            <div
                                class="border border-blue-300 rounded-lg p-3 min-h-[48px] cursor-pointer"
                                @click="toggleGeneralPurposeDropdown"
                                :class="{
                                    'ring-2 ring-blue-500 border-blue-500':
                                        showGeneralPurposeDropdown,
                                }"
                            >
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-if="generalPurposeFilter.length === 0"
                                        class="text-gray-500 text-sm"
                                    >
                                        Todas las finalidades
                                    </span>
                                    <span
                                        v-else-if="
                                            generalPurposeFilter.includes('no_purpose') &&
                                            generalPurposeFilter.length === 1
                                        "
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                    >
                                        Sin finalidad
                                    </span>
                                    <span
                                        v-else-if="generalPurposeFilter.includes('no_purpose')"
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                    >
                                        Sin finalidad
                                    </span>
                                    <span
                                        v-for="purposeId in generalPurposeFilter.filter(
                                            (id) => id !== 'no_purpose',
                                        )"
                                        :key="purposeId"
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                    >
                                        {{ getPurposeName(purposeId) }}
                                    </span>
                                </div>
                                <i
                                    class="fas fa-chevron-down absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400"
                                ></i>
                            </div>

                            <div
                                v-if="showGeneralPurposeDropdown"
                                class="absolute z-10 w-full mt-1 bg-white border border-blue-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                            >
                                <div class="p-2">
                                    <label
                                        class="flex items-center p-2 hover:bg-gray-50 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="generalPurposeFilter.length === 0"
                                            @change="selectAllGeneralPurposes"
                                            class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="text-sm text-gray-700"
                                            >Todas las finalidades</span
                                        >
                                    </label>
                                    <label
                                        class="flex items-center p-2 hover:bg-gray-50 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="generalPurposeFilter.includes('no_purpose')"
                                            @change="toggleGeneralPurpose('no_purpose')"
                                            class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="text-sm text-gray-700">Sin finalidad</span>
                                    </label>
                                    <label
                                        v-for="purpose in availablePurposes"
                                        :key="purpose.id"
                                        class="flex items-center p-2 hover:bg-gray-50 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="
                                                generalPurposeFilter.includes(purpose.id.toString())
                                            "
                                            @change="toggleGeneralPurpose(purpose.id.toString())"
                                            class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="text-sm text-gray-700">{{
                                            purpose.name
                                        }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="generalSearching" class="text-center py-2">
                    <div
                        class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 mx-auto"
                    ></div>
                </div>

                <div
                    v-if="generalSearchResults.length > 0"
                    class="bg-white border border-blue-200 rounded-lg max-h-48 overflow-y-auto"
                >
                    <div
                        v-for="question in generalSearchResults"
                        :key="question.id"
                        class="p-3 hover:bg-blue-50 border-b border-blue-100 last:border-b-0 transition-colors"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div
                                @click="addQuestion(question)"
                                class="flex-1 min-w-0 cursor-pointer"
                            >
                                <p class="font-medium text-gray-800 truncate pr-2">
                                    {{ question.text }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    <span class="break-all">{{ question.slug }}</span> •
                                    {{ questionTypes[question.type] || question.type }}
                                    <span
                                        v-if="question.categories && question.categories.length > 0"
                                        class="ml-2"
                                    >
                                        <i class="fas fa-tag mr-1 text-blue-500"></i>
                                        {{ question.categories.map((c) => c.name).join(', ') }}
                                    </span>
                                    <span v-else class="ml-2">
                                        <i class="fas fa-tag mr-1 text-gray-400"></i>
                                        <span class="text-gray-400">Sin categoría</span>
                                    </span>
                                    <span
                                        v-if="question.purposes && question.purposes.length > 0"
                                        class="ml-2"
                                    >
                                        <i class="fas fa-bullseye mr-1 text-purple-500"></i>
                                        <span class="text-purple-600 font-medium">{{
                                            question.purposes.map((p) => p.name).join(', ')
                                        }}</span>
                                    </span>
                                    <span v-else class="ml-2">
                                        <i class="fas fa-bullseye mr-1 text-gray-400"></i>
                                        <span class="text-gray-400">Sin finalidad</span>
                                    </span>
                                </p>
                            </div>
                            <div class="flex items-center space-x-2 flex-shrink-0 ml-3">
                                <button
                                    @click.stop="$emit('edit-question', question)"
                                    class="p-1 text-gray-500 hover:text-blue-600 transition-colors"
                                    title="Editar pregunta"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    @click="addQuestion(question)"
                                    class="p-1 text-green-600 hover:text-green-700 transition-colors"
                                    title="Añadir al cuestionario"
                                >
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else-if="generalQuestionSearch && !generalSearching"
                    class="text-center py-4 text-gray-500"
                >
                    <i class="fas fa-search text-2xl mb-2"></i>
                    <p class="text-sm">No se encontraron otras preguntas</p>
                </div>
            </div>
        </div>

        <!-- Lista de preguntas añadidas -->
        <div v-if="questions.length > 0">
            <h4 class="text-lg font-medium text-gray-800 mb-4">
                Preguntas añadidas ({{ questions.length }})
            </h4>
            <div class="space-y-3">
                <div
                    ref="questionsContainer"
                    class="space-y-2"
                    @dragover.prevent
                    @drop="handleDrop"
                >
                    <div
                        v-for="(question, index) in questions"
                        :key="`question-${question.id}-${index}`"
                        :draggable="!multiSelectMode"
                        @dragstart="handleDragStart($event, index)"
                        @dragend="handleDragEnd"
                        @dragover="dragOverIndex = index"
                        @dragleave="dragOverIndex = null"
                        :class="[
                            'question-item bg-white border rounded-lg p-4 transition-all duration-200',
                            multiSelectMode
                                ? 'multi-select-item cursor-pointer'
                                : 'cursor-grab active:cursor-grabbing',
                            isDragging && draggedIndex === index
                                ? 'opacity-50 scale-95 drag-preview'
                                : '',
                            isDragOver && dragOverIndex === index ? 'drag-over' : 'border-gray-200',
                            selectedQuestions.includes(index)
                                ? 'selected ring-2 ring-blue-400 bg-blue-50'
                                : 'hover:border-gray-300 hover:shadow-sm',
                        ]"
                    >
                        <div class="flex items-start space-x-3">
                            <div class="flex items-center space-x-2 mt-1">
                                <div v-if="multiSelectMode" class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :checked="selectedQuestions.includes(index)"
                                        @click.stop
                                        class="selection-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    />
                                </div>
                                <div
                                    v-else
                                    class="drag-handle text-gray-400 hover:text-gray-600 cursor-grab active:cursor-grabbing p-1 rounded"
                                >
                                    <i class="fas fa-grip-vertical text-lg"></i>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-800 mb-1 truncate pr-2">
                                            {{ question.text }}
                                        </p>
                                        <p class="text-sm text-gray-500 mb-2">
                                            <span class="break-all">{{ question.slug }}</span> •
                                            {{ questionTypes[question.type] || question.type }}
                                            <span
                                                v-if="
                                                    question.categories &&
                                                    question.categories.length > 0
                                                "
                                                class="ml-2"
                                            >
                                                <i class="fas fa-tag mr-1 text-blue-500"></i>
                                                {{
                                                    question.categories
                                                        .map((c) => c.name)
                                                        .join(', ')
                                                }}
                                            </span>
                                            <span v-else class="ml-2">
                                                <i class="fas fa-tag mr-1 text-gray-400"></i>
                                                <span class="text-gray-400">Sin categoría</span>
                                            </span>
                                            <span
                                                v-if="
                                                    question.purposes &&
                                                    question.purposes.length > 0
                                                "
                                                class="ml-2"
                                            >
                                                <i class="fas fa-bullseye mr-1 text-purple-500"></i>
                                                <span class="text-purple-600 font-medium">{{
                                                    question.purposes.map((p) => p.name).join(', ')
                                                }}</span>
                                            </span>
                                            <span v-else class="ml-2">
                                                <i class="fas fa-bullseye mr-1 text-gray-400"></i>
                                                <span class="text-gray-400">Sin finalidad</span>
                                            </span>
                                        </p>
                                    </div>

                                    <div class="flex items-center space-x-1 ml-4 flex-shrink-0">
                                        <button
                                            @click.stop="$emit('edit-question', question)"
                                            class="p-2 text-gray-400 hover:text-blue-600 transition-colors"
                                            title="Editar pregunta"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button
                                            @click.stop="removeQuestion(index)"
                                            class="p-2 text-gray-400 hover:text-red-600 transition-colors"
                                            title="Eliminar pregunta"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="isDragging"
                    class="drop-zone mt-2 p-4 border-2 border-dashed border-blue-300 bg-blue-50 rounded-lg text-center text-blue-600"
                >
                    <i class="fas fa-arrow-down text-lg mb-1"></i>
                    <p class="text-sm font-medium">Suelta aquí para reordenar las preguntas</p>
                    <p class="text-xs text-blue-500 mt-1">
                        Las preguntas se reorganizarán automáticamente
                    </p>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-8 text-gray-500">
            <i class="fas fa-question-circle text-4xl mb-2"></i>
            <p>No hay preguntas añadidas</p>
            <p class="text-sm">Busca y añade preguntas existentes o crea nuevas</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'

const props = defineProps({
    questions: {
        type: Array,
        default: () => [],
    },
    availableCategories: {
        type: Array,
        default: () => [],
    },
    availablePurposes: {
        type: Array,
        default: () => [],
    },
    questionTypes: {
        type: Object,
        default: () => ({}),
    },
    csrf: {
        type: String,
        required: true,
    },
})

const emit = defineEmits(['update:questions', 'create-question', 'edit-question'])

// Estado de búsqueda
const collectorQuestionSearch = ref('')
const collectorSearchResults = ref([])
const collectorSearching = ref(false)
const collectorCategoryFilter = ref([])
const collectorPurposeFilter = ref([])
const showCollectorCategoryDropdown = ref(false)
const showCollectorPurposeDropdown = ref(false)

const generalQuestionSearch = ref('')
const generalSearchResults = ref([])
const generalSearching = ref(false)
const generalCategoryFilter = ref([])
const generalPurposeFilter = ref([])
const showGeneralCategoryDropdown = ref(false)
const showGeneralPurposeDropdown = ref(false)

// Estado de drag and drop
const isDragging = ref(false)
const draggedIndex = ref(null)
const dragOverIndex = ref(null)
const multiSelectMode = ref(false)
const selectedQuestions = ref([])

// Timeout para búsqueda
let searchInputTimeout = null

// Funciones de búsqueda
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

        const response = await fetch(`/admin/wizards/questions/search?${params.toString()}`, {
            headers: {
                'X-CSRF-TOKEN': props.csrf,
                Accept: 'application/json',
            },
        })

        if (response.ok) {
            const data = await response.json()
            if (collectorQuestionSearch.value.trim() === query) {
                collectorSearchResults.value = data.questions || []
            }
        }
    } catch (error) {
        console.error('Error buscando preguntas de Collector:', error)
    } finally {
        collectorSearching.value = false
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

const searchGeneralQuestions = async () => {
    // Solo limpiar resultados si no hay ningún filtro activo
    if (
        !generalQuestionSearch.value.trim() &&
        generalCategoryFilter.value.length === 0 &&
        generalPurposeFilter.value.length === 0
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

        const response = await fetch(`/admin/wizards/questions/search?${params.toString()}`, {
            headers: {
                'X-CSRF-TOKEN': props.csrf,
                Accept: 'application/json',
            },
        })

        if (response.ok) {
            const data = await response.json()
            if (generalQuestionSearch.value.trim() === query) {
                generalSearchResults.value = data.questions || []
            }
        }
    } catch (error) {
        console.error('Error buscando otras preguntas:', error)
    } finally {
        generalSearching.value = false
    }
}

const handleGeneralSearchInput = () => {
    if (searchInputTimeout) {
        clearTimeout(searchInputTimeout)
    }
    // Solo limpiar si no hay ningún filtro activo
    if (
        !generalQuestionSearch.value.trim() &&
        generalCategoryFilter.value.length === 0 &&
        generalPurposeFilter.value.length === 0
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

// Funciones de categorías
const toggleCollectorCategoryDropdown = () => {
    showCollectorCategoryDropdown.value = !showCollectorCategoryDropdown.value
    showGeneralCategoryDropdown.value = false
    showCollectorPurposeDropdown.value = false
    showGeneralPurposeDropdown.value = false
}

const toggleGeneralCategoryDropdown = () => {
    showGeneralCategoryDropdown.value = !showGeneralCategoryDropdown.value
    showCollectorCategoryDropdown.value = false
    showCollectorPurposeDropdown.value = false
    showGeneralPurposeDropdown.value = false
}

const selectAllCollectorCategories = () => {
    collectorCategoryFilter.value = props.availableCategories.map((c) => c.id)
    searchCollectorQuestions()
}

const clearCollectorCategories = () => {
    collectorCategoryFilter.value = []
    searchCollectorQuestions()
}

const handleCollectorCategoryChange = () => {
    searchCollectorQuestions()
}

const selectAllGeneralCategories = () => {
    generalCategoryFilter.value = props.availableCategories.map((c) => c.id)
    searchGeneralQuestions()
}

const clearGeneralCategories = () => {
    generalCategoryFilter.value = []
    searchGeneralQuestions()
}

const handleGeneralCategoryChange = () => {
    searchGeneralQuestions()
}

// Funciones de finalidades
const toggleCollectorPurposeDropdown = () => {
    showCollectorPurposeDropdown.value = !showCollectorPurposeDropdown.value
    showCollectorCategoryDropdown.value = false
    showGeneralCategoryDropdown.value = false
    showGeneralPurposeDropdown.value = false
}

const toggleGeneralPurposeDropdown = () => {
    showGeneralPurposeDropdown.value = !showGeneralPurposeDropdown.value
    showCollectorCategoryDropdown.value = false
    showGeneralCategoryDropdown.value = false
    showCollectorPurposeDropdown.value = false
}

const selectAllCollectorPurposes = () => {
    collectorPurposeFilter.value = []
    searchCollectorQuestions()
}

const toggleCollectorPurpose = (purposeId) => {
    const index = collectorPurposeFilter.value.indexOf(purposeId)
    if (index > -1) {
        collectorPurposeFilter.value.splice(index, 1)
    } else {
        collectorPurposeFilter.value.push(purposeId)
    }
    searchCollectorQuestions()
}

const selectAllGeneralPurposes = () => {
    generalPurposeFilter.value = []
    searchGeneralQuestions()
}

const toggleGeneralPurpose = (purposeId) => {
    const index = generalPurposeFilter.value.indexOf(purposeId)
    if (index > -1) {
        generalPurposeFilter.value.splice(index, 1)
    } else {
        generalPurposeFilter.value.push(purposeId)
    }
    searchGeneralQuestions()
}

const getPurposeName = (purposeId) => {
    if (purposeId === 'no_purpose') return 'Sin finalidad'
    const purpose = props.availablePurposes.find((p) => p.id.toString() === purposeId.toString())
    return purpose ? purpose.name : purposeId
}

// Funciones de preguntas
const addQuestion = (question) => {
    const exists = props.questions.some((q) => q.id === question.id)
    if (exists) {
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

    const updatedQuestions = [...props.questions, questionToAdd]
    emit('update:questions', updatedQuestions)

    // Limpiar solo el texto de búsqueda, pero mantener los filtros de categorías y finalidades
    collectorQuestionSearch.value = ''
    generalQuestionSearch.value = ''

    // Si hay filtros activos (categorías o finalidades), volver a ejecutar la búsqueda
    // para mantener el listado visible con las preguntas filtradas
    if (collectorCategoryFilter.value.length > 0 || collectorPurposeFilter.value.length > 0) {
        searchCollectorQuestions()
    } else {
        collectorSearchResults.value = []
    }

    if (generalCategoryFilter.value.length > 0 || generalPurposeFilter.value.length > 0) {
        searchGeneralQuestions()
    } else {
        generalSearchResults.value = []
    }
}

const removeQuestion = (index) => {
    const updatedQuestions = [...props.questions]
    updatedQuestions.splice(index, 1)
    emit('update:questions', updatedQuestions)
}

// Funciones de drag and drop
const handleDragStart = (event, index) => {
    if (multiSelectMode.value) return
    isDragging.value = true
    draggedIndex.value = index
    event.dataTransfer.effectAllowed = 'move'
    event.dataTransfer.setData('text/html', event.target.outerHTML)
    event.target.style.opacity = '0.5'
}

const handleDragEnd = () => {
    isDragging.value = false
    draggedIndex.value = null
    dragOverIndex.value = null
}

const getDropIndex = (event, questionsList) => {
    const questionItems = Array.from(event.currentTarget.querySelectorAll('.question-item'))
    let dropIndex = null

    questionItems.forEach((item, index) => {
        const rect = item.getBoundingClientRect()
        const y = event.clientY

        if (y >= rect.top && y <= rect.bottom) {
            const itemCenter = rect.top + rect.height / 2
            dropIndex = y < itemCenter ? index : index + 1
        }
    })

    return dropIndex !== null ? dropIndex : questionsList.length
}

const handleDrop = (event) => {
    event.preventDefault()
    if (multiSelectMode.value || draggedIndex.value === null) return

    const dropIndex = getDropIndex(event, props.questions)
    if (dropIndex === null || dropIndex === draggedIndex.value) {
        dragOverIndex.value = null
        return
    }

    const updatedQuestions = [...props.questions]
    const questionToMove = updatedQuestions[draggedIndex.value]
    updatedQuestions.splice(draggedIndex.value, 1)
    updatedQuestions.splice(dropIndex, 0, questionToMove)

    emit('update:questions', updatedQuestions)

    isDragging.value = false
    draggedIndex.value = null
    dragOverIndex.value = null
}

// Cerrar dropdowns al hacer click fuera
onMounted(() => {
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.relative')) {
            showCollectorCategoryDropdown.value = false
            showGeneralCategoryDropdown.value = false
            showCollectorPurposeDropdown.value = false
            showGeneralPurposeDropdown.value = false
        }
    })
})
</script>

<style scoped>
.drag-over {
    border-color: #3b82f6;
    background-color: #eff6ff;
}

.drag-preview {
    opacity: 0.5;
}

.selected {
    background-color: #dbeafe;
    border-color: #60a5fa;
}
</style>
