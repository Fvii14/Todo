<template>
    <div>
        <h3 class="text-xl font-semibold text-gray-900 mb-6">
            <i class="fas fa-shield-alt text-blue-600 mr-2"></i>
            Pre-requisitos de Elegibilidad
        </h3>

        <div class="mb-6">
            <p class="text-gray-600 mb-4">
                Define los pre-requisitos que deben cumplir los usuarios antes de poder acceder a
                esta ayuda. Estos requisitos se verifican contra las respuestas del usuario en el
                sistema.
            </p>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h4 class="font-medium text-blue-900 mb-2 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    Tipos de Pre-requisitos
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <strong>Requisito Simple:</strong> Una condición única sobre una pregunta
                        específica
                    </div>
                    <div>
                        <strong>Grupo de Requisitos:</strong> Múltiples condiciones con lógica
                        AND/OR
                    </div>
                </div>
            </div>
        </div>

        <div v-if="preRequisitos.length > 0" class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-medium text-gray-800">
                    Pre-requisitos configurados ({{ preRequisitos.length }})
                </h4>
                <div class="flex items-center gap-2">
                    <button
                        @click="openCreateModal()"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                    >
                        <i class="fas fa-plus mr-2"></i>Añadir pre-requisito
                    </button>
                    <button
                        @click="reloadAll"
                        class="px-3 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                        :disabled="loading"
                    >
                        <i :class="['fas', loading ? 'fa-spinner fa-spin' : 'fa-rotate']"></i>
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                <div
                    v-for="(preRequisito, index) in preRequisitos"
                    :key="preRequisito.id || index"
                    class="bg-white border border-gray-200 rounded-lg p-4"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <h5 class="font-medium text-gray-800">
                                    {{ preRequisito.name }}
                                </h5>
                                <span
                                    :class="[
                                        'ml-2 px-2 py-1 rounded-full text-xs font-medium',
                                        preRequisito.active
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-gray-100 text-gray-800',
                                    ]"
                                >
                                    {{ preRequisito.active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>

                            <p v-if="preRequisito.description" class="text-sm text-gray-600 mb-3">
                                {{ preRequisito.description }}
                            </p>

                            <div class="text-sm text-gray-600">
                                <div class="flex items-center mb-1">
                                    <i class="fas fa-user mr-2 text-blue-500"></i>
                                    <span class="font-medium">Objetivo:</span>
                                    <span class="ml-1">{{ getTargetTypeText(preRequisito) }}</span>
                                </div>

                                <div class="flex items-center mb-1">
                                    <i class="fas fa-cog mr-2 text-purple-500"></i>
                                    <span class="font-medium">Tipo:</span>
                                    <span class="ml-1">{{ getTypeText(preRequisito.type) }}</span>
                                </div>

                                <div
                                    v-if="preRequisito.type === 'simple'"
                                    class="mt-2 p-2 bg-gray-50 rounded"
                                >
                                    <div class="text-sm">
                                        <span class="font-medium">{{
                                            getQuestionText(preRequisito)
                                        }}</span>
                                        <span class="mx-2 text-gray-400">{{
                                            getOperatorText(preRequisito.operator)
                                        }}</span>
                                        <span class="font-medium text-green-700">{{
                                            formatValue(preRequisito)
                                        }}</span>
                                    </div>
                                </div>

                                <div
                                    v-if="preRequisito.type === 'group'"
                                    class="mt-2 p-2 bg-gray-50 rounded"
                                >
                                    <div class="text-sm mb-2">
                                        <span class="font-medium"
                                            >Grupo de
                                            {{ preRequisito.rules?.length || 0 }}
                                            reglas</span
                                        >
                                        <span class="ml-2 text-gray-400"
                                            >({{
                                                preRequisito.group_logic === 'AND'
                                                    ? 'TODOS'
                                                    : 'AL MENOS UNO'
                                            }}
                                            deben cumplirse)</span
                                        >
                                    </div>
                                    <div v-if="preRequisito.rules" class="space-y-1">
                                        <template
                                            v-for="(rule, ruleIndex) in preRequisito.rules"
                                            :key="ruleIndex"
                                        >
                                            <div
                                                v-if="rule.type === 'group'"
                                                class="text-xs text-gray-600 ml-2"
                                            >
                                                • Subgrupo ({{
                                                    rule.group_logic === 'AND'
                                                        ? 'TODOS'
                                                        : 'AL MENOS UNO'
                                                }}) · {{ rule.rules?.length || 0 }} regla(s)
                                                <div
                                                    v-if="rule.rules && rule.rules.length"
                                                    class="ml-4 mt-1 space-y-0.5"
                                                >
                                                    <div
                                                        v-for="(sub, subIndex) in rule.rules"
                                                        :key="subIndex"
                                                        class="text-[11px] text-gray-600"
                                                    >
                                                        - {{ getGroupRuleQuestionText(sub) }}
                                                        {{ getOperatorText(sub.operator) }}
                                                        {{ formatValue(sub) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-else class="text-xs text-gray-600 ml-2">
                                                • {{ getGroupRuleQuestionText(rule) }}
                                                {{ getOperatorText(rule.operator) }}
                                                {{ formatValue(rule) }}
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 ml-4">
                            <button
                                @click="openEditModal(preRequisito)"
                                class="p-2 text-gray-500 hover:text-blue-600 transition-colors"
                                title="Editar pre-requisito"
                            >
                                <i class="fas fa-edit"></i>
                            </button>
                            <button
                                @click="deletePreRequisito(preRequisito, index)"
                                class="p-2 text-gray-500 hover:text-red-600 transition-colors"
                                title="Eliminar pre-requisito"
                                :disabled="savingIds.has(preRequisito.id)"
                            >
                                <i
                                    :class="[
                                        'fas',
                                        savingIds.has(preRequisito.id)
                                            ? 'fa-spinner fa-spin'
                                            : 'fa-trash',
                                    ]"
                                ></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-12">
            <i class="fas fa-shield-alt text-4xl text-gray-300 mb-4"></i>
            <h4 class="text-lg font-medium text-gray-500 mb-2">
                No hay pre-requisitos configurados
            </h4>
            <p class="text-gray-400 mb-6">
                Los usuarios podrán acceder a esta ayuda sin restricciones
            </p>
            <button
                @click="openCreateModal()"
                class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
            >
                <i class="fas fa-plus mr-2"></i>Crear primer pre-requisito
            </button>
        </div>

        <div
            v-if="showCreateModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg p-6 w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-900">
                        {{
                            editingPreRequisito
                                ? 'Editar pre-requisito'
                                : 'Crear nuevo pre-requisito'
                        }}
                    </h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="savePreRequisito">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre del pre-requisito
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Ej: Tener un hijo menor de 3 años"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de pre-requisito
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.type"
                                    @change="handleTypeChange"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required
                                >
                                    <option value="">Seleccionar tipo</option>
                                    <option
                                        v-for="(label, value) in typeOptions"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Descripción
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Descripción detallada del pre-requisito"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de objetivo
                                <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.target_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required
                            >
                                <option value="">Seleccionar objetivo</option>
                                <option
                                    v-for="(label, value) in targetTypeOptions"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <!-- Selector de tipo de conviviente (solo si es conviviente específico) -->
                        <div v-if="form.target_type === 'conviviente'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de conviviente <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.conviviente_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required
                            >
                                <option value="">Seleccionar tipo de conviviente</option>
                                <option
                                    v-for="(label, value) in convivienteTypeOptions"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <div v-if="form.type === 'simple'">
                            <div class="space-y-4">
                                <h4 class="font-medium text-gray-800 mb-3">
                                    Configuración del requisito simple
                                </h4>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Pregunta
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="questionSearch"
                                            @input="handleQuestionSearchInput"
                                            @focus="showQuestionDropdown = true"
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            placeholder="Buscar pregunta..."
                                            required
                                        />
                                        <button
                                            v-if="form.question_id"
                                            @click="clearQuestionSelection"
                                            type="button"
                                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                        >
                                            <i class="fas fa-times"></i>
                                        </button>

                                        <!-- Question dropdown -->
                                        <div
                                            v-if="
                                                showQuestionDropdown &&
                                                (questionSearchResults.length > 0 ||
                                                    questionSearching)
                                            "
                                            class="absolute left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto z-10"
                                        >
                                            <div
                                                v-if="questionSearching"
                                                class="p-4 text-center text-gray-500"
                                            >
                                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                                Buscando preguntas...
                                            </div>
                                            <div
                                                v-else-if="questionSearchResults.length > 0"
                                                class="py-1"
                                            >
                                                <div
                                                    v-for="question in questionSearchResults"
                                                    :key="question.id"
                                                    @click="selectQuestion(question)"
                                                    class="px-3 py-1.5 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 flex items-center justify-between gap-3"
                                                >
                                                    <span class="text-sm text-gray-800 truncate">{{
                                                        question.text
                                                    }}</span>
                                                    <span class="text-xs text-gray-400 shrink-0">{{
                                                        questionTypes[question.type] ||
                                                        question.type
                                                    }}</span>
                                                </div>
                                            </div>
                                            <div
                                                v-else-if="questionSearch.trim()"
                                                class="p-4 text-center text-gray-500"
                                            >
                                                <i class="fas fa-search mr-2"></i>
                                                No se encontraron preguntas
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Operador -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Operador <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            v-model="form.operator"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            required
                                        >
                                            <option value="">Seleccionar operador</option>
                                            <option
                                                v-for="(label, value) in operatorOptions"
                                                :key="value"
                                                :value="value"
                                            >
                                                {{ label }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Tipo de valor esperado -->
                                    <div v-if="!['exists', 'not_exists'].includes(form.operator)">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Tipo de valor <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            v-model="form.value_type"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            required
                                        >
                                            <option value="">Seleccionar tipo de valor</option>
                                            <option
                                                v-for="(label, value) in valueTypeOptions"
                                                :key="value"
                                                :value="value"
                                            >
                                                {{ label }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Valor esperado -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Valor esperado
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <!-- Si el operador no requiere valor -->
                                        <div
                                            v-if="['exists', 'not_exists'].includes(form.operator)"
                                            class="text-sm text-gray-500"
                                        >
                                            Este operador no requiere valor.
                                        </div>

                                        <!-- Asegurar selección de pregunta -->
                                        <input
                                            v-else-if="!currentQuestion"
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
                                            placeholder="Selecciona una pregunta primero"
                                            disabled
                                        />

                                        <!-- Boolean: Sí / No -->
                                        <select
                                            v-else-if="
                                                currentQuestion &&
                                                currentQuestion.type === 'boolean'
                                            "
                                            v-model="form.value"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        >
                                            <option value="1">Sí</option>
                                            <option value="0">No</option>
                                        </select>

                                        <!-- Select: opciones -->
                                        <select
                                            v-else-if="
                                                currentQuestion &&
                                                currentQuestion.type === 'select' &&
                                                Array.isArray(currentQuestion.options)
                                            "
                                            v-model="form.value"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        >
                                            <option
                                                v-for="(opt, idx) in currentQuestion.options"
                                                :key="idx"
                                                :value="idx.toString()"
                                            >
                                                {{ opt }}
                                            </option>
                                        </select>

                                        <!-- Multiple: selección múltiple -->
                                        <div
                                            v-else-if="
                                                currentQuestion &&
                                                currentQuestion.type === 'multiple' &&
                                                Array.isArray(currentQuestion.options)
                                            "
                                            class="space-y-2"
                                        >
                                            <div
                                                class="border border-gray-300 rounded-md p-2 max-h-36 overflow-y-auto"
                                            >
                                                <label
                                                    v-for="(opt, idx) in currentQuestion.options"
                                                    :key="idx"
                                                    class="flex items-center space-x-2 py-1"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        :value="idx.toString()"
                                                        v-model="multipleSelection"
                                                        class="rounded text-blue-600 border-gray-300"
                                                    />
                                                    <span class="text-sm text-gray-700">{{
                                                        opt
                                                    }}</span>
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                Usa operadores como "in" o "contains" para listas.
                                            </p>
                                        </div>

                                        <!-- Date: sistema dinámico -->
                                        <div
                                            v-else-if="
                                                currentQuestion && currentQuestion.type === 'date'
                                            "
                                            class="space-y-2"
                                        >
                                            <!-- DEBUG: form.value_type = {{ form.value_type }}, form.value = {{ form.value }} -->
                                            <!-- DEBUG: currentQuestion.type = {{ currentQuestion.type }}, form.value_type === 'age_minimum' = {{ form.value_type === 'age_minimum' }} -->

                                            <!-- Edad mínima -->
                                            <div
                                                v-if="form.value_type === 'age_minimum'"
                                                class="grid grid-cols-3 gap-2"
                                            >
                                                <!-- DEBUG: RENDERIZANDO AGE_MINIMUM - form.value = {{ form.value }}, form.age_unit = {{ form.age_unit }} -->
                                                <input
                                                    v-model="inputValue"
                                                    type="number"
                                                    min="0"
                                                    class="col-span-2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="Edad mínima"
                                                />
                                                <select
                                                    v-model="form.age_unit"
                                                    class="col-span-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                >
                                                    <option
                                                        v-for="(label, value) in ageUnitOptions"
                                                        :key="value"
                                                        :value="value"
                                                    >
                                                        {{ label }}
                                                    </option>
                                                </select>
                                            </div>

                                            <!-- Edad máxima -->
                                            <div
                                                v-else-if="form.value_type === 'age_maximum'"
                                                class="grid grid-cols-3 gap-2"
                                            >
                                                <input
                                                    v-model="form.value"
                                                    type="number"
                                                    min="0"
                                                    class="col-span-2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="Edad máxima"
                                                />
                                                <select
                                                    v-model="form.age_unit"
                                                    class="col-span-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                >
                                                    <option
                                                        v-for="(label, value) in ageUnitOptions"
                                                        :key="value"
                                                        :value="value"
                                                    >
                                                        {{ label }}
                                                    </option>
                                                </select>
                                            </div>

                                            <!-- Rango de edad -->
                                            <div
                                                v-else-if="form.value_type === 'age_range'"
                                                class="space-y-2"
                                            >
                                                <div class="grid grid-cols-3 gap-2">
                                                    <input
                                                        v-model="form.value"
                                                        type="number"
                                                        min="0"
                                                        class="col-span-2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                        placeholder="Edad mínima"
                                                    />
                                                    <select
                                                        v-model="form.age_unit"
                                                        class="col-span-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    >
                                                        <option
                                                            v-for="(label, value) in ageUnitOptions"
                                                            :key="value"
                                                            :value="value"
                                                        >
                                                            {{ label }}
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="grid grid-cols-3 gap-2">
                                                    <input
                                                        v-model="form.value2"
                                                        type="number"
                                                        min="0"
                                                        class="col-span-2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                        placeholder="Edad máxima"
                                                    />
                                                    <div
                                                        class="col-span-1 px-3 py-2 text-sm text-gray-500 flex items-center"
                                                    >
                                                        {{ ageUnitOptions[form.age_unit] }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Fecha relativa -->
                                            <div
                                                v-else-if="form.value_type === 'relative_date'"
                                                class="grid grid-cols-3 gap-2"
                                            >
                                                <input
                                                    v-model.number="dateRelativeAmount"
                                                    type="number"
                                                    min="0"
                                                    class="col-span-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="Cantidad"
                                                    @input="syncDateValue()"
                                                />
                                                <select
                                                    v-model="dateRelativeUnit"
                                                    class="col-span-2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    @change="syncDateValue()"
                                                >
                                                    <option value="years">años</option>
                                                    <option value="months">meses</option>
                                                    <option value="days">días</option>
                                                </select>
                                            </div>

                                            <!-- Valor exacto -->
                                            <input
                                                v-else-if="form.value_type === 'exact'"
                                                v-model="form.value"
                                                type="date"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            />

                                            <p class="text-xs text-gray-500">
                                                <span v-if="form.value_type === 'exact'"
                                                    >Fecha específica</span
                                                >
                                                <span
                                                    v-else-if="form.value_type === 'relative_date'"
                                                    >Se calculará desde hoy</span
                                                >
                                                <span v-else-if="form.value_type === 'age_minimum'"
                                                    >Se calculará dinámicamente (mayor de X
                                                    {{ ageUnitOptions[form.age_unit] }})</span
                                                >
                                                <span v-else-if="form.value_type === 'age_maximum'"
                                                    >Se calculará dinámicamente (menor de X
                                                    {{ ageUnitOptions[form.age_unit] }})</span
                                                >
                                                <span v-else-if="form.value_type === 'age_range'"
                                                    >Se calculará dinámicamente (entre X y Y
                                                    {{ ageUnitOptions[form.age_unit] }})</span
                                                >
                                            </p>
                                        </div>

                                        <!-- Integer -->
                                        <input
                                            v-else-if="
                                                currentQuestion &&
                                                currentQuestion.type === 'integer'
                                            "
                                            v-model="form.value"
                                            type="number"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            placeholder="Número"
                                        />

                                        <!-- String genérico como último recurso -->
                                        <input
                                            v-else
                                            v-model="form.value"
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            placeholder="Valor esperado"
                                        />
                                    </div>
                                </div>

                                <div v-if="form.operator === 'between'">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Segundo valor (para operador "entre")
                                    </label>
                                    <input
                                        v-model="form.value2"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Valor máximo"
                                    />
                                </div>
                            </div>
                        </div>

                        <div v-if="form.type === 'group'">
                            <GroupPreRequisitoForm
                                v-model:group-logic="form.group_logic"
                                v-model:rules="form.rules"
                                :available-questions="availableQuestions"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Mensaje de error personalizado
                            </label>
                            <input
                                v-model="form.error_message"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Mensaje que se mostrará si no se cumple el pre-requisito"
                            />
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button
                                @click="closeModal"
                                type="button"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="saving"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                            >
                                <i v-if="saving" class="fas fa-spinner fa-spin mr-2"></i>
                                {{
                                    saving
                                        ? 'Guardando...'
                                        : editingPreRequisito
                                          ? 'Actualizar'
                                          : 'Crear'
                                }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted, watch, nextTick } from 'vue'
import GroupPreRequisitoForm from './GroupPreRequisitoForm.vue'

export default {
    name: 'WizardStepPreRequisites',
    components: { GroupPreRequisitoForm },
    props: {
        csrf: { type: String, required: true },
        questionnaireName: { type: String, default: '' },
        questionnaireActive: { type: Boolean, default: false },
        preRequisitos: { type: Array, default: () => [] },
    },
    emits: ['update:preRequisitos'],
    setup(props, { emit }) {
        const loading = ref(false)
        const preRequisitos = ref([...props.preRequisitos])
        const availableQuestions = ref([])
        const showCreateModal = ref(false)
        const editingPreRequisito = ref(null)
        const saving = ref(false)
        const savingIds = ref(new Set())

        // Reaccionar a cambios en el prop preRequisitos
        watch(
            () => props.preRequisitos,
            (newPreRequisitos) => {
                preRequisitos.value = [...newPreRequisitos]
            },
            { deep: true },
        )

        // Question search functionality
        const questionSearch = ref('')
        const questionSearchResults = ref([])
        const questionSearching = ref(false)
        const showQuestionDropdown = ref(false)
        let questionSearchTimeout = null

        const form = reactive({
            name: '',
            description: '',
            type: '',
            target_type: 'solicitante',
            conviviente_type: null, // Para conviviente específico
            question_id: null,
            operator: '',
            value: null,
            value2: null,
            value_type: 'exact', // exact, relative_date, age_minimum, age_maximum, age_range
            age_unit: 'years', // years, months, days - para tipos de edad
            group_logic: 'AND',
            rules: [],
            error_message: '',
            active: true,
        })

        // Variable separada para el valor del input (para evitar problemas de reactividad)
        const inputValue = ref(null)

        // Sincronizar inputValue con form.value
        watch(inputValue, (newValue) => {
            form.value = newValue
        })

        const typeOptions = {
            simple: 'Requisito simple',
            group: 'Grupo de requisitos',
        }

        const targetTypeOptions = {
            solicitante: 'Solicitante',
            conviviente: 'Conviviente específico',
            unidad_convivencia_completa: 'Unidad de convivencia completa (con solicitante)',
            unidad_convivencia_sin_solicitante:
                'Unidad de convivencia sin solicitante (solo convivientes)',
            unidad_familiar_completa:
                'Unidad familiar completa (con solicitante + solo familiares)',
            unidad_familiar_sin_solicitante: 'Unidad familiar sin solicitante (solo familiares)',
            any_conviviente: 'Cualquier conviviente',
            any_familiar: 'Cualquier familiar (excluyendo no_familiar)',
            any_persona_unidad: 'Cualquier persona de la unidad',
        }

        const convivienteTypeOptions = {
            conyuge: 'Cónyuge',
            hijo: 'Hijo/a',
            padre: 'Padre/Madre',
            otro: 'Otro familiar',
            no_familiar: 'No familiar',
        }

        const valueTypeOptions = {
            exact: 'Valor exacto',
            relative_date: 'Fecha relativa (hace X tiempo)',
            age_minimum: 'Edad mínima (mayor de X)',
            age_maximum: 'Edad máxima (menor de X)',
            age_range: 'Rango de edad (entre X y Y)',
        }

        const ageUnitOptions = {
            years: 'años',
            months: 'meses',
            days: 'días',
        }

        const operatorOptions = {
            '==': 'Igual a',
            '!=': 'Distinto de',
            '>': 'Mayor que',
            '>=': 'Mayor o igual que',
            '<': 'Menor que',
            '<=': 'Menor o igual que',
            contains: 'Contiene',
            not_contains: 'No contiene',
            between: 'Entre',
            in: 'En la lista',
            not_in: 'No en la lista',
            exists: 'Existe',
            not_exists: 'No existe',
        }

        const questionTypes = {
            string: 'Texto',
            integer: 'Número',
            boolean: 'Sí / No',
            select: 'Selección',
            multiple: 'Selección múltiple',
            date: 'Fecha',
            info: 'Informativa',
        }

        // Estado derivado para valor esperado dinámico
        const selectedQuestionRef = ref(null)
        const selectedQuestion = computed(() => {
            if (selectedQuestionRef.value && selectedQuestionRef.value.id == form.question_id) {
                return selectedQuestionRef.value
            }
            return (
                questionSearchResults.value.find((q) => q.id == form.question_id) ||
                availableQuestions.value.find((q) => q.id == form.question_id) ||
                null
            )
        })

        const currentQuestion = computed(() => {
            const result = selectedQuestionRef.value || selectedQuestion.value
            return result
        })

        // Multiple selection helper (para type multiple)
        const multipleSelection = ref([])
        // Sincronizar form.value cuando cambia multipleSelection
        const stopMultipleWatch = watch(
            multipleSelection,
            (vals) => {
                if (Array.isArray(vals)) {
                    form.value = vals
                }
            },
            { deep: true },
        )

        // Soporte fecha relativa/absoluta
        const dateMode = ref('absolute')
        const dateAbsolute = ref('')
        const dateRelativeAmount = ref(0)
        const dateRelativeUnit = ref('years')
        const syncDateValue = () => {
            if (dateMode.value === 'absolute') {
                form.value = dateAbsolute.value
                return
            }
            // relativa => convertir a fecha exacta desde hoy
            const now = new Date()
            const d = new Date(now)
            const amount = Number(dateRelativeAmount.value) || 0
            switch (dateRelativeUnit.value) {
                case 'years':
                    d.setFullYear(d.getFullYear() - amount)
                    break
                case 'months':
                    d.setMonth(d.getMonth() - amount)
                    break
                case 'days':
                    d.setDate(d.getDate() - amount)
                    break
            }
            const yyyy = d.getFullYear()
            const mm = String(d.getMonth() + 1).padStart(2, '0')
            const dd = String(d.getDate()).padStart(2, '0')
            form.value = `${yyyy}-${mm}-${dd}`
        }

        const getTargetTypeText = (pre) => {
            const targetType = pre.target_type || pre
            const baseText = targetTypeOptions[targetType] || targetType

            if (targetType === 'conviviente' && pre.conviviente_type) {
                const convivienteText =
                    convivienteTypeOptions[pre.conviviente_type] || pre.conviviente_type
                return `${baseText} (${convivienteText})`
            }

            return baseText
        }

        const getQuestionText = (pre) => {
            // Si ya tiene la pregunta completa, usarla
            if (pre.question?.text) {
                return pre.question.text
            }

            // Si solo tiene question_id, buscar en las preguntas disponibles
            if (pre.question_id) {
                const found = availableQuestions.value.find((q) => q.id === pre.question_id)
                if (found) {
                    return found.text
                }
            }

            return 'Pregunta no encontrada'
        }

        const getGroupRuleQuestionText = (rule) => {
            if (rule?.type === 'group') {
                const count = Array.isArray(rule.rules) ? rule.rules.length : 0
                const logic = (rule.group_logic || 'AND') === 'AND' ? 'TODOS' : 'AL MENOS UNO'
                return `Subgrupo (${logic}) · ${count} regla(s)`
            }
            if (rule?.question?.text) return rule.question.text
            const byId = availableQuestions.value.find((q) => q.id == rule?.question_id)
            if (byId) return byId.text
            console.log(
                'Listado (group): pendiente cargar question_id=',
                rule?.question_id,
                'availableCount=',
                availableQuestions.value.length,
            )
            return `Pregunta #${rule?.question_id || '?'} (cargando...)`
        }
        const getTypeText = (type) => typeOptions[type] || type
        const getOperatorText = (operator) => operatorOptions[operator] || operator

        const getAgeUnitText = (unit) => {
            const unitMap = {
                years: 'años',
                months: 'meses',
                days: 'días',
            }
            return unitMap[unit] || unit
        }
        const formatValue = (pre) => {
            if (!pre) return ''
            if (Array.isArray(pre)) return pre.join(', ')

            // Para operadores exists/not_exists
            if (pre.operator === 'exists') return 'Existe'
            if (pre.operator === 'not_exists') return 'No existe'

            // Para operador between
            if (pre.operator === 'between') {
                return `${pre.value} - ${pre.value2}`
            }

            // Para pre-requisitos de edad
            if (pre.value_type === 'age_minimum') {
                const unit = getAgeUnitText(pre.age_unit || 'years')
                return `Mayor de ${pre.value} ${unit}`
            }
            if (pre.value_type === 'age_maximum') {
                const unit = getAgeUnitText(pre.age_unit || 'years')
                return `Menor de ${pre.value} ${unit}`
            }
            if (pre.value_type === 'age_range') {
                const unit = getAgeUnitText(pre.age_unit || 'years')
                return `Entre ${pre.value} y ${pre.value2} ${unit}`
            }
            if (pre.value_type === 'relative_date') {
                const unit = getAgeUnitText(pre.age_unit || 'years')
                return `Hace ${pre.value} ${unit}`
            }

            if (pre.question_id) {
                const question = availableQuestions.value.find((q) => q.id === pre.question_id)
                if (
                    question &&
                    (question.type === 'select' || question.type === 'multiple') &&
                    Array.isArray(question.options)
                ) {
                    if (question.type === 'select') {
                        const optionIndex = parseInt(pre.value)
                        if (!isNaN(optionIndex) && question.options[optionIndex]) {
                            return question.options[optionIndex]
                        }
                    } else if (question.type === 'multiple') {
                        let values = pre.value
                        if (typeof values === 'string') {
                            try {
                                values = JSON.parse(values)
                            } catch (e) {
                                return pre.value
                            }
                        }
                        if (Array.isArray(values)) {
                            return values
                                .map((val) => {
                                    const optionIndex = parseInt(val)
                                    if (!isNaN(optionIndex) && question.options[optionIndex]) {
                                        return question.options[optionIndex]
                                    }
                                    return val
                                })
                                .join(', ')
                        }
                    }
                }
            }
            return pre.value || ''
        }

        const resetForm = () => {
            Object.assign(form, {
                name: '',
                description: '',
                type: '',
                target_type: 'solicitante',
                conviviente_type: null,
                question_id: null,
                operator: '',
                value: null,
                value2: null,
                value_type: 'exact',
                age_unit: 'years',
                group_logic: 'AND',
                rules: [],
                error_message: '',
                active: true,
            })
            selectedQuestionRef.value = null
            questionSearch.value = ''
            multipleSelection.value = []
        }

        const handleTypeChange = () => {
            if (form.type === 'simple') {
                form.group_logic = 'AND'
                form.rules = []
            } else if (form.type === 'group') {
                form.question_id = null
                form.operator = ''
                form.value = null
                form.value2 = null
            }
        }

        const apiBase = computed(() => `/admin/ayudas/${props.ayudaId}/pre-requisitos`)

        const fetchJson = async (url, options = {}) => {
            const res = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': props.csrf,
                },
                credentials: 'same-origin',
                ...options,
            })
            if (!res.ok) {
                const msg = await res.text()
                throw new Error(msg || `HTTP ${res.status}`)
            }
            return res.status !== 204 ? res.json() : null
        }

        const updatePreRequisitos = (newPreRequisitos) => {
            preRequisitos.value = [...newPreRequisitos]
            emit('update:preRequisitos', preRequisitos.value)
        }

        const loadQuestions = async () => {
            try {
                const data = await fetchJson('/admin/pre-requisitos/questions')
                availableQuestions.value = Array.isArray(data) ? data : data?.data || []
            } catch (e) {
                console.error('Error cargando preguntas:', e)
            }
        }

        const loadSpecificQuestion = async (questionId) => {
            try {
                const data = await fetchJson(`/admin/questions/${questionId}`)
                if (data) {
                    // Añadir la pregunta a la lista de preguntas disponibles si no está
                    const exists = availableQuestions.value.find((q) => q.id === data.id)
                    if (!exists) {
                        availableQuestions.value.push(data)
                    }

                    selectedQuestionRef.value = data
                    questionSearch.value = data.text
                }
            } catch (e) {
                console.error('Error cargando pregunta específica:', e)
            }
        }

        const searchQuestions = async () => {
            if (!questionSearch.value.trim()) {
                questionSearchResults.value = []
                return
            }

            const query = questionSearch.value.trim()
            questionSearching.value = true
            try {
                const params = new URLSearchParams()
                params.append('search', questionSearch.value)
                params.append('limit', '20')

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
                    if (questionSearch.value.trim() === query) {
                        questionSearchResults.value = data.questions || []
                    }
                }
            } catch (error) {
                console.error('Error buscando preguntas:', error)
            } finally {
                questionSearching.value = false
            }
        }

        const handleQuestionSearchInput = () => {
            if (questionSearchTimeout) {
                clearTimeout(questionSearchTimeout)
            }

            if (!questionSearch.value.trim()) {
                questionSearchResults.value = []
                questionSearching.value = false
                return
            }

            questionSearching.value = true
            questionSearchResults.value = []
            questionSearchTimeout = setTimeout(() => {
                searchQuestions()
            }, 400)
        }

        const selectQuestion = (question) => {
            console.log('🔍 selectQuestion called with:', question)
            selectedQuestionRef.value = question
            form.question_id = question.id
            questionSearch.value = question.text
            showQuestionDropdown.value = false
            console.log('🔍 After selectQuestion - selectedQuestionRef:', selectedQuestionRef.value)
            console.log('🔍 After selectQuestion - form.question_id:', form.question_id)
            // Inicializaciones según tipo
            if (question.type === 'multiple') {
                multipleSelection.value = Array.isArray(form.value) ? [...form.value] : []
            } else if (question.type === 'boolean') {
                if (form.value !== '0' && form.value !== '1') form.value = '1'
            } else if (question.type === 'select') {
                if (typeof form.value !== 'string') form.value = '0'
            } else if (question.type === 'date') {
                dateMode.value = 'absolute'
                dateAbsolute.value = ''
                dateRelativeAmount.value = 0
                dateRelativeUnit.value = 'years'
                form.value = ''
            }
        }

        const clearQuestionSelection = () => {
            form.question_id = null
            questionSearch.value = ''
            questionSearchResults.value = []
            multipleSelection.value = []
            dateAbsolute.value = ''
            dateRelativeAmount.value = 0
            dateRelativeUnit.value = 'years'
            form.value = null
            form.value2 = null
        }

        const openCreateModal = () => {
            editingPreRequisito.value = null
            resetForm()
            showCreateModal.value = true
        }

        const openEditModal = async (pre) => {
            editingPreRequisito.value = pre
            resetForm()

            // Mapear datos existentes al formulario
            console.log('DEBUG openEditModal - pre data:', pre)
            Object.assign(form, {
                id: pre.id,
                name: pre.name,
                description: pre.description,
                type: pre.type,
                target_type: pre.target_type,
                conviviente_type: pre.conviviente_type ?? null,
                question_id: pre.question_id ?? null,
                operator: pre.operator ?? '',
                value: pre.value ?? null,
                value2: pre.value2 ?? null,
                value_type: pre.value_type ?? 'exact',
                age_unit: pre.age_unit ?? 'years',
                group_logic: pre.group_logic ?? 'AND',
                rules: pre.rules ? JSON.parse(JSON.stringify(pre.rules)) : [],
                error_message: pre.error_message ?? '',
                active: !!pre.active,
            })
            console.log('DEBUG openEditModal - form after assign:', form)
            console.log('🔍 DEBUG openEditModal - form específico:', {
                value_type: form.value_type,
                value: form.value,
                age_unit: form.age_unit,
                question_id: form.question_id,
            })

            // Establecer inputValue para evitar problemas de reactividad
            inputValue.value = form.value

            // Set question search text if question is selected
            if (pre.question_id) {
                // Buscar la pregunta en las preguntas disponibles
                const found = availableQuestions.value.find((q) => q.id == pre.question_id)
                if (found) {
                    selectedQuestionRef.value = found
                    questionSearch.value = found.text
                } else {
                    // Si no está en las preguntas cargadas, cargar la pregunta específica
                    await loadSpecificQuestion(pre.question_id)
                }
            } else {
                // Limpiar si no hay pregunta
                selectedQuestionRef.value = null
                questionSearch.value = ''
            }

            // Sincronizar valores de fecha si es necesario
            if (pre.value_type === 'relative_date' && pre.value) {
                syncDateValue()
            }

            console.log('DEBUG openEditModal - final form state:', {
                value: form.value,
                value2: form.value2,
                value_type: form.value_type,
                age_unit: form.age_unit,
                operator: form.operator,
                question_id: form.question_id,
            })

            showCreateModal.value = true

            // Forzar actualización del input después de que el modal se abra
            nextTick(() => {
                console.log('🔍 DEBUG nextTick - form.value:', form.value)
                console.log('🔍 DEBUG nextTick - inputValue.value:', inputValue.value)
                // Sincronizar inputValue con form.value
                inputValue.value = form.value
            })
        }

        // Mantener selectedQuestionRef sincronizado cuando cambia question_id
        watch(
            () => form.question_id,
            (newId) => {
                console.log('🔍 Watch triggered - newId:', newId)
                if (!newId) {
                    selectedQuestionRef.value = null
                    return
                }
                // Buscar en todos los lugares posibles
                const found =
                    questionSearchResults.value.find((q) => q.id == newId) ||
                    availableQuestions.value.find((q) => q.id == newId) ||
                    selectedQuestionRef.value
                console.log('🔍 Watch - found question:', found)
                if (found) {
                    selectedQuestionRef.value = found
                    if (!questionSearch.value) questionSearch.value = found.text
                }
            },
            { immediate: true },
        )

        const validateForm = () => {
            const errors = []
            if (!form.name) errors.push('Nombre del pre-requisito')
            if (!form.type) errors.push('Tipo de pre-requisito')
            if (!form.target_type) errors.push('Tipo de objetivo')

            if (form.type === 'simple') {
                if (!form.question_id) errors.push('Pregunta (en requisito simple)')
                if (!form.operator) errors.push('Operador (en requisito simple)')
            }

            if (form.type === 'group') {
                if (!form.rules || form.rules.length === 0) {
                    errors.push('Añade al menos una regla o grupo anidado')
                } else {
                    form.rules.forEach((r, idx) =>
                        collectRuleErrors(r, `Regla/Grupo #${idx + 1}`, errors),
                    )
                }
            }

            if (errors.length) {
                const list = errors.map((e) => `• ${e}`).join('\n')
                alert(`Por favor completa los campos requeridos:\n\n${list}`)
                return false
            }
            return true
        }

        const collectRuleErrors = (rule, prefix, errors) => {
            if (!rule) return
            if (rule.type === 'group') {
                if (!rule.group_logic) errors.push(`${prefix}: lógica del grupo (AND/OR)`)
                if (!rule.rules || rule.rules.length === 0) {
                    errors.push(`${prefix}: debe contener al menos una regla`)
                } else {
                    rule.rules.forEach((child, i) =>
                        collectRuleErrors(child, `${prefix} > Sub-${i + 1}`, errors),
                    )
                }
                return
            }
            if (!rule.question_id) errors.push(`${prefix}: pregunta`)
            if (!rule.operator) errors.push(`${prefix}: operador`)
            if (!['exists', 'not_exists'].includes(rule.operator)) {
                if (rule.value === undefined || rule.value === null || rule.value === '') {
                    errors.push(`${prefix}: valor`)
                }
                if (
                    rule.operator === 'between' &&
                    (rule.value2 === undefined || rule.value2 === null || rule.value2 === '')
                ) {
                    errors.push(`${prefix}: segundo valor (between)`)
                }
                // Validaciones específicas de fecha
                if (
                    rule.value_type &&
                    ['age_minimum', 'age_maximum', 'age_range'].includes(rule.value_type)
                ) {
                    if (!rule.age_unit) errors.push(`${prefix}: unidad de edad (años/meses/días)`)
                    if (
                        rule.value_type === 'age_range' &&
                        (rule.value2 === undefined || rule.value2 === null || rule.value2 === '')
                    ) {
                        errors.push(`${prefix}: edad máxima (rango)`)
                    }
                }
            }
        }

        const cleanPreRequisitoData = (data) => {
            const cleaned = { ...data }

            if (!['age_minimum', 'age_maximum', 'age_range'].includes(cleaned.value_type)) {
                cleaned.age_unit = null
            }

            if (cleaned.operator !== 'between') {
                cleaned.value2 = null
            }

            if (cleaned.question_id) {
                const question = availableQuestions.value.find((q) => q.id == cleaned.question_id)
                if (question) {
                    if (
                        question.type === 'select' &&
                        question.options &&
                        Array.isArray(question.options)
                    ) {
                        const optionIndex = parseInt(cleaned.value)
                        if (!isNaN(optionIndex) && question.options[optionIndex]) {
                            cleaned.value = question.options[optionIndex]
                        }
                    } else if (
                        question.type === 'multiple' &&
                        question.options &&
                        Array.isArray(question.options)
                    ) {
                        let values = cleaned.value
                        if (typeof values === 'string') {
                            try {
                                values = JSON.parse(values)
                            } catch (e) {}
                        }
                        if (Array.isArray(values)) {
                            cleaned.value = JSON.stringify(
                                values.map((val) => {
                                    const optionIndex = parseInt(val)
                                    if (!isNaN(optionIndex) && question.options[optionIndex]) {
                                        return question.options[optionIndex]
                                    }
                                    return val
                                }),
                            )
                        }
                    }
                }
            }

            if (cleaned.type === 'group' && cleaned.rules && Array.isArray(cleaned.rules)) {
                cleaned.rules = cleaned.rules.map((rule) => {
                    if (rule.type === 'group') {
                        return cleanPreRequisitoData(rule)
                    } else {
                        return cleanRuleData(rule)
                    }
                })
            }

            return cleaned
        }

        const cleanRuleData = (rule) => {
            const cleaned = { ...rule }

            if (!['age_minimum', 'age_maximum', 'age_range'].includes(cleaned.value_type)) {
                cleaned.age_unit = null
            }

            if (cleaned.operator !== 'between') {
                cleaned.value2 = null
            }

            if (cleaned.question_id) {
                const question = availableQuestions.value.find((q) => q.id == cleaned.question_id)
                if (question) {
                    if (
                        question.type === 'select' &&
                        question.options &&
                        Array.isArray(question.options)
                    ) {
                        const optionIndex = parseInt(cleaned.value)
                        if (!isNaN(optionIndex) && question.options[optionIndex]) {
                            cleaned.value = question.options[optionIndex]
                        }
                    } else if (
                        question.type === 'multiple' &&
                        question.options &&
                        Array.isArray(question.options)
                    ) {
                        let values = cleaned.value
                        if (typeof values === 'string') {
                            try {
                                values = JSON.parse(values)
                            } catch (e) {}
                        }
                        if (Array.isArray(values)) {
                            cleaned.value = JSON.stringify(
                                values.map((val) => {
                                    const optionIndex = parseInt(val)
                                    if (!isNaN(optionIndex) && question.options[optionIndex]) {
                                        return question.options[optionIndex]
                                    }
                                    return val
                                }),
                            )
                        }
                    }
                }
            }

            return cleaned
        }

        const savePreRequisito = async () => {
            if (!validateForm()) {
                alert('Por favor completa los campos requeridos')
                return
            }

            saving.value = true
            try {
                const payload = cleanPreRequisitoData(form)

                if (editingPreRequisito.value?.id) {
                    // Actualizar pre-requisito existente
                    const index = preRequisitos.value.findIndex(
                        (p) => p.id === editingPreRequisito.value.id,
                    )
                    if (index !== -1) {
                        preRequisitos.value[index] = {
                            ...payload,
                            id: editingPreRequisito.value.id,
                        }
                    }
                } else {
                    // Crear nuevo pre-requisito
                    const newId = Date.now() // ID temporal para el wizard
                    preRequisitos.value.push({ ...payload, id: newId })
                }

                updatePreRequisitos(preRequisitos.value)
                closeModal()
            } catch (e) {
                console.error('Error guardando pre-requisito:', e)
                alert('Error al guardar el pre-requisito')
            } finally {
                saving.value = false
            }
        }

        const deletePreRequisito = async (pre, index) => {
            if (!confirm('¿Estás seguro de que quieres eliminar este pre-requisito?')) return

            try {
                savingIds.value.add(pre.id)
                preRequisitos.value.splice(index, 1)
                updatePreRequisitos(preRequisitos.value)
            } catch (e) {
                console.error('Error eliminando pre-requisito:', e)
                alert('Error al eliminar el pre-requisito')
            } finally {
                savingIds.value.delete(pre.id)
            }
        }

        const closeModal = () => {
            showCreateModal.value = false
            editingPreRequisito.value = null
            resetForm()
            clearQuestionSelection()
        }

        const reloadAll = async () => {
            await loadQuestions()
        }

        onMounted(() => {
            loadQuestions().then(() => {
                preloadQuestionsFromPreRequisitos()
                setTimeout(() => {
                    preloadQuestionsFromPreRequisitos()
                }, 100)
            })

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.relative')) {
                    showQuestionDropdown.value = false
                }
            })
        })

        watch(
            () => preRequisitos.value,
            (val) => {
                console.log('Watch preRequisitos: len=', Array.isArray(val) ? val.length : val)
                preloadQuestionsFromPreRequisitos()
            },
            { deep: true },
        )

        const preloadQuestionsFromPreRequisitos = async () => {
            try {
                const ids = new Set()
                const visit = (node) => {
                    if (!node) return
                    if (Array.isArray(node)) {
                        node.forEach(visit)
                        return
                    }
                    if (node.type === 'group' && Array.isArray(node.rules)) {
                        node.rules.forEach(visit)
                    } else if (node.question_id) {
                        const exists = availableQuestions.value.find(
                            (q) => q.id == node.question_id,
                        )
                        if (!exists) ids.add(String(node.question_id))
                    }
                }
                visit(preRequisitos.value)
                if (ids.size === 0) {
                    return
                }
                const loads = Array.from(ids).map(async (id) => {
                    try {
                        await loadSpecificQuestion(id)
                        const q = availableQuestions.value.find((q) => q.id == id)
                    } catch (e) {
                        console.warn('Preload: error cargando question_id', id, e)
                    }
                })
                await Promise.all(loads)
            } catch (e) {
                console.warn('Preload questions failed:', e)
            }
        }

        return {
            // state
            loading,
            preRequisitos,
            availableQuestions,
            showCreateModal,
            editingPreRequisito,
            saving,
            savingIds,
            form,
            inputValue,
            // question search
            questionSearch,
            questionSearchResults,
            questionSearching,
            showQuestionDropdown,
            selectedQuestion,
            currentQuestion,
            // constants/helpers
            typeOptions,
            targetTypeOptions,
            convivienteTypeOptions,
            valueTypeOptions,
            ageUnitOptions,
            operatorOptions,
            questionTypes,
            getTargetTypeText,
            getQuestionText,
            getGroupRuleQuestionText,
            getTypeText,
            getOperatorText,
            getAgeUnitText,
            formatValue,
            // actions
            handleTypeChange,
            savePreRequisito,
            openCreateModal,
            openEditModal,
            deletePreRequisito,
            closeModal,
            reloadAll,
            updatePreRequisitos,
            // question search actions
            handleQuestionSearchInput,
            selectQuestion,
            clearQuestionSelection,
            loadSpecificQuestion,
        }
    },
}
</script>

<style scoped>
/* Estilos específicos del componente si es necesario */
</style>
