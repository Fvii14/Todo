{{-- Modal listado pagos administración --}}
<div x-data="pagosAdminModal()" @open-pagos-admin.window="openFor($event.detail)" x-cloak>
    <!-- Backdrop -->
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-[100] bg-black/50" @click="close()" aria-hidden="true">
    </div>

    <!-- Panel -->
    <div x-show="open" x-transition class="fixed inset-0 z-[101] flex items-center justify-center p-4">
        <div class="w-[95vw] sm:w-[1200px] xl:w-[1400px] bg-white rounded-2xl shadow-2xl overflow-hidden" @click.stop
            role="dialog" aria-modal="true" aria-labelledby="pagos-title">

            <!-- Header -->
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <div>
                    <h3 id="pagos-title" class="text-lg font-semibold">Pagos Administración</h3>
                    <p class="text-sm text-slate-600 mt-0.5">
                        <span class="font-medium" x-text="cliente"></span>
                        · <span x-text="ayuda"></span>
                    </p>
                </div>
                <button @click="close()" class="rounded-lg p-2 hover:bg-slate-100" aria-label="Cerrar">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="px-6 py-4">
                <!-- Loading -->
                <template x-if="loading">
                    <div class="py-10 text-center text-slate-500">Cargando pagos…</div>
                </template>

                <!-- Error -->
                <template x-if="error">
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
                        x-text="error"></div>
                </template>

                <!-- Empty -->
                <template x-if="!loading && !error && pagos.length === 0">
                    <div class="py-10 text-center text-slate-500">No hay pagos registrados para esta contratación.</div>
                </template>

                <!-- Table -->
                <template x-if="!loading && !error && pagos.length > 0">
                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr class="text-left">
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-3">Fecha</th>
                                    <th class="px-4 py-3 text-right">Importe (€)</th>
                                    <th class="px-4 py-3 text-right">Comisión (€)</th>
                                    <th class="px-4 py-3">Estado cobro</th>
                                    <th class="px-4 py-3">Notas</th>
                                    <th class="px-4 py-3">Creado</th>
                                    <th class="px-4 py-3">Factura</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="p in pagos" :key="p.id">
                                    <tr>
                                        <td class="px-4 py-3 font-medium" x-text="p.n_pago ?? '-'"></td>
                                        <td class="px-4 py-3" x-text="fmtFecha(p.fecha_pago)"></td>
                                        <td class="px-4 py-3 text-right" x-text="fmtEur(p.importe_pagado)"></td>

                                        <!-- Comisión -->
                                        <td class="px-4 py-3 text-right" x-text="fmtEur(p.comision)"></td>

                                        <!-- Estado Cobro / Botón -->
                                        <td class="px-4 py-3">
                                            <template x-if="p.estado_cobro === 'cobrada'">
                                                <span
                                                    class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">
                                                    Cobrada
                                                </span>
                                            </template>
                                            <template x-if="p.estado_cobro !== 'cobrada'">
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800"
                                                        x-text="p.comision > 0 ? 'Pendiente' : 'No aplica'"></span>

                                                    <!-- Botón solo si hay comisión > 0 -->
                                                    <template x-if="p.comision > 0">
                                                        <button type="button"
                                                            class="inline-flex items-center gap-1 rounded-lg border px-2 py-1 text-xs hover:bg-orange-50 disabled:opacity-60 bg-orange-200 text-orange-800"
                                                            :disabled="marcandoId === p.id" @click="marcarCobrada(p)">
                                                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none"
                                                                stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="m5 13 4 4L19 7" />
                                                            </svg>
                                                            <span
                                                                x-text="marcandoId === p.id ? 'Marcando…' : 'Marcar cobrada'"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </template>
                                        </td>

                                        <td class="px-4 py-3">
                                            <div class="max-w-[22rem] truncate" x-text="p.notas ?? ''"
                                                :title="p.notas ?? ''"></div>
                                        </td>
                                        <td class="px-4 py-3" x-text="fmtFechaHora(p.created_at)"></td>
                                        <td class="px-4 py-3">
                                            <!-- Si EXISTE factura → enlace a la ruta que genera la URL firmada -->
                                            <template x-if="p.factura_view_url">
                                                <a :href="p.factura_view_url" target="_blank" rel="noopener noreferrer"
                                                    class="inline-flex items-center gap-1 rounded-lg border px-2 py-1 text-xs hover:bg-indigo-50 bg-indigo-100 text-indigo-800"
                                                    :title="p.factura_numero ? `Factura ${p.factura_numero}` : 'Ver factura'">
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z">
                                                        </path>
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14 3v6h6"></path>
                                                    </svg>
                                                    <span x-text="p.factura_numero || 'Ver PDF'"></span>
                                                </a>
                                            </template>

                                            <!-- Si NO existe → botón generar (solo si hay comisión > 0) -->
                                            <template x-if="!p.factura_view_url && p.comision > 0">
                                                {{-- Esta comentado porque aun no se generan las facturas con todos los campos necesarios --}}
                                                {{-- <button type="button"
                                                    class="inline-flex items-center gap-1 rounded-lg border px-2 py-1 text-xs bg-blue-200 text-blue-800 hover:bg-blue-300 disabled:opacity-60"
                                                    :disabled="facturandoId === p.id" @click="facturar(p)">
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M8 16h8M8 12h8M9 8h6m-9 9V7a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v10" />
                                                    </svg>
                                                    <span
                                                        x-text="facturandoId === p.id ? 'Generando…' : 'Generar factura'"></span>
                                                </button> --}}
                                            </template>

                                            <!-- Si no hay comisión → no aplica -->
                                            <template x-if="!p.factura_view_url && p.comision <= 0">
                                                <span
                                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs bg-slate-100 text-slate-500">
                                                    No aplica
                                                </span>
                                            </template>
                                        </td>

                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-slate-50">
                                <tr>
                                    <td class="px-4 py-3 font-semibold" colspan="2">Total pagado</td>

                                    <!-- Pagado: actual / concedido -->
                                    <!-- Pagado / Concedido (con el total en negrita) -->
                                    <td class="px-4 py-3 text-right">
                                        <span x-text="fmtEur(totales.importe_pagado_total || 0)"></span>
                                        <span class="opacity-60"> / </span>
                                        <strong x-text="fmtEur(concedidoTotal || 0)"></strong>
                                    </td>

                                    <!-- Comisión asignada / Comisión total (con el total en negrita) -->
                                    <td class="px-4 py-3 text-right">
                                        <span x-text="fmtEur(totales.comision_total || 0)"></span>
                                        <span class="opacity-60"> / </span>
                                        <strong x-text="fmtEur(comisionTotal || 0)"></strong>
                                    </td>

                                    <td class="px-4 py-3" colspan="4"></td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t flex items-center justify-end gap-2">
                <button @click="close()"
                    class="inline-flex items-center rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Llama a esto desde el botón "Ver pagos"
    window.openPagosAdminModal = (payload) => {
        window.dispatchEvent(new CustomEvent('open-pagos-admin', {
            detail: payload
        }));
    };
</script>

<script>
    function pagosAdminModal() {
        return {
            open: false,
            loading: false,
            error: null,

            pagos: [],
            totales: {
                importe_pagado_total: 0,
                comision_total: 0
            }, // ← solo una vez
            cliente: '',
            ayuda: '',
            contratacionId: null,
            marcandoId: null,
            facturandoId: null,

            // totales superiores para el footer
            concedidoTotal: 0,
            comisionTotal: 0,

            openFor({
                id,
                cliente,
                ayudaNombre,
                concedidoTotal = 0,
                comisionTotal = 0
            } = {}) {
                this.open = true;
                this.loading = true;
                this.error = null;

                this.pagos = [];
                this.totales = {
                    importe_pagado_total: 0,
                    comision_total: 0
                };

                this.contratacionId = id ?? null;
                this.cliente = cliente || '';
                this.ayuda = ayudaNombre || '';

                // ✅ ahora sí existen en este scope
                this.concedidoTotal = Number(concedidoTotal) || 0;
                this.comisionTotal = Number(comisionTotal) || 0;
                //Listado
                const url = `{{ url('/operativa/liquidaciones') }}/${this.contratacionId}/pagos-admin/list`;

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(jsonOrDie)
                    .then(json => {
                        this.pagos = json.pagos || [];
                        this.totales = json.totales || {
                            importe_pagado_total: 0,
                            comision_total: 0
                        };
                    })
                    .catch(e => {
                        this.error = e.message;
                    })
                    .finally(() => {
                        this.loading = false;
                    });

            },

            // útil para refrescar tras acciones manteniendo los totales de cabecera
            refrescar() {
                this.openFor({
                    id: this.contratacionId,
                    cliente: this.cliente,
                    ayudaNombre: this.ayuda,
                    concedidoTotal: this.concedidoTotal,
                    comisionTotal: this.comisionTotal,
                });
            },

            csrf() {
                const m = document.querySelector('meta[name="csrf-token"]');
                return m ? m.getAttribute('content') : '';
            },

            marcarCobrada(p) {
                this.marcandoId = p.id;
                const url = `{{ route('operativa.liquidaciones.pagosAdmin.marcarCobrada', ['pago' => '__ID__']) }}`
                    .replace('__ID__', p.id);
                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrf(),
                            'Accept': 'application/json'
                        },
                    })
                    .then(r => r.json())
                    .then(json => {
                        if (!json.ok) throw new Error(json.message || 'No se pudo marcar como cobrada.');
                        this.refrescar(); // ← usa refrescar para no perder totales
                    })
                    .catch(e => {
                        this.error = e.message;
                    })
                    .finally(() => {
                        this.marcandoId = null;
                    });
            },

            fmtEur(v) {
                const n = Number(v ?? 0);
                return new Intl.NumberFormat('es-ES', {
                    style: 'currency',
                    currency: 'EUR'
                }).format(n);
            },
            fmtFecha(d) {
                if (!d) return '—';
                const x = new Date(d);
                return isNaN(x) ? d : x.toLocaleDateString('es-ES');
            },
            fmtFechaHora(d) {
                if (!d) return '—';
                const x = new Date(d);
                return isNaN(x) ? d : x.toLocaleString('es-ES');
            },

            facturar(p) {
                this.facturandoId = p.id;
                const run = (attempt = 1) => {
                    const url = `{{ route('operativa.liquidaciones.pagosAdmin.facturar', ['pago' => '__ID__']) }}`
                        .replace('__ID__', p.id);
                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': this.csrf(),
                                'Accept': 'application/json'
                            },
                        })
                        .then(async (r) => {
                            const ct = r.headers.get('content-type') || '';
                            const text = await r.text();
                            let data = {};
                            try {
                                if (ct.includes('application/json') && text) data = JSON.parse(text);
                            } catch {}

                            if (r.status === 409) {
                                const retry = Number(r.headers.get('Retry-After') || 0) || 1.5;
                                if (attempt < 5) {
                                    setTimeout(() => run(attempt + 1), retry * 1000 * attempt);
                                    return;
                                }
                                throw new Error(data?.message ||
                                    'El PDF sigue pendiente tras varios intentos.');
                            }

                            if (!r.ok) throw new Error(data?.message || text || `Error ${r.status}`);
                            return data || {};
                        })
                        .then((json) => {
                            // ✅ json siempre es objeto; evita “Cannot read ... 'ok'”
                            if (json.ok === false) throw new Error(json.message || 'No se pudo facturar.');
                            this.refrescar();
                        })
                        .catch((e) => {
                            this.error = e.message;
                        })
                        .finally(() => {
                            this.facturandoId = null;
                        });
                };
                run();
            },



            close() {
                this.open = false;
            }

        };
    }

    async function jsonOrDie(r) {
        const ct = r.headers.get('content-type') || '';
        const text = await r.text();
        let data = null;
        try {
            if (ct.includes('application/json') && text) data = JSON.parse(text);
        } catch {}

        if (!r.ok) {
            const err = new Error((data && data.message) || text.slice(0, 300) || `HTTP ${r.status}`);
            err.status = r.status;
            err.data = data;
            throw err;
        }
        return data || {};
    }
</script>
<script>
    async function jsonOrDie(r) {
        const ct = r.headers.get('content-type') || '';
        const text = await r.text();

        // Si status no es OK, lanza con el texto (para ver el error del servidor)
        if (!r.ok) {
            throw new Error(`Error ${r.status}: ${text.slice(0,300)}`);
        }

        // Si no es JSON, probablemente es HTML de login/CSRF/redirect
        if (!ct.includes('application/json')) {
            if (r.status === 204 || text.trim() === '') {
                // 204 sin contenido: devuelve un objeto vacío
                return {};
            }
            if (r.status === 419 || /csrf|login/i.test(text)) {
                throw new Error('Sesión caducada. Recarga la página.');
            }
            throw new Error('Respuesta no-JSON: ' + text.slice(0, 300));
        }

        // Parseo seguro
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error('JSON inválido: ' + text.slice(0, 300));
        }
    }
</script>
