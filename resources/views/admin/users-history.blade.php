{{-- resources/views/admin/users-history.blade.php --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Historial de Usuarios · Backoffice</title>

    <style>
        .tutorial-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            display: none;
        }

        .tutorial-highlight {
            position: absolute;
            border: none;
            outline: 3px solid #54debd;
            outline-offset: 2px;
            border-radius: 50%;
            background: none !important;
            background-color: none !important;
            background-image: none !important;
            box-shadow: 0 0 20px rgba(84, 222, 189, 0.6);
            pointer-events: none;
            z-index: 10000;
            animation: pulse 2s infinite;
            transition: all 0.3s ease;
            /* Forzar transparencia */
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            filter: none !important;
            /* Asegurar que no haya colores de Tailwind */
            --tw-bg-opacity: 0 !important;
            --tw-bg-opacity: 0 !important;
        }

        /* Asegurar que no haya pseudo-elementos con opacidad */
        .tutorial-highlight::before,
        .tutorial-highlight::after {
            display: none !important;
            background: transparent !important;
            background-color: transparent !important;
        }

        /* Estilo alternativo más simple si el anterior no funciona */
        .tutorial-highlight-simple {
            position: absolute;
            border: 3px solid #54debd;
            border-radius: 50%;
            background: none !important;
            pointer-events: none;
            z-index: 10000;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 20px rgba(84, 222, 189, 0.4);
                outline-color: #54debd;
                transform: scale(1);
            }

            50% {
                box-shadow: 0 0 30px rgba(84, 222, 189, 0.8);
                outline-color: #43c5a9;
                transform: scale(1.02);
            }

            100% {
                box-shadow: 0 0 20px rgba(84, 222, 189, 0.4);
                outline-color: #54debd;
                transform: scale(1);
            }
        }

        .tutorial-popup {
            position: absolute;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 300px;
            z-index: 10001;
        }

        .tutorial-popup h3 {
            color: #54debd;
            margin: 0 0 10px 0;
            font-size: 18px;
            font-weight: bold;
        }

        .tutorial-popup p {
            margin: 0 0 15px 0;
            color: #666;
            line-height: 1.4;
        }

        .tutorial-popup button {
            background: #54debd;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }

        .tutorial-popup button:hover {
            background: #43c5a9;
        }

        /* Estilos para notificaciones */
        .tutorial-notification {
            transition: all 0.3s ease;
            opacity: 1;
            transform: translateX(0);
        }

        /* Estilo alternativo usando pseudo-elementos si outline no funciona */
        .tutorial-highlight-pseudo {
            position: absolute;
            border-radius: 50%;
            background: none !important;
            pointer-events: none;
            z-index: 10000;
        }

        .tutorial-highlight-pseudo::before {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            border: 3px solid #54debd;
            border-radius: 50%;
            animation: pulse-pseudo 2s infinite;
        }

        @keyframes pulse-pseudo {
            0% {
                border-color: #54debd;
                transform: scale(1);
            }

            50% {
                border-color: #43c5a9;
                transform: scale(1.02);
            }

            100% {
                border-color: #54debd;
                transform: scale(1);
            }
        }
    </style>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
</head>

<body class="relative overflow-x-hidden">
    <!-- Tutorial personalizado -->
    <div id="tutorial-overlay" class="tutorial-overlay">
        <div id="tutorial-highlight" class="tutorial-highlight"></div>
        <div id="tutorial-popup" class="tutorial-popup">
            <h3 id="tutorial-title">🎯 ¡Recuerda revisar tus tareas!</h3>
            <p id="tutorial-description">Este botón te permite acceder a tu panel de tareas CRM. Haz
                clic para gestionar tus tareas pendientes, completadas y crear nuevas.</p>
            <div class="mb-3 text-xs text-gray-500">
                <span id="tutorial-progress">Paso 1 de 2</span>
            </div>
            <div class="flex space-x-2">
                <button id="tutorial-next" onclick="nextTutorialStep()">Siguiente</button>
                <button id="tutorial-close" onclick="hideTutorial()"
                    class="bg-gray-400 hover:bg-gray-500">Cerrar</button>
            </div>
        </div>
    </div>

    {{-- Fondo animado moderno con librerías --}}
    <div class="fixed inset-0 -z-10">
        {{-- Gradiente base blanco --}}
        <div class="absolute inset-0 bg-gradient-to-br from-white via-gray-50 to-blue-50"></div>

        {{-- Canvas para Three.js --}}
        {{-- <canvas id="threeCanvas" class="absolute inset-0 opacity-60"></canvas> --}}

        {{-- Contenedor para Particles.js --}}
        <div id="particles-js" class="absolute inset-0"></div>

        {{-- Overlay de gradiente sutil --}}
        <div class="absolute inset-0 bg-gradient-to-t from-white/30 via-transparent to-transparent">
        </div>
    </div>

    @include('layouts.headerbackoffice')

    <div class="w-4/5 mx-auto py-6 px-6 relative z-10" x-data="crmVentas()">
        {{-- Botón de Tareas CRM --}}
        <div class="fixed top-20 left-6 z-40">
            <button id="btn-tareas-crm" @click="toggleTaskPanel()"
                class="bg-[#54debd] hover:bg-[#43c5a9] text-white p-4 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-110 flex items-center justify-center"
                title="Gestionar Tareas CRM">
                <i class="fas fa-sticky-note text-2xl mr-2"></i>
                <i class="fas fa-pencil-alt text-lg"></i>
            </button>

            {{-- Botón para repetir tutorial --}}
            <button id="btn-repetir-tutorial" @click="repetirTutorial()"
                class="mt-2 bg-gray-500 hover:bg-gray-600 text-white p-2 rounded-full shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center justify-center text-xs"
                title="Repetir tutorial (se resetea el contador diario)">
                <i class="fas fa-question text-sm"></i>
            </button>

            {{-- Botón de Alertas de Ventas --}}
            <button id="btn-alertas-ventas" @click="toggleAlertasPanel()"
                class="mt-2 bg-orange-500 hover:bg-orange-600 text-white p-2 rounded-full shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center justify-center text-xs"
                title="Gestionar Alertas de Ventas">
                <i class="fas fa-bell text-sm"></i>
            </button>

            {{-- Botón de debug temporal --}}
            <button id="btn-debug-style" onclick="cambiarEstiloHighlight()"
                class="mt-2 bg-red-500 hover:bg-red-600 text-white p-2 rounded-full shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center justify-center text-xs"
                title="Cambiar estilo del highlight">
                <i class="fas fa-magic text-sm"></i>
            </button>
        </div>

        {{-- === FORMULARIO DE BÚSQUEDA === --}}
        <div
            class="bg-white/95 backdrop-blur-md rounded-2xl shadow-xl overflow-hidden mb-6 border border-white/30 shadow-[#54debd]/10">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold flex items-center text-gray-800 mb-4">
                    <i class="fas fa-search text-[#54debd] mr-2"></i>
                    Filtros de Búsqueda
                </h3>

                <form method="GET" action="{{ route('admin.users-history') }}" class="space-y-4"
                    id="filtersForm">
                    {{-- Primera fila: Búsqueda principal y botones --}}
                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2">Búsqueda</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Buscar por nombre, email o teléfono..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#54debd]/50 focus:border-[#54debd] transition-colors" />
                        </div>

                        <div class="flex items-center space-x-3">
                            <button type="submit"
                                class="px-6 py-3 bg-[#54debd] text-white font-medium rounded-lg hover:bg-[#43c5a9] transition-colors shadow-sm hover:shadow-md flex items-center">
                                <i class="fas fa-search mr-2"></i>
                                Buscar
                            </button>

                            <button type="button" @click="limpiarFiltros()"
                                class="px-6 py-3 bg-gray-500 text-white font-medium rounded-lg hover:bg-gray-600 transition-colors shadow-sm hover:shadow-md flex items-center">
                                <i class="fas fa-times mr-2"></i>
                                Limpiar
                            </button>
                        </div>
                    </div>

                    {{-- Segunda fila: Filtros principales --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Comunidad
                                Autónoma</label>
                            <select name="ccaa"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#54debd]/50 focus:border-[#54debd] transition-colors"
                                onchange="handleCcaaChange(event)">
                                <option value="">Todas las CCAA</option>
                                @foreach ($ccaas as $ccaa)
                                    <option value="{{ $ccaa->id }}"
                                        {{ request('ccaa') == $ccaa->id ? 'selected' : '' }}>
                                        {{ $ccaa->nombre_ccaa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de
                                Ayuda</label>
                            <select name="ayuda_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#54debd]/50 focus:border-[#54debd] transition-colors"
                                onchange="document.getElementById('filtersForm').submit()">
                                <option value="">Todas las ayudas</option>
                                @foreach ($ayudas as $ayuda)
                                    <option value="{{ $ayuda->id }}"
                                        {{ request('ayuda_id') == $ayuda->id ? 'selected' : '' }}>
                                        {{ $ayuda->nombre_ayuda }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado
                                Comercial</label>
                            <select name="estado_comercial"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#54debd]/50 focus:border-[#54debd] transition-colors">
                                <option value="">Todos los estados</option>
                                <option value="caliente"
                                    {{ request('estado_comercial') == 'caliente' ? 'selected' : '' }}>
                                    🔥 Caliente</option>
                                <option value="tibio"
                                    {{ request('estado_comercial') == 'tibio' ? 'selected' : '' }}>
                                    ☕ Tibio</option>
                                <option value="frio"
                                    {{ request('estado_comercial') == 'frio' ? 'selected' : '' }}>
                                    ❄️ Frío</option>
                                <option value="sin_estado"
                                    {{ request('estado_comercial') == 'sin_estado' ? 'selected' : '' }}>
                                    ❓ Sin Estado</option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2">Pipeline</label>
                            <select name="pipeline"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#54debd]/50 focus:border-[#54debd] transition-colors">
                                <option value="">Todos los pipelines</option>
                                <option value="Captado"
                                    {{ request('pipeline') == 'Captado' ? 'selected' : '' }}>👤
                                    Captado</option>
                                <option value="Test hecho"
                                    {{ request('pipeline') == 'Test hecho' ? 'selected' : '' }}>📋
                                    Test hecho</option>
                                <option value="No cualificado"
                                    {{ request('pipeline') == 'No cualificado' ? 'selected' : '' }}>
                                    ❌ No cualificado</option>
                                <option value="Cualificado"
                                    {{ request('pipeline') == 'Cualificado' ? 'selected' : '' }}>✅
                                    Cualificado</option>
                                <option value="Cuestionario completado"
                                    {{ request('pipeline') == 'Cuestionario completado' ? 'selected' : '' }}>
                                    📝 Cuestionario completado</option>
                                <option value="No beneficiario"
                                    {{ request('pipeline') == 'No beneficiario' ? 'selected' : '' }}>
                                    🚫 No beneficiario</option>
                                <option value="Beneficiario"
                                    {{ request('pipeline') == 'Beneficiario' ? 'selected' : '' }}>⭐
                                    Beneficiario</option>
                                <option value="Contrata"
                                    {{ request('pipeline') == 'Contrata' ? 'selected' : '' }}>🤝
                                    Contrata</option>
                            </select>
                        </div>
                    </div>

                    {{-- Tercera fila: Información adicional --}}
                    <div class="flex items-center justify-end pt-2">
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Los filtros se aplican automáticamente
                        </div>
                    </div>
                </form>
                <script>
                    function handleCcaaChange(event) {
                        const form = document.getElementById('filtersForm');
                        if (!form) return;
                        const ayudaSelect = form.querySelector('select[name="ayuda_id"]');
                        if (ayudaSelect) {
                            ayudaSelect.value = '';
                        }
                        form.submit();
                    }
                </script>
            </div>
        </div>

        {{-- === DASHBOARD CRM === --}}
        <div
            class="bg-white/95 backdrop-blur-md rounded-2xl shadow-xl overflow-hidden mb-6 border border-white/30 shadow-[#54debd]/10">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold flex items-center text-gray-800">
                        <i class="fas fa-chart-line text-[#54debd] mr-2"></i>
                        Dashboard CRM - Estados Comerciales
                    </h3>

                    {{-- Indicador de Filtro Activo --}}
                    <div x-show="activeFilter !== 'all'" x-transition
                        class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Filtro activo:</span>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                            :class="{
                                'bg-red-100 text-red-800': activeFilter === 'caliente',
                                'bg-yellow-100 text-yellow-800': activeFilter === 'tibio',
                                'bg-blue-100 text-blue-800': activeFilter === 'frio',
                                'bg-gray-100 text-gray-800': activeFilter === 'sin_estado'
                            }">
                            <i class="fas mr-2"
                                :class="{
                                    'fa-fire text-red-500': activeFilter === 'caliente',
                                    'fa-mug-hot text-yellow-500': activeFilter === 'tibio',
                                    'fa-snowflake text-blue-500': activeFilter === 'frio',
                                    'fa-question-circle text-gray-500': activeFilter === 'sin_estado'
                                }"></i>
                            <span x-text="getFilterLabel(activeFilter)"></span>
                            <button @click="clearFilter()"
                                class="ml-2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
                    {{-- Total Usuarios --}}
                    <div class="bg-gradient-to-r from-[#54debd] to-[#43c5a9] p-6 rounded-xl text-white cursor-pointer hover:from-[#43c5a9] hover:to-[#54debd] transition-all duration-200 transform hover:scale-105"
                        @click="applyEstadoFilter('all')">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/90 text-sm font-medium">Total Usuarios</p>
                                <p class="text-3xl font-bold">{{ $totalUsers }}</p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-full">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Estado Caliente --}}
                    <div class="bg-gradient-to-r from-red-500 to-red-600 p-6 rounded-xl text-white cursor-pointer hover:from-red-600 hover:to-red-700 transition-all duration-200 transform hover:scale-105"
                        @click="applyEstadoFilter('caliente')">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm font-medium">Caliente</p>
                                <p class="text-3xl font-bold">
                                    {{ $estadosComerciales['caliente'] ?? 0 }}</p>
                            </div>
                            <div class="bg-red-400 p-3 rounded-full">
                                <i class="fas fa-fire text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Estado Tibio --}}
                    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 p-6 rounded-xl text-white cursor-pointer hover:from-yellow-600 hover:to-yellow-700 transition-all duration-200 transform hover:scale-105"
                        @click="applyEstadoFilter('tibio')">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-yellow-100 text-sm font-medium">Tibio</p>
                                <p class="text-3xl font-bold">
                                    {{ $estadosComerciales['tibio'] ?? 0 }}</p>
                            </div>
                            <div class="bg-yellow-400 p-3 rounded-full">
                                <i class="fas fa-mug-hot text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Estado Frío --}}
                    <div class="bg-gradient-to-r from-blue-400 to-blue-500 p-6 rounded-xl text-white cursor-pointer hover:from-blue-500 hover:to-blue-600 transition-all duration-200 transform hover:scale-105"
                        @click="applyEstadoFilter('frio')">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">Frío</p>
                                <p class="text-3xl font-bold">
                                    {{ $estadosComerciales['frio'] ?? 0 }}</p>
                            </div>
                            <div class="bg-blue-300 p-3 rounded-full">
                                <i class="fas fa-snowflake text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- === DASHBOARD PIPELINES === --}}
        <div
            class="bg-white/95 backdrop-blur-md rounded-2xl shadow-xl overflow-hidden mb-6 border border-white/30 shadow-[#54debd]/10">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold flex items-center text-gray-800">
                        <i class="fas fa-project-diagram text-[#54debd] mr-2"></i>
                        Dashboard CRM - Pipelines
                    </h3>

                    {{-- Indicador de Filtro de Pipeline Activo --}}
                    <div x-show="activePipelineFilter !== 'all'" x-transition
                        class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Filtro activo:</span>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                            <i class="fas fa-project-diagram text-purple-500 mr-2"></i>
                            <span x-text="getPipelineFilterLabel(activePipelineFilter)"></span>
                            <button @click="clearPipelineFilter()"
                                class="ml-2 text-purple-400 hover:text-purple-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                {{-- Embudo de Pipeline --}}
                <div class="funnel-container">
                    {{-- Etapa 1: Captado (más ancha) --}}
                    <div class="funnel-stage funnel-stage-1"
                        @click="applyPipelineFilter('Captado')">
                        <div class="funnel-content">
                            <div class="funnel-number">{{ $pipelines['Captado'] ?? 0 }}</div>
                            <div class="funnel-label">👤 Captado</div>
                        </div>
                    </div>

                    {{-- Etapa 2: Test hecho --}}
                    <div class="funnel-stage funnel-stage-2"
                        @click="applyPipelineFilter('Test hecho')">
                        <div class="funnel-content">
                            <div class="funnel-number">{{ $pipelines['Test hecho'] ?? 0 }}</div>
                            <div class="funnel-label">📋 Test hecho</div>
                        </div>
                    </div>

                    {{-- Etapa 3: Cualificado y No cualificado (mismo nivel) --}}
                    <div class="funnel-level-3">
                        <div class="funnel-stage funnel-stage-3-left"
                            @click="applyPipelineFilter('Cualificado')">
                            <div class="funnel-content">
                                <div class="funnel-number">{{ $pipelines['Cualificado'] ?? 0 }}
                                </div>
                                <div class="funnel-label">✅ Cualificado</div>
                            </div>
                        </div>
                        <div class="funnel-stage funnel-stage-3-right"
                            @click="applyPipelineFilter('No cualificado')">
                            <div class="funnel-content">
                                <div class="funnel-number">{{ $pipelines['No cualificado'] ?? 0 }}
                                </div>
                                <div class="funnel-label">❌ No cualif.</div>
                            </div>
                        </div>
                    </div>

                    {{-- Etapa 4: Cuestionario completado --}}
                    <div class="funnel-stage funnel-stage-4"
                        @click="applyPipelineFilter('Cuestionario completado')">
                        <div class="funnel-content">
                            <div class="funnel-number">
                                {{ $pipelines['Cuestionario completado'] ?? 0 }}</div>
                            <div class="funnel-label">📝 Cuestionario</div>
                        </div>
                    </div>

                    {{-- Etapa 5: Beneficiario y No beneficiario (mismo nivel) --}}
                    <div class="funnel-level-5">
                        <div class="funnel-stage funnel-stage-5-left"
                            @click="applyPipelineFilter('Beneficiario')">
                            <div class="funnel-content">
                                <div class="funnel-number">{{ $pipelines['Beneficiario'] ?? 0 }}
                                </div>
                                <div class="funnel-label">⭐ Beneficiario</div>
                            </div>
                        </div>
                        <div class="funnel-stage funnel-stage-5-right"
                            @click="applyPipelineFilter('No beneficiario')">
                            <div class="funnel-content">
                                <div class="funnel-number">
                                    {{ $pipelines['No beneficiario'] ?? 0 }}</div>
                                <div class="funnel-label">🚫 No benef.</div>
                            </div>
                        </div>
                    </div>

                    {{-- Etapa 6: Contrata (más estrecha) --}}
                    <div class="funnel-stage funnel-stage-6"
                        @click="applyPipelineFilter('Contrata')">
                        <div class="funnel-content">
                            <div class="funnel-number">{{ $pipelines['Contrata'] ?? 0 }}</div>
                            <div class="funnel-label">🤝 Contrata</div>
                        </div>
                    </div>
                </div>

                {{-- Estilos CSS para el embudo --}}
                <style>
                    .funnel-container {
                        position: relative;
                        max-width: 350px;
                        margin: 0 auto;
                        padding: 8px 0;
                    }

                    .funnel-stage {
                        position: relative;
                        margin: 0 auto 2px;
                        background: linear-gradient(135deg, #54debd, #43c5a9);
                        border-radius: 8px;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        box-shadow: 0 3px 10px rgba(84, 222, 189, 0.3);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        min-height: 45px;
                    }

                    .funnel-stage:hover {
                        transform: scale(1.02);
                        box-shadow: 0 6px 20px rgba(84, 222, 189, 0.4);
                        background: linear-gradient(135deg, #43c5a9, #54debd);
                    }

                    .funnel-stage-1 {
                        width: 100%;
                    }

                    .funnel-stage-2 {
                        width: 75%;
                    }

                    .funnel-stage-4 {
                        width: 55%;
                    }

                    .funnel-stage-6 {
                        width: 30%;
                    }

                    /* Niveles con dos opciones lado a lado */
                    .funnel-level-3,
                    .funnel-level-5 {
                        display: flex;
                        justify-content: center;
                        gap: 12px;
                        margin: 0 auto 2px;
                        width: 55%;
                    }

                    .funnel-stage-3-left,
                    .funnel-stage-3-right,
                    .funnel-stage-5-left,
                    .funnel-stage-5-right {
                        flex: 1;
                        max-width: 150px;
                        background: linear-gradient(135deg, #54debd, #43c5a9);
                        border-radius: 8px;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        box-shadow: 0 3px 10px rgba(84, 222, 189, 0.3);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        min-height: 45px;
                    }

                    .funnel-stage-3-right,
                    .funnel-stage-5-right {
                        background: linear-gradient(135deg, #ff6b6b, #ee5a52);
                        box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
                    }

                    .funnel-stage-3-left:hover,
                    .funnel-stage-3-right:hover,
                    .funnel-stage-5-left:hover,
                    .funnel-stage-5-right:hover {
                        transform: scale(1.02);
                        box-shadow: 0 6px 20px rgba(84, 222, 189, 0.4);
                    }

                    .funnel-stage-3-right:hover,
                    .funnel-stage-5-right:hover {
                        box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
                    }

                    .funnel-content {
                        text-align: center;
                        color: white;
                    }

                    .funnel-number {
                        font-size: 1.2rem;
                        font-weight: bold;
                        margin-bottom: 2px;
                    }

                    .funnel-label {
                        font-size: 0.65rem;
                        opacity: 0.9;
                        font-weight: 500;
                    }

                    @media (max-width: 768px) {
                        .funnel-container {
                            max-width: 280px;
                            padding: 5px 0;
                        }

                        .funnel-level-3,
                        .funnel-level-5 {
                            flex-direction: column;
                            width: 90%;
                            gap: 4px;
                        }

                        .funnel-stage-3-left,
                        .funnel-stage-3-right,
                        .funnel-stage-5-left,
                        .funnel-stage-5-right {
                            max-width: none;
                            width: 100%;
                        }
                    }
                </style>

            </div>
        </div>

        {{-- === TABLA DE USUARIOS === --}}
        <div
            class="bg-white/95 backdrop-blur-md rounded-2xl shadow-xl overflow-hidden border border-white/30 shadow-[#54debd]/10">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-table text-[#54debd] mr-2"></i>
                    Listado de Usuarios
                </h3>
            </div>

            <div class="overflow-x-auto px-6 py-4">
                <div class="grid grid-cols-1 gap-6">
                    @foreach ($users as $user)
                        @php
                            $ans = $answers[$user->id] ?? collect();
                            $dni = $ans->firstWhere('question_id', 34)?->answer ?? '—';
                            $provincia = $ans->firstWhere('question_id', 36)?->answer ?? '—';

                            // Obtener estados comerciales y pipelines del usuario
                            $userAyudas = $user->ayudas()->with('ayuda')->get();

                            // Si hay filtro de ayuda activo, mostrar solo el estado comercial y pipeline de esa ayuda
                            if (request('ayuda_id')) {
                                $ayudaFiltrada = $userAyudas
                                    ->where('ayuda_id', request('ayuda_id'))
                                    ->first();
                                $estadosComerciales = $ayudaFiltrada
                                    ? collect([$ayudaFiltrada->estado_comercial])->filter()
                                    : collect();
                                $pipelines = $ayudaFiltrada
                                    ? collect([$ayudaFiltrada->pipeline])->filter()
                                    : collect();
                            } else {
                                $estadosComerciales = $userAyudas
                                    ->pluck('estado_comercial')
                                    ->filter()
                                    ->unique();
                                $pipelines = $userAyudas->pluck('pipeline')->filter()->unique();
                            }
                        @endphp
                        <div class="relative bg-white/95 backdrop-blur-md rounded-xl shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer border border-white/30 @if ($user->is_admin) border-[#54debd] bg-[#e8fffa]/95 @endif hover:scale-[1.02] hover:shadow-[#54debd]/20"
                            @click="openUserModal({{ $user->id }})"
                            data-user-id="{{ $user->id }}">
                            <div
                                class="flex flex-col sm:flex-row items-center p-4 border-b space-y-2 sm:space-y-0 sm:space-x-4">
                                <div
                                    class="h-16 w-16 aspect-square rounded-full flex items-center justify-center @if ($user->is_admin) bg-white @else bg-[#54debd] @endif">
                                    <i
                                        class="fas {{ $user->is_admin ? 'fa-user-shield' : 'fa-user' }} text-[#000] text-4xl"></i>
                                </div>

                                <div
                                    class="flex flex-wrap items-center text-sm text-gray-700 font-medium space-x-3 w-full justify-between">
                                    <div class="flex items-center space-x-3">
                                        <span
                                            class="font-medium text-gray-900">{{ $user->name }}</span>

                                        {{-- Estados Comerciales --}}
                                        <div class="flex items-center space-x-2">
                                            @foreach ($estadosComerciales as $estadoComercial)
                                                @if ($estadoComercial === 'caliente')
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                        <i
                                                            class="fas fa-fire text-red-500 mr-1"></i>
                                                        Caliente
                                                    </span>
                                                @elseif($estadoComercial === 'tibio')
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                                        <i
                                                            class="fas fa-mug-hot text-yellow-500 mr-1"></i>
                                                        Tibio
                                                    </span>
                                                @elseif($estadoComercial === 'frio')
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                                        <i
                                                            class="fas fa-snowflake text-blue-500 mr-1"></i>
                                                        Frío
                                                    </span>
                                                @endif
                                            @endforeach

                                            @if ($estadosComerciales->isEmpty())
                                                <span
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                                    <i
                                                        class="fas fa-question-circle text-gray-500 mr-1"></i>
                                                    Sin Estado
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Pipelines --}}
                                        <div class="flex items-center space-x-2">
                                            @foreach ($pipelines as $pipeline)
                                                @if ($pipeline === 'Captado')
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                        <i
                                                            class="fas fa-user-plus text-green-500 mr-1"></i>
                                                        Captado
                                                    </span>
                                                @elseif($pipeline === 'Test hecho')
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                                        <i
                                                            class="fas fa-clipboard-check text-blue-500 mr-1"></i>
                                                        Test hecho
                                                    </span>
                                                @elseif($pipeline === 'No cualificado')
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                        <i
                                                            class="fas fa-times-circle text-red-500 mr-1"></i>
                                                        No cualificado
                                                    </span>
                                                @elseif($pipeline === 'Cualificado')
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                        <i
                                                            class="fas fa-check-circle text-green-500 mr-1"></i>
                                                        Cualificado
                                                    </span>
                                                @elseif($pipeline === 'Cuestionario completado')
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                                        <i
                                                            class="fas fa-file-alt text-purple-500 mr-1"></i>
                                                        Cuestionario completado
                                                    </span>
                                                @elseif($pipeline === 'Beneficiario')
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-emerald-100 text-emerald-800 rounded-full">
                                                        <i
                                                            class="fas fa-star text-emerald-500 mr-1"></i>
                                                        Beneficiario
                                                    </span>
                                                @elseif($pipeline === 'No beneficiario')
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full">
                                                        <i
                                                            class="fas fa-user-times text-orange-500 mr-1"></i>
                                                        No beneficiario
                                                    </span>
                                                @elseif($pipeline === 'Contrata')
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full">
                                                        <i
                                                            class="fas fa-handshake text-indigo-500 mr-1"></i>
                                                        Contrata
                                                    </span>
                                                @elseif($pipeline === 'No contrata')
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                                        <i
                                                            class="fas fa-ban text-gray-500 mr-1"></i>
                                                        No contrata
                                                    </span>
                                                @endif
                                            @endforeach

                                            @if ($pipelines->isEmpty())
                                                <span
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                                    <i
                                                        class="fas fa-question-circle text-gray-500 mr-1"></i>
                                                    Sin Pipeline
                                                </span>
                                            @endif
                                        </div>

                                    </div>

                                    <div class="flex items-center space-x-3">
                                        <span>Registro:
                                            {{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y') }}</span>
                                        @if ($user->is_admin)
                                            <span
                                                class="ml-2 px-2 py-1 text-xs rounded bg-[#54debd] text-white font-semibold">Admin</span>
                                        @endif
                                        <div class="flex items-center space-x-2">
                                            <button
                                                @click.stop="openDeleteModal({{ $user->id }}, '{{ $user->name }}')"
                                                class="px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition-colors"
                                                title="Eliminar usuario">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Apartado historial de actividad --}}
                                <div class="w-full mt-8">
                                    @php
                                        $historial = $user
                                            ->historialActividad()
                                            ->orderByDesc('fecha_inicio')
                                            ->limit(3)
                                            ->get();
                                    @endphp
                                    <div
                                        class="bg-white/95 backdrop-blur-md rounded-xl shadow-xl border-t-4 border-gray-200/50 px-6 py-4 flex flex-col min-h-[70px] shadow-[#54debd]/10">
                                        <span
                                            class="font-semibold text-xs text-gray-500 uppercase tracking-wide mb-2">
                                            Historial de actividad
                                        </span>
                                        <div class="max-h-32 overflow-y-auto pr-2">
                                            @if ($historial->count())
                                                <ul class="space-y-1">
                                                    @foreach ($historial as $evento)
                                                        <li
                                                            class="text-sm text-gray-800 font-medium leading-snug flex flex-col">
                                                            <span><strong>{{ $evento->actividad }}</strong>
                                                                <span
                                                                    class="text-xs text-gray-500">({{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('d/m/Y H:i') }})</span></span>
                                                            <span
                                                                class="text-xs text-gray-600">{{ $evento->observaciones }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-gray-400">Sin historial de
                                                    actividad</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Paginación --}}
            <div class="px-6 py-4">
                {{ $users->links() }}
            </div>
        </div>

        <!-- Modal de Usuario (Popup) -->
        <div x-show="userModalOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 bg-black bg-opacity-40" @click="closeUserModal()" x-cloak>
        </div>

        <div x-show="userModalOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
            <div
                class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden border border-white/30 shadow-[#54debd]/20">
                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800">Panel de Usuario</h2>
                    <button @click="closeUserModal()"
                        class="text-3xl text-gray-400 hover:text-gray-600 transition-colors">&times;</button>
                </div>

                <div class="overflow-y-auto max-h-[calc(90vh-120px)]">
                    <div id="userModalContent" class="p-6">
                        <div class="flex items-center justify-center h-64 text-gray-400"
                            x-show="loading">
                            <svg class="animate-spin h-12 w-12 mr-3 text-[#54debd]"
                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            <span class="text-lg">Cargando usuario...</span>
                        </div>
                        <div x-show="!loading" x-html="modalHtml"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Eliminación -->
        <div x-show="deleteModalOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 bg-black bg-opacity-40" @click="closeDeleteModal()" x-cloak>
        </div>
        <div x-show="deleteModalOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
            <div
                class="bg-white/95 backdrop-blur-md rounded-lg shadow-xl max-w-md w-full p-6 border border-white/30 shadow-[#54debd]/20">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-gray-900">Confirmar eliminación</h3>
                    </div>
                </div>
                <div class="mb-6">
                    <p class="text-sm text-gray-500">
                        ¿Estás seguro de que quieres eliminar al usuario <strong
                            x-text="deleteUserName"></strong>? Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="flex justify-end space-x-3">
                    <button @click="closeDeleteModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button @click="confirmDelete()"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>

        <!-- Panel de Tareas CRM -->
        <div x-show="taskPanelOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="fixed top-32 left-6 z-50" x-cloak>
            <div
                class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl w-96 border border-white/30 shadow-[#54debd]/20">
                {{-- Header del panel --}}
                <div
                    class="p-4 border-b border-gray-200 bg-gradient-to-r from-[#54debd] to-[#43c5a9] text-white rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold flex items-center">
                            <i class="fas fa-tasks mr-2"></i>
                            Tareas CRM
                        </h3>
                        <div class="flex items-center space-x-2">
                            <button @click="loadTasks()"
                                class="text-white hover:text-gray-200 transition-colors p-1"
                                title="Recargar tareas">
                                <i class="fas fa-sync-alt text-sm"></i>
                            </button>
                            <button @click="toggleTaskPanel()"
                                class="text-white hover:text-gray-200 transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuración de Particles.js
        particlesJS('particles-js', {
            particles: {
                number: {
                    value: 80,
                    density: {
                        enable: true,
                        value_area: 800
                    }
                },
                color: {
                    value: ['#0d9488', '#1d4ed8', '#6d28d9', '#be185d']
                },
                shape: {
                    type: 'circle',
                    stroke: {
                        width: 0,
                        color: '#000000'
                    }
                },
                opacity: {
                    value: 0.7,
                    random: false,
                    anim: {
                        enable: false,
                        speed: 1,
                        opacity_min: 0.3,
                        sync: false
                    }
                },
                size: {
                    value: 3,
                    random: true,
                    anim: {
                        enable: false,
                        speed: 40,
                        size_min: 0.1,
                        sync: false
                    }
                },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: '#0d9488',
                    opacity: 0.5,
                    width: 1.5
                },
                move: {
                    enable: true,
                    speed: 2,
                    direction: 'none',
                    random: false,
                    straight: false,
                    out_mode: 'out',
                    bounce: false,
                    attract: {
                        enable: false,
                        rotateX: 600,
                        rotateY: 1200
                    }
                }
            },
            interactivity: {
                detect_on: 'canvas',
                events: {
                    onhover: {
                        enable: true,
                        mode: 'repulse'
                    },
                    onclick: {
                        enable: true,
                        mode: 'push'
                    },
                    resize: true
                },
                modes: {
                    grab: {
                        distance: 400,
                        line_linked: {
                            opacity: 1
                        }
                    },
                    bubble: {
                        distance: 400,
                        size: 40,
                        duration: 2,
                        opacity: 8,
                        speed: 3
                    },
                    repulse: {
                        distance: 200,
                        duration: 0.4
                    },
                    push: {
                        particles_nb: 4
                    },
                    remove: {
                        particles_nb: 2
                    }
                }
            },
            retina_detect: true
        });

        // Configuración de Three.js para efectos 3D sutiles
        // function initThreeJS() {
        //   const canvas = document.getElementById('threeCanvas');
        //   if (!canvas) return;

        //   const scene = new THREE.Scene();
        //   const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        //   const renderer = new THREE.WebGLRenderer({ canvas: canvas, alpha: true, antialias: true });

        //   renderer.setSize(window.innerWidth, window.innerHeight);
        //   renderer.setClearColor(0x000000, 0);

        //   // Crear geometrías flotantes
        //   const geometries = [];
        //   const colors = [0x0d9488, 0x1d4ed8, 0x6d28d9, 0xbe185d];

        //   for (let i = 0; i < 15; i++) {
        //     const geometry = new THREE.SphereGeometry(Math.random() * 0.5 + 0.1, 8, 6);
        //     const material = new THREE.MeshBasicMaterial({ 
        //       color: colors[Math.floor(Math.random() * colors.length)],
        //           transparent: true,
        //           opacity: 0.25
        //         });
        //         const mesh = new THREE.Mesh(geometry, material);

        //         mesh.position.x = (Math.random() - 0.5) * 20;
        //         mesh.position.y = (Math.random() - 0.5) * 20;
        //         mesh.position.z = (Math.random() - 0.5) * 10;

        //         geometries.push(mesh);
        //         scene.add(mesh);
        //       }

        //       camera.position.z = 5;

        //       // Animación
        //       function animate() {
        //         requestAnimationFrame(animate);

        //         geometries.forEach((mesh, index) => {
        //           mesh.rotation.x += 0.001 * (index + 1);
        //           mesh.rotation.y += 0.001 * (index + 1);
        //           mesh.position.y += Math.sin(Date.now() * 0.001 + index) * 0.001;
        //         });

        //         renderer.render(scene, camera);
        //       }

        //       animate();

        //       // Responsive
        //       window.addEventListener('resizer', () => {
        //         camera.aspect = window.innerWidth / window.innerHeight;
        //         camera.updateProjectionMatrix();
        //         renderer.setSize(window.innerWidth, window.innerHeight);
        //       });
        //     }

        //     // Inicializar Three.js cuando la página esté lista
        //     document.addEventListener('DOMContentLoaded', function() {
        //       initThreeJS();
        //     });

        function crmVentas() {
            return {
                userModalOpen: false,
                loading: false,
                modalHtml: '',
                // Variables para modal de eliminación
                deleteModalOpen: false,
                deleteUserId: null,
                deleteUserName: '',
                // Variables para filtros de estado comercial
                activeFilter: 'all',
                // Variables para filtros de pipeline
                activePipelineFilter: 'all',
                // Variables para panel de tareas
                taskPanelOpen: false,
                tasks: [],
                admins: [],
                newTask: {
                    title: '',
                    description: '',
                    status: 'pendiente',
                    assigned_to: '',
                    user_id: ''
                },
                // Variables para panel de alertas
                alertasPanelOpen: false,
                alertas: [],
                newAlerta: {
                    titulo: '',
                    nota: '',
                    fecha_alerta: '',
                    user_id: ''
                },

                // Aplicar filtro de estado comercial
                applyEstadoFilter(estado) {
                    this.activeFilter = estado;

                    // Actualizar el select del formulario
                    const estadoSelect = document.querySelector('select[name="estado_comercial"]');
                    if (estadoSelect) {
                        if (estado === 'all') {
                            estadoSelect.value = '';
                        } else if (estado === 'sin_estado') {
                            estadoSelect.value = 'sin_estado';
                        } else {
                            estadoSelect.value = estado;
                        }
                    }

                    // Limpiar otros filtros si es necesario
                    if (estado !== 'all') {
                        // Limpiar búsqueda de texto
                        const searchInput = document.querySelector('input[name="search"]');
                        if (searchInput) searchInput.value = '';

                        // Limpiar filtros de CCAA y ayuda
                        const ccaaSelect = document.querySelector('select[name="ccaa"]');
                        if (ccaaSelect) ccaaSelect.value = '';

                        const ayudaSelect = document.querySelector('select[name="ayuda_id"]');
                        if (ayudaSelect) ayudaSelect.value = '';

                        // Limpiar filtro de pipeline
                        const pipelineSelect = document.querySelector('select[name="pipeline"]');
                        if (pipelineSelect) pipelineSelect.value = '';
                        this.activePipelineFilter = 'all';

                        // Desmarcar checkbox de sin contratación
                        const sinContratacionCheckbox = document.querySelector(
                            'input[name="sin_contratacion"]');
                        if (sinContratacionCheckbox) sinContratacionCheckbox.checked = false;
                    }

                    // Aplicar el filtro enviando el formulario
                    const form = document.querySelector('form[action*="users-history"]');
                    if (form) {
                        form.submit();
                    }
                },

                // Limpiar filtro activo
                clearFilter() {
                    this.activeFilter = 'all';

                    // Limpiar el select de estado comercial
                    const estadoSelect = document.querySelector('select[name="estado_comercial"]');
                    if (estadoSelect) estadoSelect.value = '';

                    // Enviar formulario sin filtros
                    const form = document.querySelector('form[action*="users-history"]');
                    if (form) {
                        form.submit();
                    }
                },

                // Aplicar filtro de pipeline
                applyPipelineFilter(pipeline) {
                    this.activePipelineFilter = pipeline;

                    // Actualizar el select del formulario
                    const pipelineSelect = document.querySelector('select[name="pipeline"]');
                    if (pipelineSelect) {
                        if (pipeline === 'all') {
                            pipelineSelect.value = '';
                        } else {
                            pipelineSelect.value = pipeline;
                        }
                    }

                    // Limpiar otros filtros si es necesario
                    if (pipeline !== 'all') {
                        // Limpiar búsqueda de texto
                        const searchInput = document.querySelector('input[name="search"]');
                        if (searchInput) searchInput.value = '';

                        // Limpiar filtros de CCAA y ayuda
                        const ccaaSelect = document.querySelector('select[name="ccaa"]');
                        if (ccaaSelect) ccaaSelect.value = '';

                        const ayudaSelect = document.querySelector('select[name="ayuda_id"]');
                        if (ayudaSelect) ayudaSelect.value = '';

                        // Limpiar filtro de estado comercial
                        const estadoSelect = document.querySelector('select[name="estado_comercial"]');
                        if (estadoSelect) estadoSelect.value = '';
                        this.activeFilter = 'all';

                        // Desmarcar checkbox de sin contratación
                        const sinContratacionCheckbox = document.querySelector(
                            'input[name="sin_contratacion"]');
                        if (sinContratacionCheckbox) sinContratacionCheckbox.checked = false;
                    }

                    // Aplicar el filtro enviando el formulario
                    const form = document.querySelector('form[action*="users-history"]');
                    if (form) {
                        form.submit();
                    }
                },

                // Limpiar filtro de pipeline activo
                clearPipelineFilter() {
                    this.activePipelineFilter = 'all';

                    // Limpiar el select de pipeline
                    const pipelineSelect = document.querySelector('select[name="pipeline"]');
                    if (pipelineSelect) pipelineSelect.value = '';

                    // Enviar formulario sin filtros
                    const form = document.querySelector('form[action*="users-history"]');
                    if (form) {
                        form.submit();
                    }
                },

                // Limpiar todos los filtros
                limpiarFiltros() {
                    // Limpiar búsqueda
                    const searchInput = document.querySelector('input[name="search"]');
                    if (searchInput) searchInput.value = '';

                    // Limpiar CCAA
                    const ccaaSelect = document.querySelector('select[name="ccaa"]');
                    if (ccaaSelect) ccaaSelect.value = '';

                    // Limpiar ayuda
                    const ayudaSelect = document.querySelector('select[name="ayuda_id"]');
                    if (ayudaSelect) ayudaSelect.value = '';

                    // Limpiar estado comercial
                    const estadoSelect = document.querySelector('select[name="estado_comercial"]');
                    if (estadoSelect) estadoSelect.value = '';

                    // Limpiar pipeline
                    const pipelineSelect = document.querySelector('select[name="pipeline"]');
                    if (pipelineSelect) pipelineSelect.value = '';

                    // Limpiar filtros activos del dashboard
                    this.activeFilter = 'all';
                    this.activePipelineFilter = 'all';

                    // Enviar formulario sin filtros
                    const form = document.querySelector('form[action*="users-history"]');
                    if (form) {
                        form.submit();
                    }
                },

                // Obtener etiqueta del filtro
                getFilterLabel(estado) {
                    const labels = {
                        'caliente': 'Caliente',
                        'tibio': 'Tibio',
                        'frio': 'Frío',
                        'sin_estado': 'Sin Estado',
                        'all': 'Todos'
                    };
                    return labels[estado] || 'Todos';
                },

                // Obtener etiqueta del filtro de pipeline
                getPipelineFilterLabel(pipeline) {
                    const labels = {
                        'Captado': 'Captado',
                        'Test hecho': 'Test hecho',
                        'No cualificado': 'No cualificado',
                        'Cualificado': 'Cualificado',
                        'Cuestionario completado': 'Cuestionario completado',
                        'No beneficiario': 'No beneficiario',
                        'Beneficiario': 'Beneficiario',
                        'Contrata': 'Contrata',
                        'all': 'Todos'
                    };
                    return labels[pipeline] || 'Todos';
                },

                // Formatear fecha
                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                },

                openUserModal(userId) {
                    this.userModalOpen = true;
                    this.loading = true;
                    this.modalHtml = '';

                    // Obtener el filtro de ayuda activo
                    const ayudaSelect = document.querySelector('select[name="ayuda_id"]');
                    const ayudaId = ayudaSelect ? ayudaSelect.value : '';

                    // Construir la URL con el parámetro de ayuda si existe
                    let url = `/admin/panel-usuario/${userId}/partial`;
                    if (ayudaId) {
                        url += `?ayuda_id=${ayudaId}`;
                    }

                    fetch(url)
                        .then(res => {
                            if (!res.ok) throw new Error('Error al cargar el usuario');
                            return res.text();
                        })
                        .then(html => {
                            setPanelUserIdFromHtml(html);
                            this.modalHtml = html;
                            this.loading = false;
                            this.$nextTick(() => {
                                if (typeof attachNoteFormHandler === 'function') {
                                    attachNoteFormHandler();
                                }
                                setupEditableFields();
                                setupComunicacionForm();
                            });
                        })
                        .catch(() => {
                            this.modalHtml =
                                '<div class="p-8 text-red-500 text-center">Error al cargar el usuario</div>';
                            this.loading = false;
                        });
                },

                closeUserModal() {
                    this.userModalOpen = false;
                    this.modalHtml = '';
                },

                openDeleteModal(userId, userName) {
                    this.deleteUserId = userId;
                    this.deleteUserName = userName;
                    this.deleteModalOpen = true;
                },

                closeDeleteModal() {
                    this.deleteModalOpen = false;
                    this.deleteUserId = null;
                    this.deleteUserName = '';
                },

                confirmDelete() {
                    if (!this.deleteUserId) return;
                    fetch(`/admin/users/${this.deleteUserId}/delete`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                const userCard = document.querySelector(
                                    `[data-user-id="${this.deleteUserId}"]`);
                                if (userCard) {
                                    userCard.remove();
                                }
                                this.closeDeleteModal();
                                this.showNotification('Usuario eliminado correctamente', false);
                            } else {
                                this.showNotification(data.message ||
                                    'Error al eliminar el usuario', true);
                                this.closeDeleteModal();
                            }
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            this.showNotification('Error al eliminar el usuario', true);
                        });
                },

                showNotification(message, isError = false) {
                    const notification = document.createElement('div');
                    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white transition-all duration-300 transform translate-x-full ${
            isError ? 'bg-red-500' : 'bg-green-500'
          }`;
                    notification.textContent = message;

                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.classList.remove('translate-x-full');
                    }, 100);

                    setTimeout(() => {
                        notification.classList.add('translate-x-full');
                        setTimeout(() => {
                            if (notification.parentNode) {
                                notification.parentNode.removeChild(notification);
                            }
                        }, 300);
                    }, 3000);
                },

                // Formatear fecha
                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                },

                // Repetir tutorial
                repetirTutorial() {
                    try {
                        // Llamar a la función global del tutorial personalizado
                        if (typeof window.repetirTutorial === 'function') {
                            window.repetirTutorial();
                        } else {
                            console.error('Función de tutorial no disponible');
                        }
                    } catch (error) {
                        console.error('Error al ejecutar el tutorial:', error);
                    }
                },

                async loadAdmins() {
                    try {
                        const response = await fetch('/admin/crm-tasks/admins');
                        if (response.ok) {
                            const data = await response.json();
                            this.admins = data.admins || [];
                            this.showNotification('Lista de administradores cargada correctamente',
                                false);

                            const loadAdminsBtn = document.getElementById('loadAdminsBtn');
                            if (loadAdminsBtn) {
                                loadAdminsBtn.style.display = 'none';
                            }
                        } else {
                            throw new Error('Error en la respuesta del servidor');
                        }
                    } catch (error) {
                        this.showNotification('Error al cargar la lista de administradores', true);
                    }
                },

                // ================== Funciones de Alertas de Ventas ==================

                // Panel de alertas
                toggleAlertasPanel() {
                    this.alertasPanelOpen = !this.alertasPanelOpen;
                    if (this.alertasPanelOpen) {
                        this.loadAlertas();
                    }
                },

                // Cargar alertas
                async loadAlertas() {
                    try {
                        const response = await fetch('/admin/sale-alerts');
                        if (response.ok) {
                            const data = await response.json();
                            // Normalizar para tener user_name y user_phone en cada item
                            this.alertas = (data.alertas || []).map(a => ({
                                ...a,
                                user_name: a.user?.name || a.user_name || 'Usuario',
                                user_phone: a.user?.telefono || a.user_phone || ''
                            }));
                        }
                    } catch (error) {
                        console.error('Error cargando alertas:', error);
                        this.showNotification('Error al cargar las alertas', true);
                    }
                },

                // Añadir nueva alerta
                async addAlerta() {
                    if (!this.newAlerta.titulo.trim() || !this.newAlerta.user_id || !this.newAlerta
                        .fecha_alerta) {
                        this.showNotification('Por favor, completa todos los campos requeridos',
                            true);
                        return;
                    }

                    try {
                        const response = await fetch('/admin/sale-alerts', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.newAlerta)
                        });

                        if (response.ok) {
                            const data = await response.json();
                            const alerta = data.alerta;
                            alerta.user_name = (alerta.user && alerta.user.name) ? alerta.user
                                .name : 'Usuario';
                            alerta.user_phone = (alerta.user && alerta.user.telefono) ? alerta.user
                                .telefono : '';
                            this.alertas.unshift(alerta);
                            this.newAlerta.titulo = '';
                            this.newAlerta.nota = '';
                            this.newAlerta.fecha_alerta = '';
                            this.newAlerta.user_id = '';

                            // Limpiar campos de búsqueda
                            const searchInput = document.getElementById('alertaUserSearch');
                            if (searchInput) searchInput.value = '';

                            this.showNotification('Alerta creada correctamente', false);
                        } else {
                            const errorData = await response.json();
                            this.showNotification(errorData.message || 'Error al crear la alerta',
                                true);
                        }
                    } catch (error) {
                        console.error('Error creando alerta:', error);
                        this.showNotification('Error al crear la alerta', true);
                    }
                },

                // Cambiar estado de alerta (marcar como completada)
                async toggleAlertaStatus(alerta) {
                    try {
                        const response = await fetch(`/admin/sale-alerts/${alerta.id}/complete`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            }
                        });

                        if (response.ok) {
                            alerta.activa = false;
                            this.showNotification('Alerta marcada como completada', false);
                        } else {
                            this.showNotification('Error al actualizar la alerta', true);
                        }
                    } catch (error) {
                        console.error('Error actualizando alerta:', error);
                        this.showNotification('Error al actualizar la alerta', true);
                    }
                },

                // Eliminar alerta
                async deleteAlerta(alertaId) {
                    if (!confirm('¿Estás seguro de que quieres eliminar esta alerta?')) return;

                    try {
                        const response = await fetch(`/admin/sale-alerts/${alertaId}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            }
                        });

                        if (response.ok) {
                            this.alertas = this.alertas.filter(a => a.id !== alertaId);
                            this.showNotification('Alerta eliminada correctamente', false);
                        } else {
                            this.showNotification('Error al eliminar la alerta', true);
                        }
                    } catch (error) {
                        console.error('Error eliminando alerta:', error);
                        this.showNotification('Error al eliminar la alerta', true);
                    }
                },

                // Verificar si una alerta está vencida
                isVencida(fechaAlerta) {
                    return new Date(fechaAlerta) < new Date();
                },

                // Formatear fecha y hora
                formatDateTime(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                // Obtener tiempo restante hasta la alerta
                getTiempoRestante(fechaAlerta) {
                    const now = new Date();
                    const fecha = new Date(fechaAlerta);

                    if (fecha < now) {
                        return 'Vencida';
                    }

                    const diff = fecha - now;
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

                    if (days > 0) {
                        return `en ${days} día${days > 1 ? 's' : ''}`;
                    } else if (hours > 0) {
                        return `en ${hours} hora${hours > 1 ? 's' : ''}`;
                    } else {
                        return `en ${minutes} minuto${minutes > 1 ? 's' : ''}`;
                    }
                },

                init() {
                    // Verificar si hay un parámetro open_user en la URL
                    const urlParams = new URLSearchParams(window.location.search);
                    const openUserId = urlParams.get('open_user');

                    if (openUserId) {
                        // Abrir automáticamente el modal del usuario
                        setTimeout(() => {
                            this.openUserModal(parseInt(openUserId));
                        }, 500);
                    }
                }

            }
        }
    </script>

    <script>
        function setupUserSearch() {
            const searchInput = document.getElementById('userSearch');
            const searchResults = document.getElementById('searchResults');
            const selectedUserId = document.getElementById('selectedUserId');

            if (!searchInput || !searchResults || !selectedUserId) {
                console.error('No se encontraron los elementos del buscador:', {
                    searchInput: !!searchInput,
                    searchResults: !!searchResults,
                    selectedUserId: !!selectedUserId
                });
                return;
            }

            let searchTimeout;

            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.trim();

                if (query.length < 2) {
                    searchResults.classList.add('hidden');
                    return;
                }

                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchUsers(query);
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        }

        async function searchUsers(query) {
            try {
                const response = await fetch(
                    `/admin/admin-panel/users/search?q=${encodeURIComponent(query)}`);

                if (!response.ok) {
                    throw new Error('Error en la búsqueda');
                }

                const data = await response.json();
                displaySearchResults(data);
            } catch (error) {
                console.error('Error al buscar usuarios:', error);
            }
        }

        function displaySearchResults(data) {
            const searchResults = document.getElementById('searchResults');

            let users = data;
            if (data.users && Array.isArray(data.users)) {
                users = data.users;
            } else if (Array.isArray(data)) {
                users = data;
            } else {
                return;
            }

            if (users.length === 0) {
                searchResults.innerHTML =
                    '<div class="p-3 text-gray-500">No se encontraron usuarios</div>';
                searchResults.classList.remove('hidden');
                return;
            }

            const resultsHtml = users.map(user => `
    <div class="user-result p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0" 
         onclick="selectUser('${user.id}', '${user.name}')">
      <div class="font-medium text-gray-800">${user.name}</div>
      <div class="text-sm text-gray-600">${user.email || 'Sin email'}</div>
      <div class="text-sm text-gray-500">${user.phone || 'Sin teléfono'}</div>
    </div>
  `).join('');

            searchResults.innerHTML = resultsHtml;
            searchResults.classList.remove('hidden');
        }

        function selectUser(userId, userName) {
            const searchInput = document.getElementById('userSearch');
            const selectedUserId = document.getElementById('selectedUserId');
            const searchResults = document.getElementById('searchResults');

            if (searchInput && selectedUserId) {
                searchInput.value = userName;
                selectedUserId.value = userId;

                const alpineComponent = Alpine.$data(document.querySelector('[x-data]'));
                if (alpineComponent && alpineComponent.newTask) {
                    alpineComponent.newTask.user_id = userId;
                }

                searchResults.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                setupUserSearch();
                setupAlertaUserSearch();
            }, 1000);
        });

        // Configuración del buscador de usuarios para alertas
        function setupAlertaUserSearch() {
            const searchInput = document.getElementById('alertaUserSearch');
            const searchResults = document.getElementById('alertaSearchResults');
            const selectedUserId = document.getElementById('selectedAlertaUserId');

            if (!searchInput || !searchResults || !selectedUserId) {
                console.error('No se encontraron los elementos del buscador de alertas:', {
                    searchInput: !!searchInput,
                    searchResults: !!searchResults,
                    selectedUserId: !!selectedUserId
                });
                return;
            }

            let searchTimeout;

            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.trim();

                if (query.length < 2) {
                    searchResults.classList.add('hidden');
                    return;
                }

                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchUsersForAlerta(query);
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        }

        async function searchUsersForAlerta(query) {
            try {
                const response = await fetch(
                    `/admin/admin-panel/users/search?q=${encodeURIComponent(query)}`);

                if (!response.ok) {
                    throw new Error('Error en la búsqueda');
                }

                const data = await response.json();
                displayAlertaSearchResults(data);
            } catch (error) {
                console.error('Error al buscar usuarios para alerta:', error);
            }
        }

        function displayAlertaSearchResults(data) {
            const searchResults = document.getElementById('alertaSearchResults');

            let users = data;
            if (data.users && Array.isArray(data.users)) {
                users = data.users;
            } else if (Array.isArray(data)) {
                users = data;
            } else {
                return;
            }

            if (users.length === 0) {
                searchResults.innerHTML =
                    '<div class="p-3 text-gray-500">No se encontraron usuarios</div>';
                searchResults.classList.remove('hidden');
                return;
            }

            const resultsHtml = users.map(user => `
    <div class="user-result p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0" 
         onclick="selectAlertaUser('${user.id}', '${user.name}')">
      <div class="font-medium text-gray-800">${user.name}</div>
      <div class="text-sm text-gray-600">${user.email || 'Sin email'}</div>
      <div class="text-sm text-gray-500">${user.phone || 'Sin teléfono'}</div>
    </div>
  `).join('');

            searchResults.innerHTML = resultsHtml;
            searchResults.classList.remove('hidden');
        }

        function selectAlertaUser(userId, userName) {
            const searchInput = document.getElementById('alertaUserSearch');
            const selectedUserId = document.getElementById('selectedAlertaUserId');
            const searchResults = document.getElementById('alertaSearchResults');

            if (searchInput && selectedUserId) {
                searchInput.value = userName;
                selectedUserId.value = userId;

                const alpineComponent = Alpine.$data(document.querySelector('[x-data]'));
                if (alpineComponent && alpineComponent.newAlerta) {
                    alpineComponent.newAlerta.user_id = userId;
                }

                searchResults.classList.add('hidden');
            }
        }
    </script>

    <script>
        // Esta función se usará para añadir el event listener al formulario de notas tras cargar el panel por AJAX
        function attachNoteFormHandler() {
            const form = document.getElementById('addNoteForm');
            const textarea = document.getElementById('notaInput');
            const statusDiv = document.getElementById('statusMessage');
            if (!form) return;

            form.onsubmit = async function(e) {
                e.preventDefault();
                const nota = textarea.value.trim();
                if (!nota) {
                    statusDiv.textContent = 'La nota no puede estar vacía.';
                    statusDiv.classList.remove('hidden', 'bg-green-500', 'bg-red-500');
                    statusDiv.classList.add('bg-red-500');
                    setTimeout(() => statusDiv.classList.add('hidden'), 3000);
                    return;
                }

                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            nota
                        })
                    });
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({
                            message: res.statusText
                        }));
                        throw new Error(err.message || 'Error al guardar la nota');
                    }
                    const json = await res.json();

                    // Prepend nueva nota
                    const li = document.createElement('li');
                    li.className = 'border rounded-lg p-3 bg-gray-50';
                    li.innerHTML = `
        <div class="flex justify-between text-sm text-gray-600">
          <span><strong>${json.user || 'Tú'}</strong></span>
          <span>${new Date(json.created_at).toLocaleString('es-ES', {
            day:'2-digit', month:'2-digit', year:'numeric',
            hour:'2-digit', minute:'2-digit'
          })}</span>
        </div>
        <p class="mt-1 text-gray-800">${json.nota}</p>
      `;
                    document.getElementById('notesList').prepend(li);
                    textarea.value = '';
                    statusDiv.textContent = 'Nota añadida correctamente';
                    statusDiv.classList.remove('hidden', 'bg-green-500', 'bg-red-500');
                    statusDiv.classList.add('bg-green-500');
                    setTimeout(() => statusDiv.classList.add('hidden'), 3000);
                } catch (err) {
                    statusDiv.textContent = err.message;
                    statusDiv.classList.remove('hidden', 'bg-green-500', 'bg-red-500');
                    statusDiv.classList.add('bg-red-500');
                    setTimeout(() => statusDiv.classList.add('hidden'), 3000);
                }
            };
        }
    </script>

    <script>
        // Función global para mostrar mensajes de estado
        function showStatus(message, isError = false) {
            let statusDiv = document.getElementById('statusMessage');
            if (!statusDiv) {
                statusDiv = document.createElement('div');
                statusDiv.id = 'statusMessage';
                statusDiv.className = 'hidden mx-6 my-4 p-4 rounded-lg text-white';
                // Insertar al principio del panel si existe, si no al body
                const panel = document.getElementById('userPanelContent') || document.body;
                panel.prepend(statusDiv);
            }
            statusDiv.textContent = message;
            statusDiv.classList.remove('hidden', 'bg-green-500', 'bg-red-500');
            statusDiv.classList.add(isError ? 'bg-red-500' : 'bg-green-500');
            setTimeout(() => statusDiv.classList.add('hidden'), 3000);
        }

        // Función global para registrar la comunicación operativa
        function registrarComunicacionOperativa(tipo) {
            if (!window.panelUserId) {
                showStatus('No se pudo determinar el usuario', true);
                return;
            }
            fetch(`/admin/users/${window.panelUserId}/comunicacion-operativa`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        tipo_comunicacion: tipo
                    })
                })
                .then(res => res.json())
                .then(json => {
                    if (json.success) {
                        showStatus('Comunicación registrada correctamente', false);
                    } else {
                        showStatus('No se pudo registrar la comunicación', true);
                    }
                })
                .catch(err => {
                    showStatus('Error al registrar comunicación', true);
                });
        }

        // Hook para asignar el userId global al cargar el panel por AJAX
        function setPanelUserIdFromHtml(html) {
            // Busca el atributo data-user-id en el HTML parcial
            const match = html.match(/data-user-id=["'](\d+)["']/);
            if (match) {
                window.panelUserId = match[1];
            } else {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const containerWithId = tempDiv.querySelector('[data-user-id]');

                if (containerWithId) {
                    window.panelUserId = containerWithId.getAttribute('data-user-id');
                    console.log('panelUserId encontrado alternativamente:', window.panelUserId);
                } else {
                    window.panelUserId = null;
                    console.error('No se pudo encontrar data-user-id en el HTML');
                }
            }
        }
    </script>

    <script>
        function setupEditableFields() {
            const fields = document.querySelectorAll('.editable-field');

            fields.forEach(field => {
                field.removeEventListener('click', field._editHandler);

                field._editHandler = function() {
                    if (field.classList.contains('editing')) return;

                    const originalValue = field.getAttribute('data-original-value') || '';
                    const currentText = field.textContent.trim();
                    const questionType = field.getAttribute('data-question-type') || 'text';
                    const questionOptions = field.getAttribute('data-question-options');
                    const questionSlug = field.getAttribute('data-question-slug');

                    let inputElement;

                    if (questionSlug === 'comunidad_autonoma' || questionSlug ===
                        'provincia' || questionSlug === 'municipio') {
                        inputElement = document.createElement('select');
                        inputElement.style.cssText =
                            'border: 2px solid #f59e0b; padding: 4px; border-radius: 4px; min-width: 150px;';
                        loadDynamicOptions(questionSlug, inputElement, originalValue);
                    } else if (questionType === 'select' && questionOptions) {
                        inputElement = document.createElement('select');
                        inputElement.style.cssText =
                            'border: 2px solid #f59e0b; padding: 4px; border-radius: 4px; min-width: 150px;';

                        try {
                            const options = JSON.parse(questionOptions);
                            Object.entries(options).forEach(([key, value]) => {
                                const option = document.createElement('option');
                                option.value = key;
                                option.textContent = value;
                                if (key === originalValue) {
                                    option.selected = true;
                                }
                                inputElement.appendChild(option);
                            });
                        } catch (e) {
                            console.error('Error parsing options:', e);
                            inputElement = createTextInput(originalValue);
                        }
                    } else if (questionType === 'boolean') {
                        inputElement = document.createElement('select');
                        inputElement.style.cssText =
                            'border: 2px solid #f59e0b; padding: 4px; border-radius: 4px; min-width: 150px;';

                        const optionSi = document.createElement('option');
                        optionSi.value = '1';
                        optionSi.textContent = 'Sí';
                        if (originalValue === '1' || originalValue === 'true') {
                            optionSi.selected = true;
                        }

                        const optionNo = document.createElement('option');
                        optionNo.value = '0';
                        optionNo.textContent = 'No';
                        if (originalValue === '0' || originalValue === 'false' ||
                            originalValue === '') {
                            optionNo.selected = true;
                        }

                        inputElement.appendChild(optionSi);
                        inputElement.appendChild(optionNo);
                    } else if (questionType === 'date') {
                        inputElement = document.createElement('input');
                        inputElement.type = 'date';
                        inputElement.value = originalValue;
                        inputElement.style.cssText =
                            'border: 2px solid #f59e0b; padding: 4px; border-radius: 4px; min-width: 150px;';
                    } else {
                        inputElement = createTextInput(originalValue);
                    }

                    function createTextInput(value) {
                        const input = document.createElement('input');
                        input.type = 'text';
                        input.value = value;
                        input.style.cssText =
                            'border: 2px solid #f59e0b; padding: 4px; border-radius: 4px; min-width: 150px;';
                        return input;
                    }

                    const saveBtn = document.createElement('button');
                    saveBtn.textContent = '✓';
                    saveBtn.style.cssText =
                        'background: #10b981; color: white; border: none; border-radius: 4px; padding: 2px 8px; margin-left: 4px; cursor: pointer;';

                    const cancelBtn = document.createElement('button');
                    cancelBtn.textContent = '✕';
                    cancelBtn.style.cssText =
                        'background: #ef4444; color: white; border: none; border-radius: 4px; padding: 2px 8px; margin-left: 4px; cursor: pointer;';

                    const deleteBtn = document.createElement('button');
                    deleteBtn.textContent = '🗑️';
                    deleteBtn.style.cssText =
                        'background: #dc2626; color: white; border: none; border-radius: 4px; padding: 2px 8px; margin-left: 4px; cursor: pointer;';
                    deleteBtn.title = 'Eliminar respuesta';

                    field.innerHTML = '';
                    field.appendChild(inputElement);
                    field.appendChild(saveBtn);
                    field.appendChild(cancelBtn);
                    field.appendChild(deleteBtn);
                    field.classList.add('editing');

                    if (questionType === 'boolean') {
                        inputElement.focus();
                    } else if (questionType === 'select') {
                        inputElement.focus();
                    } else {
                        inputElement.focus();
                        inputElement.select();
                    }

                    function save() {
                        let newValue;

                        if (questionType === 'boolean') {
                            newValue = inputElement.value;
                        } else if (questionType === 'select') {
                            newValue = inputElement.value;
                        } else {
                            newValue = inputElement.value.trim();
                        }

                        if (questionSlug === 'comunidad_autonoma' || questionSlug ===
                            'provincia' || questionSlug === 'municipio') {
                            newValue = inputElement.value;
                        }

                        const answerId = field.getAttribute('data-answer-id');

                        let updateUrl;
                        if (answerId && answerId !== '') {
                            updateUrl =
                                `/admin/users/${window.panelUserId}/answers/${answerId}`;
                        } else if (questionSlug) {
                            updateUrl = `/admin/users/${window.panelUserId}/answers/new`;
                        } else {
                            console.error('No se puede determinar el campo a actualizar');
                            cancel();
                            return;
                        }

                        fetch(updateUrl, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    answer: newValue,
                                    question_slug: questionSlug
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    field.innerHTML = data.formatted_answer;
                                    if (questionSlug === 'comunidad_autonoma' ||
                                        questionSlug === 'provincia' || questionSlug ===
                                        'municipio') {
                                        field.setAttribute('data-original-value',
                                            inputElement.value);
                                    } else {
                                        field.setAttribute('data-original-value',
                                            newValue);
                                    }
                                    if (data.answer_id) {
                                        field.setAttribute('data-answer-id', data
                                            .answer_id);
                                    }

                                    if (questionSlug === 'comunidad_autonoma' ||
                                        questionSlug === 'provincia') {
                                        reloadDependentOptions(field);
                                    }
                                } else {
                                    console.error('Error al guardar:', data.error);
                                    cancel();
                                }
                            })
                            .catch(err => {
                                console.error('Error de conexión:', err);
                                cancel();
                            });

                        field.classList.remove('editing');
                    }

                    function cancel() {
                        field.innerHTML = currentText;
                        field.classList.remove('editing');
                    }

                    function deleteAnswer() {
                        const answerId = field.getAttribute('data-answer-id');

                        if (!answerId || answerId === '') {
                            console.error(
                                'No se puede eliminar una respuesta que no existe');
                            cancel();
                            return;
                        }

                        if (!confirm(
                                '¿Estás seguro de que quieres eliminar esta respuesta?')) {
                            return;
                        }

                        fetch(`/admin/users/${window.panelUserId}/answers/${answerId}`, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    const listItem = field.closest('li');
                                    if (listItem) {
                                        listItem.style.display = 'none';
                                    } else {
                                        const container = field.parentElement;
                                        if (container && container.querySelector(
                                                'strong')) {
                                            container.style.display = 'none';
                                        } else {
                                            field.style.display = 'none';
                                        }
                                    }
                                    field.setAttribute('data-original-value', '');
                                    field.setAttribute('data-answer-id', '');
                                    field.classList.remove('editing');
                                } else {
                                    console.error('Error al eliminar:', data.error);
                                    cancel();
                                }
                            })
                            .catch(err => {
                                console.error('Error de conexión:', err);
                                cancel();
                            });
                    }

                    saveBtn.onclick = null;
                    cancelBtn.onclick = null;
                    deleteBtn.onclick = null;
                    inputElement.onkeydown = null;
                    inputElement.onblur = null;

                    saveBtn.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        save();
                    };
                    cancelBtn.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        cancel();
                    };
                    deleteBtn.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        deleteAnswer();
                    };

                    inputElement.onkeydown = function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            save();
                        }
                        if (e.key === 'Escape') {
                            e.preventDefault();
                            cancel();
                        }
                    };

                    if (questionType === 'text' || questionType === 'date') {
                        inputElement.onblur = function() {
                            setTimeout(save, 150);
                        };
                    }
                };

                field.addEventListener('click', field._editHandler);
            });
        }

        window.toggleSolicitud = function(id) {
            event.stopPropagation();
            const detail = document.getElementById(id + '-row');
            const chevron = document.getElementById('chevron' + id.replace(/\D/g, ''));
            detail.classList.toggle('hidden');
            chevron?.classList.toggle('rotate-90');
            document.querySelectorAll('.ayuda-row').forEach(r => {
                if (r.id !== id) {
                    document.getElementById(r.id + '-row')?.classList.add('hidden');
                    document.getElementById('chevron' + r.id.replace(/\D/g, ''))?.classList
                        .remove('rotate-90');
                }
            });
        };

        function loadDynamicOptions(type, selectElement, selectedValue) {
            const params = new URLSearchParams({
                type: type
            });

            if (type === 'provincia') {
                const ccaaValue = document.querySelector('[data-question-slug="comunidad_autonoma"]')
                    ?.getAttribute('data-original-value');
                if (ccaaValue && ccaaValue !== '') params.append('parent_id', ccaaValue);
            } else if (type === 'municipio') {
                const provinciaValue = document.querySelector('[data-question-slug="provincia"]')
                    ?.getAttribute('data-original-value');
                if (provinciaValue && provinciaValue !== '') params.append('parent_id', provinciaValue);
            }

            selectElement.innerHTML = '';

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Seleccionar...';
            selectElement.appendChild(defaultOption);

            fetch(`/admin/options?${params}`)
                .then(res => {
                    if (!res.ok) throw new Error('Error en la respuesta del servidor');
                    return res.json();
                })
                .then(data => {
                    if (data.options && Object.keys(data.options).length > 0) {
                        Object.entries(data.options).forEach(([key, value]) => {
                            const option = document.createElement('option');
                            option.value = key;
                            option.textContent = value;
                            if (key === selectedValue) {
                                option.selected = true;
                            }
                            selectElement.appendChild(option);
                        });
                    } else {
                        const noOptionsOption = document.createElement('option');
                        noOptionsOption.value = '';
                        noOptionsOption.textContent = 'No hay opciones disponibles';
                        noOptionsOption.disabled = true;
                        selectElement.appendChild(noOptionsOption);
                    }
                })
                .catch(err => {
                    console.error('Error cargando opciones:', err);
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.value = selectedValue;
                    input.style.cssText =
                        'border: 2px solid #f59e0b; padding: 4px; border-radius: 4px; min-width: 150px;';
                    selectElement.parentNode.replaceChild(input, selectElement);
                });
        }

        function reloadDependentOptions(changedField) {
            const questionSlug = changedField.getAttribute('data-question-slug');

            if (questionSlug === 'comunidad_autonoma') {
                const provinciaField = document.querySelector('[data-question-slug="provincia"]');
                if (provinciaField) {
                    provinciaField.innerHTML = 'No registrado';
                    provinciaField.setAttribute('data-original-value', '');
                    provinciaField.setAttribute('data-answer-id', '');
                }

                const municipioField = document.querySelector('[data-question-slug="municipio"]');
                if (municipioField) {
                    municipioField.innerHTML = 'No registrado';
                    municipioField.setAttribute('data-original-value', '');
                    municipioField.setAttribute('data-answer-id', '');
                }
            } else if (questionSlug === 'provincia') {
                const municipioField = document.querySelector('[data-question-slug="municipio"]');
                if (municipioField) {
                    municipioField.innerHTML = 'No registrado';
                    municipioField.setAttribute('data-original-value', '');
                    municipioField.setAttribute('data-answer-id', '');
                }
            }
        }

        window.filterAyudas = function() {
            const txt = document.getElementById('searchAyudasInput')?.value.toLowerCase();
            if (!txt) return;
            document.querySelectorAll('.ayuda-row').forEach(row => {
                row.style.display = (!txt || row.textContent.toLowerCase().includes(txt)) ?
                    '' : 'none';
            });
        };

        window.deleteSolicitud = function(solicitudId, nombreSolicitud) {
            if (!confirm(
                    `¿Estás seguro de que quieres eliminar la solicitud "${nombreSolicitud}"? Esta acción no se puede deshacer.`
                )) {
                return;
            }

            fetch(`/admin/users/${window.panelUserId}/solicitudes/${solicitudId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .content
                    }
                })
                .then(res => res.json())
                .then(json => {
                    if (json.success) {
                        const row = document.getElementById(`solicitud${solicitudId}`);
                        const detailRow = document.getElementById(
                            `solicitud${solicitudId}-row`);
                        if (row) row.remove();
                        if (detailRow) detailRow.remove();

                        showStatus('Solicitud eliminada correctamente', false);
                    } else {
                        showStatus(json.message || 'Error al eliminar la solicitud', true);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showStatus('Error al eliminar la solicitud', true);
                });
        };
    </script>

    <script>
        // ================== Funciones de Comunicaciones Operativas ==================

        // Función para mostrar/ocultar el formulario
        function toggleComunicacionForm() {
            console.log('toggleComunicacionForm ejecutándose...');
            const form = document.getElementById('comunicacionForm');
            if (form) {
                form.classList.toggle('hidden');
                console.log('Formulario toggleado, hidden:', form.classList.contains('hidden'));
            } else {
                console.log('Formulario no encontrado');
            }
        }

        // Función para mostrar mensajes de estado
        function showComunicacionStatus(message, isError = false) {
            const statusDiv = document.getElementById('comunicacionStatusMessage');
            if (statusDiv) {
                statusDiv.textContent = message;
                statusDiv.classList.remove('hidden', 'bg-green-500', 'bg-red-500');
                statusDiv.classList.add(isError ? 'bg-red-500' : 'bg-green-500');
                setTimeout(() => statusDiv.classList.add('hidden'), 3000);
            }
        }

        // Función para crear una nueva comunicación
        async function createComunicacion(formData) {
            if (!window.panelUserId) {
                console.error('panelUserId no está definido:', window.panelUserId);
                showComunicacionStatus('Error: No se pudo determinar el usuario', true);
                return;
            }

            console.log('Creando comunicación para usuario:', window.panelUserId);

            try {
                const res = await fetch(`/admin/users/${window.panelUserId}/comunicaciones`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .content
                    },
                    body: JSON.stringify(formData)
                });

                if (!res.ok) {
                    const err = await res.json().catch(() => ({
                        message: res.statusText
                    }));
                    throw new Error(err.message || 'Error al guardar la comunicación');
                }

                const json = await res.json();

                if (json.success) {
                    showComunicacionStatus('Comunicación guardada correctamente', false);

                    const newCard = createComunicacionCard(json.comunicacion);
                    const comunicacionesList = document.getElementById('comunicacionesList');

                    const emptyState = comunicacionesList.querySelector('.empty-comms');
                    if (emptyState) {
                        emptyState.remove();
                    }

                    comunicacionesList.insertBefore(newCard, comunicacionesList.firstChild);

                    document.getElementById('addComunicacionForm').reset();
                    toggleComunicacionForm();

                } else {
                    showComunicacionStatus(json.message || 'Error al guardar la comunicación',
                        true);
                }
            } catch (err) {
                console.error('Error:', err);
                showComunicacionStatus(err.message, true);
            }
        }

        // Función para crear una tarjeta de comunicación HTML
        function createComunicacionCard(comunicacion) {
            const card = document.createElement('div');
            card.className = 'border rounded-lg p-3 bg-gray-50';
            card.setAttribute('data-comunicacion-id', comunicacion.id);

            const iconClass = getComunicacionIcon(comunicacion.tipo_comunicacion);
            const directionClass = comunicacion.direction === 'in' ? 'bg-green-100 text-green-800' :
                'bg-blue-100 text-blue-800';
            const directionText = comunicacion.direction === 'in' ? 'Entrante' : 'Saliente';

            card.innerHTML = `
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <i class="${iconClass}"></i>
                        <span class="font-medium text-gray-900">${comunicacion.tipo_comunicacion}</span>
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full ${directionClass}">
                            ${directionText}
                        </span>
                        ${comunicacion.auto ? '<span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full"><i class="fas fa-robot mr-1"></i>Automática</span>' : ''}
                    </div>
                    ${comunicacion.subject ? `<p class="text-sm text-gray-700 mb-1">${comunicacion.subject}</p>` : ''}
                    <div class="text-xs text-gray-500">
                        <span class="font-medium">Tramitador:</span> ${comunicacion.tramitador_email || 'N/A'}
                        <span class="mx-2">•</span>
                        <span class="font-medium">Fecha:</span> ${new Date(comunicacion.fecha_hora).toLocaleString('es-ES', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}
                    </div>
                </div>
                <button 
                    onclick="deleteComunicacion(${comunicacion.id})"
                    class="text-red-600 hover:text-red-800 transition-colors"
                    title="Eliminar comunicación">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

            return card;
        }

        function getComunicacionIcon(tipo) {
            switch (tipo) {
                case 'WhatsApp':
                    return 'fab fa-whatsapp text-green-500 text-lg';
                case 'Email':
                    return 'fas fa-envelope text-blue-500 text-lg';
                case 'Llamada':
                    return 'fas fa-phone text-purple-500 text-lg';
                default:
                    return 'fas fa-comment text-gray-500 text-lg';
            }
        }

        // Función para eliminar una comunicación
        async function deleteComunicacion(comunicacionId) {
            if (!window.panelUserId) {
                console.error('panelUserId no está definido:', window.panelUserId);
                showComunicacionStatus('Error: No se pudo determinar el usuario', true);
                return;
            }

            if (!confirm(
                    '¿Estás seguro de que quieres eliminar esta comunicación? Esta acción no se puede deshacer.'
                )) {
                return;
            }

            console.log('Eliminando comunicación para usuario:', window.panelUserId);

            try {
                const res = await fetch(
                    `/admin/users/${window.panelUserId}/comunicaciones/${comunicacionId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content
                        }
                    });

                const json = await res.json();

                if (json.success) {
                    showComunicacionStatus('Comunicación eliminada correctamente', false);

                    const card = document.querySelector(
                        `[data-comunicacion-id="${comunicacionId}"]`);
                    if (card) {
                        card.remove();

                        const comunicacionesList = document.getElementById('comunicacionesList');
                        const remainingCards = comunicacionesList.querySelectorAll(
                            '[data-comunicacion-id]');

                        if (remainingCards.length === 0) {
                            const emptyState = document.createElement('div');
                            emptyState.className = 'text-center py-8 text-gray-500 empty-comms';
                            emptyState.innerHTML = `
                            <i class="fas fa-phone-alt text-4xl text-gray-300 mb-4"></i>
                            <p>No hay comunicaciones operativas registradas.</p>
                            <p class="text-sm text-gray-400 mt-2">Haz clic en "Nueva Comunicación" para añadir una.</p>
                        `;
                            comunicacionesList.appendChild(emptyState);
                        }
                    }
                } else {
                    showComunicacionStatus(json.message || 'Error al eliminar la comunicación',
                        true);
                }
            } catch (err) {
                console.error('Error:', err);
                showComunicacionStatus('Error al eliminar la comunicación', true);
            }
        }

        // Función para registrar comunicaciones automáticas (WhatsApp y llamada)
        async function registrarComunicacionAutomatica(tipo) {
            if (!window.panelUserId) {
                console.error('panelUserId no está definido:', window.panelUserId);
                showComunicacionStatus('Error: No se pudo determinar el usuario', true);
                return;
            }

            console.log('Registrando comunicación automática para usuario:', window.panelUserId);

            try {
                const res = await fetch(
                    `/admin/users/${window.panelUserId}/comunicacion_operativa`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content
                        },
                        body: JSON.stringify({
                            tipo_comunicacion: tipo
                        })
                    });

                const json = await res.json();

                if (json.success) {
                    const comunicacionData = {
                        id: json.comunicacion.id,
                        tipo_comunicacion: tipo,
                        fecha_hora: new Date().toISOString(),
                        direction: 'out',
                        subject: null,
                        auto: true,
                        tramitador_email: json.comunicacion.tramitador_email || 'N/A'
                    };

                    const newCard = createComunicacionCard(comunicacionData);
                    const comunicacionesList = document.getElementById('comunicacionesList');

                    const emptyState = comunicacionesList.querySelector('.empty-comms');
                    if (emptyState) {
                        emptyState.remove();
                    }

                    comunicacionesList.insertBefore(newCard, comunicacionesList.firstChild);

                    showComunicacionStatus('Comunicación automática registrada correctamente',
                        false);
                } else {
                    showComunicacionStatus(json.message ||
                        'Error al registrar la comunicación automática', true);
                }
            } catch (err) {
                console.error('Error:', err);
                showComunicacionStatus('Error al registrar comunicación', true);
            }
        }

        // Función para configurar el formulario de comunicaciones después de cargar el panel
        function setupComunicacionForm() {
            const form = document.getElementById('addComunicacionForm');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const data = {
                        tipo_comunicacion: formData.get('tipo_comunicacion'),
                        fecha_hora: formData.get('fecha_hora'),
                        direction: formData.get('direction'),
                        subject: formData.get('subject')
                    };

                    if (!data.tipo_comunicacion || !data.fecha_hora || !data.direction) {
                        showComunicacionStatus(
                            'Por favor, completa todos los campos requeridos', true);
                        return;
                    }

                    await createComunicacion(data);
                });
            }
        }

        // Tutorial personalizado sin dependencias externas
        let currentTutorialStep = 0;
        const tutorialSteps = [{
                element: '#btn-tareas-crm',
                title: '🎯 ¡Recuerda revisar tus tareas!',
                description: 'Este botón te permite acceder a tu panel de tareas CRM. Haz clic para gestionar tus tareas pendientes, completadas y crear nuevas.',
                position: 'bottom'
            },
            {
                element: '#btn-repetir-tutorial',
                title: '¿Necesitas ayuda?',
                description: 'Si en algún momento necesitas recordar cómo funciona algo, puedes hacer clic en este botón para repetir el tutorial.',
                position: 'left'
            }
        ];

        function showTutorial() {
            const overlay = document.getElementById('tutorial-overlay');
            const highlight = document.getElementById('tutorial-highlight');
            const popup = document.getElementById('tutorial-popup');

            if (!overlay || !highlight || !popup) return;

            // Forzar transparencia del highlight
            highlight.style.background = 'none';
            highlight.style.backgroundColor = 'none';
            highlight.style.backgroundImage = 'none';
            highlight.style.outline = '3px solid #54debd';
            highlight.style.outlineOffset = '2px';

            overlay.style.display = 'block';
            currentTutorialStep = 0;
            showTutorialStep(0);
        }

        function showTutorialStep(stepIndex) {
            if (stepIndex >= tutorialSteps.length) {
                hideTutorial();
                return;
            }

            const step = tutorialSteps[stepIndex];
            const element = document.querySelector(step.element);

            if (!element) {
                hideTutorial();
                return;
            }

            // Actualizar contenido del popup
            document.getElementById('tutorial-title').textContent = step.title;
            document.getElementById('tutorial-description').textContent = step.description;
            document.getElementById('tutorial-progress').textContent =
                `Paso ${stepIndex + 1} de ${tutorialSteps.length}`;

            // Posicionar highlight sobre el elemento
            const rect = element.getBoundingClientRect();
            const highlight = document.getElementById('tutorial-highlight');
            const popup = document.getElementById('tutorial-popup');

            // Calcular posición del highlight
            const size = Math.max(rect.width, rect.height) + 20;
            highlight.style.width = size + 'px';
            highlight.style.height = size + 'px';
            highlight.style.left = (rect.left + rect.width / 2 - size / 2) + 'px';
            highlight.style.top = (rect.top + rect.height / 2 - size / 2) + 'px';

            // Posicionar popup según la posición especificada
            let popupLeft, popupTop;

            switch (step.position) {
                case 'bottom':
                    popupLeft = rect.left + rect.width / 2 - 150;
                    popupTop = rect.bottom + 20;
                    break;
                case 'left':
                    popupLeft = rect.left - 320;
                    popupTop = rect.top + rect.height / 2 - 60;
                    break;
                default:
                    popupLeft = rect.left + rect.width / 2 - 150;
                    popupTop = rect.bottom + 20;
            }

            // Asegurar que el popup esté dentro de la ventana
            popupLeft = Math.max(20, Math.min(popupLeft, window.innerWidth - 320));
            popupTop = Math.max(20, Math.min(popupTop, window.innerHeight - 120));

            popup.style.left = popupLeft + 'px';
            popup.style.top = popupTop + 'px';

            // Actualizar botón
            const nextButton = document.getElementById('tutorial-next');
            const closeButton = document.getElementById('tutorial-close');

            if (stepIndex === tutorialSteps.length - 1) {
                nextButton.textContent = '¡Entendido!';
                nextButton.className = 'bg-green-500 hover:bg-green-600';
            } else {
                nextButton.textContent = 'Siguiente';
                nextButton.className = 'bg-[#54debd] hover:bg-[#43c5a9]';
            }
        }

        function nextTutorialStep() {
            currentTutorialStep++;
            if (currentTutorialStep < tutorialSteps.length) {
                showTutorialStep(currentTutorialStep);
            } else {
                hideTutorial();
            }
        }

        function hideTutorial() {
            const overlay = document.getElementById('tutorial-overlay');
            if (overlay) {
                overlay.style.display = 'none';

                const tutorialKey = 'tutorial_last_shown';
                const now = new Date();
                localStorage.setItem(tutorialKey, now.toISOString());
            }
        }

        // Tutorial automático al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const shouldShowTutorial = checkTutorialDisplay();

            if (shouldShowTutorial && !hasActiveFilters()) {
                // Pequeño delay para asegurar que Alpine.js esté listo
                setTimeout(() => {
                    showTutorial();
                }, 2000);
            } else {
                const btnTareas = document.getElementById('btn-tareas-crm');
                if (btnTareas) {
                    btnTareas.title =
                        'Gestionar Tareas CRM (Haz clic en el botón de ayuda para ver el tutorial)';
                }

                if (!shouldShowTutorial) {
                    setTimeout(() => {
                        const notification = document.createElement('div');
                        notification.className =
                            'fixed top-4 right-4 z-50 px-4 py-2 bg-blue-500 text-white rounded-lg shadow-lg text-sm tutorial-notification';
                        notification.innerHTML =
                            '💡 <strong>Tip:</strong> Haz clic en el botón de ayuda (?) para ver el tutorial de tareas';
                        document.body.appendChild(notification);

                        setTimeout(() => {
                            notification.style.opacity = '0';
                            notification.style.transform = 'translateX(100%)';
                            setTimeout(() => {
                                if (notification.parentNode) {
                                    notification.parentNode.removeChild(
                                        notification);
                                }
                            }, 300);
                        }, 5000);
                    }, 3000);
                }
            }

            // Añadir funcionalidad de teclado
            document.addEventListener('keydown', function(e) {
                if (document.getElementById('tutorial-overlay').style.display ===
                    'block') {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        nextTutorialStep();
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        hideTutorial();
                    }
                }
            });
        });

        function checkTutorialDisplay() {
            const tutorialKey = 'tutorial_last_shown';
            const now = new Date();
            const lastShown = localStorage.getItem(tutorialKey);

            if (!lastShown) {
                // Primera vez que visita la página
                localStorage.setItem(tutorialKey, now.toISOString());
                return true;
            }

            const lastShownDate = new Date(lastShown);
            const timeDiff = now.getTime() - lastShownDate.getTime();
            const daysDiff = timeDiff / (1000 * 3600 * 24);

            // Mostrar tutorial si han pasado más de 1 día (24 horas)
            if (daysDiff >= 1) {
                localStorage.setItem(tutorialKey, now.toISOString());
                return true;
            }

            return false;
        }

        // Función para repetir tutorial (usada por Alpine.js)
        window.repetirTutorial = function() {
            const tutorialKey = 'tutorial_last_shown';
            localStorage.removeItem(tutorialKey);
            showTutorial();
        };

        // Función para mostrar tutorial manualmente (útil cuando hay filtros)
        window.mostrarTutorialManual = function() {
            showTutorial();
        };

        // Función para verificar si hay filtros activos
        function hasActiveFilters() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.has('sector') || urlParams.has('ccaa') || urlParams.has('ayuda_id') ||
                urlParams.has('search') || urlParams.has('estado_1') || urlParams.has('estado_2') ||
                urlParams.has('fase_1') || urlParams.has('fase_2') || urlParams.has('estado_comercial');
        }
    </script>

    <script>
        // Función para cambiar entre estilos de highlight si es necesario
        function cambiarEstiloHighlight() {
            const highlight = document.getElementById('tutorial-highlight');
            if (highlight) {
                // Cambiar a estilo con pseudo-elementos
                highlight.className = 'tutorial-highlight-pseudo';
                console.log('Cambiado a estilo con pseudo-elementos');
            }
        }

        // Función para repetir tutorial (usada por Alpine.js)
        window.repetirTutorial = function() {
            showTutorial();
        };
    </script>
</body>

</html>
