@extends('layouts.layoutperfil')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Ayudas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Tailwind desde CDN (quítalo si usas Vite) --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-800">
<x-gtm-noscript />

@section('content')
    <!-- CONTENEDOR PRINCIPAL -->
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex">
            <!-- SIDEBAR -->
            <aside class="w-64 mr-6">
                <div class="flex flex-col space-y-4 mt-5">
                    <a href="{{ url('/mis-ayudas') }}"
                       class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-50 transition">
                        Ayudas disponibles
                    </a>
                    <a href="{{ url('/ayudas-solicitadas') }}"
                       class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-50 transition">
                        Ayudas solicitadas
                    </a>
                </div>
            </aside>

            <!-- CONTENIDO PRINCIPAL -->
            <main class="flex-1">
                <h1 class="text-3xl font-bold mb-4">Mis Ayudas</h1>

                <!-- Tarjeta verde -->
                <div class="bg-green-100 border border-green-200 text-green-800 rounded p-4 mb-6">
                    <h2 class="text-xl font-semibold">
                        Estamos de enhorabuena, te pertenecen {{ count($ayudas) }} ayuda/s por valor de
                        <strong>{{ number_format($cuantia_total, 2, ',', '.') }}€</strong>
                    </h2>
                    <a href="/planes"
                       class="inline-block bg-green-600 text-white px-4 py-2 rounded mt-2 hover:bg-green-700 transition">
                        Solicitar ya
                    </a>
                    <hr class="my-3 border-green-300">
                    <h3 class="text-base">En desarrollo. Aún no puedes solicitar ayudas</h3>
                </div>

                <h2 class="text-2xl font-semibold mb-4">Ayudas disponibles</h2>

                @if ($ayudas->isEmpty())
                    <div class="bg-blue-50 border border-blue-200 text-blue-900 rounded p-3">
                        <strong>No hay ayudas disponibles en este momento.</strong>
                    </div>
                @else
                    @foreach ($ayudas as $ayuda)
                        <div class="bg-white border border-gray-200 rounded shadow-sm p-4 mb-4">
                            <h5 class="text-lg font-bold mb-2">{{ $ayuda->nombre_ayuda }}</h5>
                            <p class="text-gray-700 text-sm leading-5">
                                <strong>Organismo:</strong> {{ $ayuda->organo->nombre_organismo ?? 'Desconocido' }}<br>
                                <strong>Fechas:</strong>
                                {{ \Carbon\Carbon::parse($ayuda->fecha_inicio)->format('d/m/Y') }} -
                                {{ \Carbon\Carbon::parse($ayuda->fecha_fin)->format('d/m/Y') }}<br>
                                <strong>Presupuesto total:</strong> {{ $ayuda->presupuesto_formateado }}<br>
                            </p>
                            <a href="#"
                               class="inline-block mt-3 bg-blue-600 text-white px-4 py-1.5 rounded hover:bg-blue-700 transition">
                                Solicitar
                            </a>
                        </div>
                    @endforeach
                @endif
            </main>
        </div>
    </div>
    @endsection
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script>
         anime({
      targets: '.card',
      opacity: [0, 1],        // de invisible a visible
      translateY: [30, 0],    // de 30px abajo a 0
      delay: anime.stagger(150), // escalona cada tarjeta 150ms
      duration: 2000,
      easing: 'easeOutExpo'
    });
    </script>

    {{-- Bootstrap JS (opcional) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

