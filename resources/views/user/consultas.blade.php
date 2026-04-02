<meta name="viewport" content="width=device-width, initial-scale=1.0">

<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
        rel="stylesheet">
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
        <x-gtm-noscript />
    @endif
</head>
@include('components.header')

<div class="px-4 pt-6 pb-2 main">
    <canvas id="background-canvas"></canvas>
    <div class="bg-white p-6 rounded-2xl shadow-md w-full max-w-md mx-auto text-center card">
        <h1 class="text-2xl font-bold mb-4">¿Necesitas ayuda? Estamos aquí para ti.</h1>
        <p class="text-sm mb-3 text-gray-500">Elige cómo quieres comunicarte con nosotros:</p>
        <div class="mt-3 mb-4 text-sm text-gray-500">
            <p>Horario: Lunes a Viernes, 9:00 - 18:00</p>
        </div>

        <div class="space-y-4">
            <!-- Botón WhatsApp -->
            <a href="https://wa.me/34603236800" target="_blank"
                class="flex justify-between items-center bg-green-500 hover:bg-green-600 text-white font-semibold py-4 px-6 rounded-xl shadow-lg transition">
                <span class="flex items-center space-x-2">
                    <i class="fab fa-whatsapp"></i>
                    <span>Contactar por WhatsApp</span>
                </span>
                <span class="text-xs text-white">Respuesta rápida</span>
            </a>

            <!-- Botón Teléfono -->
            <a href="tel:+34603236800"
                class="flex justify-between items-center bg-blue-500 hover:bg-blue-600 text-white font-semibold py-4 px-6 rounded-xl shadow-lg transition"
                style="background-color: #54debd;"
                onmouseover="this.style.backgroundColor='#76e1d1'"
                onmouseout="this.style.backgroundColor='#54debd'">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-phone"></i>
                    <span>Llamar por Teléfono</span>
                </span>
                <span class="text-xs text-white">Solo en horario de atención</span>
            </a>

            <!-- Botón Email -->
            <a href="mailto:info@tutramitefacil.es"
                class="flex justify-between items-center text-white font-semibold py-4 px-6 rounded-xl shadow-lg transition"
                style="background-color: #3c3b60;"
                onmouseover="this.style.backgroundColor='#4a4a78'"
                onmouseout="this.style.backgroundColor='#3c3b60'">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-envelope"></i>
                    <span>Enviar un Email</span>
                </span>
                <span class="text-xs text-white">Hasta 24 h en responder</span>
            </a>
        </div>

    </div>
</div>

@include('components.footer')

<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Ubuntu', sans-serif;
        list-style: none;
    }

    div {
        z-index: 1;
    }

    .main {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 2rem 1rem;
        text-align: center;
    }

    canvas#background-canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
        pointer-events: none;
    }
</style>
<script>
    function getRandomInt(max) {
        return Math.floor(Math.random() * max);
    }

    const canvas = document.getElementById('background-canvas');
    const ctx = canvas.getContext('2d');
    let width, height;
    const waveCount = 6;
    const waves = [];

    function resizeCanvas() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    }

    function initWaves() {
        waves.length = 0;
        for (let i = 0; i < waveCount; i++) {
            const colors = [
                `rgba(84, 222, 189, ${0.1 + Math.random() * 0.15})`,
                `rgba(255, 51, 146, ${0.1 + Math.random() * 0.15})`,
                `rgba(52, 49, 75, ${0.1 + Math.random() * 0.15})`,
                `rgba(240, 231, 125, ${0.1 + Math.random() * 0.15})`
            ];
            waves.push({
                offset: Math.random() * 1000,
                speed: 0.005,
                amplitude: 350 + Math.random() * 40,
                frequency: 0.00001 + Math.random() * 0.003,
                color: colors[getRandomInt(4)],
                lineWidth: 1.5 + Math.random() * 0.6,
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

            for (let x = 0; x < width; x++) {
                const y = centerY + Math.sin(x * wave.frequency + wave.offset) * wave.amplitude;
                ctx.lineTo(x, y);
            }

            ctx.stroke();
            wave.offset += wave.speed;
        }

        requestAnimationFrame(draw);
    }

    window.addEventListener('resize', () => {
        resizeCanvas();
        initWaves();
    });

    resizeCanvas();
    initWaves();
    draw();
</script>
