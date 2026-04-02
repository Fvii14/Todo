@php
    use App\Models\Question;
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    @if (app()->environment('production'))
        <script>
            (function(h, o, t, j, a, r) {
                h.hj = h.hj || function() {
                    (h.hj.q = h.hj.q || []).push(arguments)
                };
                h._hjSettings = {
                    hjid: 6454479,
                    hjsv: 6
                };
                a = o.getElementsByTagName('head')[0];
                r = o.createElement('script');
                r.async = 1;
                r.src = t + h._hjSettings.hjid + j + h._hjSettings.hjsv;
                a.appendChild(r);
            })(window, document, 'https://static.hotjar.com/c/hotjar-', '.js?sv=');
        </script>
        <x-clarity-analytics />
    @endif
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if (!$isCollectorQuestionnaire)
        <title>{{ $ayuda->nombre_ayuda }} - Cuestionario de Ayuda</title>
    @else
        <title>Cuestionario inicial - Tu Trámite Fácil</title>
    @endif
    <!-- Primero carga Tailwind -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@latest/dist/tailwind.min.css"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js">
    </script>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Ubuntu', sans-serif;
            list-style: none;
        }

        main {
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .progress-bar {
            background-color: #34CA91;
            height: 12px;
            transition: width 0.3s ease;
            /* Animación suave */
            border-radius: 6px;
            clip-path: inset(0 round 6px);
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            border-radius: 50%;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.4s;
        }

        input:checked+.slider {
            background-color: #4CAF50;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        /* Estilos para las etiquetas de "Sí" y "No" */
        .toggle-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 5px;
        }

        #next-button {
            background-color: #34CA91;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #next-button:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(89, 237, 202, 0.6);
        }

        #next-button:hover {
            background-color: #2DBA82;
        }

        #prev-button:hover {
            background-color: #f8ec45;
            color: rgb(0, 0, 0);
        }

        #prev-button {
            background-color: #fdf68f;
            color: #000;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 0 0 2px rgba(245, 222, 10, 0.6);
        }

        #submit-button {
            background-color: #34CA91;
            color: #3c3a60;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #background-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            min-height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .wheel-picker-container {
            position: relative;
            width: 100%;
            height: 200px;
            background: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e9ecef;
        }

        .wheel-picker-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom,
                    rgba(248, 249, 250, 0.9) 0%,
                    transparent 20%,
                    transparent 80%,
                    rgba(248, 249, 250, 0.9) 100%);
            pointer-events: none;
            z-index: 2;
        }

        .wheel-picker {
            position: relative;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wheel-picker-selection {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            height: 40px;
            background: rgba(59, 130, 246, 0.1);
            border: 2px solid rgba(59, 130, 246, 0.3);
            border-radius: 8px;
            z-index: 1;
            pointer-events: none;
        }

        .wheel-picker-wheel {
            position: relative;
            height: 100%;
            overflow-y: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            scroll-behavior: smooth;
        }

        .wheel-picker-wheel::-webkit-scrollbar {
            display: none;
        }

        .wheel-picker-item {
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 500;
            color: #6b7280;
            transition: all 0.2s ease;
            padding: 0 20px;
            text-align: center;
        }

        .wheel-picker-item.selected {
            color: #1f2937;
            font-weight: 600;
            transform: scale(1.05);
        }

        .wheel-picker-item:hover {
            background: rgba(59, 130, 246, 0.05);
        }

        .wheel-picker-spacer {
            height: 100px;
            width: 100%;
        }
    </style>
    @php
        use Illuminate\Support\Str;
    @endphp

</head>

<body class="bg-gray-50 h-auto flex flex-col relative">
    <x-gtm-noscript />
    <canvas id="background-canvas"
        class="absolute top-0 left-0 w-full min-h-screen pointer-events-none"></canvas>
    @if (!$isCollectorQuestionnaire)
        <x-header />
    @endif
    <!-- 1. Spinner oculto -->
    <div id="spinner-overlay"
        class="fixed inset-0 bg-white bg-opacity-80 backdrop-blur-sm flex items-center justify-center z-50 hidden"
        role="alert" aria-busy="true" aria-label="Procesando…">
        <div class="flex flex-col text-center items-center gap-3 md:gap-6 bg-white p-12 md:p-20 rounded-xl md:rounded-2xl shadow-lg"
            style="max-width: 600px; margin: auto;">
            <img src="{{ asset('imagenes/833.gif') }}" alt="Cargando..."
                class="h-20 w-20 md:h-32 md:w-32 animate-pulse" />

            @if ($isCollectorQuestionnaire)
                <h2 class="text-xl md:text-3xl font-bold text-gray-800">
                    Estamos revisando tus respuestas...
                </h2>

                <p class="text-sm md:text-lg font-semibold text-gray-600">
                    🧠 Nuestro sistema legal analiza cada detalle.
                </p>

                <p class="text-sm md:text-lg font-semibold text-gray-600">
                    ⚖️ Un especialista está evaluando tu perfil.
                </p>

                <p class="text-sm md:text-lg font-semibold text-gray-600">
                    ✨ En breves instantes, sabrás qué ayudas podrías solicitar.
                </p>
            @else
                <h2 class="text-xl md:text-3xl font-bold text-gray-800">
                    Estamos validando tu caso...
                </h2>

                <p class="text-sm md:text-lg font-semibold text-gray-600">
                    👨‍⚖️ Un abogado especializado está revisando si cumples los requisitos.
                </p>

                <p class="text-sm md:text-lg font-semibold text-gray-600">
                    🛡️ Analizamos todos los criterios legales por ti.
                </p>

                <p class="text-sm md:text-lg font-semibold text-gray-600">
                    🕒 En unos segundos, sabrás si puedes acceder a esta ayuda.
                </p>
            @endif
        </div>
    </div>

    <main class="flex-grow items-center justify-center relative">
        <x-simulation-banner />
        <div class="min-h-screen py-10 flex items-start justify-center overflow-auto">
            <!-- Contenedor principal centrado en la pantalla -->
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl z-9">
                @if ($isCollectorQuestionnaire)
                    <div class="px-5">
                        <div class="flex flex-row mb-4 gap-4">
                            <!-- Se aplica la clase -ml-2 para compensar el margen del logo actual.
                            Si se cambia, puede ya no sea necesario. -->
                            <img src="/imagenes/cropped-ttflogo_back-192x192-4.png" alt=""
                                class="w-16 -ml-2">
                            <!-- Barra de progreso -->
                            <div class="flex items-center max-w-2xl w-full">
                                <!-- Barra gris de fondo -->
                                <div class="relative bg-gray-200 rounded-full h-3 flex-grow">
                                    <!-- Barra que se va llenando -->
                                    <div class="progress-bar bg-teal-400 h-3 rounded-l-full transition-all duration-300"
                                        role="progressbar" style="width: 0%;" aria-valuenow="0"
                                        aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <!-- Texto de porcentaje fuera y a la derecha -->
                                <span id="progress-text"
                                    class="ml-3 text-sm font-medium text-black whitespace-nowrap">
                                    0%
                                </span>
                            </div>
                        </div>

                        <h1 class="text-3xl font-bold text-gray-800 text-left mb-5">
                            Estamos creando tu perfil de ayudas
                        </h1>

                        <p class="text-xl mb-5">
                            Te haremos algunas preguntas para personalizar al máximo las ayudas que
                            puedes soliciar. No
                            te llevará más de 3 minutos.
                        </p>
                    @else
                        <div class="flex flex-row items-center justify-between mb-4">
                            <div class="flex flex-row items-center gap-2 sm:gap-4">
                                <div
                                    class="border border-amber-200 rounded-full bg-white shadow aspect-square flex items-center justify-center">
                                    <img src="{{ asset('imagenes/organos/' . $ayuda->organo->imagen) }}"
                                        alt="{{ $ayuda->organo->nombre_organismo }}"
                                        class=" w-auto h-auto object-contain rounded-full p-1 sm:p-2"
                                        style="max-width: 5rem;" />
                                </div>
                                <div>
                                    <h1
                                        class="sm:text-xl md:text-3xl font-bold text-gray-800 text-left m-1">
                                        {{ $ayuda->nombre_ayuda != null ? $ayuda->nombre_ayuda : $ayuda->name }}
                                    </h1>
                                </div>
                            </div>
                            <div>
                                <p class="text-lg sm:text-xl md:text-3xl font-bold">
                                    {{ $ayuda->cuantia_usuario == 0 ? 'Ilimitado' : $ayuda->getDineroFormateado($ayuda->cuantia_usuario, 0) }}<span
                                        class="text-xl">/año</span>
                                </p>
                            </div>
                        </div>

                        <!-- Barra de progreso -->
                        <div class="mb-6 flex items-center max-w-2xl w-full">
                            <!-- Barra gris de fondo -->
                            <div class="relative bg-gray-200 rounded-full h-3 flex-grow">
                                <!-- Barra que se va llenando -->
                                <div class="progress-bar bg-teal-400 h-3 rounded-l-full transition-all duration-300"
                                    role="progressbar" style="width: 0%;" aria-valuenow="0"
                                    aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <!-- Texto de porcentaje fuera y a la derecha -->
                            <span id="progress-text"
                                class="ml-3 text-sm font-medium text-black whitespace-nowrap">
                                0%
                            </span>
                        </div>
                @endif

                <!-- Formulario -->
                <form action="{{ route('checkAnswers') }}" id="questionnaire-form" method="POST"
                    enctype="multipart/form-data"
                    onsubmit="this.querySelector('button').disabled = true;">

                    <div class="{{ $isCollectorQuestionnaire ? 'border-2 p-5 rounded-lg' : '' }}">

                        @csrf
                        <input type="hidden" name="questionnaire_id"
                            value="{{ $questionnaire->id }}">

                        <!--<p class="text-sm text-gray-500 mb-4">Puedes modificar cualquier dato haciendo click en el mismo</p>-->
                        <div id="question-container" class="space-y-5">
                            @foreach ($questions as $index => $question)

                                @php
                                    $answer = $answers[$question['id']] ?? null;
                                    $bloqueada =
                                        isset($question['disable_answer']) &&
                                        $question['disable_answer'] == 1 &&
                                        !is_null($answer) &&
                                        $answer !== '';
                                @endphp

                                <div class="question-item" id="question-{{ $index }}"
                                    data-id="{{ $question['id'] }}"
                                    data-pattern="{{ $question['validation']['pattern'] ?? '' }}"
                                    data-error-message="{{ $question['validation']['error_message'] ?? '' }}"
                                    style="display: none;">
                                    <div class="bg-white  rounded-lg shadow-sm mb-3">
                                        <label
                                            class="block {{ $isCollectorQuestionnaire ? 'text-2xl' : 'text-lg' }} font-semibold text-gray-700 mb-3">{{ $question['text'] }}
                                        </label>

                                        @if ($question['type'] == 'boolean')
                                            @php
                                                $respuesta = old(
                                                    'answers.' . $question['id'],
                                                    $question['answer'],
                                                );
                                            @endphp

                                            <input type="hidden"
                                                name="answers[{{ $question['id'] }}]"
                                                value="0">
                                            <div class="flex items-center mb-4">
                                                <span class="text-gray-700">No</span>
                                                <label class="switch ml-2">
                                                    <input type="checkbox"
                                                        name="answers[{{ $question['id'] }}]"
                                                        value="1" class="hidden peer"
                                                        @if ($respuesta == 1) checked @endif
                                                        {{ $bloqueada ? 'disabled' : '' }}>
                                                    <span
                                                        class="slider round peer-checked:bg-indigo-600"></span>
                                                </label>
                                                <span class="text-gray-700 ml-2">Sí</span>
                                            </div>
                                            <!-- Aquí se imprimen el subtext de la pregunta -->
                                            @if ($question['subtext_with_link'])
                                                <p class="text-sm text-gray-500 mb-2">
                                                    {!! $question['subtext_with_link'] !!}</p>
                                            @endif

                                            @if ($bloqueada)
                                                <p
                                                    class="text-sm text-indigo-600 font-medium mt-1">
                                                    🔒 Esta información ya ha sido verificada y no
                                                    se puede modificar.
                                                </p>
                                            @endif
                                        @elseif ($question['type'] == 'integer')
                                            @if ($bloqueada)
                                                <input type="number" disabled
                                                    class="w-full placeholder:text-slate-400 text-sm p-2 rounded border transition duration-300 ease focus:outline-none bg-gray-100 border-slate-200 shadow-sm focus:shadow"
                                                    value="{{ old('answers.' . $question['id'], $question['answer']) }}">
                                                <input type="hidden"
                                                    name="answers[{{ $question['id'] }}]"
                                                    value="{{ old('answers.' . $question['id'], $question['answer']) }}">
                                            @else
                                                @if (!empty($question['integer_with_range']))
                                                    @php
                                                        $value = old(
                                                            'answers.' . $question['id'],
                                                            $question['answer'] ?? 1,
                                                        );
                                                    @endphp
                                                    <div class="wheel-picker-container">
                                                        <div class="wheel-picker-overlay"></div>
                                                        <div class="wheel-picker">
                                                            <div class="wheel-picker-selection">
                                                            </div>
                                                            @if ($question['id'] == Question::where('slug', 'pago-mensual-alquiler-generico')->value('id'))
                                                                <div class="wheel-picker-wheel"
                                                                    id="wheel-{{ $question['id'] }}">
                                                                    <div
                                                                        class="wheel-picker-spacer">
                                                                    </div>
                                                                    @for ($i = 0; $i <= 1500; $i += 25)
                                                                        <div class="wheel-picker-item"
                                                                            data-value="{{ $i }}">
                                                                            {{ number_format($i, 0, ',', '.') }}
                                                                        </div>
                                                                    @endfor
                                                                    <div
                                                                        class="wheel-picker-spacer">
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="wheel-picker-wheel"
                                                                    id="wheel-{{ $question['id'] }}">
                                                                    <div
                                                                        class="wheel-picker-spacer">
                                                                    </div>
                                                                    @for ($i = 0; $i <= 20000; $i += 1000)
                                                                        <div class="wheel-picker-item"
                                                                            data-value="{{ $i }}">
                                                                            {{ number_format($i, 0, ',', '.') }}
                                                                        </div>
                                                                    @endfor
                                                                    @for ($i = 21000; $i <= 50000; $i += 100)
                                                                        <div class="wheel-picker-item"
                                                                            data-value="{{ $i }}">
                                                                            {{ number_format($i, 0, ',', '.') }}
                                                                        </div>
                                                                    @endfor
                                                                    <div
                                                                        class="wheel-picker-spacer">
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <input type="hidden"
                                                            name="answers[{{ $question['id'] }}]"
                                                            value="{{ $value }}"
                                                            id="wheel-input-{{ $question['id'] }}">
                                                    </div>
                                                    <script>
                                                        document.addEventListener('DOMContentLoaded', function() {
                                                            initWheelPicker({{ $question['id'] }}, {{ $value }});

                                                        });
                                                    </script>
                                                @elseif ($question['id'] == Question::where('slug', 'telefono')->value('id'))
                                                    <div
                                                        class="phone-country-selector flex items-center space-x-2">
                                                        <select
                                                            class="country-select w-1/3 text-sm p-2 rounded border transition duration-300 ease focus:outline-none bg-gray-100 border-slate-200 shadow-sm focus:shadow">
                                                            <option value="">Seleccionar país
                                                            </option>
                                                        </select>
                                                        <input type="tel" id="phone-input"
                                                            class="phone-input w-2/3 placeholder:text-slate-400 text-sm p-2 rounded border transition duration-300 ease focus:outline-none bg-gray-100 border-slate-200 shadow-sm focus:shadow"
                                                            placeholder="Número de teléfono"
                                                            @if ($bloqueada) disabled @endif>
                                                        <input type="hidden"
                                                            name="answers[{{ $question['id'] }}]"
                                                            class="phone-hidden-input"
                                                            value="{{ old('answers.' . $question['id'], $question['answer']) }}">
                                                    </div>
                                                @else
                                                    <input type="number"
                                                        name="answers[{{ $question['id'] }}]"
                                                        value="{{ old('answers.' . $question['id'], $question['answer']) }}"
                                                        class="w-full placeholder:text-slate-400 text-sm p-2 rounded border transition duration-300 ease focus:outline-none bg-gray-100 border-slate-200 shadow-sm focus:shadow">
                                                @endif
                                            @endif

                                            @if ($bloqueada)
                                                <p
                                                    class="text-sm text-indigo-600 font-medium mt-1">
                                                    🔒 Esta información ya ha sido verificada y no
                                                    se puede modificar.
                                                </p>
                                            @endif
                                        @elseif ($question['type'] == 'string')
                                            <input type="text"
                                                name="answers[{{ $question['id'] }}]"
                                                value="{{ old('answers.' . $question['id'], $question['answer']) }}"
                                                class="form-input mt-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-gray-100 p-2"
                                                @if ($bloqueada) disabled @endif>

                                            @if ($bloqueada)
                                                <p
                                                    class="text-sm text-indigo-600 font-medium mt-1">
                                                    🔒 Esta información ya ha sido verificada y no
                                                    se puede modificar.
                                                </p>
                                            @endif
                                        @elseif ($question['type'] == 'date')
                                            <input type="date" max="{{ date('Y-m-d') }}"
                                                name="answers[{{ $question['id'] }}]"
                                                value="{{ old('answers.' . $question['id'], $question['answer']) }}"
                                                class="form-input mt-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-gray-100 p-2 {{ $bloqueada ? 'text-gray-500 cursor-not-allowed' : '' }}"
                                                @if ($bloqueada) disabled @endif>

                                            @if ($bloqueada)
                                                <p
                                                    class="text-sm text-indigo-600 font-medium mt-1">
                                                    🔒 Esta información ya ha sido verificada y no
                                                    se puede modificar.
                                                </p>
                                            @endif
                                        @elseif ($question['type'] == 'select')
                                            @if (isset($question['options']) && is_array($question['options']))
                                                <div class="flex flex-wrap"
                                                    data-question-type="select">
                                                    @php
                                                        $storedValue = old(
                                                            'answers.' . $question['id'],
                                                            array_search(
                                                                $answers[$question['id']] ?? '',
                                                                $question['options'],
                                                            ),
                                                        );
                                                    @endphp

                                                    @if ($question['id'] === 36)
                                                        <select
                                                            name="answers[{{ $question['id'] }}]"
                                                            id="provincia_select"
                                                            data-question-id="{{ $question['id'] }}"
                                                            class="w-full text-sm sm:text-base p-2 rounded border border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $bloqueada ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '' }}"
                                                            @if ($bloqueada) disabled @endif>
                                                            <option value="">Selecciona una
                                                                provincia</option>
                                                            @foreach ($question['options'] as $key => $option)
                                                                @php
                                                                    $value =
                                                                        is_string($key) ||
                                                                        is_numeric($key)
                                                                            ? $key
                                                                            : $option;
                                                                @endphp
                                                                <option
                                                                    value="{{ $value }}"
                                                                    @if ((string) $storedValue === (string) $value) selected @endif>
                                                                    {{ $option }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @if ($bloqueada)
                                                            <p
                                                                class="text-sm text-indigo-600 font-medium mt-1">
                                                                🔒 Esta información ya ha sido
                                                                verificada y no se puede
                                                                modificar.
                                                            </p>
                                                        @endif
                                                    @elseif ($question['id'] === 37)
                                                        <select
                                                            name="answers[{{ $question['id'] }}]"
                                                            id="municipio_select"
                                                            data-question-id="{{ $question['id'] }}"
                                                            data-selected="{{ old('answers.' . $question['id'], $question['answer']) }}"
                                                            class="w-full text-sm sm:text-base p-2 rounded border border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $bloqueada ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '' }}"
                                                            @if ($bloqueada) disabled @endif>
                                                            <option value="">Selecciona
                                                                primero una provincia
                                                            </option>
                                                        </select>

                                                        @if ($bloqueada)
                                                            <p
                                                                class="text-sm text-indigo-600 font-medium mt-1">
                                                                🔒 Esta información ya ha sido
                                                                verificada y no se puede
                                                                modificar.
                                                            </p>
                                                        @endif
                                                    @elseif ($question['id'] === 38)
                                                        <select
                                                            name="answers[{{ $question['id'] }}]"
                                                            id="ccaa_select"
                                                            data-question-id="{{ $question['id'] }}"
                                                            data-selected="{{ old('answers.' . $question['id'], $question['answer']) }}"
                                                            class="w-full text-sm sm:text-base p-2 rounded border border-gray-300 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                            <option value="">Selecciona una
                                                                Comunidad Autónoma
                                                            </option>
                                                            <option value="1">Andalucía
                                                            </option>
                                                            <option value="11">Aragón</option>
                                                            <option value="15">Principado de
                                                                Asturias</option>
                                                            <option value="13">Illes Balears
                                                            </option>
                                                            <option value="9">Canarias
                                                            </option>
                                                            <option value="14">Cantabria
                                                            </option>
                                                            <option value="5">Castilla y León
                                                            </option>
                                                            <option value="7">Castilla-La
                                                                Mancha</option>
                                                            <option value="2">Cataluña
                                                            </option>
                                                            <option value="4">Comunitat
                                                                Valenciana</option>
                                                            <option value="10">Extremadura
                                                            </option>
                                                            <option value="6">Galicia</option>
                                                            <option value="3">Comunidad de
                                                                Madrid</option>
                                                            <option value="12">Región de Murcia
                                                            </option>
                                                            <option value="16">Comunidad Foral
                                                                de Navarra</option>
                                                            <option value="8">País Vasco
                                                            </option>
                                                            <option value="17">La Rioja
                                                            </option>
                                                            <option value="18">Ciudad Autónoma
                                                                de Ceuta</option>
                                                            <option value="19">Ciudad Autónoma
                                                                de Melilla</option>
                                                        </select>
                                                    @else
                                                        <input type="hidden"
                                                            name="answers[{{ $question['id'] }}]"
                                                            id="selectedOption_{{ $question['id'] }}"
                                                            value="{{ $storedValue }}">
                                                        @foreach ($question['options'] as $key => $option)
                                                            <button type="button" id="option"
                                                                class="hover:bg-gray-200 text-gray-700 border border-gray-300 px-4 py-2 mb-2 rounded w-full transition duration-300
                                                             @if (!empty($answers) && isset($answers[$question['id']]) && $answers[$question['id']] == $option) selected bg-gray-300 @endif
                                                            {{ $bloqueada ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '' }}"
                                                                data-value="{{ $key }}"
                                                                @if (!$bloqueada) onclick="selectOption(this, {{ $question['id'] }})" @endif
                                                                @if ($bloqueada) disabled @endif>
                                                                {{ $option }}
                                                            </button>
                                                        @endforeach
                                                        @if ($bloqueada)
                                                            <p
                                                                class="text-sm text-indigo-600 font-medium mt-1">
                                                                🔒 Esta información ya ha sido
                                                                verificada y no se puede
                                                                modificar.
                                                            </p>
                                                        @endif
                                                    @endif
                                                </div>
                                            @else
                                                <p>No options available for this question.</p>
                                            @endif
                                        @elseif ($question['type'] == 'multiple')
                                            @if (isset($question['options']) && is_array($question['options']))
                                                <div class="flex flex-wrap"
                                                    data-question-type="multiple">
                                                    @php
                                                        if (isset($answers[$question['id']])) {
                                                            $answersDecoded =
                                                                json_decode(
                                                                    $answers[$question['id']],
                                                                ) ?? [];
                                                            $answersDecodedString = implode(
                                                                ',',
                                                                $answersDecoded,
                                                            );
                                                        } else {
                                                            $answersDecodedString = '';
                                                        }

                                                        $selectedOptions = [];

                                                        if (isset($answers[$question['id']])) {
                                                            $selected = $answers[$question['id']];
                                                            if (
                                                                is_string($selected) &&
                                                                Str::startsWith($selected, '[')
                                                            ) {
                                                                $selectedOptions = json_decode(
                                                                    $selected,
                                                                    true,
                                                                );
                                                            } elseif (is_array($selected)) {
                                                                $selectedOptions = $selected;
                                                            }
                                                        }
                                                    @endphp

                                                    <!-- Campo oculto para almacenar las respuestas seleccionadas -->
                                                    <input type="hidden"
                                                        name="answers[{{ $question['id'] }}][]"
                                                        id="selectedOptions_{{ $question['id'] }}"
                                                        value="{{ $answersDecodedString }}">

                                                    @foreach ($question['options'] as $key => $option)
                                                        <button type="button"
                                                            id="option-multiple"
                                                            class="hover:bg-gray-200 text-gray-700 border border-gray-300 px-4 py-2 mb-2 rounded w-full transition duration-300
                                                        @if (in_array($option, $selectedOptions)) selected bg-gray-300 @endif
                                                        {{ $bloqueada ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '' }}"
                                                            data-value="{{ $key }}"
                                                            data-question-id="{{ $question['id'] }}"
                                                            @if (!$bloqueada) onclick="selectMultipleOptions(this, {{ $question['id'] }})" @endif
                                                            @if ($bloqueada) disabled @endif>
                                                            {{ $option }}
                                                        </button>
                                                    @endforeach

                                                    @if (!isset($question['exclude_none_option']) || $question['exclude_none_option'] !== true)
                                                        <button type="button"
                                                            id="option-multiple"
                                                            class="hover:bg-gray-200 text-gray-700 border border-gray-300 px-4 py-2 mb-2 rounded w-full transition duration-300
                                                        @if (in_array('Ninguna de las anteriores', $selectedOptions)) selected bg-gray-300 @endif
                                                        {{ $bloqueada ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '' }}"
                                                            data-value="-1"
                                                            data-question-id="{{ $question['id'] }}"
                                                            @if (!$bloqueada) onclick="selectMultipleOptions(this, {{ $question['id'] }})" @endif
                                                            @if ($bloqueada) disabled @endif>
                                                            Ninguna de las anteriores
                                                        </button>
                                                    @endif

                                                    @if ($bloqueada)
                                                        <p
                                                            class="text-sm text-indigo-600 font-medium mt-1">
                                                            🔒 Esta información ya ha sido
                                                            verificada y no se puede
                                                            modificar.
                                                        </p>
                                                    @endif
                                                </div>
                                            @else
                                                <p>No options available for this question.</p>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                        </div>
                        <!-- Mensaje de error-->
                        <div id="error" style="display:none; color:red;">
                            <p id="error-message"></p>
                        </div>
                        <!-- Mensaje final -->
                        <div id="final-message"
                            class="hidden mt-4 text-center text-gray-700 bg-green-50 border border-green-300 p-4 rounded">
                            ✅ ¡Has llegado al final del formulario! <br>Pulsa en "Enviar".
                        </div>
                    </div>
                    <div id="navigation-buttons" class="flex justify-between mt-6">
                        <!-- Botón Anterior envuelto -->
                        <div class="w-[120px]">
                            <button type="button" id="prev-button"
                                class="btn btn-primary w-full px-6 py-2 bg-blue-500 text-white rounded "
                                style="visibility: hidden;">Anterior</button>
                        </div>

                        <!-- Botón Siguiente -->
                        <button type="button" id="next-button"
                            class="btn btn-secondary px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Siguiente</button>

                        <!-- Botón Enviar -->
                        <button type="submit" id="submit-button"
                            class="btn btn-success px-6 py-2 text-white rounded hover:bg-green-600 "
                            style="display: none;">Enviar</button>
                    </div>

                </form>
            </div>
        </div>
    </main>
    @if (!$isCollectorQuestionnaire)
        <x-footer />
    @endif

    <script>
        let timeStart = null; // Momento en que se muestra una pregunta

        function detectQuestionnaireVersion() {
            const conditions = @json($conditions ?? []);

            if (conditions.length > 0) {
                const firstCondition = conditions[0];
                if (firstCondition.condition !== null && firstCondition.condition !== undefined) {
                    return 'OLD';
                }
                if (firstCondition.condition === null || firstCondition.condition === undefined) {
                    return 'NEW';
                }
            }
        }
        const questionnaireVersion = detectQuestionnaireVersion();
        const questionnaireId = Number(document.querySelector('input[name="questionnaire_id"]')
            ?.value || 0);

        function formatDate(date) {
            const d = new Date(date);
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const hours = String(d.getHours()).padStart(2, '0');
            const minutes = String(d.getMinutes()).padStart(2, '0');
            const seconds = String(d.getSeconds()).padStart(2, '0');
            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        }

        const uuid = crypto.randomUUID(); // UUID para la sesión del cuestionario

        document.addEventListener("DOMContentLoaded", function() {
            if (!window.currentValuesMap) window.currentValuesMap = {};
            const multipleWrappers = document.querySelectorAll(
                '[data-question-type="multiple"]');

            multipleWrappers.forEach(wrapper => {
                const input = wrapper.querySelector('input[type="hidden"]');
                const questionId = input.id.replace('selectedOptions_', '');
                const existingValues = input.value.split(',').filter(v => v);

                // Obtenemos los textos de las opciones (botones)
                const optionButtons = Array.from(wrapper.querySelectorAll(
                    'button[data-value]'));
                const optionsText = optionButtons.map(btn => btn.textContent.trim());

                // Buscamos los índices de las respuestas existentes
                const valueIndices = existingValues.map(val => optionsText.indexOf(val))
                    .filter(index =>
                        index !== -1);

                // Guardamos en el array global los índices (no los textos)
                window.currentValuesMap[questionId] = valueIndices;



                // Marcamos visualmente los botones por índice
                valueIndices.forEach(index => {
                    const button = optionButtons[index];
                    if (button) {
                        button.classList.add('selected', 'bg-gray-300');
                    }
                });

                // También actualizamos el input oculto para reflejar los índices
                input.value = valueIndices.join(',');

                window.currentValuesMap[questionId] = valueIndices;

                valueIndices.forEach(indexValue => {
                    const button = optionButtons.find(btn => btn.getAttribute(
                            'data-value') ===
                        indexValue);
                    if (button) {
                        button.classList.add('selected', 'bg-gray-300');
                    }
                });
                input.value = valueIndices.join(',');


                if (questions.length > 0) {
                    let firstVisibleIdx = 0;
                    while (firstVisibleIdx < questions.length &&
                        questions[firstVisibleIdx] &&
                        hiddenQuestions.includes(Number(questions[firstVisibleIdx]
                            .getAttribute('data-id')))
                    ) {
                        firstVisibleIdx++;
                    }
                }
            });


            const provinciaSelect = document.getElementById('provincia_select');
            const municipioSelect = document.getElementById('municipio_select');

            const selectedMunicipioName = municipioSelect?.dataset.selected;

            if (provinciaSelect && municipioSelect) {
                const autoProvincia = provinciaSelect.value;

                if (autoProvincia) {
                    // 🟢 Si ya hay una provincia seleccionada, hacemos la carga inicial de municipios
                    municipioSelect.innerHTML = '<option>Cargando municipios...</option>';
                    municipioSelect.disabled = true;

                    fetch(`/municipios/${autoProvincia}`)
                        .then(res => res.json())
                        .then(data => {
                            municipioSelect.innerHTML =
                                '<option value="">Selecciona un municipio</option>';
                            data.forEach(m => {
                                const opt = document.createElement('option');
                                opt.value = m.id;
                                opt.textContent = m.nombre_municipio;

                                if (selectedMunicipioName &&
                                    selectedMunicipioName === m
                                    .nombre_municipio) {
                                    opt.selected = true;
                                }

                                municipioSelect.appendChild(opt);
                            });
                            municipioSelect.disabled = false;
                        });
                }

                // 🟡 Evento cuando cambia la provincia manualmente
                provinciaSelect.addEventListener('change', function() {
                    const provinciaId = this.value;

                    municipioSelect.innerHTML =
                        '<option>Cargando municipios...</option>';
                    municipioSelect.disabled = true;

                    fetch(`/municipios/${provinciaId}`)
                        .then(res => res.json())
                        .then(data => {
                            municipioSelect.innerHTML =
                                '<option value="">Selecciona un municipio</option>';
                            data.forEach(m => {
                                const opt = document.createElement(
                                    'option');
                                opt.value = m.id;
                                opt.textContent = m.nombre_municipio;
                                municipioSelect.appendChild(opt);
                            });
                            municipioSelect.disabled = false;
                        });
                });
            }


        });


        let currentQuestionIndex = 0;
        const questions = document.querySelectorAll('.question-item');
        const nextButton = document.getElementById('next-button');
        const progressBar = document.querySelector('.progress-bar');
        const prevButton = document.getElementById('prev-button');
        const submitButton = document.getElementById('submit-button');
        const errorContainer = document.getElementById('error');
        const errorMessage = document.getElementById('error-message');
        const hiddenQuestions = []; // Array para gestionar las preguntas ocultas
        const conditions = @json($conditions); // Condiciones pasadas desde el backend
        let currentStep = 0; // 🔹 Estado global del paso actual
        const steps = document.querySelectorAll('.step');
        const totalSteps = steps.length;
        let confettiFired = false;
        const navigationHistory = [];
        const progressHistory = [];
        const answeredQuestions = [];

        function saveAnswer(questionId, answer) {
            const existingIndex = answeredQuestions.findIndex(item => item.questionId === questionId);

            if (existingIndex !== -1) {
                answeredQuestions[existingIndex].answer = answer;
            } else {
                answeredQuestions.push({
                    questionId: questionId,
                    answer: answer
                });
            }
        }

        function getAnswerValue(questionId) {
            const found = answeredQuestions.find(a => String(a.questionId) == String(questionId));
            return found ? found.answer : null;
        }

        function normalizeDateOnly(value) {
            if (!value) return null;
            const str = String(value).trim();
            const datePart = str.slice(0, 10);
            if (!/^\d{4}-\d{2}-\d{2}$/.test(datePart)) {
                const d = new Date(str);
                if (isNaN(d.getTime())) return null;
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${y}-${m}-${day}`;
            }
            return datePart;
        }

        function isDateLessThan(value, cutoff) {
            const d = normalizeDateOnly(value);
            if (!d) return false;
            return d < cutoff;
        }

        function isDateGreaterThan(value, cutoff) {
            const d = normalizeDateOnly(value);
            if (!d) return false;
            return d > cutoff;
        }

        function getImvNextQuestionId(questionId) {
            if (questionnaireVersion !== 'OLD' || (questionnaireId !== 42 && questionnaireId !== 554)) {
                return null;
            }

            const qIdNum = Number(questionId);
            const dob = getAnswerValue(40);

            switch (qIdNum) {
                case 62: {
                    const a62 = getAnswerValue(62);
                    if (a62 === null || a62 === undefined) return null;
                    const val62 = String(a62).trim();
                    if (questionnaireId === 554 && val62 === '1') return '__END__';
                    if (questionnaireId === 42 && val62 === '1') return 71;
                    break;
                }
                case 63: {
                    if (questionnaireId !== 554) break;
                    const a63 = getAnswerValue(63);
                    if (a63 === null || a63 === undefined) return null;
                    const val63 = String(a63).trim();
                    if (val63 === '0') return '__END__';
                    break;
                }
                case 69: {
                    const a69 = getAnswerValue(69);
                    if (a69 === null || a69 === undefined) return null;
                    const val69 = String(a69).trim();
                    if (val69 === '0') return '__END__';
                    if (val69 === '1') return 71;
                    break;
                }
                case 71: {
                    const a71 = getAnswerValue(71);
                    if (a71 === null || a71 === undefined) return null;
                    const val71 = String(a71).trim();
                    if (val71 === '1') return 73;
                    if (val71 === '0') return 74;
                    break;
                }
                case 74: {
                    const a74 = getAnswerValue(74);
                    if (a74 === null || a74 === undefined || !dob) return null;
                    const val74 = String(a74).trim();
                    if (val74 === '1') {
                        if (isDateLessThan(dob, '1996-01-01')) return 348;
                        if (isDateGreaterThan(dob, '1996-01-01')) return 76;
                    } else if (val74 === '0') {
                        return 75;
                    }
                    break;
                }
                case 75: {
                    const a75 = getAnswerValue(75);
                    if (a75 === null || a75 === undefined || !dob) return null;
                    const val75 = String(a75).trim();
                    if (val75 === '4') {
                        return '__END__';
                    }
                    if (isDateLessThan(dob, '1996-01-01')) return 348;
                    if (isDateGreaterThan(dob, '1996-01-01')) return 76;
                    break;
                }
                case 76: {
                    const a76 = getAnswerValue(76);
                    if (a76 === null || a76 === undefined) return null;
                    const val76 = String(a76).trim();
                    if (val76 === '0') return 78;
                    break;
                }
                case 77: {
                    const a77 = getAnswerValue(77);
                    if (a77 === null || a77 === undefined) return null;
                    const val77 = String(a77).trim();
                    if (val77 === '1') return 79;
                    if (val77 === '0') return 78;
                    break;
                }
                case 78: {
                    const a78 = getAnswerValue(78);
                    if (a78 === null || a78 === undefined) return null;
                    const val78 = String(a78).trim().replace(/^\[|\]$/g, '');
                    if (val78 === '5') return '__END__';
                    return 79;
                }
                case 79: {
                    const a79 = getAnswerValue(79);
                    if (a79 === null || a79 === undefined) return null;
                    const val79 = String(a79).trim();
                    if (val79 === '1') return 81;
                    if (val79 === '0') return 80;
                }
                default:
                    break;
            }

            return null;
        }

        function cleanNavigationHistory() {
            if (navigationHistory.length <= 1) return;

            const cleaned = [];
            for (let i = 0; i < navigationHistory.length; i++) {
                if (i === 0 || navigationHistory[i] !== navigationHistory[i - 1]) {
                    cleaned.push(navigationHistory[i]);
                }
            }

            if (cleaned.length !== navigationHistory.length) {
                navigationHistory.length = 0;
                navigationHistory.push(...cleaned);
            }
        }

        // Mostrar la primera pregunta y realizamos la comprobación de condiciones
        //Si la pregunta cumple tiene condiciones ocultamos la pregunta
        questions[currentQuestionIndex].style.display = 'block';
        const questionId = questions[currentQuestionIndex].getAttribute('data-id');


        timeStart = new Date();
        nextButton.addEventListener('click', function() {
            const previousIndex = currentQuestionIndex;
            if (
                (questionnaireVersion === 'NEW' ||
                    (questionnaireVersion === 'OLD' && (questionnaireId === 42 ||
                        questionnaireId === 554)))
            ) {
                const ariaVal = progressBar ? parseFloat(progressBar.getAttribute(
                    'aria-valuenow') || '0') : null;
                if (!isNaN(ariaVal) && isFinite(ariaVal)) {
                    progressHistory.push(ariaVal);
                } else {
                    progressHistory.push(null);
                }
            }
            const questionId = questions[currentQuestionIndex].getAttribute('data-id');

            if (currentQuestionIndex < questions.length) {

                // 1️⃣ Validar y procesar la pregunta actual (esto SÍ recalcula condiciones)
                let currentQuestion = questions[currentQuestionIndex];
                const validationResult = validateAndContinue(currentQuestion, questionId);

                if (validationResult === false) {
                    return;
                }

                const currentAnswer = extractAnswer(currentQuestion);
                saveAnswer(questionId, currentAnswer);

                if (questionnaireVersion === 'OLD' && (questionnaireId === 42 ||
                        questionnaireId === 554)) {
                    const imvNext = getImvNextQuestionId(questionId);
                    if (imvNext === '__END__') {
                        if (previousIndex >= 0) {
                            navigationHistory.push(previousIndex);
                            cleanNavigationHistory();
                        }

                        questions.forEach(q => q.style.display = 'none');
                        currentQuestionIndex = questions.length;
                        nextButton.style.display = 'none';
                        submitButton.style.display = 'inline-block';
                        document.getElementById('final-message').classList.remove('hidden');
                        progressBar.style.width = '100%';
                        progressBar.setAttribute('aria-valuenow', 100);
                        updateProgress();
                        const progressText = document.getElementById('progress-text');
                        if (progressText) {
                            progressText.textContent = '100%';
                        }
                        timeStart = new Date();
                        return;
                    } else if (imvNext !== null) {
                        const targetId = String(imvNext);
                        const targetIndex = Array.from(questions).findIndex(q =>
                            q.getAttribute('data-id') == targetId
                        );
                        if (targetIndex !== -1) {
                            const targetIdNum = Number(targetId);
                            const idxHidden = hiddenQuestions.indexOf(targetIdNum);
                            if (idxHidden !== -1) {
                                hiddenQuestions.splice(idxHidden, 1);
                            }

                            if (previousIndex >= 0) {
                                navigationHistory.push(previousIndex);
                                cleanNavigationHistory();
                            }

                            if (questions[currentQuestionIndex]) {
                                questions[currentQuestionIndex].style.display = 'none';
                            }
                            currentQuestionIndex = targetIndex;
                            if (questions[currentQuestionIndex]) {
                                questions[currentQuestionIndex].style.display = 'block';
                            }
                            updateProgress();
                            prevButton.style.visibility = 'visible';
                            timeStart = new Date();
                            return;
                        }
                    }
                }

                if (questionnaireVersion === 'NEW') {
                    if (currentQuestionIndex !== previousIndex) {
                        if (currentQuestionIndex < questions.length - 1) {
                            const prevQuestion = questions[previousIndex];
                            const answer = extractAnswer(prevQuestion);
                            handleSubmitDraft('next', answer);
                        }
                        timeStart = new Date();
                        return;
                    }
                }

                questions[currentQuestionIndex].style.display = 'none';
                currentQuestionIndex++;
                // 2️⃣ Saltar preguntas ocultas SIN recalcular condiciones
                while (
                    currentQuestionIndex < questions.length &&
                    hiddenQuestions.includes(Number(questions[currentQuestionIndex]
                        .getAttribute('data-id')))
                ) {
                    currentQuestionIndex++; // solo saltamos
                }

                // 3️⃣ Mostrar la siguiente pregunta visible o el botón de enviar si ya no hay más
                if (currentQuestionIndex < questions.length) {
                    if (
                        (questionnaireVersion === 'NEW' ||
                            (questionnaireVersion === 'OLD' && (questionnaireId === 42 ||
                                questionnaireId === 554))) &&
                        previousIndex >= 0
                    ) {
                        navigationHistory.push(previousIndex);
                        cleanNavigationHistory();
                    }
                    questions[currentQuestionIndex].style.display = 'block';
                    const currentQuestionId = questions[currentQuestionIndex].getAttribute(
                        'data-id');
                    const currentQuestionText = questions[currentQuestionIndex].querySelector(
                            'label')?.textContent
                        ?.trim();

                    updateProgress();
                    prevButton.style.visibility = 'visible';
                } else {
                    // Estamos en el fin del cuestionario (sin salto IMV)
                    if (
                        (questionnaireVersion === 'NEW' ||
                            (questionnaireVersion === 'OLD' && (questionnaireId === 42 ||
                                questionnaireId === 554))) &&
                        previousIndex >= 0
                    ) {
                        navigationHistory.push(previousIndex);
                        cleanNavigationHistory();
                    }

                    if (questions[currentQuestionIndex]) {
                        const currentQuestionId = questions[currentQuestionIndex].getAttribute(
                            'data-id');
                        const currentAnswer = extractAnswer(questions[currentQuestionIndex]);
                        saveAnswer(currentQuestionId, currentAnswer);
                    }

                    nextButton.style.display = 'none';
                    submitButton.style.display = 'inline-block';
                    document.getElementById('final-message').classList.remove('hidden');
                    progressBar.style.width = '100%';
                    progressBar.setAttribute('aria-valuenow', 100);
                    updateProgress();
                    const progressText = document.getElementById('progress-text');
                    if (progressText) {
                        progressText.textContent = '100%';
                    }

                    if (currentStep === totalSteps) {
                        progressBar.style.width = '100%';
                        progressBar.setAttribute('aria-valuenow', '100');

                        // 🎉 Aquí va el confeti
                        const rect = progressBar.getBoundingClientRect();
                        const x = (rect.left + rect.width) / window.innerWidth;
                        const y = (rect.top + rect.height / 2) / window.innerHeight;

                        confetti({
                            particleCount: 50,
                            spread: 45,
                            startVelocity: 30,
                            scalar: 0.5,
                            origin: {
                                x: x,
                                y: y
                            }
                        });
                    }
                }
            }

            // Guardar borrador
            if (currentQuestionIndex < questions.length - 1) {
                const prevQuestion = questions[previousIndex];
                const answer = extractAnswer(prevQuestion);
                handleSubmitDraft('next', answer);
            }
            timeStart = new Date();
        });

        prevButton.addEventListener('click', function() {
            if (questions[currentQuestionIndex]) {
                const currentQuestionId = questions[currentQuestionIndex].getAttribute(
                    'data-id');
                const currentAnswer = extractAnswer(questions[currentQuestionIndex]);
                saveAnswer(currentQuestionId, currentAnswer);
            }

            // Oculta la pregunta actual
            if (questions[currentQuestionIndex]) {
                questions[currentQuestionIndex].style.display = 'none';
            }

            let restoredProgress = null;

            if (
                (questionnaireVersion === 'NEW' ||
                    (questionnaireVersion === 'OLD' && (questionnaireId === 42 ||
                        questionnaireId === 554))) &&
                navigationHistory.length > 0
            ) {
                const previousIndex = navigationHistory.pop();
                currentQuestionIndex = previousIndex;

                if (progressHistory.length > 0) {
                    restoredProgress = progressHistory.pop();
                }
            } else {
                do {
                    currentQuestionIndex--;
                } while (
                    currentQuestionIndex >= 0 &&
                    hiddenQuestions.includes(Number(questions[currentQuestionIndex]
                        ?.getAttribute('data-id')))
                );
            }

            // Si llega a -1, vuelve a 0
            if (currentQuestionIndex < 0) currentQuestionIndex = 0;

            // Muestra la pregunta encontrada
            if (questions[currentQuestionIndex]) {
                questions[currentQuestionIndex].style.display = 'block';
            }

            // Actualiza la visibilidad de botones
            prevButton.style.visibility = (currentQuestionIndex === 0) ? 'hidden' : 'visible';
            nextButton.style.display = 'inline-block';
            submitButton.style.display = 'none';
            document.getElementById('final-message').classList.add('hidden');

            // Recalcula la barra de progreso
            if (restoredProgress !== null && !isNaN(restoredProgress)) {
                progressBar.style.width = `${restoredProgress}%`;
                progressBar.setAttribute('aria-valuenow', restoredProgress.toFixed(0));
                const progressText = document.getElementById('progress-text');
                if (progressText) {
                    progressText.textContent = `${restoredProgress.toFixed(0)}%`;
                }
            } else {
                updateProgress();
            }

            timeStart = new Date();
        });




        // Función para seleccionar una opción

        function selectOption(button, questionId) {
            const selectedValue = button.getAttribute('data-value');
            const selectedInput = document.getElementById('selectedOption_' + questionId);

            selectedInput.value = selectedValue;

            const parent = button.closest('[data-question-type="select"]');
            if (!parent) return;

            const optionButtons = parent.querySelectorAll('button[data-value]');

            optionButtons.forEach(btn => {
                btn.classList.remove('selected', 'bg-gray-300');
            });

            button.classList.add('selected', 'bg-gray-300');
        }

        function checkConditions(questionId, answer) {
            const normalizedAnswer = Array.isArray(answer) ? answer : [answer];
            const cleanedAnswer = normalizedAnswer
                .filter(a => typeof a === 'string' || typeof a === 'number')
                .map(a => String(a).replace(/^"|"$/g, ''));

            conditions.forEach(condition => {
                if (condition.question_id == questionId) {
                    if (questionnaireVersion === 'OLD') {
                        const nextQuestionId = parseInt(condition.next_question_id, 10);
                        const operador = condition.operator || condition.operador || '==';
                        let conditionValues = [];
                        try {
                            const raw = condition.condition;
                            if (typeof raw !== 'string') {
                                conditionValues = (Array.isArray(raw) ? raw : [raw]).map(
                                    String);
                            } else {
                                let str = raw.replace(/""/g, '"');
                                let parsed;
                                try {
                                    parsed = JSON.parse(str);
                                } catch (e1) {
                                    str = str.replace(/'/g, '"');
                                    parsed = JSON.parse(str);
                                }
                                conditionValues = (Array.isArray(parsed) ? parsed : [parsed])
                                    .map(String);
                            }
                        } catch (e) {
                            if (questionId == 40) {
                                console.warn('[OLD condition] parse condition failed', {
                                    condition_id: condition.id,
                                    raw: condition.condition,
                                    error: e.message
                                });
                            }
                            return;
                        }

                        const match = cleanedAnswer.some(userVal => {
                            const userValStr = String(userVal).trim();
                            const userDateOnly = userValStr.slice(0, 10);
                            const isUserDate = /^\d{4}(-\d{2}(-\d{2})?)?$/.test(
                                userDateOnly);
                            return conditionValues.some(condVal => {
                                const condValStr = String(condVal).trim()
                                    .replace(/^"|"$/g, '');
                                const isUserNum = !isNaN(userValStr);
                                const isCondNum = !isNaN(condValStr);
                                const isCondDate = /^\d{4}(-\d{2}(-\d{2})?)?$/
                                    .test(condValStr);

                                const a = isUserNum ? parseFloat(userValStr) :
                                    userValStr;
                                const b = isCondNum ? parseFloat(condValStr) :
                                    condValStr;
                                const userCompare = isUserDate ? userDateOnly :
                                    userValStr;
                                const condCompare = isCondDate ? condValStr :
                                    condValStr;

                                let result = false;
                                switch (operador) {
                                    case '==':
                                        result = a == b;
                                        break;
                                    case '!=':
                                        result = a != b;
                                        break;
                                    case '>':
                                        if (isUserDate && isCondDate) {
                                            result = userCompare > condCompare;
                                        } else {
                                            result = isUserNum && isCondNum &&
                                                a > b;
                                        }
                                        break;
                                    case '>=':
                                        if (isUserDate && isCondDate) {
                                            result = userCompare >= condCompare;
                                        } else {
                                            result = isUserNum && isCondNum &&
                                                a >= b;
                                        }
                                        break;
                                    case '<':
                                        if (isUserDate && isCondDate) {
                                            result = userCompare < condCompare;
                                        } else {
                                            result = isUserNum && isCondNum &&
                                                a < b;
                                        }
                                        break;
                                    case '<=':
                                        if (isUserDate && isCondDate) {
                                            result = userCompare <= condCompare;
                                        } else {
                                            result = isUserNum && isCondNum &&
                                                a <= b;
                                        }
                                        break;
                                    default:
                                        result = false;
                                }
                                return result;
                            });
                        });

                        const goToEnd = condition.next_question_id == null ||
                            condition.next_question_id === '' ||
                            isNaN(nextQuestionId);
                        if (goToEnd) {
                            const questionElements = document.querySelectorAll(
                                '.question-item');
                            let currentIdx = -1;
                            const qIdNum = Number(questionId);
                            for (let i = 0; i < questionElements.length; i++) {
                                if (Number(questionElements[i].getAttribute('data-id')) ===
                                    qIdNum) {
                                    currentIdx = i;
                                    break;
                                }
                            }
                            if (currentIdx >= 0) {
                                for (let i = currentIdx + 1; i < questionElements.length; i++) {
                                    const id = Number(questionElements[i].getAttribute(
                                        'data-id'));
                                    const idx = hiddenQuestions.indexOf(id);
                                    if (questionnaireVersion === 'OLD' &&
                                        (questionnaireId === 42 || questionnaireId === 554)) {
                                        if (match) {
                                            if (idx === -1) hiddenQuestions.push(id);
                                        } else if (idx !== -1) {
                                            hiddenQuestions.splice(idx, 1);
                                        }
                                    } else {
                                        if (match && idx === -1) {
                                            hiddenQuestions.push(id);
                                        }
                                    }
                                }
                            }
                        } else if (match) {
                            if (hiddenQuestions.includes(nextQuestionId)) {
                                hiddenQuestions.splice(hiddenQuestions.indexOf(nextQuestionId),
                                    1);
                            }
                        } else {
                            if (!hiddenQuestions.includes(nextQuestionId)) {
                                hiddenQuestions.push(nextQuestionId);
                            }
                        }
                    } else if (questionnaireVersion === 'NEW') {
                        if (condition.is_composite && condition.composite_rules) {
                            const match = evaluateCompositeCondition(condition, questionId);
                            handleConditionMatch(condition, match);
                        } else {
                            const match = evaluateSimpleCondition(condition, cleanedAnswer);
                            handleConditionMatch(condition, match);
                        }
                    }
                }
            });

            if (questionnaireVersion === 'OLD' && questionId == 74) {
                const a40 = answeredQuestions.find(a => a.questionId == 40)?.answer;
                if (a40 != null && a40 !== '') {
                    checkConditions(40, a40);
                }
            }
        }

        function evaluateSimpleCondition(condition, cleanedAnswer) {
            // Normalizar operador: convertir '=' a '=='
            let operator = condition.operator || '==';
            if (operator === '=') {
                operator = '==';
            }

            let conditionValue = condition.value;

            // Si conditionValue es un array, trabajar con él directamente
            // Si es un valor único, convertirlo a array para comparación
            const expectedValues = Array.isArray(conditionValue) ? conditionValue : [conditionValue];

            switch (operator) {
                case '==':
                case '=':
                    // Comparar si algún valor de la respuesta coincide con algún valor esperado
                    return cleanedAnswer.some(val => expectedValues.some(exp => val == exp));
                case '!=':
                    // Verificar que ningún valor de la respuesta coincida con los esperados
                    return !cleanedAnswer.some(val => expectedValues.some(exp => val == exp));
                case '>':
                    return cleanedAnswer.some(val => {
                        const numVal = parseFloat(val);
                        return !isNaN(numVal) && expectedValues.some(exp => numVal > parseFloat(
                            exp));
                    });
                case '>=':
                    return cleanedAnswer.some(val => {
                        const numVal = parseFloat(val);
                        return !isNaN(numVal) && expectedValues.some(exp => numVal >=
                            parseFloat(exp));
                    });
                case '<':
                    return cleanedAnswer.some(val => {
                        const numVal = parseFloat(val);
                        return !isNaN(numVal) && expectedValues.some(exp => numVal < parseFloat(
                            exp));
                    });
                case '<=':
                    return cleanedAnswer.some(val => {
                        const numVal = parseFloat(val);
                        return !isNaN(numVal) && expectedValues.some(exp => numVal <=
                            parseFloat(exp));
                    });
                case 'in':
                    return cleanedAnswer.some(val => Array.isArray(conditionValue) && conditionValue
                        .includes(val));
                case 'contains':
                    return cleanedAnswer.some(val =>
                        expectedValues.some(exp => String(val).toLowerCase().includes(String(exp)
                            .toLowerCase()))
                    );
                case 'not_contains':
                    return !cleanedAnswer.some(val =>
                        expectedValues.some(exp => String(val).toLowerCase().includes(String(exp)
                            .toLowerCase()))
                    );
                case 'starts_with':
                    return cleanedAnswer.some(val =>
                        expectedValues.some(exp => String(val).toLowerCase().startsWith(String(exp)
                            .toLowerCase()))
                    );
                case 'ends_with':
                    return cleanedAnswer.some(val =>
                        expectedValues.some(exp => String(val).toLowerCase().endsWith(String(exp)
                            .toLowerCase()))
                    );
                default:
                    return false;
            }
        }

        function evaluateCompositeCondition(condition, currentQuestionId) {
            const rules = condition.composite_rules;
            const logic = condition.composite_logic || 'AND';

            if (!rules) {
                return false;
            }

            if (typeof rules === 'string') {
                try {
                    const parsedRules = JSON.parse(rules);
                    if (Array.isArray(parsedRules)) {
                        return evaluateCompositeCondition({
                            ...condition,
                            composite_rules: parsedRules
                        }, currentQuestionId);
                    }
                } catch (e) {
                    return false;
                }
            }

            if (!Array.isArray(rules)) {
                return false;
            }

            if (rules.length === 0) {
                return false;
            }

            const ruleResults = rules.map(rule => {
                const ruleQuestionId = rule.question_id;

                // Normalizar operador: convertir '=' a '=='
                let operator = rule.operator || '==';
                if (operator === '=') {
                    operator = '==';
                }

                let expectedValue = rule.value;
                const expectedValues = Array.isArray(expectedValue) ? expectedValue : [
                    expectedValue
                ];

                const ruleQuestionElement = document.querySelector(
                    `[data-id="${ruleQuestionId}"]`);
                if (!ruleQuestionElement) {
                    return false;
                }

                const ruleAnswer = extractAnswer(ruleQuestionElement);
                const normalizedRuleAnswer = Array.isArray(ruleAnswer) ? ruleAnswer : [
                    ruleAnswer
                ];
                const cleanedRuleAnswer = normalizedRuleAnswer
                    .filter(a => typeof a === 'string' || typeof a === 'number')
                    .map(a => String(a).replace(/^"|"$/g, ''));

                let result = false;
                switch (operator) {
                    case '==':
                    case '=':
                        result = cleanedRuleAnswer.some(val => expectedValues.some(exp => val ==
                            exp));
                        break;
                    case '!=':
                        result = !cleanedRuleAnswer.some(val => expectedValues.some(exp =>
                            val == exp));
                        break;
                    case '>':
                        result = cleanedRuleAnswer.some(val => parseFloat(val) > parseFloat(
                            expectedValue));
                        break;
                    case '>=':
                        result = cleanedRuleAnswer.some(val => parseFloat(val) >= parseFloat(
                            expectedValue));
                        break;
                    case '<':
                        result = cleanedRuleAnswer.some(val => parseFloat(val) < parseFloat(
                            expectedValue));
                        break;
                    case '<=':
                        result = cleanedRuleAnswer.some(val => parseFloat(val) <= parseFloat(
                            expectedValue));
                        break;
                    case 'in':
                        result = cleanedRuleAnswer.some(val => Array.isArray(expectedValue) &&
                            expectedValue.includes(val));
                        break;
                    case 'contains':
                        result = cleanedRuleAnswer.some(val => String(val).includes(String(
                            expectedValue)));
                        break;
                    default:
                        result = false;
                }
                return result;
            });

            let finalResult = false;
            if (logic === 'AND') {
                finalResult = ruleResults.every(result => result === true);
            } else if (logic === 'OR') {
                finalResult = ruleResults.some(result => result === true);
            }

            return finalResult;
        }

        function handleConditionMatch(condition, match) {
            const nextQuestionId = condition.next_question_id;

            if (match) {
                if (nextQuestionId === null) {
                    currentQuestionIndex = questions.length;
                    questions.forEach(q => q.style.display = 'none');
                    nextButton.style.display = 'none';
                    submitButton.style.display = 'inline-block';
                    document.getElementById('final-message').classList.remove('hidden');

                    for (let i = currentQuestionIndex; i < questions.length; i++) {
                        const questionId = questions[i].getAttribute('data-id');
                        if (!hiddenQuestions.includes(Number(questionId))) {
                            hiddenQuestions.push(Number(questionId));
                        }
                    }

                    updateProgress();
                    return;
                } else {
                    const targetIndex = Array.from(questions).findIndex(q =>
                        q.getAttribute('data-id') == nextQuestionId
                    );
                    if (targetIndex !== -1) {
                        if (questions[currentQuestionIndex]) {
                            questions[currentQuestionIndex].style.display = 'none';
                        }

                        navigationHistory.push(currentQuestionIndex);
                        currentQuestionIndex = targetIndex;

                        if (questions[currentQuestionIndex]) {
                            questions[currentQuestionIndex].style.display = 'block';
                            updateProgress();
                            prevButton.style.visibility = 'visible';
                        }
                        return;
                    }
                }
            }
        }

        function validateAndContinue(currentQuestion, questionId) {
            const errorContainer = document.getElementById('error');
            const errorMessage = document.getElementById('error-message');
            const input = currentQuestion.querySelector(
                'input[type="text"], input[type="number"], input[type="date"]');

            function showError(message) {
                errorMessage.innerHTML = message;
                errorContainer.style.display = 'block';
                changeLinesToRed();
            }

            if (questionId == 36) {
                const provinciaSelect = currentQuestion.querySelector('select');
                if (provinciaSelect && provinciaSelect.value === '') {
                    showError('Por favor, selecciona una provincia antes de continuar.');
                    return false;
                }
            } else if (questionId == 37) {
                const municipioSelect = currentQuestion.querySelector('select');
                if (municipioSelect && municipioSelect.value === '') {
                    showError('Por favor, selecciona un municipio antes de continuar.');
                    return false;
                }
            } else if (questionId == 38) {
                const ccaaSelect = currentQuestion.querySelector('select');
                if (ccaaSelect && ccaaSelect.value === '') {
                    showError('Por favor, selecciona una Comunidad Autónoma antes de continuar.');
                    return false;
                }
            } else if (questionId == {{ Question::where('slug', 'telefono')->value('id') }}) {
                console.log('telefono');
                const telefonoInput = currentQuestion.querySelector('#phone-input');
                console.log(telefonoInput);
                if (telefonoInput && telefonoInput.value === '') {
                    showError('Por favor, rellena tu teléfono antes de continuar.');
                    return false;
                }
            }

            if (input && input.value.trim() === '') {
                showError('Por favor, selecciona una opción antes de continuar.');
                return false;
            }

            // Aquí validamos el patrón regex si existe
            const pattern = currentQuestion.getAttribute('data-pattern');
            const patternError = currentQuestion.getAttribute('data-error-message');
            if (pattern && input) {
                //  RegExp a partir del patrón
                const regex = new RegExp(pattern);
                if (!regex.test(input.value.trim())) {
                    showError(patternError || 'El valor introducido no es válido.');
                    return false;
                }
            }


            // SINGLE OPTION
            if (currentQuestion.querySelector('button#option')) {
                const selectedOption = currentQuestion.querySelector('button#option.selected');
                if (!selectedOption) {
                    showError('Por favor, selecciona una opción antes de continuar.');
                    return false;
                } else {
                    const answer = selectedOption.getAttribute('data-value');
                    nextQuestion(answer);
                }
                return true;
            }

            // MULTIPLE OPTION
            if (currentQuestion.querySelector('button#option-multiple')) {
                const selectedOption = currentQuestion.querySelector('button#option-multiple.selected');
                if (!selectedOption) {
                    showError('Por favor, selecciona una opción antes de continuar.');
                    return false;
                } else {
                    const answer = selectedOption.getAttribute('data-value');
                    nextQuestion(answer);
                }
                return true;
            }

            // CHECKBOX
            if (currentQuestion.querySelector('input[type="checkbox"]')) {
                const selectedOption = currentQuestion.querySelector('input[type="checkbox"]');
                nextQuestion(selectedOption.checked ? selectedOption.value : 0);
                return true;
            }
            const select = currentQuestion.querySelector('select');
            if (select) {
                nextQuestion(select.value);
                return true;
            }

            // STRING INPUT
            if (input) { // input[type="text"] está ya validado arriba
                nextQuestion(input.value.trim());
                return true;
            }

            // DEFAULT
            const fallbackInput = currentQuestion.querySelector('input[name^="answers["]');
            const fallbackValue = fallbackInput?.value;

            if (fallbackValue !== undefined && fallbackValue !== null && fallbackValue !== '') {
                nextQuestion(fallbackValue);
            } else {
                // No hay valor → no disparamos condiciones
                nextQuestion(null);
            }

            return true;

            function nextQuestion(answer) {
                if (answer !== null && answer !== undefined && answer !== '') {
                    checkConditions(questionId, JSON.stringify(answer));
                }
                errorContainer.style.display = 'none';
            }
        }

        function extractAnswer(questionElement) {
            if (!questionElement) return '';
            const qId = questionElement.getAttribute('data-id');

            const select = questionElement.querySelector('select');
            if (select) return select.value;

            const textInput = questionElement.querySelector(
                'input[type="text"], input[type="number"], input[type="date"]');
            if (textInput) return textInput.value.trim();

            const selectedSingle = questionElement.querySelector('button#option.selected');
            if (selectedSingle) return selectedSingle.getAttribute('data-value');

            const multiInput = document.getElementById('selectedOptions_' + qId);
            if (multiInput) return multiInput.value;

            const checkbox = questionElement.querySelector('input[type="checkbox"]');
            if (checkbox) return checkbox.checked ? checkbox.value : 0;

            const selectedHidden = document.getElementById('selectedOption_' + qId);
            if (selectedHidden) return selectedHidden.value;

            const wheelInput = document.getElementById('wheel-input-' + qId);
            if (wheelInput) return wheelInput.value;

            return '';
        }
        let isSubmittingDraft = false;

        function handleSubmitDraft(direction, answer) {
            if (isSubmittingDraft) return;
            isSubmittingDraft = true;

            const timeEnd = new Date();

            const draft = {
                user_id: {{ auth()->check() ? auth()->user()->id : 'null' }},
                questionnaire_id: {{ request()->route('id') }},
                direction: direction,
                time_start: formatDate(timeStart || new Date()),
                time_end: formatDate(timeEnd),
                respuesta: answer,
                session_id: sessionStorage.getItem('questionnaire_uuid') || uuid
            };
            const fd = new FormData();
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                const inputToken = document.querySelector('input[name="_token"]');
                if (inputToken) csrfToken = inputToken.value;
            }
            if (!csrfToken) {
                //console.warn('CSRF token not found.');
                isSubmittingDraft = false;
                return;
            } else {
                fd.append('_token', csrfToken);

            }

            fd.append('_token', csrfToken);
            Object.entries(draft).forEach(([k, v]) => fd.append(k, v ?? ''));

            fetch("{{ route('storeQuestionnaireDraft') }}", {
                method: 'POST',
                body: fd,
                keepalive: true,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then((response) => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(
                            `HTTP error! status: ${response.status}, message: ${JSON.stringify(data)}`
                        );
                    });
                }
                return response.json();
            }).then((data) => {
                isSubmittingDraft = false;
            }).catch((err) => {
                isSubmittingDraft = false;
            });
        }

        function getTotalVisibleQuestions() {
            let count = 0;
            questions.forEach(qElement => {
                if (qElement && !hiddenQuestions.includes(Number(qElement.getAttribute(
                        'data-id')))) {
                    count++;
                }
            });
            return count;
        }
        // Actualizar la barra de progreso
        function updateProgress() {
            if (!progressBar) return;

            const totalVisible = getTotalVisibleQuestions();
            if (totalVisible === 0) return;

            let visiblePassed = 0;
            for (let i = 0; i < currentQuestionIndex; i++) {
                const id = Number(questions[i].getAttribute('data-id'));
                if (!hiddenQuestions.includes(id)) {
                    visiblePassed++;
                }
            }

            let progress = ((visiblePassed + 1) / totalVisible) * 100;

            if (isNaN(progress) || !isFinite(progress)) {
                progress = 0;
            }

            const finalMessageEl = document.getElementById('final-message');
            const isFinalVisible = finalMessageEl && !finalMessageEl.classList.contains('hidden');
            if (!isFinalVisible) {
                progress = Math.min(progress, 95);
            } else {
                progress = Math.min(progress, 100);
            }

            progressBar.style.width = `${progress}%`;
            progressBar.setAttribute('aria-valuenow', progress.toFixed(0));

            const progressText = document.getElementById('progress-text');
            if (progressText) {
                progressText.textContent = `${progress.toFixed(0)}%`;
            }
        }
        // Función para decrementar el progreso al retroceder
        function decrementProgress(currentIndex) {
            const totalVisible = getTotalVisibleQuestions();
            if (totalVisible === 0) return;

            // Contar solo preguntas visibles antes de la actual
            let visiblePassed = 0;
            for (let i = 0; i < currentIndex; i++) {
                const id = Number(questions[i].getAttribute('data-id'));
                if (!hiddenQuestions.includes(id)) {
                    visiblePassed++;
                }
            }

            let progress = ((visiblePassed + 1) / totalVisible) * 100;

            if (isNaN(progress) || !isFinite(progress)) {
                progress = 0;
            }

            progress = Math.min(progress, 100);

            if (currentIndex === questions.length - 1) {
                progress = Math.min(progress, 95);
            }

            const finalValue = Math.min(progress, 99);
            progressBar.style.width = `${finalValue}%`;
            progressBar.setAttribute('aria-valuenow', finalValue.toFixed(0));

            const progressText = document.getElementById('progress-text');
            if (progressText) {
                progressText.textContent = `${finalValue.toFixed(0)}%`;
            }
        }

        function selectMultipleOptions(button, questionId) {
            const selectedValue = Number(button.getAttribute('data-value'));
            const selectedInput = document.getElementById('selectedOptions_' + questionId);

            if (!window.currentValuesMap) window.currentValuesMap = {};
            if (!Array.isArray(window.currentValuesMap[questionId])) {
                window.currentValuesMap[questionId] = selectedInput.value
                    .split(',')
                    .filter(v => v !== '')
                    .map(Number);
            }

            let values = window.currentValuesMap[questionId];

            const allButtons = document.querySelectorAll(`[data-question-id="${questionId}"]`);

            if (selectedValue === -1) {

                if (button.classList.contains('selected')) {
                    values = []; // Deseleccionar "Ninguna de las anteriores" (vaciar la selección)
                    button.classList.remove('selected', 'bg-gray-300');
                } else {
                    // Desmarcar todos los botones excepto el de "ninguna"
                    allButtons.forEach(button => {
                        const val = Number(button.getAttribute('data-value'));
                        if (val !== -1) button.classList.remove('selected', 'bg-gray-300');
                    });

                    // Marcar solo "ninguna de las anteriores"
                    button.classList.add('selected', 'bg-gray-300');
                    values = [-1];
                }

            } else { // cuando no es "ninguna de las anteriores"
                // Si "ninguna" estaba seleccionada, desmarcarla
                const index = values.indexOf(-1);

                // Obtener el botón "Ninguna de las anteriores"
                const noneButton = document.querySelector(
                    `[data-question-id="${questionId}"][data-value="-1"]`);

                if (index !== -1) {
                    values.splice(index, 1);
                    if (noneButton) noneButton.classList.remove('selected', 'bg-gray-300');
                }

                // Toggle de selección normal
                if (values.includes(selectedValue)) {
                    values = values.filter(value => value !== selectedValue);
                    button.classList.remove('selected', 'bg-gray-300');
                } else {
                    values.push(selectedValue);
                    button.classList.add('selected', 'bg-gray-300');
                }
            }


            window.currentValuesMap[questionId] = values;
            selectedInput.value = values.join(',');
        }

        function refreshVisibleQuestions() {
            questions.forEach((questionElement, idx) => {
                const questionId = Number(questionElement.getAttribute('data-id'));

                if (hiddenQuestions.includes(questionId)) {
                    questionElement.style.display = 'none';
                } else {
                    questionElement.style.display = (idx === currentQuestionIndex) ? 'block' :
                        'none';
                }
            });

            // Ocultar botón anterior si estamos en la primera visible
            const firstVisibleIndex = Array.from(questions).findIndex(q => !hiddenQuestions.includes(
                Number(q.dataset.id)));
            prevButton.style.visibility = (currentQuestionIndex > firstVisibleIndex) ? 'visible' :
                'hidden';

        }

        // Escuchar cambios en los inputs para revalidar condiciones y actualizar visibilidad
        if (questionnaireVersion === 'OLD') {
            document.querySelectorAll('input, button').forEach(el => {
                el.addEventListener('change', () => {
                    const questionElement = el.closest('.question-item');
                    if (!questionElement) return;

                    const qId = questionElement.getAttribute('data-id');
                    let answer = null;

                    if (el.type === 'checkbox') {
                        answer = el.checked ? el.value : 0;
                    } else if (el.tagName === 'INPUT') {
                        answer = el.value;
                    } else if (el.tagName === 'BUTTON') {
                        if (el.hasAttribute('data-value')) {
                            const questionType = el.closest('[data-question-type]')
                                ?.getAttribute(
                                    'data-question-type');
                            if (questionType === 'multiple') {
                                answer = window.currentValuesMap[qId] || [];
                            } else {
                                answer = el.getAttribute('data-value');
                            }
                        }
                    }

                    checkConditions(qId, JSON.stringify(answer));
                    refreshVisibleQuestions();
                });
            });
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            const allInputs = document.querySelectorAll(
                'input[name^="answers["], select[name^="answers["], textarea[name^="answers["]'
            );
            allInputs.forEach(input => input.disabled = true);
            answeredQuestions.forEach(item => {
                const inputs = document.querySelectorAll(
                    `[name^="answers[${item.questionId}]"]`);
                inputs.forEach(input => {
                    input.disabled = false;
                    if (input.type === 'checkbox') {
                        input.checked = item.answer == 1;
                    } else if (input.type === 'radio') {
                        input.checked = input.value == item.answer;
                    } else {
                        input.value = item.answer;
                    }
                });
            });
        });

        //Carga los municipios a partir de la provincia cuando sea necesario
        document.addEventListener("DOMContentLoaded", function() {
            const provinciaSelect = document.getElementById('provincia_select');
            const municipioSelect = document.getElementById('municipio_select');

            if (provinciaSelect && municipioSelect) {
                provinciaSelect.addEventListener('change', function() {
                    const provinciaId = this.value;

                    municipioSelect.innerHTML =
                        '<option>Cargando municipios...</option>';
                    municipioSelect.disabled = true;

                    fetch(`/municipios/${provinciaId}`)

                        .then(res => res.json())
                        .then(data => {
                            municipioSelect.innerHTML =
                                '<option value="">Selecciona un municipio</option>';
                            data.forEach(m => {
                                const opt = document.createElement(
                                    'option');
                                opt.value = m.id;
                                opt.textContent = m.nombre_municipio;
                                municipioSelect.appendChild(opt);
                            });
                            municipioSelect.disabled = false;
                        });
                });
            }
        });

        // Manejo de teclas para avanzar con Enter
        // Evitar el comportamiento por defecto de Enter en inputs de texto
        document.getElementById('questionnaire-form').addEventListener('keydown', function(event) {
            const isInput = ['INPUT', 'TEXTAREA'].includes(event.target.tagName);
            const type = event.target.getAttribute('type');

            if (event.key === 'Enter' && isInput && ['text', 'number', 'date'].includes(type)) {
                event.preventDefault();
                document.getElementById('next-button')?.click();
            }
        });

        window.addEventListener('beforeunload', () => {
            if (currentQuestionIndex >= 0 && currentQuestionIndex < questions.length) {
                const currentQuestion = questions[currentQuestionIndex];
                if (currentQuestion) {
                    const answer = extractAnswer(currentQuestion);
                    handleSubmitDraft('next', answer);
                }
            }
        });


        function initWheelPicker(questionId, initialValue) {
            const wheel = document.getElementById(`wheel-${questionId}`);
            const input = document.getElementById(`wheel-input-${questionId}`);
            const items = wheel.querySelectorAll('.wheel-picker-item');

            if (!wheel || !input) return;

            // 🧠 Limita valor máximo
            if (initialValue > 50000) {
                initialValue = 50000;
            }

            // ✅ Buscar el valor más cercano al inicial
            let closestValue = 0;
            let minDistance = Infinity;

            items.forEach(item => {
                const value = parseInt(item.dataset.value);
                const distance = Math.abs(initialValue - value);
                if (distance < minDistance) {
                    minDistance = distance;
                    closestValue = value;
                }
            });

            // ✅ Seleccionar item inicial
            const initialItem = wheel.querySelector(`[data-value="${closestValue}"]`);
            if (initialItem) {
                initialItem.classList.add('selected');
                input.value = closestValue;

                // 🔁 Disparar evento de cambio para condiciones
                const event = new Event('change', {
                    bubbles: true
                });
                input.dispatchEvent(event);

                // Scroll al valor
                setTimeout(() => {
                    const itemTop = initialItem.offsetTop;
                    const wheelHeight = wheel.offsetHeight;
                    const itemHeight = initialItem.offsetHeight;
                    const scrollTop = itemTop - (wheelHeight / 2) + (itemHeight / 2);
                    const maxScroll = wheel.scrollHeight - wheel.offsetHeight;
                    const finalScrollTop = Math.min(scrollTop, maxScroll);
                    wheel.scrollTop = finalScrollTop;
                }, 100);
            }

            // ✅ Función para actualizar selección y emitir evento
            function updateSelection() {
                const wheelRect = wheel.getBoundingClientRect();
                const wheelCenter = wheelRect.top + wheelRect.height / 2;

                let selectedValue = null;

                items.forEach(item => {
                    const itemRect = item.getBoundingClientRect();
                    const itemCenter = itemRect.top + itemRect.height / 2;
                    const distance = Math.abs(itemCenter - wheelCenter);

                    if (distance < itemRect.height / 2) {
                        item.classList.add('selected');
                        selectedValue = item.dataset.value;
                    } else {
                        item.classList.remove('selected');
                    }
                });

                if (selectedValue !== null) {
                    input.value = selectedValue;

                    // 🔁 Disparar evento de cambio para condiciones
                    const event = new Event('change', {
                        bubbles: true
                    });
                    input.dispatchEvent(event);
                }
            }

            // 📜 Scroll detectado
            wheel.addEventListener('scroll', updateSelection);

            // 📌 Click sobre item
            items.forEach(item => {
                item.addEventListener('click', () => {
                    const value = parseInt(item.dataset.value);
                    const itemTop = item.offsetTop;
                    const wheelHeight = wheel.offsetHeight;
                    const itemHeight = item.offsetHeight;
                    const scrollTop = itemTop - (wheelHeight / 2) + (itemHeight / 2);
                    const maxScroll = wheel.scrollHeight - wheel.offsetHeight;
                    const finalScrollTop = Math.min(scrollTop, maxScroll);

                    wheel.scrollTo({
                        top: finalScrollTop,
                        behavior: 'smooth'
                    });
                });
            });

            // 🖐️ Tacto móvil
            let isScrolling = false;
            let scrollTimeout;

            wheel.addEventListener('touchstart', () => {
                isScrolling = true;
            });

            wheel.addEventListener('touchend', () => {
                isScrolling = false;
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    updateSelection();
                }, 150);
            });

            updateSelection(); // Inicializa
        }
    </script>
    {{-- Spinner de carga --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('questionnaire-form');
            const spinner = document.getElementById('spinner-overlay');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                spinner.classList.remove('hidden');


                setTimeout(() => {
                    form.submit();
                }, 2000);
            });
        });
    </script>

    <!--canvas.js-->
    <script>
        const canvas = document.getElementById('background-canvas');
        const ctx = canvas.getContext('2d');

        let width, height;
        const waveCount = 5;
        const waves = [];

        function resizeCanvas() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        }

        // Crear ondas con propiedades aleatorias
        function initWaves() {
            waves.length = 0;
            for (let i = 0; i < waveCount; i++) {
                waves.push({
                    offset: Math.random() * 1000,
                    speed: 0.005,
                    amplitude: 350 + Math.random() * 50,
                    frequency: 0.00001 + Math.random() * 0.003,
                    color: `rgb(89,237,202, ${0.1 + Math.random() * 0.15})`, // azul translúcido
                    targetColor: null,
                    transitionProgress: 1,
                    lineWidth: 3 + Math.random() * 0.6,
                });
            }
        }

        function draw() {
            ctx.clearRect(0, 0, width, height);
            const centerY = height / 2;

            for (const wave of waves) {
                ctx.beginPath();
                ctx.strokeStyle = wave.color;
                ctx.lineWidth = wave.lineWidth;

                for (let x = 0; x < width; x++) {
                    const y = centerY + Math.sin(x * wave.frequency + wave.offset) * wave.amplitude;
                    ctx.lineTo(x, y);
                }

                ctx.stroke();

                // Mover la onda suavemente
                wave.offset += wave.speed;
            }

            requestAnimationFrame(draw);
        }

        // Cambiar color a rojo durante 1 segundo
        function changeLinesToRed() {
            for (const wave of waves) {
                wave.color = 'rgba(255,0,0,0.3)',
                    wave.lineWidth = 1;
            }

            let flashes = 0;
            const maxFlashes = 4; // Número de parpadeos (rojo ↔ normal = 1 parpadeo)
            const flashInterval = 150; // Tiempo entre parpadeos en ms

            const interval = setInterval(() => {
                // Alternar el fondo entre rojo y transparente
                if (flashes % 2 === 0) {
                    canvas.style.backgroundColor = 'rgba(100, 101, 104 )'; // rojo oscuro
                } else {
                    canvas.style.backgroundColor = ''; // fondo original
                }

                flashes++;

                if (flashes >= maxFlashes) {
                    clearInterval(interval);
                    canvas.style.backgroundColor = ''; // aseguramos que vuelve al original
                    // Restaurar el color original de las ondas
                    for (const wave of waves) {
                        wave.color = `rgb(89,237,202, ${0.1 + Math.random() * 0.15})`;
                        wave.lineWidth = 3 + Math.random() * 0.6;
                    }

                }
            }, flashInterval);
        }


        // Inicializar todo
        window.addEventListener('resize', () => {
            resizeCanvas();
            initWaves();
        });

        resizeCanvas();
        initWaves();
        draw();
    </script>

    <script src="{{ asset('js/phone-country-selector.js') }}"></script>

</body>

</html>
