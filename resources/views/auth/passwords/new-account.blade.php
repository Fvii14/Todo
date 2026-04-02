<!DOCTYPE html>
<html lang="es">

<head>
    @extends('layouts.app')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App - Tu Trámite Fácil</title>
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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
        rel="stylesheet">
    <style>
        .btn-indigo-dark {
            background-color: #3C3A60;
        }

        .text-indigo-dark {
            color: #3C3A60;
        }

        .btn-purple-blue {
            background-color: #3c3b60;
        }

        .highlight-text {
            color: #3C3A60;
        }

        .container-box {
            border: 2px solid #e5e7eb91;
            border-radius: 12px;
            background-color: #f9fafc;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border-top: 4px solid #3C3A60;
        }

        .btn-google:hover {
            box-shadow: 0 0 5px #4285F4;
            transform: translateY(-1px);
        }

        .login-form {
            display: none;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .error-input {
            border-color: #ef4444 !important;
            background-color: #fef2f2;
        }

        .transition-all {
            transition: all 0.3s ease;
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .benefit-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            color: #3C3A60;
        }

        .benefit-item i {
            color: #3C3A60;
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }

        .btn-register:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 38, 38, 0.1);
        }
    </style>
</head>

<body class="bg-white h-screen flex items-center justify-center">
    <div class="container-box p-3.5 sm:p-8 w-full mx-2 overflow-auto background-color: #f9fafc;"
        style="max-width: 450px;">
        <div class="flex flex-row items-center justify-center w-full mb-4">
            <img src="/imagenes/cropped-ttflogo_back-192x192-4.png" alt="" class="w-16">
            <h1 class="text-indigo-dark text-3xl text-center font-bold mt-2">Tu Trámite Fácil</h1>
        </div>

        <div class="mb-4 py-4 text-center">

            <div class="flex items-center justify-center">
                <i class="fas fa-user-plus text-2xl highlight-text mr-2"></i>
                <h1 class="text-indigo-dark text-2xl font-bold text-gray-800">Únete a nosotros</h1>
            </div>
            <p class="text-gray-600 mt-2">Crea tu cuenta en menos de 1 minuto</p>

            @if (Cookie::get('ref_code'))
                <div
                    class="mb-4 mt-4 px-4 py-3 rounded-md bg-blue-100 border-l-4 border-emerald-500 text-emerald-800 flex items-center shadow-sm">
                    <i class="fas fa-gift mr-2 text-emerald-600"></i>
                    <span class="text-sm">
                        Estás registrándote con el código de invitación:
                        <strong>{{ Cookie::get('ref_code') }}</strong>
                    </span>
                </div>
            @endif

        </div>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 fade-in">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                @if (session('redirect_to_login'))
                    <div
                        class="mt-3 p-3 bg-blue-50 border-l-4 border-blue-500 text-blue-700 rounded">
                        <p class="font-medium">Serás redirigido al login en <span
                                id="countdown">3</span> segundos...</p>
                    </div>
                @endif
            </div>
        @endif

        <div class="space-y-3">
            <!-- Basado en las pautas de https://developers.google.com/identity/branding-guidelines?hl=es-419#custom-button -->
            <a href="{{ route('login.google') }}"
                class="btn-google flex items-center justify-center w-full p-3 border border-[#747775] rounded-lg bg-white text-indigo-dark font-mediumc transition-all focus:shadow-inner active:bg-[#3030301f] disabled:opacity-40 disabled:cursor-not-allowed">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3 inline-block overflow-visible" version="1.1"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"
                        xmlns:xlink="http://www.w3.org/1999/xlink" style="display: block;">
                        <path fill="#EA4335"
                            d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z">
                        </path>
                        <path fill="#4285F4"
                            d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z">
                        </path>
                        <path fill="#FBBC05"
                            d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z">
                        </path>
                        <path fill="#34A853"
                            d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z">
                        </path>
                        <path fill="none" d="M0 0h48v48H0z"></path>
                    </svg>
                    <span class="font-medium text-xl leading-none align-middle">Regístrate con
                        Google</span>
                </div>
            </a>
            <div class="relative">
                <div class="relative flex justify-center">
                    <span class="px-2 bg-white text-gray-500">o</span>
                </div>
            </div>

            <button id="emailLoginBtn"
                class="btn-indigo-dark w-full py-3 rounded-lg font-medium flex items-center justify-center hover:opacity-90 transition btn-register">
                <i class="fas fa-plus-circle mr-2 text-white"></i>
                <span class="text-white text-xl">Regístrate con email</span>
            </button>

            <form id="emailLoginForm" class="login-form space-y-4"
                action="{{ route('register.account') }}" method="POST">
                @csrf
                <div>
                    <input type="email" name="email" placeholder="Correo electrónico" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>
                <div>
                    <input type="password" name="password" placeholder="Contraseña" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>
                <div>
                    <input type="password" name="password2" placeholder="Repite la contraseña"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>
                @if (Cookie::get('ref_code'))
                    <input type="hidden" name="ref_by" value="{{ Cookie::get('ref_code') }}">
                @endif

                <button type="submit"
                    class="btn-purple-blue text-white w-full py-3 rounded-lg font-medium hover:opacity-90 transition flex items-center justify-center">
                    <i class="fas fa-user-check mr-2"></i>
                    <span>Crear mi cuenta</span>
                </button>
            </form>
            <div class="text-center text-indigo-dark pt-4 border-t border-gray-100 mt-4">
                <p class="text-sm">
                    ¿Ya tienes cuenta? <a href="{{ route('login') }}"
                        class="text-indigo-600 font-semibold hover:underline">Inicia sesión aquí</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('emailLoginBtn').addEventListener('click', function() {
            this.style.display = 'none';
            document.getElementById('emailLoginForm').style.display = 'block';
        });

        document.getElementById('emailLoginForm').addEventListener('submit', function(e) {
            const password = this.querySelector('input[name="password"]').value;
            const password2 = this.querySelector('input[name="password2"]').value;

            // Eliminar errores previos
            this.querySelectorAll('.error-message').forEach(el => el.remove());
            this.querySelectorAll('.error-input').forEach(el => el.classList.remove(
                'error-input'));

            if (password !== password2) {
                e.preventDefault();

                const errorMsg = document.createElement('div');
                errorMsg.classList.add('error-message');
                errorMsg.innerText = 'Las contraseñas no coinciden.';

                const passwordField = this.querySelector('input[name="password2"]');
                passwordField.classList.add('error-input');
                passwordField.parentNode.appendChild(errorMsg);
            }
        });

        // Manejar redirección automática cuando el email ya existe
        @if (session('redirect_to_login'))
            let countdown = 3;
            const countdownElement = document.getElementById('countdown');
            const loginUrl = '{{ route('login') }}';

            const timer = setInterval(function() {
                countdown--;
                countdownElement.textContent = countdown;

                if (countdown <= 0) {
                    clearInterval(timer);
                    window.location.href = loginUrl;
                }
            }, 1000);
        @endif
    </script>
</body>

</html>
