<template>
    <div class="min-h-screen bg-gray-50 py-8">
        <!-- Header -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Gestión de Preguntas</h1>
                    <p class="text-gray-600 mt-2">Administra preguntas creando nuevas, asignando categorías o finalidades y mucho más</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="openQuestionModal()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Nueva Pregunta
                    </button>
                    <button @click="openCategoryModal()" 
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        <i class="fas fa-tag mr-2"></i>Nueva Categoría
                    </button>
                    <button @click="openPurposeModal()" 
                            class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        <i class="fas fa-bullseye mr-2"></i>Nueva Finalidad
                    </button>
                </div>
            </div>
        </div>

        <!-- Mensajes -->
        <div v-if="message" 
             :class="'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6 p-4 rounded-lg ' + (messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')">
            {{ message }}
        </div>

        <!-- Tabs -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            <div class="bg-white rounded-lg shadow-md">
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6">
                        <button @click="changeTab('questions')" 
                                :class="'tab-button py-4 px-1 border-b-2 font-medium text-sm ' + 
                                        (activeTab === 'questions' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300')">
                            <i class="fas fa-question-circle mr-2"></i>
                            Preguntas ({{ pagination.total }})
                        </button>
                        <button @click="changeTab('categories')" 
                                :class="'tab-button py-4 px-1 border-b-2 font-medium text-sm ' + 
                                        (activeTab === 'categories' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300')">
                            <i class="fas fa-tags mr-2"></i>
                            Categorías ({{ filteredCategories.length }})
                        </button>
                        <button @click="changeTab('purposes')" 
                                :class="'tab-button py-4 px-1 border-b-2 font-medium text-sm ' + 
                                        (activeTab === 'purposes' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300')">
                            <i class="fas fa-bullseye mr-2"></i>
                            Finalidades ({{ filteredPurposes.length }})
                        </button>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Tab Content: Questions -->
        <div v-if="activeTab === 'questions'" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                        <input v-model="filters.search" 
                               @input="debouncedSearch"
                               type="text" 
                               placeholder="Buscar por texto o slug..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Categorías</label>
                        <div class="relative">
                            <button 
                                @click="toggleCategoryDropdown"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-left flex justify-between items-center bg-white"
                            >
                                <span v-if="filters.categories.length === 0">Todas las categorías</span>
                                <span v-else-if="filters.categories.length === 1">
                                    {{ filters.categories[0] === 'no_category' ? 'Sin categoría' : (() => {
                                        const category = allCategoriesFlat.find(c => c.id == filters.categories[0]);
                                        return category ? category.name : 'Categoría desconocida';
                                    })() }}
                                </span>
                                <span v-else>{{ filters.categories.length }} categorías seleccionadas</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>

                            <div v-if="showCategoryDropdown" 
                                 class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <div class="p-2">
                                    <label class="flex items-center p-2 hover:bg-gray-50 cursor-pointer">
                                        <input 
                                            type="checkbox"
                                            value="no_category"
                                            v-model="filters.categories"
                                            @change="applyFilters"
                                            class="mr-2 text-blue-600 focus:ring-blue-500"
                                        >
                                        <span class="text-sm font-medium text-gray-800">Sin categoría</span>
                                    </label>
                                    <label 
                                        v-for="category in allCategoriesFlat" 
                                        :key="category.id"
                                        :class="'flex items-center p-2 hover:bg-gray-50 cursor-pointer ' + (category.level > 0 ? 'ml-4' : '')"
                                    >
                                        <input 
                                            type="checkbox"
                                            :value="category.id"
                                            v-model="filters.categories"
                                            @change="applyFilters"
                                            class="mr-2 text-blue-600 focus:ring-blue-500"
                                        >
                                        <span class="text-sm" :class="category.level === 0 ? 'font-semibold' : ''" 
                                              :style="{ paddingLeft: (category.level * 20) + 'px' }">
                                            {{ category.level > 0 ? '└─ '.repeat(category.level) : '' }}{{ category.name }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select v-model="filters.type" 
                                @change="applyFilters"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los tipos</option>
                            <option v-for="(label, value) in types" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Finalidades</label>
                        <div class="relative">
                            <div class="border border-gray-300 rounded-md p-2 min-h-[42px] cursor-pointer"
                                 @click="togglePurposeDropdown"
                                 :class="{'ring-2 ring-blue-500 border-blue-500': showPurposeDropdown}">
                                <div class="flex flex-wrap gap-1">
                                    <span v-if="filters.purposes.length === 0" class="text-gray-500 text-sm">
                                        Todas las finalidades
                                    </span>
                                    <span v-else-if="filters.purposes.includes('no_purpose') && filters.purposes.length === 1" 
                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Sin finalidad
                                    </span>
                                    <span v-else-if="filters.purposes.includes('no_purpose')" 
                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Sin finalidad
                                    </span>
                                    <span v-for="purposeId in filters.purposes.filter(id => id !== 'no_purpose')" 
                                          :key="purposeId"
                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ getPurposeName(purposeId) }}
                                    </span>
                                </div>
                                <i class="fas fa-chevron-down absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                            
                            <div v-if="showPurposeDropdown" 
                                 class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <div class="p-2">
                                    <label class="flex items-center p-2 hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" 
                                               :checked="filters.purposes.length === 0"
                                               @change="selectAllPurposes"
                                               class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Todas las finalidades</span>
                                    </label>
                                    <label class="flex items-center p-2 hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" 
                                               :checked="filters.purposes.includes('no_purpose')"
                                               @change="togglePurpose('no_purpose')"
                                               class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Sin finalidad</span>
                                    </label>
                                    <label v-for="purpose in filteredPurposes" 
                                           :key="purpose.id"
                                           class="flex items-center p-2 hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" 
                                               :checked="filters.purposes.includes(purpose.id.toString())"
                                               @change="togglePurpose(purpose.id.toString())"
                                               class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">{{ purpose.name }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button @click="clearFilters" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200">
                        <i class="fas fa-times mr-2"></i>Limpiar Filtros
                    </button>
                </div>
            </div>

            <!-- Lista de Preguntas -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Preguntas del Sistema</h3>
                </div>
                
                <div v-if="loading" class="p-8 text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-4 text-gray-600">Cargando preguntas...</p>
                </div>
                
                <div v-else-if="filteredQuestions.length === 0" class="p-8 text-center">
                    <div class="text-gray-400 text-6xl mb-4">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No se encontraron preguntas</h3>
                    <p class="text-gray-500">Ajusta los filtros o crea una nueva pregunta</p>
                </div>
                
                <div v-else class="divide-y divide-gray-200">
                    <div v-for="question in filteredQuestions" :key="question?.id || Math.random()" 
                         class="p-6 hover:bg-gray-50 transition duration-150">
                        <div v-if="question" class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="text-lg font-medium text-gray-900 truncate pr-2">{{ question.text }}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ getTypeLabel(question.type) }}
                                    </span>
                                </div>
                                
                                <div class="text-sm text-gray-600 mb-3">
                                    <span class="font-medium">Slug:</span> <span class="break-all">{{ question.slug }}</span>
                                    <span v-if="question.sub_text" class="ml-4">
                                        <span class="font-medium">Subtexto:</span> {{ question.sub_text }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <span>
                                        <i class="fas fa-tag mr-1"></i>
                                        {{ getCategoryNames(question.categories ? question.categories.map(c => c.id) : []) }}
                                    </span>
                                    <span v-if="question.purposes && question.purposes.length > 0">
                                        <i class="fas fa-bullseye mr-1 text-purple-500"></i>
                                        <span class="text-purple-600 font-medium">{{ question.purposes.map(p => p.name).join(', ') }}</span>
                                    </span>
                                    <span v-else>
                                        <i class="fas fa-bullseye mr-1 text-gray-400"></i>
                                        <span class="text-gray-400">Sin finalidad</span>
                                    </span>
                                    <span v-if="question.options && question.options.length > 0">
                                        <i class="fas fa-list mr-1"></i>
                                        {{ question.options.length }} opciones
                                    </span>
                                </div>
                                
                                <div v-if="question.questionnaires && question.questionnaires.length > 0" class="mt-3">
                                    <div class="text-sm text-gray-600 mb-2">
                                        <span class="font-medium">Usado en cuestionarios:</span>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <span 
                                            v-for="questionnaire in question.questionnaires.slice(0, 4)" 
                                            :key="questionnaire.id"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                            :class="questionnaire.ayuda ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
                                        >
                                            <i class="fas fa-clipboard-list mr-1"></i>
                                            {{ questionnaire.name }}
                                            <span v-if="questionnaire.ayuda" class="ml-1 text-blue-600">
                                                ({{ questionnaire.ayuda.nombre_ayuda }})
                                            </span>
                                        </span>
                                        <span 
                                            v-if="question.questionnaires.length > 4"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600"
                                        >
                                            +{{ question.questionnaires.length - 4 }} más
                                        </span>
                                    </div>
                                </div>
                                
                                <div v-if="question.options && question.options.length > 0" class="mt-3">
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium">Opciones:</span>
                                    </div>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        <span v-for="(option, index) in question.options" :key="index"
                                              class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ option }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex space-x-2 ml-4 flex-shrink-0">
                                <button @click="openQuestionModal(question)" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </button>
                                <button @click="deleteQuestion(question.id)" 
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                                    <i class="fas fa-trash mr-1"></i>Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Controles de paginación -->
                    <div v-if="pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} preguntas
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <button @click="prevPage" 
                                        :disabled="pagination.current_page === 1"
                                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                
                                <div class="flex space-x-1">
                                    <button v-for="page in getPageNumbers()" :key="page"
                                            @click="page !== '...' ? goToPage(page) : null"
                                            :disabled="page === '...'"
                                            :class="[
                                                'px-3 py-2 text-sm font-medium rounded-md',
                                                page === '...'
                                                    ? 'text-gray-400 cursor-default'
                                                    : page === pagination.current_page
                                                        ? 'bg-blue-600 text-white'
                                                        : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'
                                            ]">
                                        {{ page }}
                                    </button>
                                </div>
                                
                                <button @click="nextPage" 
                                        :disabled="!pagination.has_more_pages"
                                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content: Categories -->
        <div v-if="activeTab === 'categories'" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Categorías de Preguntas</h3>
                </div>
                
                <div v-if="filteredCategories.length === 0" class="p-8 text-center">
                    <div class="text-gray-400 text-6xl mb-4">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay categorías creadas</h3>
                    <p class="text-gray-500">Crea tu primera categoría para organizar las preguntas</p>
                </div>
                
                <div v-else class="divide-y divide-gray-200">
                    <!-- Categorías colapsables con jerarquía infinita -->
                    <div v-for="category in visibleCategories" :key="category.id" 
                         :class="'p-6 hover:bg-gray-50 transition duration-150 ' + 
                                (category.level === 0 ? 'bg-gray-50' : '') + 
                                (category.level > 0 ? 'pl-' + (12 + category.level * 4) + ' border-l-2 border-gray-200' : '')">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <!-- Si tiene hijos, mostrar botón de toggle -->
                                    <button v-if="category.isParent" @click="toggleCategory(category.id)" 
                                            class="flex items-center text-lg font-medium text-gray-900 hover:text-blue-600 transition duration-200">
                                        <i :class="(isCategoryExpanded(category.id) ? 'fas fa-chevron-down' : 'fas fa-chevron-right') + ' mr-2 text-gray-500'"></i>
                                        <i :class="category.icon + ' mr-2 ' + category.iconColor"></i>
                                        {{ category.name }}
                                    </button>
                                    <!-- Si no tiene hijos, mostrar solo el icono y nombre -->
                                    <h4 v-else class="text-lg font-medium text-gray-900 flex items-center">
                                        <i :class="category.icon + ' mr-2 ' + category.iconColor"></i>
                                        {{ category.name }}
                                    </h4>
                                    <span :class="'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' + 
                                                (category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')">
                                        {{ category.is_active ? 'Activa' : 'Inactiva' }}
                                    </span>
                                    <span v-if="category.level === 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Categoría Principal
                                    </span>
                                    <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Nivel {{ category.level }}
                                    </span>
                                </div>
                                
                                <p v-if="category.description" class="text-gray-600">{{ category.description }}</p>
                                
                                <div class="text-sm text-gray-500 mt-2">
                                    <i class="fas fa-clock mr-1"></i>
                                    Creada {{ new Date(category.created_at).toLocaleDateString('es-ES') }}
                                    <span v-if="category.isParent" class="ml-4">
                                        <i class="fas fa-sitemap mr-1"></i>
                                        Tiene subcategorías
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex space-x-2 ml-4">
                                <button @click="openCategoryModal(category)" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </button>
                                <button @click="deleteCategory(category.id)" 
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                                    <i class="fas fa-trash mr-1"></i>Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content: Purposes -->
        <div v-if="activeTab === 'purposes'" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Finalidades de Preguntas</h3>
                </div>
                
                <div v-if="filteredPurposes.length === 0" class="p-8 text-center">
                    <div class="text-gray-400 text-6xl mb-4">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay finalidades creadas</h3>
                    <p class="text-gray-500">Crea tu primera finalidad para clasificar las preguntas</p>
                </div>
                
                <div v-else class="divide-y divide-gray-200">
                    <div v-for="purpose in filteredPurposes" :key="purpose.id" 
                         class="p-6 hover:bg-gray-50 transition duration-150">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="text-lg font-medium text-gray-900">{{ purpose.name }}</h4>
                                    <span :class="'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' + 
                                                (purpose.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')">
                                        {{ purpose.is_active ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </div>
                                
                                <p v-if="purpose.description" class="text-gray-600">{{ purpose.description }}</p>
                                
                                <div class="text-sm text-gray-500 mt-2">
                                    <i class="fas fa-clock mr-1"></i>
                                    Creada {{ new Date(purpose.created_at).toLocaleDateString('es-ES') }}
                                </div>
                            </div>
                            
                            <div class="flex space-x-2 ml-4">
                                <button @click="openPurposeModal(purpose)" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </button>
                                <button @click="deletePurpose(purpose.id)" 
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                                    <i class="fas fa-trash mr-1"></i>Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content: Sectors -->
        <div v-if="activeTab === 'sectors'" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Sectores de Preguntas</h3>
                </div>
                
                <div v-if="filteredSectors.length === 0" class="p-8 text-center">
                    <div class="text-gray-400 text-6xl mb-4">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay sectores creados</h3>
                    <p class="text-gray-500">Crea tu primer sector para organizar las preguntas</p>
                </div>
                
                <div v-else class="divide-y divide-gray-200">
                    <div v-for="sector in filteredSectors" :key="sector.id" 
                         class="p-6 hover:bg-gray-50 transition duration-150">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="text-lg font-medium text-gray-900">{{ sector.name }}</h4>
                                    <span :class="'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' + 
                                                (sector.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')">
                                        {{ sector.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                                
                                <p v-if="sector.description" class="text-gray-600">{{ sector.description }}</p>
                                
                                <div class="text-sm text-gray-500 mt-2">
                                    <i class="fas fa-clock mr-1"></i>
                                    Creado {{ new Date(sector.created_at).toLocaleDateString('es-ES') }}
                                </div>
                            </div>
                            
                            <div class="flex space-x-2 ml-4">
                                <button @click="openSectorModal(sector)" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </button>
                                <button @click="deleteSector(sector.id)" 
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                                    <i class="fas fa-trash mr-1"></i>Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Question -->
    <div v-if="showQuestionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">
                    {{ editingQuestion ? 'Editar Pregunta' : 'Nueva Pregunta' }}
                </h3>
                <button 
                    @click="closeQuestionModal"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form @submit.prevent="saveQuestion">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Texto de la pregunta <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            v-model="questionForm.text"
                            @input="generateSlug"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="¿Cuál es tu edad?"
                            required
                        ></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Slug (se genera automáticamente)
                        </label>
                        <input 
                            v-model="questionForm.slug"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Se generará automáticamente"
                            readonly
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Texto adicional (opcional)
                        </label>
                        <textarea 
                            v-model="questionForm.sub_text"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Texto adicional o explicación de la pregunta"
                        ></textarea>
                    </div>

                    <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Texto (conviviente) — opcional
                    </label>
                    <div class="text-xs text-gray-500 mb-2">
                        <p>Si lo rellenas, este texto se usará cuando la pregunta se muestre en el flujo de convivientes.</p>
                        <p class="text-blue-600 font-medium">
                            <i class="fas fa-info-circle mr-1"></i>
                            Para usar el nombre del conviviente, incluye la variable <code class="bg-gray-100 px-1 rounded">{name}</code> en el texto.
                        </p>
                        <p class="text-gray-400">
                            Ejemplo: "¿Dónde vive {name}?" se mostrará como "¿Dónde vive María?" si el conviviente se llama María.
                        </p>
                    </div>
                    <textarea
                        v-model="questionForm.text_conviviente"
                        rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Texto alternativo cuando la pregunta se muestra para un conviviente"
                    ></textarea>
                    </div>

                    <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Subtexto (conviviente) — opcional
                    </label>
                    <div class="text-xs text-gray-500 mb-2">
                        <p>Si lo rellenas, este texto se usará cuando la pregunta se muestre en el flujo de convivientes.</p>
                        <p class="text-blue-600 font-medium">
                            <i class="fas fa-info-circle mr-1"></i>
                            Para usar el nombre del conviviente, incluye la variable <code class="bg-gray-100 px-1 rounded">{name}</code> en el texto.
                        </p>
                        <p class="text-gray-400">
                            Ejemplo: "¿Dónde vive {name}?" se mostrará como "¿Dónde vive María?" si el conviviente se llama María.
                        </p>
                    </div>
                    <textarea
                        v-model="questionForm.sub_text_conviviente"
                        rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Aclaración o ayuda extra específica para convivientes"
                    ></textarea>
                    </div>


                    
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Categorías
                            </label>
                            <div class="flex space-x-2">
                                <button type="button" 
                                        @click="selectAllCategories"
                                        class="text-xs text-blue-600 hover:text-blue-800 transition-colors">
                                    <i class="fas fa-check-double mr-1"></i>Seleccionar todo
                                </button>
                                <button type="button" 
                                        @click="deselectAllCategories"
                                        class="text-xs text-gray-600 hover:text-gray-800 transition-colors">
                                    <i class="fas fa-times mr-1"></i>Limpiar
                                </button>
                            </div>
                        </div>
                        <div class="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto bg-white">
                            <div v-if="allCategoriesFlat.length === 0" class="text-gray-500 text-sm">
                                No hay categorías disponibles
                            </div>
                            <div v-else class="space-y-2">
                                <label class="flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded transition-colors">
                                    <input type="checkbox" 
                                           value="no_category"
                                           v-model="questionForm.category_ids"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                                    <span class="text-sm font-medium text-gray-800">Sin categoría</span>
                                </label>
                                <label v-for="category in allCategoriesFlat" :key="category.id" 
                                       :class="'flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded transition-colors ' + (category.level > 0 ? 'ml-4' : '')">
                                    <input type="checkbox" 
                                           :value="category.id"
                                           v-model="questionForm.category_ids"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                                    <span class="text-sm text-gray-700 flex-1" 
                                          :class="category.level === 0 ? 'font-semibold' : ''"
                                          :style="{ paddingLeft: (category.level * 20) + 'px' }">
                                        {{ category.level > 0 ? '└─ '.repeat(category.level) : '' }}{{ category.name }}
                                    </span>
                                    <span v-if="category.description" class="text-xs text-gray-500 truncate max-w-32">
                                        {{ category.description }}
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div v-if="questionForm.category_ids.length > 0" class="mt-2 text-xs text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-1"></i>
                            {{ questionForm.category_ids.length }} categoría(s) seleccionada(s)
                        </div>
                        <div v-else class="mt-2 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Selecciona una o más categorías
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Finalidades
                        </label>
                        <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-300 rounded-md p-3">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       :checked="questionForm.purpose_ids.length === 0"
                                       @change="selectAllPurposesForQuestion"
                                       class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">Sin finalidad</span>
                            </label>
                            <label v-for="purpose in filteredPurposes" 
                                   :key="purpose.id"
                                   class="flex items-center">
                                <input type="checkbox" 
                                       :value="purpose.id"
                                       v-model="questionForm.purpose_ids"
                                       class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">{{ purpose.name }}</span>
                            </label>
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Selecciona las finalidades de esta pregunta (opcional)
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de pregunta <span class="text-red-500">*</span>
                        </label>
                        <select 
                            v-model="questionForm.type"
                            @change="handleQuestionTypeChange"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required
                        >
                            <option value="">Seleccionar tipo</option>
                            <option v-for="(label, value) in types" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>
                    
                    <div v-if="['select', 'multiple'].includes(questionForm.type)">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Opciones <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <div 
                                v-for="(option, index) in questionForm.options" 
                                :key="index"
                                class="flex items-center space-x-2"
                            >
                                <input 
                                    v-model="questionForm.options[index]"
                                    type="text"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :placeholder="`Opción ${index + 1}`"
                                    required
                                >
                                <button 
                                    @click="removeOption(index)"
                                    type="button"
                                    class="p-2 text-red-400 hover:text-red-600"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <button 
                                @click="addOption"
                                type="button"
                                class="px-3 py-2 text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                <i class="fas fa-plus mr-1"></i>Añadir opción
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button 
                        @click="closeQuestionModal"
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
                        {{ saving ? 'Guardando...' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Category -->
    <div v-if="showCategoryModal" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ editingCategory ? 'Editar Categoría' : 'Nueva Categoría' }}
                </h3>
            </div>
            
            <form @submit.prevent="saveCategory" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                    <input v-model="categoryForm.name" 
                           type="text" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <textarea v-model="categoryForm.description" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Categoría padre (opcional)</label>
                    <select v-model="categoryForm.parent_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option :value="null">Sin categoría padre (categoría principal)</option>
                        <option v-for="category in allCategoriesFlat" 
                                :key="category.id" 
                                :value="category.id"
                                :disabled="editingCategory && editingCategory.id === category.id">
                            {{ '└─ '.repeat(category.level) }}{{ category.name }}
                        </option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        Deja vacío para crear una categoría principal, o selecciona una categoría existente para crear una subcategoría
                    </p>
                </div>
                
                <div class="flex items-center">
                    <input v-model="categoryForm.is_active" 
                           type="checkbox" 
                           id="category-active"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="category-active" class="ml-2 block text-sm text-gray-900">
                        Categoría activa
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button @click="closeCategoryModal" 
                            type="button"
                            class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition duration-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            :disabled="saving"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition duration-200 disabled:opacity-50">
                        <span v-if="saving">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Guardando...
                        </span>
                        <span v-else>
                            <i class="fas fa-save mr-2"></i>Guardar
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Purpose -->
    <div v-if="showPurposeModal" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ editingPurpose ? 'Editar Finalidad' : 'Nueva Finalidad' }}
                </h3>
            </div>
            
            <form @submit.prevent="savePurpose" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                    <input v-model="purposeForm.name" 
                           type="text" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <textarea v-model="purposeForm.description" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="flex items-center">
                    <input v-model="purposeForm.is_active" 
                           type="checkbox" 
                           id="purpose-active"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="purpose-active" class="ml-2 block text-sm text-gray-900">
                        Finalidad activa
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button @click="closePurposeModal" 
                            type="button"
                            class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition duration-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            :disabled="saving"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition duration-200 disabled:opacity-50">
                        <span v-if="saving">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Guardando...
                        </span>
                        <span v-else>
                            <i class="fas fa-save mr-2"></i>Guardar
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, defineComponent } from 'vue';

// Estado reactivo
const activeTab = ref('questions');
const questions = ref([]);
const categories = ref([]);
const purposes = ref([]);
const types = ref([]);
const expandedCategories = ref(new Set());
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0,
    has_more_pages: false
});

// Filtros
const filters = reactive({
    search: '',
    categories: [],
    type: '',
    purposes: []
});

// Estados de modales
const showQuestionModal = ref(false);
const showCategoryModal = ref(false);
const showPurposeModal = ref(false);
const showSectorModal = ref(false);
const editingQuestion = ref(null);
const editingCategory = ref(null);
const editingPurpose = ref(null);
const editingSector = ref(null);

// Estados de dropdowns
const showCategoryDropdown = ref(false);
const showPurposeDropdown = ref(false);

// Estados de carga
const loading = ref(false);
const saving = ref(false);

// Timeout para debounce del buscador
const searchTimeout = ref(null);

// Mensajes
const message = ref('');
const messageType = ref('success');

// Formularios
const questionForm = reactive({
    slug: '',
    text: '',
    sub_text: '',
    text_conviviente: '',
    sub_text_conviviente: '',
    type: '',
    options: [],
    category_ids: [],
    purpose_ids: [],
    regex_id: null
});

const categoryForm = reactive({
    name: '',
    description: '',
    is_active: true,
    parent_id: null
});

const sectorForm = reactive({
    name: '',
    description: '',
    is_active: true
});

const purposeForm = reactive({
    name: '',
    description: '',
    is_active: true
});

// Computed properties
// Con paginación del servidor, filteredQuestions simplemente devuelve las preguntas cargadas
const filteredQuestions = computed(() => {
    return questions.value;
});

const filteredCategories = computed(() => {
    console.log('Categorías originales:', categories.value);
    
    // Usar directamente la estructura jerárquica del backend
    if (categories.value.length > 0) {
        // Si ya viene en formato jerárquico, usarlo directamente
        if (categories.value[0].children !== undefined) {
            console.log('Categorías filtradas (jerárquico):', categories.value);
            return categories.value;
        }
        
        // Si viene en formato plano, crear estructura jerárquica completa
        const parents = categories.value.filter(cat => !cat.parent_id && cat.is_active);
        
        const buildHierarchy = (parentId) => {
            const children = categories.value.filter(cat => cat.parent_id === parentId && cat.is_active);
            return children.map(child => ({
                ...child,
                children: buildHierarchy(child.id)
            }));
        };
        
        const hierarchical = parents.map(parent => ({
            ...parent,
            children: buildHierarchy(parent.id)
        }));
        
        console.log('Categorías filtradas (plano a jerárquico):', hierarchical);
        return hierarchical;
    }
    
    return [];
});

const filteredPurposes = computed(() => {
    return purposes.value.filter(purpose => purpose.is_active);
});

// Computed property para mostrar solo las categorías visibles según el estado de expansión
const visibleCategories = computed(() => {
    const result = [];
    
    for (const parent of filteredCategories.value) {
        result.push(...renderVisibleCategories(parent));
    }
    
    console.log('Categorías visibles:', result);
    return result;
});

// Computed property para aplanar toda la jerarquía (para selectores)
const allCategoriesFlat = computed(() => {
    const result = [];
    
    const flattenCategory = (category, level = 0) => {
        result.push({
            ...category,
            level
        });
        
        if (category.children && category.children.length > 0) {
            for (const child of category.children) {
                flattenCategory(child, level + 1);
            }
        }
    };
    
    for (const parent of filteredCategories.value) {
        flattenCategory(parent);
    }
    
    return result;
});

// Métodos
const initializeData = async () => {
    // Cargar preguntas con paginación
    await loadQuestions();
    
    // Cargar categorías, finalidades y tipos
    await loadCategories();
    await loadPurposes();
    
    // Cargar tipos desde el DOM si están disponibles
    const app = document.getElementById('questions-app');
    if (app) {
        types.value = JSON.parse(app.dataset.types || '[]');
    }
};

const changeTab = (tab) => {
    activeTab.value = tab;
};

// Toggle del dropdown de categorías
const toggleCategoryDropdown = () => {
    showCategoryDropdown.value = !showCategoryDropdown.value;
};

const showMessage = (msg, type = 'success') => {
    message.value = msg;
    messageType.value = type;
    setTimeout(() => {
        message.value = '';
    }, 5000);
};

// Gestión de preguntas
const openQuestionModal = (question = null) => {
    if (question) {
        editingQuestion.value = question;
        Object.assign(questionForm, {
            slug: question.slug,
            text: question.text,
            sub_text: question.sub_text || '',
            text_conviviente: question.text_conviviente || '',
            sub_text_conviviente: question.sub_text_conviviente || '',
            type: question.type,
            options: question.options || [],
            category_ids: question.categories ? question.categories.map(c => c.id) : [],
            purpose_ids: question.purposes ? question.purposes.map(p => p.id) : [],
            regex_id: question.regex_id
        });
    } else {
        editingQuestion.value = null;
        Object.assign(questionForm, {
            slug: '',
            text: '',
            sub_text: '',
            text_conviviente: '',
            sub_text_conviviente: '',
            type: '',
            options: [],
            category_ids: [],
            purpose_ids: [],
            regex_id: null
        });
    }
    showQuestionModal.value = true;
};

const closeQuestionModal = () => {
    showQuestionModal.value = false;
    editingQuestion.value = null;
};

const saveQuestion = async () => {
    if (!questionForm.slug || !questionForm.text || !questionForm.type) {
        showMessage('Por favor, completa todos los campos obligatorios', 'error');
        return;
    }
    
    saving.value = true;
    
    try {
        const url = editingQuestion.value 
            ? `/admin/questions/${editingQuestion.value.id}` 
            : '/admin/questions';
        
        const method = editingQuestion.value ? 'PATCH' : 'POST';
        

        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(questionForm)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(data.message || 'Pregunta guardada correctamente');
            closeQuestionModal();
            // Mantener la página actual al recargar
            await loadQuestions(pagination.value.current_page);
        } else {
            showMessage(data.message || 'Error al guardar la pregunta', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error al guardar la pregunta', 'error');
    } finally {
        saving.value = false;
    }
};

const deleteQuestion = async (questionId) => {
    if (!confirm('¿Estás seguro de que quieres eliminar esta pregunta? Esta acción no se puede deshacer.')) {
        return;
    }
    
    try {
        const response = await fetch(`/admin/questions/${questionId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Pregunta eliminada correctamente');
            // Mantener la página actual al recargar
            await loadQuestions(pagination.value.current_page);
        } else {
            showMessage(data.message || 'Error al eliminar la pregunta', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error al eliminar la pregunta', 'error');
    }
};

const loadQuestions = async (page = 1) => {
    loading.value = true;
    try {
        // Construir URL con filtros
        const params = new URLSearchParams();
        params.append('page', page);
        
        if (filters.search) params.append('search', filters.search);
        if (filters.categories && filters.categories.length > 0) {
            filters.categories.forEach(categoryId => {
                params.append('categories[]', categoryId);
            });
        }
        if (filters.type) params.append('type', filters.type);
        if (filters.purposes.length > 0) {
            filters.purposes.forEach(purposeId => {
                params.append('purposes[]', purposeId);
            });
        }
        
        const url = `/admin/questions/list?${params.toString()}`; 
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        if (data.success) {
            questions.value = data.questions || [];
            if (data.pagination) {
                pagination.value = data.pagination;
            }
        }
    } catch (error) {
        console.error('Error al cargar preguntas:', error);
        showMessage('Error al cargar las preguntas', 'error');
    } finally {
        loading.value = false;
    }
};

// Funciones de paginación
const goToPage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        loadQuestions(page);
    }
};

const nextPage = () => {
    if (pagination.value.has_more_pages) {
        goToPage(pagination.value.current_page + 1);
    }
};

const prevPage = () => {
    if (pagination.value.current_page > 1) {
        goToPage(pagination.value.current_page - 1);
    }
};

const getPageNumbers = () => {
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;
    const pages = [];
    
    if (last <= 7) {
        // Si hay 7 páginas o menos, mostrar todas
        for (let i = 1; i <= last; i++) {
            pages.push(i);
        }
    } else {
        // Mostrar primeras páginas, actual y últimas
        if (current <= 4) {
            // Estamos cerca del inicio
            for (let i = 1; i <= 5; i++) {
                pages.push(i);
            }
            pages.push('...');
            pages.push(last);
        } else if (current >= last - 3) {
            // Estamos cerca del final
            pages.push(1);
            pages.push('...');
            for (let i = last - 4; i <= last; i++) {
                pages.push(i);
            }
        } else {
            // Estamos en el medio
            pages.push(1);
            pages.push('...');
            for (let i = current - 1; i <= current + 1; i++) {
                pages.push(i);
            }
            pages.push('...');
            pages.push(last);
        }
    }
    
    return pages;
};

// Funciones de filtrado
const applyFilters = () => {
    // Volver a la primera página cuando se aplican filtros
    loadQuestions(1);
};

// Función debounced para el buscador
const debouncedSearch = () => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }
    searchTimeout.value = setTimeout(() => {
        applyFilters();
    }, 700);
};

const clearFilters = () => {
    filters.search = '';
    filters.categories = [];
    filters.type = '';
    filters.purposes = [];
    showCategoryDropdown.value = false;
    showPurposeDropdown.value = false;
    loadQuestions(1);
};

// Funciones para manejo de categorías
const selectAllCategories = () => {
    questionForm.category_ids = allCategoriesFlat.value.map(cat => cat.id);
};

const deselectAllCategories = () => {
    questionForm.category_ids = [];
};

const togglePurposeDropdown = () => {
    showPurposeDropdown.value = !showPurposeDropdown.value;
};

const togglePurpose = (purposeId) => {
    const index = filters.purposes.indexOf(purposeId);
    if (index > -1) {
        filters.purposes.splice(index, 1);
    } else {
        filters.purposes.push(purposeId);
    }
    applyFilters();
};

const selectAllPurposes = () => {
    filters.purposes = [];
    applyFilters();
};

const getPurposeName = (purposeId) => {
    const purpose = filteredPurposes.value.find(p => p.id.toString() === purposeId);
    return purpose ? purpose.name : purposeId;
};

const selectAllPurposesForQuestion = () => {
    questionForm.purpose_ids = [];
};

// Gestión de categorías
const openCategoryModal = (category = null) => {
    if (category) {
        editingCategory.value = category;
        Object.assign(categoryForm, {
            name: category.name,
            description: category.description || '',
            is_active: category.is_active,
            parent_id: category.parent_id || null
        });
    } else {
        editingCategory.value = null;
        Object.assign(categoryForm, {
            name: '',
            description: '',
            is_active: true,
            parent_id: null
        });
    }
    showCategoryModal.value = true;
};

const closeCategoryModal = () => {
    showCategoryModal.value = false;
    editingCategory.value = null;
};

const saveCategory = async () => {
    if (!categoryForm.name) {
        showMessage('Por favor, ingresa el nombre de la categoría', 'error');
        return;
    }
    
    saving.value = true;
    
    try {
        const url = editingCategory.value 
            ? `/admin/question-categories/${editingCategory.value.id}` 
            : '/admin/question-categories';
        
        const method = editingCategory.value ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(categoryForm)
        });
        
        const data = await response.json();
        console.log('Respuesta del servidor:', response.status, data);
        
        if (data.success) {
            showMessage(data.message || 'Categoría guardada correctamente');
            closeCategoryModal();
            // Recargar categorías para mostrar la nueva estructura jerárquica
            await loadCategories();
        } else {
            showMessage(data.message || 'Error al guardar la categoría', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error al guardar la categoría', 'error');
    } finally {
        saving.value = false;
    }
};

const deleteCategory = async (categoryId) => {
    if (!confirm('¿Estás seguro de que quieres eliminar esta categoría? Esta acción no se puede deshacer.')) {
        return;
    }
    
    try {
        const response = await fetch(`/admin/question-categories/${categoryId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Categoría eliminada correctamente');
            await loadCategories();
        } else {
            showMessage(data.message || 'Error al eliminar la categoría', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error al eliminar la categoría', 'error');
    }
};

const loadCategories = async () => {
    try {
        const response = await fetch('/admin/question-categories?hierarchical=true');
        const data = await response.json();
        console.log('Respuesta completa del backend:', data);
        if (data.success) {
            categories.value = data.categories || [];
            console.log('Categorías cargadas:', categories.value);
        }
    } catch (error) {
        console.error('Error al cargar categorías:', error);
    }
};

const loadPurposes = async () => {
    try {
        const response = await fetch('/admin/question-purposes');
        const data = await response.json();
        if (data.success) {
            purposes.value = data.purposes || [];
            console.log('Finalidades cargadas:', purposes.value);
        }
    } catch (error) {
        console.error('Error al cargar finalidades:', error);
    }
};

// Gestión de finalidades
const openPurposeModal = (purpose = null) => {
    if (purpose) {
        editingPurpose.value = purpose;
        Object.assign(purposeForm, {
            name: purpose.name,
            description: purpose.description || '',
            is_active: purpose.is_active
        });
    } else {
        editingPurpose.value = null;
        Object.assign(purposeForm, {
            name: '',
            description: '',
            is_active: true
        });
    }
    showPurposeModal.value = true;
};

const closePurposeModal = () => {
    showPurposeModal.value = false;
    editingPurpose.value = null;
};

const savePurpose = async () => {
    if (!purposeForm.name) {
        showMessage('Por favor, ingresa el nombre de la finalidad', 'error');
        return;
    }
    
    saving.value = true;
    
    try {
        const url = editingPurpose.value 
            ? `/admin/question-purposes/${editingPurpose.value.id}` 
            : '/admin/question-purposes';
        
        const method = editingPurpose.value ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(purposeForm)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(data.message || 'Finalidad guardada correctamente');
            closePurposeModal();
            await loadPurposes();
        } else {
            showMessage(data.message || 'Error al guardar la finalidad', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error al guardar la finalidad', 'error');
    } finally {
        saving.value = false;
    }
};

const deletePurpose = async (purposeId) => {
    if (!confirm('¿Estás seguro de que quieres eliminar esta finalidad? Esta acción no se puede deshacer.')) {
        return;
    }
    
    try {
        const response = await fetch(`/admin/question-purposes/${purposeId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Finalidad eliminada correctamente');
            await loadPurposes();
        } else {
            showMessage(data.message || 'Error al eliminar la finalidad', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error al eliminar la finalidad', 'error');
    }
};

// Utilidades
const addOption = () => {
    questionForm.options.push('');
};

const removeOption = (index) => {
    questionForm.options.splice(index, 1);
};

const generateSlug = () => {
    if (questionForm.text) {
        let slug = questionForm.text
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '') // Remove accents
            .replace(/[^a-z0-9\s]/g, '') // Remove special characters except spaces
            .replace(/\s+/g, '_') // Replace spaces with underscores
            .replace(/_+/g, '_') // Replace multiple underscores with single
            .replace(/^_|_$/g, ''); // Remove leading/trailing underscores
        
        questionForm.slug = slug;
    }
};

const handleQuestionTypeChange = () => {
    if (['select', 'multiple'].includes(questionForm.type)) {
        if (questionForm.options.length === 0) {
            questionForm.options = ['', '']; // Al menos 2 opciones
        }
    } else {
        questionForm.options = [];
    }
};

const getCategoryNames = (categoryIds) => {
    if (!categoryIds || categoryIds.length === 0) {
        return 'Sin categorías';
    }
    const categoryNames = categoryIds.map(id => {
        const category = categories.value.find(c => c.id == id);
        return category ? category.name : 'Categoría desconocida';
    });
    return categoryNames.join(', ');
};

const getTypeLabel = (type) => {
    // Si types es un array de objetos con value y label
    if (Array.isArray(types.value) && types.value.length > 0 && typeof types.value[0] === 'object') {
        const typeObj = types.value.find(t => t.value === type);
        return typeObj ? typeObj.label : type;
    }
    // Si types es un objeto simple con clave-valor
    return types.value[type] || type;
};


// Métodos para manejar categorías colapsables
const toggleCategory = (categoryId) => {
    if (expandedCategories.value.has(categoryId)) {
        expandedCategories.value.delete(categoryId);
    } else {
        expandedCategories.value.add(categoryId);
    }
};

const isCategoryExpanded = (categoryId) => {
    return expandedCategories.value.has(categoryId);
};

// Función recursiva para renderizar categorías visibles según el estado de expansión
const renderVisibleCategories = (category, level = 0) => {
    const result = [];
    
    // Agregar la categoría actual
    const isParent = category.children && category.children.length > 0;
    const icon = isParent ? 'fas fa-folder' : 'fas fa-folder-open';
    const iconColor = level === 0 ? 'text-blue-600' : 'text-gray-500';
    
    result.push({
        ...category,
        icon,
        iconColor,
        level,
        isParent
    });
    
    // Solo agregar subcategorías si la categoría actual está expandida
    if (category.children && category.children.length > 0 && isCategoryExpanded(category.id)) {
        for (const child of category.children) {
            result.push(...renderVisibleCategories(child, level + 1));
        }
    }
    
    return result;
};

// Lifecycle
onMounted(() => {
    initializeData();
    
    // Cerrar dropdowns al hacer clic fuera
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.relative')) {
            showCategoryDropdown.value = false;
            showPurposeDropdown.value = false;
        }
    });
});

onUnmounted(() => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }
});
</script>

<style scoped>
.tab-button {
    transition: all 0.3s ease;
}

.tab-button.active {
    background-color: #3b82f6;
    color: white;
}

.modal-overlay {
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.card-hover {
    transition: all 0.3s ease;
}

.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}
</style>
