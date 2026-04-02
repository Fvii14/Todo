<!-- MODAL centrado -->
<div x-show="open" x-transition.opacity
    class="fixed inset-0 z-[100] flex items-center justify-center" role="dialog" aria-modal="true"
    aria-labelledby="dlg-title" @keydown.escape.prevent.stop="open = false"
    x-effect="document.body.style.overflow = open ? 'hidden' : ''">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50" @click="open = false"></div>

    <!-- Panel del modal -->
    <div x-ref="panel" x-transition tabindex="-1"
        class="relative z-[101] w-[95vw] sm:w-[1200px] xl:w-[1400px] max-h-[90vh] bg-white rounded-2xl shadow-2xl overflow-y-auto flex flex-col"
        @click.stop>

        <!-- === TU HEADER AQUÍ (pegado tal cual, solo añado sticky) === -->
        <header class="px-6 py-4 border-b flex justify-between items-center sticky top-0 bg-white">
            <div class="flex items-center space-x-4">
                <div>
                    <h3 id="dlg-title" class="text-xl font-semibold">
                        Tramitaciones <span x-text="detalle.codigo"></span>
                    </h3>
                    <p class="text-gray-600">
                        Usuario: <span x-text="detalle.user.email">test2@gmail.com</span>
                    </p>
                </div>

            </div>
            <div class="flex items-center space-x-3">
                <div class="text-right mr-2 hidden sm:block">
                    <div class="text-xs text-gray-500 mb-1">Estado actual (OPx)</div>
                    <div class="flex flex-wrap gap-1 justify-end min-h-[1.5rem]">
                        <template x-for="(codigo, index) in (detalle && detalle.estados_opx || [])"
                            :key="codigo ? codigo : 'opx-header-' + index">
                            <span
                                class="text-xs px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700"
                                x-text="codigo"></span>
                        </template>
                        <span
                            x-show="!detalle || !detalle.estados_opx || detalle.estados_opx.length === 0"
                            class="text-xs text-gray-400 italic">(ninguno)</span>
                    </div>
                </div>
                <button
                    @click="window.flowModal && window.flowModal.open(detalle && detalle.id, detalle && detalle.estados_opx || [])"
                    class="bg-[#54debd] hover:bg-[#368e79] text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">
                    Cambiar estado
                </button>
            </div>

            <button @click="open = false" class="text-gray-500 hover:text-gray-800 text-2xl"
                aria-label="Cerrar">×</button>
        </header>

        <script>
            function siguientePasoDataModal(id, estadoInicial, faseInicial, faseNombre, ayudaSlug) {
                return {
                    estado: estadoInicial,
                    fase: faseInicial,
                    faseNombre: faseNombre,
                    ayuda: ayudaSlug,
                    cargandoFlujos: false,
                    mostrarModalFlujos: false,
                    flujosDisponibles: [],
                    mostrarDeshacer: false,
                    tiempoRestante: 0,
                    estadoAnterior: null,
                    faseAnterior: null,
                    faseAnteriorNombre: null,
                    timerDeshacer: null,
                    mostrarMensaje: false,
                    mensajeTexto: '',
                    mensajeTipo: 'success',

                    async mostrarFlujos() {
                        if (!id) {
                            alert('Error: No se puede cargar los estados. ID no disponible.');
                            return;
                        }
                        this.cargandoFlujos = true;
                        try {
                            const response = await fetch(`/contrataciones/${id}/flujos-disponibles`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name=csrf-token]').content
                                }
                            });
                            if (!response.ok) throw new Error('Error al cargar los estados');
                            const data = await response.json();
                            this.flujosDisponibles = data.estados || [];
                            this.mostrarModalFlujos = true;
                        } catch (error) {
                            alert('Error al cargar los estados disponibles');
                        } finally {
                            this.cargandoFlujos = false;
                        }
                    },

                    async aplicarTransicion(estadoItem) {
                        if (!id) {
                            alert('Error: No se puede cambiar el estado. ID no disponible.');
                            return;
                        }
                        const codigo = estadoItem.codigo || estadoItem.slug;
                        if (!codigo) return;
                        try {
                            const response = await fetch(`/contrataciones/${id}/estados-opx`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name=csrf-token]').content
                                },
                                body: JSON.stringify({
                                    codigos: [codigo],
                                    replace: false
                                })
                            });
                            if (!response.ok) {
                                const errorData = await response.json();
                                throw new Error(errorData.message || errorData.error ||
                                    'Error al cambiar el estado');
                            }
                            const data = await response.json();
                            this.mostrarModalFlujos = false;
                            this.mostrarMensajeExito('Estado OPx añadido correctamente');
                            this.$dispatch('refresh-detalle');
                        } catch (error) {
                            alert(error.message || 'Error al cambiar el estado');
                        }
                    },

                    cerrarModalFlujos() {
                        this.mostrarModalFlujos = false;
                        this.flujosDisponibles = [];
                    },

                    mostrarOpcionDeshacer(estadoAnterior, faseAnterior, faseAnteriorNombre) {
                        this.estadoAnterior = estadoAnterior;
                        this.faseAnterior = faseAnterior;
                        this.faseAnteriorNombre = faseAnteriorNombre;
                        this.mostrarDeshacer = true;
                        this.tiempoRestante = 30;
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
                        setTimeout(() => {
                            this.mostrarMensaje = false;
                        }, 3000);
                    },
                    mostrarMensajeError(texto) {
                        this.mensajeTexto = texto;
                        this.mensajeTipo = 'error';
                        this.mostrarMensaje = true;
                        setTimeout(() => {
                            this.mostrarMensaje = false;
                        }, 5000);
                    }
                };
            }
        </script>

        <script>
            (function() {
                class FlowModalManager {
                    constructor() {
                        this.el = null;
                        this.build();
                    }
                    build() {
                        if (this.el) return;
                        const html = `
                        <div id="flow-modal-overlay" class="fixed inset-0 hidden z-[9999]">
                            <div class="absolute inset-0 bg-black bg-opacity-50" data-flow-close></div>
                            <div class="relative z-[10000] max-w-lg w-[90vw] mx-auto mt-[10vh] bg-white rounded-lg shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
                                <div class="p-4 border-b flex items-center justify-between flex-shrink-0">
                                    <h3 class="text-lg font-semibold">Estados OPx</h3>
                                    <button class="text-gray-500 hover:text-gray-700 text-2xl leading-none" data-flow-close>&times;</button>
                                </div>
                                <div class="p-4 overflow-y-auto flex-1" id="flow-modal-body">
                                    <p class="text-sm text-gray-600 mb-3">Puedes marcar <strong>uno o varios</strong> estados según corresponda. La contratación puede tener múltiples estados a la vez.</p>
                                    <div class="text-center text-gray-500 py-6" id="flow-loading">Cargando...</div>
                                    <form id="flow-form" class="space-y-2 hidden"></form>
                                    <div class="text-center text-gray-500 py-6 hidden" id="flow-empty">No hay estados disponibles</div>
                                </div>
                                <div class="p-4 border-t flex-shrink-0 flex gap-2">
                                    <button type="button" id="flow-save" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">Guardar</button>
                                    <button type="button" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm" data-flow-close>Cerrar</button>
                                </div>
                            </div>
                        </div>`;
                        const wrapper = document.createElement('div');
                        wrapper.innerHTML = html;
                        this.el = wrapper.firstElementChild;
                        document.body.appendChild(this.el);
                        this.el.querySelectorAll('[data-flow-close]').forEach(b => b
                            .addEventListener('click', () => this.close()));
                        this.formEl = this.el.querySelector('#flow-form');
                        this.loadingEl = this.el.querySelector('#flow-loading');
                        this.emptyEl = this.el.querySelector('#flow-empty');
                        this.el.querySelector('#flow-save').addEventListener('click', () => this
                            .save());
                    }
                    show() {
                        this.el.classList.remove('hidden');
                    }
                    close() {
                        this.el.classList.add('hidden');
                    }
                    async open(contratacionId, estadosOpxActuales = []) {
                        if (!contratacionId) {
                            alert('Error: ID no disponible');
                            return;
                        }
                        this.currentId = contratacionId;
                        this.estadosOpxActuales = Array.isArray(estadosOpxActuales) ?
                            estadosOpxActuales : [];
                        this.loadingEl.classList.remove('hidden');
                        this.emptyEl.classList.add('hidden');
                        this.formEl.classList.add('hidden');
                        this.formEl.innerHTML = '';
                        this.show();
                        try {
                            const res = await fetch(
                                `/contrataciones/${contratacionId}/flujos-disponibles`, {
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name=csrf-token]').content
                                    }
                                });
                            if (!res.ok) throw new Error('Error al cargar estados');
                            const data = await res.json();
                            const estados = data.estados || [];
                            this.loadingEl.classList.add('hidden');
                            if (estados.length === 0) {
                                this.emptyEl.classList.remove('hidden');
                                return;
                            }
                            const groupColors = {
                                'OP1': 'bg-blue-50 border-blue-200',
                                'OP2': 'bg-amber-50 border-amber-200',
                                'OP3': 'bg-green-50 border-green-200',
                                'OP4': 'bg-purple-50 border-purple-200',
                                'OP5': 'bg-gray-50 border-gray-200'
                            };
                            this.formEl.classList.remove('hidden');
                            this.formEl.innerHTML = estados.map(e => {
                                const checked = this.estadosOpxActuales.includes(e.codigo);
                                const groupClass = groupColors[e.grupo] ||
                                    'bg-gray-50 border-gray-200';
                                return `<label class="flex items-center gap-3 p-3 rounded-lg border ${groupClass} cursor-pointer hover:opacity-90">
                                    <input type="checkbox" name="estado_opx" value="${e.codigo.replace(/"/g, '&quot;')}" ${checked ? 'checked' : ''} class="rounded border-gray-300">
                                    <div>
                                        <span class="font-medium">${e.codigo}</span>
                                        <span class="text-sm text-gray-600 ml-1">(${e.grupo})</span>
                                    </div>
                                </label>`;
                            }).join('');
                        } catch (e) {
                            this.loadingEl.classList.add('hidden');
                            this.emptyEl.classList.remove('hidden');
                        }
                    }
                    getSelectedCodigos() {
                        return Array.from(this.formEl.querySelectorAll(
                            'input[name="estado_opx"]:checked')).map(inp => inp.value);
                    }
                    async save() {
                        const codigos = this.getSelectedCodigos();
                        try {
                            const res = await fetch(
                                `/contrataciones/${this.currentId}/estados-opx`, {
                                    method: 'PATCH',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name=csrf-token]').content
                                    },
                                    body: JSON.stringify({
                                        codigos: codigos,
                                        replace: true
                                    })
                                });
                            if (!res.ok) {
                                const err = await res.json().catch(() => ({}));
                                throw new Error(err.message || err.error ||
                                    'Error al guardar estados');
                            }
                            this.close();
                            window.dispatchEvent(new CustomEvent('refresh-detalle'));
                        } catch (e) {
                            alert(e.message || 'Error al guardar estados');
                        }
                    }
                }
                window.flowModal = new FlowModalManager();
            })();
        </script>

        <!-- Tabs nav -->
        <nav class="border-b border-gray-200 flex space-x-4 px-6">
            <button @click="tab='resumen'"
                :class="tab === 'resumen' ?
                    'border-b-2 border-blue-600 text-blue-600' :
                    'text-gray-600'"
                class="py-3 px-4 font-medium">Resumen</button>
            <button @click="tab='datos'"
                :class="tab === 'datos' ?
                    'border-b-2 border-blue-600 text-blue-600' :
                    'text-gray-600'"
                class="py-3 px-4 font-medium">Datos</button>
            <button @click="tab='documentos'"
                :class="tab === 'documentos' ?
                    'border-b-2 border-blue-600 text-blue-600' :
                    'text-gray-600'"
                class="py-3 px-4 font-medium">Documentos</button>
            <button x-show="ayudaId1 && ayudaId2 && otrasContrataciones.length > 0"
                @click="tab='otras-contrataciones'"
                :class="tab === 'otras-contrataciones' ?
                    'border-b-2 border-blue-600 text-blue-600' :
                    'text-gray-600'"
                class="py-3 px-4 font-medium">Otras contrataciones (<span
                    x-text="otrasContrataciones.length"></span>)</button>
        </nav>

        {{-- Cuerpo del Modal (contenido de las pestañas) --}}
        <div class="px-6 py-4 flex-1 overflow-y-auto space-y-6">
            {{-- Resumen --}}
            <div x-show="tab==='resumen'" class="space-y-4" x-data="{
                motivosSeleccionados: [],
                notasMotivos: {},
                mostrarModalCrearMotivo: false,
                nuevoMotivo: {
                    descripcion: '',
                    motivo: 'Padrón',
                    document_id: null
                },
                async eliminarMotivo(motivoId) {
                    if (!detalle || !detalle.id) {
                        alert('Error: No hay contratación seleccionada');
                        return;
                    }
                    if (!confirm('¿Seguro que quieres eliminar este motivo de subsanación?')) {
                        return;
                    }
                    try {
                        const response = await fetch('/contrataciones/' + detalle.id + '/motivos-subsanacion/' + motivoId, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            alert('✅ Motivo eliminado');
                            this.$dispatch('refresh-detalle');
                        } else {
                            alert('❌ No se pudo eliminar: ' + (data.message || 'Error desconocido'));
                        }
                    } catch (e) {
                        alert('❌ Error eliminando el motivo');
                    }
                },
                documentosDisponibles: [],
                guardandoMotivo: false,
            
                init() {
                    // Cargar documentos disponibles
                    this.cargarDocumentosDisponibles();
            
                    // Inicializar motivos seleccionados cuando detalle esté disponible
                    this.$watch('detalle', (value) => {
                        if (value && value.motivos_subsanacion_seleccionados) {
                            this.motivosSeleccionados = value.motivos_subsanacion_seleccionados.map(m => m.motivo_id);
                            // Cargar las notas existentes
                            this.notasMotivos = {};
                            value.motivos_subsanacion_seleccionados.forEach(m => {
                                if (m.nota) {
                                    this.notasMotivos[m.motivo_id] = m.nota;
                                }
                            });
                        } else {
                            this.motivosSeleccionados = [];
                            this.notasMotivos = {};
                        }
                    });
                },
            
            
                async cargarDocumentosDisponibles() {
                    try {
                        const response = await fetch('/api/documentos-disponibles', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();
                        this.documentosDisponibles = data.documentos || [];
                    } catch (error) {
                        this.documentosDisponibles = [];
                    }
                },
            
                async guardarMotivosSubsanacion() {
                    if (!detalle || !detalle.id) {
                        alert('Error: No hay contratación seleccionada');
                        return;
                    }
            
                    try {
                        const response = await fetch('/contrataciones/' + detalle.id + '/motivos-subsanacion', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                motivos: this.motivosSeleccionados.map(motivoId => ({
                                    motivo_id: motivoId,
                                    nota: this.notasMotivos[motivoId] || null
                                }))
                            })
                        });
            
                        const data = await response.json();
            
                        if (data.success) {
                            alert('✅ Motivos de subsanación guardados correctamente');
                            // Actualizar los datos del detalle
                            this.$dispatch('refresh-detalle');
                        } else {
                            alert('❌ Error al guardar los motivos: ' + (data.message || 'Error desconocido'));
                        }
                    } catch (error) {
                        alert('❌ Error al guardar los motivos de subsanación');
                    }
                },
            
                async crearMotivoSubsanacion() {
                    if (!this.nuevoMotivo.descripcion.trim()) {
                        alert('Por favor, ingresa una descripción para el motivo');
                        return;
                    }
            
                    this.guardandoMotivo = true;
            
                    try {
                        const response = await fetch('/contrataciones/' + detalle.id + '/crear-motivo-subsanacion', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                descripcion: this.nuevoMotivo.descripcion,
                                motivo: this.nuevoMotivo.motivo,
                                document_id: this.nuevoMotivo.document_id
                            })
                        });
            
                        const data = await response.json();
            
                        if (data.success) {
                            alert('✅ Motivo de subsanación creado correctamente');
                            this.mostrarModalCrearMotivo = false;
                            this.nuevoMotivo = {
                                descripcion: '',
                                motivo: 'Padrón',
                                document_id: null
                            };
                            // Actualizar los datos del detalle
                            this.$dispatch('refresh-detalle');
                        } else {
                            alert('❌ Error al crear el motivo: ' + (data.message || 'Error desconocido'));
                        }
                    } catch (error) {
                        alert('❌ Error al crear el motivo de subsanación');
                    } finally {
                        this.guardandoMotivo = false;
                    }
                },
            
                async toggleTareaEstado(tareaId, estadoActual) {
                    try {
                        // Determinar el nuevo estado basado en el estado actual
                        let nuevoEstado;
                        if (estadoActual === 'pendiente') {
                            nuevoEstado = 'en_curso';
                        } else if (estadoActual === 'en_curso') {
                            nuevoEstado = 'completada';
                        } else if (estadoActual === 'completada') {
                            nuevoEstado = 'en_curso';
                        } else {
                            alert('❌ Estado de tarea no válido');
                            return;
                        }
            
                        const response = await fetch(`/contrataciones/${detalle.id}/toggle-tarea-estado`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                tarea_id: tareaId,
                                nuevo_estado: nuevoEstado
                            })
                        });
            
                        const data = await response.json();
            
                        if (data.success) {
                            // Actualizar los datos del detalle
                            this.$dispatch('refresh-detalle');
            
                            // Si la tarea se completó y hay próximas tareas disponibles, mostrarlas
                            if (nuevoEstado === 'completada' && data.proximas_tareas && data.proximas_tareas.length > 0) {
                                // Disparar evento al componente padre para mostrar el modal
                                this.$dispatch('mostrar-proximas-tareas', {
                                    proximasTareas: data.proximas_tareas
                                });
                            }
                        } else {
                            alert('❌ Error al cambiar el estado de la tarea: ' + (data.message || 'Error desconocido'));
                        }
                    } catch (error) {
                        alert('❌ Error al cambiar el estado de la tarea');
                    }
                }
            }">
                <!-- Layout de dos columnas -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Columna 1: Datos del Cliente -->
                    <div class="bg-gray-50 p-6 rounded-lg space-y-4">
                        <h4 class="text-xl font-semibold">Datos del Cliente</h4>

                        <p><strong>Nombre usuario:</strong> <span x-text="detalle.user.name"></span>
                        </p>
                        <p><strong>DNI:</strong> <span
                                x-text="(detalle.user?.answers || []).find(a => a.question?.slug === 'dni_nie' && !a.conviviente_id && !a.arrendador_id)?.answer || ''"></span>
                        </p>
                        <p><strong>Teléfono:</strong> <span x-text="detalle.user.telefono"></span>
                        </p>
                        <p><strong>Email:</strong> <span x-text="detalle.user.email"></span></p>
                        <p><strong>Ayuda contratada:</strong> <span
                                x-text="detalle.nombre_ayuda"></span></p>
                        <p><strong>Estados OPx:</strong> <span class="font-medium"
                                x-text="(detalle && detalle.estados_opx && detalle.estados_opx.length) ? detalle.estados_opx.join(', ') : 'Ninguno'"></span>
                        </p>
                        <p x-show="detalle.tarea_en_curso"><strong>Tarea en curso:</strong>
                            <span class="font-medium text-blue-600"
                                x-text="detalle.tarea_en_curso?.nombre_completo || ''"></span>
                        </p>

                        <div class="mt-4 flex items-center space-x-2">
                            <span class="font-medium">⏳ Plazo restante:</span>
                            <span
                                class="px-3 py-1 bg-blue-100 text-blue-800 rounded font-mono text-lg"
                                x-text="countdown ? `${countdown.d}d ${countdown.h}h ${countdown.m}m ${countdown.s}s` : '—'"></span>
                        </div>
                    </div>

                    <!-- Columna 2: Tareas -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border h-[400px] flex flex-col">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-xl font-semibold">Tareas de la Contratación</h4>
                            <button
                                @click="mostrarModalCrearTarea = true; cargarTareasDisponibles()"
                                class="px-3 py-1 text-xs rounded transition bg-blue-100 text-blue-800 hover:bg-blue-200 flex items-center space-x-1">
                                <i class="bx bx-plus"></i>
                                <span>Nueva Tarea</span>
                            </button>
                        </div>

                        <!-- Contenedor scrolleable de tareas -->
                        <div class="flex-1 overflow-y-auto space-y-6 pr-2">
                            <!-- Tareas En Curso -->
                            <div>
                                <h6
                                    class="font-medium text-gray-800 mb-3 flex items-center sticky top-0 bg-white py-2">
                                    <i class="bx bx-play-circle text-blue-600 mr-2"></i>
                                    Tareas En Curso
                                    <span
                                        class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full"
                                        x-text="(detalle.tareas || []).filter(t => t.estado_tarea === 'en_curso').length"></span>
                                </h6>
                                <div class="space-y-3">
                                    <template
                                        x-for="tarea in (detalle.tareas || []).filter(t => t.estado_tarea === 'en_curso')"
                                        :key="tarea.id">
                                        <div
                                            class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-medium text-gray-800"
                                                        x-text="tarea.nombre_completo"></span>
                                                    <span
                                                        class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                        En Curso
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-1"
                                                    x-show="tarea.descripcion"
                                                    x-text="tarea.descripcion"></p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Iniciada: <span
                                                        x-text="new Date(tarea.updated_at).toLocaleDateString()"></span>
                                                </p>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <button
                                                    @click="toggleTareaEstado(tarea.id, tarea.estado_tarea)"
                                                    class="px-3 py-1 text-xs rounded transition bg-green-100 text-green-800 hover:bg-green-200">
                                                    Finalizar
                                                </button>
                                                <button @click="eliminarTarea(tarea.id)"
                                                    class="px-3 py-1 text-xs rounded transition bg-red-100 text-red-800 hover:bg-red-200"
                                                    title="Eliminar tarea">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>

                                    <div x-show="(detalle.tareas || []).filter(t => t.estado_tarea === 'en_curso').length === 0"
                                        class="text-center py-6 text-gray-500">
                                        <i
                                            class="bx bx-play-circle text-3xl mb-2 text-blue-600"></i>
                                        <p class="text-sm">No hay tareas en curso</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Tareas Pendientes -->
                            <div>
                                <h6
                                    class="font-medium text-gray-800 mb-3 flex items-center sticky top-0 bg-white py-2">
                                    <i class="bx bx-time text-yellow-600 mr-2"></i>
                                    Tareas Pendientes
                                    <span
                                        class="ml-2 px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full"
                                        x-text="(detalle.tareas || []).filter(t => t.estado_tarea === 'pendiente').length"></span>
                                </h6>
                                <div class="space-y-3">
                                    <template
                                        x-for="tarea in (detalle.tareas || []).filter(t => t.estado_tarea === 'pendiente')"
                                        :key="tarea.id">
                                        <div
                                            class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-medium text-gray-800"
                                                        x-text="tarea.nombre_completo"></span>
                                                    <span
                                                        class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                                        Pendiente
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-1"
                                                    x-show="tarea.descripcion"
                                                    x-text="tarea.descripcion"></p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Creada: <span
                                                        x-text="new Date(tarea.created_at).toLocaleDateString()"></span>
                                                </p>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <button
                                                    @click="toggleTareaEstado(tarea.id, tarea.estado_tarea)"
                                                    class="px-3 py-1 text-xs rounded transition bg-blue-100 text-blue-800 hover:bg-blue-200">
                                                    En Curso
                                                </button>
                                                <button @click="eliminarTarea(tarea.id)"
                                                    class="px-3 py-1 text-xs rounded transition bg-red-100 text-red-800 hover:bg-red-200"
                                                    title="Eliminar tarea">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>

                                    <div x-show="(detalle.tareas || []).filter(t => t.estado_tarea === 'pendiente').length === 0"
                                        class="text-center py-6 text-gray-500">
                                        <i
                                            class="bx bx-check-circle text-3xl mb-2 text-green-600"></i>
                                        <p class="text-sm">No hay tareas pendientes</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Tareas Completadas -->
                            <div>
                                <h6
                                    class="font-medium text-gray-800 mb-3 flex items-center sticky top-0 bg-white py-2">
                                    <i class="bx bx-check-circle text-green-600 mr-2"></i>
                                    Tareas Completadas
                                    <span
                                        class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full"
                                        x-text="(detalle.tareas || []).filter(t => t.estado_tarea === 'completada').length"></span>
                                </h6>
                                <div class="space-y-3">
                                    <template
                                        x-for="tarea in (detalle.tareas || []).filter(t => t.estado_tarea === 'completada')"
                                        :key="tarea.id">
                                        <div
                                            class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-medium text-gray-800"
                                                        x-text="tarea.nombre_completo"></span>
                                                    <span
                                                        class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                        Completada
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-1"
                                                    x-show="tarea.descripcion"
                                                    x-text="tarea.descripcion"></p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Completada: <span
                                                        x-text="new Date(tarea.updated_at).toLocaleDateString()"></span>
                                                </p>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <button
                                                    @click="toggleTareaEstado(tarea.id, tarea.estado_tarea)"
                                                    class="px-3 py-1 text-xs rounded transition bg-blue-100 text-blue-800 hover:bg-blue-200">
                                                    Volver a En Curso
                                                </button>
                                                <button @click="eliminarTarea(tarea.id)"
                                                    class="px-3 py-1 text-xs rounded transition bg-red-100 text-red-800 hover:bg-red-200"
                                                    title="Eliminar tarea">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>

                                    <div x-show="(detalle.tareas || []).filter(t => t.estado_tarea === 'completada').length === 0"
                                        class="text-center py-6 text-gray-500">
                                        <i class="bx bx-task text-3xl mb-2 text-gray-400"></i>
                                        <p class="text-sm">No hay tareas completadas</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Mensaje cuando no hay tareas -->
                            <div x-show="!detalle.tareas || detalle.tareas.length === 0"
                                class="text-center py-8 text-gray-500">
                                <i class="bx bx-task text-4xl mb-2"></i>
                                <p>No hay tareas asignadas a esta contratación.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Motivos de Subsanación -->
                <div x-show="detalle.tarea_en_curso && detalle.tarea_en_curso.opcion_tarea && detalle.tarea_en_curso.opcion_tarea.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').includes('subsanacion')"
                    class="bg-yellow-50 p-6 rounded-lg border border-yellow-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <i class="bx bx-check-square text-yellow-600 text-xl mr-2"></i>
                            <h4 class="text-xl font-semibold text-yellow-800">Motivos de
                                Subsanación</h4>
                        </div>
                        <button @click="mostrarModalCrearMotivo = true"
                            class="px-3 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition text-sm flex items-center space-x-1">
                            <i class="bx bx-plus"></i>
                            <span>Crear Motivo</span>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="motivo in (detalle.motivos_subsanacion || [])"
                            :key="motivo.id">
                            <div class="p-3 bg-white rounded-lg border border-yellow-200">
                                <div class="flex items-start space-x-3">
                                    <input type="checkbox" :id="'motivo_' + motivo.id"
                                        :value="motivo.id"
                                        x-model.number="motivosSeleccionados"
                                        @change="if ($event.target.checked) { if (typeof notasMotivos[motivo.id] === 'undefined') notasMotivos[motivo.id] = ''; $nextTick(() => { const el = document.getElementById('nota_' + motivo.id); if (el) { el.focus(); } }); } else { delete notasMotivos[motivo.id]; }"
                                        class="mt-1 h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                                    <div class="flex-1">
                                        <label :for="'motivo_' + motivo.id"
                                            class="text-sm font-medium text-gray-900 cursor-pointer">
                                            <span x-text="motivo.descripcion"></span>
                                            <span x-show="motivo.motivo"
                                                class="text-gray-500 ml-2">(<span
                                                    x-text="motivo.motivo"></span>)</span>
                                        </label>
                                        <div x-show="motivo.document"
                                            class="mt-1 text-xs text-gray-600">
                                            <i class="bx bx-file mr-1"></i>
                                            <span x-text="motivo.document.name"></span>
                                        </div>
                                    </div>
                                    <div class="ml-auto">
                                        <button @click="eliminarMotivo(motivo.id)"
                                            class="px-2 py-1 text-xs rounded bg-red-100 text-red-800 hover:bg-red-200"
                                            title="Eliminar motivo">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- Campo de nota que aparece cuando el motivo está seleccionado -->
                                <div x-show="motivosSeleccionados.includes(motivo.id)"
                                    class="mt-3 ml-7">
                                    <label :for="'nota_' + motivo.id"
                                        class="block text-sm font-medium text-gray-700 mb-1">
                                        Nota adicional (opcional):
                                    </label>
                                    <textarea :id="'nota_' + motivo.id" x-model="notasMotivos[motivo.id]"
                                        placeholder="Añade una nota para este motivo..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 text-sm"
                                        rows="2" maxlength="1000"></textarea>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <span
                                            x-text="(notasMotivos[motivo.id] || '').length"></span>/1000
                                        caracteres
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="!detalle.motivos_subsanacion || detalle.motivos_subsanacion.length === 0"
                            class="text-center py-4 text-gray-500">
                            <i class="bx bx-info-circle text-2xl mb-2"></i>
                            <p class="text-sm">No hay motivos de subsanación definidos para esta
                                ayuda.</p>
                        </div>
                    </div>

                    <div x-show="motivosSeleccionados.length > 0"
                        class="mt-4 flex justify-end space-x-2">
                        <button @click="guardarMotivosSubsanacion()"
                            class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition text-sm">
                            <i class="bx bx-save mr-1"></i>
                            Guardar Motivos Seleccionados
                        </button>
                    </div>
                </div>

                <!-- Modal para crear motivo de subsanación -->
                <div x-show="mostrarModalCrearMotivo" x-transition.opacity
                    class="fixed inset-0 z-[200] flex items-center justify-center" role="dialog"
                    aria-modal="true"
                    @keydown.escape.prevent.stop="mostrarModalCrearMotivo = false">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/50"
                        @click="mostrarModalCrearMotivo = false"></div>

                    <!-- Panel del modal -->
                    <div class="relative z-[201] w-[90vw] max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden"
                        @click.stop>

                        <!-- Header -->
                        <div class="px-6 py-4 border-b bg-yellow-50">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-xl font-semibold text-yellow-800">Crear Motivo
                                        de Subsanación</h3>
                                    <p class="text-yellow-600">Agrega un nuevo motivo de
                                        subsanación para esta ayuda</p>
                                </div>
                                <button @click="mostrarModalCrearMotivo = false"
                                    class="text-yellow-600 hover:text-yellow-800 text-2xl">×</button>
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div class="p-6">
                            <!-- Indicador de carga -->
                            <div x-show="guardandoMotivo"
                                class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="animate-spin rounded-full h-5 w-5 border-b-2 border-yellow-600">
                                    </div>
                                    <span class="text-yellow-800">Creando motivo de
                                        subsanación...</span>
                                </div>
                            </div>

                            <div class="space-y-4"
                                :class="{ 'opacity-50 pointer-events-none': guardandoMotivo }">
                                <!-- Descripción -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Descripción del motivo *
                                    </label>
                                    <textarea x-model="nuevoMotivo.descripcion"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                        rows="3" placeholder="Describe el motivo de subsanación..."></textarea>
                                </div>

                                <!-- Tipo de motivo -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo de motivo
                                    </label>
                                    <select x-model="nuevoMotivo.motivo"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                        <option value="Padrón">Padrón</option>
                                        <option value="Contrato">Contrato</option>
                                        <option value="Recibos">Recibos</option>
                                    </select>
                                </div>

                                <!-- Documento asociado -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Documento asociado (opcional)
                                    </label>
                                    <select x-model="nuevoMotivo.document_id"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                        <option value="">Selecciona un documento...</option>
                                        <template x-for="doc in documentosDisponibles"
                                            :key="doc.id">
                                            <option :value="doc.id" x-text="doc.name">
                                            </option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                                <button @click="mostrarModalCrearMotivo = false"
                                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                                    Cancelar
                                </button>
                                <button @click="crearMotivoSubsanacion()"
                                    :disabled="guardandoMotivo || !nuevoMotivo.descripcion.trim()"
                                    class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                    <span x-show="!guardandoMotivo">Crear Motivo</span>
                                    <span x-show="guardandoMotivo">Creando...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal para seleccionar próxima tarea -->
                <div x-show="mostrarSelectorProximaTarea" x-transition.opacity
                    class="fixed inset-0 z-[200] flex items-center justify-center" role="dialog"
                    aria-modal="true"
                    @keydown.escape.prevent.stop="mostrarSelectorProximaTarea = false">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/50"
                        @click="mostrarSelectorProximaTarea = false"></div>

                    <!-- Panel del modal -->
                    <div class="relative z-[201] w-[90vw] max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden"
                        @click.stop>

                        <!-- Header -->
                        <div class="px-6 py-4 border-b bg-green-50">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-xl font-semibold text-green-800">¡Tarea
                                        Completada!</h3>
                                    <p class="text-green-600">Selecciona la próxima tarea que
                                        quieres realizar:</p>
                                </div>
                                <button @click="mostrarSelectorProximaTarea = false"
                                    class="text-green-600 hover:text-green-800 text-2xl">×</button>
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div class="p-6">
                            <div class="space-y-3">
                                <template x-for="proximaTarea in proximasTareasDisponibles"
                                    :key="proximaTarea.tarea_id + '-' + proximaTarea.opcion_tarea_id">
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border hover:bg-gray-100 transition cursor-pointer"
                                        @click="crearTareaDesdeFlujo(proximaTarea.tarea_id, proximaTarea.opcion_tarea_id)">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <i
                                                    class="bx bx-plus-circle text-green-600 text-xl"></i>
                                                <span class="font-medium text-gray-800"
                                                    x-text="proximaTarea.nombre_completo"></span>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1"
                                                x-show="proximaTarea.descripcion"
                                                x-text="proximaTarea.descripcion"></p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button
                                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition text-sm">
                                                Crear Tarea
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="!proximasTareasDisponibles || proximasTareasDisponibles.length === 0"
                                    class="text-center py-8 text-gray-500">
                                    <i class="bx bx-check-circle text-4xl mb-2 text-green-600"></i>
                                    <p>No hay más tareas sugeridas para este flujo.</p>
                                    <p class="text-sm mt-2">Puedes cerrar este modal y continuar
                                        con otras tareas.</p>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                                <button @click="mostrarSelectorProximaTarea = false"
                                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal para crear nueva tarea -->
                <div x-show="mostrarModalCrearTarea" x-transition.opacity
                    class="fixed inset-0 z-[200] flex items-center justify-center" role="dialog"
                    aria-modal="true"
                    @keydown.escape.prevent.stop="mostrarModalCrearTarea = false">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/50"
                        @click="mostrarModalCrearTarea = false"></div>

                    <!-- Panel del modal -->
                    <div class="relative z-[201] w-[90vw] max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden"
                        @click.stop>

                        <!-- Header -->
                        <div class="px-6 py-4 border-b bg-blue-50">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-xl font-semibold text-blue-800">Crear Nueva
                                        Tarea</h3>
                                    <p class="text-blue-600">Selecciona una tarea y su opción
                                        específica:</p>
                                </div>
                                <button @click="mostrarModalCrearTarea = false"
                                    class="text-blue-600 hover:text-blue-800 text-2xl">×</button>
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div class="p-6">
                            <!-- Paso 1: Seleccionar Tarea -->
                            <div x-show="pasoCrearTarea === 1">
                                <h4 class="font-medium text-gray-800 mb-4">1. Selecciona una tarea:
                                </h4>
                                <div class="space-y-3 max-h-64 overflow-y-auto">
                                    <template x-for="tarea in tareasDisponibles"
                                        :key="tarea.id">
                                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border hover:bg-gray-100 transition cursor-pointer"
                                            @click="seleccionarTarea(tarea)">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <i
                                                        class="bx bx-task text-blue-600 text-xl"></i>
                                                    <span class="font-medium text-gray-800"
                                                        x-text="tarea.nombre"></span>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-1"
                                                    x-show="tarea.descripcion"
                                                    x-text="tarea.descripcion"></p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <span x-text="tarea.opciones.length"></span>
                                                    opciones disponibles
                                                </p>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <i class="bx bx-chevron-right text-gray-400"></i>
                                            </div>
                                        </div>
                                    </template>

                                    <div x-show="!tareasDisponibles || tareasDisponibles.length === 0"
                                        class="text-center py-8 text-gray-500">
                                        <i class="bx bx-task text-4xl mb-2 text-gray-400"></i>
                                        <p>No hay tareas disponibles para crear.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Paso 2: Seleccionar Opción -->
                            <div x-show="pasoCrearTarea === 2">
                                <div class="mb-4">
                                    <button @click="pasoCrearTarea = 1"
                                        class="text-blue-600 hover:text-blue-800 flex items-center space-x-1">
                                        <i class="bx bx-arrow-back"></i>
                                        <span>Volver</span>
                                    </button>
                                </div>
                                <h4 class="font-medium text-gray-800 mb-4">2. Selecciona una opción
                                    para "<span x-text="tareaSeleccionada?.nombre"></span>":</h4>
                                <div class="space-y-3 max-h-64 overflow-y-auto">
                                    <template x-for="opcion in tareaSeleccionada?.opciones || []"
                                        :key="opcion.id">
                                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border hover:bg-gray-100 transition cursor-pointer"
                                            @click="crearTareaManual(tareaSeleccionada.id, opcion.id)">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <i
                                                        class="bx bx-check-square text-green-600 text-xl"></i>
                                                    <span class="font-medium text-gray-800"
                                                        x-text="opcion.nombre"></span>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-1"
                                                    x-show="opcion.descripcion"
                                                    x-text="opcion.descripcion"></p>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <button
                                                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition text-sm">
                                                    Crear Tarea
                                                </button>
                                            </div>
                                        </div>
                                    </template>

                                    <div x-show="!tareaSeleccionada?.opciones || tareaSeleccionada.opciones.length === 0"
                                        class="text-center py-8 text-gray-500">
                                        <i
                                            class="bx bx-check-square text-4xl mb-2 text-gray-400"></i>
                                        <p>No hay opciones disponibles para esta tarea.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                                <button @click="mostrarModalCrearTarea = false"
                                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de actividad (ancho completo) -->
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <h5 class="font-medium mb-2">Historial de actividad</h5>
                    <ul class="divide-y divide-gray-200">
                        <template x-for="act in detalle.historial" :key="act.id">
                            <li class="py-2">
                                <p class="text-xs text-gray-500"
                                    x-text="new Date(act.fecha_inicio).toLocaleString()">
                                </p>
                                <p class="text-sm text-gray-800" x-text="act.actividad"></p>
                                <p class="text-xs text-gray-600" x-show="act.observaciones"
                                    x-text="act.observaciones">
                                </p>
                            </li>
                        </template>
                        <li x-show="!detalle.historial.length" class="py-2 text-gray-500 text-sm">
                            No hay historial de actividad.
                        </li>
                    </ul>
                </div>
            </div>

            @php
                $allDocs = collect([
                    12 => [
                        'slug' => 'documento-identidad-convivientes',
                        'name' => 'Documento de identidad convivientes',
                    ],
                    3 => ['slug' => 'firma', 'name' => 'Firma'],
                    10 => ['slug' => 'documentos-especiales', 'name' => 'Documentos especiales'],
                ])->mapWithKeys(fn($data, $id) => [$id => $data]);
            @endphp
            <script>
                // Aquí es donde Laravel inyectaría los datos.
                window.documentMeta = window.documentMeta || @json($allDocs);
            </script>

            {{-- Documentos --}}
            <template x-if="tab==='documentos'">
                <div class="space-y-4">
                    <h4 class="font-semibold mb-2">Estado de Documentación</h4>

                    {{-- Selector de documentos visibles --}}
                    <div class="bg-white p-4 rounded-lg shadow-sm border">
                        <div class="flex items-center justify-between mb-3">
                            <h5 class="font-medium text-gray-700">Configurar documentos visibles
                            </h5>
                            <button @click="toggleDocumentSelector"
                                class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                <span x-show="!mostrarSelectorDocumentos">Configurar</span>
                                <span x-show="mostrarSelectorDocumentos">Ocultar</span>
                            </button>
                        </div>

                        <div x-show="mostrarSelectorDocumentos" class="space-y-3">
                            {{-- Buscador --}}
                            <div class="relative">
                                <input type="text" x-model="busquedaDocumentos"
                                    @input="filtrarDocumentos" placeholder="Buscar documentos..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="bx bx-search text-gray-400"></i>
                                </div>
                                <button x-show="busquedaDocumentos"
                                    @click="busquedaDocumentos = ''; filtrarDocumentos()"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i class="bx bx-x text-gray-400 hover:text-gray-600"></i>
                                </button>
                            </div>

                            {{-- Contador de resultados --}}
                            <div class="text-sm text-gray-600">
                                <span x-text="documentosFiltrados.length"></span> de <span
                                    x-text="todosLosDocumentos.length"></span> documentos
                                <span x-show="busquedaDocumentos">(filtrados por "<span
                                        x-text="busquedaDocumentos"></span>")</span>
                            </div>

                            {{-- Contador de selección temporal --}}
                            <div class="text-sm text-purple-600 font-medium">
                                <i class="bx bx-check-circle mr-1"></i>
                                <span x-text="documentosSeleccionadosTemporales.length"></span>
                                documentos seleccionados
                                <span
                                    x-show="documentosSeleccionadosTemporales.length !== documentosSeleccionados.length"
                                    class="text-orange-600">
                                    (cambios pendientes de guardar)
                                </span>
                            </div>

                            {{-- Lista de documentos --}}
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-60 overflow-y-auto border rounded-lg p-2">
                                <template x-for="doc in documentosFiltrados"
                                    :key="doc.id">
                                    <label
                                        class="flex items-center space-x-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" :value="doc.id"
                                            x-model="documentosSeleccionadosTemporales"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <div class="flex-1 min-w-0">
                                            <span
                                                class="text-sm font-medium text-gray-900 block truncate"
                                                x-text="doc.name"></span>
                                            <span x-show="doc.description"
                                                class="text-xs text-gray-500 block truncate"
                                                x-text="doc.description"></span>
                                        </div>
                                    </label>
                                </template>
                                <div x-show="documentosFiltrados.length === 0"
                                    class="col-span-2 text-center text-gray-500 py-4">
                                    <i class="bx bx-search text-2xl mb-2 block"></i>
                                    <span x-show="busquedaDocumentos">No se encontraron documentos
                                        con ese término</span>
                                    <span x-show="!busquedaDocumentos">No hay documentos
                                        disponibles</span>
                                </div>
                            </div>

                            {{-- Botones de control --}}
                            <div class="flex flex-wrap gap-2">
                                <button @click="seleccionarTodosDocumentos"
                                    class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                                    Seleccionar todos
                                </button>
                                <button @click="deseleccionarTodosDocumentos"
                                    class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-700">
                                    Deseleccionar todos
                                </button>
                                <button @click="seleccionarDocumentosFiltrados"
                                    x-show="busquedaDocumentos"
                                    class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                    Seleccionar filtrados
                                </button>
                                <button @click="restablecerConfiguracionDocumentos"
                                    class="px-3 py-1 bg-orange-600 text-white rounded text-sm hover:bg-orange-700">
                                    <i class="bx bx-reset mr-1"></i>
                                    Restablecer
                                </button>
                                <button @click="guardarSeleccionDocumentos"
                                    class="px-3 py-1 bg-purple-600 text-white rounded text-sm hover:bg-purple-700 font-medium">
                                    <i class="bx bx-save mr-1"></i>
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Recibos mensuales --}}
                    <template
                        x-if="detalle.documentosRecibos && detalle.documentosRecibos.length > 0">
                        <div class="mb-6 bg-blue-50 p-4 rounded-lg">
                            <h5 class="font-medium mb-3 text-blue-800">📆 Recibos mensuales del
                                alquiler</h5>

                            <div class="flex gap-3 overflow-x-auto pb-2"
                                style="scrollbar-width: thin;">
                                <template x-for="(recibo, idx) in detalle.documentosRecibos"
                                    :key="`recibo-${recibo.slug}-${idx}`">
                                    <div class="flex-shrink-0 w-48 border rounded-lg p-3 shadow-sm bg-white"
                                        :class="{
                                            'bg-warning-subtle': recibo.uploads && recibo.uploads
                                                .some(u => u.estado === 'pendiente'),
                                            'bg-danger-subtle': recibo.uploads && recibo.uploads
                                                .some(u => u.estado === 'rechazado'),
                                            'bg-success-subtle': recibo.uploads && recibo.uploads
                                                .some(u => u.estado === 'validado')
                                        }">
                                        <div class="text-center mb-2">
                                            <span class="font-semibold text-sm"
                                                x-text="recibo.name"></span>
                                        </div>

                                        <template
                                            x-if="recibo.uploads && recibo.uploads.length > 0">
                                            <template x-for="upload in recibo.uploads"
                                                :key="upload.id">
                                                <div class="space-y-2 mb-2"
                                                    x-data="{ notaTemp: upload.nota_rechazo || '' }">
                                                    <div
                                                        class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2">
                                                            <a :href="upload.temporary_url"
                                                                target="_blank"
                                                                class="text-blue-600 hover:underline text-xs flex items-center gap-1">
                                                                <i class="bx bx-show text-sm"></i>
                                                                Ver
                                                            </a>
                                                            <button
                                                                @click="descargarDocumento(upload.download_url || upload.temporary_url, upload.nombre_personalizado || recibo.name || 'documento.pdf')"
                                                                class="text-green-600 hover:text-green-800 text-xs flex items-center gap-1 bg-transparent border-none cursor-pointer">
                                                                <i
                                                                    class="bx bx-download text-sm"></i>
                                                            </button>
                                                        </div>
                                                        <select x-model="upload.estado"
                                                            class="px-2 py-1 border rounded text-xs"
                                                            @change="upload.estado !== 'rechazado' && updateDocEstado(upload.id, upload.estado, detalle.id, null)"
                                                            :class="{
                                                                'bg-yellow-100 text-yellow-800': upload
                                                                    .estado === 'pendiente',
                                                                'bg-green-100 text-green-800': upload
                                                                    .estado === 'validado',
                                                                'bg-red-100 text-red-800': upload
                                                                    .estado === 'rechazado'
                                                            }">
                                                            <option value="pendiente">Pendiente
                                                            </option>
                                                            <option value="validado">Validado
                                                            </option>
                                                            <option value="rechazado">Rechazado
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <!-- Mostrar solo el contenido de la nota si está guardada -->
                                                    <template
                                                        x-if="upload.estado === 'rechazado' && upload.nota_rechazo && upload.nota_rechazo.length > 0">
                                                        <div class="space-y-1">
                                                            <div
                                                                class="flex items-start gap-2 bg-yellow-50 border border-yellow-200 rounded p-2">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="h-3 w-3 text-yellow-600 mt-0.5 flex-shrink-0"
                                                                    viewBox="0 0 20 20"
                                                                    fill="currentColor">
                                                                    <path fill-rule="evenodd"
                                                                        d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zM9 7a1 1 0 102 0 1 1 0 00-2 0zm2 2a1 1 0 10-2 0v4a1 1 0 102 0V9z"
                                                                        clip-rule="evenodd" />
                                                                </svg>
                                                                <p class="text-xs text-gray-700 whitespace-pre-wrap flex-1"
                                                                    x-text="upload.nota_rechazo">
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <!-- Mostrar textarea y botón de guardar si la nota no está guardada -->
                                                    <template
                                                        x-if="upload.estado === 'rechazado' && (!upload.nota_rechazo || upload.nota_rechazo.length === 0)">
                                                        <div class="space-y-1">
                                                            <textarea x-model="notaTemp"
                                                                x-effect="if(upload.estado === 'rechazado' && (!upload.nota_rechazo || upload.nota_rechazo.length === 0)) { notaTemp = upload.nota_rechazo || '' }"
                                                                rows="2" class="w-full px-2 py-1 border border-red-200 rounded text-xs"
                                                                placeholder="Nota de rechazo"></textarea>
                                                            <button
                                                                @click="upload.nota_rechazo = notaTemp; updateDocEstado(upload.id, upload.estado, detalle.id, notaTemp)"
                                                                class="w-full px-2 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700">
                                                                Guardar
                                                            </button>
                                                        </div>
                                                    </template>
                                                    <button
                                                        @click.prevent="if(confirm('¿Eliminar este recibo?')) eliminarDocumento(upload.id, $el)"
                                                        class="w-full px-2 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700">
                                                        Eliminar
                                                    </button>
                                                </div>
                                            </template>
                                        </template>

                                        <template
                                            x-if="!recibo.uploads || recibo.uploads.length === 0">
                                            <div class="space-y-2">
                                                <div
                                                    class="text-center text-gray-500 text-xs mb-2">
                                                    <img src="{{ asset('imagenes/—Pngtree—danger sign flat icon vector_9133214.png') }}"
                                                        alt="Falta"
                                                        class="w-5 h-5 mx-auto mb-1">
                                                    Falta documento
                                                </div>
                                                <input type="file"
                                                    :id="`file-recibo-${recibo.slug}-${idx}`"
                                                    @change="handleMissingFile(`recibo-${recibo.slug}`, null, $event.target)"
                                                    class="text-xs text-gray-500 w-full mb-2" />
                                                <button
                                                    :disabled="!hasMissing(`recibo-${recibo.slug}-null`)"
                                                    @click.prevent="uploadMissing(recibo.id || 7, null, recibo.slug, null, `recibo-${recibo.slug}`)"
                                                    class="w-full px-3 py-1 bg-[#54debd] text-white rounded text-xs hover:bg-[#43c5a9] disabled:opacity-50">
                                                    Subir recibo
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Documentos generales de la ayuda --}}
                    <div class="max-h-[40rem] overflow-y-auto bg-gray-50 p-4 rounded-lg">
                        <h5 class="font-medium mb-2">Documentos generales</h5>

                        <template x-for="(doc, idx) in obtenerDocumentosVisibles()"
                            :key="`gen-doc-${doc.id}-${idx}`">
                            <div class="space-y-2">
                                <!-- Mostrar todos los documentos existentes para este doc -->
                                <template
                                    x-for="ud in detalle.user.user_documents.filter(u => String(u.document_id) === String(doc.id) && u.user_id === detalle.user.id &&  (u.conviviente_index === null || u.conviviente_index === undefined))"
                                    :key="ud.id">
                                    <div class="flex justify-between items-center border p-2 rounded"
                                        x-data="{ notaTemp: ud.nota_rechazo || '' }">
                                        <div class="flex flex-col">
                                            <span x-text="doc.name"></span>
                                            <span class="text-xs text-gray-500"
                                                x-text="ud.slug"></span>
                                        </div>
                                        <div
                                            class="flex items-start sm:items-center flex-col sm:flex-row gap-2 sm:gap-3">
                                            <a :href="ud.temporary_url" target="_blank"
                                                class="text-blue-600 hover:underline text-sm">Ver
                                                documento</a>
                                            <select x-model="ud.estado"
                                                class="px-2 py-1 border rounded text-sm"
                                                @change="ud.estado !== 'rechazado' && updateDocEstado(ud.id, ud.estado, detalle.id, null)"
                                                :class="{
                                                    'bg-yellow-100 text-yellow-800 border-yellow-300': ud
                                                        .estado === 'pendiente',
                                                    'bg-green-100 text-green-800 border-green-300': ud
                                                        .estado === 'validado',
                                                    'bg-red-100 text-red-800 border-red-300': ud
                                                        .estado === 'rechazado'
                                                }">
                                                <option value="pendiente">Pendiente</option>
                                                <option value="validado">Validado</option>
                                                <option value="rechazado">Rechazado</option>
                                            </select>
                                            <!-- Mostrar solo el contenido de la nota si está guardada -->
                                            <template
                                                x-if="ud.estado === 'rechazado' && ud.nota_rechazo && ud.nota_rechazo.length > 0">
                                                <div class="w-full sm:w-80">
                                                    <label
                                                        class="block text-xs font-medium text-gray-700 mb-1">Nota
                                                        para el usuario</label>
                                                    <div
                                                        class="flex items-start gap-2 bg-yellow-50 border border-yellow-200 rounded p-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-4 w-4 text-yellow-600 mt-1 flex-shrink-0"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zM9 7a1 1 0 102 0 1 1 0 00-2 0zm2 2a1 1 0 10-2 0v4a1 1 0 102 0V9z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                        <div class="flex-1">
                                                            <p class="text-sm text-gray-700 whitespace-pre-wrap"
                                                                x-text="ud.nota_rechazo"></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            <!-- Mostrar textarea y botón de guardar si la nota no está guardada -->
                                            <template
                                                x-if="ud.estado === 'rechazado' && (!ud.nota_rechazo || ud.nota_rechazo.length === 0)">
                                                <div class="w-full sm:w-80">
                                                    <label
                                                        class="block text-xs font-medium text-gray-700 mb-1">Nota
                                                        para el usuario</label>
                                                    <div
                                                        class="flex items-start gap-2 bg-yellow-50 border border-yellow-200 rounded p-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-4 w-4 text-yellow-600 mt-1 flex-shrink-0"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zM9 7a1 1 0 102 0 1 1 0 00-2 0zm2 2a1 1 0 10-2 0v4a1 1 0 102 0V9z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                        <div class="flex-1">
                                                            <textarea x-model="notaTemp"
                                                                x-effect="if(ud.estado === 'rechazado' && (!ud.nota_rechazo || ud.nota_rechazo.length === 0)) { notaTemp = ud.nota_rechazo || '' }"
                                                                rows="2"
                                                                class="w-full px-2 py-1 border border-yellow-200 rounded text-sm bg-white placeholder:text-gray-400"
                                                                placeholder="Explica brevemente qué necesita corregir o adjuntar"></textarea>
                                                            <div class="mt-1 text-[11px] text-gray-500"
                                                                x-text="`${(notaTemp||'').length}/2000`">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            <template
                                                x-if="ud.estado === 'rechazado' && (!ud.nota_rechazo || ud.nota_rechazo.length === 0)">
                                                <button
                                                    @click="ud.nota_rechazo = notaTemp; updateDocEstado(ud.id, ud.estado, detalle.id, notaTemp)"
                                                    class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Guardar</button>
                                            </template>
                                            <button
                                                @click.prevent="if(confirm('¿Seguro que quieres eliminar este documento?')) eliminarDocumento(ud.id, $el)"
                                                class="px-2 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700 ml-2">Eliminar</button>
                                        </div>
                                    </div>
                                </template>
                                <!-- Si no hay ninguno, mostrar input de subida -->
                                <template
                                    x-if="detalle.user.user_documents.filter(u => String(u.document_id) === String(doc.id) &&u.user_id === detalle.user.id && (u.conviviente_index === null || u.conviviente_index === undefined)).length === 0">
                                    <div
                                        class="flex justify-between items-center border p-2 rounded">
                                        <span x-text="doc.name"></span>
                                        <div class="flex items-center space-x-2">
                                            <input type="file" :id="`file-${doc.id}-${idx}`"
                                                @change="handleMissingFile(doc.id, null, $event.target)"
                                                multiple class="text-sm text-gray-500" />
                                            <button :disabled="!hasMissing(`${doc.id}-null`)"
                                                @click.prevent="uploadMissing(doc.id, null, doc.slug)"
                                                class="px-3 py-1 bg-[#54debd] text-white rounded text-xs hover:bg-[#43c5a9] disabled:opacity-50 transition">
                                                Subir <span
                                                    x-show="getMissingCount(`${doc.id}-null`) > 0"
                                                    x-text="`(${getMissingCount(`${doc.id}-null`)})`"
                                                    class="ml-1"></span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Pregunta de documentos extra --}}
                    <div x-show="mostrarPreguntaDocumentosExtra()"
                        class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <i class="bx bx-question-mark-circle text-yellow-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <h5 class="font-medium text-yellow-800 mb-2">
                                    ¿Tienes que crear algún documento extra?
                                </h5>
                                <p class="text-sm text-yellow-700 mb-3">
                                    Todos los documentos generales obligatorios están validados.
                                    ¿Necesitas subir algún documento adicional?
                                </p>
                                <div class="flex space-x-3">
                                    <button @click="procesarDocumentosExtra(true)"
                                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                        <i class="bx bx-plus mr-1"></i>
                                        Sí, tengo documentos extra
                                    </button>
                                    <button @click="procesarDocumentosExtra(false)"
                                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                        <i class="bx bx-check mr-1"></i>
                                        No, continuar con el proceso
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Documentos de Convivientes --}}

                    <!-- Documentos de Convivientes - VERSION SIMPLIFICADA -->

                    <div x-show="detalle.convivienteDatos && detalle.convivienteDatos.length > 0">

                        <div class="mt-6">
                            <h4 class="font-semibold mb-2">Documentos de Convivientes</h4>

                            <div
                                class="max-h-64 overflow-y-auto bg-gray-50 p-4 rounded-lg space-y-6">
                                <!-- Un bloque por cada conviviente -->

                                <template x-for="(block, idx) in detalle.convivienteDatos"
                                    :key="`conv-${block.index}`">

                                    <div class="space-y-4 border-b pb-4">
                                        <p class="font-medium mb-2">
                                            Conviviente #<span x-text="block.index"></span>
                                        </p>

                                        <!-- Documentos fijos para convivientes -->
                                        <template x-for="docId in [12, 10, 3]"
                                            :key="`conv-${block.index}-doc-${docId}`">

                                            <div
                                                class="flex justify-between items-center border p-2 rounded">
                                                <!-- Nombre del documento -->
                                                <span
                                                    x-text="window.documentMeta[docId]?.name || `Documento ${docId}`"></span>

                                                <!-- Si ya hay un UserDocument para este conviviente/doc -->
                                                <template
                                                    x-for="ud in detalle.user.user_documents.filter(u => 
                                                        String(u.document_id) === String(docId) && 
                                                        Number(u.conviviente_index) === Number(block.index)
                                                    )"
                                                    :key="ud.id">
                                                    <div
                                                        class="flex justify-between items-center border p-2 rounded">
                                                        <div class="flex flex-col">
                                                            <span
                                                                x-text="window.documentMeta[docId]?.name || `Documento ${docId}`"></span>
                                                            <span class="text-xs text-gray-500"
                                                                x-text="ud.slug"></span>
                                                        </div>
                                                        <div class="flex items-center space-x-2">
                                                            <a :href="ud.temporary_url"
                                                                target="_blank"
                                                                class="text-blue-600 hover:underline text-sm">Ver
                                                                documento</a>
                                                            <select x-model="ud.estado"
                                                                class="px-2 py-1 border rounded text-sm"
                                                                @change="ud.estado !== 'rechazado' && updateDocEstado(ud.id, ud.estado, detalle.id, ud.nota_rechazo)"
                                                                :class="{
                                                                    'bg-yellow-100 text-yellow-800 border-yellow-300': ud
                                                                        .estado === 'pendiente',
                                                                    'bg-green-100 text-green-800 border-green-300': ud
                                                                        .estado === 'validado',
                                                                    'bg-red-100 text-red-800 border-red-300': ud
                                                                        .estado === 'rechazado'
                                                                }">
                                                                <option value="pendiente">Pendiente
                                                                </option>
                                                                <option value="validado">Validado
                                                                </option>
                                                                <option value="rechazado">Rechazado
                                                                </option>
                                                            </select>
                                                            <template
                                                                x-if="ud.estado === 'rechazado'">
                                                                <button
                                                                    @click="updateDocEstado(ud.id, ud.estado, detalle.id, ud.nota_rechazo)"
                                                                    class="px-2 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Guardar</button>
                                                            </template>
                                                            <button
                                                                @click.prevent="if(confirm('¿Seguro que quieres eliminar este documento?')) eliminarDocumento(ud.id, $el)"
                                                                class="px-2 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700 ml-2">Eliminar</button>
                                                        </div>
                                                    </div>
                                                </template>

                                                <!-- Si NO hay documento, muestro el input + botón Subir -->
                                                <template
                                                    x-if="detalle.user.user_documents.filter(u => 
                                                        String(u.document_id) === String(docId) && 
                                                        Number(u.conviviente_index) === Number(block.index)
                                                    ).length === 0">
                                                    <div class="flex items-center space-x-2">
                                                        <input type="file"
                                                            :id="`file-${docId}-${block.index}`"
                                                            @change="handleMissingFile(docId, block.index, $event.target)"
                                                            multiple
                                                            class="text-sm text-gray-500" />
                                                        <button
                                                            :disabled="!hasMissing(`${docId}-${block.index}`)"
                                                            @click.prevent="uploadMissing(docId, block.index, window.documentMeta[docId]?.slug)"
                                                            class="px-3 py-1 bg-[#54debd] text-white rounded text-xs hover:bg-[#43c5a9] disabled:opacity-50 transition">
                                                            Subir <span
                                                                x-show="getMissingCount(`${docId}-${block.index}`) > 0"
                                                                x-text="`(${getMissingCount(`${docId}-${block.index}`)})`"
                                                                class="ml-1"></span>
                                                        </button>
                                                    </div>
                                                </template>
                                                <!-- Uploader: si es múltiple SIEMPRE; si no, solo cuando no hay -->
                                                {{-- <template
                                                    x-if="isMultiDoc(doc) || detalle.user.user_documents.filter(u => u.document_id === doc.id && u.user_id === detalle.user.id && (u.conviviente_index === null || u.conviviente_index === undefined)).length === 0">
                                                    <div class="flex justify-between items-center border p-2 rounded">
                                                        <span>
                                                            <span x-text="doc.name"></span>
                                                            <span x-show="isMultiDoc(doc)"
                                                                class="ml-2 text-[11px] bg-purple-100 text-purple-800 px-2 py-0.5 rounded">Múltiple</span>
                                                        </span>
                                                        <div class="flex items-center space-x-2">
                                                            <input type="file" :id="`file-${doc.id}-${idx}`"
                                                                :multiple="isMultiDoc(doc) ? true : null"
                                                                @change="handleFileChange($event, doc.id, null)"
                                                                class="text-sm text-gray-500" />
                                                            <button :disabled="!hasMissing(`${doc.id}-null`)"
                                                                @click.prevent="uploadMissing(doc.id, null, doc.slug)"
                                                                class="px-3 py-1 bg-[#54debd] text-white rounded text-xs hover:bg-[#43c5a9] disabled:opacity-50 transition">
                                                                Subir
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template> --}}

                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Documentos especiales requeridos -->
                    <template
                        x-if="detalle.documentosEspeciales && detalle.documentosEspeciales.length">
                        <div class="max-h-64 overflow-y-auto bg-blue-50 p-4 rounded-lg">
                            <h5 class="font-medium mb-2">Documentos especiales requeridos</h5>

                            <template x-for="(doc, idx) in detalle.documentosEspeciales"
                                :key="`esp-doc-${doc.id}-${idx}`">
                                <div
                                    class="flex justify-between items-center border p-2 rounded mb-2">
                                    <span x-text="doc.name"></span>
                                    <!-- Mostrar documento existente si lo hay -->
                                    <template
                                        x-for="ud in detalle.user.user_documents.filter(u =>
                                    String(u.document_id) === String(doc.id) &&
                                    u.conviviente_index == null
                                )"
                                        :key="ud.id">
                                        <div class="flex items-center space-x-2">
                                            <a :href="ud.temporary_url" target="_blank"
                                                class="text-blue-600 hover:underline text-sm">Ver
                                                documento</a>
                                            <select x-model="ud.estado"
                                                class="px-2 py-1 border rounded text-sm"
                                                @change="ud.estado !== 'rechazado' && updateDocEstado(ud.id, ud.estado, detalle.id, ud.nota_rechazo)"
                                                :class="{
                                                    'bg-yellow-100 text-yellow-800 border-yellow-300': ud
                                                        .estado === 'pendiente',
                                                    'bg-green-100 text-green-800 border-green-300': ud
                                                        .estado === 'validado',
                                                    'bg-red-100 text-red-800 border-red-300': ud
                                                        .estado === 'rechazado'
                                                }">
                                                <option value="pendiente">Pendiente</option>
                                                <option value="validado">Validado</option>
                                                <option value="rechazado">Rechazado</option>
                                            </select>
                                            <template x-if="ud.estado === 'rechazado'">
                                                <button
                                                    @click="updateDocEstado(ud.id, ud.estado, detalle.id, ud.nota_rechazo)"
                                                    class="px-2 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Guardar</button>
                                            </template>
                                            <button
                                                @click.prevent="if(confirm('¿Seguro que quieres eliminar este documento?')) eliminarDocumento(ud.id, $el)"
                                                class="px-2 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700 ml-2">Eliminar</button>
                                        </div>
                                    </template>
                                    <!-- Si no hay documento, mostrar input de subida -->
                                    <template
                                        x-if="detalle.user.user_documents.filter(u =>
                                    String(u.document_id) === String(doc.id) &&
                                    u.conviviente_index == null
                                ).length === 0">
                                        <div class="flex items-center space-x-2">
                                            <input type="file" :id="`file-${doc.id}-null`"
                                                @change="handleMissingFile(doc.id, null, $event.target)"
                                                multiple class="text-sm text-gray-500" />
                                            <button :disabled="!hasMissing(`${doc.id}-null`)"
                                                @click.prevent="uploadMissing(doc.id, null, doc.slug)"
                                                class="px-3 py-1 bg-[#54debd] text-white rounded text-xs hover:bg-[#43c5a9] disabled:opacity-50 transition">
                                                Subir <span
                                                    x-show="getMissingCount(`${doc.id}-null`) > 0"
                                                    x-text="`(${getMissingCount(`${doc.id}-null`)})`"
                                                    class="ml-1"></span>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Documentos de Tramitación -->
                    <div class="max-h-64 overflow-y-auto bg-green-50 p-4 rounded-lg">
                        <div class="flex justify-between items-center mb-4">
                            <h5 class="font-medium">Documentos de Tramitación</h5>
                            <button @click="showAddDocumentoTramitacion = true"
                                class="px-3 py-1 bg-[#54debd] text-white rounded text-xs hover:bg-[#43c5a9] transition">
                                <i class="bx bx-plus mr-1"></i>Añadir Documento
                            </button>
                        </div>

                        <div x-show="showAddDocumentoTramitacion"
                            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                            @click.away="showAddDocumentoTramitacion = false">
                            <div class="bg-white p-6 rounded-lg max-w-md w-full mx-4">
                                <h6 class="font-semibold mb-4">Añadir Documento de Tramitación</h6>

                                <div class="space-y-4">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1">Tipo
                                            de
                                            Documento</label>
                                        <select x-model="nuevoDocumentoTramitacion.slug"
                                            @change="actualizarNombrePersonalizado()"
                                            class="w-full border rounded p-2 text-sm">
                                            <option value="">Selecciona un documento</option>
                                            <template x-for="doc in documentosInternosDisponibles"
                                                :key="doc.id">
                                                <option :value="doc.slug" x-text="doc.name">
                                                </option>
                                            </template>
                                        </select>
                                        <div class="mt-2" x-data="{ crearNuevo: false, name: '', slug: '', allowedTypes: ['application/pdf'], multiUpload: false }">
                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                    class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700"
                                                    @click="crearNuevo = !crearNuevo">
                                                    <span
                                                        x-text="crearNuevo ? 'Cancelar' : 'Crear nuevo'"></span>
                                                </button>
                                            </div>

                                            <div x-show="crearNuevo"
                                                class="mt-3 border rounded p-3 bg-gray-50 space-y-2">
                                                <div>
                                                    <label
                                                        class="block text-xs text-gray-600 mb-1">Nombre</label>
                                                    <input type="text"
                                                        class="w-full border rounded p-2 text-sm"
                                                        x-model="name"
                                                        placeholder="Ej. Certificado de empadronamiento"
                                                        @input="slug = (name||'').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'')">
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-xs text-gray-600 mb-1">Slug</label>
                                                    <input type="text"
                                                        class="w-full border rounded p-2 text-sm"
                                                        x-model="slug"
                                                        placeholder="certificado-de-empadronamiento">
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-xs text-gray-600 mb-1">Tipos
                                                        permitidos</label>
                                                    <input type="text"
                                                        class="w-full border rounded p-2 text-sm"
                                                        :value="allowedTypes.join(', ')"
                                                        @input="allowedTypes = $event.target.value.split(',').map(t => t.trim()).filter(Boolean)"
                                                        placeholder="application/pdf, image/jpeg">
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <input id="multi_upload_cb" type="checkbox"
                                                        x-model="multiUpload" class="h-4 w-4">
                                                    <label for="multi_upload_cb"
                                                        class="text-sm text-gray-700">Permitir
                                                        múltiples archivos</label>
                                                </div>
                                                <div class="flex justify-end gap-2 pt-2">
                                                    <button type="button"
                                                        class="px-3 py-2 text-xs bg-gray-200 text-gray-800 rounded hover:bg-gray-300"
                                                        @click="crearNuevo=false; name=''; slug=''; allowedTypes=['application/pdf']; multiUpload=false;">
                                                        Limpiar
                                                    </button>
                                                    <button type="button"
                                                        class="px-3 py-2 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
                                                        :disabled="!name || !slug"
                                                        @click="
                                                            fetch('/contrataciones/documentos-internos', {
                                                                method: 'POST',
                                                                headers: {
                                                                    'Content-Type': 'application/json',
                                                                    'Accept': 'application/json',
                                                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                                                },
                                                                body: JSON.stringify({ name, slug, allowed_types: allowedTypes, multi_upload: multiUpload })
                                                            }).then(async r => {
                                                                if(!r.ok){ throw new Error(await r.text()); }
                                                                return r.json();
                                                            }).then(data => {
                                                                if(data && data.success && data.documento){
                                                                    documentosInternosDisponibles = [...documentosInternosDisponibles, { id: data.documento.id, slug: data.documento.slug, name: data.documento.name }];
                                                                    nuevoDocumentoTramitacion.slug = data.documento.slug;
                                                                    crearNuevo = false;
                                                                    name=''; slug=''; allowedTypes=['application/pdf']; multiUpload=false;
                                                                    actualizarNombrePersonalizado();
                                                                    showMessage('Documento interno creado', 'success');
                                                                }
                                                            }).catch(err => {
                                                                showMessage('Error creando documento interno: ' + err.message, 'error');
                                                            });
                                                        ">
                                                        Crear documento
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1">Nombre
                                            Personalizado</label>
                                        <div class="flex space-x-2">
                                            <input type="text"
                                                x-model="nuevoDocumentoTramitacion.nombre_personalizado"
                                                placeholder="Nombre personalizado del documento"
                                                class="flex-1 border rounded p-2 text-sm">
                                            <button type="button"
                                                @click="actualizarNombrePersonalizado()"
                                                :disabled="!nuevoDocumentoTramitacion.slug"
                                                class="px-3 py-2 bg-gray-100 text-gray-600 rounded text-sm hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                                title="Regenerar nombre automático">
                                                <i class="bx bx-refresh"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end space-x-2 mt-6">
                                    <button @click="showAddDocumentoTramitacion = false"
                                        class="px-4 py-2 text-gray-600 border rounded hover:bg-gray-50">
                                        Cancelar
                                    </button>
                                    <button @click="addDocumentoTramitacion()"
                                        :disabled="!nuevoDocumentoTramitacion.slug || !
                                            nuevoDocumentoTramitacion
                                            .nombre_personalizado"
                                        class="px-4 py-2 bg-[#54debd] text-white rounded hover:bg-[#43c5a9] disabled:opacity-50">
                                        Añadir
                                    </button>
                                </div>
                            </div>
                        </div>

                        <template
                            x-if="detalle.documentosTramitacion && detalle.documentosTramitacion.length">
                            <template x-for="(doc, idx) in detalle.documentosTramitacion"
                                :key="`tram-doc-${doc.id}-${idx}`">
                                <div
                                    class="flex justify-between items-center border p-2 rounded mb-2">
                                    <div class="flex items-center space-x-2">
                                        <span x-text="doc.name"></span>
                                        <span x-show="doc.es_personalizado"
                                            class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Personalizado</span>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <template
                                            x-for="ud in detalle.user.user_documents.filter(u =>
                                            String(u.slug) === String(doc.slug) &&
                                            u.conviviente_index == null &&
                                            (doc.es_personalizado ? u.nombre_personalizado === doc.nombre_personalizado : true)
                                        )"
                                            :key="ud.id">
                                            <div class="flex items-center space-x-2">
                                                <a :href="ud.temporary_url" target="_blank"
                                                    class="text-blue-600 hover:underline text-sm">Ver
                                                    documento</a>
                                                <button
                                                    @click.prevent="if(confirm('¿Seguro que quieres eliminar este documento?')) eliminarDocumento(ud.id, $el)"
                                                    class="px-2 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">Eliminar</button>
                                            </div>
                                        </template>

                                        <template
                                            x-if="detalle.user.user_documents.filter(u =>
                                            String(u.slug) === String(doc.slug) &&
                                            u.conviviente_index == null &&
                                            (doc.es_personalizado ? u.nombre_personalizado === doc.nombre_personalizado : true)
                                        ).length === 0">
                                            <div class="flex items-center space-x-2">
                                                <input type="file" :id="`file-${doc.id}-null`"
                                                    @change="handleMissingFile(doc.id, null, $event.target)"
                                                    multiple class="text-sm text-gray-500" />
                                                <button :disabled="!hasMissing(`${doc.id}-null`)"
                                                    @click.prevent="uploadMissing(doc.document_id, null, doc.slug, doc.nombre_personalizado)"
                                                    class="px-3 py-1 bg-[#54debd] text-white rounded text-xs hover:bg-[#43c5a9] disabled:opacity-50 transition">
                                                    Subir <span
                                                        x-show="getMissingCount(`${doc.id}-null`) > 0"
                                                        x-text="`(${getMissingCount(`${doc.id}-null`)})`"
                                                        class="ml-1"></span>
                                                </button>
                                            </div>
                                        </template>

                                        <button x-show="doc.es_personalizado"
                                            @click="removeDocumentoTramitacion(doc.id)"
                                            class="px-2 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700"
                                            title="Eliminar documento de tramitación">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </template>

                        <template
                            x-if="documentosInternosDisponibles && documentosInternosDisponibles.length && 
                                       documentosInternosDisponibles.filter(docInterno => {
                                           const yaEnTramitacion = detalle.documentosTramitacion.some(docTram => String(docTram.slug) === String(docInterno.slug));
                                           const tieneDocumentos = detalle.user.user_documents.some(u => String(u.slug) === String(docInterno.slug) && u.conviviente_index == null);
                                           return !yaEnTramitacion && tieneDocumentos;
                                       }).length > 0">
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <h6 class="font-medium text-gray-700 mb-2">Documentos Internos
                                    Subidos</h6>
                                <template
                                    x-for="doc in documentosInternosDisponibles.filter(docInterno => {
                                    const yaEnTramitacion = detalle.documentosTramitacion.some(docTram => String(docTram.slug) === String(docInterno.slug));
                                    const tieneDocumentos = detalle.user.user_documents.some(u => String(u.slug) === String(docInterno.slug) && u.conviviente_index == null);
                                    return !yaEnTramitacion && tieneDocumentos;
                                })"
                                    :key="`interno-${doc.id}`">
                                    <template
                                        x-for="ud in detalle.user.user_documents.filter(u => 
                                        String(u.slug) === String(doc.slug) && u.conviviente_index == null
                                    )"
                                        :key="`ud-${ud.id}`">
                                        <div
                                            class="flex justify-between items-center border p-2 rounded mb-2 bg-gray-50">
                                            <div class="flex items-center space-x-2">
                                                <span x-text="doc.name"></span>
                                                <span
                                                    class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">Interno</span>
                                            </div>

                                            <div class="flex items-center space-x-2">
                                                <a :href="ud.temporary_url" target="_blank"
                                                    class="text-blue-600 hover:underline text-sm">Ver
                                                    documento</a>
                                                <button
                                                    @click.prevent="if(confirm('¿Seguro que quieres eliminar este documento?')) eliminarDocumento(ud.id, $el)"
                                                    class="px-2 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">Eliminar</button>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </template>

                        <div x-show="(!detalle.documentosTramitacion || detalle.documentosTramitacion.length === 0) && 
                                   (!documentosInternosDisponibles || documentosInternosDisponibles.length === 0)"
                            class="text-gray-500 text-sm">
                            No hay documentos de tramitación configurados.
                        </div>
                    </div>

                </div>
            </template>

            {{-- Datos --}}
            <style>
                [x-cloak] {
                    display: none !important
                }

                .sortable-ghost {
                    opacity: 0.4;
                }

                .sortable-chosen {
                    background-color: #f3f4f6;
                    border: 2px dashed #54debd;
                }

                .layout-controls {
                    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                    border: 1px solid #cbd5e1;
                }

                .column-layout-1 {
                    grid-template-columns: 1fr;
                }

                .column-layout-2 {
                    grid-template-columns: 1fr 1fr;
                }

                .column-layout-3 {
                    grid-template-columns: 1fr 1fr 1fr;
                }

                .column-layout-4 {
                    grid-template-columns: 1fr 1fr 1fr 1fr;
                }

                .column-layout-5 {
                    grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
                }

                @media (max-width: 768px) {

                    .column-layout-2,
                    .column-layout-3,
                    .column-layout-4,
                    .column-layout-5 {
                        grid-template-columns: 1fr;
                    }
                }

                @media (max-width: 1024px) {

                    .column-layout-4,
                    .column-layout-5 {
                        grid-template-columns: 1fr 1fr;
                    }
                }
            </style>

            <div x-show="tab==='datos'" class="space-y-1 p-8 h-full max-w-7xl mx-auto"
                x-data="{
                    subTab: 'solicitante',
                    tabBase: 'px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100',
                    tabActive: 'px-3 py-2 rounded-md text-sm font-semibold bg-[#54debd] text-white',
                    mostrarConfiguracionDatos: false,
                    layoutConfig: {
                        solicitante: { rows: [] },
                        contrato: { rows: [] },
                        direccion: { rows: [] },
                        hijos: { rows: [] },
                        convivientes: { rows: [] },
                        arrendadores: { rows: [] }
                    },
                    editingLayout: false,
                    currentSection: null,
                    _lastDetalleId: null,
                
                    init() {
                        // Cargar configuración guardada primero
                        this.loadLayoutConfig();
                        // Inicializar configuración de layout solo si no hay configuración guardada
                        // Esto mantiene la configuración personalizada del usuario
                        if (detalle && detalle.id) {
                            // Solo inicializar si no hay filas configuradas
                            const hasConfig = Object.keys(this.layoutConfig).some(section =>
                                this.layoutConfig[section].rows && this.layoutConfig[section].rows.length > 0
                            );
                            if (!hasConfig) {
                                this.initializeLayoutConfig();
                            }
                        }
                    },
                
                    initializeLayoutConfig() {
                        // Usar detalle del contexto padre, no this.detalle
                        if (!detalle || typeof detalle !== 'object' || !detalle.id) return;
                
                        const sectionToDataKey = {
                            solicitante: 'solicitanteDatos',
                            contrato: 'contratoDatos',
                            direccion: 'direccionDatos',
                            hijos: 'hijoDatos', // ojo: es hijoDatos (no hijosDatos)
                            convivientes: 'convivienteDatos',
                            arrendadores: 'arrendadorDatos'
                        };
                
                        const getDataArrayForSection = (section) => {
                            const key = sectionToDataKey[section];
                            const arr = key ? detalle[key] : [];
                            return Array.isArray(arr) ? arr : [];
                        };
                
                        // Solo inicializar filas si no existen, manteniendo la configuración personalizada
                        ['solicitante', 'contrato', 'direccion', 'hijos'].forEach(section => {
                            const dataArr = getDataArrayForSection(section);
                
                            // Solo crear filas si no hay configuración existente
                            if (!this.layoutConfig[section].rows || this.layoutConfig[section].rows.length === 0) {
                                if (dataArr.length > 0) {
                                    this.layoutConfig[section].rows = dataArr.map((_, index) => ({
                                        id: `row-${section}-${index}-${Date.now()}`,
                                        columns: 1,
                                        fields: [index]
                                    }));
                                } else {
                                    this.layoutConfig[section].rows = [];
                                }
                            } else {
                                // Si ya hay configuración, solo asegurar que los índices de fields sean válidos
                                const maxIndex = dataArr.length - 1;
                                this.layoutConfig[section].rows.forEach(row => {
                                    if (row.fields) {
                                        // Filtrar campos que ya no existen en los nuevos datos
                                        row.fields = row.fields.filter(fieldIndex => fieldIndex <= maxIndex);
                                    }
                                });
                            }
                        });
                
                        // Inicializar convivientes
                        if (!this.layoutConfig.convivientes.rows || this.layoutConfig.convivientes.rows.length === 0) {
                            const convivientesArr = getDataArrayForSection('convivientes');
                            if (convivientesArr.length > 0 && convivientesArr[0].datos && Array.isArray(convivientesArr[0].datos) && convivientesArr[0].datos.length > 0) {
                                const camposCount = convivientesArr[0].datos.length;
                                this.layoutConfig.convivientes.rows = convivientesArr[0].datos.map((_, index) => ({
                                    id: `row-convivientes-${index}-${Date.now()}`,
                                    columns: 1,
                                    fields: [index]
                                }));
                            } else {
                                this.layoutConfig.convivientes.rows = [{ id: `row-convivientes-0-${Date.now()}`, columns: 1, fields: [] }];
                            }
                        } else {
                            // Validar índices existentes
                            const convivientesArr = getDataArrayForSection('convivientes');
                            if (convivientesArr.length > 0 && convivientesArr[0].datos && Array.isArray(convivientesArr[0].datos)) {
                                const maxIndex = convivientesArr[0].datos.length - 1;
                                this.layoutConfig.convivientes.rows.forEach(row => {
                                    if (row.fields) {
                                        row.fields = row.fields.filter(fieldIndex => fieldIndex <= maxIndex);
                                    }
                                });
                            }
                        }
                
                        // Inicializar arrendadores
                        if (!this.layoutConfig.arrendadores.rows || this.layoutConfig.arrendadores.rows.length === 0) {
                            const arrendadoresArr = getDataArrayForSection('arrendadores');
                            if (arrendadoresArr.length > 0 && arrendadoresArr[0].preguntas && Array.isArray(arrendadoresArr[0].preguntas) && arrendadoresArr[0].preguntas.length > 0) {
                                const camposCount = arrendadoresArr[0].preguntas.length;
                                this.layoutConfig.arrendadores.rows = arrendadoresArr[0].preguntas.map((_, index) => ({
                                    id: `row-arrendadores-${index}-${Date.now()}`,
                                    columns: 1,
                                    fields: [index]
                                }));
                            } else {
                                this.layoutConfig.arrendadores.rows = [{ id: `row-arrendadores-0-${Date.now()}`, columns: 1, fields: [] }];
                            }
                        } else {
                            // Validar índices existentes
                            const arrendadoresArr = getDataArrayForSection('arrendadores');
                            if (arrendadoresArr.length > 0 && arrendadoresArr[0].preguntas && Array.isArray(arrendadoresArr[0].preguntas)) {
                                const maxIndex = arrendadoresArr[0].preguntas.length - 1;
                                this.layoutConfig.arrendadores.rows.forEach(row => {
                                    if (row.fields) {
                                        row.fields = row.fields.filter(fieldIndex => fieldIndex <= maxIndex);
                                    }
                                });
                            }
                        }
                    },
                
                    ensureLayoutForCurrentData() {
                        // Asegurar que existan filas para todos los datos actuales, sin perder la configuración personalizada
                        if (!detalle || typeof detalle !== 'object' || !detalle.id) return;
                
                        const sectionToDataKey = {
                            solicitante: 'solicitanteDatos',
                            contrato: 'contratoDatos',
                            direccion: 'direccionDatos',
                            hijos: 'hijoDatos',
                            convivientes: 'convivienteDatos',
                            arrendadores: 'arrendadorDatos'
                        };
                
                        const getDataArrayForSection = (section) => {
                            const key = sectionToDataKey[section];
                            const arr = key ? detalle[key] : [];
                            return Array.isArray(arr) ? arr : [];
                        };
                
                        ['solicitante', 'contrato', 'direccion', 'hijos'].forEach(section => {
                            const dataArr = getDataArrayForSection(section);
                
                            if (dataArr.length === 0) {
                                // Si no hay datos, limpiar las filas
                                this.layoutConfig[section].rows = [];
                                return;
                            }
                
                            // Si no hay filas configuradas, crear una fila por cada dato
                            if (!this.layoutConfig[section].rows || this.layoutConfig[section].rows.length === 0) {
                                this.layoutConfig[section].rows = dataArr.map((_, index) => ({
                                    id: `row-${section}-${index}-${Date.now()}`,
                                    columns: 1,
                                    fields: [index]
                                }));
                            } else {
                                // Si hay filas configuradas, asegurar que todos los índices de datos estén presentes
                                const presentIndices = new Set();
                                this.layoutConfig[section].rows.forEach(row => {
                                    if (row.fields && Array.isArray(row.fields)) {
                                        row.fields.forEach(fieldIndex => {
                                            if (fieldIndex <= dataArr.length - 1) {
                                                presentIndices.add(fieldIndex);
                                            }
                                        });
                                    }
                                });
                
                                // Agregar filas para índices que faltan
                                for (let i = 0; i < dataArr.length; i++) {
                                    if (!presentIndices.has(i)) {
                                        // Buscar una fila existente para agregar el campo, o crear una nueva
                                        const existingRow = this.layoutConfig[section].rows.find(r => r.fields && r.fields.length < 3);
                                        if (existingRow) {
                                            existingRow.fields.push(i);
                                        } else {
                                            this.layoutConfig[section].rows.push({
                                                id: `row-${section}-${i}-${Date.now()}`,
                                                columns: 1,
                                                fields: [i]
                                            });
                                        }
                                    }
                                }
                
                                // Filtrar campos que ya no existen
                                const maxIndex = dataArr.length - 1;
                                this.layoutConfig[section].rows.forEach(row => {
                                    if (row.fields) {
                                        row.fields = row.fields.filter(fieldIndex => fieldIndex <= maxIndex);
                                    }
                                });
                
                                // Eliminar filas vacías
                                this.layoutConfig[section].rows = this.layoutConfig[section].rows.filter(row =>
                                    row.fields && row.fields.length > 0
                                );
                            }
                        });
                
                        // Manejar convivientes
                        const convivientesArr = getDataArrayForSection('convivientes');
                        let convivientesCampos = [];
                        if (convivientesArr.length > 0 && convivientesArr[0].datos && Array.isArray(convivientesArr[0].datos)) {
                            convivientesCampos = convivientesArr[0].datos;
                        }
                
                        if (convivientesCampos.length === 0) {
                            // Si no hay campos, mantener al menos una fila vacía
                            if (!this.layoutConfig.convivientes.rows || this.layoutConfig.convivientes.rows.length === 0) {
                                this.layoutConfig.convivientes.rows = [{ id: `row-convivientes-0-${Date.now()}`, columns: 1, fields: [] }];
                            }
                        } else {
                            if (!this.layoutConfig.convivientes.rows || this.layoutConfig.convivientes.rows.length === 0) {
                                this.layoutConfig.convivientes.rows = convivientesCampos.map((_, index) => ({
                                    id: `row-convivientes-${index}-${Date.now()}`,
                                    columns: 1,
                                    fields: [index]
                                }));
                            } else {
                                // Validar y actualizar índices
                                const presentIndices = new Set();
                                this.layoutConfig.convivientes.rows.forEach(row => {
                                    if (row.fields && Array.isArray(row.fields)) {
                                        row.fields.forEach(fieldIndex => {
                                            if (fieldIndex <= convivientesCampos.length - 1) {
                                                presentIndices.add(fieldIndex);
                                            }
                                        });
                                    }
                                });
                
                                // Agregar campos faltantes
                                for (let i = 0; i < convivientesCampos.length; i++) {
                                    if (!presentIndices.has(i)) {
                                        const existingRow = this.layoutConfig.convivientes.rows.find(r => r.fields && r.fields.length < 3);
                                        if (existingRow) {
                                            existingRow.fields.push(i);
                                        } else {
                                            this.layoutConfig.convivientes.rows.push({
                                                id: `row-convivientes-${i}-${Date.now()}`,
                                                columns: 1,
                                                fields: [i]
                                            });
                                        }
                                    }
                                }
                
                                // Filtrar campos que ya no existen
                                const maxIndex = convivientesCampos.length - 1;
                                this.layoutConfig.convivientes.rows.forEach(row => {
                                    if (row.fields) {
                                        row.fields = row.fields.filter(fieldIndex => fieldIndex <= maxIndex);
                                    }
                                });
                
                                // Eliminar filas vacías
                                this.layoutConfig.convivientes.rows = this.layoutConfig.convivientes.rows.filter(row =>
                                    row.fields && row.fields.length > 0
                                );
                            }
                        }
                
                        // Manejar arrendadores
                        const arrendadoresArr = getDataArrayForSection('arrendadores');
                        let arrendadoresCampos = [];
                        if (arrendadoresArr.length > 0 && arrendadoresArr[0].preguntas && Array.isArray(arrendadoresArr[0].preguntas)) {
                            arrendadoresCampos = arrendadoresArr[0].preguntas;
                        }
                
                        if (arrendadoresCampos.length === 0) {
                            // Si no hay campos, mantener al menos una fila vacía
                            if (!this.layoutConfig.arrendadores.rows || this.layoutConfig.arrendadores.rows.length === 0) {
                                this.layoutConfig.arrendadores.rows = [{ id: `row-arrendadores-0-${Date.now()}`, columns: 1, fields: [] }];
                            }
                        } else {
                            if (!this.layoutConfig.arrendadores.rows || this.layoutConfig.arrendadores.rows.length === 0) {
                                this.layoutConfig.arrendadores.rows = arrendadoresCampos.map((_, index) => ({
                                    id: `row-arrendadores-${index}-${Date.now()}`,
                                    columns: 1,
                                    fields: [index]
                                }));
                            } else {
                                // Validar y actualizar índices
                                const presentIndices = new Set();
                                this.layoutConfig.arrendadores.rows.forEach(row => {
                                    if (row.fields && Array.isArray(row.fields)) {
                                        row.fields.forEach(fieldIndex => {
                                            if (fieldIndex <= arrendadoresCampos.length - 1) {
                                                presentIndices.add(fieldIndex);
                                            }
                                        });
                                    }
                                });
                
                                // Agregar campos faltantes
                                for (let i = 0; i < arrendadoresCampos.length; i++) {
                                    if (!presentIndices.has(i)) {
                                        const existingRow = this.layoutConfig.arrendadores.rows.find(r => r.fields && r.fields.length < 3);
                                        if (existingRow) {
                                            existingRow.fields.push(i);
                                        } else {
                                            this.layoutConfig.arrendadores.rows.push({
                                                id: `row-arrendadores-${i}-${Date.now()}`,
                                                columns: 1,
                                                fields: [i]
                                            });
                                        }
                                    }
                                }
                
                                // Filtrar campos que ya no existen
                                const maxIndex = arrendadoresCampos.length - 1;
                                this.layoutConfig.arrendadores.rows.forEach(row => {
                                    if (row.fields) {
                                        row.fields = row.fields.filter(fieldIndex => fieldIndex <= maxIndex);
                                    }
                                });
                
                                // Eliminar filas vacías
                                this.layoutConfig.arrendadores.rows = this.layoutConfig.arrendadores.rows.filter(row =>
                                    row.fields && row.fields.length > 0
                                );
                            }
                        }
                
                        // Asegurar que todas las filas tengan fields como array
                        Object.keys(this.layoutConfig).forEach(section => {
                            this.layoutConfig[section].rows.forEach(row => {
                                if (!Array.isArray(row.fields)) row.fields = [];
                            });
                        });
                    },
                
                    setRowColumns(section, rowId, columns) {
                        const row = this.layoutConfig[section].rows.find(r => r.id === rowId);
                        if (row) {
                            row.columns = columns;
                            this.saveLayoutConfig();
                        }
                    },
                
                    addRow(section) {
                        const newRowId = `row-${Date.now()}`;
                        this.layoutConfig[section].rows.push({
                            id: newRowId,
                            columns: 1,
                            fields: []
                        });
                        this.saveLayoutConfig();
                        // Reinicializar sortable después de añadir fila
                        this.$nextTick(() => {
                            if (this.editingLayout && this.currentSection === section) {
                                this.initializeSortable();
                            }
                        });
                    },
                
                    removeRow(section, rowId) {
                        this.layoutConfig[section].rows = this.layoutConfig[section].rows.filter(r => r.id !== rowId);
                        this.saveLayoutConfig();
                    },
                
                    startLayoutEdit(section) {
                        this.editingLayout = true;
                        this.currentSection = section;
                
                        // Asegurar que la sección tenga al menos una fila
                        if (!this.layoutConfig[section].rows.length) {
                            this.addRow(section);
                        }
                
                        this.$nextTick(() => {
                            setTimeout(() => {
                                this.initializeSortable();
                            }, 100);
                        });
                    },
                
                    stopLayoutEdit() {
                        this.destroySortable();
                        this.editingLayout = false;
                        this.currentSection = null;
                    },
                
                    initializeSortable() {
                        console.log('Inicializando Sortable para sección:', this.currentSection);
                
                        if (typeof Sortable === 'undefined') {
                            console.error('SortableJS no está cargado');
                            return;
                        }
                
                        if (!this.currentSection || !this.layoutConfig[this.currentSection]) {
                            console.error('Sección no válida para inicializar Sortable');
                            return;
                        }
                
                        // Destruir instancias existentes de Sortable
                        this.destroySortable();
                
                        // Inicializar sortable para cada fila
                        this.layoutConfig[this.currentSection].rows.forEach((row, index) => {
                            const containerId = `sortable-${this.currentSection}-${row.id}`;
                            const container = document.getElementById(containerId);
                            console.log(`Inicializando fila ${index}:`, containerId, container);
                
                            if (container) {
                                // Marcar el contenedor para poder destruirlo después
                                container._sortableInstance = new Sortable(container, {
                                    animation: 150,
                                    ghostClass: 'sortable-ghost',
                                    chosenClass: 'sortable-chosen',
                                    group: 'shared',
                                    onEnd: (evt) => {
                                        console.log('Sortable onEnd:', evt);
                
                                        // Obtener el campo movido
                                        const movedFieldIndex = parseInt(evt.item.dataset.index);
                                        const fromContainer = evt.from;
                                        const toContainer = evt.to;
                
                                        // Si se movió entre contenedores diferentes
                                        if (fromContainer !== toContainer) {
                                            // Encontrar las filas origen y destino
                                            const fromRowId = fromContainer.id.replace(`sortable-${this.currentSection}-`, '');
                                            const toRowId = toContainer.id.replace(`sortable-${this.currentSection}-`, '');
                
                                            const fromRow = this.layoutConfig[this.currentSection].rows.find(r => r.id === fromRowId);
                                            const toRow = this.layoutConfig[this.currentSection].rows.find(r => r.id === toRowId);
                
                                            if (fromRow && toRow) {
                                                // Remover el campo de la fila origen
                                                fromRow.fields = fromRow.fields.filter(f => f !== movedFieldIndex);
                
                                                // Añadir el campo a la fila destino
                                                toRow.fields.push(movedFieldIndex);
                
                                                console.log('Campo movido de fila', fromRowId, 'a fila', toRowId);
                                                console.log('Fila origen fields:', fromRow.fields);
                                                console.log('Fila destino fields:', toRow.fields);
                                            }
                                        } else {
                                            // Si se movió dentro del mismo contenedor, solo actualizar el orden
                                            const newOrder = Array.from(container.children)
                                                .filter(el => el.dataset.index !== undefined)
                                                .map(el => parseInt(el.dataset.index));
                                            row.fields = newOrder;
                                        }
                
                                        this.saveLayoutConfig();
                                    }
                                });
                                console.log('Sortable inicializado para:', containerId);
                            } else {
                                console.warn('Contenedor no encontrado:', containerId);
                            }
                        });
                    },
                
                    destroySortable() {
                        // Destruir todas las instancias de Sortable existentes
                        Object.keys(this.layoutConfig).forEach(section => {
                            this.layoutConfig[section].rows.forEach(row => {
                                const container = document.getElementById(`sortable-${section}-${row.id}`);
                                if (container && container._sortableInstance) {
                                    container._sortableInstance.destroy();
                                    container._sortableInstance = null;
                                }
                            });
                        });
                    },
                
                    getFieldData(section, fieldIndex) {
                        // Asegurarse de que siempre se lea del detalle actual del contexto padre
                        // Acceder directamente al detalle del componente padre para obtener siempre los datos más recientes
                        let currentDetalle = detalle;
                
                        // Si no está disponible en el contexto actual, intentar obtenerlo del componente padre
                        if (!currentDetalle || !currentDetalle.id) {
                            const parentElement = $el.closest('[x-data]');
                            if (parentElement && parentElement._x_dataStack) {
                                const parentData = parentElement._x_dataStack[0];
                                if (parentData && parentData.detalle) {
                                    currentDetalle = parentData.detalle;
                                }
                            }
                        }
                
                        if (!currentDetalle || typeof currentDetalle !== 'object' || !currentDetalle.id) return null;
                
                        const sectionToDataKey = {
                            solicitante: 'solicitanteDatos',
                            contrato: 'contratoDatos',
                            direccion: 'direccionDatos',
                            hijos: 'hijoDatos',
                            convivientes: 'convivienteDatos',
                            arrendadores: 'arrendadorDatos'
                        };
                
                        const dataKey = sectionToDataKey[section] || (section + 'Datos');
                
                        // Para convivientes y arrendadores, la estructura es diferente
                        if (section === 'convivientes') {
                            // Los datos están en el primer conviviente (o cualquier conviviente) en el array 'datos'
                            if (!currentDetalle[dataKey] || !Array.isArray(currentDetalle[dataKey]) || currentDetalle[dataKey].length === 0) {
                                return null;
                            }
                            const firstConviviente = currentDetalle[dataKey][0];
                            if (!firstConviviente || !firstConviviente.datos || !Array.isArray(firstConviviente.datos) || !firstConviviente.datos[fieldIndex]) {
                                return null;
                            }
                            return { ...firstConviviente.datos[fieldIndex] };
                        }
                
                        if (section === 'arrendadores') {
                            // Los datos están en el primer arrendador (o cualquier arrendador) en el array 'preguntas'
                            if (!currentDetalle[dataKey] || !Array.isArray(currentDetalle[dataKey]) || currentDetalle[dataKey].length === 0) {
                                return null;
                            }
                            const firstArrendador = currentDetalle[dataKey][0];
                            if (!firstArrendador || !firstArrendador.preguntas || !Array.isArray(firstArrendador.preguntas) || !firstArrendador.preguntas[fieldIndex]) {
                                return null;
                            }
                            return { ...firstArrendador.preguntas[fieldIndex] };
                        }
                
                        // Para las otras secciones (solicitante, contrato, direccion, hijos)
                        if (!currentDetalle[dataKey] || !Array.isArray(currentDetalle[dataKey]) || !currentDetalle[dataKey][fieldIndex]) {
                            return null;
                        }
                
                        // Retornar una copia del objeto para asegurar reactividad
                        return { ...currentDetalle[dataKey][fieldIndex] };
                    },
                
                    moveFieldToRow(section, fieldIndex, targetRowId) {
                        // Remover el campo de todas las filas
                        this.layoutConfig[section].rows.forEach(row => {
                            row.fields = row.fields.filter(f => f !== fieldIndex);
                        });
                
                        // Añadir el campo a la fila objetivo
                        const targetRow = this.layoutConfig[section].rows.find(r => r.id === targetRowId);
                        if (targetRow) {
                            targetRow.fields.push(fieldIndex);
                        }
                
                        this.saveLayoutConfig();
                    },
                
                    saveLayoutConfig() {
                        // Guardar en localStorage para persistencia
                        localStorage.setItem('datosLayoutConfig', JSON.stringify(this.layoutConfig));
                    },
                
                    loadLayoutConfig() {
                        const saved = localStorage.getItem('datosLayoutConfig');
                        if (saved) {
                            this.layoutConfig = { ...this.layoutConfig, ...JSON.parse(saved) };
                        }
                    },
                
                    resetLayoutConfig() {
                        if (confirm('¿Estás seguro de que quieres restablecer la configuración de layout? Esto eliminará todos los cambios personalizados.')) {
                            // Limpiar localStorage
                            localStorage.removeItem('datosLayoutConfig');
                
                            // Resetear configuración a valores por defecto
                            this.layoutConfig = {
                                solicitante: { rows: [] },
                                contrato: { rows: [] },
                                direccion: { rows: [] },
                                hijos: { rows: [] },
                                convivientes: { rows: [] },
                                arrendadores: { rows: [] }
                            };
                
                            // Reinicializar con datos por defecto
                            this.initializeLayoutConfig();
                
                            // Salir del modo edición
                            this.editingLayout = false;
                            this.currentSection = null;
                
                            alert('✅ Configuración restablecida correctamente');
                        }
                    }
                }" x-init="(() => {
                    // Inicializar arrendadoresDatos si no existe
                    if (detalle && !Array.isArray(detalle.arrendadoresDatos)) {
                        detalle.arrendadoresDatos = [];
                    }
                    // Cargar configuración de layout
                    loadLayoutConfig();
                })();"
                x-effect="
                    (() => {
                        // Solo actualizar cuando cambia el detalle.id, pero mantener la configuración de layout
                        const currentId = detalle && detalle.id ? detalle.id : null;
                        if (currentId && currentId !== _lastDetalleId) {
                            _lastDetalleId = currentId;
                            // Obtener el componente desde el elemento
                            const component = $el._x_dataStack && $el._x_dataStack[0];
                            if (component) {
                                // Usar setTimeout en lugar de this.$nextTick porque en x-effect this no es el componente
                                setTimeout(() => {
                                    // Asegurar que las filas existan para los nuevos datos
                                    if (component.ensureLayoutForCurrentData) {
                                        component.ensureLayoutForCurrentData();
                                    }
                                }, 100);
                            }
                        }
                    })();
                ">

                <!-- Indicador de carga -->
                <div x-show="!detalle"
                    class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600">
                        </div>
                        <span class="text-gray-600">Cargando datos...</span>
                    </div>
                </div>

                <!-- Banner informativo sobre filtrado por tarea en curso -->
                <div x-show="detalle && detalle.tarea_en_curso"
                    class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <i class="bx bx-info-circle text-blue-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-blue-800">Tarea en Curso</h4>
                            <p class="text-blue-700 font-medium"
                                x-text="detalle.tarea_en_curso?.nombre_completo || ''"></p>
                            <p x-show="detalle.tarea_en_curso?.descripcion"
                                class="text-blue-600 text-sm mt-1"
                                x-text="detalle.tarea_en_curso?.descripcion || ''"></p>
                            <div class="mt-3 p-3 bg-blue-100 rounded border border-blue-200">
                                <p class="text-blue-800 text-sm">
                                    <i class="bx bx-info-circle mr-1"></i>
                                    Los datos mostrados a continuación corresponden únicamente a
                                    esta tarea específica.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="detalle && !detalle.tarea_en_curso"
                    class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <i class="bx bx-info-circle text-gray-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-gray-800">Sin Tarea Activa</h4>
                            <p class="text-gray-600">No hay ninguna tarea en curso. Se muestran
                                todos los datos configurados para esta ayuda.</p>
                        </div>
                    </div>
                </div>

                <!-- Controles de Layout -->
                <div x-show="detalle" class="mb-6 p-4 layout-controls rounded-lg">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <i class="bx bx-layout text-blue-600 text-xl"></i>
                            <h4 class="text-lg font-semibold text-gray-800">Configuración de Datos
                            </h4>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button @click="resetLayoutConfig()"
                                class="px-3 py-1 bg-orange-500 hover:bg-orange-600 text-white rounded text-sm transition">
                                <i class="bx bx-reset"></i>
                                <span>Restablecer</span>
                            </button>
                            <button @click="editingLayout = !editingLayout"
                                :class="editingLayout ? 'bg-green-500 hover:bg-green-600' :
                                    'bg-blue-500 hover:bg-blue-600'"
                                class="px-3 py-1 text-white rounded text-sm transition">
                                <i class="bx" :class="editingLayout ? 'bx-x' : 'bx-edit'"></i>
                                <span x-text="editingLayout ? 'Guardar' : 'Editar Datos'"></span>
                            </button>
                        </div>
                    </div>

                    <div x-show="editingLayout" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <template
                                x-for="section in ['solicitante', 'contrato', 'direccion', 'hijos', 'convivientes', 'arrendadores']"
                                :key="section">
                                <div x-show="(section === 'convivientes' && detalle?.mostrarConvivientes) || (section === 'arrendadores') || (detalle[section + 'Datos'] && detalle[section + 'Datos'].length > 0)"
                                    class="bg-white p-4 rounded-lg border">
                                    <div class="flex justify-between items-center mb-3">
                                        <h5 class="font-medium text-gray-800 capitalize"
                                            x-text="section.replace(/([A-Z])/g, ' $1').trim()">
                                        </h5>
                                        <button @click="addRow(section)"
                                            class="px-2 py-1 bg-green-500 text-white rounded text-xs hover:bg-green-600 transition">
                                            <i class="bx bx-plus"></i>
                                        </button>
                                    </div>

                                    <!-- Lista de filas -->
                                    <div class="space-y-2 mb-3">
                                        <template
                                            x-for="(row, rowIndex) in layoutConfig[section].rows"
                                            :key="row.id">
                                            <div class="p-3 bg-gray-50 rounded border">
                                                <div
                                                    class="flex justify-between items-center mb-2">
                                                    <span class="text-sm font-medium">Fila <span
                                                            x-text="rowIndex + 1"></span></span>
                                                    <div class="flex space-x-1">
                                                        <button
                                                            @click="setRowColumns(section, row.id, 1)"
                                                            :class="row.columns === 1 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">1</button>
                                                        <button
                                                            @click="setRowColumns(section, row.id, 2)"
                                                            :class="row.columns === 2 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">2</button>
                                                        <button
                                                            @click="setRowColumns(section, row.id, 3)"
                                                            :class="row.columns === 3 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">3</button>
                                                        <button
                                                            @click="setRowColumns(section, row.id, 4)"
                                                            :class="row.columns === 4 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">4</button>
                                                        <button
                                                            @click="setRowColumns(section, row.id, 5)"
                                                            :class="row.columns === 5 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">5</button>
                                                        <button @click="removeRow(section, row.id)"
                                                            class="px-2 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="text-xs text-gray-600">
                                                    <span x-text="row.fields.length"></span>
                                                    campos
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Botón para reordenar -->
                                    <button @click="startLayoutEdit(section)"
                                        class="w-full px-3 py-2 bg-green-500 text-white rounded text-sm hover:bg-green-600 transition">
                                        <i class="bx bx-sort mr-1"></i>
                                        Reordenar Campos
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div id="estado-mensaje" style="display:none;"></div>

                <!-- NAV de sub-secciones -->
                <nav x-show="detalle"
                    class="sticky top-0 z-40 -mx-8 px-8 bg-white/95 backdrop-blur border-b shadow-sm">
                    <ul class="flex flex-wrap p-2">
                        <li>
                            <button type="button"
                                :class="subTab === 'solicitante' ? tabActive : tabBase"
                                @click="subTab='solicitante'">
                                Solicitante
                            </button>
                        </li>
                        <li>
                            <button type="button"
                                :class="subTab === 'contrato' ? tabActive : tabBase"
                                @click="subTab='contrato'">
                                Contrato
                            </button>
                        </li>
                        <li>
                            <button type="button"
                                :class="subTab === 'direccion' ? tabActive : tabBase"
                                @click="subTab='direccion'">
                                Dirección
                            </button>
                        </li>
                        <li x-show="detalle?.hijoDatos && detalle.hijoDatos.length > 0">
                            <button type="button"
                                :class="subTab === 'hijos' ? tabActive : tabBase"
                                @click="subTab='hijos'">
                                Hijos
                            </button>
                        </li>
                        <li x-show="detalle?.mostrarConvivientes">
                            <button type="button"
                                :class="subTab === 'convivientes' ? tabActive : tabBase"
                                @click="subTab='convivientes'">
                                Convivientes
                            </button>
                        </li>
                        <!-- SIEMPRE visible para poder crear arrendadores aunque aún no haya -->
                        <li>
                            <button type="button"
                                :class="subTab === 'arrendadores' ? tabActive : tabBase"
                                @click="subTab='arrendadores'">
                                Arrendadores
                            </button>
                        </li>
                    </ul>
                </nav>

                <form x-show="detalle" id="form-guardar-datos" method="POST"
                    :action="`/contrataciones/${detalle.id}/update-datos`">
                    @csrf
                    @method('PATCH')

                    <!-- Ancho: 1 columna a todos los breakpoints -->
                    <div class="grid grid-cols-1 gap-6">

                        <!-- Datos del Solicitante -->
                        <template
                            x-if="detalle.solicitanteDatos && detalle.solicitanteDatos.length">
                            <div class="bg-gray-100 rounded-lg" x-data="{ open: false }"
                                x-show="subTab==='solicitante'"
                                x-effect="open = (subTab==='solicitante')" x-cloak>
                                <div class="w-full text-left px-6 py-4 font-semibold flex justify-between items-center cursor-pointer"
                                    @click="open = !open">
                                    <span>Datos del Solicitante</span>
                                    <i class="bx"
                                        :class="open ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                                </div>
                                <div class="px-6 pb-6" x-show="open" x-transition>
                                    <!-- Modo edición de layout -->
                                    <div x-show="editingLayout && currentSection === 'solicitante'"
                                        class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-center justify-between mb-3">
                                            <h6 class="font-medium text-yellow-800">Modo Edición -
                                                Arrastra campos entre filas</h6>
                                            <button @click="stopLayoutEdit()"
                                                class="text-yellow-600 hover:text-yellow-800">
                                                <i class="bx bx-x text-xl"></i>
                                            </button>
                                        </div>

                                        <!-- Filas de edición -->
                                        <template
                                            x-for="(row, rowIndex) in layoutConfig.solicitante.rows"
                                            :key="row.id">
                                            <div
                                                class="mb-4 p-3 bg-white border border-yellow-300 rounded">
                                                <div
                                                    class="flex justify-between items-center mb-2">
                                                    <span class="font-medium text-sm">Fila <span
                                                            x-text="rowIndex + 1"></span> (<span
                                                            x-text="row.columns"></span>
                                                        columna<span
                                                            x-text="row.columns > 1 ? 's' : ''"></span>)</span>
                                                    <div class="flex space-x-1">
                                                        <button
                                                            @click="setRowColumns('solicitante', row.id, 1)"
                                                            :class="row.columns === 1 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">1</button>
                                                        <button
                                                            @click="setRowColumns('solicitante', row.id, 2)"
                                                            :class="row.columns === 2 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">2</button>
                                                        <button
                                                            @click="setRowColumns('solicitante', row.id, 3)"
                                                            :class="row.columns === 3 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">3</button>
                                                        <button
                                                            @click="setRowColumns('solicitante', row.id, 4)"
                                                            :class="row.columns === 4 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">4</button>
                                                        <button
                                                            @click="setRowColumns('solicitante', row.id, 5)"
                                                            :class="row.columns === 5 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">5</button>
                                                    </div>
                                                </div>
                                                <div :id="`sortable-solicitante-${row.id}`"
                                                    class="min-h-[50px] p-2 border-2 border-dashed border-yellow-400 rounded">
                                                    <template
                                                        x-for="(fieldIndex, fieldIdx) in (row.fields || [])"
                                                        :key="`sort-solicitante-${row.id}-${fieldIdx}-${fieldIndex}`">
                                                        <div :data-index="fieldIndex"
                                                            class="inline-block p-2 m-1 bg-yellow-100 border border-yellow-300 rounded cursor-move hover:bg-yellow-200">
                                                            <div
                                                                class="flex items-center space-x-2">
                                                                <i
                                                                    class="bx bx-grip-vertical text-yellow-600"></i>
                                                                <span class="text-sm font-medium"
                                                                    x-text="getFieldData('solicitante', fieldIndex)?.text"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <div x-show="!row.fields || row.fields.length === 0"
                                                        class="text-center text-gray-500 text-sm py-4">
                                                        Arrastra campos aquí
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Modo visualización normal -->
                                    <div x-show="!editingLayout || currentSection !== 'solicitante'"
                                        class="space-y-4">
                                        <template
                                            x-for="(row, rowIndex) in layoutConfig.solicitante.rows"
                                            :key="`display-row-${row.id}`">
                                            <div
                                                :class="`grid gap-4 column-layout-${row.columns}`">
                                                <template
                                                    x-for="(fieldIndex, fieldIdx) in (row.fields || [])"
                                                    :key="`solicitante-${row.id}-${fieldIdx}-${fieldIndex}`">
                                                    <template
                                                        x-if="getFieldData('solicitante', fieldIndex)">
                                                        <div class="mb-4"
                                                            x-data="{
                                                                get dato() { return getFieldData('solicitante', fieldIndex); },
                                                                i: fieldIndex
                                                            }"
                                                            :key="`solicitante-${fieldIndex}-${detalle?.id || 0}`">
                                                            <p class="font-medium text-gray-800"
                                                                x-text="dato.text"></p>
                                                            <template
                                                                x-if="dato.type === 'boolean'">
                                                                <select
                                                                    :name="`solicitanteDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2"
                                                                    x-init="// Forzar la precarga del valor después de un breve delay
                                                                    setTimeout(() => {
                                                                        if (dato.answer !== '' && dato.answer !== null) {
                                                                            $el.value = dato.answer;
                                                                        }
                                                                    }, 100);">
                                                                    <option value="">
                                                                        Selecciona</option>
                                                                    <option value="1">Sí
                                                                    </option>
                                                                    <option value="0">No
                                                                    </option>
                                                                </select>

                                                            </template>
                                                            <template
                                                                x-if="dato.type === 'select'">
                                                                <select
                                                                    :name="`solicitanteDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    :data-value="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2"
                                                                    x-init="// Forzar la precarga del valor después de un breve delay
                                                                    setTimeout(() => {
                                                                        if (dato.answer !== '' && dato.answer !== null) {
                                                                            $el.value = dato.answer;
                                                                        }
                                                                    }, 100);">
                                                                    <option value="">
                                                                        Selecciona</option>
                                                                    <template
                                                                        x-for="(label, index) in dato.options"
                                                                        :key="index">
                                                                        <option
                                                                            :value="String(index)"
                                                                            x-text="label">
                                                                        </option>
                                                                    </template>
                                                                </select>

                                                            </template>
                                                            <template
                                                                x-if="dato.type === 'multiple'">
                                                                <div>
                                                                    <template
                                                                        x-for="(label, key) in dato.options"
                                                                        :key="key">
                                                                        <label
                                                                            class="inline-flex items-center mr-3">
                                                                            <input type="checkbox"
                                                                                :name="`solicitanteDatos[${i}][answer][]`"
                                                                                :value="key"
                                                                                x-model="dato.answer">
                                                                            <span class="ml-1"
                                                                                x-text="label"></span>
                                                                        </label>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <template x-if="dato.type === 'date'">
                                                                <input type="date"
                                                                    :name="`solicitanteDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2">
                                                            </template>
                                                            <template
                                                                x-if="dato.type === 'number'">
                                                                <input type="number"
                                                                    :name="`solicitanteDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2">
                                                            </template>
                                                            <template
                                                                x-if="!['boolean','select','multiple','date','number'].includes(dato.type)">
                                                                <input type="text"
                                                                    :name="`solicitanteDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2">
                                                            </template>
                                                            <input type="hidden"
                                                                :name="`solicitanteDatos[${i}][question_slug]`"
                                                                :value="dato.slug">
                                                        </div>
                                                    </template>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Datos de Contrato -->
                        <template x-if="detalle.contratoDatos && detalle.contratoDatos.length">
                            <div class="bg-gray-100 rounded-lg" x-data="{ open: false }"
                                x-show="subTab==='contrato'"
                                x-effect="open = (subTab==='contrato')" x-cloak>
                                <div class="w-full text-left px-6 py-4 font-semibold flex justify-between items-center cursor-pointer"
                                    @click="open = !open">
                                    <span>Datos de Contrato</span>
                                    <i class="bx"
                                        :class="open ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                                </div>
                                <div class="px-6 pb-6" x-show="open" x-transition>
                                    <!-- Modo edición de layout -->
                                    <div x-show="editingLayout && currentSection === 'contrato'"
                                        class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-center justify-between mb-3">
                                            <h6 class="font-medium text-yellow-800">Modo Edición -
                                                Arrastra campos entre filas</h6>
                                            <button @click="stopLayoutEdit()"
                                                class="text-yellow-600 hover:text-yellow-800">
                                                <i class="bx bx-x text-xl"></i>
                                            </button>
                                        </div>

                                        <!-- Filas de edición -->
                                        <template
                                            x-for="(row, rowIndex) in layoutConfig.contrato.rows"
                                            :key="row.id">
                                            <div
                                                class="mb-4 p-3 bg-white border border-yellow-300 rounded">
                                                <div
                                                    class="flex justify-between items-center mb-2">
                                                    <span class="font-medium text-sm">Fila <span
                                                            x-text="rowIndex + 1"></span> (<span
                                                            x-text="row.columns"></span>
                                                        columna<span
                                                            x-text="row.columns > 1 ? 's' : ''"></span>)</span>
                                                    <div class="flex space-x-1">
                                                        <button
                                                            @click="setRowColumns('contrato', row.id, 1)"
                                                            :class="row.columns === 1 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">1</button>
                                                        <button
                                                            @click="setRowColumns('contrato', row.id, 2)"
                                                            :class="row.columns === 2 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">2</button>
                                                        <button
                                                            @click="setRowColumns('contrato', row.id, 3)"
                                                            :class="row.columns === 3 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">3</button>
                                                        <button
                                                            @click="setRowColumns('contrato', row.id, 4)"
                                                            :class="row.columns === 4 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">4</button>
                                                        <button
                                                            @click="setRowColumns('contrato', row.id, 5)"
                                                            :class="row.columns === 5 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">5</button>
                                                    </div>
                                                </div>
                                                <div :id="`sortable-contrato-${row.id}`"
                                                    class="min-h-[50px] p-2 border-2 border-dashed border-yellow-400 rounded">
                                                    <template
                                                        x-for="(fieldIndex, fieldIdx) in (row.fields || [])"
                                                        :key="`sort-contrato-${row.id}-${fieldIdx}-${fieldIndex}`">
                                                        <div :data-index="fieldIndex"
                                                            class="inline-block p-2 m-1 bg-yellow-100 border border-yellow-300 rounded cursor-move hover:bg-yellow-200">
                                                            <div
                                                                class="flex items-center space-x-2">
                                                                <i
                                                                    class="bx bx-grip-vertical text-yellow-600"></i>
                                                                <span class="text-sm font-medium"
                                                                    x-text="getFieldData('contrato', fieldIndex)?.text"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <div x-show="!row.fields || row.fields.length === 0"
                                                        class="text-center text-gray-500 text-sm py-4">
                                                        Arrastra campos aquí
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Modo visualización normal -->
                                    <div x-show="!editingLayout || currentSection !== 'contrato'"
                                        class="space-y-4">
                                        <template
                                            x-for="(row, rowIndex) in layoutConfig.contrato.rows"
                                            :key="`display-row-${row.id}`">
                                            <div
                                                :class="`grid gap-4 column-layout-${row.columns}`">
                                                <template
                                                    x-for="(fieldIndex, fieldIdx) in (row.fields || [])"
                                                    :key="`contrato-${row.id}-${fieldIdx}-${fieldIndex}`">
                                                    <template
                                                        x-if="getFieldData('contrato', fieldIndex)">
                                                        <div class="mb-4"
                                                            x-data="{
                                                                get dato() { return getFieldData('contrato', fieldIndex); },
                                                                i: fieldIndex
                                                            }"
                                                            :key="`contrato-${fieldIndex}-${detalle?.id || 0}`">
                                                            <p class="font-medium text-gray-800"
                                                                x-text="dato.text"></p>
                                                            <template
                                                                x-if="dato.type === 'boolean'">
                                                                <select
                                                                    :name="`contratoDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2"
                                                                    x-init="// Forzar la precarga del valor después de un breve delay
                                                                    setTimeout(() => {
                                                                        if (dato.answer !== '' && dato.answer !== null) {
                                                                            $el.value = dato.answer;
                                                                        }
                                                                    }, 100);">
                                                                    <option value="">
                                                                        Selecciona</option>
                                                                    <option value="1">Sí
                                                                    </option>
                                                                    <option value="0">No
                                                                    </option>
                                                                </select>

                                                            </template>
                                                            <template
                                                                x-if="dato.type === 'select'">
                                                                <select
                                                                    :name="`contratoDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    :data-value="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2"
                                                                    x-init="// Forzar la precarga del valor después de un breve delay
                                                                    setTimeout(() => {
                                                                        if (dato.answer !== '' && dato.answer !== null) {
                                                                            $el.value = dato.answer;
                                                                        }
                                                                    }, 100);">
                                                                    <option value="">
                                                                        Selecciona</option>
                                                                    <template
                                                                        x-for="(label, index) in dato.options"
                                                                        :key="index">
                                                                        <option
                                                                            :value="String(index)"
                                                                            x-text="label">
                                                                        </option>
                                                                    </template>
                                                                </select>

                                                            </template>
                                                            <template
                                                                x-if="dato.type === 'multiple'">
                                                                <div>
                                                                    <template
                                                                        x-for="(label, key) in dato.options"
                                                                        :key="key">
                                                                        <label
                                                                            class="inline-flex items-center mr-3">
                                                                            <input type="checkbox"
                                                                                :name="`contratoDatos[${i}][answer][]`"
                                                                                :value="key"
                                                                                x-model="dato.answer">
                                                                            <span class="ml-1"
                                                                                x-text="label"></span>
                                                                        </label>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <template x-if="dato.type === 'date'">
                                                                <input type="date"
                                                                    :name="`contratoDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2">
                                                            </template>
                                                            <template
                                                                x-if="dato.type === 'number'">
                                                                <input type="number"
                                                                    :name="`contratoDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2">
                                                            </template>
                                                            <template
                                                                x-if="!['boolean','select','multiple','date','number'].includes(dato.type)">
                                                                <input type="text"
                                                                    :name="`contratoDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2">
                                                            </template>
                                                            <input type="hidden"
                                                                :name="`contratoDatos[${i}][question_slug]`"
                                                                :value="dato.slug">
                                                        </div>
                                                    </template>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Datos de Dirección -->
                        <template x-if="detalle.direccionDatos && detalle.direccionDatos.length">
                            <div class="bg-gray-100 rounded-lg" x-data="{ open: false }"
                                x-show="subTab==='direccion'"
                                x-effect="open = (subTab==='direccion')" x-cloak>
                                <div class="w-full text-left px-6 py-4 font-semibold flex justify-between items-center cursor-pointer"
                                    @click="open = !open">
                                    <span>Datos de Dirección</span>
                                    <i class="bx"
                                        :class="open ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                                </div>
                                <div class="px-6 pb-6" x-show="open" x-transition>
                                    <!-- Modo edición de layout -->
                                    <div x-show="editingLayout && currentSection === 'direccion'"
                                        class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-center justify-between mb-3">
                                            <h6 class="font-medium text-yellow-800">Modo Edición -
                                                Arrastra campos entre filas</h6>
                                            <button @click="stopLayoutEdit()"
                                                class="text-yellow-600 hover:text-yellow-800">
                                                <i class="bx bx-x text-xl"></i>
                                            </button>
                                        </div>

                                        <!-- Filas de edición -->
                                        <template
                                            x-for="(row, rowIndex) in layoutConfig.direccion.rows"
                                            :key="row.id">
                                            <div
                                                class="mb-4 p-3 bg-white border border-yellow-300 rounded">
                                                <div
                                                    class="flex justify-between items-center mb-2">
                                                    <span class="font-medium text-sm">Fila <span
                                                            x-text="rowIndex + 1"></span> (<span
                                                            x-text="row.columns"></span>
                                                        columna<span
                                                            x-text="row.columns > 1 ? 's' : ''"></span>)</span>
                                                    <div class="flex space-x-1">
                                                        <button
                                                            @click="setRowColumns('direccion', row.id, 1)"
                                                            :class="row.columns === 1 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">1</button>
                                                        <button
                                                            @click="setRowColumns('direccion', row.id, 2)"
                                                            :class="row.columns === 2 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">2</button>
                                                        <button
                                                            @click="setRowColumns('direccion', row.id, 3)"
                                                            :class="row.columns === 3 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">3</button>
                                                        <button
                                                            @click="setRowColumns('direccion', row.id, 4)"
                                                            :class="row.columns === 4 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">4</button>
                                                        <button
                                                            @click="setRowColumns('direccion', row.id, 5)"
                                                            :class="row.columns === 5 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">5</button>
                                                    </div>
                                                </div>
                                                <div :id="`sortable-direccion-${row.id}`"
                                                    class="min-h-[50px] p-2 border-2 border-dashed border-yellow-400 rounded">
                                                    <template
                                                        x-for="(fieldIndex, fieldIdx) in (row.fields || [])"
                                                        :key="`sort-direccion-${row.id}-${fieldIdx}-${fieldIndex}`">
                                                        <div :data-index="fieldIndex"
                                                            class="inline-block p-2 m-1 bg-yellow-100 border border-yellow-300 rounded cursor-move hover:bg-yellow-200">
                                                            <div
                                                                class="flex items-center space-x-2">
                                                                <i
                                                                    class="bx bx-grip-vertical text-yellow-600"></i>
                                                                <span class="text-sm font-medium"
                                                                    x-text="getFieldData('direccion', fieldIndex)?.text"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <div x-show="!row.fields || row.fields.length === 0"
                                                        class="text-center text-gray-500 text-sm py-4">
                                                        Arrastra campos aquí
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Modo visualización normal -->
                                    <div x-show="!editingLayout || currentSection !== 'direccion'"
                                        class="space-y-4">
                                        <template
                                            x-for="(row, rowIndex) in layoutConfig.direccion.rows"
                                            :key="`display-row-${row.id}`">
                                            <div
                                                :class="`grid gap-4 column-layout-${row.columns}`">
                                                <template
                                                    x-for="(fieldIndex, fieldIdx) in (row.fields || [])"
                                                    :key="`direccion-${row.id}-${fieldIdx}-${fieldIndex}`">
                                                    <template
                                                        x-if="getFieldData('direccion', fieldIndex)">
                                                        <div class="mb-4"
                                                            x-data="{
                                                                get dato() { return getFieldData('direccion', fieldIndex); },
                                                                i: fieldIndex
                                                            }"
                                                            :key="`direccion-${fieldIndex}-${detalle?.id || 0}`">
                                                            <p class="font-medium text-gray-800"
                                                                x-text="dato.text"></p>
                                                            <template
                                                                x-if="dato.type === 'boolean'">
                                                                <select
                                                                    :name="`direccionDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2"
                                                                    x-init="// Forzar la precarga del valor después de un breve delay
                                                                    setTimeout(() => {
                                                                        if (dato.answer !== '' && dato.answer !== null) {
                                                                            $el.value = dato.answer;
                                                                        }
                                                                    }, 100);">
                                                                    <option value="">
                                                                        Selecciona</option>
                                                                    <option value="1">Sí
                                                                    </option>
                                                                    <option value="0">No
                                                                    </option>
                                                                </select>

                                                            </template>
                                                            <template
                                                                x-if="dato.type === 'select' && !dato.is_provincia && !dato.is_municipio">
                                                                <select
                                                                    :name="`direccionDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    :data-value="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2"
                                                                    x-init="// Forzar la precarga del valor después de un breve delay
                                                                    setTimeout(() => {
                                                                        if (dato.answer !== '' && dato.answer !== null) {
                                                                            $el.value = dato.answer;
                                                                        }
                                                                    }, 100);">
                                                                    <option value="">
                                                                        Selecciona</option>
                                                                    <template
                                                                        x-for="(label, index) in dato.options"
                                                                        :key="index">
                                                                        <option
                                                                            :value="String(index)"
                                                                            x-text="label">
                                                                        </option>
                                                                    </template>
                                                                </select>

                                                            </template>
                                                            <template x-if="dato.is_provincia">
                                                                <select
                                                                    :name="`direccionDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    :data-value="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2"
                                                                    @change="onProvinciaChange($event, dato)">
                                                                    <option value="">
                                                                        Selecciona una provincia
                                                                    </option>
                                                                    <template
                                                                        x-for="(nombre, id) in provincias"
                                                                        :key="id">
                                                                        <option
                                                                            :value="nombre"
                                                                            x-text="nombre">
                                                                        </option>
                                                                    </template>
                                                                </select>
                                                            </template>
                                                            <template x-if="dato.is_municipio">
                                                                <select
                                                                    :name="`direccionDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    :data-value="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2"
                                                                    :id="`municipio_select_${i}`">
                                                                    <option value="">
                                                                        Selecciona primero una
                                                                        provincia</option>
                                                                </select>
                                                            </template>
                                                            <template
                                                                x-if="dato.type === 'multiple'">
                                                                <div>
                                                                    <template
                                                                        x-for="(label, key) in dato.options"
                                                                        :key="key">
                                                                        <label
                                                                            class="inline-flex items-center mr-3">
                                                                            <input type="checkbox"
                                                                                :name="`direccionDatos[${i}][answer][]`"
                                                                                :value="key"
                                                                                x-model="dato.answer">
                                                                            <span class="ml-1"
                                                                                x-text="label"></span>
                                                                        </label>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <template x-if="dato.type === 'date'">
                                                                <input type="date"
                                                                    :name="`direccionDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2">
                                                            </template>
                                                            <template
                                                                x-if="dato.type === 'number'">
                                                                <input type="number"
                                                                    :name="`direccionDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2">
                                                            </template>
                                                            <template
                                                                x-if="!['boolean','select','multiple','date','number'].includes(dato.type)">
                                                                <input type="text"
                                                                    :name="`direccionDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2">
                                                            </template>
                                                            <input type="hidden"
                                                                :name="`direccionDatos[${i}][question_slug]`"
                                                                :value="dato.slug">
                                                        </div>
                                                    </template>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Datos de Hijos -->
                        <template x-if="detalle.hijoDatos && detalle.hijoDatos.length">
                            <div class="bg-gray-100 rounded-lg" x-data="{ open: false }"
                                x-show="subTab==='hijos'" x-effect="open = (subTab==='hijos')"
                                x-cloak>
                                <div class="w-full text-left px-6 py-4 font-semibold flex justify-between items-center cursor-pointer"
                                    @click="open = !open">
                                    <span>Datos de Hijos</span>
                                    <i class="bx"
                                        :class="open ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                                </div>
                                <div class="px-6 pb-6" x-show="open" x-transition>
                                    <!-- Modo edición de layout -->
                                    <div x-show="editingLayout && currentSection === 'hijos'"
                                        class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-center justify-between mb-3">
                                            <h6 class="font-medium text-yellow-800">Modo Edición -
                                                Arrastra campos entre filas</h6>
                                            <button @click="stopLayoutEdit()"
                                                class="text-yellow-600 hover:text-yellow-800">
                                                <i class="bx bx-x text-xl"></i>
                                            </button>
                                        </div>

                                        <!-- Filas de edición -->
                                        <template
                                            x-for="(row, rowIndex) in layoutConfig.hijos.rows"
                                            :key="row.id">
                                            <div
                                                class="mb-4 p-3 bg-white border border-yellow-300 rounded">
                                                <div
                                                    class="flex justify-between items-center mb-2">
                                                    <span class="font-medium text-sm">Fila <span
                                                            x-text="rowIndex + 1"></span> (<span
                                                            x-text="row.columns"></span>
                                                        columna<span
                                                            x-text="row.columns > 1 ? 's' : ''"></span>)</span>
                                                    <div class="flex space-x-1">
                                                        <button
                                                            @click="setRowColumns('hijos', row.id, 1)"
                                                            :class="row.columns === 1 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">1</button>
                                                        <button
                                                            @click="setRowColumns('hijos', row.id, 2)"
                                                            :class="row.columns === 2 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">2</button>
                                                        <button
                                                            @click="setRowColumns('hijos', row.id, 3)"
                                                            :class="row.columns === 3 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">3</button>
                                                        <button
                                                            @click="setRowColumns('hijos', row.id, 4)"
                                                            :class="row.columns === 4 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">4</button>
                                                        <button
                                                            @click="setRowColumns('hijos', row.id, 5)"
                                                            :class="row.columns === 5 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">5</button>
                                                    </div>
                                                </div>
                                                <div :id="`sortable-hijos-${row.id}`"
                                                    class="min-h-[50px] p-2 border-2 border-dashed border-yellow-400 rounded">
                                                    <template
                                                        x-for="(fieldIndex, fieldIdx) in (row.fields || [])"
                                                        :key="`sort-hijos-${row.id}-${fieldIdx}-${fieldIndex}`">
                                                        <div :data-index="fieldIndex"
                                                            class="inline-block p-2 m-1 bg-yellow-100 border border-yellow-300 rounded cursor-move hover:bg-yellow-200">
                                                            <div
                                                                class="flex items-center space-x-2">
                                                                <i
                                                                    class="bx bx-grip-vertical text-yellow-600"></i>
                                                                <span class="text-sm font-medium"
                                                                    x-text="getFieldData('hijos', fieldIndex)?.text"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <div x-show="!row.fields || row.fields.length === 0"
                                                        class="text-center text-gray-500 text-sm py-4">
                                                        Arrastra campos aquí
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Modo visualización normal -->
                                    <div x-show="!editingLayout || currentSection !== 'hijos'"
                                        class="space-y-4">
                                        <template
                                            x-for="(row, rowIndex) in layoutConfig.hijos.rows"
                                            :key="`display-row-${row.id}`">
                                            <div
                                                :class="`grid gap-4 column-layout-${row.columns}`">
                                                <template
                                                    x-for="(fieldIndex, fieldIdx) in (row.fields || [])"
                                                    :key="`hijo-${row.id}-${fieldIdx}-${fieldIndex}`">
                                                    <template
                                                        x-if="getFieldData('hijos', fieldIndex)">
                                                        <div class="mb-4"
                                                            x-data="{
                                                                get dato() { return getFieldData('hijos', fieldIndex); },
                                                                i: fieldIndex
                                                            }"
                                                            :key="`hijos-${fieldIndex}-${detalle?.id || 0}`">
                                                            <p class="font-medium text-gray-800"
                                                                x-text="dato.text"></p>
                                                            <template
                                                                x-if="dato.type === 'boolean'">
                                                                <select
                                                                    :name="`hijoDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2"
                                                                    x-init="// Forzar la precarga del valor después de un breve delay
                                                                    setTimeout(() => {
                                                                        if (dato.answer !== '' && dato.answer !== null) {
                                                                            $el.value = dato.answer;
                                                                        }
                                                                    }, 100);">
                                                                    <option value="">
                                                                        Selecciona</option>
                                                                    <option value="1">Sí
                                                                    </option>
                                                                    <option value="0">No
                                                                    </option>
                                                                </select>

                                                            </template>
                                                            <template
                                                                x-if="dato.type === 'select'">
                                                                <select
                                                                    :name="`hijoDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    :data-value="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2"
                                                                    x-init="// Forzar la precarga del valor después de un breve delay
                                                                    setTimeout(() => {
                                                                        if (dato.answer !== '' && dato.answer !== null) {
                                                                            $el.value = dato.answer;
                                                                        }
                                                                    }, 100);">
                                                                    <option value="">
                                                                        Selecciona</option>
                                                                    <template
                                                                        x-for="(label, index) in dato.options"
                                                                        :key="index">
                                                                        <option
                                                                            :value="String(index)"
                                                                            x-text="label">
                                                                        </option>
                                                                    </template>
                                                                </select>

                                                            </template>
                                                            <template
                                                                x-if="dato.type === 'multiple'">
                                                                <div>
                                                                    <template
                                                                        x-for="(label, key) in dato.options"
                                                                        :key="key">
                                                                        <label
                                                                            class="inline-flex items-center mr-3">
                                                                            <input type="checkbox"
                                                                                :name="`hijoDatos[${i}][answer][]`"
                                                                                :value="key"
                                                                                x-model="dato.answer">
                                                                            <span class="ml-1"
                                                                                x-text="label"></span>
                                                                        </label>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <template x-if="dato.type === 'date'">
                                                                <input type="date"
                                                                    :name="`hijoDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2">
                                                            </template>
                                                            <template
                                                                x-if="dato.type === 'number'">
                                                                <input type="number"
                                                                    :name="`hijoDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2">
                                                            </template>
                                                            <template
                                                                x-if="!['boolean','select','multiple','date','number'].includes(dato.type)">
                                                                <input type="text"
                                                                    :name="`hijoDatos[${i}][answer]`"
                                                                    x-model="dato.answer"
                                                                    class="mt-1 w-full border rounded p-2">
                                                            </template>
                                                            <input type="hidden"
                                                                :name="`hijoDatos[${i}][question_slug]`"
                                                                :value="dato.slug">
                                                        </div>
                                                    </template>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Convivientes -->
                        <template x-if="detalle?.mostrarConvivientes">
                            <div class="bg-gray-100 rounded-lg" x-data="{ open: true }"
                                x-show="subTab==='convivientes'"
                                x-effect="open = (subTab==='convivientes')" x-cloak>
                                <button type="button"
                                    class="w-full text-left px-6 py-4 font-semibold flex justify-between items-center"
                                    @click="open = !open" :aria-expanded="open.toString()">
                                    <span>Datos de Convivientes</span>
                                    <svg class="w-5 h-5 transform transition-transform duration-200"
                                        :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20"
                                        fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.06z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div class="px-6 pb-6" x-show="open" x-transition>
                                    <!-- Modo edición de layout -->
                                    <div x-show="editingLayout && currentSection === 'convivientes'"
                                        class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-center justify-between mb-3">
                                            <h6 class="font-medium text-yellow-800">Modo Edición -
                                                Arrastra campos entre filas</h6>
                                            <button @click="stopLayoutEdit()"
                                                class="text-yellow-600 hover:text-yellow-800">
                                                <i class="bx bx-x text-xl"></i>
                                            </button>
                                        </div>

                                        <!-- Filas de edición -->
                                        <template
                                            x-for="(row, rowIndex) in layoutConfig.convivientes.rows"
                                            :key="row.id">
                                            <div
                                                class="mb-4 p-3 bg-white border border-yellow-300 rounded">
                                                <div
                                                    class="flex justify-between items-center mb-2">
                                                    <span class="font-medium text-sm">Fila <span
                                                            x-text="rowIndex + 1"></span> (<span
                                                            x-text="row.columns"></span>
                                                        columna<span
                                                            x-text="row.columns > 1 ? 's' : ''"></span>)</span>
                                                    <div class="flex space-x-1">
                                                        <button
                                                            @click="setRowColumns('convivientes', row.id, 1)"
                                                            :class="row.columns === 1 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">1</button>
                                                        <button
                                                            @click="setRowColumns('convivientes', row.id, 2)"
                                                            :class="row.columns === 2 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">2</button>
                                                        <button
                                                            @click="setRowColumns('convivientes', row.id, 3)"
                                                            :class="row.columns === 3 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">3</button>
                                                        <button
                                                            @click="setRowColumns('convivientes', row.id, 4)"
                                                            :class="row.columns === 4 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">4</button>
                                                        <button
                                                            @click="setRowColumns('convivientes', row.id, 5)"
                                                            :class="row.columns === 5 ?
                                                                'bg-blue-500 text-white' :
                                                                'bg-gray-200 text-gray-700'"
                                                            class="px-2 py-1 rounded text-xs">5</button>
                                                    </div>
                                                </div>
                                                <div :id="`sortable-convivientes-${row.id}`"
                                                    class="min-h-[50px] p-2 border-2 border-dashed border-yellow-400 rounded">
                                                    <template x-for="fieldIndex in row.fields"
                                                        :key="`sort-convivientes-${fieldIndex}`">
                                                        <div :data-index="fieldIndex"
                                                            class="inline-block p-2 m-1 bg-yellow-100 border border-yellow-300 rounded cursor-move hover:bg-yellow-200">
                                                            <div
                                                                class="flex items-center space-x-2">
                                                                <i
                                                                    class="bx bx-grip-vertical text-yellow-600"></i>
                                                                <span class="text-sm font-medium"
                                                                    x-text="getFieldData('convivientes', fieldIndex)?.text"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <div x-show="!row.fields || row.fields.length === 0"
                                                        class="text-center text-gray-500 text-sm py-4">
                                                        Arrastra campos aquí
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Modo visualización normal -->
                                    <div x-show="!editingLayout || currentSection !== 'convivientes'"
                                        class="space-y-4">
                                        <div class="flex justify-end mb-4">
                                            <button type="button" @click="addConviviente()"
                                                class="px-3 py-1 bg-[#54debd] text-white rounded text-xs hover:bg-[#43c5a9] transition">Añadir
                                                conviviente</button>
                                        </div>

                                        <template
                                            x-if="Array.isArray(detalle.convivienteDatos) && detalle.convivienteDatos.length">
                                            <template
                                                x-for="(block, idx) in detalle.convivienteDatos"
                                                :key="block.conviviente_id ?? idx">
                                                <div class="border rounded mb-3 overflow-hidden"
                                                    x-data="{ openConv: idx === 0 }">
                                                    <button type="button"
                                                        class="w-full flex items-center justify-between px-4 py-3 bg-white hover:bg-gray-50"
                                                        @click="openConv = !openConv"
                                                        :aria-expanded="openConv.toString()">
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-medium">Conviviente
                                                                #<span
                                                                    x-text="block.index"></span></span>
                                                            <span
                                                                class="text-xs text-gray-500">ID:
                                                                <span
                                                                    x-text="block.conviviente_id"></span></span>
                                                        </div>
                                                        <svg class="w-5 h-5 transform transition-transform duration-200"
                                                            :class="openConv ? 'rotate-180' : ''"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                            aria-hidden="true">
                                                            <path fill-rule="evenodd"
                                                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.06z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                    <button type="button"
                                                        @click="removeConviviente(idx, block)"
                                                        class="mb-4 px-2 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700 transition">Eliminar
                                                        conviviente</button>
                                                    <div class="px-4 pb-4 bg-gray-50"
                                                        x-show="openConv" x-transition>
                                                        <template
                                                            x-for="(row, rowIndex) in layoutConfig.convivientes.rows"
                                                            :key="`display-row-convivientes-${row.id}-${idx}`">
                                                            <div
                                                                :class="`grid gap-4 column-layout-${row.columns} mb-4`">
                                                                <template
                                                                    x-for="(fieldIndex, fieldIdx) in (row.fields || [])"
                                                                    :key="`conviviente-${idx}-${row.id}-${fieldIdx}-${fieldIndex}`">
                                                                    <template
                                                                        x-if="block.datos && block.datos[fieldIndex]">
                                                                        <div class="mb-4"
                                                                            x-data="{
                                                                                get dato() { return block.datos[fieldIndex]; },
                                                                                idx: idx,
                                                                                j: fieldIndex
                                                                            }"
                                                                            :key="`conviviente-field-${idx}-${fieldIndex}-${detalle?.id || 0}`">
                                                                            <p class="font-medium text-gray-800"
                                                                                x-text="dato.text">
                                                                            </p>
                                                                            <template
                                                                                x-if="dato.type === 'boolean'">
                                                                                <select
                                                                                    :name="`convivienteDatos[${idx}][datos][${j}][answer]`"
                                                                                    x-model="dato.answer"
                                                                                    class="mt-1 w-full border rounded p-2"
                                                                                    x-init="setTimeout(() => {
                                                                                        if (dato.answer !== '' && dato.answer !== null) {
                                                                                            $el.value = dato.answer;
                                                                                        }
                                                                                    }, 100);">
                                                                                    <option
                                                                                        value="">
                                                                                        Selecciona
                                                                                    </option>
                                                                                    <option
                                                                                        value="1">
                                                                                        Sí</option>
                                                                                    <option
                                                                                        value="0">
                                                                                        No</option>
                                                                                </select>
                                                                            </template>
                                                                            <template
                                                                                x-if="dato.type === 'select'">
                                                                                <select
                                                                                    :name="`convivienteDatos[${idx}][datos][${j}][answer]`"
                                                                                    x-model="dato.answer"
                                                                                    :data-value="dato.answer"
                                                                                    class="mt-1 w-full border rounded p-2"
                                                                                    x-init="setTimeout(() => {
                                                                                        if (dato.answer !== '' && dato.answer !== null) {
                                                                                            $el.value = dato.answer;
                                                                                        }
                                                                                    }, 100);">
                                                                                    <option
                                                                                        value="">
                                                                                        Selecciona
                                                                                    </option>
                                                                                    <template
                                                                                        x-for="(label, index) in dato.options"
                                                                                        :key="index">
                                                                                        <option
                                                                                            :value="String(
                                                                                                index
                                                                                            )"
                                                                                            x-text="label">
                                                                                        </option>
                                                                                    </template>
                                                                                </select>
                                                                            </template>
                                                                            <template
                                                                                x-if="dato.type === 'multiple'">
                                                                                <div>
                                                                                    <template
                                                                                        x-for="(label, key) in dato.options"
                                                                                        :key="key">
                                                                                        <label
                                                                                            class="inline-flex items-center mr-3">
                                                                                            <input
                                                                                                type="checkbox"
                                                                                                :name="`convivienteDatos[${idx}][datos][${j}][answer][]`"
                                                                                                :value="parseInt
                                                                                                    (
                                                                                                        key
                                                                                                    )"
                                                                                                x-model="dato.answer">
                                                                                            <span
                                                                                                class="ml-1"
                                                                                                x-text="label"></span>
                                                                                        </label>
                                                                                    </template>
                                                                                </div>
                                                                            </template>
                                                                            <template
                                                                                x-if="dato.type === 'date'">
                                                                                <input
                                                                                    type="date"
                                                                                    :name="`convivienteDatos[${idx}][datos][${j}][answer]`"
                                                                                    x-model="dato.answer"
                                                                                    class="mt-1 w-full border rounded p-2">
                                                                            </template>
                                                                            <template
                                                                                x-if="dato.type === 'number'">
                                                                                <input
                                                                                    type="number"
                                                                                    :name="`convivienteDatos[${idx}][datos][${j}][answer]`"
                                                                                    x-model="dato.answer"
                                                                                    class="mt-1 w-full border rounded p-2">
                                                                            </template>
                                                                            <template
                                                                                x-if="!['boolean','select','multiple','date','number'].includes(dato.type)">
                                                                                <input
                                                                                    type="text"
                                                                                    :name="`convivienteDatos[${idx}][datos][${j}][answer]`"
                                                                                    x-model="dato.answer"
                                                                                    class="mt-1 w-full border rounded p-2">
                                                                            </template>
                                                                            <input type="hidden"
                                                                                :name="`convivienteDatos[${idx}][datos][${j}][question_slug]`"
                                                                                :value="dato.slug">
                                                                            <input type="hidden"
                                                                                :name="`convivienteDatos[${idx}][datos][${j}][conviviente_id]`"
                                                                                :value="block.conviviente_id">
                                                                        </div>
                                                                    </template>
                                                                </template>
                                                            </div>
                                                        </template>
                                                        <input type="hidden"
                                                            :name="`convivienteDatos[${idx}][conviviente_id]`"
                                                            :value="block.conviviente_id">
                                                    </div>
                                                </div>
                                            </template>
                                        </template>

                                        <template
                                            x-if="!detalle.convivienteDatos || !detalle.convivienteDatos.length">
                                            <div class="mb-6 p-4 border rounded space-y-3">
                                                <p class="font-medium">No hay convivientes
                                                    creados. Pulsa en "Añadir
                                                    conviviente" para crear uno.</p>
                                                <button type="button" @click="addConviviente()"
                                                    class="px-3 py-1 bg-[#54debd] text-white rounded text-xs hover:bg-[#43c5a9] transition">Añadir
                                                    conviviente</button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Datos de Arrendador (en tab SIEMPRE disponible) -->
                        <div class="bg-gray-100 rounded-lg" x-data="{ open: false }"
                            x-show="subTab==='arrendadores'"
                            x-effect="open = (subTab==='arrendadores')" x-cloak>
                            <div
                                class="w-full text-left px-6 py-4 font-semibold flex justify-between items-center">
                                <div class="flex items-center space-x-2">
                                    <span class="cursor-pointer" @click="open = !open">Datos de
                                        Arrendador</span>
                                    <i class="bx cursor-pointer"
                                        :class="open ? 'bx-chevron-up' : 'bx-chevron-down'"
                                        @click="open = !open"></i>
                                </div>
                                <button type="button" @click="addArrendador()"
                                    class="px-3 py-1 bg-[#54debd] text-white rounded text-xs hover:bg-[#43c5a9] transition">Añadir
                                    arrendador</button>
                            </div>

                            <div class="px-6 pb-6" x-show="open" x-transition>
                                <!-- Modo edición de layout -->
                                <div x-show="editingLayout && currentSection === 'arrendadores'"
                                    class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <div class="flex items-center justify-between mb-3">
                                        <h6 class="font-medium text-yellow-800">Modo Edición -
                                            Arrastra campos entre filas</h6>
                                        <button @click="stopLayoutEdit()"
                                            class="text-yellow-600 hover:text-yellow-800">
                                            <i class="bx bx-x text-xl"></i>
                                        </button>
                                    </div>

                                    <!-- Filas de edición -->
                                    <template
                                        x-for="(row, rowIndex) in layoutConfig.arrendadores.rows"
                                        :key="row.id">
                                        <div
                                            class="mb-4 p-3 bg-white border border-yellow-300 rounded">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="font-medium text-sm">Fila <span
                                                        x-text="rowIndex + 1"></span> (<span
                                                        x-text="row.columns"></span> columna<span
                                                        x-text="row.columns > 1 ? 's' : ''"></span>)</span>
                                                <div class="flex space-x-1">
                                                    <button
                                                        @click="setRowColumns('arrendadores', row.id, 1)"
                                                        :class="row.columns === 1 ?
                                                            'bg-blue-500 text-white' :
                                                            'bg-gray-200 text-gray-700'"
                                                        class="px-2 py-1 rounded text-xs">1</button>
                                                    <button
                                                        @click="setRowColumns('arrendadores', row.id, 2)"
                                                        :class="row.columns === 2 ?
                                                            'bg-blue-500 text-white' :
                                                            'bg-gray-200 text-gray-700'"
                                                        class="px-2 py-1 rounded text-xs">2</button>
                                                    <button
                                                        @click="setRowColumns('arrendadores', row.id, 3)"
                                                        :class="row.columns === 3 ?
                                                            'bg-blue-500 text-white' :
                                                            'bg-gray-200 text-gray-700'"
                                                        class="px-2 py-1 rounded text-xs">3</button>
                                                    <button
                                                        @click="setRowColumns('arrendadores', row.id, 4)"
                                                        :class="row.columns === 4 ?
                                                            'bg-blue-500 text-white' :
                                                            'bg-gray-200 text-gray-700'"
                                                        class="px-2 py-1 rounded text-xs">4</button>
                                                    <button
                                                        @click="setRowColumns('arrendadores', row.id, 5)"
                                                        :class="row.columns === 5 ?
                                                            'bg-blue-500 text-white' :
                                                            'bg-gray-200 text-gray-700'"
                                                        class="px-2 py-1 rounded text-xs">5</button>
                                                </div>
                                            </div>
                                            <div :id="`sortable-arrendadores-${row.id}`"
                                                class="min-h-[50px] p-2 border-2 border-dashed border-yellow-400 rounded">
                                                <template x-for="fieldIndex in row.fields"
                                                    :key="`sort-arrendadores-${fieldIndex}`">
                                                    <div :data-index="fieldIndex"
                                                        class="inline-block p-2 m-1 bg-yellow-100 border border-yellow-300 rounded cursor-move hover:bg-yellow-200">
                                                        <div class="flex items-center space-x-2">
                                                            <i
                                                                class="bx bx-grip-vertical text-yellow-600"></i>
                                                            <span class="text-sm font-medium"
                                                                x-text="getFieldData('arrendadores', fieldIndex)?.text"></span>
                                                        </div>
                                                    </div>
                                                </template>
                                                <div x-show="row.fields.length === 0"
                                                    class="text-center text-gray-500 text-sm py-4">
                                                    Arrastra campos aquí
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Modo visualización normal -->
                                <div x-show="!editingLayout || currentSection !== 'arrendadores'"
                                    class="space-y-4">
                                    <template
                                        x-if="Array.isArray(detalle.arrendadoresDatos) && detalle.arrendadoresDatos.length > 0">
                                        <template x-for="(arr, aIdx) in detalle.arrendadoresDatos"
                                            :key="`arrendador-${aIdx}`">
                                            <div class="mb-6 p-4 border rounded space-y-3">
                                                <p class="font-medium">Arrendador #<span
                                                        x-text="arr.index"></span></p>
                                                <button type="button"
                                                    @click="removeArrendador(aIdx, arr)"
                                                    class="px-2 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700 transition">Eliminar
                                                    arrendador</button>
                                                <input type="hidden"
                                                    :name="`arrendadorDatos[${aIdx}][arrendador_id]`"
                                                    :value="arr.arrendador_id">

                                                <template
                                                    x-for="(row, rowIndex) in layoutConfig.arrendadores.rows"
                                                    :key="`display-row-arrendadores-${row.id}-${aIdx}`">
                                                    <div
                                                        :class="`grid gap-4 column-layout-${row.columns} mb-4`">
                                                        <template
                                                            x-for="(fieldIndex, fieldIdx) in (row.fields || [])"
                                                            :key="`arrendador-${aIdx}-${row.id}-${fieldIdx}-${fieldIndex}`">
                                                            <template
                                                                x-if="arr.preguntas && arr.preguntas[fieldIndex]">
                                                                <div class="mb-4"
                                                                    x-data="{
                                                                        get preg() { return arr.preguntas[fieldIndex]; },
                                                                        aIdx: aIdx,
                                                                        pIdx: fieldIndex
                                                                    }"
                                                                    :key="`arrendador-field-${aIdx}-${fieldIndex}-${detalle?.id || 0}`">
                                                                    <p class="font-medium text-gray-800"
                                                                        x-text="preg.text"></p>
                                                                    <template
                                                                        x-if="preg.type === 'boolean'">
                                                                        <select
                                                                            :name="`arrendadorDatos[${aIdx}][preguntas][${pIdx}][answer]`"
                                                                            x-model="preg.answer"
                                                                            class="mt-1 w-full border rounded p-2"
                                                                            x-init="setTimeout(() => {
                                                                                if (preg.answer !== '' && preg.answer !== null) {
                                                                                    $el.value = preg.answer;
                                                                                }
                                                                            }, 100);">
                                                                            <option
                                                                                value="">
                                                                                Selecciona</option>
                                                                            <option
                                                                                value="1">Sí
                                                                            </option>
                                                                            <option
                                                                                value="0">No
                                                                            </option>
                                                                        </select>
                                                                    </template>
                                                                    <template
                                                                        x-if="preg.type === 'select'">
                                                                        <select
                                                                            :name="`arrendadorDatos[${aIdx}][preguntas][${pIdx}][answer]`"
                                                                            x-model="preg.answer"
                                                                            :data-value="preg.answer"
                                                                            class="mt-1 w-full border rounded p-2"
                                                                            x-init="setTimeout(() => {
                                                                                if (preg.answer !== '' && preg.answer !== null) {
                                                                                    $el.value = preg.answer;
                                                                                }
                                                                            }, 100);">
                                                                            <option
                                                                                value="">
                                                                                Selecciona</option>
                                                                            <template
                                                                                x-for="(label, index) in preg.options"
                                                                                :key="index">
                                                                                <option
                                                                                    :value="String(index)"
                                                                                    x-text="label">
                                                                                </option>
                                                                            </template>
                                                                        </select>
                                                                    </template>
                                                                    <template
                                                                        x-if="preg.type === 'multiple'">
                                                                        <div>
                                                                            <template
                                                                                x-for="(label, key) in preg.options"
                                                                                :key="key">
                                                                                <label
                                                                                    class="inline-flex items-center mr-3">
                                                                                    <input
                                                                                        type="checkbox"
                                                                                        :name="`arrendadorDatos[${aIdx}][preguntas][${pIdx}][answer][]`"
                                                                                        :value="parseInt(
                                                                                            key)"
                                                                                        x-model="preg.answer">
                                                                                    <span
                                                                                        class="ml-1"
                                                                                        x-text="label"></span>
                                                                                </label>
                                                                            </template>
                                                                        </div>
                                                                    </template>
                                                                    <template
                                                                        x-if="preg.type === 'date'">
                                                                        <input type="date"
                                                                            :name="`arrendadorDatos[${aIdx}][preguntas][${pIdx}][answer]`"
                                                                            x-model="preg.answer"
                                                                            class="mt-1 w-full border rounded p-2">
                                                                    </template>
                                                                    <template
                                                                        x-if="preg.type === 'number'">
                                                                        <input type="number"
                                                                            :name="`arrendadorDatos[${aIdx}][preguntas][${pIdx}][answer]`"
                                                                            x-model="preg.answer"
                                                                            class="mt-1 w-full border rounded p-2">
                                                                    </template>
                                                                    <template
                                                                        x-if="!['boolean','select','multiple','date','number'].includes(preg.type)">
                                                                        <input type="text"
                                                                            :name="`arrendadorDatos[${aIdx}][preguntas][${pIdx}][answer]`"
                                                                            x-model="preg.answer"
                                                                            class="mt-1 w-full border rounded p-2">
                                                                    </template>
                                                                    <input type="hidden"
                                                                        :name="`arrendadorDatos[${aIdx}][preguntas][${pIdx}][question_slug]`"
                                                                        :value="preg.slug">
                                                                </div>
                                                            </template>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </template>

                                    <template
                                        x-if="!Array.isArray(detalle.arrendadoresDatos) || detalle.arrendadoresDatos.length === 0">
                                        <div class="mb-6 p-4 border rounded space-y-3">
                                            <p class="font-medium">No hay arrendadores creados.
                                                Pulsa en "Añadir
                                                arrendador" para crear uno.</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Botones de acción para la pestaña 'Datos' -->
                    <div class="mt-4 flex justify-between items-center">
                        <button type="button" @click="mostrarConfiguracionDatos = true"
                            class="px-6 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-md shadow hover:from-blue-600 hover:to-blue-700 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-cog"></i>
                            <span>Configurar Datos de Ayuda</span>
                        </button>

                        <button type="submit"
                            class="px-4 py-2 bg-[#54debd] text-white rounded-md shadow hover:bg-[#4db6a1] transition">
                            Guardar Datos
                        </button>
                    </div>
                </form>

                <!-- Modal de Configuración de Datos de Ayuda -->
                <div x-show="mostrarConfiguracionDatos" x-transition.opacity
                    class="fixed inset-0 z-[200] flex items-center justify-center"
                    role="dialog" aria-modal="true" aria-labelledby="config-datos-title"
                    @keydown.escape.prevent.stop="mostrarConfiguracionDatos = false"
                    x-effect="document.body.style.overflow = mostrarConfiguracionDatos ? 'hidden' : ''">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/70"
                        @click="mostrarConfiguracionDatos = false"></div>

                    <!-- Panel del modal de configuración -->
                    <div x-ref="configPanel" x-transition tabindex="-1"
                        class="relative z-[201] w-[95vw] sm:w-[1200px] xl:w-[1400px] max-h-[90vh] bg-white rounded-2xl shadow-2xl overflow-y-auto flex flex-col"
                        @click.stop>

                        <!-- Header del modal de configuración -->
                        <header
                            class="px-6 py-4 border-b flex justify-between items-center sticky top-0 bg-white">
                            <div class="flex items-center space-x-4">
                                <div>
                                    <h3 id="config-datos-title" class="text-xl font-semibold">
                                        Configurar <span
                                            class="text-transparent bg-clip-text bg-gradient-to-r from-[#54debd] to-[#368e79]">Datos
                                            de Ayuda</span>
                                    </h3>
                                    <p class="text-gray-600">Configura las preguntas y datos
                                        necesarios para cada tipo de ayuda y tarea</p>
                                </div>
                            </div>

                            <button @click="mostrarConfiguracionDatos = false"
                                class="text-gray-500 hover:text-gray-800 text-2xl"
                                aria-label="Cerrar">×</button>
                        </header>

                        <!-- Contenido del formulario de configuración -->
                        <div class="flex-1 overflow-y-auto p-6" x-data="ayudaDatosConfig()"
                            x-init="init()">
                            @include('admin.ayuda_datos_form_content')
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="tab==='otras-contrataciones'" class="space-y-4">
                <div class="bg-gray-50 p-6 rounded-lg space-y-4">
                    <h4 class="text-xl font-semibold">Otras Contrataciones del Usuario</h4>
                    <p class="text-gray-600">Este usuario tiene las siguientes contrataciones
                        adicionales:</p>

                    <div class="space-y-3">
                        <template x-for="contratacion in otrasContrataciones"
                            :key="contratacion.id">
                            <div @click="openDetalle(contratacion.id)"
                                class="bg-white p-4 rounded-lg border border-gray-200 cursor-pointer hover:shadow-lg transition-shadow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h5 class="font-semibold text-gray-800"
                                            x-text="contratacion.ayuda_nombre">
                                        </h5>
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Estados OPx:</span>
                                            <span
                                                x-text="(contratacion.estados_opx && contratacion.estados_opx.length) ? contratacion.estados_opx.join(', ') : 'Ninguno'"
                                                class="text-emerald-600"></span>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Contratada el <span
                                                x-text="contratacion.fecha_contratacion"></span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Contratada
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="otrasContrataciones.length === 0" class="text-center py-8">
                            <div class="text-gray-400 mb-2">
                                <i class="bx bx-info-circle text-2xl"></i>
                            </div>
                            <p class="text-gray-500">No hay otras contrataciones para mostrar.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- Cierre de Cuerpo del Modal --}}
    </div>

</div>{{-- Cierre de 2ª columna: el side-panel --}}

<!-- SortableJS para arrastrar y soltar -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<!-- Script para la configuración de datos de ayuda -->
<script>
    function ayudaDatosConfig() {
        return {
            ayuda_id: '',
            questions: [],
            tareas: [],
            ayudas: {},
            selectedQuestions: [],
            datos: [],
            searchTerm: '',
            busquedaAyuda: '',
            tareaActiva: '',
            opcionTareaActiva: '',
            guardando: false,
            errores: [],
            tiposDato: [{
                    value: 'solicitante',
                    label: 'Solicitante'
                },
                {
                    value: 'hijo',
                    label: 'Hijo'
                },
                {
                    value: 'conviviente',
                    label: 'Conviviente'
                },
                {
                    value: 'contrato',
                    label: 'Contrato'
                },
                {
                    value: 'arrendador',
                    label: 'Arrendador'
                },
                {
                    value: 'direccion',
                    label: 'Dirección'
                },
            ],
            tipoDatoActivo: 'solicitante',
            tipoDatoMostrar: 'solicitante',

            // Variables para el modal de copiar datos
            todasLasAyudasDatos: [],
            ayudasDestinoSeleccionadasDatos: [],

            get ayudasFiltradas() {
                if (!this.busquedaAyuda) return this.ayudas;
                const busqueda = this.busquedaAyuda.toLowerCase();
                return Object.fromEntries(
                    Object.entries(this.ayudas).filter(([id, nombre]) =>
                        nombre.toLowerCase().includes(busqueda)
                    )
                );
            },

            get opcionesTareaActiva() {
                if (!this.tareaActiva) return [];
                const tarea = this.tareas.find(t => t.slug === this.tareaActiva);
                return tarea ? tarea.opciones_tareas : [];
            },

            get datosTareaActiva() {
                return this.datos.filter(d => d.tarea_id == this.getTareaId() && d
                    .opcion_tarea_id == this.opcionTareaActiva);
            },

            get datosTareaActivaFiltrados() {
                return this.datosTareaActiva.filter(d => d.tipo_dato === this.tipoDatoMostrar);
            },

            onTareaChange() {
                this.opcionTareaActiva = '';
                this.datos = [];
                if (this.ayuda_id && this.tareaActiva) {
                    this.fetchDatos();
                }
            },

            onOpcionTareaChange() {
                this.datos = [];
                if (this.ayuda_id && this.tareaActiva && this.opcionTareaActiva) {
                    this.fetchDatos();
                }
            },

            setTipoDatoActivo(tipo) {
                this.tipoDatoActivo = tipo;
                this.selectedQuestions = [];
                this.searchTerm = '';
            },

            setTipoDatoMostrar(tipo) {
                this.tipoDatoMostrar = tipo;
                this.$nextTick(() => this.initSortable());
            },

            getDatosCountByTipo(tipo) {
                return this.datosTareaActiva.filter(d => d.tipo_dato === tipo).length;
            },

            getTareaNombre() {
                if (!this.tareaActiva) return '';
                const tarea = this.tareas.find(t => t.slug === this.tareaActiva);
                return tarea ? tarea.nombre : '';
            },

            getTareaId() {
                if (!this.tareaActiva) return null;
                const tarea = this.tareas.find(t => t.slug === this.tareaActiva);
                return tarea ? tarea.id : null;
            },

            getOpcionTareaNombre() {
                if (!this.opcionTareaActiva) return '';
                const opcion = this.opcionesTareaActiva.find(o => o.id == this.opcionTareaActiva);
                return opcion ? opcion.nombre : '';
            },

            getAyudaNombre() {
                if (!this.ayuda_id) return '';
                return this.ayudas[this.ayuda_id] || '';
            },

            getTotalDatosConfigurados() {
                return this.datosTareaActiva.length;
            },

            onAyudaChange() {
                this.tareaActiva = '';
                this.opcionTareaActiva = '';
                this.datos = [];
            },

            async init() {
                // Cargar datos iniciales
                await this.cargarDatosIniciales();
            },

            async cargarDatosIniciales() {
                try {
                    const response = await fetch('/ayuda-datos/datos-iniciales', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name=csrf-token]').content
                        }
                    });
                    const data = await response.json();

                    if (data.success) {
                        this.questions = data.questions || [];
                        this.tareas = data.tareas || [];
                        this.ayudas = data.ayudas || {};
                    }
                } catch (error) {
                    console.error('Error al cargar datos iniciales:', error);
                    this.mostrarNotificacion('❌ Error al cargar los datos iniciales', 'error');
                }
            },

            async guardarDatos() {
                if (!this.puedeGuardar || this.guardando) return;

                this.guardando = true;
                this.errores = [];

                try {
                    const formData = new FormData();
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]')
                        .getAttribute('content'));
                    formData.append('ayuda_id', this.ayuda_id);
                    formData.append('tarea_id', this.getTareaId());
                    formData.append('opcion_tarea_id', this.opcionTareaActiva);

                    // Añadir datos
                    this.datosTareaActiva.forEach((dato, index) => {
                        formData.append(`datos[${index}][question_slug]`, dato
                            .question_slug);
                        formData.append(`datos[${index}][tipo_dato]`, dato.tipo_dato);

                        // Añadir condiciones si existen
                        if (dato.condiciones && dato.condiciones.length > 0) {
                            dato.condiciones.forEach((cond, condIndex) => {
                                if (cond.question_slug && cond.operador && cond
                                    .valor !== undefined) {
                                    formData.append(
                                        `datos[${index}][condiciones][${condIndex}][question_slug]`,
                                        cond.question_slug);
                                    formData.append(
                                        `datos[${index}][condiciones][${condIndex}][operador]`,
                                        cond.operador);

                                    if (Array.isArray(cond.valor)) {
                                        cond.valor.forEach(val => {
                                            formData.append(
                                                `datos[${index}][condiciones][${condIndex}][valor][]`,
                                                val);
                                        });
                                    } else {
                                        formData.append(
                                            `datos[${index}][condiciones][${condIndex}][valor]`,
                                            cond.valor);
                                    }
                                }
                            });
                        }
                    });

                    const response = await fetch('/ayuda-datos', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();

                    if (response.ok) {
                        this.mostrarNotificacion('✅ Configuración guardada exitosamente!',
                            'success');
                        // Recargar datos del configurador (orden y lista) sin romper la vista
                        await this.fetchDatos();

                        // Cerrar el modal de configuración primero
                        const root = document.querySelector('[x-data]');
                        if (root && root._x_dataStack && root._x_dataStack[0]) {
                            // Intentar cambiar la flag del contexto de la pestaña de datos si está accesible
                            try {
                                root._x_dataStack[0].mostrarConfiguracionDatos = false;
                            } catch (e) {}
                        }

                        // Pedir al padre que refresque el detalle usando window.dispatchEvent para asegurar que llegue
                        window.dispatchEvent(new CustomEvent('refresh-detalle'));

                        // También disparar el evento desde el elemento raíz para que Alpine lo capture
                        if (root) {
                            root.dispatchEvent(new CustomEvent('refresh-detalle', {
                                bubbles: true
                            }));
                        }

                        // Forzar actualización del layout después de un breve delay
                        setTimeout(() => {
                            // Buscar el componente de datos y forzar reinicialización del layout
                            const datosTab = document.querySelector(
                                '[x-show="tab===\'datos\'"]');
                            if (datosTab && datosTab._x_dataStack && datosTab
                                ._x_dataStack[0]) {
                                const datosComponent = datosTab._x_dataStack[0];
                                if (datosComponent.ensureLayoutForCurrentData) {
                                    datosComponent.ensureLayoutForCurrentData();
                                }
                            }
                        }, 500);
                    } else {
                        if (result.errors) {
                            this.errores = Object.values(result.errors).flat();
                        } else {
                            this.errores = [result.message ||
                                'Error al guardar la configuración'
                            ];
                        }
                        this.mostrarNotificacion('❌ Error al guardar la configuración',
                            'error');
                    }
                } catch (error) {
                    console.error('Error al guardar datos:', error);
                    this.errores = ['Error de conexión. Inténtalo de nuevo.'];
                    this.mostrarNotificacion('❌ Error de conexión: ' + error.message, 'error');
                } finally {
                    this.guardando = false;
                }
            },

            mostrarNotificacion(mensaje, tipo = 'info') {
                const notification = document.createElement('div');
                notification.className =
                    `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-xl transition-all duration-300 transform translate-x-full max-w-md`;

                const colores = {
                    success: 'bg-green-500 text-white',
                    error: 'bg-red-500 text-white',
                    warning: 'bg-yellow-500 text-white',
                    info: 'bg-blue-500 text-white'
                };

                notification.className += ` ${colores[tipo] || colores.info}`;
                notification.innerHTML = `
        <div class="flex items-center space-x-3">
          <i class="bx ${tipo === 'success' ? 'bx-check-circle' : tipo === 'error' ? 'bx-x-circle' : tipo === 'warning' ? 'bx-error' : 'bx-info-circle'} text-xl"></i>
          <div class="text-sm font-medium">${mensaje}</div>
        </div>
      `;

                document.body.appendChild(notification);

                setTimeout(() => notification.classList.remove('translate-x-full'), 100);

                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => notification.remove(), 300);
                }, 5000);
            },

            initSortable() {
                if (!this.$refs.list) return;
                // Cargar SortableJS si aún no está disponible
                if (typeof Sortable === 'undefined') {
                    const existing = document.querySelector('script[src*="sortablejs"]');
                    if (existing) {
                        // Esperar y reintentar cuando cargue
                        setTimeout(() => this.initSortable(), 150);
                    } else {
                        const script = document.createElement('script');
                        script.src =
                            'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';
                        script.onload = () => this.initSortable();
                        document.head.appendChild(script);
                    }
                    return;
                }
                Sortable.create(this.$refs.list, {
                    animation: 200,
                    draggable: '.draggable-item',
                    onEnd: ({
                        oldIndex,
                        newIndex
                    }) => {
                        // Solo reordena dentro de la tarea activa y tipo mostrado
                        const datosTarea = this.datosTareaActivaFiltrados;
                        const moved = datosTarea.splice(oldIndex, 1)[0];
                        datosTarea.splice(newIndex, 0, moved);
                        // Reconstruir this.datos con el nuevo orden
                        let otros = this.datos.filter(d => !(d.tarea_id == this
                            .getTareaId() && d.opcion_tarea_id == this
                            .opcionTareaActiva && d.tipo_dato === this
                            .tipoDatoMostrar));
                        this.datos = [...otros, ...datosTarea];
                    },
                });
            },

            async fetchDatos() {
                if (!this.ayuda_id || !this.tareaActiva || !this.opcionTareaActiva) {
                    this.datos = [];
                    return;
                }

                const url =
                    `/ayuda-datos/${this.ayuda_id}/datos?tarea_id=${this.getTareaId()}&opcion_tarea_id=${this.opcionTareaActiva}`;

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        this.datos = data.map(d => ({
                            question_slug: d.question_slug,
                            question_text: d.question_text,
                            tipo_dato: d.tipo_dato,
                            tarea_id: d.tarea_id,
                            opcion_tarea_id: d.opcion_tarea_id,
                            condiciones: (d.condiciones || []).map(cond => {
                                // Buscar la pregunta para obtener su texto
                                const pregunta = this.questions.find(
                                    q => q.slug === cond
                                    .question_slug);
                                return {
                                    ...cond,
                                    searchTerm: pregunta ? pregunta
                                        .text : '',
                                    showDropdown: false
                                };
                            }),
                        }));
                        this.$nextTick(() => this.initSortable());
                    })
                    .catch(() => this.datos = []);
            },

            filteredQuestions() {
                if (!this.searchTerm) return this.questions;
                return this.questions.filter(q =>
                    q.text.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                    q.slug.toLowerCase().includes(this.searchTerm.toLowerCase())
                );
            },

            getFilteredQuestionsForCondition(searchTerm) {
                if (!searchTerm) return this.questions;
                return this.questions.filter(q =>
                    q.text.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    q.slug.toLowerCase().includes(searchTerm.toLowerCase())
                );
            },

            selectQuestionForCondition(datoIndex, condIndex, questionSlug) {
                const dato = this.datosTareaActivaFiltrados[datoIndex];
                const globalIndex = this.datos.findIndex(d =>
                    d.question_slug === dato.question_slug &&
                    d.tarea_id == dato.tarea_id &&
                    d.opcion_tarea_id == dato.opcion_tarea_id &&
                    d.tipo_dato === dato.tipo_dato
                );

                if (globalIndex !== -1) {
                    const cond = this.datos[globalIndex].condiciones[condIndex];
                    cond.question_slug = questionSlug;
                    cond.searchTerm = this.questions.find(q => q.slug === questionSlug)?.text || '';
                    cond.showDropdown = false;
                    this.updateCondTipo(datoIndex, condIndex);
                }
            },

            addSelectedForTipo() {
                if (!this.tareaActiva || !this.opcionTareaActiva) {
                    alert('Debes seleccionar una tarea y opción antes de añadir preguntas.');
                    return;
                }

                // Filtrar solo las preguntas que no están ya añadidas
                const preguntasParaAñadir = this.selectedQuestions.filter(slug =>
                    !this.isQuestionAddedInCurrentTask(slug)
                );

                if (preguntasParaAñadir.length === 0) {
                    this.mostrarNotificacion(
                        `⚠️ Todas las preguntas seleccionadas ya están añadidas en el tipo de dato "${this.tiposDato.find(t => t.value === this.tipoDatoActivo)?.label}".`,
                        'warning');
                    this.selectedQuestions = [];
                    return;
                }

                let añadidas = 0;
                preguntasParaAñadir.forEach(slug => {
                    // Verificar si ya existe en el mismo tipo de dato
                    const exists = this.datos.some(d =>
                        d.question_slug === slug &&
                        d.tarea_id == this.getTareaId() &&
                        d.opcion_tarea_id == this.opcionTareaActiva &&
                        d.tipo_dato === this.tipoDatoActivo
                    );

                    if (!exists) {
                        const q = this.questions.find(q => q.slug === slug) || {};
                        this.datos.push({
                            question_slug: slug,
                            question_text: q.text || slug,
                            tipo_dato: this.tipoDatoActivo,
                            tarea_id: this.getTareaId(),
                            opcion_tarea_id: this.opcionTareaActiva,
                            condiciones: [], // SIEMPRE presente
                        });
                        añadidas++;
                    }
                });

                if (añadidas > 0) {
                    this.mostrarNotificacion(`✅ Se añadieron ${añadidas} preguntas correctamente.`,
                        'success');
                }

                this.selectedQuestions = [];
                this.searchTerm = '';
                this.$nextTick(() => this.initSortable());
            },

            removeDato(i) {
                const datoToRemove = this.datosTareaActivaFiltrados[i];
                const globalIndex = this.datos.findIndex(d =>
                    d.question_slug === datoToRemove.question_slug &&
                    d.tarea_id == datoToRemove.tarea_id &&
                    d.opcion_tarea_id == datoToRemove.opcion_tarea_id &&
                    d.tipo_dato === datoToRemove.tipo_dato
                );
                if (globalIndex !== -1) {
                    this.datos.splice(globalIndex, 1);
                }
            },

            get puedeGuardar() {
                // Solo permite guardar si todos los datos de la tarea activa tienen tipo_dato y question_slug
                return this.datosTareaActiva.length > 0 && this.datosTareaActiva.every(d => d
                    .tipo_dato && d.question_slug);
            },

            // --- Condiciones ---
            addCondicion(i) {
                const dato = this.datosTareaActivaFiltrados[i];
                const globalIndex = this.datos.findIndex(d =>
                    d.question_slug === dato.question_slug &&
                    d.tarea_id == dato.tarea_id &&
                    d.opcion_tarea_id == dato.opcion_tarea_id &&
                    d.tipo_dato === dato.tipo_dato
                );

                if (globalIndex !== -1) {
                    if (!this.datos[globalIndex].condiciones) this.datos[globalIndex]
                        .condiciones = [];
                    this.datos[globalIndex].condiciones.push({
                        question_slug: '',
                        searchTerm: '',
                        showDropdown: false,
                        operador: '=',
                        valor: '',
                    });
                }
            },

            removeCondicion(i, ci) {
                const dato = this.datosTareaActivaFiltrados[i];
                const globalIndex = this.datos.findIndex(d =>
                    d.question_slug === dato.question_slug &&
                    d.tarea_id == dato.tarea_id &&
                    d.opcion_tarea_id == dato.opcion_tarea_id &&
                    d.tipo_dato === dato.tipo_dato
                );

                if (globalIndex !== -1) {
                    this.datos[globalIndex].condiciones.splice(ci, 1);
                }
            },

            getCondTipo(slug) {
                const q = this.questions.find(q => q.slug === slug);
                return q ? q.type : 'text';
            },

            getCondOptions(slug) {
                const q = this.questions.find(q => q.slug === slug);
                return q && q.options ? q.options : [];
            },

            updateCondTipo(i, ci) {
                const dato = this.datosTareaActivaFiltrados[i];
                const globalIndex = this.datos.findIndex(d =>
                    d.question_slug === dato.question_slug &&
                    d.tarea_id == dato.tarea_id &&
                    d.opcion_tarea_id == dato.opcion_tarea_id &&
                    d.tipo_dato === dato.tipo_dato
                );

                if (globalIndex !== -1) {
                    // Al cambiar la pregunta, resetea el valor y operador
                    const slug = this.datos[globalIndex].condiciones[ci].question_slug;
                    const tipo = this.getCondTipo(slug);
                    if (tipo === 'multiple') {
                        this.datos[globalIndex].condiciones[ci].valor = [];
                    } else if (tipo === 'boolean') {
                        this.datos[globalIndex].condiciones[ci].valor = '1';
                    } else if (tipo === 'select') {
                        this.datos[globalIndex].condiciones[ci].valor = '0';
                    } else {
                        this.datos[globalIndex].condiciones[ci].valor = '';
                    }
                    this.datos[globalIndex].condiciones[ci].operador = '==';
                }
            },

            isQuestionAssociated(slug) {
                if (!this.ayuda_id) return false;
                const question = this.questions.find(q => q.slug === slug);
                return question && question.associated_ayudas && question.associated_ayudas
                    .includes(parseInt(this.ayuda_id));
            },

            isQuestionAddedInCurrentTask(slug) {
                if (!this.tareaActiva || !this.opcionTareaActiva) return false;
                return this.datosTareaActiva.some(d => d.question_slug === slug && d.tipo_dato ===
                    this.tipoDatoActivo);
            },

            getPreguntasDisponiblesCount() {
                return this.filteredQuestions().filter(q => !this.isQuestionAddedInCurrentTask(q
                    .slug)).length;
            },

            getPreguntasAñadidasCount() {
                return this.filteredQuestions().filter(q => this.isQuestionAddedInCurrentTask(q
                    .slug)).length;
            },

            // Funciones para el modal de copiar datos
            openCopiarModal() {
                if (!this.ayuda_id) {
                    this.mostrarNotificacion('❌ Debes seleccionar una ayuda primero', 'error');
                    return;
                }

                document.getElementById('copiar-datos-modal').style.display = 'flex';
                this.resetearModalCopiarDatos();
                this.cargarAyudasParaCopiarDatos();
                this.configurarAyudaOrigen();
            },

            closeCopiarDatosModal() {
                document.getElementById('copiar-datos-modal').style.display = 'none';
                this.resetearModalCopiarDatos();
            },

            resetearModalCopiarDatos() {
                document.getElementById('copiar-datos-form').reset();
                document.getElementById('ayudas-destino-search-datos').value = '';
                document.getElementById('ayudas-destino-seleccionadas-datos').innerHTML = '';
                document.getElementById('btn-copiar-datos-submit').disabled = true;
                this.ayudasDestinoSeleccionadasDatos = [];
            },

            configurarAyudaOrigen() {
                const ayudaNombre = this.ayudas[this.ayuda_id];
                document.getElementById('ayuda-origen-nombre-datos').textContent = ayudaNombre;
                document.getElementById('ayuda-origen-id-datos').textContent =
                    `ID: ${this.ayuda_id}`;

                // Cargar vista previa de todos los datos de la ayuda
                this.cargarVistaPreviaDatosOrigen(this.ayuda_id);
            },

            async cargarAyudasParaCopiarDatos() {
                try {
                    this.todasLasAyudasDatos = Object.entries(this.ayudas).map(([id, nombre]) =>
                        ({
                            id: parseInt(id),
                            nombre: nombre
                        }));
                    this.actualizarListasAyudasDatos();
                } catch (error) {
                    this.mostrarNotificacion('❌ Error al cargar las ayudas', 'error');
                }
            },

            actualizarListasAyudasDatos() {
                const listaDestino = document.getElementById('lista-ayudas-destino-datos');

                // Filtrar ayudas que no están seleccionadas como destino y que no son la ayuda origen
                const ayudasDisponiblesDestino = this.todasLasAyudasDatos.filter(ayuda =>
                    ayuda.id !== parseInt(this.ayuda_id) &&
                    !this.ayudasDestinoSeleccionadasDatos.some(sel => sel.id === ayuda.id)
                );

                listaDestino.innerHTML = ayudasDisponiblesDestino.map(ayuda => `
        <div class="ayuda-option px-4 py-2 hover:bg-gray-100 cursor-pointer" 
             data-ayuda-id="${ayuda.id}" 
             data-ayuda-nombre="${ayuda.nombre}"
             onclick="window.seleccionarAyudaDestinoDatos(${ayuda.id}, '${ayuda.nombre.replace(/'/g, "\\'")}')">
          ${ayuda.nombre}
        </div>
      `).join('');
            },

            seleccionarAyudaDestinoDatos(id, nombre) {
                if (!this.ayudasDestinoSeleccionadasDatos.some(ayuda => ayuda.id === id)) {
                    this.ayudasDestinoSeleccionadasDatos.push({
                        id,
                        nombre
                    });
                    this.actualizarListasAyudasDatos();
                    this.mostrarAyudasDestinoSeleccionadasDatos();
                    this.verificarFormularioCompletoDatos();
                }
                document.getElementById('ayudas-destino-search-datos').value = '';
                document.getElementById('lista-ayudas-destino-datos').classList.add('hidden');
            },

            quitarAyudaDestinoDatos(id) {
                this.ayudasDestinoSeleccionadasDatos = this.ayudasDestinoSeleccionadasDatos.filter(
                    ayuda => ayuda.id !== id);
                this.actualizarListasAyudasDatos();
                this.mostrarAyudasDestinoSeleccionadasDatos();
                this.verificarFormularioCompletoDatos();
            },

            mostrarAyudasDestinoSeleccionadasDatos() {
                const container = document.getElementById('ayudas-destino-seleccionadas-datos');
                container.innerHTML = this.ayudasDestinoSeleccionadasDatos.map(ayuda => `
        <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-lg p-2">
          <span class="text-green-800 font-medium">${ayuda.nombre}</span>
          <button 
            type="button" 
            onclick="window.quitarAyudaDestinoDatos(${ayuda.id})"
            class="text-green-600 hover:text-green-800 ml-2"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>
      `).join('');
            },

            verificarFormularioCompletoDatos() {
                const tieneDestinos = this.ayudasDestinoSeleccionadasDatos.length > 0;

                document.getElementById('btn-copiar-datos-submit').disabled = !tieneDestinos;
            },

            async cargarVistaPreviaDatosOrigen(ayudaId) {
                try {
                    const response = await fetch(
                        `/ayuda-datos/vista-previa?ayuda_id=${ayudaId}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name=csrf-token]').content
                            }
                        });
                    const data = await response.json();

                    if (data.success) {
                        this.mostrarVistaPreviaDatos(data.datos);
                    } else {
                        this.mostrarNotificacion('❌ Error al cargar la vista previa', 'error');
                    }
                } catch (error) {
                    this.mostrarNotificacion('❌ Error al cargar la vista previa', 'error');
                }
            },

            mostrarVistaPreviaDatos(datos) {
                const container = document.getElementById('contenido-vista-previa-datos');

                if (datos.length === 0) {
                    container.innerHTML =
                        '<p class="text-gray-500 text-sm">No hay datos de ayuda que coincidan con los filtros especificados.</p>';
                } else {
                    const agrupadas = datos.reduce((acc, dato) => {
                        if (!acc[dato.tipo_dato]) {
                            acc[dato.tipo_dato] = [];
                        }
                        acc[dato.tipo_dato].push(dato);
                        return acc;
                    }, {});

                    container.innerHTML = Object.entries(agrupadas).map(([tipoDato, datosTipo]) => `
          <div class="mb-3">
            <h5 class="font-medium text-gray-700 mb-1">${tipoDato}</h5>
            <div class="space-y-1 ml-4">
              ${datosTipo.map(dato => `
                <div class="text-sm text-gray-600">
                  • ${dato.question_text}
                  ${dato.tarea ? ` (${dato.tarea})` : ''}
                  ${dato.condiciones_count > 0 ? ` [${dato.condiciones_count} condiciones]` : ''}
                </div>
              `).join('')}
            </div>
          </div>
        `).join('');
                }

                document.getElementById('vista-previa-datos-origen').classList.remove('hidden');
            },

            async submitCopiarDatos(event) {
                event.preventDefault();

                const formData = new FormData(event.target);
                const sobrescribir = formData.get('sobrescribir') === 'on';
                const ayudasDestinoIds = this.ayudasDestinoSeleccionadasDatos.map(ayuda => ayuda
                    .id);

                if (!this.ayuda_id || ayudasDestinoIds.length === 0) {
                    this.mostrarNotificacion('❌ Debes seleccionar al menos una ayuda destino',
                        'error');
                    return;
                }

                const btnSubmit = document.getElementById('btn-copiar-datos-submit');
                const textoOriginal = btnSubmit.innerHTML;
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Copiando...';

                try {
                    const response = await fetch('/ayuda-datos/copiar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({
                            ayuda_origen_id: parseInt(this.ayuda_id),
                            ayudas_destino_ids: ayudasDestinoIds,
                            sobrescribir: sobrescribir
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.mostrarNotificacion('✅ ' + data.message, 'success');
                        this.closeCopiarDatosModal();
                    } else {
                        this.mostrarNotificacion('❌ ' + (data.message ||
                            'Error al copiar los datos'), 'error');
                    }
                } catch (error) {
                    this.mostrarNotificacion('❌ Error al copiar los datos', 'error');
                } finally {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = textoOriginal;
                }
            },

            async actualizarDatosDetalle() {
                try {
                    // Obtener el ID de la contratación desde el contexto del modal principal
                    const contratacionId = this.obtenerContratacionId();
                    if (!contratacionId) {
                        console.warn('No se pudo obtener el ID de la contratación');
                        return;
                    }

                    // Hacer una petición para obtener los datos actualizados de la contratación
                    const response = await fetch(
                        `/contrataciones/${contratacionId}/datos-actualizados`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name=csrf-token]').content
                            }
                        });

                    if (response.ok) {
                        const data = await response.json();

                        // Actualizar los datos del detalle en el contexto del modal principal
                        this.actualizarDatosEnModalPrincipal(data);

                        this.mostrarNotificacion('✅ Datos actualizados correctamente',
                            'success');
                    } else {
                        console.error('Error al actualizar los datos del detalle');
                    }
                } catch (error) {
                    console.error('Error al actualizar los datos del detalle:', error);
                }
            },

            obtenerContratacionId() {
                // Intentar obtener el ID de la contratación desde diferentes fuentes
                const form = document.getElementById('form-guardar-datos');
                if (form) {
                    const action = form.getAttribute('action');
                    const match = action.match(/\/contrataciones\/(\d+)\/update-datos/);
                    if (match) {
                        return match[1];
                    }
                }

                // Buscar en el contexto del modal principal
                const modalPrincipal = document.querySelector('[x-data]');
                if (modalPrincipal && modalPrincipal._x_dataStack) {
                    const data = modalPrincipal._x_dataStack[0];
                    if (data && data.detalle && data.detalle.id) {
                        return data.detalle.id;
                    }
                }

                return null;
            },

            actualizarDatosEnModalPrincipal(nuevosDatos) {
                // Buscar el contexto del modal principal y actualizar los datos
                const modalPrincipal = document.querySelector('[x-data]');
                if (modalPrincipal && modalPrincipal._x_dataStack) {
                    const data = modalPrincipal._x_dataStack[0];
                    if (data && data.detalle) {
                        // Actualizar los datos específicos que pueden haber cambiado
                        if (nuevosDatos.solicitanteDatos) {
                            data.detalle.solicitanteDatos = nuevosDatos.solicitanteDatos;
                        }
                        if (nuevosDatos.contratoDatos) {
                            data.detalle.contratoDatos = nuevosDatos.contratoDatos;
                        }
                        if (nuevosDatos.direccionDatos) {
                            data.detalle.direccionDatos = nuevosDatos.direccionDatos;
                        }
                        if (nuevosDatos.hijoDatos) {
                            data.detalle.hijoDatos = nuevosDatos.hijoDatos;
                        }
                        if (nuevosDatos.convivienteDatos) {
                            data.detalle.convivienteDatos = nuevosDatos.convivienteDatos;
                        }
                        if (nuevosDatos.arrendadorDatos) {
                            data.detalle.arrendadorDatos = nuevosDatos.arrendadorDatos;
                        }

                        // Forzar la actualización de Alpine.js
                        data.$nextTick(() => {
                            // Los datos se actualizarán automáticamente
                        });
                    }
                }
            }
        }
    }

    // Funciones globales para el modal de copiar datos
    function filtrarAyudasDestinoDatos() {
        const termino = document.getElementById('ayudas-destino-search-datos').value.toLowerCase();
        const opciones = document.querySelectorAll('#lista-ayudas-destino-datos .ayuda-option');

        opciones.forEach(opcion => {
            const nombre = opcion.textContent.toLowerCase();
            if (nombre.includes(termino)) {
                opcion.style.display = 'block';
            } else {
                opcion.style.display = 'none';
            }
        });
    }

    function mostrarListaAyudasDestinoDatos() {
        document.getElementById('lista-ayudas-destino-datos').classList.remove('hidden');
    }

    function ocultarListaAyudasDestinoDatos() {
        setTimeout(() => {
            document.getElementById('lista-ayudas-destino-datos').classList.add('hidden');
        }, 200);
    }

    // Funciones globales para el modal
    window.seleccionarAyudaDestinoDatos = function(id, nombre) {
        const component = Alpine.$data(document.querySelector('[x-data="ayudaDatosConfig()"]'));
        if (component) {
            component.seleccionarAyudaDestinoDatos(id, nombre);
        }
    }

    window.quitarAyudaDestinoDatos = function(id) {
        const component = Alpine.$data(document.querySelector('[x-data="ayudaDatosConfig()"]'));
        if (component) {
            component.quitarAyudaDestinoDatos(id);
        }
    }

    window.closeCopiarDatosModal = function() {
        const component = Alpine.$data(document.querySelector('[x-data="ayudaDatosConfig()"]'));
        if (component) {
            component.closeCopiarDatosModal();
        }
    }

    window.submitCopiarDatos = function(event) {
        const component = Alpine.$data(document.querySelector('[x-data="ayudaDatosConfig()"]'));
        if (component) {
            component.submitCopiarDatos(event);
        }
    }
</script>
