<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/compressorjs@1.2.1/dist/compressor.min.js"></script>
    <script src="{{ asset('js/ayuda-card.js') }}"></script>
    <script src="{{ asset('js/firma-canvas.js') }}"></script>
    <script src="{{ asset('js/referral-modal.js') }}"></script>
    <script src="{{ asset('js/upload-documents.js') }}"></script>
    <script src="/js/initModalConditions.js"></script>
    <script src="{{ asset('js/formulario-solicitud-conditions.js') }}"></script>
    <script>
        // Asegurar que openConvivienteForm esté disponible globalmente
        if (typeof window.openConvivienteForm === 'undefined') {
            console.warn('openConvivienteForm no está disponible. Verificando carga de scripts...');
        }
    </script>
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

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Detalle de Ayuda - {{ $ayudaSolicitada->ayuda->nombre_ayuda }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Ubuntu', sans-serif;
            list-style: none;
        }

        :root {
            --primary-color: #54debd;
            --primary-dark: #40d4b0;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-size: 20px;
            border-radius: 10px;
        }

        .btn-secondary {
            font-size: 17px;
            border-radius: 10px;
        }

        .btn-primary:hover {
            background-color: #3c3a60;
            border-color: white;
        }

        .progress-bar {
            width: 0%;
            transition: width 0.3s ease;
            background-color: hsl(160deg 50% 45% / 80%);
        }

        .sidebar {
            background-color: white;
        }

        .list-group-item.active {
            background-color: #54debd;
            border-color: var(--primary-color);
        }

        main {
            min-height: 100vh;
            padding: 2rem 1rem;
            text-align: left;
        }

        .container-fluid {
            padding-top: 32px;
        }

        .sidebar {
            padding-top: 10px;
        }

        .bg-purple-subtle {
            background-color: #dbb4ff91 !important;
        }

        .tooltip-trigger {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .tooltip-trigger:hover .tooltip-content,
        .tooltip-trigger:focus .tooltip-content {
            display: block !important;
        }

        /* Botón de volver mejorado */
        .btn-back-ayudas {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(84, 222, 189, 0.3);
            border: none;
            cursor: pointer;
        }

        .btn-back-ayudas:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(84, 222, 189, 0.4);
            background: linear-gradient(135deg, var(--primary-dark) 0%, #3c3a60 100%);
            color: white;
            text-decoration: none;
        }

        .btn-back-ayudas:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(84, 222, 189, 0.3);
        }

        .btn-back-ayudas i {
            transition: transform 0.3s ease;
        }

        .btn-back-ayudas:hover i {
            transform: translateX(-4px);
        }

        #informativeDocSidebar {
            max-width: 500px;
            z-index: 1055;
        }

        #informativeDocSidebar .offcanvas-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 1.25rem;
        }

        #informativeDocSidebar .offcanvas-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #212529;
        }

        #informativeDocSidebar .offcanvas-body {
            padding: 1.5rem;
        }

        #informativeDocSidebar .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            font-weight: 500;
            padding: 0.75rem 1rem;
        }

        #informativeDocSidebar .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        #informativeDocSidebar .btn-outline-primary {
            border-color: #007bff;
            color: #007bff;
        }

        #informativeDocSidebar .btn-outline-primary:hover {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
        }

        .offcanvas-backdrop {
            z-index: 1054;
        }
    </style>
</head>

<body>
    @include('components.header')
    <x-gtm-noscript />
    <x-simulation-banner />

    <div class="d-flex justify-content-center align-items-center"
        style="min-height: calc(100vh - 200px);">

        <main class="col-12 col-md-9 col-lg-8 col-xl-7" style="max-width: 1200px;">
            <div class="mb-4">
                <a href="{{ route('user.AyudasSolicitadas') }}" class="btn-back-ayudas">
                    <i class="fas fa-arrow-left me-2"></i>
                    Volver a ayudas solicitadas
                </a>
            </div>

            <div class="ayuda-card-wrapper mb-1">
                <div class="card  border-2 border-gray-300 rounded-lg shadow-md p-4"
                    data-ayuda-id="{{ $ayudaSolicitada->id }}"
                    style="position: relative; z-index: 1;">

                    {{-- Información básica de la ayuda --}}

                    <h5
                        class="text-lg sm:text-xl font-semibold break-words ayuda-title ayuda-title-md-pad color:#5fbb9c">
                        Solicitud de {{ $ayudaSolicitada->ayuda->nombre_ayuda }}
                    </h5>

                    @if ($ayudaSolicitada->ayuda->description)
                        <div
                            class="hidden md:block rounded-lg text-sm text-gray-800 leading-relaxed bg-cyan-50/20 mb-3">
                            <span class="font-semibold">Descripción:</span>
                            {!! $ayudaSolicitada->ayuda->description !!}
                        </div>
                    @endif

                    {{-- Barra de progreso y estado (según estados OPx de contratacion_estado_contratacion) --}}
                    @php
                        $displayData = $ayudaSolicitada->getAyudaCardDisplayData();
                        $porcentaje = $displayData['porcentaje'];
                    @endphp
                    <div class="mt-3">
                        <div class="text-right mt-2 font-bold"
                            style="font-size: 2rem; color: #5fbb9c;">
                            {{ $porcentaje }}% <span class="text-right mt-2"
                                style="font-size: 0.875rem; color: #6b7280;">completado</span>
                        </div>

                        <div class="progress relative bg-gray-100 rounded overflow-hidden h-4"
                            style="position: relative;">
                            <div class="progress-bar absolute left-0 top-0 h-full bg-[#54debd]"
                                style="width: {{ $porcentaje }}%; transition: width 1s ease;">
                            </div>
                        </div>

                        {{-- Estadísticas de documentos --}}
                        <x-documentos-estadisticas :ayudaSolicitada="$ayudaSolicitada" />

                    </div>

                    {{-- Estado --}}
                    <div class="mt-2">
                        <h5
                            class="text-center px-2 py-1 rounded inline-block fs-5 {{ $displayData['badge_classes'] }}">
                            {{ $displayData['label'] }}
                        </h5>
                        @if ($displayData['mensaje_estado'] ?? null)
                            <div class="alert {{ $displayData['color_mensaje'] }} mt-3"
                                role="alert">
                                {{ $displayData['mensaje_estado'] }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Contenido principal: documentos y formularios (según estados OPx de contratacion_estado_contratacion) --}}
            @php
                $componente = $ayudaSolicitada->getAyudaCardComponentName();
            @endphp

            <div class="mt-3">
                <x-dynamic-component :component="$componente" :ayudaSolicitada="$ayudaSolicitada" :estadoPrincipal="$estadoPrincipal"
                    :estadoConvivientes="$estadoConvivientes" :nConvivientes="$nConvivientes" :convivientes="$ayudaSolicitada->convivientes ?? []" :sectorAyuda="$sector_ayuda"
                    :preFormConviviente="$preFormConviviente" :preguntasPreForm="$preguntasPreForm" :ayudaTieneFormConvivientes="$ayudaTieneFormConvivientes" />
            </div>
        </main>

    </div>

    <x-upload-modal />
    @include('components.footer')

    <!-- Aquí se cargará dinámicamente el modal de conviviente -->
    <div id="modalConvivienteContainer"></div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="informativeDocSidebar"
        aria-labelledby="informativeDocSidebarLabel">
        <div class="offcanvas-header border-bottom bg-light">
            <div class="flex-grow-1">
                <h5 class="offcanvas-title fw-bold mb-1" id="informativeDocSidebarLabel">Cómo
                    conseguir tu documento</h5>
                <p class="text-muted small mb-0" id="sidebarSubtitle">No salgas de TTF, te guiamos
                    paso a paso</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body">
            <div id="sidebarContent"></div>
        </div>
    </div>

    @if ($convivienteConditions)
        <script>
            /*Se comprueban las condiciones del formulario de conviviente y hace que se oculten las preguntas que no se cumplen con las condiciones*/
            const convivienteConditionsRaw = {!! json_encode($convivienteConditions, JSON_FORCE_OBJECT) !!};
            window.convivienteConditions = convivienteConditionsRaw || {};
        </script>
    @endif

    <x-referral-modal />

    <script>
        /*Esto es para las condiciones del formulario de datos del solicitante 
                    y hace que se oculten las preguntas que no se cumplen con las condiciones*/
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar condiciones cuando el collapse se muestre
            const datosSolicitanteCollapse = document.getElementById(
                'datosSolicitante-{{ $ayudaSolicitada->id }}');
            if (datosSolicitanteCollapse) {
                datosSolicitanteCollapse.addEventListener('shown.bs.collapse', function() {
                    @if ($ayudaSolicitada->conditions_solicitud ?? null)
                        const condiciones = @json($ayudaSolicitada->conditions_solicitud);
                        const formSelector =
                            `#formDatosSolicitante-{{ $ayudaSolicitada->id }}`;
                        if (typeof window.initSolicitudConditions === 'function') {
                            window.initSolicitudConditions(condiciones, formSelector);
                        }
                    @endif
                });

                // También inicializar si ya está abierto
                if (datosSolicitanteCollapse.classList.contains('show')) {
                    @if ($ayudaSolicitada->conditions_solicitud ?? null)
                        const condiciones = @json($ayudaSolicitada->conditions_solicitud);
                        const formSelector =
                            `#formDatosSolicitante-{{ $ayudaSolicitada->id }}`;
                        setTimeout(() => {
                            if (typeof window.initSolicitudConditions === 'function') {
                                window.initSolicitudConditions(condiciones,
                                    formSelector);
                            } else {
                                console.error(
                                    'initSolicitudConditions no está disponible');
                            }
                        }, 200);
                    @endif
                }
            }

            // Verificar que openConvivienteForm esté disponible
            if (typeof window.openConvivienteForm === 'undefined') {
                console.error(
                    'openConvivienteForm no está disponible. Verificando carga de ayuda-card.js...'
                );
                // Intentar definir una función de respaldo
                window.openConvivienteForm = function(ayudaId, questionnaireId, index) {
                    console.error('openConvivienteForm llamado pero no está disponible', {
                        ayudaId,
                        questionnaireId,
                        index
                    });
                    if (!questionnaireId) {
                        alert(
                            'Error: No se encontró el cuestionario de convivientes. Por favor, contacta con soporte.'
                        );
                        return;
                    }
                    alert(
                        'Función openConvivienteForm no está disponible. Por favor, recarga la página.'
                        );
                };
            }

            if (!window.showInformativeDocSidebar) {
                window.showInformativeDocSidebar = function(header, text, link, linkText,
                    documentId, documentName, slug, multiUpload) {
                    const sidebarElement = document.getElementById('informativeDocSidebar');
                    const sidebarContent = document.getElementById('sidebarContent');
                    const sidebarLabel = document.getElementById(
                        'informativeDocSidebarLabel');

                    if (!sidebarElement || !sidebarContent) {
                        console.error('Sidebar elements not found');
                        return;
                    }

                    sidebarLabel.textContent = 'Cómo conseguir tu ' + (documentName
                        .toLowerCase() || 'documento');

                    let textWithBreaks = (text || '').replace(/\\n/g, '<br>');

                    let content = '';

                    if (textWithBreaks) {
                        content += '<div class="mb-4" style="line-height: 1.6;">';
                        content += textWithBreaks;
                        content += '</div>';
                    }

                    if (link && link.trim() !== '') {
                        content += '<div class="mb-4">';
                        content += '<a href="' + link +
                            '" target="_blank" class="btn btn-outline-primary w-100">';
                        content += (linkText || 'Abrir enlace');
                        content += '</a>';
                        content += '</div>';
                    }

                    content += '<div class="mt-4 pt-4 border-top">';
                    content +=
                        '<button type="button" class="btn btn-success w-100" data-doc-id="' +
                        documentId + '" data-doc-name="' + (documentName || '').replace(
                            /"/g, '&quot;') + '" data-doc-slug="' + (slug || '').replace(
                            /"/g, '&quot;') + '" data-multi-upload="' + multiUpload + '">';
                    content +=
                        '<i class="fas fa-check-circle me-2"></i>Ya lo tengo → Subir ahora';
                    content += '</button>';
                    content +=
                        '<p class="text-muted small mt-2 text-center mb-0">Al hacer clic podrás subir el documento</p>';
                    content += '</div>';

                    setTimeout(function() {
                        const button = sidebarContent.querySelector(
                            '.btn-success[data-doc-id]');
                        if (button) {
                            button.addEventListener('click', function() {
                                const sidebar = bootstrap.Offcanvas
                                    .getInstance(document.getElementById(
                                        'informativeDocSidebar'));
                                if (sidebar) sidebar.hide();
                                setTimeout(function() {
                                    if (typeof openModal ===
                                        'function') {
                                        const docId = parseInt(
                                            button.getAttribute(
                                                'data-doc-id'));
                                        const docName = button
                                            .getAttribute(
                                                'data-doc-name');
                                        const docSlug = button
                                            .getAttribute(
                                                'data-doc-slug');
                                        const isMulti = button
                                            .getAttribute(
                                                'data-multi-upload'
                                            ) === 'true';
                                        openModal(docId, docName,
                                            docSlug, isMulti, ''
                                        );
                                    } else {
                                        console.error(
                                            'openModal function not found'
                                        );
                                    }
                                }, 300);
                            });
                        }
                    }, 100);

                    sidebarContent.innerHTML = content;

                    const bsOffcanvas = new bootstrap.Offcanvas(sidebarElement);
                    bsOffcanvas.show();
                };
            }

            // Asegurar que el container del modal esté presente
            if (!document.getElementById('modalConvivienteContainer')) {
                const container = document.createElement('div');
                container.id = 'modalConvivienteContainer';
                document.body.appendChild(container);
            }

            // Manejo del formulario de datos del solicitante vía AJAX
            const formDatosSolicitante = document.getElementById(
                'formDatosSolicitante-{{ $ayudaSolicitada->id }}');
            if (formDatosSolicitante) {
                formDatosSolicitante.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const answers = {};
                    const processedFormData = new FormData();

                    for (const [key, value] of formData.entries()) {
                        if (!key.startsWith('answers[')) {
                            processedFormData.append(key, value);
                        }
                    }

                    for (const [key, value] of formData.entries()) {
                        if (key.startsWith('answers[')) {
                            const match = key.match(/answers\[(\d+)\](?:\[\])?/);
                            if (match) {
                                const questionId = match[1];
                                if (!answers[questionId]) {
                                    answers[questionId] = [];
                                }
                                answers[questionId].push(value);
                            }
                        }
                    }

                    Object.keys(answers).forEach(questionId => {
                        const noneCheckbox = document.querySelector(
                            `.none-option-${questionId}`);
                        if (noneCheckbox && noneCheckbox.checked) {
                            processedFormData.append(`answers[${questionId}][]`,
                                '-1');
                        } else {
                            const values = answers[questionId].filter(v => v !==
                                '-1' && v !== -1);
                            values.forEach(val => {
                                processedFormData.append(
                                    `answers[${questionId}][]`, val);
                            });
                        }
                    });

                    const submitBtn = this.querySelector('button[type="submit"]');
                    const btnText = submitBtn.querySelector('.btn-text');
                    const btnSpinner = submitBtn.querySelector('.btn-spinner');

                    btnText.classList.add('d-none');
                    btnSpinner.classList.remove('d-none');
                    submitBtn.disabled = true;

                    fetch(this.action, {
                            method: 'POST',
                            body: processedFormData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]')
                                    .getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // alert(data.message || 'Datos guardados correctamente');
                                location.reload();
                            } else {
                                alert('Error al guardar los datos: ' + (data
                                    .message ||
                                    'Error desconocido'));
                            }
                        })
                        .catch(error => {
                            alert('Error al guardar los datos: ' + error.message);
                        })
                        .finally(() => {
                            btnText.classList.remove('d-none');
                            btnSpinner.classList.add('d-none');
                            submitBtn.disabled = false;
                        });
                });
            }
        });
    </script>
</body>

</html>
