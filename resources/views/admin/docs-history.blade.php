<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Historial de Documentos · Backoffice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100">
    @include('layouts.headerbackoffice')

    <div x-data="{ open: false, userName: '', docs: [] }"
         class="w-full max-w-7xl mx-auto px-4 py-6 space-y-6">

        <h1 class="text-3xl font-bold text-gray-800 border-b pb-2 mb-6">
            Historial de Documentos
        </h1>

        {{-- Stats panel --}}
        @php
            $pendientes = $userDocuments->where('estado','pendiente')->count();
            $rechazados = $userDocuments->where('estado','rechazado')->count();
            $validados  = $userDocuments->where('estado','validado')->count();
            // $latestContracts is now passed from the controller, no need to re-query here
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-4 flex items-center">
                <i class="bx bx-time-five text-2xl text-yellow-500 mr-3"></i>
                <div>
                    <p class="text-sm text-gray-500">Pendientes</p>
                    <p class="mt-1 text-2xl font-semibold">{{ $pendientes }}</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 flex items-center">
                <i class="bx bx-x-circle text-2xl text-red-500 mr-3"></i>
                <div>
                    <p class="text-sm text-gray-500">Rechazados</p>
                    <p class="mt-1 text-2xl font-semibold">{{ $rechazados }}</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 flex items-center">
                <i class="bx bx-check-circle text-2xl text-green-500 mr-3"></i>
                <div>
                    <p class="text-sm text-gray-500">Validados</p>
                    <p class="mt-1 text-2xl font-semibold">{{ $validados }}</p>
                </div>
            </div>
        </div>

        {{-- Filtro por estado y CCAA --}}
        <form method="GET" action="{{ route('admin.docs-history') }}"
              class="flex flex-wrap items-end gap-4 mb-6">

            {{-- Estado --}}
            <div class="flex-1 min-w-[200px]">
                <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
                <select name="estado" id="estado"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Todos</option>
                    <option value="pendiente" {{ request('estado')==='pendiente'?'selected':'' }}>Pendiente</option>
                    <option value="validado"  {{ request('estado')==='validado'?'selected':'' }}>Validado</option>
                    <option value="rechazado" {{ request('estado')==='rechazado'?'selected':'' }}>Rechazado</option>
                </select>
            </div>

            {{-- CCAA --}}
            <div class="flex-1 min-w-[200px]">
                <label for="ccaa" class="block text-sm font-medium text-gray-700">CCAA</label>
                <select name="ccaa" id="ccaa"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Todas</option>
                    @foreach($ccaas as $ccaa)
                        <option value="{{ $ccaa->id }}"
                            {{ request('ccaa')==$ccaa->id ? 'selected' : '' }}>
                            {{ $ccaa->nombre_ccaa }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    class="px-4 py-2 bg-[#54debd] text-white rounded-md shadow hover:bg-[#43c5a9]">
                Filtrar
            </button>
            <a href="{{ route('admin.docs-history') }}"
               class="px-4 py-2 text-[#54debd] underline hover:text-[#43c5a9]">
                Limpiar
            </a>
        </form>
        {{-- Agrupamos por usuario solo los ítems de esta página --}}
        @php
            $docsByUser = $userDocuments->getCollection()->groupBy('user_id');
        @endphp

        @if($docsByUser->count())
            <div class="space-y-4">
                @foreach($docsByUser as $userId => $docs)
                    @php
                        $first = $docs->first();
                        $user  = $first->user;
                    @endphp
                    <div class="bg-white rounded-lg shadow p-6 flex justify-between items-center">
                        <div>
                            <p class="text-lg font-semibold">{{ $user->name }}</p>
                            <p class="text-sm text-gray-600">{{ $docs->count() }} documento(s)</p>
                            <p class="text-xs text-gray-500">ID tramitación: {{ $latestContracts[$userId] ?? '—' }}</p>
                        </div>
                        <button
                            @click.prevent="
                                userName = '{{ $user->name }}';
                                docs = {{ $docs->map(fn($d)=>[
                                    'id'=>$d->id,
                                    'name'=>$d->document->name,
                                    'estado'=>$d->estado,
                                    'url'=>$d->temporary_url
                                ])->toJson() }};
                                open = true
                            "
                            class="bg-[#54debd] hover:bg-[#43c5a9] text-white px-4 py-2 rounded"
                        >Ver documentos</button>
                    </div>
                @endforeach
            </div>
            {{-- enlaces de paginación --}}
            <div class="mt-6">
                {{ $userDocuments->appends(request()->query())->links() }}
            </div>
        @else
            <p class="text-center text-gray-500">No hay documentos que mostrar.</p>
        @endif

        {{-- Modal de documentos por usuario --}}
        <div x-show="open" x-cloak
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div @click.away="open=false"
                 class="bg-white rounded-lg w-full max-w-3xl mx-4 overflow-hidden">
                <div class="flex justify-between items-center px-6 py-4 border-b">
                    <h2 class="text-xl font-semibold">
                        Documentos de <span x-text="userName"></span>
                    </h2>
                    <button @click="open=false"
                            class="text-2xl text-gray-500 hover:text-gray-800">&times;</button>
                </div>
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    <template x-for="doc in docs" :key="doc.id">
                        <div class="flex justify-between items-center bg-gray-50 rounded p-4">
                            <div>
                                <p class="font-medium" x-text="doc.name"></p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Estado:
                                    <span :class="{
                                        'text-yellow-600': doc.estado=='pendiente',
                                        'text-green-600' : doc.estado=='validado',
                                        'text-red-600'   : doc.estado=='rechazado'
                                    }" x-text="doc.estado"></span>
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                {{-- selector + guardar igual que antes --}}
                                <select x-model="doc.estado"
                                        class="border-gray-300 rounded shadow-sm">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="validado">Validado</option>
                                    <option value="rechazado">Rechazado</option>
                                </select>
                                <button
                                    @click="
                                        let nota = null;
                                        if (doc.estado==='rechazado') {
                                            nota = prompt('Escribe la nota para el usuario (opcional):');
                                        }
                                        fetch(`/user-documents/${doc.id}`, {
                                            method:'PATCH',
                                            headers:{
                                                'Content-Type':'application/json',
                                                'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content
                                            },
                                            body: JSON.stringify({ estado: doc.estado, nota_rechazo: nota })
                                        })
                                        .then(r=>r.json())
                                        .then(data=>{ doc.estado=data.estado; alert('✅ '+data.estado) })
                                        .catch(()=>alert('❌ Error'));
                                    "
                                    class="px-3 py-1 bg-[#54debd] text-white rounded hover:bg-[#43c5a9]">
                                    Guardar
                                </button>
                                <a :href="doc.url" target="_blank"
                                   class="text-blue-600 hover:underline text-sm flex items-center">
                                    <i class="bx bx-show mr-1"></i> Ver
                                </a>
                            </div>
                        </div>
                    </template>
                    <template x-if="docs.length===0">
                        <p class="text-gray-500 text-center">— No hay documentos —</p>
                    </template>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
