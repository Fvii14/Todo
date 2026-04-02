@if ($tab === 'pagos')
    <div class="mx-auto max-w-screen-2xl px-4 py-6">
        {{-- Cabecera (mismo estilo que concesiones) --}}
        <div
            class="mb-6 rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50/80 px-5 py-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <i class="bx bx-wallet text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-semibold tracking-tight text-gray-900">Pagos y comisiones
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 leading-relaxed">
                        Registro de pagos de la Administración y comisiones asignadas. Marca la
                        comisión como cobrada cuando se haya liquidado.
                    </p>
                </div>
            </div>
        </div>

        {{-- Filtros (mismo estilo que concesiones) --}}
        <form method="GET" x-data="{
            showNoLiquidadas: {{ request()->boolean('show_no_liquidadas') ? 'true' : 'false' }},
            withPendiente: {{ request()->boolean('with_pendiente') ? 'true' : 'false' }},
            onlyMorosos: {{ request()->boolean('only_morosos') ? 'true' : 'false' }}
        }"
            x-effect="if(!showNoLiquidadas) withPendiente = false"
            class="mb-6 rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm">
            <input type="hidden" name="tab" value="pagos">

            <div class="mb-4 flex items-center gap-2 text-gray-700">
                <i class="bx bx-filter-alt text-lg text-gray-400"></i>
                <span class="text-sm font-medium">Filtros</span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-12 lg:gap-5">
                <div class="sm:col-span-2 lg:col-span-3">
                    <label
                        class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Ayuda</label>
                    <select name="ayuda_id_pagos"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        <option value="">Todas</option>
                        @foreach ($ayudas as $a)
                            <option value="{{ $a->id }}" @selected(($selectedAyudaIdPagos ?? null) == $a->id)>
                                {{ $a->nombre_ayuda }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label
                        class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Buscar</label>
                    <input type="text" name="q_pagos" value="{{ $qPagos ?? '' }}"
                        placeholder="Nombre o email del cliente"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>
                <div class="sm:col-span-2 lg:col-span-4">
                    <label
                        class="mb-2 block text-xs font-medium uppercase tracking-wide text-gray-500">Ver</label>
                    <div class="flex flex-wrap gap-x-4 gap-y-2">
                        <label
                            class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm hover:bg-gray-100 has-[:checked]:border-emerald-300 has-[:checked]:bg-emerald-50">
                            <input type="checkbox" name="show_no_liquidadas" value="1"
                                x-model="showNoLiquidadas"
                                class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-gray-700">No liquidadas</span>
                        </label>
                        <label
                            class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm hover:bg-gray-100 has-[:checked]:border-emerald-300 has-[:checked]:bg-emerald-50">
                            <input type="checkbox" name="show_liquidadas" value="1"
                                @checked(request()->boolean('show_liquidadas'))
                                class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-gray-700">Liquidadas</span>
                        </label>
                        <template x-if="showNoLiquidadas">
                            <label
                                class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm hover:bg-gray-100 has-[:checked]:border-emerald-300 has-[:checked]:bg-emerald-50"
                                x-cloak>
                                <input type="checkbox" name="with_pendiente" value="1"
                                    x-model="withPendiente"
                                    class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="text-gray-700">Con pago pendiente</span>
                            </label>
                        </template>
                        <label
                            class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm hover:bg-gray-100 has-[:checked]:border-emerald-300 has-[:checked]:bg-emerald-50">
                            <input type="checkbox" name="only_morosos" value="1"
                                x-model="onlyMorosos"
                                class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-gray-700">Solo morosos</span>
                        </label>
                    </div>
                </div>
                <div class="flex flex-wrap items-end gap-2 sm:col-span-2 lg:col-span-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        <i class="bx bx-filter-alt text-base"></i> Filtrar
                    </button>
                    <a href="{{ route('operativa.liquidaciones.index', ['tab' => 'pagos']) }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-gray-300 focus:ring-offset-2">
                        Limpiar
                    </a>
                </div>
            </div>
        </form>

        {{-- Tabla --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-600">
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Ayuda</th>
                        <th class="px-4 py-3 text-right">Concedida (€)</th>
                        <th class="px-4 py-3 text-right">Cantidad recibida (€)</th>
                        <th class="px-4 py-3 text-right">Cantidad pendiente (€)</th>
                        <th class="px-4 py-3 text-right hidden xl:table-cell">Comisión total (€)
                        </th>
                        <th class="px-4 py-3 text-right hidden xl:table-cell">Cobrado acum. (€)</th>
                        <th class="px-4 py-3 text-right hidden xl:table-cell">Pendiente cobro (€)
                        </th>
                        <th class="px-4 py-3 text-right">Liquidada</th>
                        <th class="px-4 py-3 text-right">Pagos realizados</th>
                        <th class="px-4 py-3 text-right">Moroso</th>
                        <th class="px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($contrataciones as $c)
                        <tr class="hover:bg-gray-50/80">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $c->user->name ?? '—' }}</div>
                                <div class="text-gray-500">{{ $c->user->email ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $c->ayuda->nombre_ayuda ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                {{ number_format($c->monto_total_ayuda ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ number_format($c->pagado_acum_admin ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @php
                                    $concedida = (float) ($c->monto_total_ayuda ?? 0);
                                    $recibida = (float) ($c->pagado_acum_admin ?? 0);
                                    $pendienteAyuda = max($concedida - $recibida, 0);
                                @endphp
                                {{ number_format($pendienteAyuda, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right hidden xl:table-cell">
                                {{ number_format($c->monto_comision ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right hidden xl:table-cell">
                                {{ number_format($c->cobrado_acum ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right hidden xl:table-cell">
                                {{ number_format($c->pendiente_cobro ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span @class([
                                    'inline-flex items-center justify-center rounded-full whitespace-nowrap px-2 py-1 text-xs font-medium',
                                    'bg-green-100 text-green-800' => $c->liquidada,
                                    'bg-red-100 text-red-800' => !$c->liquidada,
                                ])>
                                    {{ $c->liquidada ? 'Sí' : 'No' }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ (int) ($c->pagos_count ?? 0) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @php
                                    $pendientes = (int) ($c->pagos_pendientes ?? 0);
                                    $esMoroso = $pendientes > 1;
                                @endphp
                                <span @class([
                                    'inline-flex items-center justify-center rounded-full whitespace-nowrap px-2 py-1 text-xs font-medium',
                                    'bg-red-100 text-red-800' => $esMoroso,
                                    'bg-green-100 text-green-800' => !$esMoroso,
                                ])>
                                    {{ $esMoroso ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    {{-- Registrar pago (primario) --}}
                                    <button type="button"
                                        onclick='openPagoAdmin({
                                              id: {{ $c->id }},
                                              cliente: @json($c->user->email),
                                              ayudaNombre: @json($c->ayuda->nombre_ayuda),
                                              comisionTotal: {{ (float) ($c->monto_comision ?? 0) }},
                                              asignadoAcum:  {{ (float) ($c->asignado_acum ?? 0) }},
                                              concedidoTotal: {{ (float) ($c->monto_total_ayuda ?? 0) }},
                                              pagadoAcum:     {{ (float) ($c->pagado_acum_admin ?? 0) }},
                                              commissionPct: {{ $c->product && $c->product->commission_pct !== null && $c->product->commission_pct !== '' ? json_encode((float) $c->product->commission_pct) : 'null' }}
                                            })'
                                        class="inline-flex items-center justify-center gap-2 h-10 w-44 px-4 rounded-lg border border-transparent bg-blue-600 text-white text-sm font-medium shadow-sm hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 transition whitespace-nowrap"
                                        title="Registrar pago de la Administración"
                                        aria-label="Registrar pago de la Administración">
                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none"
                                            stroke="currentColor" stroke-width="2"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>Registrar pago</span>
                                    </button>

                                    {{-- Ver pagos (secundario) --}}
                                    <button type="button"
                                        class="inline-flex items-center justify-center gap-2 h-10 w-44 px-4 rounded-lg border border-slate-300 bg-amber-200 text-slate-700 text-sm font-medium hover:bg-amber-100 hover:border-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 transition whitespace-nowrap"
                                        onclick="openPagosAdminModal({
                                            id: {{ $c->id }},
                                            cliente: @js($c->user->name ?? ''),
                                            ayudaNombre: @js($c->ayuda->nombre_ayuda ?? ''),
                                            concedidoTotal: {{ (float) ($c->monto_total_ayuda ?? 0) }},
                                            comisionTotal:  {{ (float) ($c->monto_comision ?? 0) }}
                                            })">
                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none"
                                            stroke="currentColor" stroke-width="2"
                                            aria-hidden="true">
                                            <rect x="3" y="5" width="18" height="14"
                                                rx="2" ry="2"></rect>
                                            <path stroke-linecap="round" d="M3 10h18" />
                                            <path stroke-linecap="round" d="M7 15h6" />
                                        </svg>
                                        <span>Ver pagos</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-4 py-6 text-center text-gray-500">No hay
                                resultados con los filtros aplicados.</td>
                        </tr>
                    @endforelse
                    @include('admin.components_admin.lista_pagos')
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $contrataciones->links() }}
        </div>
    </div>
@endif
