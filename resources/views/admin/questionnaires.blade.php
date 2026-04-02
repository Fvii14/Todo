<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - Cuestionarios</title>
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

        .question-toggle {
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
        }

        .question-toggle.active {
            max-height: 1000px;
        }

        .rotate-90 {
            transform: rotate(90deg);
        }

        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .editable-field {
            cursor: pointer;
            transition: all 0.2s ease;
            border-bottom: 1px dashed transparent;
        }

        .editable-field:hover {
            border-bottom: 1px dashed #818cf8;
            background-color: #f8fafc;
        }

        .editable-field.active {
            background-color: #e0e7ff;
            border-bottom: 1px dashed #6366f1;
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

        /* Añade hasta delay-800 */
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

<body class="bg-gray-100 font-sans text-gray-800 min-h-screen">

  <!-- Header -->
  @include('layouts.headerbackoffice')

  <main class="max-w-7xl mx-auto px-4 py-8 space-y-8">


   
    <!-- Welcome Card -->
    <div class="bg-white rounded-lg shadow p-6 flex flex-col md:flex-row md:justify-between md:items-center">
      <div>
        <h2 class="text-2xl font-semibold mb-1">Gestión de Cuestionarios</h2>
        <p class="text-gray-600">Administra todos los cuestionarios y sus preguntas</p>
      </div>
      <div class="mt-4 md:mt-0 flex space-x-4">
        <div class="bg-indigo-50 px-4 py-2 rounded flex items-center space-x-2">
          <i class="fas fa-clipboard text-[#54debd]"></i>
          <span>{{ $questionnaires->count() }} cuestionario/s</span>
        </div>
        <div class="bg-indigo-50 px-4 py-2 rounded flex items-center space-x-2">
          <i class="fas fa-question text-[#54debd]"></i>
          <span>{{ $allQuestions->count() }} pregunta/s</span>
        </div>
      </div>
    </div>

        <!-- Filters and Actions -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover">
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4 mt-4">
                    <button onclick="showModal('addQuestionnaireModal')"
                        class="bg-[#54debd] hover:bg-[#43c5a9] text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2 transition-colors">
                        <i class="fas fa-plus"></i>
                        <span>Nuevo Cuestionario</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md overflow-hidden card-hover">
            <div class="p-6 border-b border-gray-100">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-table text-[#54debd] mr-2"></i>
                        Listado de Cuestionarios
                    </h3>
                    <div class="w-full md:w-64">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" id="searchQuestionnairesInput"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-[#54debd] focus:border-[#54debd]"
                                placeholder="Buscar cuestionarios..." onkeyup="filterQuestionnaires()">
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
                                Estado</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Preguntas</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Creación</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="questionnairesTableBody">
                        @foreach($questionnaires as $questionnaire)
                            <tr class="questionnaire-row transition-colors hover:bg-gray-50"
                                data-status="{{ $questionnaire->active ? 'active' : 'inactive' }}"
                                id="questionnaire{{ $questionnaire->id }}"
                                onclick="toggleQuestions('questionnaire{{ $questionnaire->id }}')">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <i class="fas fa-clipboard-list text-[#54debd]"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $questionnaire->name }}</div>
                                            <div class="text-sm text-gray-500">ID: {{ $questionnaire->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($questionnaire->active)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Activo
                                        </span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap cursor-pointer">
                                    <div class="text-sm text-gray-900 flex items-center">
                                        <span class="editable-field">{{ $questionnaire->questions_count }}</span>
                                        <i class="fas fa-chevron-right ml-2 text-xs transition-transform duration-200"
                                            id="chevron{{ $questionnaire->id }}"></i>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $questionnaire->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button
                                        onclick="event.stopPropagation(); showEditQuestionnaireModal({{ $questionnaire->id }}, '{{ $questionnaire->name }}', {{ $questionnaire->active ? 'true' : 'false' }})"
                                        class="text-[#54debd] hover:text-[#54debe] mr-2">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button
                                        onclick="event.stopPropagation(); confirmDeleteQuestionnaire({{ $questionnaire->id }})"
                                        class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <a href="{{ route('logicacuestionarios', ['id' => $questionnaire->id]) }}" 
                                        class="text-[#54debd] hover:text-[#54debd]">
                                         <i class="fas fa-cogs"></i>
                                     </a>        
                                </td>
                            </tr>
                            <!-- Questions row -->
                            <tr  id="questionnaire{{ $questionnaire->id }}-row">
                                <td colspan="5" class="px-6 py-4 bg-gray-50">
                                    <div class="pl-12">
                                        <div class="flex items-center justify-between mt-4 mb-2">
                                            <h4 class="font-medium text-gray-700">Preguntas:</h4>
                                            <button
                                                onclick="showModal('addQuestionsModal', '{{ $questionnaire->questions }}')"
                                                class="flex items-center gap-1 px-3 py-1 bg-[#54debd] hover:bg-[#43c5a9] text-white text-sm rounded-md transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4v16m8-8H4" />
                                                </svg>
                                                Gestionar preguntas
                                            </button>
                                        </div>
                                        <ul class="space-y-2">
                                            @foreach($questionnaire->questions as $question)
                                                <li class="bg-gray-100 p-4 rounded mb-4">
                                                    <div class="flex items-center justify-between">
                                                        <span class="font-medium text-gray-800">{{ $question->text }}</span>
                                                        <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded">{{ $question->type }}</span>
                                                    </div>

                                                    @if(in_array($question->type, ['select', 'multiple']) && !empty($question->options))
                                                        <ul class="mt-3 flex flex-wrap">
                                                            @foreach($question->options as $option)
                                                                <li class="text-sm bg-white border border-gray-300 rounded px-3 py-1 mr-2 mb-2">
                                                                    {{ $option }}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-6 py-3 flex items-center justify-between border-t border-gray-200">
                <div class="flex-1 flex justify-between sm:hidden">
                    <a href="#"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Anterior
                    </a>
                    <a href="#"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Siguiente
                    </a>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between"></div>
            </div>
        </div>
    </main>

    <!-- Add Questionnaire Modal -->
    <div id="addQuestionnaireModal" class="fixed inset-0 overflow-y-auto z-50 hidden" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay" aria-hidden="true">
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <form method="POST" action="{{ route('questionnaires.store') }}">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-clipboard-list text-[#54debd]"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Nuevo Cuestionario
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <!-- Nombre del cuestionario -->
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre del
                                            cuestionario</label>
                                        <input type="text" name="name" id="name" required
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm">
                                    </div>

                                    <!-- Estado (toggle) -->
                                    <div class="flex items-center">
                                        <span class="mr-3 text-sm font-medium text-gray-700">Estado</span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="active" value="1" class="sr-only peer" checked>
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#54debd]">
                                            </div>
                                            <span class="ml-3 text-sm font-medium text-gray-700">Activo</span>
                                        </label>
                                    </div>

                                    <!-- Pestañas para selección de preguntas -->
                                    <div class="mt-4">
                                        <!-- Contenido de pestaña de preguntas existentes -->
                                        <div id="existing-questions-tab" class="pt-4">

                                            <!-- Filtros añadidos -->
                                            <div class="mb-4">
                                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Filtrar preguntas
                                                </h4>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <div>
                                                        <label for="filterCategory"
                                                            class="block text-xs font-medium text-gray-600">Categoría</label>
                                                        <select id="filterCategory">
                                                            <option value="">TODAS</option>
                                                            @foreach($categorias as $categoria)
                                                                <option value="{{ $categoria }}">
                                                                    {{ strtoupper($categoria) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-search text-gray-400"></i>
                                                </div>
                                                <input type="text" id="questionSearch" placeholder="Buscar preguntas..."
                                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md bg-gray-50 focus:ring-indigo-500 focus:border-[#54debd] sm:text-sm">
                                            </div>

                                            <div class="mt-3 space-y-2 max-h-60 overflow-y-auto"
                                                id="availableQuestionsList">
                                                @foreach($allQuestions as $question)
                                                    <div class="flex items-start question-row"
                                                        data-sector="{{ $question->sector }}"
                                                        data-categoria="{{ $question->categoria }}">
                                                        <div class="flex items-center h-5 mt-1">
                                                            <input id="question-{{ $question->id }}" name="questions[]"
                                                                type="checkbox" value="{{ $question->id }}"
                                                                class="focus:ring-[#54debd] h-4 w-4 text-[#54debd] border-gray-300 rounded">
                                                        </div>
                                                        <div class="ml-3 text-sm">
                                                            <label for="question-{{ $question->id }}"
                                                                class="font-medium text-gray-700">{{ $question->text }}</label>
                                                            <p class="text-gray-500 text-xs mt-1">
                                                                <span
                                                                    class="bg-gray-100 px-2 py-1 rounded">{{ $question->type }}</span>
                                                                <span class="ml-2">{{ $question->slug }}</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Contenido de pestaña de nuevas preguntas -->
                                        <div id="new-questions-tab" class="pt-4 hidden">
                                            <div id="newQuestionsContainer" class="space-y-4">
                                                <!-- Aquí se añadirán dinámicamente los campos de nuevas preguntas -->
                                            </div>
                                            <button type="button" onclick="addNewQuestionField()"
                                                class="mt-3 inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-[#54debd] hover:bg-[#54debd] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd]">
                                                <i class="fas fa-plus mr-1"></i> Añadir otra pregunta
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#54debd] text-base font-medium text-white hover:bg-[#54debd] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:ml-3 sm:w-auto sm:text-sm">
                                Guardar cuestionario
                            </button>
                            <button type="button" onclick="closeModal('addQuestionnaireModal')"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    </div>

    <!-- Edit Questionnaire Modal -->
    <div id="editQuestionnaireModal" class="fixed inset-0 overflow-y-auto z-50 hidden" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay" aria-hidden="true">
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="editQuestionnaireForm" method="POST" action="">
                    @csrf
                    @method('PUT') <!-- Usamos PUT para actualizar -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-edit text-[#54debd]"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Editar Cuestionario
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <!-- Nombre del cuestionario -->
                                    <div>
                                        <label for="edit_name"
                                            class="block text-sm font-medium text-gray-700">Nombre</label>
                                        <input type="text" name="name" id="edit_name" required
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm">
                                    </div>

                                    <!-- Estado (toggle) -->
                                    <div class="flex items-center">
                                        <span class="mr-3 text-sm font-medium text-gray-700">Estado</span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="active" id="edit_active" value="1"
                                                class="sr-only peer">
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#54debd]">
                                            </div>
                                            <span class="ml-3 text-sm font-medium text-gray-700"
                                                id="edit_status_text">Inactivo</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#54debd] text-base font-medium text-white hover:bg-[#4bc6b5] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar cambios
                        </button>
                        <button type="button" onclick="closeModal('editQuestionnaireModal')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Question Modal -->
    <div id="editQuestionModal" class="fixed inset-0 overflow-y-auto z-50 hidden" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay" aria-hidden="true">
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="editQuestionForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-question text-[#54debd]"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Editar Pregunta
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <!-- Texto de la pregunta -->
                                    <div>
                                        <label for="edit_question_text"
                                            class="block text-sm font-medium text-gray-700">Texto</label>
                                        <input type="text" name="text" id="edit_question_text" required
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm">
                                    </div>

                                    <!-- Tipo de pregunta -->
                                    <div>
                                        <label for="edit_question_type"
                                            class="block text-sm font-medium text-gray-700">Tipo</label>
                                        <select name="type" id="edit_question_type" required
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm">
                                            <option value="string">Texto</option>
                                            <option value="select">Selección</option>
                                            <option value="boolean">Sí/No</option>
                                            <option value="date">Fecha</option>
                                            <option value="integer">Número</option>
                                        </select>
                                    </div>

                                    <!-- Slug -->
                                    <div>
                                        <label for="edit_question_slug"
                                            class="block text-sm font-medium text-gray-700">Slug</label>
                                        <input type="text" disabled name="slug" id="edit_question_slug" class="mt-1 block w-full bg-gray-100 border border-gray-200 rounded-md py-2 px-3 text-gray-500 cursor-not-allowed sm:text-sm" style="pointer-events: none;">
                                    </div>

                                    <!-- Selector de Categoría -->
                                    <div>
                                        <label for="edit_question_category"
                                            class="block text-sm font-medium text-gray-700">Categoría</label>
                                        <div class="flex space-x-2">
                                            <select name="category" id="edit_question_category" required
                                                class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm uppercase">
                                                <option value="">Seleccionar categoría</option>
                                                @foreach ($categorias as $categoria)
                                                    <option value="{{ $categoria->id }}">
                                                        {{ strtoupper($categoria->name) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" onclick="showCreateCategoryModal()" 
                                                class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#54debd] text-base font-medium text-white hover:bg-[#54debd] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar cambios
                        </button>
                        <button type="button" onclick="closeModal('editQuestionModal')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Existing Question Modal -->
    <div id="addQuestionModal" class="fixed inset-0 overflow-y-auto z-50 hidden" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay" aria-hidden="true">
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        </div>
    </div>

    <div id="addQuestionsModal" class="fixed inset-0 overflow-y-auto z-50 hidden" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay" aria-hidden="true">
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Gestionar preguntas
                            </h3>
                            <div class="mt-4">
                                <div class="mb-8">
                                    <h4 class="font-medium text-gray-700 mb-2">Preguntas actuales:</h4>
                                    <div class="bg-gray-100 p-4 rounded-lg">
                                        <ul class="space-y-3" id="preguntasList">
                                        </ul>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="font-medium text-gray-700 mb-2">Añadir nueva pregunta:</h4>
                                    <form method="POST" action="{{ route('questionnairequestion.store') }}"
                                        class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="questionnaire_id" id="questionnaire_id" value="#">

                                        <div>
                                            <label for="document_id"
                                                class="block text-sm font-medium text-gray-700">Selecciona una
                                                pregunta:</label>
                                            <select id="document_id" name="document_id"
                                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base bg-gray-50 border border-gray-300 text-gray-700 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm rounded-md shadow-sm">
                                                @foreach($allQuestions as $question)
                                                    <option value="{{ $question->id }}">{{ $question->text }} -
                                                        {{ $question->type }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="flex justify-end">
                                            <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-[#54debd] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#54debd] active:bg-[#54debd] focus:outline-none focus:border-[#54debd] focus:ring focus:ring-[#54debd] disabled:opacity-25 transition">
                                                Añadir pregunta
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeModal('addQuestionsModal')"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#54debd] text-base font-medium text-white hover:bg-[#54debd] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:ml-3 sm:w-auto sm:text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 overflow-y-auto z-50 hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay" aria-hidden="true">
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
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
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Eliminar
                        </button>
                        <button type="button" onclick="closeModal('deleteModal')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6 mt-12">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <div class="w-8 h-8 bg-[#f8f8f8] rounded-lg flex items-center justify-center">
                        <i class="fas fa-folder-open text-[#54debd]"></i>
                    </div>
                    <span class="font-medium">Collector by TTF</span>
                </div>
                <div class="text-sm text-gray-500">
                    Panel de administración por <a href="https://tutramitefacil.es/" target="_blank"
                        class="text-[#54debd] hover:text-[#54debd]">TuTrámiteFácil</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Notification -->
    @if(session('success'))
        <div id="toast"
            class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button onclick="document.getElementById('toast').remove()" class="ml-4">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @elseif(session('error'))
        <div id="toast"
            class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center justify-between">
            <span>{{ session('error') }}</span>
            <button onclick="document.getElementById('toast').remove()" class="ml-4">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <script>
        // Auto-hide the toast after 5 seconds
        setTimeout(() => {
            const toast = document.getElementById('toast');
            if (toast) toast.remove();
        }, 5000);

        // Toggle questions visibility
        function toggleQuestions(questionnaireId) {
            // Evita que se active al hacer clic en otros elementos
            event.stopPropagation();

            const row = document.getElementById(`${questionnaireId}-row`);
            const chevron = document.getElementById(`chevron${questionnaireId.replace('questionnaire', '')}`);

            // Alternar visibilidad
            row.classList.toggle('hidden');

            // Alternar icono
            if (chevron) {
                chevron.classList.toggle('rotate-90');
            }

            // Cerrar otros cuestionarios abiertos
            document.querySelectorAll('[id$="-row"]').forEach(otherRow => {
                if (otherRow.id !== `${questionnaireId}-row` && !otherRow.classList.contains('hidden')) {
                    otherRow.classList.add('hidden');
                    const otherChevronId = otherRow.id.replace('-row', '').replace('questionnaire', 'chevron');
                    const otherChevron = document.getElementById(otherChevronId);
                    if (otherChevron) otherChevron.classList.remove('rotate-90');
                }
            });
        }

        // Show modal
        function showModal(modalId, ayudaId = null) {
            document.getElementById(modalId).classList.remove('hidden');
            if (modalId == "addQuestionsModal" && ayudaId) {
                ayudaId = JSON.parse(ayudaId)
                const preguntasList = document.getElementById('preguntasList');
                preguntasList.innerHTML = '';
                console.log(ayudaId)

                ayudaId.forEach(pregunta => {
                    let answerText = pregunta.es_obligatorio ? "Sí" : "No";

                    const li = document.createElement('li');
                    li.className = 'border-l-4 border-indigo-200 pl-4 py-2 group hover:bg-gray-50 transition-colors';
                    li.innerHTML = `
                        <div class="flex flex-col sm:flex-row sm:items-baseline gap-1">
                            <span class="font-medium text-gray-800 flex-1">
                                ${pregunta.text}
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">
                                    ${pregunta.type}
                                </span>
                                </span>
                                <form method="POST" action="/questionnaire-questions/${pregunta.pivot.questionnaire_id}/${pregunta.id}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity"
                                            onclick="return confirm('¿Estás seguro de que quieres desacoplar este pregunta?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    `;

                    preguntasList.appendChild(li);
                    document.getElementById('questionnaire_id').value = pregunta.pivot.questionnaire_id
                });
            }
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Show add question modal
        function showAddQuestionModal(questionnaireId) {
            document.getElementById('addQuestionQuestionnaireId').value = questionnaireId;
            loadAvailableQuestions(questionnaireId);
            showModal('addQuestionModal');
        }

        // Show create question modal
        function showCreateQuestionModal() {
            document.getElementById('new_question_text').value = '';
            document.getElementById('new_question_type').value = 'string';
            document.getElementById('new_question_slug').value = '';

            showModal('createQuestionModal');
        }

        // Show edit questionnaire modal
        function showEditQuestionnaireModal(id, name, isActive) {
            const form = document.getElementById('editQuestionnaireForm');
            form.action = `/questionnaires/${id}`; // Actualiza la ruta del formulario

            document.getElementById('edit_name').value = name;
            document.getElementById('edit_active').checked = isActive;

            // Actualiza el texto del estado (Activo/Inactivo)
            const statusText = document.getElementById('edit_status_text');
            statusText.textContent = isActive ? 'Activo' : 'Inactivo';

            showModal('editQuestionnaireModal');
        }

        // Show edit question modal
        function showEditQuestionModal(questionId, text, type, slug) {
            const form = document.getElementById('editQuestionForm');
            form.action = `/question/${questionId}`; // Usa la ruta que definiste

            // Rellena los campos con los datos actuales
            document.getElementById('edit_question_text').value = text;
            document.getElementById('edit_question_type').value = type;
            document.getElementById('edit_question_slug').value = slug;

            showModal('editQuestionModal');
        }

        function confirmDeleteQuestion(questionId) {
            document.getElementById('deleteForm').action = `/question/${questionId}`;
            document.getElementById('deleteModalText').textContent = '¿Estás seguro de que deseas eliminar esta pregunta? Esta acción no se puede deshacer.';
            showModal('deleteModal');
        }

        // Confirm delete questionnaire
        function confirmDeleteQuestionnaire(questionnaireId) {
            document.getElementById('deleteForm').action = `/questionnaires/${questionnaireId}`;
            document.getElementById('deleteModalText').textContent = '¿Estás seguro de que deseas eliminar este cuestionario? Esta acción no se puede deshacer.';
            showModal('deleteModal');
        }

        // Confirm detach question
        function confirmDetachQuestion(questionnaireId, questionId) {
            document.getElementById('deleteForm').action = `/questionnaires/${questionnaireId}/detach/${questionId}`;
            document.getElementById('deleteModalText').textContent = '¿Estás seguro de que deseas desvincular esta pregunta del cuestionario?';
            showModal('deleteModal');
        }

        // Filter questionnaires
        function filterQuestionnaires() {
            const searchValue = document.getElementById('searchQuestionnairesInput').value.toLowerCase();

            document.querySelectorAll('.questionnaire-row').forEach(row => {
                const name = row.querySelector('.text-sm.font-medium').textContent.toLowerCase();
                const isActive = row.getAttribute('data-status') === 'active';

                const matchesSearch = name.includes(searchValue);

                if (matchesSearch) {
                    row.style.display = '';
                    const questionRow = document.getElementById(`${row.id}-row`);
                    if (questionRow) questionRow.style.display = '';
                } else {
                    row.style.display = 'none';
                    const questionRow = document.getElementById(`${row.id}-row`);
                    if (questionRow) questionRow.style.display = 'none';
                }
            });
        }

        function filterQuestions() {
            const searchValue = document.getElementById('searchQuestionsInput').value.toLowerCase();

            document.querySelectorAll('.question-row').forEach(row => {
                const text = row.querySelector('td:first-child').textContent.toLowerCase();
                const type = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const slug = row.querySelector('td:nth-child(3)').textContent.toLowerCase();

                const matchesSearch = text.includes(searchValue) ||
                    type.includes(searchValue) ||
                    slug.includes(searchValue);

                if (matchesSearch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Load available questions for a questionnaire
        function loadAvailableQuestions(questionnaireId) {
            fetch(`/questions/available?questionnaire_id=${questionnaireId}`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('availableQuestionsList');
                    container.innerHTML = '';

                    if (data.length === 0) {
                        container.innerHTML = '<p class="text-sm text-gray-500">No hay preguntas disponibles para añadir</p>';
                        return;
                    }

                    data.forEach(question => {
                        const div = document.createElement('div');
                        div.className = 'flex items-start';
                        div.innerHTML = `
                            <div class="flex items-center h-5">
                                <input id="question-${question.id}" name="questions[]"`
                    })
                });
        }

        // Añadir campo para nueva pregunta
        function addNewQuestionField() {
            const container = document.getElementById('newQuestionsContainer');
            const questionId = Date.now(); // ID temporal

            const questionDiv = document.createElement('div');
            questionDiv.className = 'bg-gray-50 p-3 rounded-md';
            questionDiv.innerHTML = `
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="new_question_text_${questionId}" class="block text-sm font-medium text-gray-700">Texto de la pregunta</label>
                        <input type="text" name="new_questions[${questionId}][text]" id="new_question_text_${questionId}" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="new_question_type_${questionId}" class="block text-sm font-medium text-gray-700">Tipo</label>
                        <select name="new_questions[${questionId}][type]" id="new_question_type_${questionId}" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="text">Texto</option>
                            <option value="select">Selección</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="radio">Radio</option>
                        </select>
                    </div>
                </div>
                <div class="mt-2 flex justify-end">
                    <button type="button" onclick="this.parentNode.remove()" class="text-red-600 hover:text-red-800 text-sm">
                        <i class="fas fa-trash mr-1"></i> Eliminar
                    </button>
                </div>
            `;

            container.appendChild(questionDiv);
        }

        // Buscar preguntas existentes
        document.getElementById('questionSearch').addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase();
            const questions = document.querySelectorAll('#availableQuestionsList > div');

            questions.forEach(question => {
                const text = question.querySelector('label').textContent.toLowerCase();
                question.style.display = text.includes(searchTerm) ? 'flex' : 'none';
            });
        });
    </script>
    <script>
        function filterQuestionsBySelectors() {
            const sector = document.getElementById('filterSector').value;
            const categoria = document.getElementById('filterCategory').value;

            document.querySelectorAll('.question-row').forEach(row => {
                const rowSector = row.getAttribute('data-sector');
                const rowCategoria = row.getAttribute('data-categoria');

                const matchesSector = !sector || rowSector === sector;
                const matchesCategoria = !categoria || rowCategoria === categoria;

                row.style.display = (matchesSector && matchesCategoria) ? '' : 'none';
            });
        }
    </script>
    <script>
        function autoFillSlugPregunta() {
            const texto = document.getElementById('new_question_text').value;
            let slug = texto.normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .trim()
                .replace(/\s+/g, '_');
            document.getElementById('new_question_slug').value = slug;
        }
    </script>

    <!-- Modal para crear categoría -->
    <div id="createCategoryModal" class="fixed inset-0 overflow-y-auto z-50 hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="createCategoryForm">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-tag text-blue-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Nueva Categoría</h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="category_name" class="block text-sm font-medium text-gray-700">Nombre</label>
                                        <input type="text" name="category_name" id="category_name"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="category_description" class="block text-sm font-medium text-gray-700">Descripción (opcional)</label>
                                        <textarea name="description" id="category_description" rows="2"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#54debd] focus:border-[#54debd] sm:text-sm"></textarea>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Crear Categoría
                        </button>
                        <button type="button" onclick="closeModal('createCategoryModal')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Funciones para mostrar modales
        function showCreateSectorModal() {
            document.getElementById('createSectorModal').classList.remove('hidden');
        }

        function showCreateCategoryModal() {
            document.getElementById('createCategoryModal').classList.remove('hidden');
        }

        document.getElementById('createCategoryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('/admin/question-categories', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Recargar la página para mostrar la nueva categoría
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al crear la categoría');
            }
        });


    </script>
</body>

</html>
