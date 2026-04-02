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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.showUploadPopup = @json(session('success') ? true : false);
    </script>
    <script src="{{ asset('js/upload-documents.js') }}"></script>
    <script src="/js/initModalConditions.js"></script>
    <script src="{{ asset('js/formulario-solicitud-conditions.js') }}"></script>
    <script src="{{ asset('js/documentos-conditions.js') }}"></script>

    @vite(['resources/js/conviviente-builder-modal.js'])

    <meta name="csrf-token" content="{{ csrf_token() }}">
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

    <title>Ayudas Solicitadas</title>
    <style>
        .border-error {
            border: 2px solid red !important;
        }

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

        .card {
            opacity: 1;
            transform: translateY(0);
            display: block;
        }

        .ayudas-container {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .ayuda-card-wrapper {
            position: relative;
            z-index: 1;
        }

        .progress-bar {
            width: 0%;
            transition: width 0.3s ease;
            background-color: #54debd;
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

        h1 {
            padding: 20px;
            padding-top: 30px;
            font-size: 30px !important;
            font-weight: normal !important;
        }

        .flex {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        h5,
        .progress,
        .text-sm {
            margin-bottom: 1rem;
        }

        .container-fluid {
            padding-top: 32px;
        }

        .sidebar {
            padding-top: 10px;
        }

        @media (max-width: 767px) {
            .card .flex {
                flex-direction: column;
                align-items: center;
            }

            .card .flex>div {
                text-align: center;
                width: 100%;
                margin-bottom: 1rem;
            }
        }

        @media (min-width: 768px) {
            .flex>div {
                flex: 1 1 30%;
            }
        }

        .doc-toggle {
            display: none;
            margin-top: 1rem;
        }

        #reciboSlider {
            display: flex;
            overflow-x: auto;
            gap: 1rem;
            padding: 0;
            scroll-snap-type: x mandatory;
        }

        .card-recibo {
            flex: 0 0 auto;
            scroll-snap-align: center;
        }

        .bg-purple-subtle {
            background-color: #dbb4ff91 !important;
            /* naranja clarito */
        }

        #toggleButtonDatosSolicitante {
            background-color: #3c3a60;
            color: white;
        }

        #toggleButtonDatosSolicitante:hover {
            background-color: #54debe;
            color: #3c3a60;
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
    @php
        function extraerMesAnioDesdeNombre($name)
        {
            $pattern =
                '/(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)\s+(\d{4})/i';

            if (preg_match($pattern, $name, $matches)) {
                $mesNombre = strtolower($matches[1]);
                $anio = $matches[2];

                $meses = [
                    'enero' => '01',
                    'febrero' => '02',
                    'marzo' => '03',
                    'abril' => '04',
                    'mayo' => '05',
                    'junio' => '06',
                    'julio' => '07',
                    'agosto' => '08',
                    'septiembre' => '09',
                    'octubre' => '10',
                    'noviembre' => '11',
                    'diciembre' => '12',
                ];

                $mes = $meses[$mesNombre] ?? '00';
                return "{$anio}_{$mes}";
            }

            return 'unknown';
        }
    @endphp

    <div class="container-fluid">
        <div class="row">
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

            <main class="flex-1">
                <h1 class="text-2xl font-bold mb-4">Ayudas Solicitadas</h1>
                @if ($ayudasSolicitadas->isEmpty())
                    <div class="bg-blue-50 border border-blue-200 text-blue-900 rounded p-3">
                        <strong>No hay ayudas solicitadas en este momento.</strong>
                    </div>
                @else
                    <div class="ayudas-container" style="max-width: 900px; margin: 0 auto;">
                        @foreach ($ayudasSolicitadas as $ayudaSolicitada)
                            <x-ayuda-card-simple :ayudaSolicitada="$ayudaSolicitada" />
                            @if (!$loop->last)
                                <div class="my-4 border-t-2 border-gray-200"></div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </main>
        </div>
    </div>

    <x-upload-modal />

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

    <script>
        if (!window.showInformativeDocSidebar) {
            window.showInformativeDocSidebar = function(header, text, link, linkText, documentId,
                documentName, slug, multiUpload) {
                const sidebarElement = document.getElementById('informativeDocSidebar');
                const sidebarContent = document.getElementById('sidebarContent');
                const sidebarLabel = document.getElementById('informativeDocSidebarLabel');

                if (!sidebarElement || !sidebarContent) {
                    console.error('Sidebar elements not found');
                    return;
                }

                sidebarLabel.textContent = 'Cómo conseguir tu ' + (documentName.toLowerCase() ||
                    'documento');

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
                content += '<button type="button" class="btn btn-success w-100" data-doc-id="' +
                    documentId + '" data-doc-name="' + (documentName || '').replace(/"/g,
                        '&quot;') + '" data-doc-slug="' + (slug || '').replace(/"/g, '&quot;') +
                    '" data-multi-upload="' + multiUpload + '">';
                content += '<i class="fas fa-check-circle me-2"></i>Ya lo tengo → Subir ahora';
                content += '</button>';
                content +=
                    '<p class="text-muted small mt-2 text-center mb-0">Al hacer clic podrás subir el documento</p>';
                content += '</div>';

                setTimeout(function() {
                    const button = sidebarContent.querySelector(
                        '.btn-success[data-doc-id]');
                    if (button) {
                        button.addEventListener('click', function() {
                            const sidebar = bootstrap.Offcanvas.getInstance(document
                                .getElementById('informativeDocSidebar'));
                            if (sidebar) sidebar.hide();
                            setTimeout(function() {
                                if (typeof openModal === 'function') {
                                    const docId = parseInt(button
                                        .getAttribute('data-doc-id'));
                                    const docName = button.getAttribute(
                                        'data-doc-name');
                                    const docSlug = button.getAttribute(
                                        'data-doc-slug');
                                    const isMulti = button.getAttribute(
                                        'data-multi-upload') === 'true';
                                    openModal(docId, docName, docSlug,
                                        isMulti, '');
                                } else {
                                    console.error(
                                        'openModal function not found');
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
    </script>

    @include('components.footer')
</body>

</html>
