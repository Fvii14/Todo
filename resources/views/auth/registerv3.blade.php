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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collector - Verificación AEAT y Carpeta Ciudadana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        .progress-bar {
            height: 6px;
            transition: width 0.6s ease;
        }

        .floating-card {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .input-focus-effect:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
        }
    </style>
</head>

<body
    class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-900 min-h-screen flex items-center justify-center p-4">
    <x-gtm-noscript />

    <div
        class="w-full max-w-2xl bg-white rounded-2xl overflow-hidden floating-card transition-all duration-300 transform hover:-translate-y-1">
        <!-- Progress Bar -->
        <div class="h-2 bg-gray-200 w-full">
            <div id="globalProgress" class="progress-bar bg-indigo-600 h-full" style="width: 0%"></div>
        </div>

        <!-- Header -->
        <div class="p-8 pb-0">
            <div class="flex items-center justify-center mb-6">
                <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mr-4">
                    <i class="fas fa-folder-open text-indigo-600 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Collector</h1>
                    <p class="text-sm text-gray-500">Proyecto en desarrollo por <a href="https://tutramitefacil.es/"
                            target="_blank"
                            class="text-indigo-600 hover:text-indigo-800 transition-colors">TuTrámiteFácil</a></p>
                </div>
            </div>

            <!-- Service Indicator -->
            <span class="text-sm font-medium text-gray-600">Hola <span id="usernamePlaceholder"
                    class="font-semibold text-indigo-600">{{ $username }}</span></span>
            <div class="flex justify-between items-center mt-8 mb-6 px-4">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center mb-2">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <span class="text-xs font-medium text-indigo-600">1. AEAT</span>
                </div>
                <div class="flex-1 h-1 mx-2 bg-indigo-200 rounded-full">
                    <div id="progressIndicator" class="h-full bg-indigo-600 rounded-full" style="width: 0%"></div>
                </div>
                <div class="flex flex-col items-center">
                    <div id="carpetaStep"
                        class="w-10 h-10 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center mb-2">
                        <i class="fas fa-folder"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-500">2. Carpeta Ciudadana</span>
                </div>
            </div>
        </div>

        <!-- Form Container -->
        <div class="p-8 pt-4">
            <!-- AEAT Section -->
            <div id="aeatSection">
                <form id="verificationForm" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="dni" class="block text-sm font-medium text-gray-700 mb-1">DNI/NIE</label>
                            <div class="relative">
                                <input type="text" id="dni"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl input-focus-effect transition-all"
                                    placeholder="12345678X" required>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-id-card text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha de
                                Validez</label>
                            <div class="relative">
                                <input type="date" id="fecha"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl input-focus-effect transition-all"
                                    required>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <!-- <i class="fas fa-calendar-alt text-gray-400"></i> -->
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono de
                                contacto</label>
                            <div class="relative">
                                <input type="number" id="telefono"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl input-focus-effect transition-all"
                                    required>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-phone-alt text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="submitBtn"
                        class="w-full py-3.5 px-4 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all flex items-center justify-center">
                        <span id="submitText">Obtener Código AEAT</span>
                        <i id="submitSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                    </button>
                </form>

                <div id="aeatResult" class="mt-6 p-5 rounded-xl hidden transition-all duration-300">
                    <div class="flex items-start">
                        <div id="aeatResultIcon" class="flex-shrink-0 mr-3"></div>
                        <div>
                            <h4 class="text-lg font-medium" id="aeatResultTitle"></h4>
                            <p id="aeatResultText" class="text-sm mt-1"></p>
                        </div>
                    </div>
                </div>

                <div id="aeatNextSteps"
                    class="mt-6 p-5 bg-blue-50 border border-blue-200 rounded-xl hidden transition-all duration-300">
                    <h4 class="text-lg font-medium text-blue-800 mb-3 flex items-center">
                        <i class="fas fa-list-ol text-blue-500 mr-2"></i> Próximos pasos AEAT
                    </h4>
                    <ol class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start">
                            <span
                                class="flex items-center justify-center bg-blue-100 text-blue-800 rounded-full w-5 h-5 flex-shrink-0 mr-2 text-xs font-medium">1</span>
                            <span>Abre la aplicación <strong>cl@ve PIN</strong> en tu móvil</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="flex items-center justify-center bg-blue-100 text-blue-800 rounded-full w-5 h-5 flex-shrink-0 mr-2 text-xs font-medium">2</span>
                            <span>Acepta la solicitud de verificación recibida</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="flex items-center justify-center bg-blue-100 text-blue-800 rounded-full w-5 h-5 flex-shrink-0 mr-2 text-xs font-medium">3</span>
                            <span>Haz clic en "Escanear AEAT" cuando completes la verificación</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="flex items-center justify-center bg-blue-100 text-blue-800 rounded-full w-5 h-5 flex-shrink-0 mr-2 text-xs font-medium">4</span>
                            <span><strong>Si no tienes la app Cl@ve PIN</strong>, en 1 minuto podrás recibir un PIN por
                                SMS.</span>
                        </li>
                    </ol>

                    <div id="smsFallbackBox" class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg hidden">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mr-3 text-yellow-600">
                                <i class="fas fa-mobile-alt text-xl mt-1"></i>
                            </div>
                            <div>
                                <h5 class="font-medium text-yellow-800">¿No recibiste el código en cl@ve PIN?</h5>
                                <p class="text-sm text-yellow-700 mt-1">Pasado 1 minuto, puedes solicitar un código por
                                    SMS.</p>
                                <button id="requestSmsBtn"
                                    class="mt-2 px-4 py-2 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700 transition-colors">
                                    Solicitar código por SMS
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="pinInputContainer" class="mt-4 hidden">
                        <label for="pinInput" class="block text-sm font-medium text-gray-700 mb-1">Código recibido por
                            SMS</label>
                        <div class="flex space-x-2">
                            <input type="text" id="pinInput"
                                class="flex-1 px-4 py-3 border border-gray-300 rounded-xl input-focus-effect"
                                placeholder="Ej: 4A7B9C">
                            <br>
                            <button id="submitPinBtn"
                                class="px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors">
                                Validar
                            </button>
                        </div>
                    </div>

                    <button id="scanAeatButton"
                        class="w-full mt-4 py-3 px-4 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all flex items-center justify-center">
                        <span id="scanAeatText">Escanear documentos AEAT</span>
                        <i id="scanAeatSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                    </button>
                </div>
            </div>

            <!-- Carpeta Ciudadana Section -->
            <div id="carpetaSection" class="hidden">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">¡AEAT completado!</h3>
                    <p class="text-gray-600 mt-1">Ahora puedes continuar con la Carpeta Ciudadana</p>
                </div>

                <div id="carpetaForm" class="space-y-5">
                    <button id="requestCarpetaButton"
                        class="w-full py-3.5 px-4 bg-purple-600 text-white font-medium rounded-xl hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all flex items-center justify-center">
                        <span id="requestCarpetaText">Solicitar código Carpeta Ciudadana</span>
                        <i id="requestCarpetaSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                    </button>
                </div>

                <div id="carpetaResult" class="mt-6 p-5 rounded-xl hidden transition-all duration-300">
                    <div class="flex items-start">
                        <div id="carpetaResultIcon" class="flex-shrink-0 mr-3"></div>
                        <div>
                            <h4 class="text-lg font-medium" id="carpetaResultTitle"></h4>
                            <p id="carpetaResultText" class="text-sm mt-1"></p>
                        </div>
                    </div>
                </div>

                <div id="carpetaNextSteps"
                    class="mt-6 p-5 bg-blue-50 border border-blue-200 rounded-xl hidden transition-all duration-300">
                    <h4 class="text-lg font-medium text-blue-800 mb-3 flex items-center">
                        <i class="fas fa-list-ol text-blue-500 mr-2"></i> Próximos pasos Carpeta
                    </h4>
                    <ol class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start">
                            <span
                                class="flex items-center justify-center bg-blue-100 text-blue-800 rounded-full w-5 h-5 flex-shrink-0 mr-2 text-xs font-medium">1</span>
                            <span>Abre <strong>cl@ve PIN</strong> nuevamente en tu móvil</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="flex items-center justify-center bg-blue-100 text-blue-800 rounded-full w-5 h-5 flex-shrink-0 mr-2 text-xs font-medium">2</span>
                            <span>Acepta la nueva solicitud de verificación</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="flex items-center justify-center bg-blue-100 text-blue-800 rounded-full w-5 h-5 flex-shrink-0 mr-2 text-xs font-medium">3</span>
                            <span>Haz clic en "Escanear Carpeta" al completar la verificación</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="flex items-center justify-center bg-blue-100 text-blue-800 rounded-full w-5 h-5 flex-shrink-0 mr-2 text-xs font-medium">4</span>
                            <span><strong>Si no tienes la app Cl@ve PIN</strong>, en 1 minuto podrás recibir un PIN por
                                SMS.</span>
                        </li>
                    </ol>

                    <div id="carpetaSmsFallbackBox"
                        class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg hidden">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mr-3 text-yellow-600">
                                <i class="fas fa-mobile-alt text-xl mt-1"></i>
                            </div>
                            <div>
                                <h5 class="font-medium text-yellow-800">¿No recibiste el código en cl@ve PIN?</h5>
                                <p class="text-sm text-yellow-700 mt-1">Pasado 1 minuto, puedes solicitar un código por
                                    SMS.</p>
                                <button id="requestCarpetaSmsBtn"
                                    class="mt-2 px-4 py-2 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700 transition-colors">
                                    Solicitar código por SMS
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="carpetaPinInputContainer" class="mt-4 hidden">
                        <label for="carpetaPinInput" class="block text-sm font-medium text-gray-700 mb-1">Código
                            recibido por SMS</label>
                        <div class="flex space-x-2">
                            <input type="text" id="carpetaPinInput"
                                class="flex-1 px-4 py-3 border border-gray-300 rounded-xl input-focus-effect"
                                placeholder="Ej: 8D2E5F">
                            <button id="submitCarpetaPinBtn"
                                class="px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors">
                                Validar
                            </button>
                        </div>
                    </div>

                    <button id="scanCarpetaButton"
                        class="w-full mt-4 py-3 px-4 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all flex items-center justify-center">
                        <span id="scanCarpetaText">Escanear Carpeta Ciudadana</span>
                        <i id="scanCarpetaSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                    </button>
                </div>
            </div>

            <!-- Completion Section -->
            <div id="completionSection"
                class="mt-6 p-6 bg-green-50 border border-green-200 rounded-xl text-center hidden">
                <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-green-800 mb-2">¡Proceso completado!</h3>
                <p class="text-gray-700 mb-4">Toda la información ha sido recopilada correctamente.</p>
                <button id="newRequestButton"
                    class="py-2.5 px-6 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors">
                    Realizar nueva solicitud
                </button>
            </div>
        </div>
    </div>

    <script>
        let registrationData = {};
        document.getElementById('requestCarpetaSmsBtn').addEventListener('click', async function() {
            const email = document.getElementById('email').value;
            const btn = this;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Solicitando...';

            try {
                const response = await fetch('https://sandboxapp.tutramitefacil.es/puppeteer/api/ClickSMSPin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email,
                        selector: 'CC'
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('carpetaSmsFallbackBox').classList.add('hidden');
                    document.getElementById('carpetaPinInputContainer').classList.remove('hidden');
                } else {
                    alert(data.message || 'Error al solicitar SMS');
                }
            } catch (error) {
                alert('Error de conexión: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Solicitar código por SMS';
            }
        });

        document.getElementById('submitCarpetaPinBtn').addEventListener('click', async function() {
            const email = document.getElementById('email').value;
            const pin = document.getElementById('carpetaPinInput').value.trim();
            const btn = this;

            if (!pin) {
                alert('Por favor ingresa el código recibido por SMS');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Validando...';

            try {
                const response = await fetch(
                    'https://sandboxapp.tutramitefacil.es/puppeteer/api/SubmitSMSPin', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email,
                            pin
                        })
                    });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('carpetaPinInputContainer').classList.add('hidden');
                    alert('¡PIN validado correctamente! Ahora puedes escanear Carpeta Ciudadana.');
                } else {
                    alert(data.message || 'PIN incorrecto');
                }
            } catch (error) {
                alert('Error de conexión: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Validar';
            }
        });

        // Manejador para solicitar SMS
        document.getElementById('requestSmsBtn').addEventListener('click', async function() {
            const email = document.getElementById('email').value;
            const btn = this;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Solicitando...';

            try {
                const response = await fetch('https://sandboxapp.tutramitefacil.es/puppeteer/api/ClickSMSPin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email,
                        selector: 'AEAT'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Ocultar SMS box y mostrar input para PIN
                    document.getElementById('smsFallbackBox').classList.add('hidden');
                    document.getElementById('pinInputContainer').classList.remove('hidden');
                } else {
                    alert(data.message || 'Error al solicitar SMS');
                }
            } catch (error) {
                alert('Error de conexión: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Solicitar código por SMS';
            }
        });

        // Manejador para enviar PIN
        document.getElementById('submitPinBtn').addEventListener('click', async function() {
            const email = document.getElementById('email').value;
            const pin = document.getElementById('pinInput').value.trim();
            const btn = this;

            if (!pin) {
                alert('Por favor ingresa el código recibido por SMS');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Validando...';

            try {
                const response = await fetch(
                    'https://sandboxapp.tutramitefacil.es/puppeteer/api/SubmitSMSPin', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email,
                            pin
                        })
                    });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('pinInputContainer').classList.add('hidden');
                    alert('¡PIN validado correctamente! Ahora puedes escanear AEAT.');
                } else {
                    alert(data.message || 'PIN incorrecto');
                }
            } catch (error) {
                alert('Error de conexión: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Validar';
            }
        });
        // Manejador para el formulario AEAT
        document.getElementById('verificationForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');
            const aeatResult = document.getElementById('aeatResult');
            const aeatResultIcon = document.getElementById('aeatResultIcon');
            const aeatResultTitle = document.getElementById('aeatResultTitle');
            const aeatResultText = document.getElementById('aeatResultText');
            const aeatNextSteps = document.getElementById('aeatNextSteps');

            // Obtener valores del formulario
            const email = document.getElementById('email').value;
            const dni = document.getElementById('dni').value;
            const fechaInput = document.getElementById('fecha');

            // Formatear fecha (AAAA-MM-DD)
            const fecha = new Date(fechaInput.value).toISOString().split('T')[0];

            // Mostrar carga
            submitBtn.disabled = true;
            submitText.textContent = 'Procesando AEAT...';
            submitSpinner.classList.remove('hidden');

            aeatResult.classList.add('hidden');
            aeatNextSteps.classList.add('hidden');

            try {
                // Realizar petición al endpoint AEAT
                const response = await fetch(
                    'https://sandboxapp.tutramitefacil.es/puppeteer/api/AEATVerificationCode', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            dni: dni,
                            fecha: fecha,
                            email: email
                        })
                    });

                const data = await response.json();

                // Mostrar resultado
                aeatResult.classList.remove('hidden');

                if (data.success) {
                    aeatResult.className =
                        'mt-6 p-5 bg-green-50 border border-green-200 rounded-xl transition-all duration-300';
                    aeatResultIcon.innerHTML =
                        '<i class="fas fa-check-circle text-green-500 text-xl mt-1"></i>';
                    aeatResultTitle.textContent = '¡Código AEAT obtenido!';
                    aeatResultText.innerHTML =
                        `Tu código de verificación es: <span class="font-bold text-green-800">${data.verificationCode}</span>`;

                    // Mostrar próximos pasos
                    aeatNextSteps.classList.remove('hidden');
                    // Mostrar opción SMS después de 1 minuto
                    setTimeout(() => {
                        document.getElementById('smsFallbackBox').classList.remove('hidden');
                    }, 60000); // 60 segundos

                    // Actualizar progreso
                    document.getElementById('globalProgress').style.width = '50%';
                    document.getElementById('progressIndicator').style.width = '100%';
                } else {
                    aeatResult.className =
                        'mt-6 p-5 bg-red-50 border border-red-200 rounded-xl transition-all duration-300';
                    aeatResultIcon.innerHTML = '<i class="fas fa-times-circle text-red-500 text-xl mt-1"></i>';
                    aeatResultTitle.textContent = 'Error en AEAT';
                    aeatResultText.textContent = data.message || 'No se pudo obtener el código';
                }
            } catch (error) {
                aeatResult.classList.remove('hidden');
                aeatResult.className =
                    'mt-6 p-5 bg-red-50 border border-red-200 rounded-xl transition-all duration-300';
                aeatResultIcon.innerHTML = '<i class="fas fa-times-circle text-red-500 text-xl mt-1"></i>';
                aeatResultTitle.textContent = 'Error de conexión';
                aeatResultText.textContent = error.message;
            } finally {
                submitBtn.disabled = false;
                submitText.textContent = 'Obtener Código AEAT';
                submitSpinner.classList.add('hidden');
            }
        });

        // Manejador para escanear AEAT
        document.getElementById('scanAeatButton').addEventListener('click', async function() {
            const scanButton = this;
            const scanText = document.getElementById('scanAeatText');
            const scanSpinner = document.getElementById('scanAeatSpinner');

            scanButton.disabled = true;
            scanText.textContent = 'Escaneando AEAT...';
            scanSpinner.classList.remove('hidden');

            try {
                // Llamar al endpoint de escaneo AEAT
                const response = await fetch(
                    'https://sandboxapp.tutramitefacil.es/puppeteer/api/AEATScanInfo', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            dni: document.getElementById('dni').value,
                            fecha: document.getElementById('fecha').value,
                            email: document.getElementById('email').value
                        })
                    });

                const data = await response.json();

                if (data.success) {
                    // Construir el objeto data para la API de registro
                    registrationData = {
                        name: data.data.apellidosYNombres,
                        full_name: data.data.apellidosYNombres,
                        email: document.getElementById('email').value,
                        telefono: document.getElementById('telefono').value,
                        password: document.getElementById('password').value,
                        dni: data.data.nif,
                        domicilio_fiscal: data.data.domicilioFiscal,
                        fecha_nacimiento: data.data.fechaNacimiento,
                        estado_civil: data.data.estadoCivil,
                        sexo: data.data.sexo,
                        casilla435: data.data.casilla435,
                        casilla460: data.data.casilla460,
                        noDeudas: data.data.noDeudas
                    };
                    // Ocultar sección AEAT y mostrar Carpeta Ciudadana
                    document.getElementById('aeatSection').classList.add('hidden');
                    document.getElementById('carpetaSection').classList.remove('hidden');

                    // Actualizar UI de progreso
                    document.getElementById('carpetaStep').className =
                        'w-10 h-10 rounded-full bg-purple-600 text-white flex items-center justify-center mb-2';
                    document.querySelector('#carpetaSection + div').textContent = 'Carpeta Ciudadana';
                    document.getElementById('globalProgress').style.width = '75%';
                } else {
                    alert(`Error: ${data.message || 'No se pudieron escanear los documentos de AEAT'}`);
                }
            } catch (error) {
                alert(`Error en la conexión: ${error.message}`);
            } finally {
                scanButton.disabled = false;
                scanText.textContent = 'Escanear documentos AEAT';
                scanSpinner.classList.add('hidden');
            }
        });

        // Manejador para solicitar código Carpeta Ciudadana
        document.getElementById('requestCarpetaButton').addEventListener('click', async function() {
            const requestButton = this;
            const requestText = document.getElementById('requestCarpetaText');
            const requestSpinner = document.getElementById('requestCarpetaSpinner');
            const carpetaResult = document.getElementById('carpetaResult');
            const carpetaResultIcon = document.getElementById('carpetaResultIcon');
            const carpetaResultTitle = document.getElementById('carpetaResultTitle');
            const carpetaResultText = document.getElementById('carpetaResultText');
            const carpetaNextSteps = document.getElementById('carpetaNextSteps');

            requestButton.disabled = true;
            requestText.textContent = 'Solicitando código...';
            requestSpinner.classList.remove('hidden');

            carpetaResult.classList.add('hidden');
            carpetaNextSteps.classList.add('hidden');

            try {
                const response = await fetch(
                    'https://sandboxapp.tutramitefacil.es/puppeteer/api/CCVerificationCode', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            dni: document.getElementById('dni').value,
                            fecha: document.getElementById('fecha').value,
                            email: document.getElementById('email').value
                        })
                    });
                const data = await response.json();
                console.log(await response)

                // Mostrar resultado
                carpetaResult.classList.remove('hidden');

                if (data.success) {
                    carpetaResult.className =
                        'mt-6 p-5 bg-green-50 border border-green-200 rounded-xl transition-all duration-300';
                    carpetaResultIcon.innerHTML =
                        '<i class="fas fa-check-circle text-green-500 text-xl mt-1"></i>';
                    carpetaResultTitle.textContent = '¡Código Carpeta obtenido!';
                    carpetaResultText.innerHTML =
                        `Tu código de verificación es: <span class="font-bold text-green-800">${data.data}</span>`;

                    setTimeout(() => {
                        document.getElementById('carpetaSmsFallbackBox').classList.remove('hidden');
                    }, 60000);

                    // Mostrar próximos pasos
                    carpetaNextSteps.classList.remove('hidden');
                } else {
                    carpetaResult.className =
                        'mt-6 p-5 bg-red-50 border border-red-200 rounded-xl transition-all duration-300';
                    carpetaResultIcon.innerHTML =
                        '<i class="fas fa-times-circle text-red-500 text-xl mt-1"></i>';
                    carpetaResultTitle.textContent = 'Error en Carpeta';
                    carpetaResultText.textContent = data.message || 'No se pudo obtener el código';
                }
            } catch (error) {
                carpetaResult.classList.remove('hidden');
                carpetaResult.className =
                    'mt-6 p-5 bg-red-50 border border-red-200 rounded-xl transition-all duration-300';
                carpetaResultIcon.innerHTML = '<i class="fas fa-times-circle text-red-500 text-xl mt-1"></i>';
                carpetaResultTitle.textContent = 'Error de conexión';
                carpetaResultText.textContent = error.message;
            } finally {
                requestButton.disabled = false;
                requestText.textContent = 'Solicitar código Carpeta Ciudadana';
                requestSpinner.classList.add('hidden');
            }
        });

        // Manejador para escanear Carpeta Ciudadana
        document.getElementById('scanCarpetaButton').addEventListener('click', async function() {
            const scanButton = this;
            const scanText = document.getElementById('scanCarpetaText');
            const scanSpinner = document.getElementById('scanCarpetaSpinner');

            scanButton.disabled = true;
            scanText.textContent = 'Escaneando Carpeta...';
            scanSpinner.classList.remove('hidden');

            try {
                const response = await fetch('https://sandboxapp.tutramitefacil.es/puppeteer/api/CCScanInfo', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        dni: document.getElementById('dni').value,
                        fecha: document.getElementById('fecha').value,
                        email: document.getElementById('email').value
                    })
                });

                const data = await response.json();
                console.log(response);

                if (data.success) {
                    document.getElementById('globalProgress').style.width = '100%';
                    registrationData.ssStatus = data.ssStatus;
                    registrationData.domicilio = data.domicilioData.find(item => item.key === "Domicilio")
                        ?.value || "";
                    registrationData.codigoPostal = data.domicilioData.find(item => item.key ===
                        "Código Postal")?.value || "";
                    registrationData.municipio = data.domicilioData.find(item => item.key === "Municipio")
                        ?.value || "";
                    registrationData.provincia = data.domicilioData.find(item => item.key === "Provincia")
                        ?.value || "";
                    registrationData.entidadColectiva = data.domicilioData.find(item => item.key ===
                        "Entidad Colectiva")?.value || "";
                    registrationData.entidadSingular = data.domicilioData.find(item => item.key ===
                        "Entidad Singular")?.value || "";
                    registrationData.nucleoDiseminado = data.domicilioData.find(item => item.key ===
                        "Núcleo / Diseminado")?.value || "";
                    registrationData.fechaVariacion = data.domicilioData.find(item => item.key ===
                        "Fecha variación")?.value || "";
                    registrationData.estaTrabajando = data.result.estaTrabajando;
                    axios.post('https://sandboxapp.tutramitefacil.es/register-user', registrationData)
                        .then(function(response) {
                            window.location.href = '/dashboard';
                        })
                        .catch(function(error) {
                            if (error.response && error.response.data.errors) {
                                let errors = error.response.data.errors;
                                let errorMessages = '';
                                for (let key in errors) {
                                    errorMessages += errors[key].join(', ') + '<br>';
                                }
                                document.getElementById('errorMessages').innerHTML = errorMessages;
                            }
                        });
                } else {
                    alert(`Error: ${data.message || 'No se pudo escanear la Carpeta Ciudadana'}`);
                }
            } catch (error) {
                alert(`Error en la conexión: ${error.message}`);
            } finally {
                scanButton.disabled = false;
                scanText.textContent = 'Escanear Carpeta Ciudadana';
                scanSpinner.classList.add('hidden');
            }
        });

        // Manejador para nueva solicitud
        document.getElementById('newRequestButton').addEventListener('click', function() {
            // Resetear formulario
            document.getElementById('verificationForm').reset();

            // Ocultar todas las secciones especiales
            document.getElementById('aeatResult').classList.add('hidden');
            document.getElementById('aeatNextSteps').classList.add('hidden');
            document.getElementById('carpetaResult').classList.add('hidden');
            document.getElementById('carpetaNextSteps').classList.add('hidden');
            document.getElementById('completionSection').classList.add('hidden');

            // Mostrar sección inicial
            document.getElementById('aeatSection').classList.remove('hidden');
            document.getElementById('carpetaSection').classList.add('hidden');

            // Resetear progreso
            document.getElementById('globalProgress').style.width = '0%';
            document.getElementById('progressIndicator').style.width = '0%';
            document.getElementById('carpetaStep').className =
                'w-10 h-10 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center mb-2';
        });
    </script>
</body>

</html>
