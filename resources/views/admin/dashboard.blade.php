<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Collector</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .sensitive-data {
            filter: blur(5px);
            transition: filter 0.3s ease;
        }

        .sensitive-data:hover {
            filter: blur(0);
        }

        .sensitive-container:hover .sensitive-data {
            filter: blur(0);
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

        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .slide-fade-enter-active,
        .slide-fade-leave-active {
            transition: all 0.3s ease;
        }

        .slide-fade-enter-from,
        .slide-fade-leave-to {
            transform: translateY(20px);
            opacity: 0;
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

<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-800 min-h-screen">

    <!-- Header -->
    <header class="bg-indigo-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 sm:px-6 sm:py-4">
            <div class="flex items-center justify-between">
                <!-- Logo y nombre -->
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-folder-open text-lg sm:text-xl"></i>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold truncate">
                        <a href="/dashboard" class="text-white hover:text-indigo-100 inline-flex">
                            <span class="animate-float">C</span>
                            <span class="animate-float delay-100">o</span>
                            <span class="animate-float delay-200">l</span>
                            <span class="animate-float delay-300">l</span>
                            <span class="animate-float delay-400">e</span>
                            <span class="animate-float delay-500">c</span>
                            <span class="animate-float delay-600">t</span>
                            <span class="animate-float delay-700">o</span>
                            <span class="animate-float delay-800">r</span>
                        </a>
                        <span class="text-indigo-200">Admin</span>
                    </h1>
                </div>

                <!-- User info y logout -->
                <div class="flex items-center space-x-4">
                    <!-- Admin badge -->
                    <div
                        class="hidden sm:flex items-center bg-indigo-800 px-3 py-1 rounded-full text-xs font-medium">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Administrador
                    </div>

                    <!-- User info - Solo muestra icono en mobile -->
                    <div class="flex items-center">
                        <div
                            class="w-8 h-8 rounded-full bg-indigo-400 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <span
                            class="font-medium ml-2 hidden sm:inline truncate max-w-[100px] md:max-w-none">{{ Auth::user()->name }}</span>
                    </div>

                    <!-- Logout button -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="flex items-center justify-center w-8 h-8 sm:w-auto sm:px-2 sm:h-auto text-indigo-100 hover:text-white transition-colors"
                            aria-label="Cerrar sesión">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="hidden sm:inline ml-1">Salir</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-8">
        <!-- Welcome Card -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover">
            <div class="p-6 md:p-8 bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">Panel de Administración</h2>
                        <p class="opacity-90">Gestión completa de todos los usuarios del sistema</p>
                    </div>
                    <div class="mt-4 md:mt-0 flex space-x-3">
                        <div
                            class="bg-white bg-opacity-20 px-4 py-2 rounded-lg flex items-center space-x-2">
                            <i class="fas fa-users"></i>
                            <span>{{ $totalUsers }} usuarios registrados</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-8 mb-8">
            <div
                class="bg-white rounded-2xl shadow-md overflow-hidden card-hover lg:col-span-1 flex flex-col">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-rocket text-indigo-500 mr-2"></i>
                        Acceso rápido
                    </h3>
                </div>
                <div class="p-6 flex flex-col h-[400px]">
                    <div class="space-y-4 overflow-y-auto pr-2">
                        <a href="{{ route('questionnaires.index') }}"
                            class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors mb-4">
                            <div
                                class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-clipboard-list text-indigo-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium">Cuestionarios</h4>
                                <p class="text-sm text-gray-500">Administrar preguntas y
                                    cuestionarios</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
                        </a>

                        <!-- Segundo enlace - Ayudas -->
                        <a href="{{ route('ayudas.index') }}"
                            class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors mb-4">
                            <div
                                class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-euro text-indigo-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium">Ayudas</h4>
                                <p class="text-sm text-gray-500">Administrar ayudas, requisitos y
                                    documentos</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
                        </a>
                        <a href="{{ route('admin.cobros') }}"
                            class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                            <div
                                class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-credit-card text-indigo-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium">Cobros</h4>
                                <p class="text-sm text-gray-500">Realizar cargos a los usuarios</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
                        </a>
                        <a href="{{ route('admin.solicitudes') }}"
                            class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                            <div
                                class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-question text-indigo-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium">Solicitudes</h4>
                                <p class="text-sm text-gray-500">Comprueba las solicitudes de los
                                    usuarios</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
                        </a>

                        <a href="{{ route('admin.tramites') }}"
                            class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                            <div
                                class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-4 mb-4">
                                <i class="fas fa-euro text-indigo-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium">Trámites</h4>
                                <p class="text-sm text-gray-500">Administrar los trámites e info de
                                    los usuarios</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
                        </a>
                        <a href="{{ route('admin.historialquestionnaire') }}"
                            class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                            <div
                                class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-question text-indigo-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium">Historial Formularios</h4>
                                <p class="text-sm text-gray-500">Visualizar las respuestas de los
                                    usuarios</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md overflow-hidden card-hover">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-table text-indigo-500 mr-2"></i>
                    Listado de Usuarios
                </h3>
            </div>

            <div class="overflow-x-auto">
                <form method="GET" action="{{ route('dashboard') }}" class="px-6 py-4">
                    <div class="flex items-center space-x-2">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Buscar por nombre o email"
                            class="px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Buscar
                        </button>
                    </div>
                </form>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                NIF</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Provincia</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Collector Real</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Registro</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="usersTableBody">
                        @foreach ($users as $user)
                            @php
                                $userAnswers = $answers[$user->id] ?? collect();
                                $dni = $userAnswers->firstWhere('question_id', 34)?->answer ?? '—';
                                $provincia =
                                    $userAnswers->firstWhere('question_id', 36)?->answer ?? '—';
                                $collector_real =
                                    $userAnswers->firstWhere('question_id', 90) !== null;
                            @endphp
                            <tr class="user-row transition-colors @if ($user->is_admin) bg-purple-50 border-l-4 border-purple-600 @else hover:bg-gray-50 @endif"
                                data-has-data="{{ $user->taxInfo ? 'true' : 'false' }}"
                                data-province="{{ $user->taxInfo ? $user->taxInfo->ccia : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{-- DESACOPLADO: enlace a panel-usuario --}}
                                    {{-- <a href="{{ route('admin.panel-usuario', [$user -> id]) }}" class="flex items-center"> --}}
                                    <span class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 rounded-full @if ($user->is_admin) bg-purple-100 @else bg-indigo-100 @endif flex items-center justify-center">
                                            <i
                                                class="fas @if ($user->is_admin) fa-user-shield text-purple-600 @else fa-user text-indigo-600 @endif"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="flex items-center">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $user->name }}
                                                </div>
                                                @if ($user->is_admin)
                                                    <span
                                                        class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                        <i class="fas fa-crown mr-1"></i>Admin
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}
                                            </div>
                                        </div>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $dni }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $provincia }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($collector_real)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Sí
                                        </span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            No
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @if ($user->taxInfo)
                                            <!-- He añadido comillas simples porque me marcaba error, no si es correcto-->
                                            <a href="#"
                                                onclick="showUserDetails('{{ $user->id }}')"
                                                class="text-indigo-600 hover:text-indigo-900">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                        @if (!$user->is_admin)
                                            <!-- He añadido comillas simples porque me marcaba error, no si es correcto-->
                                            <a href="{{ route('admin.userDetail', $user->id) }}"
                                                target="_blank"
                                                class="text-green-600 hover:text-red-900">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="#"
                                                onclick="confirmDelete('{{ $user->id }}')"
                                                class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">
                {{ $users->links() }}
            </div>
        </div>
        </div>
    </main>

    <!-- User Details Modal -->
    <div id="userDetailsModal" class="fixed inset-0 overflow-y-auto z-50 hidden"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div
            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay"
                aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-user text-indigo-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900"
                                id="modal-title">
                                Detalles del usuario
                            </h3>
                            <div class="mt-2">
                                <!-- Dynamic content will be inserted here by JavaScript -->
                                <div id="userDetailsContent"
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Content loaded via AJAX -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeModal('userDetailsModal')"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="fixed inset-0 overflow-y-auto z-50 hidden"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div
            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay"
                aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-edit text-blue-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900"
                                id="modal-title">
                                Editar usuario
                            </h3>
                            <div class="mt-2">
                                <!-- Dynamic content will be inserted here by JavaScript -->
                                <div id="editUserContent">
                                    <!-- Content loaded via AJAX -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="saveUserChanges()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Guardar cambios
                    </button>
                    <button type="button" onclick="closeModal('editUserModal')"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 overflow-y-auto z-50 hidden"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div
            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay"
                aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900"
                                id="modal-title">
                                Confirmar eliminación
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    ¿Estás seguro de que deseas eliminar este usuario? Esta acción
                                    no se puede deshacer.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="deleteUser()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Eliminar
                    </button>
                    <button type="button" onclick="closeModal('deleteModal')"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
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
                        <i class="fas fa-folder-open text-indigo-600"></i>
                    </div>
                    <span class="font-medium">Collector by TTF</span>
                </div>
                <div class="text-sm text-gray-500">
                    Panel de administración por <a href="https://tutramitefacil.es/"
                        target="_blank"
                        class="text-indigo-600 hover:text-indigo-800">TuTrámiteFácil</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        const usuariosConDatos = {{ $usersWithTaxInfo }};
        const usuariosSinDatos = {{ count($users) - $usersWithTaxInfo }};
        let currentUserId = null;
        let currentEditingField = null;
        let originalValue = null;

        function filterUsers() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const provinceFilter = document.getElementById('provinceFilter').value;

            const rows = document.querySelectorAll('.user-row');

            rows.forEach(row => {
                const name = row.querySelector('td:first-child .text-gray-900').textContent
                    .toLowerCase();
                const email = row.querySelector('td:first-child .text-gray-500').textContent
                    .toLowerCase();
                const hasData = row.getAttribute('data-has-data') === 'true';
                const province = row.getAttribute('data-province') || '';

                const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
                const matchesStatus =
                    (statusFilter === '' ||
                        (statusFilter === 'with_data' && hasData) ||
                        (statusFilter === 'without_data' && !hasData));
                const matchesProvince = provinceFilter === '' || province === provinceFilter;

                if (matchesSearch && matchesStatus && matchesProvince) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function showUserDetails(userId) {
            currentUserId = userId;
            fetch(`/admin/users/${userId}/details`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('userDetailsContent').innerHTML = `
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Nombre completo</p>
                                <p class="font-medium">${data.user.name}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Email</p>
                                <p class="font-medium">${data.user.email}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Fecha registro</p>
                                <p class="font-medium">${new Date(data.user.created_at).toLocaleDateString()}</p>
                            </div>
                            ${data.taxInfo ? `
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">NIF</p>
                                        <p class="font-medium">${data.taxInfo.nif}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Provincia</p>
                                        <p class="font-medium">${data.taxInfo.provincia}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Base imponible</p>
                                        <p class="font-medium">${data.taxInfo.base_imponible_general}€</p>
                                    </div>
                                    ` : '<p class="text-gray-500">El usuario no ha completado su información fiscal</p>'}
                        </div>
                        <div class="space-y-4">
                            ${data.taxInfo ? `
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Domicilio fiscal</p>
                                        <p class="font-medium">${data.taxInfo.domicilio}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Municipio</p>
                                        <p class="font-medium">${data.taxInfo.municipio}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Código postal</p>
                                        <p class="font-medium">${data.taxInfo.codigo_postal}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Estado civil</p>
                                        <p class="font-medium">${data.taxInfo.estado_civil}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Fecha nacimiento</p>
                                        <p class="font-medium">${new Date(data.taxInfo.fecha_nacimiento).toLocaleDateString()}</p>
                                    </div>
                                    <div class="pt-4">
                                        <div class="flex flex-col sm:flex-row gap-3">
                                            ${data.taxInfo.certificado_irpf ? `
                                    <a href="/Justificantes/${data.taxInfo.certificado_irpf.split('/').pop()}"
                                    target="_blank"
                                    class="flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                        <i class="fas fa-file-pdf mr-2"></i>
                                        Ver Certificado IRPF
                                    </a>
                                    ` : ''}

                                            ${data.taxInfo.corriente_pago ? `
                                    <a href="/Justificantes/${data.taxInfo.corriente_pago.split('/').pop()}"
                                    target="_blank"
                                    class="flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                        <i class="fas fa-file-invoice-dollar mr-2"></i>
                                        Ver Corriente de Pago
                                    </a>
                                    ` : ''}
                                        </div>
                                    </div>
                                    ` : ''}
                        </div>
                    `;

                    openModal('userDetailsModal');
                });
        }

        function editUser(userId) {
            currentUserId = userId;

            fetch(`/admin/users/${userId}/edit`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editUserContent').innerHTML = `
                        <div class="space-y-4">
                            <div>
                                <label for="editName" class="block text-sm font-medium text-gray-700">Nombre</label>
                                <input type="text" id="editName" value="${data.name}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="editEmail" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" id="editEmail" value="${data.email}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>

                            ${data.taxInfo ? `
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="editNif" class="block text-sm font-medium text-gray-700">NIF</label>
                                            <input type="text" id="editNif" value="${data.taxInfo.nif}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>

                                        <div>
                                            <label for="editProvincia" class="block text-sm font-medium text-gray-700">Provincia</label>
                                            <select id="editProvincia" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                ${data.provinces.map(prov =>
                                                    `<option value="${prov}" ${prov === data.taxInfo.provincia ? 'selected' : ''}>${prov}</option>`
                                                ).join('')}
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="editBaseGeneral" class="block text-sm font-medium text-gray-700">Base imponible general</label>
                                            <input type="number" id="editBaseGeneral" value="${data.taxInfo.base_imponible_general}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>

                                        <div>
                                            <label for="editBaseAhorro" class="block text-sm font-medium text-gray-700">Base imponible ahorro</label>
                                            <input type="number" id="editBaseAhorro" value="${data.taxInfo.base_imponible_ahorro}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="editDomicilio" class="block text-sm font-medium text-gray-700">Domicilio</label>
                                        <input type="text" id="editDomicilio" value="${data.taxInfo.domicilio}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                    ` : '<p class="text-gray-500">El usuario no tiene información fiscal para editar</p>'}
                        </div>
                    `;

                    openModal('editUserModal');
                });
        }

        function saveUserChanges() {
            const userData = {
                name: document.getElementById('editName').value,
                email: document.getElementById('editEmail').value,
                nif: document.getElementById('editNif')?.value,
                provincia: document.getElementById('editProvincia')?.value,
                base_imponible_general: document.getElementById('editBaseGeneral')?.value,
                base_imponible_ahorro: document.getElementById('editBaseAhorro')?.value,
                domicilio: document.getElementById('editDomicilio')?.value
            };

            fetch(`/admin/users/${currentUserId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(userData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(
                            `.user-row[data-user-id="${currentUserId}"]`);
                        alert('Cambios guardados correctamente');
                        closeModal('editUserModal');
                        location.reload();
                    } else {
                        alert('Error al guardar los cambios: ' + (data.message || ''));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al guardar los cambios');
                });
        }

        function confirmDelete(userId) {
            currentUserId = userId;
            openModal('deleteModal');
        }

        function deleteUser() {
            fetch(`/admin/users/${currentUserId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Usuario eliminado correctamente');
                        closeModal('deleteModal');
                        document.querySelector(`.user-row[data-user-id="${currentUserId}"]`)
                            .remove();
                    } else {
                        alert('Error al eliminar el usuario: ' + (data.message || ''));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el usuario');
                });
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.body.style.overflow = '';
        }

        const ctx = document.createElement('canvas');
        document.getElementById('donutChart').innerHTML = ''; // Limpia el ícono de carga
        document.getElementById('donutChart').appendChild(ctx);

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Con datos', 'Sin datos'],
                datasets: [{
                    data: [usuariosConDatos, usuariosSinDatos],
                    backgroundColor: ['#4F46E5', '#E0E7FF'],
                    borderWidth: 1
                }]
            },
            options: {
                cutout: '10%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const editableFields = document.querySelectorAll('.editable-field');

            editableFields.forEach(field => {
                field.addEventListener('click', function() {
                    if (currentEditingField) {
                        currentEditingField.textContent = originalValue;
                        currentEditingField.classList.remove('active');
                    }

                    currentEditingField = this;
                    originalValue = this.textContent;
                    this.classList.add('active');

                    const input = document.createElement('input');
                    input.type = 'text';
                    input.value = originalValue;
                    input.className =
                        'border border-indigo-300 rounded px-2 py-1 text-sm w-full';

                    this.textContent = '';
                    this.appendChild(input);
                    input.focus();

                    input.addEventListener('blur', saveInlineEdit);
                    input.addEventListener('keyup', function(e) {
                        if (e.key === 'Enter') {
                            saveInlineEdit();
                        } else if (e.key === 'Escape') {
                            cancelInlineEdit();
                        }
                    });
                });
            });

            function saveInlineEdit() {
                const newValue = currentEditingField.querySelector('input').value;
                const fieldName = currentEditingField.getAttribute('data-field');
                const userId = currentEditingField.getAttribute('data-user-id');

                fetch(`/admin/users/${userId}/update-field`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            field: fieldName,
                            value: newValue
                        })
                    })

                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            currentEditingField.textContent = newValue;
                        } else {
                            currentEditingField.textContent = originalValue;
                            alert('Error al actualizar: ' + (data.message || ''));
                        }
                        currentEditingField.classList.remove('active');
                        currentEditingField = null;
                        originalValue = null;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        currentEditingField.textContent = originalValue;
                        currentEditingField.classList.remove('active');
                        currentEditingField = null;
                        originalValue = null;
                        alert('Error al actualizar');
                    });
            }

            function cancelInlineEdit() {
                currentEditingField.textContent = originalValue;
                currentEditingField.classList.remove('active');
                currentEditingField = null;
                originalValue = null;
            }
        });
    </script>
</body>

</html>
