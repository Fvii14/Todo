<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No eres beneficiario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Ubuntu', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <x-gtm-noscript />
    @include('components.simulation-banner')
    <div
        class="max-w-2xl mx-auto p-6 md:p-10 bg-white rounded-xl shadow-md space-y-3 text-base md:text-[16px]">
        <!-- Título y mensajes -->
        <div>
            <h1 class="text-xl md:text-3xl font-bold text-gray-800">
                No puedes solicitar esta ayuda (por ahora)
            </h1>
            <div class="mt-3 md:mt-4 text-gray-700 text-sm md:text-base">

                <p class="font-semibold text-gray-900">No cumples con los siguientes requisitos:</p>

                @if (session('motivos') && is_array(session('motivos')))
                    <ul
                        class="mt-2 list-disc list-inside space-y-1 text-sm md:text-base text-red-600">
                        @foreach (session('motivos') as $motivo)
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-circle-xmark text-red-500 mt-0.5"></i>
                                <span>{{ $motivo }}</span>
                            </li>
                        @endforeach
                    </ul>
                @elseif (session('motivos'))
                    <p class="mt-2 text-red-600 text-sm md:text-base">
                        <i class="fa-solid fa-circle-xmark text-red-500 mr-1"></i>
                        {!! session('motivos') !!}
                    </p>
                @endif

                @php
                    session()->forget('motivos');
                @endphp

                <p class="text-gray-600 mt-4">A veces hay excepciones o cambios según tu comunidad
                    autónoma. ¿Quieres
                    que
                    revisemos tu caso?</p>

            </div>

        </div>
        <!-- Formulario -->
        <form action="{{ route('user.solicitar-revision') }}" method="POST"
            class="flex flex-col gap-2">
            @csrf
            <input type="hidden" name="ayuda_id" value="{{ request('ayuda_id') }}">
            <textarea name="comentario" id="comentario"
                placeholder="¿Nos das más info? Nos ayuda a revisar tu caso."
                class="flex-grow px-3 py-2 md:py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-400 text-sm md:text-base placeholder-gray-400"></textarea>
            <button type="submit"
                class="px-3 py-2 md:py-2.5 hover:bg-green-500 text-white text-sm md:text-base font-semibold rounded-md transition"
                style="background-color: #2fd6a6;">
                Revisar mi caso
            </button>
        </form>
        <!-- Botón volver -->
        <a href="{{ route('user.home') }}"
            class="flex items-center text-gray-700 hover:text-black text-sm md:text-base mt-4 font-semibold">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            <span>Volver a inicio</span>
        </a>
    </div>

</body>

</html>
