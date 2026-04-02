<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ayudas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="{{ asset('js/help-sidebar.js') }}"></script>
    <style>
        .help-sidebar {
            transition: transform 0.3s ease-in-out;
        }
        
        .help-sidebar.closed {
            transform: translateX(100%);
        }
        
        .help-sidebar.open {
            transform: translateX(0);
        }
        
        .help-button {
            transition: all 0.2s ease-in-out;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .info-chip {
            transition: all 0.2s ease;
        }

        .info-chip:hover {
            transform: scale(1.05);
        }

        .section-divider {
            border-top: 1px dashed #e2e8f0;
        }

        .help-toggle {
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
        }

        .help-toggle.active {
            max-height: 1000px;
        }

        .rotate-90 {
            transform: rotate(90deg);
        }

        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .animate-float {
            animation: float 1s ease-in-out infinite;
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        .delay-400 {
            animation-delay: 0.4s;
        }

        .delay-500 {
            animation-delay: 0.5s;
        }

        .delay-600 {
            animation-delay: 0.6s;
        }

        .delay-700 {
            animation-delay: 0.7s;
        }

        .delay-800 {
            animation-delay: 0.8s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-3px);
            }
        }

        .wizard-card-ayuda {
            border-left: 4px solid #3b82f6;
        }

        .wizard-card-collector {
            border-left: 4px solid #10b981;
        }

        .wizard-card-ayuda:hover {
            box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.1), 0 10px 10px -5px rgba(59, 130, 246, 0.04);
        }

        .wizard-card-collector:hover {
            box-shadow: 0 20px 25px -5px rgba(16, 185, 129, 0.1), 0 10px 10px -5px rgba(16, 185, 129, 0.04);
        }

        .wizard-type-badge {
            transition: all 0.2s ease;
        }

        .wizard-type-badge:hover {
            transform: scale(1.05);
        }

        .wizard-section {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e2e8f0;
        }

        .wizard-section-header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .wizard-count-badge {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            font-weight: 600;
        }

        .wizard-section-ayuda {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-color: #3b82f6;
        }

        .wizard-section-collector {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border-color: #10b981;
        }

        .wizard-section-content {
            transition: all 0.3s ease-in-out;
            overflow: hidden;
        }

        .wizard-section-content.collapsed {
            max-height: 0;
            opacity: 0;
            margin-top: 0;
        }

        .wizard-section-content.expanded {
            max-height: 600px;
            opacity: 1;
            overflow-y: auto;
        }

        .wizard-section-content::-webkit-scrollbar {
            width: 6px;
        }

        .wizard-section-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .wizard-section-content::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .wizard-section-content::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .toggle-icon {
            transition: transform 0.3s ease;
        }

        .toggle-icon.rotated {
            transform: rotate(180deg);
        }

        .wizard-section-header {
            cursor: pointer;
            user-select: none;
        }

        .wizard-section-header:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-800 min-h-screen">

    @include('layouts.headerbackoffice')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Mis wizards</h1>
        @if (!$wizards->isEmpty())
            <div class="flex gap-2">
                <button onclick="openImportModal()"
                    class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-upload mr-2"></i>Importar wizard
                </button>
                <a href="{{ route('wizards.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Nuevo wizard
                </a>
            </div>
        @else
            <button onclick="openImportModal()"
                class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-upload mr-2"></i>Importar wizard
            </button>
        @endif
        </div>

        @if ($wizards->isEmpty())
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">
                    <i class="fas fa-magic"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No tienes wizards creados</h3>
                <p class="text-gray-500 mb-6">Crea tu primer wizard para simplificar el proceso de creación de ayudas
                </p>
                <a href="{{ route('wizards.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                    Crear mi primer wizard
                </a>
            </div>
        @else
            @php
                $wizardsByType = $wizards->groupBy('type');
                
                $wizardsByType = $wizardsByType->map(function ($wizards) {
                    return $wizards->sortBy(function ($wizard) {
                        $statusPriority = match($wizard->status) {
                            'completed' => 1,
                            'in_review' => 2,
                            'draft' => 3,
                            default => 4
                        };
                        
                        return [$statusPriority, $wizard->updated_at->timestamp * -1];
                    });
                });
            @endphp

            @foreach ($wizardsByType as $type => $wizardsOfType)
                <div class="wizard-section wizard-section-{{ $type }}">
                    <div class="wizard-section-header" onclick="toggleSection('{{ $type }}')">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                        @if($type === 'ayuda')
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-hands-helping text-blue-600"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Wizards de ayuda</h2>
                        @elseif($type === 'collector')
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-flag text-green-600"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Wizards collector</h2>
                        @else
                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-magic text-gray-600"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Wizards {{ $type }}</h2>
                        @endif
                                <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium wizard-count-badge">
                                    {{ $wizardsOfType->count() }} wizard{{ $wizardsOfType->count() !== 1 ? 's' : '' }}
                                </span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-down toggle-icon" id="toggle-icon-{{ $type }}"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="wizard-section-content expanded" id="content-{{ $type }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($wizardsOfType as $wizard)
                    <div
                        class="bg-white rounded-lg shadow-md hover:shadow-lg transition duration-200 border border-gray-200 wizard-card-{{ $wizard->type }} flex flex-col">
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    @if($wizard->type === 'ayuda')
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-hands-helping text-blue-600"></i>
                                        </div>
                                    @elseif($wizard->type === 'collector')
                                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-flag text-green-600"></i>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-magic text-gray-600"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <h3 class="font-semibold text-gray-900">
                                            {{ $wizard->title ?: 'Wizard sin título' }}
                                        </h3>
                                        <p class="text-sm text-gray-500 capitalize">{{ $wizard->type }}</p>
                                        @if($wizard->isDuplicate() && $wizard->duplication_reason)
                                            <p class="text-xs text-gray-400 mt-1">
                                                <i class="fas fa-info-circle mr-1"></i>{{ $wizard->duplication_reason }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @switch($wizard->status)
                                        @case('draft')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-edit mr-1"></i>Borrador
                                            </span>
                                        @break

                                        @case('in_review')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-eye mr-1"></i>En revisión
                                            </span>
                                        @break

                                        @case('completed')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-1"></i>Completado
                                            </span>
                                        @break
                                    @endswitch
                                    
                                    @if($wizard->isDuplicate())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                              title="Duplicado de: {{ $wizard->duplicatedFrom->title ?? 'Wizard original' }}">
                                            <i class="fas fa-copy mr-1"></i>Duplicado
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if ($wizard->description)
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $wizard->description }}</p>
                            @endif

                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <div class="flex items-center space-x-4">
                                    <span>
                                        <i class="fas fa-layer-group mr-1"></i>
                                        Paso {{ $wizard->current_step }}
                                    </span>
                                    @if($wizard->status === 'completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Activo
                                        </span>
                                    @elseif($wizard->status === 'in_review')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-eye mr-1"></i>En revisión
                                        </span>
                                    @endif
                                </div>
                                <span>
                                    <i class="fas fa-clock mr-1"></i>
                                    @php
                                        \Carbon\Carbon::setLocale('es');
                                    @endphp
                                    {{ $wizard->updated_at->diffForHumans() }}
                                </span>
                            </div>

                            @if($wizard->isDuplicate())
                                <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 mb-4">
                                    <div class="flex items-start">
                                        <i class="fas fa-copy text-purple-500 mr-2 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-sm text-purple-800 font-medium">
                                                Duplicado de: {{ $wizard->duplicatedFrom->title ?? 'Wizard original' }}
                                            </p>
                                            <p class="text-xs text-purple-600 mt-1">
                                                Creado el {{ $wizard->duplicated_at->format('d/m/Y H:i') }}
                                            </p>
                                            @if($wizard->duplication_reason)
                                                <p class="text-xs text-purple-600 mt-1">
                                                    <i class="fas fa-comment mr-1"></i>{{ $wizard->duplication_reason }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="flex space-x-2 mt-auto">
                                <a href="{{ route('wizards.show', $wizard) }}"
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition duration-200">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </a>

                                <a href="{{ route('wizards.export', $wizard) }}"
                                    class="bg-purple-600 hover:bg-purple-700 text-white py-2 px-3 rounded-md text-sm font-medium transition duration-200"
                                    title="Exportar wizard">
                                    <i class="fas fa-download"></i>
                                </a>

                                <button onclick="openDuplicateModal({{ $wizard->id }}, '{{ $wizard->title }}', '{{ $wizard->description }}', '{{ $wizard->type }}')"
                                    class="bg-green-600 hover:bg-green-700 text-white py-2 px-3 rounded-md text-sm font-medium transition duration-200"
                                    title="Duplicar wizard">
                                    <i class="fas fa-copy"></i>
                                </button>

                                <button onclick="deleteWizard({{ $wizard->id }})"
                                    class="bg-red-600 hover:bg-red-700 text-white py-2 px-3 rounded-md text-sm font-medium transition duration-200">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                        @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div id="duplicateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Duplicar Wizard</h3>
                    <button onclick="closeDuplicateModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="duplicateForm">
                    <input type="hidden" id="duplicateWizardId">
                    <input type="hidden" id="duplicateWizardType">
                    
                    <div class="mb-4">
                        <label for="duplicateTitle" class="block text-sm font-medium text-gray-700 mb-2">
                            Título del nuevo wizard <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="duplicateTitle" name="title" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Ej: Copia de mi wizard">
                    </div>
                    
                    <div class="mb-4">
                        <label for="duplicateDescription" class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción (opcional)
                        </label>
                        <textarea id="duplicateDescription" name="description" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Describe brevemente el propósito de este wizard duplicado"></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="duplicationReason" class="block text-sm font-medium text-gray-700 mb-2">
                            Razón de la duplicación (opcional)
                        </label>
                        <input type="text" id="duplicationReason" name="duplication_reason"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Ej: Versión para testing, nueva variante, etc.">
                    </div>
                    
                    <div class="bg-blue-50 p-3 rounded-lg mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            <p class="text-sm text-blue-700">
                                El wizard duplicado mantendrá toda la configuración original pero empezará desde el paso 1 en estado borrador.
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeDuplicateModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <i class="fas fa-copy mr-2"></i>Duplicar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="importModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Importar Wizard</h3>
                    <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="importForm" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="importFile" class="block text-sm font-medium text-gray-700 mb-2">
                            Archivo JSON <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="importFile" name="file" accept=".json" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">
                            Selecciona un archivo JSON exportado previamente de un wizard.
                        </p>
                    </div>
                    
                    <div class="bg-purple-50 p-3 rounded-lg mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-purple-500 mr-2"></i>
                            <p class="text-sm text-purple-700">
                                El wizard importado se creará como borrador. Podrás editarlo después de importarlo.
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeImportModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <i class="fas fa-upload mr-2"></i>Importar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function deleteWizard(wizardId) {
            if (confirm('¿Estás seguro de que quieres eliminar este wizard? Esta acción no se puede deshacer.\nRECUERDA: Un wizard no es lo mismo que una ayuda.\nA pesar de que borres el wizard, la ayuda seguirá existiendo. Si quieres borrar la ayuda, debes hacerlo desde la sección de ayudas.')) {
                fetch(`/admin/wizards/${wizardId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert('Error al eliminar el wizard: ' + (data.message || 'Error desconocido'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al eliminar el wizard: ' + error.message);
                    });
            }
        }

        function openDuplicateModal(wizardId, title, description, type) {
            document.getElementById('duplicateWizardId').value = wizardId;
            document.getElementById('duplicateWizardType').value = type;
            document.getElementById('duplicateTitle').value = `Copia de ${title}`;
            document.getElementById('duplicateDescription').value = description || '';
            document.getElementById('duplicationReason').value = '';
            
            document.getElementById('duplicateModal').classList.remove('hidden');
        }

        function closeDuplicateModal() {
            document.getElementById('duplicateModal').classList.add('hidden');
            document.getElementById('duplicateForm').reset();
        }

        document.getElementById('duplicateForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Duplicando...';

            const formData = new FormData(this);
            const data = {
                title: formData.get('title'),
                description: formData.get('description'),
                duplication_reason: formData.get('duplication_reason')
            };

            const wizardId = document.getElementById('duplicateWizardId').value;

            fetch(`/admin/wizards/${wizardId}/duplicate`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeDuplicateModal();
                        window.location.href = `/admin/wizards/${data.wizard.id}`;
                    } else {
                        alert('Error al duplicar el wizard: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al duplicar el wizard: ' + error.message);
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                });
        });

        document.getElementById('duplicateModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDuplicateModal();
            }
        });

        function toggleSection(type) {
            const content = document.getElementById(`content-${type}`);
            const icon = document.getElementById(`toggle-icon-${type}`);
            
            if (content.classList.contains('expanded')) {
                content.classList.remove('expanded');
                content.classList.add('collapsed');
                icon.classList.add('rotated');
            } else {
                content.classList.remove('collapsed');
                content.classList.add('expanded');
                icon.classList.remove('rotated');
            }
        }

        function openImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
            document.getElementById('importForm').reset();
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
            document.getElementById('importForm').reset();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('.wizard-section-content');
            sections.forEach(section => {
                section.classList.add('expanded');
            });

            const importForm = document.getElementById('importForm');
            if (importForm) {
                importForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData();
                    const fileInput = document.getElementById('importFile');
                    
                    if (!fileInput.files.length) {
                        alert('Por favor selecciona un archivo JSON');
                        return;
                    }
                    
                    formData.append('file', fileInput.files[0]);
                    formData.append('_token', '{{ csrf_token() }}');
                    
                    const submitButton = this.querySelector('button[type="submit"]');
                    const originalText = submitButton.innerHTML;
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Importando...';
                    
                    fetch('{{ route("wizards.import") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            closeImportModal();
                            alert('Wizard importado correctamente');
                            window.location.reload();
                        } else {
                            alert('Error al importar el wizard: ' + (data.message || 'Error desconocido'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al importar el wizard: ' + error.message);
                    })
                    .finally(() => {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                    });
                });
            }

            const importModal = document.getElementById('importModal');
            if (importModal) {
                importModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeImportModal();
                    }
                });
            }
        });
    </script>

    <div id="help-sidebar-app">
        <help-sidebar 
            title="Wizards"
            main-title="Gestión de wizards"
            main-description="Un wizard es una herramienta que te permite crear ayudas completas en un mismo sitio. Desde el nombre de la ayuda hasta las preguntas, lógicas, flujos, etc."
            :features="[
                'Crear wizards de ayuda y marketing',
                'Gestionar el estado y progreso de tus wizards',
                'Editar y eliminar wizards según sea necesario',
                'Seguir el progreso paso a paso de cada wizard'
            ]"
            :steps="[
                'Crea un nuevo wizard con el botón correspondiente',
                'Selecciona el tipo de wizard que necesitas',
                'Completa la configuración paso a paso'
            ]"
            additional-info="Los wizards te permiten crear ayudas complejas de forma simplificada. Puedes crear wizards de ayuda (con cuestionarios, preguntas y lógica) o wizards de email (para envíos masivos). Cada wizard tiene un estado que indica su progreso: borrador, en revisión o completado."
            important-note="Los wizards en estado 'borrador' se pueden eliminar, pero los que están en revisión o completados no. Siempre revisa el estado antes de hacer cambios importantes."
        ></help-sidebar>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.HelpSidebar.init('help-sidebar-app');
        });
    </script>
</body>

</html>
