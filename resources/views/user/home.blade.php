<!DOCTYPE html>
<html lang="es">

<head>
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
        <x-clarity-analytics />
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
            --primary-color: #40d4b0;
            --primary-dark: #59edca;
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
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .help-card {
            border-left: 4px solid var(--primary-color);
        }

        .list-group-item.active {
            background-color: #54debd;
            border-color: var(--primary-color);
        }

        .alert-success {
            background-color: rgba(89, 237, 202, 0.2);
            border-color: var(--primary-color);
        }

        .alert-open-deadline {
            border-width: 2px;
            border-color: #f87171 !important;
            /* rojo tipo Tailwind red-400 */
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
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        h1 {
            padding: 20px;
            padding-top: 30px;
            font-size: 30px !important;
        }

        .primero {
            margin-bottom: 15px;
        }

        .sort-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1.5rem;
            align-items: center;
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

        @media (min-width: 768px) {
            .sidebar {
                margin-bottom: 2rem;
            }
        }

        @media (max-width: 767px) {
            .sort-container {
                justify-content: flex-start;
                margin-top: 1rem;
            }
        }
    </style>
</head>

<body>

    @include('components.header')
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

    @php
        $mostrarBannerBonoCultural = $contrato_bono_cultural ?? false;
    @endphp

    @if ($mostrarBannerBonoCultural)
        <div class="alert alert-info  rounded shadow" style="background-color: #3C3A60;">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-white mb-1">🎁 ¡Invita y gana dinero
                        ilimitado!</h3>
                    <p class="text-sm text-white">
                        Por cada persona que contrate el Bono Cultural Joven gracias a tu código, te
                        damos 5 €. ¡Así de
                        fácil! 🙌
                    </p>

                </div>
                <button onclick="openReferralModal()"
                    class="bg-green-400 hover:bg-yellow-500 text-black px-4 py-2 rounded-lg font-bold shadow-md">
                    💰 Invita y gana 5 €
                </button>

            </div>
        </div>
    @endif
    <div class="container-fluid px-4">
        <div class="flex flex-col md:flex-row">
            <!-- Sidebar -->
            <div class="col-12 col-md-3 sidebar mb-3">
                <div class="list-group">

                    @php
                        function badgeClass($count)
                        {
                            if ($count <= 2) {
                                return 'bg-warning';
                            } elseif ($count <= 4) {
                                return 'bg-orange';
                            } else {
                                return 'bg-danger';
                            }
                        }
                        function badgeClassAyudas($count)
                        {
                            if ($count <= 5) {
                                return 'bg-green-600';
                            } elseif ($count <= 10) {
                                return 'bg-orange';
                            } else {
                                return 'bg-danger';
                            }
                        }
                        function badgeText($count)
                        {
                            return $count > 10 ? '10+' : $count;
                        }

                        // Detectar si estamos en la ruta de ayudassolicitadas
                        $onSolicitadas = request()->is('ayudas-solicitadas*');
                    @endphp

                    {{-- Ayudas disponibles --}}
                    <a href="{{ route('user.home') }}"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $onSolicitadas ? '' : 'active' }}">
                        Ayudas disponibles
                        @if (!empty($ayudas->count()))
                            <span
                                class="badge {{ badgeClassAyudas($ayudas->count()) }} rounded-pill">
                                {{ badgeText($ayudas->count()) }}
                            </span>
                        @endif
                    </a>

                    {{-- Ayudas solicitadas con badge --}}
                    <a href="{{ url('ayudas-solicitadas') }}"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $onSolicitadas ? 'active' : '' }}">
                        Ayudas solicitadas
                        @if (!empty($documentacionCount))
                            <span class="badge {{ badgeClass($documentacionCount) }} rounded-pill">
                                {{ badgeText($documentacionCount) }}
                            </span>
                        @endif
                    </a>

                </div>
            </div>

            <!-- Main Content -->
            <div class="w-full lg:w-3/5">

                @if (isset($motivo_sin_ayudas) && $motivo_sin_ayudas === 'sin_dni')
                    <div class="flex flex-col justify-center items-center mb-3 mt-2 text-center">
                        <h2 class="mb-3 text-2xl sm:text-3xl md:text-4xl font-bold">⚠️ Necesitas DNI
                            o NIE para
                            solicitar ayudas</h2>
                        <h2 class="text-base sm:text-xl">
                            Actualmente no podemos mostrarte ayudas porque has indicado que no
                            tienes DNI o NIE.
                        </h2>
                    </div>
                @elseif ($ayudas->isEmpty())
                    <div class="flex flex-col justify-center items-center mb-3 mt-2 text-center">
                        <h2 class="mb-3 text-2xl sm:text-3xl md:text-4xl font-bold">No hemos
                            encontrado ayudas para
                            ti</h2>
                        <h2 class="text-base sm:text-xl">
                            En este momento no hay ayudas disponibles que se ajusten a tu perfil. Te
                            avisaremos si se
                            abre alguna nueva.
                        </h2>
                    </div>
                @else
                    <div class="flex flex-col justify-center items-center mb-3 mt-2 text-center">
                        <h2 class="mb-3 text-2xl sm:text-3xl md:text-4xl font-bold">¡Increíble!</h2>
                        <h2 class="text-base sm:text-2lg md:text-xl">
                            Hemos detectado
                            {{ $ayudas->count() == 1 ? '1 ayuda pública' : $ayudas->count() . ' ayudas públicas' }}
                            que
                            puedes pedir, por un total de
                            <strong>{{ number_format($cuantia_total, 0, ',', '.') }} €</strong>
                        </h2>
                    </div>

                    <div class="alert alert-success px-4 py-3">
                        <div class="flex flex-row items-center gap-4">
                            <img src="{{ asset('imagenes/estilo-trofeo-plano.png') }}"
                                alt="Trofeo" class="w-20 h-auto object-cover" />

                            <div class="text-center w-full">
                                <h2 class="text-base sm:text-xl md:text-2xl font-bold">
                                    Puedes conseguir hasta
                                    <span
                                        class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-emerald-600">
                                        {{ number_format($cuantia_total, 0, ',', '.') }} €
                                    </span>
                                </h2>

                                <h2 class="text-base sm:text-lg md:text-xl mb-0">En menos de 15
                                    minutos</h2>
                            </div>

                        </div>
                    </div>

                    <div
                        class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
                        <h2
                            class="mt-4 sm:mb-5 mb-3 text-start text-2xl sm:text-3xl md:text-4xl font-bold">
                            Ayudas
                            disponibles</h2>
                        <div class="sort-container">
                            <span class="sort-label">Ordenar por:</span>
                            <select id="sortSelect" class="sort-select">
                                <option value="fecha">Fecha de cierre (más cercana)</option>
                                <option value="cantidad">Cantidad (mayor a menor)</option>
                            </select>
                        </div>
                    </div>

                    <p class="text-left text-base sm:text-lg md:text-xl mt-4 mb-4">
                        👇 Empieza por la que prefieras. Solo necesitas completar un pequeño
                        formulario.
                    </p>

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

                                <div class="card-body px-1">
                                    <h3
                                        class="card-title text-center md:text-left text-xl md:text-2xl font-bold mb-3 md:mb-4 mt-10 md:mt-8">
                                        {{ $ayuda->nombre_ayuda }}
                                    </h3>
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
                                                            Sin fecha de fin definida
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div
                                        class="flex flex-col md:flex-row justify-between items-start gap-4">
                                        <div
                                            class="flex flex-row items-center justify-between md:justify-start w-full md:w-auto gap-4">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="md:border-2 rounded-full p-1.5 sm:p-2 md:p-2 bg-white shadow md:shadow-lg">
                                                    <img src="{{ asset('imagenes/organos/' . $ayuda->organo->imagen) }}"
                                                        alt="{{ $ayuda->organo->nombre_organismo }}"
                                                        class="w-8 h-8 sm:w-10 sm:h-10 md:w-14 md:h-14 lg:w-16 lg:h-16 object-contain rounded-full" />
                                                </div>
                                            </div>
                                            <div
                                                class="md:hidden text-right pr-2 whitespace-nowrap">
                                                <p class="text-base sm:text-lg">Cuantía hasta: <span
                                                        class="text-lg sm:text-xl font-bold">
                                                        {{ $ayuda->cuantia_usuario == 0 ? 'Ilimitado' : $ayuda->getDineroFormateado($ayuda->cuantia_usuario, 0) }}
                                                    </span></p>
                                            </div>

                                            <div class="hidden md:block">
                                                <p class="text-base sm:text-lg md:text-xl">Cuantía
                                                    hasta: <span
                                                        class="text-lg sm:text-xl md:text-2xl lg:text-3xl font-bold">
                                                        {{ $ayuda->cuantia_usuario == 0 ? 'Ilimitado' : $ayuda->getDineroFormateado($ayuda->cuantia_usuario, 0) }}
                                                    </span></p>
                                            </div>
                                        </div>
                                        <div
                                            class="w-full md:w-auto p-2 sm:p-3 rounded-2xl alert alert-success-none-bg {{ $ayuda->estado_plazo }}">
                                            <div
                                                class="flex flex-row gap-2 sm:gap-3 items-center md:items-start justify-center md:justify-start">
                                                <i class="fa-solid fa-calendar-check text-sm sm:text-xl pt-1 md:pt-0"
                                                    style="color: #63E6BE;"></i>
                                                <div class="md:hidden text-xs sm:text-sm">
                                                    <span>
                                                        Inicio:
                                                        {{ \Carbon\Carbon::parse($ayuda->fecha_inicio)->format('d/m/Y') }}
                                                        |
                                                        Fin: @if ($ayuda->fecha_fin)
                                                            {{ \Carbon\Carbon::parse($ayuda->fecha_fin)->format('d/m/Y') }}
                                                        @else
                                                            Sin fecha de fin
                                                        @endif
                                                    </span>
                                                </div>
                                                <div
                                                    class="hidden md:flex flex-col text-xs sm:text-sm md:text-base lg:text-xl">
                                                    <p>
                                                        Fecha de inicio:
                                                        {{ \Carbon\Carbon::parse($ayuda->fecha_inicio)->format('d/m/Y') }}
                                                    </p>
                                                    <p>
                                                        Fecha de fin:
                                                        @if ($ayuda->fecha_fin)
                                                            {{ \Carbon\Carbon::parse($ayuda->fecha_fin)->format('d/m/Y') }}
                                                        @else
                                                            Sin fecha de fin
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="flex flex-col md:flex-row justify-between items-center gap-3 mt-3">
                                        @if (
                                            $ayuda->description ||
                                                $ayuda->presupuesto ||
                                                $ayuda->fecha_inicio_periodo ||
                                                $ayuda->fecha_fin_periodo)
                                            <details class="group w-full md:w-auto">
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
                                                            stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </summary>
                                                <div class="mt-2 p-3 rounded-lg text-lg"
                                                    style="background-color:rgba(199, 255, 241, 0.12)">
                                                    @if ($ayuda->description)
                                                        <div class="mb-3">
                                                            <strong>Descripción:</strong>
                                                            {!! $ayuda->description !!}
                                                        </div>
                                                    @endif

                                                    <div class="flex flex-wrap items-center gap-4">
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
                                                                        ) . ' mil millones de €';
                                                                    } elseif (
                                                                        $presupuesto >= 1_000_000
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
                                                                            $presupuesto / 1_000,
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
                                                                <strong>Periodo cubierto:</strong>
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
                                                                        gastos dentro de estas
                                                                        fechas.
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </details>
                                        @endif
                                        <div class="w-full md:w-auto text-center md:text-right">
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
                        @endforeach
                    </div>

                    <style>
                        .tooltip-trigger {
                            position: relative;
                            display: inline-flex;
                            align-items: center;
                        }

                        .tooltip-trigger .tooltip-content {
                            bottom: 100%;
                            left: 50%;
                            transform: translateX(-50%);
                        }

                        .tooltip-trigger:hover .tooltip-content,
                        .tooltip-trigger:focus .tooltip-content {
                            display: block !important;
                        }
                    </style>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Añadir eventos para los tooltips
                            const tooltipTriggers = document.querySelectorAll('.tooltip-trigger');

                            tooltipTriggers.forEach(trigger => {
                                const icon = trigger.querySelector('i');
                                const tooltip = trigger.querySelector('.tooltip-content');

                                // Mostrar tooltip al hacer hover en el icono o en el texto
                                icon.addEventListener('mouseenter', () => tooltip.classList.remove(
                                    'hidden'));
                                icon.addEventListener('mouseleave', () => tooltip.classList.add(
                                    'hidden'));
                                icon.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    tooltip.classList.toggle('hidden');
                                });

                                // Mostrar tooltip al hacer hover en el contenedor
                                trigger.addEventListener('mouseenter', () => tooltip.classList.remove(
                                    'hidden'));
                                trigger.addEventListener('mouseleave', () => tooltip.classList.add(
                                    'hidden'));

                                // Cerrar tooltip al hacer click fuera
                                document.addEventListener('click', (e) => {
                                    if (!trigger.contains(e.target)) {
                                        tooltip.classList.add('hidden');
                                    }
                                });
                            });
                        });
                    </script>
                @endif
            </div>
        </div>
    </div>

    @include('components.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

                function sortByDate() {
                    ayudas.sort((a, b) => {
                        const prioridadA = getPlazoPriority(a);
                        const prioridadB = getPlazoPriority(b);

                        if (prioridadA !== prioridadB) {
                            return prioridadA - prioridadB; // primero abiertos/pronto
                        }

                        const fechaA = parseInt(a.dataset.fecha);
                        const fechaB = parseInt(b.dataset.fecha);

                        if (fechaA === 9999999999 && fechaB === 9999999999) return 0;
                        if (fechaA === 9999999999) return 1;
                        if (fechaB === 9999999999) return -1;

                        return fechaA - fechaB;
                    });

                    renderSortedAyudas();
                }

                function sortByAmount() {
                    ayudas.sort((a, b) => {
                        const prioridadA = getPlazoPriority(a);
                        const prioridadB = getPlazoPriority(b);

                        if (prioridadA !== prioridadB) {
                            return prioridadA - prioridadB; // primero abiertos/pronto
                        }

                        const cantidadA = parseFloat(a.dataset.cantidad);
                        const cantidadB = parseFloat(b.dataset.cantidad);

                        return cantidadB - cantidadA;
                    });

                    renderSortedAyudas();
                }

                function renderSortedAyudas() {
                    while (ayudasContainer.firstChild) {
                        ayudasContainer.removeChild(ayudasContainer.firstChild);
                    }

                    ayudas.forEach(ayuda => {
                        ayudasContainer.appendChild(ayuda);
                    });
                }

                sortSelect.addEventListener('change', function() {
                    if (this.value === 'fecha') {
                        sortByDate();
                    } else if (this.value === 'cantidad') {
                        sortByAmount();
                    }
                });

                sortByDate();
            }

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
            @elseif (request()->cookie('ayuda_duplicada'))
                Swal.fire({
                    icon: 'error',
                    title: '¡Ayuda duplicada!',
                    text: "Ya tienes una ayuda solicitada para esta ayuda.",
                    confirmButtonText: 'Cerrar'
                });
                document.cookie =
                    'ayuda_duplicada=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
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
            } else if (missingAnswers.length > 0) {
                showAllMissingAnswersModal(ayudaId, missingAnswers, redirectUrl);
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
                    confirmButtonText: 'Entendido',
                    cancelButtonText: 'Cerrar'
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
                    `<div class="mb-5 p-4 bg-light rounded-lg border-start border-4 border-primary">`;
                questionsHtml +=
                    `<h4 class="font-weight-bold text-primary mb-3 d-flex align-items-center">`;
                questionsHtml += `<i class="fas fa-user-circle me-2"></i>${personName}`;
                questionsHtml += `</h4>`;

                personReqs.forEach((req, index) => {
                    const qid = req.question_id || req.fallback_question_id;
                    if (qid) {
                        questionsHtml +=
                            `<div class="question-group mb-4 p-3 bg-white rounded border shadow-sm" data-question-id="${qid}" data-target-type="${req.target_type}" data-conviviente-type="${req.conviviente_type || ''}">`;
                        questionsHtml +=
                            `<label class="font-weight-bold text-dark mb-2 d-block">${req.name}</label>`;
                        questionsHtml += `<div class="question-input"></div>`;
                        questionsHtml += `</div>`;

                        questionsData.push({
                            questionId: qid,
                            targetType: req.target_type,
                            convivienteType: req.conviviente_type,
                            name: req.name
                        });
                    } else {
                        console.warn('No question ID found for requirement:', req);
                    }
                });

                questionsHtml += `</div>`;
            });

            const {
                value: answers
            } = await Swal.fire({
                title: '<i class="fas fa-clipboard-list text-primary me-2"></i>Información requerida',
                html: `
                    <div class="text-left">
                        <div class="alert alert-info d-flex align-items-center mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <span>Necesitamos la siguiente información para verificar los prerrequisitos:</span>
                        </div>
                        <div id="all-questions-container" style="max-height: 400px; overflow-y: auto;">
                            ${questionsHtml}
                        </div>
                    </div>
                `,
                width: '750px',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-save me-2"></i>Guardar respuestas',
                cancelButtonText: '<i class="fas fa-times me-2"></i>Cancelar',
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
                                            <select class="form-control form-control-lg">
                                                <option value="">Selecciona una opción...</option>
                                                <option value="1">Sí</option>
                                                <option value="0">No</option>
                                            </select>
                                        `;
                                        break;
                                    case 'select':
                                        const options = question.options ? (
                                            typeof question.options ===
                                            'string' ? JSON.parse(question
                                            .options) : question.options
                                        ) : [];
                                        inputHtml =
                                            `<select class="form-control form-control-lg"><option value="">Selecciona una opción...</option>`;
                                        options.forEach((option, idx) => {
                                            inputHtml +=
                                                `<option value="${option}">${option}</option>`;
                                        });
                                        inputHtml += `</select>`;
                                        break;
                                    case 'multiple':
                                        const multiOptions = question.options ? (
                                            typeof question.options ===
                                            'string' ? JSON.parse(question
                                            .options) : question.options
                                        ) : [];
                                        inputHtml = `<div class="form-check-group">`;
                                        multiOptions.forEach((option, idx) => {
                                            inputHtml += `
                                                <div class="form-check p-2 border rounded mb-2 hover-bg-light">
                                                    <input class="form-check-input" type="checkbox" value="${idx}" id="q${questionData.questionId}_${idx}">
                                                    <label class="form-check-label w-100" for="q${questionData.questionId}_${idx}">${option}</label>
                                                </div>
                                            `;
                                        });
                                        inputHtml += `</div>`;
                                        break;
                                    case 'date':
                                        inputHtml =
                                            `<input type="date" class="form-control form-control-lg">`;
                                        break;
                                    case 'integer':
                                        inputHtml =
                                            `<input type="number" class="form-control form-control-lg" step="1" placeholder="Ingresa un número">`;
                                        break;
                                    default:
                                        inputHtml =
                                            `<input type="text" class="form-control form-control-lg" placeholder="Ingresa tu respuesta">`;
                                }

                                questionContainer.innerHTML = inputHtml;
                            }
                        } catch (error) {
                            console.error('Error loading question:', error);
                            questionContainer.innerHTML =
                                '<p class="text-danger">Error al cargar la pregunta</p>';
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
                            .includes(
                                'multiple')) {
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
                const qid = missingReq.question_id || missingReq.fallback_question_id;
                if (!qid) {
                    const msg = missingReq.error_message || missingReq.name ||
                        'Falta información para determinar la pregunta a realizar.';
                    Swal.fire({
                        title: 'Información insuficiente',
                        text: msg,
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }
                const response = await fetch(
                    `/api/ayudas/${ayudaId}/missing-answer/${qid}?target_type=${missingReq.target_type}&conviviente_type=${missingReq.conviviente_type || ''}`, {
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

                            // Manejar diferentes tipos de input
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
                                // Múltiples checkboxes
                                const checkedBoxes = Array.from(checkboxes).filter(cb =>
                                    cb.checked);
                                if (checkedBoxes.length === 0) {
                                    Swal.showValidationMessage(
                                        'Por favor, selecciona al menos una opción');
                                    return false;
                                }
                                answer = checkedBoxes.map(cb => cb.value);
                            } else if (radios.length > 0) {
                                // Radio buttons
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
                                // Select dropdown
                                if (!select.value) {
                                    Swal.showValidationMessage(
                                        'Por favor, selecciona una opción');
                                    return false;
                                }
                                answer = select.value;
                            } else if (textInput) {
                                // Text, date, number inputs
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
                            // Mostrar spinner mientras se guarda
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
                                // Guardar la respuesta
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
                                        // Todos los requisitos cumplidos
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
                                            showAllMissingAnswersModal(ayudaId, saveData
                                                .missingAnswers,
                                                redirectUrl);
                                            return;
                                        }

                                        // No cumple los prerrequisitos
                                        let errorMessage =
                                            'Para solicitar esta ayuda necesitas cumplir todos los prerrequisitos:\n\n';

                                        if (saveData.unmetRequirements && saveData
                                            .unmetRequirements
                                            .length > 0) {
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
                // Verificar si la ayuda tiene pre-requisitos
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
                // En caso de error, proceder directamente
                window.location.href = redirectUrl;
            }
        }

        // Función para mostrar el modal de pre-requisitos
        async function showPrerequisitesModal(ayudaId, redirectUrl, keepSpinner = false) {
            try {
                // Verificar pre-requisitos
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
                    // Error en la verificación
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
                const qid = missingReq.question_id || missingReq.fallback_question_id;
                if (!qid) {
                    const msg = missingReq.error_message || missingReq.name ||
                        'Falta información para determinar la pregunta a realizar.';
                    Swal.fire({
                        title: 'Información insuficiente',
                        text: msg,
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }
                const response = await fetch(
                    `/api/ayudas/${ayudaId}/missing-answer/${qid}?target_type=${missingReq.target_type}&conviviente_type=${missingReq.conviviente_type || ''}`, {
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
                                JSON.parse(question
                                    .options || '[]');
                            inputHtml =
                                `<select name="answer" class="w-full border rounded px-3 py-2">`;
                            inputHtml += `<option value="">Selecciona una opción</option>`;
                            options.forEach((option, index) => {
                                inputHtml += `<option value="${option}">${option}</option>`;
                            });
                            inputHtml += `</select>`;
                            break;
                        case 'multiple':
                            const multiOptions = Array.isArray(question.options) ? question
                                .options : JSON.parse(
                                    question.options || '[]');
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
                                            showMissingAnswerModal(ayudaId, saveData
                                                .missingAnswers[0],
                                                redirectUrl);
                                            return;
                                        }

                                        let errorMessage =
                                            'Para solicitar esta ayuda necesitas cumplir todos los prerrequisitos:\n\n';
                                        if (saveData.unmetRequirements && saveData
                                            .unmetRequirements
                                            .length > 0) {
                                            saveData.unmetRequirements.forEach((
                                            req) => {
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
                                console.error('Error saving answer (direct):', error);
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

    <!-- Animaciones y estilos personalizados -->
    <style>
        @keyframes zoomFadeIn {
            0% {
                opacity: 0;
                transform: scale(0.95);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-zoom {
            animation: zoomFadeIn 0.3s ease-out forwards;
        }

        .share-btn:hover {
            transform: scale(1.05);
            transition: transform 0.2s ease-in-out;
        }
    </style>

    <!-- Modal compartir referido -->
    <div id="referralModal"
        class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-xl shadow-lg max-w-lg w-full p-6 relative animate-zoom">
            <!-- Botón cerrar -->
            <button onclick="closeReferralModal()"
                class="absolute top-2 right-3 text-gray-500 hover:text-red-500 text-xl">&times;</button>

            <!-- Título -->
            <h2 class="text-xl font-semibold text-center mb-4">Invita a tus amigos y gana
                recompensas</h2>

            <p class="text-gray-700 mb-2 text-center">Comparte este enlace para que se registren
                contigo:</p>

            <!-- Campo con enlace -->
            <div class="flex items-center border rounded-lg px-4 py-2 bg-gray-50 mb-4">
                <input id="referralLink" type="text"
                    class="w-full bg-transparent focus:outline-none" readonly value="">
                <button onclick="copyReferralLink()"
                    class="text-sm text-blue-600 font-medium ml-2 hover:underline">Copiar</button>
            </div>

            <!-- Botones redes -->
            <div class="grid grid-cols-2 gap-3 text-center text-white text-sm font-medium">
                <a id="shareWhatsapp"
                    class="share-btn flex items-center justify-center bg-green-500 py-2 rounded-lg hover:bg-green-600">
                    <i class="fab fa-whatsapp mr-2"></i> WhatsApp
                </a>
                <a id="shareTelegram"
                    class="share-btn flex items-center justify-center bg-blue-400 py-2 rounded-lg hover:bg-blue-500">
                    <i class="fab fa-telegram-plane mr-2"></i> Telegram
                </a>
                <a id="shareX"
                    class="share-btn flex items-center justify-center bg-black py-2 rounded-lg hover:bg-gray-800">
                    <i class="fab fa-x-twitter mr-2"></i> X (Twitter)
                </a>
                <a id="shareEmail"
                    class="share-btn flex items-center justify-center bg-indigo-500 py-2 rounded-lg hover:bg-indigo-600">
                    <i class="fas fa-envelope mr-2"></i> Email
                </a>
            </div>
        </div>
        <div id="copy-message"
            class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gray-900 text-white text-sm px-6 py-3 rounded-xl shadow-lg z-[999999] transition-opacity duration-300">
            ✅ ¡Enlace copiado!
        </div>

    </div>
    <script>
        function openReferralModal() {
            const userRefCode = "{{ auth()->user()->ref_code ?? '' }}";
            const url = `${window.location.origin}/invite?ref=${userRefCode}`;

            document.getElementById("referralLink").value = url;

            document.getElementById("shareWhatsapp").href =
                `https://wa.me/?text=${encodeURIComponent("Regístrate con mi enlace: " + url)}`;
            document.getElementById("shareTelegram").href =
                `https://t.me/share/url?url=${encodeURIComponent(url)}&text=Regístrate conmigo`;
            document.getElementById("shareX").href =
                `https://twitter.com/intent/tweet?text=${encodeURIComponent("Regístrate con mi enlace: " + url)}`;
            document.getElementById("shareEmail").href =
                `mailto:?subject=Únete a Tu Trámite Fácil&body=Regístrate aquí: ${url}`;

            document.getElementById("referralModal").classList.remove("hidden");
        }

        function closeReferralModal() {
            document.getElementById("referralModal").classList.add("hidden");
        }

        function copyReferralLink() {
            const input = document.getElementById("referralLink");
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value).then(() => {
                const message = document.getElementById("copy-message");
                message.classList.remove("hidden");

                setTimeout(() => {
                    message.classList.add("hidden");
                }, 700);
            });
        }
    </script>

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
