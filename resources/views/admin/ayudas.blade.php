<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - Ayudas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-800 min-h-screen">

    @include('layouts.headerbackoffice')

    <main class="container mx-auto px-6 py-8">
        <!-- Welcome Card -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover">
            <div class="p-6 md:p-8 bg-gradient-to-r from-[#54debd] to-[#0ecfa2] text-black">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">Gestión de Ayudas</h2>
                        <p class="opacity-90">Administra todas las ayudas y documentos </p>
                    </div>
                    <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
                        <div class="bg-white bg-opacity-20 px-4 py-2 rounded-lg flex items-center space-x-2">
                            <i class="fas fa-hands-helping"></i>
                            <span>Hay {{ $ayudas->count() }} ayuda/s registrada/s</span>
                        </div>
                        <div class="bg-white bg-opacity-20 px-4 py-2 rounded-lg flex items-center space-x-2">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ $ayudas->where('activo', true)->count() }} activas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover">
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4 mt-4">
                    <button onclick="showModal('addAyudaModal')" class="bg-[#000000] hover:bg-[#3a3a3a] text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2 transition-colors">
                        <i class="fas fa-plus"></i>
                        <span>Nueva Ayuda</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden card-hover">
            <div class="p-6 border-b border-gray-100">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-table text-[#54debd] mr-2"></i>
                        Listado de Ayudas
                    </h3>
                    <div class="w-full md:w-64">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input
                                type="text"
                                id="searchAyudasInput"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-[#54debd] focus:border-[#54debd]"
                                placeholder="Buscar ayudas..."
                                onkeyup="filterAyudas()">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="w-full">
                <table class="w-full divide-y divide-gray-200 table-fixed">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Título</th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6 hidden md:table-cell">Slug</th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Estado</th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Categoría</th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">Formulario</th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="ayudasTableBody">
                        @foreach($ayudas as $ayuda)
                        <tr class="ayuda-row transition-colors hover:bg-gray-50 cursor-pointer" data-status="{{ $ayuda->activa ? 'active' : 'inactive' }}" id="ayuda{{ $ayuda->id }}" onclick="toggleAyudaDetails({{ $ayuda->id }})">
                            <td class="px-3 py-3">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <i class="fas fa-euro text-[#54debd] text-xs"></i>
                                    </div>
                                    <div class="ml-2 min-w-0 flex-1">
                                        <div class="text-sm font-medium text-gray-900 truncate flex items-center">
                                            <span class="truncate">{{ $ayuda->nombre_ayuda }}</span>
                                            <i id="ayuda{{ $ayuda->id }}-icon" class="fas fa-chevron-right ml-1 text-xs text-gray-400 transition-transform flex-shrink-0"></i>
                                        </div>
                                        <div class="text-xs text-gray-500">ID: {{ $ayuda->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3 hidden md:table-cell">
                                <div class="text-xs text-gray-600 truncate" title="{{ $ayuda->slug }}">
                                    {{ $ayuda->slug }}
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                @if($ayuda->activo)
                                <span class="px-2 py-1 inline-flex text-xs leading-4 font-semibold rounded-full bg-green-100 text-green-800">
                                    Activa
                                </span>
                                @else
                                <span class="px-2 py-1 inline-flex text-xs leading-4 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Inactiva
                                </span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <span class="px-2 py-1 inline-flex text-xs leading-4 font-semibold rounded-full bg-blue-100 text-[#54debd] truncate max-w-full" title="{{ ucfirst(str_replace('_', ' ', $ayuda->sector)) }}">
                                    {{ ucfirst(str_replace('_', ' ', $ayuda->sector)) }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                @if($ayuda->questionnaire)
                                <a href="{{ url('dashboard/cuestionarios#questionnaire' . $ayuda->questionnaire->id) }}" class="text-xs text-[#54debd] hover:underline truncate block" title="{{ $ayuda->questionnaire->name }}">
                                    {{ \Illuminate\Support\Str::limit($ayuda->questionnaire->name, 20) }}
                                </a>
                                @else
                                <span class="text-xs text-gray-500">Ninguno</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="event.stopPropagation(); showModal('editAyudaModal', '{{ $ayuda }}')" class="text-[#54debd] hover:text-blue-900" aria-label="Editar ayuda">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="event.stopPropagation(); confirmDeleteAyuda({{ $ayuda->id }}, event)" class="text-red-600 hover:text-red-900" aria-label="Borrar ayuda">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Detalles de la ayuda -->
                        <tr class="hidden" id="ayuda{{ $ayuda->id }}-row">
                            <td colspan="6" class="px-3 py-4 bg-gray-50">
                                <div class="pl-12">
                                    <div class="flex items-center justify-between mt-4 mb-2">
                                        <h4 class="font-medium  text-gray-700">Requisitos para conceder:</h4>
                                        <button onclick="showModal('addRequisitoModal', '{{ $ayuda->requisitos }}')" class="flex items-center gap-1 px-3 py-1 bg-[#54debd] hover:bg-[#54debd] text-white text-sm rounded-md transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Gestionar requisitos
                                        </button>
                                    </div>
                                    <div class="bg-gray-100 p-4 rounded-lg">
                                        <ul class="space-y-3">
                                            @foreach($ayuda->requisitos as $requisito)
                                            <li class="border-l-4 border-indigo-200 pl-4 py-2">
                                                <div class="flex flex-col sm:flex-row sm:items-baseline gap-1">
                                                    <span class="font-medium text-gray-800 flex-1">
                                                        {{ $requisito->question->text ?? 'Pregunta eliminada' }}
                                                    </span>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs bg-indigo-100 text-[#000000] px-2 py-1 rounded-full">
                                                            {{ $requisito->question->type ?? 'N/A' }}
                                                        </span>
                                                        <span class="text-sm text-gray-600">Respuesta esperada:</span>
                                                        <span class="text-sm font-semibold text-green-600 bg-green-50 px-2 py-1 rounded">
                                                            {{ $requisito->formatted_expected_answer }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="pl-12">
                                    <div class="flex items-center justify-between mt-4 mb-2">
                                        <h4 class="font-medium mt-4 mb-2 text-gray-700">Documentos necesarios:</h4>
                                        <button onclick="showModal('addDocumentoModal', {{ json_encode($ayuda->documents->map(function($doc) {
                                            return [
                                                'id' => $doc->documento->id,
                                                'name' => $doc->documento->name,
                                                'description' => $doc->documento->description,
                                                'es_obligatorio' => $doc->es_obligatorio
                                            ];
                                        })) }})" class="flex items-center gap-1 px-3 py-1 bg-[#54debd] hover:bg-[#43c5a9] text-white text-sm rounded-md transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Gestionar documentos
                                        </button>
                                    </div>
                                    <div class="bg-gray-100 p-4 rounded-lg">
                                        <ul class="space-y-3">
                                            @foreach($ayuda->documents as $ayudaDocumento)
                                            <li class="border-l-4 border-indigo-200 pl-4 py-2">
                                                <div class="flex flex-col sm:flex-row sm:items-baseline gap-1">
                                                    <span class="font-medium text-gray-800 flex-1">
                                                        {{ $ayudaDocumento->documento->name }}
                                                    </span>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs bg-indigo-100 text-[#000000] px-2 py-1 rounded-full">
                                                            {{ $ayudaDocumento->documento->description }}
                                                        </span>
                                                        <span class="text-sm text-gray-600">Obligatorio: </span>
                                                        <span class="text-sm font-semibold text-green-600 bg-green-50 px-2 py-1 rounded">
                                                            {{ $ayudaDocumento->es_obligatorio == 1 ? 'Sí' : 'No' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="pl-12">
                                    <div class="flex items-center justify-between mt-4 mb-2">
                                        <h4 class="font-medium mt-4 mb-2 text-gray-700">Documentos necesarios de convivientes:</h4>
                                        <button onclick="showModal('addDocumentoConvivienteModal', {{ json_encode($ayuda->ayudaDocumentosConvivientes->map(function($doc) {
                                            return [
                                                'id' => $doc->id,
                                                'documento_id' => $doc->documento->id,
                                                'name' => $doc->documento->name,
                                                'description' => $doc->documento->description,
                                                'es_obligatorio' => $doc->es_obligatorio
                                            ];
                                        })) }}, {{ $ayuda->id }})" class="flex items-center gap-1 px-3 py-1 bg-purple-500 hover:bg-purple-600 text-white text-sm rounded-md transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Gestionar documentos de convivientes
                                        </button>
                                    </div>
                                    <div class="bg-gray-100 p-4 rounded-lg" id="documentosConvivientesAyuda{{ $ayuda->id }}">
                                        <ul class="space-y-3">
                                            @foreach($ayuda->ayudaDocumentosConvivientes as $ayudaDocumentoConviviente)
                                            <li class="border-l-4 border-purple-200 pl-4 py-2">
                                                <div class="flex flex-col sm:flex-row sm:items-baseline gap-1">
                                                    <span class="font-medium text-gray-800 flex-1">
                                                        {{ $ayudaDocumentoConviviente->documento->name }}
                                                    </span>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs bg-purple-100 text-[#000000] px-2 py-1 rounded-full">
                                                            {{ $ayudaDocumentoConviviente->documento->description }}
                                                        </span>
                                                        <span class="text-sm text-gray-600">Obligatorio: </span>
                                                        <span class="text-sm font-semibold text-green-600 bg-green-50 px-2 py-1 rounded">
                                                            {{ $ayudaDocumentoConviviente->es_obligatorio == 1 ? 'Sí' : 'No' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-6 py-3 flex items-center justify-between border-t border-gray-200">
                <div class="flex-1 flex justify-between sm:hidden"></div>
            </div>
        </div>
    </main>

    <!-- Add Ayuda Modal -->
    <div id="addAyudaModal" class="fixed inset-0 overflow-y-auto z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <form method="POST" action="{{ route('ayudas.store') }}">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-[#54debd] sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-euro text-[#54debd]"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Nueva Ayuda</h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="nombre_ayuda" class="block text-sm font-medium text-gray-700">Nombre de la ayuda *</label>
                                        <input type="text" name="nombre_ayuda" id="nombre_ayuda" required
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="sector" class="block text-sm font-medium text-gray-700">Sector *</label>
                                        <select name="sector" id="sector" required class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 sm:text-sm">
                                            <option value="">Seleccione un sector</option>
                                            @foreach ($sectores as $sector)
                                            <option value="{{ $sector }}">{{ ucfirst($sector) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="questionnaire_id" class="block text-sm font-medium text-gray-700">Cuestionario *</label>
                                        <select name="questionnaire_id" id="questionnaire_id" class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 sm:text-sm">
                                            <option value="">Seleccione un cuestionario</option>
                                            @foreach($questionnaires as $cuestionario)
                                            <option value="{{ $cuestionario->id }}">{{ $cuestionario->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Presupuesto -->
                                    <div>
                                        <label for="presupuesto" class="block text-sm font-medium text-gray-700">Presupuesto (€)</label>
                                        <input type="number" name="presupuesto" id="presupuesto" step="0.01" class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 sm:text-sm">
                                    </div>

                                    <!-- Cuantía por usuario -->
                                    <div>
                                        <label for="cuantia_usuario" class="block text-sm font-medium text-gray-700">Cuantía por usuario (€)</label>
                                        <input type="number" name="cuantia_usuario" id="cuantia_usuario" step="0.01" class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 sm:text-sm">
                                    </div>

                                    <!-- Fechas -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">Fecha de inicio</label>
                                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 sm:text-sm">
                                        </div>
                                        <div>
                                            <label for="fecha_fin" class="block text-sm font-medium text-gray-700">Fecha de fin (opcional)</label>
                                            <input type="date" name="fecha_fin" id="fecha_fin" class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 sm:text-sm">
                                        </div>
                                    </div>

                                    <!-- Órgano -->
                                    <div>
                                        <label for="organo_id" class="block text-sm font-medium text-gray-700">Órgano *</label>
                                        <select name="organo_id" id="organo_id" required class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 sm:text-sm">
                                            <option value="">Seleccione un órgano</option>
                                            @foreach($organos as $organo)
                                            <option value="{{ $organo->id }}">{{ $organo->nombre_organismo }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Estado (toggle) -->
                                    <div class="flex items-center">
                                        <span class="mr-3 text-sm font-medium text-gray-700">Estado</span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="activo" value="1" class="sr-only peer" checked>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#54debd]"></div>
                                            <span class="ml-3 text-sm font-medium text-gray-700">Activa</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#54debd] text-base font-medium text-white hover:bg-[#4bc6b5] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:ml-3 sm:w-auto sm:text-sm">
                            Crear Ayuda
                        </button>
                        <button type="button" onclick="closeModal('addAyudaModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Editar Ayuda Modal -->
    <div id="editAyudaModal" class="fixed inset-0 overflow-y-auto z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <form id="editAyudaForm" action="{{ route('ayudas.update', $ayuda->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-[#54debd] sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-euro text-[#54debd]"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Editar ayuda</h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="nombre_ayuda" class="block text-sm font-medium text-gray-700">Nombre de la ayuda *</label>
                                        <input type="text" name="nombre_ayuda" id="nombre_ayuda" required
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="sector" class="block text-sm font-medium text-gray-700">Sector *</label>
                                        <select name="sector" id="sector" required class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 sm:text-sm">
                                            <option value="">Seleccione un sector</option>
                                            @foreach ($sectores as $sector)
                                            <option value="{{ $sector }}">{{ ucfirst($sector) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="questionnaire_id" class="block text-sm font-medium text-gray-700">Cuestionario *</label>
                                        <select name="questionnaire_id" id="questionnaire_id" class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 sm:text-sm">
                                            <option value="">Seleccione un cuestionario</option>
                                            @foreach($questionnaires as $cuestionario)
                                            <option value="{{ $cuestionario->id }}">{{ $cuestionario->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Presupuesto -->
                                    <div>
                                        <label for="presupuesto" class="block text-sm font-medium text-gray-700">Presupuesto (€)</label>
                                        <input type="number" name="presupuesto" id="presupuesto" step="0.01" class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 sm:text-sm">
                                    </div>

                                    <!-- Cuantía por usuario -->
                                    <div>
                                        <label for="cuantia_usuario" class="block text-sm font-medium text-gray-700">Cuantía por usuario (€)</label>
                                        <input type="number" name="cuantia_usuario" id="cuantia_usuario" step="0.01" class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 sm:text-sm">
                                    </div>

                                    <!-- Fechas -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">Fecha de inicio</label>
                                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 sm:text-sm">
                                        </div>
                                        <div>
                                            <label for="fecha_fin" class="block text-sm font-medium text-gray-700">Fecha de fin (opcional)</label>
                                            <input type="date" name="fecha_fin" id="fecha_fin" class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 sm:text-sm">
                                        </div>
                                    </div>

                                    <!-- Órgano -->
                                    <div>
                                        <label for="organo_id" class="block text-sm font-medium text-gray-700">Órgano *</label>
                                        <select name="organo_id" id="organo_id" required class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 sm:text-sm">
                                            <option value="">Seleccione un órgano</option>
                                            @foreach($organos as $organo)
                                            <option value="{{ $organo->id }}">{{ $organo->nombre_organismo }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Estado (toggle) -->
                                    <div class="flex items-center">
                                        <span class="mr-3 text-sm font-medium text-gray-700">Estado</span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="activo" value="1" class="sr-only peer" checked>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#54debd]"></div>
                                            <span class="ml-3 text-sm font-medium text-gray-700">Activa</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#54debd] text-base font-medium text-white hover:bg-[#4bc6b5] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:ml-3 sm:w-auto sm:text-sm">
                            Editar ayuda
                        </button>
                        <button type="button" onclick="closeModal('editAyudaModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 overflow-y-auto z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Confirmar eliminación
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500" id="deleteModalText">
                                        ¿Estás seguro de que deseas eliminar este elemento?
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Eliminar
                        </button>
                        <button type="button" onclick="closeModal('deleteModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para añadir requisitos -->
    <div id="addRequisitoModal" class="fixed inset-0 overflow-y-auto z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Gestionar requisitos
                            </h3>
                            <div class="mt-4">
                                <!-- Requisitos existentes -->
                                <div class="mb-8">
                                    <h4 class="font-medium text-gray-700 mb-2">Requisitos actuales:</h4>
                                    <div class="bg-gray-100 p-4 rounded-lg">
                                        <ul class="space-y-3" id="requisitosList">
                                            <!-- Los requisitos existentes se cargarán aquí dinámicamente -->
                                        </ul>
                                    </div>
                                </div>

                                <!-- Añadir nuevo requisito -->
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-2">Añadir nuevo requisito:</h4>
                                    <form method="POST" action="{{ route('ayudarequisito.store') }}" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="ayuda_id" value="{{ $ayuda->id }}">

                                        <div>
                                            <label for="question_id" class="block text-sm font-medium text-gray-700">Selecciona una pregunta:</label>
                                            <select id="question_id" name="question_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base bg-gray-50 border border-gray-300 text-gray-700 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm rounded-md shadow-sm">
                                                @foreach($allQuestions as $question)
                                                <option value="{{ $question->id }}">{{ $question->text }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Respuesta esperada:</label>
                                            <div class="mt-1 space-x-4">
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="respuesta_expected" value="1" class="focus:ring-[#54debd] h-4 w-4 text-[#54debd] border-gray-300" checked>
                                                    <span class="ml-2 text-sm text-gray-700">Sí</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="respuesta_expected" value="0" class="focus:ring-[#54debd] h-4 w-4 text-[#54debd] border-gray-300">
                                                    <span class="ml-2 text-sm text-gray-700">No</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="flex justify-end">
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#54debd] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#54debd] active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                                                Añadir requisito
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeModal('addRequisitoModal')" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#54debd] text-base font-medium text-white hover:bg-[#54debd] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:ml-3 sm:w-auto sm:text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para gestionar documentos de convivientes -->
    <div id="addDocumentoConvivienteModal" class="fixed inset-0 overflow-y-auto z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Gestionar documentos de convivientes
                            </h3>
                            <div class="mt-4">
                                <!-- Documentos existentes -->
                                <div class="mb-8">
                                    <h4 class="font-medium text-gray-700 mb-2">Documentos actuales de convivientes:</h4>
                                    <div class="bg-gray-100 p-4 rounded-lg">
                                        <ul class="space-y-3" id="documentosConvivientesList">
                                            <!-- Los documentos existentes se cargarán aquí dinámicamente -->
                                        </ul>
                                    </div>
                                </div>

                                <!-- Añadir nuevo documento de conviviente -->
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-2">Añadir nuevo documento de conviviente:</h4>
                                    <div id="mensajeConviviente" class="mb-4 hidden"></div>
                                    <form id="formDocumentoConviviente" method="POST" action="{{ route('ayudadocumentoconviviente.store') }}" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="ayuda_id" id="ayuda_id_conviviente" value="">

                                        <div>
                                            <label for="document_id_conviviente" class="block text-sm font-medium text-gray-700">Selecciona un documento:</label>
                                            <select id="document_id_conviviente" name="document_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base bg-gray-50 border border-gray-300 text-gray-700 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm rounded-md shadow-sm">
                                                @foreach($allDocuments as $document)
                                                <option value="{{ $document->id }}">{{ $document->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">¿Es obligatorio?</label>
                                            <div class="mt-1 space-x-4">
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="es_obligatorio" value="1" class="focus:ring-[#54debd] h-4 w-4 text-[#54debd] border-gray-300" checked>
                                                    <span class="ml-2 text-sm text-gray-700">Sí</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="es_obligatorio" value="0" class="focus:ring-[#54debd] h-4 w-4 text-[#54debd] border-gray-300">
                                                    <span class="ml-2 text-sm text-gray-700">No</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="flex justify-end">
                                            <button type="submit" id="btnSubmitConviviente" class="inline-flex items-center px-4 py-2 bg-purple-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-600 active:bg-purple-700 focus:outline-none focus:border-purple-700 focus:ring focus:ring-purple-300 disabled:opacity-25 transition">
                                                Añadir documento de conviviente
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeModal('addDocumentoConvivienteModal')" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-500 text-base font-medium text-white hover:bg-purple-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Cerrar
                    </button>
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
                    Panel de administración por <a href="https://tutramitefacil.es/" target="_blank" class="text-[#54debd] hover:text-indigo-800">TuTrámiteFácil</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Notification -->
    @if(session('success'))
    <div id="toast" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center justify-between">
        <span>{{ session('success') }}</span>
        <button onclick="document.getElementById('toast').remove()" class="ml-4">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @elseif($errors->any())
    <div id="toast" class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center justify-between">
        <ul>
            @foreach($errors->all() as $error)
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

        function showModal(modalId, ayudaId = null, ayudaIdValue = null) {
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
                    li.className = 'border-l-4 border-indigo-200 pl-4 py-2 group hover:bg-gray-50 transition-colors';
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
                    li.className = 'border-l-4 border-indigo-200 pl-4 py-2 group hover:bg-gray-50 transition-colors';
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
            } else if (modalId == 'addDocumentoConvivienteModal' && ayudaId !== null && typeof ayudaId === 'object' && ayudaId.length !== undefined) {
                const documentosConvivientesList = document.getElementById('documentosConvivientesList');
                documentosConvivientesList.innerHTML = '';
                
                // El tercer parámetro es el ID de la ayuda
                if (ayudaIdValue) {
                    document.getElementById('ayuda_id_conviviente').value = ayudaIdValue;
                }

                ayudaId.forEach(documento => {
                    let answerText = documento.es_obligatorio ? "Sí" : "No";

                    const li = document.createElement('li');
                    li.className = 'border-l-4 border-purple-200 pl-4 py-2 group hover:bg-gray-50 transition-colors';
                    li.innerHTML = `
                        <div class="flex flex-col sm:flex-row sm:items-baseline gap-1">
                            <span class="font-medium text-gray-800 flex-1">
                                ${documento.name}
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full">
                                    ${documento.description || ''}
                                </span>
                                <span class="text-sm text-gray-600">¿Obligatorio?</span>
                                <span class="text-sm font-semibold text-green-600 bg-green-50 px-2 py-1 rounded">
                                    ${answerText}
                                </span>
                                        <button type="button" 
                                                class="text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity"
                                                onclick="eliminarDocumentoConviviente(${documento.id}, this)">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                            </div>
                        </div>
                    `;

                    documentosConvivientesList.appendChild(li);
                });
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            
            // Si se cierra el modal de documentos de convivientes, actualizar la vista
            if (modalId === 'addDocumentoConvivienteModal') {
                const ayudaIdInput = document.getElementById('ayuda_id_conviviente');
                if (ayudaIdInput && ayudaIdInput.value) {
                    actualizarDocumentosConvivientesVista(ayudaIdInput.value);
                }
            }
        }

        // Función para actualizar la vista de documentos de convivientes
        function actualizarDocumentosConvivientesVista(ayudaId) {
            fetch(`/ayudadocumentoconviviente/ayuda/${ayudaId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const container = document.getElementById(`documentosConvivientesAyuda${ayudaId}`);
                    if (container) {
                        const ul = container.querySelector('ul');
                        if (ul) {
                            ul.innerHTML = '';
                            
                            if (data.documentos.length === 0) {
                                ul.innerHTML = '<li class="text-gray-500 text-sm">No hay documentos de convivientes configurados</li>';
                            } else {
                                data.documentos.forEach(documento => {
                                    const answerText = documento.es_obligatorio ? "Sí" : "No";
                                    const li = document.createElement('li');
                                    li.className = 'border-l-4 border-purple-200 pl-4 py-2';
                                    li.innerHTML = `
                                        <div class="flex flex-col sm:flex-row sm:items-baseline gap-1">
                                            <span class="font-medium text-gray-800 flex-1">
                                                ${documento.name}
                                            </span>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs bg-purple-100 text-[#000000] px-2 py-1 rounded-full">
                                                    ${documento.description || ''}
                                                </span>
                                                <span class="text-sm text-gray-600">Obligatorio: </span>
                                                <span class="text-sm font-semibold text-green-600 bg-green-50 px-2 py-1 rounded">
                                                    ${answerText}
                                                </span>
                                            </div>
                                        </div>
                                    `;
                                    ul.appendChild(li);
                                });
                            }
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error al actualizar documentos de convivientes:', error);
            });
        }

        function confirmDeleteAyuda(id, event) {
            event.stopPropagation();
            document.getElementById('deleteForm').action = `/ayudas/${id}`;
            document.getElementById('deleteModalText').textContent = '¿Estás seguro de que deseas eliminar esta ayuda? Esta acción no se puede deshacer.';
            showModal('deleteModal');
        }

        function confirmDeleteDocument(id) {
            document.getElementById('deleteForm').action = `/documento/${id}`;
            document.getElementById('deleteModalText').textContent = '¿Estás seguro de que deseas eliminar este documento? Esta acción no se puede deshacer.';
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

        // Manejar el formulario de documentos de convivientes con AJAX
        document.addEventListener('DOMContentLoaded', function() {
            const formDocumentoConviviente = document.getElementById('formDocumentoConviviente');
            if (formDocumentoConviviente) {
                formDocumentoConviviente.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const btnSubmit = document.getElementById('btnSubmitConviviente');
                    const mensajeDiv = document.getElementById('mensajeConviviente');
                    const formData = new FormData(this);
                    
                    // Deshabilitar el botón mientras se procesa
                    btnSubmit.disabled = true;
                    btnSubmit.textContent = 'Guardando...';
                    
                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Mostrar mensaje de éxito
                            mensajeDiv.className = 'mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded';
                            mensajeDiv.textContent = data.message || 'Documento añadido correctamente';
                            mensajeDiv.classList.remove('hidden');
                            
                            // Añadir el nuevo documento a la lista
                            const documentosConvivientesList = document.getElementById('documentosConvivientesList');
                            const nuevoDocumento = data.documento;
                            const answerText = nuevoDocumento.es_obligatorio ? "Sí" : "No";
                            
                            const li = document.createElement('li');
                            li.className = 'border-l-4 border-purple-200 pl-4 py-2 group hover:bg-gray-50 transition-colors';
                            li.innerHTML = `
                                <div class="flex flex-col sm:flex-row sm:items-baseline gap-1">
                                    <span class="font-medium text-gray-800 flex-1">
                                        ${nuevoDocumento.name}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full">
                                            ${nuevoDocumento.description || ''}
                                        </span>
                                        <span class="text-sm text-gray-600">¿Obligatorio?</span>
                                        <span class="text-sm font-semibold text-green-600 bg-green-50 px-2 py-1 rounded">
                                            ${answerText}
                                        </span>
                                        <form method="POST" action="/ayudadocumentoconviviente/${nuevoDocumento.id}" class="inline documento-conviviente-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    class="text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity"
                                                    onclick="eliminarDocumentoConviviente(${nuevoDocumento.id}, this)">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            `;
                            
                            documentosConvivientesList.appendChild(li);
                            
                            // Limpiar el formulario
                            this.reset();
                            const ayudaId = formData.get('ayuda_id');
                            document.getElementById('ayuda_id_conviviente').value = ayudaId;
                            
                            // Actualizar la vista principal
                            actualizarDocumentosConvivientesVista(ayudaId);
                            
                            // Ocultar mensaje después de 3 segundos
                            setTimeout(() => {
                                mensajeDiv.classList.add('hidden');
                            }, 3000);
                        } else {
                            // Mostrar mensaje de error
                            mensajeDiv.className = 'mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded';
                            mensajeDiv.textContent = data.message || 'Error al añadir el documento';
                            mensajeDiv.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        mensajeDiv.className = 'mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded';
                        mensajeDiv.textContent = 'Error al procesar la solicitud';
                        mensajeDiv.classList.remove('hidden');
                    })
                    .finally(() => {
                        // Rehabilitar el botón
                        btnSubmit.disabled = false;
                        btnSubmit.textContent = 'Añadir documento de conviviente';
                    });
                });
            }
        });

        // Función para eliminar documento de conviviente sin recargar
        function eliminarDocumentoConviviente(id, button) {
            if (!confirm('¿Estás seguro de que quieres desacoplar este documento de conviviente?')) {
                return;
            }
            
            const form = button.closest('form');
            const li = button.closest('li');
            
            const token = document.querySelector('meta[name="csrf-token"]')?.content || 
                         form.querySelector('input[name="_token"]')?.value ||
                         document.querySelector('input[name="_token"]')?.value;
            
            fetch(`/ayudadocumentoconviviente/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    li.remove();
                    
                    // Actualizar la vista principal
                    if (data.ayuda_id) {
                        actualizarDocumentosConvivientesVista(data.ayuda_id);
                    }
                    
                    // Mostrar mensaje de éxito temporal
                    const mensajeDiv = document.getElementById('mensajeConviviente');
                    mensajeDiv.className = 'mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded';
                    mensajeDiv.textContent = data.message || 'Documento eliminado correctamente';
                    mensajeDiv.classList.remove('hidden');
                    setTimeout(() => {
                        mensajeDiv.classList.add('hidden');
                    }, 3000);
                } else {
                    alert(data.message || 'Error al eliminar el documento');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        }
    </script>
</body>

</html>