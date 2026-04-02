<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planes disponibles - {{ $ayuda->nombre_ayuda ?? 'Ayuda' }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- FontAwesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <style>
        #background-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .product-card {
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .swiper-button-next,
        .swiper-button-prev {
            color: #54DEBD;
            background: white;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 20px;
        }

        .swiper-pagination-bullet-active {
            background: #54DEBD;
        }

        /* Paginación más visible en mobile */
        .swiper-pagination-one-time-mobile,
        .swiper-pagination-monthly-mobile {
            position: relative;
            margin-top: 1.5rem;
        }

        .swiper-pagination-one-time-mobile .swiper-pagination-bullet,
        .swiper-pagination-monthly-mobile .swiper-pagination-bullet {
            width: 12px;
            height: 12px;
            background: #cbd5e1;
            opacity: 1;
            margin: 0 4px;
        }

        .swiper-pagination-one-time-mobile .swiper-pagination-bullet-active,
        .swiper-pagination-monthly-mobile .swiper-pagination-bullet-active {
            background: #6366f1;
            width: 24px;
            border-radius: 6px;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .badge-recomendado {
            position: absolute;
            top: -12px;
            right: 20px;
            background: linear-gradient(135deg, #54DEBD 0%, #3bbfa6 100%);
            color: white;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col relative">
    <canvas id="background-canvas" class="fixed top-0 left-0 w-full h-full z-0 pointer-events-none"></canvas>

    @include('components.header')
    <x-gtm-noscript />

    <main class="relative z-10 flex-1 py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Elige tu plan
                </h1>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Selecciona el plan que mejor se adapte a tus necesidades para esta ayuda
                </p>
            </div>

            @if($productosOneTime->isEmpty() && $productosMonthly->isEmpty())
                <!-- Sin productos -->
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                    <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                    <h2 class="text-2xl font-semibold text-gray-700 mb-2">No hay productos disponibles</h2>
                    <p class="text-gray-500">No se encontraron productos relacionados con esta ayuda.</p>
                    <a href="{{ route('user.home') }}" class="inline-block mt-6 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Volver a mis ayudas
                    </a>
                </div>
            @else
                <!-- Productos de Pago Único -->
                @if($productosOneTime->isNotEmpty())
                    <div class="mb-16">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-3xl font-bold text-gray-900 mb-2">
                                    <i class="fas fa-credit-card text-indigo-600 mr-3"></i>
                                    Planes de Pago Único
                                </h2>
                                <p class="text-gray-600">Paga una sola vez y accede a todos los beneficios</p>
                            </div>
                        </div>

                        <!-- Grid Desktop - Productos fijos -->
                        <div class="hidden md:grid md:grid-cols-3 md:gap-6 max-w-5xl mx-auto">
                            @foreach($productosOneTime as $index => $producto)
                                <div class="bg-white rounded-2xl shadow-lg p-8 h-full flex flex-col relative product-card {{ ($producto->pivot->recomendado ?? false) ? 'ring-4 ring-indigo-200' : '' }}">
                                    @if($producto->pivot->recomendado ?? false)
                                        <span class="badge-recomendado">
                                            <i class="fas fa-star mr-1"></i>Recomendado
                                        </span>
                                    @endif
                                    
                                    <div class="flex-1">
                                        <div class="text-center mb-6">
                                            <div class="w-20 h-20 mx-auto mb-4 rounded-full gradient-bg flex items-center justify-center">
                                                <i class="fas fa-check-circle text-white text-3xl"></i>
                                            </div>
                                            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $producto->product_name }}</h3>
                                            <div class="mb-4">
                                                <span class="text-4xl font-bold text-indigo-600">{{ number_format($producto->price / 100, 2, ',', '.') }}€</span>
                                                <span class="text-gray-500 text-sm ml-2">pago único</span>
                                            </div>
                                            @if(!is_null($producto->commission_pct))
                                                <div class="mb-2">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                        Nuestra comisión: {{ number_format($producto->commission_pct, 2, ',', '.') }}%
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        <ul class="space-y-3 mb-8">
                                            @if($producto->servicios && $producto->servicios->count() > 0)
                                                @foreach($producto->servicios as $servicio)
                                                    <li class="flex items-start">
                                                        <i 
                                                            class="{{ $servicio->icono ?? 'fas fa-check-circle' }} mr-3 mt-1"
                                                            style="color: {{ $servicio->color ?? '#10b981' }};"
                                                        ></i>
                                                        <span class="text-gray-700">{{ $servicio->nombre }}</span>
                                                    </li>
                                                @endforeach
                                            @else
                                                <li class="flex items-start">
                                                    <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                                                    <span class="text-gray-700">Preparación completa de la solicitud</span>
                                                </li>
                                                <li class="flex items-start">
                                                    <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                                                    <span class="text-gray-700">Revisión de documentos</span>
                                                </li>
                                                <li class="flex items-start">
                                                    <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                                                    <span class="text-gray-700">Presentación de la ayuda</span>
                                                </li>
                                                <li class="flex items-start">
                                                    <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                                                    <span class="text-gray-700">Seguimiento del proceso</span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>

                                    <button onclick="handleStripeCheckout('{{ $producto->id }}')"
                                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                                        <i class="fas fa-arrow-right mr-2"></i>
                                        Seleccionar plan
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <!-- Carrusel Mobile -->
                        <div class="block md:hidden max-w-sm mx-auto relative">
                            @if($productosOneTime->count() > 1)
                                <div class="text-center mb-4">
                                    <p class="text-sm text-gray-600 flex items-center justify-center">
                                        <i class="fas fa-arrows-alt-h mr-2 text-indigo-500"></i>
                                        Desliza para ver más planes ({{ $productosOneTime->count() }} disponibles)
                                    </p>
                                </div>
                            @endif
                            <div class="swiper-container-one-time-mobile">
                                <div class="swiper-wrapper">
                                    @foreach($productosOneTime as $index => $producto)
                                        <div class="swiper-slide">
                                            <div class="bg-white rounded-2xl shadow-lg p-6 h-full flex flex-col relative product-card {{ ($producto->pivot->recomendado ?? false) ? 'ring-4 ring-indigo-200' : '' }}">
                                                @if($producto->pivot->recomendado ?? false)
                                                    <span class="badge-recomendado">
                                                        <i class="fas fa-star mr-1"></i>Recomendado
                                                    </span>
                                                @endif
                                                
                                                <div class="flex-1">
                                                    <div class="text-center mb-4">
                                                        <div class="w-16 h-16 mx-auto mb-3 rounded-full gradient-bg flex items-center justify-center">
                                                            <i class="fas fa-check-circle text-white text-2xl"></i>
                                                        </div>
                                                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $producto->product_name }}</h3>
                                                        <div class="mb-3">
                                                            <span class="text-3xl font-bold text-indigo-600">{{ number_format($producto->price / 100, 2, ',', '.') }}€</span>
                                                            <span class="text-gray-500 text-xs ml-1">pago único</span>
                                                        </div>
                                                        @if(!is_null($producto->commission_pct))
                                                            <div class="mb-2">
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold bg-yellow-100 text-yellow-800">
                                                                    Nuestra comisión: {{ number_format($producto->commission_pct, 2, ',', '.') }}%
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <ul class="space-y-2 mb-6 text-sm">
                                                        
                                                            @foreach($producto->servicios as $servicio)
                                                                <li class="flex items-start">
                                                                    <i 
                                                                        class="{{ $servicio->icono ?? 'fas fa-check-circle' }} mr-2 mt-0.5 text-xs"
                                                                        style="color: {{ $servicio->color ?? '#10b981' }};"
                                                                    ></i>
                                                                    <span class="text-gray-700">{{ $servicio->nombre }}</span>
                                                                </li>
                                                            @endforeach
                                                        
            
                                                    </ul>
                                                </div>

                                                <button onclick="handleStripeCheckout('{{ $producto->id }}')"
                                                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-300 shadow-lg">
                                                    <i class="fas fa-arrow-right mr-2"></i>
                                                    Seleccionar
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <!-- Paginación más visible -->
                                <div class="swiper-pagination-one-time-mobile mt-6"></div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Productos de Pago Mensual -->
                @if($productosMonthly->isNotEmpty())
                    <div class="mb-16">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-3xl font-bold text-gray-900 mb-2">
                                    <i class="fas fa-calendar-alt text-purple-600 mr-3"></i>
                                    Suscripciones Mensuales
                                </h2>
                                <p class="text-gray-600">Paga mensualmente y accede a todas las ayudas del año</p>
                            </div>
                        </div>

                        <!-- Grid Desktop - Productos fijos -->
                        <div class="hidden md:grid md:grid-cols-3 md:gap-6 max-w-5xl mx-auto">
                            @foreach($productosMonthly as $index => $producto)
                                <div class="bg-white rounded-2xl shadow-lg p-8 h-full flex flex-col relative product-card {{ ($producto->pivot->recomendado ?? false) ? 'ring-4 ring-purple-200' : '' }}">
                                    @if($producto->pivot->recomendado ?? false)
                                        <span class="badge-recomendado">
                                            <i class="fas fa-star mr-1"></i>Recomendado
                                        </span>
                                    @endif
                                    
                                    <div class="flex-1">
                                        <div class="text-center mb-6">
                                            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center">
                                                <i class="fas fa-infinity text-white text-3xl"></i>
                                            </div>
                                            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $producto->product_name }}</h3>
                                            <div class="mb-4">
                                                <span class="text-4xl font-bold text-purple-600">{{ number_format($producto->price / 100, 2, ',', '.') }}€</span>
                                                <span class="text-gray-500 text-sm ml-2">/mes</span>
                                            </div>
                                            @if(!is_null($producto->commission_pct))
                                                <div class="mb-2">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                        Nuestra comisión: {{ number_format($producto->commission_pct, 2, ',', '.') }}%
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        <ul class="space-y-3 mb-8">
                                            
                                                @foreach($producto->servicios as $servicio)
                                                    <li class="flex items-start">
                                                        <i 
                                                            class="{{ $servicio->icono ?? 'fas fa-check-circle' }} mr-3 mt-1"
                                                            style="color: {{ $servicio->color ?? '#10b981' }};"
                                                        ></i>
                                                        <span class="text-gray-700">{{ $servicio->nombre }}</span>
                                                    </li>
                                                @endforeach
                                            
                                               
                                        </ul>
                                    </div>

                                    <button onclick="handleStripeCheckout('{{ $producto->id }}')"
                                        class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                                        <i class="fas fa-arrow-right mr-2"></i>
                                        Seleccionar plan
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <!-- Carrusel Mobile -->
                        <div class="block md:hidden max-w-sm mx-auto relative">
                            @if($productosMonthly->count() > 1)
                                <div class="text-center mb-4">
                                    <p class="text-sm text-gray-600 flex items-center justify-center">
                                        <i class="fas fa-arrows-alt-h mr-2 text-purple-500"></i>
                                        Desliza para ver más planes ({{ $productosMonthly->count() }} disponibles)
                                    </p>
                                </div>
                            @endif
                            <div class="swiper-container-monthly-mobile">
                                <div class="swiper-wrapper">
                                    @foreach($productosMonthly as $index => $producto)
                                        <div class="swiper-slide">
                                            <div class="bg-white rounded-2xl shadow-lg p-6 h-full flex flex-col relative product-card {{ ($producto->pivot->recomendado ?? false) ? 'ring-4 ring-purple-200' : '' }}">
                                                @if($producto->pivot->recomendado ?? false)
                                                    <span class="badge-recomendado">
                                                        <i class="fas fa-star mr-1"></i>Recomendado
                                                    </span>
                                                @endif
                                                
                                                <div class="flex-1">
                                                    <div class="text-center mb-4">
                                                        <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center">
                                                            <i class="fas fa-infinity text-white text-2xl"></i>
                                                        </div>
                                                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $producto->product_name }}</h3>
                                                        <div class="mb-3">
                                                            <span class="text-3xl font-bold text-purple-600">{{ number_format($producto->price / 100, 2, ',', '.') }}€</span>
                                                            <span class="text-gray-500 text-xs ml-1">/mes</span>
                                                        </div>
                                                        @if(!is_null($producto->commission_pct))
                                                            <div class="mb-2">
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold bg-yellow-100 text-yellow-800">
                                                                    Nuestra comisión: {{ number_format($producto->commission_pct, 2, ',', '.') }}%
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <ul class="space-y-2 mb-6 text-sm">
                                                        @if($producto->servicios && $producto->servicios->count() > 0)
                                                            @foreach($producto->servicios as $servicio)
                                                                <li class="flex items-start">
                                                                    <i 
                                                                        class="{{ $servicio->icono ?? 'fas fa-check-circle' }} mr-2 mt-0.5 text-xs"
                                                                        style="color: {{ $servicio->color ?? '#10b981' }};"
                                                                    ></i>
                                                                    <span class="text-gray-700">{{ $servicio->nombre }}</span>
                                                                </li>
                                                            @endforeach
                                                        @else
                                                            <li class="flex items-start">
                                                                <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5 text-xs"></i>
                                                                <span class="text-gray-700">Todas las ayudas</span>
                                                            </li>
                                                            <li class="flex items-start">
                                                                <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5 text-xs"></i>
                                                                <span class="text-gray-700">Avisos personalizados</span>
                                                            </li>
                                                            <li class="flex items-start">
                                                                <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5 text-xs"></i>
                                                                <span class="text-gray-700">Soporte continuo</span>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>

                                                <button onclick="handleStripeCheckout('{{ $producto->id }}')"
                                                    class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-300 shadow-lg">
                                                    <i class="fas fa-arrow-right mr-2"></i>
                                                    Seleccionar
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <!-- Paginación más visible -->
                                <div class="swiper-pagination-monthly-mobile mt-6"></div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Botón volver -->
                <div class="text-center mt-12">
                    <a href="{{ route('user.home') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver a mis ayudas
                    </a>
                </div>
            @endif
        </div>
    </main>

    @include('components.footer')

    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        // Función para manejar el checkout de Stripe
        function handleStripeCheckout(productId) {
            console.log("Botón clickeado, producto ID:", productId);

            fetch(`/checkout`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    product_id: productId
                })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.id) {
                    var stripe = Stripe("{{ config('services.stripe.key') }}");
                    stripe.redirectToCheckout({
                        sessionId: data.id
                    });
                } else if (data.url) {
                    // Si viene una URL directa (para pagos únicos)
                    window.location.href = data.url;
                } else {
                    alert('Error al crear la sesión de pago');
                }
            })
            .catch(function(error) {
                console.error('Error en el fetch:', error);
                alert('Hubo un error, intente nuevamente');
            });
        }

        // Inicializar carrusel de pago único (Mobile) - Desktop ya no usa carrusel
        @if($productosOneTime->isNotEmpty())
        if (document.querySelector('.swiper-container-one-time-mobile')) {
            new Swiper('.swiper-container-one-time-mobile', {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: false,
                pagination: {
                    el: '.swiper-pagination-one-time-mobile',
                    clickable: true,
                    dynamicBullets: true,
                    dynamicMainBullets: 3,
                },
            });
        }
        @endif

        // Inicializar carrusel de pago mensual (Mobile) - Desktop ya no usa carrusel
        @if($productosMonthly->isNotEmpty())
        if (document.querySelector('.swiper-container-monthly-mobile')) {
            new Swiper('.swiper-container-monthly-mobile', {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: false,
                pagination: {
                    el: '.swiper-pagination-monthly-mobile',
                    clickable: true,
                    dynamicBullets: true,
                    dynamicMainBullets: 3,
                },
            });
        }
        @endif

        // Fondo animado
        const canvas = document.getElementById('background-canvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            let width, height;
            const waves = [];
            const waveCount = 4;

            function resizeCanvas() {
                width = canvas.width = window.innerWidth;
                height = canvas.height = window.innerHeight;
            }

            function initWaves() {
                waves.length = 0;
                for (let i = 0; i < waveCount; i++) {
                    const colors = [
                        `rgba(84, 222, 189, ${0.05 + Math.random() * 0.05})`,
                        `rgba(99, 102, 241, ${0.05 + Math.random() * 0.05})`,
                        `rgba(147, 51, 234, ${0.05 + Math.random() * 0.05})`,
                        `rgba(236, 72, 153, ${0.05 + Math.random() * 0.05})`
                    ];
                    waves.push({
                        offset: Math.random() * 1000,
                        speed: 0.003 + Math.random() * 0.002,
                        amplitude: 450 + Math.random() * 50,
                        frequency: 0.00005 + Math.random() * 0.00005, // Frecuencia aumentada (más ondas por distancia)
                        color: colors[i % colors.length],
                        lineWidth: 4 + Math.random() * 1.5, // Grosor aumentado (antes: 1-1.5, ahora: 2.5-4)
                    });
                }
            }

            function draw() {
                ctx.clearRect(0, 0, width, height);
                const centerY = height / 2;
                for (const wave of waves) {
                    ctx.beginPath();
                    ctx.strokeStyle = wave.color;
                    ctx.lineWidth = wave.lineWidth;
                    for (let x = 0; x < width; x++) {
                        const y = centerY + Math.sin(x * wave.frequency + wave.offset) * wave.amplitude;
                        ctx.lineTo(x, y);
                    }
                    ctx.stroke();
                    wave.offset += wave.speed;
                }
                requestAnimationFrame(draw);
            }

            window.addEventListener('resize', () => {
                resizeCanvas();
                initWaves();
            });

            resizeCanvas();
            initWaves();
            draw();
        }
    </script>
</body>
</html>
