@php $isPartial = isset($partial) && $partial; @endphp

@if (!isset($user))
    @php
        throw new Exception('Variable $user no está definida en la vista panel-usuario');
    @endphp
@endif

@if (!$isPartial)
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Panel de Usuario</title>
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

            .question-toggle {
                transition: all 0.3s ease;
                max-height: 0;
                overflow: hidden;
            }

            .question-toggle.active {
                max-height: 1000px;
            }

            .rotate-90 {
                transform: rotate(90deg);
            }

            .modal-overlay {
                background-color: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);
            }

            .editable-field {
                cursor: pointer;
                transition: all 0.2s ease;
                border-bottom: 1px dashed transparent;
            }

            .editable-field:hover {
                border-bottom: 1px dashed #818cf8;
                background-color: #f8fafc;
            }

            .editable-field.active {
                background-color: #e0e7ff;
                border-bottom: 1px dashed #6366f1;
            }

            .editable-field.editing {
                background-color: #fef3c7;
                border: 2px solid #f59e0b;
                border-radius: 4px;
                padding: 4px 8px;
                outline: none;
            }

            .editable-field.editing:focus {
                border-color: #d97706;
                box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
            }

            .save-button {
                background-color: #10b981;
                color: white;
                border: none;
                border-radius: 4px;
                padding: 2px 8px;
                font-size: 12px;
                cursor: pointer;
                margin-left: 4px;
                display: none;
            }

            .save-button:hover {
                background-color: #059669;
            }

            .cancel-button {
                background-color: #ef4444;
                color: white;
                border: none;
                border-radius: 4px;
                padding: 2px 8px;
                font-size: 12px;
                cursor: pointer;
                margin-left: 4px;
                display: none;
            }

            .cancel-button:hover {
                background-color: #dc2626;
            }

            .crm-timeline {
                animation: fadeIn 0.3s ease-in-out;
            }

            .crm-timeline .timeline-item {
                transition: all 0.2s ease;
            }

            .crm-timeline .timeline-item:hover {
                transform: translateX(4px);
                background-color: #f8fafc;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .timeline-phase-indicator {
                transition: all 0.3s ease;
            }

            .timeline-phase-indicator.completed {
                transform: scale(1.2);
            }

            .editable-field:hover {
                background-color: #f3f4f6;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.2s ease;
            }

            .editable-field:hover::after {
                content: " ✏️";
                opacity: 0.7;
                font-size: 0.9em;
            }

            .editable-field.editing .save-button,
            .editable-field.editing .cancel-button {
                display: inline-block;
            }

            .editable-field.editing input {
                min-width: 150px;
                font-size: inherit;
                font-family: inherit;
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

            /* Añade hasta delay-800 */
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
    @include('layouts.headerbackoffice')

    <body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
@endif
<!-- Header -->
<!-- Welcome Card -->
<div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 card-hover"
    data-user-id="{{ $user->id }}">
    <div class="p-6 md:p-8 bg-gradient-to-r from-[#54debd] to-[#43e4bf] text-black">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between text-left">
            <div>
                <h2 class="text-2xl font-bold mb-2">Panel de usuario</h2>
                <p class="opacity-90 f">Administra el usuario</p>
            </div>
        </div>
    </div>
</div>

<!-- Tarjeta principal del perfil -->
<div class="bg-white rounded-2xl shadow-md overflow-hidden card-hover mb-8">
    <div class="p-6 border-b border-gray-100">
        <div class="flex flex-row justify-between mb-5">
            <h3 class="text-lg font-semibold flex items-center">
                <i class="fa-solid fa-address-card text-[#54debd] mr-3"></i>
                Perfil usuario
            </h3>
        </div>
        <div class="flex flex-col lg:flex-row items-stretch gap-8">
            <!-- Foto y nombre -->
            <div class="flex flex-col items-center justify-center w-full lg:w-1/3">
                <div
                    class="w-28 h-28 rounded-full @if ($user->is_admin) bg-black border-purple-1000 @else bg-white @endif flex items-center justify-center border border-[#54debd] shadow-sm object-cover card-hover">
                    <i
                        class="fas @if ($user->is_admin) ml-3 fa-user-shield not-last-of-type text-[#54debd] @else fa-user text-[#54debd] @endif text-6xl"></i>
                </div>

                <div class="text-2xs font-medium text-gray-900 mt-2">{{ $user->name }}</div>
                <div class="text-2xs text-gray-500">{{ $user->email }}</div>
            </div>

            <!-- Separador vertical -->
            <div class="hidden lg:flex items-center">
                <div class="w-px bg-gray-300 self-stretch"></div>
            </div>

            <!-- Datos del usuario -->
            <div class="w-full lg:w-2/3">
                <!-- Datos de Contacto -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-phone text-blue-500 mr-2"></i>
                        Datos de Contacto
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                        <div>
                            <p class="font-semibold text-sm text-gray-600">Email</p>
                            <p class="text-gray-900">{{ $user->email }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-600">Teléfono</p>
                            <p class="editable-field" data-question-slug="telefono"
                                data-original-value="{{ $userData['telefono'] ?? '' }}">
                                {{ $userData['telefono'] ?? 'No registrado' }}
                                @if (!empty($userData['telefono']))
                                    @php
                                        // Formatear el teléfono para WhatsApp (eliminar espacios, guiones, paréntesis)
                                        $telefonoWhatsapp = preg_replace(
                                            '/[^0-9+]/',
                                            '',
                                            $userData['telefono'],
                                        );
                                        // Si no empieza por +, asumimos España (+34)
                                        if ($telefonoWhatsapp && $telefonoWhatsapp[0] !== '+') {
                                            $telefonoWhatsapp =
                                                '+34' . ltrim($telefonoWhatsapp, '0');
                                        }
                                        $mensajeWhatsapp = urlencode(
                                            'Hola ' .
                                                ($userData['nombre_completo'] ?? '') .
                                                ', te contactamos desde TuTramiteFacil.',
                                        );
                                    @endphp
                                    <span
                                        class="ml-2 flex flex-row items-center space-x-2 inline-flex align-middle">
                                        <a href="https://wa.me/{{ ltrim($telefonoWhatsapp, '+') }}?text={{ $mensajeWhatsapp }}"
                                            target="_blank" title="Enviar WhatsApp"
                                            class="inline-block align-middle"
                                            onclick="registrarComunicacionOperativa('WhatsApp');">
                                            <i
                                                class="fab fa-whatsapp text-black text-xl hover:text-gray-700"></i>
                                        </a>
                                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $userData['telefono']) }}"
                                            title="Llamar" class="inline-block align-middle"
                                            onclick="registrarComunicacionOperativa('Llamada');">
                                            <i
                                                class="fas fa-phone text-black text-xl hover:text-gray-700"></i>
                                        </a>
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Datos Personales -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-user text-green-500 mr-2"></i>
                        Datos Personales
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                        <div>
                            <p class="font-semibold text-sm text-gray-600">DNI/NIE</p>
                            <p class="editable-field" data-question-slug="dni_nie"
                                data-original-value="{{ $userData['dni_nie'] ?? '' }}"
                                data-answer-id="{{ $user->answers->where('question_id', 34)->first()->id ?? '' }}"
                                data-question-type="text">
                                {{ $userData['dni_nie'] ?? 'No registrado' }}
                            </p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-600">Nombre Completo</p>
                            <p class="editable-field" data-question-slug="solo_nombre"
                                data-original-value="{{ $userData['solo_nombre'] ?? '' }}"
                                data-answer-id="{{ $user->answers->where('question_id', 33)->first()->id ?? '' }}">
                                @php
                                    $nombre = $userData['solo_nombre'] ?? null;
                                    $primerApellido = $userData['primer_apellido'] ?? null;
                                    $segundoApellido = $userData['segundo_apellido'] ?? null;
                                    $nombreCompleto = trim(
                                        collect([$nombre, $primerApellido, $segundoApellido])
                                            ->filter()
                                            ->implode(' '),
                                    );
                                @endphp
                                {{ $nombreCompleto ?: 'No registrado' }}
                            </p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-600">Estado Civil</p>
                            @php
                                $estadoCivil = $userData['estado_civil'] ?? null;
                            @endphp

                            <p class="editable-field" data-question-slug="estado_civil"
                                data-original-value="{{ $estadoCivil ?? '' }}"
                                data-question-type="select"
                                data-question-options='{"1":"Soltero/a","2":"Casado/a","3":"Viudo/a","4":"Divorciado/a"}'>
                                @switch($estadoCivil)
                                    @case('1')
                                        Soltero/a
                                    @break

                                    @case('2')
                                        Casado/a
                                    @break

                                    @case('3')
                                        Viudo/a
                                    @break

                                    @case('4')
                                        Divorciado/a
                                    @break

                                    @default
                                        No registrado
                                @endswitch
                            </p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-600">Fecha de Alta</p>
                            <p class="text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Datos de Ubicación -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                        Datos de Ubicación
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                        <div>
                            <p class="font-semibold text-sm text-gray-600">Comunidad Autónoma</p>
                            <p class="editable-field" data-question-slug="comunidad_autonoma"
                                data-original-value="{{ $userData['comunidad_autonoma'] ?? '' }}"
                                data-question-type="select">
                                {{ optional(\App\Models\Ccaa::find($userData['comunidad_autonoma']))->nombre_ccaa ??
                                    'No registrado' }}
                            </p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-600">Provincia</p>
                            <p class="editable-field" data-question-slug="provincia"
                                data-original-value="{{ $userData['provincia'] ?? '' }}"
                                data-question-type="select">
                                {{ optional(\App\Models\Provincia::find($userData['provincia']))->nombre_provincia ??
                                    'No registrado' }}
                            </p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-600">Municipio</p>
                            <p class="editable-field" data-question-slug="municipio"
                                data-original-value="{{ $userData['municipio'] ?? '' }}"
                                data-question-type="select">
                                {{ optional(\App\Models\Municipio::find($userData['municipio']))->nombre_municipio ??
                                    'No registrado' }}
                            </p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-600">Domicilio</p>
                            <p class="editable-field" data-question-slug="domicilio"
                                data-original-value="{{ $userData['domicilio'] ?? '' }}">
                                {{ $userData['domicilio'] ?? 'No registrado' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Datos Económicos -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-euro-sign text-yellow-500 mr-2"></i>
                        Datos Económicos
                    </h4>
                    @php
                        // Buscar todas las respuestas del usuario que correspondan a preguntas con categoría "datos-economicos"
                        $preguntasEconomicas = $user
                            ->answers()
                            ->whereHas('question', function ($q) {
                                $q->where('categoria', 'datos-economicos');
                            })
                            ->get();
                    @endphp

                    @if ($preguntasEconomicas->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                            @foreach ($preguntasEconomicas as $respuesta)
                                @if ($respuesta->question)
                                    <div>
                                        <p class="font-semibold text-sm text-gray-600">
                                            {{ $respuesta->question->text }}</p>
                                        <p class="text-gray-900 editable-field"
                                            data-answer-id="{{ $respuesta->id }}"
                                            data-original-value="{{ $respuesta->answer }}"
                                            data-question-type="{{ $respuesta->question->type }}"
                                            data-question-options="{{ $respuesta->question->options ? json_encode($respuesta->question->options) : '' }}">
                                            {{ $respuesta->getFormattedAnswer() }}
                                        </p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No hay datos económicos registrados.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bloque de Datos Collector (desplegable) --}}
<div class="bg-white rounded-2xl shadow-md overflow-hidden card-hover mb-8"
    x-data="{ open: false }">
    <div class="p-6 border-b border-gray-100 flex items-center cursor-pointer select-none"
        @click="open = !open">
        <h3 class="text-lg font-semibold flex items-center">
            <i class="fas fa-database text-[#54debd] mr-2"></i>
            Datos Collector
        </h3>
        <span class="ml-auto">
            <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas transition-all"></i>
        </span>
    </div>
    <div class="px-6 py-4" x-show="open" x-transition>
        @if ($collectorQuestions->count())
            @php
                $preguntasConRespuesta = $collectorQuestions->filter(function ($pregunta) use (
                    $collectorAnswers,
                ) {
                    return isset($collectorAnswers[$pregunta->id]) &&
                        $collectorAnswers[$pregunta->id]->question &&
                        $collectorAnswers[$pregunta->id]->answer !== null &&
                        $collectorAnswers[$pregunta->id]->answer !== '';
                });

                // Agrupar preguntas por categoría
                $preguntasPorCategoria = $preguntasConRespuesta->groupBy('categoria');
            @endphp

            @if ($preguntasConRespuesta->count())
                <div class="space-y-6">
                    @foreach ($preguntasPorCategoria as $categoria => $preguntas)
                        @php
                            // Definir iconos y colores por categoría
                            $iconosCategoria = [
                                'datos-personales' => [
                                    'icon' => 'fas fa-user',
                                    'color' => 'text-green-500',
                                ],
                                'datos-contacto' => [
                                    'icon' => 'fas fa-phone',
                                    'color' => 'text-blue-500',
                                ],
                                'datos-ubicacion' => [
                                    'icon' => 'fas fa-map-marker-alt',
                                    'color' => 'text-red-500',
                                ],
                                'datos-economicos' => [
                                    'icon' => 'fas fa-euro-sign',
                                    'color' => 'text-yellow-500',
                                ],
                                'datos-laborales' => [
                                    'icon' => 'fas fa-briefcase',
                                    'color' => 'text-purple-500',
                                ],
                                'datos-familiares' => [
                                    'icon' => 'fas fa-users',
                                    'color' => 'text-pink-500',
                                ],
                                'datos-fiscales' => [
                                    'icon' => 'fas fa-file-invoice-dollar',
                                    'color' => 'text-indigo-500',
                                ],
                                'otros' => [
                                    'icon' => 'fas fa-info-circle',
                                    'color' => 'text-gray-500',
                                ],
                            ];

                            $icono = $iconosCategoria[$categoria] ?? $iconosCategoria['otros'];
                            $tituloCategoria = ucwords(str_replace('-', ' ', $categoria));
                        @endphp

                        <div class="mb-4">
                            <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="{{ $icono['icon'] }} {{ $icono['color'] }} mr-2"></i>
                                {{ $tituloCategoria }}
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                                @foreach ($preguntas as $pregunta)
                                    <div>
                                        <p class="font-semibold text-sm text-gray-600">
                                            {{ $pregunta->text }}</p>
                                        @if (in_array($pregunta->slug, ['provincia', 'municipio']))
                                            <span
                                                class="text-gray-700 bg-gray-100 px-2 py-1 rounded opacity-70 cursor-not-allowed select-none"
                                                title="Este campo no es editable">
                                                <i class="fas fa-lock mr-1 text-gray-400"></i>
                                                {{ $collectorAnswers[$pregunta->id]->getFormattedAnswer() }}
                                            </span>
                                        @else
                                            <span class="text-gray-700 editable-field"
                                                data-answer-id="{{ $collectorAnswers[$pregunta->id]->id }}"
                                                data-original-value="{{ $collectorAnswers[$pregunta->id]->answer }}"
                                                data-question-type="{{ $pregunta->type }}"
                                                data-question-options="{{ $pregunta->options ? json_encode($pregunta->options) : '' }}">
                                                {{ $collectorAnswers[$pregunta->id]->getFormattedAnswer() }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <span class="text-gray-500">No hay respuestas registradas para Collector.</span>
            @endif
        @else
            <span class="text-gray-500">No hay preguntas de Collector configuradas.</span>
        @endif
    </div>
</div>

{{-- Listado de Solicitudes/Trámites --}}
<div class="bg-white rounded-2xl shadow-md overflow-hidden card-hover mb-8">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-lg font-semibold flex items-center">
            <i class="fas fa-list-alt text-[#54debd] mr-2"></i>
            Listado de Solicitudes/Trámites
        </h3>
        <input type="text" id="searchAyudasInput" onkeyup="filterAyudas()"
            placeholder="Buscar en solicitudes..."
            class="w-64 px-3 py-2 border rounded-lg focus:ring-[#54debd]/50 focus:border-[#54debd]" />
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-left">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 uppercase text-xs font-medium text-gray-500">Formulario
                        asociado</th>
                    <th class="px-6 py-3 uppercase text-xs font-medium text-gray-500">Categoría
                    </th>
                    <th class="px-6 py-3 uppercase text-xs font-medium text-gray-500">Estado
                        solicitud</th>
                    <th class="px-6 py-3 uppercase text-xs font-medium text-gray-500">Estado
                        trámite</th>
                    <th class="px-6 py-3 uppercase text-xs font-medium text-gray-500">Doc-Fase</th>
                    <th class="px-6 py-3 uppercase text-xs font-medium text-gray-500">Fecha
                        solicitud</th>
                    <th class="px-6 py-3 uppercase text-xs font-medium text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody id="ayudasTableBody" class="bg-white divide-y divide-gray-200">
                @php
                    // IDs de ayudas que ya tenían solicitud
                    $solicitudAyudaIds = $ayudasSolicitadas->pluck('ayuda_id')->toArray();
                @endphp

                {{-- 1º Recorro las solicitudes de ayuda --}}
                @foreach ($ayudasSolicitadas as $sol)
                    @php
                        $hasContract = in_array($sol->ayuda_id, $contratacionAyudaIds);
                        $mostrar =
                            ($sol->estado === 'Pendiente de tramitar' && !$hasContract) ||
                            $sol->estado === 'Rechazado' ||
                            $hasContract;
                        $questionnaire = $sol->ayuda->questionnaire ?? null;
                        $preguntas = $questionnaire ? $questionnaire->questions : collect();
                        $respuestas = $user
                            ->answers()
                            ->whereIn('question_id', $preguntas->pluck('id'))
                            ->get()
                            ->keyBy('question_id');

                        // Validar que el enum esté definido correctamente
                        $tipoFormulario = null;
                        if (
                            $questionnaire &&
                            $questionnaire->tipo &&
                            method_exists($questionnaire->tipo, 'value')
                        ) {
                            $tipoFormulario = $questionnaire->tipo->value;
                        }
                    @endphp
                    @if ($mostrar)
                        <tr class="ayuda-row hover:bg-gray-50" id="solicitud{{ $sol->id }}">
                            <td class="px-6 py-4">
                                @if ($sol->ayuda && $sol->ayuda->questionnaire)
                                    <div class="flex flex-col">
                                        <span
                                            class="font-medium text-gray-900">{{ $sol->ayuda->questionnaire->name ?? '—' }}</span>
                                        @if ($tipoFormulario)
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full mt-1">
                                                <i
                                                    class="fas fa-clipboard-list text-blue-500 mr-1"></i>
                                                {{ ucfirst($tipoFormulario) }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ ucfirst(strtolower($sol->ayuda->sector ?? 'Desconocido')) }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($sol->estado === 'Pendiente de tramitar' && !$hasContract)
                                    <span
                                        class="px-2 inline-flex text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Beneficiario</span>
                                @elseif($sol->estado === 'Rechazado')
                                    <span
                                        class="px-2 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">No
                                        beneficiario</span>
                                @elseif($hasContract)
                                    <span
                                        class="px-2 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">Contratado</span>
                                @else
                                    <span
                                        class="px-2 inline-flex text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ $sol->estado ?? 'Sin estado' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $sol->estado ?? '—' }}</td>
                            <td class="px-6 py-4">{{ $sol->fase ?? '—' }}</td>
                            <td class="px-6 py-4">
                                @if ($sol->fecha_solicitud)
                                    {{ \Carbon\Carbon::parse($sol->fecha_solicitud)->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-gray-400">Sin fecha</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-chevron-right transition-transform"
                                        id="chevron{{ $sol->id }}" style="cursor:pointer"
                                        onclick="toggleSolicitud('solicitud{{ $sol->id }}')"></i>
                                    @if ($sol->estado === 'Rechazado')
                                        <button
                                            onclick="deleteSolicitud({{ $sol->id }}, '{{ $sol->ayuda->questionnaire->name ?? 'Solicitud' }}')"
                                            class="text-red-600 hover:text-red-800 transition-colors"
                                            title="Eliminar solicitud rechazada">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr id="solicitud{{ $sol->id }}-row" class="hidden bg-gray-50">
                            <td colspan="7" class="px-6 py-4">
                                {{-- Detalle de preguntas y respuestas --}}
                                @if ($questionnaire)
                                    <div class="mb-3">
                                        <h4
                                            class="text-sm font-semibold text-blue-700 mb-2 flex items-center">
                                            <i
                                                class="fas fa-clipboard-list text-blue-500 mr-2"></i>
                                            Formulario: {{ $questionnaire->name ?? 'Sin nombre' }}
                                            {{ $tipoFormulario ? '(' . $tipoFormulario . ')' : '' }}
                                        </h4>
                                    </div>
                                    @if ($preguntas->count())
                                        <div class="space-y-3">
                                            @foreach ($preguntas as $pregunta)
                                                @if (isset($respuestas[$pregunta->id]))
                                                    <div
                                                        class="bg-white p-3 rounded-lg border border-gray-200">
                                                        <div class="flex items-start space-x-3">
                                                            <div
                                                                class="flex-shrink-0 w-2 h-2 bg-blue-400 rounded-full mt-2">
                                                            </div>
                                                            <div class="flex-1">
                                                                <div
                                                                    class="text-sm font-medium text-gray-900 mb-1">
                                                                    {{ $pregunta->text }}
                                                                </div>
                                                                <div class="text-sm text-gray-700 editable-field"
                                                                    data-answer-id="{{ $respuestas[$pregunta->id]->id }}"
                                                                    data-original-value="{{ $respuestas[$pregunta->id]->answer }}"
                                                                    data-question-type="{{ $pregunta->type }}"
                                                                    data-question-options="{{ $pregunta->options ? json_encode($pregunta->options) : '' }}">
                                                                    <span
                                                                        class="font-medium text-gray-600">Respuesta:</span>
                                                                    {{ $respuestas[$pregunta->id]->getFormattedAnswer() }}
                                                                </div>
                                                                @if ($pregunta->sub_text)
                                                                    <div
                                                                        class="text-xs text-gray-500 mt-1">
                                                                        {{ $pregunta->sub_text }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                        @if ($preguntas->filter(fn($p) => isset($respuestas[$p->id]))->isEmpty())
                                            <div class="text-center py-4 text-gray-500">
                                                <i
                                                    class="fas fa-info-circle text-2xl text-gray-300 mb-2"></i>
                                                <p class="text-sm">No hay respuestas registradas
                                                    para este formulario.</p>
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-center py-4 text-gray-500">
                                            <i
                                                class="fas fa-exclamation-triangle text-2xl text-gray-300 mb-2"></i>
                                            <p class="text-sm">No hay preguntas configuradas en
                                                este formulario.</p>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-center py-4 text-gray-500">
                                        <i
                                            class="fas fa-info-circle text-2xl text-gray-300 mb-2"></i>
                                        <p class="text-sm">No hay formulario asociado a esta ayuda.
                                        </p>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach

                {{-- 2º Ahora agrego las contrataciones que NO tenían solicitud previa --}}
                @foreach ($contrataciones as $c)
                    @if (!in_array($c->ayuda_id, $solicitudAyudaIds))
                        @php
                            $questionnaire = $c->ayuda->questionnaire ?? null;
                            $preguntas = $questionnaire ? $questionnaire->questions : collect();
                            $respuestas = $user
                                ->answers()
                                ->whereIn('question_id', $preguntas->pluck('id'))
                                ->get()
                                ->keyBy('question_id');

                            // Validar que el enum esté definido correctamente
                            $tipoFormularioContratacion = null;
                            if (
                                $questionnaire &&
                                $questionnaire->tipo &&
                                method_exists($questionnaire->tipo, 'value')
                            ) {
                                $tipoFormularioContratacion = $questionnaire->tipo->value;
                            }
                        @endphp
                        <tr class="ayuda-row hover:bg-gray-50"
                            id="contratacion{{ $c->id }}">
                            <td class="px-6 py-4">
                                @if ($c->ayuda && $c->ayuda->questionnaire)
                                    <div class="flex flex-col">
                                        <span
                                            class="font-medium text-gray-900">{{ $c->ayuda->questionnaire->name ?? '—' }}</span>
                                        @if ($tipoFormularioContratacion === 'pre')
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full mt-1">
                                                <i
                                                    class="fas fa-file-contract text-green-500 mr-1"></i>
                                                Pre-Solicitud
                                            </span>
                                        @elseif($tipoFormularioContratacion)
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full mt-1">
                                                <i class="fas fa-file-alt text-gray-500 mr-1"></i>
                                                {{ ucfirst($tipoFormularioContratacion) }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ ucfirst(strtolower($c->ayuda->sector ?? 'Desconocido')) }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">Contratado</span>
                            </td>
                            <td class="px-6 py-4">—</td> {{-- sin relación tramite --}}
                            <td class="px-6 py-4">—</td> {{-- sin relación tramite --}}
                            <td class="px-6 py-4">{{ $c->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <i class="fas fa-chevron-right transition-transform"
                                    id="chevronC{{ $c->id }}" style="cursor:pointer"
                                    onclick="toggleSolicitud('contratacion{{ $c->id }}')"></i>
                            </td>
                        </tr>
                        <tr id="contratacion{{ $c->id }}-row" class="hidden bg-gray-50">
                            <td colspan="7" class="px-6 py-4">
                                @if ($c->ayuda && $c->ayuda->questionnaire)
                                    @php
                                        $questionnaire = $c->ayuda->questionnaire;
                                        $preguntas = $questionnaire
                                            ->questions()
                                            ->orderBy('questionnaire_questions.orden')
                                            ->get();
                                        $respuestas = $user
                                            ->answers()
                                            ->whereIn('question_id', $preguntas->pluck('id'))
                                            ->get()
                                            ->keyBy('question_id');
                                    @endphp

                                    @if ($tipoFormularioContratacion === 'pre')
                                        <div class="mb-3">
                                            <h4
                                                class="text-sm font-semibold text-green-700 mb-2 flex items-center">
                                                <i
                                                    class="fas fa-file-contract text-green-500 mr-2"></i>
                                                Formulario Pre-Solicitud de la Contratación
                                                ({{ $preguntas->count() }} preguntas)
                                            </h4>
                                        </div>
                                        @if ($preguntas->count())
                                            <div class="space-y-3">
                                                @foreach ($preguntas as $pregunta)
                                                    @if (isset($respuestas[$pregunta->id]))
                                                        <div
                                                            class="bg-white p-3 rounded-lg border border-gray-200">
                                                            <div
                                                                class="flex items-start space-x-3">
                                                                <div
                                                                    class="flex-shrink-0 w-2 h-2 bg-green-400 rounded-full mt-2">
                                                                </div>
                                                                <div class="flex-1">
                                                                    <div
                                                                        class="text-sm font-medium text-gray-900 mb-1">
                                                                        {{ $pregunta->text }}
                                                                    </div>
                                                                    <div
                                                                        class="text-sm text-gray-700">
                                                                        <span
                                                                            class="font-medium text-gray-600">Respuesta:</span>
                                                                        {{ $respuestas[$pregunta->id]->getFormattedAnswer() }}
                                                                    </div>
                                                                    @if ($pregunta->sub_text)
                                                                        <div
                                                                            class="text-xs text-gray-500 mt-1">
                                                                            {{ $pregunta->sub_text }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                            @if ($preguntas->filter(fn($p) => isset($respuestas[$p->id]))->isEmpty())
                                                <div class="text-center py-4 text-gray-500">
                                                    <i
                                                        class="fas fa-info-circle text-2xl text-gray-300 mb-2"></i>
                                                    <p class="text-sm">No hay respuestas
                                                        registradas para este formulario.</p>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-center py-4 text-gray-500">
                                                <i
                                                    class="fas fa-exclamation-triangle text-2xl text-gray-300 mb-2"></i>
                                                <p class="text-sm">No hay preguntas configuradas en
                                                    este formulario.</p>
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-center py-4 text-gray-500">
                                            <i
                                                class="fas fa-info-circle text-2xl text-gray-300 mb-2"></i>
                                            <p class="text-sm">Formulario de tipo:
                                                {{ $tipoFormulacion ?? 'No definido' }}</p>
                                            <p class="text-xs text-gray-400">Esta contratación no
                                                tiene formulario pre-solicitud.</p>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-center py-4 text-gray-500">
                                        <i
                                            class="fas fa-info-circle text-2xl text-gray-300 mb-2"></i>
                                        <p class="text-sm">No hay formulario asociado a esta
                                            contratación.</p>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach

            </tbody>

        </table>
    </div>
</div> {{-- Mensaje de estado --}}
<div id="statusMessage" class="hidden mx-6 my-4 p-4 rounded-lg text-white"></div>

{{-- Bloque de Estados Comerciales --}}
<div class="bg-white rounded-2xl shadow-md overflow-hidden card-hover mb-8"
    x-data="{ timelineOpen: null }" data-timeline-open="">
    <div class="p-6 border-b border-gray-100">
        <h3 class="text-lg font-semibold flex items-center">
            <i class="fas fa-chart-line text-[#54debd] mr-2"></i>
            Estados Comerciales
        </h3>
    </div>
    <div class="p-6">
        @if (isset($estadosComerciales) &&
                (count($estadosComerciales['caliente']) > 0 ||
                    count($estadosComerciales['tibio']) > 0 ||
                    count($estadosComerciales['frio']) > 0))
            <div class="space-y-6">
                {{-- Estado Caliente --}}
                @if (count($estadosComerciales['caliente']) > 0)
                    <div class="border-l-4 border-red-500 bg-red-50 rounded-r-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-semibold text-red-800 flex items-center">
                                <i class="fas fa-fire text-red-500 mr-2"></i>
                                Estado Caliente
                            </h4>
                            <span
                                class="bg-red-500 text-white text-sm font-bold px-3 py-1 rounded-full">
                                {{ count($estadosComerciales['caliente']) }}
                            </span>
                        </div>
                        <div class="space-y-3">
                            @foreach ($estadosComerciales['caliente'] as $userAyuda)
                                @if ($userAyuda->ayuda)
                                    <div class="bg-white border border-red-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <span
                                                    class="font-medium text-red-900">{{ $userAyuda->ayuda->nombre_ayuda }}</span>
                                                <div class="text-sm text-red-700 mt-1">
                                                    <span class="font-medium">Fecha:</span>
                                                    {{ $userAyuda->fecha_solicitud ? \Carbon\Carbon::parse($userAyuda->fecha_solicitud)->format('d/m/Y') : 'Sin fecha' }}
                                                </div>
                                                @if ($userAyuda->tags)
                                                    <div class="text-sm text-red-600 mt-1">
                                                        <span class="font-medium">Tags:</span>
                                                        {{ $userAyuda->tags }}
                                                    </div>
                                                @endif
                                            </div>
                                            <button
                                                @click="timelineOpen === '{{ $userAyuda->ayuda_id }}' ? timelineOpen = null : timelineOpen = '{{ $userAyuda->ayuda_id }}'"
                                                :class="timelineOpen === '{{ $userAyuda->ayuda_id }}' ?
                                                    'bg-red-200 text-red-900' :
                                                    'bg-red-100 hover:bg-red-200 text-red-800'"
                                                class="ml-4 px-3 py-2 rounded-lg transition-colors flex items-center space-x-2 relative">
                                                <i :class="timelineOpen === '{{ $userAyuda->ayuda_id }}' ?
                                                    'fas fa-chevron-up' : 'fas fa-history'"
                                                    class="transition-transform duration-200"></i>
                                                <span
                                                    x-text="timelineOpen === '{{ $userAyuda->ayuda_id }}' ? 'Ocultar Timeline' : 'Ver Timeline'"></span>
                                                @if (($userAyuda->crm_history ?? collect())->count() > 0)
                                                    <span
                                                        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                                        {{ ($userAyuda->crm_history ?? collect())->count() }}
                                                    </span>
                                                @endif
                                            </button>
                                        </div>

                                        {{-- Línea temporal desplegable --}}
                                        <div x-show="timelineOpen === '{{ $userAyuda->ayuda_id }}'"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0 transform scale-95"
                                            x-transition:enter-end="opacity-100 transform scale-100"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100 transform scale-100"
                                            x-transition:leave-end="opacity-0 transform scale-95"
                                            class="mt-4 pt-4 border-t border-red-200"
                                            data-timeline-content="{{ $userAyuda->ayuda_id }}">
                                            @include('admin.components.crm-timeline', [
                                                'crmHistory' =>
                                                    $userAyuda->crm_history ?? collect(),
                                            ])
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Estado Tibio --}}
                @if (count($estadosComerciales['tibio']) > 0)
                    <div class="border-l-4 border-yellow-500 bg-yellow-50 rounded-r-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-semibold text-yellow-800 flex items-center">
                                <i class="fas fa-mug-hot text-yellow-500 mr-2"></i>
                                Estado Tibio
                            </h4>
                            <span
                                class="bg-yellow-500 text-white text-sm font-bold px-3 py-1 rounded-full">
                                {{ count($estadosComerciales['tibio']) }}
                            </span>
                        </div>
                        <div class="space-y-3">
                            @foreach ($estadosComerciales['tibio'] as $userAyuda)
                                @if ($userAyuda->ayuda)
                                    <div class="bg-white border border-yellow-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <span
                                                    class="font-medium text-yellow-900">{{ $userAyuda->ayuda->nombre_ayuda }}</span>
                                                <div class="text-sm text-yellow-700 mt-1">
                                                    <span class="font-medium">Fecha:</span>
                                                    {{ $userAyuda->fecha_solicitud ? \Carbon\Carbon::parse($userAyuda->fecha_solicitud)->format('d/m/Y') : 'Sin fecha' }}
                                                </div>
                                                @if ($userAyuda->tags)
                                                    <div class="text-sm text-yellow-600 mt-1">
                                                        <span class="font-medium">Tags:</span>
                                                        {{ $userAyuda->tags }}
                                                    </div>
                                                @endif
                                            </div>
                                            <button
                                                @click="timelineOpen === '{{ $userAyuda->ayuda_id }}' ? timelineOpen = null : timelineOpen = '{{ $userAyuda->ayuda_id }}'"
                                                :class="timelineOpen === '{{ $userAyuda->ayuda_id }}' ?
                                                    'bg-yellow-200 text-yellow-900' :
                                                    'bg-yellow-100 hover:bg-yellow-200 text-yellow-800'"
                                                class="ml-4 px-3 py-2 rounded-lg transition-colors flex items-center space-x-2 relative">
                                                <i :class="timelineOpen === '{{ $userAyuda->ayuda_id }}' ?
                                                    'fas fa-chevron-up' : 'fas fa-history'"
                                                    class="transition-transform duration-200"></i>
                                                <span
                                                    x-text="timelineOpen === '{{ $userAyuda->ayuda_id }}' ? 'Ocultar Timeline' : 'Ver Timeline'"></span>
                                                @if (($userAyuda->crm_history ?? collect())->count() > 0)
                                                    <span
                                                        class="absolute -top-1 -right-1 bg-yellow-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                                        {{ ($userAyuda->crm_history ?? collect())->count() }}
                                                    </span>
                                                @endif
                                            </button>
                                        </div>

                                        {{-- Línea temporal desplegable --}}
                                        <div x-show="timelineOpen === '{{ $userAyuda->ayuda_id }}'"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0 transform scale-95"
                                            x-transition:enter-end="opacity-100 transform scale-100"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100 transform scale-100"
                                            x-transition:leave-end="opacity-0 transform scale-95"
                                            class="mt-4 pt-4 border-t border-yellow-200"
                                            data-timeline-content="{{ $userAyuda->ayuda_id }}">
                                            @include('admin.components.crm-timeline', [
                                                'crmHistory' =>
                                                    $userAyuda->crm_history ?? collect(),
                                            ])
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Estado Frío --}}
                @if (count($estadosComerciales['frio']) > 0)
                    <div class="border-l-4 border-blue-500 bg-blue-50 rounded-r-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-semibold text-blue-800 flex items-center">
                                <i class="fas fa-snowflake text-blue-500 mr-2"></i>
                                Estado Frío
                            </h4>
                            <span
                                class="bg-blue-500 text-white text-sm font-bold px-3 py-1 rounded-full">
                                {{ count($estadosComerciales['frio']) }}
                            </span>
                        </div>
                        <div class="space-y-3">
                            @foreach ($estadosComerciales['frio'] as $userAyuda)
                                @if ($userAyuda->ayuda)
                                    <div class="bg-white border border-blue-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <span
                                                    class="font-medium text-blue-900">{{ $userAyuda->ayuda->nombre_ayuda }}</span>
                                                <div class="text-sm text-blue-700 mt-1">
                                                    <span class="font-medium">Fecha:</span>
                                                    {{ $userAyuda->fecha_solicitud ? \Carbon\Carbon::parse($userAyuda->fecha_solicitud)->format('d/m/Y') : 'Sin fecha' }}
                                                </div>
                                                @if ($userAyuda->tags)
                                                    <div class="text-sm text-blue-600 mt-1">
                                                        <span class="font-medium">Tags:</span>
                                                        {{ $userAyuda->tags }}
                                                    </div>
                                                @endif
                                            </div>
                                            <button
                                                @click="timelineOpen === '{{ $userAyuda->ayuda_id }}' ? timelineOpen = null : timelineOpen = '{{ $userAyuda->ayuda_id }}'"
                                                :class="timelineOpen === '{{ $userAyuda->ayuda_id }}' ?
                                                    'bg-blue-200 text-blue-900' :
                                                    'bg-blue-100 hover:bg-blue-200 text-blue-800'"
                                                class="ml-4 px-3 py-2 rounded-lg transition-colors flex items-center space-x-2 relative">
                                                <i :class="timelineOpen === '{{ $userAyuda->ayuda_id }}' ?
                                                    'fas fa-chevron-up' : 'fas fa-history'"
                                                    class="transition-transform duration-200"></i>
                                                <span
                                                    x-text="timelineOpen === '{{ $userAyuda->ayuda_id }}' ? 'Ocultar Timeline' : 'Ver Timeline'"></span>
                                                @if (($userAyuda->crm_history ?? collect())->count() > 0)
                                                    <span
                                                        class="absolute -top-1 -right-1 bg-blue-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                                        {{ ($userAyuda->crm_history ?? collect())->count() }}
                                                    </span>
                                                @endif
                                            </button>
                                        </div>

                                        {{-- Línea temporal desplegable --}}
                                        <div x-show="timelineOpen === '{{ $userAyuda->ayuda_id }}'"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0 transform scale-95"
                                            x-transition:enter-end="opacity-100 transform scale-100"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100 transform scale-100"
                                            x-transition:leave-end="opacity-0 transform scale-95"
                                            class="mt-4 pt-4 border-t border-blue-200"
                                            data-timeline-content="{{ $userAyuda->ayuda_id }}">
                                            @include('admin.components.crm-timeline', [
                                                'crmHistory' =>
                                                    $userAyuda->crm_history ?? collect(),
                                            ])
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Mensaje si no hay estados comerciales --}}
                @if (count($estadosComerciales['caliente']) == 0 &&
                        count($estadosComerciales['tibio']) == 0 &&
                        count($estadosComerciales['frio']) == 0)
                    <div class="text-center py-8">
                        <i class="fas fa-info-circle text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">No hay estados comerciales configurados para este
                            usuario.</p>
                        <p class="text-sm text-gray-400 mt-2">Los estados comerciales se configuran
                            automáticamente según la actividad del usuario.</p>
                    </div>
                @endif
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-info-circle text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500">No hay estados comerciales configurados para este usuario.
                </p>
                <p class="text-sm text-gray-400 mt-2">Los estados comerciales se configuran
                    automáticamente según la actividad del usuario.</p>
            </div>
        @endif
    </div>
</div>

{{-- Sección de Comunicaciones Operativas --}}
<div class="bg-white rounded-2xl shadow-md overflow-hidden card-hover mt-8">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-lg font-semibold flex items-center">
            <i class="fas fa-phone-alt text-[#54debd] mr-2"></i>
            Comunicaciones Operativas
        </h3>
        <button type="button" id="nuevaComunicacionBtn" onclick="toggleComunicacionForm()"
            class="px-4 py-2 bg-[#54debd] text-white rounded-md hover:bg-[#43c5a9] transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nueva Comunicación
        </button>
    </div>
    <div id="comunicacionStatusMessage"
        class="hidden mx-6 mt-4 p-3 rounded-md text-white text-center font-medium"></div>
    <div id="comunicacionForm" class="hidden px-6 py-4 border-b border-gray-100">
        <h4 class="text-lg font-semibold mb-4 text-gray-800">Nueva Comunicación</h4>
        <form id="addComunicacionForm"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de
                    Comunicación</label>
                <select name="tipo_comunicacion" required
                    class="w-full px-3 py-2 border rounded-lg focus:ring-[#54debd]/50 focus:border-[#54debd]">
                    <option value="">Seleccionar...</option>
                    <option value="WhatsApp">WhatsApp</option>
                    <option value="Email">Email</option>
                    <option value="Llamada">Llamada</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha y Hora</label>
                <input type="datetime-local" name="fecha_hora" required
                    value="{{ now()->format('Y-m-d\TH:i') }}"
                    class="w-full px-3 py-2 border rounded-lg focus:ring-[#54debd]/50 focus:border-[#54debd]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                <select name="direction" required
                    class="w-full px-3 py-2 border rounded-lg focus:ring-[#54debd]/50 focus:border-[#54debd]">
                    <option value="out">Saliente</option>
                    <option value="in">Entrante</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Asunto
                    (Opcional)</label>
                <input type="text" name="subject"
                    placeholder="Ej: Llamada de seguimiento, Consulta sobre ayuda..."
                    class="w-full px-3 py-2 border rounded-lg focus:ring-[#54debd]/50 focus:border-[#54debd]">
            </div>
            <div class="md:col-span-3 flex justify-end space-x-3">
                <button type="button" id="cancelarComunicacionBtn"
                    onclick="toggleComunicacionForm()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-[#54debd] text-white rounded-md hover:bg-[#43c5a9] transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Comunicación
                </button>
            </div>
        </form>
    </div>
    <div class="px-6 py-4">
        <div id="comunicacionesList" class="space-y-3 max-h-48 overflow-y-auto">
            @forelse($comunicacionesOperativas ?? [] as $comunicacion)
                <div class="border rounded-lg p-3 bg-gray-50"
                    data-comunicacion-id="{{ $comunicacion->id }}">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                @switch($comunicacion->tipo_comunicacion)
                                    @case('WhatsApp')
                                        <i class="fab fa-whatsapp text-green-500 text-lg"></i>
                                    @break

                                    @case('Email')
                                        <i class="fas fa-envelope text-blue-500 text-lg"></i>
                                    @break

                                    @case('Llamada')
                                        <i class="fas fa-phone text-purple-500 text-lg"></i>
                                    @break
                                @endswitch
                                <span
                                    class="font-medium text-gray-900">{{ $comunicacion->tipo_comunicacion }}</span>
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full {{ $comunicacion->direction === 'in' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $comunicacion->direction === 'in' ? 'Entrante' : 'Saliente' }}
                                </span>
                                @if ($comunicacion->auto)
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                        <i class="fas fa-robot mr-1"></i>
                                        Automática
                                    </span>
                                @endif
                            </div>
                            @if ($comunicacion->subject)
                                <p class="text-sm text-gray-700 mb-1">
                                    {{ $comunicacion->subject }}</p>
                            @endif
                            <div class="text-xs text-gray-500">
                                <span class="font-medium">Tramitador:</span>
                                {{ $comunicacion->tramitador->email ?? 'N/A' }}
                                <span class="mx-2">•</span>
                                <span class="font-medium">Fecha:</span>
                                {{ \Carbon\Carbon::parse($comunicacion->fecha_hora)->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        <button onclick="deleteComunicacion({{ $comunicacion->id }})"
                            class="text-red-600 hover:text-red-800 transition-colors"
                            title="Eliminar comunicación">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                @empty
                    <div class="text-center py-8 text-gray-500 empty-comms">
                        <i class="fas fa-phone-alt text-4xl text-gray-300 mb-4"></i>
                        <p>No hay comunicaciones operativas registradas.</p>
                        <p class="text-sm text-gray-400 mt-2">Haz clic en "Nueva Comunicación" para
                            añadir una.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Sección de Historial de Actividad --}}
    <div class="bg-white rounded-2xl shadow-md overflow-hidden card-hover mt-8">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold flex items-center">
                <i class="fas fa-history text-[#54debd] mr-2"></i>
                Historial de actividad
            </h3>
        </div>
        <div class="px-6 py-4 space-y-4">
            <ul class="space-y-3">
                @forelse($historialActividad as $actividad)
                    <li class="border rounded-lg p-3 bg-gray-50">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span><strong>{{ $actividad->actividad }}</strong></span>
                            <span>{{ \Carbon\Carbon::parse($actividad->fecha_inicio)->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="mt-1 text-gray-800">{{ $actividad->observaciones }}</p>
                    </li>
                @empty
                    <li class="text-gray-500">No hay historial de actividad aún.</li>
                @endforelse
            </ul>
        </div>
    </div>
    </div>

    <script>
        const statusDiv = document.getElementById('statusMessage');

        function showStatus(message, isError = false) {
            statusDiv.textContent = message;
            statusDiv.classList.remove('hidden', 'bg-green-500', 'bg-red-500');
            statusDiv.classList.add(isError ? 'bg-red-500' : 'bg-green-500');
            setTimeout(() => statusDiv.classList.add('hidden'), 3000);
        }
    </script>

    <script>
        // Funciones globales
        window.filterAyudas = function() {
            const txt = document.getElementById('searchAyudasInput').value.toLowerCase();

            document.querySelectorAll('.ayuda-row').forEach(row => {
                const text = row.textContent.toLowerCase();
                const matchesText = !txt || text.includes(txt);

                row.style.display = matchesText ? '' : 'none';

                // Ocultar también la fila de detalle si la fila principal está oculta
                const detailRow = document.getElementById(row.id + '-row');
                if (detailRow) {
                    detailRow.style.display = row.style.display;
                }
            });
        };

        window.toggleTimeline = function(ayudaId) {
            const alpineElement = document.querySelector('[x-data*="timelineOpen"]');
            if (alpineElement && alpineElement.__x) {
                const alpine = alpineElement.__x;
                if (alpine.timelineOpen === ayudaId) {
                    alpine.timelineOpen = null;
                } else {
                    alpine.timelineOpen = ayudaId;
                }
            } else {
                const currentOpen = document.querySelector('[data-timeline-open]')?.getAttribute(
                    'data-timeline-open');

                document.querySelectorAll('[data-timeline-content]').forEach(el => {
                    el.style.display = 'none';
                });

                if (currentOpen === ayudaId) {
                    document.querySelector('[data-timeline-open]')?.removeAttribute(
                        'data-timeline-open');
                } else {
                    document.querySelector('[data-timeline-open]')?.setAttribute(
                        'data-timeline-open', ayudaId);
                    const targetTimeline = document.querySelector(
                        `[data-timeline-content="${ayudaId}"]`);
                    if (targetTimeline) {
                        targetTimeline.style.display = 'block';
                    }
                }
            }
        };

        if (typeof window !== 'undefined') {
            window.toggleTimeline = window.toggleTimeline || function(ayudaId) {
                console.log('toggleTimeline llamado con:', ayudaId);
                const alpineElement = document.querySelector('[x-data*="timelineOpen"]');
                if (alpineElement && alpineElement.__x) {
                    const alpine = alpineElement.__x;
                    if (alpine.timelineOpen === ayudaId) {
                        alpine.timelineOpen = null;
                    } else {
                        alpine.timelineOpen = ayudaId;
                    }
                }
            };
        }

        window.toggleSolicitud = function(id) {
            event.stopPropagation();
            const detail = document.getElementById(id + '-row');
            const chevron = document.getElementById('chevron' + id.replace(/\D/g, ''));
            detail.classList.toggle('hidden');
            chevron?.classList.toggle('rotate-90');
            document.querySelectorAll('.ayuda-row').forEach(r => {
                if (r.id !== id) {
                    document.getElementById(r.id + '-row')?.classList.add('hidden');
                    document.getElementById('chevron' + r.id.replace(/\D/g, ''))?.classList
                        .remove('rotate-90');
                }
            });
        };

        window.deleteSolicitud = function(solicitudId, nombreSolicitud) {
            if (!confirm(
                    `¿Estás seguro de que quieres eliminar la solicitud "${nombreSolicitud}"? Esta acción no se puede deshacer.`
                )) {
                return;
            }

            fetch(`/admin/users/{{ $user->id }}/solicitudes/${solicitudId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .content
                    }
                })
                .then(res => res.json())
                .then(json => {
                    if (json.success) {
                        // Eliminar las filas de la tabla
                        const row = document.getElementById(`solicitud${solicitudId}`);
                        const detailRow = document.getElementById(
                            `solicitud${solicitudId}-row`);
                        if (row) row.remove();
                        if (detailRow) detailRow.remove();

                        showStatus('Solicitud eliminada correctamente', false);
                    } else {
                        showStatus(json.message || 'Error al eliminar la solicitud', true);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showStatus('Error al eliminar la solicitud', true);
                });
        };
    </script>

    <script>
        function registrarComunicacionOperativa(tipo) {
            fetch("{{ route('admin.users.comunicacion_operativa', $user->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                    },
                    body: JSON.stringify({
                        tipo_comunicacion: tipo
                    })
                })
                .then(res => res.json())
                .then(json => {
                    if (json.success) {
                        showStatus('Comunicación registrada correctamente', false);
                    } else {
                        showStatus('No se pudo registrar la comunicación', true);
                    }
                })
                .catch(err => {
                    showStatus('Error al registrar comunicación', true);
                });
        }
    </script>

    @if (!$isPartial)
        </body>

        </html>
    @endif
