<template>
    <div class="min-h-screen bg-gray-50 py-8">
        <!-- Spinner de página completa durante la carga -->
        <div
            v-if="loadingEdit"
            class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg p-8 flex flex-col items-center">
                <svg
                    class="animate-spin h-12 w-12 text-blue-600 mb-4"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                </svg>
                <p class="text-lg font-medium text-gray-900">
                    Cargando editor...
                </p>
                <p class="text-sm text-gray-600 mt-2">
                    Por favor espera mientras se cargan los datos
                </p>
            </div>
        </div>

        <div
            v-if="loadingEditConditions"
            class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg p-8 flex flex-col items-center">
                <svg
                    class="animate-spin h-12 w-12 text-blue-600 mb-4"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                </svg>
                <p class="text-lg font-medium text-gray-900">
                    Cargando editor de condiciones...
                </p>
                <p class="text-sm text-gray-600 mt-2">
                    Por favor espera mientras se cargan los datos
                </p>
            </div>
        </div>

        <!-- Mostrar el componente de edición cuando esté activo -->
        <EditarRequisitosComponent
            v-if="showEditComponent"
            :item-id="editingItem.id"
            :item-type="editingItem.type"
            :item-name="editingItem.name"
            :questions="editingItem.questions || []"
            :all-questions="editingItem.allQuestions || []"
            :existing-requirements="editingItem.existingRequirements || []"
            @go-back="goBackToMain"
            @requirements-updated="handleRequirementsUpdated"
        />

        <EditarCondicionesComponent
            v-if="showEditConditions"
            :item-id="editingConditionsItem.id"
            :item-type="editingConditionsItem.type"
            :item-name="editingConditionsItem.name"
            :questions="editingConditionsItem.questions || []"
            :all-questions="editingConditionsItem.allQuestions || []"
            :existing-conditions="
                editingConditionsItem.existingConditions || []
            "
            @go-back="goBackFromConditions"
            @conditions-updated="handleConditionsUpdated"
        />

        <!-- Mostrar la vista principal cuando no esté editando -->
        <div
            v-if="!showEditComponent && !showEditConditions"
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
        >
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Gestión de Ayudas y Cuestionarios
                </h1>
                <p class="mt-2 text-gray-600">
                    Administra ayudas y cuestionarios
                </p>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex w-full">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            activeTab === tab.id
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'flex-1 py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
                        ]"
                    >
                        <div class="flex items-center justify-center space-x-2">
                            <span>{{ tab.icon }}</span>
                            <span>{{ tab.label }}</span>
                            <span
                                v-if="tab.count"
                                class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs"
                            >
                                {{ tab.count }}
                            </span>
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <transition name="tab-fade" mode="out-in">
                <div
                    v-if="activeTab === 'ayudas'"
                    key="ayudas"
                    class="space-y-6"
                >
                    <!-- Search and Filters -->
                    <div
                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                    >
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <label
                                    for="search"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Buscar ayudas</label
                                >
                                <div class="relative">
                                    <input
                                        id="search"
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Buscar por nombre, descripción..."
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                    >
                                        <svg
                                            class="h-5 w-5 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="sm:w-48">
                                <label
                                    for="status"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Estado</label
                                >
                                <select
                                    id="status"
                                    v-model="statusFilter"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Todos los estados</option>
                                    <option value="activa">Activa</option>
                                    <option value="inactiva">Inactiva</option>
                                </select>
                            </div>
                            <div class="sm:w-48">
                                <label
                                    for="organo"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Órgano</label
                                >
                                <select
                                    id="organo"
                                    v-model="organoFilter"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Todos los órganos</option>
                                    <option
                                        v-for="organo in organos"
                                        :key="organo.id"
                                        :value="organo.id"
                                    >
                                        {{ organo.nombre_organismo }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div
                        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Lista de Ayudas
                                </h3>
                            </div>
                        </div>

                        <!-- Loading State -->
                        <div v-if="loading" class="px-6 py-12 text-center">
                            <div
                                class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-blue-600"
                            >
                                <svg
                                    class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Cargando ayudas...
                            </div>
                        </div>

                        <!-- Table Content -->
                        <div
                            v-else-if="paginatedAyudas.length > 0"
                            class="table-container"
                        >
                            <table
                                class="min-w-full divide-y divide-gray-200 table-fixed"
                            >
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            v-for="column in columns"
                                            :key="column.key"
                                            @click="sortBy(column.key)"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                        >
                                            <div
                                                class="flex items-center space-x-1"
                                            >
                                                <span>{{ column.label }}</span>
                                                <svg
                                                    v-if="
                                                        sortKey === column.key
                                                    "
                                                    class="h-4 w-4"
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path
                                                        v-if="
                                                            sortOrder === 'asc'
                                                        "
                                                        fill-rule="evenodd"
                                                        d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                    <path
                                                        v-else
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </div>
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr
                                        v-for="ayuda in paginatedAyudas"
                                        :key="ayuda.id"
                                        class="hover:bg-gray-50 transition-colors duration-150"
                                    >
                                        <td class="px-6 py-4">
                                            <div
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ ayuda.nombre }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ID: {{ ayuda.id }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div
                                                class="text-sm text-gray-900 max-w-xs"
                                            >
                                                <div class="line-clamp-3">
                                                    {{ ayuda.descripcion }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="
                                                    getStatusClasses(
                                                        ayuda.activo,
                                                    )
                                                "
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                            >
                                                {{
                                                    ayuda.activo == 1
                                                        ? "Activa"
                                                        : "Inactiva"
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                        >
                                            {{
                                                ayuda.organo
                                                    ?.nombre_organismo || "N/A"
                                            }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                        >
                                            <span
                                                v-if="ayuda.questionnaire_id"
                                                class="text-blue-600 hover:text-blue-800 cursor-pointer underline"
                                                @click="
                                                    goToCuestionario(
                                                        ayuda.questionnaire_id,
                                                    )
                                                "
                                            >
                                                Ver cuestionario
                                            </span>
                                            <span v-else class="text-gray-400"
                                                >Sin cuestionario</span
                                            >
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                        >
                                            {{
                                                formatDate(
                                                    ayuda.create_time ||
                                                        ayuda.created_at,
                                                )
                                            }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                        >
                                            <button
                                                @click="
                                                    editAyudaRequisitos(ayuda)
                                                "
                                                class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-blue-600 rounded hover:bg-blue-50 transition"
                                            >
                                                Editar requisitos
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="px-6 py-12 text-center">
                            <svg
                                class="mx-auto h-12 w-12 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">
                                No se encontraron ayudas
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Intenta ajustar los filtros de búsqueda.
                            </p>
                        </div>

                        <!-- Pagination -->
                        <div
                            v-if="totalPages > 1"
                            class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
                        >
                            <div class="flex-1 flex justify-between sm:hidden">
                                <button
                                    @click="previousPage"
                                    :disabled="currentPage === 1"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Anterior
                                </button>
                                <button
                                    @click="nextPage"
                                    :disabled="currentPage === totalPages"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Siguiente
                                </button>
                            </div>
                            <div
                                class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between"
                            >
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Mostrando
                                        <span class="font-medium">{{
                                            startIndex + 1
                                        }}</span>
                                        a
                                        <span class="font-medium">{{
                                            endIndex
                                        }}</span>
                                        de
                                        <span class="font-medium">{{
                                            filteredAyudas.length
                                        }}</span>
                                        resultados
                                    </p>
                                </div>
                                <div>
                                    <nav
                                        class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                        aria-label="Pagination"
                                    >
                                        <button
                                            @click="previousPage"
                                            :disabled="currentPage === 1"
                                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <span class="sr-only"
                                                >Anterior</span
                                            >
                                            <svg
                                                class="h-5 w-5"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>

                                        <template
                                            v-for="page in visiblePages"
                                            :key="page"
                                        >
                                            <button
                                                v-if="page !== '...'"
                                                @click="goToPage(page)"
                                                :class="[
                                                    page === currentPage
                                                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                ]"
                                            >
                                                {{ page }}
                                            </button>
                                            <span
                                                v-else
                                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                                            >
                                                ...
                                            </span>
                                        </template>

                                        <button
                                            @click="nextPage"
                                            :disabled="
                                                currentPage === totalPages
                                            "
                                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <span class="sr-only"
                                                >Siguiente</span
                                            >
                                            <svg
                                                class="h-5 w-5"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else-if="activeTab === 'cuestionarios'"
                    key="cuestionarios"
                    class="space-y-6"
                >
                    <div
                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                    >
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <label
                                    for="search-cuestionarios"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Buscar cuestionarios</label
                                >
                                <div class="relative">
                                    <input
                                        id="search-cuestionarios"
                                        v-model="searchCuestionarios"
                                        type="text"
                                        placeholder="Buscar por nombre..."
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                    >
                                        <svg
                                            class="h-5 w-5 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="sm:w-48">
                                <label
                                    for="status-cuestionarios"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Estado</label
                                >
                                <select
                                    id="status-cuestionarios"
                                    v-model="statusFilterCuestionarios"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Todos los estados</option>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Lista de Cuestionarios
                                </h3>
                            </div>
                        </div>

                        <div
                            v-if="filteredCuestionarios.length > 0"
                            class="table-container"
                        >
                            <table
                                class="min-w-full divide-y divide-gray-200 table-fixed"
                            >
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            @click="
                                                sortByCuestionarios('nombre')
                                            "
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                        >
                                            <div
                                                class="flex items-center space-x-1"
                                            >
                                                <span>Cuestionario</span>
                                                <svg
                                                    v-if="
                                                        sortKeyCuestionarios ===
                                                        'nombre'
                                                    "
                                                    class="h-4 w-4"
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path
                                                        v-if="
                                                            sortOrderCuestionarios ===
                                                            'asc'
                                                        "
                                                        fill-rule="evenodd"
                                                        d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                    <path
                                                        v-else
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </div>
                                        </th>
                                        <th
                                            @click="
                                                sortByCuestionarios('active')
                                            "
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                        >
                                            <div
                                                class="flex items-center space-x-1"
                                            >
                                                <span>Estado</span>
                                                <svg
                                                    v-if="
                                                        sortKeyCuestionarios ===
                                                        'active'
                                                    "
                                                    class="h-4 w-4"
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path
                                                        v-if="
                                                            sortOrderCuestionarios ===
                                                            'asc'
                                                        "
                                                        fill-rule="evenodd"
                                                        d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                    <path
                                                        v-else
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </div>
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Ayuda
                                        </th>
                                        <th
                                            @click="
                                                sortByCuestionarios(
                                                    'created_at',
                                                )
                                            "
                                            class="px-6 py-3 text-left text-xs font-medium text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                        >
                                            <div
                                                class="flex items-center space-x-1"
                                            >
                                                <span>Fecha Creación</span>
                                                <svg
                                                    v-if="
                                                        sortKeyCuestionarios ===
                                                        'created_at'
                                                    "
                                                    class="h-4 w-4"
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path
                                                        v-if="
                                                            sortOrderCuestionarios ===
                                                            'asc'
                                                        "
                                                        fill-rule="evenodd"
                                                        d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                    <path
                                                        v-else
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </div>
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr
                                        v-for="q in paginatedCuestionarios"
                                        :key="q.id"
                                        class="hover:bg-gray-50 transition-colors duration-150"
                                    >
                                        <td class="px-6 py-4">
                                            <div
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ q.nombre }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ID: {{ q.id }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="
                                                    getStatusClasses(q.active)
                                                "
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                                >{{
                                                    q.active == 1
                                                        ? "Activo"
                                                        : "Inactivo"
                                                }}</span
                                            >
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                        >
                                            <span
                                                v-if="getAyudaRelacionada(q.id)"
                                                class="text-blue-600 hover:text-blue-800 cursor-pointer underline"
                                                @click="
                                                    goToAyuda(
                                                        getAyudaRelacionada(
                                                            q.id,
                                                        ).id,
                                                    )
                                                "
                                            >
                                                Ver ayuda
                                            </span>
                                            <span v-else class="text-gray-400"
                                                >Sin ayuda</span
                                            >
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                        >
                                            {{ formatDate(q.created_at) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-left text-sm font-medium"
                                        >
                                            <button
                                                @click="
                                                    editCuestionarioCondiciones(
                                                        q,
                                                    )
                                                "
                                                class="inline-flex items-center px-3 py-1.5 border border-indigo-600 text-indigo-600 rounded hover:bg-indigo-50 transition"
                                            >
                                                Editar condiciones
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="px-6 py-12 text-center">
                            <svg
                                class="mx-auto h-12 w-12 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">
                                No se encontraron cuestionarios
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Intenta ajustar los filtros de búsqueda.
                            </p>
                        </div>

                        <!-- Pagination for Cuestionarios -->
                        <div
                            v-if="totalPagesCuestionarios > 1"
                            class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
                        >
                            <div class="flex-1 flex justify-between sm:hidden">
                                <button
                                    @click="previousPageCuestionarios"
                                    :disabled="currentPageCuestionarios === 1"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Anterior
                                </button>
                                <button
                                    @click="nextPageCuestionarios"
                                    :disabled="
                                        currentPageCuestionarios ===
                                        totalPagesCuestionarios
                                    "
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Siguiente
                                </button>
                            </div>
                            <div
                                class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between"
                            >
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Mostrando
                                        <span class="font-medium">{{
                                            startIndexCuestionarios + 1
                                        }}</span>
                                        a
                                        <span class="font-medium">{{
                                            endIndexCuestionarios
                                        }}</span>
                                        de
                                        <span class="font-medium">{{
                                            filteredCuestionarios.length
                                        }}</span>
                                        resultados
                                    </p>
                                </div>
                                <div>
                                    <nav
                                        class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                        aria-label="Pagination"
                                    >
                                        <button
                                            @click="previousPageCuestionarios"
                                            :disabled="
                                                currentPageCuestionarios === 1
                                            "
                                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <span class="sr-only"
                                                >Anterior</span
                                            >
                                            <svg
                                                class="h-5 w-5"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                        <template
                                            v-for="page in visiblePagesCuestionarios"
                                            :key="page"
                                        >
                                            <button
                                                v-if="page !== '...'"
                                                @click="
                                                    goToPageCuestionarios(page)
                                                "
                                                :class="[
                                                    page ===
                                                    currentPageCuestionarios
                                                        ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                ]"
                                            >
                                                {{ page }}
                                            </button>
                                            <span
                                                v-else
                                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                                                >...</span
                                            >
                                        </template>
                                        <button
                                            @click="nextPageCuestionarios"
                                            :disabled="
                                                currentPageCuestionarios ===
                                                totalPagesCuestionarios
                                            "
                                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <span class="sr-only"
                                                >Siguiente</span
                                            >
                                            <svg
                                                class="h-5 w-5"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import EditarRequisitosComponent from "./EditarRequisitosComponent.vue";
import EditarCondicionesComponent from "./EditarCondicionesComponent.vue";

// Reactive data
const ayudas = ref([]);
const organos = ref([]);
const cuestionarios = ref([]);
const loading = ref(true);
const searchQuery = ref("");
const statusFilter = ref("");
const organoFilter = ref("");
const searchCuestionarios = ref("");
const statusFilterCuestionarios = ref("");
const currentPage = ref(1);
const itemsPerPage = ref(10);
const sortKey = ref("id");
const sortOrder = ref("desc");
const activeTab = ref("ayudas");
const currentPageCuestionarios = ref(1);
const sortKeyCuestionarios = ref("id");
const sortOrderCuestionarios = ref("desc");
const showEditComponent = ref(false);
const loadingEdit = ref(false);
const editingItem = ref({
    id: null,
    type: "",
    name: "",
    questions: [],
    allQuestions: [],
    existingRequirements: [],
});

const showEditConditions = ref(false);
const editingConditionsItem = ref(null);
const loadingEditConditions = ref(false);

// Tabs configuration
const tabs = ref([
    { id: "ayudas", label: "Ayudas", icon: "📋", count: 0 },
    { id: "cuestionarios", label: "Cuestionarios", icon: "📝", count: 0 },
]);

// Table columns configuration
const columns = ref([
    { key: "id", label: "Ayuda" },
    { key: "descripcion", label: "Descripción" },
    { key: "estado", label: "Estado" },
    { key: "organo", label: "Órgano" },
    { key: "cuestionario", label: "Cuestionario" },
    { key: "created_at", label: "Fecha Creación" },
]);

// Computed properties
const filteredAyudas = computed(() => {
    let filtered = ayudas.value;
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(
            (ayuda) =>
                ayuda.nombre?.toLowerCase().includes(query) ||
                ayuda.descripcion?.toLowerCase().includes(query) ||
                ayuda.slug?.toLowerCase().includes(query),
        );
    }
    if (statusFilter.value) {
        if (statusFilter.value === "activa")
            filtered = filtered.filter((ayuda) => ayuda.activo == 1);
        else if (statusFilter.value === "inactiva")
            filtered = filtered.filter((ayuda) => ayuda.activo == 0);
    }
    if (organoFilter.value)
        filtered = filtered.filter(
            (ayuda) => ayuda.organo_id === parseInt(organoFilter.value),
        );
    filtered.sort((a, b) => {
        let aVal = a[sortKey.value];
        let bVal = b[sortKey.value];
        if (sortKey.value === "organo") {
            aVal = a.organo?.nombre_organismo || "";
            bVal = b.organo?.nombre_organismo || "";
        }
        if (typeof aVal === "string") {
            aVal = aVal.toLowerCase();
            bVal = bVal.toLowerCase();
        }
        if (aVal < bVal) return sortOrder.value === "asc" ? -1 : 1;
        if (aVal > bVal) return sortOrder.value === "asc" ? 1 : -1;
        return 0;
    });
    return filtered;
});

const filteredCuestionarios = computed(() => {
    let filtered = cuestionarios.value;
    if (searchCuestionarios.value) {
        const query = searchCuestionarios.value.toLowerCase();
        filtered = filtered.filter((q) =>
            q.nombre?.toLowerCase().includes(query),
        );
    }
    if (statusFilterCuestionarios.value) {
        if (statusFilterCuestionarios.value === "activo")
            filtered = filtered.filter((q) => q.active == 1);
        else if (statusFilterCuestionarios.value === "inactivo")
            filtered = filtered.filter((q) => q.active == 0);
    }

    // Aplicar ordenamiento
    filtered.sort((a, b) => {
        let aVal = a[sortKeyCuestionarios.value];
        let bVal = b[sortKeyCuestionarios.value];

        if (sortKeyCuestionarios.value === "nombre") {
            aVal = aVal?.toLowerCase() || "";
            bVal = bVal?.toLowerCase() || "";
        }

        if (aVal < bVal) return sortOrderCuestionarios.value === "asc" ? -1 : 1;
        if (aVal > bVal) return sortOrderCuestionarios.value === "asc" ? 1 : -1;
        return 0;
    });

    return filtered;
});

const totalPages = computed(() =>
    Math.ceil(filteredAyudas.value.length / itemsPerPage.value),
);
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value);
const endIndex = computed(() =>
    Math.min(
        startIndex.value + itemsPerPage.value,
        filteredAyudas.value.length,
    ),
);

const paginatedAyudas = computed(() => {
    return filteredAyudas.value.slice(startIndex.value, endIndex.value);
});

const visiblePages = computed(() => {
    const pages = [];
    const total = totalPages.value;
    const current = currentPage.value;

    if (total <= 7) {
        for (let i = 1; i <= total; i++) {
            pages.push(i);
        }
    } else {
        if (current <= 4) {
            for (let i = 1; i <= 5; i++) {
                pages.push(i);
            }
            pages.push("...");
            pages.push(total);
        } else if (current >= total - 3) {
            pages.push(1);
            pages.push("...");
            for (let i = total - 4; i <= total; i++) {
                pages.push(i);
            }
        } else {
            pages.push(1);
            pages.push("...");
            for (let i = current - 1; i <= current + 1; i++) {
                pages.push(i);
            }
            pages.push("...");
            pages.push(total);
        }
    }

    return pages;
});

const totalPagesCuestionarios = computed(() =>
    Math.ceil(filteredCuestionarios.value.length / itemsPerPage.value),
);
const startIndexCuestionarios = computed(
    () => (currentPageCuestionarios.value - 1) * itemsPerPage.value,
);
const endIndexCuestionarios = computed(() =>
    Math.min(
        startIndexCuestionarios.value + itemsPerPage.value,
        filteredCuestionarios.value.length,
    ),
);

const paginatedCuestionarios = computed(() => {
    return filteredCuestionarios.value.slice(
        startIndexCuestionarios.value,
        endIndexCuestionarios.value,
    );
});

const visiblePagesCuestionarios = computed(() => {
    const pages = [];
    const total = totalPagesCuestionarios.value;
    const current = currentPageCuestionarios.value;

    if (total <= 7) {
        for (let i = 1; i <= total; i++) {
            pages.push(i);
        }
    } else {
        if (current <= 4) {
            for (let i = 1; i <= 5; i++) {
                pages.push(i);
            }
            pages.push("...");
            pages.push(total);
        } else if (current >= total - 3) {
            pages.push(1);
            pages.push("...");
            for (let i = total - 4; i <= total; i++) {
                pages.push(i);
            }
        } else {
            pages.push(1);
            pages.push("...");
            for (let i = current - 1; i <= current + 1; i++) {
                pages.push(i);
            }
            pages.push("...");
            pages.push(total);
        }
    }

    return pages;
});

// Methods
const fetchAyudas = () => {
    try {
        loading.value = true;

        // Obtener datos desde los atributos data del DOM
        const container = document.getElementById("gestion-ayudas-app");
        if (container) {
            const ayudasData = container.dataset.ayudas;
            const organosData = container.dataset.organos;
            const cuestionariosData = container.dataset.cuestionarios;

            if (ayudasData) {
                ayudas.value = JSON.parse(ayudasData);
            }

            if (organosData) {
                organos.value = JSON.parse(organosData);
            }
            if (cuestionariosData) {
                cuestionarios.value = JSON.parse(cuestionariosData);
            }
        }

        // Mapear los campos del modelo Ayuda a los campos esperados por el componente
        ayudas.value = ayudas.value.map((ayuda) => ({
            id: ayuda.id,
            nombre: ayuda.nombre_ayuda || "Sin nombre",
            descripcion: ayuda.description || "Sin descripción",
            activo: ayuda.activo,
            organo_id: ayuda.organo_id,
            organo: ayuda.organo,
            create_time: ayuda.create_time,
            created_at: ayuda.created_at,
            questionnaire_id: ayuda.questionnaire_id,
            slug: ayuda.slug,
        }));

        // Mapear cuestionarios si existen
        cuestionarios.value = (cuestionarios.value || []).map((q) => ({
            id: q.id,
            nombre: q.name || q.title || "Sin nombre",
            active: q.active || q.activo || 1,
            created_at: q.created_at || null,
        }));

        // Actualizar contadores de tabs
        tabs.value[0].count = ayudas.value.length;
        tabs.value[1].count = cuestionarios.value.length;
    } catch (error) {
        console.error("Error procesando datos de ayudas/cuestionarios:", error);
        ayudas.value = [];
        organos.value = [];
        cuestionarios.value = [];
    } finally {
        loading.value = false;
    }
};

const createNewAyuda = () => {
    console.log("Crear nueva ayuda");
    // Implementar lógica para crear nueva ayuda
};

const createNewQuestionnaire = () => {
    console.log("Crear nuevo cuestionario");
    // Implementar lógica para crear nuevo cuestionario
};

const createNewRelation = () => {
    console.log("Crear nueva relación");
    // Implementar lógica para crear nueva relación
};

const manageQuestionnaire = (ayuda) => {
    console.log("Gestionar cuestionario para ayuda:", ayuda);
    // Implementar lógica para gestionar cuestionario
};

const sortBy = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === "asc" ? "desc" : "asc";
    } else {
        sortKey.value = key;
        sortOrder.value = "asc";
    }
    currentPage.value = 1;
};

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const previousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const getStatusClasses = (activo) => {
    if (activo == 1) {
        return "bg-green-100 text-green-800";
    } else if (activo == 0) {
        return "bg-red-100 text-red-800";
    }
    return "bg-gray-100 text-gray-800";
};

const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    return new Date(dateString).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const viewAyuda = (ayuda) => {
    console.log("Ver ayuda:", ayuda);
    // Implementar lógica para ver detalles de la ayuda
};

const editAyuda = (ayuda) => {
    console.log("Editar ayuda:", ayuda);
    // Implementar lógica para editar la ayuda
};

const deleteAyuda = (ayuda) => {
    if (
        confirm(
            `¿Estás seguro de que quieres eliminar la ayuda "${ayuda.nombre}"?`,
        )
    ) {
        console.log("Eliminar ayuda:", ayuda);
        // Implementar lógica para eliminar la ayuda
    }
};

const editAyudaRequisitos = async (ayuda) => {
    try {
        loadingEdit.value = true;

        // Cargar requisitos existentes de la ayuda
        let requisitosData = { requisitos: [] };
        try {
            // Intentar cargar requisitos desde la ruta de requisitos JSON
            const response = await fetch(
                `/admin/ayudas/${ayuda.id}/requisitos`,
            );
            if (response.ok) {
                const data = await response.json();
                requisitosData = data;
                console.log("Requisitos existentes cargados:", data);
            } else {
                console.log("No hay requisitos existentes, se crearán nuevos");
            }
        } catch (error) {
            console.log(
                "Error cargando requisitos existentes, se crearán nuevos:",
                error,
            );
        }

        // Cargar todas las preguntas del sistema
        const questionsResponse = await fetch("/admin/questions/all");
        if (!questionsResponse.ok) {
            const errorData = await questionsResponse.json().catch(() => ({}));
            console.error(
                "Error cargando preguntas:",
                questionsResponse.status,
                errorData,
            );
            throw new Error(
                `Error ${questionsResponse.status}: ${errorData.message || "No se pudieron cargar las preguntas"}`,
            );
        }
        const allQuestionsData = await questionsResponse.json();

        // Cargar preguntas del cuestionario de la ayuda (si existe)
        let ayudaQuestions = [];
        if (ayuda.questionnaire_id) {
            try {
                const questionnaireResponse = await fetch(
                    `/admin/ayudas/${ayuda.questionnaire_id}/questionnaire`,
                );
                const questionnaireData = await questionnaireResponse.json();
                ayudaQuestions = questionnaireData.questions || [];
            } catch (error) {
                console.log("No se pudo cargar el cuestionario");
            }
        }

        editingItem.value = {
            id: ayuda.id,
            type: "ayuda",
            name: ayuda.nombre,
            questions: ayudaQuestions,
            allQuestions: allQuestionsData.questions || [],
            existingRequirements: requisitosData.requisitos || [],
        };
        showEditComponent.value = true;
    } catch (error) {
        console.error("Error cargando datos para edición:", error);
        // Fallback con datos básicos
        editingItem.value = {
            id: ayuda.id,
            type: "ayuda",
            name: ayuda.nombre,
            questions: [],
            allQuestions: [],
            existingRequirements: [],
        };
        showEditComponent.value = true;
    } finally {
        loadingEdit.value = false;
    }
};

const editCuestionarioCondiciones = async (q) => {
    try {
        loadingEditConditions.value = true;
        const questionsResponse = await fetch("/admin/questions/all");
        if (!questionsResponse.ok) {
            const errorData = await questionsResponse.json().catch(() => ({}));
            console.error(
                "Error cargando preguntas:",
                questionsResponse.status,
                errorData,
            );
            throw new Error(
                `Error ${questionsResponse.status}: ${errorData.message || "No se pudieron cargar las preguntas"}`,
            );
        }
        const allQuestionsData = await questionsResponse.json();

        let cuestionarioQuestions = [];
        try {
            const questionnaireResponse = await fetch(
                `/admin/questionnaires/${q.id}/questions`,
            );
            if (questionnaireResponse.ok) {
                const questionnaireData = await questionnaireResponse.json();
                cuestionarioQuestions = questionnaireData.questions || [];
            }
        } catch (error) {
            console.log("No se pudo cargar el cuestionario");
        }

        let existingConditions = [];
        try {
            const conditionsResponse = await fetch(
                `/admin/questionnaires/${q.id}/conditions`,
            );
            console.log("🔍 Response status:", conditionsResponse.status);
            console.log("🔍 Response ok:", conditionsResponse.ok);

            if (conditionsResponse.ok) {
                const conditionsData = await conditionsResponse.json();
                console.log("🔍 conditionsData completo:", conditionsData);
                console.log(
                    "🔍 conditionsData.conditions:",
                    conditionsData.conditions,
                );
                console.log(
                    "🔍 Array.isArray(conditionsData):",
                    Array.isArray(conditionsData),
                );

                if (Array.isArray(conditionsData)) {
                    existingConditions = conditionsData;
                    console.log(
                        "🔍 Formato: Array directo, condiciones asignadas:",
                        existingConditions.length,
                    );
                } else if (
                    conditionsData.conditions &&
                    Array.isArray(conditionsData.conditions)
                ) {
                    existingConditions = conditionsData.conditions;
                    console.log(
                        "🔍 Formato: Objeto con .conditions, condiciones asignadas:",
                        existingConditions.length,
                    );
                } else {
                    existingConditions = [];
                    console.log(
                        "🔍 Formato: No reconocido, array vacío asignado",
                    );
                }

                console.log(
                    "🔍 existingConditions después de asignar:",
                    existingConditions,
                );
                console.log(
                    "🔍 existingConditions.length:",
                    existingConditions.length,
                );
            } else {
                console.log("No hay condiciones existentes, se crearán nuevas");
            }
        } catch (error) {
            console.log(
                "Error cargando condiciones existentes, se crearán nuevas:",
                error,
            );
        }

        const editingItem = {
            id: q.id,
            type: "cuestionario",
            name: q.nombre,
            questions: cuestionarioQuestions,
            allQuestions: allQuestionsData.questions || [],
            existingConditions: existingConditions,
        };
        editingConditionsItem.value = editingItem;
        showEditConditions.value = true;
    } catch (error) {
        console.error(
            "Error cargando datos para edición de condiciones:",
            error,
        );
        editingConditionsItem.value = {
            id: q.id,
            type: "cuestionario",
            name: q.nombre,
            questions: [],
            allQuestions: [],
            existingConditions: [],
        };
        showEditConditions.value = true;
    } finally {
        loadingEditConditions.value = false;
    }
};

const sortByCuestionarios = (key) => {
    if (sortKeyCuestionarios.value === key) {
        sortOrderCuestionarios.value =
            sortOrderCuestionarios.value === "asc" ? "desc" : "asc";
    } else {
        sortKeyCuestionarios.value = key;
        sortOrderCuestionarios.value = "asc";
    }
    currentPageCuestionarios.value = 1;
};

const goToPageCuestionarios = (page) => {
    if (page >= 1 && page <= totalPagesCuestionarios.value) {
        currentPageCuestionarios.value = page;
    }
};

const goBackFromConditions = () => {
    showEditConditions.value = false;
    editingConditionsItem.value = null;
};

const handleConditionsUpdated = (data) => {
    console.log("Condiciones actualizadas:", data);
};

const previousPageCuestionarios = () => {
    if (currentPageCuestionarios.value > 1) {
        currentPageCuestionarios.value--;
    }
};

const nextPageCuestionarios = () => {
    if (currentPageCuestionarios.value < totalPagesCuestionarios.value) {
        currentPageCuestionarios.value++;
    }
};

const goToCuestionario = (questionnaireId) => {
    // Cambiar a la pestaña de cuestionarios
    activeTab.value = "cuestionarios";

    // Limpiar filtros y búsquedas
    searchCuestionarios.value = "";
    statusFilterCuestionarios.value = "";
    currentPageCuestionarios.value = 1;
    sortKeyCuestionarios.value = "id";
    sortOrderCuestionarios.value = "desc";

    // Buscar el cuestionario por ID y mostrar su nombre en la búsqueda
    const cuestionario = cuestionarios.value.find(
        (q) => q.id === questionnaireId,
    );

    if (cuestionario) {
        searchCuestionarios.value = cuestionario.nombre;
    }
};

const getAyudaRelacionada = (questionnaireId) => {
    return ayudas.value.find(
        (ayuda) => ayuda.questionnaire_id === questionnaireId,
    );
};

const goToAyuda = (ayudaId) => {
    // Cambiar a la pestaña de ayudas
    activeTab.value = "ayudas";

    // Limpiar filtros y búsquedas
    searchQuery.value = "";
    statusFilter.value = "";
    organoFilter.value = "";
    currentPage.value = 1;
    sortKey.value = "id";
    sortOrder.value = "desc";

    // Buscar la ayuda por ID y mostrar su nombre en la búsqueda
    const ayuda = ayudas.value.find((a) => a.id === ayudaId);
    if (ayuda) {
        searchQuery.value = ayuda.nombre;
    }
};

const goBackToMain = () => {
    showEditComponent.value = false;
    editingItem.value = {
        id: null,
        type: "",
        name: "",
        questions: [],
        allQuestions: [],
        existingRequirements: [],
    };
};

const handleRequirementsUpdated = async (data) => {
    try {
        // Guardar los requisitos actualizados en el backend usando la ruta existente
        const response = await fetch(
            `/admin/ayudas/${data.itemId}/requisitos-json`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
                body: JSON.stringify({
                    requisitos: data.requirements.map((req) => ({
                        descripcion: req.description,
                        json_regla: JSON.stringify(req),
                    })),
                }),
            },
        );

        if (response.ok) {
            console.log("Requisitos guardados correctamente");
            // Aquí podrías mostrar un toast de éxito
        } else {
            console.error("Error guardando requisitos");
            // Aquí podrías mostrar un toast de error
        }
    } catch (error) {
        console.error("Error en la comunicación con el backend:", error);
    }
};

// Watchers
watch([searchQuery, statusFilter, organoFilter], () => {
    currentPage.value = 1;
});

watch([searchCuestionarios, statusFilterCuestionarios], () => {
    currentPageCuestionarios.value = 1;
});

// Lifecycle
onMounted(() => {
    fetchAyudas();
});
</script>

<style scoped>
/* Estilos para truncar texto */
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Mejorar la tabla para evitar scroll horizontal */
.table-container {
    overflow-x: auto;
    max-width: 100%;
}

/* Asegurar que las columnas tengan anchos apropiados */
.table-fixed {
    table-layout: fixed;
}

/* Table column widths */
.table-fixed th:nth-child(1) {
    width: 20%;
} /* Nombre */
.table-fixed th:nth-child(2) {
    width: 25%;
} /* Descripción */
.table-fixed th:nth-child(3) {
    width: 10%;
} /* Estado */
.table-fixed th:nth-child(4) {
    width: 15%;
} /* Órgano */
.table-fixed th:nth-child(5) {
    width: 15%;
} /* Cuestionario */
.table-fixed th:nth-child(6) {
    width: 10%;
} /* Fecha */
.table-fixed th:nth-child(7) {
    width: 10%;
} /* Acciones */

/* Tab transitions */
.tab-fade-enter-active,
.tab-fade-leave-active {
    transition: all 0.3s ease-in-out;
}

.tab-fade-enter-from {
    opacity: 0;
    transform: translateX(20px);
}

.tab-fade-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}

.tab-fade-enter-to,
.tab-fade-leave-from {
    opacity: 1;
    transform: translateX(0);
}
</style>
