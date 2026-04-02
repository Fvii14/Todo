<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

                        <span class="text-indigo-200">{{ isset($titulo) ? $titulo : 'Ayudas' }}</span>
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