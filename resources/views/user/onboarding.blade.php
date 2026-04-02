<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Fin del Administrador de etiquetas de Google -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Ayudas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        <script>
            (function(w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({
                    'gtm.start': new Date().getTime(),
                    event: 'gtm.js'
                });
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s),
                    dl = l != 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', 'GTM-W9GF583');
        </script>
        <x-clarity-analytics />
        <x-gtm-noscript />
    @endif
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Ubuntu', sans-serif;
            list-style: none;
        }

        :root {
            --primary-color: #59edca;
            --primary-dark: #40d4b0;
        }

        body {
            background-color: #f8f9fa;
        }

        #popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 20px;
            border-radius: 10px;
            z-index: 9999;
            width: 90%;
            max-width: 500px;
        }

        .popup-content {
            text-align: center;
        }

        .btn-accept {
            margin: 10px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-accept:hover {
            background-color: #45a049;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
            padding: 0.5rem 1.5rem;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .help-card {
            border-left: 4px solid var(--primary-color);
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            overflow: hidden;
        }

        .help-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background-color: rgba(89, 237, 202, 0.2);
            border-color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
        }

        .alert-success-none-bg {
            border-color: var(--primary-color);
        }

        .plazo-abierto {
            border-color: var(--primary-color);
            background-color: rgba(64, 212, 176, 0.12);
        }

        .plazo-pronto {
            border-color: #facc15;
            background-color: rgba(250, 204, 21, 0.10);
        }

        .plazo-cerrado {
            border-color: #d1d5db;
        }

        .container-fluid {
            max-width: 1200px;
            padding: 2rem 1.5rem;
        }

        h1 {
            color: #333;
            font-size: 2.2rem !important;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }

        .card-title {
            color: #333;
            font-weight: 600;
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }

        .card-body {
            padding: 1.8rem;
        }

        .ayuda-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .ayuda-info-item {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 6px;
        }

        .ayuda-info-item strong {
            display: block;
            margin-bottom: 0.5rem;
            color: #444;
            font-size: 0.95rem;
        }

        .ayuda-info-item p {
            color: #555;
            font-size: 1rem;
            margin-bottom: 0;
        }

        .solicitar-btn {
            margin-top: 1.5rem;
            text-align: right;
        }

        .header-highlight {
            margin-bottom: 2.5rem;
        }

        /* Estilos para el selector de ordenación */
        .sort-container {
            display: flex;
            margin-bottom: 1.5rem;
        }

        .sort-label {
            margin-right: 0.5rem;
            font-weight: 500;
            color: #555;
        }

        .sort-select {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            background-color: white;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .sort-select:hover {
            border-color: var(--primary-color);
        }

        .sort-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(64, 212, 176, 0.2);
        }

        @media (max-width: 768px) {
            .container-fluid {
                padding: 1.5rem 1rem;
            }

            .card-body {
                padding: 1.2rem;
            }

            .ayuda-info {
                grid-template-columns: 1fr;
            }

            .solicitar-btn {
                text-align: center;
            }

            .sort-container {
                justify-content: flex-start;
                margin-top: 1rem;
            }
        }

        .empty-state {
            text-align: center;
            padding: 3rem 0;
        }

        .empty-state .alert {
            display: inline-block;
            text-align: left;
        }
    </style>
</head>

<body>
    <x-gtm-noscript />
    <x-simulation-banner />
    {{-- @if ($ref_code)
        <div id="popup" style="display:block;">
            <div class="popup-content">
                <h3>Unirte a la unidad familiar</h3>
                <p>¿Deseas unirte a la unidad familiar de quien te ha invitado?</p>
                <button class="btn-accept" onclick="acceptInvitation()">Aceptar</button>
                <button class="btn-accept" onclick="rejectInvitation()">Rechazar</button>
            </div>
        </div>
    @endif --}}

    <div class="container-fluid px-4">
        <div class="flex flex-col md:flex-row justify-center items-center">
            <!-- Main Content -->
            <div class="w-full lg:w-3/5">
                <div class="flex flex-col justify-center items-center mb-3 mt-2 text-center">
                    <h2 class="mb-3 text-2xl sm:text-3xl md:text-4xl font-bold">¡Increíble!</h2>
                    <h2 class="text-base sm:text-2lg md:text-xl">
                        Hemos detectado
                        {{ $ayudas->count() == 1 ? '1 ayuda pública' : $ayudas->count() . ' ayudas públicas' }}
                        que
                        puedes pedir, por un total de
                        <strong
                            style="white-space: nowrap;">{{ number_format($cuantia_total, 0, ',', '.') }}&nbsp;€</strong>
                    </h2>
                </div>

                <div class="alert alert-success px-4 py-3">
                    <div class="flex flex-row items-center gap-4">
                        <img src="{{ asset('imagenes/estilo-trofeo-plano.png') }}" alt="Trofeo"
                            class="w-20 h-auto object-cover" />
                        <div class="">
                            <h2 class="text-lg sm:text-2xl md:text-3xl font-bold">Puedes conseguir
                                hasta</h2>
                            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold">
                                {{ number_format($cuantia_total, 0, ',', '.') }} €
                            </h2>
                            <h2 class="text-lg sm:text-xl md:text-2xl mb-0">En menos de 15 minutos
                            </h2>
                        </div>
                    </div>
                </div>

                <div
                    class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
                    <h2
                        class="mt-4 sm:mb-5 mb-3 text-start text-2xl sm:text-3xl md:text-4xl font-bold">
                        Ayudas
                        disponibles</h2>
                </div>
                <p class="text-left text-base sm:text-lg md:text-xl mt-4 mb-4">
                    👇 Empieza por la que prefieras. Solo necesitas completar un pequeño formulario.
                </p>

                @if ($ayudas->isEmpty())
                    <div class="alert alert-info">
                        <strong>No hay ayudas disponibles en este momento.</strong>
                    </div>
                @else
                    <div class="sort-container">
                        <label for="sortSelect" class="sort-label">Ordenar por:</label>
                        <select id="sortSelect" class="sort-select">
                            <option value="fecha">Fecha límite</option>
                            <option value="cantidad">Cuantía</option>
                        </select>
                    </div>

                    <div id="ayudasContainer">
                        @foreach ($ayudas as $ayuda)
                            @php
                                $hoy = \Carbon\Carbon::now();
                                $inicio = \Carbon\Carbon::parse($ayuda->fecha_inicio);
                                $fin = $ayuda->fecha_fin
                                    ? \Carbon\Carbon::parse($ayuda->fecha_fin)
                                    : null;
                                $ayudaAbierta = $inicio->lte($hoy) && (!$fin || $fin->gte($hoy));
                                $tieneCuentaAtras = $ayudaAbierta && $ayuda->fecha_fin;
                            @endphp
                            <div class="alert alert-success-none-bg {{ $ayuda->estado_plazo }} {{ $tieneCuentaAtras ? 'border-4 border-green-400 shadow-2xl ring-2 ring-green-400' : ($ayudaAbierta && !$ayuda->fecha_fin ? 'border-2 border-red-400 border shadow-lg' : 'shadow-md') }}"
                                data-fecha="{{ $ayuda->fecha_fin ? \Carbon\Carbon::parse($ayuda->fecha_fin)->timestamp : '9999999999' }}"
                                data-cantidad="{{ $ayuda->cuantia_usuario }}"
                                data-abierta="{{ $ayudaAbierta ? '1' : '0' }}">
                                <div class="card-body px-1 relative">
                                    <div
                                        class="flex flex-col md:flex-row justify-between items-start">
                                        <h3
                                            class="card-title text-left text-xl md:text-2xl font-bold mb-3 mt-10 md:mt-8">
                                            {{ $ayuda->nombre_ayuda }}</h3>
                                    </div>

                                    <!--Cuenta atras -->
                                    <div class="absolute -top-3 -right-3 z-[5]">
                                        @if ($ayudaAbierta && $ayuda->fecha_fin)
                                            <div class="relative">
                                                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-full 
                                                            bg-white shadow-xl"
                                                    style="border: 1px solid #374151;">

                                                    {{-- Puntito parpadeando --}}
                                                    <span class="relative flex h-3 w-3">
                                                        <span
                                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-200 opacity-75"></span>
                                                        <span
                                                            class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                                    </span>

                                                    <div class="flex flex-col leading-tight">
                                                        <span
                                                            class="text-[10px] sm:text-xs font-bold uppercase tracking-wide"
                                                            style="color: #111827;">
                                                            Plazo abierto
                                                        </span>
                                                        <span
                                                            class="text-[9px] sm:text-xs font-medium"
                                                            style="color: #374151;">
                                                            Cierra en
                                                            <span
                                                                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md font-mono font-bold ttf-countdown text-[8px] sm:text-xs"
                                                                style="background-color: #F3F4F6; color: #111827;"
                                                                data-fecha-fin="{{ \Carbon\Carbon::parse($ayuda->fecha_fin)->timestamp }}">
                                                                cargando...
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($ayudaAbierta && !$ayuda->fecha_fin)
                                            <div class="relative">
                                                <div
                                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-full 
                                                            bg-white border border-emerald-400 
                                                            shadow-xl">

                                                    <span class="relative flex h-3 w-3">
                                                        <span
                                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-200 opacity-75"></span>
                                                        <span
                                                            class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                                    </span>

                                                    <div class="flex flex-col leading-tight">
                                                        <span
                                                            class="text-[10px] sm:text-xs font-bold uppercase tracking-wide text-emerald-700">
                                                            Plazo abierto
                                                        </span>
                                                        <span
                                                            class="text-[9px] sm:text-xs font-medium text-emerald-800">
                                                            Sin fecha de fin
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Contenedor columnas -->
                                    <div class="flex flex-col flex-wrap justify-between gap-3">
                                        <!-- Primera columna -->
                                        <div
                                            class="flex-1 flex flex-row space-y-2 justify-between px-2">
                                            <div class="flex flex-row items-center space-x-4">
                                                <div
                                                    class="border border-amber-200 rounded-full p-1.5 sm:p-2 bg-white shadow">
                                                    <img src="{{ asset('imagenes/organos/' . $ayuda->organo->imagen) }}"
                                                        alt="{{ $ayuda->organo->nombre_organismo }}"
                                                        class="w-8 h-8 sm:w-10 sm:h-10 md:w-14 md:h-14 object-contain rounded-full" />
                                                </div>
                                                <div>
                                                    <p class="text-base sm:text-lg md:text-2xl">
                                                        Hasta</p>
                                                    <p
                                                        class="text-lg sm:text-xl md:text-3xl font-bold">
                                                        {{ $ayuda->cuantia_usuario == 0 ? 'Ilimitado' : $ayuda->getDineroFormateado($ayuda->cuantia_usuario, 0) }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div
                                                class="p-2 sm:p-3 rounded-2xl alert alert-success-none-bg {{ $ayuda->estado_plazo }}">
                                                <div
                                                    class="flex flex-row gap-2 sm:gap-3 items-start">
                                                    <i class="fa-solid fa-calendar-check text-sm sm:text-xl pt-1 ms:pt-0 lg-"
                                                        style="color: #63E6BE;"></i>
                                                    <div
                                                        class="flex flex-col text-xs sm:text-sm md:text-xl">
                                                        <p>
                                                            Del
                                                            {{ \Carbon\Carbon::parse($ayuda->fecha_inicio)->format('d/m/Y') }}
                                                        </p>
                                                        <p>
                                                            @if ($ayuda->fecha_fin)
                                                                al
                                                                {{ \Carbon\Carbon::parse($ayuda->fecha_fin)->format('d/m/Y') }}
                                                            @else
                                                                Sin fecha de fin
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Segunda columna -->
                                        <div class="flex-1 flex flex-col gap-3">
                                            @if (
                                                $ayuda->description ||
                                                    $ayuda->presupuesto ||
                                                    $ayuda->fecha_inicio_periodo ||
                                                    $ayuda->fecha_fin_periodo)
                                                <details class="group">
                                                    <summary
                                                        class="flex items-center cursor-pointer text-sm sm:text-xl"
                                                        style="color: #40d4b0;">
                                                        <i
                                                            class="fa-solid fa-circle-info text-sm sm:text-xl mr-2"></i>
                                                        <span>Ver descripción completa</span>
                                                        <svg class="w-4 h-4 ml-1 transform transition-transform duration-200 group-open:rotate-180"
                                                            fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </summary>
                                                    <div class="mt-2 p-3 rounded-lg text-lg"
                                                        style="background-color: #e2f4ee;">
                                                        @if ($ayuda->description)
                                                            <div class="mb-3">
                                                                <strong>Descripción:</strong>
                                                                {!! $ayuda->description !!}
                                                            </div>
                                                        @endif

                                                        <div
                                                            class="flex flex-wrap items-center gap-4">
                                                            @if ($ayuda->presupuesto)
                                                                <div>
                                                                    <strong>Presupuesto:</strong>
                                                                    @php
                                                                        $presupuesto =
                                                                            $ayuda->presupuesto;
                                                                        if (
                                                                            $presupuesto >=
                                                                            1_000_000_000
                                                                        ) {
                                                                            echo number_format(
                                                                                $presupuesto /
                                                                                    1_000_000_000,
                                                                                0,
                                                                                '.',
                                                                                '.',
                                                                            ) .
                                                                                ' mil millones de €';
                                                                        } elseif (
                                                                            $presupuesto >=
                                                                            1_000_000
                                                                        ) {
                                                                            echo number_format(
                                                                                $presupuesto /
                                                                                    1_000_000,
                                                                                0,
                                                                                '.',
                                                                                '.',
                                                                            ) . ' millones de €';
                                                                        } elseif (
                                                                            $presupuesto >= 1_000
                                                                        ) {
                                                                            echo number_format(
                                                                                $presupuesto /
                                                                                    1_000,
                                                                                0,
                                                                                '.',
                                                                                '.',
                                                                            ) . ' mil €';
                                                                        } else {
                                                                            echo number_format(
                                                                                $presupuesto,
                                                                                0,
                                                                                '.',
                                                                                '.',
                                                                            ) . ' €';
                                                                        }
                                                                    @endphp
                                                                </div>
                                                            @endif

                                                            @if ($ayuda->fecha_inicio_periodo || $ayuda->fecha_fin_periodo)
                                                                <div class="flex items-center">
                                                                    <strong>Periodo
                                                                        cubierto:</strong>
                                                                    <div
                                                                        class="inline-flex items-center relative tooltip-trigger ml-1">
                                                                        @if ($ayuda->fecha_inicio_periodo)
                                                                            {{ \Carbon\Carbon::parse($ayuda->fecha_inicio_periodo)->format('d/m/Y') }}
                                                                        @endif
                                                                        @if ($ayuda->fecha_fin_periodo)
                                                                            -
                                                                            {{ \Carbon\Carbon::parse($ayuda->fecha_fin_periodo)->format('d/m/Y') }}
                                                                        @endif
                                                                        <i
                                                                            class="fas fa-info-circle ml-1 text-blue-500 cursor-pointer"></i>
                                                                        <div
                                                                            class="tooltip-content absolute z-10 hidden bg-white p-3 rounded shadow-lg border border-gray-200 text-sm w-64">
                                                                            Las ayudas solo cubrirán
                                                                            gastos dentro de
                                                                            estas fechas.
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </details>
                                            @endif
                                            <div class="flex justify-end">
                                                @if ($ayuda->cuestionarioPrincipal)
                                                    @if ($ayuda->hasPrerequisites ?? false)
                                                        <button
                                                            onclick="showPrerequisitesModalFromData({{ $ayuda->id }}, '{{ $ayuda->yaComenzada ? route('user.beneficiario', ['ayuda_id' => $ayuda->id]) : route('user.form-specific', ['id' => $ayuda->cuestionarioPrincipal->id]) }}', window.prerequisitesData{{ $ayuda->id }})"
                                                            class="btn btn-primary font-bold text-xs sm:text-sm md:text-base whitespace-nowrap px-4">
                                                            {{ $ayuda->yaComenzada ? 'Continuar solicitud' : 'Solicitar ahora' }}
                                                        </button>
                                                    @else
                                                        <a href="{{ $ayuda->yaComenzada ? route('user.beneficiario', ['ayuda_id' => $ayuda->id]) : route('user.form-specific', ['id' => $ayuda->cuestionarioPrincipal->id]) }}"
                                                            class="btn btn-primary font-bold text-xs sm:text-sm md:text-base whitespace-nowrap px-4">
                                                            {{ $ayuda->yaComenzada ? 'Continuar solicitud' : 'Solicitar ahora' }}
                                                        </a>
                                                    @endif
                                                @elseif (!$ayuda->cuestionarioPrincipal)
                                                    <a href="{{ route('user.beneficiario', ['ayuda_id' => $ayuda->id]) }}"
                                                        class="btn btn-primary font-bold text-xs sm:text-sm md:text-base whitespace-nowrap px-4">
                                                        Contratar ayuda
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                @endif
            </div>
        </div>
    </div>

    @include('components.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Funcionalidad de ordenación
            const sortSelect = document.getElementById('sortSelect');
            const ayudasContainer = document.getElementById('ayudasContainer');

            if (ayudasContainer) {
                const ayudas = Array.from(ayudasContainer.children);

                // Prioriza siempre las ayudas con plazo abierto
                function getPlazoPriority(element) {
                    // Usa el atributo data-abierta que viene del PHP
                    const abierta = element.dataset.abierta === '1';
                    // 0 = plazo abierto, 1 = plazo cerrado
                    return abierta ? 0 : 1;
                }

                // Función para ordenar por fecha (default)
                function sortByDate() {
                    ayudas.sort((a, b) => {
                        const prioridadA = getPlazoPriority(a);
                        const prioridadB = getPlazoPriority(b);

                        if (prioridadA !== prioridadB) {
                            return prioridadA - prioridadB; // primero abiertos
                        }

                        const fechaA = parseInt(a.dataset.fecha);
                        const fechaB = parseInt(b.dataset.fecha);

                        // Las que no tienen fecha van al final
                        if (fechaA === 9999999999 && fechaB === 9999999999) return 0;
                        if (fechaA === 9999999999) return 1;
                        if (fechaB === 9999999999) return -1;

                        return fechaA - fechaB;
                    });

                    renderSortedAyudas();
                }

                // Función para ordenar por cantidad
                function sortByAmount() {
                    ayudas.sort((a, b) => {
                        const prioridadA = getPlazoPriority(a);
                        const prioridadB = getPlazoPriority(b);

                        if (prioridadA !== prioridadB) {
                            return prioridadA - prioridadB; // primero abiertos
                        }

                        const cantidadA = parseFloat(a.dataset.cantidad);
                        const cantidadB = parseFloat(b.dataset.cantidad);

                        return cantidadB - cantidadA;
                    });

                    renderSortedAyudas();
                }

                // Función para renderizar las ayudas ordenadas
                function renderSortedAyudas() {
                    // Limpiar el contenedor
                    while (ayudasContainer.firstChild) {
                        ayudasContainer.removeChild(ayudasContainer.firstChild);
                    }

                    // Añadir las ayudas ordenadas
                    ayudas.forEach(ayuda => {
                        ayudasContainer.appendChild(ayuda);
                    });
                }

                // Event listener para el selector de ordenación
                sortSelect.addEventListener('change', function() {
                    if (this.value === 'fecha') {
                        sortByDate();
                    } else if (this.value === 'cantidad') {
                        sortByAmount();
                    }
                });

                // Ordenar por fecha al cargar la página
                sortByDate();
            }

            // Funcionalidad existente de SweetAlerts
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Solicitud enviada!',
                    text: "{{ session('success') }}",
                    confirmButtonText: 'Cerrar'
                });
            @elseif (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Solicitud rechazada',
                    html: "{!! session('error') !!}",
                    confirmButtonText: 'Cerrar'
                });
            @endif

            const flashMessage = localStorage.getItem('flash_success');
            if (flashMessage) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Usuario registrado!',
                    text: flashMessage,
                    confirmButtonText: 'Cerrar'
                });
                localStorage.removeItem('flash_success');
            }
        });

        function acceptInvitation() {
            window.location.href = "/accept-invitation";
        }

        function rejectInvitation() {
            fetch("{{ route('delete.ref.code.cookie') }}", {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('popup').style.display = 'none';
                    } else {
                        alert('Hubo un error al eliminar la cookie.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        async function showPrerequisitesModalFromData(ayudaId, redirectUrl, prerequisitesData) {
            const allRequirementsMet = prerequisitesData.every(req => req.userMeets);
            const missingAnswers = prerequisitesData.filter(req => !req.userMeets && req
                .userAnswer === null);

            if (allRequirementsMet) {
                window.location.href = redirectUrl;
                return;
            } else if (missingAnswers.length > 0) {
                await showAllMissingAnswersModal(ayudaId, missingAnswers, redirectUrl);
                return;
            } else {
                let requirementsText = '';
                prerequisitesData.forEach((req, index) => {
                    const status = req.userMeets ? '✅' : '❌';
                    const targetInfo = req.target_info ?
                        ` (${req.target_info.display_name})` : '';
                    requirementsText += `${status} ${req.name}${targetInfo}\n`;
                });

                Swal.fire({
                    title: 'No cumples los requisitos',
                    text: `Para solicitar esta ayuda necesitas cumplir los siguientes requisitos:\n\n${requirementsText}`,
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                }).then(() => {
                    window.location.reload();
                });
            }
        }

        async function showAllMissingAnswersModal(ayudaId, missingAnswers, redirectUrl) {
            const groupedByPerson = {};
            missingAnswers.forEach(req => {
                const personKey = req.target_info ? req.target_info.display_name : 'Tú';
                if (!groupedByPerson[personKey]) {
                    groupedByPerson[personKey] = [];
                }
                groupedByPerson[personKey].push(req);
            });

            let questionsHtml = '';
            let questionsData = [];

            let hasValidQuestions = false;
            Object.keys(groupedByPerson).forEach(personName => {
                const personReqs = groupedByPerson[personName];
                personReqs.forEach(req => {
                    const qid = req.question_id || req.fallback_question_id;
                    if (qid) {
                        hasValidQuestions = true;
                    }
                });
            });

            if (!hasValidQuestions) {
                let requirementsText = '';
                missingAnswers.forEach((req, index) => {
                    const status = '❌';
                    const targetInfo = req.target_info ?
                        ` (${req.target_info.display_name})` : '';
                    requirementsText += `${status} ${req.name}${targetInfo}\n`;
                });

                Swal.fire({
                    title: 'No cumples los requisitos',
                    text: `Para solicitar esta ayuda necesitas cumplir los siguientes requisitos:\n\n${requirementsText}`,
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                }).then(() => {
                    window.location.reload();
                });
                return;
            }

            Object.keys(groupedByPerson).forEach(personName => {
                const personReqs = groupedByPerson[personName];
                questionsHtml +=
                    `<div class="mb-6 p-4 bg-gray-50 rounded-lg"><h4 class="font-bold text-blue-600 text-lg mb-4">${personName}</h4>`;

                personReqs.forEach((req, index) => {
                    const qid = req.question_id || req.fallback_question_id;
                    if (qid) {
                        questionsHtml +=
                            `<div class="question-group mb-4 p-3 bg-white rounded border" data-question-id="${qid}" data-target-type="${req.target_type}" data-conviviente-type="${req.conviviente_type || ''}">`;
                        questionsHtml +=
                            `<label class="block font-semibold text-gray-700 mb-2">${req.name}</label>`;
                        questionsHtml += `<div class="question-input"></div>`;
                        questionsHtml += `</div>`;

                        questionsData.push({
                            questionId: qid,
                            targetType: req.target_type,
                            convivienteType: req.conviviente_type,
                            name: req.name
                        });
                    }
                });

                questionsHtml += `</div>`;
            });

            const {
                value: answers
            } = await Swal.fire({
                title: 'Información requerida',
                html: `
                    <div class="text-left max-h-96 overflow-y-auto">
                        <p class="mb-4 text-gray-600">Necesitamos la siguiente información para verificar los prerrequisitos:</p>
                        <div id="all-questions-container">
                            ${questionsHtml}
                        </div>
                    </div>
                `,
                width: '700px',
                showCancelButton: true,
                confirmButtonText: 'Guardar respuestas',
                cancelButtonText: 'Cancelar',
                didOpen: async () => {
                    for (let i = 0; i < questionsData.length; i++) {
                        const questionData = questionsData[i];
                        const questionContainer = document.querySelector(
                            `[data-question-id="${questionData.questionId}"] .question-input`
                            );

                        try {
                            const response = await fetch(
                                `/api/ayudas/${ayudaId}/missing-answer/${questionData.questionId}?target_type=${questionData.targetType}&conviviente_type=${questionData.convivienteType}`
                                );
                            const data = await response.json();

                            if (data.success && data.question) {
                                const question = data.question;
                                let inputHtml = '';

                                switch (question.type) {
                                    case 'boolean':
                                        inputHtml = `
                                            <select class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">Selecciona...</option>
                                                <option value="1">Sí</option>
                                                <option value="0">No</option>
                                            </select>
                                        `;
                                        break;
                                    case 'select':
                                        const options = question.options ? (
                                            typeof question.options === 'string' ?
                                            JSON.parse(question.options) : question
                                            .options) : [];
                                        inputHtml =
                                            `<select class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><option value="">Selecciona...</option>`;
                                        options.forEach((option, idx) => {
                                            inputHtml +=
                                                `<option value="${option}">${option}</option>`;
                                        });
                                        inputHtml += `</select>`;
                                        break;
                                    case 'multiple':
                                        const multiOptions = question.options ? (
                                            typeof question.options === 'string' ?
                                            JSON.parse(question.options) : question
                                            .options) : [];
                                        inputHtml = `<div class="space-y-2">`;
                                        multiOptions.forEach((option, idx) => {
                                            inputHtml += `
                                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                                    <input type="checkbox" value="${idx}" class="mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                    <span class="text-gray-700">${option}</span>
                                                </label>
                                            `;
                                        });
                                        inputHtml += `</div>`;
                                        break;
                                    case 'date':
                                        inputHtml =
                                            `<input type="date" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">`;
                                        break;
                                    case 'integer':
                                        inputHtml =
                                            `<input type="number" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" step="1">`;
                                        break;
                                    default:
                                        inputHtml =
                                            `<input type="text" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">`;
                                }

                                questionContainer.innerHTML = inputHtml;
                            }
                        } catch (error) {
                            console.error('Error loading question:', error);
                            questionContainer.innerHTML =
                                '<p class="text-red-500">Error al cargar la pregunta</p>';
                        }
                    }
                },
                preConfirm: async () => {
                    const allAnswers = [];
                    let allValid = true;

                    for (let i = 0; i < questionsData.length; i++) {
                        const questionData = questionsData[i];
                        const questionContainer = document.querySelector(
                            `[data-question-id="${questionData.questionId}"] .question-input`
                            );
                        const input = questionContainer.querySelector('input, select');

                        if (!input) continue;

                        let answer = '';
                        if (questionData.name.includes('múltiple') || questionData.name
                            .includes('multiple')) {
                            const checkboxes = questionContainer.querySelectorAll(
                                'input[type="checkbox"]:checked');
                            answer = Array.from(checkboxes).map(cb => cb.value).join(
                                ',');
                        } else {
                            answer = input.value;
                        }

                        if (!answer) {
                            allValid = false;
                            Swal.showValidationMessage(
                                `Por favor responde: ${questionData.name}`);
                            break;
                        }

                        allAnswers.push({
                            question_id: questionData.questionId,
                            answer: answer,
                            target_type: questionData.targetType,
                            conviviente_type: questionData.convivienteType
                        });
                    }

                    if (!allValid) return false;

                    try {
                        const savePromises = allAnswers.map(answerData =>
                            fetch(`/api/ayudas/${ayudaId}/save-answer`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(answerData)
                            })
                        );

                        const saveResponses = await Promise.all(savePromises);
                        const saveResults = await Promise.all(saveResponses.map(r => r
                            .json()));

                        console.log('All answers saved:', saveResults);

                        const verifyResponse = await fetch(
                            `/api/ayudas/${ayudaId}/verify-prerequisites`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                }
                            });

                        const verifyData = await verifyResponse.json();

                        if (verifyData.userMeetsRequirements) {
                            Swal.fire({
                                title: '¡Perfecto!',
                                text: 'Cumples todos los requisitos. Redirigiendo...',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = redirectUrl;
                            });
                        } else {
                            let errorMessage =
                                'Para solicitar esta ayuda necesitas cumplir todos los prerrequisitos:\n\n';
                            if (verifyData.preRequisitos) {
                                verifyData.preRequisitos.forEach((req) => {
                                    if (!req.userMeets) {
                                        errorMessage +=
                                            `• ${req.error_message || req.name}\n`;
                                    }
                                });
                            }

                            Swal.fire({
                                title: 'No cumples los requisitos',
                                text: errorMessage,
                                icon: 'warning',
                                confirmButtonText: 'Entendido',
                                timer: 8000
                            }).then(() => {
                                window.location.reload();
                            });
                        }

                    } catch (error) {
                        console.error('Error saving answers:', error);
                        Swal.showValidationMessage('Error al guardar las respuestas');
                        return false;
                    }
                }
            });
        }

        async function showMissingAnswerModalFromData(ayudaId, missingReq, redirectUrl) {
            try {
                const response = await fetch(
                    `/api/ayudas/${ayudaId}/missing-answer/${missingReq.question_id}?target_type=${missingReq.target_type}&conviviente_type=${missingReq.conviviente_type || ''}`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });

                const data = await response.json();
                if (data.success) {
                    const question = data.question;
                    const targetInfo = data.target_info;

                    let inputHtml = '';

                    switch (question.type) {
                        case 'boolean':
                            inputHtml = `
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="answer" value="Sí" class="mr-2">
                                        Sí
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="answer" value="No" class="mr-2">
                                        No
                                    </label>
                                </div>
                            `;
                            break;
                        case 'select':
                            const options = JSON.parse(question.options || '[]');
                            inputHtml = `<select name="answer" class="w-full border rounded px-3 py-2">
                                <option value="">Selecciona una opción</option>
                                ${options.map(option => `<option value="${option}">${option}</option>`).join('')}
                            </select>`;
                            break;
                        case 'multiple':
                            const multipleOptions = JSON.parse(question.options || '[]');
                            inputHtml = `<div class="space-y-2">
                                ${multipleOptions.map(option => `
                                        <label class="flex items-center">
                                            <input type="checkbox" name="answer" value="${option}" class="mr-2">
                                            ${option}
                                        </label>
                                    `).join('')}
                            </div>`;
                            break;
                        case 'date':
                            inputHtml =
                                `<input type="date" name="answer" class="w-full border rounded px-3 py-2">`;
                            break;
                        case 'integer':
                            inputHtml =
                                `<input type="number" name="answer" class="w-full border rounded px-3 py-2" placeholder="Ingresa un número">`;
                            break;
                        default:
                            inputHtml =
                                `<input type="text" name="answer" class="w-full border rounded px-3 py-2" placeholder="Ingresa tu respuesta">`;
                    }

                    const targetDescription = targetInfo ? targetInfo.description :
                        'Esta pregunta es necesaria para verificar los requisitos';

                    Swal.fire({
                        title: 'Información requerida',
                        html: `
                            <div class="text-left">
                                <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                                    <p class="text-sm text-blue-700 mb-2">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        ${targetDescription}
                                    </p>
                                </div>
                                <p class="mb-4 font-medium">Pregunta:</p>
                                <div class="bg-gray-50 p-4 rounded mb-4">
                                    <h4 class="font-semibold mb-3 text-lg">${question.text}</h4>
                                    <div class="mt-3">${inputHtml}</div>
                                </div>
                                <p class="text-sm text-gray-600">Esta información es necesaria para verificar si cumples los requisitos de la ayuda.</p>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Responder y continuar',
                        cancelButtonText: 'Cancelar',
                        preConfirm: () => {
                            let answer = null;

                            const checkboxes = document.querySelectorAll(
                                '.swal2-html-container input[type="checkbox"]');
                            const radios = document.querySelectorAll(
                                '.swal2-html-container input[type="radio"]');
                            const select = document.querySelector(
                                '.swal2-html-container select');
                            const textInput = document.querySelector(
                                '.swal2-html-container input[type="text"], .swal2-html-container input[type="date"], .swal2-html-container input[type="number"]'
                                );

                            if (checkboxes.length > 0) {
                                const checkedBoxes = Array.from(checkboxes).filter(cb =>
                                    cb.checked);
                                if (checkedBoxes.length === 0) {
                                    Swal.showValidationMessage(
                                        'Por favor, selecciona al menos una opción');
                                    return false;
                                }
                                answer = checkedBoxes.map(cb => cb.value);
                            } else if (radios.length > 0) {
                                const checkedRadio = document.querySelector(
                                    '.swal2-html-container input[type="radio"]:checked'
                                    );
                                if (!checkedRadio) {
                                    Swal.showValidationMessage(
                                        'Por favor, selecciona una opción');
                                    return false;
                                }
                                answer = checkedRadio.value;
                            } else if (select) {
                                if (!select.value) {
                                    Swal.showValidationMessage(
                                        'Por favor, selecciona una opción');
                                    return false;
                                }
                                answer = select.value;
                            } else if (textInput) {
                                if (!textInput.value.trim()) {
                                    Swal.showValidationMessage(
                                        'Por favor, completa el campo');
                                    return false;
                                }
                                answer = textInput.value;
                            }

                            if (!answer || (Array.isArray(answer) && answer.length ===
                                    0)) {
                                Swal.showValidationMessage(
                                    'Por favor, responde la pregunta');
                                return false;
                            }

                            return answer;
                        }
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Guardando respuesta...',
                                text: 'Por favor espera',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            try {
                                const saveResponse = await fetch(
                                    `/api/ayudas/${ayudaId}/save-answer`, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                            'Accept': 'application/json',
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            question_id: question.id,
                                            answer: result.value,
                                            target_type: missingReq
                                                .target_type,
                                            conviviente_type: missingReq
                                                .conviviente_type
                                        })
                                    });

                                const saveData = await saveResponse.json();

                                if (saveData.success) {
                                    if (saveData.allRequirementsMet) {
                                        Swal.fire({
                                            title: '¡Perfecto!',
                                            text: 'Cumples todos los requisitos. Redirigiendo...',
                                            icon: 'success',
                                            timer: 2000,
                                            showConfirmButton: false
                                        }).then(() => {
                                            window.location.href = redirectUrl;
                                        });
                                    } else {
                                        if (saveData.missingAnswers && saveData
                                            .missingAnswers.length > 0) {
                                            showMissingAnswerModalFromData(ayudaId,
                                                saveData.missingAnswers[0],
                                                redirectUrl);
                                            return;
                                        }

                                        let errorMessage =
                                            'Para solicitar esta ayuda necesitas cumplir todos los prerrequisitos:\n\n';
                                        if (saveData.unmetRequirements && saveData
                                            .unmetRequirements.length > 0) {
                                            saveData.unmetRequirements.forEach((req,
                                                index) => {
                                                errorMessage +=
                                                    `• ${req.error_message}\n`;
                                            });
                                        } else {
                                            errorMessage +=
                                                '• Revisa tu perfil y vuelve a intentarlo.';
                                        }

                                        Swal.fire({
                                            title: 'No cumples los requisitos',
                                            text: errorMessage,
                                            icon: 'warning',
                                            confirmButtonText: 'Entendido',
                                            timer: 8000
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    }
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: saveData.message ||
                                            'Error al guardar la respuesta',
                                        icon: 'error'
                                    });
                                }
                            } catch (error) {
                                console.error('Error saving answer:', error);
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Error al guardar la respuesta',
                                    icon: 'error'
                                });
                            }
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo obtener la información necesaria',
                        icon: 'error'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al procesar la solicitud',
                    icon: 'error'
                });
            }
        }

        async function checkPrerequisites(ayudaId, redirectUrl) {
            Swal.fire({
                title: 'Verificando requisitos...',
                text: 'Por favor espera mientras verificamos si cumples los requisitos',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch(`/api/ayudas/${ayudaId}/has-prerequisites`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.hasPrerequisites) {
                    Swal.update({
                        title: 'Analizando tu perfil...',
                        text: 'Verificando respuestas y requisitos específicos'
                    });

                    await showPrerequisitesModal(ayudaId, redirectUrl, true);
                } else {
                    Swal.close();
                    window.location.href = redirectUrl;
                }
            } catch (error) {
                console.error('Error checking prerequisites:', error);
                Swal.close();
                window.location.href = redirectUrl;
            }
        }

        async function showPrerequisitesModal(ayudaId, redirectUrl, keepSpinner = false) {
            try {
                const response = await fetch(`/api/ayudas/${ayudaId}/verify-prerequisites`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (!keepSpinner) {
                    Swal.close();
                }

                if (data.success) {
                    if (data.userMeetsRequirements) {
                        window.location.href = redirectUrl;
                    } else {
                        const missingAnswers = data.preRequisitos.filter(req => !req.userMeets &&
                            req.userAnswer === null);

                        if (missingAnswers.length > 0) {
                            showAllMissingAnswersModal(ayudaId, missingAnswers, redirectUrl);
                        } else {
                            let requirementsText = '';
                            data.preRequisitos.forEach((req, index) => {
                                const status = req.userMeets ? '✅' : '❌';

                                const targetInfo = req.target_info ?
                                    ` (${req.target_info.display_name})` : '';
                                requirementsText += `${status} ${req.name}${targetInfo}\n`;
                                if (req.description) {
                                    requirementsText += `   ${req.description}\n`;
                                }
                                if (req.userAnswer) {
                                    requirementsText +=
                                        `   Tu respuesta: ${req.userAnswer}\n`;
                                }
                                requirementsText += '\n';
                            });

                            Swal.fire({
                                title: 'Pre-requisitos no cumplidos',
                                html: `
                                    <div class="text-left">
                                        <p class="mb-3">Para solicitar esta ayuda, necesitas cumplir los siguientes requisitos:</p>
                                        <div class="bg-gray-100 p-3 rounded text-sm font-mono whitespace-pre-line">${requirementsText}</div>
                                        <p class="mt-3 text-sm text-gray-600">
                                            • Actualiza tu perfil para cumplir los requisitos<br>
                                            • Completa el cuestionario general si no lo has hecho<br>
                                            • Contacta con soporte si crees que hay un error
                                        </p>
                                    </div>
                                `,
                                icon: 'warning',
                                confirmButtonText: 'Entendido',
                                showCancelButton: true,
                                cancelButtonText: 'Cerrar'
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    }
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Error al verificar pre-requisitos',
                        icon: 'error',
                        confirmButtonText: 'Cerrar'
                    });
                }
            } catch (error) {
                console.error('Error verifying prerequisites:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Error de conexión. Por favor, intenta de nuevo.',
                    icon: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        }

        async function showMissingAnswerModal(ayudaId, missingReq, redirectUrl) {
            try {
                const response = await fetch(
                    `/api/ayudas/${ayudaId}/missing-answer/${missingReq.question_id}?target_type=${missingReq.target_type}&conviviente_type=${missingReq.conviviente_type || ''}`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Accept': 'application/json'
                        }
                    });

                const data = await response.json();

                if (data.success) {
                    const question = data.question;
                    const targetInfo = data.target_info;

                    let inputHtml = '';

                    switch (question.type) {
                        case 'boolean':
                            inputHtml = `
                                <div class="flex gap-4 justify-center">
                                    <label class="flex items-center">
                                        <input type="radio" name="answer" value="1" class="mr-2">
                                        Sí
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="answer" value="0" class="mr-2">
                                        No
                                    </label>
                                </div>
                            `;
                            break;
                        case 'select':
                            const options = Array.isArray(question.options) ? question.options :
                                JSON.parse(question.options || '[]');
                            inputHtml =
                                `<select name="answer" class="w-full border rounded px-3 py-2">`;
                            inputHtml += `<option value="">Selecciona una opción</option>`;
                            options.forEach((option, index) => {
                                inputHtml += `<option value="${index}">${option}</option>`;
                            });
                            inputHtml += `</select>`;
                            break;
                        case 'multiple':
                            const multiOptions = Array.isArray(question.options) ? question
                                .options : JSON.parse(question.options || '[]');
                            inputHtml = `<div class="space-y-2">`;
                            multiOptions.forEach((option, index) => {
                                inputHtml += `
                                    <label class="flex items-center">
                                        <input type="checkbox" name="answer[]" value="${index}" class="mr-2">
                                        ${option}
                                    </label>
                                `;
                            });
                            inputHtml += `</div>`;
                            break;
                        case 'date':
                            inputHtml =
                                `<input type="date" name="answer" class="w-full border rounded px-3 py-2">`;
                            break;
                        case 'integer':
                            inputHtml =
                                `<input type="number" name="answer" class="w-full border rounded px-3 py-2" placeholder="Ingresa un número">`;
                            break;
                        default:
                            inputHtml =
                                `<input type="text" name="answer" class="w-full border rounded px-3 py-2" placeholder="Ingresa tu respuesta">`;
                    }

                    const targetDescription = targetInfo ? targetInfo.description :
                        'Esta pregunta es necesaria para verificar los requisitos';

                    Swal.fire({
                        title: 'Información requerida',
                        html: `
                            <div class="text-left">
                                <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                                    <p class="text-sm text-blue-700 mb-2">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        ${targetDescription}
                                    </p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded mb-4">
                                    <h4 class="font-semibold mb-3 text-lg">${question.text}</h4>
                                    <div class="mt-3">
                                        ${inputHtml}
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600">
                                    Esta información es necesaria para verificar si cumples los requisitos de la ayuda.
                                </p>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Responder y continuar',
                        cancelButtonText: 'Cancelar',
                        preConfirm: () => {
                            let answer = null;

                            const activeInput = document.querySelector(
                                '.swal2-html-container input:not([disabled])');
                            if (activeInput) {
                                if (activeInput.type === 'checkbox') {
                                    answer = activeInput.checked ? 'Sí' : 'No';
                                } else if (activeInput.type === 'radio') {
                                    const checkedRadio = document.querySelector(
                                        '.swal2-html-container input[type="radio"]:checked'
                                        );
                                    answer = checkedRadio ? checkedRadio.value : null;
                                } else {
                                    answer = activeInput.value;
                                }
                            }

                            if (!answer || (Array.isArray(answer) && answer.length ===
                                    0)) {
                                Swal.showValidationMessage(
                                    'Por favor, responde la pregunta');
                                return false;
                            }

                            return answer;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Respuesta registrada',
                                text: 'Tu respuesta ha sido registrada. Verificando requisitos...',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo cargar la pregunta requerida',
                        icon: 'error',
                        confirmButtonText: 'Cerrar'
                    });
                }
            } catch (error) {
                console.error('Error loading missing answer:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Error al cargar la pregunta requerida',
                    icon: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        }
    </script>

    @if (isset($motivo_sin_ayudas) && $motivo_sin_ayudas === 'sin_dni')
        <div id="popupDni"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 text-center">
                <h2 class="text-lg font-semibold mb-4">⚠️ Atención</h2>
                <p class="text-gray-700 mb-6">
                    Actualmente no puedes solicitar ninguna ayuda porque has indicado que no tienes
                    DNI o NIE.
                </p>
                <button onclick="cerrarPopup()"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Entendido
                </button>
            </div>
        </div>

        <script>
            function cerrarPopup() {
                document.getElementById('popupDni').remove();
            }
        </script>
    @endif

    <script>
        @foreach ($ayudas as $ayuda)
            @if ($ayuda->hasPrerequisites ?? false)
                window.prerequisitesData{{ $ayuda->id }} = {!! json_encode($ayuda->prerequisitesInfo ?? []) !!};
            @endif
        @endforeach
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countdownElems = document.querySelectorAll('.ttf-countdown');
            if (!countdownElems.length) return;

            function formatTiempo(restanteSegundos) {
                if (restanteSegundos <= 0) {
                    return '¡últimas horas!';
                }

                const dias = Math.floor(restanteSegundos / 86400);
                let resto = restanteSegundos - dias * 86400;

                const horas = Math.floor(resto / 3600);
                resto -= horas * 3600;

                const minutos = Math.floor(resto / 60);
                const segundos = Math.floor(resto % 60);

                if (dias > 0) {
                    return `${dias}d ${String(horas).padStart(2, '0')}h`;
                }

                return `${String(horas).padStart(2, '0')}h ${String(minutos).padStart(2, '0')}m ${String(segundos).padStart(2, '0')}s`;
            }

            function actualizarCountdowns() {
                const ahora = Math.floor(Date.now() / 1000);

                countdownElems.forEach(el => {
                    const tsFin = parseInt(el.dataset.fechaFin, 10);
                    if (!tsFin) return;

                    const diff = tsFin - ahora;
                    el.textContent = formatTiempo(diff);
                });
            }

            actualizarCountdowns();
            setInterval(actualizarCountdowns, 1000);
        });
    </script>
</body>

</html>
