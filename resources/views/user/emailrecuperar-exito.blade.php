<!DOCTYPE html>
<html lang="es">

<head>
    @extends('layouts.app')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correo Enviado - Tu Trámite Fácil</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .btn-cyan {
            background-color: #19fcc0;
        }

        .btn-purple-blue {
            background-color: #3c3b60;
        }

        .highlight-text {
            color: #D63384;
        }

        .container-box {
            border: 7px solid #e5e7eb91;
            border-radius: 12px;
            /* Bordes redondeados */
            background-color: white;
            /* Fondo blanco */
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
    </style>
</head>

<body class="bg-white h-screen flex items-center justify-center">
    <x-gtm-noscript />
    <div class="container-box p-8 max-w-md w-full mx-4">
        <div class="mb-10 p-4">
            <h1 class="text-2xl text-center font-bold text-gray-800">¡Correo enviado!</h1>
        </div>

        <div class="space-y-4">
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 fade-in">
                Hemos enviado un enlace para restablecer tu contraseña.
                <br>Por favor, revisa tu bandeja de entrada.
            </div>

            <div class="text-center">
                <a href="{{ url('/') }}"
                    class="btn-purple-blue text-white w-full py-3 rounded-lg font-medium hover:opacity-90 transition inline-block">
                    Volver al inicio
                </a>
            </div>
        </div>
    </div>
</body>

</html>
