<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Detalle usuario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .metadata-tech-trigger {
            transition: all 0.2s;
            box-shadow: 0 2px 5px rgba(99, 102, 241, 0.3);
        }

        .metadata-tech-trigger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(99, 102, 241, 0.4);
        }

        #metadata-tech-popup {
            backdrop-filter: blur(4px);
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-800 min-h-screen">

    @include('layouts.headerbackoffice')

    <main class="container mx-auto px-6 py-8">
        <!-- Header con gradiente -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover">
            <div class="p-6 md:p-8 bg-gradient-to-r from-[#54debd] to-[#0bc096] text-black">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">Detalles del Usuario</h2>
                        <p class="opacity-90">Información completa del perfil y respuestas</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white bg-opacity-20">
                            <i class="fas fa-user-circle mr-2"></i>
                            ID: {{ $user->id }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de perfil -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-id-card text-[#54debd] mr-2"></i>
                    Perfil Básico
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-center">
                        <div
                            class="flex-shrink-0 h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                            <i class="fas fa-user text-[#54debd]"></i>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-sm font-medium text-gray-500">Nombre de usuario</h4>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $user->name ?? 'No especificado' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-envelope text-[#54debd]"></i>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-sm font-medium text-gray-500">Correo electrónico</h4>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $user->email }}</p>
                        </div>
                    </div>
                    <!-- Añade más campos básicos según necesites -->
                </div>
            </div>
        </div>

        <!-- Tarjeta de respuestas -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden card-hover">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-clipboard-check text-[#54debd] mr-2"></i>
                    Respuestas del Usuario
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pregunta
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Respuesta
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($userDetails as $detail)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-normal">
                                    <div class="text-sm font-medium text-gray-900">
                                        {!! $detail['question'] !!}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-normal">
                                    <div class="text-sm text-gray-900">
                                        @if ($detail['slug'] === 'metadata_tech')
                                            <div class="text-sm text-gray-900">
                                                <button onclick="openMetadataTechPopup()"
                                                    class="metadata-tech-trigger inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                                    <i class="fas fa-code mr-1"></i> Ver datos técnicos
                                                </button>
                                            </div>
                                        @elseif (is_array($detail['answer']))
                                            <ul class="list-disc pl-5">
                                                @foreach ($detail['answer'] as $item)
                                                    <li>{{ $item }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            {{ $detail['answer'] ?? 'No respondido' }}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sección de acciones -->
        <div class="mt-6 flex flex-col sm:flex-row justify-end gap-4">
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-arrow-left mr-2"></i> Volver atrás
            </a>
            <button
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#54debd] hover:bg-[#0bc096] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd]">
                <i class="fas fa-envelope mr-2"></i> Contactar usuario
            </button>
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
                    Panel de administración por <a href="https://tutramitefacil.es/" target="_blank"
                        class="text-[#54debd] hover:text-[#0bc096]">TuTrámiteFácil</a>
                </div>
            </div>
        </div>
    </footer>

    <div id="metadata-tech-popup"
        class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black bg-opacity-50">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-xl font-bold">Metadatos</h2>
                    </div>
                    <button onclick="closeMetadataTechPopup()" class="text-white opacity-80 hover:opacity-100">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <div id="metadata-tech-content" class="space-y-4"></div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-100 p-4 flex justify-end">
                <button onclick="closeMetadataTechPopup()"
                    class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 transition-colors">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <script>
        function openMetadataTechPopup() {
            const popup = document.getElementById('metadata-tech-popup');
            popup.classList.remove('hidden');

            const metadataTech = @json($userDetails->firstWhere('slug', 'metadata_tech')['answer'] ?? '{}');

            try {
                const parsedData = typeof metadataTech === 'string' ? JSON.parse(metadataTech) : metadataTech;
                renderUserFriendlyJSON(parsedData);
            } catch (e) {
                document.getElementById('metadata-tech-content').innerHTML = `
      <div class="bg-red-50 text-red-700 p-4 rounded-lg">
        <i class="fas fa-exclamation-circle mr-2"></i> Error al procesar los datos técnicos
      </div>
    `;
            }
        }

        function closeMetadataTechPopup() {
            document.getElementById('metadata-tech-popup').classList.add('hidden');
        }

        function renderUserFriendlyJSON(data, parentKey = '') {
            const container = document.getElementById('metadata-tech-content');
            container.innerHTML = '';

            if (!data || typeof data !== 'object') {
                container.innerHTML = '<p>No hay datos técnicos disponibles</p>';
                return;
            }

            const renderSection = (key, value, depth = 0) => {
                const sectionId = `${parentKey}${key.replace(/\s+/g, '-')}`;
                const isObject = typeof value === 'object' && value !== null;
                const isArray = Array.isArray(value);

                let content = '';

                if (isObject) {
                    const children = Object.entries(value).map(([childKey, childValue]) =>
                        renderSection(childKey, childValue, depth + 1)
                    ).join('');

                    content = `
        <div class="ml-${depth * 4} border-l-2 border-gray-200 pl-4">
          ${children}
        </div>
      `;
                } else if (isArray) {
                    content = `
        <ul class="ml-${depth * 4} list-disc pl-5 space-y-1">
          ${value.map(item => `<li>${item}</li>`).join('')}
        </ul>
      `;
                } else {
                    let displayValue = value;
                    if (typeof value === 'boolean') displayValue = value ? 'Sí' : 'No';
                    if (value === null) displayValue = 'N/A';
                }

                return `
      <div class="mb-4">
        <div class="flex items-center justify-between cursor-pointer p-3 bg-white rounded-lg border border-gray-200 hover:border-indigo-300 transition-colors" 
             onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.toggle-icon').classList.toggle('fa-chevron-down'); this.querySelector('.toggle-icon').classList.toggle('fa-chevron-up')">
          <div class="flex items-center">
            <span class="font-semibold text-indigo-700 mr-2">${key}:</span>
            ${!isObject && !isArray ? `<span class="text-gray-600">${value}</span>` : ''}
          </div>
          ${(isObject || isArray) ? `
                                                                                                                <i class="toggle-icon fa-solid fa-chevron-down text-gray-400 text-sm"></i>
                                                                                                              ` : ''}
        </div>
        
        <div class="${(isObject || isArray) ? 'hidden' : ''}">
          ${content}
        </div>
      </div>
    `;
            };

            container.innerHTML = Object.entries(data)
                .map(([key, value]) => renderSection(key, value))
                .join('');
        }

        document.getElementById('metadata-tech-popup').addEventListener('click', function(e) {
            if (e.target === this) closeMetadataTechPopup();
        });
    </script>
</body>

</html>
