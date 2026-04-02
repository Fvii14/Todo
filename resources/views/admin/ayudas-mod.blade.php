<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Ayudas - Backoffice</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
@include('layouts.headerbackoffice')

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">
            Modificar <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#54debd] to-[#368e79]">Ayudas</span>
        </h1>
        <p class="text-gray-600">Modifica fechas, estado y cuantías de las ayudas disponibles</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-6">
        @foreach($ayudas as $ayuda)
            <form method="POST" action="{{ route('ayudas.update', $ayuda->id) }}" class="bg-white shadow rounded-lg p-6">
                @csrf
                @method('PUT')

                <h2 class="text-xl font-semibold mb-4">{{ $ayuda->nombre_ayuda }}</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Fecha inicio visible -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha inicio</label>
                        <input type="date" name="fecha_inicio"
                               value="{{ optional($ayuda->fecha_inicio)->format('Y-m-d') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                    </div>

                    <!-- Fecha fin visible -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha fin</label>
                        <input type="date" name="fecha_fin"
                               value="{{ optional($ayuda->fecha_fin)->format('Y-m-d') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                    </div>

                    <!-- Inicio del plazo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Inicio periodo (recibos)</label>
                        <input type="date" name="fecha_inicio_periodo"
                               value="{{ optional($ayuda->fecha_inicio_periodo)->format('Y-m-d') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                    </div>

                    <!-- Fin del plazo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fin periodo (recibos)</label>
                        <input type="date" name="fecha_fin_periodo"
                               value="{{ optional($ayuda->fecha_fin_periodo)->format('Y-m-d') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                    </div>

                    <!-- Presupuesto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Presupuesto (€)</label>
                        <input type="number" step="0.01" min="0" name="presupuesto"
                               value="{{ $ayuda->presupuesto }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                    </div>

                    <!-- Cuantía por usuario -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cuantía por usuario (€)</label>
                        <input type="number" step="1" min="0" name="cuantia_usuario"
                               value="{{ $ayuda->cuantia_usuario }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>

                <!-- Checkbox activo -->
                <div class="mt-4 flex items-center">
                    <input type="hidden" name="activo" value="0">
                    <input type="checkbox" name="activo" value="1" id="activo_{{ $ayuda->id }}" class="h-5 w-5 text-green-600"
                           {{ $ayuda->activo ? 'checked' : '' }}>
                    <label for="activo_{{ $ayuda->id }}" class="ml-2 text-sm text-gray-700">Activo</label>
                </div>

                <!-- Botón -->
                <div class="mt-4 flex justify-end">
                    <button type="submit"
                            class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">
                        Guardar cambios
                    </button>
                </div>
            </form>
        @endforeach
    </div>
</div>
</body>
</html>
