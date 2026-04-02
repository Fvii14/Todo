<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ayudas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#3b82f6',
                            600: '#2563eb',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-800 min-h-screen">
    @include('layouts.headerbackoffice')
    
    <div class="w-full max-w-screen-xl mx-auto px-4 py-8 space-y-8">
        <!-- Breadcrumb -->
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboardv2') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors">
                        <i class="fas fa-home mr-2"></i>
                        Inicio
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                        <a href="{{ route('ayudas.recursos') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors">
                            Recursos de la ayuda
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                        <a href="{{ route('ayudas.recursos.edit', $ayuda->id) }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors">
                            Recursos de {{ $ayuda->nombre_ayuda }}
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                        <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2">Nuevo recurso para {{ $ayuda->nombre_ayuda }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Añadir recursos a la ayuda</h1>
                <p class="text-gray-600 mt-1">Estás agregando un nuevo recurso para: <span class="font-semibold text-primary-600">{{ $ayuda->nombre_ayuda }}</span></p>
            </div>
        </div>

        <!-- Form Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Form Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Detalles del recurso</h2>
                <p class="text-sm text-gray-500 mt-1">Complete todos los campos requeridos</p>
            </div>
            
            <!-- Form Content -->
            <form action="{{ route('ayudas.recursos.store', $ayuda->id) }}" method="post" class="p-6 space-y-6">
                @csrf
                
                <!-- Resource Selection Section -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-200">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-layer-group text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800">Selección de recurso</h3>
                            <p class="text-sm text-gray-600">Elige entre crear un nuevo recurso o usar uno existente</p>
                        </div>
                    </div>
                    
                    <!-- Option Tabs -->
                    <div class="flex space-x-1 bg-white rounded-lg p-1 shadow-sm border border-gray-200 mb-4">
                        <button type="button" id="new-resource-tab" 
                                class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200 bg-primary-600 text-white shadow-sm">
                            <i class="fas fa-plus mr-2"></i>Nuevo recurso
                        </button>
                        <button type="button" id="existing-resource-tab" 
                                class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200 text-gray-600 hover:text-gray-800 hover:bg-gray-100">
                            <i class="fas fa-folder-open mr-2"></i>Recurso existente
                        </button>
                    </div>
                    
                    <!-- New Resource Form -->
                    <div id="new-resource-form" class="space-y-4">
                        <!-- Title Field -->
                        <div>
                            <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">Título <span class="text-red-500">*</span></label>
                            <input type="text" name="titulo" id="titulo" 
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all outline-none"
                                   placeholder="Ej: Guía paso a paso">
                        </div>
                        
                        <!-- Description Field -->
                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="3"
                                      class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all outline-none"
                                      placeholder="Breve descripción del recurso"></textarea>
                        </div>
                        
                        <!-- Type Field -->
                        <div>
                            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo de recurso</label>
                            <select name="tipo" id="tipo" 
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all outline-none">
                                <option value="texto">Texto</option>
                                <option value="video">Video</option>
                                <option value="imagen">Imagen</option>
                                <option value="enlace">Enlace</option>
                            </select>
                        </div>
                        
                        <!-- Content Field -->
                        <div>
                            <label for="contenido_texto" class="block text-sm font-medium text-gray-700 mb-1">Contenido</label>
                            <textarea name="contenido_texto" id="contenido_texto" rows="5"
                                      class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all outline-none"
                                      placeholder="Contenido detallado del recurso"></textarea>
                        </div>
                        
                        <!-- URL Fields -->
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="url_archivo" class="block text-sm font-medium text-gray-700 mb-1">URL de video o imagen</label>
                                <input type="text" name="url_archivo" id="url_archivo" 
                                       class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all outline-none"
                                       placeholder="https://ejemplo.com/video">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Existing Resource Selector -->
                    <div id="existing-resource-form" class="hidden space-y-4">
                        <div class="bg-white rounded-lg border border-gray-200 p-4">
                            <label for="recurso_id" class="block text-sm font-medium text-gray-700 mb-3">Seleccionar recurso existente</label>
                            <select name="recurso_id" id="recurso_id" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all outline-none">
                                <option value="">-- Selecciona un recurso --</option>
                                @foreach($recursos_disponibles as $recurso)
                                    <option value="{{ $recurso->id }}" 
                                            data-tipo="{{ $recurso->tipo }}"
                                            data-descripcion="{{ $recurso->descripcion }}">
                                        {{ $recurso->titulo }} ({{ ucfirst($recurso->tipo) }})
                                    </option>
                                @endforeach
                            </select>
                            
                            <!-- Resource Preview -->
                            <div id="resource-preview" class="mt-4 p-4 bg-gray-50 rounded-lg hidden">
                                <h4 class="font-medium text-gray-800 mb-2">Vista previa del recurso</h4>
                                <div class="space-y-2 text-sm text-gray-600">
                                    <p><strong>Tipo:</strong> <span id="preview-tipo"></span></p>
                                    <p><strong>Descripción:</strong> <span id="preview-descripcion"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order and Active Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="orden" class="block text-sm font-medium text-gray-700 mb-1">Orden de visualización</label>
                        <input type="number" name="orden" id="orden" value="0" min="0"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all outline-none">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="activo" id="activo" value="1" checked
                               class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <label for="activo" class="ml-2 text-sm font-medium text-gray-700">Activo</label>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('ayudas.recursos.edit', $ayuda->id) }}" 
                       class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 focus:ring-2 focus:ring-primary-200 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-save mr-2"></i> Guardar recurso
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tab switching functionality
        const newResourceTab = document.getElementById('new-resource-tab');
        const existingResourceTab = document.getElementById('existing-resource-tab');
        const newResourceForm = document.getElementById('new-resource-form');
        const existingResourceForm = document.getElementById('existing-resource-form');
        const recursoSelect = document.getElementById('recurso_id');
        const resourcePreview = document.getElementById('resource-preview');
        const previewTipo = document.getElementById('preview-tipo');
        const previewDescripcion = document.getElementById('preview-descripcion');

        // Tab switching
        newResourceTab.addEventListener('click', () => {
            newResourceTab.className = 'flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200 bg-primary-600 text-white shadow-sm';
            existingResourceTab.className = 'flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200 text-gray-600 hover:text-gray-800 hover:bg-gray-100';
            newResourceForm.classList.remove('hidden');
            existingResourceForm.classList.add('hidden');
            // Clear existing resource selection
            recursoSelect.value = '';
            resourcePreview.classList.add('hidden');
        });

        existingResourceTab.addEventListener('click', () => {
            existingResourceTab.className = 'flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200 bg-primary-600 text-white shadow-sm';
            newResourceTab.className = 'flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200 text-gray-600 hover:text-gray-800 hover:bg-gray-100';
            existingResourceForm.classList.remove('hidden');
            newResourceForm.classList.add('hidden');
        });

        // Resource preview
        recursoSelect.addEventListener('change', () => {
            const selectedOption = recursoSelect.options[recursoSelect.selectedIndex];
            if (recursoSelect.value) {
                previewTipo.textContent = selectedOption.dataset.tipo;
                previewDescripcion.textContent = selectedOption.dataset.descripcion || 'Sin descripción';
                resourcePreview.classList.remove('hidden');
            } else {
                resourcePreview.classList.add('hidden');
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const isNewResource = !newResourceForm.classList.contains('hidden');
            
            if (isNewResource) {
                const titulo = document.getElementById('titulo').value.trim();
                if (!titulo) {
                    e.preventDefault();
                    alert('El título es obligatorio para crear un nuevo recurso.');
                    return;
                }
            } else {
                const recursoId = recursoSelect.value;
                if (!recursoId) {
                    e.preventDefault();
                    alert('Debes seleccionar un recurso existente.');
                    return;
                }
            }
        });
    </script>
</body>

</html>