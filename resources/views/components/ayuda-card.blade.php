@props([
    'ayudaSolicitada',
    'estadoPrincipal' => [],
    'estadoConvivientes' => [],
    'nConvivientes' => 0,
    'convivientes' => collect(),
    'sectorAyuda' => null,
    'preFormConviviente' => false,
    'preguntasPreForm' => collect(),
])

<style>
    /* clase para el título: centrado */
    .ayuda-title {
        text-align: center;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-wrap: break-word;
        line-height: 1.15;
    }

    @media (min-width: 768px) {
        .ayuda-title-md-pad {
            padding-left: 120px;
            text-align: center;
        }
    }
</style>

<div class="ayuda-card-wrapper mb-1">
    <div class="card bg-green-50 border-2 border-gray-300 rounded-lg shadow-md p-4"
        data-ayuda-id="{{ $ayudaSolicitada->id }}" style="position: relative; z-index: 1;">
        <div class="relative mt-3 mb-4 flex flex-col sm:block text-center sm:text-center">
            <!-- Imagen: centrada en móvil, izquierda en escritorio -->
            <div
                class=" sm:mb-0 sm:absolute sm:left-1 sm:top-1/2 sm:transform sm:-translate-y-1/2 sm:ml-6 flex justify-center sm:justify-start">
                <img src="{{ asset('imagenes/organos/' . $ayudaSolicitada->ayuda->organo->imagen) }}"
                    alt="{{ $ayudaSolicitada->ayuda->organo->nombre_organismo }}"
                    class="w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 object-contain rounded-full" />
            </div>
            <!-- Título centrado siempre -->
            <h5 class="text-lg sm:text-xl font-semibold break-words ayuda-title ayuda-title-md-pad">
                {{ $ayudaSolicitada->ayuda->nombre_ayuda }}
            </h5>
        </div>
        {{-- Fila 1: Descripción completa --}}
        @if ($ayudaSolicitada->ayuda->description)
            <!-- Versión móvil (texto truncado + botón) -->
            <div x-data="{ expanded: false }"
                class="rounded-lg text-sm text-gray-800 leading-relaxed bg-cyan-50/20 relative md:hidden">
                <span class="font-semibold">Descripción:</span>
                <div x-show="!expanded">
                    {!! Str::limit(strip_tags($ayudaSolicitada->ayuda->description), 150, '...') !!}
                </div>
                <div x-show="expanded" x-cloak>
                    {!! $ayudaSolicitada->ayuda->description !!}
                </div>
                <div class="text-end mt-2">
                    <button @click="expanded = !expanded"
                        class="text-blue-600 hover:underline text-xs font-medium">
                        <span x-show="!expanded">Ver más</span>
                        <span x-show="expanded">Ver menos</span>
                    </button>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

            <!-- Versión escritorio (texto completo sin botón) -->
            <div
                class="hidden md:block rounded-lg text-sm text-gray-800 leading-relaxed bg-cyan-50/20">
                <span class="font-semibold">Descripción:</span>
                {!! $ayudaSolicitada->ayuda->description !!}
            </div>
        @endif

        {{-- Fila 2: 3 columnas --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-1 ">

            {{-- Columna 1: Fechas generales --}}
            @if ($ayudaSolicitada->ayuda->fecha_inicio || $ayudaSolicitada->ayuda->fecha_fin)
                <div class="rounded-lg text-sm text-gray-800 bg-cyan-50/20 leading-relaxed">
                    @if ($ayudaSolicitada->ayuda->fecha_inicio)
                        <div>
                            <span class="font-semibold">📅 Inicio del plazo de
                                solicitud:</span>
                            {{ \Carbon\Carbon::parse($ayudaSolicitada->ayuda->fecha_inicio)->format('d/m/Y') }}
                        </div>
                    @endif
                    @if ($ayudaSolicitada->ayuda->fecha_fin)
                        <div>
                            <span class="font-semibold">⛔ Fin del plazo de solicitud:</span>
                            {{ \Carbon\Carbon::parse($ayudaSolicitada->ayuda->fecha_fin)->format('d/m/Y') }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- Columna 2: Presupuesto y periodo cubierto --}}
            @if (
                $ayudaSolicitada->ayuda->presupuesto ||
                    $ayudaSolicitada->ayuda->fecha_inicio_periodo ||
                    $ayudaSolicitada->ayuda->fecha_fin_periodo)
                <div class="rounded-lg text-sm text-gray-800 bg-cyan-50/20 leading-relaxed">
                    {{-- Presupuesto --}}
                    @if ($ayudaSolicitada->ayuda->presupuesto)
                        <div>
                            <span class="font-semibold">Presupuesto:</span>
                            @php
                                $presupuesto = $ayudaSolicitada->ayuda->presupuesto;
                                if ($presupuesto >= 1_000_000_000) {
                                    echo number_format($presupuesto / 1_000_000_000, 0, '.', '.') .
                                        ' mil millones de €';
                                } elseif ($presupuesto >= 1_000_000) {
                                    echo number_format($presupuesto / 1_000_000, 0, '.', '.') .
                                        ' millones de €';
                                } elseif ($presupuesto >= 1_000) {
                                    echo number_format($presupuesto / 1_000, 0, '.', '.') .
                                        ' mil €';
                                } else {
                                    echo number_format($presupuesto, 0, '.', '.') . ' €';
                                }
                            @endphp
                        </div>
                    @endif

                    {{-- Periodo cubierto --}}
                    @if ($ayudaSolicitada->ayuda->fecha_inicio_periodo || $ayudaSolicitada->ayuda->fecha_fin_periodo)
                        <div class="text-left">
                            <span class="font-semibold">Ayuda aplicable al periodo:</span>
                            <span class="block md:hidden"> </span>
                            @if ($ayudaSolicitada->ayuda->fecha_inicio_periodo)
                                {{ \Carbon\Carbon::parse($ayudaSolicitada->ayuda->fecha_inicio_periodo)->format('d/m/Y') }}
                            @endif
                            @if ($ayudaSolicitada->ayuda->fecha_fin_periodo)
                                -
                                {{ \Carbon\Carbon::parse($ayudaSolicitada->ayuda->fecha_fin_periodo)->format('d/m/Y') }}
                            @endif
                            <i class="fas fa-info-circle text-blue-500 cursor-pointer ml-1"
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Las ayudas solo cubrirán gastos dentro de estas fechas."></i>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Columna 3: Enlaces relacionados --}}
            @if ($ayudaSolicitada->ayuda->enlaces)
                <div
                    class="rounded-lg text-sm text-gray-800 bg-cyan-50/20 leading-relaxed flex flex-col gap-1">
                    <span class="font-semibold">Enlaces relacionados:</span>
                    @foreach ($ayudaSolicitada->ayuda->enlaces as $enlace)
                        <a href="{{ $enlace->url }}" class="text-blue-600 hover:underline text-sm"
                            target="_blank">
                            {{ $enlace->texto_boton }}
                        </a>
                    @endforeach
                </div>
            @endif

        </div>

        <!-- Barra de progreso y estado (según estados OPx de contratacion_estado_contratacion) -->
        @php
            $displayData = $ayudaSolicitada->getAyudaCardDisplayData();
            $porcentaje = $displayData['porcentaje'];
        @endphp
        <div class="mt-3 relative">
            <div class="progress relative bg-gray-100 rounded overflow-hidden h-4"
                style="position: relative;">
                <div class="progress-bar absolute left-0 top-0 h-full bg-[#54debd]"
                    style="width: 0%; transition: width 1s ease;">
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const progressBar = document.querySelector(
                        '[data-ayuda-id="{{ $ayudaSolicitada->id }}"] .progress-bar');
                    if (progressBar) {
                        setTimeout(() => {
                            progressBar.style.width = '{{ $porcentaje }}%';
                        }, 100); // Pequeña pausa para que la animación sea visible
                    }
                });
            </script>

            <div class="text-center mt-2">
                Porcentaje: {{ $porcentaje }}%
            </div>
            <!-- Datos básicos -->
            <div class="flex justify-between mt-2">
                <div class="text-lg font-medium text-gray-800">
                    @if ($ayudaSolicitada->getAyudaCardComponentName() === 'ayuda-card.estados.documentacion')
                        <p>Te falta poco para completar tu expediente. Sube los datos y
                            documentos necesarios para que tramitemos tu ayuda (hasta
                            {{ number_format($ayudaSolicitada->ayuda->cuantia_usuario, 0, '.', '.') }}€)
                        </p>
                    @endif
                </div>
            </div>
            <!-- Estado -->
            <div class="mt-2">
                <h5
                    class="text-center px-2 py-1 rounded inline-block fs-5 {{ $displayData['badge_classes'] }}">
                    {{ $displayData['label'] }}
                </h5>
                @if ($displayData['mensaje_estado'] ?? null)
                    <div class="alert {{ $displayData['color_mensaje'] }} mt-3" role="alert">
                        {{ $displayData['mensaje_estado'] }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Botón "Ver más" SIEMPRE visible (oculto cuando el contenido se muestra siempre por OPx) -->
        <div class="flex justify-center my-2">
            @if (!$ayudaSolicitada->getAyudaCardMostrarSiempre())
                <div class="flex justify-center my-2">
                    <button type="button"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-blue-700 bg-blue-100 border border-blue-400 rounded hover:bg-blue-200 transition collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#ayudaDetalles-{{ $ayudaSolicitada->id }}"
                        aria-expanded="false"
                        aria-controls="ayudaDetalles-{{ $ayudaSolicitada->id }}">
                        ➕ Ver más
                    </button>
                </div>
            @endif

        </div>

        <!-- Contenido colapsable (componente según estados OPx de contratacion_estado_contratacion) -->
        @php
            $componente = $ayudaSolicitada->getAyudaCardComponentName();
            $mostrarSiempre = $ayudaSolicitada->getAyudaCardMostrarSiempre();
        @endphp

        @if ($mostrarSiempre)
            <!-- Para componentes específicos, mostrar siempre el contenido -->
            <div class="mt-3">
                <x-dynamic-component :component="$componente" :ayudaSolicitada="$ayudaSolicitada" :estadoPrincipal="$estadoPrincipal"
                    :estadoConvivientes="$estadoConvivientes" :nConvivientes="$nConvivientes" :convivientes="$ayudaSolicitada->convivientes ?? []" :sectorAyuda="$sectorAyuda"
                    :preFormConviviente="$preFormConviviente" :preguntasPreForm="$preguntasPreForm" />
            </div>
        @else
            <!-- Para otros estados, mantener el comportamiento colapsable -->
            <div class="collapse mt-3" id="ayudaDetalles-{{ $ayudaSolicitada->id }}">
                <x-dynamic-component :component="$componente" :ayudaSolicitada="$ayudaSolicitada" :estadoPrincipal="$estadoPrincipal"
                    :estadoConvivientes="$estadoConvivientes" :nConvivientes="$nConvivientes" :convivientes="$ayudaSolicitada->convivientes ?? []" :sectorAyuda="$sectorAyuda"
                    :preFormConviviente="$preFormConviviente" :preguntasPreForm="$preguntasPreForm" />
            </div>
        @endif
    </div>
</div>
<!-- Separador entre ayudas -->
@if (!$loop->last)
    <div class="my-4 border-t-2 border-gray-200"></div>
@endif
