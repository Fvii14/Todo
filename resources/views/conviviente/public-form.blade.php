<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Formulario para conviviente - Tu Trámite Fácil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="{{ asset('js/initPageConditions.js') }}"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            margin: 0;
            background-image: url('https://tutramitefacil.es/wp-content/uploads/2024/01/footer-creative-bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        #agradecimiento {
            display: none;
            min-height: calc(100vh - 200px);
            text-align: center;
            padding: 2rem;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            background-color: #ffffff;
        }

        #firma-canvas {
            touch-action: none;
        }
    </style>
</head>

<body class="bg-gray-50">

    <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-lg my-8 flex flex-col" id="formularioConviviente">
        <form id="convivienteForm" method="POST" action="{{ route('conviviente.store') }}">
            @csrf
            <input type="hidden" name="index" value="{{ $convivienteIndex }}">
            <input type="hidden" name="questionnaire_id" value="{{ $questionnaireId }}">
            <input type="hidden" name="document_id" value="3">

            <!-- Logo -->
            <div class="text-center mb-6 mt-3">
                <img src="https://tutramitefacil.es/wp-content/uploads/2024/04/LOGO-NUEVO-TTF-2024.png"
                    alt="Tu Trámite Fácil" class="mx-auto h-16 w-auto">
            </div>

            <!-- Aviso -->
            <div class="bg-teal-100 border-l-4 border-teal-400 p-4 rounded-md text-gray-700 mb-8">
                <h5 class="font-semibold mb-2">📝 Información importante antes de empezar</h5>
                <p>Este formulario ha sido enviado por un miembro de tu vivienda a través de <strong>Tu Trámite
                        Fácil</strong>.
                    Solo necesitas rellenar unos datos básicos para ayudar en la <strong>tramitación de una ayuda
                        pública de alquiler</strong>.</p>
                <p>Tu información es <strong>confidencial</strong>, se usa exclusivamente para este trámite y no se
                    compartirá con terceros.</p>
                <p>👉 <strong>Rellenarlo te llevará solo unos minutos</strong>, ¡y es clave para que puedan seguir con
                    la solicitud!</p>
            </div>

            <!-- Preguntas -->
            <div id="convivienteModalBody">
                @foreach ($questions as $question)
                    @php
                        $esObligatoria = true;
                        $respuestaFaltante =
                            ($question['answer'] === null ||
                                $question['answer'] === '' ||
                                $question['answer'] === [] ||
                                $question['answer'] == -1) &&
                            $esObligatoria;
                        $enunciado = str_replace(
                            ['El conviviente', 'el conviviente'],
                            ['Usted', 'usted'],
                            $question['text'],
                        );
                        $subtext = isset($question['subtext'])
                            ? str_replace(
                                ['El conviviente', 'el conviviente'],
                                ['Usted', 'usted'],
                                $question['subtext'],
                            )
                            : null;
                    @endphp

                    <div class="mb-6 pb-4 border-b question-item {{ $respuestaFaltante ? 'bg-yellow-100' : '' }}"
                        data-id="{{ $question['id'] }}">
                        <label class="block font-semibold text-gray-800 mb-1">
                            {{ $enunciado }}
                            @if ($esObligatoria)
                                <span class="text-red-600">*</span>
                            @endif
                        </label>

                        @if ($subtext)
                            <div class="text-gray-500 text-sm mb-2">{{ $subtext }}</div>
                        @endif

                        @if ($question['type'] === 'string')
                            <input type="text" name="answers[{{ $question['id'] }}]"
                                value="{{ old('answers.' . $question['id'], $question['answer']) }}"
                                class="border rounded-md p-3 w-full focus:outline-none focus:ring-2 focus:ring-teal-400"
                                pattern="{{ $question['validation']['pattern'] ?? '' }}"
                                title="{{ $question['validation']['error_message'] ?? '' }}">
                        @elseif ($question['type'] === 'boolean')
                            @php
                                $selected = old('answers.' . $question['id'], $question['answer']);
                            @endphp
                            <div class="flex gap-4 mt-1">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="answers[{{ $question['id'] }}]" value="1"
                                        class="form-radio text-teal-400" {{ $selected == 1 ? 'checked' : '' }}>
                                    <span class="ml-2">Sí</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="answers[{{ $question['id'] }}]" value="0"
                                        class="form-radio text-teal-400" {{ $selected == 0 ? 'checked' : '' }}>
                                    <span class="ml-2">No</span>
                                </label>
                            </div>
                        @elseif ($question['type'] === 'select' && !empty($question['options']))
                            <select name="answers[{{ $question['id'] }}]"
                                class="border rounded-md p-3 w-full focus:outline-none focus:ring-2 focus:ring-teal-400">
                                <option value="-1">Seleccione una opción</option>
                                @foreach ($question['options'] as $key => $option)
                                    @php
                                        $value = is_numeric($key) ? $option : $key;
                                        $selected = old('answers.' . $question['id'], $question['answer']);
                                    @endphp
                                    <option value="{{ $value }}"
                                        @if ($selected == $value) selected @endif>{{ $option }}</option>
                                @endforeach
                            </select>
                        @elseif ($question['type'] === 'multiple' && !empty($question['options']))
                            @php
                                $selectedOptions = old('answers.' . $question['id'], $question['answer']);
                                $selectedOptions = is_array($selectedOptions)
                                    ? $selectedOptions
                                    : json_decode($selectedOptions, true) ?? [];
                                $hasNoneSelected = in_array(-1, $selectedOptions) || $question['answer'] === -1 || $question['answer'] === '-1';
                            @endphp
                            <div class="mt-2">
                                @foreach ($question['options'] as $key => $option)
                                    <label class="inline-flex items-center mr-4 mb-2">
                                        <input type="checkbox" 
                                            class="form-checkbox text-teal-400 multiple-checkbox-{{ $question['id'] }}"
                                            data-question-id="{{ $question['id'] }}"
                                            name="answers[{{ $question['id'] }}][]" 
                                            value="{{ $key }}"
                                            {{ $hasNoneSelected ? 'disabled' : '' }}
                                            @if (in_array($key, $selectedOptions)) checked @endif>
                                        <span class="ml-2">{{ $option }}</span>
                                    </label>
                                @endforeach
                                {{-- Opción "Ninguna de las anteriores" --}}
                                <div class="mt-2 pt-2 border-t border-gray-300">
                                    <label class="inline-flex items-center mr-4 mb-2">
                                        <input type="checkbox" 
                                            class="form-checkbox text-teal-400 none-option-{{ $question['id'] }}"
                                            data-question-id="{{ $question['id'] }}"
                                            name="answers[{{ $question['id'] }}][]" 
                                            value="-1"
                                            {{ $hasNoneSelected ? 'checked' : '' }}>
                                        <span class="ml-2 font-semibold">Ninguna de las anteriores</span>
                                    </label>
                                </div>
                            </div>
                        @elseif ($question['type'] === 'date')
                            <input type="date" name="answers[{{ $question['id'] }}]"
                                value="{{ $question['answer'] ?? '' }}"
                                class="border rounded-md p-3 w-full focus:outline-none focus:ring-2 focus:ring-teal-400">
                        @elseif ($question['type'] === 'integer')
                            <input type="number" name="answers[{{ $question['id'] }}]"
                                value="{{ old('answers.' . $question['id'], $question['answer']) }}"
                                class="border rounded-md p-3 w-full focus:outline-none focus:ring-2 focus:ring-teal-400"
                                min="0" step="1">
                        @elseif ($question['type'] === 'builder' && ($question['slug'] ?? '') === 'calculadora')
                            @php
                                $calculadoraData = null;
                                if (!empty($question['answer'])) {
                                    try {
                                        $calculadoraData = is_string($question['answer']) ? json_decode($question['answer'], true) : $question['answer'];
                                    } catch (Exception $e) {
                                        $calculadoraData = null;
                                    }
                                }
                            @endphp
                            <div id="calculadora-{{ $question['id'] }}" class="calculadora-container" data-question-id="{{ $question['id'] }}">
                                <!-- La calculadora se renderizará aquí con JavaScript -->
                            </div>
                            <input type="hidden" name="answers[{{ $question['id'] }}]" id="calculadora-{{ $question['id'] }}-data" value="{{ $question['answer'] ?? '' }}" class="calculadora-input">
                        @else
                            <div class="text-red-600">[Tipo de pregunta "{{ $question['type'] }}" no implementado]
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Firma del conviviente -->
            <div class="mt-6">
                <label class="block font-semibold text-gray-800 mb-1">✍️ Firma <span
                        class="text-red-600">*</span></label>
                <div class="bg-blue-50 border-l-4 border-blue-300 p-2 text-sm text-gray-700 mb-2">
                    ℹ️ La Administración pública requiere la firma de todos los empadronados para tramitar correctamente
                    la ayuda de alquiler. Tu firma solo confirma que resides en la vivienda; no implica responsabilidad
                    económica ni contractual.
                </div>
                <p class="text-gray-500 text-sm mb-2">Firma con el ratón o con el dedo si estás en móvil.</p>
                <canvas id="firma-canvas" width="400" height="200"
                    class="border border-teal-400 rounded-md w-full max-w-md h-48 bg-gray-50"></canvas>
                <div id="firma-error" class="text-red-600 mt-2 hidden">⚠️ Por favor, realiza tu firma antes de
                    continuar.</div>
                <input type="hidden" name="firma_base64" id="firma_base64">
                <button type="button" class="mt-2 px-4 py-2 bg-purple-200 text-black rounded-md hover:bg-purple-300"
                    onclick="clearFirmaCanvas()">Borrar firma</button>
            </div>

            <!-- Submit -->
            <div class="text-right mt-6">
                <button type="submit"
                    class="bg-teal-400 text-gray-800 font-semibold rounded-lg px-6 py-3 hover:bg-teal-500">Enviar
                    formulario</button>
            </div>
        </form>
    </div>

    <!-- Agradecimiento -->
    <div id="agradecimiento" class="flex flex-col justify-center items-center">
        <h3 class="text-gray-800 text-2xl mb-2">✅ ¡Gracias por completar el formulario!</h3>
        <p class="text-gray-600 text-lg">Hemos recibido tus respuestas. Ya puedes cerrar esta página.</p>
        <button onclick="window.location.href='https://tutramitefacil.es/'"
            class="mt-6 px-6 py-3 bg-teal-400 text-gray-800 rounded-lg font-semibold hover:bg-teal-500">👉 ¿Quieres
            saber si tú también puedes recibir alguna ayuda?</button>
    </div>

    <footer class="bg-gray-100 text-center text-gray-500 py-6">
        @include('components.footer')
    </footer>

    <!-- JS -->
    <script>
        window.condiciones = @json($conditions);
        
        // Manejo de "Ninguna de las anteriores" para preguntas múltiples
        function initMultipleNoneOption() {
            function handleNoneOption(questionId, isChecked) {
                const regularCheckboxes = document.querySelectorAll(`.multiple-checkbox-${questionId}`);
                
                if (isChecked) {
                    regularCheckboxes.forEach(cb => {
                        cb.checked = false;
                        cb.disabled = true;
                    });
                } else {
                    regularCheckboxes.forEach(cb => {
                        cb.disabled = false;
                    });
                }
            }

            function handleRegularOption(questionId) {
                const noneCheckbox = document.querySelector(`.none-option-${questionId}`);
                if (noneCheckbox && noneCheckbox.checked) {
                    noneCheckbox.checked = false;
                    handleNoneOption(questionId, false);
                }
            }

            document.querySelectorAll('[class*="none-option-"]').forEach(checkbox => {
                const questionId = checkbox.getAttribute('data-question-id');
                if (!checkbox.hasAttribute('data-listener-added')) {
                    checkbox.setAttribute('data-listener-added', 'true');
                    checkbox.addEventListener('change', function() {
                        handleNoneOption(questionId, this.checked);
                    });
                }
            });

            document.querySelectorAll('[class*="multiple-checkbox-"]').forEach(checkbox => {
                const questionId = checkbox.getAttribute('data-question-id');
                if (!checkbox.hasAttribute('data-listener-added')) {
                    checkbox.setAttribute('data-listener-added', 'true');
                    checkbox.addEventListener('change', function() {
                        handleRegularOption(questionId);
                    });
                }
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar condiciones de visibilidad PRIMERO
            if (window.initPageConditions && window.condiciones) {
                window.initPageConditions(window.condiciones);
            }
            
            // Inicializar "Ninguna de las anteriores" después de un pequeño delay
            setTimeout(initMultipleNoneOption, 150);
            
            const form = document.getElementById('convivienteForm');
            const agradecimiento = document.getElementById('agradecimiento');
            const formularioConviviente = document.getElementById('formularioConviviente');

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validar preguntas visibles (solo las que no están ocultas por condiciones)
                const visibles = form.querySelectorAll('.question-item');
                let hayErrores = false;

                visibles.forEach(function(item) {
                    // Saltar si está oculta por condiciones o no está visible
                    if (item.offsetParent === null || item.style.display === 'none') return;

                    const input = item.querySelector('input:not(.calculadora-input), select, textarea');
                    
                    // Para calculadoras, verificar si hay datos
                    const calculadoraContainer = item.querySelector('.calculadora-container');
                    if (calculadoraContainer) {
                        const calculadoraInput = item.querySelector('.calculadora-input');
                        if (calculadoraInput && (!calculadoraInput.value || calculadoraInput.value === '')) {
                            hayErrores = true;
                            item.classList.add('bg-yellow-100');
                        } else {
                            item.classList.remove('bg-yellow-100');
                        }
                        return;
                    }

                    if (input?.type === 'radio') {
                        const radios = item.querySelectorAll('input[type="radio"]');
                        const algunoMarcado = Array.from(radios).some(r => r.checked);
                        if (!algunoMarcado) {
                            hayErrores = true;
                            item.classList.add('bg-yellow-100');
                        } else {
                            item.classList.remove('bg-yellow-100');
                        }
                    } else if (input?.type === 'checkbox') {
                        const checkboxes = item.querySelectorAll('input[type="checkbox"]');
                        const algunoMarcado = Array.from(checkboxes).some(c => c.checked);
                        if (!algunoMarcado) {
                            hayErrores = true;
                            item.classList.add('bg-yellow-100');
                        } else {
                            item.classList.remove('bg-yellow-100');
                        }
                    } else if (input && input.value === '' && input.value !== '-1') {
                        hayErrores = true;
                        item.classList.add('bg-yellow-100');
                    } else if (input) {
                        item.classList.remove('bg-yellow-100');
                    }
                });

                if (hayErrores) {
                    mostrarPopup('⚠️ Por favor, completa todas las preguntas visibles antes de continuar.');
                    return;
                }

                // Validar firma
                const firmaBase64 = document.getElementById('firma_base64').value;
                if (!firmaBase64 || firmaBase64.trim() === "") {
                    document.getElementById('firma-error').classList.remove('hidden');
                    mostrarPopup('⚠️ Por favor, realiza tu firma antes de enviar el formulario.');
                    return;
                } else {
                    document.getElementById('firma-error').classList.add('hidden');
                }

                // Guardar datos de calculadoras antes de enviar
                document.querySelectorAll('.calculadora-container').forEach(container => {
                    const questionId = container.getAttribute('data-question-id');
                    const calculadora = window.calculadoras && window.calculadoras[questionId];
                    if (calculadora) {
                        const data = calculadora.getData();
                        const hiddenInput = document.getElementById(`calculadora-${questionId}-data`);
                        if (hiddenInput) {
                            hiddenInput.value = JSON.stringify(data);
                        }
                    }
                });

                // Procesar "Ninguna de las anteriores" antes de enviar
                const formData = new FormData(form);
                const processedFormData = new FormData();
                
                // Copiar todos los campos excepto answers
                for (const [key, value] of formData.entries()) {
                    if (!key.startsWith('answers[')) {
                        processedFormData.append(key, value);
                    }
                }
                
                // Procesar respuestas
                const answers = {};
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
                
                // Procesar cada pregunta múltiple
                Object.keys(answers).forEach(questionId => {
                    const noneCheckbox = document.querySelector(`.none-option-${questionId}`);
                    if (noneCheckbox && noneCheckbox.checked) {
                        processedFormData.append(`answers[${questionId}][]`, '-1');
                    } else {
                        const values = answers[questionId].filter(v => v !== '-1' && v !== -1);
                        values.forEach(val => {
                            processedFormData.append(`answers[${questionId}][]`, val);
                        });
                    }
                });
                
                // Añadir respuestas que no son múltiples
                for (const [key, value] of formData.entries()) {
                    if (key.startsWith('answers[') && !key.includes('[]')) {
                        processedFormData.append(key, value);
                    }
                }
                
                // Enviar formulario

                fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: processedFormData
                    })
                    .then(response => {
                        if (response.ok) {
                            formularioConviviente.style.display = 'none';
                            agradecimiento.style.display = 'flex';
                        } else {
                            mostrarPopup(
                                '❌ Hubo un problema al enviar el formulario. Inténtalo de nuevo.');
                        }
                    })
                    .catch(() => {
                        mostrarPopup('❌ Error de red. Por favor, revisa tu conexión.');
                    });
            });
        });

        function mostrarPopup(mensaje) {
            let popup = document.getElementById('popupMensaje');
            let texto = document.getElementById('popupMensajeTexto');
            if (popup && texto) {
                texto.textContent = mensaje;
                popup.classList.remove('hidden');
            }
        }

        function cerrarPopup() {
            const popup = document.getElementById('popupMensaje');
            if (popup) {
                popup.classList.add('hidden');
            }
        }
    </script>

    <!-- Popup -->
    <div id="popupMensaje" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-xl max-w-md text-center shadow-lg border-l-4 border-red-600">
            <p id="popupMensajeTexto" class="text-gray-800 text-lg mb-4"></p>
            <button onclick="cerrarPopup()"
                class="px-4 py-2 bg-teal-400 text-gray-800 rounded-lg font-semibold hover:bg-teal-500">Cerrar</button>
        </div>
    </div>

    <!-- Popup de mensaje -->
    <div id="popupMensaje"
        style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(0, 0, 0, 0.4); z-index: 9999; justify-content: center; align-items: center;">
        <div
            style="background: white; padding: 2rem; border-radius: 16px; max-width: 500px; text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);border-left: 5px solid #ff0000;">
            <p id="popupMensajeTexto" style="color: #3C3A60; font-size: 1.2rem;"></p>
            <button onclick="cerrarPopup()"
                style="margin-top: 1rem; background: #54DEBD; color: #3C3A60;
            border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Cerrar
            </button>
        </div>
    </div>

    

























    <script>
    const canvas = document.getElementById('firma-canvas');
    const ctx = canvas.getContext('2d');
    let dibujando = false;

    function iniciarFirma(e) {
        dibujando = true;
        ctx.beginPath();
        ctx.moveTo(getX(e), getY(e));
    }

    function dibujarFirma(e) {
        if (!dibujando) return;
        ctx.lineTo(getX(e), getY(e));
        ctx.strokeStyle = "#000";
        ctx.lineWidth = 2;
        ctx.lineCap = "round";
        ctx.stroke();
    }

    function detenerFirma() {
        if (!dibujando) return;
        dibujando = false;
        ctx.closePath();
        document.getElementById('firma_base64').value = canvas.toDataURL('image/png');
    }

    function getX(e) {
        return e.touches ? e.touches[0].clientX - canvas.getBoundingClientRect().left : e.offsetX;
    }

    function getY(e) {
        return e.touches ? e.touches[0].clientY - canvas.getBoundingClientRect().top : e.offsetY;
    }

    function clearFirmaCanvas() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('firma_base64').value = "";
    }

    // Eventos ratón
    canvas.addEventListener('mousedown', iniciarFirma);
    canvas.addEventListener('mousemove', dibujarFirma);
    canvas.addEventListener('mouseup', detenerFirma);
    canvas.addEventListener('mouseout', detenerFirma);

    // Eventos táctiles (móvil)
    canvas.addEventListener('touchstart', iniciarFirma);
    canvas.addEventListener('touchmove', dibujarFirma);
    canvas.addEventListener('touchend', detenerFirma);

    // Validación antes de enviar
    document.getElementById('convivienteForm').addEventListener('submit', function(e) {
        const firmaBase64 = document.getElementById('firma_base64').value;
        if (!firmaBase64 || firmaBase64.trim() === "") {
            e.preventDefault();
            document.getElementById('firma-error').classList.remove('hidden');
            mostrarPopup('⚠️ Por favor, realiza tu firma antes de enviar el formulario.');
            return false;
        }
        document.getElementById('firma-error').classList.add('hidden');
        
        // Guardar datos de calculadoras antes de enviar
        document.querySelectorAll('.calculadora-container').forEach(container => {
            const questionId = container.getAttribute('data-question-id');
            const calculadora = window.calculadoras && window.calculadoras[questionId];
            if (calculadora) {
                const data = calculadora.getData();
                const hiddenInput = document.getElementById(`calculadora-${questionId}-data`);
                if (hiddenInput) {
                    hiddenInput.value = JSON.stringify(data);
                }
            }
        });
    });
</script>

<!-- Script para la calculadora -->
<script>
    window.calculadoras = {};
    
    function initCalculadora(questionId, initialData = null) {
        const container = document.getElementById(`calculadora-${questionId}`);
        if (!container) return;
        
        const incomes = initialData?.incomes || [];
        let totalMonths = incomes.reduce((sum, inc) => sum + (inc.months || 0), 0);
        
        function formatCurrency(amount) {
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'EUR',
                minimumFractionDigits: 2
            }).format(amount || 0);
        }
        
        function calculateTotals() {
            totalMonths = incomes.reduce((sum, inc) => sum + (inc.months || 0), 0);
            const totalGrossIncome = incomes.reduce((sum, inc) => sum + (inc.annual || 0), 0);
            const estimatedDeductions = totalGrossIncome * 0.15; // 15% de deducciones estimadas
            const netIncome = totalGrossIncome - estimatedDeductions;
            
            return { totalMonths, totalGrossIncome, estimatedDeductions, netIncome };
        }
        
        function render() {
            const { totalMonths, totalGrossIncome, estimatedDeductions, netIncome } = calculateTotals();
            const remainingMonths = Math.max(0, 12 - totalMonths);
            
            container.innerHTML = `
                <div class="bg-white rounded-lg p-4 mb-6 border border-gray-200">
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-gray-700">Meses utilizados (últimos 12)</span>
                            <span class="text-sm font-medium ${remainingMonths === 0 ? 'text-red-600' : 'text-gray-800'}">${totalMonths} / 12</span>
                        </div>
                        <div class="w-full h-2 rounded bg-gray-200 overflow-hidden">
                            <div class="h-2 transition-all ${totalMonths < 9 ? 'bg-green-500' : (totalMonths < 12 ? 'bg-yellow-500' : 'bg-red-500')}" 
                                 style="width: ${Math.min(100, Math.round((totalMonths/12)*100))}%"></div>
                        </div>
                        <div class="mt-1 text-xs ${remainingMonths === 0 ? 'text-red-600' : 'text-gray-500'}">
                            ${remainingMonths === 0 ? 'Has alcanzado el límite de 12 meses' : `Te quedan ${remainingMonths} mes(es) disponibles`}
                        </div>
                    </div>
                    
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded">
                        <p class="text-sm text-blue-900">
                            Introduce tus ingresos brutos de los <strong>últimos 12 meses</strong>. Puedes repartirlos por tipo (Trabajo, Pensión, Prestación, etc.), pero el <strong>total de meses no puede superar 12</strong>.
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de ingreso</label>
                            <select id="calc-type-${questionId}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400">
                                <option value="">Seleccionar...</option>
                                <option value="Trabajo">Trabajo</option>
                                <option value="Pensión">Pensión</option>
                                <option value="Prestación">Prestación</option>
                                <option value="Renta">Renta</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Meses percibidos</label>
                            <input type="number" id="calc-months-${questionId}" min="1" max="${remainingMonths}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400"
                                   placeholder="12" ${remainingMonths === 0 ? 'disabled' : ''}>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Importe medio</label>
                            <input type="number" id="calc-amount-${questionId}" step="0.01" min="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400"
                                   placeholder="1200">
                        </div>
                        <div>
                            <button onclick="addIncome(${questionId})" 
                                    ${remainingMonths === 0 ? 'disabled' : ''}
                                    class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-md font-medium transition-colors">
                                ➕ Añadir ingreso
                            </button>
                        </div>
                    </div>
                    
                    ${incomes.length > 0 ? `
                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                <div class="grid grid-cols-4 gap-4 text-sm font-medium text-gray-700">
                                    <div>Tipo</div>
                                    <div>Meses</div>
                                    <div>Importe medio</div>
                                    <div>Importe anual</div>
                                </div>
                            </div>
                            <div class="divide-y divide-gray-200">
                                ${incomes.map((income, index) => `
                                    <div class="px-4 py-3 hover:bg-gray-50">
                                        <div class="grid grid-cols-4 gap-4 items-center">
                                            <div class="text-sm text-gray-900">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">${income.type}</span>
                                            </div>
                                            <div class="text-sm text-gray-900">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">${income.months} meses</span>
                                            </div>
                                            <div class="text-sm text-gray-900">${formatCurrency(income.amount)}</div>
                                            <div class="text-sm text-gray-900 font-medium">${formatCurrency(income.annual)}</div>
                                            <div class="col-span-4 text-right">
                                                <button onclick="removeIncome(${questionId}, ${index})" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                    Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                                <div class="px-4 py-3 bg-gray-50 flex items-center justify-end gap-6">
                                    <div class="text-sm text-gray-700">
                                        Total anual: <span class="font-semibold">${formatCurrency(totalGrossIncome)}</span>
                                    </div>
                                    <div class="text-sm text-gray-500">Deducciones estimadas: ${formatCurrency(estimatedDeductions)}</div>
                                    <div class="text-sm text-gray-900 font-semibold">Neto estimado: ${formatCurrency(netIncome)}</div>
                                </div>
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
        }
        
        function addIncome(questionId) {
            const type = document.getElementById(`calc-type-${questionId}`).value;
            const months = parseInt(document.getElementById(`calc-months-${questionId}`).value);
            const amount = parseFloat(document.getElementById(`calc-amount-${questionId}`).value);
            
            if (!type || !months || !amount) {
                mostrarPopup('⚠️ Por favor, completa todos los campos del ingreso.');
                return;
            }
            
            const { totalMonths } = calculateTotals();
            if (totalMonths + months > 12) {
                mostrarPopup('⚠️ El total de meses no puede superar 12.');
                return;
            }
            
            incomes.push({
                type: type,
                months: months,
                amount: amount,
                annual: amount * months
            });
            
            document.getElementById(`calc-type-${questionId}`).value = '';
            document.getElementById(`calc-months-${questionId}`).value = '';
            document.getElementById(`calc-amount-${questionId}`).value = '';
            
            render();
            updateHiddenInput(questionId);
        }
        
        function removeIncome(questionId, index) {
            incomes.splice(index, 1);
            render();
            updateHiddenInput(questionId);
        }
        
        function updateHiddenInput(questionId) {
            const data = {
                incomes: incomes,
                totalGrossIncome: calculateTotals().totalGrossIncome,
                estimatedDeductions: calculateTotals().estimatedDeductions,
                netIncome: calculateTotals().netIncome
            };
            const hiddenInput = document.getElementById(`calculadora-${questionId}-data`);
            if (hiddenInput) {
                hiddenInput.value = JSON.stringify(data);
            }
        }
        
        window.calculadoras[questionId] = {
            incomes: incomes,
            addIncome: (qId) => addIncome(qId),
            removeIncome: (qId, idx) => removeIncome(qId, idx),
            getData: () => {
                const { totalGrossIncome, estimatedDeductions, netIncome } = calculateTotals();
                return {
                    incomes: incomes,
                    totalGrossIncome: totalGrossIncome,
                    estimatedDeductions: estimatedDeductions,
                    netIncome: netIncome
                };
            }
        };
        
        // Hacer funciones globales para los botones
        window.addIncome = function(questionId) {
            if (window.calculadoras[questionId]) {
                window.calculadoras[questionId].addIncome(questionId);
            }
        };
        
        window.removeIncome = function(questionId, index) {
            if (window.calculadoras[questionId]) {
                window.calculadoras[questionId].removeIncome(questionId, index);
            }
        };
        
        render();
        updateHiddenInput(questionId);
    }
    
    // Inicializar calculadoras al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        // Esperar a que las condiciones se inicialicen primero
        setTimeout(() => {
            document.querySelectorAll('.calculadora-container').forEach(container => {
                const questionElement = container.closest('.question-item');
                // Solo inicializar si la pregunta está visible
                if (questionElement && questionElement.style.display !== 'none') {
                    const questionId = container.getAttribute('data-question-id');
                    const hiddenInput = document.getElementById(`calculadora-${questionId}-data`);
                    let initialData = null;
                    
                    if (hiddenInput && hiddenInput.value) {
                        try {
                            initialData = JSON.parse(hiddenInput.value);
                        } catch (e) {
                            console.error('Error parsing calculadora data:', e);
                        }
                    }
                    
                    initCalculadora(questionId, initialData);
                }
            });
        }, 100);
        
        // Re-inicializar calculadoras cuando cambien las condiciones
        const originalRefresh = window.refreshVisibleQuestions;
        window.refreshVisibleQuestions = function() {
            if (originalRefresh) originalRefresh();
            
            // Re-inicializar calculadoras visibles después de actualizar visibilidad
            setTimeout(() => {
                document.querySelectorAll('.calculadora-container').forEach(container => {
                    const questionElement = container.closest('.question-item');
                    const questionId = container.getAttribute('data-question-id');
                    
                    // Si la pregunta está visible y la calculadora no está inicializada
                    if (questionElement && questionElement.style.display !== 'none' && !window.calculadoras[questionId]) {
                        const hiddenInput = document.getElementById(`calculadora-${questionId}-data`);
                        let initialData = null;
                        
                        if (hiddenInput && hiddenInput.value) {
                            try {
                                initialData = JSON.parse(hiddenInput.value);
                            } catch (e) {
                                console.error('Error parsing calculadora data:', e);
                            }
                        }
                        
                        initCalculadora(questionId, initialData);
                    }
                });
            }, 50);
        };
    });
</script>

</body>

</html>
