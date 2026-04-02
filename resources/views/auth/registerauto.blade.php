<!DOCTYPE html>
<html lang="es">

<head>

    @if (app()->environment('production'))
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
    @endif

    <!-- Fin del Administrador de etiquetas de Google -->
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @extends ('layouts.app')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        .hidden {
            display: none;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            /* Esto evita el scroll en toda la página */
        }

        .iframe-container {
            height: 100vh;
            /* Ocupa toda la altura visible */
            width: 100%;
        }

        #contentIframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .main-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100%;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-900">
    <x-gtm-noscript />

    <!-- Contenedor principal -->
    <div class="main-container">
        <!-- Contenedor del iframe -->
        <div class="iframe-container rounded-xl shadow-lg border border-gray-200 bg-white">
            <!-- Iframe responsivo -->
            <iframe id="contentIframe" src="{{ $iframe }}"
                onload="document.getElementById('loader').style.display = 'none';"
                allow="fullscreen" loading="lazy"></iframe>
        </div>
    </div>

    <div id="loading-overlay"
        class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center hidden z-50">
        <div
            class="w-16 h-16 border-4 border-t-green-500 border-gray-200 rounded-full animate-spin">
        </div>
    </div>

    <script>
        window.addEventListener("message", function(event) {
            if (event?.data?.name && event.data.sessionId) {
                console.info("📩 Bankflip Event:", event.data.name, event.data.sessionId);

                if (event.data.name == "session_completed" || event.data.name ==
                    "user_requested_closure") {
                    const sessionId = event.data.sessionId;
                    const apiUrl = "{{ route('savecollector') }}";
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')
                        .getAttribute('content');
                    const overlay = document.getElementById('loading-overlay');
                    overlay.classList.remove('hidden');

                    fetch(apiUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                sessionId: sessionId
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(err => {
                                    throw new Error(err.message ||
                                        `HTTP error! status: ${response.status}`
                                        );
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                console.log("✅ Datos procesados:", data);

                                if (data.message) {
                                    localStorage.setItem('flash_success', data.message);
                                }

                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                }
                            }
                        })
                        .catch(error => {
                            overlay.classList.add('hidden');
                            alert(
                                "Hubo un problema con la redirección. Por favor pulse el botón para continuar en Tu Trámite Fácil"
                            );
                        });
                }
            }
        });

        function resizeIframe() {
            const iframe = document.getElementById('contentIframe');
            try {
                iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';
            } catch (e) {
                console.log("No se puede ajustar el iframe:", e);
            }
        }

        document.getElementById('contentIframe').addEventListener('load', function() {
            resizeIframe();
        });

        document.getElementById('contentIframe').addEventListener('error', function() {
            document.getElementById('loader').innerHTML = `
                <div class="text-center p-6 text-red-500">
                    <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
                    <p class="font-medium">Error al cargar el contenido</p>
                    <button onclick="window.location.reload()" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                        Reintentar
                    </button>
                </div>
            `;
        });
    </script>

</body>

</html>
