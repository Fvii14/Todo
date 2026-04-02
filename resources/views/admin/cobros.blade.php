<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Pago exitoso</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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

<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-800 min-h-screen">

    <!-- Header -->
    <header class="bg-indigo-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 sm:px-6 sm:py-4">
            <div class="flex items-center justify-between">
                <!-- Logo y nombre -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
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
                        <span class="text-indigo-200">Cobros</span>
                    </h1>
                </div>

                <!-- User info y logout -->
                <div class="flex items-center space-x-4">
                    <!-- Admin badge -->
                    <div class="hidden sm:flex items-center bg-indigo-800 px-3 py-1 rounded-full text-xs font-medium">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Administrador
                    </div>

                    <!-- User info - Solo muestra icono en mobile -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-indigo-400 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <span class="font-medium ml-2 hidden sm:inline truncate max-w-[100px] md:max-w-none">{{ Auth::user()->name }}</span>
                    </div>

                    <!-- Logout button -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="flex items-center justify-center w-8 h-8 sm:w-auto sm:px-2 sm:h-auto text-indigo-100 hover:text-white transition-colors" aria-label="Cerrar sesión">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="hidden sm:inline ml-1">Salir</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>
    <div class="min-h-screen flex justify-center items-center bg-gray-100">
        <div class="bg-white shadow-lg rounded-3xl p-8 w-full max-w-md relative z-10">
            <h2 class="text-center text-2xl font-bold mb-6">💼 Cobro manual a usuarios</h2>

            @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">
                {{ session('error') }}
            </div>
            @endif

            <form method="GET" action="{{ route('admin.cobros') }}">
                <label class="block text-sm font-medium mb-1 text-gray-700">Tipo de ayuda</label>
                <select name="ayuda_id" class="w-full border-2 border-blue-500 rounded-lg px-3 py-2 mb-4">
                    <option value="">Todas</option>
                    @foreach($ayudas as $a)
                        <option value="{{ $a->id }}" {{ request('ayuda_id') == $a->id ? 'selected' : '' }}>
                            {{ $a->nombre_ayuda }}
                        </option>
                    @endforeach
                </select>
            
                <label class="block text-sm font-medium mb-1 text-gray-700">Comunidad Autónoma</label>
                <select name="comunidad_autonoma" class="w-full border-2 border-blue-500 rounded-lg px-3 py-2 mb-4">
                    <option value="">Todas</option>
                    @foreach($ccaa as $c)
                        <option value="{{ $c->id }}" {{ request('ccaa_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->nombre_ccaa }}
                        </option>
                    @endforeach
                </select>
            
                <label class="block text-sm font-medium mb-1 text-gray-700">Buscar usuario</label>
                <input type="text" name="busqueda" value="{{ request('busqueda') }}"
                    placeholder="Nombre, DNI o teléfono"
                    class="w-full border-2 border-blue-500 rounded-lg px-3 py-2 mb-4"
                >
            
                <button type="submit"
                    class="w-full bg-indigo-600 text-white font-semibold py-2 rounded-lg hover:bg-indigo-700 transition mb-6">
                    🔍 Filtrar usuarios
                </button>
            </form>
            
            <form method="POST" action="/admin/cobros">
                @csrf
            
                <label class="block text-sm font-medium mb-1 text-gray-700">Selecciona usuario</label>
                <select name="user_id" class="w-full border-2 border-blue-500 rounded-lg px-3 py-2 mb-4" required>
                    @if(count($users))
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->email }}</option>
                        @endforeach
                    @else
                        <option value="">No hay usuarios con estos filtros</option>
                    @endif
                </select>
            
                <label class="block text-sm font-medium mb-1 text-gray-700">Importe (€)</label>
                <input type="number" step="0.01" min="0.5" name="amount"
                    placeholder="Ej. 10.00"
                    class="w-full border-2 border-blue-500 rounded-lg px-3 py-2 mb-6"
                    required>
            
                <button type="submit"
                    class="w-full bg-blue-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    💳 Cobrar al usuario
                </button>
            </form>
            
        </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
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
                    Panel de administración por <a href="https://tutramitefacil.es/" target="_blank" class="text-indigo-600 hover:text-indigo-800">TuTrámiteFácil</a>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>
