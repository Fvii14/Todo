<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Administrador de etiquetas de Google -->
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
    <!-- Fin del Administrador de etiquetas de Google -->

    <!-- Clarity Analytics -->
    @if (app()->environment('production'))
        <x-clarity-analytics />
    @endif
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
    </style>
</head>

<body
    class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-900 min-h-screen flex flex-col items-center justify-start p-4">
    <x-gtm-noscript />
    <x-simulation-banner />

    <h2
        class="text-center text-2xl md:text-3xl font-bold text-gray-800 leading-tight max-w-5xl mx-auto px-4 mt-8">
        Descubre en menos de 2 minutos qué ayudas públicas puedes recibir
    </h2>

    <div class="max-w-4xl mx-auto my-8 px-4">
        <div class="mt-6 space-y-4 bg-blue-50 p-6 rounded-xl border border-blue-100">
            <p class="text-gray-700 text-base md:text-lg flex items-start">
                <span class="mr-2 text-blue-500">🎯</span> Te ayudamos a saber a qué ayudas puedes
                optar según tu
                situación.
            </p>
            <p class="text-gray-700 text-base md:text-lg flex items-start">
                <span class="mr-2 text-blue-500">🧩</span> Tú eliges cómo empezar: contándonos tu
                caso o dejándonos
                comprobarlo por ti.
            </p>
            <p class="text-gray-700 text-base md:text-lg flex items-start">
                <span class="mr-2 text-blue-500">🔐</span> Usamos tus datos solo para ayudarte a
                conocer las ayudas
                disponibles. No los compartimos con terceros y puedes eliminarlos en cualquier
                momento.
            </p>
        </div>
    </div>

    <!-- Contenedor principal de opciones -->
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row gap-8 px-4 my-8">
        <div
            class="flex-1 bg-[#e6fff8] rounded-xl shadow-md overflow-hidden border border-[#3C3A60] hover:shadow-lg transition-all duration-300 relative">
            <div class="absolute top-0 left-0 w-full h-1 bg-[#3C3A60]"></div>
            <div class="p-6 md:p-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="bg-[#3C3A60] p-2 rounded-full mr-3 border-2 border-[#17FCC0]">
                            <i class="fa-solid fa-check text-[#17FCC0] text-lg"></i>
                        </div>
                        <h3 class="text-sm md:text-xl font-bold text-[#3C3A60]">
                            Opción 1: Que lo hagamos por ti
                            <br>
                            <span
                                class="ml-2 bg-[#17FCC0] text-[#3C3A60] text-xs font-bold px-2 py-0.5 rounded-full md:hidden">★
                                Recomendado</span>
                        </h3>
                    </div>
                    <span
                        class="hidden md:flex items-center bg-[#17FCC0] text-[#3C3A60] font-bold px-3 py-1 rounded-full ml-4"
                        style="font-size: 0.68rem;">
                        ★ Recomendado
                    </span>
                </div>
                <p class="text-gray-600 mb-2 text-sm md:text-base italic">
                    (Requiere Cl@ve PIN)
                </p>

                <p class="text-[#3C3A60] mb-6 text-base md:text-lg">
                    Conectamos con Hacienda y la Seguridad Social para decirte al instante si puedes
                    recibir ayudas.
                </p>

                <div class="mb-6">
                    <h4 class="font-semibold text-[#3C3A60] mb-2 flex items-center">
                        <span
                            class="bg-white text-[#17FCC0] rounded-full w-5 h-5 flex items-center justify-center mr-2 text-sm border-2 border-[#3C3A60]">📌</span>
                        Ideal si:
                    </h4>
                    <ul class="space-y-2 pl-7">
                        <li class="text-[#3C3A60] flex items-start">
                            <span class="text-[#3C3A60] mr-2 font-bold">•</span> Tienes Cl@ve PIN
                        </li>
                        <li class="text-[#3C3A60] flex items-start">
                            <span class="text-[#3C3A60] mr-2 font-bold">•</span> Prefieres que
                            revisemos tus datos
                            oficiales
                        </li>
                        <li class="text-[#3C3A60] flex items-start">
                            <span class="text-[#3C3A60] mr-2 font-bold">•</span> Quieres una
                            evaluación más precisa
                        </li>
                    </ul>
                </div>

                <div class="mb-8">
                    <h4 class="font-semibold text-[#3C3A60] mb-2 flex items-center">
                        <span
                            class="bg-white text-[#3C3A60] rounded-full w-5 h-5 flex items-center justify-center mr-2 text-sm border-2 border-[#3C3A60]">✓</span>
                        ¿Qué conseguimos por ti?
                    </h4>
                    <ul class="space-y-2 pl-7">
                        <li class="text-[#3C3A60] flex items-start">
                            <span class="text-[#3C3A60] mr-2 font-bold">•</span> Descubre qué ayudas
                            puedes solicitar de
                            forma personalizada
                        </li>
                        <li class="text-[#3C3A60] flex items-start">
                            <span class="text-[#3C3A60] mr-2 font-bold">•</span> Accedemos al
                            informe de vida laboral y
                            tu declaración de la renta
                        </li>
                        <li class="text-[#3C3A60] flex items-start">
                            <span class="text-[#3C3A60] mr-2 font-bold">•</span> Todo con tu
                            autorización y máxima
                            seguridad.
                        </li>
                    </ul>
                </div>

                <a href="{{ route('registerauto') }}"
                    class="block w-full text-center bg-[#3C3A60] hover:bg-[#2a2848] text-white font-medium py-3 px-6 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5 border-2 border-transparent hover:border-[#17FCC0] group">
                    <span class="group-hover:text-[#17FCC0] transition-colors">Conectar con la
                        administración
                        pública</span>
                    <i
                        class="fas fa-arrow-right ml-2 group-hover:text-[#17FCC0] transition-colors"></i>
                </a>

                <p class="text-xs text-[#3C3A60] mt-4 text-center">
                    <i class="fas fa-lock mr-1"></i> Tus datos están protegidos y nunca se
                    compartirán
                </p>
            </div>
        </div>

        <!-- Opción 2 - Tarjeta -->
        <div
            class="flex-1 bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:border-blue-200 transition-all duration-300">
            <div class="p-6 md:p-8">
                <div class="flex items-center mb-4">
                    <div class="bg-green-100 p-2 rounded-full mr-3">
                        <i class="fa-solid fa-thumbs-up text-green-500 text-lg"></i>
                    </div>
                    <h3 class="text-sm md:text-xl font-bold text-gray-800">
                        Opción 2: Cuéntanos tu situación
                    </h3>
                </div>
                <p class="text-gray-600 mb-2 text-sm md:text-base italic">
                    (Recomendado si no tienes Cl@ve PIN)
                </p>
                <p class="text-gray-700 mb-6 text-base md:text-lg">
                    Hazlo tú mismo en 1 minuto. Sin documentos. Sin tecnicismos
                </p>

                <div class="mb-6">
                    <h4 class="font-semibold text-[#3C3A60] mb-2 flex items-center">
                        <span
                            class="bg-white text-[#17FCC0] rounded-full w-5 h-5 flex items-center justify-center mr-2 text-sm border-2 border-[#3C3A60]">📌</span>
                        Ideal si:
                    </h4>
                    <ul class="space-y-2 pl-7">
                        <li class="text-[#3C3A60] flex items-start">
                            <span class="text-[#3C3A60] mr-2 font-bold">•</span> No tienes Cl@ve PIN
                        </li>
                        <li class="text-[#3C3A60] flex items-start">
                            <span class="text-[#3C3A60] mr-2 font-bold">•</span> Quieres entender
                            las ayudas que se
                            ajustan a tu situación
                        </li>
                        <li class="text-[#3C3A60] flex items-start">
                            <span class="text-[#3C3A60] mr-2 font-bold">•</span> Prefieres ir paso a
                            paso
                        </li>
                    </ul>
                </div>

                <div class="mb-8">
                    <h4 class="font-semibold text-[#3C3A60] mb-2 flex items-center">
                        <span
                            class="bg-white text-[#3C3A60] rounded-full w-5 h-5 flex items-center justify-center mr-2 text-sm border-2 border-[#3C3A60]">✓</span>
                        ¿Qué consigues?
                    </h4>
                    <ul class="space-y-2 pl-7">
                        <li class="text-[#3C3A60] flex items-start">
                            <span class="text-[#3C3A60] mr-2 font-bold">•</span> Descubre qué ayudas
                            puedes solicitar de
                            forma manual.
                        </li>
                        <li class="text-[#3C3A60] flex items-start">
                            <span class="text-[#3C3A60] mr-2 font-bold">•</span> Sin necesidad de
                            subir documentos ni
                            conectarte con Hacienda
                        </li>
                        <li class="text-[#3C3A60] flex items-start">
                            <span class="text-[#3C3A60] mr-2 font-bold">•</span> Solo contestando
                            unas preguntas creamos
                            tu perfil personalizado
                        </li>
                    </ul>
                </div>

                <a href="{{ route('onboarder') }}"
                    class="block w-full text-center bg-gradient-to-r from-[#17FCC0] to-[#17FCC0] hover:from-[#17FCC0] hover:to-[#17FCC0] text-black font-medium py-3 px-6 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5">
                    Empezar formulario
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>

                <p class="text-xs text-gray-500 mt-4 text-center">
                    <i class="fas fa-lock mr-1"></i> Tus datos están protegidos y nunca se
                    compartirán
                </p>
            </div>
        </div>
    </div>
</body>

</html>
