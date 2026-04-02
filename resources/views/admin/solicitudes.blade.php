<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Solicitudes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .info-chip {
            transition: all 0.2s ease;
        }

        .info-chip:hover {
            transform: scale(1.05);
        }

        .section-divider {
            border-top: 1px dashed #e2e8f0;
        }

        .help-toggle {
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
        }

        .help-toggle.active {
            max-height: 1000px;
        }

        .rotate-90 {
            transform: rotate(90deg);
        }

        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .animate-float {
            animation: float 1s ease-in-out infinite;
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        .delay-400 {
            animation-delay: 0.4s;
        }

        .delay-500 {
            animation-delay: 0.5s;
        }

        .delay-600 {
            animation-delay: 0.6s;
        }

        .delay-700 {
            animation-delay: 0.7s;
        }

        .delay-800 {
            animation-delay: 0.8s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-3px);
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-800 min-h-screen">

    @include('layouts.headerbackoffice')

    <main class="container mx-auto px-6 py-8">
        <!-- Welcome Card -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover">
            <div class="p-6 md:p-8 bg-gradient-to-r from-[#54debd] to-[#00b88d] text-black">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">Gestión de Solicitudes</h2>
                        <p class="opacity-90">Comprueba las solicitudes de los usuarios</p>
                    </div>

                </div>
            </div>
        </div>
        <div>
        </div>
        <div class="bg-white rounded-2xl shadow-md overflow-hidden card-hover">
            <div class="p-6 border-b border-gray-100">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-table texT-[#54debd] mr-2"></i>
                        Listado de Solicitudes
                    </h3>
                    <div class="w-full md:w-64">
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" id="searchAyudasInput"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-[#54debd] focus:border-[#54debd]"
                                placeholder="Buscar en solicitudes..." onkeyup="filterAyudas()">
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID Solicitud</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Formulario asociado</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Categoría</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha solicitud</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Preguntas</th>
                        </tr>
                    </thead>
                    <tbody class="bayudasg-white divide-y divide-gray-200" id="ayudasTableBody">
                        @foreach ($solicitudes as $solicitud)
                            @php
                                $observaciones = is_string($solicitud->observaciones)
                                    ? json_decode($solicitud->observaciones, true)
                                    : (is_array($solicitud->observaciones)
                                        ? $solicitud->observaciones
                                        : []);

                                $preguntas = \App\Models\Question::whereIn(
                                    'id',
                                    array_keys($observaciones),
                                )
                                    ->get()
                                    ->keyBy('id');

                                // Contamos respuestas válidas (ni null ni vacías)
                                $respuestasValidas = collect($observaciones)->filter(function (
                                    $valor,
                                ) {
                                    return $valor !== null && $valor !== '';
                                });
                                $cantidadRespuestas = $respuestasValidas->count();
                            @endphp

                            <!--<tr class="@if ($solicitud->user->is_admin) bg-purple-50 border-l-4 border-purple-600 @else hover:bg-gray-50 @endif">-->
                            <tr id="solicitud{{ $solicitud->id }}"
                                class="hover:bg-gray-50 ayuda-row cursor-pointer"
                                onclick="toggleSolicitud('solicitud{{ $solicitud->id }}')">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <i class="fas fa-question  text-[#54debd]"></i>
                                        </div>
                                        <div class="ml-4 text-sm font-medium text-gray-900">ID:
                                            {{ $solicitud->id }}
                                        </div>
                                    </div>
                                </td>
                                <td class="ml-4">
                                    {{-- DESACOPLADO: enlace a panel-usuario (se mantiene userDetail) --}}
                                    {{-- <a href="{{ route('admin.panel-usuario', [$solicitud->user->id]) }}"> --}}
                                    <div>
                                        <div class="flex items-center">
                                            <a href="{{ route('admin.userDetail', $solicitud->user->id) }}"
                                                target="_blank"
                                                class="text-sm font-medium text-[#000000] hover:text-blue-800 hover:underline cursor-pointer">
                                                {{ $solicitud->user->name }}
                                            </a>
                                            @if ($solicitud->user->is_admin)
                                                <span
                                                    class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    <i class="fas fa-crown mr-1"></i>Admin
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $solicitud->user->email }}</div>
                                    </div>
                                    {{-- </a> --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $solicitud->ayuda->questionnaire->name ?? 'Ninguno' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @switch($solicitud->estado)
                                        @case('Aprobado')
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Aprobado
                                            </span>
                                        @break

                                        @case('Pendiente de tramitar')
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pte. tramite
                                            </span>
                                        @break

                                        @case('Presentada')
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Presentada
                                            </span>
                                        @break

                                        @case('Rechazado')
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Rechazado
                                            </span>
                                        @break

                                        @default
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ $solicitud->estado ?? 'Desconocido' }}
                                            </span>
                                    @endswitch
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ ucfirst(strtolower($solicitud->ayuda->sector ?? 'Desconocido')) }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $solicitud->fecha_solicitud ? $solicitud->fecha_solicitud->format('d/m/Y H:i') : 'Desconocido' }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 flex items-center">
                                        <span
                                            class="editable-field">{{ $cantidadRespuestas }}</span>
                                        <i class="fas fa-chevron-right ml-2 text-xs transition-transform duration-200"
                                            id="chevron{{ $solicitud->id }}"></i>
                                    </div>
                                </td>
                            </tr>
                            <tr id="solicitud{{ $solicitud->id }}-row" class="hidden">
                                @php
                                    $observaciones = is_string($solicitud->observaciones)
                                        ? json_decode($solicitud->observaciones, true)
                                        : (is_array($solicitud->observaciones)
                                            ? $solicitud->observaciones
                                            : []);

                                    $preguntas = \App\Models\Question::whereIn(
                                        'id',
                                        array_keys($observaciones),
                                    )
                                        ->get()
                                        ->keyBy('id');

                                    // Filtramos las respuestas no vacías
                                    $respuestasValidas = collect($observaciones)->filter(function (
                                        $valor,
                                    ) {
                                        return $valor !== null &&
                                            $valor !== '' &&
                                            !(is_array($valor) && empty($valor));
                                    });
                                @endphp
                                <td colspan="7" class="px-6 py-4 bg-gray-50">
                                    <div class="pl-12">

                                        <table
                                            class="table-auto w-full border-separate border-spacing-x-4 border-spacing-y-2">
                                            @if ($respuestasValidas->isEmpty())
                                                <tr>
                                                    <td colspan="2"
                                                        class="text-gray-600 px-4 py-4">
                                                        El usuario no completó la solicitud.
                                                    </td>
                                                </tr>
                                            @else
                                                <tr class="pl-0">
                                                    <th scope="col"
                                                        class="w-2/3 text-left font-medium text-gray-700 tracking-wider">
                                                        Preguntas:
                                                    </th>
                                                    <th scope="col"
                                                        class="w-1/2 text-left font-medium text-gray-700 tracking-wider">
                                                        Respuestas:
                                                    </th>
                                                </tr>

                                                <tbody>
                                                    @foreach ($observaciones as $slug => $respuesta)
                                                        @php
                                                            // Buscamos el objeto Question por slug (o por ID, según tu lógica)
                                                            $pregunta = $preguntas[$slug] ?? null;
                                                        @endphp

                                                        @if (
                                                            $pregunta &&
                                                                !is_null($respuesta) &&
                                                                $respuesta !== '' &&
                                                                !(is_array($respuesta) && empty($respuesta)))
                                                            <tr class="bg-gray-100">
                                                                <td
                                                                    class="align-top font-medium px-4 py-2 w-2/3">
                                                                    {{ $pregunta->text }}
                                                                </td>
                                                                <td
                                                                    class="align-top font-normal px-4 py-2 w-1/2">
                                                                    @switch($pregunta->type)
                                                                        @case('boolean')
                                                                            @if ($respuesta === '1' || $respuesta === 1)
                                                                                Sí
                                                                                @elseif
                                                                                ($respuesta === '0' || $respuesta === 0)
                                                                                No
                                                                            @else
                                                                                Desconocido
                                                                            @endif
                                                                        @break

                                                                        @case('date')
                                                                            @php
                                                                                $fecha = \Carbon\Carbon::parse(
                                                                                    $respuesta,
                                                                                );
                                                                            @endphp
                                                                            {{ $fecha ? $fecha->format('d/m/Y H:i') : 'Sin fecha' }}
                                                                        @break

                                                                        @case('select')
                                                                            @php
                                                                                $idx = (int) $respuesta;
                                                                                $label = $pregunta->labelForOption(
                                                                                    $idx,
                                                                                );
                                                                            @endphp
                                                                            {{ $label ?? 'No seleccionado' }}
                                                                        @break

                                                                        @case('multiple')
                                                                            @php
                                                                                $ids = (array) $respuesta;
                                                                                $labelsArray = $pregunta->labelsForMultiple(
                                                                                    $ids,
                                                                                );
                                                                                $texto = count(
                                                                                    $labelsArray,
                                                                                )
                                                                                    ? implode(
                                                                                        ', ',
                                                                                        $labelsArray,
                                                                                    )
                                                                                    : 'Sin opciones';
                                                                            @endphp
                                                                            {{ $texto }}
                                                                        @break

                                                                        @default
                                                                            {{ $respuesta }}
                                                                    @endswitch
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                            @endif
                    </tbody>

                </table>
            </div>
            </td>
            </tr>
            </tr>
            </tbody>
            @endforeach
            </tbody>
            </table>
        </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6 mt-12">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-folder-open text-[#54debd]"></i>
                    </div>
                    <span class="font-medium">Collector by TTF</span>
                </div>
                <div class="text-sm text-gray-500">
                    Panel de administración por <a href="https://tutramitefacil.es/"
                        target="_blank"
                        class="text-[#54debd] hover:text-indigo-800">TuTrámiteFácil</a>
                </div>
            </div>
        </div>
    </footer>
    <script>
        function filterAyudas() {
            const searchValue = document.getElementById('searchAyudasInput').value.toLowerCase();

            document.querySelectorAll('.ayuda-row').forEach(row => {
                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(searchValue) ? '' : 'none';
            });
        }

        function toggleSolicitud(id) {
            event.stopPropagation();

            const row = document.getElementById(`${id}-row`);
            const chevron = document.getElementById(`chevron${id.replace('solicitud', '')}`);

            // Alternar visibilidad del detalle
            row.classList.toggle('hidden');

            // Girar el ícono
            if (chevron) {
                chevron.classList.toggle('rotate-90');
            }

            // Cerrar otros abiertos
            document.querySelectorAll('.ayuda-row').forEach((tr) => {
                const otherId = tr.id;
                if (otherId !== id) {
                    const otherRow = document.getElementById(`${otherId}-row`);
                    const otherChevron = document.getElementById(
                        `chevron${otherId.replace('solicitud', '')}`);
                    if (otherRow && !otherRow.classList.contains('hidden')) {
                        otherRow.classList.add('hidden');
                        if (otherChevron) {
                            otherChevron.classList.remove('rotate-90');
                        }
                    }
                }
            });
        }
    </script>
</body>

</html>
