<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navbar fijo -->
        <nav class="fixed top-0 left-0 right-0 bg-white z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-center items-center h-20 px-2 relative">
                    <!-- Botón para abrir sidebar en mobile -->
                    <button
                        @click="toggleSidebar"
                        class="lg:hidden absolute left-4 p-2 text-gray-600 hover:text-gray-900"
                    >
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <img
                        :src="logoUrl"
                        alt="TTF"
                        class="h-16 w-auto max-w-full"
                        @error="onLogoError"
                        v-show="!logoError"
                    />
                    <div
                        v-show="logoError"
                        class="h-16 w-full max-w-md bg-gradient-to-r from-blue-600 to-purple-600 rounded flex items-center justify-center"
                    >
                        <span class="text-white font-bold text-2xl">TTF</span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Contenido principal -->
        <div class="pt-20 flex flex-col lg:flex-row">
            <!-- Overlay para mobile -->
            <div
                v-if="showSidebar && isMobile"
                @click="toggleSidebar"
                class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
            ></div>

            <!-- Sidebar -->
            <div
                :class="[
                    'fixed lg:sticky lg:top-20 w-80 bg-white h-full lg:h-[calc(100vh-5rem)] overflow-y-auto z-50 transition-transform duration-300 ease-in-out',
                    isMobile
                        ? showSidebar
                            ? 'translate-x-0'
                            : '-translate-x-full'
                        : 'translate-x-0',
                ]"
            >
                <div class="p-4 lg:p-6">
                    <h2 class="text-base lg:text-lg font-semibold text-gray-900 mb-4 lg:mb-6">
                        <i class="fas fa-list-ul mr-2 text-blue-600"></i>
                        Secciones
                    </h2>

                    <!-- Secciones del solicitante -->
                    <div v-if="onboarderData?.sections?.length > 0" class="mb-6 lg:mb-8">
                        <h3
                            class="text-xs lg:text-sm font-medium text-gray-500 uppercase tracking-wide mb-2 lg:mb-3"
                        >
                            Solicitante
                        </h3>
                        <div class="space-y-1 lg:space-y-2">
                            <button
                                v-for="(section, index) in onboarderData.sections"
                                :key="`solicitante-${index}`"
                                @click="selectSection('solicitante', index, null, null, 'sidebar')"
                                :disabled="isSolicitanteSectionBlocked(index)"
                                :class="[
                                    'w-full text-left px-3 lg:px-4 py-2 lg:py-3 rounded-lg transition-all duration-200 flex items-center justify-between group text-sm lg:text-base relative overflow-hidden',
                                    currentSection.type === 'solicitante' &&
                                    currentSection.index === index
                                        ? 'bg-blue-50 border-2 border-blue-200 text-blue-700'
                                        : isSolicitanteSectionBlocked(index)
                                          ? 'bg-gray-100 text-gray-400 cursor-not-allowed opacity-60'
                                          : getSolicitanteSectionProgress(index) > 0
                                            ? 'bg-gray-50 text-gray-700'
                                            : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900',
                                ]"
                            >
                                <div
                                    v-if="
                                        !isSolicitanteSectionBlocked(index) &&
                                        getSolicitanteSectionProgress(index) > 0
                                    "
                                    class="progress-bar"
                                    :style="{
                                        width: `${getSolicitanteSectionProgress(index)}%`,
                                    }"
                                ></div>

                                <div class="flex items-center relative z-10">
                                    <i
                                        :class="[
                                            'fas mr-3 text-sm',
                                            section.is_skippeable ? 'fa-forward' : 'fa-user',
                                            currentSection.type === 'solicitante' &&
                                            currentSection.index === index
                                                ? 'text-blue-600'
                                                : isSolicitanteSectionBlocked(index)
                                                  ? 'text-gray-400'
                                                  : 'text-gray-400',
                                        ]"
                                    ></i>
                                    <div>
                                        <div class="font-medium">
                                            {{ section.name }}
                                        </div>
                                        <div
                                            v-if="section.description"
                                            class="text-xs text-gray-500 mt-1"
                                        >
                                            {{ section.description }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 relative z-10">
                                    <span
                                        v-if="
                                            section.is_skippeable &&
                                            !isSolicitanteSectionBlocked(index)
                                        "
                                        class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full"
                                    >
                                        Skippeable
                                    </span>
                                    <i
                                        v-if="isSolicitanteSectionBlocked(index)"
                                        class="fas fa-lock text-red-500 text-sm"
                                    ></i>
                                    <i
                                        :class="[
                                            'fas text-xs',
                                            currentSection.type === 'solicitante' &&
                                            currentSection.index === index
                                                ? 'fa-chevron-right text-blue-600'
                                                : isSolicitanteSectionBlocked(index)
                                                  ? 'fa-chevron-right text-gray-400'
                                                  : 'fa-chevron-right text-gray-400',
                                        ]"
                                    ></i>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Sección de Convivientes -->
                    <div v-if="(onboarderData?.conviviente_types?.length || 0) > 0" class="mb-8">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">
                            Convivientes
                        </h3>

                        <!-- Botón para añadir conviviente -->
                        <button
                            @click="showAddConvivienteModal = true"
                            :disabled="!isAllSolicitanteSectionsComplete()"
                            :class="[
                                'w-full px-4 py-3 rounded-lg transition-colors mb-4 flex items-center justify-center',
                                isAllSolicitanteSectionsComplete()
                                    ? 'bg-green-600 hover:bg-green-700 text-white'
                                    : 'bg-gray-300 text-gray-500 cursor-not-allowed',
                            ]"
                        >
                            <i class="fas fa-plus mr-2"></i>
                            Añadir conviviente
                        </button>

                        <div
                            v-if="!isAllSolicitanteSectionsComplete()"
                            class="text-xs text-gray-500 text-center mb-4"
                        >
                            <i class="fas fa-info-circle mr-1"></i>
                            Completa todas las secciones de solicitante para añadir convivientes
                        </div>

                        <!-- Lista de convivientes añadidos -->
                        <div v-if="addedConvivientes.length > 0" class="space-y-3">
                            <div
                                v-for="(conviviente, convivienteIndex) in addedConvivientes"
                                :key="`added-conviviente-${convivienteIndex}`"
                                class="border border-gray-200 rounded-lg p-3"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <i
                                            :class="[conviviente.type.icon, 'text-orange-600 mr-2']"
                                        ></i>
                                        <span class="font-medium text-gray-900">
                                            {{ conviviente.type.name }} #{{ convivienteIndex + 1 }}
                                        </span>
                                    </div>
                                    <button
                                        @click="removeConviviente(convivienteIndex)"
                                        class="text-red-500 hover:text-red-700 text-sm"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <!-- Secciones del conviviente -->
                                <div class="space-y-1">
                                    <button
                                        v-for="(section, sectionIndex) in conviviente.type.sections"
                                        v-show="
                                            shouldShowConvivienteSection(
                                                convivienteIndex,
                                                sectionIndex,
                                            )
                                        "
                                        :key="`conviviente-${convivienteIndex}-${sectionIndex}`"
                                        @click="
                                            selectSection(
                                                'conviviente',
                                                null,
                                                convivienteIndex,
                                                sectionIndex,
                                                'sidebar',
                                            )
                                        "
                                        :class="[
                                            'w-full text-left px-3 py-2 rounded-md transition-all duration-200 flex items-center justify-between group text-sm',
                                            currentSection.type === 'conviviente' &&
                                            currentSection.convivienteIndex === convivienteIndex &&
                                            currentSection.sectionIndex === sectionIndex
                                                ? 'bg-orange-50 border border-orange-200 text-orange-700'
                                                : 'bg-gray-50 hover:bg-gray-100 text-gray-600 hover:text-gray-800',
                                        ]"
                                    >
                                        <div class="flex items-center">
                                            <i
                                                :class="[
                                                    'fas mr-2 text-xs',
                                                    section.is_skippeable
                                                        ? 'fa-forward'
                                                        : 'fa-home',
                                                    currentSection.type === 'conviviente' &&
                                                    currentSection.convivienteIndex ===
                                                        convivienteIndex &&
                                                    currentSection.sectionIndex === sectionIndex
                                                        ? 'text-orange-600'
                                                        : 'text-gray-400',
                                                ]"
                                            ></i>
                                            <span>{{ section.name }}</span>
                                        </div>
                                        <div class="flex items-center space-x-1">
                                            <i
                                                :class="[
                                                    'fas text-xs',
                                                    currentSection.type === 'conviviente' &&
                                                    currentSection.convivienteIndex ===
                                                        convivienteIndex &&
                                                    currentSection.sectionIndex === sectionIndex
                                                        ? 'fa-chevron-right text-orange-600'
                                                        : 'fa-chevron-right text-gray-400',
                                                ]"
                                            ></i>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Mensaje cuando no hay convivientes -->
                        <div v-else class="text-center py-6 text-gray-500">
                            <i class="fas fa-users text-2xl mb-2"></i>
                            <p class="text-sm">No hay convivientes añadidos</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="flex-1 p-4 lg:p-8 lg:ml-0" :class="{ 'ml-0': !isMobile || !showSidebar }">
                <div v-if="loading" class="flex items-center justify-center h-64">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                        <p class="text-gray-600">Cargando onboarder...</p>
                    </div>
                </div>

                <div v-else-if="!onboarderData" class="flex items-center justify-center h-64">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-4xl text-yellow-500 mb-4"></i>
                        <p class="text-gray-600">No se encontró el onboarder</p>
                    </div>
                </div>

                <div v-else>
                    <!-- Header de la sección actual -->
                    <div class="mb-6 lg:mb-8">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                            <div class="mb-4 lg:mb-0">
                                <div class="flex items-center mb-2">
                                    <div
                                        v-if="currentSection.type === 'conviviente'"
                                        class="flex items-center mr-3"
                                    >
                                        <i class="fas fa-users text-orange-600 mr-2"></i>
                                        <span
                                            class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-medium"
                                        >
                                            Conviviente #{{
                                                (currentSection.convivienteIndex || 0) + 1
                                            }}
                                        </span>
                                    </div>
                                    <div v-else class="flex items-center mr-3">
                                        <i class="fas fa-user text-blue-600 mr-2"></i>
                                        <span
                                            class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium"
                                        >
                                            Solicitante
                                        </span>
                                    </div>
                                </div>
                                <h1 class="text-xl lg:text-2xl font-bold text-gray-900">
                                    {{ currentSectionData?.name || 'Selecciona una sección' }}
                                </h1>
                                <p
                                    v-if="currentSectionData?.description"
                                    class="text-gray-600 mt-1 text-sm lg:text-base"
                                >
                                    {{ currentSectionData.description }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-2 lg:space-x-4">
                                <span
                                    v-if="currentSectionData?.is_skippeable"
                                    class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium"
                                >
                                    <i class="fas fa-forward mr-1"></i>
                                    Sección Skippeable
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Preguntas de la sección -->
                    <div
                        v-if="currentSectionData?.questions?.length > 0"
                        class="space-y-4 lg:space-y-6"
                    >
                        <div
                            v-for="(question, questionIndex) in currentSectionData.questions.filter(
                                (q) => (q.screen ?? 0) === currentScreenIndex,
                            )"
                            v-show="shouldShowQuestion(question)"
                            :key="`question-${questionIndex}`"
                            class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6"
                        >
                            <div
                                class="flex flex-col lg:flex-row lg:items-start lg:justify-between mb-4"
                            >
                                <div class="flex-1 mb-2 lg:mb-0">
                                    <h3 class="text-base lg:text-lg font-medium text-gray-900 mb-2">
                                        {{ getQuestionText(question) || 'Pregunta' }}
                                    </h3>
                                    <p
                                        v-if="getQuestionSubText(question)"
                                        class="text-sm text-gray-600 mb-3"
                                    >
                                        {{ getQuestionSubText(question) }}
                                    </p>
                                    <div
                                        class="flex flex-wrap items-center gap-2 lg:gap-4 text-xs lg:text-sm text-gray-500"
                                    >
                                        <span class="flex items-center">
                                            <i class="fas fa-tag mr-1"></i>
                                            {{ getQuestionTypeLabel(question.question?.type) }}
                                        </span>
                                        <span
                                            v-if="question.is_builder"
                                            class="flex items-center text-blue-600"
                                        >
                                            <i class="fas fa-code mr-1"></i>
                                            Builder
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span
                                        v-if="question.required_condition"
                                        class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs"
                                    >
                                        Obligatoria
                                    </span>
                                    <span
                                        v-if="question.optional_condition"
                                        class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs"
                                    >
                                        Opcional
                                    </span>
                                </div>
                            </div>

                            <!-- Campo de respuesta -->
                            <div class="mt-4" :data-question-id="question.question?.id">
                                <component
                                    :is="getQuestionComponent(question.question)"
                                    :question="question"
                                    :questionIndex="questionIndex"
                                    :value="
                                        question.question?.type === 'multiple'
                                            ? getAnswerValue(
                                                  question.question?.id,
                                                  currentSection.type,
                                                  currentSection.convivienteIndex,
                                              )
                                            : getLocationSelectValue(
                                                  question.question?.id,
                                                  question.question?.slug,
                                                  currentSection.type,
                                                  currentSection.convivienteIndex,
                                              )
                                    "
                                    :answers="answers"
                                    :options="getQuestionOptions(question)"
                                    :selectedCcaa="
                                        getAnswerBySlug(
                                            'comunidad_autonoma',
                                            currentSection.type,
                                            currentSection.convivienteIndex,
                                        )
                                    "
                                    :selectedProvincia="
                                        getAnswerBySlug(
                                            'provincia',
                                            currentSection.type,
                                            currentSection.convivienteIndex,
                                        )
                                    "
                                    :blocked="!!isQuestionBlocked(question)"
                                    :verifyingMunicipio="
                                        verifyingMunicipioForQuestion === question.question?.id
                                    "
                                    @update="updateAnswer"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="
                            currentSection.type === 'solicitante' && !isSolicitanteSectionComplete
                        "
                        class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg"
                    >
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                            <p class="text-yellow-800 text-sm">
                                Completa todas las preguntas de esta sección para continuar.
                            </p>
                        </div>
                    </div>

                    <!-- Botones de navegación -->
                    <div
                        v-if="currentSectionData"
                        class="mt-6 lg:mt-8 flex flex-col sm:flex-row justify-between gap-3"
                    >
                        <button
                            @click="previousSection"
                            :disabled="!canGoPrevious"
                            class="bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700 px-4 lg:px-6 py-3 rounded-lg transition-colors text-sm lg:text-base"
                        >
                            <i class="fas fa-arrow-left mr-2"></i>
                            Anterior
                        </button>

                        <button
                            @click="nextSection"
                            :disabled="
                                !canGoNext ||
                                (currentSection.type === 'solicitante' &&
                                    !isSolicitanteSectionComplete) ||
                                (currentSection.type === 'conviviente' &&
                                    !isCurrentConvivienteSectionComplete())
                            "
                            class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-4 lg:px-6 py-3 rounded-lg transition-colors text-sm lg:text-base"
                        >
                            Siguiente
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para añadir conviviente -->
        <div
            v-if="showAddConvivienteModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Añadir Conviviente</h3>
                        <button
                            @click="showAddConvivienteModal = false"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <p class="text-gray-600 mb-4">
                        Selecciona el tipo de conviviente que quieres añadir:
                    </p>

                    <div class="space-y-3">
                        <button
                            v-for="(
                                convivienteType, typeIndex
                            ) in onboarderData?.conviviente_types || []"
                            :key="`type-${typeIndex}`"
                            @click="addConviviente(convivienteType)"
                            class="w-full text-left p-4 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50 transition-colors"
                        >
                            <div class="flex items-center">
                                <i
                                    :class="[convivienteType.icon, 'text-orange-600 mr-3 text-lg']"
                                ></i>
                                <div>
                                    <div class="font-medium text-gray-900">
                                        {{ convivienteType.name }}
                                    </div>
                                    <div
                                        v-if="convivienteType.description"
                                        class="text-sm text-gray-500 mt-1"
                                    >
                                        {{ convivienteType.description }}
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            @click="showAddConvivienteModal = false"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
                        >
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="showConvivientesHub"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
        >
            <div
                class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
            >
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">
                            <i class="fas fa-users text-blue-600 mr-2"></i>
                            ¿Finalizar cuestionario?
                        </h2>
                        <button
                            @click="closeConvivientesHub"
                            class="text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">
                            <i class="fas fa-user text-blue-600 mr-2"></i>
                            Datos del solicitante completados
                        </h3>
                        <p class="text-blue-700">
                            Has completado todas las secciones del solicitante. Ahora puedes agregar
                            convivientes o finalizar el cuestionario.
                        </p>
                    </div>

                    <div v-if="addedConvivientes.length > 0" class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-list text-gray-600 mr-2"></i>
                            Convivientes agregados ({{ addedConvivientes.length }})
                        </h3>
                        <div class="grid gap-4">
                            <div
                                v-for="(conviviente, index) in addedConvivientes"
                                :key="index"
                                class="bg-gray-50 border border-gray-200 rounded-lg p-4 flex items-center justify-between"
                            >
                                <div class="flex items-center">
                                    <i
                                        :class="conviviente.type.icon"
                                        class="text-2xl text-blue-600 mr-3"
                                    ></i>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">
                                            {{ conviviente.type.name }}
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            {{ conviviente.type.sections?.length || 0 }}
                                            secciones
                                        </p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button
                                        @click="editConviviente(index)"
                                        class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
                                    >
                                        <i class="fas fa-edit mr-1"></i>
                                        Editar
                                    </button>
                                    <button
                                        @click="removeConvivienteFromHub(index)"
                                        class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors"
                                    >
                                        <i class="fas fa-trash mr-1"></i>
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8 mb-6">
                        <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-600 mb-2">
                            No hay convivientes agregados
                        </h3>
                        <p class="text-gray-500">
                            Puedes agregar convivientes o finalizar el cuestionario directamente.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 justify-between">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button
                                @click="openAddConvivienteModal"
                                class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center"
                            >
                                <i class="fas fa-plus mr-2"></i>
                                Agregar conviviente
                            </button>
                        </div>
                        <button
                            @click="showFinishConfirmation()"
                            class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center justify-center"
                        >
                            <i class="fas fa-check mr-2"></i>
                            Finalizar cuestionario
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="showFinishModal"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
        >
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mr-3"></i>
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar Finalización</h3>
                    </div>

                    <div class="mb-6">
                        <p class="text-gray-700 mb-4">
                            ¿Estás seguro de que quieres finalizar el cuestionario?
                        </p>

                        <div
                            v-if="!isAllSectionsComplete()"
                            class="bg-red-50 border border-red-200 rounded p-3 mb-4"
                        >
                            <p class="text-red-800 text-sm">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                No puedes finalizar el cuestionario hasta completar todas las
                                secciones obligatorias.
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeFinishModal"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="finishOnboarder"
                            :disabled="isSaving || !isAllSectionsComplete()"
                            :class="[
                                'px-4 py-2 rounded transition-colors',
                                isSaving || !isAllSectionsComplete()
                                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                    : 'bg-purple-600 text-white hover:bg-purple-700',
                            ]"
                        >
                            <i v-if="isSaving" class="fas fa-spinner fa-spin mr-1"></i>
                            <i v-else class="fas fa-check mr-1"></i>
                            {{ isSaving ? 'Guardando...' : 'Finalizar' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="isSaving"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center"
        >
            <div class="bg-white rounded-lg shadow-xl p-8 flex flex-col items-center">
                <i class="fas fa-spinner fa-spin text-purple-600 text-3xl mb-4"></i>
                <p class="text-gray-700 text-lg">Guardando respuestas...</p>
            </div>
        </div>

        <div class="fixed top-4 right-4 z-50 space-y-2">
            <transition-group name="notification" tag="div">
                <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    :class="[
                        'w-96 max-w-md bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden',
                        notification.type === 'warning' ? 'border-l-4 border-yellow-400' : '',
                        notification.type === 'success' ? 'border-l-4 border-green-400' : '',
                        notification.type === 'error' ? 'border-l-4 border-red-400' : '',
                        notification.type === 'info' ? 'border-l-4 border-blue-400' : '',
                    ]"
                >
                    <div class="p-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i
                                    :class="[
                                        'h-4 w-4',
                                        notification.type === 'warning'
                                            ? 'fas fa-exclamation-triangle text-yellow-400'
                                            : '',
                                        notification.type === 'success'
                                            ? 'fas fa-check-circle text-green-400'
                                            : '',
                                        notification.type === 'error'
                                            ? 'fas fa-times-circle text-red-400'
                                            : '',
                                        notification.type === 'info'
                                            ? 'fas fa-info-circle text-blue-400'
                                            : '',
                                    ]"
                                ></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900 leading-tight">
                                    {{ notification.message }}
                                </p>
                            </div>
                            <div class="ml-3 flex-shrink-0">
                                <button
                                    @click="removeNotification(notification.id)"
                                    class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    <span class="sr-only">Cerrar</span>
                                    <i class="fas fa-times h-4 w-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition-group>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import axios from 'axios'
import TextQuestion from './onboarder/TextQuestion.vue'
import BooleanQuestion from './onboarder/BooleanQuestion.vue'
import SelectQuestion from './onboarder/SelectQuestion.vue'
import MultipleQuestion from './onboarder/MultipleQuestion.vue'
import LocationSelectQuestion from './onboarder/LocationSelectQuestion.vue'
import DateQuestion from './onboarder/DateQuestion.vue'
import NumberQuestion from './onboarder/NumberQuestion.vue'
import BuilderQuestion from './onboarder/BuilderQuestion.vue'
import { useQuestionVisibility } from '../composables/index.js'

const {
    onboarderData,
    answers,
    userAnswers,
    addedConvivientes,
    currentSection,
    shouldShowQuestion,
    isDependentQuestionVisible,
    isQuestionHidden,
    isConvivienteSectionSkipped,
    normalizeCondition,
    evaluateCondition,
    getAnswerByQuestionId,
    checkIfUserIsFromBankflip,
    isDateValue,
    calculateAge,
    getVisibleAnswers,
    shouldShowQuestionForConviviente,
} = useQuestionVisibility()

// Reactive data
const loading = ref(true)
const showAddConvivienteModal = ref(false)
const logoUrl = ref('/favicon.ico')
const logoError = ref(false)
const showSidebar = ref(false)
const isMobile = ref(false)
const showConvivientesHub = ref(false)
const showFinishModal = ref(false)
const isSaving = ref(false)
const notifications = ref([])
const notificationId = ref(0)
const onboarderStartTime = ref(null)
const sectionStartTime = ref(null)
const currentOnboarderId = ref(null)
const sectionMetrics = ref({})
const convivienteMetrics = ref({})
const abandonmentTracked = ref(false)
const verifyingMunicipioForQuestion = ref(null)

// Computed
const currentSectionData = computed(() => {
    if (!onboarderData.value || !currentSection.value.type) return null

    if (currentSection.value.type === 'solicitante') {
        return onboarderData.value.sections?.[currentSection.value.index]
    } else if (currentSection.value.type === 'conviviente') {
        const conviviente = addedConvivientes.value[currentSection.value.convivienteIndex]
        console.log('Current conviviente:', conviviente)
        console.log('Section index:', currentSection.value.sectionIndex)
        console.log(
            'Section data:',
            conviviente?.type?.sections?.[currentSection.value.sectionIndex],
        )
        return conviviente?.type?.sections?.[currentSection.value.sectionIndex]
    }

    return null
})

const canGoPrevious = computed(() => {
    if (!currentSection.value.type) return false

    if (currentSection.value.type === 'solicitante') {
        const section = onboarderData.value?.sections?.[currentSection.value.index]
        if (section && currentScreenIndex.value > 0) {
            return true
        }
        return currentSection.value.index > 0
    } else if (currentSection.value.type === 'conviviente') {
        const convivienteIndex = currentSection.value.convivienteIndex
        const currentSectionIndex = currentSection.value.sectionIndex
        const conviviente = addedConvivientes.value[convivienteIndex]
        const section = conviviente?.type?.sections?.[currentSectionIndex]
        if (section && currentScreenIndex.value > 0) {
            return true
        }
        const previousAvailableSection = findPreviousAvailableConvivienteSection(
            convivienteIndex,
            currentSectionIndex,
        )
        if (previousAvailableSection !== null) {
            return true
        }

        if (convivienteIndex > 0) {
            const lastSectionIndex = findLastAvailableConvivienteSection(convivienteIndex - 1)
            if (lastSectionIndex !== null) {
                return true
            }
        }

        return onboarderData.value?.sections?.length > 0
    }

    return false
})

const currentScreenIndex = ref(0)

const getSectionQuestionsByScreen = (section, screenIndex) => {
    if (!section?.questions) return []
    return section.questions.filter((q) => {
        const qScreen = q.screen !== undefined && q.screen !== null ? Number(q.screen) : 0
        return qScreen === screenIndex
    })
}

const getSectionTotalScreens = (section) => {
    if (!section?.questions || section.questions.length === 0) return 1
    let maxScreen = 0
    section.questions.forEach((q) => {
        const qScreen = q.screen !== undefined && q.screen !== null ? Number(q.screen) : 0
        if (qScreen > maxScreen) maxScreen = qScreen
    })
    return maxScreen + 1
}

const canGoNext = computed(() => {
    if (!currentSection.value.type) return false

    if (currentSection.value.type === 'solicitante') {
        if (!isSolicitanteSectionComplete.value) return false
        const section = onboarderData.value?.sections?.[currentSection.value.index]
        const totalScreens = getSectionTotalScreens(section)
        if (currentScreenIndex.value < totalScreens - 1) return true
        if (currentSection.value.index >= (onboarderData.value?.sections?.length || 0) - 1) {
            return (onboarderData.value?.conviviente_types?.length || 0) > 0
        }
        return true
    } else if (currentSection.value.type === 'conviviente') {
        const conviviente = addedConvivientes.value[currentSection.value.convivienteIndex]
        if (!conviviente) return false
        const section = conviviente.type.sections?.[currentSection.value.sectionIndex]
        const totalScreens = getSectionTotalScreens(section)
        if (currentScreenIndex.value < totalScreens - 1) return true
        if (currentSection.value.sectionIndex >= (conviviente.type.sections?.length || 0) - 1) {
            return true
        }
        return true
    }

    return false
})

const isLastSolicitanteSection = computed(() => {
    if (!currentSection.value || currentSection.value.type !== 'solicitante') return false
    return currentSection.value.index === (onboarderData.value?.sections?.length || 0) - 1
})

const isSolicitanteSectionComplete = computed(() => {
    if (currentSection.value.type !== 'solicitante') return true
    if (!currentSectionData.value?.questions) return true

    const questionsThisScreen = getSectionQuestionsByScreen(
        currentSectionData.value,
        currentScreenIndex.value,
    )

    const visibleQuestions = questionsThisScreen.filter((question) => shouldShowQuestion(question))

    return visibleQuestions.every((question) => {
        const questionId = question.question?.id
        if (!questionId) return true

        if (!isQuestionRequired(question)) {
            return true
        }

        const answer = getAnswerValue(questionId, 'solicitante', null)
        return answer !== undefined && answer !== null && answer !== ''
    })
})

const sectionProgressCache = ref({})

const getSolicitanteSectionProgress = (sectionIndex) => {
    if (!onboarderData.value?.sections?.[sectionIndex]) return 0

    const cacheKey = `section_${sectionIndex}`
    if (sectionProgressCache.value[cacheKey] !== undefined) {
        return sectionProgressCache.value[cacheKey]
    }

    const section = onboarderData.value.sections[sectionIndex]
    if (!section.questions) return 0

    const questionsThisScreen = getSectionQuestionsByScreen(section, currentScreenIndex.value)

    const visibleQuestions = questionsThisScreen.filter((question) => {
        if (isQuestionHidden(question)) return false
        if (!question.condition) return true
        return evaluateCondition(question.condition, getAnswerByQuestionId)
    })

    if (visibleQuestions.length === 0) {
        sectionProgressCache.value[cacheKey] = 100
        return 100
    }

    let answeredQuestions = 0
    visibleQuestions.forEach((question) => {
        const questionId = question.question?.id
        if (!questionId) return

        const answer = getAnswerValue(questionId, 'solicitante', null)
        if (answer !== undefined && answer !== null && answer !== '') {
            answeredQuestions++
        }
    })

    const progress = Math.round((answeredQuestions / visibleQuestions.length) * 100)
    sectionProgressCache.value[cacheKey] = progress
    return progress
}

const isSolicitanteSectionBlocked = (sectionIndex) => {
    if (sectionIndex === 0) return false

    for (let i = 0; i < sectionIndex; i++) {
        if (!isSolicitanteSectionCompleteByIndex(i)) {
            return true
        }
    }

    return false
}

// Methods
const loadOnboarder = async () => {
    try {
        loading.value = true
        const response = await axios.get('/api/onboarders/completed')

        if (response.data.success) {
            onboarderData.value = response.data.data
            currentOnboarderId.value = onboarderData.value.id
            if (onboarderData.value?.conviviente_types) {
                onboarderData.value.conviviente_types.forEach((type, index) => {
                    if (type.sections) {
                        type.sections.forEach((section, sIndex) => {
                            console.log(`  Section ${sIndex}:`, section)
                            console.log(`  Questions:`, section.questions)
                        })
                    }
                })
            }

            await loadUserAnswers()
            startOnboarderTracking()

            // Seleccionar la primera sección por defecto
            if (onboarderData.value.sections?.length > 0) {
                selectSection('solicitante', 0, null, null, 'initial')
            }
        }
    } catch (error) {
        console.error('Error loading onboarder:', error)
    } finally {
        loading.value = false
    }
}

const loadUserAnswers = async () => {
    try {
        const response = await axios.get('/user-answers')
        userAnswers.value = response.data.data || {}
    } catch (error) {
        console.error('Error loading user answers:', error)
    }
}

const getQuestionTypeLabel = (type) => {
    const labels = {
        text: 'Texto',
        string: 'Texto',
        boolean: 'Sí/No',
        select: 'Selección',
        multiple: 'Múltiple',
        date: 'Fecha',
        number: 'Número',
        integer: 'Número',
        builder: 'Builder',
    }
    return labels[type] || 'Desconocido'
}

const getQuestionText = (question) => {
    if (!question.question) return 'Pregunta'

    let text = ''

    if (currentSection.value.type === 'conviviente' && question.question.text_conviviente) {
        text = question.question.text_conviviente
    } else {
        text = question.question.text
    }

    return interpolateVariables(text, question)
}

const getQuestionSubText = (question) => {
    if (!question.question) return ''

    let subText = ''

    if (currentSection.value.type === 'conviviente' && question.question.sub_text_conviviente) {
        subText = question.question.sub_text_conviviente
    } else {
        subText = question.question.sub_text
    }

    if (!subText || subText.trim() === '') return ''

    return interpolateVariables(subText, question)
}

const interpolateVariables = (text, currentQuestion) => {
    if (!text || typeof text !== 'string') return text

    const variableRegex = /\{(\w+)\}/g
    const matches = text.match(variableRegex)

    if (!matches) return text

    let interpolatedText = text

    matches.forEach((match) => {
        const variableName = match.replace(/[{}]/g, '')
        const replacement = getVariableValue(variableName, currentQuestion)

        if (replacement === '') {
            const beforeMatch = interpolatedText.substring(0, interpolatedText.indexOf(match))
            const afterMatch = interpolatedText.substring(
                interpolatedText.indexOf(match) + match.length,
            )

            let cleanBefore = beforeMatch
            let cleanAfter = afterMatch

            if (
                cleanBefore.endsWith(' ') &&
                (cleanAfter.startsWith(' ') || /^[.,!?;:]/.test(cleanAfter))
            ) {
                cleanBefore = cleanBefore.slice(0, -1)
            }

            interpolatedText = cleanBefore + cleanAfter
        } else {
            interpolatedText = interpolatedText.replace(match, replacement)
        }
    })

    interpolatedText = interpolatedText.replace(/\s+/g, ' ')

    return interpolatedText
}

const getVariableValue = (variableName, currentQuestion) => {
    const variableMapping = {
        name: ['nombre', 'nombre_completo', 'primer_nombre'],
        nombre: ['nombre', 'nombre_completo', 'primer_nombre'],
        apellido: ['apellido', 'apellidos'],
        edad: ['edad', 'fecha_nacimiento'],
        ciudad: ['ciudad', 'municipio', 'localidad'],
    }

    const possibleSlugs = variableMapping[variableName.toLowerCase()] || [
        variableName.toLowerCase(),
    ]

    if (currentSection.value.type === 'conviviente') {
        const conviviente = addedConvivientes.value[currentSection.value.convivienteIndex]
        if (!conviviente || !conviviente.type.sections) return ''

        for (
            let sectionIndex = 0;
            sectionIndex <= currentSection.value.sectionIndex;
            sectionIndex++
        ) {
            const section = conviviente.type.sections[sectionIndex]
            if (!section || !section.questions) continue

            const maxQuestionIndex = section.questions.length

            for (let questionIndex = 0; questionIndex < maxQuestionIndex; questionIndex++) {
                const question = section.questions[questionIndex]
                if (!question.question) continue

                if (question.question.slug && possibleSlugs.includes(question.question.slug)) {
                    const answer = getAnswerValue(
                        question.question.id,
                        'conviviente',
                        currentSection.value.convivienteIndex,
                    )
                    if (answer && answer.trim() !== '') {
                        return answer.trim()
                    }
                }

                if (question.question.text) {
                    const questionText = question.question.text.toLowerCase()
                    if (possibleSlugs.some((slug) => questionText.includes(slug))) {
                        const answer = getAnswerValue(
                            question.question.id,
                            'conviviente',
                            currentSection.value.convivienteIndex,
                        )
                        if (answer && answer.trim() !== '') {
                            return answer.trim()
                        }
                    }
                }
            }
        }
    } else {
        const currentSectionData = getCurrentSectionDataForInterpolation()
        if (!currentSectionData || !currentSectionData.questions) return ''

        const currentQuestionIndex = currentSectionData.questions.findIndex(
            (q) => q === currentQuestion,
        )
        if (currentQuestionIndex === -1) return ''

        for (let i = 0; i < currentQuestionIndex; i++) {
            const prevQuestion = currentSectionData.questions[i]
            if (!prevQuestion.question) continue

            if (prevQuestion.question.slug && possibleSlugs.includes(prevQuestion.question.slug)) {
                const answer = getAnswerValue(prevQuestion.question.id, 'solicitante', null)
                if (answer && answer.trim() !== '') {
                    return answer.trim()
                }
            }

            if (prevQuestion.question.text) {
                const questionText = prevQuestion.question.text.toLowerCase()
                if (possibleSlugs.some((slug) => questionText.includes(slug))) {
                    const answer = getAnswerValue(prevQuestion.question.id, 'solicitante', null)
                    if (answer && answer.trim() !== '') {
                        return answer.trim()
                    }
                }
            }
        }
    }

    return ''
}

const getCurrentSectionDataForInterpolation = () => {
    if (!onboarderData.value || !currentSection.value.type) return null

    if (currentSection.value.type === 'solicitante') {
        return onboarderData.value.sections?.[currentSection.value.index]
    } else if (currentSection.value.type === 'conviviente') {
        const conviviente = addedConvivientes.value[currentSection.value.convivienteIndex]
        return conviviente?.type?.sections?.[currentSection.value.sectionIndex]
    }

    return null
}

const getQuestionComponent = (question) => {
    if (
        question?.type === 'select' &&
        (question?.slug === 'comunidad_autonoma' ||
            question?.slug === 'provincia' ||
            question?.slug === 'municipio')
    ) {
        return LocationSelectQuestion
    }

    const components = {
        text: TextQuestion,
        string: TextQuestion,
        boolean: BooleanQuestion,
        select: SelectQuestion,
        multiple: MultipleQuestion,
        date: DateQuestion,
        number: NumberQuestion,
        integer: NumberQuestion,
        builder: BuilderQuestion,
    }
    return components[question?.type] || TextQuestion
}

const evaluateConditionalOptions = (conditionalOptions, question) => {
    if (
        !conditionalOptions ||
        !conditionalOptions.conditionalConfigs ||
        conditionalOptions.conditionalConfigs.length === 0
    ) {
        return conditionalOptions?.defaultOptions || []
    }

    for (const config of conditionalOptions.conditionalConfigs) {
        if (evaluateConditionalConfig(config)) {
            return config.options || []
        }
    }

    return conditionalOptions.defaultOptions || []
}

const evaluateConditionalConfig = (config) => {
    if (!config.dependsOnQuestionId || !config.conditionType || !config.expectedValue) {
        return false
    }

    const dependentAnswer = getAnswerByQuestionId(config.dependsOnQuestionId)

    if (dependentAnswer === null || dependentAnswer === undefined) {
        return false
    }

    let result = false
    switch (config.conditionType) {
        case 'equals':
            result = dependentAnswer === config.expectedValue
            break
        case 'not_equals':
            result = dependentAnswer !== config.expectedValue
            break
        case 'contains':
            result = String(dependentAnswer)
                .toLowerCase()
                .includes(String(config.expectedValue).toLowerCase())
            break
        case 'not_contains':
            result = !String(dependentAnswer)
                .toLowerCase()
                .includes(String(config.expectedValue).toLowerCase())
            break
        default:
            result = false
    }
    return result
}

const getQuestionOptions = (question) => {
    if (['comunidad_autonoma', 'provincia', 'municipio'].includes(question?.question?.slug)) {
        if (question?.conditional_options) {
            const conditionalOptions = question.conditional_options
            const applicableOptions = evaluateConditionalOptions(conditionalOptions, question)
            if (applicableOptions.length > 0) {
                return applicableOptions
            }
        }
        return question?.question?.options || []
    }

    if (question?.question?.type === 'select' || question?.question?.type === 'multiple') {
        if (
            question?.selected_options &&
            Array.isArray(question.selected_options) &&
            question.selected_options.length > 0
        ) {
            const allOptions = question?.question?.options || []
            if (Array.isArray(allOptions) && allOptions.length > 0) {
                return allOptions.filter((option) => {
                    const optionValue = option.value || option
                    return question.selected_options.includes(optionValue)
                })
            }
        }

        if (question?.conditional_options) {
            const conditionalOptions = question.conditional_options

            const applicableOptions = evaluateConditionalOptions(conditionalOptions, question)
            if (applicableOptions.length > 0) {
                return applicableOptions
            }
        }

        if (question?.options) {
            try {
                const parsedOptions =
                    typeof question.options === 'string'
                        ? JSON.parse(question.options)
                        : question.options

                if (Array.isArray(parsedOptions) && parsedOptions.length > 0) {
                    return parsedOptions
                }
            } catch (error) {
                console.error('Error parsing options:', error)
            }
        }

        return question?.question?.options || []
    }

    return []
}

const getAnswerValue = (questionId, sectionType = null, convivienteIndex = null) => {
    const key = getAnswerKey(questionId, sectionType, convivienteIndex)

    if (answers.value[key] !== undefined) {
        return answers.value[key]
    }

    if (userAnswers.value[questionId] && sectionType === 'solicitante') {
        let answerValue =
            userAnswers.value[questionId].formatted_answer || userAnswers.value[questionId].answer

        if (userAnswers.value[questionId].question_slug === 'genero') {
            if (answerValue === 'M') {
                answerValue = 'Mujer'
            } else if (answerValue === 'H') {
                answerValue = 'Hombre'
            }
        }

        // Traducción especial para estado civil
        if (userAnswers.value[questionId].question_slug === 'estado_civil') {
            if (answerValue === 'Soltero') {
                answerValue = 'Soltero/a'
            } else if (answerValue === 'Casado') {
                answerValue = 'Casado/a'
            } else if (answerValue === 'Viudo') {
                answerValue = 'Viudo/a'
            } else if (answerValue === 'Divorciado') {
                answerValue = 'Divorciado/a'
            }
        }

        return answerValue
    }

    return undefined
}

const getAnswerKey = (questionId, sectionType = null, convivienteIndex = null) => {
    if (sectionType === 'conviviente' && convivienteIndex !== null) {
        return `conviviente_${convivienteIndex}_${questionId}`
    }
    return `solicitante_${questionId}`
}

const getLocationSelectValue = (questionId, slug, sectionType = null, convivienteIndex = null) => {
    const value = getAnswerValue(questionId, sectionType, convivienteIndex)
    if (!value) return null

    if (['comunidad_autonoma', 'provincia', 'municipio'].includes(slug)) {
        return value
    }

    return value
}

const getAnswerBySlug = (slug, sectionType = null, convivienteIndex = null) => {
    if (!onboarderData.value) return null

    if (sectionType === 'solicitante' || sectionType === null) {
        for (const section of onboarderData.value.sections || []) {
            for (const question of section.questions || []) {
                if (question.question?.slug === slug) {
                    let answerValue = getAnswerValue(question.question.id, 'solicitante', null)

                    return answerValue || null
                }
            }
        }
    }

    if (sectionType === 'conviviente' && convivienteIndex !== null) {
        const conviviente = addedConvivientes.value[convivienteIndex]
        if (conviviente) {
            for (const section of conviviente.type.sections || []) {
                for (const question of section.questions || []) {
                    if (question.question?.slug === slug) {
                        let answerValue = getAnswerValue(
                            question.question.id,
                            'conviviente',
                            convivienteIndex,
                        )

                        if (slug === 'genero' && answerValue) {
                            if (answerValue === 'M') {
                                answerValue = 'Mujer'
                            } else if (answerValue === 'H') {
                                answerValue = 'Hombre'
                            }
                        }

                        if (slug === 'estado_civil' && answerValue) {
                            if (answerValue === 'Soltero') {
                                answerValue = 'Soltero/a'
                            } else if (answerValue === 'Casado') {
                                answerValue = 'Casado/a'
                            } else if (answerValue === 'Viudo') {
                                answerValue = 'Viudo/a'
                            } else if (answerValue === 'Divorciado') {
                                answerValue = 'Divorciado/a'
                            }
                        }

                        return answerValue || null
                    }
                }
            }
        }
    }

    return null
}

const updateAnswer = async (questionId, value) => {
    const sectionType = currentSection.value.type
    const convivienteIndex = currentSection.value.convivienteIndex

    const key = getAnswerKey(questionId, sectionType, convivienteIndex)
    answers.value[key] = value

    const questionSlug = findQuestionSlugById(questionId, sectionType, convivienteIndex)
    if (questionSlug === 'provincia') {
        await clearInvalidMunicipio(value, sectionType, convivienteIndex)
    }

    if (sectionType === 'conviviente' && convivienteIndex !== null) {
        checkAndSkipConvivienteSection(convivienteIndex, currentSection.value.sectionIndex)
    }
}

const findQuestionSlugById = (questionId, sectionType, convivienteIndex) => {
    if (!onboarderData.value) return null

    if (sectionType === 'solicitante' || sectionType === null) {
        for (const section of onboarderData.value.sections || []) {
            for (const question of section.questions || []) {
                if (question.question?.id === questionId) {
                    return question.question?.slug
                }
            }
        }
    }

    if (sectionType === 'conviviente' && convivienteIndex !== null) {
        const conviviente = addedConvivientes.value[convivienteIndex]
        if (conviviente) {
            for (const section of conviviente.type.sections || []) {
                for (const question of section.questions || []) {
                    if (question.question?.id === questionId) {
                        return question.question?.slug
                    }
                }
            }
        }
    }

    return null
}

const clearInvalidMunicipio = async (newProvincia, sectionType, convivienteIndex) => {
    if (!newProvincia) return

    let municipioQuestionId = null

    if (sectionType === 'solicitante' || sectionType === null) {
        for (const section of onboarderData.value.sections || []) {
            for (const question of section.questions || []) {
                if (question.question?.slug === 'municipio') {
                    municipioQuestionId = question.question.id
                    break
                }
            }
            if (municipioQuestionId) break
        }
    }

    if (sectionType === 'conviviente' && convivienteIndex !== null) {
        const conviviente = addedConvivientes.value[convivienteIndex]
        if (conviviente) {
            for (const section of conviviente.type.sections || []) {
                for (const question of section.questions || []) {
                    if (question.question?.slug === 'municipio') {
                        municipioQuestionId = question.question.id
                        break
                    }
                }
                if (municipioQuestionId) break
            }
        }
    }

    if (!municipioQuestionId) return

    const currentMunicipio = getAnswerValue(municipioQuestionId, sectionType, convivienteIndex)
    if (!currentMunicipio) return

    try {
        verifyingMunicipioForQuestion.value = municipioQuestionId

        const response = await axios.get('/admin/searchMunicipios', {
            params: { provincia: newProvincia },
        })
        const municipios = response.data

        const municipioExists = municipios.some((mun) => {
            const munValue = mun.value || mun.nombre || mun
            return munValue === currentMunicipio
        })

        if (!municipioExists) {
            const municipioKey = getAnswerKey(municipioQuestionId, sectionType, convivienteIndex)
            answers.value[municipioKey] = null
        }
    } catch (error) {
        console.error('Error verificando municipio:', error)
        throw error
    } finally {
        verifyingMunicipioForQuestion.value = null
    }
}

const checkAndSkipConvivienteSection = (convivienteIndex, sectionIndex) => {
    const conviviente = addedConvivientes.value[convivienteIndex]
    if (!conviviente?.type?.sections?.[sectionIndex]) {
        return
    }

    const section = conviviente.type.sections[sectionIndex]
    if (!section.skip_condition) {
        return
    }

    try {
        const skipCondition =
            typeof section.skip_condition === 'string'
                ? JSON.parse(section.skip_condition)
                : section.skip_condition

        if (!skipCondition.dependsOnQuestionId) {
            return
        }

        const shouldSkip = evaluateSkipCondition(skipCondition, convivienteIndex)

        if (shouldSkip) {
            skipConvivienteSection(convivienteIndex, sectionIndex)
        } else {
        }
    } catch (error) {
        console.error('Error evaluando condición de skip:', error)
    }
}

const evaluateSkipCondition = (condition, convivienteIndex) => {
    if (!condition || !condition.dependsOnQuestionId) return false
    condition = normalizeCondition(condition)

    const questionId = condition.dependsOnQuestionId.id || condition.dependsOnQuestionId
    let dependentAnswer
    if (condition.personType && condition.personType === 'solicitante') {
        const key = `solicitante_${questionId}`
        dependentAnswer = answers.value[key]
    } else {
        const key = `conviviente_${convivienteIndex}_${questionId}`
        dependentAnswer = answers.value[key]
    }

    if (dependentAnswer === undefined || dependentAnswer === null) return false

    switch (condition.conditionType) {
        case 'age_less_than':
            const age = calculateAge(dependentAnswer)
            return age !== null && age < Number(condition.expectedValue)
        case 'age_greater_than':
            const age2 = calculateAge(dependentAnswer)
            return age2 !== null && age2 > Number(condition.expectedValue)
        case 'age_between':
            const age3 = calculateAge(dependentAnswer)
            return (
                age3 !== null &&
                age3 >= Number(condition.expectedValue) &&
                age3 <= Number(condition.expectedValue2)
            )
        case 'equals':
            return dependentAnswer == condition.expectedValue
        case 'not_equals':
            return dependentAnswer != condition.expectedValue
        case 'less_than':
            if (isDateValue(dependentAnswer) && isDateValue(condition.expectedValue)) {
                return new Date(dependentAnswer) < new Date(condition.expectedValue)
            }
            return Number(dependentAnswer) < Number(condition.expectedValue)
        case 'greater_than':
            if (isDateValue(dependentAnswer) && isDateValue(condition.expectedValue)) {
                return new Date(dependentAnswer) > new Date(condition.expectedValue)
            }
            return Number(dependentAnswer) > Number(condition.expectedValue)
        default:
            return false
    }
}

const skipConvivienteSection = (convivienteIndex, sectionIndex) => {
    const conviviente = addedConvivientes.value[convivienteIndex]
    if (!conviviente?.type?.sections?.[sectionIndex]) return

    const skipKey = `conviviente_${convivienteIndex}_section_skipped_${sectionIndex}`
    answers.value[skipKey] = true

    nextConvivienteSection(convivienteIndex, sectionIndex)
}

const nextConvivienteSection = (convivienteIndex, currentSectionIndex) => {
    const conviviente = addedConvivientes.value[convivienteIndex]
    if (!conviviente?.type?.sections) return

    const totalSections = conviviente.type.sections.length

    for (let nextIndex = currentSectionIndex + 1; nextIndex < totalSections; nextIndex++) {
        const nextSection = conviviente.type.sections[nextIndex]
        if (nextSection && shouldShowConvivienteSection(convivienteIndex, nextIndex)) {
            selectSection('conviviente', null, convivienteIndex, nextIndex, 'skip_auto')
            return
        }
    }

    showConvivientesHub.value = true
}

const evaluateConditionForSidebarConv = (condition, convivienteIndex) => {
    return evaluateCondition(condition, (questionId) => {
        const key = `conviviente_${convivienteIndex}_${questionId}`
        return answers.value[key]
    })
}

const shouldShowConvivienteSection = (convivienteIndex, sectionIndex) => {
    if (isConvivienteSectionSkipped(convivienteIndex, sectionIndex)) {
        return false
    }

    const conviviente = addedConvivientes.value[convivienteIndex]
    if (!conviviente?.type?.sections?.[sectionIndex]) return false

    const section = conviviente.type.sections[sectionIndex]

    if (section.skip_condition) {
        try {
            const skipCondition =
                typeof section.skip_condition === 'string'
                    ? JSON.parse(section.skip_condition)
                    : section.skip_condition
            if (skipCondition.dependsOnQuestionId) {
                const shouldSkip = evaluateSkipCondition(skipCondition, convivienteIndex)
                if (shouldSkip) return false
            }
        } catch (error) {
            console.error('Error evaluando condición de skip para sidebar:', error)
        }
    }

    if (Array.isArray(section.questions) && section.questions.length > 0) {
        const hasVisibleQuestion = section.questions.some((q) => {
            if (!q?.condition) return true

            const normalizedCondition = normalizeCondition(q.condition)
            if (normalizedCondition.dependsOnQuestionId) {
                const dependentQuestionVisible = isDependentQuestionVisible(
                    normalizedCondition.dependsOnQuestionId,
                    normalizedCondition.personType,
                    normalizedCondition.personIndex,
                )
                if (!dependentQuestionVisible) {
                    return false
                }
            }

            return evaluateConditionForSidebarConv(q.condition, convivienteIndex)
        })
        return hasVisibleQuestion
    }

    return true
}

const findPreviousAvailableConvivienteSection = (convivienteIndex, currentSectionIndex) => {
    const conviviente = addedConvivientes.value[convivienteIndex]
    if (!conviviente?.type?.sections) return null

    for (let i = currentSectionIndex - 1; i >= 0; i--) {
        if (shouldShowConvivienteSection(convivienteIndex, i)) {
            return i
        }
    }

    return null
}

const findLastAvailableConvivienteSection = (convivienteIndex) => {
    const conviviente = addedConvivientes.value[convivienteIndex]
    if (!conviviente?.type?.sections) return null

    const totalSections = conviviente.type.sections.length

    for (let i = totalSections - 1; i >= 0; i--) {
        if (shouldShowConvivienteSection(convivienteIndex, i)) {
            return i
        }
    }

    return null
}

const isQuestionBlocked = (question) => {
    if (currentSection.value.type !== 'solicitante') {
        return false
    }

    const isFromBankflip = checkIfUserIsFromBankflip()
    const shouldBlock = Boolean(question.block_if_bankflip_filled) && isFromBankflip
    return shouldBlock
}

const isQuestionRequired = (question) => {
    if (isQuestionBlocked(question)) {
        return false
    }

    if (isQuestionHidden(question)) {
        return false
    }

    if (currentSection.value.type === 'solicitante') {
        if (question.requiredCondition) {
            const normalizedCondition = normalizeCondition(question.requiredCondition)
            return evaluateCondition(normalizedCondition, getAnswerByQuestionId)
        }

        if (question.optionalCondition) {
            const normalizedCondition = normalizeCondition(question.optionalCondition)
            return !evaluateCondition(normalizedCondition, getAnswerByQuestionId)
        }
        return true
    }

    if (currentSection.value.type === 'conviviente') {
        if (question.requiredCondition && question.requiredCondition.isDefault === true) {
            return true
        }

        if (question.requiredCondition && question.requiredCondition.dependsOnQuestionId) {
            const normalizedCondition = normalizeCondition(question.requiredCondition)
            return evaluateCondition(normalizedCondition, getAnswerByQuestionId)
        }

        if (question.optionalCondition) {
            const normalizedCondition = normalizeCondition(question.optionalCondition)
            return !evaluateCondition(normalizedCondition, getAnswerByQuestionId)
        }

        return false
    }

    return false
}

const skipSection = () => {
    console.log('Skipping section')

    if (currentSectionData.value) {
        const sectionKey =
            currentSection.value.type === 'solicitante'
                ? `section_skipped_solicitante_${currentSection.value.index}`
                : `section_skipped_conviviente_${currentSection.value.convivienteIndex}_${currentSection.value.sectionIndex}`
        answers.value[sectionKey] = true
    }

    nextSection()
}

const openConvivientesHub = () => {
    showConvivientesHub.value = true
}

const closeConvivientesHub = () => {
    showConvivientesHub.value = false
}

const openAddConvivienteModal = () => {
    closeConvivientesHub()
    showAddConvivienteModal.value = true
}

const editConviviente = (index) => {
    closeConvivientesHub()
    selectSection('conviviente', null, index, 0, 'start_conviviente')
}

const removeConvivienteFromHub = (index) => {
    addedConvivientes.value.splice(index, 1)
    Object.keys(answers.value).forEach((key) => {
        if (key.startsWith(`conviviente_${index}_`)) {
            delete answers.value[key]
        }
    })
}

const startConvivienteSections = () => {
    if (addedConvivientes.value.length > 0) {
        closeConvivientesHub()
        selectSection('conviviente', null, 0, 0, 'start_conviviente')
    }
}

const isAllSolicitanteSectionsComplete = () => {
    if (onboarderData.value?.sections) {
        for (let i = 0; i < onboarderData.value.sections.length; i++) {
            if (!isSolicitanteSectionCompleteByIndex(i)) {
                return false
            }
        }
    }
    return true
}

const isAllSectionsComplete = () => {
    const missingQuestions = getAllMissingRequiredQuestions()
    return missingQuestions.length === 0
}

const isConvivienteSectionCompleteFromData = (section, typeIndex, sectionIndex) => {
    if (!section || !section.questions) {
        return true
    }

    if (isConvivienteSectionSkipped(typeIndex, sectionIndex)) {
        return true
    }

    try {
        const isQuestionRequiredForConviviente = (question) => {
            if (isQuestionBlockedForConviviente(question)) return false
            if (isQuestionHiddenForConviviente(question)) return false

            let requiredCondition = question.required_condition
            if (typeof requiredCondition === 'string') {
                try {
                    requiredCondition = JSON.parse(requiredCondition)
                } catch (error) {
                    requiredCondition = null
                }
            }

            if (requiredCondition && requiredCondition.isDefault === true) {
                return true
            }

            if (requiredCondition && requiredCondition.dependsOnQuestionId) {
                const result = evaluateCondition(
                    requiredCondition,
                    getAnswerByQuestionIdForConviviente,
                )
                return result
            }

            if (question.optional_condition) {
                const result = !evaluateCondition(
                    question.optional_condition,
                    getAnswerByQuestionIdForConviviente,
                )
                return result
            }

            return false
        }

        const getAnswerByQuestionIdForConviviente = (questionId) => {
            for (
                let convivienteIndex = 0;
                convivienteIndex < addedConvivientes.value.length;
                convivienteIndex++
            ) {
                const key = `conviviente_${convivienteIndex}_${questionId}`
                if (answers.value[key] !== undefined) {
                    return answers.value[key]
                }
            }
            return undefined
        }

        const isQuestionBlockedForConviviente = (question) => {
            if (!question.block_if_bankflip_filled) return false
            const answer = getAnswerByQuestionIdForConviviente(question.question?.id)
            return answer !== undefined && answer !== null && answer !== ''
        }

        const isQuestionHiddenForConviviente = (question) => {
            if (!question.hide_if_bankflip_filled) return false
            const answer = getAnswerByQuestionIdForConviviente(question.question?.id)
            return answer !== undefined && answer !== null && answer !== ''
        }

        const shouldShowQuestionForConviviente = (question) => {
            if (isQuestionBlockedForConviviente(question)) return false
            if (isQuestionHiddenForConviviente(question)) return false
            return true
        }

        const useScreenFilter =
            currentSection.value.type === 'conviviente' &&
            currentSection.value.convivienteIndex === typeIndex &&
            currentSection.value.sectionIndex === sectionIndex
        const questionsThisScreen = useScreenFilter
            ? getSectionQuestionsByScreen(section, currentScreenIndex.value)
            : section.questions
        const visibleQuestions = questionsThisScreen.filter(shouldShowQuestionForConviviente)

        const isComplete = visibleQuestions.every((question, index) => {
            const questionId = question.question?.id
            if (!questionId) {
                return true
            }

            const isRequired = isQuestionRequiredForConviviente(question)
            if (!isRequired) {
                return true
            }

            const answer = getAnswerByQuestionIdForConviviente(questionId)
            const hasAnswer = answer !== undefined && answer !== null && answer !== ''
            return hasAnswer
        })

        return isComplete
    } catch (error) {
        return true
    }
}

const isConvivienteSectionComplete = (convivienteIndex, sectionIndex) => {
    const conviviente = addedConvivientes.value[convivienteIndex]
    if (!conviviente?.type?.sections?.[sectionIndex]) return true

    if (isConvivienteSectionSkipped(convivienteIndex, sectionIndex)) {
        return true
    }

    const section = conviviente.type.sections[sectionIndex]
    if (!section.questions) return true

    const getAnswerByQuestionIdForConviviente = (questionId) => {
        const key = `conviviente_${convivienteIndex}_${questionId}`
        return answers.value[key]
    }

    const isQuestionBlockedForConviviente = (question) => {
        if (!question.block_if_bankflip_filled) return false
        const answer = getAnswerByQuestionIdForConviviente(question.question?.id)
        return answer !== undefined && answer !== null && answer !== ''
    }

    const isQuestionHiddenForConviviente = (question) => {
        if (!question.hide_if_bankflip_filled) return false
        const answer = getAnswerByQuestionIdForConviviente(question.question?.id)
        return answer !== undefined && answer !== null && answer !== ''
    }

    const shouldShowQuestionForConviviente = (question) => {
        if (isQuestionBlockedForConviviente(question)) return false
        if (isQuestionHiddenForConviviente(question)) return false
        return true
    }

    const useScreenFilter2 =
        currentSection.value.type === 'conviviente' &&
        currentSection.value.convivienteIndex === convivienteIndex &&
        currentSection.value.sectionIndex === sectionIndex
    const questionsThisScreen2 = useScreenFilter2
        ? getSectionQuestionsByScreen(section, currentScreenIndex.value)
        : section.questions
    const visibleQuestions = questionsThisScreen2.filter(shouldShowQuestionForConviviente)

    const isComplete = visibleQuestions.every((question, index) => {
        const questionId = question.question?.id

        if (!questionId) {
            return true
        }

        const isRequired = isQuestionRequiredForConviviente(question)

        if (!isRequired) {
            return true
        }

        const answer = getAnswerByQuestionIdForConviviente(questionId)
        const hasAnswer = answer !== undefined && answer !== null && answer !== ''

        return hasAnswer
    })

    return isComplete
}

const isCurrentConvivienteSectionComplete = () => {
    if (currentSection.value.type !== 'conviviente') {
        return true
    }

    const convivienteIndex = currentSection.value.convivienteIndex
    const sectionIndex = currentSection.value.sectionIndex

    if (convivienteIndex === null || sectionIndex === null) {
        return true
    }

    const conviviente = addedConvivientes.value[convivienteIndex]

    if (!conviviente?.type?.sections?.[sectionIndex]) {
        return true
    }

    const section = conviviente.type.sections[sectionIndex]
    const result = isConvivienteSectionCompleteFromData(section, convivienteIndex, sectionIndex)
    return result
}

const getAllMissingRequiredQuestions = () => {
    const missingQuestions = []

    if (onboarderData.value?.sections) {
        onboarderData.value.sections.forEach((section, sectionIndex) => {
            const sectionMissing = getMissingQuestionsForSolicitanteSection(sectionIndex)
            if (sectionMissing.length > 0) {
                missingQuestions.push({
                    section: `Solicitante - ${section.name}`,
                    questions: sectionMissing,
                })
            }
        })
    }

    if (addedConvivientes.value.length > 0) {
        addedConvivientes.value.forEach((addedConviviente, convivienteIndex) => {
            const convivienteType = addedConviviente.type

            if (convivienteType.sections) {
                convivienteType.sections.forEach((section, sectionIndex) => {
                    const sectionMissing = getMissingQuestionsForAddedConvivienteSection(
                        convivienteIndex,
                        sectionIndex,
                    )
                    if (sectionMissing.length > 0) {
                        missingQuestions.push({
                            section: `Conviviente ${convivienteIndex + 1} - ${section.name}`,
                            questions: sectionMissing,
                        })
                    }
                })
            }
        })
    }

    return missingQuestions
}

const getMissingQuestionsForAddedConvivienteSection = (convivienteIndex, sectionIndex) => {
    const addedConviviente = addedConvivientes.value[convivienteIndex]
    if (!addedConviviente) return []

    const section = addedConviviente.type?.sections?.[sectionIndex]
    if (!section || !section.questions) return []

    const getAnswerByQuestionIdForConviviente = (questionId) => {
        const key = `conviviente_${convivienteIndex}_${questionId}`
        return answers.value[key]
    }

    const isQuestionHiddenForConviviente = (question) => {
        if (!question.hide_if_bankflip_filled) return false
        const answer = getAnswerByQuestionIdForConviviente(question.question?.id)
        return answer !== undefined && answer !== null && answer !== ''
    }

    const evaluateConditionForConviviente = (condition) => {
        if (!condition || !condition.dependsOnQuestionId) return true
        const normalized = normalizeCondition(condition)
        const dep = getAnswerByQuestionIdForConviviente(normalized.dependsOnQuestionId)
        if (dep === undefined || dep === null) return false
        return evaluateCondition(normalized, getAnswerByQuestionIdForConviviente)
    }

    const isRequiredForConviviente = (question) => {
        if (isQuestionHiddenForConviviente(question)) return false

        let requiredCondition = question.required_condition
        if (typeof requiredCondition === 'string') {
            try {
                requiredCondition = JSON.parse(requiredCondition)
            } catch (error) {
                requiredCondition = null
            }
        }

        if (requiredCondition && requiredCondition.isDefault === true) {
            return true
        }

        if (requiredCondition && requiredCondition.dependsOnQuestionId) {
            return evaluateConditionForConviviente(requiredCondition)
        }

        if (question.optional_condition) {
            return !evaluateConditionForConviviente(question.optional_condition)
        }

        return false
    }

    const visible = section.questions.filter(
        (q) =>
            !isQuestionHiddenForConviviente(q) &&
            (!q.condition || evaluateConditionForConviviente(q.condition)),
    )

    const missing = visible.filter((q) => {
        const qid = q.question?.id
        if (!qid) return false
        const required = isRequiredForConviviente(q)
        if (!required) return false
        const ans = getAnswerByQuestionIdForConviviente(qid)
        return ans === undefined || ans === null || ans === ''
    })

    return missing
}

const getMissingQuestionsForConvivienteSection = (typeIndex, sectionIndex) => {
    const convivienteType = onboarderData.value?.conviviente_types?.[typeIndex]
    const section = convivienteType?.sections?.[sectionIndex]

    if (!section || !section.questions) return []

    const getAnswerByQuestionIdForConviviente = (questionId) => {
        for (
            let convivienteIndex = 0;
            convivienteIndex < addedConvivientes.value.length;
            convivienteIndex++
        ) {
            const key = `conviviente_${convivienteIndex}_${questionId}`
            if (answers.value[key] !== undefined) {
                return answers.value[key]
            }
        }
        return undefined
    }

    const isQuestionHiddenForConviviente = (question) => {
        if (!question.hide_if_bankflip_filled) return false
        const answer = getAnswerByQuestionIdForConviviente(question.question?.id)
        return answer !== undefined && answer !== null && answer !== ''
    }

    const evaluateConditionForConviviente = (condition) => {
        if (!condition || !condition.dependsOnQuestionId) return true
        const normalized = normalizeCondition(condition)
        const dep = getAnswerByQuestionIdForConviviente(normalized.dependsOnQuestionId)
        if (dep === undefined || dep === null) return false
        return evaluateCondition(normalized, getAnswerByQuestionIdForConviviente)
    }

    const isRequiredForConviviente = (question) => {
        if (isQuestionHiddenForConviviente(question)) return false

        let requiredCondition = question.required_condition
        if (typeof requiredCondition === 'string') {
            try {
                requiredCondition = JSON.parse(requiredCondition)
            } catch (error) {
                requiredCondition = null
            }
        }

        if (requiredCondition && requiredCondition.isDefault === true) {
            return true
        }

        if (requiredCondition && requiredCondition.dependsOnQuestionId) {
            return evaluateConditionForConviviente(requiredCondition)
        }

        if (question.optional_condition) {
            return !evaluateConditionForConviviente(question.optional_condition)
        }

        return false
    }

    const visible = section.questions.filter(
        (q) =>
            !isQuestionHiddenForConviviente(q) &&
            (!q.condition || evaluateConditionForConviviente(q.condition)),
    )

    const missing = visible.filter((q) => {
        const qid = q.question?.id
        if (!qid) return false
        const required = isRequiredForConviviente(q)
        if (!required) return false
        const ans = getAnswerByQuestionIdForConviviente(qid)
        return ans === undefined || ans === null || ans === ''
    })

    return missing
}

const showDetailedMissingQuestionsError = (missingSections) => {
    if (missingSections.length === 0) {
        showNotification('No hay preguntas faltantes.', 'info')
        return
    }

    let message = `Faltan ${missingSections.length} sección${missingSections.length > 1 ? 'es' : ''} por completar:\n\n`

    missingSections.forEach((section, index) => {
        message += `${index + 1}. ${section.section}:\n`
        const maxQuestions = 3
        const questionsToShow = section.questions.slice(0, maxQuestions)
        questionsToShow.forEach((q) => {
            message += `   • ${q.question?.text || 'Pregunta sin texto'}\n`
        })
        if (section.questions.length > maxQuestions) {
            message += `   ... y ${section.questions.length - maxQuestions} más\n`
        }
        message += '\n'
    })

    showNotification(message, 'error', 10000)
}

const showFinishConfirmation = () => {
    const missingQuestions = getAllMissingRequiredQuestions()

    if (missingQuestions.length > 0) {
        showDetailedMissingQuestionsError(missingQuestions)
        return
    }
    showFinishModal.value = true
}

const closeFinishModal = () => {
    showFinishModal.value = false
}

const finishOnboarder = async () => {
    try {
        isSaving.value = true
        if (currentSection.value.type && currentSection.value.type !== null) {
            const currentSectionId =
                currentSection.value.type === 'solicitante'
                    ? onboarderData.value?.sections?.[currentSection.value.index]?.id
                    : addedConvivientes.value[currentSection.value.convivienteIndex]?.type
                          ?.sections?.[currentSection.value.sectionIndex]?.id

            if (currentSectionId) {
                endSectionTracking(
                    currentSectionId,
                    currentSection.value.type,
                    currentSection.value.type === 'conviviente'
                        ? addedConvivientes.value[currentSection.value.convivienteIndex]?.type?.id
                        : null,
                )
            }
        }

        endOnboarderTracking()
        const visibleAnswers = getVisibleAnswers()
        const answersData = {}
        const convivientesData = []

        Object.keys(visibleAnswers).forEach((key) => {
            if (key.startsWith('solicitante_')) {
                const questionId = key.replace('solicitante_', '')
                answersData[questionId] = visibleAnswers[key]
            }
        })

        addedConvivientes.value.forEach((conviviente, index) => {
            const convivienteAnswers = {}

            Object.keys(visibleAnswers).forEach((key) => {
                if (key.startsWith(`conviviente_${index}_`)) {
                    const questionId = key.replace(`conviviente_${index}_`, '')
                    convivienteAnswers[questionId] = visibleAnswers[key]
                }
            })

            if (Object.keys(convivienteAnswers).length > 0) {
                convivientesData.push({
                    tipo: conviviente.type.name,
                    answers: convivienteAnswers,
                })
            }
        })

        const response = await axios.post('/onboarder/finish', {
            onboarder_id: currentOnboarderId.value,
            answers: answersData,
            convivientes: convivientesData,
        })

        if (response.data.success) {
            abandonmentTracked.value = false

            showNotification('¡Cuestionario completado exitosamente!', 'success')

            window.location.href = '/onboarding'
        } else {
            throw new Error(response.data.message || 'Error al guardar las respuestas')
        }
    } catch (error) {
        console.error('Error al finalizar:', error)
        showNotification('Error al finalizar el cuestionario', 'error')
        isSaving.value = false
        throw new Error('error al finalizar el cuestionario. motivo: ' + error)
    }
}

const showNotification = (message, type = 'warning', duration = 5000) => {
    const id = notificationId.value++
    const notification = {
        id,
        message,
        type,
        show: true,
    }

    notifications.value.push(notification)

    setTimeout(() => {
        removeNotification(id)
    }, duration)
}

const removeNotification = (id) => {
    const index = notifications.value.findIndex((n) => n.id === id)
    if (index > -1) {
        notifications.value.splice(index, 1)
    }
}

const trackMetric = async (action, data = {}) => {
    if (!currentOnboarderId.value) return

    try {
        await axios.post('/api/onboarders/metrics', {
            onboarder_id: currentOnboarderId.value,
            action,
            ...data,
        })
    } catch (error) {
        console.error('Error tracking metric:', error)
    }
}

const trackScreenMetric = async (action, screenIndex, sectionId, convivienteTypeId = null) => {
    if (!currentOnboarderId.value) return

    try {
        await axios.post('/api/onboarders/metrics', {
            onboarder_id: currentOnboarderId.value,
            action,
            section_id: sectionId,
            conviviente_type_id: convivienteTypeId,
            screen_index: screenIndex,
        })
    } catch (error) {
        console.error('Error tracking screen metric:', error)
    }
}

const startOnboarderTracking = () => {
    onboarderStartTime.value = Date.now()
    trackMetric('onboarder_started')
}

const endOnboarderTracking = () => {
    if (onboarderStartTime.value) {
        const duration = Math.floor((Date.now() - onboarderStartTime.value) / 1000)
        trackMetric('onboarder_completed', { duration_seconds: duration })
    }
}

const startSectionTracking = (sectionId, sectionType = 'solicitante', convivienteTypeId = null) => {
    sectionStartTime.value = Date.now()
    const key = `${sectionType}_${sectionId}_${convivienteTypeId || ''}`
    sectionMetrics.value[key] = {
        sectionId,
        sectionType,
        convivienteTypeId,
        startTime: sectionStartTime.value,
    }

    trackMetric('section_started', {
        section_id: sectionId,
        section_type: sectionType,
        conviviente_type_id: convivienteTypeId,
    })
}

const endSectionTracking = (sectionId, sectionType = 'solicitante', convivienteTypeId = null) => {
    if (!sectionStartTime.value) return

    const duration = Math.floor((Date.now() - sectionStartTime.value) / 1000)
    const key = `${sectionType}_${sectionId}_${convivienteTypeId || ''}`

    if (sectionMetrics.value[key]) {
        sectionMetrics.value[key].duration = duration
        sectionMetrics.value[key].completed = true
    }

    trackMetric('section_completed', {
        section_id: sectionId,
        section_type: sectionType,
        conviviente_type_id: convivienteTypeId,
        duration_seconds: duration,
    })

    sectionStartTime.value = null
}

const trackConvivienteAction = (action, convivienteTypeId, convivienteIndex) => {
    trackMetric(`conviviente_${action}`, {
        conviviente_type_id: convivienteTypeId,
        conviviente_index: convivienteIndex,
    })
}

const trackNavigation = (fromSection, toSection, action = 'navigate') => {
    trackMetric('navigation', {
        from_section: fromSection,
        to_section: toSection,
        action,
    })
}

const trackAbandonment = () => {
    // Evitar duplicados - solo trackear una vez por sesión
    if (abandonmentTracked.value || !onboarderStartTime.value) return

    abandonmentTracked.value = true
    const duration = Math.floor((Date.now() - onboarderStartTime.value) / 1000)
    trackMetric('onboarder_abandoned', {
        duration_seconds: duration,
        last_section: currentSection.value,
    })
}

const previousSection = () => {
    if (currentSection.value.type === 'solicitante') {
        const section = onboarderData.value?.sections?.[currentSection.value.index]
        if (section) {
            if (currentScreenIndex.value > 0) {
                currentScreenIndex.value -= 1
                return
            }
        }
        if (currentSection.value.index > 0) {
            const prevSection = onboarderData.value?.sections?.[currentSection.value.index - 1]
            const maxScreen = getSectionTotalScreens(prevSection) - 1
            selectSection('solicitante', currentSection.value.index - 1, null, null, 'previous')
            currentScreenIndex.value = maxScreen
        }
    } else if (currentSection.value.type === 'conviviente') {
        const convivienteIndex = currentSection.value.convivienteIndex
        const currentSectionIndex = currentSection.value.sectionIndex

        const conviviente = addedConvivientes.value[convivienteIndex]
        const section = conviviente?.type?.sections?.[currentSectionIndex]
        if (section) {
            if (currentScreenIndex.value > 0) {
                currentScreenIndex.value -= 1
                return
            }
        }

        const previousAvailableSection = findPreviousAvailableConvivienteSection(
            convivienteIndex,
            currentSectionIndex,
        )

        if (previousAvailableSection !== null) {
            const prevSection = conviviente?.type?.sections?.[previousAvailableSection]
            const maxScreen = getSectionTotalScreens(prevSection) - 1 // 0-based
            selectSection(
                'conviviente',
                null,
                convivienteIndex,
                previousAvailableSection,
                'previous',
            )
            currentScreenIndex.value = maxScreen
        } else {
            if (convivienteIndex > 0) {
                const prevConviviente = addedConvivientes.value[convivienteIndex - 1]
                const lastSectionIndex = findLastAvailableConvivienteSection(convivienteIndex - 1)

                if (lastSectionIndex !== null) {
                    const prevConvivienteSection =
                        prevConviviente?.type?.sections?.[lastSectionIndex]
                    const maxScreen = getSectionTotalScreens(prevConvivienteSection) - 1
                    selectSection(
                        'conviviente',
                        null,
                        convivienteIndex - 1,
                        lastSectionIndex,
                        'previous',
                    )
                    currentScreenIndex.value = maxScreen
                } else {
                    const lastSectionIndex = (onboarderData.value?.sections?.length || 1) - 1
                    const lastSection = onboarderData.value?.sections?.[lastSectionIndex]
                    const maxScreen = getSectionTotalScreens(lastSection) - 1 // 0-based
                    selectSection('solicitante', lastSectionIndex, null, null, 'previous')
                    currentScreenIndex.value = maxScreen
                }
            } else {
                const lastSectionIndex = (onboarderData.value?.sections?.length || 1) - 1
                const lastSection = onboarderData.value?.sections?.[lastSectionIndex]
                const maxScreen = getSectionTotalScreens(lastSection) - 1
                selectSection('solicitante', lastSectionIndex, null, null, 'previous')
                currentScreenIndex.value = maxScreen
            }
        }
    }
}

const getMissingRequiredQuestions = () => {
    if (currentSection.value.type !== 'solicitante') return []
    if (!currentSectionData.value?.questions) return []

    const questionsThisScreen = getSectionQuestionsByScreen(
        currentSectionData.value,
        currentScreenIndex.value,
    )
    const visibleQuestions = questionsThisScreen.filter((question) => shouldShowQuestion(question))

    const missingQuestions = visibleQuestions.filter((question) => {
        const questionId = question.question?.id
        if (!questionId) return false

        const isRequired = isQuestionRequired(question)
        const answer = getAnswerValue(questionId, 'solicitante', null)
        const isEmpty = answer === undefined || answer === null || answer === ''

        return isRequired && isEmpty
    })

    return missingQuestions
}

const showDetailedValidationError = (missingQuestions) => {
    if (missingQuestions.length === 0) {
        showNotification(
            'Por favor, completa todas las preguntas de esta sección antes de continuar.',
            'warning',
        )
        return
    }

    const questionTexts = missingQuestions
        .map((q) => q.question?.text || 'Pregunta sin texto')
        .slice(0, 3)
    const moreCount = missingQuestions.length - 3

    let message = `Faltan ${missingQuestions.length} pregunta${missingQuestions.length > 1 ? 's' : ''} requerida${missingQuestions.length > 1 ? 's' : ''}:\n\n`
    message += questionTexts.join('\n')
    if (moreCount > 0) {
        message += `\n... y ${moreCount} más`
    }

    showNotification(message, 'error')

    setTimeout(() => {
        const firstMissingQuestion = missingQuestions[0]
        if (firstMissingQuestion?.question?.id) {
            scrollToQuestion(firstMissingQuestion.question.id)
        }
    }, 100)
}

const scrollToQuestion = (questionId) => {
    const element = document.querySelector(`[data-question-id="${questionId}"]`)
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
        })

        element.classList.add('ring-2', 'ring-red-500', 'ring-opacity-50')
        setTimeout(() => {
            element.classList.remove('ring-2', 'ring-red-500', 'ring-opacity-50')
        }, 3000)
    }
}

const nextSection = () => {
    if (!canGoNext.value) return

    if (currentSection.value.type === 'solicitante') {
        if (!isSolicitanteSectionComplete.value) {
            const missingQuestions = getMissingRequiredQuestions()
            showDetailedValidationError(missingQuestions)
            return
        }
        const section = onboarderData.value?.sections?.[currentSection.value.index]
        const totalScreens = getSectionTotalScreens(section)
        if (currentScreenIndex.value < totalScreens - 1) {
            trackScreenMetric('screen_completed', currentScreenIndex.value, section.id)
            currentScreenIndex.value += 1
            trackScreenMetric('screen_started', currentScreenIndex.value, section.id)
            return
        }
        if (currentSection.value.index >= (onboarderData.value?.sections?.length || 0) - 1) {
            showConvivientesHub.value = true
        } else {
            selectSection('solicitante', currentSection.value.index + 1, null, null, 'next')
        }
    } else if (currentSection.value.type === 'conviviente') {
        const conviviente = addedConvivientes.value[currentSection.value.convivienteIndex]
        if (!conviviente) return
        const section = conviviente.type.sections?.[currentSection.value.sectionIndex]
        const totalScreens = getSectionTotalScreens(section)
        if (currentScreenIndex.value < totalScreens - 1) {
            trackScreenMetric(
                'screen_completed',
                currentScreenIndex.value,
                section.id,
                conviviente.type.id,
            )
            currentScreenIndex.value += 1
            trackScreenMetric(
                'screen_started',
                currentScreenIndex.value,
                section.id,
                conviviente.type.id,
            )
            return
        }
        const total = conviviente.type.sections?.length || 0
        const curr = currentSection.value.sectionIndex
        let foundNext = -1
        for (let i = curr + 1; i < total; i++) {
            if (shouldShowConvivienteSection(currentSection.value.convivienteIndex, i)) {
                foundNext = i
                break
            }
        }
        if (foundNext >= 0) {
            selectSection(
                'conviviente',
                null,
                currentSection.value.convivienteIndex,
                foundNext,
                'next',
            )
            return
        }

        if (currentSection.value.convivienteIndex < addedConvivientes.value.length - 1) {
            const nextConv = currentSection.value.convivienteIndex + 1
            const nextConvSections = addedConvivientes.value[nextConv]?.type?.sections || []
            let firstVisible = -1
            for (let i = 0; i < nextConvSections.length; i++) {
                if (shouldShowConvivienteSection(nextConv, i)) {
                    firstVisible = i
                    break
                }
            }
            if (firstVisible >= 0) {
                selectSection('conviviente', null, nextConv, firstVisible, 'next')
            } else {
                showConvivientesHub.value = true
            }
        } else {
            showConvivientesHub.value = true
        }
    }
}

const addConviviente = (convivienteType) => {
    addedConvivientes.value.push({
        type: convivienteType,
        answers: {},
    })
    showAddConvivienteModal.value = false
    trackConvivienteAction('added', convivienteType.id, addedConvivientes.value.length - 1)

    // Seleccionar automáticamente la primera sección del nuevo conviviente
    if (convivienteType.sections?.length > 0) {
        selectSection('conviviente', null, addedConvivientes.value.length - 1, 0, 'add_conviviente')
    }
}

const removeConviviente = (convivienteIndex) => {
    const convivienteType = addedConvivientes.value[convivienteIndex]?.type
    if (convivienteType) {
        trackConvivienteAction('removed', convivienteType.id, convivienteIndex)
    }

    addedConvivientes.value.splice(convivienteIndex, 1)

    // Si el conviviente eliminado era el actual, volver a la primera sección del solicitante
    if (
        currentSection.value.type === 'conviviente' &&
        currentSection.value.convivienteIndex === convivienteIndex
    ) {
        if (onboarderData.value?.sections?.length > 0) {
            selectSection('solicitante', 0, null, null, 'remove_conviviente')
        }
    }
}

const onLogoError = () => {
    logoError.value = true
}

const toggleSidebar = () => {
    showSidebar.value = !showSidebar.value
}

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024 // lg breakpoint
}

const isSolicitanteSectionCompleteByIndex = (sectionIndex) => {
    if (!onboarderData.value?.sections?.[sectionIndex]) return true

    const section = onboarderData.value.sections[sectionIndex]
    if (!section.questions) return true
    const questionsThisScreen = section.questions

    const getAnswerByQuestionIdForSection = (questionId) => {
        const formAnswers = Object.keys(answers.value).find((key) => key.endsWith(`_${questionId}`))
        if (formAnswers && answers.value[formAnswers] !== undefined) {
            return answers.value[formAnswers]
        }

        if (userAnswers.value[questionId]) {
            const userAnswer = userAnswers.value[questionId]
            let answerValue = userAnswer.formatted_answer || userAnswer.answer

            if (userAnswer.question_slug === 'genero') {
                if (answerValue === 'M') {
                    answerValue = 'Mujer'
                } else if (answerValue === 'H') {
                    answerValue = 'Hombre'
                }
            }

            if (userAnswer.question_slug === 'estado_civil') {
                if (answerValue === 'Soltero') {
                    answerValue = 'Soltero/a'
                } else if (answerValue === 'Casado') {
                    answerValue = 'Casado/a'
                } else if (answerValue === 'Viudo') {
                    answerValue = 'Viudo/a'
                } else if (answerValue === 'Divorciado') {
                    answerValue = 'Divorciado/a'
                }
            }

            return answerValue
        }

        return undefined
    }

    const isQuestionBlockedForSection = (question) => {
        const isFromBankflip = checkIfUserIsFromBankflip()
        return Boolean(question.block_if_bankflip_filled) && isFromBankflip
    }

    const evaluateConditionForSection = (condition, getAnswerFunction) => {
        return evaluateCondition(condition, getAnswerFunction)
    }

    const shouldShowQuestionForSection = (question) => {
        const isQuestionHiddenForSection = (question) => {
            const isFromBankflip = checkIfUserIsFromBankflip()
            if (Boolean(question.hide_if_bankflip_filled) && isFromBankflip) return true
            return false
        }

        if (isQuestionHiddenForSection(question)) {
            return false
        }

        if (
            question.show_if_bankflip_filled !== null &&
            question.show_if_bankflip_filled !== undefined
        ) {
            const isFromBankflip = checkIfUserIsFromBankflip()

            if (
                question.show_if_bankflip_filled === 1 ||
                question.show_if_bankflip_filled === true
            ) {
                if (!isFromBankflip) {
                    return false
                }
            } else if (
                question.show_if_bankflip_filled === 0 ||
                question.show_if_bankflip_filled === false
            ) {
                if (isFromBankflip) {
                    return false
                }
            }
        }

        if (!question.condition) return true
        return evaluateConditionForSection(question.condition, getAnswerByQuestionIdForSection)
    }

    const isQuestionRequiredForSection = (question) => {
        if (isQuestionBlockedForSection(question)) {
            return false
        }

        const isQuestionHiddenForSection = (question) => {
            const isFromBankflip = checkIfUserIsFromBankflip()
            if (Boolean(question.hide_if_bankflip_filled) && isFromBankflip) return true
            return false
        }

        if (isQuestionHiddenForSection(question)) {
            return false
        }

        if (question.requiredCondition) {
            return evaluateConditionForSection(
                question.requiredCondition,
                getAnswerByQuestionIdForSection,
            )
        }

        if (question.optionalCondition) {
            return !evaluateConditionForSection(
                question.optionalCondition,
                getAnswerByQuestionIdForSection,
            )
        }

        return true
    }

    const visibleQuestions = questionsThisScreen.filter(shouldShowQuestionForSection)

    const isComplete = visibleQuestions.every((question) => {
        const questionId = question.question?.id
        if (!questionId) return true

        const isRequired = isQuestionRequiredForSection(question)
        const answer = getAnswerByQuestionIdForSection(questionId)
        const isValid = answer !== undefined && answer !== null && answer !== ''

        if (!isRequired) {
            return true
        }

        return isValid
    })

    return isComplete
}

const selectSection = (
    type,
    index,
    convivienteIndex = null,
    sectionIndex = null,
    action = 'navigate',
) => {
    if (
        type === 'solicitante' &&
        currentSection.value.type === 'solicitante' &&
        action !== 'previous' &&
        action !== 'sidebar' &&
        index > currentSection.value.index
    ) {
        if (isSolicitanteSectionBlocked(index)) {
            showBlockedSolicitanteSectionsWarning(index)
            return
        }
        if (!isSolicitanteSectionCompleteByIndex(currentSection.value.index)) {
            showCurrentSolicitanteSectionMissingWarning()
            return
        }
    }

    if (currentSection.value.type && currentSection.value.type !== null) {
        const prevSectionId =
            currentSection.value.type === 'solicitante'
                ? onboarderData.value?.sections?.[currentSection.value.index]?.id
                : addedConvivientes.value[currentSection.value.convivienteIndex]?.type?.sections?.[
                      currentSection.value.sectionIndex
                  ]?.id

        if (prevSectionId) {
            endSectionTracking(
                prevSectionId,
                currentSection.value.type,
                currentSection.value.type === 'conviviente'
                    ? addedConvivientes.value[currentSection.value.convivienteIndex]?.type?.id
                    : null,
            )
        }
    }

    const fromSection = currentSection.value.type
        ? `${currentSection.value.type}_${currentSection.value.index || currentSection.value.convivienteIndex}_${currentSection.value.sectionIndex || ''}`
        : null
    const toSection = `${type}_${index || convivienteIndex}_${sectionIndex || ''}`

    if (fromSection) {
        trackNavigation(fromSection, toSection, action)
    }

    currentSection.value = {
        type,
        index,
        convivienteIndex,
        sectionIndex,
    }

    currentScreenIndex.value = 0

    if (type === 'solicitante' && onboarderData.value?.sections?.[index]?.id) {
        const section = onboarderData.value.sections[index]
        startSectionTracking(section.id, 'solicitante')
        trackScreenMetric('screen_started', 0, section.id)
    } else if (
        type === 'conviviente' &&
        addedConvivientes.value[convivienteIndex]?.type?.sections?.[sectionIndex]?.id
    ) {
        const section = addedConvivientes.value[convivienteIndex].type.sections[sectionIndex]
        const convivienteTypeId = addedConvivientes.value[convivienteIndex].type.id
        startSectionTracking(section.id, 'conviviente', convivienteTypeId)
        trackScreenMetric('screen_started', 0, section.id, convivienteTypeId)
    }

    // Cerrar sidebar en mobile después de seleccionar
    if (isMobile.value) {
        showSidebar.value = false
    }

    if (type === 'conviviente' && convivienteIndex !== null) {
        checkAndSkipConvivienteSection(convivienteIndex, sectionIndex)
    }
}
const handleBeforeUnload = () => {
    // beforeunload es más confiable para abandono real
    trackAbandonment()
}

const handleVisibilityChange = () => {
    // Solo trackear abandono si la página ha estado oculta por más de 30 segundos
    // Esto evita trackear cambios temporales de pestaña
    if (document.hidden && !abandonmentTracked.value) {
        setTimeout(() => {
            if (document.hidden && !abandonmentTracked.value) {
                trackAbandonment()
            }
        }, 30000) // 30 segundos
    }
}

// Lifecycle
onMounted(() => {
    loadOnboarder()
    checkMobile()
    window.addEventListener('resize', checkMobile)
    window.addEventListener('beforeunload', handleBeforeUnload)
    document.addEventListener('visibilitychange', handleVisibilityChange)
})

// Watcher para forzar re-renderización cuando cambien las respuestas
watch(
    answers,
    (newAnswers, oldAnswers) => {
        const hasSolicitanteChanges = Object.keys(newAnswers).some(
            (key) => key.startsWith('solicitante_') && newAnswers[key] !== oldAnswers?.[key],
        )

        if (hasSolicitanteChanges) {
            sectionProgressCache.value = {}
        }
        // Forzar re-renderización del componente
    },
    { deep: true },
)

// Cleanup
onUnmounted(() => {
    window.removeEventListener('resize', checkMobile)
    window.removeEventListener('beforeunload', handleBeforeUnload)
    document.removeEventListener('visibilitychange', handleVisibilityChange)
})

const getPendingSolicitanteSections = (targetIndex) => {
    const pending = []
    const sections = onboarderData.value?.sections || []
    for (let i = 0; i < Math.min(targetIndex, sections.length); i++) {
        if (!isSolicitanteSectionCompleteByIndex(i)) {
            pending.push({ index: i, name: sections[i]?.name || `Sección ${i + 1}` })
        }
    }
    return pending
}

const showBlockedSolicitanteSectionsWarning = (targetIndex) => {
    const pending = getPendingSolicitanteSections(targetIndex)
    if (pending.length === 0) {
        showNotification(
            'Completa las secciones anteriores antes de acceder a esta sección.',
            'warning',
        )
        return
    }

    const nearest = pending[pending.length - 1]
    const missing = getMissingQuestionsForSolicitanteSection(nearest.index)
    if (missing && missing.length > 0) {
        const maxShow = 4
        const items = missing.slice(0, maxShow).map((q) => ` ${q.question?.text || 'Pregunta'}`)
        const more = missing.length - maxShow
        const message = `Completa la sección "${nearest.name}":\n\n${items.join('\n')}${more > 0 ? `\n... y ${more} más` : ''}`
        showNotification(message, 'warning', 8000)
        return
    }

    const maxShow = 4
    const names = pending.slice(0, maxShow).map((p) => ` ${p.name}`)
    const more = pending.length - maxShow
    const message = `${pending.length} sección${pending.length > 1 ? 'es' : ''} pendiente${pending.length > 1 ? 's' : ''} antes de avanzar:\n\n${names.join('\n')}${more > 0 ? `\n... y ${more} más` : ''}`
    showNotification(message, 'warning', 7000)
}

const showCurrentSolicitanteSectionMissingWarning = () => {
    const missing = getMissingRequiredQuestions()
    if (!missing || missing.length === 0) {
        showNotification('Completa la sección actual antes de avanzar.', 'warning')
        return
    }
    const maxShow = 3
    const names = missing.slice(0, maxShow).map((q) => ` ${q.question?.text || 'Pregunta'}`)
    const more = missing.length - maxShow
    const message = `Faltan respuestas en esta sección:\n\n${names.join('\n')}${more > 0 ? `\n... y ${more} más` : ''}`
    showNotification(message, 'error', 8000)
}

const getMissingQuestionsForSolicitanteSection = (sectionIndex) => {
    const section = onboarderData.value?.sections?.[sectionIndex]
    if (!section || !section.questions) return []

    const getAnswerByQuestionIdForSection = (questionId) => {
        const formKey = Object.keys(answers.value).find((key) => key.endsWith(`_${questionId}`))
        if (formKey && answers.value[formKey] !== undefined) return answers.value[formKey]
        if (userAnswers.value[questionId]) {
            const ua = userAnswers.value[questionId]
            let v = ua.formatted_answer || ua.answer
            if (ua.question_slug === 'genero') v = v === 'M' ? 'Mujer' : v === 'H' ? 'Hombre' : v
            if (ua.question_slug === 'estado_civil') {
                if (v === 'Soltero') v = 'Soltero/a'
                else if (v === 'Casado') v = 'Casado/a'
                else if (v === 'Viudo') v = 'Viudo/a'
                else if (v === 'Divorciado') v = 'Divorciado/a'
            }
            return v
        }
        return undefined
    }

    const isQuestionHiddenForSection = (question) => {
        const shouldHide =
            question.hide_if_bankflip_filled && userAnswers.value[question.question?.id]
        if (shouldHide) return true
        if (
            question.show_if_bankflip_filled !== null &&
            question.show_if_bankflip_filled !== undefined
        ) {
            const isFromBankflip = checkIfUserIsFromBankflip()
            if (
                (question.show_if_bankflip_filled === 1 ||
                    question.show_if_bankflip_filled === true) &&
                !isFromBankflip
            )
                return true
            if (
                (question.show_if_bankflip_filled === 0 ||
                    question.show_if_bankflip_filled === false) &&
                isFromBankflip
            )
                return true
        }
        return false
    }

    const evaluateConditionForSectionLite = (condition) => {
        if (!condition || !condition.dependsOnQuestionId) return true
        const normalized = normalizeCondition(condition)
        const dep = getAnswerByQuestionIdForSection(normalized.dependsOnQuestionId)
        if (dep === undefined || dep === null) return false
        return evaluateCondition(normalized, getAnswerByQuestionIdForSection)
    }

    const isRequiredForSection = (question) => {
        if (isQuestionHiddenForSection(question)) return false
        if (question.requiredCondition)
            return evaluateConditionForSectionLite(question.requiredCondition)
        if (question.optionalCondition)
            return !evaluateConditionForSectionLite(question.optionalCondition)
        return true
    }

    const visible = section.questions.filter(
        (q) =>
            !isQuestionHiddenForSection(q) &&
            (!q.condition || evaluateConditionForSectionLite(q.condition)),
    )
    const missing = visible.filter((q) => {
        const qid = q.question?.id
        if (!qid) return false
        const required = isRequiredForSection(q)
        if (!required) return false
        const ans = getAnswerByQuestionIdForSection(qid)
        return ans === undefined || ans === null || ans === ''
    })
    return missing
}
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
    transition: all 0.3s ease;
}

.notification-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.notification-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.notification-move {
    transition: transform 0.3s ease;
}

.progress-bar {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: linear-gradient(to right, #4ade80, #22c55e);
    border-radius: 0.5rem;
    transition: width 0.7s ease-out;
    z-index: 1;
}
</style>
