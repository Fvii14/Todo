<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Historial de Documentos · Backoffice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-100">
    @include('layouts.headerbackoffice')

    <div x-data="{ open: false, userName: '', docs: [] }" class="w-full max-w-7xl mx-auto px-4 py-6 space-y-6">

        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover">
            <div class="p-6 md:p-8 bg-gradient-to-r from-[#54debd] to-[#0ecfa2] text-black">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">Gestión de Documentos</h2>
                        <p class="opacity-90">Administra todos los documentos </p>
                    </div>
                    <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
                        <div class="bg-white bg-opacity-20 px-4 py-2 rounded-lg flex items-center space-x-2">
                            <i class="fas fa-hands-helping"></i>
                            <span>Hay {{ $allDocuments->count() }} documento/s registrado/s</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover">
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4 mt-4">
                    <button onclick="showModal('createDocumentoModal')" class="bg-[#000000] hover:bg-[#3a3a3a] text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2 transition-colors">
                        <i class="fas fa-plus"></i>
                        <span>Nuevo documento</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md overflow-hidden mt-8 card-hover">
            <div class="p-6 border-b border-gray-100">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-file text-[#54debd] mr-2"></i>
                        Listado completo de Documentos
                        @if(!empty($search))
                            <span class="ml-2 text-sm font-normal text-gray-500">
                                (Buscando: "{{ $search }}")
                            </span>
                        @endif
                    </h3>
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="w-full md:w-64">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" id="searchQuestionsInput"
                                    value="{{ $search ?? '' }}"
                                    class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-[#54debd] focus:border-[#54debd]"
                                    placeholder="Buscar documentos..." 
                                    onkeyup="debounceSearch()">
                                <div id="searchLoading" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden">
                                    <i class="fas fa-spinner fa-spin text-[#54debd]"></i>
                                </div>
                                @if(!empty($search))
                                    <a href="{{ route('admin.documentos', ['per_page' => request('per_page', 15)]) }}" 
                                       class="absolute inset-y-0 right-0 pr-3 flex items-center text-red-400 hover:text-red-600"
                                       title="Limpiar búsqueda">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Selector de elementos por página -->
                        <div class="w-full md:w-48">
                            <div class="flex items-center space-x-2">
                                <label for="per_page" class="text-sm text-gray-600">Mostrar:</label>
                                <select id="per_page" onchange="changePerPage(this.value)" 
                                    class="block w-full pl-3 pr-10 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 focus:ring-[#54debd] focus:border-[#54debd]">
                                    <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                    <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>


            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nombre</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Descripción</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tipos admitidos</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Eliminar</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="questionsTableBody">
                        @foreach ($allDocuments as $document)
                            <tr class="document-row hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $document->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $document->description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach (explode(', ', $document->allowed_types) as $type)
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded bg-blue-50 text-[#000000]">
                                                {{ $type }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">

                                    <button onclick="confirmDeleteDocument({{ $document->id }})"
                                        class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                @if(!empty($search) && $allDocuments->count() == 0)
                    <!-- Mensaje cuando no hay resultados de búsqueda -->
                    <div class="px-6 py-12 text-center bg-white">
                        <div class="flex flex-col items-center space-y-4">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-search text-gray-400 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">No se encontraron documentos</h3>
                                <p class="text-gray-500">No hay resultados para "{{ $search }}"</p>
                                <a href="{{ route('admin.documentos', ['per_page' => request('per_page', 15)]) }}" 
                                   class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-[#54debd] hover:bg-[#4fd1b5]">
                                    <i class="fas fa-times mr-2"></i>
                                    Limpiar búsqueda
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Paginación -->
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        @if ($allDocuments->onFirstPage())
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white cursor-not-allowed">
                                Anterior
                            </span>
                        @else
                            <a href="{{ $allDocuments->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Anterior
                            </a>
                        @endif
                        
                        @if ($allDocuments->hasMorePages())
                            <a href="{{ $allDocuments->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Siguiente
                            </a>
                        @else
                            <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white cursor-not-allowed">
                                Siguiente
                            </span>
                        @endif
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                @if(!empty($search))
                                    Mostrando
                                    <span class="font-medium">{{ $allDocuments->firstItem() }}</span>
                                    a
                                    <span class="font-medium">{{ $allDocuments->lastItem() }}</span>
                                    de
                                    <span class="font-medium">{{ $allDocuments->total() }}</span>
                                    resultados para "{{ $search }}"
                                @else
                                    Mostrando
                                    <span class="font-medium">{{ $allDocuments->firstItem() }}</span>
                                    a
                                    <span class="font-medium">{{ $allDocuments->lastItem() }}</span>
                                    de
                                    <span class="font-medium">{{ $allDocuments->total() }}</span>
                                    resultados
                                @endif
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                {{-- Botón Anterior --}}
                                @if ($allDocuments->onFirstPage())
                                    <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                @else
                                    <a href="{{ $allDocuments->previousPageUrl() }}" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                @endif

                                {{-- Números de página --}}
                                @foreach ($allDocuments->getUrlRange(1, $allDocuments->lastPage()) as $page => $url)
                                    @if ($page == $allDocuments->currentPage())
                                        <span class="relative inline-flex items-center px-4 py-2 border border-[#54debd] bg-[#54debd] text-sm font-medium text-white">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach

                                {{-- Botón Siguiente --}}
                                @if ($allDocuments->hasMorePages())
                                    <a href="{{ $allDocuments->nextPageUrl() }}" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                @else
                                    <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                @endif
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Documento Modal -->
        <div id="createDocumentoModal" class="fixed inset-0 overflow-y-auto z-50 hidden" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay"
                    aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <form method="POST" action="{{ route('documento.store') }}">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-file text-[#54debd]"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Nuevo
                                        Documento</h3>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <label for="nombre_documento"
                                                class="block text-sm font-medium text-gray-700">Nombre del documento
                                                *</label>
                                            <input type="text" name="nombre_documento" id="nombre_documento" required
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm"
                                                oninput="autoFillSlugDocumento()">
                                        </div>
                                        <div>
                                            <label for="slug" class="block text-sm font-medium text-gray-700">Slug
                                                *</label>
                                            <input type="text" name="slug" id="slug" required
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm">
                                            <small class="text-gray-400">Se autocompleta, pero puedes modificarlo
                                                manualmente.</small>
                                        </div>
                                        <div>
                                            <label for="descripcion_documento"
                                                class="block text-sm font-medium text-gray-700">Descripción del
                                                documento </label>
                                            <input type="text" name="descripcion_documento"
                                                id="descripcion_documento"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Formatos permitidos
                                                *</label>
                                            <div class="mt-2 space-y-2">
                                                <!-- Checkbox para PDF -->
                                                <div class="flex items-center">
                                                    <input id="format_pdf" name="allowed_formats[]" type="checkbox"
                                                        value="application/pdf"
                                                        class="h-4 w-4 text-[#54debd] focus:ring-[#54debd] border-gray-300 rounded"
                                                        checked>
                                                    <label for="format_pdf"
                                                        class="ml-2 block text-sm text-gray-700">PDF</label>
                                                </div>
                                                <!-- Checkbox para JPG -->
                                                <div class="flex items-center">
                                                    <input id="format_jpg" name="allowed_formats[]" type="checkbox"
                                                        value="image/jpeg"
                                                        class="h-4 w-4 text-[#54debd] focus:ring-[#54debd] border-gray-300 rounded"
                                                        checked>
                                                    <label for="format_jpg"
                                                        class="ml-2 block text-sm text-gray-700">JPG</label>
                                                </div>
                                                <!-- Checkbox para PNG -->
                                                <div class="flex items-center">
                                                    <input id="format_png" name="allowed_formats[]" type="checkbox"
                                                        value="image/png"
                                                        class="h-4 w-4 text-[#54debd] focus:ring-[#54debd] border-gray-300 rounded"
                                                        checked>
                                                    <label for="format_png"
                                                        class="ml-2 block text-sm text-gray-700">PNG</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#54debd] text-base font-medium text-white hover:bg-[#54debd] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:ml-3 sm:w-auto sm:text-sm">
                                Crear documento
                            </button>
                            <button type="button" onclick="closeModal('createDocumentoModal')"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="addDocumentoModal" class="fixed inset-0 overflow-y-auto z-50 hidden" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay"
                    aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Gestionar documentos
                                </h3>
                                <div class="mt-4">
                                    <div class="mb-8">
                                        <h4 class="font-medium text-gray-700 mb-2">Documentos actuales:</h4>
                                        <div class="bg-gray-100 p-4 rounded-lg">
                                            <ul class="space-y-3" id="documentosList">
                                            </ul>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="font-medium text-gray-700 mb-2">Añadir nuevo documento:</h4>
                                        <form method="POST" action="{{ route('ayudadocumento.store') }}"
                                            class="space-y-4">
                                            @csrf

                                            <div>
                                                <label for="document_id"
                                                    class="block text-sm font-medium text-gray-700">Selecciona una
                                                    pregunta:</label>
                                                <select id="document_id" name="document_id"
                                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base bg-gray-50 border border-gray-300 text-gray-700 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm rounded-md shadow-sm">
                                                    @foreach ($allDocuments as $document)
                                                        <option value="{{ $document->id }}">{{ $document->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700">Obligatorio:</label>
                                                <div class="mt-1 space-x-4">
                                                    <label class="inline-flex items-center">
                                                        <input type="radio" name="es_obligatorio" value="1"
                                                            class="focus:ring-[#54debd] h-4 w-4 text-[#54debd] border-gray-300"
                                                            checked>
                                                        <span class="ml-2 text-sm text-gray-700">Sí</span>
                                                    </label>
                                                    <label class="inline-flex items-center">
                                                        <input type="radio" name="es_obligatorio" value="0"
                                                            class="focus:ring-[#54debd] h-4 w-4 text-[#54debd] border-gray-300">
                                                        <span class="ml-2 text-sm text-gray-700">No</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="flex justify-end">
                                                <button type="submit"
                                                    class="inline-flex items-center px-4 py-2 bg-[#54debd] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#54debd] active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                                                    Añadir documento
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" onclick="closeModal('addDocumentoModal')"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#54debd] text-base font-medium text-white hover:bg-[#54debd] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:ml-3 sm:w-auto sm:text-sm">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6 mt-12">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-folder-open text-[#54debd]"></i>
                    </div>
                    <span class="font-medium">Collector by TTF</span>
                </div>
                <div class="text-sm text-gray-500">
                    Panel de administración por <a href="https://tutramitefacil.es/" target="_blank"
                        class="text-[#54debd] hover:text-indigo-800">TuTrámiteFácil</a>
                </div>
            </div>
        </div>
    </footer>
    @if (session('success'))
        <div id="toast"
            class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button onclick="document.getElementById('toast').remove()" class="ml-4">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @elseif($errors->any())
        <div id="toast"
            class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center justify-between">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button onclick="document.getElementById('toast').remove()" class="ml-4">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif
    <script>
        setTimeout(() => {
            const toast = document.getElementById('toast');
            if (toast) toast.remove();
        }, 5000);

        function showModal(modalId, ayudaId = null) {
            event.stopPropagation();
            document.getElementById(modalId).classList.remove('hidden');

            if (modalId == 'editAyudaModal' && ayudaId !== null) {
                let ayudaObj = JSON.parse(ayudaId);
                const form = document.querySelector('#editAyudaForm');
                form.action = `/ayudas/${ayudaObj.id}`;
                for (const key in ayudaObj) {
                    const input = document.querySelector(`#editAyudaModal #${key}`);
                    if (input) {
                        if (input.type === "date" && ayudaObj[key]) {
                            input.value = ayudaObj[key].split('T')[0];
                        } else {
                            input.value = ayudaObj[key];
                        }
                    }
                }
            } else if (modalId == 'addRequisitoModal' && ayudaId !== null) {
                let requisitos = JSON.parse(ayudaId);
                const requisitosList = document.getElementById('requisitosList');

                // Limpiar lista existente
                requisitosList.innerHTML = '';

                // Agregar cada requisito a la lista
                requisitos.forEach(requisito => {
                    let answerText = requisito.formatted_expected_answer || requisito.respuesta_expected;

                    if (requisito.question?.type === 'boolean') {
                        answerText = requisito.respuesta_expected === "1" ? "Sí" : "No";
                    }

                    const li = document.createElement('li');
                    li.className =
                    'border-l-4 border-indigo-200 pl-4 py-2 group hover:bg-gray-50 transition-colors';
                    li.innerHTML = `
                        <div class="flex flex-col sm:flex-row sm:items-baseline gap-1">
                            <span class="font-medium text-gray-800 flex-1">
                                ${requisito.question?.text || 'Pregunta eliminada'}
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">
                                    ${requisito.question?.type || 'N/A'}
                                </span>
                                <span class="text-sm text-gray-600">Respuesta esperada:</span>
                                <span class="text-sm font-semibold text-green-600 bg-green-50 px-2 py-1 rounded">
                                    ${answerText}
                                </span>
                                <form method="POST" action="/ayudarequisito/${requisito.id}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity"
                                            onclick="return confirm('¿Estás seguro de que deseas eliminar este requisito?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    `;

                    requisitosList.appendChild(li);
                });
            } else if (modalId == 'addDocumentoModal' && ayudaId !== null) {
                const documentosList = document.getElementById('documentosList');
                documentosList.innerHTML = '';
                console.log(ayudaId)

                ayudaId.forEach(documento => {
                    let answerText = documento.es_obligatorio ? "Sí" : "No";

                    const li = document.createElement('li');
                    li.className =
                    'border-l-4 border-indigo-200 pl-4 py-2 group hover:bg-gray-50 transition-colors';
                    li.innerHTML = `
                        <div class="flex flex-col sm:flex-row sm:items-baseline gap-1">
                            <span class="font-medium text-gray-800 flex-1">
                                ${documento.name}
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">
                                    ${documento.description}
                                </span>
                                <span class="text-sm text-gray-600">¿Obligatorio?</span>
                                <span class="text-sm font-semibold text-green-600 bg-green-50 px-2 py-1 rounded">
                                    ${answerText ? "Sí" : "No"}
                                </span>
                                <form method="POST" action="/ayudadocumento/${documento.id}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity"
                                            onclick="return confirm('¿Estás seguro de que quieres desacoplar este documento?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    `;

                    documentosList.appendChild(li);
                });
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function confirmDeleteAyuda(id, event) {
            event.stopPropagation();
            document.getElementById('deleteForm').action = `/ayudas/${id}`;
            document.getElementById('deleteModalText').textContent =
                '¿Estás seguro de que deseas eliminar esta ayuda? Esta acción no se puede deshacer.';
            showModal('deleteModal');
        }

        function confirmDeleteDocument(id) {
            document.getElementById('deleteForm').action = `/documento/${id}`;
            document.getElementById('deleteModalText').textContent =
                '¿Estás seguro de que deseas eliminar este documento? Esta acción no se puede deshacer.';
            showModal('deleteModal');
        }

        function toggleAyudaDetails(id) {
            const detailsRow = document.getElementById(`ayuda${id}-row`);
            const icon = document.getElementById(`ayuda${id}-icon`);

            if (detailsRow.classList.contains('hidden')) {
                detailsRow.classList.remove('hidden');
                if (icon) icon.classList.add('rotate-90');
            } else {
                detailsRow.classList.add('hidden');
                if (icon) icon.classList.remove('rotate-90');
            }
        }

        function autoFillSlugDocumento() {
            const nombre = document.getElementById('nombre_documento').value;
            let slug = nombre.normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .trim()
                .replace(/\s+/g, '_');
            document.getElementById('slug').value = slug;
        }

        // Función para búsqueda AJAX en tiempo real
        let searchTimeout;
        let currentSearch = '';
        
        function debounceSearch() {
            const searchTerm = document.getElementById('searchQuestionsInput').value;
            const loadingSpinner = document.getElementById('searchLoading');
            const tableBody = document.getElementById('questionsTableBody');
            const pagination = document.querySelector('.bg-white.px-4.py-3');
            
            clearTimeout(searchTimeout);
            
            // Mostrar loading
            if (loadingSpinner) loadingSpinner.classList.remove('hidden');
            
            searchTimeout = setTimeout(() => {
                if (searchTerm === currentSearch) {
                    if (loadingSpinner) loadingSpinner.classList.add('hidden');
                    return;
                }
                
                currentSearch = searchTerm;
                
                // Realizar búsqueda AJAX
                fetch('{{ route("admin.documentos.search") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        search: searchTerm,
                        per_page: {{ request('per_page', 15) }}
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Actualizar tabla
                    updateTableWithResults(data);
                    
                    // Ocultar loading
                    if (loadingSpinner) loadingSpinner.classList.add('hidden');
                })
                .catch(error => {
                    console.error('Error en la búsqueda:', error);
                    if (loadingSpinner) loadingSpinner.classList.add('hidden');
                });
            }, 300); // Debounce de 300ms
        }
        
        function updateTableWithResults(data) {
            const tableBody = document.getElementById('questionsTableBody');
            const pagination = document.querySelector('.bg-white.px-4.py-3');
            const title = document.querySelector('h3.text-lg.font-semibold');
            
            // Actualizar título con término de búsqueda
            if (title) {
                if (data.search) {
                    title.innerHTML = '<i class="fas fa-file text-[#54debd] mr-2"></i>Listado completo de Documentos <span class="ml-2 text-sm font-normal text-gray-500">(Buscando: "' + data.search + '")</span>';
                } else {
                    title.innerHTML = '<i class="fas fa-file text-[#54debd] mr-2"></i>Listado completo de Documentos';
                }
            }
            
            // Limpiar tabla
            tableBody.innerHTML = '';
            
            if (data.documents.length === 0 && data.search) {
                // Mostrar mensaje de no resultados
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center space-y-4">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-search text-gray-400 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">No se encontraron documentos</h3>
                                    <p class="text-gray-500">No hay resultados para "${data.search}"</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
                
                // Ocultar paginación
                if (pagination) pagination.style.display = 'none';
            } else {
                // Generar filas de la tabla
                data.documents.forEach(document => {
                    const types = document.allowed_types.split(', ').map(type => 
                        `<span class="px-2 py-1 text-xs font-medium rounded bg-blue-50 text-[#000000]">${type}</span>`
                    ).join('');
                    
                    const row = `
                        <tr class="document-row hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">${document.name}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">${document.description || ''}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    ${types}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="confirmDeleteDocument(${document.id})" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
                
                // Actualizar paginación
                updatePagination(data.pagination, data.search);
                
                // Mostrar paginación
                if (pagination) pagination.style.display = 'flex';
            }
        }
        
        function updatePagination(pagination, search) {
            const paginationContainer = document.querySelector('.bg-white.px-4.py-3');
            if (!paginationContainer) return;
            
            // Actualizar información de resultados
            const resultsInfo = paginationContainer.querySelector('.text-sm.text-gray-700');
            if (resultsInfo) {
                if (search) {
                    resultsInfo.innerHTML = `
                        Mostrando <span class="font-medium">${pagination.from}</span> a 
                        <span class="font-medium">${pagination.to}</span> de 
                        <span class="font-medium">${pagination.total}</span> resultados para "${search}"
                    `;
                } else {
                    resultsInfo.innerHTML = `
                        Mostrando <span class="font-medium">${pagination.from}</span> a 
                        <span class="font-medium">${pagination.to}</span> de 
                        <span class="font-medium">${pagination.total}</span> resultados
                    `;
                }
            }
        }
        
        function changePerPage(perPage) {
            const searchTerm = document.getElementById('searchQuestionsInput').value;
            
            // Realizar búsqueda AJAX con nuevo per_page
            fetch('{{ route("admin.documentos.search") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    search: searchTerm,
                    per_page: perPage
                })
            })
            .then(response => response.json())
            .then(data => {
                updateTableWithResults(data);
            })
            .catch(error => {
                console.error('Error al cambiar elementos por página:', error);
            });
        }
    </script>
</body>

</html>
