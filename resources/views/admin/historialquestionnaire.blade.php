<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Cuestionarios - Backoffice</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
@include('layouts.headerbackoffice')

<main class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#54debd] to-[#368e79]">Historial de Cuestionarios</span>
        </h1>
        <p class="text-gray-600">Visualiza el comportamiento de los usuarios en los cuestionarios</p>
        <div class="mt-4">
            <span class="inline-block bg-gradient-to-r from-[#54debd] to-[#43c5a9] text-white px-4 py-2 rounded-full text-sm font-medium">
                Total sesiones: {{ $totalSesiones }}
            </span>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8 transition-all duration-300 hover:shadow-2xl">
        <div class="p-6 md:p-8 bg-gradient-to-r from-[#54debd] to-[#0bc096] text-black">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold mb-2">Datos Generales</h2>
                    <p class="opacity-90 text-black">Estadísticas del historial de cuestionarios</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de métricas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white rounded-xl shadow-md overflow-hidden transition-transform duration-300 hover:scale-[1.02]">
            <div class="p-6 flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Cuestionarios sin completar</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $registrosUnicos }}</p>
                    <p class="text-xs text-gray-400 mt-2">Últimos 30 días</p>
                </div>
                <div class="bg-blue-50 p-3 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#54debd]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden transition-transform duration-300 hover:scale-[1.02]">
            <div class="p-6 flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Usuarios únicos</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $usuariosUnicos }}</p>
                    <p class="text-xs text-gray-400 mt-2">Usuarios distintos</p>
                </div>
                <div class="bg-green-50 p-3 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden transition-transform duration-300 hover:scale-[1.02]">
            <div class="p-6 flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Tiempo promedio</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $tiempoPromedio }}</p>
                    <p class="text-xs text-gray-400 mt-2">Minutos por sesión</p>
                </div>
                <div class="bg-amber-50 p-3 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

<div class="container mx-auto p-6 space-y-6">
    <h1 class="text-2xl font-bold">Historial de Formularios</h1>

    @foreach ($formularios as $formulario)
        @php
            $formularioId = $formulario['formulario_id'];
            $sesiones     = $formulario['sesiones'];
        @endphp

        <div x-data="{ open: false }" class="bg-white border border-gray-200 shadow rounded-xl">
            <!-- ▶️ T O G G L E  -->
            <button
                @click="open = !open"
                class="w-full flex items-center justify-between px-6 py-4 text-left bg-white hover:bg-[#54debd] rounded-t-xl transition">
                <h2 class="text-lg font-semibold text-black">
                    {{ $formulario['formulario_name'] }}
                </h2>
                <svg :class="{ 'rotate-180': open }"
                     class="w-5 h-5 text-[#54debd] transition-transform duration-300"
                     xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- ▶️ C O N T E N I D O -->
            <div x-show="open" x-transition class="p-6 border-t border-gray-200 space-y-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase tracking-wide text-xs">
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($sesiones as $session)
                                <tr>
                                    <td class="px-4 py-2">({{ $session['user_id'] }}) : {{ $session['user_name'] }}</td>
                                    <td class="px-4 py-2">{{ $session['time_end'] }}</td>
                                    <td class="px-4 py-2 font-mono text-xs">{{ $session['session_token'] }}</td>
                                    <td class="px-4 py-2">{{ $session['total_interactions'] }}</td>
                                    <td class="px-4 py-2">{{ $session['total_minutes'] }}</td>
                                    <td class="px-4 py-2">
                                        @php
                                            $traducciones = [
                                                'next'   => 'Siguiente',
                                                'back'   => 'Atrás',
                                                'submit' => 'Enviar',
                                                '1'      => 'Sí',
                                                '0'      => 'No',
                                            ];
                                        @endphp
                                        <ul class="list-disc list-inside space-y-1">
                                            @foreach ($session['acciones'] as $accion)
                                                <li>
                                                    {{ $traducciones[$accion['direction']] ?? ucfirst($accion['direction']) }}
                                                    – Respuesta:
                                                    {{ $traducciones[$accion['respuesta']] ?? ucfirst($accion['respuesta']) }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $sesiones->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

</main>

</body>
</html>