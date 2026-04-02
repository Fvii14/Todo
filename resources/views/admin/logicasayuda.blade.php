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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

    <div class="pt-4 px-4 max-w-7xl mx-auto pt-0 pb-8">
        <nav class="flex" aria-label="Breadcrumb" class="mb-8">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboardv2') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors">
                        <i class="fas fa-home mr-2"></i>
                        Inicio
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                        <a href="{{ route('admin.logicas') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors">
                            Lógicas y condiciones de ayudas
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                        <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2">Lógicas y condiciones de {{ $ayuda->nombre_ayuda }}</span>
                    </div>
                </li>
            </ol>
        </nav>
        <div class="flex justify-between items-center mt-4 mb-4">
            <h1 class="text-2xl font-bold">Lógica y condiciones de <span class="text-green-600">{{ $ayuda->nombre_ayuda }}</span></h1>
            @if($questionnaire)
                <a href="{{ route('user.form-specific', ['id' => $questionnaire->id]) }}" 
                   target="_blank"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                    <i class="fas fa-play"></i>
                    Probar condiciones (NO mandes el formulario)
                </a>
            @endif
        </div>
        

        <div x-data="{ tab: 'logica' }" class="w-full">
            <div class="flex mb-4">
                <button :class="tab === 'logica' ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 border'" @click="tab = 'logica'" class="flex-1 px-4 py-2 rounded-t border-b-0">Requisitos</button>
                <button :class="tab === 'condiciones' ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 border'" @click="tab = 'condiciones'" class="flex-1 px-4 py-2 rounded-t border-b-0">Condiciones</button>
                <button :class="tab === 'playground' ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 border'" @click="tab = 'playground'" class="flex-1 px-4 py-2 rounded-t border-b-0">Playground</button>
                <button :class="tab === 'versiones' ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 border'" @click="tab = 'versiones'" class="flex-1 px-4 py-2 rounded-t border-b-0">Versiones</button>
                <button :class="tab === 'tester' ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 border'" @click="tab = 'tester'" class="flex-1 px-4 py-2 rounded-t border-b-0">Tester</button>
            </div>
            <div x-show="tab === 'logica'" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-4"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform -translate-x-4">
                <div id="vueflow-app"
                    data-ayuda="{{ $ayuda->nombre_ayuda }}"
                    data-ayuda-id="{{ $ayuda->id }}"
                    data-requisitos='@json($ayudaRequisitos)'
                    data-questions='@json($questionTexts)'
                    data-csrf="{{ csrf_token() }}"></div>
            </div>
            <div x-show="tab === 'condiciones'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-4"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform -translate-x-4">
                <div id="vueflow-condiciones-app"
                    data-ayuda-id="{{ $ayuda->id }}"
                    data-questionnaire-id="{{ $questionnaire->id ?? null }}"
                    data-csrf="{{ csrf_token() }}"></div>
            </div>
            <div x-show="tab === 'playground'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-4"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform -translate-x-4">
                <div id="playground-container"
                     data-ayuda-id="{{ $ayuda->id }}"
                     data-csrf="{{ csrf_token() }}"></div>
            </div>
            <div x-show="tab === 'versiones'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-4"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform -translate-x-4">
                <div id="versiones-container"
                     data-ayuda-id="{{ $ayuda->id }}"
                     data-questionnaire-id="{{ $questionnaire->id ?? null }}"
                     data-csrf="{{ csrf_token() }}"></div>
            </div>
            <div x-show="tab === 'tester'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-4"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform -translate-x-4"
                 x-data="{ testerSection: null }">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-xl font-bold mb-2 text-gray-800">Tester de requisitos</h3>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                                👤
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800">Perfil Simulado</h4>
                                                <p class="text-sm text-gray-600">Crea un perfil personalizado</p>
                                            </div>
                                        </div>
                                        <button 
                                            @click="testerSection = 'perfil-simulado'"
                                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                                            <i class="fas fa-play"></i>
                                            Probar
                                        </button>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg p-4 border border-purple-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                                👥
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800">Usuario Real</h4>
                                                <p class="text-sm text-gray-600">Prueba usuarios existentes</p>
                                            </div>
                                        </div>
                                        <button 
                                            @click="testerSection = 'usuario-real'"
                                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                                            <i class="fas fa-play"></i>
                                            Probar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow">
                        <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6 text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-xl font-bold mb-2 text-gray-800">Tester de condiciones</h3>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-lg p-4 border border-orange-200">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                            🔀
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-800">Flujo de Preguntas</h4>
                                            <p class="text-sm text-gray-600">Comprueba si el recorrido del cuestionario es correcto</p>
                                        </div>
                                    </div>
                                    <button 
                                        @click="testerSection = 'condiciones'"
                                        class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                                        <i class="fas fa-play"></i>
                                        Probar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenido dinámico -->
                <div class="space-y-6">
                    
                    <!-- Perfil Simulado -->
                    <div x-show="testerSection === 'perfil-simulado'"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-y-4"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-4">
                        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                        👤
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-800">Perfil Simulado</h3>
                                        <p class="text-gray-600">Crea un perfil personalizado para probar los requisitos</p>
                                    </div>
                                </div>
                                <button 
                                    @click="testerSection = null"
                                    class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                            <div id="tester-perfil-container"
                             data-ayuda-id="{{ $ayuda->id }}"
                             data-csrf="{{ csrf_token() }}"></div>
                        </div>
                    </div>
                    <div x-show="testerSection === 'usuario-real'"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-y-4"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-0">
                        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                        👥
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-800">Usuario Real</h3>
                                        <p class="text-gray-600">Prueba usuarios existentes contra los requisitos</p>
                                    </div>
                                </div>
                                <button 
                                    @click="testerSection = null"
                                    class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                            <div id="tester-usuario-container"
                                 data-ayuda-id="{{ $ayuda->id }}"
                                 data-csrf="{{ csrf_token() }}"></div>
                        </div>
                    </div>

                    <div x-show="testerSection === 'condiciones'"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-y-4"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-4">
                        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                        🔄
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-800">Tester de Condiciones</h3>
                                        <p class="text-gray-600">Prueba la lógica de flujo del cuestionario</p>
                                    </div>
                                </div>
                                <button 
                                    @click="testerSection = null"
                                    class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                            <div id="tester-condiciones-container"
                             data-ayuda-id="{{ $ayuda->id }}"
                                 data-csrf="{{ csrf_token() }}"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="help-sidebar-app">
        <help-sidebar 
            title="Lógicas y condiciones de {{ $ayuda->nombre_ayuda }}"
            main-title="Lógicas y condiciones de {{ $ayuda->nombre_ayuda }}"
            main-description="Esta sección te permite gestionar de forma completa las lógicas y condiciones de la ayuda {{ $ayuda->nombre_ayuda }}. Aquí puedes configurar requisitos, condiciones de flujo, probar la lógica y validar el funcionamiento."
            :features="[
                'Configurar requisitos de elegibilidad con editor visual',
                'Gestionar condiciones de flujo del cuestionario',
                'Probar la lógica con datos simulados y reales',
                'Validar el funcionamiento completo del sistema',
                'Editor playground para experimentar con la lógica'
            ]"
            :steps="[
                'Selecciona el tab que quieres configurar',
                'Configura los requisitos o condiciones según necesites',
                'Usa el playground para experimentar',
                'Prueba todo con el tester antes de activar'
            ]"
            additional-info='<div class="space-y-4">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-200">
                    <h5 class="font-semibold text-blue-800 mb-2">📋 Tab requisitos</h5>
                    <p class="text-sm text-blue-700">Editor visual para crear y gestionar los requisitos que determinan si un usuario es elegible para esta ayuda. Usa el editor de nodos para crear flujos lógicos complejos.</p>
                </div>
                
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-lg border border-green-200">
                    <h5 class="font-semibold text-green-800 mb-2">🔄 Tab condiciones</h5>
                    <p class="text-sm text-green-700">Gestiona las condiciones que controlan el flujo del cuestionario. Define qué preguntas se muestran según las respuestas anteriores del usuario.</p>
                </div>
                
                <div class="bg-gradient-to-r from-purple-50 to-violet-50 p-4 rounded-lg border border-purple-200">
                    <h5 class="font-semibold text-purple-800 mb-2">🎮 Tab playground</h5>
                    <p class="text-sm text-purple-700">Área de experimentación para probar diferentes configuraciones sin afectar la lógica activa. Ideal para desarrollar y refinar la lógica antes de implementarla.</p>
                </div>
                
                <div class="bg-gradient-to-r from-orange-50 to-red-50 p-4 rounded-lg border border-orange-200">
                    <h5 class="font-semibold text-orange-800 mb-2">🧪 Tab tester</h5>
                    <p class="text-sm text-orange-700">Herramientas de prueba para validar que la lógica funciona correctamente. Incluye tester de requisitos con perfiles simulados y usuarios reales, y tester de condiciones para validar el flujo del cuestionario.</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Herramienta</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Propósito</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Cuándo Usar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="px-3 py-2 text-sm font-medium text-blue-600">Editor Visual</td>
                                <td class="px-3 py-2 text-sm text-gray-600">Crear flujos lógicos complejos</td>
                                <td class="px-3 py-2 text-sm text-gray-600">Al configurar requisitos iniciales</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 text-sm font-medium text-green-600">Gestor de Condiciones</td>
                                <td class="px-3 py-2 text-sm text-gray-600">Controlar flujo del cuestionario</td>
                                <td class="px-3 py-2 text-sm text-gray-600">Para personalizar la experiencia</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 text-sm font-medium text-purple-600">Playground</td>
                                <td class="px-3 py-2 text-sm text-gray-600">Experimentar sin riesgo</td>
                                <td class="px-3 py-2 text-sm text-gray-600">Durante el desarrollo</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 text-sm font-medium text-orange-600">Tester</td>
                                <td class="px-3 py-2 text-sm text-gray-600">Validar funcionamiento</td>
                                <td class="px-3 py-2 text-sm text-gray-600">Antes de activar cambios</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <i class="bx bx-info-circle text-yellow-400 text-xl mr-2"></i>
                        <div>
                            <h5 class="text-sm font-semibold text-yellow-800 mb-1">Flujo de trabajo recomendado</h5>
                            <ol class="text-sm text-yellow-700 list-decimal list-inside space-y-1">
                                <li>Configura los requisitos básicos en el tab Requisitos</li>
                                <li>Define las condiciones de flujo en el tab Condiciones</li>
                                <li>Experimenta en el Playground para refinar la lógica</li>
                                <li>Prueba todo con el Tester antes de activar</li>
                                <li>Usa "Probar condiciones" para ver el resultado final</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>'
            important-note="IMPORTANTE: Siempre prueba tus cambios con el Tester antes de activarlos. Los cambios en la lógica pueden afectar a usuarios existentes. Usa el botón 'Probar condiciones' para ver exactamente cómo se comportará el cuestionario."
        >
            <div class="bg-indigo-50 border-l-4 border-indigo-400 p-4">
                <div class="flex">
                    <i class="bx bx-bulb text-indigo-400 text-xl mr-2"></i>
                    <div>
                        <h5 class="text-sm font-semibold text-indigo-800 mb-1">Consejos pro</h5>
                        <ul class="text-sm text-indigo-700 list-disc list-inside space-y-1">
                            <li>Usa el Playground para experimentar sin riesgo</li>
                            <li>Prueba con usuarios reales en el Tester</li>
                            <li>Valida el flujo completo antes de activar</li>
                        </ul>
                    </div>
                </div>
            </div>
        </help-sidebar>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.csrfToken = document.getElementById('vueflow-app').dataset.csrf;
            window.HelpSidebar.init('help-sidebar-app');
        });
    </script>
</body>

</html>

