<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recursos de {{ $contratacion->ayuda->nombre_ayuda }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    @endif
    <style>
        :root {
            --primary-color: #54debc;
            --primary-dark: #3c3b60;
            --primary-light: #e8f9f5;
            --gray-light: #f8f9fa;
            --gray-medium: #e9ecef;
            --text-dark: #212529;
            --text-medium: #495057;
            --text-light: #6c757d;
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            list-style: none;
        }

        body {
            background-color: #f5f7fa;
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            padding: 0 1.5rem;
        }

        /* Botón de regreso */
        .btn-back {
            background-color: white;
            border: 1px solid var(--gray-medium);
            color: var(--text-medium);
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .btn-back:hover {
            background-color: var(--gray-light);
            color: var(--text-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* Header de la ayuda */
        .ayuda-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 2.5rem;
            border-radius: 16px;
            margin-bottom: 2.5rem;
            box-shadow: 0 10px 20px rgba(64, 212, 176, 0.2);
            position: relative;
            overflow: hidden;
        }

        .ayuda-header::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }

        .organismo-logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.2);
            padding: 14px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: var(--transition);
            z-index: 1;
            position: relative;
        }

        .organismo-logo:hover {
            transform: scale(1.05);
        }

        .estado-badge {
            font-size: 0.85rem;
            padding: 0.5rem 1.2rem;
            border-radius: 25px;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        h1 {
            font-size: 2.2rem !important;
            font-weight: 700;
            margin-bottom: 1rem !important;
            line-height: 1.3;
        }

        /* Acordeón de recursos */
        .accordion {
            --bs-accordion-border-radius: 12px;
            --bs-accordion-inner-border-radius: 10px;
            --bs-accordion-btn-padding-x: 1.75rem;
            --bs-accordion-btn-padding-y: 1.5rem;
            --bs-accordion-body-padding-x: 1.75rem;
            --bs-accordion-body-padding-y: 1.5rem;
            --bs-accordion-active-color: var(--primary-dark);
            --bs-accordion-active-bg: var(--primary-light);
            --bs-accordion-btn-focus-box-shadow: 0 0 0 0.25rem rgba(64, 212, 176, 0.25);
        }

        .accordion-button {
            font-weight: 600;
            font-size: 1.15rem;
            color: var(--text-dark);
            transition: var(--transition);
        }

        .accordion-button:not(.collapsed) {
            box-shadow: none;
        }

        .accordion-button::after {
            background-size: 1.2rem;
        }

        .accordion-item {
            margin-bottom: 1.25rem;
            border: none;
            border-radius: 12px !important;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: var(--transition);
        }

        .accordion-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }

        .tipo-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 1rem;
        }

        .tipo-texto { background-color: #e3f2fd; color: #1976d2; }
        .tipo-video { background-color: #fce4ec; color: #c2185b; }
        .tipo-imagen { background-color: #f3e5f5; color: #7b1fa2; }
        .tipo-documento { background-color: #e8f5e9; color: #2e7d32; }

        /* Contenido de recursos */
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 10px;
        }

        .text-content {
            line-height: 1.8;
            font-size: 1.05rem;
            color: var(--text-medium);
        }

        .text-content h3 {
            color: var(--primary-dark);
            margin: 1.5rem 0 1rem;
            font-weight: 600;
        }

        .text-content p {
            margin-bottom: 1.25rem;
        }

        .imagen-container {
            text-align: center;
            margin: 1.5rem 0;
        }

        .imagen-container img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: var(--transition);
        }

        .imagen-container img:hover {
            transform: scale(1.02);
        }

        /* Estado vacío */
        .empty-resources {
            text-align: center;
            padding: 4rem 1rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .empty-resources i {
            font-size: 3.5rem;
            color: var(--gray-medium);
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            width: 100px;
            height: 100px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .empty-resources h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.75rem;
        }

        .empty-resources p {
            color: var(--text-light);
            max-width: 500px;
            margin: 0 auto 1.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .ayuda-header {
                padding: 1.5rem;
            }
            
            .organismo-logo {
                width: 70px;
                height: 70px;
                margin-bottom: 1rem;
            }
            
            h1 {
                font-size: 1.8rem !important;
            }
            
            .accordion-button {
                font-size: 1rem;
                padding: 1.25rem;
            }
            
            .tipo-badge {
                margin-left: 0;
                margin-top: 0.5rem;
                display: inline-flex;
            }
        }
    </style>
</head>

<body>
    @include('components.header')
    <x-gtm-noscript />

    <div class="container my-5">
        <!-- Botón de regreso -->
        <div class="mb-4">
            <a href="{{ route('user.recursos') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Volver a Recursos
            </a>
        </div>

        <!-- Header de la ayuda -->
        <div class="ayuda-header">
            <div class="row align-items-center">
                <div class="col-auto">
                    @if($contratacion->ayuda->organo && $contratacion->ayuda->organo->imagen)
                        <img src="{{ asset('imagenes/organos/' . $contratacion->ayuda->organo->imagen) }}" 
                             alt="Logo {{ $contratacion->ayuda->organo->nombre_organismo }}" 
                             class="organismo-logo">
                    @else
                        <div class="organismo-logo d-flex align-items-center justify-content-center">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                    @endif
                </div>
                
                <div class="col">
                    <h1 class="mb-2">{{ $contratacion->ayuda->nombre_ayuda }}</h1>
                    <p class="mb-3" style="opacity: 0.9;">{{ $contratacion->ayuda->description ?? 'Recursos disponibles para esta ayuda.' }}</p>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        @if($contratacion->ayuda->recursos->isNotEmpty())
                            <span class="estado-badge">
                                <i class="fas fa-file-alt"></i>
                                {{ $contratacion->ayuda->recursos->count() }} recursos disponibles
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recursos en acordeón -->
        @if($contratacion->ayuda->recursos->isEmpty())
            <div class="empty-resources">
                <i class="fas fa-file-alt"></i>
                <h3>No hay recursos disponibles</h3>
                <p>Esta ayuda aún no tiene recursos asociados. Vuelve más tarde.</p>
            </div>
        @else
            <div class="accordion" id="recursosAccordion">
                @foreach($contratacion->ayuda->recursos as $index => $recurso)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ $index }}">
                            <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                    aria-controls="collapse{{ $index }}">
                                {{ $recurso->titulo }}
                                <span class="tipo-badge tipo-{{ $recurso->tipo }}">
                                    <i class="fas fa-{{ $recurso->tipo === 'video' ? 'play-circle' : ($recurso->tipo === 'imagen' ? 'image' : 'file-alt') }}"></i>
                                    {{ ucfirst($recurso->tipo) }}
                                </span>
                            </button>
                        </h2>
                        <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                             aria-labelledby="heading{{ $index }}" data-bs-parent="#recursosAccordion">
                            <div class="accordion-body">
                                @if($recurso->descripcion)
                                    <p class="text-muted mb-3">{{ $recurso->descripcion }}</p>
                                @endif
                                
                                @switch($recurso->tipo)
                                    @case('texto')
                                        <div class="text-content">
                                            {!! $recurso->contenido_texto !!}
                                        </div>
                                        @break
                                        
                                    @case('video')
                                        <div class="video-container">
                                            <iframe 
                                                width="100%" 
                                                height="315" 
                                                src="{{ str_replace('watch?v=', 'embed/', $recurso->url_video) }}" 
                                                frameborder="0" 
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                allowfullscreen>
                                            </iframe>
                                        </div>
                                        @if($recurso->contenido_texto)
                                            <div class="text-content mt-4">
                                                {!! $recurso->contenido_texto !!}
                                            </div>
                                        @endif
                                        @break
                                        
                                    @case('imagen')
                                        <div class="imagen-container">
                                            @if($recurso->archivo_imagen)
                                                <img src="{{ $recurso->imagen_url }}" 
                                                     alt="{{ $recurso->titulo }}" 
                                                     class="img-fluid">
                                            @elseif($recurso->url_imagen)
                                                <img src="{{ $recurso->url_imagen }}" 
                                                     alt="{{ $recurso->titulo }}" 
                                                     class="img-fluid">
                                            @endif
                                        </div>
                                        @if($recurso->contenido_texto)
                                            <div class="text-content">
                                                {!! $recurso->contenido_texto !!}
                                            </div>
                                        @endif
                                        @break
                                    @case('enlace')
                                        <div class="imagen-container">
                                            <a href="{{ $recurso->url_enlace }}" target="_blank">
                                                <i class="fas fa-external-link-alt"></i>
                                                {{ $recurso->url_enlace }}
                                            </a>
                                        </div>
                                        @if($recurso->contenido_texto)
                                            <div class="text-content">
                                                {!! $recurso->contenido_texto !!}
                                            </div>
                                        @endif
                                        @break
                                @endswitch
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Efecto suave al abrir/cerrar acordeón
        document.querySelectorAll('.accordion-button').forEach(button => {
            button.addEventListener('click', () => {
                const accordionItem = button.closest('.accordion-item');
                if (button.classList.contains('collapsed')) {
                    accordionItem.style.transform = 'translateY(0)';
                    accordionItem.style.boxShadow = '0 3px 10px rgba(0,0,0,0.08)';
                } else {
                    accordionItem.style.transform = 'translateY(-5px)';
                    accordionItem.style.boxShadow = '0 8px 20px rgba(0,0,0,0.12)';
                }
            });
        });
    </script>
</body>
</html>