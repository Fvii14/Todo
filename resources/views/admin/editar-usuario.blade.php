<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Panel de Trámites</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <header class="bg-indigo-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 sm:px-6 sm:py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-folder-open text-lg sm:text-xl"></i>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold truncate">
                        <a href="/dashboard" class="text-white hover:text-indigo-100 inline-flex">
                            <span class="animate-float">C</span><span class="animate-float delay-100">o</span><span
                                class="animate-float delay-200">l</span><span
                                class="animate-float delay-300">l</span><span
                                class="animate-float delay-400">e</span><span
                                class="animate-float delay-500">c</span><span
                                class="animate-float delay-600">t</span><span
                                class="animate-float delay-700">o</span><span class="animate-float delay-800">r</span>
                        </a>
                        <span class="text-indigo-200">Panel de usuario</span>
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="hidden sm:flex items-center bg-indigo-800 px-3 py-1 rounded-full text-xs font-medium">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Administrador
                    </div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-indigo-400 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <span
                            class="font-medium ml-2 hidden sm:inline truncate max-w-[100px] md:max-w-none">{{ Auth::user()->name }}</span>
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

    <main class="container mx-auto p-6">
        @if (session('success'))
        <div class="mb-4 p-4 rounded-md bg-green-100 text-green-800 border border-green-300 flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        <!-- Welcome Card -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover">
            <div class="p-6 md:p-8 bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between text-left">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">Editor de usuario</h2>
                        <p class="opacity-90 f">Edita el usuario</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta principal del perfil -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <div class="bg-white rounded-2xl shadow-md overflow-hidden lg:col-span-3 card-hover">
                <div class="p-6 border-b border-gray-100">

                    <form method="POST" action="{{ route('admin.actualizar-respuestas', [$user->id]) }}"
                        class="space-y-6">
                        @csrf

                        @php
                        // Filtrar preguntas de sector 'collector' y 'datos-personales'
                        $respuestasCollector = $respuestas->filter(fn($r) => in_array($r->sector, ['collector',
                        'datos-personales']));
                        @endphp

                        @if ($respuestasCollector->isNotEmpty())
                        <h3 class="text-lg font-semibold text-indigo-600 mt-4">Datos Personales</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach ($respuestasCollector as $item)
                            <div>
                                <label for="q{{ $item->question_id }}"
                                    class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ $item->pregunta }}
                                </label>

                                @switch($item->tipo)
                                @case('boolean')
                                <input type="hidden" name="answers[{{ $item->question_id }}]" value="0">
                                <div class="flex items-center mb-4">
                                    <span class="text-sm text-gray-700">No</span>
                                    <label class="relative inline-block w-12 h-7 mx-2">
                                        <input type="checkbox" name="answers[{{ $item->question_id }}]" value="1"
                                            class="sr-only peer" @if ($item->respuesta == '1') checked @endif>
                                        <div
                                            class="w-12 h-7 bg-gray-300 rounded-full peer-checked:bg-green-600 transition duration-300 ease-in-out">
                                        </div>
                                        <div
                                            class="absolute top-0.5 left-0.5 w-6 h-6 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5">
                                        </div>
                                    </label>
                                    <span class="text-sm text-gray-700">Sí</span>
                                </div>
                                @break

                                @case('select')
                                @php
                                $options = $item->options ? json_decode($item->options, true) : [];
                                @endphp
                                <select name="answers[{{ $item->question_id }}]" id="q{{ $item->question_id }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Selecciona una opción --</option>
                                    @foreach ($options as $opcion)
                                    <option value="{{ $opcion }}" @if ($item->respuesta == $opcion) selected
                                        @endif>{{ $opcion }}
                                    </option>
                                    @endforeach
                                </select>
                                @break

                                @case('multiple')
                                @php
                                $options = $item->options ? json_decode($item->options, true) : [];
                                $respuestasSeleccionadas = is_array($item->respuesta) ? $item->respuesta : explode(',',
                                $item->respuesta ?? '');
                                @endphp
                                <select name="answers[{{ $item->question_id }}][]" id="q{{ $item->question_id }}"
                                    multiple
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @foreach ($options as $opcion)
                                    <option value="{{ $opcion }}" @if (in_array($opcion, $respuestasSeleccionadas))
                                        selected @endif>
                                        {{ $opcion }}</option>
                                    @endforeach
                                </select>
                                @break

                                @default
                                @php
                                $inputType = in_array($item->tipo, ['text', 'number', 'email', 'date']) ? $item->tipo :
                                'text';
                                @endphp
                                <input type="{{ $inputType }}" id="q{{ $item->question_id }}"
                                    name="answers[{{ $item->question_id }}]"
                                    value="{{ old('answers.' . $item->question_id, $item->respuesta) }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                                @endswitch
                            </div>
                            @endforeach
                        </div>

                        <div class="pt-4">
                            <button type="submit"
                                class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Guardar cambios
                            </button>
                        </div>
                        @else
                        <div
                            class="flex items-center gap-3 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-md text-xl">
                            <i class="fa-solid fa-circle-info" style="color: #FFD43B;"></i>
                            <p>Vaya... este usuario aún no tiene datos registrados :(</p>
                        </div>
                        @endif

                    </form>
                </div>
    </main>

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
                    Panel de administración por
                    <a href="https://tutramitefacil.es/" target="_blank"
                        class="text-indigo-600 hover:text-indigo-800">TuTrámiteFácil</a>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>