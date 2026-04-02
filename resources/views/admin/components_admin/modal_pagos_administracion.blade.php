{{-- Helpers globales para abrir/cerrar este modal --}}
<script>
    const PAGOS_ADMIN_ACTION_TMPL = @json(route('operativa.liquidaciones.pagosAdmin.store', ['contratacion' => '__ID__']));

    function openPagoAdmin(contratacion) {
        const action = PAGOS_ADMIN_ACTION_TMPL.replace('__ID__', contratacion.id);

        window.openPagoAdminModal({
            action,
            id: contratacion.id,
            cliente: contratacion.cliente,
            ayudaNombre: contratacion.ayudaNombre,
            comisionTotal: Number(contratacion.comisionTotal ?? 0),
            asignadoAcum: Number(contratacion.asignadoAcum ?? 0),
            concedidoTotal: Number(contratacion.concedidoTotal ?? 0),
            pagadoAcum: Number(contratacion.pagadoAcum ?? 0),
            commissionPct: contratacion.commissionPct != null && contratacion
                .commissionPct !== '' ? Number(contratacion.commissionPct) : null,
        });
    }
</script>

<div x-data="{
    open: false,
    action: '',
    form: { id: null, cliente: '', ayudaNombre: '', importe_pagado: 0, fecha_pago: '', notas: '', comision: null },
    comisionTotal: 0,
    asignadoAcum: 0,
    propuesta: 0,
    userEditedComision: false,
    commissionPct: null,

    concedidoTotal: 0,
    pagadoAcum: 0,

    pendienteAsignar() {
        return Math.max((Number(this.comisionTotal) || 0) - (Number(this.asignadoAcum) || 0), 0);
    },
    pendienteConcedido() {
        const tot = Number(this.concedidoTotal) || 0;
        const pag = Number(this.pagadoAcum) || 0;
        return Math.max(tot - pag, 0);
    },

    calcPropuesta() {
        const pago = Math.min(Number(this.form.importe_pagado) || 0, this.pendienteConcedido());
        this.form.importe_pagado = pago;

        if (this.commissionPct != null && this.commissionPct !== '') {
            const pct = Number(this.commissionPct) || 0;
            this.propuesta = Math.round(pago * pct / 100 * 100) / 100;
        } else {
            this.propuesta = 0;
        }

        if (!this.userEditedComision) {
            this.form.comision = isFinite(this.propuesta) ? Number(this.propuesta) : 0;
        }
    },

    setComisionToPropuesta() {
        this.userEditedComision = false;
        this.form.comision = (isFinite(this.propuesta) ? Number(this.propuesta) : 0);
    },

    openModal(d) {
        this.open = true;
        this.action = d.action || '';
        this.form.id = d.id || null;
        this.form.cliente = d.cliente || '';
        this.form.ayudaNombre = d.ayudaNombre || '';
        this.comisionTotal = Number(d.comisionTotal || 0);
        this.asignadoAcum = Number(d.asignadoAcum || 0);
        this.concedidoTotal = Number(d.concedidoTotal || 0);
        this.pagadoAcum = Number(d.pagadoAcum || 0);
        this.commissionPct = d.commissionPct != null && d.commissionPct !== '' ? Number(d.commissionPct) : null;
        this.userEditedComision = false;
        this.calcPropuesta();
    },

    close() { this.open = false; }
}" @open-pago-admin.window="openModal($event.detail)"
    @close-pago-admin.window="close()" x-cloak class="relative z-50">
    <div x-show="open" x-transition.opacity style="display:none"
        class="fixed inset-0 bg-black/40 z-[800]" @click="close()"></div>
    <div x-show="open" x-transition style="display:none"
        class="fixed inset-0  z-[801] flex items-center justify-center p-4" role="dialog"
        aria-modal="true">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Registrar pago de la Administración</h2>
                    <p class="text-sm text-gray-600 mt-1"
                        x-text="form.cliente && form.ayudaNombre ? `${form.cliente} · ${form.ayudaNombre}` : ''">
                    </p>
                </div>
                <button type="button" class="p-1 rounded hover:bg-gray-100" @click="close()"
                    aria-label="Cerrar">✕</button>
            </div>

            <form :action="action" method="POST" class="px-5 py-4 space-y-4"
                @input="calcPropuesta()">
                @csrf

                <div>
                    <label class="block text-sm font-medium mb-1">Importe pagado (€)</label>
                    {{-- <input type="number" step="0.01" min="0.01" name="importe_pagado"
                        x-model.number="form.importe_pagado" class="w-full rounded border-gray-300"> --}}
                    <input type="number" step="0.01" min="0.01" name="importe_pagado"
                        x-model.number="form.importe_pagado" :max="pendienteConcedido()"
                        :disabled="pendienteConcedido() <= 0"
                        @input="const maxImp = pendienteConcedido(); form.importe_pagado = Math.min(Math.max(Number(form.importe_pagado)||0, 0), maxImp); calcPropuesta();"
                        class="w-full rounded border-gray-300">
                    <!-- Aviso cuando no queda concesión por pagar -->
                    <template x-if="pendienteConcedido() <= 0">
                        <div
                            class="mt-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                            No puedes registrar más pagos: la cantidad concedida ya está
                            completamente pagada.
                        </div>
                    </template>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Fecha pago</label>
                        <input type="date" name="fecha_pago" x-model="form.fecha_pago"
                            class="w-full rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Comisión a cobrar en este pago
                            (€)</label>
                        <template x-if="commissionPct != null">
                            <p class="text-xs text-gray-600 mb-1">
                                Porcentaje del producto: <strong
                                    x-text="Number(commissionPct).toFixed(2)"></strong> %
                            </p>
                        </template>
                        <template x-if="commissionPct == null">
                            <p class="text-xs text-amber-600 mb-1">
                                Este contrato no tiene producto con porcentaje de comisión; se
                                registrará sin comisión.
                            </p>
                        </template>
                        <div class="flex gap-2">
                            <input type="number" step="0.01" min="0" name="comision"
                                x-model.number="form.comision" :readonly="commissionPct != null"
                                :class="commissionPct != null ? 'bg-gray-100 cursor-not-allowed' : ''"
                                class="w-full rounded border-gray-300">

                            <template x-if="commissionPct == null">
                                <button type="button"
                                    class="px-3 rounded border bg-green-400 text-sm hover:bg-green-500"
                                    @click.prevent="setComisionToPropuesta()"
                                    title="Restaurar sugerencia">
                                    Usar sugerencia
                                </button>
                            </template>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Comisión (según producto): <strong
                                x-text="propuesta.toFixed(2)"></strong> €
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Notas</label>
                    <textarea name="notas" x-model="form.notas" class="w-full rounded border-gray-300" rows="2"
                        placeholder="Observaciones…"></textarea>
                </div>

                <div class="p-3 rounded bg-blue-50 text-blue-800 text-sm">
                    <template x-if="commissionPct != null">
                        <div>· Comisión según producto (<span
                                x-text="Number(commissionPct).toFixed(2)"></span>% del importe):
                            <span x-text="propuesta.toFixed(2)"></span> €</div>
                    </template>
                    <template x-if="commissionPct == null">
                        <div>· Sin porcentaje de producto; comisión: <span
                                x-text="propuesta.toFixed(2)"></span> €</div>
                    </template>
                    <div>
                        {{-- Total concedido --}}

                        · Total concedido: <span x-text="Number(concedidoTotal).toFixed(2)"></span>
                        €</br>
                        . Cantidad ya pagada: <span x-text="Number(pagadoAcum).toFixed(2)"></span>
                        €</br>
                        · Pendiente de concesión por pagar: <strong
                            x-text="pendienteConcedido().toFixed(2)"></strong> €</br>
                        <hr class="my-2 border-dashed border-slate-400">
                        · Comisión total contrato: <span
                            x-text="Number(comisionTotal).toFixed(2)"></span> €</br>
                        · Comision asignada acum.: <span
                            x-text="Number(asignadoAcum).toFixed(2)"></span> €</br>
                        · Pendiente de asignar: <strong
                            x-text="pendienteAsignar().toFixed(2)"></strong> €</br>
                    </div>
                    <div>· Propuesta estimada: <strong x-text="propuesta.toFixed(2)"></strong> €
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" class="px-4 py-2 rounded border hover:bg-gray-50"
                        @click="close()">Cancelar</button>
                    {{-- <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Guardar
                        pago</button> --}}
                    <!-- Desactivar guardar si no queda concesión -->
                    <button type="submit"
                        class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700"
                        :disabled="pendienteConcedido() <= 0">
                        Guardar pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
