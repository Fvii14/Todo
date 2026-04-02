<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <!-- Cargar Stripe.js -->
    <script src="https://js.stripe.com/v3/"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <x-gtm-noscript />

    <!-- Contenedor principal del checkout -->
    <div class="container mx-auto px-6 py-8 bg-white shadow-lg rounded-lg">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Plan Summary</h2>
        </div>

        <!-- Resumen del plan -->
        <div class="flex mb-8">
            <div class="flex-shrink-0">
                <!-- Aquí puedes poner el icono o imagen del plan -->
                <img src="https://via.placeholder.com/150" alt="Plan Icon" class="h-24 w-24 rounded-full">
            </div>
            <div class="ml-6">
                <h3 class="text-xl font-semibold">{{ $product->product_name }}</h3>
                <p class="text-sm text-gray-600">{{ $product->description }}</p>
                <p class="mt-2 text-lg font-bold">{{ number_format($product->price, 2) }}€ / mes</p>
            </div>
        </div>

        <!-- Características del plan -->
        <div class="space-y-4 mb-8">
            <div><span class="font-semibold">12 GB RAM</span></div>
            <div><span class="font-semibold">6 vCPU cores</span></div>
            <div><span class="font-semibold">32 TB Traffic</span></div>
            <div><span class="font-semibold">300 GB Cloud Storage</span></div>
        </div>

        <!-- Formulario de pago -->
        <div class="flex justify-end">
            <form action="{{ route('checkout.payment') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <button id="checkout-button" type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                    Subscribe Now
                </button>
            </form>
        </div>
    </div>

    <!-- Script para Stripe Checkout -->
    <script>
        // Inicializa Stripe con tu clave pública
        var stripe = Stripe('pk_test_XXXXXXX'); // Reemplaza con tu clave pública de Stripe

        // Selecciona el botón de checkout
        var checkoutButton = document.getElementById('checkout-button');

        checkoutButton.addEventListener('click', function(event) {
            event.preventDefault(); // Prevenir el comportamiento por defecto del formulario

            // Llamada al backend para crear la sesión de Stripe
            fetch('/checkout/payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), // CSRF Token
                    },
                    body: JSON.stringify({
                        product_id: '{{ $product->id }}' // Pasamos el ID del producto al backend
                    })
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(sessionId) {
                    // Redirigir al usuario a Stripe Checkout usando el sessionId
                    return stripe.redirectToCheckout({
                        sessionId: sessionId.id
                    });
                })
                .then(function(result) {
                    if (result.error) {
                        alert(result.error.message); // Mostrar cualquier error
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error); // Manejar cualquier error
                });
        });
    </script>

</body>

</html>
