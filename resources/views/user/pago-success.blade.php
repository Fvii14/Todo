<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Exitoso</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta http-equiv="refresh" content="5;url={{ route('user.AyudasSolicitadas') }}">
</head>
<body class="bg-green-50 flex items-center justify-center h-screen">
    <x-gtm-noscript />
    <div class="bg-white p-10 md:p-16 rounded-lg shadow-2xl text-center max-w-md mx-auto">
        <div class="mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-green-500 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">¡Operación Exitosa!</h1>
        <p class="text-gray-600 mb-8 text-lg">
            Tu solicitud ha sido procesada correctamente.
        </p>
        <p class="text-gray-500 text-sm">
            Serás redirigido en <span id="countdown">5</span> segundos a tus ayudas solicitadas...
        </p>
        <div class="mt-8">
            <a href="{{ route('user.AyudasSolicitadas') }}" class="text-green-600 hover:text-green-700 font-semibold">
                Si no eres redirigido, haz clic aquí.
            </a>
        </div>
    </div>

    <script>
        let seconds = 5;
        const countdownElement = document.getElementById('countdown');
        // Usamos la función route() de Laravel para generar la URL dinámicamente
        const redirectUrl = "{{ route('user.AyudasSolicitadas') }}";

        const interval = setInterval(() => {
            seconds--;
            if (countdownElement) {
                countdownElement.textContent = seconds;
            }
            if (seconds <= 0) {
                clearInterval(interval);
                window.location.href = redirectUrl;
            }
        }, 1000);
    </script>
</body>
</html>