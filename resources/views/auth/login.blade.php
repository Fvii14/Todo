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
    <meta property="og:type" content="website">
    <meta property="og:title" content="Collector by TTF">
    <meta property="og:description" content="Accede a tu información fiscal de forma segura">
    <meta property="og:site_name" content="Collector">
    <meta property="og:image" content="https://d31u1w5651ly23.cloudfront.net/articulos/articulos-247824.jpg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Collector</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-card {
            transition: all 0.3s ease;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.15);
        }

        .input-focus {
            transition: all 0.3s ease;
        }

        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
        }

        .gradient-bg {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans min-h-screen flex items-center justify-center p-4">
    <x-gtm-noscript />
    <div class="w-full max-w-md">
        <div class="login-card bg-white rounded-2xl overflow-hidden">
            <!-- Header -->
            <div class="gradient-bg text-white p-8 text-center">
                <div class="flex justify-center mb-4">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-folder-open text-white text-2xl"></i>
                    </div>
                </div>
                <h1 class="text-3xl font-bold mb-2">Collector</h1>
                <p class="opacity-90">Proyecto en desarrollo por <a href="https://tutramitefacil.es/" target="_blank"
                        class="font-semibold hover:underline">TuTrámiteFácil</a></p>
            </div>

            <!-- Form -->
            <div class="p-8 pt-6">
                <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">Accede a tu cuenta</h2>

                <form action="{{ url('/login') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <div class="relative">
                            <input type="email" name="email" placeholder="Correo electrónico" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl input-focus focus:border-indigo-500 focus:ring-indigo-500">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="relative">
                            <input type="password" name="password" placeholder="Contraseña" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl input-focus focus:border-indigo-500 focus:ring-indigo-500">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                        </div>
                        <!-- Enlace de recuperación de contraseña -->
                        <div class="text-right mt-2">
                            <a href="{{ url('/recuperar') }}"
                                class="text-sm text-indigo-600 hover:text-indigo-800 transition-colors">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition-colors duration-300 flex items-center justify-center">
                        <span>Iniciar sesión</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                    <div class="mt-4">
                        <a href="{{ url('/auth/google') }}"
                            class="w-full py-3.5 px-4 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition-colors duration-300 flex items-center justify-center">
                            <i class="fab fa-google mr-2"></i> Iniciar sesión con Google
                        </a>
                    </div>

                    <a></a>
                </form>

                <div class="mt-6 text-center text-sm text-gray-600">
                    <a href="{{ url('/registerv3') }}"
                        class="text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                        ¿No tienes cuenta? <span class="underline">Regístrate aquí</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer Links -->
        <div class="mt-6 text-center text-xs text-gray-500 space-x-4">
            <a href="#" class="hover:text-gray-700 transition-colors">Términos de servicio</a>
            <a href="#" class="hover:text-gray-700 transition-colors">Privacidad</a>
            <a href="#" class="hover:text-gray-700 transition-colors">Ayuda</a>
        </div>
    </div>

</body>

</html>
