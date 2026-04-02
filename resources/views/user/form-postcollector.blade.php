<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario PostCollector</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .2s;
  border-radius: 34px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .2s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #4f46e5; /* indigo-600 */
}

input:checked + .slider:before {
  transform: translateX(20px);
}
</style>

<main class="relative min-h-screen flex items-center justify-center bg-gray-50">
    <x-gtm-noscript />
    <canvas id="background-canvas" class="absolute top-0 left-0 w-full h-full pointer-events-none z-0"></canvas>
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl z-10 relative">
        
        <form action="{{ route('AnswersPostCollector') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div id="question-container" class="space-y-5">
                <h1>¡Estás a punto de terminar! Solo una última pregunta más y habrás finalizado</h1>
                @foreach ($questions as $index => $question)
                    @if (in_array($question['id'], [24, 25, 41]))
                        <div class="question-item" id="question-{{ $index }}" data-id="{{ $question['id'] }}" style="display: none;">
                            <div class="bg-white p-4 rounded-lg shadow-sm mb-3">
                                <label class="block text-lg font-semibold text-gray-700 mb-3">
                                    {{ $question['text'] }}
                                </label>

                                @if ($question['type'] == 'boolean')
                                    <input type="hidden" name="answers[{{ $question['id'] }}]" value="0">
                                    <div class="flex items-center mb-4">
                                        <span class="text-gray-700">No</span>
                                        <label class="switch ml-2">
                                            <input type="checkbox"
                                                name="answers[{{ $question['id'] }}]"
                                                value="1"
                                                @if (old('answers.' . $question['id'], $question['answer']) == 1) checked @endif
                                                class="hidden peer">
                                            <span class="slider round peer-checked:bg-indigo-600"></span>
                                        </label>
                                        <span class="text-gray-700 ml-2">Sí</span>
                                    </div>
                                @elseif ($question['type'] == 'string')
                                    <input type="text"
                                        name="answers[{{ $question['id'] }}]"
                                        value="{{ old('answers.' . $question['id'], $question['answer']) }}"
                                        class="form-input mt-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-gray-100 p-2">
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Botón siguiente -->
            <button type="button" id="nextBtn"
                class="mt-4 bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 transition">
                Siguiente
            </button>

            <!-- Botón enviar (oculto al principio) -->
            <button type="submit" id="submitBtn"
                class="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition hidden">
                Enviar
            </button>

            <!-- Botón atrás (oculto al inicio) -->
            <button type="button" id="backBtn"
                class="mt-4 bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 transition hidden">
                Atrás
            </button>


        </form>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mt-6">
                {{ session('success') }}
            </div>
        @endif
    </div>
</main>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const steps = [
                document.querySelector('[data-id="24"]'),
                document.querySelector('[data-id="25"]'),
                document.querySelector('[data-id="41"]'),
            ];

            const input24 = steps[0]?.querySelector('input[type="checkbox"]');

            const nextBtn = document.getElementById('nextBtn');
            const backBtn = document.getElementById('backBtn');
            const submitBtn = document.getElementById('submitBtn');

            let currentStepIndex = 0;

            // Mostrar solo la primera
            steps.forEach((step, index) => {
                if (step) step.style.display = index === 0 ? 'block' : 'none';
            });

            nextBtn.classList.remove('hidden');
            backBtn.classList.add('hidden');
            submitBtn.classList.add('hidden');

            const goToStep = (index) => {
                steps.forEach((step, i) => {
                    if (step) step.style.display = i === index ? 'block' : 'none';
                });

                currentStepIndex = index;

                // Botones
                backBtn.classList.toggle('hidden', index === 0);
                submitBtn.classList.toggle('hidden', index !== steps.length - 1);
                nextBtn.classList.toggle('hidden', index === steps.length - 1);
            };

            nextBtn.addEventListener('click', () => {
                if (currentStepIndex === 0) {
                    if (input24?.checked) {
                        // Sí → directo a 26
                        goToStep(2);
                    } else {
                        // No → a 25
                        goToStep(1);
                    }
                } else if (currentStepIndex === 1) {
                    goToStep(2); // de 25 a 26
                }
            });

            backBtn.addEventListener('click', () => {
                if (currentStepIndex === 2) {
                    if (input24?.checked) {
                        goToStep(0); // si vino directo, vuelve a 24
                    } else {
                        goToStep(1); // si vino de 25, vuelve ahí
                    }
                } else if (currentStepIndex === 1) {
                    goToStep(0); // de 25 a 24
                }
            });

            input24?.addEventListener('change', () => {
                if (currentStepIndex === 0) {
                    nextBtn.textContent = input24.checked ? 'Ir a última pregunta' : 'Siguiente';
                }
            });

            if (input24?.checked) {
                nextBtn.textContent = 'Ir a última pregunta';
            }
        });
        </script>



        <script>
        const canvas = document.getElementById('background-canvas');
        const ctx = canvas.getContext('2d');

        let width, height;
        const waveCount = 5;
        const waves = [];

        function resizeCanvas() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        }

        // Crear ondas con propiedades aleatorias
        function initWaves() {
            waves.length = 0;
            for (let i = 0; i < waveCount; i++) {
                waves.push({
                    offset: Math.random() * 1000,
                    speed: 0.005,
                    amplitude: 350 + Math.random() * 50,
                    frequency: 0.00001 + Math.random() * 0.003,
                    color: `rgb(89,237,202, ${0.1 + Math.random() * 0.15})`, // azul translúcido
                    targetColor: null,
                    transitionProgress: 1,
                    lineWidth: 3 + Math.random() * 0.6,
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

                // Mover la onda suavemente
                wave.offset += wave.speed;
            }

            requestAnimationFrame(draw);
        }

        // Cambiar color a rojo durante 1 segundo
        function changeLinesToRed() {
            for (const wave of waves) {
                wave.color = 'rgba(255,0,0,0.3)',
                    wave.lineWidth = 1;
            }

            let flashes = 0;
            const maxFlashes = 4; // Número de parpadeos (rojo ↔ normal = 1 parpadeo)
            const flashInterval = 150; // Tiempo entre parpadeos en ms

            const interval = setInterval(() => {
                // Alternar el fondo entre rojo y transparente
                if (flashes % 2 === 0) {
                    canvas.style.backgroundColor = 'rgba(100, 101, 104 )'; // rojo oscuro
                } else {
                    canvas.style.backgroundColor = ''; // fondo original
                }

                flashes++;

                if (flashes >= maxFlashes) {
                    clearInterval(interval);
                    canvas.style.backgroundColor = ''; // aseguramos que vuelve al original
                    // Restaurar el color original de las ondas
                    for (const wave of waves) {
                        wave.color = `rgb(89,237,202, ${0.1 + Math.random() * 0.15})`;
                        wave.lineWidth = 3 + Math.random() * 0.6;
                    }

                }
            }, flashInterval);
        }


        // Inicializar todo
        window.addEventListener('resize', () => {
            resizeCanvas();
            initWaves();
        });

        resizeCanvas();
        initWaves();
        draw();
    </script>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4 mt-4">
                {{ session('success') }}
            </div>
        @endif

    @include('components.footer')
    
</body>
</html>