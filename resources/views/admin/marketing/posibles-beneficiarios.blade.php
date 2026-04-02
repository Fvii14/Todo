<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Marketing - Posibles Beneficiarios</title>
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
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-800 min-h-screen">

    @include('layouts.headerbackoffice')

    <main class="container mx-auto px-6 py-8">
        <!-- Welcome Card -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover">
            <div class="p-6 md:p-8 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">Posibles Beneficiarios - Marketing</h2>
                        <p class="opacity-90">Genera listados de posibles beneficiarios para
                            campañas de marketing</p>
                    </div>
                    <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
                        @if ($ayudaSeleccionada && $posiblesBeneficiarios->total() > 0)
                            <div
                                class="bg-white bg-opacity-20 px-4 py-2 rounded-lg flex items-center space-x-2">
                                <i class="fas fa-users"></i>
                                <span>{{ $posiblesBeneficiarios->total() }} posibles
                                    beneficiarios</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de Selección -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-filter text-indigo-500 mr-2"></i>
                    Seleccionar Ayuda
                </h3>
                <form method="GET" action="{{ route('posibles-beneficiarios.index') }}"
                    class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label for="ayuda_id"
                            class="block text-sm font-medium text-gray-700 mb-2">Ayuda</label>
                        <select name="ayuda_id" id="ayuda_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Selecciona una ayuda --</option>
                            @foreach ($ayudas as $ayuda)
                                <option value="{{ $ayuda->id }}"
                                    {{ $ayudaId == $ayuda->id ? 'selected' : '' }}>
                                    {{ $ayuda->nombre_ayuda }} (ID: {{ $ayuda->id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition-colors">
                            <i class="fas fa-search mr-2"></i>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mensajes de éxito/error -->
        @if (session('success'))
            <div
                class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        @endif

        @if ($ayudaSeleccionada)
            <!-- Información sobre el reporte -->
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-semibold text-blue-800 mb-2">
                            ¿Qué usuarios se incluyen en el reporte?
                        </h3>
                        <div class="text-sm text-blue-700 space-y-1">
                            <p class="flex items-start">
                                <i class="fas fa-check-circle text-blue-500 mr-2 mt-0.5"></i>
                                <span>Usuarios que <strong>NO han contratado</strong> esta
                                    ayuda</span>
                            </p>
                            <p class="flex items-start">
                                <i class="fas fa-check-circle text-blue-500 mr-2 mt-0.5"></i>
                                <span>Usuarios que <strong>NO han solicitado</strong> esta ayuda (no
                                    tienen registro en el formulario específico)</span>
                            </p>
                            <p class="flex items-start">
                                <i class="fas fa-check-circle text-blue-500 mr-2 mt-0.5"></i>
                                <span>Usuarios que tienen <strong>CCAA definida</strong> en su
                                    perfil</span>
                            </p>
                            @if ($ayudaSeleccionada->ccaa_id)
                                <p class="flex items-start">
                                    <i class="fas fa-check-circle text-blue-500 mr-2 mt-0.5"></i>
                                    <span>Usuarios que pertenecen a la <strong>Comunidad
                                            Autónoma</strong> de esta ayuda
                                        ({{ App\Models\Ccaa::find($ayudaSeleccionada->ccaa_id)->nombre_ccaa ?? 'N/A' }})</span>
                                </p>
                            @else
                                <p class="flex items-start">
                                    <i class="fas fa-check-circle text-blue-500 mr-2 mt-0.5"></i>
                                    <span>Usuarios de <strong>todas las Comunidades
                                            Autónomas</strong> (ayuda estatal)</span>
                                </p>
                            @endif
                            <p class="flex items-start">
                                <i class="fas fa-check-circle text-blue-500 mr-2 mt-0.5"></i>
                                <span>Usuarios que son <strong>posibles beneficiarios</strong> según
                                    sus respuestas (todas las respuestas disponibles cumplen los
                                    requisitos)</span>
                            </p>
                        </div>
                        <p class="text-xs text-blue-600 mt-3 italic">
                            Este reporte está diseñado para campañas de marketing dirigidas a
                            usuarios potenciales que aún no han iniciado el proceso de solicitud.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-800">
                                Ayuda seleccionada: <span
                                    class="text-indigo-600">{{ $ayudaSeleccionada->nombre_ayuda }}</span>
                            </h4>
                            <p class="text-sm text-gray-600 mt-1">ID: {{ $ayudaSeleccionada->id }}
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <form method="POST"
                                action="{{ route('posibles-beneficiarios.generar') }}"
                                class="inline" id="formGenerarReporte"
                                onsubmit="mostrarProcesando()">
                                @csrf
                                <input type="hidden" name="ayuda_id"
                                    value="{{ $ayudaSeleccionada->id }}">
                                <button type="submit" id="btnGenerarReporte"
                                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-sync-alt mr-2"></i>
                                    <span id="textoBoton">Generar Reporte</span>
                                </button>
                            </form>
                            @if ($posiblesBeneficiarios->total() > 0)
                                <form method="POST"
                                    action="{{ route('posibles-beneficiarios.descargar-csv') }}"
                                    class="inline">
                                    @csrf
                                    <input type="hidden" name="ayuda_id"
                                        value="{{ $ayudaSeleccionada->id }}">
                                    <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-download mr-2"></i>
                                        Descargar CSV
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Listado de Posibles Beneficiarios -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden card-hover">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-table text-indigo-500 mr-2"></i>
                        Listado de Posibles Beneficiarios
                    </h3>
                </div>

                @if ($posiblesBeneficiarios->total() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nombre y Apellidos
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Teléfono
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        CCAA
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($posiblesBeneficiarios as $beneficiario)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $beneficiario->nombre_completo ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $beneficiario->email }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $beneficiario->telefono ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $beneficiario->ccaa ?? 'N/A' }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $posiblesBeneficiarios->links() }}
                    </div>
                @else
                    <div class="p-6 text-center">
                        <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600">No hay posibles beneficiarios registrados para
                            esta ayuda.</p>
                        <p class="text-sm text-gray-500 mt-2">Haz clic en "Generar Reporte" para
                            evaluar todos los usuarios.</p>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-md overflow-hidden card-hover">
                <div class="p-6 text-center">
                    <i class="fas fa-info-circle text-indigo-500 text-4xl mb-4"></i>
                    <p class="text-gray-600">Selecciona una ayuda para comenzar</p>
                </div>
            </div>
        @endif
    </main>

    <!-- Modal de Procesando -->
    <div id="modalProcesando"
        class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md mx-4">
            <div class="text-center">
                <div
                    class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mb-4">
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Generando Reporte</h3>
                <p class="text-gray-600 mb-4">Estamos evaluando todos los usuarios. Esto puede
                    tardar varios minutos...</p>
                <p class="text-sm text-gray-500">Por favor, no cierres esta ventana.</p>
            </div>
        </div>
    </div>

    <script>
        function mostrarProcesando() {
            const modal = document.getElementById('modalProcesando');
            const btn = document.getElementById('btnGenerarReporte');
            const texto = document.getElementById('textoBoton');

            if (modal && btn) {
                modal.classList.remove('hidden');
                btn.disabled = true;
                if (texto) {
                    texto.textContent = 'Procesando...';
                }
            }
        }

        // Si hay un mensaje de éxito, ocultar el modal después de un momento
        @if (session('success'))
            setTimeout(function() {
                const modal = document.getElementById('modalProcesando');
                if (modal) {
                    modal.classList.add('hidden');
                }
            }, 2000);
        @endif
    </script>

</body>

</html>
