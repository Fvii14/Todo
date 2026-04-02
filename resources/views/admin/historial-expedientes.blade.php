<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tramitaciones</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Alpine.js --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100">
    @include('layouts.headerbackoffice')

    <div x-data="management()" x-init="init()"
        x-on:open-detail.window="openDetalle($event.detail?.id ?? $event.detail)"
        x-on:refresh-detalle.window="refreshDetalle()"
        x-on:mostrar-proximas-tareas.window="mostrarProximasTareas($event.detail.proximasTareas)"
        class="flex w-full max-w-full px-4 md:px-8 lg:px-16 py-6">

        <div class="w-full pr-0 transition-all duration-300">

            <h1 class="text-3xl font-bold text-gray-800 border-b pb-2 mb-6">Historial de Clientes
            </h1>

            <!-- Overlay de carga global mientras se obtiene show.json -->
            <div x-show="loading" x-transition.opacity
                class="fixed inset-0 z-[9998] bg-white/70 backdrop-blur-sm flex items-center justify-center"
                style="display: none;">
                <div
                    class="flex items-center px-4 py-3 bg-white rounded-xl shadow-lg border border-gray-100">
                    <svg class="animate-spin h-6 w-6 text-[#54debd]" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span class="ml-3 text-sm text-gray-600">Cargando…</span>
                </div>
            </div>

            <div class="w-full px-4 py-6 space-y-6">
                {{-- 0. Filtros por estado (solo OPx) --}}
                <div x-data="metricasEstado()" class="space-y-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-700">Estados OPx</h3>
                            <button @click="limpiarFiltros()"
                                class="px-3 py-1 text-sm bg-gray-100 text-gray-600 rounded-md hover:bg-gray-200 transition">
                                <i class="bx bx-refresh mr-1"></i>
                                Limpiar filtros
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            @foreach ($estadosOPxAgrupados as $grupo => $items)
                                @foreach ($items as $estadoOpx)
                                    <button
                                        @click="seleccionarEstadoOPx('{{ $estadoOpx->codigo }}')"
                                        class="flex items-center space-x-2 p-3 rounded-lg border transition
                                        {{ request('estado_opx') === $estadoOpx->codigo
                                            ? 'bg-[#54debd]/20 border-[#54debd]'
                                            : 'bg-white border-gray-200 hover:shadow' }}"
                                        :class="estadoOpxSeleccionado === '{{ $estadoOpx->codigo }}'
                                            ?
                                            'bg-[#54debd]/20 border-[#54debd]' :
                                            'bg-white border-gray-200 hover:shadow'">
                                        <div class="text-sm text-left">
                                            <div class="font-medium">{{ $estadoOpx->codigo }}</div>
                                            <div class="text-xs text-gray-500">{{ $grupo }}
                                            </div>
                                            <div class="font-bold text-lg">
                                                {{ $estadosOPxCounts[$estadoOpx->codigo] ?? 0 }}
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 1. Filtro por sector --}}
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="grid grid-cols-3 gap-6">
                        @foreach (['vivienda' => 'bx-home', 'hijos' => 'bx-child', 'familia' => 'bx-group'] as $sector => $icon)
                            <a href="{{ route('admin.historialexpedientes', array_merge(request()->except('page', 'sector'), ['sector' => $sector])) }}"
                                class="flex flex-col items-center p-4 rounded-lg border transition
                                {{ request('sector') === $sector ? 'bg-[#54debd]/20 border-[#54debd]' : 'bg-white border-gray-200 hover:shadow' }}">
                                <i class="bx {{ $icon }} text-4xl mb-2 text-gray-700"></i>
                                <span class="text-lg font-medium">{{ ucfirst($sector) }}</span>
                                <span
                                    class="text-sm text-gray-500">{{ $sectorCounts[$sector] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- 2. Filtros adicionales: CCAA, Ayuda, Estado y Fase --}}
                <form method="GET" class="space-y-4" id="filtrosForm">
                    {{-- Mantener sector seleccionado --}}
                    <input type="hidden" name="sector" value="{{ request('sector') }}">

                    {{-- Búsqueda por texto --}}
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <label for="search"
                            class="block text-sm font-medium text-gray-700 mb-1">Buscar
                            usuario</label>
                        <input type="text" name="search" id="search"
                            value="{{ request('search') }}"
                            placeholder="Buscar por nombre, email o teléfono"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-[#54debd]/50 focus:border-[#54debd]" />
                    </div>

                    {{-- Filtro Universal Dinámico --}}
                    <div class="bg-white rounded-lg shadow-sm p-4" id="filtro-universal">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Filtros</h3>
                            <button type="button" id="agregar-filtro-btn"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-[#54debd] hover:bg-[#43c5a9] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd]">
                                <i class="bx bx-plus mr-1"></i>
                                Agregar Filtro
                            </button>
                        </div>

                        <div class="space-y-3" id="filtros-container">
                            <div id="filtros-vacios" class="text-center py-8 text-gray-500">
                                <i class="bx bx-filter text-4xl mb-2"></i>
                                <p>No hay filtros aplicados</p>
                                <p class="text-sm">Haz clic en "Agregar Filtro" para comenzar</p>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="flex space-x-4 mt-6">
                            <button type="button" id="aplicar-filtros-btn"
                                class="px-6 py-2 bg-[#54debd] text-white rounded-md shadow hover:bg-[#43c5a9] transition">
                                Aplicar Filtros
                            </button>
                            <a href="{{ route('admin.historialexpedientes') }}"
                                class="px-6 py-2 text-[#54debd] underline hover:text-[#43c5a9] transition">
                                Limpiar filtros
                            </a>
                        </div>
                    </div>

                    <!-- Selector de orden por fecha -->
                    <div class="bg-white rounded-lg shadow-sm p-4 flex items-center space-x-4">
                        <span class="font-medium text-gray-700">Ordenar por fecha:</span>
                        <select name="order"
                            class="border-gray-300 rounded-md shadow-sm focus:ring focus:ring-[#54debd]/50 focus:border-[#54debd]">
                            <option value="asc"
                                {{ request('order', 'asc') == 'asc' ? 'selected' : '' }}>
                                Más antiguas primero
                            </option>
                            <option value="desc"
                                {{ request('order') == 'desc' ? 'selected' : '' }}>
                                Más recientes primero
                            </option>
                        </select>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <label for="came_from_airtable"
                            class="block text-sm font-medium text-gray-700 mb-1">Origen
                            Airtable</label>
                        <select name="came_from_airtable" id="came_from_airtable"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-[#54debd]/50 focus:border-[#54debd]">
                            <option value="">Todos</option>
                            <option value="1"
                                {{ request('came_from_airtable') == '1' ? 'selected' : '' }}>Viene
                                de Airtable</option>
                            <option value="0"
                                {{ request('came_from_airtable') == '0' ? 'selected' : '' }}>No
                                viene de Airtable</option>
                        </select>
                    </div>

                </form>

                @if ($expedientes->count())
                    <div class="grid grid-cols-1 gap-6">
                        @foreach ($expedientes as $expediente)
                            @php
                                // Calcular totales de documentos requeridos
                                $totalDocs =
                                    $expediente->documentosGenerales->count() +
                                    $expediente->documentosEspeciales->count();
                                // Subidos y validados
                                $uploaded = $expediente->user->userDocuments->count();
                                $validated = $expediente->user->userDocuments
                                    ->where('estado', 'validado')
                                    ->count();
                                // Comunicaciones (excluyendo ciertas mail_classes)
                                $communications = \App\Models\MailTracking::where(
                                    'user_id',
                                    $expediente->user->id,
                                )
                                    ->whereNotIn('mail_class', [
                                        'App\\Mail\\UserNoBeneficiarioMail',
                                        'App\\Mail\\UserBeneficiarioMail',
                                        'App\\Mail\\FirstVisitMail',
                                        'App\\Mail\\WelcomeMail',
                                    ])
                                    ->count();
                            @endphp

                            <div @click='openDetalle({{ $expediente->id }})'
                                class="w-full bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden flex flex-row cursor-pointer">
                                {{-- Columna 1: Datos --}}
                                <div class="flex-1 p-4 flex flex-col justify-center">
                                    <h2 class="text-lg font-semibold text-gray-800">
                                        {{ $expediente->user->name ?? 'Sin nombre' }}
                                    </h2>
                                    <p class="text-gray-600">{{ $expediente->user->email }}</p>
                                    <p class="text-blue-600">{{ $expediente->user->telefono }}</p>

                                    @if ($expediente->user->came_from_airtable)
                                        <p class="text-xs text-blue-600 font-medium">
                                            Migración: {{ $expediente->user->came_from_airtable }}
                                        </p>
                                    @endif
                                </div>
                                {{-- Columna 2: Métricas --}}
                                <div
                                    class="flex-1 p-4 flex flex-col justify-center space-y-2 text-sm text-gray-700">
                                    <div class="flex items-center space-x-1">
                                        <i class="bx bx-cloud-upload text-lg"></i>
                                        <span>Subidos
                                            {{ $uploaded }}/{{ $totalDocs }}</span>
                                    </div>
                                    <div class="flex items-center space-x-1">
                                        <i class="bx bx-check-shield text-lg"></i>
                                        <span>Validados
                                            {{ $validated }}/{{ $totalDocs }}</span>
                                    </div>
                                    <div class="flex items-center space-x-1">
                                        <i class="bx bx-message-dots text-lg"></i>
                                        <span>Comunicac. {{ $communications }}</span>
                                    </div>
                                    <div class="flex items-center space-x-1">
                                        <i class="bx bx-list-check text-lg"></i>
                                        <span>Datos
                                            {{ $expediente->totalDatosContestados }}/{{ $expediente->totalDatos }}</span>
                                    </div>
                                </div>

                                {{-- Columna 3: Historial de actividad --}}
                                <div class="flex-1 p-4 flex flex-col justify-center">
                                    <div class="bg-white p-2 rounded-lg shadow-sm border"
                                        style="max-height: 120px; overflow-y: auto;">
                                        <h5 class="font-medium mb-1 text-xs">Historial de actividad
                                        </h5>
                                        <ul class="divide-y divide-gray-200 text-xs">
                                            @forelse($expediente->historial as $act)
                                                <li class="py-1">
                                                    <span
                                                        class="text-gray-500">{{ \Carbon\Carbon::parse($act->fecha_inicio)->format('d/m/Y H:i') }}</span>:
                                                    <span
                                                        class="text-gray-800">{{ $act->actividad }}</span>
                                                    @if ($act->observaciones)
                                                        <span
                                                            class="text-gray-600">({{ $act->observaciones }})</span>
                                                    @endif
                                                </li>
                                            @empty
                                                <li class="py-1 text-gray-400">Sin actividad
                                                    registrada.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>

                                {{-- Columna 4: Botón Siguiente Paso (solo OPx) --}}
                                <div class="p-3 ml-auto flex flex-col items-center gap-2"
                                    @click.stop x-data="siguientePasoData({{ $expediente->id }}, '', '', '', '{{ $expediente->ayuda->slug ?? '' }}', {{ json_encode($expediente->estados_opx ?? []) }})">

                                    {{-- Estados OPx (pueden ser varios) --}}
                                    <div class="text-center w-full mt-1">
                                        <div class="text-xs text-gray-500 mb-1">Estados OPx</div>
                                        <div
                                            class="flex flex-wrap gap-1 justify-center min-h-[1.5rem]">
                                            <template x-for="codigo in estadosOPx"
                                                :key="codigo">
                                                <span class="text-xs px-1.5 py-0.5 rounded"
                                                    :class="claseBadgeOPx(codigo)"
                                                    x-text="codigo"></span>
                                            </template>
                                            <span x-show="!estadosOPx || estadosOPx.length === 0"
                                                class="text-xs text-gray-400 italic">(ninguno)</span>
                                        </div>
                                    </div>

                                    {{-- Botón Siguiente Paso --}}
                                    <button @click="mostrarFlujos()" :disabled="cargandoFlujos"
                                        class="bg-[#54debd] hover:bg-[#368e79] disabled:bg-gray-400 text-white px-3 py-2 rounded-md text-xs font-medium transition-colors">
                                        <span x-show="!cargandoFlujos">Cambiar estado</span>
                                        <span x-show="cargandoFlujos" class="flex items-center">
                                            <svg class="animate-spin h-3 w-3 mr-1"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12"
                                                    cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4" fill="none"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                            </svg>
                                            Cargando...
                                        </span>
                                    </button>

                                    {{-- Opción de Deshacer --}}
                                    <div x-show="mostrarDeshacer"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                                        <div
                                            class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                                            <div class="flex items-center mb-4">
                                                <div class="flex-shrink-0">
                                                    <svg class="h-8 w-8 text-orange-500"
                                                        fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <div class="ml-3">
                                                    <h3 class="text-lg font-medium text-gray-900">
                                                        ¿Deshacer última acción?</h3>
                                                    <p class="text-sm text-gray-500">Tienes <span
                                                            x-text="tiempoRestante"></span>
                                                        segundos para decidir</p>
                                                </div>
                                            </div>

                                            <div class="mb-4 p-3 bg-gray-50 rounded-md">
                                                <p class="text-sm text-gray-700">
                                                    <strong>Estado anterior:</strong> <span
                                                        x-text="estadoAnterior"></span><br>
                                                    <span x-show="faseAnterior"><strong>Fase
                                                            anterior:</strong> <span
                                                            x-text="faseAnteriorNombre || faseAnterior"></span></span>
                                                </p>
                                            </div>

                                            <div class="flex justify-end space-x-3">
                                                <button @click="ocultarOpcionDeshacer()"
                                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                                                    Aceptar
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Mensajes de éxito/error --}}
                                    <div x-show="mostrarMensaje"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform translate-y-2"
                                        class="fixed top-4 right-4 z-50 max-w-sm">
                                        <div :class="{
                                            'bg-green-500': mensajeTipo === 'success',
                                            'bg-red-500': mensajeTipo === 'error'
                                        }"
                                            class="text-white px-4 py-3 rounded-lg shadow-lg flex items-center">
                                            <svg x-show="mensajeTipo === 'success'"
                                                class="w-5 h-5 mr-2" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                            <svg x-show="mensajeTipo === 'error'"
                                                class="w-5 h-5 mr-2" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                            <span x-text="mensajeTexto"></span>
                                        </div>
                                    </div>

                                    {{-- Modal de estados disponibles --}}
                                    <div x-show="mostrarModalFlujos"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0"
                                        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                                        @click.self="cerrarModalFlujos()">
                                        <div
                                            class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-96 overflow-y-auto">
                                            <div class="p-4 border-b">
                                                <h3 class="text-lg font-semibold">Estados OPx</h3>
                                                <p class="text-sm text-gray-600">Selecciona uno o
                                                    varios estados OPx para esta contratación</p>
                                            </div>
                                            <div class="p-4">
                                                <p class="text-sm text-gray-600 mb-3">Marca uno o
                                                    varios estados. La contratación puede tener
                                                    múltiples estados a la vez.</p>
                                                <div x-show="estadosDisponibles.length === 0"
                                                    class="text-center text-gray-500 py-4">
                                                    No hay estados disponibles
                                                </div>
                                                <form x-ref="formEstadosOPx"
                                                    x-show="estadosDisponibles.length > 0"
                                                    class="space-y-2"
                                                    @submit.prevent="guardarEstadosOPx()">
                                                    <template
                                                        x-for="estadoItem in estadosDisponibles"
                                                        :key="estadoItem.id">
                                                        <label
                                                            :class="{
                                                                'bg-amber-50 border-amber-300': estadoItem
                                                                    .grupo === 'OP1',
                                                                'bg-sky-50 border-sky-300': estadoItem
                                                                    .grupo === 'OP2',
                                                                'bg-violet-50 border-violet-300': estadoItem
                                                                    .grupo === 'OP3',
                                                                'bg-emerald-50 border-emerald-300': estadoItem
                                                                    .grupo === 'OP4',
                                                                'bg-rose-50 border-rose-300': estadoItem
                                                                    .grupo === 'OP5',
                                                                'bg-gray-50 border-gray-300': ![
                                                                    'OP1', 'OP2', 'OP3', 'OP4',
                                                                    'OP5'
                                                                ].includes(estadoItem.grupo)
                                                            }"
                                                            class="flex items-center gap-3 w-full text-left p-3 rounded-lg border cursor-pointer hover:opacity-90 transition-opacity">
                                                            <input type="checkbox"
                                                                name="estado_opx"
                                                                :value="estadoItem.codigo"
                                                                :checked="estadosOPx.includes(estadoItem
                                                                    .codigo)"
                                                                class="rounded border-gray-300">
                                                            <div>
                                                                <div class="font-medium"
                                                                    x-text="estadoItem.codigo">
                                                                </div>
                                                                <div class="text-xs text-gray-500 mt-1"
                                                                    x-text="estadoItem.grupo">
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </template>
                                                </form>
                                            </div>
                                            <div class="p-4 border-t flex gap-2">
                                                <button type="button"
                                                    @click="guardarEstadosOPx()"
                                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">
                                                    Guardar
                                                </button>
                                                <button type="button"
                                                    @click="cerrarModalFlujos()"
                                                    class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm">
                                                    Cerrar
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Rechazo -->
                                    <div x-show="mostrarModalRechazo"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0"
                                        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                                        @click.self="mostrarModalRechazo = false">
                                        <div
                                            class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4">
                                            <div class="p-4 border-b">
                                                <h3 class="text-lg font-semibold">Registrar rechazo
                                                </h3>
                                                <p class="text-sm text-gray-600">Selecciona los
                                                    motivos y añade una descripción</p>
                                            </div>
                                            <div
                                                class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 mb-2">Motivos</label>
                                                    <template x-if="motivosRechazo.length === 0">
                                                        <div class="text-sm text-gray-500">No hay
                                                            motivos disponibles</div>
                                                    </template>
                                                    <div class="grid grid-cols-1 gap-2">
                                                        <template x-for="m in motivosRechazo"
                                                            :key="m.id">
                                                            <label
                                                                class="flex items-center space-x-2">
                                                                <input type="checkbox"
                                                                    :value="m.id"
                                                                    x-model="motivoIdsSeleccionados"
                                                                    class="rounded border-gray-300">
                                                                <span class="text-sm"
                                                                    x-text="m.nombre"></span>
                                                            </label>
                                                        </template>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                                                    <textarea x-model="rechazoDescripcion" rows="4"
                                                        class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-red-300"
                                                        placeholder="Explica el motivo del rechazo..."></textarea>
                                                </div>
                                            </div>
                                            <div class="p-4 border-t flex justify-end space-x-2">
                                                <button @click="mostrarModalRechazo = false"
                                                    class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md text-sm">Cancelar</button>
                                                <button @click="confirmarRechazo()"
                                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm">Confirmar
                                                    rechazo</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8">{{ $expedientes->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="w-full flex flex-col items-center justify-center py-16">
                        <span class="text-2xl text-gray-400 mb-4">ðŸ˜•</span>
                        <p class="text-center text-gray-500 text-lg">No hay tramitaciones
                            registradas para este filtro.
                        </p>
                    </div>
                @endif
            </div> {{-- Cierre de div.w-full.px-4.py-6.space-y-6 --}}
        </div> {{-- Cierre de 1Âª columna --}}

        <!-- 2Âª columna: el side-panel -->
        <x-historial-expedientes.modal-detalle :open="$open ?? false" />

    </div> {{-- Cierre de contenedor principal flex --}}

    <script>
        function management() {
            return {
                open: false,
                loading: false,
                showSolicitante: false,
                showHijo: false,
                showContrato: false,
                showConvivientes: false,
                showArrendadores: false,
                // --- Estado y salida del countdown ---
                countdown: {
                    days: '--',
                    hours: '--',
                    minutes: '--',
                    seconds: '--',
                },
                remaining: 0,
                formatted: '',
                detalle: {
                    ayuda: {
                        documentos: [],
                        datos: []
                    },
                    user: {
                        answers: [],
                        user_documents: [],
                        convivientes: []
                    },
                    historial: [],
                    solicitanteDatos: [],
                    convivienteDatos: [],
                    hijoDatos: [],
                    contratoDatos: [],
                    arrendadorDatos: [],
                    documentosTramitacion: [],
                    documentosGenerales: [],
                    documentosEspeciales: [],
                    documentosRecibos: [],
                    recibosSubidos: {},
                },
                tab: 'resumen',
                missingFiles: {},
                uploading: false,
                showAddDocumentoTramitacion: false,
                documentosInternosDisponibles: [],
                nuevoDocumentoTramitacion: {
                    slug: '',
                    nombre_personalizado: ''
                },
                documentosInternosMap: {}, // Mapa slug -> id para documentos internos
                mostrarSelectorDocumentos: false,
                todosLosDocumentos: [],
                documentosFiltrados: [],
                documentosSeleccionados: [],
                documentosSeleccionadosTemporales: [],
                busquedaDocumentos: '',
                // Variables para el sistema de tabs
                ayudaId1: null,
                ayudaId2: null,
                otrasContrataciones: [],
                provincias: @json($provincias ?? []),
                mostrarSelectorProximaTarea: false,
                proximasTareasDisponibles: [],
                mostrarModalCrearTarea: false,
                tareasDisponibles: [],
                tareaSeleccionada: null,
                pasoCrearTarea: 1,

                showMessage(message, type = 'info') {
                    const notification = document.createElement('div');
                    notification.className =
                        `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;

                    if (type === 'success') {
                        notification.className += ' bg-green-500 text-white';
                    } else if (type === 'error') {
                        notification.className += ' bg-red-500 text-white';
                    } else {
                        notification.className += ' bg-blue-500 text-white';
                    }

                    notification.textContent = message;

                    // AÃ±adir al DOM
                    document.body.appendChild(notification);

                    // Animar entrada
                    setTimeout(() => {
                        notification.classList.remove('translate-x-full');
                    }, 100);

                    // Auto-eliminar despuÃ©s de 3 segundos
                    setTimeout(() => {
                        notification.classList.add('translate-x-full');
                        setTimeout(() => {
                            if (notification.parentNode) {
                                notification.parentNode.removeChild(notification);
                            }
                        }, 300);
                    }, 3000);
                },

                init() {
                    // Autoabrir modal si viene parametro contratacion_id
                    if (typeof this.initAutoOpenFromQuery === 'function') {
                        this.initAutoOpenFromQuery();
                    }
                },

                showBackendError(status, statusText, errorBody) {

                    // Crear modal de error detallado
                    const errorModal = document.createElement('div');
                    errorModal.className =
                        'fixed inset-0 z-[9999] bg-black bg-opacity-50 flex items-center justify-center p-4';
                    errorModal.innerHTML = `
                        <div class="bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                            <div class="bg-red-600 text-white px-6 py-4 flex justify-between items-center">
                                <h3 class="text-xl font-bold">ðŸš¨ Error del Backend</h3>
                                <button onclick="this.closest('.fixed').remove()" class="text-white hover:text-red-200 text-2xl">Ã—</button>
                            </div>
                            
                            <div class="p-6 space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-red-50 p-3 rounded">
                                        <strong>Status:</strong> ${status}
                                    </div>
                                    <div class="bg-red-50 p-3 rounded">
                                        <strong>Status Text:</strong> ${statusText}
                                    </div>
                                </div>
                                
                                <div>
                                    <strong class="block mb-2">Error del Servidor:</strong>
                                    <div class="bg-gray-100 p-4 rounded font-mono text-sm overflow-auto max-h-64">
                                        ${this.escapeHtml(errorBody)}
                                    </div>
                                </div>
                                
                                <div class="bg-yellow-50 p-4 rounded border border-yellow-200">
                                    <strong>ðŸ’¡ InformaciÃ³n para el Desarrollador:</strong>
                                    <ul class="mt-2 text-sm space-y-1">
                                        <li>â€¢ Este error ocurriÃ³ en el servidor (backend)</li>
                                        <li>â€¢ Revisa los logs de Laravel en el servidor</li>
                                        <li>â€¢ El problema estÃ¡ en ContratacionController@showJson</li>
                                        <li>â€¢ Verifica dependencias y configuraciÃ³n del servidor</li>
                                    </ul>
                                </div>
                                
                                <div class="flex justify-end space-x-3">
                                    <button onclick="this.closest('.fixed').remove()" 
                                            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                                        Cerrar
                                    </button>
                                    <button onclick="navigator.clipboard.writeText('Status: ${status}\\nStatus Text: ${statusText}\\nError: ${errorBody}')" 
                                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                        ðŸ“‹ Copiar Error
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;

                    // AÃ±adir al DOM
                    document.body.appendChild(errorModal);

                    // Auto-eliminar despuÃ©s de 30 segundos
                    setTimeout(() => {
                        if (errorModal.parentNode) {
                            errorModal.remove();
                        }
                    }, 30000);
                },

                escapeHtml(text) {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                },

                async openDetalle(payload) {

                    // payload debe ser ID o {id:...}
                    const id = typeof payload === 'number' ?
                        payload :
                        (typeof payload === 'string' ? Number(payload) : payload?.id);

                    if (!id) {
                        return;
                    }

                    this.alertasCargadas = false;

                    // Limpiar cache para forzar recarga completa cuando se cambia de tarjeta
                    this._detalleCache = this._detalleCache || {};
                    delete this._detalleCache[id];

                    const url = "{{ route('contrataciones.show.json', ':id') }}".replace(':id',
                        id);

                    this.open = true;
                    this.loading = true;
                    this.error = null;

                    try {
                        const res = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                        });

                        if (!res.ok) {

                            // CAPTURAR ERROR DEL BACKEND VISUALMENTE
                            try {
                                const errorText = await res.text();

                                // Mostrar error visual en la interfaz
                                this.showBackendError(res.status, res.statusText, errorText);

                                // Intentar parsear como JSON si es posible
                                try {
                                    const errorJson = JSON.parse(errorText);
                                } catch (parseError) {}
                            } catch (readError) {
                                this.showBackendError(res.status, res.statusText,
                                    'No se pudo leer el error del servidor');
                            }

                            throw new Error(`Error ${res.status}: ${res.statusText}`);
                        }

                        const data = await res.json();

                        // Normaliza ayuda por si viene en distintas claves
                        const ayudaObj = data.ayuda ?? data.detalle?.ayuda ?? this.detalle?.ayuda ??
                        {
                            nombre_ayuda: '',
                            fecha_fin: null
                        };

                        // Guarda listas internas si existen
                        this.documentosInternosDisponibles = data.documentosInternosDisponibles ??
                        [];
                        this.documentosInternosMap = data.documentosInternosMap ?? {};

                        // NormalizaciÃ³n con defaults para no romper el template
                        const detalleNormalizado = {
                            ...data,
                            ayuda: ayudaObj,
                            user: data.user ?? null,
                            historial: data.historial ?? [],
                            solicitanteDatos: data.solicitanteDatos ?? [],
                            contratoDatos: data.contratoDatos ?? [],
                            direccionDatos: data.direccionDatos ?? [],
                            convivienteDatos: data.convivienteDatos ?? [],
                            hijoDatos: data.hijoDatos ?? [],
                            arrendadorDatos: data.arrendadorDatos ?? [],
                            documentosGenerales: data.documentosGenerales ?? [],
                            documentosEspeciales: data.documentosEspeciales ?? [],
                            documentosTramitacion: data.documentosTramitacion ?? [],
                            documentosRecibos: data.documentosRecibos ?? [],
                            recibosSubidos: data.recibosSubidos ?? {},
                        };

                        this.detalle = detalleNormalizado;
                        this._detalleCache[id] = detalleNormalizado; // cachear

                        this.$nextTick(() => {
                            this.detalle = {
                                ...this.detalle
                            };

                            // Cargar configuraciÃ³n de documentos despuÃ©s de que el detalle estÃ© completamente asignado
                            this.cargarConfiguracionDocumentos();
                        });

                        // Cuenta atrÃ¡s basada en plazo_fin_ts
                        this.initCountdown();

                        // Cargar todos los documentos si no estÃ¡n cargados
                        if (this.todosLosDocumentos.length === 0) {
                            this.cargarTodosLosDocumentos();
                        }

                    } catch (e) {
                        if (e.name === 'TypeError') {} else if (e.name ===
                            'SyntaxError') {} else if (e.name === 'NetworkError') {}

                        this.error = e.message || 'No se pudo cargar el detalle.';
                    } finally {
                        this.loading = false;
                    }
                },

                initAutoOpenFromQuery() {
                    try {
                        // 1) Query param (compatibilidad)
                        const params = new URLSearchParams(window.location.search);
                        const idFromQuery = params.get('contratacion_id');
                        if (idFromQuery) this.openDetalle(Number(idFromQuery));

                        // 2) Hash de la URL: #open-<id>
                        const hash = window.location.hash || '';
                        const match = hash.match(/^#open-(\d+)$/);
                        if (match && match[1]) {
                            this.openDetalle(Number(match[1]));
                        }

                        // 3) Suscribirse a cambios de hash
                        window.addEventListener('hashchange', () => {
                            const h = window.location.hash || '';
                            const m = h.match(/^#open-(\d+)$/);
                            if (m && m[1]) {
                                this.openDetalle(Number(m[1]));
                            }
                        });
                    } catch (e) {
                        // silencioso
                    }
                },

                inicializarRespuestasMultiples() {
                    // Inicializar respuestas mÃºltiples para todos los tipos de datos
                    ['solicitanteDatos', 'hijoDatos', 'contratoDatos', 'direccionDatos'].forEach(
                        tipo => {
                            if (this.detalle[tipo]) {
                                this.detalle[tipo].forEach(dato => {
                                    if (dato.type === 'multiple' && !Array.isArray(dato
                                            .answer)) {
                                        dato.answer = [];
                                    }
                                });
                            }
                        });

                    // Convivientes
                    if (this.detalle.convivienteDatos) {
                        this.detalle.convivienteDatos.forEach(conv => {
                            conv.datos.forEach(dato => {
                                if (dato.type === 'multiple' && !Array.isArray(dato
                                        .answer)) {
                                    dato.answer = [];
                                }
                            });
                        });
                    }

                    // Arrendadores
                    if (this.detalle.arrendadoresDatos) {
                        this.detalle.arrendadoresDatos.forEach(arr => {
                            arr.preguntas.forEach(preg => {
                                if (preg.type === 'multiple' && !Array.isArray(preg
                                        .answer)) {
                                    preg.answer = [];
                                }
                            });
                        });
                    }

                    // Forzar actualizaciÃ³n de Alpine.js despuÃ©s de un breve delay
                    setTimeout(() => {
                        this.$nextTick(() => {
                            // Esto fuerza a Alpine.js a re-evaluar los bindings
                            this.detalle = {
                                ...this.detalle
                            };

                            // Forzar preselecciÃ³n de selects
                            this.preseleccionarSelects();
                        });
                    }, 100);
                },

                preseleccionarSelects() {
                    // Buscar todos los selects y forzar la preselecciÃ³n
                    setTimeout(() => {
                        const selects = document.querySelectorAll('select[x-model]');

                        // Solo procesar selects que no sean del tramitador o estado
                        selects.forEach((select) => {
                            const model = select.getAttribute('x-model');
                            if (model && !model.includes('tramitadorSeleccionado') && !
                                model.includes(
                                    'detalle.estado')) {
                                // Intentar obtener el valor del atributo data-value si existe
                                const dataValue = select.getAttribute('data-value');
                                if (dataValue && dataValue !== '') {
                                    select.value = dataValue;
                                    select.dispatchEvent(new Event('change', {
                                        bubbles: true
                                    }));
                                    select.dispatchEvent(new Event('input', {
                                        bubbles: true
                                    }));
                                }
                            }
                        });

                        setTimeout(() => {
                            this.preseleccionarProvinciasMunicipios();
                        }, 100);
                    }, 200);
                },

                preseleccionarProvinciasMunicipios() {
                    const direccionDatos = this.detalle.direccionDatos;
                    if (!direccionDatos) {
                        return;
                    }

                    const provinciaDato = direccionDatos.find(d => d.is_provincia);
                    const municipioDato = direccionDatos.find(d => d.is_municipio);

                    if (provinciaDato && provinciaDato.answer) {
                        const municipioValue = this.detalle.user?.answers?.find(a => a.question
                                ?.slug === 'municipio')
                            ?.answer || '';

                        setTimeout(() => {
                            this.cargarMunicipiosParaProvincia(provinciaDato.answer,
                                municipioDato, municipioValue);
                        }, 300);
                    }
                },

                cargarMunicipiosParaProvincia(provinciaNombre, municipioDato, municipioAPreseleccionar =
                    null) {

                    if (!provinciaNombre || !municipioDato) {
                        return;
                    }

                    let provinciaId = null;
                    for (const [id, nombre] of Object.entries(this.provincias)) {
                        if (nombre === provinciaNombre) {
                            provinciaId = id;
                            break;
                        }
                    }

                    if (!provinciaId) {
                        return;
                    }

                    const idx = this.detalle.direccionDatos.findIndex(d => d.is_municipio);
                    const municipioSelect = document.getElementById(`municipio_select_${idx}`);

                    if (municipioSelect) {
                        municipioSelect.innerHTML = '<option value="">Cargando municipios...</option>';
                        municipioSelect.disabled = true;
                    }

                    fetch(`/municipios/${provinciaId}`)
                        .then(res => res.json())
                        .then(data => {
                            municipioDato.options = {};
                            if (municipioSelect) {
                                municipioSelect.innerHTML =
                                    '<option value="">Selecciona un municipio</option>';
                            }
                            data.forEach(m => {
                                municipioDato.options[m.nombre_municipio] = m
                                    .nombre_municipio;
                                if (municipioSelect) {
                                    const opt = document.createElement('option');
                                    opt.value = m.nombre_municipio;
                                    opt.textContent = m.nombre_municipio;
                                    municipioSelect.appendChild(opt);
                                }
                            });
                            if (municipioSelect) {
                                municipioSelect.disabled = false;
                                // Preseleccionar si ya habÃ­a respuesta
                                if (municipioAPreseleccionar && municipioAPreseleccionar.trim() !==
                                    '') {
                                    setTimeout(() => {
                                        municipioSelect.value = municipioAPreseleccionar;
                                        municipioSelect.dispatchEvent(new Event('change', {
                                            bubbles: true
                                        }));
                                        municipioSelect.dispatchEvent(new Event('input', {
                                            bubbles: true
                                        }));
                                    }, 100);
                                }
                            }
                        })
                        .catch(error => {});
                },

                initCountdown() {
                    // limpia si ya habÃ­a uno
                    if (this._cdInt) {
                        clearInterval(this._cdInt);
                    }

                    const target = Number(this.detalle?.plazo_fin_ts);

                    if (!target || Number.isNaN(target)) {
                        this.countdown = null;
                        return;
                    }

                    const tick = () => {
                        const now = Date.now();
                        let diff = Math.max(0, target - now);

                        const d = Math.floor(diff / 86400000);
                        diff %= 86400000;
                        const h = Math.floor(diff / 3600000);
                        diff %= 3600000;
                        const m = Math.floor(diff / 60000);
                        diff %= 60000;
                        const s = Math.floor(diff / 1000);

                        this.countdown = {
                            d,
                            h,
                            m,
                            s
                        };
                    };

                    tick();
                    this._cdInt = setInterval(tick, 1000);
                },

                closeDetalle() {
                    this.open = false;
                    this.detalle = {};
                    this._detalleCache = {}; // Limpiar cache
                    if (this._cdInt) clearInterval(this._cdInt);
                    this.countdown = null;
                    // Limpiar configuraciÃ³n de documentos al cerrar
                    this.documentosSeleccionados = [];
                    this.documentosSeleccionadosTemporales = [];
                },
                // Devuelve true si el documento con 'key' en missingFiles tiene un archivo seleccionado
                hasMissing(key) {
                    const v = this.missingFiles[key];
                    return Array.isArray(v) ? v.length > 0 : false;
                },

                getMissingCount(key) {
                    const v = this.missingFiles[key];
                    return Array.isArray(v) ? v.length : 0;
                },
                // Devuelve true si el documento es de subida mÃºltiple, buscando en documentMeta
                isMultiById(id) {
                    const meta = window.documentMeta?.[id];
                    return !!(meta && (meta.multi_upload === 1 || meta.multi_upload === true));
                },

                isMultiDoc(doc) {
                    // doc.multi_upload llega en muchos endpoints; si no, intenta con documentMeta
                    return !!(
                        (doc && (doc.multi_upload === 1 || doc.multi_upload === true)) ||
                        this.isMultiById(doc?.id)
                    );
                },
                //esta funcion devuelve true si el usuario o conviviente tiene un documento subido
                hasDocs(documentId, convivIdx) {
                    if (!this.detalle.user || !this.detalle.user.user_documents) {
                        return false;
                    }

                    const hasDoc = this.detalle.user.user_documents.some(u =>
                        u.document_id === documentId &&
                        (convivIdx == null ?
                            (u.conviviente_index == null || u.conviviente_index === undefined) :
                            u.conviviente_index === convivIdx)
                    );


                    return hasDoc;
                },
                //esta funcion guarda en el objeto missingFiles los archivos seleccionados en los inputs de tipo file
                handleMissingFile(docId, convivienteId, inputEl) {
                    const key = `${docId}-${convivienteId}`;
                    // Convertir FileList a Array para poder usar forEach
                    this.missingFiles[key] = Array.from(inputEl.files);
                },
                //esta funcion sube los documentos que faltan 
                // async uploadMissing(docId, convivienteIndex = null, slug = null, nombrePersonalizado = null) {
                //     const key = `${docId}-${(convivienteIndex ?? 'null')}`;
                //     const file = this.missingFiles[key];
                //     if (!file) {
                //         alert('Selecciona un archivo antes de subirlo');
                //         return;
                //     }

                //     this.uploading = true;

                //     // --- resuelve el document_id real ---
                //     let realDocumentId;
                //     if (nombrePersonalizado) {
                //         const docPersonalizado = this.detalle.documentosTramitacion.find(
                //             d => d.id === docId && d.es_personalizado
                //         );
                //         realDocumentId = docPersonalizado?.document_id ?? this.getDocumentIdBySlug(slug);
                //     } else {
                //         realDocumentId = docId;
                //     }
                //     if (!realDocumentId || isNaN(parseInt(realDocumentId))) {
                //         console.error('Invalid document_id:', realDocumentId, 'for slug:', slug);
                //         alert('âŒ No se pudo determinar el ID del documento.');
                //         this.uploading = false;
                //         return;
                //     }

                //     const formData = new FormData();
                //     formData.append('file', file);
                //     formData.append('document_id', parseInt(realDocumentId));
                //     formData.append('slug', slug ?? (window.documentMeta?.[docId]?.slug ?? ''));
                //     if (convivienteIndex != null) formData.append('conviviente_index', convivienteIndex);
                //     if (nombrePersonalizado) formData.append('nombre_personalizado', nombrePersonalizado);

                //     try {
                //         const res = await fetch(`/contrataciones/${this.detalle.id}/upload-missing-document`, {
                //             method: 'POST',
                //             credentials: 'same-origin',
                //             headers: {
                //                 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                //                 'Accept': 'application/json',
                //                 'X-Requested-With': 'XMLHttpRequest'
                //             },
                //             body: formData
                //         });

                //         if (!res.ok) {
                //             const text = await res.text();
                //             throw new Error(text || `Error ${res.status}`);
                //         }

                //         await this.refreshDetalle();

                //         // Limpieza local
                //         delete this.missingFiles[key];
                //         const inputId = `file-${docId}-${(convivienteIndex ?? 'null')}`;
                //         const fileInput = document.getElementById(inputId);
                //         if (fileInput) fileInput.value = '';

                //         alert('âœ… Documento subido correctamente');
                //     } catch (err) {
                //         console.error('uploadMissing error:', err);
                //         alert('âŒ Error al subir el documento:\n' + err.message);
                //     } finally {
                //         this.uploading = false;
                //     }
                // },

                async uploadMissing(documentId, convivIdx = null, slug = null, nombrePersonalizado =
                    null, customKey = null) {
                    try {
                        // Para documentos de tramitaciÃ³n, necesitamos encontrar el ID del documento de tramitaciÃ³n
                        // para usar como key en missingFiles
                        let tramitacionDocId = null;
                        let realDocumentId = documentId;

                        if (this.detalle.documentosTramitacion) {
                            // Si se pasa nombrePersonalizado, buscar por nombre personalizado para mayor precisión
                            if (nombrePersonalizado) {
                                const tramitacionDoc = this.detalle.documentosTramitacion.find(
                                    doc =>
                                    doc.document_id == documentId && doc
                                    .nombre_personalizado === nombrePersonalizado
                                );
                                if (tramitacionDoc) {
                                    tramitacionDocId = tramitacionDoc.id;
                                }
                            } else {
                                // Si no hay nombre personalizado, buscar por document_id (comportamiento original)
                                const tramitacionDoc = this.detalle.documentosTramitacion.find(
                                    doc => doc.document_id == documentId);
                                if (tramitacionDoc) {
                                    tramitacionDocId = tramitacionDoc.id;
                                }
                            }
                        }

                        const key = customKey ?
                            `${customKey}-${(convivIdx ?? 'null')}` :
                            (tramitacionDocId ? `${tramitacionDocId}-${(convivIdx ?? 'null')}` :
                                `${documentId}-${(convivIdx ?? 'null')}`);
                        const files = this.missingFiles[key];

                        if (!files || !Array.isArray(files) || files.length === 0) {
                            this.showMessage(
                                'Por favor, selecciona al menos un archivo antes de subirlo',
                                'error');
                            return;
                        }

                        // Mostrar mensaje de carga y activar estado de subida
                        this.uploading = true;
                        this.showMessage('ðŸ“¤ Subiendo documento(s)...', 'info');

                        const fd = new FormData();
                        fd.append('document_id', realDocumentId);
                        fd.append('contratacion_id', this.detalle?.id);
                        if (slug) fd.append('slug', slug);
                        if (nombrePersonalizado) fd.append('nombre_personalizado',
                            nombrePersonalizado);
                        if (convivIdx !== null && convivIdx !== undefined) fd.append(
                            'conviviente_index', convivIdx);

                        files.forEach((file, index) => {
                            fd.append(`files[${index}]`, file);
                        });

                        // Determinar si es un documento de tramitaciÃ³n
                        const esDocumentoTramitacion = tramitacionDocId !== null;

                        // Usar la ruta correcta segÃºn el tipo de documento
                        const url = esDocumentoTramitacion ?
                            `/contrataciones/${this.detalle.id}/upload-documento-tramitacion` :
                            `/contrataciones/${this.detalle.id}/upload-missing-document`;

                        const res = await fetch(url, {
                            method: 'POST',
                            body: fd,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name=csrf-token]').content
                            }
                        });

                        if (!res.ok) {
                            const errorText = await res.text();
                            this.showMessage('âŒ Error subiendo archivo(s): ' + (errorText ||
                                `Error ${res.status}`), 'error');
                            this.uploading = false;
                            return;
                        }
                        let data;
                        try {
                            data = await res.json();
                        } catch (e) {
                            this.showMessage('âŒ Error en la respuesta del servidor', 'error');
                            this.uploading = false;
                            return;
                        }

                        if (data.id || (Array.isArray(data) && data.length > 0)) {
                            const nuevosDocs = Array.isArray(data) ? data : [data];
                            this.detalle.user.user_documents = [...this.detalle.user.user_documents,
                                ...nuevosDocs
                            ];

                            if (customKey && customKey.startsWith('recibo-')) {
                                const reciboSlug = customKey.replace('recibo-', '');
                                const recibo = this.detalle.documentosRecibos?.find(r => r.slug ===
                                    reciboSlug);
                                if (recibo) {
                                    if (!recibo.uploads) {
                                        recibo.uploads = [];
                                    }
                                    // Agregar los nuevos uploads con toda la información
                                    nuevosDocs.forEach(doc => {
                                        const docCompleto = {
                                            id: doc.id,
                                            user_id: doc.user_id || this.detalle.user
                                                .id,
                                            document_id: doc.document_id ||
                                                realDocumentId,
                                            slug: doc.slug || slug,
                                            estado: doc.estado || 'pendiente',
                                            nota_rechazo: doc.nota_rechazo || null,
                                            nombre_personalizado: doc
                                                .nombre_personalizado || null,
                                            conviviente_index: doc.conviviente_index ||
                                                null,
                                            temporary_url: doc.temporary_url || null
                                        };
                                        recibo.uploads.push(docCompleto);
                                    });
                                    recibo.subido = recibo.uploads.length > 0;
                                }
                            }

                            this.detalle = {
                                ...this.detalle
                            };
                        } else {}

                        const inputId = customKey ?
                            `file-${customKey}-${(convivIdx ?? 'null')}` :
                            `file-${documentId}-${(convivIdx ?? 'null')}`;
                        const fileInput = document.getElementById(inputId);
                        if (fileInput) {
                            fileInput.value = '';
                        }

                        delete this.missingFiles[key];

                        this.$nextTick(() => {

                        });

                        this.showMessage('âœ… Documento(s) subido(s) correctamente', 'success');
                    } catch (error) {
                        this.showMessage('âŒ Error inesperado al subir archivo(s): ' + error
                            .message, 'error');
                    } finally {
                        this.uploading = false;
                    }
                },



                // Actualizar el tramitador
                updateTramitador(expedienteId, selectedTramitadorId) {
                    if (!selectedTramitadorId) {
                        alert('Por favor, selecciona un tramitador.');
                        return;
                    }
                },

                async refreshDetalle() {
                    const id = this.detalle?.id;
                    if (!id) return;

                    const url = "{{ route('contrataciones.show.json', ':id') }}".replace(':id',
                        id);

                    try {
                        const res = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                        });
                        if (!res.ok) throw new Error(`Error ${res.status}`);
                        const data = await res.json();

                        // Reemplaza completamente con los datos del servidor
                        this.detalle = {
                            ...data,
                            ayuda: data.ayuda ?? (data.detalle?.ayuda ? {
                                nombre_ayuda: data.detalle.ayuda.nombre_ayuda
                            } : {
                                nombre_ayuda: ''
                            }),
                            user: data.user ?? this.detalle.user,
                            historial: data.historial ?? [],
                            solicitanteDatos: data.solicitanteDatos ?? [],
                            contratoDatos: data.contratoDatos ?? [],
                            direccionDatos: data.direccionDatos ?? [],
                            convivienteDatos: data.convivienteDatos ?? [],
                            hijoDatos: data.hijoDatos ?? [],
                            arrendadorDatos: data.arrendadorDatos ?? [],
                            documentosGenerales: data.documentosGenerales ?? [],
                            documentosEspeciales: data.documentosEspeciales ?? [],
                            documentosTramitacion: data.documentosTramitacion ?? [],
                            documentosRecibos: data.documentosRecibos ?? [],
                            recibosSubidos: data.recibosSubidos ?? {},
                            motivos_subsanacion: data.motivos_subsanacion ?? [],
                            motivos_subsanacion_seleccionados: data
                                .motivos_subsanacion_seleccionados ?? [],
                            tarea_en_curso: data.tarea_en_curso ?? null,
                        };
                        this.$nextTick(() => {
                            this.detalle = {
                                ...this.detalle
                            };
                        });
                    } catch (e) {}
                },

                mostrarProximasTareas(proximasTareas) {
                    this.proximasTareasDisponibles = proximasTareas;
                    this.mostrarSelectorProximaTarea = true;
                },

                updateDocEstado(id, estado, contratacionId, nota_rechazo = null) {
                    // Buscar el documento antes de actualizarlo para saber si es un recibo
                    const documento = this.detalle.user.user_documents.find(u => u.id === id);
                    const esRecibo = documento && documento.slug && documento.slug.startsWith(
                        'recibo_');

                    fetch(`/user-documents/${id}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')
                                    .content
                            },
                            body: JSON.stringify({
                                estado,
                                contratacion_id: contratacionId,
                                nota_rechazo: nota_rechazo
                            })
                        })
                        .then(r => r.json())
                        .then((data) => {
                            // Actualizar en memoria el documento dentro de detalle.user.user_documents
                            if (this.detalle && this.detalle.user && Array.isArray(this.detalle.user
                                    .user_documents)) {
                                this.detalle.user.user_documents = this.detalle.user.user_documents
                                    .map(u => {
                                        if (u.id === id) {
                                            return {
                                                ...u,
                                                estado: data.estado,
                                                nota_rechazo: data.nota_rechazo
                                            };
                                        }
                                        return u;
                                    });
                            }

                            // Si es un recibo, actualizar también en el array de recibos
                            if (esRecibo && documento.slug) {
                                const reciboSlug = documento.slug;
                                const recibo = this.detalle.documentosRecibos?.find(r => r.slug ===
                                    reciboSlug);
                                if (recibo && recibo.uploads) {
                                    recibo.uploads = recibo.uploads.map(u => {
                                        if (u.id === id) {
                                            return {
                                                ...u,
                                                estado: data.estado,
                                                nota_rechazo: data.nota_rechazo
                                            };
                                        }
                                        return u;
                                    });
                                }
                            }

                            // Mostrar mensaje de éxito si se guardó la nota de rechazo
                            if (data.message && data.success) {
                                this.showMessage(data.message, 'success');
                            }

                            // Forzar actualización de la vista
                            this.detalle = {
                                ...this.detalle
                            };
                        })
                        .catch(() => this.showMessage('âŒ Error al actualizar documento', 'error'));
                },

                addConviviente() {
                    fetch(`/contrataciones/${this.detalle.id}/convivientes`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')
                                    .content,
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(({
                            conviviente,
                            datos
                        }) => {
                            // AÃ±adir directamente al array de Alpine
                            this.detalle.convivienteDatos.push({
                                conviviente_id: conviviente.id,
                                index: conviviente.index,
                                datos: datos.map(d => ({
                                    ...d,
                                    answer: d.type === 'multiple' ? [] : ''
                                }))
                            });
                            this.refreshDetalle();
                            alert('âœ… Conviviente aÃ±adido correctamente');
                        })
                        .catch(() => alert('âŒ Error al aÃ±adir conviviente'));
                },

                removeConviviente(idx, block) {
                    if (!block.conviviente_id) {
                        // aÃºn no guardado en BD: solo elimina del array
                        this.detalle.convivienteDatos.splice(idx, 1);
                        alert('âœ… Conviviente eliminado');
                        return;
                    }
                    fetch(`/contrataciones/${this.detalle.id}/convivientes/${block.conviviente_id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')
                                    .content,
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => {
                            if (!res.ok) throw new Error();
                            // Eliminar directamente del array de Alpine
                            this.detalle.convivienteDatos = this.detalle.convivienteDatos.filter(
                                c => c
                                .conviviente_id !== block.conviviente_id);
                            alert('âœ… Conviviente eliminado');
                        })
                        .catch(() => alert('âŒ Error al eliminar conviviente'));
                },

                addArrendador() {
                    fetch(`/contrataciones/${this.detalle.id}/arrendadores`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')
                                    .content,
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(({
                            arrendador,
                            preguntas
                        }) => {
                            // AÃ±adir directamente al array de Alpine
                            this.detalle.arrendadoresDatos.push({
                                arrendador_id: arrendador.id,
                                index: arrendador.index,
                                preguntas: preguntas.map(p => ({
                                    ...p,
                                    answer: p.type === 'multiple' ? [] : ''
                                }))
                            });
                            alert('âœ… Arrendador creado');
                        })
                        .catch(() => alert('âŒ Error al crear arrendador'));
                },

                removeArrendador(idx, block) {
                    if (!block.arrendador_id) {
                        // aÃºn no guardado en BD: solo elimina del array
                        this.detalle.arrendadoresDatos.splice(idx, 1);
                        alert('âœ… Arrendador eliminado');
                        return;
                    }
                    fetch(`/contrataciones/${this.detalle.id}/arrendadores/${block.arrendador_id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')
                                    .content,
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // Eliminar directamente del array de Alpine
                                this.detalle.arrendadoresDatos = this.detalle.arrendadoresDatos
                                    .filter(a => a
                                        .arrendador_id !== block.arrendador_id);
                                alert('âœ… Arrendador eliminado');
                            } else {
                                alert('âŒ Error al eliminar arrendador');
                            }
                        })
                        .catch(() => alert('âŒ Error al eliminar arrendador'));
                },

                // guardar datos de solicitante y hijo
                saveDatos() {
                    // 1) Solicitud, hijo y contrato â†’ enviamos question_slug en lugar de question_id
                    const solicitante = (this.detalle.solicitanteDatos || []).map(d => ({
                        question_slug: d.slug,
                        answer: d.answer
                    }));
                    const hijo = (this.detalle.hijoDatos || []).map(d => ({
                        question_slug: d.slug,
                        answer: d.answer
                    }));
                    const contrato = (this.detalle.contratoDatos || []).map(d => ({
                        question_slug: d.slug,
                        answer: d.answer
                    }));
                    const direccion = (this.detalle.direccionDatos || []).map(d => ({
                        question_slug: d.slug,
                        answer: d.answer
                    }));

                    // 2) Convivientes â†’ misma estructura que arrendadores

                    const convivientes = (this.detalle.convivienteDatos || []).map(block => ({
                        conviviente_id: block.conviviente_id,
                        preguntas: (block.datos || []).map(d => ({
                            question_slug: d.slug,
                            answer: d.answer
                        }))
                    }));


                    // 3) Arrendadores â†’ corregir question_slug por slug
                    const arrendadores = (this.detalle.arrendadorDatos || []).map(a => ({
                        arrendador_id: a.arrendador_id, // Incluir el ID del arrendador
                        preguntas: (a.preguntas || []).map(p => ({
                            question_slug: p
                                .slug, // Corregido: usar p.slug en lugar de p.question_slug
                            answer: p.answer
                        }))
                    }));

                    // 4) Payload
                    const payload = {
                        solicitanteDatos: solicitante,
                        hijoDatos: hijo,
                        contratoDatos: contrato,
                        direccionDatos: direccion,
                        convivienteDatos: convivientes,
                        arrendadorDatos: arrendadores
                    };

                    fetch(`/contrataciones/${this.detalle.id}/update-datos`, {
                            method: 'PATCH',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')
                                    .content,
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(async res => {
                            if (!res.ok) throw await res.text();
                            return res.json();
                        })
                        .then(() => alert('âœ… Datos actualizados correctamente'))
                        .catch(err => {
                            alert('âŒ Error al actualizar los datos (mira la consola)');
                        });
                },

                descargarDocumento(downloadUrl, nombre) {
                    if (downloadUrl) {
                        window.location.href = downloadUrl;
                    } else {
                        console.warn('No hay download_url disponible, usando método alternativo');
                    }
                },

                eliminarDocumento(id, el) {
                    // Buscar el documento antes de eliminarlo para saber si es un recibo
                    const documento = this.detalle.user.user_documents.find(u => u.id === id);
                    const esRecibo = documento && documento.slug && documento.slug.startsWith(
                        'recibo_');

                    fetch(`/user-documents/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')
                                    .content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                contratacion_id: this.detalle.id
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // Elimina del array de Alpine, lo que refresca la vista
                                this.detalle.user.user_documents = this.detalle.user.user_documents
                                    .filter(u => u.id !== id);

                                // Si es un recibo, actualizar el array de recibos
                                if (esRecibo && documento.slug) {
                                    const reciboSlug = documento.slug;
                                    const recibo = this.detalle.documentosRecibos?.find(r => r
                                        .slug === reciboSlug);
                                    if (recibo && recibo.uploads) {
                                        recibo.uploads = recibo.uploads.filter(u => u.id !== id);
                                        recibo.subido = recibo.uploads.length > 0;
                                    }
                                }

                                // Forzar actualización de la vista
                                this.detalle = {
                                    ...this.detalle
                                };

                                this.showMessage('âœ… Documento eliminado', 'success');
                            } else {
                                this.showMessage('âŒ Error al eliminar el documento', 'error');
                            }
                        })
                        .catch(() => this.showMessage('âŒ Error al eliminar el documento', 'error'));
                },

                cargarDocumentosInternos() {
                    fetch('/contrataciones/documentos-internos', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')
                                    .content
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.documentosInternosDisponibles = data.documentos;
                                this.documentosInternosMap = {};
                                data.documentos.forEach(doc => {
                                    this.documentosInternosMap[doc.slug] = doc.id;
                                });
                            }
                        })
                        .catch(err => {});
                },

                getDocumentIdBySlug(slug) {
                    return this.documentosInternosMap[slug] || null;
                },

                addDocumentoTramitacion() {
                    if (!this.nuevoDocumentoTramitacion.slug || !this.nuevoDocumentoTramitacion
                        .nombre_personalizado) {
                        this.showMessage('Por favor, completa todos los campos', 'error');
                        return;
                    }

                    fetch(`/contrataciones/${this.detalle.id}/documentos-tramitacion`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')
                                    .content
                            },
                            body: JSON.stringify(this.nuevoDocumentoTramitacion)
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                const nuevoDoc = {
                                    id: data.documento.id,
                                    slug: data.documento.slug,
                                    name: data.documento.nombre_personalizado,
                                    nombre_personalizado: data.documento.nombre_personalizado,
                                    orden: data.documento.orden,
                                    document_id: data.documento
                                        .document_id, // Incluir el document_id real
                                    es_personalizado: true
                                };

                                this.detalle.documentosTramitacion.push(nuevoDoc);

                                this.nuevoDocumentoTramitacion = {
                                    slug: '',
                                    nombre_personalizado: ''
                                };
                                this.showAddDocumentoTramitacion = false;

                                this.showMessage(
                                    'âœ… Documento de tramitaciÃ³n aÃ±adido correctamente',
                                    'success');
                            } else {
                                this.showMessage('âŒ ' + (data.message ||
                                    'Error al aÃ±adir documento'), 'error');
                            }
                        })
                        .catch(err => {
                            this.showMessage('âŒ Error al aÃ±adir documento de tramitaciÃ³n',
                                'error');
                        });
                },

                removeDocumentoTramitacion(documentoId) {
                    if (!confirm(
                            'Â¿EstÃ¡s seguro de que quieres eliminar este documento de tramitaciÃ³n?'
                        )) {
                        return;
                    }

                    fetch(`/contrataciones/${this.detalle.id}/documentos-tramitacion/${documentoId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')
                                    .content
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.detalle.documentosTramitacion = this.detalle
                                    .documentosTramitacion.filter(d => d
                                        .id !== documentoId);
                                this.showMessage(
                                    'âœ… Documento de tramitaciÃ³n eliminado correctamente',
                                    'success');
                            } else {
                                this.showMessage('âŒ ' + (data.message ||
                                    'Error al eliminar documento'), 'error');
                            }
                        })
                        .catch(err => {
                            this.showMessage('âŒ Error al eliminar documento de tramitaciÃ³n',
                                'error');
                        });
                },

                obtenerOtrasContrataciones(userId) {
                    fetch(`/api/contrataciones-usuario/${userId}?exclude_ayuda_1=${this.ayudaId1}&exclude_ayuda_2=${this.ayudaId2}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')
                                    .content,
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            this.otrasContrataciones = data.contrataciones || [];
                        })
                        .catch(error => {
                            this.otrasContrataciones = [];
                        });
                },

                onProvinciaChange(event, dato) {
                    const provinciaNombre = event.target.value;
                    // Buscar el select de municipio correspondiente
                    const direccionDatos = this.detalle.direccionDatos;
                    const municipioDato = direccionDatos.find(d => d.is_municipio);
                    if (!municipioDato) return;
                    municipioDato.options = [];
                    municipioDato.answer = '';
                    const idx = direccionDatos.findIndex(d => d.is_municipio);
                    const municipioSelect = document.getElementById(`municipio_select_${idx}`);
                    if (municipioSelect) {
                        municipioSelect.innerHTML = '<option value="">Cargando municipios...</option>';
                        municipioSelect.disabled = true;
                    }
                    if (!provinciaNombre) {
                        if (municipioSelect) {
                            municipioSelect.innerHTML =
                                '<option value="">Selecciona primero una provincia</option>';
                            municipioSelect.disabled = false;
                        }
                        return;
                    }

                    let provinciaId = null;
                    for (const [id, nombre] of Object.entries(this.provincias)) {
                        if (nombre === provinciaNombre) {
                            provinciaId = id;
                            break;
                        }
                    }

                    if (!provinciaId) {
                        if (municipioSelect) {
                            municipioSelect.innerHTML =
                                '<option value="">Error: Provincia no encontrada</option>';
                            municipioSelect.disabled = false;
                        }
                        return;
                    }

                    fetch(`/municipios/${provinciaId}`)
                        .then(res => res.json())
                        .then(data => {
                            municipioDato.options = {};
                            if (municipioSelect) {
                                municipioSelect.innerHTML =
                                    '<option value="">Selecciona un municipio</option>';
                            }
                            data.forEach(m => {
                                municipioDato.options[m.nombre_municipio] = m
                                    .nombre_municipio;
                                if (municipioSelect) {
                                    const opt = document.createElement('option');
                                    opt.value = m.nombre_municipio;
                                    opt.textContent = m.nombre_municipio;
                                    municipioSelect.appendChild(opt);
                                }
                            });
                            if (municipioSelect) {
                                municipioSelect.disabled = false;
                                // Preseleccionar si ya habÃ­a respuesta
                                if (municipioDato.answer) {
                                    municipioSelect.value = municipioDato.answer;
                                }
                            }
                        });
                },

                close() {
                    this.open = false;
                    if (this._cdInt) clearInterval(this._cdInt);
                    this.countdown = null;
                    // Limpiar configuraciÃ³n de documentos al cerrar
                    this.documentosSeleccionados = [];
                    this.documentosSeleccionadosTemporales = [];
                },
                generarNombrePersonalizado(slug) {
                    if (!slug || !this.detalle || !this.detalle.user) return '';

                    const nombreUsuario = this.detalle.user.name;
                    let identificador = '';

                    if (nombreUsuario && nombreUsuario.trim() !== '') {
                        // Limpiar el nombre del usuario (solo letras, números y espacios)
                        const nombreLimpio = nombreUsuario.replace(/[^a-zA-Z0-9\s]/g, '').trim();
                        if (nombreLimpio) {
                            identificador = nombreLimpio;
                        } else {
                            // Si el nombre limpio está vacío, usar user_id
                            identificador = `user_${this.detalle.user.id}`;
                        }
                    } else {
                        // Si no hay nombre, usar user_id
                        identificador = `user_${this.detalle.user.id}`;
                    }

                    return `${slug}_${identificador}`;
                },
                actualizarNombrePersonalizado() {
                    if (this.nuevoDocumentoTramitacion.slug) {
                        // Siempre generar un nombre personalizado cuando se selecciona un slug
                        this.nuevoDocumentoTramitacion.nombre_personalizado = this
                            .generarNombrePersonalizado(this
                                .nuevoDocumentoTramitacion.slug);
                    }
                },
                limpiarFormularioDocumentoTramitacion() {
                    this.nuevoDocumentoTramitacion = {
                        slug: '',
                        nombre_personalizado: ''
                    };
                },

                // Funciones para configuraciÃ³n de documentos
                async cargarTodosLosDocumentos() {
                    console.log('ðŸ“š cargarTodosLosDocumentos ejecutÃ¡ndose...');
                    try {
                        const response = await fetch('/api/documentos', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name=csrf-token]').content
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Error al cargar documentos');
                        }

                        const data = await response.json();
                        this.todosLosDocumentos = data.documents || [];
                        this.documentosFiltrados = [...this.todosLosDocumentos];
                        console.log('âœ… Documentos cargados:', this.todosLosDocumentos);
                    } catch (error) {
                        console.error('âŒ Error cargando documentos:', error);
                        this.showMessage('âŒ Error al cargar documentos disponibles', 'error');
                    }
                },

                cargarConfiguracionDocumentos() {
                    console.log('ðŸ”§ cargarConfiguracionDocumentos ejecutÃ¡ndose...');
                    console.log('ðŸ“Š detalle disponible:', !!this.detalle, 'ID:', this.detalle?.id);


                    // Obtener ID de la contrataciÃ³n actual
                    const contratacionId = this.detalle?.id;
                    if (!contratacionId) {
                        console.log(
                            'âš ï¸ No hay ID de contrataciÃ³n, usando configuraciÃ³n por defecto');
                        this.documentosSeleccionados = this.obtenerIdsDocumentosAyuda();
                        this.documentosSeleccionadosTemporales = [...this.documentosSeleccionados];
                        return;
                    }

                    // Usar cookie especÃ­fica para esta contrataciÃ³n
                    const cookieName = `documentos_visibles_${contratacionId}`;
                    const configuracion = this.getCookie(cookieName);

                    if (configuracion) {
                        try {
                            const documentosCargados = JSON.parse(configuracion);
                            // Normalizar IDs a nÃºmeros
                            this.documentosSeleccionados = documentosCargados.map(id => Number(id));
                            console.log(
                                'ðŸ“‹ ConfiguraciÃ³n cargada desde cookie especÃ­fica (normalizada):',
                                this.documentosSeleccionados);
                        } catch (e) {
                            // Si hay error al parsear, usar configuraciÃ³n por defecto (solo documentos de la ayuda)
                            this.documentosSeleccionados = this.obtenerIdsDocumentosAyuda();
                            console.log(
                                'âš ï¸ Error parseando cookie especÃ­fica, usando configuraciÃ³n por defecto:',
                                this.documentosSeleccionados);
                        }
                    } else {
                        // ConfiguraciÃ³n por defecto: solo documentos de la ayuda
                        this.documentosSeleccionados = this.obtenerIdsDocumentosAyuda();
                        console.log('ðŸ“‹ Usando configuraciÃ³n por defecto para contrataciÃ³n:',
                            contratacionId);
                    }
                    // Inicializar la selecciÃ³n temporal con la configuraciÃ³n actual
                    this.documentosSeleccionadosTemporales = [...this.documentosSeleccionados];
                    console.log('ðŸ”„ SelecciÃ³n temporal inicializada:', this
                        .documentosSeleccionadosTemporales);

                    // Forzar actualizaciÃ³n de la vista para que se reflejen los cambios
                    this.$nextTick(() => {
                        this.detalle = {
                            ...this.detalle
                        };
                        console.log('ðŸ”„ Vista actualizada despuÃ©s de cargar configuraciÃ³n');
                    });
                },

                obtenerIdsDocumentosAyuda() {
                    // Obtener IDs de los documentos que pertenecen a la ayuda actual
                    const documentosAyuda = this.detalle.documentosGenerales || [];
                    return documentosAyuda.map(doc => doc.id);
                },

                async guardarConfiguracionDocumentos() {
                    // Obtener ID de la contrataciÃ³n actual
                    const contratacionId = this.detalle?.id;
                    if (!contratacionId) {
                        console.log(
                            'âš ï¸ No hay ID de contrataciÃ³n, no se puede guardar la configuraciÃ³n'
                        );
                        return;
                    }

                    try {
                        // Guardar configuración en la BD via API
                        const response = await fetch(
                            `/contrataciones/${contratacionId}/configurar-documentos`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    document_ids: this.documentosSeleccionados
                                })
                            });

                        if (response.ok) {
                            const data = await response.json();
                            if (data.success) {
                                console.log('💾 Configuración guardada en BD correctamente');
                            } else {
                                throw new Error(data.message || 'Error al guardar configuración');
                            }
                        } else {
                            throw new Error(`Error HTTP: ${response.status}`);
                        }
                    } catch (error) {
                        console.error('❌ Error guardando configuración en BD:', error);
                        this.showMessage('❌ Error al guardar la configuración de documentos',
                            'error');
                    }
                },

                toggleDocumentSelector() {
                    this.mostrarSelectorDocumentos = !this.mostrarSelectorDocumentos;
                    if (this.mostrarSelectorDocumentos && this.todosLosDocumentos.length === 0) {
                        this.cargarTodosLosDocumentos();
                    }
                    if (this.mostrarSelectorDocumentos) {
                        this.documentosSeleccionadosTemporales = [...this.documentosSeleccionados];
                    }
                },

                filtrarDocumentos() {
                    if (!this.busquedaDocumentos.trim()) {
                        this.documentosFiltrados = [...this.todosLosDocumentos];
                    } else {
                        const termino = this.busquedaDocumentos.toLowerCase();
                        this.documentosFiltrados = this.todosLosDocumentos.filter(doc =>
                            doc.name.toLowerCase().includes(termino) ||
                            (doc.description && doc.description.toLowerCase().includes(termino)) ||
                            (doc.slug && doc.slug.toLowerCase().includes(termino))
                        );
                    }
                },

                obtenerDocumentosVisibles() {
                    console.log('ðŸ“‹ documentosSeleccionados:', this.documentosSeleccionados);
                    console.log('ðŸ“š todosLosDocumentos:', this.todosLosDocumentos);
                    console.log('ðŸ“„ documentosGenerales:', this.detalle.documentosGenerales);

                    // Si no hay documentos seleccionados, no mostrar ningún documento
                    if (!this.documentosSeleccionados || this.documentosSeleccionados.length === 0) {
                        console.log('âš ï¸ No hay documentos seleccionados, mostrando solo ayuda');
                        return [];
                    }

                    // Si no hay todosLosDocumentos cargados, no mostrar ningún documento
                    if (!this.todosLosDocumentos || this.todosLosDocumentos.length === 0) {
                        console.log('âš ï¸ No hay todosLosDocumentos cargados, mostrando solo ayuda');
                        return [];
                    }

                    // Normalizar IDs a nÃºmeros para comparaciÃ³n correcta
                    const documentosSeleccionadosNormalizados = this.documentosSeleccionados.map(id =>
                        Number(id));
                    console.log('ðŸ”¢ documentosSeleccionados normalizados:',
                        documentosSeleccionadosNormalizados);

                    // Filtrar solo los documentos que están seleccionados
                    const documentosSeleccionados = this.todosLosDocumentos.filter(doc =>
                        documentosSeleccionadosNormalizados.includes(Number(doc.id))
                    );

                    console.log('ðŸŽ¯ documentosSeleccionados filtrados:', documentosSeleccionados);

                    // Crear un mapa para evitar duplicados
                    const documentosMap = new Map();

                    // Agregar solo los documentos seleccionados
                    documentosSeleccionados.forEach(doc => {
                        // Verificar si el usuario tiene documentos subidos para este tipo
                        const userDocs = this.detalle.user?.user_documents?.filter(ud =>
                            String(ud.document_id) === String(doc.id) &&
                            ud.user_id === this.detalle.user.id &&
                            (ud.conviviente_index === null || ud.conviviente_index ===
                                undefined)
                        ) || [];

                        // Convertir el formato para que sea compatible con el template
                        const documentoFormateado = {
                            id: doc.id,
                            name: doc.name,
                            slug: doc.slug,
                            tipo: 'general',
                            es_personalizado: false,
                            subido: userDocs.length > 0,
                            uploads: userDocs
                        };

                        console.log('âž• Agregando documento seleccionado:',
                            documentoFormateado);
                        documentosMap.set(doc.id, documentoFormateado);
                    });

                    const resultado = Array.from(documentosMap.values());
                    console.log('âœ… Resultado final:', resultado);
                    return resultado;
                },

                seleccionarTodosDocumentos() {
                    this.documentosSeleccionadosTemporales = this.todosLosDocumentos.map(doc => doc.id);
                },

                deseleccionarTodosDocumentos() {
                    this.documentosSeleccionadosTemporales = [];
                },

                seleccionarDocumentosFiltrados() {
                    const idsFiltrados = this.documentosFiltrados.map(doc => doc.id);
                    // Agregar los filtrados a los ya seleccionados temporalmente (sin duplicados)
                    const nuevosSeleccionados = [...new Set([...this.documentosSeleccionadosTemporales,
                        ...idsFiltrados
                    ])];
                    this.documentosSeleccionadosTemporales = nuevosSeleccionados;
                },

                async guardarSeleccionDocumentos() {
                    console.log('ðŸ’¾ guardarSeleccionDocumentos ejecutÃ¡ndose...');
                    console.log('ðŸ“‹ documentosSeleccionadosTemporales:', this
                        .documentosSeleccionadosTemporales);

                    // Normalizar IDs a nÃºmeros antes de guardar
                    this.documentosSeleccionados = this.documentosSeleccionadosTemporales.map(id =>
                        Number(id));
                    await this.guardarConfiguracionDocumentos();
                    this.mostrarSelectorDocumentos = false;

                    console.log('âœ… documentosSeleccionados actualizados (normalizados):', this
                        .documentosSeleccionados);

                    // Forzar actualizaciÃ³n de la vista
                    this.$nextTick(() => {
                        this.detalle = {
                            ...this.detalle
                        };
                        console.log(
                            'ðŸ”„ Vista actualizada, ejecutando obtenerDocumentosVisibles...'
                        );
                        // Forzar ejecuciÃ³n de la funciÃ³n
                        this.obtenerDocumentosVisibles();
                    });

                    this.showMessage(
                        'âœ… ConfiguraciÃ³n de documentos guardada correctamente para esta contrataciÃ³n',
                        'success');
                },

                async restablecerConfiguracionDocumentos() {
                    if (confirm(
                            'Â¿EstÃ¡s seguro de que quieres restablecer la configuraciÃ³n de documentos a los valores por defecto? Esto seleccionarÃ¡ solo los documentos de la ayuda actual.'
                        )) {
                        this.documentosSeleccionados = this.obtenerIdsDocumentosAyuda();
                        this.documentosSeleccionadosTemporales = [...this.documentosSeleccionados];
                        await this.guardarConfiguracionDocumentos();
                        this.$nextTick(() => {
                            this.detalle = {
                                ...this.detalle
                            };
                        });
                        this.showMessage(
                            'âœ… ConfiguraciÃ³n restablecida a los documentos de la ayuda para esta contrataciÃ³n',
                            'success');
                    }
                },

                // Funciones para manejar cookies
                setCookie(name, value, days) {
                    let expires = "";
                    if (days) {
                        const date = new Date();
                        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                        expires = "; expires=" + date.toUTCString();
                    }
                    document.cookie = name + "=" + (value || "") + expires + "; path=/";
                },

                getCookie(name) {
                    const nameEQ = name + "=";
                    const ca = document.cookie.split(';');
                    for (let i = 0; i < ca.length; i++) {
                        let c = ca[i];
                        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
                    }
                    return null;
                },

                init() {
                    // Autoabrir modal si viene parametro contratacion_id
                    if (typeof this.initAutoOpenFromQuery === 'function') {
                        this.initAutoOpenFromQuery();
                    }
                },

                // FunciÃ³n para verificar si todos los documentos generales estÃ¡n validados
                mostrarPreguntaDocumentosExtra() {

                    if (!this.detalle || !this.detalle.ayuda || !this.detalle.ayuda.documentos) {
                        return false;
                    }

                    // Obtener documentos generales obligatorios
                    const documentosGeneralesObligatorios = this.detalle.ayuda.documentos.filter(doc =>
                        doc.tipo === 'general' && doc.es_obligatorio === true
                    );

                    if (documentosGeneralesObligatorios.length === 0) {
                        console.log('No hay documentos generales obligatorios');
                        return false;
                    }

                    // Verificar que todos los documentos generales estÃ©n validados
                    const documentosValidados = this.detalle.user.user_documents.filter(ud =>
                        documentosGeneralesObligatorios.some(doc => doc.id === ud.document_id) &&
                        ud.estado === 'validado'
                    );

                    const documentosIdsValidados = documentosValidados.map(ud => ud.document_id);
                    const todosValidados = documentosGeneralesObligatorios.every(doc =>
                        documentosIdsValidados.includes(doc.id)
                    );

                    // Mostrar la pregunta si:
                    // 1. Todos los documentos generales están validados, O
                    const debeMostrar = todosValidados;

                    return debeMostrar;
                },

                // FunciÃ³n para procesar la respuesta sobre documentos extra
                async procesarDocumentosExtra(tieneDocumentosExtra) {
                    try {
                        const response = await fetch(
                            `/contrataciones/${this.detalle.id}/documentos-extra`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name=csrf-token]').content
                                },
                                body: JSON.stringify({
                                    tiene_documentos_extra: tieneDocumentosExtra
                                })
                            });

                        const data = await response.json();

                        if (data.success) {
                            if (tieneDocumentosExtra) {
                                alert(
                                    'âœ… Respuesta guardada. Puedes subir los documentos extra cuando estÃ©n listos.'
                                );
                            } else {
                                alert(
                                    'âœ… Proceso continuado automÃ¡ticamente. Se ha actualizado el estado de la contrataciÃ³n.'
                                );
                                // Recargar los datos del detalle
                                await this.openDetalle(this.detalle.id);
                            }
                        } else {
                            alert('âŒ Error: ' + (data.message ||
                                'No se pudo procesar la respuesta'));
                        }
                    } catch (error) {
                        console.error('Error al procesar documentos extra:', error);
                        alert('âŒ Error al procesar la respuesta');
                    }
                }

            }
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ccaaSelect = document.getElementById('ccaa_id');
            const ayudaSelect1 = document.getElementById('ayuda_id_1');
            const ayudaSelect2 = document.getElementById('ayuda_id_2');

            if (ccaaSelect && ayudaSelect1 && ayudaSelect2) {
                ccaaSelect.addEventListener('change', function() {
                    const ccaaId = this.value;

                    ayudaSelect1.innerHTML = '<option value="">Todas</option>';
                    ayudaSelect2.innerHTML = '<option value="">Todas</option>';

                    if (ccaaId) {
                        fetch(`/api/ayudas-por-ccaa/${ccaaId}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name=csrf-token]')
                                        .content,
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(ayudas => {
                                ayudas.forEach(ayuda => {
                                    const option1 = document.createElement(
                                        'option');
                                    option1.value = ayuda.id;
                                    option1.textContent = ayuda
                                        .nombre_ayuda;
                                    ayudaSelect1.appendChild(option1);
                                    const option2 = document.createElement(
                                        'option');
                                    option2.value = ayuda.id;
                                    option2.textContent = ayuda
                                        .nombre_ayuda;
                                    ayudaSelect2.appendChild(option2);
                                });
                            })
                            .catch(error => {});
                    }
                });
            }
            const form = document.getElementById('form-guardar-datos');
            const mensaje = document.getElementById('estado-mensaje');

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(form);
                    fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': form.querySelector(
                                    'input[name=_token]').value,
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            mostrarMensaje('success', data.message ||
                                'Datos guardados correctamente');
                            setTimeout(() => {
                                if (window.Alpine) {
                                    const root = document.querySelector(
                                        '[x-data]');
                                    if (root && root.__x) {
                                        root.__x.$data.open = true;
                                    }
                                }
                            }, 100);
                        })
                        .catch(err => {
                            mostrarMensaje('error', 'Error al guardar los datos');
                        });
                });
            }

            function mostrarMensaje(tipo, texto) {
                mensaje.style.display = 'block';
                mensaje.className = tipo === 'success' ?
                    'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4' :
                    'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
                mensaje.textContent = texto;
                setTimeout(() => {
                    mensaje.style.display = 'none';
                }, 3000);
            }

            // Inicializar configuraciÃ³n de documentos
            const root = document.querySelector('[x-data]');
            if (root && root.__x) {
                root.__x.$data.cargarTodosLosDocumentos();
            }
        });
    </script>

    <script>
        // Filtros por estado: solo OPx
        function metricasEstado() {
            return {
                estadoOpxSeleccionado: '{{ request('estado_opx') }}',

                limpiarFiltros() {
                    const params = new URLSearchParams(window.location.search);
                    params.delete('estado_opx');
                    params.delete('page');
                    window.location.href = `${window.location.pathname}?${params.toString()}`;
                },

                seleccionarEstadoOPx(codigo) {
                    if (this.estadoOpxSeleccionado === codigo) {
                        this.limpiarFiltroOPx();
                        return;
                    }
                    this.estadoOpxSeleccionado = codigo;
                    const params = new URLSearchParams(window.location.search);
                    params.set('estado_opx', codigo);
                    params.delete('page');
                    window.location.href = `${window.location.pathname}?${params.toString()}`;
                },

                limpiarFiltroOPx() {
                    this.estadoOpxSeleccionado = '';
                    const params = new URLSearchParams(window.location.search);
                    params.delete('estado_opx');
                    params.delete('page');
                    window.location.href = `${window.location.pathname}?${params.toString()}`;
                }
            };
        }

        // FunciÃ³n para manejar los filtros de fases dinÃ¡micos
        function filtroFases() {
            return {
                fase1: '{{ request('fase_1') }}',
                fase2: '{{ request('fase_2') }}',
                fasesDisponibles1: [],
                fasesDisponibles2: [],
                mostrarFase1: false,
                mostrarFase2: false,

                init() {
                    this.actualizarFasesDisponibles();
                },

                actualizarFasesDisponibles() {
                    const estado1 = document.getElementById('estado_1')?.value || '';
                    const estado2 = document.getElementById('estado_2')?.value || '';

                    if (estado1) {
                        this.fasesDisponibles1 = this.obtenerFasesParaEstado(estado1);
                        this.mostrarFase1 = true;
                    } else {
                        this.fasesDisponibles1 = [];
                        this.mostrarFase1 = false;
                        this.fase1 = '';
                    }

                    if (estado2) {
                        this.fasesDisponibles2 = this.obtenerFasesParaEstado(estado2);
                        this.mostrarFase2 = true;
                    } else {
                        this.fasesDisponibles2 = [];
                        this.mostrarFase2 = false;
                        this.fase2 = '';
                    }
                },

                obtenerFasesParaEstado(estado) {
                    const fasesDisponibles = window.fasesDisponibles || {};
                    return fasesDisponibles[estado] || [];
                },

                actualizarFase2() {
                    if (this.fase1 && this.fasesDisponibles2.length > 0) {
                        const faseValida = this.fasesDisponibles2.some(f => f.slug === this.fase2);
                        if (!faseValida) {
                            this.fase2 = '';
                        }
                    }
                }
            };
        }

        // FunciÃ³n global para actualizar fases disponibles (llamada desde los selects de estado)
        function actualizarFasesDisponibles() {
            const filtroFasesComponent = document.querySelector('[x-data*="filtroFases"]');
            if (filtroFasesComponent && filtroFasesComponent._x_dataStack) {
                const data = filtroFasesComponent._x_dataStack[0];
                if (data && data.actualizarFasesDisponibles) {
                    data.actualizarFasesDisponibles();
                }
            }
        }

        // FunciÃ³n para manejar el botÃ³n "Siguiente paso"
        function siguientePasoData(id, estadoInicial, faseInicial, faseNombre, ayudaSlug,
            estadosOpxInicial = []) {
            return {
                estado: estadoInicial,
                fase: faseInicial,
                faseNombre: faseNombre,
                ayuda: ayudaSlug,
                estadosOPx: Array.isArray(estadosOpxInicial) ? estadosOpxInicial : [],
                cargandoFlujos: false,
                mostrarModalFlujos: false,
                estadosDisponibles: [],
                // Rechazo
                mostrarModalRechazo: false,
                motivosRechazo: [],
                motivoIdsSeleccionados: [],
                rechazoDescripcion: '',
                mostrarDeshacer: false,
                tiempoRestante: 0,
                estadoAnterior: null,
                faseAnterior: null,
                faseAnteriorNombre: null,
                timerDeshacer: null,
                mostrarMensaje: false,
                mensajeTexto: '',
                mensajeTipo: 'success',

                claseBadgeOPx(codigo) {
                    const g = (codigo || '').split('-')[0];
                    return {
                        'OP1': 'bg-amber-100 text-amber-800',
                        'OP2': 'bg-sky-100 text-sky-800',
                        'OP3': 'bg-violet-100 text-violet-800',
                        'OP4': 'bg-emerald-100 text-emerald-800',
                        'OP5': 'bg-rose-100 text-rose-800'
                    } [g] || 'bg-gray-100 text-gray-800';
                },

                async mostrarFlujos() {
                    console.log('mostrarFlujos llamado. id:', id);

                    if (!id) {
                        console.error('No se puede cargar estados: id no estÃ¡ definido');
                        alert('Error: No se puede cargar los estados. ID no disponible.');
                        return;
                    }

                    this.cargandoFlujos = true;
                    try {
                        const response = await fetch(`/contrataciones/${id}/flujos-disponibles`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Error al cargar los estados');
                        }

                        const data = await response.json();
                        this.estadosDisponibles = data.estados || [];
                        this.mostrarModalFlujos = true;
                    } catch (error) {
                        console.error('Error al cargar estados:', error);
                        alert('Error al cargar los estados disponibles');
                    } finally {
                        this.cargandoFlujos = false;
                    }
                },

                async guardarEstadosOPx() {
                    if (!id) {
                        alert('Error: No se puede guardar. ID no disponible.');
                        return;
                    }
                    const form = this.$refs.formEstadosOPx;
                    if (!form) return;
                    const codigos = Array.from(form.querySelectorAll(
                        'input[name="estado_opx"]:checked')).map(inp => inp.value);
                    try {
                        const response = await fetch(`/contrataciones/${id}/estados-opx`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                codigos: codigos,
                                replace: true
                            })
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || 'Error al actualizar estados OPx');
                        }
                        this.estadosOPx = data.estados_opx || [];
                        this.mostrarModalFlujos = false;
                        this.mostrarMensajeExito('Estados OPx actualizados correctamente');
                        this.$dispatch('estados-opx-actualizados', {
                            contratacionId: id,
                            estados_opx: data.estados_opx
                        });
                    } catch (error) {
                        console.error('Error al guardar estados OPx:', error);
                        this.mostrarMensajeError(error.message ||
                            'Error al actualizar los estados OPx');
                    }
                },

                cerrarModalFlujos() {
                    this.mostrarModalFlujos = false;
                    this.estadosDisponibles = [];
                },

                mostrarOpcionDeshacer(estadoAnterior, faseAnterior, faseAnteriorNombre) {
                    this.estadoAnterior = estadoAnterior;
                    this.faseAnterior = faseAnterior;
                    this.faseAnteriorNombre = faseAnteriorNombre;
                    this.mostrarDeshacer = true;
                    this.tiempoRestante = 30;

                    // Iniciar countdown
                    this.timerDeshacer = setInterval(() => {
                        this.tiempoRestante--;
                        if (this.tiempoRestante <= 0) {
                            this.ocultarOpcionDeshacer();
                        }
                    }, 1000);
                },

                ocultarOpcionDeshacer() {
                    this.mostrarDeshacer = false;
                    this.tiempoRestante = 0;
                    this.estadoAnterior = null;
                    this.faseAnterior = null;
                    if (this.timerDeshacer) {
                        clearInterval(this.timerDeshacer);
                        this.timerDeshacer = null;
                    }
                },

                mostrarMensajeExito(texto) {
                    this.mensajeTexto = texto;
                    this.mensajeTipo = 'success';
                    this.mostrarMensaje = true;

                    // Ocultar mensaje despuÃ©s de 3 segundos
                    setTimeout(() => {
                        this.mostrarMensaje = false;
                    }, 3000);
                },

                mostrarMensajeError(texto) {
                    this.mensajeTexto = texto;
                    this.mensajeTipo = 'error';
                    this.mostrarMensaje = true;

                    // Ocultar mensaje despuÃ©s de 5 segundos
                    setTimeout(() => {
                        this.mostrarMensaje = false;
                    }, 5000);
                }
            };
        }

        // Cambio de estado: solo vía PATCH /contrataciones/{id}/estados-opx (siguientePasoData.guardarEstadosOPx y flowModal)
    </script>

    <script>
        // Legacy: ya no usamos fases por estado (solo OPx)
        window.fasesDisponibles = {};
    </script>

    <script>
        class FiltroUniversal {
            constructor() {
                this.filtros = [];
                this.ccaas = @json(
                    $ccaas->map(function ($nombre, $id) {
                            return ['value' => $id, 'label' => $nombre];
                        })->values());
                this.ayudas = @json(
                    $ayudasPorCcaa->map(function ($nombre, $id) {
                            return ['value' => $id, 'label' => $nombre];
                        })->values());
                this.init();
            }

            init() {
                this.cargarFiltrosExistentes();
                this.bindEvents();
                this.renderFiltros();
            }

            bindEvents() {
                document.getElementById('agregar-filtro-btn').addEventListener('click', () => this
                    .agregarFiltro());
                document.getElementById('aplicar-filtros-btn').addEventListener('click', () => this
                    .enviarFormulario());
            }

            cargarFiltrosExistentes() {
                const urlParams = new URLSearchParams(window.location.search);
                this.filtros = [];

                // Cargar filtros desde la URL
                let index = 0;
                while (urlParams.has(`filtros[${index}][campo]`)) {
                    const campo = urlParams.get(`filtros[${index}][campo]`);
                    const operador = urlParams.get(`filtros[${index}][operador]`);
                    const valor = urlParams.get(`filtros[${index}][valor]`);

                    if (campo && operador && valor) {
                        this.filtros.push({
                            campo: campo,
                            operador: operador,
                            valor: valor,
                            operadoresDisponibles: this.getOperadoresParaCampo(campo),
                            opcionesDisponibles: this.getOpcionesParaCampo(campo)
                        });
                    }
                    index++;
                }
            }

            getOperadoresParaCampo(campo) {
                const operadores = {
                    'ccaa_id': [{
                            value: 'igual_a',
                            label: 'igual a'
                        },
                        {
                            value: 'diferente_de',
                            label: 'diferente de'
                        }
                    ],
                    'ayuda_id': [{
                            value: 'igual_a',
                            label: 'igual a'
                        },
                        {
                            value: 'diferente_de',
                            label: 'diferente de'
                        }
                    ]
                };
                return operadores[campo] || [];
            }

            getOpcionesParaCampo(campo) {
                const opciones = {
                    'ccaa_id': this.ccaas,
                    'ayuda_id': this.ayudas
                };
                return opciones[campo] || [];
            }

            agregarFiltro() {
                this.filtros.push({
                    campo: '',
                    operador: '',
                    valor: '',
                    operadoresDisponibles: [],
                    opcionesDisponibles: []
                });
                this.renderFiltros();
            }

            eliminarFiltro(index) {
                this.filtros.splice(index, 1);
                this.renderFiltros();
            }

            actualizarOperadores(index) {
                const filtro = this.filtros[index];
                filtro.operadoresDisponibles = this.getOperadoresParaCampo(filtro.campo);
                filtro.opcionesDisponibles = this.getOpcionesParaCampo(filtro.campo);
                filtro.operador = '';
                filtro.valor = '';
                this.renderFiltros();
            }

            renderFiltros() {
                const container = document.getElementById('filtros-container');
                const vacios = document.getElementById('filtros-vacios');

                if (!container) {
                    console.error('No se encontró el contenedor de filtros');
                    return;
                }

                if (this.filtros.length === 0) {
                    if (vacios) {
                        vacios.style.display = 'block';
                    }
                    return;
                }

                if (vacios) {
                    vacios.style.display = 'none';
                }

                container.innerHTML = this.filtros.map((filtro, index) => `
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg" data-index="${index}">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Donde</span>
                        
                        <select class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring focus:ring-[#54debd]/50 focus:border-[#54debd]" 
                                data-field="campo">
                            <option value="" disabled ${!filtro.campo ? 'selected' : ''}>Seleccionar campo</option>
                            <option value="ccaa_id" ${filtro.campo === 'ccaa_id' ? 'selected' : ''}>Comunidad Autónoma</option>
                            <option value="ayuda_id" ${filtro.campo === 'ayuda_id' ? 'selected' : ''}>Ayuda</option>
                        </select>
                        
                        <select class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring focus:ring-[#54debd]/50 focus:border-[#54debd]" 
                                data-field="operador">
                            <option value="" disabled ${!filtro.operador ? 'selected' : ''}>Seleccionar operador</option>
                            ${filtro.operadoresDisponibles.map(op => 
                                `<option value="${op.value}" ${filtro.operador == op.value ? 'selected' : ''}>${op.label}</option>`
                            ).join('')}
                        </select>
                        
                        <select class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring focus:ring-[#54debd]/50 focus:border-[#54debd]" 
                                data-field="valor">
                            <option value="">Seleccionar una opción</option>
                            ${filtro.opcionesDisponibles.map(opcion => 
                                `<option value="${opcion.value}" ${filtro.valor == opcion.value ? 'selected' : ''}>${opcion.label}</option>`
                            ).join('')}
                        </select>
                        
                        <div class="flex items-center space-x-1">
                            <button type="button" 
                                    class="p-1 text-red-600 hover:text-red-800 hover:bg-red-100 rounded transition"
                                    title="Eliminar filtro">
                                <i class="bx bx-trash text-sm"></i>
                            </button>
                            <div class="p-1 text-gray-400 cursor-move" title="Arrastrar para reordenar">
                                <i class="bx bx-dots-vertical text-sm"></i>
                            </div>
                        </div>
                    </div>
                `).join('');

                // Bind events para los selects
                this.bindSelectEvents();
            }

            bindSelectEvents() {
                this.filtros.forEach((filtro, index) => {
                    const row = document.querySelector(`[data-index="${index}"]`);
                    if (row) {
                        const selects = row.querySelectorAll('select');
                        selects.forEach((select, selectIndex) => {
                            select.addEventListener('change', (e) => {
                                const field = e.target.dataset.field;
                                filtro[field] = e.target.value;

                                if (field === 'campo') {
                                    this.actualizarOperadores(index);
                                }
                            });
                        });

                        // Botón eliminar
                        const deleteBtn = row.querySelector(
                            'button[title="Eliminar filtro"]');
                        if (deleteBtn) {
                            deleteBtn.addEventListener('click', () => {
                                this.eliminarFiltro(index);
                            });
                        }
                    }
                });
            }

            enviarFormulario() {
                this.crearCamposOcultos();

                document.getElementById('filtrosForm').submit();
            }

            crearCamposOcultos() {
                const existingHidden = document.querySelectorAll('input[name^="filtros["]');
                existingHidden.forEach(input => input.remove());

                this.filtros.forEach((filtro, index) => {
                    if (filtro.campo && filtro.operador && filtro.valor) {
                        const campoInput = document.createElement('input');
                        campoInput.type = 'hidden';
                        campoInput.name = `filtros[${index}][campo]`;
                        campoInput.value = filtro.campo;
                        document.getElementById('filtrosForm').appendChild(campoInput);

                        const operadorInput = document.createElement('input');
                        operadorInput.type = 'hidden';
                        operadorInput.name = `filtros[${index}][operador]`;
                        operadorInput.value = filtro.operador;
                        document.getElementById('filtrosForm').appendChild(operadorInput);

                        const valorInput = document.createElement('input');
                        valorInput.type = 'hidden';
                        valorInput.name = `filtros[${index}][valor]`;
                        valorInput.value = filtro.valor;
                        document.getElementById('filtrosForm').appendChild(valorInput);
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            window.filtroUniversal = new FiltroUniversal();
        });
    </script>

    <script>
        // Fallback global: abrir modal por hash/query incluso si Alpine se retrasa
        (function() {
            function dispatchOpen(id) {
                if (!id) return;
                try {
                    window.dispatchEvent(new CustomEvent('open-detail', {
                        detail: Number(id)
                    }));
                } catch (e) {}
            }

            function checkAndOpen() {
                try {
                    const params = new URLSearchParams(window.location.search);
                    const fromQuery = params.get('contratacion_id');
                    if (fromQuery) {
                        dispatchOpen(fromQuery);
                        return;
                    }
                    const hash = window.location.hash || '';
                    const m = hash.match(/^#open-(\d+)$/);
                    if (m && m[1]) {
                        dispatchOpen(m[1]);
                    }
                } catch (e) {}
            }
            document.addEventListener('alpine:init', checkAndOpen);
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(checkAndOpen, 0);
            });
            window.addEventListener('load', function() {
                setTimeout(checkAndOpen, 0);
            });
            window.addEventListener('hashchange', checkAndOpen);
        })();
    </script>
</body>

</html>
