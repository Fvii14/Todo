<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard · Backoffice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="{{ asset('js/help-sidebar.js') }}"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
        
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

    <div class="pt-4 px-4 max-w-7xl mx-auto pt-0">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="{{ route('user.home') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors">
                        <i class="fas fa-home mr-2"></i>
                        Inicio
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                        <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2">Lógicas y condiciones de ayudas</span>
                    </div>
                </li>
            </ol>
        </nav>
        <div class="py-6 space-y-6">
            <h1 class="text-2xl font-bold">Lógicas</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($ayudas as $ayuda)
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <a href="{{ route('admin.logicas.ayuda', $ayuda->id) }}">
                            <h2 class="text-lg font-bold">{{ $ayuda->nombre_ayuda }}</h2>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div id="help-sidebar-app">
        <help-sidebar 
            title="Lógicas"
            main-title="Lógicas y condiciones de cuestionarios"
            main-description="Esta sección te permite gestionar tanto las lógicas como las condiciones de los cuestionarios."
            :features="[
                'Configurar condiciones para establecer los posibles saltos de los cuestionarios',
                'Configurar lógicas para establecer de qué manera un usuario es (o no) beneficiario'
            ]"
            :steps="[
                'Selecciona una ayuda de la lista para ver sus lógicas actuales',
            ]"
            additional-info='<div class="space-y-3">
                 <p class="font-semibold text-gray-800">RECUERDA:</p>
                 <div class="overflow-x-auto">
                     <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                         <thead class="bg-gray-50">
                             <tr>
                                 <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Tipo</th>
                                 <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Cuándo se evalúa</th>
                                 <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Función</th>
                             </tr>
                         </thead>
                         <tbody class="divide-y divide-gray-200">
                             <tr>
                                 <td class="px-3 py-2 text-sm font-medium text-blue-600">Condiciones</td>
                                 <td class="px-3 py-2 text-sm text-gray-600">Tiempo real</td>
                                 <td class="px-3 py-2 text-sm text-gray-600">Establecen los "posibles caminos" del cuestionario</td>
                             </tr>
                             <tr>
                                 <td class="px-3 py-2 text-sm font-medium text-green-600">Lógicas</td>
                                 <td class="px-3 py-2 text-sm text-gray-600">Al final</td>
                                 <td class="px-3 py-2 text-sm text-gray-600">Determinan si el usuario es beneficiario</td>
                             </tr>
                         </tbody>
                     </table>
                 </div>
             </div>'
        ></help-sidebar>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.HelpSidebar.init('help-sidebar-app');
        });
    </script>
</body>

</html>
