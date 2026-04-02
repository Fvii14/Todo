<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de Contraseña</title>
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
        <div class="mb-10 p-4">
            <h1 class="text-2xl text-center font-bold text-gray-800">Recuperar Contraseña</h1>
            <p class="text-center text-gray-600 mt-2">Introduce tu correo electrónico para recibir el enlace para
                restablecer tu contraseña</p>
        </div>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 fade-in">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 fade-in">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 fade-in">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div>
                <input type="email"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 @error('email') error-input @enderror"
                    id="email" name="email" value="{{ old('email') }}" required placeholder="Correo electrónico">
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit"
                class="btn-purple-blue text-white w-full py-3 rounded-lg font-medium hover:opacity-90 transition">
                Enviar enlace de recuperación
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="{{ route('login') }}" class="text-blue-900 underline">Volver al inicio de sesión</a>
        </div>
    </div>
</body>

</html>
