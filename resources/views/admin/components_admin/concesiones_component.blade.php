    <div class="mx-auto max-w-7xl px-4 py-6">
        <div
            class="mb-6 rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50/80 px-5 py-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <i class="bx bx-check-double text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-semibold tracking-tight text-gray-900">Concesiones</h1>
                    <p class="mt-1 text-sm text-gray-500 leading-relaxed">
                        Aquí se indica que la administración ha publicado la resolución de una ayuda
                        y la cantidad concedida al cliente.
                    </p>
                </div>
            </div>
        </div>

        {{-- Mensajes (igual estilo que usas en otras vistas) --}}
        @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Filtros --}}
        <form method="GET" id="filtrosForm"
            class="mb-6 rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm">
            <input type="hidden" name="tab" value="concesiones">
            <div class="mb-4 flex items-center gap-2 text-gray-700">
                <i class="bx bx-filter-alt text-lg text-gray-400"></i>
                <span class="text-sm font-medium">Filtros</span>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-12 lg:gap-5">
                <div class="sm:col-span-2 lg:col-span-3">
                    <label
                        class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Ayuda</label>
                    <select name="ayuda_id"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        <option value="">Todas</option>
                        @foreach ($ayudas as $a)
                            <option value="{{ $a->id }}" @selected($selectedAyudaId == $a->id)>
                                {{ $a->nombre_ayuda }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label
                        class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Buscar</label>
                    <input type="text" name="q" value="{{ $q }}"
                        placeholder="Nombre o email del cliente"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>
                <div class="sm:col-span-2 lg:col-span-4">
                    <label
                        class="mb-2 block text-xs font-medium uppercase tracking-wide text-gray-500">Estado(s)
                        OPx</label>
                    <div class="flex flex-wrap gap-x-5 gap-y-2">
                        <label
                            class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm hover:bg-gray-100 has-[:checked]:border-emerald-300 has-[:checked]:bg-emerald-50">
                            <input type="checkbox" name="estado_opx[]" value="OP1-Resolucion"
                                class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                @checked(in_array('OP1-Resolucion', $selectedEstadosOPx ?? []))>
                            <span class="text-gray-700">OP1-Resolucion</span>
                        </label>
                        <label
                            class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm hover:bg-gray-100 has-[:checked]:border-emerald-300 has-[:checked]:bg-emerald-50">
                            <input type="checkbox" name="estado_opx[]" value="OP4"
                                class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                @checked(in_array('OP4', $selectedEstadosOPx ?? []))>
                            <span class="text-gray-700">OP4-Customer Success</span>
                        </label>
                    </div>
                    <p
                        class="mt-2 rounded-lg border border-amber-200/80 bg-amber-50/80 px-3 py-2 text-xs text-amber-800">
                        <strong>Nota:</strong> usa OP4 cuando necesites modificar la cantidad
                        concedida. Para el día a día, filtra por OP1-Resolucion.
                    </p>
                </div>
                <div class="flex flex-wrap items-end gap-2 sm:col-span-2 lg:col-span-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        <i class="bx bx-filter-alt text-base"></i> Filtrar
                    </button>
                    <a href="{{ route('operativa.liquidaciones.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-gray-300 focus:ring-offset-2">
                        Limpiar
                    </a>
                </div>
            </div>
        </form>

        {{-- Resultados --}}
        <div class="overflow-x-auto bg-white rounded-xl shadow">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-600">
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Ayuda</th>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Fecha contratación</th>
                        <th class="px-4 py-3 text-right">Comisión (€)</th>
                        <th class="px-4 py-3 text-right">Concedida (€)</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Liquidada</th>
                        <th class="px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($contrataciones as $c)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium">
                                    {{ $c->user->name ?? '—' }}
                                </div>
                                <div class="text-gray-500">{{ $c->user->email ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                {{ $c->ayuda->nombre_ayuda ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $c->product->product_name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ \Illuminate\Support\Carbon::parse($c->fecha_contratacion)->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ number_format($c->monto_comision ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                {{-- Editar en línea la cantidad concedida (monto_total_ayuda) --}}
                                <form method="POST"
                                    action="{{ route('operativa.liquidaciones.updateConcedida', $c->id) }}"
                                    class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="number" name="monto_total_ayuda" step="0.01"
                                        min="0"
                                        value="{{ old('monto_total_ayuda', $c->monto_total_ayuda) }}"
                                        class="w-32 rounded border-gray-300 text-right">
                                </form>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $codigos =
                                        $c->estadosContratacion?->pluck('codigo')->all() ?? [];
                                    $hasResolucion = in_array('OP1-Resolucion', $codigos);
                                    $hasTramitacion = in_array('OP1-Tramitacion', $codigos);
                                @endphp
                                @if ($codigos !== [])
                                    <div class="flex flex-wrap gap-1">
                                        @if ($hasResolucion)
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">OP1-Resolucion</span>
                                        @endif
                                        @if ($hasTramitacion)
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">OP1-Tramitacion</span>
                                        @endif
                                        @foreach ($codigos as $cod)
                                            @if ($cod !== 'OP1-Resolucion' && $cod !== 'OP1-Tramitacion')
                                                <span
                                                    class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-slate-100 text-slate-700">{{ $cod }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-right">
                                <span @class([
                                    'inline-flex items-center justify-center rounded-full whitespace-nowrap',
                                    'px-1.5 py-1.5 text-[16px] leading-4 font-medium',
                                    'bg-green-100 text-green-800' => $c->liquidada,
                                    'bg-red-100 text-red-800' => !$c->liquidada,
                                ])>
                                    {{ $c->liquidada ? 'Sí' : 'No' }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    @php
                                        $estadosAccion = $c->estadosContratacion ?? collect();
                                        $enOP4 = $estadosAccion->contains('grupo', 'OP4');
                                        $pct = $c->product?->commission_pct;
                                        $comisionFija = $pct !== null && $pct !== '';
                                        $payload = [
                                            'id' => $c->id,
                                            'ayuda' => (float) ($c->monto_total_ayuda ?? 0),
                                            'comision' => (float) ($c->monto_comision ?? 0),
                                            'pct' => $pct,
                                            'comisionFija' => $comisionFija,
                                            'action' => route(
                                                'operativa.liquidaciones.updateMontos',
                                                $c->id,
                                            ),
                                            'cliente' => $c->user->name ?? '',
                                            'ayudaNombre' => $c->ayuda->nombre_ayuda ?? '',
                                        ];
                                        $btnClasses = $enOP4
                                            ? 'bg-amber-200 hover:bg-amber-300 text-amber-900 border border-amber-300'
                                            : 'bg-emerald-600 hover:bg-emerald-700 text-white border border-emerald-700';
                                    @endphp

                                    <button type="button"
                                        class="inline-flex items-center gap-2 px-3 py-1 rounded text-xs transition {{ $btnClasses }}"
                                        title="{{ $enOP4 ? 'Editar concesión/comisión' : 'Registrar concesión/comisión' }}"
                                        onclick='openMontosModal(@json($payload))'>
                                        {{ $enOP4 ? 'Editar concesión/comisión' : 'Registrar concesión/comisión' }}
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                                No hay resultados con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $contrataciones->links() }}
        </div>
    </div>
