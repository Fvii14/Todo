<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Ayuda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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

    <!-- Google Tag Manager -->
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
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-W9GF583');
    </script>
    <!-- End Google Tag Manager -->
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W9GF583" height="0"
            width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <x-gtm-noscript />
    @include('components.simulation-banner')
    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4">
        <div
            class="w-full md:w-3/4 lg:w-3/5 max-w-5xl bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-white p-6 md:p-8 text-black">
                <div class="flex items-center justify-between">
                    <!-- Imagen izquierda -->
                    <img src="{{ asset('imagenes/cropped-ttflogo_back-192x192-4.png') }}"
                        alt="Logo TTF" class="w-10 h-10 md:w-16 md:h-16 object-contain" />

                    <!-- Texto centrado -->
                    <div class="text-center">
                        <h1 class="text-2xl font-bold">¡Enhorabuena!</h1>
                        <h2 class="text-lg font-medium">
                            Eres beneficiario de {{ $ayuda->nombre_ayuda }}
                        </h2>
                    </div>

                    <!-- Imagen derecha -->
                    <img src="{{ asset('imagenes/organos/' . $ayuda->organo->imagen) }}"
                        alt="Imagen Ayuda"
                        class="w-8 h-8 sm:w-12 sm:h-12 md:w-14 md:h-14 object-contain rounded-full" />
                </div>
            </div>

            <div class="p-4 pt-0 md:p-6 md:pt-0 lg:p-8 lg:pt-0">
                <div class="mb-6">
                    <div class="bg-blue-50 rounded-xl p-6 mb-6">
                        <ul class="space-y-3 list-disc pl-5">
                            <li class="flex items-start">
                                <span class="mr-3">✅</span>
                                <span class="text-gray-700"><strong>Cumples requisitos</strong> para
                                    esta ayuda</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-3">💰</span>
                                <span class="text-gray-700">Puedes recibir hasta
                                    <strong>{{ $cuantia_usuario }} €</strong></span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-3">📅</span>
                                <span class="text-gray-700">
                                    Plazo:
                                    {!! $fecha_fin
                                        ? 'Hasta el <strong>' . \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') . '</strong>'
                                        : '<strong>Sin plazo final</strong>' !!}
                                </span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-3">🏛️</span>
                                <span class="text-gray-700">Organismo:
                                    <strong>{{ $organo_name }}</strong></span>
                            </li>
                        </ul>
                    </div>
                    <div class="px-2 mb-3 text-gray-700 leading-relaxed">
                        <h3 class="text-lg font-medium">Nos encargamos de todo el proceso
                            administrativo por ti:</h3>
                        <ul class="space-y-3 list-disc pl-5">
                            <li class="flex items-start">
                                <span class="mr-3">📄</span>
                                <span class="text-gray-700">Presentación de la solicitud</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-3">🔍</span>
                                <span class="text-gray-700">Seguimiento con la
                                    <strong>administración</strong></span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-3">🗺️</span>
                                <span class="text-gray-700">Subsanaciones y
                                    <strong>reclamaciones</strong></span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-3">✉️</span>
                                <span class="text-gray-700">Notificación de resolución</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-3">💸</span>
                                <span class="text-gray-700">Acompañamiento hasta el cobro</span>
                            </li>
                        </ul>
                        @if (empty($ayuda->pago) || $ayuda->pago != 1)
                            <h3 class="text-lg font-medium mt-4">Condiciones económicas:</h3>
                            <ul class="space-y-3 list-disc pl-5">
                                <li class="flex items-start">
                                    <span class="mr-3">✅</span>
                                    <span class="text-gray-700">Solo cobramos si conseguimos la
                                        ayuda para ti</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="mr-3">✅</span>
                                    <span class="text-gray-700">Nuestra comisión es del
                                        <strong>20%</strong> del importe
                                        concedido.</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="mr-3">✅</span>
                                    <span class="text-gray-700">Solo se te cobrará <strong>cuando
                                            recibas el dinero en
                                            tu
                                            cuenta</strong></span>
                                </li>
                            </ul>
                        @else
                            <h3 class="text-lg font-medium mt-4">Precio del servicio:</h3>
                            @php
                                $productoPago = $ayuda->productos->first();
                            @endphp
                            @if ($productoPago)
                                <div class="text-gray-700 mt-2 mb-2">
                                    <span>El precio para tramitar esta ayuda es de
                                        <strong>{{ number_format($productoPago->price, 2, ',', '.') }}&nbsp;€</strong>
                                        (pago único).</span>
                                </div>
                            @else
                                <div class="text-gray-700 mt-2 mb-2">
                                    <span><strong>Precio no disponible.</strong></span>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="flex flex-col space-y-3 mt-0 pt-0">
                    <form action="{{ route('contratacion.store') }}" method="POST" class="w-full"
                        onsubmit="this.querySelector('button').disabled = true;">
                        <div class="bg-white p-3 md:p-3 text-black pt-0">
                            <div class="flex flex-col space-y-2 mb-2 mt-0">
                                <!-- Primer checkbox + label -->
                                <div class="flex items-start">
                                    <input type="checkbox" id="checkbox1" class="mr-2" required />
                                    <label for="checkbox1" class="text-gray-700 text-xs">
                                        He leído y acepto los
                                        <a target="_blank"
                                            href="https://tutramitefacil.es/terminos-y-condiciones/"
                                            class="text-indigo-600 hover:text-indigo-800 font-medium">
                                            Términos y Condiciones
                                        </a>
                                        del servicio, y autorizo a Tu Trámite Fácil a gestionar esta
                                        ayuda en mi nombre.
                                    </label>
                                </div>

                                <!-- Segundo checkbox + label -->
                                @if (empty($ayuda->pago) || $ayuda->pago != 1)
                                    <div class="flex items-start">
                                        <input type="checkbox" id="checkbox2" class="mr-2 mt-1"
                                            required />
                                        <label for="checkbox2" class="text-gray-700 text-xs">
                                            Consiento el cobro automático de la comisión a éxito
                                            mediante adeudo SEPA,
                                            una vez haya recibido la ayuda en mi cuenta bancaria.
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @csrf
                        <input type="hidden" name="ayuda_id" value="{{ $ayuda->id }}">

                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            Contrata tu ayuda <span class="ml-2">›</span>
                        </button>
                    </form>

                    <button
                        class="flex items-center justify-center text-indigo-600 hover:text-indigo-800 font-medium"
                        onclick="window.location.href='{{ route('user.home') }}'">
                        <span class="mr-2">‹</span> Volver a mis ayudas
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
