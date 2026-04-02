<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trámites - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        main {
            min-height: 100vh;
            padding: 2rem 1rem;
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

    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-100 text-green-800 border border-green-300 px-6 py-3 rounded-lg shadow-md z-50 flex items-center justify-center space-x-2 w-full max-w-3xl">
            <i class="fas fa-check-circle text-green-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Header -->
    <header class="bg-indigo-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 sm:px-6 sm:py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-indigo-500 rounded-xl flex items-center justify-center">
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
                        <span class="text-indigo-200">Trámites</span>
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div
                        class="hidden sm:flex items-center bg-indigo-800 px-3 py-1 rounded-full text-xs font-medium">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Administrador
                    </div>
                    <div class="flex items-center">
                        <div
                            class="w-8 h-8 rounded-full bg-indigo-400 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <span
                            class="font-medium ml-2 hidden sm:inline truncate max-w-[100px] md:max-w-none">
                            {{ Auth::user()->name }}
                        </span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="flex items-center justify-center w-8 h-8 sm:w-auto sm:px-2 sm:h-auto text-indigo-100 hover:text-white transition-colors">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="hidden sm:inline ml-1">Salir</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main -->
    <main class="container mx-auto px-6 py-8">
        <!-- Welcome Card -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover">
            <div class="p-6 md:p-8 bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">Gestión de Tramites</h2>
                        <p class="opacity-90">Comprueba los tramites de los usuarios</p>
                    </div>

                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover">
            <div class="p-6 border-b border-gray-100">
                <div class="flex flex-col gap-4">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-users text-indigo-500 mr-2"></i>
                        Buscar usuarios
                    </h3>

                    <!-- FORMULARIO DE BÚSQUEDA -->
                    <form method="GET" action="{{ route('admin.tramites') }}">
                        <!-- Botón y filtros -->
                        <div class="space-y-4 mb-4">
                            <!-- Botón para mostrar/ocultar filtros -->
                            <p type="button" id="toggleFilters"
                                class="text-indigo-600 font-medium focus:outline-none">
                                Filtrado adicional
                            </p>

                            <!-- Contenedor de filtros-->
                            <div id="additionalFilters"
                                class="hidden md:flex md:space-x-4 md:space-y-0 flex-col md:flex-row transition-all duration-300">
                                <!-- Comunidad Autónoma -->
                                <div class="flex flex-col w-full">
                                    <label for="ccaa"
                                        class="text-sm font-medium text-gray-700">Comunidad
                                        Autónoma</label>
                                    <select id="ccaa" name="ccaa"
                                        class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">-- TODAS --</option>
                                        @foreach ($ccaas as $ccaa)
                                            <option value="{{ $ccaa }}"
                                                {{ request('ccaa') == $ccaa ? 'selected' : '' }}>
                                                {{ mb_strtoupper($ccaa, 'UTF-8') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Estado del proceso -->
                                <div class="flex flex-col w-full">
                                    <label for="estado"
                                        class="text-sm font-medium text-gray-700">Estado del
                                        proceso</label>
                                    <select id="estado" name="estado"
                                        class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">-- TODOS --</option>
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado }}"
                                                {{ request('estado') == $estado ? 'selected' : '' }}>
                                                {{ mb_strtoupper($estado, 'UTF-8') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Ayuda contratada -->
                                <div class="flex flex-col w-full">
                                    <label for="ayuda"
                                        class="text-sm font-medium text-gray-700">Ayuda
                                        contratada</label>
                                    <select id="ayuda" name="ayuda"
                                        class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">-- TODAS --</option>
                                        @foreach ($ayudas as $ayuda)
                                            <option value="{{ $ayuda }}"
                                                {{ request('ayuda') == $ayuda ? 'selected' : '' }}>
                                                {{ mb_strtoupper($ayuda, 'UTF-8') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Ayuda activa -->
                                <div class="flex flex-col w-full">
                                    <label for="activo"
                                        class="text-sm font-medium text-gray-700">¿Ayuda
                                        activa?</label>
                                    <select name="activo" id="activo"
                                        class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">-- TODAS --</option>
                                        <option value="1"
                                            {{ request('activo') === '1' ? 'selected' : '' }}>SÍ
                                        </option>
                                        <option value="0"
                                            {{ request('activo') === '0' ? 'selected' : '' }}>NO
                                        </option>
                                    </select>
                                </div>

                                <div id="additionalFilters"
                                    class="hidden md:flex md:space-x-4 md:space-y-0 flex-col md:flex-row transition-all duration-300">
                                    <!-- Fase de documentación -->
                                    <div class="flex flex-col w-full">
                                        <label for="fase"
                                            class="text-sm font-medium text-gray-700">Fase</label>
                                        <select id="fase" name="fase"
                                            class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">-- TODAS --</option>
                                            @foreach ($fases as $fase)
                                                <option value="{{ $fase }}"
                                                    {{ request('fase') == $fase ? 'selected' : '' }}>
                                                    {{ mb_strtoupper($fase, 'UTF-8') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Campo de búsqueda -->
                            <div class="relative mb-4">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" name="q"
                                    value="{{ old('q', $query ?? '') }}"
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Buscar por nombre completo, e-mail, teléfono, DNI...">
                            </div>

                            <!-- Botón buscar -->
                            <div>
                                <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                    Buscar
                                </button>
                            </div>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr class="text-xs text-left font-medium text-gray-500 uppercase">
                            <th class="px-6 py-3">Nombre del Cliente</th>
                            <th class="px-6 py-3">Ayuda Contratada</th>
                            <th class="px-6 py-3">Usuario</th>
                            <th class="px-6 py-3">Teléfono</th>
                            <th class="px-6 py-3">CCAA</th>
                            <th class="px-6 py-3">Estado del Proceso</th>
                            <th class="px-6 py-3">Doc-Fase</th>
                            <th class="text-center px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @isset($contrataciones)
                            @forelse ($contrataciones as $contratacion)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{-- DESACOPLADO: enlace a panel-usuario --}}
                                        {{-- <a href="{{ route('admin.panel-usuario', ['user'=>$contratacion->user_id]) }}"> --}}
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $contratacion->user->answers->firstWhere('question.slug', 'nombre_completo')?->answer ?? ($contratacion->user->name ?? 'No disponible') }}
                                            </div>
                                            @if ($contratacion->user->is_admin)
                                                <span
                                                    class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    <i class="fas fa-crown mr-1"></i>Admin
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $contratacion->user->email }}</div>
                                        {{-- </a> --}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $contratacion->ayuda->nombre_ayuda ?? 'Sin ayuda' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $contratacion->user->name ?? 'Sin nombre ' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $contratacion->user->answers->firstWhere('question.slug', 'telefono')->answer ?? 'Sin teléfono' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $contratacion->user->answers->firstWhere('question.slug', 'coumunidad_autonoma')->answer ?? 'Sin CCAA' }}

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap capitalize">
                                        {{ $contratacion->estado ?? 'Sin estado' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $contratacion->fase ?? 'Sin documentación' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <button
                                            onclick="showEditEstadoModal({{ $contratacion->id }}, '{{ $contratacion->estado }}')"
                                            class="text-indigo-600 hover:text-indigo-900 mr-2">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                @if (request('q'))
                                    <tr>
                                        <td colspan="5"
                                            class="px-6 py-4 text-center text-gray-500">
                                            No se encontraron resultados.
                                        </td>
                                    </tr>
                                @endif
                            @endforelse
                        @endisset
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal: Editar Estado de Contratación -->
    <div id="editEstadoModal" class="fixed inset-0 overflow-y-auto z-50 hidden"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div
            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay"
                aria-hidden="true">
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                <form id="editEstadoForm" method="POST" action="">
                    @csrf
                    @method('POST')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">

                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-tasks text-indigo-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Cambiar Estado del Proceso
                                </h3>

                                <div class="mt-4">
                                    <label for="modal_estado"
                                        class="block text-sm font-medium text-gray-700 mb-1">
                                        Estado
                                    </label>
                                    <select name="estado" id="modal_estado"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado }}">
                                                {{ ucfirst($estado) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar cambios
                        </button>
                        <button type="button" onclick="closeModal('editEstadoModal')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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

    <!-- Script para toggle filtros -->
    <script>
        document.getElementById('toggleFilters').addEventListener('click', function() {
            const filters = document.getElementById('additionalFilters');
            filters.classList.toggle('hidden');
        });

        function showModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }


        function showEditEstadoModal(id, estadoActual) {
            const modal = document.getElementById('editEstadoModal');
            const form = document.getElementById('editEstadoForm');
            const select = document.getElementById('modal_estado');

            // Actualizar la URL de acción con el id correcto
            form.action = `/admin/estado/${id}/update`;

            // Seleccionar el estado actual en el select
            select.value = estadoActual;

            // Mostrar el modal
            modal.classList.remove('hidden');
        }

        // Cerrar modal al hacer click fuera del contenido
        document.querySelector('.modal-overlay').addEventListener('click', () => {
            document.getElementById('editEstadoModal').classList.add('hidden');
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>

</html>
