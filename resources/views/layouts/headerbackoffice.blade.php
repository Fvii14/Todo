<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Backoffice')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100">

    <header class="relative overflow-hidden">
        {{-- Animated gradient background --}}
        <div
            class="absolute inset-0 animate-gradient-x bg-gradient-to-r from-[#54debd] via-[#63ffdb] to-[#368e79]">
        </div>

        <div class="relative container mx-auto flex items-center justify-between px-6 py-4">
            {{-- Logo / Marca --}}
            <a href="{{ route('admin.dashboardv2') }}" class="flex items-center space-x-2 group">
                <svg class="w-8 h-8 text-black group-hover:animate-bounce transition" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m2 9h-2a2 2 0 01-2-2v-5H8v5a2 2 0 01-2 2H4" />
                </svg>
                <span
                    class="text-2xl font-extrabold text-black tracking-wide group-hover:text-white transition-colors">
                    Backoffice
                </span>
            </a>

            {{-- Navegación --}}
            <nav class="flex space-x-6">
                <a href="{{ route('admin.dashboardv2') }}"
                    class="relative inline-block px-3 py-2 text-sm font-semibold uppercase text-black transition-transform hover:scale-105 group">
                    Dashboard
                    <span
                        class="absolute left-0 bottom-0 w-full h-0.5 bg-black transition-transform origin-left scale-x-0 group-hover:scale-x-100"></span>
                </a>
                <a href="{{ route('admin.work-tray') }}"
                    class="relative inline-block px-3 py-2 text-sm font-semibold uppercase text-black transition-transform hover:scale-105 group">
                    Bandeja de trabajo
                    <span
                        class="absolute left-0 bottom-0 w-full h-0.5 bg-black transition-transform origin-left scale-x-0 group-hover:scale-x-100"></span>
                </a>
                <a href="{{ route('admin.historialexpedientes') }}"
                    class="relative inline-block px-3 py-2 text-sm font-semibold uppercase text-black transition-transform hover:scale-105 group">
                    Clientes
                    <span
                        class="absolute left-0 bottom-0 w-full h-0.5 bg-black transition-transform origin-left scale-x-0 group-hover:scale-x-100"></span>
                </a>
                {{-- DESACOPLADO: enlace a users-history --}}
                {{-- <a href="{{ route('admin.users-history') }}"
               class="relative inline-block px-3 py-2 text-sm font-semibold uppercase text-black transition-transform hover:scale-105 group">
                Usuarios
                <span class="absolute left-0 bottom-0 w-full h-0.5 bg-black transition-transform origin-left scale-x-0 group-hover:scale-x-100"></span>
            </a> --}}
                <form action="{{ route('logout') }}" method="POST"
                    class="relative inline-block px-3 py-2 text-sm font-semibold uppercase text-black transition-transform hover:scale-105 group">
                    @csrf
                    <button type="submit"
                        class="flex items-center justify-center w-8 h-8 sm:w-auto sm:px-2 sm:h-auto"
                        aria-label="Cerrar sesión">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="hidden sm:inline ml-1">Salir</span>
                    </button>
                </form>
            </nav>
        </div>

        {{-- Gradient animation keyframes --}}
        <style>
            @keyframes gradient-x {
                0% {
                    background-position: 0% 50%;
                }

                50% {
                    background-position: 100% 50%;
                }

                100% {
                    background-position: 0% 50%;
                }
            }

            .animate-gradient-x {
                background-size: 200% 200%;
                animation: gradient-x 8s ease infinite;
            }
        </style>
    </header>

    <main class="container mx-auto px-3 py-5">
        @yield('content')
    </main>

    @include('components.floating-button-create-task')
</body>

</html>
