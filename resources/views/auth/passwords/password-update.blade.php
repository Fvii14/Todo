<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contraseña Actualizada</title>
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
            background-color: white;
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
    <div class="container-box p-8 max-w-md w-full mx-4">
        <div class="mb-10 p-4 text-center">
            <h1 class="text-2xl font-bold text-gray-800">¡Contraseña Actualizada!</h1>
            <p class="text-gray-600 mt-2">Tu contraseña ha sido cambiada correctamente</p>
        </div>

        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 fade-in">
            <p class="font-medium">Ahora puedes iniciar sesión con tu nueva contraseña.</p>
        </div>

        <div class="space-y-4">
            <a href="{{ route('login') }}"
                class="btn-purple-blue text-white w-full py-3 rounded-lg font-medium flex items-center justify-center hover:opacity-90 transition">
                Iniciar Sesión
            </a>

            <div class="text-center">
                <a href="{{ url('/') }}" class="text-blue-900 underline">Volver al inicio</a>
            </div>
        </div>
    </div>
</body>

</html>
