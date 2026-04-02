<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flujos de Tramitación - Backoffice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        #ayudas-list-container {
            scroll-behavior: smooth;
        }
        
        #ayudas-list-container .ayuda-checkbox {
            pointer-events: auto;
        }
        
        #ayudas-list-container > div {
            scroll-margin: 0;
        }
    </style>
</head>
<body class="bg-gray-100">

@include('layouts.headerbackoffice')

<!-- Contenido principal -->
<main class="container mx-auto py-8 px-4" x-data="flujoManager()">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">
            Gestión de <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#54debd] to-[#368e79]">Flujos de Transiciones</span>
        </h1>
        <p class="text-gray-600">Configura las transiciones válidas para cada tipo de ayuda</p>
    </div>
            <!-- Selección de Ayuda -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Seleccionar Ayuda</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="ayuda-select" class="block text-sm font-medium text-gray-700 mb-2">Tipo de Ayuda</label>
                        <select id="ayuda-select" x-model="ayudaSeleccionada" @change="cargarFlujos()" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecciona una ayuda</option>
                            <template x-for="ayuda in ayudas" :key="ayuda.id">
                                <option :value="ayuda.id" x-text="ayuda.nombre"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button @click="mostrarFormularioCrear()" 
                                :disabled="!ayudaSeleccionada"
                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-md text-sm font-medium">
                            Crear Nuevo Flujo
                        </button>
                        <button @click="openCopiarModal()" 
                                :disabled="!ayudaSeleccionada"
                                class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-copy mr-1"></i>
                            Copiar Flujos
                        </button>
                    </div>
                </div>
            </div>

            <!-- Lista de Flujos -->
            <div class="bg-white rounded-lg shadow" x-show="ayudaSeleccionada">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Flujos:</h3>
                </div>
                
                <div class="p-6">
                    
                    <div x-show="cargando" class="text-center py-4">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-gray-500">Cargando flujos...</p>
                    </div>

                    <div x-show="!cargando && flujos.length === 0" class="text-center py-8">
                        <p class="text-gray-500">No hay flujos definidos para esta ayuda.</p>
                        <button @click="mostrarFormularioCrear()" 
                                class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Crear Primer Flujo
                        </button>
                    </div>

                    <div x-show="!cargando && flujos.length > 0" class="space-y-4">
                        <template x-for="flujo in flujos" :key="flujo.id">
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                ESTADO Y FASE
                                            </span>
                                            
                                            <div class="flex items-center space-x-4">
                                                <!-- Estado -->
                                                <div class="flex items-center space-x-1">
                                                    <span class="text-xs text-gray-500">Estado:</span>
                                                    <span x-text="flujo.estado_origen?.nombre || flujo.estado_origen" 
                                                          class="px-2 py-1 bg-gray-100 rounded text-sm"></span>
                                                    <span class="text-gray-400">→</span>
                                                    <span x-text="flujo.estado_destino?.nombre || flujo.estado_destino" 
                                                          class="px-2 py-1 bg-gray-100 rounded text-sm"></span>
                                                </div>
                                                
                                                <!-- Fase -->
                                                <div class="flex items-center space-x-1">
                                                    <span class="text-xs text-gray-500">Fase:</span>
                                                    <span x-text="flujo.fase_origen?.nombre || flujo.fase_origen || 'Sin fase'" 
                                                          class="px-2 py-1 bg-gray-100 rounded text-sm"></span>
                                                    <span class="text-gray-400">→</span>
                                                    <span x-text="flujo.fase_destino?.nombre || flujo.fase_destino || 'Sin fase'" 
                                                          class="px-2 py-1 bg-gray-100 rounded text-sm"></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <p x-show="flujo.descripcion" class="mt-2 text-sm text-gray-600" x-text="flujo.descripcion"></p>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <button @click="editarFlujo(flujo)" 
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Editar
                                        </button>
                                        <button @click="eliminarFlujo(flujo.id)" 
                                                class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Modal de Crear/Editar Flujo (JS puro) -->
            <div id="modal-flujo" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
                <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900" id="modal-flujo-titulo">Crear Nuevo Flujo</h3>
                            <button onclick="window.ModalFlujos.close()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <form id="form-flujo" onsubmit="return window.ModalFlujos.handleSubmit(event)">
                            <div class="space-y-4">
                                <!-- Tipo de Transición fijo -->
                                <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                                    <div class="flex items-center">
                                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                        <span class="text-sm text-blue-800 font-medium">Tipo: Estado y Fase</span>
                                    </div>
                                    <p class="text-xs text-blue-600 mt-1">Todas las transiciones incluyen tanto estados como fases (opcionales)</p>
                                </div>

                                <!-- Estado Origen -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Estado Origen <span class="text-red-500">*</span>
                                    </label>
                                    <div id="estado-origen-container" class="flex flex-wrap gap-2"></div>
                                </div>

                                <!-- Fase Origen -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Fase Origen <span class="text-gray-500">(Opcional)</span>
                                    </label>
                                    <div id="fase-origen-container" class="flex flex-wrap gap-2"></div>
                                </div>

                                <!-- Estado Destino -->
                                <div id="estado-destino-wrapper" class="opacity-50 pointer-events-none">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Estado Destino <span class="text-red-500">*</span>
                                    </label>
                                    <div id="estado-destino-container" class="flex flex-wrap gap-2"></div>
                                </div>

                                <!-- Fase Destino -->
                                <div id="fase-destino-wrapper" class="opacity-50 pointer-events-none">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Fase Destino <span class="text-gray-500">(Opcional)</span>
                                    </label>
                                    <div id="fase-destino-container" class="flex flex-wrap gap-2"></div>
                                </div>

                                <!-- Descripción -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción (Opcional)</label>
                                    <textarea id="descripcion-flujo"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                              rows="3" placeholder="Describe esta transición..."></textarea>
                                </div>
                            </div>

                            <!-- Mensaje de validación -->
                            <div id="form-validation-msg" class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md hidden"></div>

                            <div class="flex justify-end space-x-3 mt-6">
                                <button type="button" onclick="window.ModalFlujos.close()" 
                                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Cancelar
                                </button>
                                <button id="btn-guardar-flujo" type="submit" class="px-4 py-2 text-white rounded-md text-sm font-medium transition-colors bg-green-600 hover:bg-green-700">
                                    Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Copiar Flujos -->
    <div id="copiar-flujos-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-xl p-6 w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-bold mb-4">Copiar Todos los Flujos de Ayuda</h3>
            
            <form id="copiar-flujos-form" onsubmit="window.submitCopiarFlujos(event)">
                <!-- Ayuda Origen (solo mostrar, no seleccionar) -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-800 mb-2">Ayuda Origen</h4>
                    <div class="flex items-center justify-between">
                        <span class="text-blue-900 font-medium" id="ayuda-origen-nombre-flujos"></span>
                        <span class="text-blue-600 text-sm" id="ayuda-origen-id-flujos"></span>
                    </div>
                </div>

                <!-- Ayudas Destino -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Ayudas Destino</label>
                    <div class="relative" id="search-container-flujos">
                        <input 
                            type="text" 
                            id="ayudas-destino-search-flujos"
                            placeholder="Buscar ayudas destino..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            onkeyup="filtrarAyudasDestinoFlujos()"
                            onfocus="mostrarListaAyudasDestinoFlujos()"
                        >
                        <div id="lista-ayudas-destino-flujos" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                            <!-- Se cargará dinámicamente -->
                        </div>
                    </div>
                    <div id="ayudas-destino-seleccionadas-flujos" class="mt-2 space-y-1">
                        <!-- Se mostrarán las ayudas seleccionadas -->
                    </div>
                </div>

                <!-- Opciones de copia -->
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Opciones de copia</h4>
                    <div class="space-y-2">
                        <label class="flex items-center space-x-2">
                            <input 
                                type="checkbox" 
                                id="sobrescribir-flujos" 
                                name="sobrescribir"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <span class="text-sm text-gray-700">Sobrescribir flujos existentes</span>
                        </label>
                        <p class="text-xs text-gray-500 ml-6">
                            Si está marcado, los flujos existentes en las ayudas destino serán reemplazados. 
                            Si no, se saltarán los flujos que ya existen.
                        </p>
                    </div>
                </div>

                <!-- Vista previa de los flujos origen -->
                <div id="vista-previa-flujos-origen" class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Flujos que se copiarán</h4>
                    <div id="contenido-vista-previa-flujos" class="border border-gray-300 rounded-lg p-4 max-h-40 overflow-y-auto bg-gray-50">
                        <!-- Se cargará dinámicamente -->
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button"
                        onclick="window.closeCopiarFlujosModal()"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit"
                        id="btn-copiar-flujos-submit"
                        class="px-6 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-200"
                        disabled
                    >
                        <i class="fas fa-copy mr-2"></i>
                        Copiar todos los flujos
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    window.ModalFlujos = (function() {
        let estados = [];
        let fases = [];
        let ayudaId = null;
        let modo = 'crear';
        let inicial = null;
        let onSavedCb = null;

        const state = {
            estado_origen: '',
            fase_origen: '',
            estado_destino: '',
            fase_destino: '',
            descripcion: ''
        };

        function qs(id) { return document.getElementById(id); }

        function renderBotones(container, items, getKey, getLabel, currentValue, onClick, activeClasses, inactiveClasses) {
            container.innerHTML = '';
            items.forEach(item => {
                const key = getKey(item);
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `${currentValue === key ? activeClasses : inactiveClasses} px-3 py-1 rounded-full text-sm`;
                btn.textContent = getLabel(item);
                btn.addEventListener('click', () => onClick(key));
                container.appendChild(btn);
            });
        }

        function render() {
            // Estado Origen
            renderBotones(
                qs('estado-origen-container'),
                estados,
                e => e.slug,
                e => e.nombre,
                state.estado_origen,
                key => {
                    state.estado_origen = key;
                    // Reset dependientes
                    if (!fases.some(f => f.slug === state.fase_origen && f.estado === key)) state.fase_origen = '';
                    state.estado_destino = '';
                    state.fase_destino = '';
                    render();
                },
                'bg-blue-600 text-white',
                'bg-gray-100 text-gray-700 hover:bg-gray-200'
            );

            // Fase Origen
            const fasesOrigen = fases.filter(f => f.estado === state.estado_origen);
            const faseOrigenContainer = qs('fase-origen-container');
            faseOrigenContainer.innerHTML = '';
            const btnSinFaseOrigen = document.createElement('button');
            btnSinFaseOrigen.type = 'button';
            btnSinFaseOrigen.className = `${!state.fase_origen ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'} px-3 py-1 rounded-full text-sm`;
            btnSinFaseOrigen.textContent = 'Sin fase';
            btnSinFaseOrigen.addEventListener('click', () => { state.fase_origen = ''; render(); });
            faseOrigenContainer.appendChild(btnSinFaseOrigen);
            renderBotones(
                faseOrigenContainer,
                fasesOrigen,
                f => f.slug,
                f => f.nombre,
                state.fase_origen,
                key => { state.fase_origen = key; render(); },
                'bg-green-600 text-white',
                'bg-gray-100 text-gray-700 hover:bg-gray-200'
            );

            // Destino wrappers habilitar/deshabilitar
            const destinoDisabled = !state.estado_origen;
            qs('estado-destino-wrapper').className = destinoDisabled ? 'opacity-50 pointer-events-none' : '';
            qs('fase-destino-wrapper').className = destinoDisabled ? 'opacity-50 pointer-events-none' : '';

            // Estado Destino
            renderBotones(
                qs('estado-destino-container'),
                estados,
                e => e.slug,
                e => e.nombre,
                state.estado_destino,
                key => {
                    state.estado_destino = key;
                    if (!fases.some(f => f.slug === state.fase_destino && f.estado === key)) state.fase_destino = '';
                    render();
                },
                'bg-blue-600 text-white',
                'bg-gray-100 text-gray-700 hover:bg-gray-200'
            );

            // Fase Destino
            const fasesDestino = fases.filter(f => f.estado === state.estado_destino);
            const faseDestinoContainer = qs('fase-destino-container');
            faseDestinoContainer.innerHTML = '';
            const btnSinFaseDestino = document.createElement('button');
            btnSinFaseDestino.type = 'button';
            btnSinFaseDestino.className = `${!state.fase_destino ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'} px-3 py-1 rounded-full text-sm`;
            btnSinFaseDestino.textContent = 'Sin fase';
            btnSinFaseDestino.addEventListener('click', () => { state.fase_destino = ''; render(); });
            faseDestinoContainer.appendChild(btnSinFaseDestino);
            renderBotones(
                faseDestinoContainer,
                fasesDestino,
                f => f.slug,
                f => f.nombre,
                state.fase_destino,
                key => { state.fase_destino = key; render(); },
                'bg-green-600 text-white',
                'bg-gray-100 text-gray-700 hover:bg-gray-200'
            );

            // Descripción
            qs('descripcion-flujo').value = state.descripcion || '';

            // Validación UI
            const valido = validar();
            const msg = qs('form-validation-msg');
            if (!valido && (state.estado_origen || state.estado_destino || state.fase_origen || state.fase_destino)) {
                msg.classList.remove('hidden');
                msg.innerHTML = '<div class="flex items-center"><i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i><span class="text-sm text-yellow-800">Los campos Estado Origen y Estado Destino son obligatorios.</span></div>';
            } else {
                msg.classList.add('hidden');
                msg.innerHTML = '';
            }
            qs('btn-guardar-flujo').disabled = !valido;
            qs('btn-guardar-flujo').className = `px-4 py-2 text-white rounded-md text-sm font-medium transition-colors ${valido ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed'}`;
        }

        function validar() {
            const estadoOrigenValido = state.estado_origen && state.estado_origen.trim() !== '';
            const estadoDestinoValido = state.estado_destino && state.estado_destino.trim() !== '';
            const faseOrigenValida = !state.fase_origen || state.fase_origen.trim() !== '';
            const faseDestinoValida = !state.fase_destino || state.fase_destino.trim() !== '';
            return estadoOrigenValido && estadoDestinoValido && faseOrigenValida && faseDestinoValida;
        }

        async function handleSubmit(event) {
            event.preventDefault();
            if (!validar()) return false;
            const url = (modo === 'editar' && inicial?.id) ? `/admin/flujos/${inicial.id}` : '{{ route('admin.flujos.store') }}';
            const method = (modo === 'editar' && inicial?.id) ? 'PUT' : 'POST';
            const payload = {
                tipo: 'ambos',
                estado_origen: state.estado_origen,
                estado_destino: state.estado_destino,
                fase_origen: state.fase_origen === '' ? null : state.fase_origen,
                fase_destino: state.fase_destino === '' ? null : state.fase_destino,
                descripcion: qs('descripcion-flujo').value || '',
                ayuda_id: ayudaId
            };
            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    close();
                    onSavedCb && onSavedCb();
                } else {
                    alert(data.message || 'Error al guardar el flujo');
                }
            } catch (e) {
                console.error('Error al guardar flujo:', e);
                alert('Error al guardar el flujo');
            }
            return false;
        }

        function open({ modo: m, estados: e, fases: f, ayudaId: a, onSaved, inicial: init }) {
            modo = m || 'crear';
            estados = e || [];
            fases = f || [];
            ayudaId = a;
            onSavedCb = onSaved;
            inicial = init || null;
            // Inicializar estado
            state.estado_origen = inicial?.estado_origen || '';
            state.fase_origen = inicial?.fase_origen || '';
            state.estado_destino = inicial?.estado_destino || '';
            state.fase_destino = inicial?.fase_destino || '';
            state.descripcion = inicial?.descripcion || '';
            qs('modal-flujo-titulo').textContent = modo === 'editar' ? 'Editar Flujo' : 'Crear Nuevo Flujo';
            qs('modal-flujo').style.display = 'block';
            render();
        }

        function close() {
            qs('modal-flujo').style.display = 'none';
        }

        return { open, close, handleSubmit };
    })();
    function flujoManager() {
        return {
            ayudas: {!! json_encode($ayudas) !!},
            estados: {!! json_encode($estados) !!},
            fases: {!! json_encode($fases) !!},
            ayudaSeleccionada: '',
            flujos: [],
            cargando: false,
            mostrarModal: false,
            flujoEditando: null,
            guardando: false,
            
            // Variables para el modal de copia
            todasLasAyudasFlujos: [],
            ayudasDestinoSeleccionadasFlujos: [],
            
            init() {
                // Debug: verificar datos cargados
                console.log('Estados cargados:', this.estados);
                console.log('Fases cargadas:', this.fases);
                console.log('Ayudas cargadas:', this.ayudas);
            },

            actualizarCamposRequeridos() {
                // Validar campos requeridos en tiempo real
                this.validarFormulario();
            },

            validarFormulario() {
                // Validar estados (obligatorios)
                const estadoOrigenValido = this.formulario.estado_origen && this.formulario.estado_origen.trim() !== '';
                const estadoDestinoValido = this.formulario.estado_destino && this.formulario.estado_destino.trim() !== '';
                
                const faseOrigenValida = !this.formulario.fase_origen || this.formulario.fase_origen.trim() !== '';
                const faseDestinoValida = !this.formulario.fase_destino || this.formulario.fase_destino.trim() !== '';
                
                return estadoOrigenValido && estadoDestinoValido && faseOrigenValida && faseDestinoValida;
            },

            esCampoValido(campo) {
                switch(campo) {
                    case 'estado_origen':
                    case 'estado_destino':
                        return this.formulario[campo] && this.formulario[campo].trim() !== '';
                    case 'fase_origen':
                    case 'fase_destino':
                        return !this.formulario[campo] || this.formulario[campo].trim() !== '';
                    default:
                        return true;
                }
            },
            formulario: {
                tipo: '',
                estado_origen: '',
                estado_destino: '',
                fase_origen: '',
                fase_destino: '',
                descripcion: ''
            },

            async cargarFlujos() {
                if (!this.ayudaSeleccionada) {
                    return;
                }

                this.cargando = true;
                try {
                    const response = await fetch(`{{ route('admin.flujos.por-ayuda') }}?ayuda_id=${this.ayudaSeleccionada}`);
                    const data = await response.json();
                    this.flujos = data.flujos || [];
                } catch (error) {
                    console.error('Error al cargar flujos:', error);
                    this.flujos = [];
                } finally {
                    this.cargando = false;
                }
            },

            mostrarFormularioCrear() {
                this.flujoEditando = null;
                window.ModalFlujos.open({
                    modo: 'crear',
                    estados: this.estados,
                    fases: this.fases,
                    ayudaId: this.ayudaSeleccionada,
                    onSaved: async () => { await this.cargarFlujos(); }
                });
            },

            editarFlujo(flujo) {
                this.flujoEditando = flujo;
                const estadoOrigen = flujo.estado_origen?.slug || flujo.estado_origen || '';
                const estadoDestino = flujo.estado_destino?.slug || flujo.estado_destino || '';
                const faseOrigen = flujo.fase_origen?.slug || flujo.fase_origen || '';
                const faseDestino = flujo.fase_destino?.slug || flujo.fase_destino || '';
                window.ModalFlujos.open({
                    modo: 'editar',
                    estados: this.estados,
                    fases: this.fases,
                    ayudaId: this.ayudaSeleccionada,
                    inicial: {
                        estado_origen: estadoOrigen,
                        estado_destino: estadoDestino,
                        fase_origen: faseOrigen,
                        fase_destino: faseDestino,
                        descripcion: flujo.descripcion || '',
                        id: flujo.id
                    },
                    onSaved: async () => { await this.cargarFlujos(); }
                });
            },

            cerrarModal() {
                window.ModalFlujos.close();
                this.flujoEditando = null;
            },

            async guardarFlujo() {},

            async eliminarFlujo(flujo) {
                if (!confirm('¿Estás seguro de que quieres eliminar este flujo?')) {
                    return;
                }

                try {
                    const response = await fetch(`/admin/flujos/${flujo.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        await this.cargarFlujos();
                    } else {
                        alert(result.message || 'Error al eliminar el flujo');
                    }
                } catch (error) {
                    console.error('Error al eliminar flujo:', error);
                    alert('Error al eliminar el flujo');
                }
            },

            // Funciones para el modal de copia
            async openCopiarModal() {
                if (!this.ayudaSeleccionada) {
                    alert('❌ Debes seleccionar una ayuda primero');
                    return;
                }
                
                document.getElementById('copiar-flujos-modal').style.display = 'flex';
                this.resetearModalCopiarFlujos();
                await this.cargarAyudasParaCopiarFlujos();
                this.configurarAyudaOrigenFlujos();
                
                setTimeout(() => {
                    this.mostrarListaAyudasDestinoFlujos();
                }, 100);
            },

            resetearModalCopiarFlujos() {
                this.ayudasDestinoSeleccionadasFlujos = [];
                document.getElementById('ayudas-destino-search-flujos').value = '';
                document.getElementById('sobrescribir-flujos').checked = false;
                document.getElementById('contenido-vista-previa-flujos').innerHTML = '';
                this.verificarFormularioCompletoFlujos();
            },

            async cargarAyudasParaCopiarFlujos() {
                try {
                    const response = await fetch('/admin/flujos/ayudas-para-copiar', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        this.todasLasAyudasFlujos = data.ayudas;
                        this.actualizarListasAyudasFlujos();
                    } else {
                        alert('❌ Error al cargar las ayudas');
                    }
                } catch (error) {
                    console.error('Error al cargar ayudas:', error);
                    alert('❌ Error al cargar las ayudas');
                }
            },

            configurarAyudaOrigenFlujos() {
                const ayudaNombre = this.ayudas.find(a => a.id == this.ayudaSeleccionada)?.nombre || 'Ayuda no encontrada';
                document.getElementById('ayuda-origen-nombre-flujos').textContent = ayudaNombre;
                document.getElementById('ayuda-origen-id-flujos').textContent = `ID: ${this.ayudaSeleccionada}`;
                
                // Cargar vista previa de todos los flujos de la ayuda
                this.cargarVistaPreviaFlujosOrigen(this.ayudaSeleccionada);
            },

            actualizarListasAyudasFlujos() {
                const listaDestino = document.getElementById('lista-ayudas-destino-flujos');
                const container = document.getElementById('ayudas-list-container');
                
                const scrollTop = container ? container.scrollTop : 0;
                
                // Mostrar todas las ayudas excepto la ayuda origen, pero marcar las ya seleccionadas
                const ayudasDisponiblesDestino = this.todasLasAyudasFlujos.filter(ayuda => 
                    ayuda.id !== parseInt(this.ayudaSeleccionada)
                );

                const totalAyudas = ayudasDisponiblesDestino.length;
                const seleccionadas = this.ayudasDestinoSeleccionadasFlujos.length;
                const todasSeleccionadas = seleccionadas === totalAyudas && totalAyudas > 0;

                listaDestino.innerHTML = `
                    <!-- Header con checkboxes de selección masiva -->
                    <div class="sticky top-0 bg-white border-b border-gray-200 p-3 mb-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        id="select-all-checkbox"
                                        ${todasSeleccionadas ? 'checked' : ''}
                                        onchange="window.toggleSelectAllFlujos(this.checked)"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                    >
                                    <span class="text-sm font-medium text-gray-700">
                                        Seleccionar todas (${seleccionadas}/${totalAyudas})
                                    </span>
                                </label>
                            </div>
                            <div class="flex space-x-2">
                                <button 
                                    type="button"
                                    onclick="window.deseleccionarTodasLasAyudasFlujos()"
                                    class="text-xs bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded transition-colors"
                                    ${seleccionadas === 0 ? 'disabled class="bg-gray-400 cursor-not-allowed"' : ''}
                                >
                                    <i class="fas fa-times mr-1"></i>Limpiar selección
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lista de ayudas con checkboxes -->
                    <div class="space-y-1 max-h-60 overflow-y-auto" id="ayudas-list-container">
                        ${ayudasDisponiblesDestino.map(ayuda => {
                            const yaSeleccionada = this.ayudasDestinoSeleccionadasFlujos.some(sel => sel.id === ayuda.id);
                            
                            return `
                                <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg cursor-pointer border ${yaSeleccionada ? 'bg-green-50 border-green-200' : 'border-gray-200'}" 
                                     data-ayuda-id="${ayuda.id}">
                                    <input 
                                        type="checkbox" 
                                        class="ayuda-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                        data-ayuda-id="${ayuda.id}"
                                        data-ayuda-nombre="${ayuda.nombre}"
                                        ${yaSeleccionada ? 'checked' : ''}
                                        onchange="window.toggleAyudaFlujos(${ayuda.id}, '${ayuda.nombre.replace(/'/g, "\\'")}', this.checked)"
                                    >
                                    <div class="flex-1">
                                        <span class="text-sm font-medium ${yaSeleccionada ? 'text-green-800' : 'text-gray-900'}">
                                            ${ayuda.nombre}
                                        </span>
                                    </div>
                                    ${yaSeleccionada ? '<i class="fas fa-check text-green-600"></i>' : ''}
                                </div>
                            `;
                        }).join('')}
                    </div>
                `;
                
                setTimeout(() => {
                    const newContainer = document.getElementById('ayudas-list-container');
                    if (newContainer) {
                        newContainer.scrollTop = scrollTop;
                    }
                }, 10);
            },

            seleccionarAyudaDestinoFlujos(id, nombre) {
                const yaSeleccionada = this.ayudasDestinoSeleccionadasFlujos.some(ayuda => ayuda.id === id);
                
                if (yaSeleccionada) {
                    this.quitarAyudaDestinoFlujos(id);
                } else {
                    this.ayudasDestinoSeleccionadasFlujos.push({ id, nombre });
                    this.actualizarListasAyudasFlujos();
                    this.mostrarAyudasDestinoSeleccionadasFlujos();
                    this.verificarFormularioCompletoFlujos();
                }
            },

            quitarAyudaDestinoFlujos(id) {
                this.ayudasDestinoSeleccionadasFlujos = this.ayudasDestinoSeleccionadasFlujos.filter(ayuda => ayuda.id !== id);
                this.actualizarListasAyudasFlujos();
                this.mostrarAyudasDestinoSeleccionadasFlujos();
                this.verificarFormularioCompletoFlujos();
            },

            mostrarAyudasDestinoSeleccionadasFlujos() {
                const container = document.getElementById('ayudas-destino-seleccionadas-flujos');
                
                if (this.ayudasDestinoSeleccionadasFlujos.length === 0) {
                    container.innerHTML = '<p class="text-gray-500 text-sm">No hay ayudas seleccionadas</p>';
                    return;
                }
                
                container.innerHTML = `
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Ayudas seleccionadas (${this.ayudasDestinoSeleccionadasFlujos.length}):</span>
                        <button 
                            type="button" 
                            onclick="window.limpiarTodasLasSeleccionesFlujos()"
                            class="text-xs text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50"
                            title="Limpiar todas las selecciones"
                        >
                            <i class="fas fa-trash mr-1"></i>Limpiar todo
                        </button>
                    </div>
                    ${this.ayudasDestinoSeleccionadasFlujos.map(ayuda => `
                        <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-lg p-2 mb-1">
                            <span class="text-green-800 font-medium">${ayuda.nombre}</span>
                            <button 
                                type="button" 
                                onclick="window.quitarAyudaDestinoFlujos(${ayuda.id})"
                                class="text-green-600 hover:text-green-800 ml-2 p-1 rounded-full hover:bg-green-100"
                                title="Quitar selección"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `).join('')}
                `;
            },

            verificarFormularioCompletoFlujos() {
                const tieneDestinos = this.ayudasDestinoSeleccionadasFlujos.length > 0;
                
                document.getElementById('btn-copiar-flujos-submit').disabled = !tieneDestinos;
            },

            async cargarVistaPreviaFlujosOrigen(ayudaId) {
                try {
                    const response = await fetch(`/admin/flujos/vista-previa?ayuda_id=${ayudaId}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        this.mostrarVistaPreviaFlujos(data.flujos);
                    } else {
                        alert('❌ Error al cargar la vista previa');
                    }
                } catch (error) {
                    console.error('Error al cargar vista previa:', error);
                    alert('❌ Error al cargar la vista previa');
                }
            },

            mostrarVistaPreviaFlujos(flujos) {
                const container = document.getElementById('contenido-vista-previa-flujos');
                
                if (flujos.length === 0) {
                    container.innerHTML = '<p class="text-gray-500 text-center">No hay flujos para copiar</p>';
                    return;
                }

                container.innerHTML = flujos.map(flujo => `
                    <div class="mb-2 p-2 bg-white rounded border">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium">${flujo.estado_origen?.nombre || flujo.estado_origen}</span>
                            <span class="text-gray-400">→</span>
                            <span class="text-sm font-medium">${flujo.estado_destino?.nombre || flujo.estado_destino}</span>
                            ${flujo.fase_origen || flujo.fase_destino ? `
                                <span class="text-gray-400">|</span>
                                <span class="text-xs text-gray-600">${flujo.fase_origen?.nombre || flujo.fase_origen || 'Sin fase'}</span>
                                <span class="text-gray-400">→</span>
                                <span class="text-xs text-gray-600">${flujo.fase_destino?.nombre || flujo.fase_destino || 'Sin fase'}</span>
                            ` : ''}
                        </div>
                        ${flujo.descripcion ? `<p class="text-xs text-gray-500 mt-1">${flujo.descripcion}</p>` : ''}
                    </div>
                `).join('');
            },

            async submitCopiarFlujos(event) {
                event.preventDefault();
                
                const formData = new FormData(event.target);
                const sobrescribir = formData.get('sobrescribir') === 'on';
                const ayudasDestinoIds = this.ayudasDestinoSeleccionadasFlujos.map(ayuda => ayuda.id);
                
                if (!this.ayudaSeleccionada || ayudasDestinoIds.length === 0) {
                    alert('❌ Debes seleccionar al menos una ayuda destino');
                    return;
                }

                const btnSubmit = document.getElementById('btn-copiar-flujos-submit');
                const textoOriginal = btnSubmit.innerHTML;
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Copiando...';

                try {
                    const response = await fetch('/admin/flujos/copiar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            ayuda_origen_id: parseInt(this.ayudaSeleccionada),
                            ayudas_destino_ids: ayudasDestinoIds,
                            sobrescribir: sobrescribir
                        })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        alert('✅ ' + data.message);
                        this.closeCopiarFlujosModal();
                        await this.cargarFlujos(); // Recargar la lista de flujos
                    } else {
                        alert('❌ ' + (data.message || 'Error al copiar los flujos'));
                    }
                } catch (error) {
                    console.error('Error al copiar flujos:', error);
                    alert('❌ Error al copiar los flujos');
                } finally {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = textoOriginal;
                }
            },

            closeCopiarFlujosModal() {
                document.getElementById('copiar-flujos-modal').style.display = 'none';
                this.resetearModalCopiarFlujos();
            },

            limpiarTodasLasSeleccionesFlujos() {
                this.ayudasDestinoSeleccionadasFlujos = [];
                this.actualizarListasAyudasFlujos();
                this.mostrarAyudasDestinoSeleccionadasFlujos();
                this.verificarFormularioCompletoFlujos();
            },

            seleccionarTodasLasAyudasFlujos() {
                const ayudasDisponibles = this.todasLasAyudasFlujos.filter(ayuda => 
                    ayuda.id !== parseInt(this.ayudaSeleccionada)
                );
                
                ayudasDisponibles.forEach(ayuda => {
                    if (!this.ayudasDestinoSeleccionadasFlujos.some(sel => sel.id === ayuda.id)) {
                        this.ayudasDestinoSeleccionadasFlujos.push({ id: ayuda.id, nombre: ayuda.nombre });
                    }
                });
                
                this.actualizarListasAyudasFlujos();
                this.mostrarAyudasDestinoSeleccionadasFlujos();
                this.verificarFormularioCompletoFlujos();
            },

            deseleccionarTodasLasAyudasFlujos() {
                this.ayudasDestinoSeleccionadasFlujos = [];
                this.actualizarListasAyudasFlujos();
                this.mostrarAyudasDestinoSeleccionadasFlujos();
                this.verificarFormularioCompletoFlujos();
            },

            toggleAyudaFlujos(id, nombre, checked) {
                
                if (checked) {
                    if (!this.ayudasDestinoSeleccionadasFlujos.some(ayuda => ayuda.id === id)) {
                        this.ayudasDestinoSeleccionadasFlujos.push({ id, nombre });
                    }
                } else {
                    this.ayudasDestinoSeleccionadasFlujos = this.ayudasDestinoSeleccionadasFlujos.filter(ayuda => ayuda.id !== id);
                }
                
                this.actualizarElementoAyuda(id, checked);
                this.actualizarHeaderSeleccion();
                this.mostrarAyudasDestinoSeleccionadasFlujos();
                this.verificarFormularioCompletoFlujos();
            },

            actualizarElementoAyuda(id, checked) {
                const container = document.getElementById('ayudas-list-container');
                if (!container) return;
                
                const elementos = container.querySelectorAll('[data-ayuda-id]');
                elementos.forEach(elemento => {
                    if (parseInt(elemento.getAttribute('data-ayuda-id')) === id) {
                        const checkbox = elemento.querySelector('input[type="checkbox"]');
                        const span = elemento.querySelector('span');
                        const icono = elemento.querySelector('i');
                        
                        if (checkbox) checkbox.checked = checked;
                        
                        if (checked) {
                            elemento.className = elemento.className.replace('border-gray-200', 'bg-green-50 border-green-200');
                            if (span) span.className = span.className.replace('text-gray-900', 'text-green-800');
                            if (!icono) {
                                const iconoElement = document.createElement('i');
                                iconoElement.className = 'fas fa-check text-green-600';
                                elemento.appendChild(iconoElement);
                            }
                        } else {
                            elemento.className = elemento.className.replace('bg-green-50 border-green-200', 'border-gray-200');
                            if (span) span.className = span.className.replace('text-green-800', 'text-gray-900');
                            if (icono) icono.remove();
                        }
                    }
                });
            },

            actualizarHeaderSeleccion() {
                const totalAyudas = this.todasLasAyudasFlujos.filter(ayuda => 
                    ayuda.id !== parseInt(this.ayudaSeleccionada)
                ).length;
                const seleccionadas = this.ayudasDestinoSeleccionadasFlujos.length;
                const todasSeleccionadas = seleccionadas === totalAyudas && totalAyudas > 0;
                
                const checkboxAll = document.getElementById('select-all-checkbox');
                const contador = document.querySelector('#select-all-checkbox + span');
                
                if (checkboxAll) checkboxAll.checked = todasSeleccionadas;
                if (contador) contador.textContent = `Seleccionar todas (${seleccionadas}/${totalAyudas})`;
            },

            toggleSelectAllFlujos(checked) {
                if (checked) {
                    // Seleccionar todas las ayudas disponibles
                    const ayudasDisponibles = this.todasLasAyudasFlujos.filter(ayuda => 
                        ayuda.id !== parseInt(this.ayudaSeleccionada)
                    );
                    
                    this.ayudasDestinoSeleccionadasFlujos = ayudasDisponibles.map(ayuda => ({
                        id: ayuda.id,
                        nombre: ayuda.nombre
                    }));
                } else {
                    // Deseleccionar todas
                    this.ayudasDestinoSeleccionadasFlujos = [];
                }
                
                this.actualizarListasAyudasFlujos();
                this.mostrarAyudasDestinoSeleccionadasFlujos();
                this.verificarFormularioCompletoFlujos();
            },

            mostrarListaAyudasDestinoFlujos() {
                const lista = document.getElementById('lista-ayudas-destino-flujos');
                if (lista) {
                    lista.classList.remove('hidden');
                }
            },

            ocultarListaAyudasDestinoFlujos() {
                const lista = document.getElementById('lista-ayudas-destino-flujos');
                if (lista) {
                    lista.classList.add('hidden');
                }
            },

        };
    }

    // Funciones globales para el modal de copia
    window.seleccionarAyudaDestinoFlujos = function(id, nombre) {
        const element = document.querySelector('main[x-data="flujoManager()"]');
        if (element) {
            const component = Alpine.$data(element);
            if (component && component.seleccionarAyudaDestinoFlujos) {
                component.seleccionarAyudaDestinoFlujos(id, nombre);
            }
        }
    }

    window.quitarAyudaDestinoFlujos = function(id) {
        const element = document.querySelector('main[x-data="flujoManager()"]');
        if (element) {
            const component = Alpine.$data(element);
            if (component && component.quitarAyudaDestinoFlujos) {
                component.quitarAyudaDestinoFlujos(id);
            }
        }
    }

    window.closeCopiarFlujosModal = function() {
        const element = document.querySelector('main[x-data="flujoManager()"]');
        if (element) {
            const component = Alpine.$data(element);
            if (component && component.closeCopiarFlujosModal) {
                component.closeCopiarFlujosModal();
            }
        }
    }

    window.submitCopiarFlujos = function(event) {
        const element = document.querySelector('main[x-data="flujoManager()"]');
        if (element) {
            const component = Alpine.$data(element);
            if (component && component.submitCopiarFlujos) {
                component.submitCopiarFlujos(event);
            }
        }
    }

    window.limpiarTodasLasSeleccionesFlujos = function() {
        const element = document.querySelector('main[x-data="flujoManager()"]');
        if (element) {
            const component = Alpine.$data(element);
            if (component && component.limpiarTodasLasSeleccionesFlujos) {
                component.limpiarTodasLasSeleccionesFlujos();
            }
        }
    }

    window.seleccionarTodasLasAyudasFlujos = function() {
        const element = document.querySelector('main[x-data="flujoManager()"]');
        if (element) {
            const component = Alpine.$data(element);
            if (component && component.seleccionarTodasLasAyudasFlujos) {
                component.seleccionarTodasLasAyudasFlujos();
            }
        }
    }

    window.deseleccionarTodasLasAyudasFlujos = function() {
        const element = document.querySelector('main[x-data="flujoManager()"]');
        if (element) {
            const component = Alpine.$data(element);
            if (component && component.deseleccionarTodasLasAyudasFlujos) {
                component.deseleccionarTodasLasAyudasFlujos();
            }
        }
    }

    window.toggleAyudaFlujos = function(id, nombre, checked) {
        const element = document.querySelector('main[x-data="flujoManager()"]');
        if (element) {
            const component = Alpine.$data(element);
            if (component && component.toggleAyudaFlujos) {
                component.toggleAyudaFlujos(id, nombre, checked);
            }
        }
    }

    window.toggleSelectAllFlujos = function(checked) {
        const element = document.querySelector('main[x-data="flujoManager()"]');
        if (element) {
            const component = Alpine.$data(element);
            if (component && component.toggleSelectAllFlujos) {
                component.toggleSelectAllFlujos(checked);
            }
        }
    }

    // Funciones para búsqueda y filtrado
    function filtrarAyudasDestinoFlujos() {
        const searchTerm = document.getElementById('ayudas-destino-search-flujos').value.toLowerCase();
        const labels = document.querySelectorAll('#lista-ayudas-destino-flujos label');
        
        labels.forEach(label => {
            const nombre = label.querySelector('span').textContent.toLowerCase();
            if (nombre.includes(searchTerm)) {
                label.style.display = 'flex';
            } else {
                label.style.display = 'none';
            }
        });
    }

    function mostrarListaAyudasDestinoFlujos() {
        document.getElementById('lista-ayudas-destino-flujos').classList.remove('hidden');
    }

    function ocultarListaAyudasDestinoFlujos() {
        setTimeout(() => {
            document.getElementById('lista-ayudas-destino-flujos').classList.add('hidden');
        }, 200);
    }

    document.addEventListener('click', function(event) {
        const searchContainer = document.getElementById('search-container-flujos');
        const lista = document.getElementById('lista-ayudas-destino-flujos');
        
        if (searchContainer && lista && !searchContainer.contains(event.target)) {
            lista.classList.add('hidden');
        }
    });
</script>

</main>

</body>
</html>
