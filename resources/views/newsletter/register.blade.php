{{-- resources/views/newsletter/register.blade.php --}}
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Suscripción a la newsletter · TTF</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Raleway + Tailwind vía CDN para probar rápido (en producción puedes usar tu build/vite) --}}
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            color-scheme: light;
        }

        body {
            font-family: 'Raleway', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
        }
    </style>
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

<body class="bg-gray-200"
    style="background-image: url('https://tutramitefacil.es/wp-content/uploads/2018/08/footer-creative-bg.jpg'); background-size: cover; background-attachment: fixed;">
    <x-gtm-noscript />
    {{-- Formulario centrado --}}
    <main class="min-h-screen flex items-center justify-center py-10 px-4">

        <div class="w-full max-w-md bg-white shadow rounded-xl p-6">
            <div class="flex flex-row items-center justify-center w-full mb-4">
                <img src="/imagenes/cropped-ttflogo_back-192x192-4.png" alt="" class="w-16">
                <h1 class="text-3xl text-center font-bold mt-2 " style="color:#3c3a60">Tu Trámite Fácil</h1>
            </div>
            <h1 class="text-2xl font-semibold mb-1" style="color:#3c3a60">Suscríbete a nuestra newsletter</h1>
            <p class="text-m text-gray-600 mb-6" style="color:#3c3a60">Recibe novedades, convocatorias y trucos para
                aprovechar ayudas.</p>

            {{-- Éxito --}}
            @if (session('status'))
                <div class="mb-4 rounded-md bg-emerald-50 p-3 text-emerald-700 text-sm" role="status" aria-live="polite">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Errores --}}
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 p-3 text-red-700 text-sm">
                    <ul class="list-disc ml-4">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('newsletter.store') }}" novalidate>
                @csrf

                {{-- Honeypot anti-spam --}}
                <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">

                <div class="space-y-1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700"style="color:#3c3a60 ">Nombre</label>
                        <input type="text" name="first_name" autocomplete="given-name" value="{{ old('first_name') }}" autofocus
                            class="mt-1 block w-full rounded-md border-gray-300 focus:ring-emerald-500 focus:border-emerald-500  h-12 px-4 text-base"
                            placeholder="Tu nombre" style="margin:10px " />
                    </div>

                    <div>
                        <label class="block text-sm font-medium "style="color:#3c3a60">Email</label>
                        <input type="email" name="email" autocomplete="email" inputmode="email" value="{{ old('email') }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 focus:ring-emerald-500 focus:border-emerald-500  h-12 px-4 text-base"
                            placeholder="tu@email.com" />
                    </div>

                    <div class="flex items-start gap-2">
                        <input id="consent" name="consent" type="checkbox" value="1" required
                            class="mt-1 h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                        <label for="consent" class="text-sm text-gray-700"style="color:#3c3a60">
                            Acepto recibir comunicaciones y la <a
                                href="{{ url('https://tutramitefacil.es/politica-de-privacidad/') }}"
                                class="text-emerald-700 underline">política de privacidad</a>.
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full inline-flex justify-center items-center gap-2 rounded-md text-white px-4 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        style="background:#54debd">
                        Suscribirme
                    </button>

                    <p class="text-xs text-gray-500 text-center"style="color:#3c3a60">
                        Podrás darte de baja en cualquier momento desde los correos que te enviemos.
                    </p>
                </div>
            </form>
        </div>
    </main>


    <!-- Canvas de fondo -->
    <canvas id="background-canvas"
        style="position:fixed; inset:0; width:100vw; height:100vh; z-index:-1; display:block;"></canvas>

    <!--canvas.js-->
    <script>
        (function() {
            const canvas = document.getElementById('background-canvas');
            const ctx = canvas.getContext('2d');

            let width, height;
            const waveCount = 5;
            const waves = [];

            function resizeCanvas() {
                // El atributo width/height del canvas controla el buffer (no solo CSS)
                width = canvas.width = window.innerWidth;
                height = canvas.height = window.innerHeight;
            }

            function rand(min, max) {
                return Math.random() * (max - min) + min;
            }

            // Crear ondas con propiedades aleatorias
            function initWaves() {
                waves.length = 0;
                for (let i = 0; i < waveCount; i++) {
                    waves.push({
                        offset: Math.random() * 1000,
                        speed: 0.005,
                        amplitude: rand(120, 520), // un poco más moderado para pantallas pequeñas
                        frequency: rand(0.001, 0.003), // frecuencia visible
                        color: `rgba(89, 237, 202, ${rand(0.10, 0.25)})`,
                        lineWidth: rand(2.5, 3.8),
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

                    // Punto inicial
                    const y0 = centerY + Math.sin(0 * wave.frequency + wave.offset) * wave.amplitude;
                    ctx.moveTo(0, y0);

                    for (let x = 1; x < width; x++) {
                        const y = centerY + Math.sin(x * wave.frequency + wave.offset) * wave.amplitude;
                        ctx.lineTo(x, y);
                    }
                    ctx.stroke();

                    wave.offset += wave.speed; // animación
                }

                requestAnimationFrame(draw);
            }

            // Parpadeo + cambio a rojo temporalmente
            function changeLinesToRed() {
                for (const wave of waves) {
                    wave.color = 'rgba(255, 0, 0, 0.30)';
                    wave.lineWidth = 1.2;
                }

                let flashes = 0;
                const maxFlashes = 4; // nº de cambios fondo on/off
                const flashInterval = 150;

                const interval = setInterval(() => {
                    canvas.style.backgroundColor = (flashes % 2 === 0) ? 'rgba(100,101,104,0.25)' : '';
                    flashes++;
                    if (flashes >= maxFlashes) {
                        clearInterval(interval);
                        canvas.style.backgroundColor = '';
                        // restaurar colores aleatorios
                        for (const wave of waves) {
                            wave.color = `rgba(89, 237, 202, ${Math.random() * 0.15 + 0.10})`;
                            wave.lineWidth = Math.random() * 0.6 + 3;
                        }
                    }
                }, flashInterval);
            }

            // Exponer la función al botón de ejemplo
            window.changeLinesToRed = changeLinesToRed;

            // Inicializar
            function start() {
                resizeCanvas();
                initWaves();
                draw();
            }

            // Asegura que el DOM existe (por si el script se cargó en <head>)
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', start);
            } else {
                start();
            }

            window.addEventListener('resize', () => {
                resizeCanvas();
                initWaves();
            });
        })();
    </script>
</body>

</html>
