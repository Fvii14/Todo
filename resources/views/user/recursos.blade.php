<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recursos - Tu Trámite Fácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
            --primary-color: #059669;
            --primary-light: #059669;
            --primary-dark: #059669;
            --surface-50: #FAFAFA;
            --surface-100: #F5F5F5;
            --surface-200: #EEEEEE;
            --surface-300: #E0E0E0;
            --surface-800: #424242;
            --success-100: #D1FAE5;
            --success-600: #059669;
            --warning-100: #FEF3C7;
            --warning-600: #D97706;
            --info-100: #DBEAFE;
            --info-600: #2563EB;
            --error-100: #FEE2E2;
            --error-600: #DC2626;
            --glass-effect: rgba(255, 255, 255, 0.08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Ubuntu', sans-serif;
            list-style: none;
        }

        body {
            background-color: var(--surface-50);
            color: var(--surface-800);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            padding: 0 1.5rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 1.5rem;
            position: relative;
            display: inline-block;
        }

        h1:after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            border-radius: 2px;
        }

        /* Card Styles */
        .ayuda-card {
            border: none;
            border-radius: 12px;
            margin-bottom: 2rem;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            position: relative;
        }

        .ayuda-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary-color), var(--primary-light));
        }

        .ayuda-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            transform: translateY(-4px);
        }

        .ayuda-card .card-body {
            padding: 2rem;
        }

        /* Organismo Logo */
        .organismo-logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            border-radius: 12px;
            background: white;
            padding: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--surface-200);
        }

        .logo-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #F3F4F6, #E5E7EB);
            color: #9CA3AF;
        }

        /* Text Styles */
        .ayuda-title {
            font-size: 1.35rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .ayuda-description {
            color: #6B7280;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        /* Status Badges */
        .estado-badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            letter-spacing: 0.02em;
            display: inline-flex;
            align-items: center;
        }

        .estado-badge i {
            margin-right: 0.35rem;
            font-size: 0.7rem;
        }

        .estado-procesando { background-color: var(--warning-100); color: var(--warning-600); }
        .estado-tramitando { background-color: var(--info-100); color: var(--info-600); }
        .estado-tramitada { background-color: var(--success-100); color: var(--success-600); }
        .estado-concedida { background-color: var(--success-100); color: var(--success-600); }
        .estado-rechazada { background-color: var(--error-100); color: var(--error-600); }

        /* Button Styles */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 500;
            letter-spacing: 0.02em;
            padding: 0.625rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(79, 70, 229, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(79, 70, 229, 0.3);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--surface-300);
            color: var(--surface-800);
            font-weight: 500;
            padding: 0.625rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            background: var(--surface-100);
            border-color: var(--surface-300);
            color: var(--surface-800);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 1rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }

        .empty-state i {
            font-size: 3.5rem;
            color: #E5E7EB;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #F9FAFB, #F3F4F6);
            width: 100px;
            height: 100px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 0.75rem;
        }

        .empty-state p {
            color: #6B7280;
            max-width: 500px;
            margin: 0 auto 1.5rem;
        }

        /* Resource Count */
        .resource-count {
            display: inline-flex;
            align-items: center;
            font-size: 0.85rem;
            color: var(--primary-color);
            font-weight: 500;
            background-color: rgba(79, 70, 229, 0.1);
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
        }

        .resource-count i {
            margin-right: 0.35rem;
            font-size: 0.8rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .ayuda-card .row {
                flex-direction: column;
                gap: 1rem;
            }
            
            .ayuda-card .col-auto:last-child {
                align-self: flex-start;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .ayuda-card {
            animation: fadeIn 0.4s ease-out forwards;
        }

        /* Delay animations for each card */
        .ayuda-card:nth-child(1) { animation-delay: 0.1s; }
        .ayuda-card:nth-child(2) { animation-delay: 0.2s; }
        .ayuda-card:nth-child(3) { animation-delay: 0.3s; }
        .ayuda-card:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>

<body>
    @include('components.header')
    <x-gtm-noscript />

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1>Recursos de Mis Ayudas</h1>
        </div>
        
        @if($contrataciones->isEmpty())
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>No tienes contrataciones activas</h3>
                <p>Cuando tengas ayudas contratadas, aquí podrás acceder a todos los recursos disponibles para cada una de ellas.</p>
                <button class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Explorar ayudas
                </button>
            </div>
        @else
            <div class="row">
                @foreach($contrataciones as $contratacion)
                    @if($contratacion->ayuda)
                        <div class="col-12 mb-4">
                            <div class="ayuda-card">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            @if($contratacion->ayuda->organo && $contratacion->ayuda->organo->imagen)
                                                <img src="{{ asset('imagenes/organos/' . $contratacion->ayuda->organo->imagen) }}" 
                                                     alt="Logo {{ $contratacion->ayuda->organo->nombre_organismo }}" 
                                                     class="organismo-logo">
                                            @else
                                                <div class="organismo-logo logo-placeholder">
                                                    <i class="fas fa-building"></i>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="col">
                                            <div class="ayuda-info">
                                                <h5 class="ayuda-title">{{ $contratacion->ayuda->nombre_ayuda }}</h5>
                                                <p class="ayuda-description">
                                                    {{ $contratacion->ayuda->description ?? 'Ayuda disponible para tu situación.' }}
                                                </p>
                                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                                    @if($contratacion->ayuda->recursos->isNotEmpty())
                                                        <span class="resource-count">
                                                            <i class="fas fa-file-alt"></i>
                                                            {{ $contratacion->ayuda->recursos->count() }} recursos
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-auto">
                                            <a href="{{ route('user.ayuda-recurso', $contratacion->id) }}" 
                                               class="btn btn-primary">
                                                <i class="fas fa-arrow-right me-2"></i>
                                                Ver recursos
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    <script>
        document.querySelectorAll('.ayuda-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transition = 'all 0.25s cubic-bezier(0.4, 0, 0.2, 1)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transition = 'all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1)';
            });
        });
    </script>
</body>

</html>