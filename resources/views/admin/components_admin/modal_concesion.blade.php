{{-- MODAL Concesión/Comisión --}}
<div x-data="modalConcesion()" x-init="window.addEventListener('open-montos', e => openModal(e.detail));
window.addEventListener('close-montos', () => close());" x-cloak class="relative z-100">

    <!-- Backdrop -->
    <div x-show="open" x-transition.opacity style="display:none" class="fixed inset-0 bg-black/40"
        @click="close()"></div>

    <!-- Modal -->
    <div x-show="open" x-transition @keydown.escape.window="close()" style="display:none"
        class="fixed inset-0 flex items-center justify-center p-4 z-[301]" role="dialog"
        aria-modal="true">
        <div class="w-full max-w-3xl bg-white rounded-2xl shadow-xl">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Concesión y comisión</h2>
                    <p class="text-sm text-gray-600 mt-1"
                        x-text="form.cliente && form.ayudaNombre ? `${form.cliente} · ${form.ayudaNombre}` : ''">
                    </p>
                </div>
                <button type="button" class="p-1 rounded hover:bg-gray-100" @click="close()"
                    aria-label="Cerrar">✕</button>
            </div>

            <form :action="action" method="POST" class="px-5 py-4 space-y-5"
                @input="onTotalChange()">
                @csrf
                @method('PUT')
                <input type="hidden" name="reparto_total_conocido"
                    :value="(reparto.enabled && reparto.totalConocido) ? 1: 0">

                <!-- Concesión -->
                <div>
                    <label class="block text-sm font-medium mb-1">Cantidad concedida por la
                        Administración (€)</label>
                    <input type="number" step="0.01" min="0" name="monto_total_ayuda"
                        class="w-full rounded border-gray-300" x-model.number="form.ayuda">
                </div>

                <!-- Comisión -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Comisión de TTF (€)</label>
                        <template x-if="form.comisionFija">
                            <div>
                                <div class="w-full py-2 px-3 rounded border border-gray-200 bg-gray-50 text-gray-700"
                                    x-text="format2(form.ayuda * (form.pct||0) / 100) + ' € (fija por producto: ' + format2(form.pct) + '%)'">
                                </div>
                                <input type="hidden" name="monto_comision"
                                    :value="format2(form.ayuda * (form.pct || 0) / 100)">
                            </div>
                        </template>
                        <template x-if="!form.comisionFija">
                            <input type="number" step="0.01" min="0"
                                name="monto_comision" class="w-full rounded border-gray-300"
                                x-model.number="form.comision">
                        </template>
                    </div>

                    <div x-show="!form.comisionFija">
                        <label class="block text-sm font-medium mb-1">Porcentaje (opcional)</label>
                        <div class="flex gap-2">
                            <input type="number" step="0.01" min="0" max="100"
                                class="w-full rounded border-gray-300" placeholder="Ej. 15"
                                x-model.number="form.pct">
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" class="rounded border-gray-300"
                                    x-model="form.usar_pct" @change="onUsarPctChange">
                                Usar %
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1" x-show="form.pct !== null">
                            Sugerida: <span
                                x-text="format2(form.ayuda * (form.pct||0) / 100)"></span> €
                        </p>
                    </div>
                </div>

                <!-- NUEVO: Reparto por años -->
                <div class="rounded-lg border p-4">
                    <div class="flex items-center justify-between gap-4 mb-3">
                        <div class="flex items-center gap-3">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" class="rounded border-gray-300"
                                    x-model="reparto.enabled" @change="initReparto()">
                                <span class="font-medium">Repartir por años</span>
                            </label>
                            <template x-if="reparto.enabled">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="checkbox" class="rounded border-gray-300"
                                        x-model="reparto.totalConocido"
                                        @change="recalcDistribucion()">
                                    Total conocido (reparto automático)
                                </label>
                            </template>
                        </div>
                        {{-- <template x-if="reparto.enabled && reparto.totalConocido">
                            <button type="button" class="text-sm rounded border px-3 py-1 hover:bg-gray-50"
                                @click="recalcDistribucion()">
                                Recalcular reparto
                            </button>
                        </template> --}}
                    </div>

                    <template x-if="reparto.enabled">
                        <div class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Primer año</label>
                                    <input type="number" class="w-full rounded border-gray-300"
                                        x-model.number="reparto.inicio" @input="initReparto()">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">N.º de
                                        años</label>
                                    <input type="number" min="1"
                                        class="w-full rounded border-gray-300"
                                        x-model.number="reparto.n" @input="initReparto()">
                                </div>
                                <div class="self-end text-sm text-gray-600">
                                    <div>Total concedido: <strong
                                            x-text="format2(form.ayuda)"></strong> €</div>
                                    <div>Suma anualidades: <strong
                                            x-text="format2(sumaDistribucion())"></strong> €
                                    </div>
                                    <div x-show="Math.abs(delta()) > 0.009" class="text-amber-700">
                                        Diferencia: <strong x-text="format2(delta())"></strong> €
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto rounded border">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-50 text-gray-600">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Año</th>
                                            <th class="px-3 py-2 text-right">Importe (€)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        <template x-for="(it,i) in reparto.items"
                                            :key="it.anio">
                                            <tr>
                                                <td class="px-3 py-2">
                                                    <input type="number"
                                                        class="w-28 rounded border-gray-300"
                                                        x-model.number="it.anio"
                                                        @input="/* permitir ajustar años puntualmente */">
                                                    <input type="hidden"
                                                        :name="`anualidades[${i}][anio]`"
                                                        :value="it.anio">
                                                </td>
                                                <td class="px-3 py-2 text-right">
                                                    <input type="number" step="0.01"
                                                        min="0"
                                                        class="w-40 text-right rounded border-gray-300"
                                                        :readonly="reparto.totalConocido"
                                                        x-model.number="it.importe"
                                                        :name="`anualidades[${i}][importe]`">
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-xs text-gray-600">
                                <p class="mt-1" x-show="reparto.totalConocido">
                                    * Reparto automático a partes iguales; se ajusta el último año
                                    para cuadrar
                                    céntimos.
                                </p>
                                <p class="mt-1" x-show="!reparto.totalConocido">
                                    * Puedes editar los importes por año libremente; la suma puede
                                    diferir del total
                                    mientras no esté cerrado.
                                </p>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" class="px-4 py-2 rounded border hover:bg-gray-50"
                        @click="close()">Cancelar</button>
                    <button type="submit"
                        class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function modalConcesion() {
        const currentYear = new Date().getFullYear();
        return {
            open: false,
            action: '',
            form: {
                id: null,
                ayuda: 0,
                comision: 0,
                pct: null,
                usar_pct: true,
                comisionFija: false,
                cliente: '',
                ayudaNombre: ''
            },
            reparto: {
                enabled: false,
                totalConocido: true,
                inicio: currentYear - 1,
                n: 2,
                items: []
            },
            format2(v) {
                return (Number(v) || 0).toFixed(2);
            },

            openModal(d) {
                this.open = true;
                this.action = d.action || '';
                this.form.id = d.id || null;
                this.form.ayuda = parseFloat(d.ayuda) || 0;
                this.form.comision = parseFloat(d.comision) || 0;
                this.form.pct = (d.pct !== '' && d.pct !== null && d.pct !== undefined) ?
                    parseFloat(d.pct) : null;
                this.form.comisionFija = !!d.comisionFija;
                this.form.usar_pct = (this.form.pct !== null) && !this.form.comisionFija;
                this.form.cliente = d.cliente || '';
                this.form.ayudaNombre = d.ayudaNombre || '';
                if (this.form.comisionFija && this.form.pct) {
                    this.form.comision = +(this.form.ayuda * this.form.pct / 100).toFixed(2);
                } else if (this.form.usar_pct && this.form.pct) {
                    this.form.comision = +(this.form.ayuda * this.form.pct / 100).toFixed(2);
                }
                this.initReparto();
            },

            close() {
                this.open = false;
            },

            onTotalChange() {
                this.$nextTick(() => {
                    if (this.form.usar_pct && this.form.pct) {
                        this.form.comision = +(this.form.ayuda * this.form.pct / 100)
                            .toFixed(2);
                    }
                    if (this.reparto.enabled && this.reparto.totalConocido) {
                        this.recalcDistribucion();
                    }
                });
            },

            initReparto() {
                if (!this.reparto.enabled) {
                    this.reparto.items = [];
                    return;
                }
                const n = Math.max(1, Number(this.reparto.n) || 1);
                const start = Number(this.reparto.inicio) || currentYear;
                // genera años consecutivos preservando importes existentes si coinciden
                const prev = this.reparto.items;
                this.reparto.items = Array.from({
                    length: n
                }, (_, i) => {
                    const anio = start + i;
                    const ex = prev.find(x => x.anio === anio);
                    return ex ? {
                        ...ex
                    } : {
                        anio,
                        importe: 0
                    };
                });
                this.recalcDistribucion();
            },

            recalcDistribucion() {
                if (!this.reparto.enabled || !this.reparto.totalConocido) return;
                const total = Math.max(0, Number(this.form.ayuda) || 0);
                const n = Math.max(1, this.reparto.items.length);
                const cents = Math.round(total * 100);
                const base = Math.floor(cents / n);
                const resto = cents - base * n;
                this.reparto.items = this.reparto.items.map((it, idx) => ({
                    ...it,
                    importe: (base + (idx === n - 1 ? resto : 0)) / 100
                }));
            },
            onUsarPctChange() {
                this.$nextTick(() => {
                    // Si activas "Usar %", recalculamos al instante; si lo desactivas, no tocamos la comisión (manual)
                    if (this.form.usar_pct && this.form.pct) {
                        this.form.comision = +(this.form.ayuda * this.form.pct / 100)
                            .toFixed(2);
                    }
                });
            },

            sumaDistribucion() {
                return this.reparto.items.reduce((acc, it) => acc + (Number(it.importe) || 0), 0);
            },

            delta() {
                const total = Number(this.form.ayuda) || 0;
                const suma = this.sumaDistribucion();
                return +(total - suma).toFixed(2);
            }
        }
    }
</script>
