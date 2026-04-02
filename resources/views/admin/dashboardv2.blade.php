<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard · Backoffice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    {{-- Alpine.js --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body x-data="{ drawerOpen: false }" class="h-full bg-gray-100 relative pt-0">

    @include('layouts.headerbackoffice')

    {{-- Botón fijo a la izquierda para abrir/cerrar --}}
    <button @click="drawerOpen = !drawerOpen"
        class="fixed top-1/2 left-0 transform -translate-y-1/2 bg-white p-2 rounded-r shadow-lg z-50 focus:outline-none">
        <i :class="drawerOpen ? 'bx bx-chevron-left' : 'bx bx-chevron-right'" class="text-2xl"></i>
    </button>

    {{-- Drawer lateral --}}
    <x-sidebar-admin />

    {{-- Contenido principal --}}
    <div class="pt-4 px-4 max-w-7xl mx-auto pt-0">
        <div class="py-6 space-y-8">

            {{-- Título principal --}}
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Panel de Administración</h1>
                <p class="text-gray-600">Guía completa del sistema de gestión de ayudas</p>
            </div>

            {{-- Explicación breve de secciones del menú superior --}}
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">¿Qué puedes hacer desde el menú?
                </h2>
                <p class="text-gray-600">Selecciona una sección para ir directamente.</p>
            </div>

            {{-- Lista de enlaces a los apartados del header (diseño alternativo) --}}
            <div class="bg-white rounded-lg shadow divide-y">
                <a href="{{ route('admin.work-tray') }}"
                    class="flex items-start p-5 hover:bg-gray-50 transition">
                    <i class="bx bx-task text-2xl text-[#54debd] mr-3 mt-0.5"></i>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">Bandeja de trabajo</h3>
                        <p class="text-sm text-gray-600">Tareas y expedientes asignados para
                            gestionar día a día.</p>
                    </div>
                </a>
                <a href="{{ route('admin.historialexpedientes') }}"
                    class="flex items-start p-5 hover:bg-gray-50 transition">
                    <i class="bx bx-user text-2xl text-blue-600 mr-3 mt-0.5"></i>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">Clientes</h3>
                        <p class="text-sm text-gray-600">Consulta y gestiona los expedientes
                            vinculados a cada cliente.</p>
                    </div>
                </a>
                {{-- DESACOPLADO: enlace a users-history --}}
                {{-- <a href="{{ route('admin.users-history') }}" class="flex items-start p-5 hover:bg-gray-50 transition">
          <i class="bx bx-id-card text-2xl text-indigo-600 mr-3 mt-0.5"></i>
          <div>
            <h3 class="text-base font-semibold text-gray-800">Usuarios</h3>
            <p class="text-sm text-gray-600">Historial y gestión de usuarios del sistema.</p>
          </div>
        </a> --}}
                <a href="{{ route('posibles-beneficiarios.index') }}"
                    class="flex items-start p-5 hover:bg-gray-50 transition">
                    <i class="bx bx-group text-2xl text-purple-600 mr-3 mt-0.5"></i>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">Posibles Beneficiarios -
                            Marketing</h3>
                        <p class="text-sm text-gray-600">Genera listados de posibles beneficiarios
                            para campañas de marketing.</p>
                    </div>
                </a>
            </div>

        </div>
    </div>

</body>

</html>
